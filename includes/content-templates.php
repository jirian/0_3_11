<?php

#Category
//$inputCategory = '<option value="" selected>--Select Category--</option>';
//$inputCategory = '';
$categoryList = '';
$results = $qls->SQL->select('*', 'app_object_category');
while ($row = $qls->SQL->fetch_assoc($results)){
	$default = [];
	if ($row['defaultOption'] == 1){
		$default['option'] = 'selected';
		$default['button'] = '*';
	}
	//$inputCategory .= '<option data-value="category'.$row['name'].'" id="categoryOption'.$row['id'].'" value="'.$row['id'].'" '.$default['option'].'>'.$row['name'].'</option>';
	$categoryList .= '<button id="categoryList'.$row['id'].'" type="button" class="category'.$row['name'].' btn-block btn waves-effect waves-light" data-id="'.$row['id'].'" data-name="'.$row['name'].'" data-color="'.$row['color'].'" data-default="'.$row['defaultOption'].'">'.$row['name'].$default['button'].'</button>';
}

function generateOrientation(&$qls){
	#Orientation
	$inputOrientation = '';
	$results = $qls->SQL->select('*', 'shared_object_portOrientation');
	while ($row = $qls->SQL->fetch_assoc($results)){
		$default = '';
		if ($row['defaultOption'] == 1){
			$default = 'checked';
		}
		$inputOrientation .= '<div class="radio"><input class="objectPortOrientation" data-value="'.$row['name'].'" type="radio" name="objectPortOrientationRadio" id="portOrientationRadio'.$row['name'].'" value="'.$row['value'].'" '.$default.'><label for="portOrientationRadio'.$row['name'].'">'.$row['name'].'</label></div>';
	}
	return $inputOrientation;
}

function generatePortType(&$qls){
	#PortType
	$inputPortType = '';
	$results = $qls->SQL->select('*', 'shared_object_portType');
	while ($row = $qls->SQL->fetch_assoc($results)){
		$default = '';
		if ($row['defaultOption'] == 1){
			$default = 'selected';
		}
		$inputPortType .= '<option data-value="'.$row['name'].'" value="'.$row['value'].'" '.$default.'>'.$row['name'].'</option>';
	}
	return $inputPortType;
}

function generateMediaType(&$qls){
	#MediaType
	$inputMediaType = '';
	$results = $qls->SQL->select('*', 'shared_mediaType', array('display' => array('=', 1)));
	while ($row = $qls->SQL->fetch_assoc($results)){
		$default = '';
		if ($row['defaultOption'] == 1){
			$default = 'selected';
		}
		$inputMediaType .= '<option data-value="'.$row['name'].'" value="'.$row['value'].'" '.$default.'>'.$row['name'].'</option>';
	}
	return $inputMediaType;
}
?>
