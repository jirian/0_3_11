<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$objectID = $data['objID'];
		$objectFace = $data['objFace'];
		$partitionDepth = $data['partitionDepth'];
		$portID = $data['portID'];
		$portPopulated = $data['portPopulated'];
		
		if($portPopulated) {
			$qls->SQL->insert(
				'app_populated_port',
				array(
					'object_id',
					'object_face',
					'object_depth',
					'port_id'
				),
				array(
					$objectID,
					$objectFace,
					$partitionDepth,
					$portID
				)
			);
			
			// Log history
			$portName = $qls->App->generateObjectPortName($objectID, $objectFace, $partitionDepth, $portID);
			$actionString = 'Marked port as populated: <strong>'.$portName.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else {
			$qls->SQL->delete(
				'app_populated_port',
				array(
					'object_id' => array('=', $objectID),
					'AND',
					'object_face' => array('=', $objectFace),
					'AND',
					'object_depth' => array('=', $partitionDepth),
					'AND',
					'port_id' => array('=', $portID)
				)
			);
			
			// Log history
			$portName = $qls->App->generateObjectPortName($objectID, $objectFace, $partitionDepth, $portID);
			$actionString = 'Marked port as unpopulated: <strong>'.$portName.'</strong>';
			$qls->App->logAction(3, 2, $actionString);
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate object ID
	$validate->validateObjectID($data['objID']);
	
	//Validate object face
	$validate->validateObjectFace($data['objFace']);
	
	//Validate partition depth
	$validate->validatePartitionDepth($data['partitionDepth']);
	
	//Validate port ID
	$validate->validatePortID($data['portID'], 'port ID');
	
	//Validate port populated
	$validate->validateTrueFalse($data['portPopulated'], 'port populated flag');
	
	return;
}
?>
