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
		$userID = $data['userID'];
		$userType = $data['userType'];
		
		if($data['action'] == 'role') {
			$groupID = $data['groupID'];
			
			if($userType == 'active') {
				$qls->SQL->update('users', array('group_id' => $groupID), array('id' => array('=', $userID)));
			} else if($userType == 'invitation') {
				$qls->SQL->update('invitations', array('group_id' => $groupID), array('id' => array('=', $userID)));
			}
		} else if($data['action'] == 'delete') {
			if($userType == 'active') {
				if($userID != $qls->user_info['id']) {
					$query = $qls->SQL->select('*', 'users', array('id' => array('=', $userID)));
					if($qls->SQL->num_rows($query)) {
						$qls->Admin->remove_user($userID);
					} else {
						$errMsg = 'User ID does not exist.';
						array_push($validate->returnData['error'], $errMsg);
					}
				} else {
					$errMsg = 'Cannot remove yourself.';
					array_push($validate->returnData['error'], $errMsg);
				}
			} else if($userType == 'invitation') {
				$qls->SQL->delete('invitations', array('id' => array('=', $userID), 'AND', 'used' => array('=', 0)));
			}
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	// Validate action
	$actionArray = array('role', 'delete');
	$action = $data['action'];
	if($validate->validateInArray($action, $actionArray, 'action')) {
		
		if($action == 'role') {
			$groupID = $data['groupID'];
			$validate->validateID($groupID, 'groupID');
		}
	}
	
	// Validate userID
	$userID = $data['userID'];
	$validate->validateID($userID, 'userID');
	
	// Validate userType
	$userTypeArray = array('active', 'invitation');
	$userType = $data['userType'];
	$validate->validateInArray($userType, $userTypeArray, 'user type');
	
	return;
}

?>
