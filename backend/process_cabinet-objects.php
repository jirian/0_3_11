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
		$objectID = $data['objectID'];
		
		if($action == 'add') {
			
			$cabinetID = $data['cabinetID'];
			$cabinetFace = $data['cabinetFace'];
			$templateCombined = $data['templateCombined'];
			$name = $data['name'];
			$RU = isset($data['RU']) ? $data['RU'] : 0;
			
			if($templateCombined == 'yes') {
				$combinedTemplate = $qls->App->combinedTemplateArray[$objectID];
				$objectID = $combinedTemplate['template_id'];
				$childTemplateData = json_decode($combinedTemplate['childTemplateData'], true);
			}
			
			$object = $qls->App->templateArray[$objectID];
			
			$objectMountConfig = $object['templateMountConfig'];
			$RUSize = $object['templateRUSize'];
			
			if ($cabinetFace == 0) {
				$cabinetFront = 0;
				if ($objectMountConfig == 1) {
					$cabinetBack = 1;
				} else {
					$cabinetBack = null;
				}
			} else {
				$cabinetBack = 0;
				if ($objectMountConfig == 1) {
					$cabinetFront = 1;
				} else {
					$cabinetFront = null;
				}
			}
			
			$parent_id = isset($data['parent_id']) ? $data['parent_id'] : 0;
			$parent_face = isset($data['parent_face']) ? $data['parent_face'] : 0;
			$parent_depth = isset($data['parent_depth']) ? $data['parent_depth'] : 0;
			$insertSlotX = isset($data['insertSlotX']) ? $data['insertSlotX'] : 0;
			$insertSlotY = isset($data['insertSlotY']) ? $data['insertSlotY'] : 0;
			
			if ($object['templateType'] == 'Insert') {
				checkInsertCompatibility($parent_id, $parent_face, $parent_depth, $objectID, false, $qls, $validate);
				detectInsertCollision($parent_id, $parent_face, $parent_depth, $insertSlotX, $insertSlotY, $qls, $validate);
			} else {
				detectCollision($RUSize, $cabinetFace, $RU, $objectMountConfig, $cabinetID, $qls, $validate);
				detectOverlap($RUSize, $RU, $cabinetID, $qls, $validate);
			}
			
			if(count($validate->returnData['error'])) {
				echo json_encode($validate->returnData);
				return;
			}
				
			//Insert data into DB
			$qls->SQL->insert('app_object', array(
					'env_tree_id',
					'template_id', 
					'name',
					'RU',
					'cabinet_front',
					'cabinet_back',
					'parent_id',
					'parent_face',
					'parent_depth',
					'insertSlotX',
					'insertSlotY'
				), array(
					$cabinetID,
					$objectID,
					$name,
					$RU,
					$cabinetFront,
					$cabinetBack,
					$parent_id,
					$parent_face,
					$parent_depth,
					$insertSlotX,
					$insertSlotY
				)
			);
			
			$newObjID = $qls->SQL->insert_id();
			$newObjChildIDArray = array();
			
			if(isset($childTemplateData)) {
				foreach($childTemplateData as $childTemplate) {
					$templateID = $childTemplate['templateID'];
					$name = $childTemplate['name'];
					$RU = 0;
					$cabinetFront = 0;
					$cabinetBack = null;
					$parent_id = $newObjID;
					$parent_face = $childTemplate['parentFace'];
					$parent_depth = $childTemplate['parentDepth'];
					$insertSlotX = $childTemplate['encX'];
					$insertSlotY = $childTemplate['encY'];
					
					//Insert data into DB
					$qls->SQL->insert('app_object', array(
							'env_tree_id',
							'template_id', 
							'name',
							'RU',
							'cabinet_front',
							'cabinet_back',
							'parent_id',
							'parent_face',
							'parent_depth',
							'insertSlotX',
							'insertSlotY'
						), array(
							$cabinetID,
							$templateID,
							$name,
							$RU,
							$cabinetFront,
							$cabinetBack,
							$parent_id,
							$parent_face,
							$parent_depth,
							$insertSlotX,
							$insertSlotY
						)
					);
					
					$newObjChildID = $qls->SQL->insert_id();
					$newObjChildIDArray[$parent_face][$parent_depth][$insertSlotX][$insertSlotY] = $newObjChildID;
				}
			}
			
			//This tells the client what the new object_id is
			$validate->returnData['success'] = $newObjID;
			$validate->returnData['data']['parentID'] = $newObjID;
			$validate->returnData['data']['childrenID'] = $newObjChildIDArray;
			
			// Log history
			$cabinetName = $qls->App->envTreeArray[$cabinetID]['nameString'];
			$actionString = 'Added new object: <strong>'.$cabinetName.'.'.$name.'</strong>';
			$qls->App->logAction(2, 1, $actionString);
			
		} else if($action == 'updateObject') {
			$cabinetID = $data['cabinetID'];
			$RU = $data['RU'];
			$cabinetFace = $data['cabinetFace'];
			$objectTemplateID = $qls->SQL->fetch_row($qls->SQL->select('template_id', 'app_object', array('id' => array('=', $objectID))))[0];
			$object = $qls->SQL->fetch_assoc(
				$qls->SQL->select(
					'*',
					'app_object_templates',
					array(
						'id' => array(
							'=',
							$objectTemplateID
						)
					)
				)
			);
			$objectMountConfig = $object['templateMountConfig'];
			$RUSize = $object['templateRUSize'];
			
			detectCollision($RUSize, $cabinetFace, $RU, $objectMountConfig, $cabinetID, $qls, $validate, $objectID);
			detectOverlap($RUSize, $RU, $cabinetID, $qls, $validate);
			
			if(count($validate->returnData['error'])) {
				echo json_encode($validate->returnData);
				return;
			}
			
			// Update DB entry
			$qls->SQL->update('app_object', array(
				'RU' => $RU
				),
				'id = '.$objectID
			);
			
			// Log history
			$objectName = $qls->App->objectArray[$objectID]['nameString'];
			$actionString = 'Moved object: <strong>'.$objectName.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else if($action == 'updateInsert') {
			$parent_id = $data['parent_id'];
			$parent_face = $data['parent_face'];
			$parent_depth = $data['parent_depth'];
			$insertSlotX = $data['insertSlotX'];
			$insertSlotY = $data['insertSlotY'];
			$objectTemplateID = $qls->SQL->fetch_row($qls->SQL->select('template_id', 'app_object', array('id' => array('=', $objectID))))[0];
			
			checkInsertCompatibility($parent_id, $parent_face, $parent_depth, $objectTemplateID, $objectID, $qls, $validate);
			detectInsertCollision($parent_id, $parent_face, $parent_depth, $insertSlotX, $insertSlotY, $qls, $validate);
			
			if(count($validate->returnData['error'])) {
				echo json_encode($validate->returnData);
				return;
			}
			
			//Update DB entry
			$qls->SQL->update('app_object', array(
				'parent_id' => $parent_id,
				'parent_face' => $parent_face,
				'parent_depth' => $parent_depth,
				'insertSlotX' => $insertSlotX,
				'insertSlotY' => $insertSlotY
				),
				'id = '.$objectID
			);
			
			// Log history
			$objectName = $qls->App->objectArray[$objectID]['nameString'];
			$actionString = 'Moved insert: <strong>'.$objectName.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else if($action == 'edit') {
			$name = $data['value'];
			
			$qls->SQL->update('app_object',
				array('name' => $name),
				array('id' => array('=', $objectID))
			);
			
			$validate->returnData['success'] = $name;
			
			// Log history
			$objectName = $qls->App->objectArray[$objectID]['nameString'];
			$actionString = 'Changed object name: From <strong>'.$objectName.'</strong> to <strong>'.$name.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else if($action == 'delete') {
			$safeToDelete = true;
			
			// Check object for connections
			if(isset($qls->App->inventoryArray[$objectID])) {
				$safeToDelete = false;
			}
			
			// Check insert(s) for connections
			if(isset($qls->App->insertArray[$objectID])) {
				foreach($qls->App->insertArray[$objectID] as $insert) {
					$insertID = $insert['id'];
					if(isset($qls->App->inventoryArray[$insertID])) {
						$safeToDelete = false;
					}
				}
			}
			
			if($safeToDelete) {
				// Remove insert peer entries and populated ports
				if(isset($qls->App->insertArray[$objectID])) {
					foreach($qls->App->insertArray[$objectID] as $insert) {
						$insertID = $insert['id'];
						$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $insertID), 'OR', 'b_id' => array('=', $insertID)));
						$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $insertID)));
					}
				}
				
				// Remove object peer entries and populated ports
				$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
				$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $objectID)));
				
				// Delete inserts
				$qls->SQL->delete('app_object', array('parent_id'=>array('=', $objectID)));
				
				//Delete object
				$qls->SQL->delete('app_object', array('id'=>array('=', $objectID)));
				
				// Log history
				$objectName = $qls->App->objectArray[$objectID]['nameString'];
				$actionString = 'Delected object: <strong>'.$objectName.'</strong>';
				$qls->App->logAction(2, 3, $actionString);
				
			} else {
				$errorMsg = 'Object cannot be deleted.  Cables are connected to it.';
				array_push($validate->returnData['error'], $errorMsg);
			}
			
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function checkInsertCompatibility($parent_id, $parent_face, $parent_depth, $objectTemplateID, $objectID, &$qls, &$validate){
	$compatible = true;
	$parentTemplateID = $qls->SQL->fetch_row($qls->SQL->select('template_id', 'app_object', array('id' => array('=', $parent_id))))[0];
	$objectTemplateID = $objectID ? $qls->SQL->fetch_row($qls->SQL->select('template_id', 'app_object', array('id' => array('=', $objectID))))[0] : $objectTemplateID;
	$parent = $qls->SQL->fetch_assoc($qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $parentTemplateID), 'AND', 'side' => array('=', $parent_face), 'AND', 'depth' => array('=', $parent_depth))));
	$insert = $qls->SQL->fetch_assoc($qls->SQL->select('*', 'app_object_templates', 'id='.$objectTemplateID));
	
	if($parent['encTolerance'] == 'Strict') {
		if($parent['hUnits'] != $insert['templateHUnits']) {
			error_log('Debug: hUnits');
			$compatible = false;
		}
		if($parent['vUnits'] != $insert['templateVUnits']) {
			error_log('Debug: vUnits');
			$compatible = false;
		}
		if($parent['encLayoutX'] != $insert['templateEncLayoutX']) {
			error_log('Debug: layoutX');
			$compatible = false;
		}
		if($parent['encLayoutY'] != $insert['templateEncLayoutY']) {
			error_log('Debug: layoutY');
			$compatible = false;
		}
	}
	
	if($parent['partitionFunction'] != $insert['templateFunction']) {
		error_log('Debug: function');
		$compatible = false;
	}

	if(!$compatible) {
		$errorMsg = 'Insert is not compatible with this enclosure slot.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	return;
}

function detectInsertCollision($parent_id, $parent_face, $parent_depth, $insertSlotX, $insertSlotY, &$qls, &$validate){
	$query = $qls->SQL->select(
		'id',
		'app_object',
		array(
			'parent_id' => array(
				'=',
				$parent_id
			),
			'AND',
			'parent_face' => array(
				'=',
				$parent_face,
			),
			'AND',
			'parent_depth' => array(
				'=',
				$parent_depth,
			),
			'AND',
			'insertSlotX' => array(
				'=',
				$insertSlotX,
			),
			'AND',
			'insertSlotY' => array(
				'=',
				$insertSlotY,
			)
		)
	);
	$results = $qls->SQL->num_rows($query);
	if($results > 0){
		$errorMsg = 'Enclosure slot is occupied.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	return;
}

function detectCollision($RUSize, $cabinetFace, $RU, $objectMountConfig, $cabinetID, &$qls, &$validate, $objectID=0){
	$cabinetFaceAttr = $cabinetFace == 0 ? 'cabinet_front' : 'cabinet_back';
	if($objectMountConfig == 0) {
		$query = $qls->SQL->select(
			'*',
			'app_object',
			'env_tree_id = '.$cabinetID.' AND id <> '.$objectID.' AND RU <> 0 AND '.$cabinetFaceAttr.' IS NOT NULL'
		);
	} else {
		$query = $qls->SQL->select(
			'*',
			'app_object',
			'env_tree_id = '.$cabinetID.' AND id <> '.$objectID.' AND RU <> 0 AND (cabinet_front IS NOT NULL OR cabinet_back IS NOT NULL)'
		);
	}
	
	$occupiedSpacialArray = array();
	$objectSpacialArray = range(($RU-$RUSize)+1, $RU);
	
	while($row = $qls->SQL->fetch_assoc($query)) {
		$template = $qls->SQL->fetch_row($qls->SQL->select('templateRUSize', 'app_object_templates', array('id' => array('=', $row['template_id']))));
		$tempArray = range(($row['RU']-$template[0])+1, $row['RU']);
		$occupiedSpacialArray = array_merge($occupiedSpacialArray, $tempArray);
	}
	
	if(sizeof(array_intersect($occupiedSpacialArray, $objectSpacialArray))){
		$errorMsg = 'Object overlaps with one that\'s already installed.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	return;
}

function detectOverlap($RUSize, $RU, $cabinetID, &$qls, &$validate){
	$objectBtmRU = ($RU-$RUSize)+1;
	$objectTopRU = $RU;
	$query = $qls->SQL->select('size', 'app_env_tree', array('id' => array('=', $cabinetID)));
	$cabinet = $qls->SQL->fetch_row($query);
	$cabinetSize = $cabinet[0];
	if($objectBtmRU < 1 || $objectTopRU > $cabinetSize){
		$errorMsg = 'Object extends past the cabinet space.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	return;
}

function validate(&$data, &$validate, &$qls){
	
	// Validate objectID and action
	$objectID = $data['objectID'];
	$actionArray = array('add', 'delete', 'edit', 'updateObject', 'updateInsert');
	$action = $data['action'];
	if($validate->validateInArray($action, $actionArray, 'action') and $validate->validateID($objectID, 'object ID')) {
	
		switch($action){
			case 'add':
			
				// Validate cabinet ID
				if($validate->validateID($data['cabinetID'], 'cabinet ID')) {
					$name = $qls->App->findUniqueName($data['cabinetID'], 'object');
					if($name === false) {
						$errMsg = 'Unable to find unique name.';
						array_push($validate->returnData['error'], $errMsg);
					} else {
						$data['name'] = $name;
					}
				}
				
				// Validate template combined
				$templateCombined = $data['templateCombined'];
				$templateCombinedArray = array('yes', 'no');
				$validate->validateInArray($templateCombined, $templateCombinedArray, 'template combined flag');
		
				// Validate cabinet RU
				if($data['RU'] != 0) {
					$validate->validateRUSize($data['RU'], 'cabinet RU');
				}
				
				// Validate entitlement
				$query = $qls->SQL->select('id', 'app_object');
				$objNum = $qls->SQL->num_rows($query) + 1;
				
				if(!$qls->App->checkEntitlement('object', $objNum)) {
					$errMsg = 'Exceeded entitled object count.';
					array_push($validate->returnData['error'], $errMsg);
				}
				
				// Validate cabinet face
				$cabinetFace = $data['cabinetFace'];
				$validate->validateObjectFace($cabinetFace);
				
				// Validate insert related input
				if(isset($data['parent_id']) or isset($data['parent_face']) or isset($data['parent_depth']) or isset($data['insertSlotX']) or isset($data['insertSlotY'])) {
					$parent_id = $data['parent_id'];
					$parent_face = $data['parent_face'];
					$parent_depth = $data['parent_depth'];
					$insertSlotX = $data['insertSlotX'];
					$insertSlotY = $data['insertSlotY'];
					
					$validate->validateID($parent_id, 'parent id');
					$validate->validateObjectFace($parent_face, 'parent face');
					$validate->validateID($parent_depth, 'parent depth');
					$validate->validateID($insertSlotX, 'insert slot X');
					$validate->validateID($insertSlotY, 'insert slot Y');
				}
				break;
				
			case 'delete':
				break;
				
			case 'updateObject':
			
				// Validate cabinet ID
				$cabinetID = $data['cabinetID'];
				$validate->validateID($cabinetID, 'cabinet id');
				
				// Validate cabinet RU
				$objectRU = $data['RU'];
				$validate->validateRUSize($objectRU);
				
				// Validate cabinet face
				$cabinetFace = $data['cabinetFace'];
				$validate->validateObjectFace($cabinetFace);
				
				break;
				
			case 'updateInsert':
				
				// Validate insert related input
				$parent_id = $data['parent_id'];
				$parent_face = $data['parent_face'];
				$parent_depth = $data['parent_depth'];
				$insertSlotX = $data['insertSlotX'];
				$insertSlotY = $data['insertSlotY'];
				
				$validate->validateID($parent_id, 'parent id');
				$validate->validateObjectFace($parent_face, 'parent face');
				$validate->validateID($parent_depth, 'parent depth');
				$validate->validateID($insertSlotX, 'insert slot X');
				$validate->validateID($insertSlotY, 'insert slot Y');
				
				break;
				
			case 'edit':
			
				// Validate object existence
				$table = 'app_object';
				$where = array('id' => array('=', $objectID));
				if($object = $validate->validateExistenceInDB($table, $where, 'Object does not exist.')) {
					
					$parentID = $object['parent_id'];
					$cabinetID = $object['env_tree_id'];
					
					if($validate->validateNameText($data['value'], 'object name')) {
						
						$name = $data['value'];
						$table = 'app_object';
						if($parentID) {
							$where = array('name' => array('=', $name), 'AND', 'env_tree_id' => array('=', $cabinetID), 'AND', 'parent_id' => array('=', $parentID));
						} else {
							$where = array('name' => array('=', $name), 'AND', 'env_tree_id' => array('=', $cabinetID));
						}
						$validate->validateDuplicate($table, $where, 'Duplicate object name found in the same cabinet.');
					}
				}
				
				break;
		}
	}
	return;
}
?>
