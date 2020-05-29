<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}

	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$action = $data['action'];
		
		if($action == 'add') {
			$nodeID = $data['nodeID'];
			$name = $data['name'];
			$type = $data['type'];
			$positionTop = $data['positionTop'];
			$positionLeft = $data['positionLeft'];
			$templateID = $qls->App->floorplanObjDetails[$type]['templateID'];
			
			//Insert data into DB
			$qls->SQL->insert('app_object', array(
					'env_tree_id',
					'name',
					'template_id',
					'position_top',
					'position_left'
				), array(
					$nodeID,
					$name,
					$templateID,
					$positionTop,
					$positionLeft
				)
			);
			
			//This tells the client what the new object_id is
			$validate->returnData['success']['id'] = $qls->SQL->insert_id();
			$validate->returnData['success']['name'] = $name;
		} else if($action == 'editLocation') {
			$objectID = $data['objectID'];
			$positionTop = $data['positionTop'];
			$positionLeft = $data['positionLeft'];
			
			//Update DB entry
			$qls->SQL->update('app_object', array(
				'position_top' => $positionTop,
				'position_left' => $positionLeft
				),
				'id = '.$objectID
			);
		} else if($action == 'editName') {
			$objectID = $data['objectID'];
			$name = $data['value'];
			
			//Update DB entry
			$qls->SQL->update('app_object', array(
				'name' => $name
				),
				'id = '.$objectID
			);
		} else if($action == 'delete') {
			$objectID = $data['objectID'];
			
			//Delete DB entry
			$qls->SQL->delete('app_object', array('id' => array('=', $objectID)));
			$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID)));
			$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $objectID)));
			$query = $qls->SQL->select('*', 'app_inventory', array('a_object_id' => array('=', $objectID), 'OR', 'b_object_id' => array('=', $objectID)));
			while($row = $qls->SQL->fetch_assoc($query)) {
				if($row['a_id'] or $row['b_id']) {
					$attrArray = array('a', 'b');
					foreach($attrArray as $attrPrefix) {
						if($row[$attrPrefix.'_object_id'] == $objectID) {
							$set = array(
								$attrPrefix.'_object_id' => 0,
								$attrPrefix.'_port_id' => 0,
								$attrPrefix.'_object_face' => 0,
								$attrPrefix.'_object_depth' => 0
							);
							$qls->SQL->update('app_inventory', $set, array('id' => array('=', $row['id'])));
						}
					}
				} else {
					$qls->SQL->delete('app_inventory', array('id' => array('=', $row['id'])));
				}
			}
		} else {
			$errorMsg = 'Invalid action.';
			array_push($validate->returnData['error'], $errorMsg);
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate(&$data, &$validate, &$qls){
	
	//Validate action
	$actionArray = array('add', 'editLocation', 'editName', 'delete');
	if($validate->validateInArray($data['action'], $actionArray, 'action')) {
		$action = $data['action'];
		
		if($action == 'add') {
			
			//Validate object type
			$typeArray = array('walljack', 'wap', 'device', 'camera');
			$validate->validateInArray($data['type'], $typeArray, 'type');
			
			//Validate positions
			$validate->validateID($data['positionTop'], 'object ID');
			$validate->validateID($data['positionLeft'], 'object position');
			
			//Validate node ID
			if($validate->validateID($data['nodeID'], 'cabinet ID')) {
				$name = $qls->App->findUniqueName($data['nodeID'], 'object');
				if($name === false) {
					$errMsg = 'Unable to find unique name.';
					array_push($validate->returnData['error'], $errMsg);
				} else {
					$data['name'] = $name;
				}
			}
			
		} else if($action == 'editLocation') {
			
			//Validate positions
			$validate->validateID($data['positionTop'], 'object position');
			$validate->validateID($data['positionLeft'], 'object position');
			
			//Validate object ID
			$validate->validateID($data['objectID'], 'object ID');
			
		} else if($action == 'editName') {
			//Validate object ID
			$validate->validateID($data['objectID'], 'object ID');
			
			//Validate object name
			$validate->validateObjectName($data['value'], 'object name');
		} else if($action == 'editName') {
			//Validate object ID
			$validate->validateID($data['objectID'], 'object ID');
		}
	}

	return;
}
?>
