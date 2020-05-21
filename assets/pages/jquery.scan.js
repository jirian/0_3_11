/**
 * Scan
 * This page scans cable barcodes to be processed
 */

function enableFinalize(){
	var localConnectorType = $(document).data('localConnectorType');
	var cableMediaType = $(document).data('cableMediaType');
	var remoteConnectorType = $(document).data('remoteConnectorType');
	
	if(localConnectorType & cableMediaType & remoteConnectorType) {
		$('#buttonFinalize').off('click');
		$('#buttonFinalize').on('click', function(){
			var data = {
				id: $(document).data('cableID'),
				property: 'cableEditable'
				};
			data = JSON.stringify(data);
			$.post('backend/process_cable.php', {'data':data}, function(response){
				var responseJSON = JSON.parse(response);
				if ($(responseJSON.error).size() > 0){
					displayError(responseJSON.error);
				} else {
					destroyEditables();
					$('.requiredFlag').empty();
					$('#buttonFinalize').hide();
				}
			});
		}).prop('disabled', false).show();
	} else {
		$('#buttonFinalize').prop('disabled', true);
	}
	$('#buttonFinalize').show();
	$('.requiredFlag').html('*');
}

function destroyEditables(){
	var localConnector = $('#localConnectorType').html();
	var cableLength = $('#cableLength').html();
	var cableMediaType = $('#cableMediaType').html();
	var remoteConnector = $('#remoteConnectorType').html();
	
	$('#localConnectorType').editable('destroy');
	$('#cableLength').editable('destroy');
	$('#cableMediaType').editable('destroy');
	$('#remoteConnectorType').editable('destroy');
	
	$('#localConnectorTypeContainer').html(localConnector);
	$('#cableLengthContainer').html(cableLength);
	$('#cableMediaTypeContainer').html(cableMediaType);
	$('#remoteConnectorTypeContainer').html(remoteConnector);
}

function buildFullPath(localConnectorCode39){
	var data = {connectorCode39: localConnectorCode39};
	data = JSON.stringify(data);
	$.post('backend/retrieve_path_full.php', {'data':data}).done(function(response){
		var responseJSON = JSON.parse(response);
		if (responseJSON.active == 'inactive'){
			window.location.replace('/app/login.php');
		} else if ($(responseJSON.error).size() > 0){
			displayError(responseJSON.error);
		} else {
			$('#pathContainer').html(responseJSON.success);
			$('.cableArrow').on('click', function(){
				var data = {codeResult: {code: $(this).attr('data-Code39')}};
				scanCallback(data);
			});
		}
	});
}

function validateCode39(scanData){
	return scanData.match('[a-zA-Z0-9]+') ? true : false;
}

function toggleSwitch(switch_elem, on){
    if (on){ // turn it on
        if ($(switch_elem)[0].checked){ // it already is so do 
            // nothing
        }else{
            $(switch_elem).trigger('click').attr("checked", "checked"); // it was off, turn it on
        }
    }else{ // turn it off
        if ($(switch_elem)[0].checked){ // it's already on so 
            $(switch_elem).trigger('click').removeAttr("checked"); // turn it off
        }else{ // otherwise 
            // nothing, already off
        }
    }
}

function scanCallback(data){
	
	var code = data.codeResult.code;
	if(validateCode39(code)) {
		$('#scanModal').modal('hide');
		
		var data = {
			connectorCode39: code,
		};
		data = JSON.stringify(data);
		
		$.post('backend/retrieve_connector_data.php', {'data':data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else if (responseJSON.success != ''){
				var success = responseJSON.success;
				var cable = success.cable;
				var localAttr = cable.localAttrPrefix;
				var remoteAttr = cable.remoteAttrPrefix;
				
				var localConnectorObjectID = cable['local_object_id'];
				var localConnectorObjectFace = cable['local_object_face'];
				var localConnectorObjectDepth = cable['local_object_depth'];
				var localConnectorPortID = cable['local_object_port'];
				var localConnectorNodeID = '4-'+localConnectorObjectID+'-'+localConnectorObjectFace+'-'+localConnectorObjectDepth+'-'+localConnectorPortID;
				
				var remoteConnectorObjectID = cable['remote_object_id'];
				var remoteConnectorObjectFace = cable['remote_object_face'];
				var remoteConnectorObjectDepth = cable['remote_object_depth'];
				var remoteConnectorPortID = cable['remote_object_port'];
				var remoteConnectorNodeID = '4-'+remoteConnectorObjectID+'-'+remoteConnectorObjectFace+'-'+remoteConnectorObjectDepth+'-'+remoteConnectorPortID;
				
				$(document).data('cableID', cable.rowID);
				$(document).data('localConnectorID', cable['localEndID']);
				$(document).data('localConnectorCode39', cable['localEndCode39']);
				$(document).data('localAttrPrefix', localAttr);
				
				$('#localConnectorPathContainer').empty();
				$('#remoteConnectorPathContainer').empty();
				
				$('#localConnectorCode39').html(cable['localEndCode39']);
				$('#localConnectorTypeContainer').html(success.connectorTypeInfo[cable['localConnector']]);
				$('#localConnectorPathContainer').html('<a class="clickableConnectorPath" data-selectedNodeID="'+localConnectorNodeID+'" data-connectorID="'+cable['localEndID']+'" data-modalTitle="Local Connector Path" href="#">'+success.localConnectorFlatPath+'</a>');
				
				$('#cableLengthContainer').html(cable.length);
				$('#cableUnitOfLength').html(cable.unitOfLength);
				$('#cableMediaTypeContainer').html(success.cableMediaTypeInfo[cable.mediaType]);
				
				if(cable['remoteEndID'] != 0) {
					$('#remoteVerify').show();
					$('#remoteInitialize').hide();
					
					$(document).data('remoteConnectorID', cable['remoteEndID']);
					$(document).data('remoteConnectorCode39', cable['remoteEndCode39']);
					$(document).data('remoteAttrPrefix', remoteAttr);
					
					$('#remoteConnectorCode39').html(cable['remoteEndCode39']);
					$('#remoteConnectorTypeContainer').html(success.connectorTypeInfo[cable['remoteConnector']]);
					$('#remoteConnectorPathContainer').html('<a class="clickableConnectorPath" data-selectedNodeID="'+remoteConnectorNodeID+'" data-connectorID="'+cable['remoteEndID']+'" data-modalTitle="Remote Connector Path" href="#">'+success.remoteConnectorFlatPath+'</a>');
				} else {
					$('#remoteVerify').hide();
					$('#remoteInitialize').show();
					$('#remoteConnectorPathContainer').html('-');
				}
				
				//initializeEnvTree();
				initializePathSelector();
				
				$(document).data('verified', 'unknown');
				$('#buttonVerify').prop('disabled', false);
				handleVerification();
				
				if(cable.editable == 1) {
					handleEditables(success);
					$(document).data('localConnectorType', cable['localConnector'] > 0 ? true : false);
					$(document).data('remoteConnectorType', cable['remoteConnector'] > 0 ? true : false);
					$(document).data('cableMediaType', cable['mediaType'] > 0 ? true : false);
					enableFinalize();
				}
				
				buildFullPath(cable['localEndCode39']);
			}
		});
	}
}

function initializePathSelector(){
	$('.clickableConnectorPath').off('click');
	$('.clickableConnectorPath').on('click', function(e){
		e.preventDefault();
		
		var connectorID = $(this).attr('data-connectorID');
		var modalTitle = $(this).attr('data-modalTitle');
		var selectedNodeID = $(this).attr('data-selectedNodeID');
		$(document).data('selectedNodeID', selectedNodeID);
		$(document).data('connectorID', connectorID);
		
		$('#objTree').jstree(true).settings.core.data = {url: 'backend/retrieve_environment-tree.php?scope=portScan&connectorID='+connectorID};
		$('#objTree').jstree(true).refresh();
		$('#objTree').jstree('deselect_all');
		$('#objTree').jstree('select_node', selectedNodeID);
		$('#objectTreeModalLabel').html(modalTitle);
		$('#objectTreeModal').modal('show');
	});
}

function verifyCallback(data){
	var code = data.codeResult.code;
	if(validateCode39(code)) {
		$('#scanModal').modal('hide');
		
		var data = {
			connectorCode39: $(document).data('localConnectorCode39'),
			verifyCode39: code
			};
		data = JSON.stringify(data);
		
		$.post('backend/retrieve_connector_data.php', {'data':data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else if (responseJSON.success != ''){
				$(document).data('verified', responseJSON.success.verified);
				handleVerification();
			} else {
				var errMsg = ['Something unexpected happened.'];
				displayError(errMsg);
			}
		});
	}
}

function initializeCallback(data) {
	
	var code = data.codeResult.code;
	if(validateCode39(code)) {
		$('#scanModal').modal('hide');
		
		var data = {
			connectorCode39: $(document).data('localConnectorCode39'),
			initializeCode39: code
			};
		data = JSON.stringify(data);
		
		$.post('backend/retrieve_connector_data.php', {'data':data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace('/app/login.php');
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else if (responseJSON.success != ''){
				$('#remoteVerify').show();
				$('#remoteInitialize').hide();
				
				var success = responseJSON.success;
				var cable = success.cable;
				var localAttr = cable.localAttrPrefix;
				var remoteAttr = cable.remoteAttrPrefix;
				
				var remoteConnectorObjectID = cable['remote_object_id'];
				var remoteConnectorObjectFace = cable['remote_object_face'];
				var remoteConnectorObjectDepth = cable['remote_object_depth'];
				var remoteConnectorPortID = cable['remote_object_port'];
				var remoteConnectorNodeID = '4-'+remoteConnectorObjectID+'-'+remoteConnectorObjectFace+'-'+remoteConnectorObjectDepth+'-'+remoteConnectorPortID;
				
				$(document).data('remoteConnectorID', cable['remoteEndID']);
				$(document).data('remoteConnectorCode39', cable['remoteEndCode39']);
				$(document).data('remoteAttrPrefix', remoteAttr);
				
				$('#remoteConnectorCode39').html(cable['remoteEndCode39']);
				$('#remoteConnectorTypeContainer').html(success.connectorTypeInfo[cable['remoteConnector']]);
				$('#remoteConnectorPathContainer').html('<a class="clickableConnectorPath" data-selectedNodeID="'+remoteConnectorNodeID+'" data-connectorID="'+cable['remoteEndID']+'" data-modalTitle="Remote Connector Path" href="#">'+success.remoteConnectorFlatPath+'</a>');
				//getConnectorPath(success.remoteAttrPrefix, cable['remoteEndID'], 'remote');
				
				initializePathSelector();
				
				$(document).data('verified', 'unknown');
				$('#buttonVerify').prop('disabled', false);
				handleVerification();
				
				if(cable.editable == 1) {
					handleEditables(success);
					$(document).data('localConnectorType', cable['localConnector'] > 0 ? true : false);
					$(document).data('remoteConnectorType', cable['remoteConnector'] > 0 ? true : false);
					$(document).data('cableMediaType', cable['mediaType'] > 0 ? true : false);
					enableFinalize();
				}
				
				buildFullPath(cable['localEndCode39']);
			}
		});
	}
}

function handleVerification(){
	var remoteConnectorVerified = $(document).data('verified');
	// Clear identifying verify button classes
	$("#buttonVerify").removeClass (function (index, className) {
		return (className.match (/(^|\s)btn-\S+/g) || []).join(' ');
	});
	$("#buttonVerifyIcon").removeClass (function (index, className) {
		return (className.match (/(^|\s)fa-\S+/g) || []).join(' ');
	});
	
	// Add verify button classes depending on verified status
	if (remoteConnectorVerified == 'yes') {
		$('#buttonVerify').addClass('btn-success');
		$('#buttonVerifyIcon').addClass('fa-check');
	} else if (remoteConnectorVerified == 'no') {
		$('#buttonVerify').addClass('btn-danger');
		$('#buttonVerifyIcon').addClass('fa-times');
	} else {
		$('#buttonVerify').addClass('btn-info');
		$('#buttonVerifyIcon').addClass('fa-exclamation');
	}
}

function handleEditables(success){
	var cable = success.cable;
	
	var localConnectorTypeHTML = '<span><a href="#" class="connectorType" id="localConnectorType" data-type="select" data-pk="'+cable['localEndID']+'" data-property="connectorType" data-connectorTypeEnd="localConnectorType"></a></span>';
	$('#localConnectorTypeContainer').html(localConnectorTypeHTML);
	
	var cableLengthHTML ='<span><a href="#" id="cableLength" data-type="number" data-min="1" data-pk="'+cable.rowID+'" data-property="cableLength"></a></span>';
	$('#cableLengthContainer').html(cableLengthHTML);
	
	var cableMediaTypeHTML = '<span><a href="#" id="cableMediaType" data-type="select" data-pk="'+cable.rowID+'" data-property="cableMediaType"></a></span>';
	$('#cableMediaTypeContainer').html(cableMediaTypeHTML);
	
	var remoteConnectorTypeHTML = '<span><a href="#" class="connectorType" id="remoteConnectorType" data-type="select" data-pk="'+cable['remoteEndID']+'" data-property="connectorType" data-connectorTypeEnd="remoteConnectorType">-</a></span>';
	$('#remoteConnectorTypeContainer').html(remoteConnectorTypeHTML);
	
	//Make connector type selectable
	$('.connectorType').editable({
		showbuttons: false,
		mode: 'inline',
		source: success.connectorTypeInfo,
		url: 'backend/process_cable.php',
		params: function(params){
			var data = {
				property: $(this).attr('data-property'),
				id: params.pk,
				value: params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		success: function(response){
			var connectorTypeEnd = $(this).attr('data-connectorTypeEnd');
			var responseJSON = JSON.parse(response);
			if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
				return 'error';
			} else {
				$(document).data(connectorTypeEnd, true);
				enableFinalize();
			}
		}
	});

	//Make length selectable
	$('#cableLength').editable({
		showbuttons: false,
		mode: 'inline',
		url: 'backend/process_cable.php',
		params: function(params){
			var data = {
				property: $(this).attr('data-property'),
				id: params.pk,
				value: params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		success: function(response){
			var responseJSON = JSON.parse(response);
			if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
				return 'error';
			}
		}
	});
	// Media type is required before length can be set
	if(success.cable.mediaType == 0) {
		$('#cableLength').editable('option', 'disabled', true);
	}
	
	//Make cable media type selectable
	$('#cableMediaType').editable({
		showbuttons: false,
		mode: 'inline',
		source: success.cableMediaTypeInfo,
		url: 'backend/process_cable.php',
		params: function(params){
			var data = {
				property: $(this).attr('data-property'),
				id: params.pk,
				value: params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		success: function(response){
			var responseJSON = JSON.parse(response);
			if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
				return 'error';
			} else {
				$('#cableUnitOfLength').html(responseJSON.success);
				$('#cableLength').editable('option', 'disabled', false);
				$(document).data('cableMediaType', true);
				enableFinalize();
			}
		}
	});
	
	//Set value of connector type selectable
	$('#localConnectorType').editable('setValue', cable['localConnector']);
	if(cable['remoteEndID'] > 0) {
		$('#remoteConnectorType').editable('setValue', cable['remoteConnector']);
	}
	$('#cableLength').editable('setValue', cable.length);
	$('#cableMediaType').editable('setValue', cable.mediaType);
}

function configureApp(){
	var App = {
        init: function() {
            var self = this;

            Quagga.init(this.state, function(err) {
                if (err) {
                    return self.handleError(err);
                }
                App.attachListeners();
                App.checkCapabilities();
                Quagga.start();
            });
        },
        handleError: function(err) {
            console.log(err);
        },
        checkCapabilities: function() {
            var track = Quagga.CameraAccess.getActiveTrack();
            var capabilities = {};
            if (typeof track.getCapabilities === 'function') {
                capabilities = track.getCapabilities();
            }
			if(capabilities.torch) {
				$('#flashContainer').show();
			}
        },
        attachListeners: function() {
            var self = this;

            $("#torchCheckbox").on("change", function(e) {
                e.preventDefault();
				var target = $(e.target);
				var state = 'settings.torch';
				var value = target.prop('checked');
                self.setState('settings.torch', value);
            });
        },
        _accessByPath: function(obj, path, val) {
            var parts = path.split('.'),
                depth = parts.length,
                setter = (typeof val !== "undefined") ? true : false;

            return parts.reduce(function(o, key, i) {
                if (setter && (i + 1) === depth) {
                    if (typeof o[key] === "object" && typeof val === "object") {
                        Object.assign(o[key], val);
                    } else {
                        o[key] = val;
                    }
                }
                return key in o ? o[key] : {};
            }, obj);
        },
        _convertNameToState: function(name) {
            return name.replace("_", ".").split("-").reduce(function(result, value) {
                return result + value.charAt(0).toUpperCase() + value.substring(1);
            });
        },
        detachListeners: function() {
			$("#torchCheckbox").off("change");
        },
        applySetting: function(setting, value) {
            var track = Quagga.CameraAccess.getActiveTrack();
            if (track && typeof track.getCapabilities === 'function') {
                switch (setting) {
                case 'zoom':
                    return track.applyConstraints({advanced: [{zoom: parseFloat(value)}]});
                case 'torch':
                    return track.applyConstraints({advanced: [{torch: !!value}]});
                }
            }
        },
        setState: function(path, value) {
            var self = this;

            if (typeof self._accessByPath(self.inputMapper, path) === "function") {
                value = self._accessByPath(self.inputMapper, path)(value);
            }

            if (path.startsWith('settings.')) {
                var setting = path.substring(9);
                return self.applySetting(setting, value);
            }
            self._accessByPath(self.state, path, value);

            App.detachListeners();
            Quagga.stop();
            App.init();
        },
        inputMapper: {
            inputStream: {
                constraints: function(value){
                    if (/^(\d+)x(\d+)$/.test(value)) {
                        var values = value.split('x');
                        return {
                            width: {min: parseInt(values[0])},
                            height: {min: parseInt(values[1])}
                        };
                    }
                    return {
                        deviceId: value
                    };
                }
            },
            numOfWorkers: function(value) {
                return parseInt(value);
            },
            decoder: {
                readers: function(value) {
                    if (value === 'ean_extended') {
                        return [{
                            format: "ean_reader",
                            config: {
                                supplements: [
                                    'ean_5_reader', 'ean_2_reader'
                                ]
                            }
                        }];
                    }
                    return [{
                        format: value + "_reader",
                        config: {}
                    }];
                }
            }
        },
        state: {
            inputStream: {
				name : "Live",
				type : "LiveStream",
				target: document.querySelector('#scanner'),
                constraints: {
                    width: {min: 200},
                    height: {min: 200},
                    facingMode: "environment"
                }
            },
            locator: {
                patchSize: "x-large",
                halfSample: true
            },
            numOfWorkers: navigator.hardwareConcurrency,
            frequency: 10,
            decoder : {
				readers : ["code_39_reader"]
			},
            locate: false,
			multiple: false
        },
        lastResult : null
    };
	
	return App;
}

function initializeEnvTree(){
	
	// Ajax Tree
	$('#objTree')
	.on('select_node.jstree', function(e, data){
		if(data.node.type == 'port') {
			$('#buttonObjectTreeModalSave').prop("disabled", false);
		} else {
			$('#buttonObjectTreeModalSave').prop("disabled", true);
		}
		
	})
	.on('refresh.jstree', function(){
		
		var selectedNodeID = $(document).data('selectedNodeID');
		
		if($('#objTree').jstree(true).get_node(selectedNodeID)) {
			
			// If the selected nodeID exists, select it
			$('#objTree').jstree('deselect_all');
			$('#objTree').jstree('select_node', selectedNodeID);
			
		} else {
			
			// If the selected nodeID does not exist, select the currently selected parent
			var selectedNode = $('#objTree').jstree('get_selected', true);
			if(selectedNode.length) {
				var selectedNodeParentID = selectedNode[0].parent;
				$('#objTree').jstree('deselect_all');
				$('#objTree').jstree('select_node', selectedNodeParentID);
			}
		}
		
	})
	.jstree({
		'core' : {
			'multiple': false,
			'check_callback': function(operation, node, node_parent, node_position, more){
				if(operation == 'move_node'){
					return node_parent.type === 'location';
				}
				return true;
			},
			'themes': {
				'responsive': false
			},
			'data': {'url' : false,
				'data': function (node) {
					return { 'id' : node.id };
				}
			}
		},
		'state' : {
			'key' : 'connectorNavigation'
		},
		"types" : {
			'default' : {
				'icon' : 'fa fa-building'
			},
			'location' : {
				'icon' : 'fa fa-building'
			},
			'pod' : {
				'icon' : 'zmdi zmdi-group-work'
			},
			'cabinet' : {
				'icon' : 'fa fa-server'
			},
			'object' : {
				'icon' : 'fa fa-minus'
			},
			'port' : {
				'icon' : 'fa fa-circle'
			},
			'floorplan' : {
				'icon' : 'fa fa-map-o'
			}
        },
		"plugins" : [ "search", "state", "types", "wholerow" ]
    });
}

function postProcessCable(){
	var data = this;
	dataStringified = JSON.stringify(data);
	$.post('backend/process_cable.php', {data:dataStringified}, function(response){
		var responseJSON = JSON.parse(response);
		if (responseJSON.active == 'inactive'){
			window.location.replace("/");
		} else if ($(responseJSON.error).size() > 0){
			displayErrorElement(responseJSON.error, $('#alertMsgObjTree'));
		} else if (responseJSON.confirm) {
			$('#confirmModal').find('.modal-body').html(responseJSON.data.confirmMsg);
			$('#confirmModal').modal('show');
			
			$(document).data('confirmFunction', postProcessCable);
			$(document).data('confirmData', data);
		} else {
			var connectorPathContainer = $('a[data-connectorid="'+data.connectorID+'"]');
			$(connectorPathContainer).html(responseJSON.success.connectorFlatPath).attr('data-selectedNodeID', data.value);
			$('#objectTreeModal').modal('hide');
			buildFullPath(localConnectorCode39);
		}
	});
}

$(document).ready(function() {

	$('#printFullPath').on('click', function(){
		$('#pathContainer').printThis({
			importStyle: true,
			removeInline: true,
			removeInlineSelector: "img"
		});
	});

	$(document).data('verified', 'unknown');
	$(document).data('localConnectorType', false);
	$(document).data('remoteConnectorType', false);
	$(document).data('cableMediaType', false);
	
	var App = configureApp();
	initializeEnvTree();
	
	$('#buttonScan').on('click', function(){
		$('#alertMsg').empty();
		$(document).data('scanFunction', scanCallback);
		$('#scanModal').modal('show');
	});
	
	$('#buttonVerify').on('click', function(){
		$('#alertMsg').empty();
		$(document).data('scanFunction', verifyCallback);
		$('#scanModal').modal('show');
	});
	
	$('#buttonInitialize').on('click', function(){
		$('#alertMsg').empty();
		$(document).data('scanFunction', initializeCallback);
		$('#scanModal').modal('show');
	});
	
	$('#scanModal').on('hidden.bs.modal', function (e){
		toggleSwitch($('#torchCheckbox'), false);
		if(Quagga.initialized != null) {
			Quagga.stop();
			Quagga.offDetected();
			Quagga.initialized = undefined;
		}
	});
	
	$('#scanModal').on('shown.bs.modal', function (){
		$('#manualEntryInput').focus();
		$('#manualCheckbox').on('change', function(e){
			var manualScan = $(e.target).prop('checked');
			var scanMethod = $('#scanMethod').val();
			if(manualScan == scanMethod) {
				// Switch to manual scanning
				$('#scannerContainer').hide();
				$('#manualEntry').show();
				toggleSwitch($('#torchCheckbox'), false);
				Quagga.stop();
				Quagga.offDetected();
				Quagga.initialized = undefined;
			} else {
				// Switch to barcode scanning
				if($('#scanModal').hasClass('in')) {
					if(Quagga.initialized == null) {
						App.init();
						Quagga.initialized = true;
						Quagga.onDetected($(document).data('scanFunction'));
					}
				}
				$('#manualEntry').hide();
				$('#scannerContainer').show();
			}
		});
		
		if($('#scanModal').hasClass('in')) {
			if($('#scannerContainer').is(':visible')) {
				if(Quagga.initialized == null) {
					App.init();
					Quagga.initialized = true;
					Quagga.onDetected($(document).data('scanFunction'));
				}
			}
		}
	});
	
	$('#manualEntrySubmit').on('click', function(e){
		e.preventDefault();
		var code = $('#manualEntryInput').val();
		var data = {
			codeResult: {
				code: code
			}
		};
		$(document).data('scanFunction')(data);
	});
	
	$('#buttonObjectTreeModalSave').on('click', function(){
		var selectedNode = $('#objTree').jstree('get_selected', true);
		var value = selectedNode[0].data.globalID;
		var connectorID = $(document).data('connectorID');
		var localConnectorCode39 = $(document).data('localConnectorCode39');
		
		var data = {
			property: 'connectionScan',
			value: value,
			connectorID: connectorID
		};
		
		postProcessCable.call(data);
	});
	
	$('#buttonObjectTreeModalClear').on('click', function(){
		var connectorID = $(document).data('connectorID');
		var connectorPath = $('a[data-connectorid="'+connectorID+'"]');
		var selectedNodeID = $(connectorPath).attr('data-selectednodeid');
		var localConnectorCode39 = $(document).data('localConnectorCode39');
		
		var data = {
			property: 'connectionScan',
			value: 'clear',
			connectorID: connectorID
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_cable.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				$(connectorPath).html(responseJSON.success.connectorFlatPath).attr('data-selectedNodeID', '4-0-0-0-0');
				
				var node = $('#objTree').jstree(true).get_node(selectedNodeID);
				if(node) {
					var nodeText = node.text;
					var newText = nodeText.replace('*', '');
					$('#objTree').jstree('rename_node', node, newText);
				}
				$('#objTree').jstree('deselect_all');
				$('#objectTreeModal').modal('hide');
				buildFullPath(localConnectorCode39);
			}
		});
	});
	
	if($('#connectorCodeParam').length) {
		var connectorCode = $('#connectorCodeParam').val();
		var data = {codeResult:{code:connectorCode}};
		scanCallback(data);
	}
	
});

