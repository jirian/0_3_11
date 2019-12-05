<?php

//Retreive categories
$category = array();
$categoryInfo = $qls->SQL->select('*', 'app_object_category');
while ($categoryRow = $qls->SQL->fetch_assoc($categoryInfo)){
	$category[$categoryRow['id']]['name'] = $categoryRow['name'];
	$category[$categoryRow['id']]['color'] = $categoryRow['color'];
}

//Retreive rackable objects
$templates = array();
$results = $qls->SQL->select('*', 'app_object_templates', array('templateCategory_id' => array('<>', null)), 'templateName ASC');
while ($row = $qls->SQL->fetch_assoc($results)){
	$templateID = $row['id'];
	$categoryID = $row['templateCategory_id'];
	$categoryName = $category[$categoryID]['name'];
	$partitionDataArray = json_decode($row['templatePartitionData'], true);
	$templates[$categoryName][$templateID] = $row;
	$templates[$categoryName][$templateID]['templatePartitionData'] = $partitionDataArray;
}
?>