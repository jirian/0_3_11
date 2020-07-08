<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$action = $data['action'];
		if($action == 'add') {
			$name = $data['name'];
			$category_id = $data['category'];
			$type = $data['type'];
			
			$mediaTypeArray = array();
			$query = $qls->SQL->select('*', 'shared_mediaType');
			while($row = $qls->SQL->fetch_assoc($query)) {
				$mediaTypeArray[$row['value']] = $row;
			}
			
			$objectPortTypeArray = array();
			$query = $qls->SQL->select('*', 'shared_object_portType');
			while($row = $qls->SQL->fetch_assoc($query)) {
				$objectPortTypeArray[$row['value']] = $row;
			}
			
			$RUSize = $data['RUSize'];
			$function = $data['function'];
			$mountConfig = $data['mountConfig'];
			$encLayoutX = isset($data['encLayoutX']) ? $data['encLayoutX'] : null;
			$encLayoutY = isset($data['encLayoutY']) ? $data['encLayoutY'] : null;
			$hUnits = isset($data['hUnits']) ? $data['hUnits'] : null;
			$vUnits = isset($data['vUnits']) ? $data['vUnits'] : null;
			$partitionData = json_encode($data['objects']);
			$frontImage = ($data['frontImage'] != '') ? strtolower($data['frontImage']) : null;
			$rearImage = ($data['rearImage'] != '') ? strtolower($data['rearImage']) : null;
			
			// Insert template data into DB
			$qls->SQL->insert('app_object_templates', array(
					'templateName',
					'templateCategory_id',
					'templateType',
					'templateRUSize',
					'templateFunction',
					'templateMountConfig',
					'templateEncLayoutX',
					'templateEncLayoutY',
					'templateHUnits',
					'templateVUnits',
					'templatePartitionData',
					'frontImage',
					'rearImage'
				), array(
					$name,
					$category_id,
					$type,
					$RUSize,
					$function,
					$mountConfig,
					$encLayoutX,
					$encLayoutY,
					$hUnits,
					$vUnits,
					$partitionData,
					$frontImage,
					$rearImage
				)
			);
			
			$objectID = $qls->SQL->insert_id();
			
			// Gather compatibility data
			$compatibilityArray = array();
			foreach($data['objects'] as $face){
				array_push($compatibilityArray, getCompatibilityInfo($face));
			}
			
			// Insert compatibility data into DB
			foreach($compatibilityArray as $side=>$face){
				foreach($face as $element){
					$partitionType = $element['partitionType'];
					
					if($partitionType == 'Connectable') {
						$portType = $element['portType'];
						$mediaType = $function == 'Endpoint' ? 8 : $element['mediaType'];
						$mediaCategory = $function == 'Endpoint' ? 5 : $mediaTypeArray[$mediaType]['category_id'];
						$mediaCategoryType = $objectPortTypeArray[$portType]['category_type_id'];
						$portTotal = array_key_exists('portX', $element) ? $element['portX'] * $element['portY'] : 0;
						
						$columnArray = array(
							'template_id',
							'side',
							'depth',
							'portLayoutX',
							'portLayoutY',
							'portTotal',
							'templateType',
							'partitionType',
							'partitionFunction',
							'portOrientation',
							'portType',
							'mediaType',
							'mediaCategory',
							'mediaCategoryType',
							'direction',
							'hUnits',
							'vUnits',
							'flex',
							'portNameFormat'
						);
						
						$valueArray = array(
							$objectID,
							$side,
							$element['depth'],
							$element['portX'],
							$element['portY'],
							$portTotal,
							$type,
							$element['partitionType'],
							$function,
							$element['portOrientation'],
							$portType,
							$mediaType,
							$mediaCategory,
							$mediaCategoryType,
							$element['direction'],
							$element['hUnits'],
							$element['vUnits'],
							$element['flex'],
							$element['portNameFormat']
						);
						
					} else if($partitionType == 'Enclosure') {
						$columnArray = array(
							'template_id',
							'side',
							'depth',
							'encTolerance',
							'encLayoutX',
							'encLayoutY',
							'templateType',
							'partitionType',
							'partitionFunction',
							'direction',
							'hUnits',
							'vUnits',
							'flex'
						);
						
						$valueArray = array(
							$objectID,
							$side,
							$element['depth'],
							$element['encTolerance'],
							$element['encX'],
							$element['encY'],
							$type,
							$element['partitionType'],
							$function,
							$element['direction'],
							$element['hUnits'],
							$element['vUnits'],
							$element['flex']
						);
					}
					
					
					$qls->SQL->insert('app_object_compatibility', $columnArray, $valueArray);
				}
			}
			
			//return errors and results
			$validate->returnData['success'] = 'Object was added.';
			
			// Log action in history
			$actionString = 'Created template: <strong>'.$name.'</strong>';
			$qls->App->logAction(1, 1, $actionString);
				
		} else if($action == 'delete') {
			
			$templateDeleted = false;
			$id = $data['id'];
			$templateCombined = $data['templateCombined'];
			
			if($templateCombined == 'yes') {
				$qls->SQL->delete('app_combined_templates', array('id' => array('=', $id)));
				$templateDeleted = true;
			} else {
				$result = $qls->SQL->select('id', 'app_object', array('template_id' => array('=', $id)));
				if ($qls->SQL->num_rows($result) == 0) {
					$name = $qls->App->templateArray[$id]['templateName'];
					$qls->SQL->delete('app_object_templates', array('id' => array('=', $id)));
					$qls->SQL->delete('app_object_compatibility', array('template_id' => array('=', $id)));
					$templateDeleted = true;
					
				} else {
					array_push($validate->returnData['error'], 'Object is in use.');
				}
			}
			
			if($templateDeleted) {
				$validate->returnData['success'] = 'Object was deleted.';
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Deleted template: <strong>'.$name.'</strong>';
				$qls->App->logAction(1, 3, $actionString);
			}
			
		} else if($action == 'edit') {
			$value = $data['value'];
			$templateID = $data['templateID'];
			if($data['attribute'] == 'templateName'){
				$origName = $qls->App->templateArray[$templateID]['templateName'];
				$attribute = 'templateName';
				$return = $value;
				$qls->SQL->update('app_object_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Changed template name: from <strong>'.$origName.'</strong> to <strong>'.$value.'</strong>';
				$qls->App->logAction(1, 2, $actionString);
			} else if($data['attribute'] == 'combinedTemplateName'){
				$origName = $qls->App->combinedTemplateArray[$templateID]['templateName'];
				$attribute = 'templateName';
				$return = $value;
				$qls->SQL->update('app_combined_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Changed template name: from <strong>'.$origName.'</strong> to <strong>'.$value.'</strong>';
				$qls->App->logAction(1, 2, $actionString);
			} else if($data['attribute'] == 'inline-category') {
				$templateName = $qls->App->templateArray[$templateID]['templateName'];
				$origCategoryID = $qls->App->templateArray[$templateID]['templateCategory_id'];
				$origCategoryName = $qls->App->categoryArray[$origCategoryID]['name'];
				$newCategoryName = $qls->App->categoryArray[$value]['name'];
				$attribute = 'templateCategory_id';
				$return = $qls->SQL->fetch_row($qls->SQL->select('name', 'app_object_category', array('id' => array('=', $value))))[0];
				$qls->SQL->update('app_object_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Changed <strong>'.$templateName.'</strong> template category: from <strong>'.$origCategoryName.'</strong> to <strong>'.$newCategoryName.'</strong>';
				$qls->App->logAction(1, 2, $actionString);
			} else if($data['attribute'] == 'combinedTemplateCategory') {
				$templateName = $qls->App->combinedTemplateArray[$templateID]['templateName'];
				$origCategoryID = $qls->App->combinedTemplateArray[$templateID]['templateCategory_id'];
				$origCategoryName = $qls->App->categoryArray[$origCategoryID]['name'];
				$newCategoryName = $qls->App->categoryArray[$value]['name'];
				$attribute = 'templateCategory_id';
				$return = $qls->SQL->fetch_row($qls->SQL->select('name', 'app_object_category', array('id' => array('=', $value))))[0];
				$qls->SQL->update('app_combined_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Changed <strong>'.$templateName.'</strong> template category: from <strong>'.$origCategoryName.'</strong> to <strong>'.$newCategoryName.'</strong>';
				$qls->App->logAction(1, 2, $actionString);
			} else if($data['attribute'] == 'inline-portOrientation') {
				
				// Collect template IDs
				$templateFace = $data['templateFace'];
				$templateDepth = $data['templateDepth'];
				$portOrientationID = $data['value'];
				
				// Store and manipulate template partition data
				$template = $qls->App->templateArray[$templateID];
				$templatePartitionData = json_decode($template['templatePartitionData'], true);
				updatePartitionData($templatePartitionData[$templateFace], $templateDepth, $portOrientationID, 'portOrientation');
				$templatePartitionDataJSON = json_encode($templatePartitionData);
				
				// Update template partition data
				$qls->SQL->update('app_object_templates', array('templatePartitionData' => $templatePartitionDataJSON), array('id' => array('=', $templateID)));
				$qls->SQL->update('app_object_compatibility', array('portOrientation' => $portOrientationID), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $templateFace), 'AND', 'depth' => array('=', $templateDepth)));
				
			} else if($data['attribute'] == 'inline-enclosureTolerance') {
				
				// Collect template IDs
				$templateFace = $data['templateFace'];
				$templateDepth = $data['templateDepth'];
				$encTolerance = strtolower($data['value']);
				
				// Store and manipulate template partition data
				$template = $qls->App->templateArray[$templateID];
				$templatePartitionData = json_decode($template['templatePartitionData'], true);
				updatePartitionData($templatePartitionData[$templateFace], $templateDepth, ucfirst($encTolerance), 'encTolerance');
				$templatePartitionDataJSON = json_encode($templatePartitionData);
				
				// Update template partition data
				$qls->SQL->update('app_object_templates', array('templatePartitionData' => $templatePartitionDataJSON), array('id' => array('=', $templateID)));
				$qls->SQL->update('app_object_compatibility', array('encTolerance' => ucfirst($encTolerance)), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $templateFace), 'AND', 'depth' => array('=', $templateDepth)));
				
			} else if($data['attribute'] == 'portNameFormat') {
				
				// Collect data
				$templateFace = $data['templateFace'];
				$depth = $data['templateDepth'];
				$portNameFormat = $data['value'];
				$portNameFormatJSON = json_encode($portNameFormat);
				
				// Update compatibility port name format
				$qls->SQL->update('app_object_compatibility', array('portNameFormat' => $portNameFormatJSON), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $templateFace), 'AND', 'depth' => array('=', $depth)));
				
				// Update template partition data
				$template = $qls->App->templateArray[$templateID];
				$templatePartitionData = json_decode($template['templatePartitionData'], true);
				updatePartitionData($templatePartitionData[$templateFace], $depth, $portNameFormat, 'portNameFormat');
				$templatePartitionDataJSON = json_encode($templatePartitionData);
				$qls->SQL->update('app_object_templates', array('templatePartitionData' => $templatePartitionDataJSON), array('id' => array('=', $templateID)));
				
				// Generate new port name range
				$query = $qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $templateFace), 'AND', 'depth' => array('=', $depth)));
				$compatibility = $qls->SQL->fetch_assoc($query);
				$portLayoutX = $compatibility['portLayoutX'];
				$portLayoutY = $compatibility['portLayoutY'];
				$portTotal = $portLayoutX * $portLayoutY;
				$firstPortIndex = 0;
				$lastPortIndex = $portTotal - 1;
				$firstPortName = $qls->App->generatePortName($portNameFormat, $firstPortIndex, $portTotal);
				$lastPortName = $qls->App->generatePortName($portNameFormat, $lastPortIndex, $portTotal);
				$portRangeString = $firstPortName.'&#8209;'.$lastPortName;
				$return = $portRangeString;
			}
			
			$validate->returnData['success'] = $return;
		}

	}
	echo json_encode($validate->returnData);
	return;
}

function updatePartitionData(&$partitionData, $depth, $value, $attribute, &$counter=0){
	
	foreach($partitionData as &$element) {
		error_log('Debug: '.$depth.'-'.$counter.'-'.$element['partitionType']);
		if($counter == $depth) {
			$element[$attribute] = $value;
			return;
		} else if(isset($element['children'])){
			$counter++;
			updatePartitionData($element['children'], $depth, $value, $attribute, $counter);
		}
		$counter++;
	}
	return;
}

function getCompatibilityInfo($face, $dataArray=array(), &$depthCounter=0){
	foreach($face as $element){
		$partitionType = $element['partitionType'];
		if($partitionType == 'Generic') {
			if(isset($element['children'])){
				$depthCounter++;
				$dataArray = getCompatibilityInfo($element['children'], $dataArray, $depthCounter);
			}
			
		} else if($partitionType == 'Connectable') {
			$tempArray = array();
			$tempArray['depth'] = $depthCounter;
			$tempArray['portX'] = $element['valueX'];
			$tempArray['portY'] = $element['valueY'];
			$tempArray['partitionType'] = $element['partitionType'];
			$tempArray['portOrientation'] = $element['portOrientation'];
			$tempArray['portType'] = $element['portType'];
			$tempArray['mediaType'] = $element['mediaType'];
			$tempArray['direction'] = $element['direction'];
			$tempArray['hUnits'] = $element['hUnits'];
			$tempArray['vUnits'] = $element['vUnits'];
			$tempArray['flex'] = $element['flex'];
			$tempArray['portNameFormat'] = json_encode($element['portNameFormat']);
			array_push($dataArray, $tempArray);
		
		} else if($partitionType == 'Enclosure') {
				$tempArray = array();
				$tempArray['depth'] = $depthCounter;
				$tempArray['encX'] = $element['valueX'];
				$tempArray['encY'] = $element['valueY'];
				$tempArray['encTolerance'] = $element['encTolerance'];
				$tempArray['partitionType'] = $element['partitionType'];
				$tempArray['direction'] = $element['direction'];
				$tempArray['hUnits'] = $element['hUnits'];
				$tempArray['vUnits'] = $element['vUnits'];
				$tempArray['flex'] = $element['flex'];
				array_push($dataArray, $tempArray);
		}
		$depthCounter++;
	}
	return $dataArray;
}

function validate($data, &$validate, &$qls){
	
	// Validate 'add' values
	if ($data['action'] == 'add'){
		
		// Validate template images
		$errorMsg = 'Invalid template image.';
		$imageNameArray = array('frontImage', 'rearImage');
		foreach($imageNameArray as $imageName) {
			if(isset($data[$imageName])) {
				$image = strtolower($data[$imageName]);
				if($image != '') {
					$imageArray = explode('.', $image);
					if(count($imageArray) == 2) {
						$imgExtensionArray = array('jpg', 'jpeg', 'png', 'gif');
						$validate->validateMD5($imageArray[0], $errorMsg);
						$validate->validateInArray($imageArray[1], $imgExtensionArray, 'template image');
					} else {
						array_push($validate->returnData['error'], $errorMsg);
					}
				}
			} else {
				array_push($validate->returnData['error'], $errorMsg);
			}
		}
		
		//Validate template name
		if($validate->validateNameText($data['name'], 'template name')) {
			//Validate templateName duplicate
			$templateName = $data['name'];
			$table = 'app_object_templates';
			$where = array('templateName' => array('=', $templateName));
			$errorMsg = 'Duplicate template name.';
			$validate->validateDuplicate($table, $where, $errorMsg);
		}
		
		//Validate category
		if($validate->validateID($data['category'], 'categoryID')) {
			//Validate category existence
			$categoryID = $data['category'];
			$table = 'app_object_category';
			$where = array('id' => array('=', $categoryID));
			$errorMsg = 'Invalid categoryID.';
			$validate->validateExistenceInDB($table, $where, $errorMsg);
		}
		
		//Validate type <Standard|Insert>
		$validate->validateObjectType($data['type']);

		//Validate function <Endpoint|Passive>
		$validate->validateObjectFunction($data['function']);
		
		//Validate category RU
		$validate->validateRUSize($data['RUSize']);

		if ($data['type'] == 'Standard'){
			
			//Validate mounting configuration <0|1>
			$validate->validateMountConfig($data['mountConfig']);

		}
		
		if(is_array($data['objects']) and (count($data['objects']) >= 1 and count($data['objects']) <= 2)) {
			
			// Create array containing all portIDs for entire template
			$portCollection = array();
			foreach ($data['objects'] as $face) {
				if($validate->validateTemplateJSON($face[0])) {
					enumeratePortIDs($qls, $face, $portCollection);
				}
			}
			
			// Check that all port IDs are unique
			$portCollectionWorking = array();
			$portCollectionDuplicates = array();
			foreach($portCollection as $port) {
				if(in_array($port, $portCollectionWorking)) {
					array_push($portCollectionDuplicates, $port);
				}
				array_push($portCollectionWorking, $port);
			}
			if(count($portCollectionDuplicates)) {
				$duplicatePortStringLength = 3;
				$duplicatePortString = implode(', ', array_slice($portCollectionDuplicates, 0, $duplicatePortStringLength));
				$duplicatePortString .= (count($portCollectionDuplicates) > $duplicatePortStringLength) ? '...' : '';
				$errorMsg = 'Template contains duplicate port IDs: '.$duplicatePortString;
				array_push($validate->returnData['error'], $errorMsg);
			}
		} else {
			$errorMsg = 'Invalid template JSON structure.';
			array_push($validate->returnData['error'], $errorMsg);
		}

	} else if($data['action'] == 'delete'){
		
		// Validate templateCombined
		$templateCombinedArray = array('yes', 'no');
		$validate->validateInArray($data['templateCombined'], $templateCombinedArray, 'combined template flag');
		
		// Validate template ID
		$validate->validateObjectID($data['id']);
		
	} else if($data['action'] == 'edit'){
		//Validate object ID
		if($validate->validateID($data['templateID'], 'templateID')) {
			$templateID = $data['templateID'];
			$templateFace = $data['templateFace'];
			$templateDepth = $data['templateDepth'];
			$attribute = $data['attribute'];
			
			$attributeArray = array(
				'inline-category',
				'templateName',
				'portNameFormat',
				'inline-portOrientation',
				'inline-enclosureTolerance',
				'combinedTemplateCategory',
				'combinedTemplateName'
			);
			
			$combinedAttributeArray = array(
				'combinedTemplateCategory',
				'combinedTemplateName'
			);
			
			if($validate->validateInArray($attribute, $attributeArray, 'edit attribute')) {
				//Validate object existence
				$table = (in_array($attribute, $combinedAttributeArray)) ? 'app_combined_templates' : 'app_object_templates';
				$where = array('id' => array('=', $templateID));
				$errorMsg = 'Invalid templateID.';
				if($validate->validateExistenceInDB($table, $where, $errorMsg)) {
					
					if($attribute == 'inline-category' or $attribute == 'combinedTemplateCategory'){
						$categoryID = $data['value'];
						
						//Validate categoryID
						if($validate->validateID($categoryID, 'categoryID')) {
							$table = 'app_object_category';
							$where = array('id' => array('=', $categoryID));
							$errorMsg = 'Invalid categoryID.';
							$validate->validateExistenceInDB($table, $where, $errorMsg);
						}
					} else if($attribute == 'templateName' or $attribute == 'combinedTemplateName') {
						$templateName = $data['value'];
						
						//Validate templateName
						if($validate->validateNameText($templateName, 'template name')) {
							
							//Validate templateName duplicate
							$table = 'app_object_templates';
							$where = array('templateName' => array('=', $templateName));
							$errorMsg = 'Duplicate template name.';
							$validate->validateDuplicate($table, $where, $errorMsg);
						}
					} else if($data['attribute'] == 'portNameFormat') {
						if(isset($qls->App->compatibilityArray[$templateID][$templateFace][$templateDepth])) {
							$compatibility = $qls->App->compatibilityArray[$templateID][$templateFace][$templateDepth];
							
							if($compatibility['partitionType'] == 'Connectable') {
								$portNameFormat = $data['value'];
								$portTotal = $compatibility['portLayoutX'] * $compatibility['portLayoutY'];
								if($validate->validatePortNameFormat($portNameFormat, $portTotal)) {
									
									$portCollection = array();
									foreach($qls->App->compatibilityArray[$templateID] as $face => $side) {
										foreach($side as $depth => $partition) {
											
											if($partition['partitionType'] == 'Connectable') {
												$portTotal = $partition['portLayoutX'] * $partition['portLayoutY'];
												if($face == $templateFace and $depth == $templateDepth) {
													$workingPortNameFormat = $portNameFormat;
												} else {
													$workingPortNameFormat = json_decode($partition['portNameFormat'], true);
												}
												
												for($x=0; $x<$portTotal; $x++) {
													$portName = $qls->App->generatePortName($workingPortNameFormat, $x, $portTotal);
													array_push($portCollection, $portName);
												}
											}
										}
									}
									
									// Check that all port IDs are unique
									$portCollectionWorking = array();
									$portCollectionDuplicates = array();
									foreach($portCollection as $port) {
										if(in_array($port, $portCollectionWorking)) {
											array_push($portCollectionDuplicates, $port);
										}
										array_push($portCollectionWorking, $port);
									}
									if(count($portCollectionDuplicates)) {
										$duplicatePortStringLength = 3;
										$duplicatePortString = implode(', ', array_slice($portCollectionDuplicates, 0, $duplicatePortStringLength));
										$duplicatePortString .= (count($portCollectionDuplicates) > $duplicatePortStringLength) ? '...' : '';
										$errorMsg = 'Template contains duplicate port IDs: '.$duplicatePortString;
										array_push($validate->returnData['error'], $errorMsg);
									}
								}
							} else {
								$errorMsg = 'Invalid partition type.';
								array_push($validate->returnData['error'], $errorMsg);
							}
						
						} else {
							$errorMsg = 'Invalid template data.';
							array_push($validate->returnData['error'], $errorMsg);
						}
					} else if($data['attribute'] == 'inline-portOrientation') {
						$portOrientationID = $data['value'];
						$portOrientationIDArray = array(1, 2, 3, 4);
						$errMsg = 'port orientation ID';
						$validate->validateInArray($portOrientationID, $portOrientationIDArray, $reference);
					} else if($data['attribute'] == 'inline-enclosureTolerance') {
						$encTolerance = strtolower($data['value']);
						$encToleranceArray = array('strict', 'loose');
						$errMsg = 'enclosure tolerance';
						$validate->validateInArray($encTolerance, $encToleranceArray, $reference);
					}
				}
			}
		}
	} else if($data['action'] == 'combinedTemplate') {
		
		//Validate template name
		if($validate->validateNameText($data['name'], 'template name')) {
			//Validate templateName duplicate
			$templateName = $data['name'];
			$table = 'app_object_templates';
			$where = array('templateName' => array('=', $templateName));
			$errorMsg = 'Duplicate template name.';
			$validate->validateDuplicate($table, $where, $errorMsg);
		}
		
		//Validate category
		if($validate->validateID($data['category'], 'categoryID')) {
			//Validate category existence
			$categoryID = $data['category'];
			$table = 'app_object_category';
			$where = array('id' => array('=', $categoryID));
			$errorMsg = 'Invalid categoryID.';
			$validate->validateExistenceInDB($table, $where, $errorMsg);
		}
		
		//Validate template ID
		if($validate->validateID($data['parentTemplateID'], 'template ID')) {
			//Validate template existence
			$parentTemplateID = $data['parentTemplateID'];
			$table = 'app_object_templates';
			$where = array('id' => array('=', $parentTemplateID));
			$errorMsg = 'Invalid template ID.';
			$validate->validateExistenceInDB($table, $where, $errorMsg);
		}
		
		if(isset($data['childTemplateArray'])) {
			$childTemplateArray = $data['childTemplateArray'];
			if(is_array($childTemplateArray)) {
				foreach($childTemplateArray as $childTemplateID => $childTemplateDetails) {
					
					$encX = $childTemplateDetails['encX'];
					$encY = $childTemplateDetails['encY'];
					$parentFace = $childTemplateDetails['parentFace'];
					$parentDepth = $childTemplateDetails['parentDepth'];
					
					//Validate child template ID
					if($validate->validateID($childTemplateID, 'child template ID')) {
						//Validate template existence
						$table = 'app_object_templates';
						$where = array('id' => array('=', $childTemplateID));
						$errorMsg = 'Invalid child template ID.';
						$validate->validateExistenceInDB($table, $where, $errorMsg);
					}
					
					// Validate child template details
				}
			}
		}
		
	} else {
		//Error
		$errorMsg = 'Invalid action.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	return;
}

function enumeratePortIDs(&$qls, $data, &$portCollection){
	foreach($data as $partition) {
		if($partition['partitionType'] == 'Connectable') {
			$portTotal = $partition['valueX'] * $partition['valueY'];
			$portNameFormat = $partition['portNameFormat'];
			for($x=0; $x<$portTotal; $x++) {
				$portName = $qls->App->generatePortName($portNameFormat, $x, $portTotal);
				array_push($portCollection, $portName);
			}
		} else if(isset($partition['children'])) {
			enumeratePortIDs($qls, $partition['children'], $portCollection);
		}
	}
	return true;
}

?>
