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
		
		// Functions required to create $path
		include_once $_SERVER['DOCUMENT_ROOT'].'/includes/path_functions.php';
		// Create $path
		//if($connectorCode39) {
			//include_once $_SERVER['DOCUMENT_ROOT'].'/includes/content_cable_path.php';
		//} else {
			include_once $_SERVER['DOCUMENT_ROOT'].'/includes/content_port_path.php';
		//}
		
		$validate->returnData['success'] = $qls->App->buildPathFull($path);
		//error_log('-=END RETRIEVE PATH FULL=-');
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
}
?>
