function getDimensions(elem){
	
	var canvasLeft = $('#canvasBuildSpace').offset().left;
	var canvasTop = $('#canvasBuildSpace').offset().top;
	
	var elemLeft = $(elem).offset().left;
	var elemTop = $(elem).offset().top;
	
	var elemLeftOrig = elemLeft;
	var elemTopOrig = elemTop;
	
	var elemWidth = $(elem).width();
	var elemHeight = $(elem).height();
	var elemCenterX = elemLeft - canvasLeft + (elemWidth / 2);
	var elemCenterY = elemTop - canvasTop  + (elemHeight / 2);
	var elemLeft = elemLeft - canvasLeft;
	var elemRight = elemLeft + elemWidth;
	var elemRightOrig = elemLeftOrig + elemWidth;
	var elemTop = elemTop - canvasTop;
	var elemBottom = elemTop + elemHeight;
	
	var dimensions = {
		leftOrig: elemLeftOrig,
		topOrig: elemTopOrig,
		rightOrig: elemRightOrig,
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

function drawConnection(elementArray){
	context.strokeStyle = 'blue';
	context.lineWidth = 3;
	context.beginPath();
	
	$.each(elementArray, function(index, element){
		var elemA = element[0];
		var elemB = element[1];
		
		var connectionStyle = $('#connectionStyle').val();
		
		var elemACabinet = $(elemA).closest('.cabinetContainer');
		var elemACabinetID = $(elemACabinet).data('cabinetId');
		var elemACabinetDimensions = getDimensions(elemACabinet);
		var elemADimensions = getDimensions(elemA);
		var elemAPartition = $(elemA).closest('.partition');
		var elemAPartitionDimensions = getDimensions(elemAPartition);
		
		context.moveTo(elemADimensions.centerX, elemADimensions.centerY);
		
		if(typeof elemB == 'object') {
			var elemBCabinet = $(elemB).closest('.cabinetContainer');
			var elemBCabinetID = $(elemBCabinet).data('cabinetId');
			var elemBCabinetDimensions = getDimensions(elemBCabinet);
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
			
			if(connectionStyle == 0) {
				context.lineTo(elemADimensions.centerX, elemAPartHBoundary);
				context.lineTo(elemACabinetDimensions.left - canvasInset, elemAPartHBoundary);
				
				if(elemACabinetID != elemBCabinetID) {
					
					// Ports are in different cabinets
					if(elemACabinetDimensions.top >= elemBCabinetDimensions.top) {
						
						// Connection should be routed up
						var elemACabinetHBoundary = elemACabinetDimensions.top - canvasInset;
					} else {
						
						// Connection should be routed down
						var elemACabinetHBoundary = elemACabinetDimensions.bottom + canvasInset;
					}
					
					context.lineTo(elemACabinetDimensions.left - canvasInset, elemACabinetHBoundary);
					context.lineTo(elemBCabinetDimensions.left - canvasInset, elemACabinetHBoundary);

				}
				context.lineTo(elemBCabinetDimensions.left - canvasInset, elemBPartHBoundary);
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
		} else {
			context.lineTo(elemADimensions.centerX, elemAPartitionDimensions.top);
			context.lineTo(elemACabinetDimensions.left - canvasInset, elemAPartitionDimensions.top);
			context.lineTo(elemACabinetDimensions.left - canvasInset, elemACabinetDimensions.top - canvasInset);
			
			var left = elemACabinetDimensions.leftOrig - canvasInset;
			var top = elemACabinetDimensions.topOrig - canvasInset;
			addCabButton(left, top, elemB);
		}
		
	});
	context.stroke();
}

function drawTrunk(elementArray){
	context.strokeStyle = 'black';
	context.lineWidth = 3;
	context.beginPath();
	
	$.each(elementArray, function(index, element){
		var elemA = element[0];
		var elemB = element[1];
	
		var canvasDimensions = getDimensions($('#canvasBuildSpace'));
		var elemACabinet = $(elemA).closest('.cabinetContainer');
		var elemACabinetID = $(elemACabinet).data('cabinetId');
		var elemACabinetDimensions = getDimensions($(elemA).closest('.cabinetContainer'));
		var elemADimensions = getDimensions(elemA);
		
		context.moveTo(elemADimensions.right, elemADimensions.centerY);
		
		if(typeof elemB == 'object') {
			
			var elemBCabinet = $(elemB).closest('.cabinetContainer');
			var elemBCabinetID = $(elemBCabinet).data('cabinetId');
			var elemBCabinetDimensions = getDimensions($(elemB).closest('.cabinetContainer'));
			var elemBDimensions = getDimensions(elemB);
			
			context.lineTo(elemACabinetDimensions.right + canvasInset, elemADimensions.centerY);
			
			if(elemACabinetID != elemBCabinetID) {
				if(elemACabinetDimensions.top <= elemBCabinetDimensions.top) {
					elemBCabinetHBoundary = elemBCabinetDimensions.top - canvasInset;
				} else {
					elemBCabinetHBoundary = elemBCabinetDimensions.bottom + canvasInset;
				}
				context.lineTo(elemACabinetDimensions.right + canvasInset, elemBCabinetHBoundary);
				context.lineTo(elemBCabinetDimensions.right + canvasInset, elemBCabinetHBoundary);
			}
			context.lineTo(elemBCabinetDimensions.right + canvasInset, elemBDimensions.centerY);
			context.lineTo(elemBDimensions.right, elemBDimensions.centerY);

		} else {
			context.lineTo(elemACabinetDimensions.right + canvasInset, elemADimensions.centerY);
			context.lineTo(elemACabinetDimensions.right + canvasInset, elemACabinetDimensions.top - canvasInset);
			context.strokeRect(elemADimensions.left, elemADimensions.top, elemADimensions.width, elemADimensions.height);
			
			var left = elemACabinetDimensions.rightOrig + canvasInset;
			var top = elemACabinetDimensions.topOrig - canvasInset;
			addCabButton(left, top, elemB);
		}
		
	});
	context.stroke();
}

function addCabButton(left, top, globalID){
	var addCab = $('<a class="addCabButton" data-global-id="'+globalID+'" href="#"style="z-index:1001; position:absolute;"><i class="fa fa-plus" style="color:#039cfd; background-color:white;"></i></a>');
	$('#canvasBuildSpace').after(addCab);
	var left = left - ($(addCab).width()/2);
	var top = top - ($(addCab).height()/2);
	$(addCab).css({'left':left+'px', 'top':top+'px'});
	makeAddCabButtonClickable(addCab);
}

function highlightElement(elemArray, color){
	$.each(elemArray, function(index, elem){
		context.strokeStyle = color;
		context.beginPath();
		
		var elemDimensions = getDimensions(elem);
		
		context.strokeRect(elemDimensions.left, elemDimensions.top, elemDimensions.width, elemDimensions.height);
	});
}

function clearPaths(){
	var canvasHeight = $('#canvasBuildSpace').height();
	var canvasWidth = $('#canvasBuildSpace').width();
	context.clearRect(0, 0, canvasWidth, canvasHeight);
	$('a.addCabButton').remove();
}

function crawlPath(selectedPort){
	var portArray = [];
	var connectionArray = [];
	var trunkArray = [];
	var partitionArray = [];
	var selectedPeerID = $(selectedPort).data('peerGlobalId');
	var selectedPortOrig = selectedPort;
	
	for(x=0; x<2; x++) {
		
		if(x == 1) {
			var selectedPartition = $(selectedPortOrig).closest('.partition');
			var selectedPartitionPeerID = $(selectedPartition).data('peerGlobalId');
			
			if($('#'+selectedPartitionPeerID).length) {
				var selectedPartitionPeer = $('#'+selectedPartitionPeerID);
				trunkArray.push([selectedPartition, selectedPartitionPeer]);
				partitionArray.push(selectedPartition, selectedPartitionPeer);
				
				var selectedPartitionPeerIDArray = selectedPartitionPeerID.split('-');
				var peerID = selectedPartitionPeerIDArray[2];
				var peerFace = selectedPartitionPeerIDArray[3];
				var peerDepth = selectedPartitionPeerIDArray[4];
				var peerPort = $(selectedPortOrig).data('portIndex');
				
				var selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
			} else {
				if(selectedPartitionPeerID != 'none') {
					trunkArray.push([selectedPartition, selectedPartitionPeerID]);
				}
				var selectedPort = false;
			}
		}
		
		while($(selectedPort).length) {
			var selectedPortID = $(selectedPort).attr('id');
			var selectedPartition = $(selectedPort).closest('.partition');
			var connectedPortID = $(selectedPort).data('connectedGlobalId');
			var connectedPort = $('#'+connectedPortID);
			
			portArray.push(selectedPort);
			
			if($(connectedPort).length) {
				
				portArray.push(connectedPort)
				connectionArray.push([selectedPort, connectedPort]);
				
			} else {
				if(connectedPortID != 'none') {
					connectionArray.push([selectedPort, connectedPortID]);
				}
				break;
			}
			
			var connectedPartition = $(connectedPort).closest('.partition');
			var connectedPartitionPeerID = $(connectedPartition).data('peerGlobalId');
			
			if($('#'+connectedPartitionPeerID).length) {
				
				var connectedPartitionPeer = $(connectedPartitionPeerID);
				trunkArray.push([connectedPartition, connectedPartitionPeer]);
				partitionArray.push(connectedPartition, connectedPartitionPeer);
				
				var connectedPartitionPeerIDArray = connectedPartitionPeerID.split('-');
				var peerID = connectedPartitionPeerIDArray[2];
				var peerFace = connectedPartitionPeerIDArray[3];
				var peerDepth = connectedPartitionPeerIDArray[4];
				
				var connectedPortIDArray = connectedPortID.split('-');
				var peerPort = connectedPortIDArray[5];
				var selectedPort = $('#port-4-'+peerID+'-'+peerFace+'-'+peerDepth+'-'+peerPort);
			} else {
				if(connectedPartitionPeerID != 'none') {
					trunkArray.push([connectedPartition, connectedPartitionPeerID]);
				}
				break;
			}
		}
	}
	
	return {
		'partitionArray': partitionArray,
		'trunkArray': trunkArray,
		'portArray': portArray,
		'connectionArray': connectionArray
	};
}

function makePortsHoverable(){
	
	resizeCanvas();
	redraw();
	
	$('#buildSpaceContent').find('.port').each(function(){
		$(this).unbind('mouseenter mouseleave click.drawConnections');
	});
	$('#buildSpaceContent').find('.port').each(function(){
		$(this).hover(function(){
			
			var pathElementArray = crawlPath(this);
			
			highlightElement(pathElementArray['partitionArray'], 'black');
			drawTrunk(pathElementArray['trunkArray']);
			highlightElement(pathElementArray['portArray'], 'blue');
			drawConnection(pathElementArray['connectionArray']);
			
		}, function(){
			redraw();
		});
		
		$(this).on('click.drawConnections', function(){
			
			if(typeof $(this).data('pathID') == 'undefined') {
				
				var pathElementArray = crawlPath(this);
				
				// Get pathID
				pathID++;
				
				// Associate ports to pathIDs
				$.each(pathElementArray['portArray'], function(){
					$(this).data('pathID', pathID);
				});
				
				// Store connection path
				var workingPathData = {
					'portArray': pathElementArray['portArray'],
					'connectionArray': pathElementArray['connectionArray'],
					'trunkArray': pathElementArray['trunkArray'],
					'partitionArray': pathElementArray['partitionArray']
				};
				pathData[pathID] = workingPathData;
			} else {
				
				// Clear pathIDs from ports
				var thisPathID = $(this).data('pathID');
				$.each(pathData[thisPathID]['portArray'], function(){
					$(this).removeData('pathID');
				});
				
				// Clear connection path
				delete pathData[thisPathID];
			}
		});
	});

}

function refreshPathData(){
	if(typeof pathData != 'undefined') {
		pathDataOrig = pathData;
		pathData = {};
		$.each(pathDataOrig, function(pathID, path){
			$.each(path['portArray'], function(portIndex, port){
				var portID = $(port).attr('id');
				if($('#'+portID).length) {
					var pathElementArray = crawlPath($('#'+portID));
					
					// Associate ports to pathIDs
					$.each(pathElementArray['portArray'], function(){
						$(this).data('pathID', pathID);
					});
					
					// Store connection path
					var workingPathData = {
						'portArray': pathElementArray['portArray'],
						'connectionArray': pathElementArray['connectionArray'],
						'trunkArray': pathElementArray['trunkArray'],
						'partitionArray': pathElementArray['partitionArray']
					};
					pathData[pathID] = workingPathData;
					
					return false;
				}
			});
		});
	}
}

function makeCabArrowsClickable(){
	$('.cabMoveArrow').unbind('click');
	
	$('.cabMoveArrow').click(function(){
		var direction = $(this).data('cabMoveDirection');
		var cabinet = $(this).closest('.diagramCabinetContainer');
		if(direction == 'left') {
			$(cabinet).insertBefore($(cabinet).prev()).animate();
		} else {
			$(cabinet).insertAfter($(cabinet).next());
		}
		redraw();
	});
}

function makeCabCloseClickable(){
	$('.cabClose').unbind('click');
	
	$('.cabClose').click(function(){
		var cabinet = $(this).closest('.diagramCabinetContainer');
		var locationBoxes = $(cabinet).parents('.diagramLocationBox');
		
		// Delete cabinet
		$(cabinet).remove();
		
		// Clean up empty location boxes
		$(locationBoxes).each(function(){
			if(!$(this).find('.diagramCabinetContainer').length) {
				$(this).remove();
			}
		});
		
		// Refresh all paths
		refreshPathData();
		
		redraw();
	});
}

function resizeCanvas() {
	$('#canvasBuildSpace').attr('width', $('#canvasBuildSpace').parent().width());
	$('#canvasBuildSpace').attr('height', $('#canvasBuildSpace').parent().height());
	//redraw();
}

function redraw() {
	clearPaths();
	//if(typeof $(document).data('pathData') != 'undefined') {
	if(typeof pathData != 'undefined') {
		//$.each($(document).data('pathData'), function(pathID, path){
		$.each(pathData, function(pathID, path){
			drawTrunk(path['trunkArray']);
			highlightElement(path['partitionArray'], 'black');
			drawConnection(path['connectionArray']);
			highlightElement(path['portArray'], 'blue');
		});
	}
}

function initializeCanvas() {
	// Register an event listener to call the resizeCanvas() function 
	// each time the window is resized.
	window.addEventListener('resize', resizeCanvas, false);
	htmlCanvas = document.getElementById('canvasBuildSpace');
	context = htmlCanvas.getContext('2d');
	canvasInset = 10;
	context.lineWidth = 10;
	pathData = {};
	pathID = 0;
	
}
