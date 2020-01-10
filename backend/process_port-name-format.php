<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		
			// Generate new port name range
			$portNameFormat = $data['portNameFormat'];
			$portTotal = $data['portTotal'];
			$portNameListLong = '';
			$portNameListShort = '';
			
			for($x=0; $x<$portTotal; $x++) {
				$portName = $qls->App->generatePortName($portNameFormat, $x, $portTotal);
				
				if($x < 3) {
					$portNameListShort .= $portName.', ';
				}
				
				if($x < 10) {
					$portNameListLong .= $portName.'<br>';
				}
				
				if($x == 0) {
					$portNameFirst = $portName;
				} else if($x == ($portTotal - 1)) {
					$portNameLast = $portName;
				}
			}
			
			$portNameListShort .= '...';
			$portNameListLong .= '...';
			$portRange = $portNameFirst.'&#8209;'.$portNameLast;
			
			$validate->returnData['success']['portNameListLong'] = $portNameListLong;
			$validate->returnData['success']['portNameListShort'] = $portNameListShort;
			$validate->returnData['success']['portRange'] = $portRange;

	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	$portNameFormat = $data['portNameFormat'];
	$portTotal = $data['portTotal'];
	
	// Validate port total
	if($validate->validatePortTotal($portTotal)) {
		
		// Validate port name format
		$validate->validatePortNameFormat($portNameFormat, $portTotal);
		
	}
	
	return;
}

?>
