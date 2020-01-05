<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

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
		switch($data['action']) {
				
			case 'accept':
				$userID = $qls->user_info['id'];
				$userGroupID = $qls->user_info['group_id'];
				$code = $data['invitationCode'];

				$queryInvitation = $qls->SQL->select('*', 'invitations', array('to_id' => array('=', $userID), 'AND', 'code' => array('=', $code), 'AND', 'used' => array('=', 0)));
				if($qls->SQL->num_rows($queryInvitation)) {
					if(canDelete($qls, $validate)) {
						$invitation = $qls->SQL->fetch_assoc($queryInvitation);
						$qls->SQL->update('users', array('group_id' => 5, 'org_id' => $invitation['org_id']), array('id' => array('=', $qls->user_info['id'])));
						$qls->SQL->update('invitations', array('used' => 1), array('id' => array('=', $invitation['id'])));
						$validate->returnData['success'] = 'You have joined the new organization.';
					} else {
						$errorMsg = 'You are the only administrator... your team needs you!';
						array_push($validate->returnData['error'], $errorMsg);
					}
				} else {
					$errorMsg = 'Invitation code is invalid, does not exist, or has already been used.';
					array_push($validate->returnData['error'], $errorMsg);
				}
				break;
				
			case 'decline':
				$code = $data['invitationCode'];
				$qls->SQL->delete('invitations', array('id' => array('=', $code)));
				break;

			case 'revert':
				if(canDelete($qls, $validate)) {
					$originalOrgID = $qls->user_info['original_org_id'];
					$originalGroupID = $qls->user_info['original_group_id'];
					$userID = $qls->user_info['id'];
					$qls->SQL->update('users', array('org_id' => $originalOrgID, 'group_id' => $originalGroupID), array('id' => array('=', $userID)));
					$validate->returnData['success'] = 'You have joined the new organization.';
				} else {
					$errorMsg = 'You are the only administrator... your team needs you!';
					array_push($validate->returnData['error'], $errorMsg);
				}
				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$actionArray = array('accept', 'decline', 'revert');
	$action = $data['action'];
	
	if($validate->validateInArray($action, $actionArray, 'action')) {
		if($action == 'accept' or $action == 'decline') {
			$invitationCode = $data['invitationCode'];
			'Invalid invitation Code.';
			$validate->validateSHA($invitationCode, $errMsg);
		}
	}
	
	return;
}

function canDelete(&$qls, &$validate){
	$userGroupID = $qls->user_info['group_id'];
	$userOrgID = $qls->user_info['org_id'];
	$administratorCount = 0;
	$queryUsers = $qls->SQL->select('*', 'users', array('org_id' => array('=', $userOrgID)));
	$userCount = $qls->SQL->num_rows($queryUsers);
	while($row = $qls->SQL->fetch_assoc($queryUsers)) {
		if($row['groupID'] == 3) {
			$administratorCount++;
		}
	}

	if($userGroupID == 3 and ($userCount > 1 and $administratorCount < 2)) {
		return false;
	} else {
		return true;
	}
}
?>
