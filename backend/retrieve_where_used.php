<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$templateID = $data['templateID'];
		$html = '';
		
		if(count($qls->App->objectByTemplateArray[$templateID])) {
			foreach($qls->App->objectByTemplateArray[$templateID] as $objID) {
				$objName = $qls->App->objectArray[$objID]['nameString'];
				$objBox = $qls->App->wrapObject($objID, $objName);
				$html .= $objBox;
			}
		} else {
			$html = 'Not in use.';
		}
		
		$validate->returnData['success'] = $html;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate template ID
	$validate->validateObjectID($data['templateID']);
	
	return;
}

?>
