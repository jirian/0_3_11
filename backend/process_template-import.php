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
	$dataJSON = $_POST['data'];
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		$templateID = $data['templateID'];
		
		// POST Request
		$POSTData = array('data' => $dataJSON);

		$ch = curl_init('https://patchcablemgr.com/public/template-import.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");
		 
		// Submit the POST request
		$result = curl_exec($ch);
		error_log('Debug: '.$result);
		
		// Collect POST response
		$importData = json_decode($result, true);
		
		//Check for request errors.
		if(curl_errno($ch)) {
			array_push($validate->returnData['error'], curl_error($ch));
			echo json_encode($validate->returnData);
			return;
		}
		
		// Close cURL session handle
		curl_close($ch);
		
		// Check for errors
		if(isset($importData['error'])) {
			foreach($importData['error'] as $serverErrMsg) {
				$errMsg = 'Server Error - '.$serverErrMsg;
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		// Retreive template front image
		if($importData['success']['template']['frontImage']) {
			getImgFile($importData['success']['template']['frontImage'], $validate);
		}
		
		// Retreive template rear image
		if($importData['success']['template']['rearImage']) {
			getImgFile($importData['success']['template']['frontImage'], $validate);
		}
		
		// Check for errors
		if(count($validate->returnData['error'])) {
			echo json_encode($validate->returnData);
			return;
		}
		
		// Obtain default category information.
		// This is needed to categorize the imported template.
		$importedCategoryName = $importData['success']['category']['name'];
		$importedCategoryColor = $importData['success']['category']['color'];
		$categoryFound = false;
		foreach($qls->App->categoryArray as $category) {
			if(strtolower($category['name']) == strtolower($importedCategoryName)) {
				$categoryID = $category['id'];
				$categoryFound = true;
			}
		}
		
		if(!$categoryFound) {
			$qls->SQL->insert('app_object_category', array('name', 'color', 'defaultOption'), array($importedCategoryName, $importedCategoryColor, 0));
			$categoryID = $qls->SQL->insert_id();
		}
		
		// Format the template data received from patchcablemgr.com to be inserted into the DB
		$templateNameArray = array();
		$templateValueArray = array();
		foreach($importData['success']['template'] as $name => $value) {
			if($name != 'id') {
				array_push($templateNameArray, $name);
				
				if($name == 'templateCategory_id') {
					
					array_push($templateValueArray, $categoryID);
					
				} else if($name == 'templateName') {
					
					$uniqueName = $qls->App->findUniqueName(null, 'template', $value);
					if($uniqueName == false) {
						$errMsg = 'Unable to find unique template name.';
						array_push($validate->returnData['error'], $errMsg);
						echo json_encode($validate->returnData);
						return;
					} else {
						array_push($templateValueArray, $uniqueName);
					}
					
				// Necessary for transition from 0.1.3 to 0.1.4
				} else if($name == 'templatePartitionData') {
					
					$partitionData = json_decode($value, true);
					foreach($partitionData as &$face) {
						$qls->App->alterTemplatePartitionDataLayoutName($face);
						$qls->App->alterTemplatePartitionDataDimensionUnits($face);
					}
					$value = json_encode($partitionData);
					array_push($templateValueArray, $value);
					
				} else {
					array_push($templateValueArray, $value);
				}
			}
		}
		
		// Insert template data into DB
		$qls->SQL->insert('app_object_templates', $templateNameArray, $templateValueArray);
		$newTemplateID = $qls->SQL->insert_id();
		
		// Loop through template compatibility data and insert into DB
		foreach($importData['success']['templateCompatibilities'] as $templateCompatibility) {
			$templateCompatibilityNameArray = array();
			$templateCompatibilityValueArray = array();
			foreach($templateCompatibility as $name => $value) {
				if($name != 'id') {
					array_push($templateCompatibilityNameArray, $name);
					if($name == 'template_id') {
						array_push($templateCompatibilityValueArray, $newTemplateID);
					} else {
						array_push($templateCompatibilityValueArray, $value);
					}
				}
			}
			
			// Necessary for transition from 0.1.3 to 0.1.4
			if($templateCompatibility['partitionType'] == 'Enclosure') {
				if(!isset($templateCompatibility['encTolerance'])) {
					array_push($templateCompatibilityNameArray, 'encTolerance');
					array_push($templateCompatibilityValueArray, 'Loose');
				}
			}
			
			// Insert template compatibility data into DB
			$qls->SQL->insert('app_object_compatibility', $templateCompatibilityNameArray, $templateCompatibilityValueArray);
		}
		
		// Log action in history
		$actionString = 'Imported template: <strong>'.$uniqueName.'</strong>';
		$qls->App->logAction(1, 1, $actionString);
		
		$validate->returnData['success'] = 'This template has been imported to the '.$importedCategoryName.' category.';
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	//Validate template ID
	$validate->validateID($data['templateID'], 'template ID');
}

function getImgFile($filename, &$validate) {
	$url = 'https://patchcablemgr.com/images/templateImages/'.$filename;
	$filePath = '../images/templateImages/'.$filename;
	if(!file_exists($filePath)) {
		$imgFile = fopen($filePath, "x+");
		if ($imgFile == FALSE){
			$errMsg = 'Cannot open template image file for saving.';
			array_push($validate->returnData['error'], $errMsg);
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $imgFile);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		curl_exec($ch);
		if(curl_errno($ch)) {
			array_push($validate->returnData['error'], curl_error($ch));
		}

		curl_close($ch);
		fclose($imgFile);
	}
}
?>
