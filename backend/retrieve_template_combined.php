<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

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
		
		$combinedTemplate = $qls->App->combinedTemplateArray[$templateID];
		$templateName = $combinedTemplate['templateName'];
		$templateCategoryID = $combinedTemplate['templateCategory_id'];
		$templateCategory = $qls->App->categoryArray[$templateCategoryID];
		$templateCategoryName = $templateCategory['name'];
		$categoryArray = array();
		foreach($qls->App->categoryArray as $categoryEntry) {
			array_push($categoryArray, array($categoryEntry['id'] => $categoryEntry['name']));
		}
		
		// Compile response data
		$returnData = array(
			'templateName' => $templateName,
			'categoryName' => $templateCategoryName,
			'categoryArray' => $categoryArray,
			'categoryID' => $templateCategoryID
		);
		
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate object ID
	$validate->validateObjectID($data['templateID']);
	
	return;
}
?>
