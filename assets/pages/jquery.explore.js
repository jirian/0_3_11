/**
 * Theme: Uplon Admin Template
 * Author: Coderthemes
 * Tree view
 */

function makeAddCabButtonClickable(addCabButton){
	$(addCabButton).click(function(event){
		event.preventDefault();
		var globalID = $(this).data('globalId');
		var globalIDArray = globalID.split('-');
		var cabinetID = globalIDArray[2];
	});
}
 
function handlePathFindButton(){
	var buttonState = true;
	if($(document).data('clickedObjPortID') !== null) {
		if($(document).data('selectedFloorplanObjectType') == 'wap') {
			$('#buttonPathFinder').addClass('disabled');
			$('#buttonPortConnector').addClass('disabled');
			$('#buttonObjectTreeModalClear').addClass('disabled');
		} else {
			$('#buttonPathFinder').removeClass('disabled');
			$('#buttonPortConnector').removeClass('disabled');
			$('#buttonObjectTreeModalClear').removeClass('disabled');
		}
	}
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

function makeCableConnectorsClickable(){
	$('.cableConnector').off('click');
	$('.cableConnector').on('click', function(){
		var code39 = $(this).data('code39');
		if(code39 != 0) {
			window.location.href = '/scan.php?connectorCode='+code39;
		}
	});
}

function retrievePortPath(objID, objFace, partitionDepth, portID){
	
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
			/* $('#containerFullPath').find('img').each(function(){
				$(this).css('height', $(this).parent().height());
			}); */
			makeCableConnectorsClickable();
		}
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
	
	retrievePortPath(objID, objFace, partitionDepth, portID);
	
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
		cabinetArray: [{
			id: cabinetID,
			face: cabinetFace,
			type: 'cabinet'
		}],
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
			$('#buildSpaceContent').html(response.data[0].html);
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
			if($.inArray(flag, portFlagArray) === -1) {
				
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
			var PIndex = portFlagContentsArray.indexOf(flag);
			
			if(PIndex >= 0) {
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
	
	
}

function fitFloorplan(){
	
	var imgWidth = $('#floorplanContainer').width();
	var imgHeight = $('#floorplanContainer').height();
	var containerWidth = $('#floorplanWindow').width();
	
	var scale = containerWidth / imgWidth;
	scale = (scale > 4) ? 4 : scale;
	var imgHeightScaled = imgHeight * scale;
	var imgWidthScaled = imgWidth * scale;
	
	if(scale > 1) {
		var imgHeightDiff = imgHeightScaled - imgHeight;
		var imgWidthDiff = imgWidthScaled - imgWidth;
		var scaleDirection = 1;
	} else {
		var imgHeightDiff = imgHeight - imgHeightScaled;
		var imgWidthDiff = imgWidth - imgWidthScaled;
		var scaleDirection = -1;
	}
	
	$('#floorplanWindow').css({height:imgHeightScaled+'px'});
	
	// Scale floorplan
	panzoom.zoom(scale);
	
	// Pan floorplan accounting for scale and "50% 50%" transform origin
	panzoom.pan(((imgWidthDiff/2)/scale)*scaleDirection, ((imgHeightDiff/2)/scale)*scaleDirection);

}

function objectInBounds(offset){
	var floorplanWindow = $('#floorplanWindow');
	var floorplanContainer = $('#floorplanContainer');

	// PanZoom
	var panzoomScale = panzoom.getScale();
	var panzoomLeft = panzoom.getPan().x;
	var panzoomTop = panzoom.getPan().y;
	
	// Window
	var floorplanWindowTop = $(floorplanWindow).offset().top;
	var floorplanWindowLeft = $(floorplanWindow).offset().left;
	var floorplanWindowHeight = $(floorplanWindow).height();
	var floorplanWindowWidth = $(floorplanWindow).width();
	
	// Image
	var imgWidth = $(floorplanContainer).width();
	var imgHeight = $(floorplanContainer).height();
	var imgHeightScaled = imgHeight * panzoomScale;
	var imgWidthScaled = imgWidth * panzoomScale;
	if(panzoomScale > 1) {
		var imgHeightDiff = imgHeightScaled - imgHeight;
		var imgWidthDiff = imgWidthScaled - imgWidth;
		var scaleDirection = 1;
		
	} else {
		var imgHeightDiff = imgHeight - imgHeightScaled;
		var imgWidthDiff = imgWidth - imgWidthScaled;
		var scaleDirection = -1;
	}
	var imgWidthMargin = imgWidthDiff/2;
	var imgWidthMarginScaled = imgWidthMargin/panzoomScale;
	var imgHeightMargin = imgHeightDiff/2;
	var imgHeightMarginScaled = imgHeightMargin/panzoomScale;
	
	var floorplanImgTop = panzoomTop - (imgHeightMarginScaled*scaleDirection);
	var floorplanImgLeft = panzoomLeft - (imgWidthMarginScaled*scaleDirection);
	var floorplanImgRight = floorplanImgLeft + imgWidth;
	var floorplanImgBottom = floorplanImgTop + imgHeight;
	
	// Object - Top
	var objectTop = offset.top;
	var objectTopWindowRelative = objectTop - floorplanWindowTop;
	var objectTopWindowRelativeScaled = objectTopWindowRelative / panzoomScale;
	
	// Object - Left
	var objectLeft = offset.left;
	var objectLeftWindowRelative = objectLeft - floorplanWindowLeft;
	var objectLeftWindowRelativeScaled = objectLeftWindowRelative / panzoomScale;
	
	// Left Boundary
	if(floorplanImgLeft < 0) {
		var floorplanBoundaryLeft = 0;
	} else {
		var floorplanBoundaryLeft = floorplanImgLeft;
	}
	// Top Boundary
	if(floorplanImgTop < 0) {
		var floorplanBoundaryTop = 0;
	} else {
		var floorplanBoundaryTop = floorplanImgTop;
	}
	// Right Boundary
	if(floorplanImgRight <= floorplanWindowWidth/panzoomScale) {
		var floorplanBoundaryRight = floorplanImgRight;
	} else {
		var floorplanBoundaryRight = floorplanWindowWidth/panzoomScale;
	}
	// Bottom Boundary
	if(floorplanImgBottom <= floorplanWindowHeight/panzoomScale) {
		var floorplanBoundaryBottom = floorplanImgBottom;
	} else {
		var floorplanBoundaryBottom = floorplanWindowHeight/panzoomScale;
	}

	if(objectTopWindowRelativeScaled > floorplanBoundaryTop && objectTopWindowRelativeScaled < floorplanBoundaryBottom && objectLeftWindowRelativeScaled > floorplanBoundaryLeft && objectLeftWindowRelativeScaled < floorplanBoundaryRight) {
		var accept = true;
	} else {
		var accept = false;
	}
	
	return accept;
}

$( document ).ready(function() {
	
	$('#printFullPath').on('click', function(event){
		event.preventDefault();
		$('#containerFullPath').printThis({
			importStyle: true,
			removeInline: true,
			removeInlineSelector: "img"
		});
	});
	
	$('#printPathFinder	').on('click', function(event){
		event.preventDefault();
		$('#containerCablePath').printThis({
			importStyle: true,
			removeInline: true,
			removeInlineSelector: "img"
		});
	});
	
	// requires jquery.drawConnections.js
	initializeCanvas();
	
	// Export to Viso button
	$('#buttonVisioExport').on('click', function(){
		window.open('/backend/export-visio.php');
	});
	
	    
    $("#rangeResults").ionRangeSlider({
        min: 1,
        max: $('#pathFinderMaxResults').val(),
		from: $('#pathFinderMaxResultsDefault').val()
    });
	
	$("#rangeDepth").ionRangeSlider({
        min: 1,
        max: $('#pathFinderMaxDepth').val(),
		from: $('#pathFinderMaxDepthDefault').val()
    });
	
	// Handle path finder button
	$(document).data('portClickedFlag', false);
	$(document).data('clickedObjPortID', null);
	$(document).data('cabinetView', 'port');
	$(document).data('cabinetFace', 0);
	handlePathFindButton();
	
	elem = document.getElementById('floorplanContainer');
	panzoom = Panzoom(elem);
	
	$('#btnZoomIn').on('click', panzoom.zoomIn);
	$('#btnZoomOut').on('click', panzoom.zoomOut);
	$('#btnZoomReset').on('click', panzoom.reset);
	
	$('#floorplanContainer').parent().bind('mousewheel DOMMouseScroll', function(event){
		
		var mouseCoords = {top:event.pageY, left:event.pageX};
		var mouseInBounds = objectInBounds(mouseCoords);
		
		if(mouseInBounds) {
			event.preventDefault();
			
			var pzMatrix = panzoom.getScale();
			var pzScale = parseFloat(pzMatrix, 10);
			
			if (event.originalEvent.wheelDelta > 0 || event.originalEvent.detail < 0) {
				var newScale = pzScale + (pzScale * 0.3);
			}
			else {
				var newScale = pzScale - (pzScale * 0.3);
			}
			
			panzoom.zoomToPoint(newScale, event);
		}
	});
	
	elem.addEventListener('panzoomreset', function(){
		fitFloorplan();
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
		var objDepth = $(document).data('clickedObjPartitionDepth');
		var objPort = $(document).data('clickedObjPortID');
		var portPopulated = $(this).is(':checked');
		
		var interfaceSelectionElem = $('#selectPort').find(':selected');
		if(portPopulated) {
			$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).addClass('populated');
			portDesignation(interfaceSelectionElem, 'add', 'P');
		} else {
			$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).removeClass('populated');
			portDesignation(interfaceSelectionElem, 'remove', 'P');
		}
		
		var data = {
			objID: objID,
			objFace: objFace,
			partitionDepth: objDepth,
			portID: objPort,
			portPopulated: portPopulated
		}
		
		data = JSON.stringify(data);
	
		// Retrieve the selected port's path
		$.post('backend/process_port_populated.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if($(responseJSON.error).size() > 0) {
				displayError(responseJSON.error);
			} else {
				retrievePortPath(objID, objFace, objDepth, objPort);
			}
		});
	});
	
	$('#buttonPathFinderRun').on('click', function(){
		$(this).children('span').html('<i class="fa fa-spin fa-cog"></i>').prop("disabled", true);
		
		var results = $('#rangeResults').data('ionRangeSlider').result.from;
		var depth = $('#rangeDepth').data('ionRangeSlider').result.from;
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
			endpointB: endpointB,
			results: results,
			depth: depth
		}
		data = JSON.stringify(data);
		$.post('backend/process_path_finder.php', {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.error != ''){
				displayError(responseJSON.error, $('#alertMsgModal'));
			} else {
				var pathID = 0;
				$('#containerCablePath').html('');
				$.each(responseJSON.success, function(pathType, pathTypeArray){
					$.each(pathTypeArray, function(index, path){
						var pathContainer = $('<div id="containerCablePath'+pathID+'" class="containerCablePath" style="display:none;"></div>');
						$(pathContainer).html(path.pathHTML);
						$('#containerCablePath').append($(pathContainer));
						pathID++;
					});
				});
				
				var table = '';
				var pathID = 0;
				$.each(responseJSON.success, function(pathType, pathTypeArray){
					$.each(pathTypeArray, function(index, path){
						var pathTypeCountArray = path.pathTypeCountArray;
						var total = 0;
						total += pathTypeCountArray.local;
						total += pathTypeCountArray.adjacent;
						total += pathTypeCountArray.path;
						
						table += '<tr data-pathid="'+pathID+'">';
						table += '<td>'+pathType+'</td>';
						table += '<td>'+pathTypeCountArray.local+'</td>';
						table += '<td>'+pathTypeCountArray.adjacent+'</td>';
						table += '<td>'+pathTypeCountArray.path+'</td>';
						table += '<td>'+total+'</td>';
						table += '</tr>';
						pathID++;
					});
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

	$('#buttonPortConnector').on('click', function(event){
		
		event.preventDefault();
		if($(this).hasClass('disabled')) {
			return false;
		}
		
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
				$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).addClass('populated').data('connectedGlobalId', 'port-'+responseJSON.success.peerPortID);
				if($('#port-'+responseJSON.success.peerPortID).length) {
					$('#port-'+responseJSON.success.peerPortID).addClass('populated').data('connectedGlobalId', '#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort);
				}
				if($('#port-'+responseJSON.success.oldPeerPortID).length) {
					$('#port-'+responseJSON.success.oldPeerPortID).removeClass('populated').data('connectedGlobalId', 'none');
				}
				var interfaceSelectionElem = $('#selectPort').find(':selected');
				portDesignation(interfaceSelectionElem, 'add', 'C');
				$('#checkboxPopulated').prop("checked", true);
				$('#checkboxPopulated').prop("disabled", true);
				//$('#containerFullPath').html(responseJSON.success.pathFull);
				//makeCableArrowsClickable();
				retrievePortPath(objID, objFace, objDepth, objPort);
				refreshPathData();
				redraw();
				
				$('#objTree').jstree('deselect_all');
				$('#objectTreeModal').modal('hide');
				
				$(document).data('peerPortID', value);
			}
		});
	});
	
	$('#buttonObjectTreeModalClear').on('click', function(){
		
		event.preventDefault();
		if($(this).hasClass('disabled')) {
			return false;
		}
		
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
				$('#port-4-'+objID+'-'+objFace+'-'+objDepth+'-'+objPort).removeClass('populated').data('connectedGlobalId', 'none');
				if($('#port-'+responseJSON.success.peerPortID).length) {
					$('#port-'+responseJSON.success.peerPortID).removeClass('populated').data('connectedGlobalId', 'none');
				}
				
				var interfaceSelectionElem = $('#selectPort').find(':selected');
				portDesignation(interfaceSelectionElem, 'remove', 'C');
				$('#checkboxPopulated').prop("checked", false);
				$('#checkboxPopulated').prop("disabled", false);
				//$('#containerFullPath').html(responseJSON.success.pathFull);
				retrievePortPath(objID, objFace, objDepth, objPort);
				refreshPathData();
				redraw();
				
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
		var button = e.relatedTarget;
		if($(button).hasClass('disabled')) {
			return false;
		}
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
					
					$('#imgDummy').load(function(){
						var imgHeight = $(this).height();
						var imgWidth = $(this).width();
						$('#floorplanContainer').css({"background-image":"url("+floorplanImgPath+")", "height":imgHeight, "width":imgWidth});
						fitFloorplan();
					}).attr('src', floorplanImgPath);
					
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