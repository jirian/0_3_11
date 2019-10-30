<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');
require_once '../includes/path_functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$serverName = $data['value'];
		$qls->SQL->update('config', array('value' => $serverName), array('name' => array('=', 'cookie_domain')));
		$validate->returnData['success'] = $serverName;
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	// Validate servername
	$serverName = $data['value'];
	$validate->validateServerName($serverName);
	
	// Do not update server name if the app is hosted
	if(array_key_exists('PCM_Hosted', getallheaders())) {
		$errorMsg = 'Cannot change server name of hosted app.';
		array_push($validate->returnData['error'], $errorMsg);
	}
}

?>
