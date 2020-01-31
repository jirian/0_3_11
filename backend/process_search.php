<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	if(isset($_GET['term'])) {
		$term = $_GET['term'];
		$autoCompleteData = array();
			
		// Objects
		foreach($qls->App->objectArray as $object) {
			if(strpos(strtolower($object['name']), strtolower($term)) !== false) {
				$obj = $qls->App->objectArray[$object['id']];
				$label = 'Explore - '.$obj['nameString'];
				$objID = $object['id'];
				$parentID = $object['env_tree_id'];
				$value = 'explore-'.$objID.'-'.$parentID;
				array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
			}
		}
		
		// Templates
		foreach($qls->App->templateArray as $template) {
			if(strpos(strtolower($template['templateName']), strtolower($term)) !== false) {
				$templateType = $template['templateType'];
				if($templateType != 'walljack' and $templateType != 'wap' and $templateType != 'device') {
					$label = 'Template - '.$template['templateName'];
					$templateID = $template['id'];
					$value = 'template-'.$templateID;
					array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
				}
			}
		}
		
		// Environment
		foreach($qls->App->envTreeArray as $location) {
			if(strpos(strtolower($location['name']), strtolower($term)) !== false) {
				$locationID = $location['id'];
				$treePathString = $qls->App->buildTreePathString($locationID);
				$label = 'Environment - '.$treePathString;
				$value = 'environment-'.$locationID;
				array_push($autoCompleteData, array('label'=>$label, 'value'=>$value));
			}
		}
		
		echo json_encode($autoCompleteData);
		
	} else if(isset($_GET['select'])) {
		
		$value = $_GET['select'];
		$data = explode('-', $value);
		$appFunction = $data[0];
		$subjectID = $data[1];
		
		if($appFunction == 'explore') {
			$parentID = $data[2];
			header('Location: /explore.php?objID='.$subjectID.'&parentID='.$parentID);
			exit();
		} else if($appFunction == 'template') {
			header('Location: /templates.php?templateID='.$subjectID);
			exit();
		} else if($appFunction == 'environment') {
			header('Location: /environment.php?nodeID='.$subjectID);
			exit();
		}
		
	} else if(isset($_GET['search'])) {
		$searchTerm = $_GET['search'];
		echo 'Search Term: '.$searchTerm.'<br><br>This function is in progress<br>Last updated: 4-May-2019';
	}
}

?>
