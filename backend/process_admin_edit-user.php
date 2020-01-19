<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

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
		$action = $data['action'];
		
		if($action == 'role') {
			$groupID = $data['groupID'];
			
			if($userType == 'active') {
				$qls->SQL->update('users', array('group_id' => $groupID), array('id' => array('=', $userID)));
			} else if($userType == 'invitation') {
				$qls->SQL->update('invitations', array('group_id' => $groupID), array('id' => array('=', $userID)));
			}
		} else if($action == 'status') {
			
			$status = strtolower($data['status']);
			$qls->SQL->update('users', array('blocked' => $status), array('id' => array('=',$userID)));
			
		} else if($action == 'mfa') {
			
			$mfa = $data['state'];
			// Can only turn MFA off
			if($mfa == 0) {
				$qls->SQL->update('users', array('mfa' => $mfa), array('id' => array('=',$userID)));
			}
			
		} else if($action == 'delete') {
			if($userType == 'active') {
				$query = $qls->SQL->select('*', 'users', array('id' => array('=', $userID)));
				if($qls->SQL->num_rows($query)) {
					$qls->Admin->remove_user($userID);
				} else {
					$errMsg = 'User ID does not exist.';
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
	$actionArray = array('status', 'mfa', 'role', 'delete');
	$action = $data['action'];
	if($validate->validateInArray($action, $actionArray, 'action')) {
		
		if($action == 'role') {
			$groupID = $data['groupID'];
			$validate->validateID($groupID, 'groupID');
		}
		
		if($action == 'status') {
			$status = $data['status'];
			$statusArray = array('yes', 'no');
			$validate->validateInArray($status, $statusArray, 'status');
		}
		
		if($action == 'mfa') {
			$mfa = $data['state'];
			$mfaArray = array(0, 1);
			$validate->validateInArray($mfa, $mfaArray, 'mfa');
		}
	}
	
	// Validate userID
	$userID = $data['userID'];
	if($validate->validateID($userID, 'userID')) {
		if($userID == $qls->user_info['id']) {
			$errMsg = 'Cannot administer your own account.';
			array_push($validate->returnData['error'], $errMsg);
		}
	}
	
	// Validate userType
	$userTypeArray = array('active', 'invitation');
	$userType = $data['userType'];
	$validate->validateInArray($userType, $userTypeArray, 'user type');
	
	return;
}

?>
