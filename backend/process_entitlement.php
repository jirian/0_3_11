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
		
		$action = strtolower($data['action']);
		
		if($action == 'update') {
			
			$entitlementID = strtolower($data['entitlementID']);
			$qls->SQL->update('app_organization_data', array('entitlement_id' => $entitlementID), array('id' => array('=', 1)));
			$qls->App->updateEntitlementData($entitlementID);
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
			
		} else if($action == 'check') {
			
			$entitlementID = $qls->App->entitlementArray['id'];
			$qls->App->updateEntitlementData($entitlementID);
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
			
		} else if($action == 'cancel') {
			$qls->App->cancelEntitlement();
			$qls->App->gatherEntitlementData();
			$validate->returnData['success'] = $qls->App->entitlementArray;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$actionsArray = array('update', 'check', 'cancel');
	$action = strtolower($data['action']);
	
	//Validate action
	if($validate->validateInArray($action, $actionsArray, 'action')) {
		
		if($action == 'update') {
			
			// Validate entitlement ID
			$entitlementID = strtolower($data['entitlementID']);
			$validate->validateSHA($entitlementID, 'Invalid entitlement ID.');
		}
	}
}

?>
