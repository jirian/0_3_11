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
				
			case 'scrollLock':
				$scrollLock = $data['value'] ? 1 : 0;
				$qls->SQL->update('users', array('scrollLock' => $scrollLock), array('id' => array('=', $qls->user_info['id'])));
				$validate->returnData['success'] = 'Template scroll has been updated.';
				break;
				
			case 'connectionStyle':
				$connectionStyle = $data['value'];
				$qls->SQL->update('users', array('connectionStyle' => $connectionStyle), array('id' => array('=', $qls->user_info['id'])));
				$validate->returnData['success'] = 'Connection Style has been updated.';
				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	$propertyArray = array('timezone', 'scanMethod', 'scrollLock', 'connectionStyle');
	
	if($validate->validateInArray($data['property'], $propertyArray, 'property')) {
		
		if($data['property'] == 'timezone') {
			
			// Validate timezone
			$validate->validateTimezone($data['value'], $qls);
			
		} else if($data['property'] == 'scanMethod') {
			
			// Validate scanMethod
			$scanMethodArray = array('manual', 'barcode');
			$validate->validateInArray($data['value'], $scanMethodArray, 'scan method');
			
		} else if($data['property'] == 'scrollLock'){
			
			if(!is_bool($data['value'])) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		} else if($data['property'] == 'scrollLock'){
			$connectionStyleArray = array(0, 1, 2);
			
			if(!in_array($data['value'], $connectionStyleArray)) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
	}
	return;
}

?>
