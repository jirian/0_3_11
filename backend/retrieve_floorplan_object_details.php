<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
//require_once '../includes/path_functions.php';
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
		$objectID = $data['objectID'];
		
		$floorplanObject = $qls->App->objectArray[$objectID];
		$floorplanObjectTemplate = $qls->App->templateArray[$floorplanObject['template_id']];
		
		$peerArray = array();
		$query = $qls->SQL->select('*', 'app_object_peer', array('a_id' => array('=', $objectID)));
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($peerArray, $row);
		}
		
		$type = $floorplanObjectTemplate['templateType'];
		$peerIDArray = array();
		$objPortArray = array();
		error_log('Debug: '.$type);
		if($type == 'walljack' or $type == 'wap') {
			$trunkable = true;
			$trunkFlatPath = count($peerArray) ? 'Yes' : 'No';
			
			foreach($peerArray as $peer) {
				$peerID = $peer['b_id'];
				$peerTemplateID = $qls->App->objectArray[$peerID]['template_id'];
				$peerFace = $peer['b_face'];
				$peerDepth = $peer['b_depth'];
				$peerPort = $peer['b_port'];
				$peerCompatibility = $qls->App->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
				error_log(json_encode('peerTemplateID'.$peerTemplateID.'peerFace'.$peerFace.'peerDepth'.$peerDepth));
				$peerPortLayoutX = $peerCompatibility['portLayoutX'];
				$peerPortLayoutY = $peerCompatibility['portLayoutY'];
				$peerPortTotal = $peerPortLayoutX * $peerPortLayoutY;
				$peerPortNameFormatJSON = $peerCompatibility['portNameFormat'];
				$peerPortNameFormat = json_decode($peerPortNameFormatJSON, true);
				$peerPortName = $qls->App->generatePortName($peerPortNameFormat, $peerPort, $peerPortTotal);
				
				$peerID = '4-'.$peerID.'-'.$peerFace.'-'.$peerDepth.'-'.$peerPort;
				$objPort = array(
					'peerEntryID' => $peer['id'],
					'portName' => $peerPortName
				);
				
				array_push($peerIDArray, $peerID);
				array_push($objPortArray, $objPort);
			}
		} else if($type == 'device') {
			$trunkable = false;
			$trunkFlatPath = 'N/A';
		}
		
		$returnData = array(
			'name' => $floorplanObject['name'],
			'trunkable' => $trunkable,
			'peerIDArray' => $peerIDArray,
			'objPortArray' => $objPortArray,
			'trunkFlatPath' => $trunkFlatPath
		);
		
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	
	//Validate object ID
	$validate->validateObjectID($data['objectID']);
	
	return;
}
?>
