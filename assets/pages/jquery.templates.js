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
		if($(variables['selectedObj']).css('flex-direction') == 'column') {
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
	var flexDirection = $(variables['selectedObj']).css('flex-direction');
	var partitionOrientation = $('input.partitionAxis:checked').val();
	var parentUnitAttr = partitionOrientation == 'h' ? 'data-vunits' : 'data-hunits';
	var parentFlexUnits = parseInt($(variables['selectedObj']).parent().attr(parentUnitAttr), 10);
	var spaceTaken = 0;
	var spaceAvailable = parentFlexUnits - spaceTaken;
	
	if(isParent) {
		$(variables['selectedObj']).children('.flex-container').each(function(){
			spaceTaken += parseInt($(this).attr(parentUnitAttr), 10)
		});
	}
	
	// Handle partition add button
	if(spaceAvailable == 0 || (spaceAvailable == 1 && parentFlexUnits == 1)){
		$('#customPartitionAdd').addClass('disabled').prop('disabled', true);
	} else {
		$('#customPartitionAdd').removeClass('disabled').prop('disabled', false);
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

function loadProperties(){
	var variables = getVariables();
	
	// If selected object is not the parent container...
	if(!$(variables['selectedObj']).hasClass('flex-container-parent')){
		var flexDirection = $(variables['selectedObj']).css('flex-direction');
		var partitionStep = flexDirection == 'column' ? 0.1 : 0.5;
		var selectedUnitAttr = flexDirection == 'column' ? 'data-hUnits' : 'data-vUnits';
		var parentUnitAttr = flexDirection == 'column' ? 'data-vUnits' : 'data-hUnits';
		var flexUnits = parseInt($(variables['selectedObj']).attr(selectedUnitAttr), 10);
		var parentFlexUnits = parseInt($(variables['selectedParent']).attr(selectedUnitAttr), 10);
		var siblingUnits = 0;
		$(variables['selectedObj']).siblings().each(function(){
			siblingUnits += parseInt($(this).attr(selectedUnitAttr), 10);
		});
		var takenUnits = siblingUnits + flexUnits;
		var availableUnits = parentFlexUnits - takenUnits;
		
		// Calculate the space taken by dependent partitions
		var unitsTaken = 0;
		$(variables['selectedObj']).children().each(function(){
			workingUnitsTaken = 0;
			$(this).children().each(function(){
				workingUnitsTaken += parseInt($(this).attr(parentUnitAttr), 10);
			});
			unitsTaken = workingUnitsTaken > unitsTaken ? workingUnitsTaken : unitsTaken;
		});
		var partitionMin = partitionStep * unitsTaken == 0 ? partitionStep :  partitionStep * unitsTaken;
		var partitionSize = partitionStep * flexUnits;
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

function setInputValues(defaultValues){
	if(defaultValues) {
		var templateType = $('input[name="objectTypeRadio"]:checked').val();
		
		var encLayoutX = 0;
		var encLayoutY = 0;
		var encInsertFitment = 'Strict';
		var portLayoutX = 1;
		var portLayoutY = 1;
		var partitionType = 'Generic';
		var portOrientation = 1;
		var portType = 1;
		var mediaType = 1;
	} else {
		var variables = getVariables();
		
		var encLayoutX = $(variables['selectedObj']).data('encLayoutX');
		var encLayoutY = $(variables['selectedObj']).data('encLayoutY');
		var encInsertFitment = $(variables['selectedObj']).data('encInsertFitment');
		var portLayoutX = $(variables['selectedObj']).data('portLayoutX');
		var portLayoutY = $(variables['selectedObj']).data('portLayoutY');
		var partitionType = $(variables['selectedObj']).data('partitionType');
		var portOrientation = $(variables['selectedObj']).data('portOrientation');
		var portType = $(variables['selectedObj']).data('portType');
		var mediaType = $(variables['selectedObj']).data('mediaType');
	}
	console.log(encLayoutX);
	console.log(encLayoutY);
	console.log(encInsertFitment);
	console.log(portLayoutX);
	console.log(portLayoutY);
	console.log(partitionType);
	console.log(portOrientation);
	console.log(portType);
	console.log(mediaType);
	$('#inputEnclosureLayoutX').val(encLayoutX);
	$('#inputEnclosureLayoutY').val(encLayoutY);
	$('[name="enclosureInsertFitment"][value='+encInsertFitment+']').prop('checked', true);
	$('#inputPortLayoutX').val(portLayoutX);
	$('#inputPortLayoutY').val(portLayoutY);
	$('[name="partitionType"][value='+partitionType+']').prop('checked', true);
	$('input.objectPortOrientation[value="'+portOrientation+'"]').prop('checked', true);
	$('#inputPortType').children('[value="'+portType+'"]').prop('selected', true);
	$('#inputMediaType').children('[value="'+mediaType+'"]').prop('selected', true);
}

function resizePartition(inputValue){
	var variables = getVariables();
	var flexDirection = $(variables['selectedObj']).css('flex-direction');
	var selectedUnitAttr = flexDirection == 'column' ? 'data-hUnits' : 'data-vUnits';
	var parentUnitAttr = flexDirection == 'column' ? 'data-vUnits' : 'data-hUnits';
	var parentFlexUnits = parseInt($(variables['selectedParent']).parent().attr(selectedUnitAttr), 10);
	var partitionFlexUnits = flexDirection == 'row' ? inputValue*2 : inputValue*10;
	var partitionFlexSize = partitionFlexUnits/parentFlexUnits;
	$(variables['selectedObj']).css('flex-grow', partitionFlexSize);
	$(variables['selectedObj']).attr(selectedUnitAttr, partitionFlexUnits);
	var dependentFlexSize = 1/partitionFlexUnits;
	$(variables['selectedObj']).children().each(function(){
		$(this).attr(selectedUnitAttr, partitionFlexUnits);
		$(this).children().each(function(){
			var dependentFlexUnits = $(this).attr(selectedUnitAttr);
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
	if($(variables['obj']).children('.flex-container-parent').css('flex-direction') == 'column') {
		$(variables['obj']).children('.flex-container-parent').children().each(function(){
			spaceTaken += parseInt($(this).attr('data-vUnits'), 10);
		});
	} else {
		$(variables['obj']).children('.flex-container-parent').children().each(function(){
			workingSpaceTaken = 0;
			$(this).children().each(function(){
				workingSpaceTaken += parseInt($(this).attr('data-vUnits'), 10);
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
	
	$(obj).data('portLayoutX', 1);
	$(obj).data('portLayoutY', 1);
	$(obj).data('encLayoutX', 1);
	$(obj).data('encLayoutY', 1);
	$(obj).data('encInsertFitment', 'Strict');
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
	var unitAttr = axis == 'h' ? 'data-vUnits' : 'data-hUnits';
	var flexUnits = parseInt($(variables['selectedParent']).attr(unitAttr), 10);
	var flex = 1/flexUnits;
	var html = '';
	
	// Set flex-direction according to the orientation input
	$(variables['selectedObj']).css('flex-direction', parentFlexDirection).data('direction', parentFlexDirection);
	var vUnits = axis == 'h' ? 1 : parseInt($(variables['selectedObj']).attr('data-vUnits'), 10);
	var hUnits = axis == 'h' ? parseInt($(variables['selectedObj']).attr('data-hUnits'), 10) : 1;
	html += '<div class="flex-container border-black transparency-20" style="flex:'+flex+'; flex-direction:'+flexDirection+';" data-hUnits="'+hUnits+'" data-vUnits="'+vUnits+'"></div>';
	$(variables['selectedObj']).append(html);
	var newObj = $(variables['selectedObj']).children().last();
	$(newObj).on('click', function(event){
		event.stopPropagation();
		
		// Highlight this object
		$(variables['obj']).find('.rackObjSelected').removeClass('rackObjSelected');
		$(this).addClass('rackObjSelected');
		
		// Enable the 'size' input
		$('#inputCustomPartitionSize').prop('disabled', false);
		
		loadProperties();
		setInputValues(false);
		togglePartitionTypeDependencies();
		handleOrientationInput();
		updatePortNameDisplay();
	});
	setDefaultData(newObj);
	$(newObj).data('direction', flexDirection);
	
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
	var portType = $('#inputPortType').find('option:selected').attr('data-value');
	var x = $('#inputPortLayoutX').val();
	var y = $('#inputPortLayoutY').val();
	var table = buildTable(x, y, '');
	$(variables['selectedObj']).html(table);
	$(variables['selectedObj']).find('.tableCol').each(function(){
		$(this).html('<div class="port '+portType+'"></div>');
	});
	$(variables['selectedObj']).data('portLayoutX', x);
	$(variables['selectedObj']).data('portLayoutY', y);
}

function makeRackObjectsClickable(){
	$('.categoryTitle').on('click', function(){
		var categoryName = $(this).attr('data-categoryName');
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
			var partitionDepth =  parseInt($(this).attr('data-depth'), 10);
		}
		$('#selectedPartitionDepth').val(partitionDepth);
		
		//Store objectID
		var templateID = $(object).attr('data-templateid');
		$('#selectedObjectID').val(templateID);
		
		//Store objectFace
		var templateFace = $(object).attr('data-objectFace');
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
					$('#elementTemplateImage').hide();
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

function expandRackUnit(objectRUSize){
	for (var x=0; x<2; x++) {
		$('.RackUnit'+x).show();
		$('.RackUnit'+x).eq(0).attr('rowspan', objectRUSize);
		for (y=1; y<objectRUSize; y++) {
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
					var partitionFlexUnits = parseInt($(this).attr('data-vUnits'), 10);
					var partitionFlexSize = partitionFlexUnits/parentFlexUnits;
					$(this).css('flex', partitionFlexSize + ' 1 0%');
				});
			});
		} else {
			$(flexContainerParent).children().each(function(){
				var partitionFlexUnits = parseInt($(this).attr('data-vUnits'), 10);
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
	$('#inputCurrentSide').val(sideValue);
}

function switchSidesDetail(sideValue){
	if(sideValue==1){
		$('#availableContainer0').hide();
		$('#availableContainer1').show();
	} else {
		$('#availableContainer0').show();
		$('#availableContainer1').hide();
	}
}

function getVariables(){
	var variables = [];
	variables['currentSide'] = $('#inputCurrentSide').val();
	variables['obj'] = $('#previewObj'+variables['currentSide']);
	variables['selectedObj'] = $(variables['obj']).find('.rackObjSelected');
	variables['selectedParent'] = $(variables['selectedObj']).parent();
	variables['RUSize'] = $('#inputRU').val();
	variables['halfRU'] = 1/(variables['RUSize']*2);
	return variables;
}

function reapplyCategory(){
	var variables = getVariables();
	var selectedCategory = $("#inputCategory").find('option:selected').attr('data-value');
	
	$(variables['selectedObj']).removeClass (function (index, css) {
		return (css.match (/(^|\s)category\S+/g) || []).join(' ');
	});
	
	$(variables['selectedObj']).addClass(selectedCategory);
}

function buildObj(objID, hUnits, vUnits, direction){
	var obj = $('#previewObj'+objID);
	$(obj).html('<div class="flex-container-parent" style="flex-direction:column" data-hUnits="'+hUnits+'" data-vUnits="'+vUnits+'"></div>');
	var newObj = $(obj).children('.flex-container-parent');
	$(newObj).addClass('rackObjSelected');
	setDefaultData(newObj);
	$(newObj).data('direction', direction);
	$(newObj).on('click', function(){
		// Highlight the object
		$(this).find('.rackObjSelected').removeClass('rackObjSelected');
		$(this).addClass('rackObjSelected');
		
		loadProperties();
		setInputValues(false);
		togglePartitionTypeDependencies();
		handleOrientationInput();
		updatePortNameDisplay();
	});
	loadProperties();
	setInputValues(true);
	togglePartitionTypeDependencies();
	handleOrientationInput();
}

function setCategory(){
	variables = getVariables();
	var category = $('#inputCategory').find('option:selected').attr('data-value');
	$(variables['obj']).removeClass (function (index, css) {
		return (css.match (/(^|\s)category\S+/g) || []).join(' ');
	});
	$(variables['obj']).addClass(category);
}

function buildObjectArray(elem){
	var parent = [];

	$(elem).children('div').each(function(){
		var tempData = $(this).data();
		tempData['flex'] = $(this).css('flex-grow');
		if ($(this).children('div.flex-container').length) {
			tempData['children'] = buildObjectArray(this);
		}
		if($(this).data('partitionType') == 'Connectable' && $(this).data('portLayoutX') == 0 && $(this).data('portLayoutY') == 0) {
			tempData['partitionType'] = 'Generic';
		}
		parent.push(tempData);
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
	$('#inline-portOrientation').editable('disable').html('-');
}

function togglePartitionTypeDependencies(){
	var partitionType = $('input.partitionType:checked').val();
	$('.dependantField.partitionType').hide();
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
		$(templates).hide().attr('data-visible', false);
		
		$.each(templates, function(indexTemplate, valueTemplate){
			var templateObj = $(this);
			var templateName = $(valueTemplate).attr('data-templatename').toLowerCase();
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
				$(templateObj).show().attr('data-visible', true);
			}
		});
		
		$.each(categoryContainers, function(){
			if($(this).find('.object-wrapper[data-visible="true"]').size()) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	} else {
		$(templates).show().attr('data-visible', true);;
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
		$(document).data('focusedPortNameField', $(this));
		handlePortNameOptions();
		var focusedPortNameField = $(this);
		$('.portNameFields').removeClass('input-focused');
		$(focusedPortNameField).addClass('input-focused');
	});
}

function handlePortNameOptions(){
	var focusedPortNameField = $(document).data('focusedPortNameField');
	var valuePortNameType = $(focusedPortNameField).attr('data-type');
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
			var optionString = convertNumToDeg(x);
			$('#selectPortNameFieldOrder').append('<option value="'+x+'">'+optionString+'</option>');
		}
		
		var valuePortNameCount = $(focusedPortNameField).attr('data-count');
		var valuePortNameOrder = $(focusedPortNameField).attr('data-order');
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

function convertNumToDeg(num){
	if(num==1) {
		string = '1st';
	} else if(num==2) {
		string = '2nd';
	} else if(num==3) {
		string = '3rd';
	} else {
		string = num+'th';
	}
	
	return string;
}

function reorderIncrementals(newOrder){
	var focusedPortNameField = $(document).data('focusedPortNameField');
	var originalOrder = parseInt($(focusedPortNameField).attr('data-order'));
	var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
	$(incrementalElements).each(function(){
		
		var currentOrder = parseInt($(this).attr('data-order'));
		if(newOrder > originalOrder) {
			var adjustment = -1;
		} else if(newOrder < originalOrder) {
			var adjustment = 1;
		}
		
		if(currentOrder < originalOrder && currentOrder >= newOrder) {
			var x = currentOrder + adjustment;
		} else if(currentOrder == originalOrder || currentOrder == newOrder){
			var x = newOrder;
		} else {
			var x = currentOrder;
		}
		
		var y = convertNumToDeg(x);
		$(this).attr('data-order', x);
		$(this).prev('em').html(y);
	});
}

function resetIncrementals(){
	var focusedPortNameField = $(document).data('focusedPortNameField');
	var incrementalElements = $('.portNameFields[data-type="incremental"], .portNameFields[data-type="series"]');
	
	// Set order to last if this is an unorderable field changing to an orderable field?
	var selectedOrder = parseInt($(focusedPortNameField).attr('data-order'));
	if(selectedOrder == 0) {
		$(focusedPortNameField).attr('data-order', incrementalElements.length);
	}
	
	incrementalElements = incrementalElements.sort(function(a, b) {
		return parseInt($(a).attr('data-order')) - parseInt($(b).attr('data-order'));
	});
	
	$(incrementalElements).each(function(index){
		var newOrder = index+1;
		var newOrderString = convertNumToDeg(newOrder);
		$(this).attr('data-order', newOrder);
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
		var elementType = $(this).attr('data-type');
		var elementValue = $(this).val();
		var elementCount = parseInt($(this).attr('data-count'));
		var elementOrder = parseInt($(this).attr('data-order'));
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
		var portLayoutX = $(variables['selectedObj']).data('portLayoutX');
		var portLayoutY = $(variables['selectedObj']).data('portLayoutY');
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
		var categoryContainers = $(containerElement).find('.categoryCatalogContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	}).on('itemRemoved', function(event){
		var containerElement = $('#templateCatalogAvailableContainer');
		var inputElement = $('#templateCatalogFilter');
		var categoryContainers = $(containerElement).find('.categoryCatalogContainerEntire');
		filterTemplates(containerElement, inputElement, categoryContainers);
	});
	
	// Make catalog titles expandable
	$('.templateCatalogCategoryTitle').on('click', function(){
		var categoryName = $(this).attr('data-categoryName');
		if($('.templateCatalogCategory'+categoryName+'Container').is(':visible')) {
			$('.templateCatalogCategory'+categoryName+'Container').hide(400);
			$(this).children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
		} else {
			$('.templateCatalogCategoryContainer').hide(400);
			$('.templateCatalogCategoryTitle').children('i').removeClass('fa-caret-down').addClass('fa-caret-right');
			$('.templateCatalogCategory'+categoryName+'Container').show(400);
			$(this).children('i').removeClass('fa-caret-right').addClass('fa-caret-down');
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
			var partitionDepth =  parseInt($(this).attr('data-depth'), 10);
		}
		
		//Store objectID
		var objID = $(object).attr('data-templateid');
		$(document).data('templateCatalogID', objID);
		
		//Store objectFace
		var objFace = $(object).attr('data-objectFace');
		
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

$( document ).ready(function() {
	toggleObjectTypeDependencies();
	
	$('#containerTemplateCatalog').load('https://patchcablemgr.com/public/template-catalog-013.php', function(){
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
	setObjectSize();
	for(x=0; x<2; x++) {
		buildObj(x, 10, 2, 'column');
	}
	setCategory();
	
	// Clone a template to the workspace
	$('#objClone').click(function(){
		var templateID = $('#selectedObjectID').val();
		var templateObj = $('#availableContainer0').find('[data-templateid="19"]');
		var variables = getVariables();
		$(variables['obj']).html($(templateObj).clone());
		$('.flex-container, .flex-container-parent').on('click', function(event){
			event.stopPropagation();
			
			// Highlight this object
			$(variables['obj']).find('.rackObjSelected').removeClass('rackObjSelected');
			$(this).addClass('rackObjSelected');
			
			// Enable the 'size' input
			$('#inputCustomPartitionSize').prop('disabled', false);
			
			loadProperties();
			setInputValues(false);
			togglePartitionTypeDependencies();
			handleOrientationInput();
			updatePortNameDisplay();
		});
	});
	
	// Delete a temlate
	$('#objDelete').click(function(){
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

	//colorpicker start
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
	
	//Select category
	$('#categoryList').children('button').on('click', function(){
		if($(this).hasClass('rackObjSelected')) {
			resetCategoryForm(defaultCategoryColor);
		} else {
			$('#categoryList').children('button').removeClass('rackObjSelected');
			$(this).addClass('rackObjSelected');
			$('#inputCategoryID').val($(this).attr('data-id'));
			$('#inputCategoryCurrentID').val($(this).attr('data-id'));
			$('#inputCategoryName').val($(this).attr('data-name'));
			$('#color-picker').spectrum('set', $(this).attr('data-color'));
			if($(this).attr('data-default') == 1) {
				$('#inputCategoryDefault').prop({'checked':true,'disabled':true});
			} else {
				$('#inputCategoryDefault').prop({'checked':false,'disabled':false});
			}
		}
	});
	
	//Delete selected categories
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
	
	//Category Manager form save
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
					$(currentDefault).attr('data-default', 0);
					$(currentDefault).html($(currentDefault).attr('data-name'));
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
							$('#inputCategoryID').val($(this).attr('data-id'));
							$('#inputCategoryCurrentID').val($(this).attr('data-id'));
							$('#inputCategoryName').val($(this).attr('data-name'));
							$('#color-picker').spectrum('set', $(this).attr('data-color'));
							if($(this).attr('data-default') == 1) {
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
					$('#categoryOption'+responseJSON.success.id).attr('data-value', 'category'+responseJSON.success.name);
					$('#categoryOption'+responseJSON.success.id).val(responseJSON.success.id);
					$('#categoryOption'+responseJSON.success.id).html(responseJSON.success.name);
					
					// Update category button
					$('#categorList'+responseJSON.success.id).removeClass(function(index, className){
						return (className.match (/(^|\s)category\S+/g) || []).join(' ');
					});
					$('#categoryList'+responseJSON.success.id).addClass('category'+responseJSON.success.name);
					$('#categoryList'+responseJSON.success.id).attr('data-name', responseJSON.success.name);
					$('#categoryList'+responseJSON.success.id).attr('data-color', responseJSON.success.color);
					var defaultIdentifier = '';
					if(responseJSON.success.defaultOption == 1) {
						defaultIdentifier = '*';
					}
					$('#categoryList'+responseJSON.success.id).attr('data-default', responseJSON.success.defaultOption);
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
	
	//Form submit
	$('#objectEditor-Submit').on('click', function(){
		// Gather user input
		var data = {};
		data['action'] = "add";
		data['name'] = $('#inputName').val();
		data['category'] = $('#inputCategory').val();
		data['type'] = $('input[name="objectTypeRadio"]:checked').val();
		data['function'] = $('input[name="objectFunction"]:checked').val();
		data['objects'] = [];
		if(data['type'] == 'Insert'){
			var encLayoutX = parseInt($('#previewObj3').attr('data-encLayoutX'), 10);
			var encLayoutY = parseInt($('#previewObj3').attr('data-encLayoutY'), 10);
			var objHUnits = parseInt($('#previewObj3').attr('data-hUnits'), 10);
			var objVUnits = parseInt($('#previewObj3').attr('data-vUnits'), 10);
			var insertRUSize = Math.ceil(objVUnits/2);
			data['RUSize'] = insertRUSize;
			data['encLayoutX'] = encLayoutX;
			data['encLayoutY'] = encLayoutY;
			data['hUnits'] = objHUnits;
			data['vUnits'] = objVUnits;
			data['objects'].push(buildObjectArray('#previewObj3'));
		} else {
			data['RUSize'] = $('#inputRU').val();
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
	
	//Category
	$('#inputCategory').on('change', function(){
		setCategory();
	});
	
	//Side Count Selector
	$('.sideCount').on('change', function(){
		if ($(this).val() == 1) {
			//4-Post
			$('#inputSideCount').val(1);
			$('.sideSelector').prop('disabled', false);
			$('#objectTypeInsert0').prop('disabled', true);
			$('#objectTypeInsert1').prop('disabled', true);
		} else {
			//2-Post
			$('#inputSideCount').val(0);
			$('.sideSelector').prop('disabled', true);
			$('#objectTypeInsert0').prop('disabled', false);
			switchSides(0);
			loadProperties();
			setInputValues(false);
			togglePartitionTypeDependencies();
			handleOrientationInput();
			$('#sideSelectorFront').prop('checked', true);
		}
	});
	
	//Side Switcher
	$('.sideSelector').on('change', function(){
		var selectedSide = $(this).val();
		switchSides(selectedSide);
		setCategory();
		setObjectSize();
		loadProperties();
		setInputValues(false);
		togglePartitionTypeDependencies();
		handleOrientationInput();
	});
	
	//Detail Side Switcher
	$('.sideSelectorDetail').on('change', function(){
		switchSidesDetail($(this).val());
	});
	
	//RU Size
	$('#inputRU').on('change', function(){
		var variables = getVariables();
		
		for(x=0; x<2; x++) {
			var flexUnits = variables['RUSize'] * 2;
			$('#previewObj'+x).attr('data-vUnits', flexUnits);
			$('#previewObj'+x).children('.flex-container-parent').attr('data-vUnits', flexUnits);
		}
		
		expandRackUnit(variables['RUSize']);
		setObjectSize();
		loadProperties();
		setInputValues(false);
		handleOrientationInput();
	});
	
	// Object Type
	$('input.objectType').on('change', function(){
		var category = $('#inputCategory').find('option:selected').attr('data-value');
		toggleObjectTypeDependencies();
		
		switch($(this).val()) {
			case 'Insert':
				// Inserts cannot be enclosures
				$('#inputPartitionTypeEnclosure').prop('disabled', true);
				
				// Inserts cannot be 4-post mountable
				$('.sideSelector').prop('disabled', true);
				$('#inputSideCount2Post').prop('checked', true);
				$('#sideSelectorFront').prop('checked', true);
				switchSides(0);
				
				// Variables must be retrieved after switchSides() because it updates the reference to the currently selected object
				var variables = getVariables();
				loadProperties();
				handleOrientationInput();
				
				// Disable relevant input fields
				$('#objectPartitionAddRemove, #objectPartitionSize, #objectMediaType, #objectPortType, #objectPortOrientation, #objectPortLayout').prop('disabled', true);
				
				// Clear the preview object of any user formatting
				$(variables['obj']).html('Select enclosure from "Available Objects" section.');
				$(variables['obj']).removeClass(function(index, css) {
					return (css.match(/(^|\s)category\S+/g) || []).join(' ');
				});
				$('.enclosure').on('click', function(){
					
					var enclosureParent = $(this).parent();
					var enclosureObject = $(this).closest('.flex-container-parent');
					var enclosureObjectParent = $(enclosureObject).parent();
					var objectFlexDirection = $(enclosureObjectParent).css('flex-direction');
					var objectFunction = $(enclosureObjectParent).attr('data-objectFunction');
					var hUnits = parseInt($(enclosureParent).attr('data-hunits'), 10);
					var vUnits = parseInt($(enclosureParent).attr('data-vunits'), 10);
					var encLayoutX = parseInt($(this).attr('data-encLayoutX'), 10);
					var encLayoutY = parseInt($(this).attr('data-encLayoutY'), 10);
					$('[name="objectFunction"][value='+objectFunction+']').prop('checked', true);
					setInputValues(true);
					var RUSize = parseInt($(enclosureObjectParent).attr('data-RUSize'), 10);
					$('#inputRU').val(RUSize);
					
					// Enable input fields
					$('#objectType, #objectMediaType, #objectPortType, #objectPortOrientation, #objectPortLayout').prop('disabled', false);
					
					// Adjust the RU table cell size
					expandRackUnit(RUSize);
					
					// Mark the selected so it can be referenced in preview object
					$(this).addClass('selectedEnclosure');
					
					// Copy the selected object to preview object
					$(variables['obj']).html($(enclosureObject).clone().removeClass('rackObjSelected'));
				
					// Cleanup marking
					$(this).removeClass('selectedEnclosure');
					
					$(variables['obj'])
					.find('.selectedEnclosure')
					.find('.tableRow:first')
					.find('.tableCol:first')
					.removeClass('enclosureTable')
					//.data(data)
					.html('<div id="previewObj3" class="objBaseline" data-hUnits="'+hUnits+'" data-vUnits="'+vUnits+'" data-encLayoutX="'+encLayoutX+'" data-encLayoutY="'+encLayoutY+'"></div>');
					
					// Original preview object needs to grow first
					setObjectSize();
					
					$('#inputCurrentSide').val(3);
					setObjectSize();
					buildObj(3, hUnits, vUnits, objectFlexDirection);
					setCategory();
					togglePartitionTypeDependencies();
					//buildPortTable();
					//updatePortNameDisplay();
				});
				break;
				
			case 'Standard':
				// Variables must be retrieved here... see above comment under 'Insert' case
				var variables = getVariables();
				$('#inputPartitionTypeEnclosure').prop('disabled', false);
				for(x=0; x<2; x++) {
					buildObj(x, 10, 2, 'column');
				}
				$('#inputCurrentSide').val(0);
				expandRackUnit(variables['RUSize']);
				setObjectSize();
				setCategory();
				
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
				loadProperties();
				setInputValues(false);
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
		//setDefaultData(variables['selectedObj']);
		$(variables['selectedObj']).data('partitionType', partitionType);
		$(variables['selectedObj']).empty();
		loadProperties();
		setInputValues(false);
		togglePartitionTypeDependencies();
		handleOrientationInput();
		if(partitionType == 'Connectable') {
			buildPortTable();
			updatePortNameDisplay();
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
		$(document).data('portNameFormatAction', $(invoker).attr('data-portNameAction'));
		
		setPortNameInput();
		updatePortNameDisplay();
		setPortNameFieldFocus();
		handlePortNameOptions();
		$('.portNameFields').on('keyup', function(){
			updateportNameFormat();
			updatePortNameDisplay();
		});
		
		if(!$('.portNameFields:focus').length) {
			$('.portNameFields').first().focus();
		}
	});
	
	// Port Naming Add Field
	$('#buttonAddPortNameField').on('click', function(){
		var fieldFocused = $(document).data('focusedPortNameField');
		var nameFieldHTML = $('<div class="col-sm-2 no-padding"><em>&nbsp</em><input type="text" class="portNameFields form-control" value="Port" data-type="static" data-count="0" data-order="0"></div>');
		nameFieldHTML.insertAfter(fieldFocused.parent());
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
		var fieldFocused = $(document).data('focusedPortNameField');
		var portNameFields = $('.portNameFields');
		var fieldFocusedIndex = $(portNameFields).index(fieldFocused);
		
		$(fieldFocused).parent().remove();
		resetIncrementals();
		$(portNameFields).eq(fieldFocusedIndex-1).focus();
		handlePortNameFieldAddRemoveButtons();
		updateportNameFormat();
		updatePortNameDisplay();
	});
	
	// Port Naming Type Selection
	$('#selectPortNameFieldType').on('change', function(){
		var focusedPortNameField = $(document).data('focusedPortNameField');
		var valuePortNameType = $(this).val();
		
		focusedPortNameField.attr('data-type', valuePortNameType);
		
		if(valuePortNameType == 'static') {
			$(focusedPortNameField).attr('data-order', 0);
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
		var focusedPortNameField = $(document).data('focusedPortNameField');
		var valueCount = $(this).val();
		focusedPortNameField.attr('data-count', valueCount);
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
			var elementType = $(this).attr('data-type');
			var elementValue = $(this).val();
			if(elementType == 'incremental') {
				var elementCount = parseInt($(this).attr('data-count'));
				if(elementCount == 0) {
					elementCount = portTotal;
				}
			} else if(elementType == 'series') {
				elementValue = elementValue.split(',');
				var elementCount = elementValue.length;
			}
			var elementOrder = parseInt($(this).attr('data-order'));
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
				var dataType = $(this).attr('data-type');
				if(dataType == 'static') {
					portString = portString + $(this).val();
				} else if(dataType == 'incremental' || dataType == 'series') {
					var incrementalOrder = parseInt($(this).attr('data-order'));
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
		
		// Remove any port or enclosure tables but preserve partitions
		$(variables['selectedObj']).children('table').remove();
		
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
		var isParentChild = $(variables['selectedObj']).parent().hasClass('flex-container-parent');
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
		
		if(isParentChild && isHorizontal){
			resetRUSize();
		}
		
		loadProperties();
		setInputValues(false);
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
		
		$(variables['selectedObj']).data('encLayoutX', x);
		$(variables['selectedObj']).data('encLayoutY', y);
		
		//Apply the selected category to the active object
		$(".activeObj").addClass($('#inputCategory').find('option:selected').attr('data-value'));
	});
	
	// Enclosure Strict Insert Fitment
	$('[id^=inputEnclosureInsertFitment]').on('change', function(){
		var enclosureInsertFitment = $(this).val();
		var variables = getVariables();
		$(variables['selectedObj']).data('encInsertFitment', enclosureInsertFitment);
	});
	
	//Port Layout
	$('[id^=inputPortLayout]').on('change', function(){
		buildPortTable();
		updatePortNameDisplay();
	});
	
	//Set port orientation
	$('input.objectPortOrientation').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('portOrientation', $(this).val());
	});
	
	//Set port type
	$('#inputPortType').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('portType', $(this).val());
	});
	
	//Set media type
	$('#inputMediaType').on('change', function(){
		var variables = getVariables();
		$(variables['selectedObj']).data('mediaType', $(this).val());
	});
	
	//Select template requested by app
	if($('#templateID').length) {
		var templateID = $('#templateID').val();
		// Dropdown template category
		$('#availableContainer0').find('[data-templateid='+templateID+']').closest('.categoryContainerEntire').children('.categoryTitle').click();
		// Select template
		$('#availableContainer0').find('[data-templateid='+templateID+']').click();
		$('#templateID').remove();
	}
	
});
