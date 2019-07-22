<?php
define('QUADODO_IN_SYSTEM', true);
require_once $_SERVER['DOCUMENT_ROOT'].'/app/includes/header.php';
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
			$type= $data['type'];
			
			$mediaTypeArray = array();
			$query = $qls->shared_SQL->select('*', 'table_mediaType');
			while($row = $qls->shared_SQL->fetch_assoc($query)) {
				$mediaTypeArray[$row['value']] = $row;
			}
			
			$objectPortTypeArray = array();
			$query = $qls->shared_SQL->select('*', 'table_object_portType');
			while($row = $qls->shared_SQL->fetch_assoc($query)) {
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
			
			// Insert template data into DB
			$qls->app_SQL->insert('table_object_templates', array(
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
					'templatePartitionData'
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
					$partitionData
				)
			);
			
			$objectID = $qls->app_SQL->insert_id();
			
			// Gather compatibility data
			$compatibilityArray = array();
			foreach($data['objects'] as $face){
				array_push($compatibilityArray, getCompatibilityInfo($face));
			}
			
			// Insert compatibility data into DB
			foreach($compatibilityArray as $side=>$face){
				foreach($face as $element){
					$portType = $element['portType'];
					$mediaType = $function == 'Endpoint' ? 8 : $element['mediaType'];
					$mediaCategory = $function == 'Endpoint' ? 5 : $mediaTypeArray[$mediaType]['category_id'];
					$mediaCategoryType = $objectPortTypeArray[$portType]['category_type_id'];
					$portTotal = array_key_exists('portX', $element) ? $element['portX'] * $element['portY'] : 0;
					
					$qls->app_SQL->insert('table_object_compatibility', array(
							'template_id',
							'side',
							'depth',
							'portLayoutX',
							'portLayoutY',
							'portTotal',
							'encLayoutX',
							'encLayoutY',
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
						), array(
							$objectID,
							$side,
							$element['depth'],
							$element['portX'],
							$element['portY'],
							$portTotal,
							$element['encX'],// needs to come from enclosure object
							$element['encY'],//
							$type,
							$element['partitionType'],
							$function,
							$element['portOrientation'],
							$portType,
							$mediaType,
							$mediaCategory,
							$mediaCategoryType,
							$element['direction'],//
							$element['hUnits'],//
							$element['vUnits'],//
							$element['flex'],
							$element['portNameFormat']
						)
					);
				}
			}
			
			//return errors and results
			$validate->returnData['success'] = 'Object was added.';
			
			// Log action in history
			$actionString = 'Created new template: <strong>'.$name.'</strong>';
			$qls->App->logAction(1, 1, $actionString);
				
		} else if($action == 'delete') {
			$id = $data['id'];
			$result = $qls->app_SQL->select('id', 'table_object', array('template_id' => array('=', $id)));
			if ($qls->app_SQL->num_rows($result) == 0) {
				$name = $qls->App->templateArray[$id]['templateName'];
				$qls->app_SQL->delete('table_object_templates', array('id' => array('=', $id)));
				$qls->app_SQL->delete('table_object_compatibility', array('template_id' => array('=', $id)));
				$validate->returnData['success'] = 'Object was deleted.';
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Deleted template: <strong>'.$name.'</strong>';
				$qls->App->logAction(1, 3, $actionString);
			} else {
				array_push($validate->returnData['error'], 'Object is in use.');
			}
			
		} else if($action == 'edit') {
			$value = $data['value'];
			$templateID = $data['templateID'];
			if($data['attribute'] == 'inline-templateName'){
				$origName = $qls->App->templateArray[$templateID]['templateName'];
				$attribute = 'templateName';
				$return = $value;
				$qls->app_SQL->update('table_object_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
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
				$return = $qls->app_SQL->fetch_row($qls->app_SQL->select('name', 'table_object_category', array('id' => array('=', $value))))[0];
				$qls->app_SQL->update('table_object_templates', array($attribute => $value), array('id' => array('=', $templateID)));
				
				// Log action in history
				// $qls->App->logAction($function, $actionType, $actionString)
				$actionString = 'Changed <strong>'.$templateName.'</strong> template category: from <strong>'.$origCategoryName.'</strong> to <strong>'.$newCategoryName.'</strong>';
				$qls->App->logAction(1, 2, $actionString);
			} else if($data['attribute'] == 'portNameFormat') {
				$side = $data['templateFace'];
				$depth = $data['templateDepth'];
				$portNameFormat = $data['value'];
				$portNameFormatJSON = json_encode($portNameFormat);
				
				// Update compatibility port name format
				$qls->app_SQL->update('table_object_compatibility', array('portNameFormat' => $portNameFormatJSON), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $side), 'AND', 'depth' => array('=', $depth)));
				
				// Update template partition data
				$query = $qls->app_SQL->select('*', 'table_object_templates', array('id' => array('=', $templateID)));
				$template = $qls->app_SQL->fetch_assoc($query);
				$templatePartitionData = json_decode($template['templatePartitionData'], true);
				updatePortNameFormat($templatePartitionData[$side], $depth, $portNameFormat);
				$templatePartitionDataJSON = json_encode($templatePartitionData);
				$qls->app_SQL->update('table_object_templates', array('templatePartitionData' => $templatePartitionDataJSON), array('id' => array('=', $templateID)));
				
				// Generate new port name range
				$query = $qls->app_SQL->select('*', 'table_object_compatibility', array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $side), 'AND', 'depth' => array('=', $depth)));
				$compatibility = $qls->app_SQL->fetch_assoc($query);
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

function updatePortNameFormat(&$partitionData, $depth, $value, $counter=0){
	foreach($partitionData as &$element) {
		if($counter == $depth) {
			$element['portNameFormat'] = $value;
			return;
		} else if(isset($element['children'])){
			$counter++;
			updatePortNameFormat($element['children'], $depth, $value, $counter);
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
			$tempArray['portX'] = $element['portLayoutX'];
			$tempArray['portY'] = $element['portLayoutY'];
			$tempArray['partitionType'] = $element['partitionType'];
			$tempArray['portOrientation'] = $element['portOrientation'];
			$tempArray['portType'] = $element['portType'];
			$tempArray['mediaType'] = $element['mediaType'];
			$tempArray['direction'] = $element['direction'];
			$tempArray['hUnits'] = $element['hunits'];
			$tempArray['vUnits'] = $element['vunits'];
			$tempArray['flex'] = $element['flex'];
			$tempArray['portNameFormat'] = json_encode($element['portNameFormat']);
			array_push($dataArray, $tempArray);
		
		} else if($partitionType == 'Enclosure') {
				$tempArray = array();
				$tempArray['depth'] = $depthCounter;
				$tempArray['encX'] = $element['encLayoutX'];
				$tempArray['encY'] = $element['encLayoutY'];
				$tempArray['partitionType'] = $element['partitionType'];
				$tempArray['direction'] = $element['direction'];
				$tempArray['hUnits'] = $element['hunits'];
				$tempArray['vUnits'] = $element['vunits'];
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
		//Validate template name
		if($validate->validateNameText($data['name'], 'template name')) {
			//Validate templateName duplicate
			$templateName = $data['name'];
			$table = 'table_object_templates';
			$where = array('templateName' => array('=', $templateName));
			$errorMsg = 'Duplicate template name.';
			$validate->validateDuplicate($table, $where, $errorMsg);
		}
		
		//Validate category
		if($validate->validateID($data['category'], 'categoryID')) {
			//Validate category existence
			$categoryID = $data['category'];
			$table = 'table_object_category';
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
			foreach ($data['objects'] as $face) {
				$validate->validateTemplateJSON($face[0]);
			}
		} else {
			$errorMsg = 'Invalid template JSON structure.';
			array_push($validate->returnData['error'], $errorMsg);
		}

	} else if($data['action'] == 'delete'){
		
		//Validate object ID
		$validate->validateObjectID($data['id']);
		
	} else if($data['action'] == 'edit'){
		//Validate object ID
		if($validate->validateID($data['templateID'], 'templateID')) {
			$templateID = $data['templateID'];
			$templateFace = $data['templateFace'];
			$templateDepth = $data['templateDepth'];
			
			//Validate object existence
			$table = 'table_object_templates';
			$where = array('id' => array('=', $templateID));
			$errorMsg = 'Invalid templateID.';
			if($validate->validateExistenceInDB($table, $where, $errorMsg)) {
				
				if($data['attribute'] == 'inline-category'){
					$categoryID = $data['value'];
					
					//Validate categoryID
					if($validate->validateID($categoryID, 'categoryID')) {
						$table = 'table_object_category';
						$where = array('id' => array('=', $categoryID));
						$errorMsg = 'Invalid categoryID.';
						$validate->validateExistenceInDB($table, $where, $errorMsg);
					}
				} else if($data['attribute'] == 'inline-templateName') {
					$templateName = $data['value'];
					
					//Validate templateName
					if($validate->validateNameText($templateName, 'template name')) {
						
						//Validate templateName duplicate
						$table = 'table_object_templates';
						$where = array('templateName' => array('=', $templateName));
						$errorMsg = 'Duplicate template name.';
						$validate->validateDuplicate($table, $where, $errorMsg);
					}
				} else if($data['attribute'] == 'portNameFormat') {
					$query = $qls->app_SQL->select('*', 'table_object_compatibility', array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $templateFace), 'AND', 'depth' => array('=', $templateDepth)));
					if($qls->app_SQL->num_rows($query) == 1) {
						$compatibility = $qls->app_SQL->fetch_assoc($query);
						
						if($compatibility['partitionType'] == 'Connectable') {
							$portNameFormat = $data['value'];
							$validate->validatePortNameFormat($portNameFormat);
						} else {
							$errorMsg = 'Invalid partition type.';
							array_push($validate->returnData['error'], $errorMsg);
						}
					} else {
						$errorMsg = 'Invalid template data.';
						array_push($validate->returnData['error'], $errorMsg);
					}
				} else {
					//Error
					$errorMsg = 'Invalid attribute.';
					array_push($validate->returnData['error'], $errorMsg);
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

?>
