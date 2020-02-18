/**
 * Object Editor
 * This page creates custom cabinet objects (servers, switches, routers, etc.)
 */

function handlePartitionOrientaion(){
	var variables = getVariables();
	var isParent = $(variables['selectedObj']).hasClass('flex-container-parent');
	var hasChildren = $(variables['selectedObj']).children('.flex-container').length > 0;
	var disabledState = isParent && !hasChildren ? false : true;
	
	// Check the appropriate partition orientation radio
	if(!isParent || hasChildren){
		if($(variables['selectedObj']).data('direction') == 'column') {
			$('#partitionH').prop('checked', true);
		} else {
			$('#partitionV').prop('checked', true);
		}
	}
	
	$('#partitionH').prop('disabled', disabledState);
	$('#partitionV').prop('disabled', disabledState);
}

function handlePartitionAddRemove(){
	var variables = getVariables();
	var isParent = $(variables['selectedObj']).hasClass('flex-container-parent');
	var partitionType = $(variables['selectedObj']).data('partitionType');
	var flexDirection = $(variables['selectedObj']).data('direction');
	var unitAttr = flexDirection == 'column' ? 'vUnits' : 'hUnits';
	var partitionUnits = $(variables['selectedObj']).data(unitAttr);
	var childUnits = 0;
	
	// Sum of child units
	$(variables['selectedObj']).children('.flex-container').each(function(){
		childUnits += parseInt($(this).data(unitAttr), 10)
	});
	
	// Units available
	var availableUnits = partitionUnits - childUnits;
	if(availableUnits && partitionType == 'Generic'){
		// Enable
		$('#customPartitionAdd').removeClass('disabled').prop('disabled', false);
	} else {
		// Disable
		$('#customPartitionAdd').addClass('disabled').prop('disabled', true);
	}
	
	// Handle partition remove button
	if(isParent){
		$('#customPartitionRemove').addClass('disabled').prop('disabled', true);
	} else {
		$('#customPartitionRemove').removeClass('disabled').prop('disabled', false);
	}
}
 
function handleOrientationInput(){
	handlePartitionOrientaion();
	handlePartitionAddRemove();
}

function setPartitionSizeInput(){
	var variables = getVariables();
	
	// If selected object is not the parent container...
	if(!$(variables['selectedObj']).hasClass('flex-container-parent')){
		var flexDirection = $(variables['selectedObj']).data('direction');
		var partitionStep = flexDirection == 'column' ? 1 : 0.5;
		var selectedUnitAttr = flexDirection == 'column' ? 'hUnits' : 'vUnits';
		var parentUnitAttr = flexDirection == 'column' ? 'vUnits' : 'hUnits';
		var flexUnits = parseInt($(variables['selectedObj']).data(selectedUnitAttr), 10);
		var parentFlexUnits = parseInt($(variables['selectedParent']).data(selectedUnitAttr), 10);
		var siblingUnits = 0;
		$(variables['selectedObj']).siblings().each(function(){
			siblingUnits += parseInt($(this).data(selectedUnitAttr), 10);
		});
		var takenUnits = siblingUnits + flexUnits;
		var availableUnits = parentFlexUnits - takenUnits;
		
		// Calculate the space taken by dependent partitions
		var unitsTaken = 0;
		$(variables['selectedObj']).children().each(function(){
			workingUnitsTaken = 0;
			$(this).children().each(function(){
				workingUnitsTaken += parseInt($(this).data(selectedUnitAttr), 10);
			});
			unitsTaken = workingUnitsTaken > unitsTaken ? workingUnitsTaken : unitsTaken;
		});
		var partitionMin = partitionStep * unitsTaken == 0 ? partitionStep :  partitionStep * unitsTaken;
		var partitionSize = ((partitionStep * 10) * (flexUnits * 10)) / 100;
		var partitionMax = partitionStep * (flexUnits + availableUnits);
		
		$('#inputCustomPartitionSize').val(partitionSize);
		$('#inputCustomPartitionSize').attr('min', partitionMin);
		$('#inputCustomPartitionSize').attr('max', partitionMax);
		$('#inputCustomPartitionSize').attr('step', partitionStep);
	
	// If the selected object is the parent container,
	// default and disable section size input.
	}else{
		$('#inputCustomPartitionSize').val(.5);
		$('#inputCustomPartitionSize').prop('disabled', true);
	}
}

function setInputValues(){
	var variables = getVariables();
	
	var partitionType = $(variables['selectedObj']).data('partitionType');
	$('[name="partitionType"][value='+partitionType+']').prop('checked', true);
	
	if(partitionType == 'Generic') {
		
	} else if(partitionType == 'Connectable') {
		
		var portLayoutX = $(variables['selectedObj']).data('valueX');
		var portLayoutY = $(variables['selectedObj']).data('valueY');
		var portOrientation = $(variables['selectedObj']).data('portOrientation');
		var portType = $(variables['selectedObj']).data('portType');
		var mediaType = $(variables['selectedObj']).data('mediaType');
		
		$('#inputPortLayoutX').val(portLayoutX);
		$('#inputPortLayoutY').val(portLayoutY);
		$('input.objectPortOrientation[value="'+portOrientation+'"]').prop('checked', true);
		$('#inputPortType').children('[value="'+portType+'"]').prop('selected', true);
		$('#inputMediaType').children('[value="'+mediaType+'"]').prop('selected', true);
		
	} else if(partitionType == 'Enclosure') {
		
		var encLayoutX = $(variables['selectedObj']).data('valueX');
		var encLayoutY = $(variables['selectedObj']).data('valueY');
		var encTolerance = $(variables['selectedObj']).data('encTolerance');
		
		$('#inputEnclosureLayoutX').val(encLayoutX);
		$('#inputEnclosureLayoutY').val(encLayoutY);
		$('[name="enclosureTolerance"][value='+encTolerance+']').prop('checked', true);
		
	}
}

function resizePartition(inputValue){
	var variables = getVariables();
	var flexDirection = $(variables['selectedObj']).data('direction');
	var selectedUnitAttr = flexDirection == 'column' ? 'hUnits' : 'vUnits';
	var parentFlexUnits = parseInt($(variables['selectedParent']).parent().data(selectedUnitAttr), 10);
	var partitionFlexUnits = flexDirection == 'row' ? inputValue*2 : inputValue;
	var partitionFlexSize = partitionFlexUnits/parentFlexUnits;
	$(variables['selectedObj']).css('flex-grow', partitionFlexSize);
	$(variables['selectedObj']).data(selectedUnitAttr, partitionFlexUnits);
	var dependentFlexSize = 1/partitionFlexUnits;
	$(variables['selectedObj']).children().each(function(){
		$(this).data(selectedUnitAttr, partitionFlexUnits);
		$(this).children().each(function(){
			var dependentFlexUnits = $(this).data(selectedUnitAttr);
			$(this).css('flex-grow', dependentFlexSize*dependentFlexUnits);
		});
	});
}

function partitionRemoveButtonStatus(status){
	var object = $('#customPartitionRemove');
	switch(status){
		case 'disable':
			$(object).addClass('disabled').prop('disabled', true);
			break;
			
		case 'enable':
			$(object).removeClass('disabled').prop('disabled', false);
			break;
	}
}

function resetRUSize(){
	var variables = getVariables();
	
	// Calculate the space taken by dependent partitions
	var spaceTaken = 0;
	//if($(variables['obj']).children('.flex-container-parent').css('flex-direction') == 'column') {
	var containerParentDirection = $(variables['obj']).children('.flex-container-parent').data('direction');
	if(containerParentDirection == 'column') {
		$(variables['obj']).children('.flex-container-parent').children().each(function(){
			spaceTaken += parseInt($(this).data('vUnits'), 10);
		});
	} else {
		$(variables['obj']).children('.flex-container-parent').children().each(function(){
			workingSpaceTaken = 0;
			$(this).children().each(function(){
				workingSpaceTaken += parseInt($(this).data('vUnits'), 10);
			});
			spaceTaken = workingSpaceTaken > spaceTaken ? workingSpaceTaken : spaceTaken;
		});
	}
	
	// Update RUSize Input
	var min = Math.ceil(spaceTaken/2);
	var min = min > 0 ? min : 1;
	$('#inputRU').attr('min', min);
}

function setDefaultData(obj){
	var portNameFormat = [
		{
			type:"static",
			value:"Port",
			count: 0,
			order:0
		}, {
			type:"incremental",
			value:"1",
			count:0,
			order:1
		}
	];
	
	$(obj).data('valueX', 1);
	$(obj).data('valueY', 1);
	$(obj).data('encTolerance', 'Strict');
	$(obj).data('partitionType', 'Generic');
	$(obj).data('portOrientation', 1);
	$(obj).data('portType', 1);
	$(obj).data('mediaType', 1);
	$(obj).data('portNameFormat', portNameFormat);
}

function addPartition(){
	var variables = getVariables();
	var axis = $('input.partitionAxis:checked').val();
	
	var parentFlexDirection = axis == 'h' ? 'column' : 'row';
	var flexDirection = axis == 'h' ? 'row' : 'column';
	var unitAttr = axis == 'h' ? 'vUnits' : 'hUnits';
	var flexUnits = parseInt($(variables['selectedParent']).data(unitAttr), 10);
	var flex = 1/flexUnits;
	var html = '';
	
	// Set flex-direction according to the orientation input
	$(variables['selectedObj']).css('flex-direction', parentFlexDirection).data('direction', parentFlexDirection);
	var vUnits = axis == 'h' ? 1 : parseInt($(variables['selectedObj']).data('vUnits'), 10);
	var hUnits = axis == 'h' ? parseInt($(variables['selectedObj']).data('hUnits'), 10) : 1;
	html += '<div class="flex-container border-black transparency-20" style="flex:'+flex+'; flex-direction:'+flexDirection+';" data-direction="'+flexDirection+'" data-h-units="'+hUnits+'" data-v-units="'+vUnits+'"></div>';
	$(variables['selectedObj']).append(html);
	var newObj = $(variables['selectedObj']).children().last();
	$(newObj).on('click', function(event){
		event.stopPropagation();
		makeTemplatePartitionClickable(this);
	});
	setDefaultData(newObj);
	
	return;
}

function buildTable(inputX, inputY, className, border=false){
	var table = '';
	
	for (y = 0; y < inputY; y++){
		
		// Determine border side
		if(border) {
			if(y == inputY) {
				var rowBorderClass = 'borderBottom';
			} else {
				var rowBorderClass = 'borderTop';
			}
		} else {
			var rowBorderClass = '';
		}
		
		// Create row
		table += '<div class="'+rowBorderClass+' tableRow">';
		
		for (x = 0; x < inputX; x++){
			
			// Determine border side
			if(border) {
				if(x == inputX) {
					var colBorderClass = 'borderRight';
				} else {
					var colBorderClass = 'borderLeft';
				}
			} else {
				var colBorderClass = '';
			}
			
			// Create column
			table += '<div class="'+className+' '+colBorderClass+' tableCol"></div>';
		}
		table += '</div>';
	}
	
	return table;
}

function buildPortTable(){
	var variables = getVariables();
	var portType = $('#inputPortType').find('option:selected').data('value');
	var x = $(variables['selectedObj']).data('valueX');
	var y = $(variables['selectedObj']).data('valueY');
	var table = buildTable(x, y, '');
	$(variables['selectedObj']).html(table);
	$(variables['selectedObj']).find('.tableCol').each(function(){
		$(this).html('<div class="port '+portType+'"></div>');
	});
}

function makeRackObjectsClickable(){
	$('#templateContainer').find('.categoryTitle').on('click', function(){
		var categoryName = $(this).data('categoryName');
		if($('.category'+categoryName+'Container').is(':visible')) {
			$('.category'+categoryName+'Container').hide(400);
			$(this).children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
		} else {
			$('.categoryContainer').hide(400);
			$('.categoryTitle').children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
			$('.category'+categoryName+'Container').show(400);
			$(this).children('i').removeClass('fa-caret-right').addClass('fa-caret-down');
		}
	});
	
	$('#availableContainer').find('.selectable').on('click', function(event){
		event.stopPropagation();
		if($(this).hasClass('stockObj')) {
			var object = $(this);
			var partitionDepth = 0;
		} else {
			var object = $(this).closest('.stockObj');
			var partitionDepth =  parseInt($(this).data('depth'), 10);
		}
		$('#selectedPartitionDepth').val(partitionDepth);
		
		// Store template name
		var templateName = $(object).data('templateName');
		$(document).data('selectedTemplateName', templateName);
		
		// Store template category name
		var templateCategoryName = $(object).data('templateCategoryName');
		$(document).data('selectedTemplateCategoryName', templateCategoryName);
		
		//Store templateID
		var templateID = $(object).data('templateId');
		$('#selectedObjectID').val(templateID);
		$(document).data('selectedTemplateID', templateID);
		
		//Store objectFace
		var templateFace = $(object).data('objectFace');
		$('#selectedObjectFace').val(templateFace);
		
		initializeImageUpload(templateID, templateFace);
		
		//Store cabinetFace
		var cabinetFace = $('#currentCabinetFace').val();
		
		//Remove hightlight from all racked objects
		$('#availableContainer').find('.rackObjSelected').removeClass('rackObjSelected');
		
		//Hightlight the selected racked object
		$(this).addClass('rackObjSelected');
		
		//Collect object data
		var data = {
			objID: templateID,
			page: 'editor',
			objFace: templateFace,
			cabinetFace: cabinetFace,
			partitionDepth: partitionDepth
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/retrieve_object_details.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				var response = responseJSON.success;
				$('#detailObjName').html(response.objectName);
				$('#inline-templateName').editable('setValue', response.templateName).editable('enable');
				$('#detailTrunkedTo').html(response.trunkedTo);
				$('#detailObjType').html(response.objectType);
				$('#detailObjFunction').html(response.function);
				$('#detailRUSize').html(response.RUSize);
				$('#detailMountConfig').html(response.mountConfig);
				$('#detailPortType').html(response.portType);
				$('#detailMediaType').html(response.mediaType);
				$('#detailTemplateImage').html('<img id="elementTemplateImage" src="" height="" width="">');
				$('#detailTemplateImage').append('<div id="templateImageActionContainer"><a id="templateImageAction" href="#">' + response.templateImgAction + '</a></div>');
				$('#objClone').removeClass('disabled');
				$('#objFind').removeClass('disabled');
				$('#objDelete').removeClass('disabled');
				
				// Port Range
				if(response.portRange == 'N/A') {
					if(!$('#detailPortRange').hasClass('no-modal')) {
						$('#detailPortRange').addClass('no-modal');
					}
					$('#detailPortRange').html(response.portRange);
				} else {
					$('#detailPortRange').removeClass('no-modal');
					$('#detailPortRange').html('<a href="#">'+response.portRange+'</a>');
				}
				$(document).data('portNameFormatEdit', response.portNameFormat);
				$(document).data('portTotalEdit', response.portTotal);
				
				// Object Image
				if(response.templateImgExists) {
					$('#templateImageActionContainer').append('<span id="templateImageDeleteContainer"> - <a id="templateImageDelete" href="#">delete</a></span>');
					$('#elementTemplateImage').attr({
						src:response.templateImgPath,
						height:response.templateImgHeight + 'px',
						width:response.templateImgWidth + '%'
					});
				} else {
					$('#elementTemplateImage').attr({
						height:response.templateImgHeight + 'px',
						width:response.templateImgWidth + '%'
					}).hide();
				}
				
				// Object Category
				$('#inline-category').editable('destroy');
				$('#inline-category').editable({
					showbuttons: false,
					mode: 'inline',
					source: response.categoryArray,
					url: 'backend/process_object-custom.php',
					send: 'always',
					params: function(params){
						var data = {
							'action':'edit',
							'templateID':templateID,
							'attribute':$(this).attr('id'),
							'value':params.value
						};
						params.data = JSON.stringify(data);
						return params;
					},
					success: function(response) {
						var selectedObjID = $('#selectedObjectID').val();
						var responseJSON = JSON.parse(response);
						if (responseJSON.active == 'inactive'){
							window.location.replace("/");
						} else if ($(responseJSON.error).size() > 0){
							displayError(responseJSON.error);
						} else {
							//var selectedObjID = $('#selectedObjectID').val();
							var category = responseJSON.success;
							$('.obj'+selectedObjID).removeClass (function (index, css) {
								return (css.match (/(^|\s)category\S+/g) || []).join(' ');
							});
							$('.obj'+selectedObjID).addClass('category'+category);
							reloadTemplates();
						}
					}
				});
				$('#inline-category').editable('setValue', response.categoryID).editable('enable');
				
				// Object Port Orientation
				//$('#inline-portOrientation').editable('destroy');
				if(response.portOrientationID != false) {
					$('#detailPortOrientation').html('<a href="#" id="inline-portOrientation" data-type="select">-</a>');
					$('#inline-portOrientation').editable({
						showbuttons: false,
						mode: 'inline',
						source: response.portOrientationArray,
						url: 'backend/process_object-custom.php',
						send: 'always',
						params: function(params){
							var data = {
								'action':'edit',
								'templateID':templateID,
								'templateFace':templateFace,
								'templateDepth':partitionDepth,
								'attribute':$(this).attr('id'),
								'value':params.value
							};
							params.data = JSON.stringify(data);
							return params;
						},
						success: function(response) {
							var selectedObjID = $('#selectedObjectID').val();
							var responseJSON = JSON.parse(response);
							if (responseJSON.active == 'inactive'){
								window.location.replace("/");
							} else if ($(responseJSON.error).size() > 0){
								displayError(responseJSON.error);
							}
						}
					});
					$('#inline-portOrientation').editable('setValue', response.portOrientationID).editable('enable');
				} else {
					$('#detailPortOrientation').html(response.portOrientationName);
				}
				
				// Object EnclosureTolerance
				//$('#inline-portOrientation').editable('destroy');
				if(response.encTolerance != 'N/A') {
					$('#detailEnclosureTolerance').html('<a href="#" id="inline-enclosureTolerance" data-type="select">-</a>');
					$('#inline-enclosureTolerance').editable({
						showbuttons: false,
						mode: 'inline',
						source: response.encToleranceArray,
						url: 'backend/process_object-custom.php',
						send: 'always',
						params: function(params){
							var data = {
								'action':'edit',
								'templateID':templateID,
								'templateFace':templateFace,
								'templateDepth':partitionDepth,
								'attribute':$(this).attr('id'),
								'value':params.value
							};
							params.data = JSON.stringify(data);
							return params;
						},
						success: function(response) {
							var selectedObjID = $('#selectedObjectID').val();
							var responseJSON = JSON.parse(response);
							if (responseJSON.active == 'inactive'){
								window.location.replace("/");
							} else if ($(responseJSON.error).size() > 0){
								displayError(responseJSON.error);
							}
						}
					});
					$('#inline-enclosureTolerance').editable('setValue', response.encTolerance).editable('enable');
				} else {
					$('#detailEnclosureTolerance').html(response.encTolerance);
				}
				
				// Object Image
				$('#templateImageAction').on('click', function(event){
					event.preventDefault();
					$('#modalImageUpload').modal('show');
				});
				$('#templateImageDelete').on('click', function(event){
					event.preventDefault();
					
					var data = {
						templateID: templateID,
						templateFace: templateFace
						};
					data = JSON.stringify(data);
					
					$.post("backend/process_template-image-delete.php", {data:data}, function(response){
						var responseJSON = JSON.parse(response);
						if (responseJSON.active == 'inactive'){
							window.location.replace("/");
						} else if ($(responseJSON.error).size() > 0){
							displayError(responseJSON.error);
						} else {
							$('#elementTemplateImage').hide();
							$('#templateImageDeleteContainer').remove();
							$('#templateImageAction').html('upload');
						}
					});
				});
			}
		});
	});
}

function makeTemplatePartitionClickable(elem){
	var variables = getVariables();
	// Highlight this object
	$(variables['obj']).find('.rackObjSelected').removeClass('rackObjSelected');
	$(elem).addClass('rackObjSelected');
	
	// Enable the 'size' input
	$('#inputCustomPartitionSize').prop('disabled', false);
	
	setPartitionSizeInput();
	setInputValues();
	togglePartitionTypeDependencies();
	handleOrientationInput();
	updatePortNameDisplay();
}

function expandRackUnit(RUSize){
	
	// Expand RU for each cabinet side
	for (var x=0; x<2; x++) {
		
		var cabinetSize = $('#cabinetContainer'+x).children('table').find('tr').length;
		var RUChange = false;
		
		if(RUSize > cabinetSize) {
			
			// Cabinet needs to grow
			var RUDiff = RUSize - cabinetSize;
			var rackUnitHTML = '<tr class="cabinet"><td class="cabinetRailRU cabinetRailLeft cabinet">1</td><td class="RackUnit'+x+'"></td><td class="cabinetRailRU cabinetRailRight cabinet"></td></tr>';
			for (i = 0; i < RUDiff; i++) {
				$('#cabinetContainer'+x).children('table').append(rackUnitHTML);
			}
			cabinetSize = RUSize;
			RUChange = true;
		} else if(RUSize < cabinetSize) {
			
			// Cabinet needs to shrink
			var RUDiff = cabinetSize - RUSize;
			for (i = 0; i < RUDiff && cabinetSize > 5; i++) {
				$('#cabinetContainer'+x).children('table').find('tr').last().remove();
				cabinetSize--;
			}
			RUChange = true;
		}
		
		// Renumber RUs
		if(RUChange) {
			$.each($('#cabinetContainer'+x).children('table').find('tr'), function(){
				var index = $('#cabinetContainer'+x).children('table').find('tr').index(this);
				var RUNumber = cabinetSize - index;
				$(this).children().first().html(RUNumber);
			});
		}
		
		$('.RackUnit'+x).show();
		$('.RackUnit'+x).eq(0).attr('rowspan', RUSize);
		for (y=1; y<RUSize; y++) {
			$('.RackUnit'+x).eq(y).hide();
		}
	}
}

function setObjectSize(){
	var variables = getVariables();
	$(variables['obj']).height(0);
	var height = $(variables['obj']).parent().height();
	$(variables['obj']).height(height);
	
	if ($('input.objectType:checked').val() == 'Standard') {
		var parentFlexUnits = variables['RUSize']*2;
		var flexContainerParent = $(variables['obj']).children('.flex-container-parent');
		var flexDirection = $(flexContainerParent).css('flex-direction');
		
		// Recalculate dependent horizontal partitions.
		if(flexDirection == 'row') {
			$(flexContainerParent).children().each(function(){
				$(this).children().each(function(){
					var partitionFlexUnits = parseInt($(this).data('vUnits'), 10);
					var partitionFlexSize = partitionFlexUnits/parentFlexUnits;
					$(this).css('flex', partitionFlexSize + ' 1 0%');
				});
			});
		} else {
			$(flexContainerParent).children().each(function(){
				var partitionFlexUnits = parseInt($(this).data('vUnits'), 10);
				var partitionFlexSize = partitionFlexUnits/parentFlexUnits;
				$(this).css('flex', partitionFlexSize + ' 1 0%');
			});
		}
	}
}

function switchSides(sideValue){
	if (sideValue == 1) {
		$('#cabinetContainer0').hide();
		$('#cabinetContainer1').show();
	} else {
		$('#cabinetContainer1').hide();
		$('#cabinetContainer0').show();
	}
	//$('#inputCurrentSide').val(sideValue);
	$(document).data('templateSide', sideValue);
}

function switchSidesDetail(sideValue){
	if(sideValue==1){
		$('#availableContainer0').hide();
		$('#availableContainer1').show();
	} else {
		$('#availableContainer0').show();
		$('#availableContainer1').hide();
	}
	$(document).data('availableTemplateSide', sideValue);
}

function getVariables(){
	var variables = [];
	variables['currentSide'] = $(document).data('templateSide');
	variables['obj'] = $('#previewObj'+variables['currentSide']);
	variables['selectedObj'] = $(variables['obj']).find('.rackObjSelected');
	variables['selectedParent'] = $(variables['selectedObj']).parent();
	variables['RUSize'] = $('#inputRU').val();
	variables['halfRU'] = 1/(variables['RUSize']*2);
	return variables;
}

function reapplyCategory(){
	var variables = getVariables();
	var selectedCategory = $("#inputCategory").find('option:selected').data('value');
	
	$(variables['selectedObj']).removeClass (function (index, css) {
		return (css.match (/(^|\s)category\S+/g) || []).join(' ');
	});
	
	$(variables['selectedObj']).addClass(selectedCategory);
}

function buildObj(objID, hUnits, vUnits, direction){
	var obj = $('#previewObj'+objID);
	$(obj).html('<div class="flex-container-parent rackObjSelected" style="flex-direction:'+direction+'" data-direction="'+direction+'" data-h-units="'+hUnits+'" data-v-units="'+vUnits+'"></div>');
	var newObj = $(obj).children('.flex-container-parent');
	//$(newObj).addClass('rackObjSelected');
	setDefaultData(newObj);
	//$(newObj).data('direction', direction);
	$(newObj).on('click', function(){
		// Highlight the object
		$(this).find('.rackObjSelected').removeClass('rackObjSelected');
		$(this).addClass('rackObjSelected');
		
		setPartitionSizeInput();
		setInputValues();
		togglePartitionTypeDependencies();
		handleOrientationInput();
		updatePortNameDisplay();
	});
	setPartitionSizeInput();
	setInputValues();
	togglePartitionTypeDependencies();
	handleOrientationInput();
	//$(newObj).click();
}

function setCategory(){
	variables = getVariables();
	var category = $('#inputCategory').find('option:selected').data('value');
	$(variables['obj']).removeClass (function (index, css) {
		return (css.match (/(^|\s)category\S+/g) || []).join(' ');
	});
	$(variables['obj']).addClass(category);
}

function buildObjectArray(elem){
	var parent = [];

	$(elem).children('div').each(function(){
		var workingArray = $(this).data();
		workingArray['flex'] = $(this).css('flex-grow');
		if ($(this).children('div.flex-container').length) {
			workingArray['children'] = buildObjectArray(this);
		}
		if($(this).data('partitionType') == 'Connectable' && $(this).data('valueX') == 0 && $(this).data('valueY') == 0) {
			workingArray['partitionType'] = 'Generic';
		}
		parent.push(workingArray);
	});

	return parent;
}

function resetTemplateDetails(){
	$('#detailObjName').html('-');
	$('#detailTrunkedTo').html('-');
	$('#detailObjType').html('-');
	$('#detailObjFunction').html('-');
	$('#detailRUSize').html('-');
	$('#detailMountConfig').html('-');
	$('#detailPortRange').html('-');
	if(!$('#detailPortRange').hasClass('no-modal')) {
		$('#detailPortRange').addClass('no-modal');
	}
	$('#detailPortType').html('-');
	$('#detailMediaType').html('-');
	$('#detailTemplateImage').html('-');
	$('#inline-templateName').editable('setValue', '-').editable('disable');
	$('#inline-category').editable('disable').html('-');
	$('#inline-portOrientation').editable('destroy').html('-');
	$('#detailPortOrientation').html('-');
	$('#inline-enclosureTolerance').editable('destroy').html('-');
	$('#detailEnclosureTolerance').html('-');
	
	$('#objClone').addClass('disabled');
	$('#objFind').addClass('disabled');
	$('#objDelete').addClass('disabled');
}

function togglePartitionTypeDependencies(){
	var partitionType = $('input.partitionType:checked').val();
	var objectType = $('input.objectType:checked').val();
	$('.dependantField.partitionType').hide();
	$('#partitionH, #partitionV, #customPartitionAdd, #customPartitionRemove, #objectPartitionSize, #objectMediaType, #objectPortType, #objectPortOrientation, #objectPortLayout').prop('disabled', false);
	switch(partitionType) {
		case 'Generic':
			$('.dependantField.partitionType.generic').show().prop('disabled', false);
			break;
			
		case 'Connectable':
			var objectFunction = $('input.objectFunction:checked').val();
			switch(objectFunction) {
				case 'Endpoint':
					$('.dependantField.partitionType.connectable.endpoint').show().prop('disabled', false);
					break;
					
				case 'Passive':
					$('.dependantField.partitionType.connectable.passive').show().prop('disabled', false);
					break;
			}
			break;
			
		case 'Enclosure':
			$('.dependantField.partitionType.enclosureField').show();
			break;
	}
	
	switch(objectType) {
		case 'Standard':
			$('#inputPartitionTypeGeneric').prop('disabled', false);
			$('#inputPartitionTypeConnectable').prop('disabled', false);
			$('#inputPartitionTypeEnclosure').prop('disabled', false);
			break;
			
		case 'Insert':
			$('#inputPartitionTypeGeneric').prop('disabled', false);
			$('#inputPartitionTypeConnectable').prop('disabled', false);
			$('#inputPartitionTypeEnclosure').prop('disabled', true);
			break;
	}
}

function toggleObjectTypeDependencies(){
	var objectType = $('input.objectType:checked').val();
	$('.dependantField.objectType').hide();
	switch(objectType) {
		case 'Standard':
			$('.dependantField.objectType.standard').show().prop('disabled', false);
			break;
			
		case 'Insert':
			$('.dependantField.objectType.insert').show().prop('disabled', false);
			break;
	}
}

function resetCategoryForm(defaultCategoryColor){
	$('#inputCategoryID').val(0);
	$('#inputCategoryCurrentID').val(0);
	$('#inputCategoryName').val('');
	$('#color-picker').spectrum('set', defaultCategoryColor);
	$('#inputCategoryDefault').prop({'checked':false,'disabled':false});
	$('#categoryList').children('button').removeClass('rackObjSelected');
}

function initializeEditable(){
	//Object Name
	$('#inline-templateName').editable({
		display: function(value){
			$(this).text(value);
		},
		pk: 1,
		mode: 'inline',
		url: 'backend/process_object-custom.php',
		params: function(params){
			var templateID = $('#selectedObjectID').val();
			var data = {
				'action':'edit',
				'templateID':templateID,
				'attribute':$(this).attr('id'),
				'value':params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		success: function(response) {
			var templateID = $('#selectedObjectID').val();
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				$('.templateName'+templateID).html(responseJSON.success);
			}
		}
	}).editable('option', 'disabled', true);
}

function filterTemplates(containerElement, inputElement, categoryContainers){
	var tags = $(inputElement).tagsinput('items');
	var templates = $(containerElement).find('.object-wrapper');
	
	if($(tags).length) {
		$(templates).hide().removeClass('templateVisible');
		
		$.each(templates, function(indexTemplate, valueTemplate){
			var templateObj = $(valueTemplate);
			var templateName = $(valueTemplate).data('templateName').toLowerCase();
			var match = true;
			$.each(tags, function(indexTag, valueTag){
				var tag = valueTag.toLowerCase();
				if(templateName.indexOf(tag) >= 0 && match) {
					match = true;
				} else {
					match = false;
				}
			});
			
			if(match) {
				$(templateObj).show().addClass('templateVisible');
			}
		});
		
		$.each(categoryContainers, function(){
			if($(this).find('.object-wrapper.templateVisible').size()) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	} else {
		$(templates).show().addClass('templateVisible');;
		$(categoryContainers).show();
	}
}

function initializeImageUpload(templateID, templateFace){
	$('#fileTemplateImage').remove();
	$('#containerTemplateImage').html('<input type="file" name="files[]" id="fileTemplateImage" multiple="multiple">');
	$('#fileTemplateImage').filer({
        limit: 1,
        maxSize: null,
        extensions: null,
        changeInput: '<div class="jFiler-input-dragDrop"><div class="jFiler-input-inner"><div class="jFiler-input-icon"><i class="icon-jfi-cloud-up-o"></i></div><div class="jFiler-input-text"><h3>Drag & Drop file here</h3> <span style="display:inline-block; margin: 15px 0">or</span></div><a class="jFiler-input-choose-btn btn btn-custom waves-effect waves-light">Browse Files</a></div></div>',
        showThumbs: true,
        theme: "dragdropbox",
        templates: {
            box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
            item: '<li class="jFiler-item">\
                        <div class="jFiler-item-container">\
                            <div class="jFiler-item-inner">\
                                <div class="jFiler-item-thumb">\
                                    <div class="jFiler-item-status"></div>\
                                    <div class="jFiler-item-info">\
                                        <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                        <span class="jFiler-item-others">{{fi-size2}}</span>\
                                    </div>\
                                    {{fi-image}}\
                                </div>\
                                <div class="jFiler-item-assets jFiler-row">\
                                    <ul class="list-inline pull-left">\
                                        <li>{{fi-progressBar}}</li>\
                                    </ul>\
                                    <ul class="list-inline pull-right">\
                                        <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                    </ul>\
                                </div>\
                            </div>\
                        </div>\
                    </li>',
            itemAppend: '<li class="jFiler-item">\
                            <div class="jFiler-item-container">\
                                <div class="jFiler-item-inner">\
                                    <div class="jFiler-item-thumb">\
                                        <div class="jFiler-item-status"></div>\
                                        <div class="jFiler-item-info">\
                                            <span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 25}}</b></span>\
                                            <span class="jFiler-item-others">{{fi-size2}}</span>\
                                        </div>\
                                        {{fi-image}}\
                                    </div>\
                                    <div class="jFiler-item-assets jFiler-row">\
                                        <ul class="list-inline pull-left">\
                                            <li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
                                        </ul>\
                                        <ul class="list-inline pull-right">\
                                            <li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
                                        </ul>\
                                    </div>\
                                </div>\
                            </div>\
                        </li>',
            progressBar: '<div class="bar"></div>',
            itemAppendToEnd: false,
            removeConfirmation: true,
            _selectors: {
                list: '.jFiler-items-list',
                item: '.jFiler-item',
                progressBar: '.bar',
                remove: '.jFiler-item-trash-action'
            }
        },
        dragDrop: {
            dragEnter: null,
            dragLeave: null,
            drop: null,
        },
        uploadFile: {
            url: 'backend/process_image-upload.php',
            data: {
				action:'templateImage',
				templateID:templateID,
				templateFace:templateFace
			},
            type: 'POST',
            enctype: 'multipart/form-data',
            beforeSend: function(){},
            success: function(data, el){
                var parent = el.find(".jFiler-jProgressBar").parent();
                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                    $("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");
                });
				var responseJSON = JSON.parse(data);
				var response = responseJSON.success;
				$('#detailTemplateImage').children('img').attr('src', response.imgPath).show();
            },
            error: function(el){
                var parent = el.find(".jFiler-jProgressBar").parent();
                el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
                    $("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");
                });
            },
            statusCode: null,
            onProgress: null,
            onComplete: null
        },
        addMore: false,
        clipBoardPaste: true,
        excludeName: null,
        beforeRender: null,
        afterRender: null,
        beforeShow: null,
        beforeSelect: null,
        onSelect: null,
        afterShow: null,
        onRemove: null,
        onEmpty: null,
        options: null,
        captions: {
            button: "Choose Files",
            feedback: "Choose files To Upload",
            feedback2: "files were chosen",
            drop: "Drop file here to Upload",
            removeConfirmation: "Are you sure you want to remove this file?",
            errors: {
                filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
                filesType: "Only Images are allowed to be uploaded.",
                filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
                filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
            }
        }
    });
}

function reloadTemplates(){
	$('#templateContainer').children().remove();
	$('#templateContainer').load('/backend/retrieve_build-objects.php', function(){
		makeRackObjectsClickable();
	});
}

function setPortNameFieldFocus(){
	$('.portNameFields').off('focus');
	$('.portNameFields').focus(function(){
		//$(document).data('focusedPortNameField', $(this));
		//handlePortNameOptions();
		var focusedPortNameField = $(this);
		$('.portNameFields').removeClass('input-focused');
		$(focusedPortNameField).addClass('input-focused');
		handlePortNameOptions();
	});
}

function handlePortNameOptions(){
	//var focusedPortNameField = $(document).data('focusedPortNameField');
	var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
	var valuePortNameType = $(focusedPortNameField).data('type');
	$('#selectPortNameFieldType').children('option[value="'+valuePortNameType+'"]').prop('selected', true);
	
	if(valuePortNameType == 'static') {
		
		// Set identifier
		$(focusedPortNameField).prev('em').html('&nbsp;');
		
		$('#inputPortNameFieldCount').val(0);
		$('#selectPortNameFieldOrder').children('option[value="1"]').prop('selected', true);
		$('#inputPortNameFieldCount').prop("disabled", true);
		$('#selectPortNameFieldOrder').prop("disabled", true);
	} else if(valuePortNameType == 'incremental' || valuePortNameType == 'series'){
		var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
		var numberOfIncrementals = $(incrementalElements).length;
		
		// Adjust order select
		$('#selectPortNameFieldOrder').empty();
		var x;
		for(x=1; x<=numberOfIncrementals; x++) {
			var optionString = convertNumToDeg(x, numberOfIncrementals);
			$('#selectPortNameFieldOrder').append('<option value="'+x+'">'+optionString+'</option>');
		}
		
		var valuePortNameCount = $(focusedPortNameField).data('count');
		var valuePortNameOrder = $(focusedPortNameField).data('order');
		$('#inputPortNameFieldCount').val(valuePortNameCount);
		$('#selectPortNameFieldOrder').children('option[value="'+valuePortNameOrder+'"]').prop('selected', true);
		$('#selectPortNameFieldOrder').prop("disabled", false);
		if(valuePortNameType == 'series') {
			$('#inputPortNameFieldCount').prop("disabled", true);
		} else if(valuePortNameType == 'incremental') {
			$('#inputPortNameFieldCount').prop("disabled", false);
		}
	}
}

function convertNumToDeg(num, total){
	
	// Invert the number... in a series of 3 incremental fields, the 3rd field in order will display as "1st"
	var numArray = new Array(total);
	for(x=0; x<total; x++) {
		numArray[x] = x+1;
	}
	invertedNum = numArray[total - num];
	
	// Determine superscript
	if(invertedNum==1) {
		superScript = 'st';
	} else if(invertedNum==2) {
		superScript = 'nd';
	} else if(invertedNum==3) {
		superScript = 'rd';
	} else {
		superScript = 'th';
	}
	
	// Smoosh it together
	string = invertedNum + superScript;
	
	return string;
}

function reorderIncrementals(newOrder){
	//var focusedPortNameField = $(document).data('focusedPortNameField');
	
	var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
	var numberOfIncrementals = $(incrementalElements).length;
	var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
	var originalOrder = parseInt($(focusedPortNameField).data('order'));
	$(incrementalElements).each(function(){
		
		var currentOrder = parseInt($(this).data('order'));
		
		if(currentOrder > originalOrder) {
			if(currentOrder > newOrder) {
				var x = currentOrder;
			} else if(currentOrder < newOrder) {
				var x = currentOrder - 1;
			} else {
				var x = currentOrder - 1;
			}
		} else if(currentOrder < originalOrder) {
			if(currentOrder > newOrder) {
				var x = currentOrder + 1;
			} else if(currentOrder < newOrder) {
				var x = currentOrder;
			} else {
				var x = currentOrder + 1;
			}
		} else {
			var x = newOrder;
		}
		
		var y = convertNumToDeg(x, numberOfIncrementals);
		$(this).data('order', x);
		$(this).prev('em').html(y);
	});
}

function resetIncrementals(){
	//var focusedPortNameField = $(document).data('focusedPortNameField');
	var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
	var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
	var numberOfIncrementals = $(incrementalElements).length;
	
	// Set order to last if this is an unorderable field changing to an orderable field?
	var selectedOrder = parseInt($(focusedPortNameField).data('order'));
	
	/*
	if(selectedOrder == 0) {
		$(focusedPortNameField).data('order', numberOfIncrementals);
	}
	*/
	
	incrementalElements = incrementalElements.sort(function(a, b) {
		return parseInt($(a).data('order')) - parseInt($(b).data('order'));
	});
	
	$(incrementalElements).each(function(index){
		var newOrder = index+1;
		var newOrderString = convertNumToDeg(newOrder, numberOfIncrementals);
		$(this).data('order', newOrder);
		$(this).prev().html(newOrderString);
	});
}

function handlePortNameFieldAddRemoveButtons(){
	var portNameFieldCount = $('#portNameFieldContainer').children().length;
	
	if(portNameFieldCount >= 5) {
		$('#buttonAddPortNameField').prop("disabled", true);
	} else {
		$('#buttonAddPortNameField').prop("disabled", false);
	}
	
	if(portNameFieldCount <= 1) {
		$('#buttonDeletePortNameField').prop("disabled", true);
	} else {
		$('#buttonDeletePortNameField').prop("disabled", false);
	}
}

function updateportNameFormat(){
	var allElements = $('.portNameFields');
	var allElementsArray = [];
	
	$(allElements).each(function(){
		var elementType = $(this).data('type');
		var elementValue = $(this).val();
		var elementCount = parseInt($(this).data('count'));
		var elementOrder = parseInt($(this).data('order'));
		if(elementType == 'series') {
			elementValue = elementValue.split(',');
			var elementCount = elementValue.length;
		}
		
		allElementsArray.push({
			type: elementType,
			value: elementValue,
			count: elementCount,
			order: elementOrder
		});
	});
	
	if($(document).data('portNameFormatAction') == 'edit') {
		$(document).data('portNameFormatEdit', allElementsArray);
	} else {
		var variables = getVariables();
		$(variables['selectedObj']).data('portNameFormat', allElementsArray);
	}
}

function updatePortNameDisplay(){
	if($(document).data('portNameFormatAction') == 'edit') {
		var portTotal = $(document).data('portTotalEdit');
		var portNameFormat = $(document).data('portNameFormatEdit');
	} else {
		var variables = getVariables();
		var portLayoutX = $(variables['selectedObj']).data('valueX');
		var portLayoutY = $(variables['selectedObj']).data('valueY');
		var portTotal = portLayoutX * portLayoutY;
		var portNameFormat = $(variables['selectedObj']).data('portNameFormat');
	}
	
	var data = {
		portNameFormat: portNameFormat,
		portTotal: portTotal
	};
	
	// Convert to JSON string so it can be posted
	data = JSON.stringify(data);
	
	// Post user input
	$.post('backend/process_port-name-format.php', {'data':data}, function(responseJSON){
		var response = JSON.parse(responseJSON);
		if (response.active == 'inactive'){
			window.location.replace("/");
		} else if ($(response.error).size() > 0){
			displayErrorElement(response.error, $('#alertMsgPortName'));
			$('#portNameDisplayConfig').html('Error');
			$('#portNameDisplay').html('Error');
		} else {
			$('#alertMsgPortName').empty();
			
			if($(document).data('portNameFormatAction') == 'add') {
				$('#portNameDisplay').html(response.success.portNameListShort);
			} else if($(document).data('portNameFormatAction') == 'edit') {
				$('#detailPortRange').html('<a href="#">'+response.success.portRange+'</a>');
			} else {
				$('#portNameDisplay').html(response.success.portNameListShort);
			}
			
			$('#portNameDisplayConfig').html(response.success.portNameListLong);
		}
	});
}

function setPortNameInput(){
	if($(document).data('portNameFormatAction') == 'edit') {
		var portNameFormat = $(document).data('portNameFormatEdit');
	} else {
		var variables = getVariables();
		var portNameFormat = $(variables['selectedObj']).data('portNameFormat');
	}
	
	$('#portNameFieldContainer').empty();
	$.each(portNameFormat, function(key, item){
		var nameFieldHTML = $('<div class="col-sm-2 no-padding"><em>&nbsp</em><input type="text" class="portNameFields form-control" value="'+item.value+'" data-type="'+item.type+'" data-count="'+item.count+'" data-order="'+item.order+'"></div>');
		$('#portNameFieldContainer').append(nameFieldHTML);
	});
	resetIncrementals();
	$('.portNameFields').on('keyup', function(){
		updateportNameFormat();
		updatePortNameDisplay();
	});
}

function initializeTemplateCatalog(){
	// Make catalog filterable
	$('#templateCatalogFilter').on('itemAdded', function(event){
		var containerElement = $('#templateCatalogAvailableContainer');
		var inputElement = $('#templateCatalogFilter');
		var categoryContainers = $(containerElement).find('.categoryContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	}).on('itemRemoved', function(event){
		var containerElement = $('#templateCatalogAvailableContainer');
		var inputElement = $('#templateCatalogFilter');
		var categoryContainers = $(containerElement).find('.categoryContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	});
	
	// Make catalog titles expandable
	$('#containerTemplateCatalog').find('.categoryTitle').on('click', function(){
		var categoryName = $(this).data('categoryName');
		if($('#containerTemplateCatalog').find('.category'+categoryName+'Container').is(':visible')) {
			$('#containerTemplateCatalog').find('.category'+categoryName+'Container').hide(400);
			$('#containerTemplateCatalog').children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
		} else {
			$('#containerTemplateCatalog').find('.categoryContainer').hide(400);
			$('#containerTemplateCatalog').find('.categoryTitle').children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
			$('#containerTemplateCatalog').find('.category'+categoryName+'Container').show(400);
			$('#containerTemplateCatalog').children('i').removeClass('fa-caret-right').addClass('fa-caret-down');
		}
	});
	
	// Retreive template details when clicked
	$('#templateCatalogAvailableContainer').find('.selectable').click(function(event){
		event.stopPropagation();
		if($(this).hasClass('stockObj')) {
			var object = $(this);
			var partitionDepth = 0;
		} else {
			var object = $(this).closest('.stockObj');
			var partitionDepth =  parseInt($(this).data('depth'), 10);
		}
		
		//Store objectID
		var objID = $(object).data('templateId');
		$(document).data('templateCatalogID', objID);
		
		//Store objectFace
		var objFace = $(object).data('objectFace');
		
		//Remove hightlight from all racked objects
		$('#templateCatalogAvailableContainer').find('.rackObjSelected').removeClass('rackObjSelected');
		
		//Hightlight the selected racked object
		$(this).addClass('rackObjSelected');
		
		//Collect object data
		var data = {
			objID: objID,
			objFace: objFace,
			partitionDepth: partitionDepth
			};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("https://patchcablemgr.com/public/template-catalog-details.php", {data:data}, function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				var response = responseJSON.success;
				$('#detailTemplateCatalogObjName').html(response.objectName);
				$('#detailTemplateCatalogTemplateName').html(response.templateName);
				$('#detailTemplateCatalogCategory').html(response.categoryName);
				$('#detailTemplateCatalogTrunkedTo').html(response.trunkedTo);
				$('#detailTemplateCatalogObjType').html(response.objectType);
				$('#detailTemplateCatalogObjFunction').html(response.function);
				$('#detailTemplateCatalogRUSize').html(response.RUSize);
				$('#detailTemplateCatalogMountConfig').html(response.mountConfig);
				$('#detailTemplateCatalogPortRange').html(response.portRange);
				$('#detailTemplateCatalogPortType').html(response.portType);
				$('#detailTemplateCatalogMediaType').html(response.mediaType);
				$('#detailTemplateCatalogImage').html('<img id="elementTemplateCatalogImage" src="" height="" width=""></img>');
				$('#elementTemplateCatalogImage').attr({
					src:response.templateImgPath,
					height:response.templateImgHeight + 'px',
					width:response.templateImgWidth + '%'
				});
				$('#buttonTemplateCatalogImport').prop("disabled", false);
			}
		});
	});
	
	// Import selected template
	$('#buttonTemplateCatalogImport').on('click', function(){
		var templateID = $(document).data('templateCatalogID');
		
		//Collect object data
		var data = {
			templateID: templateID
			};
		data = JSON.stringify(data);
		
		$.post("backend/process_template-import.php", {data:data}, function(responseJSON){
			var response = JSON.parse(responseJSON);
			if (response.active == 'inactive'){
				window.location.replace("/");
			} else if ($(response.error).size() > 0){
				displayErrorElement(response.error, $('#alertMsgCatalog'));
			} else {
				displaySuccessElement(response.success, $('#alertMsgCatalog'));
				reloadTemplates();
			}
		});
	});
}

function handleMountConfig(mountConfig){
	if (mountConfig == 1) {
		//4-Post
		$('#inputSideCount4Post').prop('checked', true);
		$('#inputSideCount').val(1);
		$('.sideSelector').prop('disabled', false);
		$('#objectTypeInsert0').prop('disabled', true);
		$('#objectTypeInsert1').prop('disabled', true);
	} else {
		//2-Post
		$('#inputSideCount2Post').prop('checked', true);
		$('#inputSideCount').val(0);
		$('.sideSelector').prop('disabled', true);
		$('#objectTypeInsert0').prop('disabled', false);
		setPartitionSizeInput();
		setInputValues();
		togglePartitionTypeDependencies();
		handleOrientationInput();
		$('#sideSelectorFront').prop('checked', true);
	}
}

function handleScrollLock(){
	var scrollLockState = $('#checkboxScrollLock').is(':checked');
	
	if(!scrollLockState) {
		var topnavHeight = $('#topnav').height();
		var cabinetTop = $('#cabinetContainer').offset().top;
		var cabinetHeight = $('#cabinetContainer').outerHeight();
		var cabinetBuffer = 30;
		var cabinetTopToNav = cabinetTop - topnavHeight - cabinetBuffer;
		$('#cabinetContainer').offset({top:cabinetTop});

		$(window).scroll(function() {
			var windowTop = $(window).scrollTop();
			var cabinetBottom = cabinetHeight + $('#cabinetContainer').offset().top;
			var footerTop = $('footer').offset().top;
			if(windowTop > cabinetTopToNav && cabinetBottom <= footerTop+5) {
				var cabinetOffset = windowTop + topnavHeight + cabinetBuffer;
				if(cabinetOffset+cabinetHeight > footerTop) {
					cabinetOffset = footerTop - cabinetHeight;
				}
				$('#cabinetContainer').offset({top:cabinetOffset});
			} else if(windowTop < cabinetTopToNav) {
				$('#cabinetContainer').offset({top:cabinetTop});
			}
		});
	} else {
		$('#cabinetContainer').css('position', 'static');
		$(window).unbind('scroll');
	}
	
}

function buildInsertParent(RUSize, hUnits, vUnits, encLayoutX, encLayoutY){
	var variables = getVariables();
	var totalVUnits = RUSize * 2;
	var flexRow = hUnits/24;
	var flexCol = vUnits/totalVUnits;
	var table = '';
	
	table += '<div class="flex-container-parent" style="flex-direction:row;">';
	table += '<div style="display:flex; flex-direction:column; flex:'+flexRow+'">';
	table += '<div style="display:flex; flex-direction:column; flex:'+flexCol+'">';
	table += buildTable(encLayoutX, encLayoutY, 'enclosureTable', true);
	table += '</div>';
	table += '</div>';
	
	// Clear the preview object of any user formatting
	$(variables['obj']).removeClass(function(index, css) {
		return (css.match(/(^|\s)category\S+/g) || []).join(' ');
	});
	
	expandRackUnit(RUSize);
	setObjectSize();
	$(variables['obj']).html(table);
	
	$(variables['obj'])
	.find('.tableRow:first')
	.find('.tableCol:first')
	.html('<div id="previewObj3" class="objBaseline" data-h-units="'+hUnits+'" data-v-units="'+vUnits+'" data-value-x="'+encLayoutX+'" data-value-y="'+encLayoutY+'"></div>');
}

$( document ).ready(function() {
	
	var scrollLockState = $('#checkboxScrollLock').is(':checked');
	if(scrollLockState) {
		var scrollLockSetting = 'locked';
	} else {
		var scrollLockSetting = 'unlocked';
	}
	$('#scrollState').html(scrollLockSetting);
	handleScrollLock();	
	toggleObjectTypeDependencies();
	
	//$('#containerTemplateCatalog').load('https://patchcablemgr.com/public/template-catalog-013.php', function(){
	$('#containerTemplateCatalog').load('/backend/retrieve_template-catalog.php', function(){
		initializeTemplateCatalog();
	});
	
	$('#templateFilter').on('itemAdded', function(event){
		var containerElement = $('#availableObjects');
		var inputElement = $('#templateFilter');
		var categoryContainers = $(containerElement).find('.categoryContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	}).on('itemRemoved', function(event){
		var containerElement = $('#availableObjects');
		var inputElement = $('#templateFilter');
		var categoryContainers = $(containerElement).find('.categoryContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	});
	
	//X-editable buttons style
	$.fn.editableform.buttons = 
	'<button type="submit" class="btn btn-sm btn-primary editable-submit waves-effect waves-light"><i class="zmdi zmdi-check"></i></button>' +
	'<button type="button" class="btn btn-sm editable-cancel btn-secondary waves-effect"><i class="zmdi zmdi-close"></i></button>';
	initializeEditable();
	
	var defaultCategoryColor = '#d3d3d3';
	makeRackObjectsClickable();
	$(document).data('obj', $('#previewObj0'));
	$(document).data('templateSide', 0);
	$(document).data('availableTemplateSide', 0);
	setObjectSize();
	for(x=0; x<2; x++) {
		buildObj(x, 24, 2, 'column');
	}
	setCategory();
	
	$('#checkboxScrollLock').on('change', function(){
		handleScrollLock();
	});
	
	// Clone a template to the workspace
	$('#objClone').click(function(e){
		e.preventDefault();
		if($(this).hasClass('disabled')) {
			return false;
		}
		
		var templateID = $('#selectedObjectID').val();
		var templateSide = $(document).data('availableTemplateSide');
		var templateObj = $('#availableContainer'+templateSide).find('[data-template-id="'+templateID+'"]');
		var templateName = $(templateObj).data('templateName');
		var templateType = $(templateObj).data('templateType');
		var templateFrontImage = $(templateObj).data('templateFrontImage');
		var templateRearImage = $(templateObj).data('templateRearImage');
		
		// Store template data
		var RUSize = $(templateObj).data('ruSize');
		var categoryID = $(templateObj).data('templateCategoryId');
		var category = $(templateObj).data('templateCategoryName');
		var face = $(templateObj).data('objectFace');
		var templateFunction = $(templateObj).data('templateFunction');
		var templateObjChild = $(templateObj).children()[0];
		
		$('[name="objectTypeRadio"][value='+templateType+']').prop('checked', true);
		$('[name="category"][value='+categoryID+']').prop('selected', true);
		$('#inputName').val(templateName);
		$('#inputFrontImage').val(templateFrontImage);
		$('#inputRearImage').val(templateRearImage);
		
		if(templateType == 'Insert') {
			
			var parentHUnits = $(templateObj).data('parentHUnits');
			var parentVUnits = $(templateObj).data('parentVUnits');
			var parentEncLayoutX = $(templateObj).data('parentEncLayoutX');
			var parentEncLayoutY = $(templateObj).data('parentEncLayoutY');
			buildInsertParent(RUSize, parentHUnits, parentVUnits, parentEncLayoutX, parentEncLayoutY);
			switchSides(3);
			setCategory();
			$('#previewObj3').html($(templateObjChild).clone());
			var object = $('#previewObj3');
			objFaceArray = [object];
			
		} else {
			switchSides(0);
		
			for(x=0; x<2; x++) {
				var flexUnits = RUSize * 2;
				$('#previewObj'+x).data('vUnits', flexUnits);
				$('#previewObj'+x).children('.flex-container-parent').data('vUnits', flexUnits);
			}
		
			// Clone template faces to workspace and save them for reference
			var mountConfig = $(templateObj).data('objectMountConfig');
			if(mountConfig == 0) {
				$('#previewObj0').html($(templateObjChild).clone());
				var object = $('#previewObj0');
				
				objFaceArray = [object];
			} else {
				
				// Find the index of the opposite face
				var faceOpposite = face == 0 ? 1 : 0;
				
				// Store the opposite face
				var templateObjOpposite = $('#availableContainer'+faceOpposite).find('[data-template-id="'+templateID+'"]');
				var templateObjOppositeChild = $(templateObjOpposite).children()[0];
				
				// Clone front and rear to workspace
				$('#previewObj'+face).html($(templateObjChild).clone());
				$('#previewObj'+faceOpposite).html($(templateObjOppositeChild).clone());
				var object = $('#previewObj'+face);
				var objectOpposite = $('#previewObj'+faceOpposite);
				
				objFaceArray = [object, objectOpposite];
			}
			
		}
		
		// Apply template data
		handleMountConfig(mountConfig);
		$('#inputRU').val(RUSize);
		$('#inputCategory').children('[value='+categoryID+']').prop('selected', true);
		$('[name="objectFunction"][value='+templateFunction+']').prop('checked', true);
		expandRackUnit(RUSize);
		setObjectSize();
		setCategory();
		toggleObjectTypeDependencies();
		togglePartitionTypeDependencies();
		
		$.each(objFaceArray, function(){
			
			$(this).find('.flex-container').addClass('border-black transparency-20');
			$(this).find('.flex-container, .flex-container-parent').on('click', function(event){
				event.stopPropagation();
				makeTemplatePartitionClickable(this);
			});
		});
		
		var variables = getVariables();
		$(variables['selectedObj']).click();
	});
	
	// Delete a temlate
	$('#confirmObjDelete').click(function(){
		var templateID = $('#selectedObjectID').val();
		var data = {};
		data['id'] = templateID;
		data['action'] = 'delete';
		data = JSON.stringify(data);
		$.post('backend/process_object-custom.php', {'data':data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else if (responseJSON.success != ''){
				displaySuccess(responseJSON.success);
				$('#availableContainer').find('.object'+templateID).remove();
				resetTemplateDetails();
			} else {
				displayError(['Something went wrong.']);
			}
		});
	});

	// Start colorpicker
	$('#color-picker').spectrum({
		preferredFormat: 'hex',
		showButtons: false,
		showPaletteOnly: true,
		showPalette: true,
		color: defaultCategoryColor,
		palette: [
			[
			'#d581d6',
			'#d6819f',
			'#d68d8d',
			'#e59881',
			'#d6d678',
			'#a9a9a9'
			],
			[
			'#95d681',
			'#81d6a1',
			'#81d6ce',
			'#81bad6',
			'#92b2d6',
			'#d3d3d3'
			]
		],
		change: function(color){
			$('#color-picker').val(color);
		}
	}).val(defaultCategoryColor);
	
	// Select category
	$('#categoryList').children('button').on('click', function(){
		if($(this).hasClass('rackObjSelected')) {
			resetCategoryForm(defaultCategoryColor);
		} else {
			$('#categoryList').children('button').removeClass('rackObjSelected');
			$(this).addClass('rackObjSelected');
			$('#inputCategoryID').val($(this).data('id'));
			$('#inputCategoryCurrentID').val($(this).data('id'));
			$('#inputCategoryName').val($(this).data('name'));
			$('#color-picker').spectrum('set', $(this).data('color'));
			if($(this).data('default') == 1) {
				$('#inputCategoryDefault').prop({'checked':true,'disabled':true});
			} else {
				$('#inputCategoryDefault').prop({'checked':false,'disabled':false});
			}
		}
	});
	
	// Delete selected categories
	$('#manageCategories-Delete').on('click', function(){
		var data = JSON.stringify($('#manageCategoriesCurrent-Form').serializeArray());
		$.post('backend/process_object-category.php', {'data':data}, function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				$('#alertMsgCategory').empty();
				$.each(responseJSON.error, function(index, errorTxt){
					alertMsg += '<div class="alert alert-danger" role="alert">';
					alertMsg += '<strong>Oops!</strong>  '+errorTxt;
					alertMsg += '</div>';
					$('#alertMsgCategory').append(alertMsg);
				});
				$("html, body").animate({ scrollTop: 0 }, "slow");
			} else {
				$('#categoryOption'+responseJSON.success).remove();
				$('#categoryList'+responseJSON.success).remove();
				resetCategoryForm(defaultCategoryColor);
				alertMsg += '<div class="alert alert-success" role="alert">';
				alertMsg += '<strong>Success!</strong>  Category was deleted.';
				alertMsg += '</div>';
				$('#alertMsgCategory').html(alertMsg);
			}
		});
	});
	
	// Category Manager form save
	$('#manageCategories-Save').on('click', function(event){
		var defaultOptionProp = $('#inputCategoryDefault').prop('disabled');
		if(defaultOptionProp) {
			$('#inputCategoryDefault').prop('disabled', false);
		}
		var data = JSON.stringify($('#manageCategories-Form').serializeArray());
		$('#inputCategoryDefault').prop('disabled', defaultOptionProp);
		$.post('backend/process_object-category.php', {'data':data}, function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				$('#alertMsgCategory').empty();
				$.each(responseJSON.error, function(index, errorTxt){
					alertMsg += '<div class="alert alert-danger" role="alert">';
					alertMsg += '<strong>Oops!</strong>  '+errorTxt;
					alertMsg += '</div>';
					$('#alertMsgCategory').append(alertMsg);
				});
				$("html, body").animate({ scrollTop: 0 }, "slow");
			} else {
				$("#customStyle").load('includes/content-custom_style.php');
				if(responseJSON.success.defaultOption == 1) {
					var currentDefault = $('#categoryList').children('button[data-default="1"]');
					$(currentDefault).data('default', 0);
					$(currentDefault).html($(currentDefault).data('name'));
				}
				if(responseJSON.success.action == 'add') {
					$('#inputCategory').append('<option id="categoryOption'+responseJSON.success.id+'" data-value="category'+responseJSON.success.name+'" value="'+responseJSON.success.id+'">'+responseJSON.success.name+'</option>');
					var defaultIdentifier = responseJSON.success.defaultOption == 1 ? '*' : '';
					$('#categoryList').append('<button id="categoryList'+responseJSON.success.id+'" type="button" class="category'+responseJSON.success.name+' btn-block btn waves-effect waves-light" data-id="'+responseJSON.success.id+'" data-name="'+responseJSON.success.name+'" data-color="'+responseJSON.success.color+'" data-default="'+responseJSON.success.defaultOption+'">'+responseJSON.success.name+defaultIdentifier+'</button>');
					$('#categoryList').children().last().on('click', function(){
						if($(this).hasClass('rackObjSelected')) {
							resetCategoryForm(defaultCategoryColor);
						} else {
							$('#categoryList').children('button').removeClass('rackObjSelected');
							$(this).addClass('rackObjSelected');
							$('#inputCategoryID').val($(this).data('id'));
							$('#inputCategoryCurrentID').val($(this).data('id'));
							$('#inputCategoryName').val($(this).data('name'));
							$('#color-picker').spectrum('set', $(this).data('color'));
							if($(this).data('default') == 1) {
								$('#inputCategoryDefault').prop({'checked':true,'disabled':true});
							} else {
								$('#inputCategoryDefault').prop({'checked':false,'disabled':false});
							}
						}
					});
					resetCategoryForm(defaultCategoryColor);
					var successTxt = responseJSON.success;
					var alertMsg = '';
					alertMsg += '<div class="alert alert-success" role="alert">';
					alertMsg += '<strong>Success!</strong>  Category added.';
					alertMsg += '</div>';
					$('#alertMsgCategory').html(alertMsg);
				} else {
					// Update category select option
					$('#categoryOption'+responseJSON.success.id).data('value', 'category'+responseJSON.success.name);
					$('#categoryOption'+responseJSON.success.id).val(responseJSON.success.id);
					$('#categoryOption'+responseJSON.success.id).html(responseJSON.success.name);
					
					// Update category button
					$('#categorList'+responseJSON.success.id).removeClass(function(index, className){
						return (className.match (/(^|\s)category\S+/g) || []).join(' ');
					});
					$('#categoryList'+responseJSON.success.id).addClass('category'+responseJSON.success.name);
					$('#categoryList'+responseJSON.success.id).data('name', responseJSON.success.name);
					$('#categoryList'+responseJSON.success.id).data('color', responseJSON.success.color);
					var defaultIdentifier = '';
					if(responseJSON.success.defaultOption == 1) {
						defaultIdentifier = '*';
					}
					$('#categoryList'+responseJSON.success.id).data('default', responseJSON.success.defaultOption);
					$('#categoryList'+responseJSON.success.id).html(responseJSON.success.name+defaultIdentifier);
					resetCategoryForm(defaultCategoryColor);
					var alertMsg = '';
					alertMsg += '<div class="alert alert-success" role="alert">';
					alertMsg += '<strong>Success!</strong>  Category updated.';
					alertMsg += '</div>';
					$('#alertMsgCategory').html(alertMsg);
				}
			}
		});
	});
	
	// Form submit
	$('#objectEditor-Submit').on('click', function(){
		// Gather user input
		var data = {};
		data['action'] = "add";
		data['name'] = $('#inputName').val();
		data['category'] = $('#inputCategory').val();
		data['type'] = $('input[name="objectTypeRadio"]:checked').val();
		data['function'] = $('input[name="objectFunction"]:checked').val();
		data['RUSize'] = $('#inputRU').val();
		data['frontImage'] = $('#inputFrontImage').val();
		data['rearImage'] = $('#inputRearImage').val();
		data['objects'] = [];
		if(data['type'] == 'Insert'){
			var encLayoutX = parseInt($('#previewObj3').data('valueX'), 10);
			var encLayoutY = parseInt($('#previewObj3').data('valueY'), 10);
			var objHUnits = parseInt($('#previewObj3').data('hUnits'), 10);
			var objVUnits = parseInt($('#previewObj3').data('vUnits'), 10);
			var insertRUSize = Math.ceil(objVUnits/2);
			//data['RUSize'] = insertRUSize;
			data['encLayoutX'] = encLayoutX;
			data['encLayoutY'] = encLayoutY;
			data['hUnits'] = objHUnits;
			data['vUnits'] = objVUnits;
			data['objects'].push(buildObjectArray('#previewObj3'));
		} else {
			//data['RUSize'] = $('#inputRU').val();
			data['mountConfig'] = $('input[name="sideCount"]:checked').val();
			for (var x=0; x<=data['mountConfig']; x++) {
				data['objects'].push(buildObjectArray('#previewObj'+x));
			}
		}
		
		// Convert to JSON string so it can be posted
		data = JSON.stringify(data);
		
		// Post user input
		$.post('backend/process_object-custom.php', {'data':data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				location.reload();
			}
		});
	});
	
	// Category
	$('#inputCategory').on('change', function(){
		setCategory();
	});
	
	// Side Count Selector
	$('.sideCount').on('change', function(){
		switchSides(0);
		var mountConfig = $(this).val();
		handleMountConfig(mountConfig);
	});
	
	// Side Switcher
	$('.sideSelector').on('change', function(){
		var selectedSide = $(this).val();
		switchSides(selectedSide);
		setCategory();
		setObjectSize();
		setPartitionSizeInput();
		setInputValues();
		togglePartitionTypeDependencies();
		handleOrientationInput();
	});
	
	// Detail Side Switcher
	$('.sideSelectorDetail').on('change', function(){
		switchSidesDetail($(this).val());
	});
	
	// RU Size
	$('#inputRU').on('change', function(){
		var variables = getVariables();
		
		for(x=0; x<2; x++) {
			var flexUnits = variables['RUSize'] * 2;
			$('#previewObj'+x).data('vUnits', flexUnits);
			$('#previewObj'+x).children('.flex-container-parent').data('vUnits', flexUnits);
		}
		
		expandRackUnit(variables['RUSize']);
		setObjectSize();
		setPartitionSizeInput();
		setInputValues();
		handleOrientationInput();
	});
	
	// Object Type
	$('input.objectType').on('change', function(){
		var category = $('#inputCategory').find('option:selected').data('value');
		$('inputFrontImage').val('');
		$('inputRearImage').val('');
		toggleObjectTypeDependencies();
		
		switch($(this).val()) {
			case 'Insert':
				
				// Inserts cannot be 4-post mountable
				$('.sideSelector').prop('disabled', true);
				$('#inputSideCount2Post').prop('checked', true);
				$('#sideSelectorFront').prop('checked', true);
				switchSides(3);
				
				// Variables must be retrieved after switchSides() because it updates the reference to the currently selected object
				var variables = getVariables();
				
				// Disable relevant input fields
				$('#partitionH, #partitionV, #inputPartitionTypeGeneric, #inputPartitionTypeConnectable, #inputPartitionTypeEnclosure, #customPartitionAdd, #customPartitionRemove, #objectPartitionSize, #objectMediaType, #objectPortType, #objectPortOrientation, #objectPortLayout').prop('disabled', true);
				
				// Clear the preview object of any user formatting
				$('#previewObj0').html('Select enclosure from "Available Objects" section.');
				$('#previewObj0').removeClass(function(index, css) {
					return (css.match(/(^|\s)category\S+/g) || []).join(' ');
				});
				$('.enclosure').on('click', function(){
					switchSides(0);
					
					var enclosureParent = $(this).parent();
					var enclosureObject = $(this).closest('.flex-container-parent');
					var enclosureObjectParent = $(enclosureObject).parent();
					var objectFlexDirection = $(enclosureObjectParent).css('flex-direction');
					var objectFunction = $(enclosureObjectParent).data('templateFunction');
					var hUnits = parseInt($(enclosureParent).data('hUnits'), 10);
					var vUnits = parseInt($(enclosureParent).data('vUnits'), 10);
					var encLayoutX = parseInt($(enclosureParent).data('valueX'), 10);
					var encLayoutY = parseInt($(enclosureParent).data('valueY'), 10);
					$('[name="objectFunction"][value='+objectFunction+']').prop('checked', true);
					var RUSize = parseInt($(enclosureObjectParent).data('ruSize'), 10);
					$('#inputRU').val(RUSize);
					
					// Enable input fields
					$('#partitionH, #partitionV, #inputPartitionTypeGeneric, #inputPartitionTypeConnectable, #objectType, #objectMediaType, #objectPortType, #objectPortOrientation, #objectPortLayout').prop('disabled', false);

					buildInsertParent(RUSize, hUnits, vUnits, encLayoutX, encLayoutY)
					
					$(document).data('templateSide', 3);

					buildObj(3, hUnits, vUnits, objectFlexDirection);
					setCategory();
					togglePartitionTypeDependencies();
					handleOrientationInput();
				});
				break;
				
			case 'Standard':
				switchSides(0);
				// Variables must be retrieved here... see above comment under 'Insert' case
				var variables = getVariables();
				$('#inputPartitionTypeGeneric, #inputPartitionTypeConnectable, #inputPartitionTypeEnclosure').prop('disabled', false);
				for(x=0; x<2; x++) {
					buildObj(x, 24, 2, 'column');
				}
				
				$(document).data('templateSide', 0);
				expandRackUnit(variables['RUSize']);
				setObjectSize();
				setCategory();
				handleOrientationInput();
				
				// Remove the click hook from enclosure elements
				// that were added when selecting 'insert' object type.
				$('.enclosure').off('click');
				break;
		}
		
	});
	
	// Object Function
	$('input.objectFunction').on('change', function(){
		switch($(this).val()) {
			case 'Endpoint':
				// Re-enable mounting config
				$('input.sideCount').prop('disabled', false);
				break;
				
			case 'Passive':
				// Default mounting config, only Endpoints can be 4-post
				$('.sideSelector').prop('disabled', true);
				$('input.sideCount').prop('disabled', true);
				$('#inputSideCount2Post').prop('checked', true);
				$('#sideSelectorFront').prop('checked', true);
				switchSides(0);
				setPartitionSizeInput();
				setInputValues();
				handleOrientationInput();
				break;
		}
		// Display only relevant input
		togglePartitionTypeDependencies();
	});
	
	// Partition Type
	$('input.partitionType').on('change', function(){
		var variables = getVariables();
		var partitionType = $(this).val();
		setDefaultData(variables['selectedObj']);
		$(variables['selectedObj']).data('partitionType', partitionType);
		$(variables['selectedObj']).empty();
		setPartitionSizeInput();
		setInputValues();
		togglePartitionTypeDependencies();
		
		// Connectable and Enclosure tables require flex-direction column
		if(partitionType == 'Connectable' || partitionType == 'Enclosure') {
			$(variables['selectedObj']).css('flex-direction', 'column');
		} else {
			var parentDirection = $(variables['selectedParent']).css('flex-direction');
			if(parentDirection == 'column') {
				var partitionDirection = 'row';
			} else {
				var partitionDirection = 'column';
			}
			$(variables['selectedObj']).css('flex-direction', partitionDirection).data('direction', partitionDirection);
		}
		
		handleOrientationInput();
		
		if(partitionType == 'Connectable') {
			buildPortTable();
			updatePortNameDisplay();
		} else if(partitionType == 'Enclosure') {
			var x = $('#inputEnclosureLayoutX').val();
			var y = $('#inputEnclosureLayoutY').val();
			var table = buildTable(x, y, 'enclosureTable', true);
			$(variables['selectedObj']).html(table);
		}
	});
	
	// Prevent Modal if Invoker is Disabled
	$('#portNameModal').on('hide.bs.modal', function (e) {
		if($(document).data('portNameFormatAction') == 'edit') {
			// Gather user input
			var data = {
				action: 'edit',
				attribute: 'portNameFormat',
				templateID: $('#selectedObjectID').val(),
				templateFace: $('#selectedObjectFace').val(),
				templateDepth: $('#selectedPartitionDepth').val(),
				value: $(document).data('portNameFormatEdit')
			};
			
			// Convert to JSON string so it can be posted
			data = JSON.stringify(data);
			
			// Post for validation
			$.post('backend/process_object-custom.php', {'data':data}, function(responseJSON){
				var response = JSON.parse(responseJSON);
				if (response.active == 'inactive'){
					window.location.replace("/");
				} else if ($(response.error).size() > 0){
					//$('#alertMsgPortName')
					displayError(response.error);
				} else {
					$('#detailPortRange').html('<a href="#">'+response.success+'</a>');
				}
			});
		}
	});
	
	// Prevent Modal if Invoker is Disabled
	$('#portNameModal').on('show.bs.modal', function (e) {
		var invoker = $(e.relatedTarget);
		if($(invoker).hasClass('no-modal')) {
			return false;
		}  
	});
	
	// Focus First Port Name Field
	$('#portNameModal').on('shown.bs.modal', function (e){
		
		var invoker = $(e.relatedTarget);
		$(document).data('portNameFormatAction', $(invoker).data('portNameAction'));
		
		setPortNameInput();
		updatePortNameDisplay();
		setPortNameFieldFocus();
		
		if(!$('.portNameFields:focus').length) {
			$('.portNameFields').first().focus();
		}
		
		//handlePortNameOptions();
		
		$('.portNameFields').on('keyup', function(){
			updateportNameFormat();
			updatePortNameDisplay();
		});
	});
	
	// Port Naming Add Field
	$('#buttonAddPortNameField').on('click', function(){
		//var fieldFocused = $(document).data('focusedPortNameField');
		var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
		var nameFieldHTML = $('<div class="col-sm-2 no-padding"><em>&nbsp</em><input type="text" class="portNameFields form-control" value="Port" data-type="static" data-count="0" data-order="0"></div>');
		nameFieldHTML.insertAfter(focusedPortNameField.parent());
		setPortNameFieldFocus();
		handlePortNameOptions();
		nameFieldHTML.children('input').focus();
		handlePortNameFieldAddRemoveButtons();
		updateportNameFormat();
		updatePortNameDisplay();
		
		$('.portNameFields').off('keyup');
		$('.portNameFields').on('keyup', function(){
			updateportNameFormat();
			updatePortNameDisplay();
		});
	});
	
	// Port Naming Remove Field
	$('#buttonDeletePortNameField').on('click', function(){
		//var fieldFocused = $(document).data('focusedPortNameField');
		var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
		var portNameFields = $('.portNameFields');
		var fieldFocusedIndex = $(portNameFields).index(focusedPortNameField);
		
		$(focusedPortNameField).parent().remove();
		resetIncrementals();
		$(portNameFields).eq(fieldFocusedIndex-1).focus();
		handlePortNameFieldAddRemoveButtons();
		updateportNameFormat();
		updatePortNameDisplay();
	});
	
	// Port Naming Type Selection
	$('#selectPortNameFieldType').on('change', function(){
		//var focusedPortNameField = $(document).data('focusedPortNameField');
		var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
		var valuePortNameType = $(this).val();
		
		focusedPortNameField.data('type', valuePortNameType);
		$(focusedPortNameField).attr('data-type', valuePortNameType);
		
		if(valuePortNameType == 'static') {
			$(focusedPortNameField).data('order', 0);
			$(focusedPortNameField).val('Port');
		} else if(valuePortNameType == 'incremental') {
			$(focusedPortNameField).val('1');
		} else if(valuePortNameType == 'series') {
			$(focusedPortNameField).val('a,b,c');
		}
		resetIncrementals();
		handlePortNameOptions();
		updateportNameFormat();
		updatePortNameDisplay();
	});
	
	// Port Naming Count
	$('#inputPortNameFieldCount').on('change', function(){
		//var focusedPortNameField = $(document).data('focusedPortNameField');
		var focusedPortNameField = $('#portNameFieldContainer').find('.input-focused');
		var valueCount = $(this).val();
		focusedPortNameField.data('count', valueCount);
		updateportNameFormat();
		updatePortNameDisplay();
	});
	
	// Port Naming Order
	$('#selectPortNameFieldOrder').on('change', function(){
		var valueOrder = parseInt($(this).val());
		reorderIncrementals(valueOrder);
		updateportNameFormat();
		updatePortNameDisplay();
	});
	
	// Port Naming Results Update
	$('#buttonPortNameModalUpdate').on('click', function(){
		var portTotal = 10;
		
		var portStringArray = [];
		var allElements = $('.portNameFields');
		var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
		var incrementalCount = $(incrementalElements).length;
		var incrementalArray = {};
		$(incrementalElements).each(function(){
			var elementType = $(this).data('type');
			var elementValue = $(this).val();
			if(elementType == 'incremental') {
				var elementCount = parseInt($(this).data('count'));
				if(elementCount == 0) {
					elementCount = portTotal;
				}
			} else if(elementType == 'series') {
				elementValue = elementValue.split(',');
				var elementCount = elementValue.length;
			}
			var elementOrder = parseInt($(this).data('order'));
			var elementNumerator = 0;
			incrementalArray[elementOrder] = {
				type: elementType,
				value: elementValue,
				count: elementCount,
				order: elementOrder,
				numerator: elementNumerator
			};
		});
		
		$.each(incrementalArray, function(index, item){
			if(item.order == incrementalCount) {
				item.numerator = 1;
			} else {
				var y = item.order+1;
				for(var x = y; x <= incrementalCount; x++) {
					item.numerator += incrementalArray[x].count;
				}
			}
		});
		
		for(var x=0; x<portTotal; x++) {
			var portString = '';
			$(allElements).each(function(){
				var dataType = $(this).data('type');
				if(dataType == 'static') {
					portString = portString + $(this).val();
				} else if(dataType == 'incremental' || dataType == 'series') {
					var incrementalOrder = parseInt($(this).data('order'));
					var incremental = incrementalArray[incrementalOrder];	
					var howMuchToIncrement = Math.floor(x/incremental.numerator);
					
					if(howMuchToIncrement >= incremental.count) {
						var rollOver = Math.floor(howMuchToIncrement / incremental.count);
						howMuchToIncrement = howMuchToIncrement - (rollOver*incremental.count);
					}
					if(dataType == 'incremental') {
						portString = portString + (parseInt(incremental.value) + howMuchToIncrement);
					} else if(dataType == 'series') {
						portString = portString + incremental.value[howMuchToIncrement];
					}
				}
			});
			portStringArray.push(portString);
		}
		$('#portNameResults').empty();
		$.each(portStringArray, function(index, item){
			$('#portNameResults').append(item+'<br>');
		});
		$('#portNameResults').append('...');
	});
	
	// Custom Partition Orientation
	$('.partitionAxis').on('change', function(){
		handlePartitionAddRemove();
	});
	
	// Custom Partition Add
	$('#customPartitionAdd').on('click', function(){
		var variables = getVariables();
		
		setDefaultData(variables['selectedObj']);
		addPartition();
		handleOrientationInput();
		
		// If the added partition affects obj RUSize,
		// reset the RUSize minimum.
		var isParent = $(variables['selectedObj']).hasClass('flex-container-parent');
		var isParentChild = $(variables['selectedObj']).parent().hasClass('flex-container-parent');
		var isHorizontal = $(variables['selectedObj']).css('flex-direction') == 'column' ? true : false;
		
		if((isParent || isParentChild) && isHorizontal){
			resetRUSize();
		}
	});
	
	// Custom Partition Remove
	$('[id^=customPartitionRemove]').on('click', function(){
		var variables = getVariables();
		var partitionDepth = $(variables['selectedObj']).parentsUntil('.flex-container-parent').length;
		var isHorizontal = $(variables['selectedObj']).css('flex-direction') == 'row' ? true : false;
		
		// Check to see if the object being deleted is the only one.
		if($(variables['selectedParent']).children().length > 1){
			// Select the preceding object
			var selectedObjIndex = $(variables['selectedObj']).index();
			$(variables['selectedParent']).children().eq(selectedObjIndex-1).addClass('rackObjSelected');
		} else {
			$(variables['selectedParent']).addClass('rackObjSelected');
			$('.dependantField.connectable').prop('disabled', false);
		}
		$(variables['selectedObj']).remove();
		
		if(partitionDepth < 2 && isHorizontal){
			resetRUSize();
		}
		
		setPartitionSizeInput();
		setInputValues();
		togglePartitionTypeDependencies();
		handleOrientationInput();
	});
	
	// Custom Partition Size
	$('#inputCustomPartitionSize').on('change', function(){
		var variables = getVariables();
		resizePartition($(this).val());
		var isParentChild = $(variables['selectedObj']).parent().hasClass('flex-container-parent');
		var isParentChildNested = $(variables['selectedObj']).parent().parent().hasClass('flex-container-parent');
		var isHorizontal = $(variables['selectedObj']).css('flex-direction') == 'row' ? true : false;
		if((isParentChild || isParentChildNested) && isHorizontal){
			resetRUSize();
		}
	});
	
	// Enclosure Layout
	$('[id^=inputEnclosureLayout]').on('change', function(){
		var variables = getVariables();
		var x = $('#inputEnclosureLayoutX').val();
		var y = $('#inputEnclosureLayoutY').val();
		var table = buildTable(x, y, 'enclosureTable', true);
		$(variables['selectedObj']).html(table);
		
		$(variables['selectedObj']).data('valueX', x);
		$(variables['selectedObj']).data('valueY', y);
		
		//Apply the selected category to the active object
		$(".activeObj").addClass($('#inputCategory').find('option:selected').data('value'));
	});
	
	// Enclosure Strict Insert Fitment
	$('[id^=inputEnclosureTolerance]').on('change', function(){
		var enclosureTolerance = $(this).val();
		var variables = getVariables();
		$(variables['selectedObj']).data('encTolerance', enclosureTolerance);
	});
	
	// Port Layout
	$('[id^=inputPortLayout]').on('change', function(){
		var variables = getVariables();
		var x = $('#inputPortLayoutX').val();
		var y = $('#inputPortLayoutY').val();
		$(variables['selectedObj']).data('valueX', x);
		$(variables['selectedObj']).data('valueY', y);
		buildPortTable();
		updatePortNameDisplay();
	});
	
	// Set port orientation
	$('input.objectPortOrientation').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('portOrientation', $(this).val());
	});
	
	// Set port type
	$('#inputPortType').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('portType', $(this).val());
		buildPortTable();
	});
	
	// Set media type
	$('#inputMediaType').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('mediaType', $(this).val());
	});
	
	// Prevent modal if disabled
	$('#modalTemplateDeleteConfirm').on('show.bs.modal', function (e){
		var button = e.relatedTarget;
		if($(button).hasClass('disabled')) {
			return false;
		}
	});
	
	// Set template name in delete confirm modal
	$('#modalTemplateDeleteConfirm').on('shown.bs.modal', function (e){
		var templateName = $(document).data('selectedTemplateName');
		var templateCategoryName = $(document).data('selectedTemplateCategoryName');
		$('#deleteTemplateName').html(templateName + ' (' + templateCategoryName + ')');
	});
	
	// Prevent modal if disabled
	$('#modalTemplateWhereUsed').on('show.bs.modal', function (e){
		var button = e.relatedTarget;
		if($(button).hasClass('disabled')) {
			return false;
		}
	});
	
	// Find where template is used
	$('#modalTemplateWhereUsed').on('shown.bs.modal', function (e){
		var templateID = $(document).data('selectedTemplateID');
		var templateName = $(document).data('selectedTemplateName');
		var templateCategoryName = $(document).data('selectedTemplateCategoryName');
		$('#whereUsedTemplateName').html(templateName + ' (' + templateCategoryName + ')');
		
		//Collect template data
		var data = {
			templateID: templateID
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/retrieve_where_used.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				var response = responseJSON.success;
				$('#whereUsedResults').html(response);
			}
		});
	});
	
	// Select template requested by app
	if($('#templateID').length) {
		var templateID = $('#templateID').val();
		// Dropdown template category
		$('#availableContainer0').find('[data-template-id='+templateID+']').closest('.categoryContainerEntire').children('.categoryTitle').click();
		// Select template
		$('#availableContainer0').find('[data-template-id='+templateID+']').children(':first').click();
		$('#templateID').remove();
	}
	
});
