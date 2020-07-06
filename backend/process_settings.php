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
				
			case 'pathOrientation':
				$pathOrientation = $data['value'];
				
				if($qls->org_info['global_setting_path_orientation'] == 1) {
					if($qls->user_info['group_id'] == 3) {
						$query = $qls->SQL->select('*', 'users');
						while($row = $qls->SQL->fetch_assoc($query)) {
							$qls->SQL->update('users', array('pathOrientation' => $pathOrientation), array('id' => array('=', $row['id'])));
						}
					} else {
						$errMsg = 'Path Orientation has been set globally by Administrator.';
						array_push($validate->returnData['error'], $errMsg);
					}
				} else {
					$qls->SQL->update('users', array('pathOrientation' => $pathOrientation), array('id' => array('=', $qls->user_info['id'])));
				}
				
				$validate->returnData['success'] = 'Path Orientation has been updated.';
				break;
				
			case 'globalPathOrientation':
				if($qls->user_info['group_id'] == 3) {
					$globalPathOrientation = $data['value'] ? 1 : 0;
					$qls->SQL->update('app_organization_data', array('global_setting_path_orientation' => $globalPathOrientation), array('id' => array('=', $qls->org_info['id'])));
					
					$pathOrientation = $qls->user_info['pathOrientation'];
					$query = $qls->SQL->select('*', 'users');
					while($row = $qls->SQL->fetch_assoc($query)) {
						$qls->SQL->update('users', array('pathOrientation' => $pathOrientation), array('id' => array('=', $row['id'])));
					}
					
					$validate->returnData['success'] = 'Path Orientation Global Setting has been updated.';
				} else {
					$errMsg = 'Setting requires Administrator role.';
					array_push($validate->returnData['error'], $errMsg);
				}
				break;
				
			case 'treeSize':
				$treeSize = $data['value'];
				
				$qls->SQL->update('users', array('treeSize' => $treeSize), array('id' => array('=', $qls->user_info['id'])));
				
				$validate->returnData['success'] = 'Tree Size has been updated.';
				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	$propertyArray = array('timezone', 'scanMethod', 'scrollLock', 'connectionStyle', 'pathOrientation', 'globalPathOrientation', 'treeSize');
	
	if($validate->validateInArray($data['property'], $propertyArray, 'property')) {
		
		if($data['property'] == 'timezone') {
			
			// Validate timezone
			$validate->validateTimezone($data['value'], $qls);
			
		} else if($data['property'] == 'scanMethod') {
			
			// Validate scanMethod
			$scanMethodArray = array('manual', 'barcode');
			$validate->validateInArray($data['value'], $scanMethodArray, 'scan method');
			
		} else if($data['property'] == 'scrollLock' or $data['property'] == 'globalPathOrientation'){
			
			if(!is_bool($data['value'])) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		} else if($data['property'] == 'connectionStyle'){
			$connectionStyleArray = array(0, 1, 2);
			
			if(!in_array($data['value'], $connectionStyleArray)) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		} else if($data['property'] == 'pathOrientation'){
			$pathOrientationArray = array(0, 1);
			
			if(!in_array($data['value'], $pathOrientationArray)) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		} else if($data['property'] == 'treeSize'){
			$treeSizeArray = array(0, 1);
			
			if(!in_array($data['value'], $treeSizeArray)) {
				$errMsg = 'Invalid value.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
	}
	return;
}

?>
