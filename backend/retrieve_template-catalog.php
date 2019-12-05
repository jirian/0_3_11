<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

$ch = curl_init('https://patchcablemgr.com/public/template-catalog-data.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");

$responseJSON = curl_exec($ch);
$response = json_decode($responseJSON, true);

if(curl_errno($ch)) {
	echo '<strong>Error:</strong> Unable to contact template catalog server.';
} else {
	$templateArray = $response['templateArray'];
	$categoryArray = $response['categoryArray'];
	$templates = array();
	foreach($templateArray as $template){
		
		$templateID = $template['id'];
		$categoryID = $template['templateCategory_id'];
		$categoryName = $categoryArray[$categoryID]['name'];
		$template['categoryData'] = $categoryArray[$categoryID];
		
		
		if($template['templatePartitionData']) {
			$partitionDataJSON = $template['templatePartitionData'];
			$partitionData = json_decode($partitionDataJSON, true);
			$template['templatePartitionData'] = $partitionData;
			
			foreach($template['templatePartitionData'] as &$face) {
				$qls->App->alterTemplatePartitionDataLayoutName($face);
				$qls->App->alterTemplatePartitionDataDimensionUnits($face);
			}
		}
		
		$templates[$categoryName][$templateID] = $template;
	}
	require_once $_SERVER['DOCUMENT_ROOT'].'/includes/content-build-objects.php';
}

?>
