<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$objID = $data['objID'];
		$objFace = $data['objFace'];
		$objDepth = $data['partitionDepth'];
		$portID = $data['portID'];
		
		if(isset($qls->App->objectArray[$objID])) {
			$objName = $qls->App->getPortNameString($objID, $objFace, $objDepth, $portID);
		} else {
			$objName = 'None';
		}
		
		$returnObj = $qls->App->wrapObject($objID, $objName);
		
		$validate->returnData['success'] = $returnObj;
		
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	//Validate object ID
	$validate->validateObjectID($data['objID']);
	
	//Validate object face
	$validate->validateObjectFace($data['objFace']);

	//Validate partition depth
	$validate->validatePartitionDepth($data['partitionDepth']);
	
	//Validate portID
	$validate->validatePortID($data['portID']);
	
	return true;
}
?>
