/**
 * Theme: Uplon Admin Template
 * Author: Coderthemes
 * Tree view
 */

function handlePathFindButton(){
	var buttonState = true;
	if($(document).data('clickedObjPortID') !== null) {
		if($(document).data('selectedFloorplanObjectType') == 'wap') {
			buttonState = true;
		} else {
			buttonState = false;
		}
	}
	
	$('#buttonPathFinder').prop('disabled', buttonState);
	$('#buttonPortConnector').prop('disabled', buttonState);
}

function displayError(errMsg, alertDisplay){
	$(alertDisplay).empty();
	$(errMsg).each(function(index, value){
		var html = '<div class="alert alert-danger" role="alert">';
		html += '<strong>Oops!</strong>  '+value;
		html += '</div>';
		$(alertDisplay).append(html);
	});
	$("html, body").animate({ scrollTop: 0 }, "slow");
}

function clearSelectionDetails(){
	$('.objDetail').html('-');
	$('#containerFullPath').empty();
	$(document).data('clickedObjPortID', null);
	$(document).data('portClickedFlag', false);
	$(document).data('selectedFloorplanObjectType', '');
	handlePathFindButton();
	
	//Clear the hightlight around any highlighted object
	$('.rackObjSelected').removeClass('rackObjSelected');
	
	$('#checkboxPopulated').prop("disabled", true);
	$('#checkboxPopulated').prop("checked", false);
	$('#selectPort').empty();
}

function makeRackObjectsClickable(){
	$('.port').click(function(event){
		$(document).data('portClickedFlag', true);
		
		var portIndex = $(this).data('portIndex');
		
		//Store PortID
		$(document).data('clickedObjPortID', portIndex);
	});
	
	$('#cabinetTable').find('.selectable').click(function(event){
		event.stopPropagation();
		
		// Clear path container
		$('#containerFullPath').empty();

		if ($(document).data('portClickedFlag') === false) {
			if ($(this).data('partitionType') == 'Connectable') {
				$(document).data('clickedObjPortID', 0);
			} else {
				$(document).data('clickedObjPortID', null);
			}
		}
		
		// Handle path finder button
		handlePathFindButton();
		
		if($(this).hasClass('rackObj')) {
			var object = $(this);
			var partitionDepth = 0;
		} else {
			var object = $(this).closest('.rackObj');
			var partitionDepth =  parseInt($(this).data('depth'), 10);
		}
		
		//Store objectID
		var objID = $(object).data('templateObjectId');
		var objFace = $(object).data('objectFace');
		var cabinetFace = $(document).data('currentCabinetFace');

		$(document).data('clickedObjID', objID);
		$(document).data('clickedObjFace', objFace);
		$(document).data('clickedObjPartitionDepth', partitionDepth);
		
		// Highlight selected object
		$('.rackObjSelected').removeClass('rackObjSelected');
		$(this).addClass('rackObjSelected');
		
		if ($(this).data('partitionType') == 'Connectable') {
			processPortSelection();
		} else {
			$('#selectPort').empty();
			$('#selectPort').prop("disabled", true);
			$('#checkboxPopulated').prop("checked", false);
			$('#checkboxPopulated').prop("disabled", true);
		}
		
		//Collect object data
		var data = {
			objID: objID,
			page: 'build',
			objFace: objFace,
			cabinetFace: cabinetFace,
			partitionDepth: partitionDepth
		};
		
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/retrieve_object_details.php", {data:data}, function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				var response = responseJSON.success;
				$('#detailObjName').html(response.objectName);
				$('#detailTemplateName').html(response.templateName);
				$('#detailCategory').html(response.categoryName);
				$('#detailTrunkedTo').html(response.trunkFlatPath);
				$('#detailObjType').html(response.objectType);
				$('#detailObjFunction').html(response.function);
				$('#detailRUSize').html(response.RUSize);
				$('#detailMountConfig').html(response.mountConfig);
				$('#detailPortRange').html(response.portRange);
				$('#detailPortOrientation').html(response.portOrientationName);
				$('#detailPortType').html(response.portType);
				$('#detailMediaType').html(response.mediaType);
				if(response.templateImgExists) {
					$('#detailTemplateImage').html('<img id="elementTemplateImage" src="" height="" width="">');
					$('#elementTemplateImage').attr({
						src:response.templateImgPath,
						height:response.templateImgHeight + 'px',
						width:response.templateImgWidth + '%'
					});
				} else {
					$('#detailTemplateImage').html('None');
				}
				$('#inline-name').editable('option', 'disabled', false);
			}
		});
		$(document).data('portClickedFlag', false);
	});
}

function makeFloorplanObjectsClickable(){
	$('#floorplanContainer').find('.selectable').off('click');
	$('#floorplanContainer').find('.selectable').on('click', function(event){
		event.stopPropagation();
		var objectID = $(this).attr('data-objectID');
		var objectType = $(this).attr('data-type');
		$(document).data('selectedFloorplanObject', $(this));
		$(document).data('selectedFloorplanObjectID', objectID);
		$(document).data('selectedFloorplanObjectType', objectType);
		$(document).data('clickedObjID', objectID);
		$(document).data('clickedObjFace', 0);
		$(document).data('clickedObjPartitionDepth', 0);
		
		var objTableEntrySelection = $('#floorplanObjectTableContainer').find('.selectedObjTableEntry');
		if(objTableEntrySelection.length) {
			var portID = objTableEntrySelection.attr('data-portID');
			if(portID !== 'null') {
				portID = parseInt(portID, 10);
			} else {
				portID = null;
			}
			objTableEntrySelection.removeClass('selectedObjTableEntry');
		} else {
			var firstObjTableEntry = $('#floorplanObjectTableContainer').find('tr[data-id="'+objectID+'"]').first();
			var portID = firstObjTableEntry.attr('data-portID');
			
			if(portID !== 'null') {
				portID = parseInt(portID, 10);
			} else {
				portID = null;
			}
		}
		
		$(document).data('clickedObjPortID', portID);
		if(portID != null) {
			processPortSelection();
		}
		handlePathFindButton();
		
		//Highlight selected object
		$('.floorplanObjSelected').removeClass('floorplanObjSelected');
		$(this).addClass('floorplanObjSelected');
		
		//Collect object data
		var data = {
			objectID: objectID
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/retrieve_floorplan_object_details.php", {data:data}, function(responseJSON){
			var response = JSON.parse(responseJSON);
			if (response.active == 'inactive'){
				window.location.replace("/");
			} else if ($(response.error).size() > 0){
				displayError(response.error);
			} else {
				var response = response.success;
				$('#floorplanDetailName').html(response.name);
				$('#floorplanDetailType').html(objectType);
				$('#floorplanDetailTrunkedTo').html(response.trunkFlatPath);
			}
		});
		$('#floorplanObjectTableBody').children().removeClass('table-info');
		$('#floorplanObjectTableBody').children('[data-id="'+objectID+'"]').addClass('table-info');
	});
}

function makeCableArrowsClickable(){
	$('.cableArrow').off('click');
	$('.cableArrow').on('click', function(){
		var code39 = $(this).attr('data-Code39');
		if(code39 != 0) {
			window.location.href = '/scan.php?connectorCode='+code39;
		}
	});
}

function getDimensions(elem){
	var canvasLeft = $('#canvasBuildSpace').offset().left;
	var canvasTop = $('#canvasBuildSpace').offset().top;
	
	var elemLeft = $(elem).offset().left;
	var elemTop = $(elem).offset().top;
	
	var elemWidth = $(elem).width();
	var elemHeight = $(elem).height();
	var elemCenterX = elemLeft - canvasLeft + (elemWidth / 2);
	var elemCenterY = elemTop - canvasTop  + (elemHeight / 2);
	var elemLeft = elemLeft - canvasLeft;
	var elemRight = elemLeft + elemWidth;
	var elemTop = elemTop - canvasTop;
	var elemBottom = elemTop + elemHeight;
	
	var dimensions = {
		left: elemLeft,
		right: elemRight,
		top: elemTop,
		bottom: elemBottom,
		centerX: elemCenterX,
		centerY: elemCenterY,
		width: elemWidth,
		height: elemHeight
	};
	return dimensions;
}

function drawConnection(elemA, elemB){
	context.strokeStyle = 'blue';
	context.lineWidth = 3;
	context.beginPath();
	
	var connectionStyle = $('#connectionStyle').val();
	
	var canvasDimensions = getDimensions($('#canvasBuildSpace'));
	
	var elemADimensions = getDimensions(elemA);
	var elemAPartition = $(elemA).closest('.partition');
	var elemAPartitionDimensions = getDimensions(elemAPartition);
	
	var elemBDimensions = getDimensions(elemB);
	var elemBPartition = $(elemB).closest('.partition');
	var elemBPartitionDimensions = getDimensions(elemBPartition);
	
	if(elemBDimensions.top >= elemADimensions.top) {
		var elemAPartHBoundary = elemAPartitionDimensions.bottom;
		var elemBPartHBoundary = elemBPartitionDimensions.top;
	} else {
		var elemAPartHBoundary = elemAPartitionDimensions.top;
		var elemBPartHBoundary = elemBPartitionDimensions.bottom;
	}
	
	context.moveTo(elemADimensions.centerX, elemADimensions.centerY);
	
	if(connectionStyle == 0) {
		context.lineTo(elemADimensions.centerX, elemAPartHBoundary);
		context.lineTo(canvasDimensions.left + canvasInset, elemAPartHBoundary);
		context.lineTo(canvasDimensions.left + canvasInset, elemBPartHBoundary);
		context.lineTo(elemBDimensions.centerX, elemBPartHBoundary);
		context.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
	} else if(connectionStyle == 1) {
		context.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
	} else if(connectionStyle == 2) {
		var arcSize = 30;
		context.bezierCurveTo((elemADimensions.centerX - arcSize), elemADimensions.centerY, (elemBDimensions.centerX - arcSize), elemBDimensions.centerY, elemBDimensions.centerX, elemBDimensions.centerY);
	} else {
		context.lineTo(elemBDimensions.centerX, elemBDimensions.centerY);
	}
	context.stroke();
}

function drawTrunk(elemA, elemB){
	context.strokeStyle = 'black';
	context.lineWidth = 3;
	context.beginPath();
	
	var canvasDimensions = getDimensions($('#canvasBuildSpace'));
	
	var elemADimensions = getDimensions(elemA);
	var elemBDimensions = getDimensions(elemB);
	
	context.moveTo(elemADimensions.right, elemADimensions.centerY);
	context.lineTo(canvasDimensions.right - canvasInset, elemADimensions.centerY);
	context.lineTo(canvasDimensions.right - canvasInset, elemBDimensions.centerY);
	context.lineTo(elemBDimensions.right, elemBDimensions.centerY);
	
	context.strokeRect(elemADimensions.left, elemADimensions.top, elemADimensions.width, elemADimensions.height);
	context.strokeRect(elemBDimensions.left, elemBDimensions.top, elemBDimensions.width, elemBDimensions.height);
	context.stroke();
}

function highlightElement(elem){
	context.strokeStyle = 'blue';
	context.beginPath();
	
	var elemDimensions = getDimensions(elem);
	
	context.strokeRect(elemDimensions.left, elemDimensions.top, elemDimensions.width, elemDimensions.height);
}

function makePortsHoverable(){
	resizeCanvas();
	$('#buildSpaceContent').find('.port').each(function(){
		$(this).hover(function(){
			
			var selectedPort = $(this);
			var selectedPeerID = $(selectedPort).data('peerGlobalId');
			
			for(x=0; x<2; x++) {
				
				if(x == 1) {
					var selectedPartition = $(this).closest('.partition');
					var selectedPartitionPeerID = $(selectedPartition).data('peerGlobalId');
					
					if($('#'+selectedPartitionPeerID).length) {
						var selectedPartitionPeer = $('#'+selectedPartitionPeerID);
						drawTrunk(selectedPartition, selectedPartitionPeer);
						
						var selectedPartitionPeerIDArray = selectedPartitionPeerID.split('-');
						var peerID = selectedPartitionPeerIDArray[2];
						var peerFace = selectedPartitionPeerIDArray[3];
						var peerDepth = selectedPartitionPeerIDArray[4];
						var peerPort = $(this).data('portIndex');
						
						var selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
					} else {
						var selectedPort = false;
					}
				}
				
				while($(selectedPort).length) {
					var selectedPortID = $(selectedPort).attr('id');
					var selectedPartition = $(selectedPort).closest('.partition');
					var connectedPortID = $(selectedPort).data('connectedGlobalId');
					var connectedPort = $('#'+connectedPortID);
					highlightElement(selectedPort);
					
					if($(connectedPort).length) {
						highlightElement(connectedPort);
						drawConnection(selectedPort, connectedPort);
						
					} else {
						break;
					}
					
					var connectedPartition = $(connectedPort).closest('.partition');
					var connectedPartitionPeerID = $(connectedPartition).data('peerGlobalId');
					var connectedPartitionPeer = $('#'+connectedPartitionPeerID);
					
					if($(connectedPartitionPeer).length) {
						drawTrunk(connectedPartition, connectedPartitionPeer);
						
						var connectedPartitionPeerIDArray = connectedPartitionPeerID.split('-');
						var peerID = connectedPartitionPeerIDArray[2];
						var peerFace = connectedPartitionPeerIDArray[3];
						var peerDepth = connectedPartitionPeerIDArray[4];
						
						var connectedPortIDArray = connectedPortID.split('-');
						var peerPort = connectedPortIDArray[5];
						var selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
					} else {
						break;
					}
				}
			}
			
		}, function(){
			var canvasHeight = $('#canvasBuildSpace').height();
			var canvasWidth = $('#canvasBuildSpace').height();
			context.clearRect(0, 0, canvasWidth, canvasHeight);
		});
	});
}

function processPortSelection(){
	var objID = $(document).data('clickedObjID');
	var objFace = $(document).data('clickedObjFace');
	var partitionDepth = $(document).data('clickedObjPartitionDepth');
	var portID = $(document).data('clickedObjPortID');
	
	var data = {
		objID: objID,
		objFace: objFace,
		partitionDepth: partitionDepth,
		portID: portID
	}
	
	data = JSON.stringify(data);
	
	// Retrieve the selected port's path
	$.post('backend/retrieve_path_full.php', {data:data}, function(response){
		var responseJSON = JSON.parse(response);
		if($(responseJSON.error).size() > 0) {
			displayError(responseJSON.error);
		} else {
			$('#containerFullPath').html(responseJSON.success);
			makeCableArrowsClickable();
		}
	});
	
	// Retrieve the selected port object string for path finder
	$.post('backend/retrieve_object.php', {data:data}, function(response){
		var responseJSON = JSON.parse(response);
		if($(responseJSON.error).size() > 0) {
			displayError(responseJSON.error);
		} else {
			$('#pathFinderModalTitle').html(responseJSON.success);
		}
	});
	
	// Retrieve the selected port details
	$.post('backend/retrieve_port_details.php', {data:data}, function(response){
		var responseJSON = JSON.parse(response);
		if($(responseJSON.error).size() > 0) {
			displayError(responseJSON.error);
		} else {
			$('#checkboxPopulated').prop("checked", responseJSON.success.populatedChecked);
			$('#checkboxPopulated').prop("disabled", responseJSON.success.populatedDisabled);
		
			$('#selectPort').html(responseJSON.success.portOptions);
			
			if(responseJSON.success.portOptions != '') {
				$('#selectPort').prop("disabled", false);
				$('#selectPort').off('change');
				$('#selectPort').on('change', function(){
					var portID = parseInt($(this).children('option:selected').val(), 10);
					$(document).data('clickedObjPortID', portID);
					processPortSelection();
					$(document).data('portClickedFlag', true);
					handlePathFindButton();
				});
			} else {
				$('#selectPort').prop("disabled", true);
				$('#checkboxPopulated').prop("checked", false);
				$('#checkboxPopulated').prop("disabled", true);
			}
			
			$(document).data('peerPortID', responseJSON.success.peerPortID);
		}
	});
}

function setObjectSize(obj){
	$(obj).each(function(){
		$(this).height($(this).parent().height()-1);
	});
}

function retrieveCabinet(cabinetID, cabinetFace, cabinetView){
	
	//Collect object data
	var data = {
		id: cabinetID,
		face: cabinetFace,
		view: cabinetView,
		page: 'explore'
	};
	data = JSON.stringify(data);
	
	//Retrieve object details
	$.post("backend/create_build_space.php", {data:data}, function(responseJSON){
		var response = JSON.parse(responseJSON);
		if (response.active == 'inactive'){
			window.location.replace("/");
		} else if ($(response.error).size() > 0){
			displayError(response.error);
		} else {
			$('#buildSpaceContent').html(response.data.html);
			makeRackObjectsClickable();
		
			//Make the objects height fill the <td> container
			setObjectSize($('.rackObj:not(.insert)'));
			
			makePortsHoverable();
			//makePartitionsHoverable();
			
			if($('#objID').length) {
				selectObject($('#cabinetTable'));
			}
		}
	});
}

function getFloorplanObjectPeerTable(){
	var cabinetID = $(document).data('cabinetID');
	
	//Collect object data
	var data = {
		cabinetID: cabinetID,
		action: 'getFloorplanObjectPeerTable'
	};
	data = JSON.stringify(data);

	//Retrieve floorplan details
	$.post("backend/process_cabinet.php", {data:data}, function(response){
		var response = $.parseJSON(response);
		if (response.error != ''){
			displayError(response.error);
		} else {
	
			$('#floorplanObjectTable').remove();
			var table = '';
			table += '<table id="floorplanObjectTable" class="table table-hover">';
			table += '<thead>';
			table += '<tr>';
			table += '<th>Name</th>';
			table += '<th>PortName</th>';
			table += '</tr>';
			table += '</thead>';
			table += '<tbody id="floorplanObjectTableBody">';
			
			$.each(response.success.floorplanObjectPeerTable, function(index, item){
				table += '<tr data-id="'+item.objID+'" data-portID="'+item.portID+'" data-peerEntryID="'+item.peerEntryID+'" style="cursor: pointer;">';
				table += '<td>'+item.objName+'</td>';
				table += '<td>'+item.peerPortName+'</td>';
				table += '</tr>';
			});
			table += '</tbody>';
			table += '</table>';
			
			$('#floorplanObjectTableContainer').html($(table).on('click', 'tr', function(){
				$(this).addClass('selectedObjTableEntry');
				var floorplanObjID = $(this).attr('data-id');
				$('#floorplanObj'+floorplanObjID).click();
			}));
			
			$('#floorplanObjectTable').DataTable({
				'paging': false,
				'info': false,
				'scrollY': '200px',
				'scrollCollapse': true
			});
		}
		
		// App node selection
		if($('#objID').length) {
			selectObject($('#floorplanContainer'));
		}
	});
}

function selectObject(parentObject){
	var objID = $('#objID').val();
	$(parentObject).find('[data-template-object-id='+objID+']').children('.selectable:first').click();
	$('#objID').remove();
}

function portDesignation(elem, action, flag) {
	var optionText = $(elem).text();
	var portFlagPattern = /\[[\w\,]*\w\]/g;
	var portFlagArray = portFlagPattern.exec(optionText);
	
	if(action == 'add') {
		if(portFlagArray != null) {
			if(!$.inArray(flag, portFlagArray)) {
				var portFlagString = portFlagArray[0];
				var portFlagContents = portFlagString.substring(1, portFlagString.length - 1);
				var portFlagContentsArray = portFlagContents.split(',');
				portFlagContentsArray.push(flag);
				var newPortFlagContents = portFlagContentsArray.join(',');
				$('#selectPort').find(':selected').text(optionText.replace(portFlagString, '['+newPortFlagContents+']'));
			}
		} else {
			$('#selectPort').find(':selected').text(optionText+' ['+flag+']');
		}
	} else {
		if(portFlagArray != null) {
			var portFlagString = portFlagArray[0];
			var portFlagContents = portFlagString.substring(1, portFlagString.length - 1);
			var portFlagContentsArray = portFlagContents.split(',');
			var PIndex = portFlagContentsArray.indexOf('P');
			portFlagContentsArray.splice(PIndex, 1);
			var newPortFlagContents = portFlagContentsArray.join(',');
			if(newPortFlagContents.length) {
				var newPortFlagString = '['+newPortFlagContents+']';
			} else {
				var newPortFlagString = '';
			}
			$('#selectPort').find(':selected').text(optionText.replace(portFlagString, newPortFlagString));
		}
	}
	
	
}

function initializeCanvas() {
	// Register an event listener to call the resizeCanvas() function 
	// each time the window is resized.
	window.addEventListener('resize', resizeCanvas, false);
}

function resizeCanvas() {
	$('#canvasBuildSpace').attr('width', $('#buildSpaceContent').width());
	$('#canvasBuildSpace').attr('height', $('#buildSpaceContent').height());
	//redraw();
}

function redraw() {
	var context = $('#canvasBuildSpace')[0].getContext('2d');
	context.strokeStyle = 'blue';
	context.strokeRect(0, 0, $('#buildSpaceContent').width(), $('#buildSpaceContent').height());
}

$( document ).ready(function() {
	
	// Cabinet Canvas
	canvasInset = 10;
	htmlCanvas = document.getElementById('canvasBuildSpace');
	context = htmlCanvas.getContext('2d');
	context.lineWidth = 10;
	initializeCanvas();
	
	// Export to Viso button
	$('#buttonVisioExport').on('click', function(){
		window.open('/backend/export-visio.php');
	});
	
	// Handle path finder button
	$(document).data('portClickedFlag', false);
	$(document).data('clickedObjPortID', null);
	$(document).data('cabinetView', 'port');
	$(document).data('cabinetFace', 0);
	handlePathFindButton();
	
	$('#floorplanContainer').panzoom({
		$zoomIn: $('#btnZoomIn'),
		$zoomOut: $('#btnZoomOut'),
		$reset: $('#btnZoomReset')
	});
	
	$('#selectCabinetView').on('change', function(){
		var cabinetID = $(document).data('cabinetID');
		var cabinetFace = $(document).data('cabinetFace');
		var cabinetView = $(this).val();
		$(document).data('cabinetView', cabinetView);
		retrieveCabinet(cabinetID, cabinetFace, cabinetView);
	});
	
	$('#checkboxPopulated').on('click', function(){
		var objID = $(document).data('clickedObjID');
		var objFace = $(document).data('clickedObjFace');
		var partitionDepth = $(document).data('clickedObjPartitionDepth');
		var portID = $(document).data('clickedObjPortID');
		var portPopulated = $(this).is(':checked');
		
		var interfaceSelectionElem = $('#selectPort').find(':selected');
		if(portPopulated) {
			$('#port-4-'+objID+'-'+objFace+'-'+partitionDepth+'-'+portID).addClass('populated');
			portDesignation(interfaceSelectionElem, 'add', 'P');
		} else {
			$('#port-4-'+objID+'-'+objFace+'-'+partitionDepth+'-'+portID).removeClass('populated');
			portDesignation(interfaceSelectionElem, 'remove', 'P');
		}
		
		var data = {
			objID: objID,
			objFace: objFace,
			partitionDepth: partitionDepth,
			portID: portID,
			portPopulated: portPopulated
		}
		
		data = JSON.stringify(data);
	
		// Retrieve the selected port's path
		$.post('backend/process_port_populated.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if($(responseJSON.error).size() > 0) {
				displayError(responseJSON.error);
			}
		});
	});
	
	$('#buttonPathFinderRun').on('click', function(){
		$(this).children('span').html('<i class="fa fa-spin fa-cog"></i>').prop("disabled", true);
		var endpointA = {
			objID: $(document).data('clickedObjID'),
			objFace: $(document).data('clickedObjFace'),
			objDepth: $(document).data('clickedObjPartitionDepth'),
			objPortID: $(document).data('clickedObjPortID'),
		}
		var selectedNode = $('#pathFinderTree').jstree('get_selected', true);
		var value = selectedNode[0].data.globalID;
		var valueArray = value.split('-');
		var endpointB = {
			objID: valueArray[1],
			objFace: valueArray[2],
			objDepth: valueArray[3],
			objPortID: valueArray[4]
		}
		var data = {
			endpointA: endpointA,
			endpointB: endpointB
		}
		data = JSON.stringify(data);
		$.post('backend/process_path_finder.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.error != ''){
				displayError(responseJSON.error, $('#alertMsgModal'));
			} else {
				var html = '';
				$(responseJSON.success).each(function(index, value){
					html += '<div id="containerCablePath'+index+'" class="containerCablePath" style="display:none;">';
					html += '<table>';
					$(value).each(function(i, val){
						// ---Start---
						if('near' in val) {
							html += '<tr>';
								html += '<td>';
									html += val.near;
								html += '</td>';
								if('distance' in val) {
									html += '<td rowspan="2" style="vertical-align:middle;">';
										html += '<div class="cableArrow" title="'+val.nearPortType+'"><svg width="20" height="20" style="display:block;"><g><path stroke="#000000" fill="#ffffff" id="00000017" transform="rotate(-180 10,10)" d="m12.34666,15.4034l0.12924,-1.39058l-1.52092,-0.242c-3.85063,-0.61265 -7.62511,-3.21056 -9.7267,-6.69472c-0.37705,-0.62509 -0.62941,-1.22733 -0.56081,-1.33833c0.15736,-0.25462 3.99179,-2.28172 4.31605,-2.28172c0.13228,0 0.45004,0.37281 0.70613,0.82847c1.09221,1.9433 3.91879,3.97018 5.9089,4.2371l0.80686,0.10823l-0.13873,-1.2018c-0.14402,-1.24763 -0.10351,-1.50961 0.23337,-1.50961c0.21542,0 6.64622,4.79111 6.83006,5.08858c0.13947,0.22565 -0.74504,1.06278 -3.91187,3.70233c-1.37559,1.14654 -2.65852,2.08463 -2.85095,2.08463c-0.308,0 -0.33441,-0.16643 -0.22064,-1.39058l0,0l0,0l0,-0.00001z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null"></path></g></svg></div>';
										html += val.distance;
										html += '<div class="cableArrow" title="'+val.farPortType+'"><svg width="20" height="20" style="display:block;"><g><path stroke="#000000" fill="#ffffff" id="00000024" transform="rotate(-180 10,10)" stroke-dasharray="null" stroke-linejoin="null" stroke-linecap="null" d="m12.34666,4.88458l0.12924,1.38058l-1.52092,0.24026c-3.85063,0.60825 -7.62511,3.18748 -9.7267,6.64659c-0.37705,0.6206 -0.62941,1.21851 -0.56081,1.32871c0.15736,0.25279 3.99179,2.26532 4.31605,2.26532c0.13228,0 0.45004,-0.37013 0.70613,-0.82251c1.09221,-1.92933 3.91879,-3.94164 5.9089,-4.20664l0.80686,-0.10745l-0.13873,1.19316c-0.14402,1.23866 -0.10351,1.49876 0.23337,1.49876c0.21542,0 6.64622,-4.75667 6.83006,-5.052c0.13947,-0.22403 -0.74504,-1.05514 -3.91187,-3.67571c-1.37559,-1.1383 -2.65852,-2.06964 -2.85095,-2.06964c-0.308,0 -0.33441,0.16523 -0.22064,1.38058l0,0l0,0l0,0.00001l0.00001,-0.00001z"></path></g></svg></div>';
									html += '</td>';
								}
							html += '</tr>';
						}
						if('far' in val) {
							html += '<tr>';
								html += '<td>';
									html += val.far;
								html += '</td>';
							html += '</tr>';
						}
						// ---End---
						if(i<($(value).length-1)) {
							html += '<tr>';
								html += '<td style="text-align:center;">';
									html += '<svg width="20" height="40"><g><path stroke="#000000" fill="#ffffff" transform="rotate(-90 10,20)" d="m-6.92393,20.00586l9.84279,-8.53669l0,4.26834l14.26478,0l0,-4.26834l9.84279,8.53669l-9.84279,8.53665l0,-4.26832l-14.26478,0l0,4.26832l-9.84279,-8.53665z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null" stroke-width="null"></path></g></svg>';
								html += '</td>';
							html += '</tr>';
						}
					});
					html += '</table>';
					html += '</div>';
				});
				$('#containerCablePath').html(html);
				
				var table = '';
				$(responseJSON.success).each(function(index, value){
					var mediaType = '';
					var local = 0;
					var adjacent = 0;
					var path = 0;
					var total = 0;
					$(value).each(function(i, val){
						if(val.pathType == 'local') {
							local = local + 1;
						} else if(val.pathType == 'adjacent') {
							adjacent = adjacent + 1;
						} else if(val.pathType == 'path') {
							path = path + 1;
						}
						if('mediaType' in val) {
							mediaType = val.mediaType;
						}
						total = local + adjacent + path;
					});
					table += '<tr data-pathid="'+index+'">';
						table += '<td>'+mediaType+'</td>';
						table += '<td>'+local+'</td>';
						table += '<td>'+adjacent+'</td>';
						table += '<td>'+path+'</td>';
						table += '<td>'+total+'</td>';
					table += '</tr>';
				});
				// Initialize cable path table
				$('#cablePathTable').DataTable().off('click');
				$('#cablePathTable').DataTable().destroy();
				$('#cablePathTableBody').html(table);
				var pathTable = $('#cablePathTable').DataTable({
					'searching': false,
					'paging': false,
					'info': false
				}).on('click', 'tr', function(){
					if($(this).hasClass('tableRowHighlight')) {
						$(this).removeClass('tableRowHighlight');
						$('.containerCablePath').hide();
					} else {
						pathTable.$('tr.tableRowHighlight').removeClass('tableRowHighlight');
						$(this).addClass('tableRowHighlight');
						var pathIndex = $(this).attr('data-pathid');
						$('.containerCablePath').hide();
						$('#containerCablePath'+pathIndex).show();
					}
				});
				
				$('#buttonPathFinderRun').children('span').html('<i class="fa fa-cogs"></i>').prop("disabled", false);
			}
		});
	});

	$('#buttonPortConnector').on('click', function(){
		var modalTitle = $(this).attr('data-modalTitle');
		var objectID = $(document).data('clickedObjID');
		var objectFace = $(document).data('clickedObjFace');
		var objectDepth = $(document).data('clickedObjPartitionDepth');
		var objectPort = $(document).data('clickedObjPortID');
		
		$('#objTree').jstree(true).settings.core.data = {url: 'backend/retrieve_environment-tree.php?scope=portExplore&objID='+objectID+'&objFace='+objectFace+'&objDepth='+objectDepth+'&objPort='+objectPort};
		$('#objTree').jstree(true).refresh();
		$('#alertMsgObjTree').empty();
		$('#objectTreeModalLabel').html(modalTitle);
		$('#objectTreeModal').modal('show');
	});
	
	$('#buttonObjectTreeModalSave').on('click', function(){
		var selectedNode = $('#objTree').jstree('get_selected', true);
		var value = selectedNode[0].data.globalID;
		var objID = $(document).data('clickedObjID');
		var objFace = $(document).data('clickedObjFace');
		var objDepth = $(document).data('clickedObjPartitionDepth');
		var objPort = $(document).data('clickedObjPortID');
		
		var data = {
			property: 'connectionExplore',
			value: value,
			objID: objID,
			objFace: objFace,
			objDepth: objDepth,
			objPort: objPort
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_cable.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayErrorElement(responseJSON.error, $('#alertMsgObjTree'));
			} else {
				var optionText = $('#selectPort').find(':selected').text();
				$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).addClass('populated');
				if($('#'+responseJSON.success.peerPortID).length) {
					$('#port-'+responseJSON.success.peerPortID).addClass('populated');
				}
				if($('#'+responseJSON.success.oldPeerPortID).length) {
					$('#port-'+responseJSON.success.oldPeerPortID).removeClass('populated');
				}
				var interfaceSelectionElem = $('#selectPort').find(':selected');
				portDesignation(interfaceSelectionElem, 'add', 'C');
				$('#checkboxPopulated').prop("checked", true);
				$('#checkboxPopulated').prop("disabled", true);
				$('#containerFullPath').html(responseJSON.success.pathFull);
				makeCableArrowsClickable();
				
				$('#objTree').jstree('deselect_all');
				$('#objectTreeModal').modal('hide');
				
				$(document).data('peerPortID', value);
			}
		});
	});
	
	$('#buttonObjectTreeModalClear').on('click', function(){
		var objID = $(document).data('clickedObjID');
		var objFace = $(document).data('clickedObjFace');
		var objDepth = $(document).data('clickedObjPartitionDepth');
		var objPort = $(document).data('clickedObjPortID');
		
		var data = {
			property: 'connectionExplore',
			value: 'clear',
			objID: objID,
			objFace: objFace,
			objDepth: objDepth,
			objPort: objPort
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_cable.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).removeClass('populated');
				if($('#port-'+responseJSON.success.peerPortID).length) {
					$('#port-'+responseJSON.success.peerPortID).removeClass('populated');
				}
				if(responseJSON.success.oldPeerPortID) {
					$('#port-'+responseJSON.success.oldPeerPortID).removeClass('populated');
				}
				var interfaceSelectionElem = $('#selectPort').find(':selected');
				portDesignation(interfaceSelectionElem, 'remove', 'C');
				$('#checkboxPopulated').prop("checked", false);
				$('#checkboxPopulated').prop("disabled", false);
				$('#containerFullPath').html(responseJSON.success.pathFull);
				makeCableArrowsClickable();
				
				$('#objTree').jstree('deselect_all');
				$('#objectTreeModal').modal('hide');
			}
		});
	});
	
	$('.sideSelectorCabinet').on('change', function(){
		var cabinetFace = $(this).val();
		var cabinetID = $(document).data('cabinetID');
		var cabinetView = $(document).data('cabinetView');
		$(document).data('cabinetFace', cabinetFace);
		retrieveCabinet(cabinetID, cabinetFace, cabinetView);
		if (cabinetFace == 0) {
			$('#detailsContainer1').hide();
			$('#detailsContainer0').show();
		} else {
			$('#detailsContainer1').show();
			$('#detailsContainer0').hide();
		}
	});
	
	$('#modalPathFinder').on('show.bs.modal', function(e){
		var objectID = $(document).data('clickedObjID');
		var objectFace = $(document).data('clickedObjFace');
		var objectDepth = $(document).data('clickedObjPartitionDepth');
		var objectPort = $(document).data('clickedObjPortID');
	
		$('#pathFinderTree').jstree(true).settings.core.data = {url: 'backend/retrieve_environment-tree.php?scope=portExplorePathFinder&objID='+objectID+'&objFace='+objectFace+'&objDepth='+objectDepth+'&objPort='+objectPort};
		$('#pathFinderTree').jstree(true).refresh();
	});
	
	// Ajax Tree
	$('#pathFinderTree')
	.on('select_node.jstree', function(e, data){
		if(data.node.type == 'port') {
			$('#buttonPathFinderRun').prop("disabled", false);
		} else {
			$('#buttonPathFinderRun').prop("disabled", true);
		}
	})
	.on('refresh.jstree', function(){
		var selectedNodes = $('#objTree').jstree('get_selected');
		if(selectedNodes.length) {
			$('#buttonObjectTreeModalSave').prop("disabled", false);
		} else {
			$('#buttonObjectTreeModalSave').prop("disabled", true);
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
			'key' : 'pathFinderNavigation'
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
		var peerPortID = $(document).data('peerPortID');
		
		$('#objTree').jstree('deselect_all');
		$('#objTree').jstree('select_node', peerPortID);
		
		var selectedNodes = $('#objTree').jstree('get_selected');
		if(selectedNodes.length) {
			$('#buttonObjectTreeModalSave').prop("disabled", false);
		} else {
			$('#buttonObjectTreeModalSave').prop("disabled", true);
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
			'key' : 'portExploreNavigation'
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
	
	// Ajax Tree
	$('#ajaxTree')
	.on('select_node.jstree', function (e, data) {
		clearSelectionDetails();
		var portAndPathObject = $('#portAndPath').detach();
		$('#rowCabinet').hide();
		$('#rowFloorplan').hide();
		$('#floorplanDetails').hide();
		$('#floorplanContainer').children('i').remove();
		
		//Store objectID
		var cabinetID = data.node.id;
		$(document).data('cabinetID', cabinetID);
		if(data.node.type == 'cabinet'){
			$('#portAndPathContainerCabinet').html(portAndPathObject);
			$('#rowCabinet').show();
			
			var cabinetFace = $(document).data('cabinetFace');
			var cabinetView = $(document).data('cabinetView');
			retrieveCabinet(cabinetID, cabinetFace, cabinetView);
		} else if (data.node.type == 'floorplan') {
			$('#portAndPathContainerFloorplan').html(portAndPathObject);
			$('#rowFloorplan').show();
			$('#floorplanDetails').show();
			
			//Collect object data
			var data = {
				cabinetID: cabinetID,
				action: 'getFloorplan'
			};
			data = JSON.stringify(data);

			//Retrieve floorplan details
			$.post("backend/process_cabinet.php", {data:data}, function(response){
				var response = $.parseJSON(response);
				if (response.error != ''){
					displayError(response.error);
				} else {
					var walljackObject = '<i class="floorplanObject selectable fa fa-square-o fa-lg" data-type="walljack"></i>';
					var wapObject = '<i class="floorplanObject selectable fa fa-wifi fa-2x" data-type="wap"></i>';
					var deviceObject = '<i class="floorplanObject selectable fa fa-laptop fa-2x" data-type="device"></i>';
					
					var floorplanImgPath = '/images/floorplanImages/'+response.success.floorplanImg;
					$('#imgFloorplan').attr('src', floorplanImgPath);
					
					$.each(response.success.floorplanObjectData, function(index, item){
						if(item.type == 'walljack') {
							var object = $(walljackObject);
						} else if(item.type == 'wap') {
							var object = $(wapObject);
						} else if(item.type == 'device') {
							var object = $(deviceObject);
						}
						var positionTop = item.position_top+'px';
						var positionLeft = item.position_left+'px';
						
						$('#floorplanContainer')
						.append(object
							.css({
								'z-index': 1000,
								'position': 'absolute',
								'top': positionTop,
								'left': positionLeft})
							.hover(
								function(){
									$('#floorplanContainer').panzoom('option', {
										disablePan: true
									});
								},
								function(){
									$('#floorplanContainer').panzoom('option', {
										disablePan: false
									});
								})
							.attr('data-objectID', item.id)
							.attr('id', 'floorplanObj'+item.id)
						);
						makeFloorplanObjectsClickable();
					});
				}
			});
			getFloorplanObjectPeerTable();
		} else if (data.node.type == 'location' || data.node.type == 'pod') {
			$("#buildSpaceContent").html("Please select a cabinet from the Environment Tree.");
			$('#portAndPathContainerCabinet').html(portAndPathObject);
			$('#rowCabinet').show();
		} else {
			$("#buildSpaceContent").html("Error");
		}

	})
	.jstree({
		'core' : {
			'check_callback' : function(operation, node, node_parent, node_position, more){
				if(operation == 'move_node'){
					return node_parent.type === 'location';
				}
				return true;
			},
			'themes' : {
				'responsive': false
			},
			'data' : {
				'url' : function (node) {
					return 'backend/process_environment-tree.php';
				}
			}
		},
		'state' : {
			'key' : 'envNavigation',
			'filter': function(state){
				if($('#parentID').length) {
					var parentID = $('#parentID').val();
					state.core.selected = [parentID];
					$('#parentID').remove();
				}
				return state;
			}
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
			'floorplan' : {
				'icon' : 'fa fa-map-o'
			}
        },
		"plugins" : [ "search", "state", "types", "wholerow" ]
    });
	
});