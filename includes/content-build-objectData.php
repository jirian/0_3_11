<?php

//Retreive categories
$category = array();
if($templateCatalog == true) {
	$categoryInfo = $qls->SQL->select('*', 'table_template_object_category');
} else {
	$categoryInfo = $qls->SQL->select('*', 'app_object_category');
}
while ($categoryRow = $qls->SQL->fetch_assoc($categoryInfo)){
	$category[$categoryRow['id']]['name'] = $categoryRow['name'];
	$category[$categoryRow['id']]['color'] = $categoryRow['color'];
}

//Retreive port orientation
$portOrientation = array();
$results = $qls->SQL->select('*', 'shared_object_portOrientation');
while ($row = $qls->SQL->fetch_assoc($results)){
	$portOrientation[$row['id']]['name'] = $row['name'];
}

//Retreive port type
$portType = array();
$results = $qls->SQL->select('*', 'shared_object_portType');
while ($row = $qls->SQL->fetch_assoc($results)){
	$portType[$row['id']]['name'] = $row['name'];
}

//Retreive media type
$mediaType = array();
$results = $qls->SQL->select('*', 'shared_mediaType');
while ($row = $qls->SQL->fetch_assoc($results)){
	$mediaType[$row['value']]['name'] = $row['name'];
}

//Retreive rackable objects
$templates = array();
if($templateCatalog == true) {
	$results = $qls->SQL->select('*', 'table_template_object_templates', array('templateCategory_id' => array('<>', null)), 'templateName ASC');
} else {
	$results = $qls->SQL->select('*', 'app_object_templates', array('templateCategory_id' => array('<>', null)), 'templateName ASC');
}
while ($row = $qls->SQL->fetch_assoc($results)){
	$categoryName = $category[$row['templateCategory_id']]['name'];
	$templates[$categoryName][$row['id']]['id'] = $row['id'];
	$templates[$categoryName][$row['id']]['name'] = $row['templateName'];
	$templates[$categoryName][$row['id']]['categoryID'] = $row['templateCategory_id'];
	$templates[$categoryName][$row['id']]['categoryName'] = $category[$row['templateCategory_id']]['name'];
	$templates[$categoryName][$row['id']]['categoryColor'] = $category[$row['templateCategory_id']]['color'];
	$templates[$categoryName][$row['id']]['type'] = $row['templateType'];
	$templates[$categoryName][$row['id']]['RUSize'] = $row['templateRUSize'];
	$templates[$categoryName][$row['id']]['function'] = $row['templateFunction'];
	$templates[$categoryName][$row['id']]['mountConfig'] = $row['templateMountConfig'];
	$templates[$categoryName][$row['id']]['hUnits'] = $row['templateHUnits'];
	$templates[$categoryName][$row['id']]['vUnits'] = $row['templateVUnits'];
	$templates[$categoryName][$row['id']]['encLayoutX'] = $row['templateEncLayoutX'];
	$templates[$categoryName][$row['id']]['encLayoutY'] = $row['templateEncLayoutY'];
	$templates[$categoryName][$row['id']]['partitionData'] = json_decode($row['templatePartitionData'], true);
}
?>