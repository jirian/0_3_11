<?php
define('QUADODO_IN_SYSTEM', true);
require_once $_SERVER['DOCUMENT_ROOT'].'/app/includes/header.php';
$qls->Security->check_auth_page('administrator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once $_SERVER['DOCUMENT_ROOT'].'/app/includes/Validate.class.php';
	
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		switch($data['property']) {
			case 'timezone':
				$timezone = $data['value'];
				$qls->SQL->update('users', array('timezone' => $timezone), array('id' => array('=', $qls->user_info['id'])));
				$validate->returnData['success'] = 'Timezone has been updated.';
				break;
				
			case 'scanMethod':
				$scanMethod = $data['value'] == 'manual' ? 0 : 1;
				$qls->SQL->update('users', array('scanMethod' => $scanMethod), array('id' => array('=', $qls->user_info['id'])));
				$validate->returnData['success'] = 'Scan method has been updated.';
				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	$propertyArray = array('timezone', 'scanMethod');
	
	if($validate->validateInArray($data['property'], $propertyArray, 'property')) {
		
		if($data['property'] == 'timezone') {
			
			// Validate timezone
			$validate->validateTimezone($data['value'], $qls);
			
		} else if($data['property'] == 'scanMethod') {
			
			// Validate scanMethod
			$scanMethodArray = array('manual', 'barcode');
			$validate->validateInArray($data['value'], $scanMethodArray, 'scan method');
			
		}
		
	}
	return;
}

?>
