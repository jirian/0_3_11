<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	include_once $_SERVER['DOCUMENT_ROOT'].'/includes/Validate.class.php';
	
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$connectorCode39 = isset($data['connectorCode39']) ? $data['connectorCode39'] : false;
		$objID = isset($data['objID']) ? $data['objID'] : false;
		$objPort = isset($data['portID']) ? $data['portID'] : false;
		$objFace = isset($data['objFace']) ? $data['objFace'] : false;
		$objDepth = isset($data['partitionDepth']) ? $data['partitionDepth'] : false;

		// Create $path
		include_once $_SERVER['DOCUMENT_ROOT'].'/includes/content-path.php';
		error_log('Debug (path): '.json_encode($path));
		$validate->returnData['success'] = $qls->App->buildPathFull($path, $connectorCode39);
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
}
?>
