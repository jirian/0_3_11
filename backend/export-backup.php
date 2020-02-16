<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('administrator.php');

// Template Compatibility
$templateEnclosureArray = array();
$query = $qls->SQL->select('*', 'app_object_compatibility');
while($row = $qls->SQL->fetch_assoc($query)) {
	$templateID = $row['template_id'];
	$templateFace = $row['side'];
	$templateDepth = $row['depth'];
	
	if($row['partitionType'] == 'Enclosure') {
		if(!array_key_exists($templateID, $templateEnclosureArray)) {
			$templateEnclosureArray[$templateID] = array(
				$templateFace => array(
					$templateDepth => $row
			));
		} else if(!array_key_exists($templateFace, $templateEnclosureArray[$templateID])) {
			$templateEnclosureArray[$templateID][$templateFace] = array(
				$templateDepth => $row
			);
		} else {
			$templateEnclosureArray[$templateID][$templateFace][$templateDepth] = $row;
		}
	}
}

// Cabinet Adjacencies
$envAdjArray = array();
$query = $qls->SQL->select('*', 'app_cabinet_adj');
while($row = $qls->SQL->fetch_assoc($query)) {
	$envAdjArray[$row['left_cabinet_id']]['right'] = $row['right_cabinet_id'];
	$envAdjArray[$row['right_cabinet_id']]['left'] = $row['left_cabinet_id'];
}

// Objects
$objectArray = array();
$insertArray = array();
$query = $qls->SQL->select('*', 'app_object');
while($row = $qls->SQL->fetch_assoc($query)) {
	if($row['parent_id'] == 0) {
		$objectArray[$row['id']] = $row;
	} else {
		$parentID = $row['parent_id'];
		$parentFace = $row['parent_face'];
		$parentDepth = $row['parent_depth'];
		$encX = $row['insertSlotX'];
		$encY = $row['insertSlotY'];
		
		if(!array_key_exists($parentID, $insertArray)) {
			$insertArray[$parentID] = array(
				$parentFace => array(
					$parentDepth => array(
						$encX => array(
							$encY => null
			))));
		} else if(!array_key_exists($parentFace, $insertArray[$parentID])) {
			$insertArray[$parentID][$parentFace] = array(
				$parentDepth => array(
					$encX => array(
						$encY => null
			)), 'enclosureCount' => 0);
		} else if(!array_key_exists($parentDepth, $insertArray[$parentID][$parentFace])) {
			$insertArray[$parentID][$parentFace][$parentDepth] = array(
				$encX => array(
					$encY => null
			));
		} else if(!array_key_exists($encX, $insertArray[$parentID][$parentFace][$parentDepth])) {
			$insertArray[$parentID][$parentFace][$parentDepth][$encX] = array(
				$encY => null
			);
		}
		
		$insertArray[$parentID][$parentFace]['enclosureCount'] = $insertArray[$parentID][$parentFace]['enclosureCount'] + 1;
		$insertArray[$parentID][$parentFace][$parentDepth][$encX][$encY] = $row;
	}
}
		
// Open ZIP File
$zip = new ZipArchive();
$zipFilename = $_SERVER['DOCUMENT_ROOT'].'/userDownloads/export.zip';
if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
    die('Cannot open zip file.');
}

// Create .csv files
createCabinets($qls);
createCablePaths($qls);
createObjects($qls, $objectArray);
createObjectInserts($qls, $objectArray, $insertArray, $templateEnclosureArray);
createTemplates($qls);
createCategories($qls);
createConnections($qls);
createTrunks($qls);
createVersion();

// Add database data
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Categories.csv', '01 - Categories.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Templates.csv', '02 - Templates.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinets.csv', '03 - Cabinets.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Cable Paths.csv', '04 - Cabinet Cable Paths.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Objects.csv', '05 - Cabinet Objects.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Object Inserts.csv', '06 - Object Inserts.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Connections.csv', '07 - Connections.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Trunks.csv', '08 - Trunks.csv');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Version.txt', 'Version.txt');
$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/userDownloads/README.txt', 'README.txt');

// Identify template images in use
$templateImageArray = array();
foreach($qls->App->templateArray as $template) {
	if($template['frontImage']) {
		array_push($templateImageArray, $template['frontImage']);
	}
	if($template['rearImage']) {
		array_push($templateImageArray, $template['rearImage']);
	}
}

// Add template images
if ($templateImageDir = opendir($_SERVER['DOCUMENT_ROOT'].'/images/templateImages/')) {
	$zip->addEmptyDir('templateImages');
	while (false !== ($templateImageFile = readdir($templateImageDir))) {
		if (in_array($templateImageFile, $templateImageArray)) {
			$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/images/templateImages/'.$templateImageFile, 'templateImages/'.$templateImageFile);
		}
	}
	closedir($templateImageDir);
} else {
	die('Could not open template image directory.');
}

// Identify flooplan images in use
$floorplanImageArray = array();
foreach($qls->App->envTreeArray as $env) {
	if($env['floorplan_img']) {
		array_push($floorplanImageArray, $env['floorplan_img']);
	}
}

// Add floorplan images
if ($floorplanImageDir = opendir($_SERVER['DOCUMENT_ROOT'].'/images/floorplanImages/')) {
	$zip->addEmptyDir('floorplanImages');
	while (false !== ($floorplanImageFile = readdir($floorplanImageDir))) {
		if (in_array($floorplanImageFile, $floorplanImageArray)) {
			$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/images/floorplanImages/'.$floorplanImageFile, 'floorplanImages/'.$floorplanImageFile);
		}
	}
	closedir($floorplanImageDir);
} else {
	die('Could not open floorplan image directory.');
}

$zip->close();

$yourfile = $_SERVER['DOCUMENT_ROOT'].'/userDownloads/export.zip';

$file_name = basename($yourfile);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename='.$file_name);
header('Content-Length: '.filesize($yourfile));

readfile($yourfile);

unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Categories.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Templates.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinets.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Cable Paths.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Objects.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Object Inserts.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Connections.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Trunks.csv');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Version.txt');
unlink($_SERVER['DOCUMENT_ROOT'].'/userDownloads/export.zip');

exit;

function createCabinets(&$qls){
	$fileCabinets = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinets.csv', 'w');

	$csvHeader = array(
		'Name',
		'Type',
		'RU Size',
		'RU Orientation',
		'Adj Left',
		'Adj Right',
		'**Floorplan Image'
	);

	fputcsv($fileCabinets,$csvHeader);

	$csvArray = array();
	foreach($qls->App->envTreeArray as $location) {
		$size = $location['type'] == 'cabinet' ? $location['size'] : '';
		if($location['type'] == 'cabinet') {
			$size = $location['size'];
			$orientation = ($location['ru_orientation'] == 0) ? 'BottomUp' : 'TopDown';
		} else {
			$size = '';
			$orientation = '';
		}
		
		$floorplanImg = $location['type'] == 'floorplan' ? $location['floorplan_img'] : '';
		$adjLeft = isset($envAdjArray[$location['id']]) ? $qls->App->envTreeArray[$envAdjArray[$location['id']]['left']]['nameString'] : '';
		$adjRight = isset($envAdjArray[$location['id']]) ? $qls->App->envTreeArray[$envAdjArray[$location['id']]['right']]['nameString'] : '';
		$name = $location['nameString'];
		$line = array(
			$name,
			$location['type'],
			$size,
			$orientation,
			$adjLeft,
			$adjRight,
			$floorplanImg
		);
		$csvArray[$location['nameString']] = $line;
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileCabinets, $line);
	}
	
	fclose($fileCabinets);
}

function createCablePaths(&$qls){
	$fileCabinetCablePaths = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Cable Paths.csv', 'w');

	$csvHeader = array(
		'Cabinet A',
		'Cabinet B',
		'Distance (m.)',
		'Notes'
	);
	fputcsv($fileCabinetCablePaths, $csvHeader);

	$csvArray = array();
	$query = $qls->SQL->select('*', 'app_cable_path');
	while($row = $qls->SQL->fetch_assoc($query)) {
		if($row['cabinet_b_id'] != 0) {
			$line = array(
				$qls->App->envTreeArray[$row['cabinet_a_id']]['nameString'],
				$qls->App->envTreeArray[$row['cabinet_b_id']]['nameString'],
				$row['distance']*.001,
				$row['notes']
			);
			$csvArray[$qls->App->envTreeArray[$row['cabinet_a_id']]['nameString']] = $line;
		}
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileCabinetCablePaths, $line);
	}
	
	fclose($fileCabinetCablePaths);
}

function createObjects(&$qls, $objectArray){
	$fileCabinetObjects = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Cabinet Objects.csv', 'w');

	$csvHeader = array(
		'Name',
		'Cabinet',
		'**Template',
		'RU',
		'Cabinet Face',
		'**Flooplan Object X',
		'**Flooplan Object Y'
	);
	fputcsv($fileCabinetObjects, $csvHeader);

	$floorplanObjTemplateArray = array(
		1 => 'walljack',
		2 => 'wap',
		3 => 'device'
	);

	$csvArray = array();
	foreach($objectArray as $object) {
		$templateID = $object['template_id'];
		$floorplanObj = (isset($floorplanObjTemplateArray[$templateID])) ? true : false;
		$name = $object['name'];
		$cabinet = $qls->App->envTreeArray[$object['env_tree_id']]['nameString'];
		$template = $floorplanObj ? $floorplanObjTemplateArray[$templateID] : $qls->App->templateArray[$templateID]['templateName'];
		$RUSize = $floorplanObj ? '' : $qls->App->templateArray[$templateID]['templateRUSize'];
		$topRU = $floorplanObj ? '' : $object['RU'];
		$bottomRU = $floorplanObj ? '' : $topRU - ($RUSize - 1);
		if($floorplanObj) {
			$cabinetFace = '';
		} else {
			if($object['cabinet_front'] != null) {
				$cabinetFace = $object['cabinet_front'] == 0 ? 'Front' : 'Rear';
			} else {
				$cabinetFace = $object['cabinet_rear'] == 0 ? 'Rear' : 'Front';
			}
		}
		$original = $cabinet.'.'.$name;
		$posTop = $floorplanObj ? $object['position_top'] : '';
		$posLeft = $floorplanObj ? $object['position_left'] : '';
		
		$line = array(
			$name,
			$cabinet,
			$template,
			$bottomRU,
			$cabinetFace,
			$posLeft,
			$posTop
		);
		$csvArray[$original] = $line;
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileCabinetObjects, $line);
	}
	
	fclose($fileCabinetObjects);
}

function createObjectInserts(&$qls, $objectArray, $insertArray, $templateEnclosureArray){
	$fileObjectInserts = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Object Inserts.csv', 'w');

	$csvHeader = array(
		'**Object',
		'**Face',
		'**Slot',
		'Insert Name',
		'**Insert Template'
	);
	fputcsv($fileObjectInserts, $csvHeader);

	$csvArray = array();
	foreach($objectArray as $object) {
		$templateID = $object['template_id'];
		
		if(array_key_exists($templateID, $templateEnclosureArray)) {
			$objectID = $object['id'];
			$cabinetID = $object['env_tree_id'];
			$objectName = $object['name'];
			$parentID = $object['parent_id'];
			$parentFace = $object['parent_id'];
			$parentDepth = $object['parent_id'];
			$slotX = $object['insertSlotX'];
			$slotY = $object['insertSlotY'];
			$cabinetNameString = $qls->App->envTreeArray[$cabinetID]['nameString'];
			$objectNameString = $cabinetNameString.'.'.$objectName;
			$objectNameString = $objectNameString;
		
			foreach($templateEnclosureArray[$templateID] as $face=>$templateFace) {
				$faceString = $face == 0 ? 'Front' : 'Rear';
				
				foreach($templateFace as $depth=>$templatePartition) {

					for($y=0; $y<$templatePartition['encLayoutY']; $y++) {
						for($x=0; $x<$templatePartition['encLayoutX']; $x++) {

							$enc = $depth;
							$row = chr($y+65);
							$col = $x+1;
							$slotID = 'Enc'.$enc.'Slot'.$row.$col;
							$line = array(
								$objectNameString,
								$faceString,
								$slotID
							);
							
							if(isset($insertArray[$objectID][$face][$depth][$x][$y])) {
								$insert = $insertArray[$objectID][$face][$depth][$x][$y];
								$insertName = $insert['name'];
								$insertTemplateID = $insert['template_id'];
								$insertTemplateName = $qls->App->templateArray[$insertTemplateID]['templateName'];
								array_push($line, $insertName);
								array_push($line, $insertTemplateName);
							} else {
								array_push($line, '');
								array_push($line, '');
							}
							
							if(!array_key_exists($objectNameString, $csvArray)) {
								$csvArray[$objectNameString] = array();
							}
							
							array_push($csvArray[$objectNameString], $line);
						}
					}
				}
			}
		}
	}

	ksort($csvArray);
	foreach($csvArray as $object) {
		foreach($object as $encSlot) {
			fputcsv($fileObjectInserts, $encSlot);
		}
	}
	
	fclose($fileObjectInserts);
}

function createTemplates(&$qls){
	$fileTemplates = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Templates.csv', 'w');

	$csvHeader = array(
		'Name',
		'Category',
		'**Type',
		'**Function',
		'**RU Size',
		'**Mount Config',
		'**Template Structure'
	);
	fputcsv($fileTemplates, $csvHeader);

	$csvArray = array();
	foreach($qls->App->templateArray as $template) {
		// Skip system generated templates
		$templateID = $template['id'];
		if($templateID > 3) {
			$templateID = $template['id'];
			$templateCategoryID = $template['templateCategory_id'];
			$templateName = $template['templateName'];
			$templateCategoryName = $qls->App->categoryArray[$templateCategoryID]['name'];
			$templateType = $template['templateType'];
			$templateFunction = $template['templateFunction'];
			$templateRUSize = $template['templateRUSize'];
			$templateMountConfig = $template['templateMountConfig'];
			$sizeX = $template['templateEncLayoutX'] ? $template['templateEncLayoutX'] : null;
			$sizeY = $template['templateEncLayoutY'] ? $template['templateEncLayoutY'] : null;
			$parentH = $template['templateHUnits'] ? $template['templateHUnits'] : null;
			$parentV = $template['templateVUnits'] ? $template['templateVUnits'] : null;
			$templateStructure = json_decode($template['templatePartitionData'], true);
			$templateFrontImage = $template['frontImage'] ? $template['frontImage'] : null;
			$templateRearImage = $template['rearImage'] ? $template['rearImage'] : null;
			$templateJSON = json_encode(array(
				'sizeX' => $sizeX,
				'sizeY' => $sizeY,
				'parentH' => $parentH,
				'parentV' => $parentV,
				'frontImage' => $templateFrontImage,
				'rearImage' => $templateRearImage,
				'structure' => $templateStructure,
			));
			if($templateMountConfig !== null) {
				$templateMountConfigString = $templateMountConfig == 0 ? '2-Post' : '4-Post';
			} else {
				$templateMountConfigString = 'N/A';
			}
			
			$line = array(
				$templateName,
				$templateCategoryName,
				$templateType,
				$templateFunction,
				$templateRUSize,
				$templateMountConfigString,
				$templateJSON
			);
			
			$csvArray[$templateName] = $line;
		}
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileTemplates, $line);
	}
	
	fclose($fileTemplates);
}

function createCategories(&$qls){
	$fileCategories = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Categories.csv', 'w');

	$csvHeader = array(
		'Name',
		'Color',
		'Default'
	);
	fputcsv($fileCategories, $csvHeader);

	$csvArray = array();
	foreach($qls->App->categoryArray as $category) {
		$categoryID = $category['id'];
		$categoryName = $category['name'];
		$categoryColor = $category['color'];
		$categoryDefault = $category['defaultOption'] == 1 ? 'X' : '';
		
		$line = array(
			$categoryName,
			$categoryColor,
			$categoryDefault
		);
		
		$csvArray[$categoryName] = $line;
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileCategories, $line);
	}
	
	fclose($fileCategories);
}

function createConnections(&$qls){
	$fileConnections = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Connections.csv', 'w');

	$csvHeader = array(
		'PortA',
		'CableA ID',
		'CableA Connector Type',
		'PortB',
		'CableB ID',
		'CableB Connector Type',
		'Media Type',
		'Length'
	);
	fputcsv($fileConnections, $csvHeader);

	$csvArray = array();
	foreach($qls->App->inventoryAllArray as $connection) {
		$aObjID = $connection['a_object_id'];
		$aObjFace = $connection['a_object_face'];
		$aObjDepth = $connection['a_object_depth'];
		$aObjPort = $connection['a_port_id'];
		$aCode39 = $connection['a_code39'] ? $connection['a_code39'] : 'None';
		$aConnectorID = $connection['a_connector'];
		$aConnector = $aConnectorID ? $qls->App->connectorTypeValueArray[$aConnectorID]['name'] : 'None';
		
		$bObjID = $connection['b_object_id'];
		$bObjFace = $connection['b_object_face'];
		$bObjDepth = $connection['b_object_depth'];
		$bObjPort = $connection['b_port_id'];
		$bCode39 = $connection['b_code39'] == 0 ? 'None' : $connection['b_code39'];
		$bConnectorID = $connection['b_connector'];
		$bConnector = $bConnectorID ? $qls->App->connectorTypeValueArray[$bConnectorID]['name'] : 'None';
		
		if($aObjID) {
			$aObjectName = $qls->App->generateObjectPortName($aObjID, $aObjFace, $aObjDepth, $aObjPort);
		} else {
			$aObjectName = 'None';
		}
		
		if($bObjID) {
			$bObjectName = $qls->App->generateObjectPortName($bObjID, $bObjFace, $bObjDepth, $bObjPort);
		} else {
			$bObjectName = 'None';
		}
		
		$mediaTypeID = $connection['mediaType'];
		$mediaType = $mediaTypeID ? $qls->App->mediaTypeValueArray[$mediaTypeID]['name'] : 'None';
		$length = $connection['length'];
		if($mediaTypeID and $length) {
			$lengthString = $qls->App->calculateCableLength($mediaTypeID, $length, true);
		} else {
			$lengthString = 'None';
		}
		
		$line = array(
			$aObjectName,
			$aCode39,
			$aConnector,
			$bObjectName,
			$bCode39,
			$bConnector,
			$mediaType,
			$lengthString
		);
		
		array_push($csvArray, $line);
	}

	foreach($qls->App->populatedPortAllArray as $port) {
		$objID = $port['object_id'];
		$objFace = $port['object_face'];
		$objDepth = $port['object_depth'];
		$objPort = $port['port_id'];
		$objectName = $qls->App->getPortNameString($objID, $objFace, $objDepth, $objPort);
		
		$line = array(
			$objectName,
			'None',
			'None',
			'None',
			'None',
			'None',
			'None',
			'None'
		);
		
		array_push($csvArray, $line);
	}

	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileConnections, $line);
	}
	
	fclose($fileConnections);
}

function createTrunks(&$qls){
	$fileTrunks = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Trunks.csv', 'w');

	$csvHeader = array(
		'Trunk Peer A',
		'Trunk Peer B'
	);

	fputcsv($fileTrunks,$csvHeader);

	$csvArray = array();
	$objectPeerArray = array();
	$floorplanPeerArray = array();
	
	foreach($qls->App->peerArray as $objAID => $obj) {
		
		$objectA = $qls->App->objectArray[$objAID];
		$objATemplateID = $objectA['template_id'];
		$objATemplate = $qls->App->templateArray[$objATemplateID];
		$objATemplateType = $objATemplate['templateType'];
		
		array_push($objectPeerArray, $objAID);
		foreach($obj as $objAFace => $face) {
			
			foreach($face as $objADepth => $partition) {
				
				if($partition['floorplanPeer']) {
					
					foreach($partition['peerArray'] as $objBID => $peerObj) {
						
						foreach($peerObj as $objBFace => $peerFace) {
							foreach($peerFace as $objBDepth => $peerPartition) {
								$floorplanPeerArrayEntryArray = array($objAID.'-'.$objAFace.'-'.$objADepth, $objBID.'-'.$objBFace.'-'.$objBDepth);
								if($objBID < $objAID) {
									$floorplanPeerArrayEntryArray = array_reverse($floorplanPeerArrayEntryArray);
								}
								$floorplanPeerArrayEntry = implode('-',$floorplanPeerArrayEntryArray);
								
								if(!in_array($floorplanPeerArrayEntry, $floorplanPeerArray)) {
									array_push($floorplanPeerArray, $floorplanPeerArrayEntry);
									
									// Gather objB details
									$objectB = $qls->App->objectArray[$objBID];
									$objBTemplateID = $objectB['template_id'];
									$objBCompatibility = $qls->App->compatibilityArray[$objBTemplateID][$objBFace][$objBDepth];
									$objBTemplateType = $objBCompatibility['templateType'];
									$objBPortNameFormat = json_decode($objBCompatibility['portNameFormat'], true);
									$objBPortTotal = $objBCompatibility['portTotal'];
									
									// Gather objA details
									$objACompatibility = $qls->App->compatibilityArray[$objATemplateID][$objAFace][$objADepth];
									$objAPortNameFormat = json_decode($objACompatibility['portNameFormat'], true);
									$objAPortTotal = $objACompatibility['portTotal'];
									
									foreach($peerPartition as $portArray) {
										$walljackPortID = $portArray[0];
										$peerPortID = $portArray[1];
										if($objATemplateType == 'walljack' or $objBTemplateType == 'walljack') {
											if($objATemplateType == 'walljack') {
												$portName = $qls->App->generatePortName($objBPortNameFormat, $peerPortID, $objBPortTotal);
												$objAPort = $portName.'('.$walljackPortID.')';
												$objBPort = $portName;
											} else {
												$portName = $qls->App->generatePortName($objAPortNameFormat, $peerPortID, $objAPortTotal);
												$objBPort = $portName.'('.$walljackPortID.')';
												$objAPort = $portName;
											}
										} else {
											$objAPort = $qls->App->generatePortName($objAPortNameFormat, $peerPortID, $objAPortTotal);
											$objBPort = $qls->App->generatePortName($objBPortNameFormat, $peerPortID, $objBPortTotal);
										}
										
										$objectAName = $qls->App->objectArray[$objAID]['nameString'];
										$objectAPortName = $objectAName.'.'.$objAPort;
										$objectBName = $qls->App->objectArray[$objBID]['nameString'];
										$objectBPortName = $objectBName.'.'.$objBPort;
										$line = array(
											$objectAPortName,
											$objectBPortName
										);
										$csvArray[$objectAName.$walljackPortID] = $line;
									}
								}
							}
						}
					}
				} else {
					$objBID = $partition['peerID'];
					if(!in_array($objBID, $objectPeerArray)) {
						$objBFace = $partition['peerFace'];
						$objBDepth = $partition['peerDepth'];
						
						$objectB = $qls->App->objectArray[$objBID];
						$objBTemplateID = $objectB['template_id'];
						$objBTemplate = $qls->App->templateArray[$objBTemplateID];
						$objBTemplateType = $objBTemplate['templateType'];
						
						// Create an array of data for each peer
						$portNumberArray = array(
							'a' => array(
								'templateID' => $objATemplateID,
								'face' => $objAFace,
								'partition' => $objADepth
							),
							'b' => array(
								'templateID' => $objBTemplateID,
								'face' => $objBFace,
								'partition' => $objBDepth
							)
						);
						
						// Generate first and last port names for each peer
						foreach($portNumberArray as &$peerObject) {
							$peerObjectTemplateID = $peerObject['templateID'];
							$peerObjectFace = $peerObject['face'];
							$peerObjectPartition = $peerObject['partition'];
							$compatibility = $qls->App->compatibilityArray[$peerObjectTemplateID][$peerObjectFace][$peerObjectPartition];
							$portNameFormat = json_decode($compatibility['portNameFormat'], true);
							$portTotal = $compatibility['portTotal'];
							$peerObject['firstPort'] = $qls->App->generatePortName($portNameFormat, 0, $portTotal);
							$peerObject['lastPort'] = $qls->App->generatePortName($portNameFormat, $portTotal-1, $portTotal);
						}
						
						// Store first and last port names for each peer
						$objAFirstPort = $portNumberArray['a']['firstPort'];
						$objALastPort = $portNumberArray['a']['lastPort'];
						$objBFirstPort = $portNumberArray['b']['firstPort'];
						$objBLastPort = $portNumberArray['b']['lastPort'];
						$objectAName = $qls->App->objectArray[$objAID]['nameString'];
						$objectBName = $qls->App->objectArray[$objBID]['nameString'];
						$objectAPortRangeName = $objectAName.'.'.$objAFirstPort.'-'.$objALastPort;
						$objectBPortRangeName = $objectBName.'.'.$objBFirstPort.'-'.$objBLastPort;
						$line = array(
							$objectAPortRangeName,
							$objectBPortRangeName
						);
						$csvArray[$objectAName] = $line;
					}
				}
			}
		}
	}
	
	ksort($csvArray);
	foreach($csvArray as $line) {
		fputcsv($fileTrunks, $line);
	}
	
	fclose($fileTrunks);
}

function createVersion(){
	$fileVersion = fopen($_SERVER['DOCUMENT_ROOT'].'/userDownloads/Version.txt', 'w');
	fwrite($fileVersion, PCM_VERSION);
	fclose($fileVersion);
}

?>
