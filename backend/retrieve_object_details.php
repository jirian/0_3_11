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
		$objectFace = $data['objFace'];
		$cabinetFace = $data['cabinetFace'];
		$objectID = $data['objID'];
		$partitionDepth = $data['partitionDepth'];
		
		if ($data['page'] == 'build') {
			//Retreive object info
			$objectInfo = $qls->App->objectArray[$objectID];
			$templateID = $objectInfo['template_id'];
			$objectName = $objectInfo['name'];
		} else {
			$templateID = $objectID;
			$objectName = $trunkedTo = 'N/A';
		}
		
		// Retrieve template info
		$templateInfo = $qls->App->templateArray[$templateID];
		$categoryID = $templateInfo['templateCategory_id'];
		
		// Retrieve category info
		$categoryName = $qls->App->categoryArray[$categoryID]['name'];
		
		// Compile list of categories to be used for template category selection
		$categoryArray = array();
		foreach($qls->App->categoryArray as $categoryEntry) {
			array_push($categoryArray, array($categoryEntry['id'] => $categoryEntry['name']));
		}
		
		// Compile list of enclosure tolerances
		$encToleranceArray = array('Strict' => 'Strict', 'Loose' => 'Loose');
		
		// Retrieve port orientation info
		$portOrientationArray = array();
		foreach($qls->App->portOrientationArray as $portOrientationArrayEntry) {
			array_push($portOrientationArray, array($portOrientationArrayEntry['id'] => $portOrientationArrayEntry['name']));
		}
		
		//Retrieve partition info
		$partitionData = $qls->App->compatibilityArray[$templateID][$objectFace][$partitionDepth];
		$partitionType = $partitionData['partitionType'];
		$portNameFormat = $portTotal = false;
		$peerIDArray = array();
		
		if($partitionType == 'Connectable'){
			require_once '../includes/path_functions.php';
			$portNameFormat = json_decode($partitionData['portNameFormat'], true);
			$portLayoutX = $partitionData['portLayoutX'];
			$portLayoutY = $partitionData['portLayoutY'];
			error_log('Debug: '.$portLayoutX.'x'.$portLayoutY);
			$portTotal = $portLayoutX * $portLayoutY;
			$portIndexFirst = 0;
			$portIndexLast = $portTotal - 1;
			$portNameFirst = $qls->App->generatePortName($portNameFormat, $portIndexFirst, $portTotal);
			if($portTotal > 1) {
				$portNameLast = '&nbsp;&#8209;&nbsp;'.$qls->App->generatePortName($portNameFormat, $portIndexLast, $portTotal);
			} else {
				$portNameLast = '';
			}
			$portRange = $portNameFirst.$portNameLast;
			$portProperties = getPortProperties($qls);
			$portType = $portProperties['portType'][$partitionData['portType']];
			$portOrientationID = $partitionData['portOrientation'];
			$portOrientationName = $portOrientationID ? $qls->App->portOrientationArray[$portOrientationID]['name'] : 'N/A';
			$mediaType = $partitionData['partitionFunction'] == 'Passive' ? $portProperties['mediaType'][$partitionData['mediaType']] : 'N/A';
			$encTolerance = 'N/A';
			
			// Get peer information
			$peerIsFloorplanObject = false;
			if($peerData = $qls->App->peerArray[$objectID][$objectFace][$partitionDepth]) {
				$peerID = $peerData['peerID'];
				$peerFace = $peerData['peerFace'];
				$peerDepth = $peerData['peerDepth'];
				$peerID = '3-'.$peerID.'-'.$peerFace.'-'.$peerDepth.'-0';
				array_push($peerIDArray, $peerID);
				$peerIsFloorplanObject = $peerData['floorplanPeer'] ? true : false;
			}
			
			// Set object trunking properties
			if($peerIsFloorplanObject) {
				$trunkFlatPath = 'Floorplan object(s)';
			} else {
				$trunkFlatPath = buildTrunkFlatPath($objectID, $objectFace, $partitionDepth, $qls);
			}
			$trunkable = true;
		} else if($partitionType == 'Enclosure'){
			$encTolerance = $partitionData['encTolerance'];
			$portRange = $portType = $portOrientationName = $mediaType = $trunkFlatPath = 'N/A';
			$trunkable = $portOrientationID = false;
		} else {
			// Generic partition... these won't be in the compatibility table so catch them with an else
			$partitionType = $portRange = $portType = $portOrientationName = $mediaType = $trunkFlatPath = $encTolerance = 'N/A';
			$trunkable = $portOrientationID = false;
		}
		
		if($templateInfo['templateType'] == 'Standard') {
			$mountConfig = $templateInfo['templateMountConfig'] == 0 ? '2-Post' : '4-Post';
			$RUSize = $templateInfo['templateRUSize'];
		} else if($templateInfo['templateType'] == 'Insert'){
			$mountConfig = $RUSize = 'N/A';
		}
		
		$templateImgFilename = $objectFace == 0 ? $templateInfo['frontImage'] : $templateInfo['rearImage'];
		
		// Determine image dimensions
		$RUHeight = 25;
		$hUnits = $templateInfo['templateHUnits'];
		$vUnits = $templateInfo['templateVUnits'];
		$encX = $templateInfo['templateEncLayoutX'];
		$encY = $templateInfo['templateEncLayoutY'];
		if($templateInfo['templateType'] == 'Standard') {
			$templateImgHeight = $RUSize * $RUHeight;
			$templateImgWidth = 100;
		} else if($templateInfo['templateType'] == 'Insert'){
			$templateImgHeight = round((($RUHeight / 2) * $vUnits) / $encY);
			$templateImgWidth = round((($hUnits / 24) * 100) * (1 / $encX));
		}
		
		// Determine image state
		if($templateImgFilename !== null) {
			$templateImgExists = true;
			$templateImgAction = 'update';
			$templateImgPath = '/images/templateImages/'.$templateImgFilename;
		} else {
			$templateImgExists = false;
			$templateImgAction = 'upload';
			$templateImgPath = '';
		}
		
		// Compile response data
		$returnData = array(
			'objectName' => $objectName,
			'templateName' => $templateInfo['templateName'],
			'trunkedTo' => $trunkedTo,
			'categoryName' => $categoryName,
			'categoryArray' => $categoryArray,
			'categoryID' => $categoryID,
			'objectType' => $templateInfo['templateType'],
			'RUSize' => $RUSize,
			'function' => $templateInfo['templateFunction'],
			'mountConfig' => $mountConfig,
			'partitionType' => $partitionType,
			'portRange' => $portRange,
			'portTotal' => $portTotal,
			'portNameFormat' => $portNameFormat,
			'portType' => $portType,
			'portOrientationArray' => $portOrientationArray,
			'portOrientationName' => $portOrientationName,
			'portOrientationID' => $portOrientationID,
			'mediaType' => $mediaType,
			'encToleranceArray' => $encToleranceArray,
			'encTolerance' => $encTolerance,
			'templateImgExists' => $templateImgExists,
			'templateImgAction' => $templateImgAction,
			'templateImgPath' => $templateImgPath,
			'templateImgHeight' => $templateImgHeight,
			'templateImgWidth' => $templateImgWidth,
			'trunkable' => $trunkable,
			'trunkFlatPath' => $trunkFlatPath,
			'peerIDArray' => $peerIDArray
		);
		
		$validate->returnData['success'] = $returnData;
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	//Validate page name
	$validate->validatePageName($data['page']);
	
	//Validate object ID
	$validate->validateObjectID($data['objID']);
	
	//Validate object face
	$validate->validateObjectFace($data['objFace']);

	//Validate partition depth
	$validate->validatePartitionDepth($data['partitionDepth']);
	
	return;
}

function getPortProperties(&$qls){
	$portProperties = array();
	
	$query = $qls->SQL->select('*', 'shared_object_portType');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['portType'][$row['value']] = $row['name'];
	}
	
	$query = $qls->SQL->select('*', 'shared_object_portOrientation');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['portOrientation'][$row['value']] = $row['name'];
	}
	
	$query = $qls->SQL->select('*', 'shared_mediaType');
	while($row = $qls->SQL->fetch_assoc($query)){
		$portProperties['mediaType'][$row['value']] = $row['name'];
	}
	return $portProperties;
}
?>
