<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
require_once '../includes/image-uploader.class.php';
$qls->Security->check_auth_page('administrator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	$validate->returnData['debug'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	if (!count($validate->returnData['error'])){
		$uploader = new Uploader();
		$filename = md5(time().$_SERVER['REMOTE_ADDR']);
		$data = $uploader->upload($_FILES['files'], array(
			'limit' => 1, //Maximum Limit of files. {null, Number}
			'maxSize' => 2, //Maximum Size of files {null, Number(in MB's)}
			'extensions' => array('zip'), //Whitelist for file extension. {null, Array(ex: array('jpg', 'png'))}
			'required' => false, //Minimum one file is required for upload {Boolean}
			'uploadDir' => $_SERVER['DOCUMENT_ROOT'].'/userUploads/', //Upload directory {String}
			'title' => $filename, //New file name {null, String, Array} *please read documentation in README.md
			'removeFiles' => true, //Enable file exclusion {Boolean(extra for jQuery.filer), String($_POST field name containing json data with file names)}
			'perms' => null, //Uploaded file permisions {null, Number}
			'onCheck' => null, //A callback function name to be called by checking a file for errors (must return an array) | ($file) | Callback
			'onError' => null, //A callback function name to be called if an error occured (must return an array) | ($errors, $file) | Callback
			'onSuccess' => null, //A callback function name to be called if all files were successfully uploaded | ($files, $metas) | Callback
			'onUpload' => null, //A callback function name to be called if all files were successfully uploaded (must return an array) | ($file) | Callback
			'onComplete' => null, //A callback function name to be called when upload is complete | ($file) | Callback
			'onRemove' => 'onFilesRemoveCallback' //A callback function name to be called by removing files (must return an array) | ($removed_files) | Callback
		));
		
		if($data['isComplete']){
			$expectedFilenames = array(
				'01 - Categories.csv',
				'02 - Templates.csv',
				'03 - Cabinets.csv',
				'04 - Cabinet Cable Paths.csv',
				'05 - Cabinet Objects.csv',
				'06 - Object Inserts.csv',
				'07 - Connections.csv',
				'08 - Trunks.csv'
			);
			
			$zipFilename = $data['data']['metas'][0]['name'];
			$zip = new ZipArchive;
			$res = $zip->open($_SERVER['DOCUMENT_ROOT'].'/userUploads/'.$zipFilename);
			if ($res === TRUE) {
				// Get Filenames
				$filenameArray = array();
				for ($i=0; $i<$zip->numFiles; $i++) {
					array_push($filenameArray, $zip->getNameIndex($i));
				}
				
				foreach($expectedFilenames as $expectedFilename) {
					if(!in_array($expectedFilename, $filenameArray)) {
						$errMsg = 'File '.$expectedFilename.' does not exist.';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
				
				// Extract Files
				$zip->extractTo($_SERVER['DOCUMENT_ROOT'].'/userUploads/');
				$zip->close();
				
// Prepare Required Data
				
				// Cabinet Adjacencies
				$envAdjArray = array();
				$query = $qls->SQL->select('*', 'app_cabinet_adj');
				while($row = $qls->SQL->fetch_assoc($query)) {
					$envAdjArray[$row['left_cabinet_id']]['right'] = $row['right_cabinet_id'];
					$envAdjArray[$row['right_cabinet_id']]['left'] = $row['left_cabinet_id'];
				}
				
				// Get Env Tree
				$envTreeArray = array();
				$query = $qls->SQL->select('*', 'app_env_tree');
				while($row = $qls->SQL->fetch_assoc($query)) {
					//if($row['type'] != 'floorplan') {
						$envTreeArray[$row['id']] = $row;
					//}
				}
				
				// Generate nameString and nameHash for Environment Tree
				foreach($envTreeArray as &$envTree) {
					$parentID = $envTree['parent'];
					$name = $envTree['name'];
					while($parentID != '#') {
						$name = $envTreeArray[$parentID]['name'].'.'.$name;
						$parentID = $envTreeArray[$parentID]['parent'];
					}
					$nameHash = md5(strtolower($name));
					$envTree['nameString'] = $name;
					$envTree['nameHash'] = $nameHash;
				}
				
				// Cable Paths
				$tableCablePathArray = $qls->App->cablePathArray;
				
				// Categories
				$tableCategoryArray = $qls->App->categoryArray;
				
				// Templates
				$tableTemplateArray = array();
				$query = $qls->SQL->select('*', 'app_object_templates');
				while($row = $qls->SQL->fetch_assoc($query)) {
					//if($row['id'] != 1 and $row['id'] != 2 and $row['id'] != 3) {
						$tableTemplateArray[$row['id']] = $row;
					//}
				}
				
				// Enclosure Compatibility
				$tableEnclosureCompatibilityArray = array();
				$query = $qls->SQL->select('*', 'app_object_compatibility');
				while($row = $qls->SQL->fetch_assoc($query)) {
					$templateID = $row['template_id'];
					$templateFace = $row['side'];
					$templateDepth = $row['depth'];
					
					if($row['partitionType'] == 'Enclosure') {
						if(!array_key_exists($templateID, $tableEnclosureCompatibilityArray)) {
							$tableEnclosureCompatibilityArray[$templateID] = array(
								$templateFace => array(
									$templateDepth => $row
							));
						} else if(!array_key_exists($templateFace, $tableEnclosureCompatibilityArray[$templateID])) {
							$tableEnclosureCompatibilityArray[$templateID][$templateFace] = array(
								$templateDepth => $row
							);
						} else {
							$tableEnclosureCompatibilityArray[$templateID][$templateFace][$templateDepth] = $row;
						}
					}
				}
				
				// Objects
				$tableObjectArray = array();
				$query = $qls->SQL->select('*', 'app_object', array('parent_id' => array('=', 0)));
				while($row = $qls->SQL->fetch_assoc($query)) {
					//if($row['template_id'] != 1 and $row['template_id'] != 2 and $row['template_id'] != 3) {
						$tableObjectArray[$row['id']] = $row;
					//}
				}
				
				// Inserts
				$tableInsertArray = array();
				$query = $qls->SQL->select('*', 'app_object', array('parent_id' => array('<>', 0)));
				while($row = $qls->SQL->fetch_assoc($query)) {
					$tableInsertArray[$row['id']] = $row;
				}
				
				// Get Cabinet Objects
				$cabinetObjects = array();
				$query = $qls->SQL->select('*', 'app_object');
				while($row = $qls->SQL->fetch_assoc($query)) {
					if(!isset($cabinetObjects[$row['env_tree_id']])) {
						$cabinetObjects[$row['env_tree_id']] = array();
					}
					array_push($cabinetObjects[$row['env_tree_id']], $row);
				}
				
				// Get Connections
				//$connectionArray = $qls->App->inventoryAllArray;

				// Build Existing Arrays
				$existingCabinetArray = buildExistingCabinetArray($envTreeArray, $envAdjArray);
				$existingPathArray = buildExistingPathArray($tableCablePathArray, $envTreeArray);
				$existingCategoryArray = buildExistingCategoryArray($tableCategoryArray);
				$existingTemplateArray = buildExistingTemplateArray($qls, $tableCategoryArray);
				$existingObjectArray = buildExistingObjectArray($qls, $tableObjectArray, $envTreeArray);
				$existingInsertArray = buildExistingInsertArray($tableInsertArray, $envTreeArray, $tableObjectArray, $tableEnclosureCompatibilityArray);
				//$existingConnectionArray = buildExistingConnectionArray($qls, $connectionArray);
				$occupancyArray = array();
				
				
				$importedCabinetArray = array();
				$importedPathArray = array();
				$importedCategoryArray = array();
				$importedTemplateArray = array();
				$importedObjectArray = array();
				$importedInsertArray = array();
				$importedConnectionArray = array();
				$importedCabinetOccupancyArray = array();
				$importedTrunkArray = array();
				
				foreach($expectedFilenames as $csvFilename) {
					if($csvFile = fopen($_SERVER['DOCUMENT_ROOT'].'/userUploads/'.$csvFilename, 'r')) {
						if($csvFilename == '03 - Cabinets.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedCabinetArray($csvLine, $csvLineNumber, $csvFilename, $importedCabinetArray, $existingCabinetArray, $validate);
								}
							}
						} else if($csvFilename == '04 - Cabinet Cable Paths.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedPathArray($csvLine, $csvLineNumber, $csvFilename, $importedPathArray, $validate);
								}
							}
						} else if($csvFilename == '01 - Categories.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedCategoryArray($csvLine, $csvLineNumber, $csvFilename, $importedCategoryArray, $existingCategoryArray, $validate);
								}
							}
						} else if($csvFilename == '02 - Templates.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedTemplateArray($csvLine, $csvLineNumber, $csvFilename, $importedTemplateArray, $existingTemplateArray);
								}
							}
						} else if($csvFilename == '05 - Cabinet Objects.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedObjectArray($csvLine, $csvLineNumber, $csvFilename, $importedObjectArray, $occupancyArray, $existingTemplateArray, $existingObjectArray, $validate);
								}
							}
						} else if($csvFilename == '06 - Object Inserts.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedInsertArray($csvLine, $csvLineNumber, $csvFilename, $importedInsertArray, $existingInsertArray, $validate);
								}
							}
						} else if($csvFilename == '07 - Connections.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedConnectionArray($csvLine, $csvLineNumber, $csvFilename, $importedConnectionArray);
								}
							}
						} else if($csvFilename == '08 - Trunks.csv') {
							$csvLineNumber = 0;
							while($csvLine = fgetcsv($csvFile)) {
								$csvLineNumber++;
								if($csvLineNumber > 1 and $csvLine[0] != '') {
									buildImportedTrunkArray($csvLine, $csvLineNumber, $csvFilename, $importedTrunkArray);
								}
							}
						}
					} else {
						$errMsg = 'Could not open '.$expectedFilename.'.';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
				
				// Validation
				validateImportedCabinets($importedCabinetArray, $existingCabinetArray, $occupancyArray, $validate);
				validateImportedPaths($importedPathArray, $importedCabinetArray, $validate);
				validateImportedCategories($importedCategoryArray, $existingCategoryArray, $validate);
				validateImportedTemplates($importedTemplateArray, $existingTemplateArray, $importedCategoryArray, $validate);
				validateImportedObjects($importedObjectArray, $existingObjectArray, $importedCabinetArray, $importedTemplateArray, $existingTemplateArray, $existingCabinetOccupancyArray, $validate);
				validateImportedInserts($importedInsertArray, $existingInsertArray, $importedObjectArray, $importedTemplateArray, $validate);
				$templateImageArray = validateImportedImages('templateImages', 'template', $validate);
				$floorplanImageArray = validateImportedImages('floorplanImages', 'floorplan', $validate);
				
				if(count($validate->returnData['error']) == 0) {
					// Copy template images
					foreach($templateImageArray as $templateImage) {
						copy($_SERVER['DOCUMENT_ROOT'].'/userUploads/templateImages/'.$templateImage, $_SERVER['DOCUMENT_ROOT'].'/images/templateImages/'.$templateImage);
					}
					
					// Copy florplan images
					foreach($floorplanImageArray as $floorplanImage) {
						copy($_SERVER['DOCUMENT_ROOT'].'/userUploads/floorplanImages/'.$floorplanImage, $_SERVER['DOCUMENT_ROOT'].'/images/floorplanImages/'.$floorplanImage);
					}
					
					$qls->SQL->transaction('BEGIN');
					
					// Clear app tables if importing as restore
					clearAppTables($qls);
					
					// Find Category Changes
					$categoryAdds = findCategoryAdds($importedCategoryArray);
					
					// Process Category Changes
					insertCategoryAdds($qls, $categoryAdds, $importedCategoryArray);
					
					// Find Template Changes
					$templateAdds = findTemplateAdds($importedTemplateArray);
					
					// Process Template Changes
					insertTemplateAdds($qls, $templateAdds, $importedTemplateArray, $importedCategoryArray);
					
					// Find Cabinet Changes
					$cabinetAdds = findCabinetAdds($importedCabinetArray, $existingCabinetArray);
					
					// Process Cabinet Changes
					insertCabinetAdds($qls, $cabinetAdds, $importedCabinetArray, $existingCabinetArray);
					
					
					// Populate importedPathArray with cabinet IDs...
					// ... this should be done after imported cabinets are inserted into the DB
					populateImportedPathCabinetIDs($importedPathArray, $importedCabinetArray);
					
					
					
					// Find Path Changes
					$pathAdds = findPathAdds($importedPathArray, $existingPathArray);
					
					// Process Path Changes
					insertPathAdds($qls, $pathAdds, $importedCabinetArray);
					
					
					// Populate importedObjectArray with cabinet IDs...
					// ... this should be done after imported cabinets are inserted into the DB
					populateImportedObjectCabinetIDs($importedObjectArray, $importedCabinetArray);
					
					
					
					// Find Object Changes
					$objectAdds = findObjectAdds($importedObjectArray);
					
					// Process Object Changes
					insertObjectAdds($qls, $objectAdds, $importedObjectArray, $importedCabinetArray, $importedTemplateArray);
					
					
					
					// Find Insert Changes
					$insertAdds = findInsertAdds($importedInsertArray);
					
					// Process Insert Changes
					insertInsertAdds($qls, $insertAdds, $importedInsertArray, $importedObjectArray, $importedCabinetArray, $importedTemplateArray);
					
					
					
					// Validate and apply connection data
					unset($qls->App);
					$qls->App = new App($qls);
					//$compatibilityArray = buildCompatibilityArray($qls);
					//$objectArray = buildObjectArray($qls);
					//$templateArray = buildTemplateArray($qls);
					//$envTreeArray = buildEnvTreeArray($qls);
					$portArray = buildPortArray($qls);
					validateImportedConnections($qls, $importedConnectionArray, $portArray, $importedObjectArray, $importedTrunkArray, $validate);
					validateImportedTrunks($qls, $importedTrunkArray, $portArray, $importedObjectArray, $validate);
					
					if(count($validate->returnData['error']) == 0) {
						//$validate->returnData['debug']['connectionArray'] = $importedConnectionArray;
						$validate->returnData['debug']['importedTrunkArray'] = $importedTrunkArray;
						//$validate->returnData['debug']['importedObjectArray'] = $importedObjectArray;
						//$validate->returnData['debug']['portArray'] = $portArray;
						//$validate->returnData['debug']['insertArray'] = $importedInsertArray;
						processConnections($qls, $importedConnectionArray);
						processTrunks($qls, $importedTrunkArray, $importedObjectArray, $portArray);
						$qls->SQL->transaction('COMMIT');
						$validate->returnData['success'] = 'Import finished successfully.';
					} else {
						$qls->SQL->transaction('ROLLBACK');
					}
				}
				
			} else {
				array_push($validate->returnData['error'], $res);
			}
		}
		
		if($data['hasErrors']){
			foreach($data['errors'] as $errMsg) {
				array_push($validate->returnData['error'], $errMsg);
			}
		}
	}
	echo json_encode($validate->returnData);
}

// Cabinet Arrays
function buildExistingCabinetArray($envTreeArray, $envAdjArray){
	$return = array();
	
	foreach($envTreeArray as $row) {
		$rowID = $row['id'];
		$cabinetNameHash = $row['nameHash'];
		$row['size'] = $row['type'] == 'cabinet' ? $row['size'] : null;
		$row['name'] = $row['nameString'];
		$row['nameHash'] = $cabinetNameHash;
		$return[$cabinetNameHash] = $row;
		$return[$cabinetNameHash]['left'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['left']]['nameString'] : null;
		$return[$cabinetNameHash]['leftName'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['left']]['nameString'] : null;
		$return[$cabinetNameHash]['leftNameHash'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['left']]['nameHash'] : null;
		
		$return[$cabinetNameHash]['right'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['right']]['nameString'] : null;
		$return[$cabinetNameHash]['rightName'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['right']]['nameString'] : null;
		$return[$cabinetNameHash]['rightNameHash'] = isset($envAdjArray[$rowID]) ? $envTreeArray[$envAdjArray[$rowID]['right']]['nameHash'] : null;
	}
	
	return $return;
}

function buildImportedCabinetArray($csvLine, $csvLineNumber, $csvFilename, &$importedCabinetArray, $existingCabinetArray, &$validate){
	$cabinetName = $csvLine[0];
	$cabinetType = $csvLine[1];
	$cabinetSize = $csvLine[2];
	$cabinetLeft = $csvLine[3];
	$cabinetRight = $csvLine[4];
	//$originalCabinetName = ($GLOBALS['importType'] == 'edit') ? $csvLine[5] : '';
	$originalCabinetName = '';
	$floorplanImg = $csvLine[6];
	$importedCabinetNameHash = md5(strtolower($cabinetName));
	$originalCabinetNameHash = md5(strtolower($originalCabinetName));
	$cabinetParent = explode('.', $cabinetName);
	$name = array_pop($cabinetParent);
	$cabinetParent = implode('.', $cabinetParent);
	$cabinetParentHash = md5(strtolower($cabinetParent));
	
	if(array_key_exists($importedCabinetNameHash, $importedCabinetArray)) {
		$errMsg = 'Duplicate name on line '.$csvLineNumber.' of '.$csvFilename;
		array_push($validate->returnData['error'], $errMsg);
	} else {
		$importedCabinetArray[$importedCabinetNameHash] = array();
	}
	
	if(array_key_exists($importedCabinetNameHash, $existingCabinetArray)) {
		$importedCabinetArray[$importedCabinetNameHash]['id'] = $existingCabinetArray[$importedCabinetNameHash]['id'];
	}
	
	$importedCabinetArray[$importedCabinetNameHash]['fileName'] = $csvFilename;
	$importedCabinetArray[$importedCabinetNameHash]['line'] = $csvLineNumber;
	$importedCabinetArray[$importedCabinetNameHash]['nameString'] = $cabinetName;
	$importedCabinetArray[$importedCabinetNameHash]['name'] = $name;
	$importedCabinetArray[$importedCabinetNameHash]['nameHash'] = $importedCabinetNameHash;
	$importedCabinetArray[$importedCabinetNameHash]['parentName'] = $cabinetParent;
	$importedCabinetArray[$importedCabinetNameHash]['parentNameHash'] = $cabinetParentHash;
	$importedCabinetArray[$importedCabinetNameHash]['type'] = $cabinetType != '' ? $cabinetType : null;
	$importedCabinetArray[$importedCabinetNameHash]['size'] = $cabinetSize != '' ? $cabinetSize : null;
	$importedCabinetArray[$importedCabinetNameHash]['left'] = $cabinetLeft != '' ? $cabinetLeft : null;
	$importedCabinetArray[$importedCabinetNameHash]['leftHash'] = $cabinetLeft != '' ? md5(strtolower($cabinetLeft)) : null;
	$importedCabinetArray[$importedCabinetNameHash]['right'] = $cabinetRight != '' ? $cabinetRight : null;
	$importedCabinetArray[$importedCabinetNameHash]['rightHash'] = $cabinetRight != '' ? md5(strtolower($cabinetRight)) : null;
	$importedCabinetArray[$importedCabinetNameHash]['originalCabinetName'] = $originalCabinetName;
	$importedCabinetArray[$importedCabinetNameHash]['originalCabinetNameHash'] = $originalCabinetNameHash;
	$importedCabinetArray[$importedCabinetNameHash]['id'] = $originalCabinetNameHash ? $existingCabinetArray[$originalCabinetNameHash]['id'] : null;
	$importedCabinetArray[$importedCabinetNameHash]['floorplanImg'] = ($floorplanImg != '') ? strtolower($floorplanImg) : null;
}




// Path Arrays
function buildExistingPathArray($tableCablePathArray, $envTreeArray){
	$return = array();
	
	foreach($tableCablePathArray as $row) {
		$cabinetAID = $row['cabinet_a_id'];
		$cabinetBID = $row['cabinet_b_id'];
		$distance = $row['distance'];
		$entracen = $row['path_entrance_ru'];
		$notes = $row['notes'];
		
		$cabinetAName = $envTreeArray[$cabinetAID]['nameString'];
		$cabinetBName = $envTreeArray[$cabinetBID]['nameString'];
		
		$cabinetANameHash = $envTreeArray[$cabinetAID]['nameHash'];
		$cabinetBNameHash = $envTreeArray[$cabinetBID]['nameHash'];
		
		$cabinetComparison = strcasecmp($cabinetAName, $cabinetBName);
		if($cabinetComparison < 0) {
			$pathHash = md5(strtolower($cabinetAName.$cabinetBName));
		} else if($cabinetComparison > 0) {
			$pathHash = md5(strtolower($cabinetBName.$cabinetAName));
		}
		
		$return[$pathHash] = $row;
		$return[$pathHash]['cabinets'] = array(
			$cabinetANameHash => array(
				'column' => 'cabinetA',
				'name' => $cabinetAName,
				'nameHash' => $cabinetANameHash,
				'id' => $cabinetAID
			),
			$cabinetBNameHash => array(
				'column' => 'cabinetB',
				'name' => $cabinetBName,
				'nameHash' => $cabinetBNameHash,
				'id' => $cabinetBID
			)
		);
		$importedPathArray[$pathHash]['distance'] = $distance;
		$importedPathArray[$pathHash]['entrance'] = $entrance;
		$importedPathArray[$pathHash]['notes'] = $notes;
	}
	
	return $return;
}

function buildImportedPathArray($csvLine, $csvLineNumber, $csvFilename, &$importedPathArray, &$validate){
	$cabinetA = $csvLine[0];
	$cabinetB = $csvLine[1];
	$distance = $csvLine[2];
	$notes = $csvLine[3];
	$cabinetAHash = md5(strtolower($cabinetA));
	$cabinetBHash = md5(strtolower($cabinetB));
	
	$cabinetComparison = strcasecmp($cabinetA, $cabinetB);
	if($cabinetComparison < 0) {
		$pathHash = md5(strtolower($cabinetA.$cabinetB));
	} else if($cabinetComparison > 0) {
		$pathHash = md5(strtolower($cabinetB.$cabinetA));
	} else {
		$pathHash = md5(strtolower($cabinetA.$cabinetB));
		$errMsg = 'Cannot create a path with the same cabinet as both endpoints on line '.$csvLineNumber.' of "'.$csvFilename.'".';
		array_push($validate->returnData['error'], $errMsg);
	}
	
	/*
	if(array_key_exists($pathHash, $importedPathArray)) {
		$errMsg = 'Duplicate path entry on line '.$csvLineNumber.' of "'.$csvFilename.'".';
		array_push($validate->returnData['error'], $errMsg);
	} else {
		$importedPathArray[$pathHash] = array();
	}
	*/
	
	$importedPathArray[$pathHash]['cabinets'] = array(
		$cabinetAHash => array(
			'column' => 'cabinetA',
			'attribute' => 'cabinet_a_id',
			'name' => $cabinetA,
			'nameHash' => $cabinetAHash
		),
		$cabinetBHash => array(
			'column' => 'cabinetB',
			'attribute' => 'cabinet_b_id',
			'name' => $cabinetB,
			'nameHash' => $cabinetBHash
		)
	);
	$importedPathArray[$pathHash]['pathHash'] = $pathHash;
	$importedPathArray[$pathHash]['distance'] = $distance;
	$importedPathArray[$pathHash]['entrance'] = 42;
	$importedPathArray[$pathHash]['notes'] = $notes;
	$importedPathArray[$pathHash]['fileName'] = $csvFilename;
	$importedPathArray[$pathHash]['line'] = $csvLineNumber;
}



// Object Arrays
function buildExistingObjectArray(&$qls, &$tableObjectArray, $envTreeArray){
	$return = array();
	
	foreach($tableObjectArray as &$object) {
		$objectID = $object['id'];
		$objectName = $object['name'];
		$cabinetID = $object['env_tree_id'];
		$cabinetNameString = $envTreeArray[$cabinetID]['nameString'];
		$cabinetNameHash = $envTreeArray[$cabinetID]['nameHash'];
		$objectNameString = $cabinetNameString.'.'.$objectName;
		$objectNameHash = md5(strtolower($objectNameString));
		$templateID = $object['template_id'];
		if($templateID > 3) {
			// Rack objects have cabinet face and RU
			$cabinetFace = $object['cabinet_front'] == 0 ? 'front' : 'rear';
			$RUSize = $qls->App->templateArray[$templateID]['templateRUSize'];
			$topRU = $object['RU'];
			$bottomRU = $topRU - ($RUSize - 1);
		} else {
			// floorplan objects do not have cabinet face and RU
			$cabinetFace = null;
			$bottomRU = null;
		}
		
		$object['RU'] = $bottomRU;
		$object['cabinetNameString'] = $cabinetNameString;
		$object['cabinetNameHash'] = $cabinetNameHash;
		$object['nameString'] = $objectNameString;
		$object['nameHash'] = $objectNameHash;
		$object['cabinetFace'] = $cabinetFace;
		$return[$objectNameHash] = $object;
	}
	
	return $return;
}

function buildImportedObjectArray($csvLine, $csvLineNumber, $csvFilename, &$importedObjectArray, &$occupancyArray, $existingTemplateArray, $existingObjectArray, &$validate){
	$objectName = $csvLine[0];
	$cabinetName = $csvLine[1];
	$cabinetNameHash = md5(strtolower($cabinetName));
	$objectNameString = $cabinetName.'.'.$objectName;
	$objectNameHash = md5(strtolower($objectNameString));
	$templateName = $csvLine[2];
	$templateNameLower = strtolower($templateName);
	$templateNameHash = md5($templateNameLower);
	$RU = $csvLine[3];
	$cabinetFace = strtolower($csvLine[4]);
	//$originalObjectName = ($GLOBALS['importType'] == 'edit') ? $csvLine[5] : '';
	$originalObjectName = '';
	$originalObjectNameHash = md5(strtolower($originalObjectName));
	$originalCabinetNameArray = explode('.', $originalObjectName);
	array_pop($originalCabinetNameArray);
	$originalCabinetName = implode('.', $originalCabinetNameArray);
	$originalCabinetNameHash = md5(strtolower($originalCabinetName));
	$template = $existingTemplateArray[$templateNameHash];
	$floorplanPosLeft = $csvLine[6];
	$floorplanPosTop = $csvLine[7];
	
	if(!array_key_exists($objectNameHash, $importedObjectArray)) {
		$objectType = ($templateNameLower == 'wap' or $templateNameLower == 'walljack' or $templateNameLower == 'device') ? 'floorplanObject' : 'cabinetObject';
		$importedObjectArray[$objectNameHash]['line'] = $csvLineNumber;
		$importedObjectArray[$objectNameHash]['fileName'] = $csvFilename;
		$importedObjectArray[$objectNameHash]['objectName'] = $objectName;
		$importedObjectArray[$objectNameHash]['objectNameString'] = $objectNameString;
		$importedObjectArray[$objectNameHash]['objectNameHash'] = $objectNameHash;
		$importedObjectArray[$objectNameHash]['type'] = $objectType;
		$importedObjectArray[$objectNameHash]['cabinetName'] = $cabinetName;
		$importedObjectArray[$objectNameHash]['cabinetNameHash'] = $cabinetNameHash;
		$importedObjectArray[$objectNameHash]['templateName'] = $templateName;
		$importedObjectArray[$objectNameHash]['templateNameHash'] = $templateNameHash;
		$importedObjectArray[$objectNameHash]['RU'] = $objectType == 'cabinetObject' ? $RU : null;
		$importedObjectArray[$objectNameHash]['cabinetFace'] = $objectType == 'cabinetObject' ? $cabinetFace : null;
		$importedObjectArray[$objectNameHash]['originalObjectName'] = $originalObjectName;
		$importedObjectArray[$objectNameHash]['originalObjectNameHash'] = $originalObjectNameHash;
		$importedObjectArray[$objectNameHash]['originalCabinetName'] = $originalCabinetName;
		$importedObjectArray[$objectNameHash]['originalCabinetNameHash'] = $originalCabinetNameHash;
		$importedObjectArray[$objectNameHash]['id'] = $originalObjectName ? $existingObjectArray[$originalObjectNameHash]['id'] : null;
		$importedObjectArray[$objectNameHash]['posLeft'] = $objectType == 'floorplanObject' ? $floorplanPosLeft : null;
		$importedObjectArray[$objectNameHash]['posTop'] = $objectType == 'floorplanObject' ? $floorplanPosTop : null;
		
		if($template and $objectType == 'cabinetObject') {
			$RUSize = $template['templateRUSize'];
			$mountConfig = $template['templateMountConfig'];
			$bottomRU = $RU;
			$topRU = $bottomRU + ($RUSize - 1);
			
			if(!array_key_exists($cabinetNameHash, $occupancyArray)) {
				$occupancyArray[$cabinetNameHash] = array('topOccupiedRU' => 0, 'front' => array(), 'rear' => array());
			}
			
			if($topRU > $occupancyArray[$cabinetNameHash]['topOccupiedRU']) {
				$occupancyArray[$cabinetNameHash]['topOccupiedRU'] = $topRU;
			}
			
			$errMsg = 'Object on line '.$csvLineNumber.' of "'.$csvFilename.'" overlaps with another object at RU ';
			for($x=$bottomRU; $x<=$topRU; $x++) {
				if($cabinetFace == 'front') {
					if($mountConfig == 0) {
						if(in_array($x, $occupancyArray[$cabinetNameHash]['front'])) {
							array_push($validate->returnData['error'], $errMsg.$x.'.');
						}
						array_push($occupancyArray[$cabinetNameHash]['front'], $x);
					} else if($mountConfig == 1) {
						if(in_array($x, $occupancyArray[$cabinetNameHash]['front']) or in_array($x, $occupancyArray[$cabinetNameHash]['rear'])) {
							array_push($validate->returnData['error'], $errMsg.$x.'.');
						}
						array_push($occupancyArray[$cabinetNameHash]['front'], $x);
						array_push($occupancyArray[$cabinetNameHash]['rear'], $x);
					}
				} else if($cabinetFace == 'rear') {
					if($mountConfig == 0) {
						if(in_array($x, $occupancyArray[$cabinetNameHash]['rear'])) {
							array_push($validate->returnData['error'], $errMsg.$x.'.');
						}
						array_push($occupancyArray[$cabinetNameHash]['rear'], $x);
					} else if($mountConfig == 1) {
						if(in_array($x, $occupancyArray[$cabinetNameHash]['front']) or in_array($x, $occupancyArray[$cabinetNameHash]['rear'])) {
							array_push($validate->returnData['error'], $errMsg.$x.'.');
						}
						array_push($occupancyArray[$cabinetNameHash]['front'], $x);
						array_push($occupancyArray[$cabinetNameHash]['rear'], $x);
					}
				}
			}
		}
	} else {
		$errMsg = 'Duplicate original object on line '.$csvLineNumber.' of "'.$csvFilename.'".';
		array_push($validate->returnData['error'], $errMsg);
	}
}



// Insert Arrays
function buildExistingInsertArray($tableInsertArray, $envTreeArray, $tableObjectArray, $tableEnclosureCompatibilityArray){
	$return = array();
	
	foreach($tableInsertArray as $insert) {
		$insertID = $insert['id'];
		$insertName = $insert['name'];
		$cabinetID = $insert['env_tree_id'];
		$parentID = $insert['parent_id'];
		$parentFaceID = $insert['parent_face'];
		$parentFace = $parentFaceID == 0 ? 'Front' : 'Rear';
		$parentDepth = $insert['parent_depth'];
		$parentRow = $insert['insertSlotY'];
		$parentCol = $insert['insertSlotX'];
		$parentCompatibility = $tableEnclosureCompatibilityArray[$parentID][$parentFaceID][$parentDepth];
		
		$enc = $parentDepth;
		$row = chr($parentRow+65);
		$col = $parentCol + 1;
		$slotID = 'Enc'.$enc.'Slot'.$row.$col;
		
		$cabinetNameString = $envTreeArray[$cabinetID]['nameString'];
		$cabinetNameHash = $envTreeArray[$cabinetID]['nameHash'];
		$objectNameString = $cabinetNameString.'.'.$tableObjectArray[$parentID]['name'];
		$objectNameHash = md5(strtolower($objectNameString));
		$insertNameString = $objectNameString.'.'.$parentFace.'.'.$slotID.'.'.$insertName;
		$insertNameHash = md5(strtolower($insertNameString));
		
		$insert['cabinetNameString'] = $cabinetNameString;
		$insert['cabinetNameHash'] = $cabinetNameHash;
		$insert['objectNameString'] = $objectNameString;
		$insert['objectNameHash'] = $objectNameHash;
		$insert['slotID'] = $slotID;
		$insert['insertName'] = $insertName;
		$insert['insertNameString'] = $insertNameString;
		$insert['insertNameHash'] = $insertNameHash;
		$return[$insertNameHash] = $insert;
	}
	
	return $return;
}

function buildImportedInsertArray($csvLine, $csvLineNumber, $csvFilename, &$importedInsertArray, $existingInsertArray, &$validate){
	$objectNameString = $csvLine[0];
	$face = $csvLine[1];
	$slotID = strtolower($csvLine[2]);
	$insertName = $csvLine[3];
	$templateName = $csvLine[4];
	//$originalInsert = ($GLOBALS['importType'] == 'edit') ? strtolower($csvLine[5]) : '';
	$originalInsert = '';
	$originalInsertArray = explode('.', $originalInsert);
	$originalInsertName = array_pop($originalInsertArray);
	$originalInsertSlotID = array_pop($originalInsertArray);
	$originalInsertFace = array_pop($originalInsertArray);
	$originalInsertObjectNameString = implode('.', $originalInsertArray);
	
	if($templateName !='') {
		$insertNameString = $objectNameString.'.'.$face.'.'.$slotID.'.'.$insertName;
		$objectNameHash = md5(strtolower($objectNameString));
		$insertNameHash = md5(strtolower($insertNameString));
		$originalInsertHash = md5($originalInsert);
		$originalInsertObjectNameHash = md5(strtolower($originalInsertObjectNameString));
		$templateNameHash = md5(strtolower($templateName));
		
		if(!array_key_exists($insertNameHash, $importedInsertArray)) {
			$importedInsertArray[$insertNameHash]['line'] = $csvLineNumber;
			$importedInsertArray[$insertNameHash]['fileName'] = $csvFilename;
			$importedInsertArray[$insertNameHash]['objectNameString'] = $objectNameString;
			$importedInsertArray[$insertNameHash]['objectNameHash'] = $objectNameHash;
			$importedInsertArray[$insertNameHash]['objectFace'] = $face;
			$importedInsertArray[$insertNameHash]['parent_face'] = strtolower($face) == 'front' ? 0 : 1;
			$importedInsertArray[$insertNameHash]['slotID'] = $slotID;
			$importedInsertArray[$insertNameHash]['insertName'] = $insertName;
			$importedInsertArray[$insertNameHash]['insertNameString'] = $insertNameString;
			$importedInsertArray[$insertNameHash]['insertNameHash'] = $insertNameHash;
			$importedInsertArray[$insertNameHash]['originalInsert'] = $originalInsert;
			$importedInsertArray[$insertNameHash]['originalInsertHash'] = $originalInsertHash;
			$importedInsertArray[$insertNameHash]['originalInsertName'] = $originalInsertName;
			$importedInsertArray[$insertNameHash]['originalInsertSlotID'] = $originalInsertSlotID;
			$importedInsertArray[$insertNameHash]['originalInsertFace'] = $originalInsertFace;
			$importedInsertArray[$insertNameHash]['originalInsertObjectNameString'] = $originalInsertObjectNameString;
			$importedInsertArray[$insertNameHash]['originalInsertObjectNameHash'] = $originalInsertObjectNameHash;
			$importedInsertArray[$insertNameHash]['templateNameHash'] = $templateNameHash;
			$importedInsertArray[$insertNameHash]['id'] = $originalInsert ? $existingInsertArray[$originalInsertHash]['id'] : null;
		} else {
			$errMsg = 'Duplicate insert name on line '.$csvLineNumber.' of '.$csvFilename;
			array_push($validate->returnData['error'], $errMsg);
		}
	}
}



// Category Arrays
function buildExistingCategoryArray($tableCategoryArray){
	$return = array();
	
	foreach($tableCategoryArray as $category) {
		$categoryName = $category['name'];
		$categoryNameHash = md5(strtolower($categoryName));
		$category['nameHash'] = $categoryNameHash;

		$return[$categoryNameHash] = $category;
	}
	
	return $return;
}

function buildImportedCategoryArray($csvLine, $csvLineNumber, $csvFilename, &$importedCategoryArray, $existingCategoryArray, &$validate){
	$categoryName = $csvLine[0];
	$categoryColor = $csvLine[1];
	$categoryDefaultOption = $csvLine[2];
	//$originalCategoryName = ($GLOBALS['importType'] == 'edit') ? $csvLine[3] : '';
	$originalCategoryName = '';
	$originalCategoryNameHash = md5(strtolower($originalCategoryName));
	$categoryNameHash = md5(strtolower($categoryName));
	
	$categoryDefaultOption = strtolower($categoryDefaultOption) == 'x' ? 1 : 0;
	
	$importedCategoryArray[$categoryNameHash]['name'] = $categoryName;
	$importedCategoryArray[$categoryNameHash]['nameHash'] = $categoryNameHash;
	$importedCategoryArray[$categoryNameHash]['color'] = $categoryColor;
	$importedCategoryArray[$categoryNameHash]['defaultOption'] = $categoryDefaultOption;
	$importedCategoryArray[$categoryNameHash]['originalCategoryName'] = $originalCategoryName;
	$importedCategoryArray[$categoryNameHash]['originalCategoryNameHash'] = $originalCategoryNameHash;
	$importedCategoryArray[$categoryNameHash]['line'] = $csvLineNumber;
	$importedCategoryArray[$categoryNameHash]['fileName'] = $csvFilename;
	if($originalCategoryName != '') {
		$importedCategoryArray[$categoryNameHash]['id'] = $existingCategoryArray[$originalCategoryNameHash]['id'];
	}
}




// Template Arrays
function buildExistingTemplateArray(&$qls, $tableCategoryArray){
	$return = array();
	
	foreach($qls->App->templateArray as $template) {
		$templateName = $template['templateName'];
		$templateNameHash = md5(strtolower($templateName));
		$template['templateNameHash'] = $templateNameHash;
		$categoryName = $tableCategoryArray[$template['templateCategory_id']]['name'];
		$template['categoryName'] = $categoryName;
		$template['categoryNameHash'] = md5(strtolower($categoryName));
		$return[$templateNameHash] = $template;
	}
	
	return $return;
}

function buildImportedTemplateArray($csvLine, $csvLineNumber, $csvFilename, &$importedTemplateArray, $existingTemplateArray){
	$templateName = $csvLine[0];
	$templateCategoryName = $csvLine[1];
	//$templateOriginalTemplateName = ($GLOBALS['importType'] == 'edit') ? $csvLine[2] : '';
	$templateOriginalTemplateName = '';
	$templateType = $csvLine[3];
	$templateFunction = $csvLine[4];
	$templateRUSize = $csvLine[5];
	$templateMountConfig = $csvLine[6];
	$templateStructure = $csvLine[7];
	$templateNameHash = md5(strtolower($templateName));
	$templateCategoryNameHash = md5(strtolower($templateCategoryName));
	$originalTemplateNameHash = md5(strtolower($templateOriginalTemplateName));
	
	$importedTemplateArray[$templateNameHash]['templateName'] = $templateName;
	$importedTemplateArray[$templateNameHash]['templateNameHash'] = $templateNameHash;
	$importedTemplateArray[$templateNameHash]['categoryName'] = $templateCategoryName;
	$importedTemplateArray[$templateNameHash]['categoryNameHash'] = $templateCategoryNameHash;
	$importedTemplateArray[$templateNameHash]['originalTemplateName'] = $templateOriginalTemplateName;
	$importedTemplateArray[$templateNameHash]['originalTemplateNameHash'] = $originalTemplateNameHash;
	$importedTemplateArray[$templateNameHash]['templateType'] = strtolower($templateType);
	$importedTemplateArray[$templateNameHash]['templateFunction'] = strtolower($templateFunction);
	$importedTemplateArray[$templateNameHash]['templateRUSize'] = $templateRUSize;
	$importedTemplateArray[$templateNameHash]['templateMountConfig'] = strtolower($templateMountConfig);
	$importedTemplateArray[$templateNameHash]['templateStructure'] = json_decode($templateStructure, true);
	$importedTemplateArray[$templateNameHash]['line'] = $csvLineNumber;
	$importedTemplateArray[$templateNameHash]['fileName'] = $csvFilename;
	
	if($templateOriginalTemplateName != '') {
		$importedTemplateArray[$templateNameHash]['id'] = $existingTemplateArray[$originalTemplateNameHash]['id'];
	}
}




// Connection Arrays
function buildImportedConnectionArray($csvLine, $csvLineNumber, $csvFilename, &$importedConnectionArray){
	$portA = strtolower($csvLine[0]);
	$aCode39 = strtolower($csvLine[1]);
	$aConnector = strtolower($csvLine[2]);
	$portB = strtolower($csvLine[3]);
	$bCode39 = strtolower($csvLine[4]);
	$bConnector = strtolower($csvLine[5]);
	$mediaType = strtolower($csvLine[6]);
	$length = strtolower($csvLine[7]);
	
	if(preg_match('/\([0-9]\)|\([1-9]{1}[0-9]+\)/', $portA, $match)) {
		$matchLen = strlen($match[0]);
		$portStrLen = strlen($portA);
		$portAID = substr($match[0], 1, $matchLen-2);
		$portA = substr($portA, 0, $portStrLen-$matchLen);
		$portANameArray = array($portA, $portAID);
		$portA = implode('.', $portANameArray);
		
	}
	
	if(preg_match('/\([0-9]\)|\([1-9]{1}[0-9]+\)/', $portB, $match)) {
		$matchLen = strlen($match[0]);
		$portStrLen = strlen($portB);
		$portBID = substr($match[0], 1, $matchLen-2);
		$portB = substr($portB, 0, $portStrLen-$matchLen);
		$portBNameArray = array($portB, $portBID);
		$portB = implode('.', $portBNameArray);
		
	}
	
	$aPortNameHash = ($portA != '' and $portA != 'none') ? md5($portA) : false;
	$bPortNameHash = ($portB != '' and $portB != 'none') ? md5($portB) : false;
	$aCode39 = ($aCode39 != '' and $aCode39 != 'none') ? $aCode39 : false;
	$bCode39 = ($bCode39 != '' and $bCode39 != 'none') ? $bCode39 : false;
	$aConnector = ($aConnector != '' and $aConnector != 'none' and $aCode39) ? $aConnector : false;
	$bConnector = ($bConnector != '' and $bConnector != 'none' and $bCode39) ? $bConnector : false;
	$mediaType = ($mediaType != '' and $mediaType != 'none') ? $mediaType : false;
	$length = ($length != '' and $length != 'none') ? $length : false;
	
	$addConnection = false;
	
	if($aPortNameHash or $aCode39) {
		$addConnection = true;
		$workingArray = array(
			'portName' => $portA,
			'portNameHash' => $aPortNameHash,
			'code39' => strtoupper($aCode39),
			'connector' => $aConnector,
			'peerPortName' => $portB,
			'peerPortNameHash' => $bPortNameHash,
			'peerCode39' => strtoupper($bCode39),
			'peerConnector' => $bConnector
		);
	} else if($bPortNameHash or $bCode39) {
		$addConnection = true;
		$workingArray = array(
			'portName' => $portB,
			'portNameHash' => $bPortNameHash,
			'code39' => strtoupper($bCode39),
			'connector' => $bConnector,
			'peerPortName' => $portA,
			'peerPortNameHash' => $aPortNameHash,
			'peerCode39' => strtoupper($aCode39),
			'peerConnector' => $aConnector
		);
	}
	
	if($addConnection) {
		$workingArray['mediaType'] = $mediaType;
		$workingArray['length'] = $length;
		$workingArray['line'] = $csvLineNumber;
		$workingArray['fileName'] = $csvFilename;
		array_push($importedConnectionArray, $workingArray);
	}
}




// Trunk Arrays
function buildImportedTrunkArray($csvLine, $csvLineNumber, $csvFilename, &$importedTrunkArray){
	
	$objA = strtolower($csvLine[0]);
	$objB = strtolower($csvLine[1]);
	
	$objAArray = explode('.', $objA);
	$objAPort = array_pop($objAArray);
	$objAName = implode('.', $objAArray);
	$objANameHash = md5(strtolower($objAName));
	
	// Extract objA portID from port name
	if(preg_match('/\([0-9]\)|\([1-9]{1}[0-9]+\)/', $objAPort, $match)) {
		$stringLength = strlen($objAPort);
		$matchLength = strlen($match[0]);
		$portID = substr($match[0], 1, $matchLength-2);
		$portName = substr($objAPort, 0, $stringLength-$matchLength);
		$objAPortNameArray = array($objAName, $portName, $portID);
	} else {
		$portRangeArray = explode('-', $objAPort);
		$firstPort = $portRangeArray[0];
		$objAPortNameArray = array($objAName, $firstPort);
	}
	$objAPortName = implode('.', $objAPortNameArray);
	$objAPortNameHash = md5(strtolower($objAPortName));
	
	$objBArray = explode('.', $objB);
	$objBPort = array_pop($objBArray);
	$objBName = implode('.', $objBArray);
	$objBNameHash = md5(strtolower($objBName));
	
	// Extract objB portID from port name
	if(preg_match('/\([0-9]\)|\([1-9]{1}[0-9]+\)/', $objBPort, $match)) {
		$stringLength = strlen($objBPort);
		$matchLength = strlen($match[0]);
		$portID = substr($match[0], 1, $matchLength-2);
		$portName = substr($objBPort, 0, $stringLength-$matchLength);
		$objBPortNameArray = array($objBName, $portName, $portID);
	} else {
		$portRangeArray = explode('-', $objBPort);
		$firstPort = $portRangeArray[0];
		$objBPortNameArray = array($objBName, $firstPort);
	}
	$objBPortName = implode('.', $objBPortNameArray);
	$objBPortNameHash = md5(strtolower($objBPortName));
	
	$importedTrunkArray[$objAPortNameHash]['name'] = $objAName;
	$importedTrunkArray[$objAPortNameHash]['nameHash'] = $objANameHash;
	$importedTrunkArray[$objAPortNameHash]['portName'] = $objAPortName;
	$importedTrunkArray[$objAPortNameHash]['portNameHash'] = $objAPortNameHash;
	$importedTrunkArray[$objAPortNameHash]['peerPortNameHash'] = $objBPortNameHash;
	$importedTrunkArray[$objAPortNameHash]['line'] = $csvLineNumber;
	$importedTrunkArray[$objAPortNameHash]['fileName'] = $csvFilename;
	
	$importedTrunkArray[$objBPortNameHash]['name'] = $objBName;
	$importedTrunkArray[$objBPortNameHash]['nameHash'] = $objBNameHash;
	$importedTrunkArray[$objBPortNameHash]['portName'] = $objBPortName;
	$importedTrunkArray[$objBPortNameHash]['portNameHash'] = $objBPortNameHash;
	$importedTrunkArray[$objBPortNameHash]['peerPortNameHash'] = $objAPortNameHash;
	$importedTrunkArray[$objBPortNameHash]['line'] = $csvLineNumber;
	$importedTrunkArray[$objBPortNameHash]['fileName'] = $csvFilename;
}



// Validation
function validateImportedCabinets($importedCabinetArray, $existingCabinetArray, $occupancyArray, &$validate){
	
	$arrayOriginalHashes = array();
	$arrayImportedHashes = array();
	$allowedLocationTypes = array(
		'location',
		'pod',
		'cabinet',
		'floorplan'
	);
			
	foreach($importedCabinetArray as $cabinet) {
		// Validation Cabinet
		$cabinetName = $cabinet['nameString'];
		$cabinetNameHash = $cabinet['nameHash'];
		$cabinetType = $cabinet['type'];
		$cabinetSize = $cabinet['size'];
		$cabinetLeft = $cabinet['left'];
		$cabinetRight = $cabinet['right'];
		$csvLineNumber = $cabinet['line'];
		$csvFilename = $cabinet['fileName'];
		$cabinetParentName = $cabinet['parentName'];
		$cabinetParentNameHash = $cabinet['parentNameHash'];
		$topOccupiedRU = $occupancyArray[$cabinetNameHash]['topOccupiedRU'];
		$originalCabinetName = $cabinet['originalCabinetName'];
		$originalCabinetNameHash = $cabinet['originalCabinetNameHash'];
		$floorplanImg = $cabinet['floorplanImg'];
		
		// Validate Cabinet Name
		$cabinetNameArray = explode('.', $cabinetName);
		foreach($cabinetNameArray as $cabinetNameFragment) {
			$validate->validateNameText($cabinetNameFragment, 'Name '.$cabinetNameFragment.' on line '.$csvLineNumber.' of '.$csvFilename);
		}
		
		// Validate Location Type
		$validate->validateInArray($cabinetType, $allowedLocationTypes, 'type '.$cabinetType.' on line '.$csvLineNumber.' of '.$csvFilename);
		
		// Validate Cabinet Specific Values
		if($cabinetType == 'cabinet') {
			
			// Validate RU Size
			$validate->validateRUSize($cabinetSize, 'Invalid RU size on line '.$csvLineNumber.' of "'.$csvFilename.'".');
			
			if($cabinetSize < $topOccupiedRU) {
				$errMsg = 'Cabinet RU size on line '.$csvLineNumber.' of "'.$csvFilename.'" is less than the top occupied RU.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Validate Cabinet Adjacencies
			$cabinetSides = array(array('left', 'right'), array('right', 'left'));
			foreach($cabinetSides as $cabinetSideArray) {
				$cabinetSide = $cabinetSideArray[0];
				$cabinetSideName = $cabinet[$cabinetSide];
				$cabinetSideHash = $cabinet[$cabinetSide.'Hash'];
				
				if($cabinetSideName) {
				
					// Adjacenct Cabinet Needs to Exist
					if(array_key_exists($cabinetSideHash, $importedCabinetArray)) {
						$adjacentCabinet = $importedCabinetArray[$cabinetSideHash];
						$adjacentCabinetSideName = $adjacentCabinet[$cabinetSideArray[1]];
						
						// Adjacent Cabinet Needs to be of Type Cabinet
						if($adjacentCabinet['type'] == 'cabinet') {
							
							// Get Adjacent Cabinet Parent Name
							$adjacentCabinetParentName = $adjacentCabinet['parentName'];
							
							// Parents Must Match, Meaning Cabinets Are in Same Location
							if($cabinetParentName == $adjacentCabinetParentName) {
								if($cabinetName != $adjacentCabinetSideName) {
									$errMsg = ucfirst($cabinetSide).' adjacency does not agree on line '.$csvLineNumber.' of "'.$csvFilename.'".';
									array_push($validate->returnData['error'], $errMsg);
								}
							} else {
								$errMsg = ucfirst($cabinetSide).' adjacency is not in the same location on line '.$csvLineNumber.' of "'.$csvFilename.'".';
								array_push($validate->returnData['error'], $errMsg);
							}
						} else {
							$errMsg = ucfirst($cabinetSide).' adjacency is not a cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'".';
							array_push($validate->returnData['error'], $errMsg);
						}
					} else {
						$errMsg = ucfirst($cabinetSide).' adjacency is not a cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'".';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
			}
		} else if($cabinetType == 'floorplan') {
			
			// Floorplans cannot have cabinet adjacencies
			if($cabinet['leftHash'] or $cabinet['rightHash']) {
				$errMsg = 'Floorplan on line '.$csvLineNumber.' of "'.$csvFilename.'" cannot be configured with cabinet adjacencies.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Validate floorplan image
			if($floorplanImg) {
				$floorplanImgArray = explode('.', $floorplanImg);
				if(count($floorplanImgArray) == 2) {
					$floorplanImgName = $floorplanImgArray[0];
					$floorplanImgExt = $floorplanImgArray[1];
					$floorplanImgExtArray = array(
						'jpg',
						'jpeg',
						'gif',
						'png'
					);
					$validate->validateInArray($floorplanImgExt, $floorplanImgExtArray, 'Floorplan Image name on line '.$csvLineNumber.' of "'.$csvFilename.'".');
					if($floorplanImgName != 'floorplan-default') {
						$validate->validateMD5($floorplanImgName, 'Invalid Flooplan Image name on line '.$csvLineNumber.' of "'.$csvFilename.'".');
					}
				} else {
					$errMsg = 'Invalid Floorplan Image name on line '.$csvLineNumber.' of "'.$csvFilename.'"';
					array_push($validate->returnData['error'], $errMsg);
				}
			} else {
				$errMsg = 'Floorplan Image cannot be blank on line '.$csvLineNumber.' of "'.$csvFilename.'"';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		if($cabinetType != 'floorplan') {
			// Only floorplans can have a floorplan image
			if($floorplanImg) {
				$errMsg = 'Entry on line '.$csvLineNumber.' of "'.$csvFilename.'" cannot be configured with a floorplan image.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		// Validate Parent Exists
		if($cabinetParentName != '') {
			if(!array_key_exists($cabinetParentNameHash, $importedCabinetArray)) {
				$errMsg = 'Path does not exist on line '.$csvLineNumber.' of "'.$csvFilename.'".';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		// Validate Original Cabinet Name
		if($originalCabinetName != '') {
			if(!array_key_exists($originalCabinetNameHash, $existingCabinetArray)) {
				$errMsg = 'Original cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Check for duplicate Original Cabinet Names
			if(in_array($originalCabinetNameHash, $arrayOriginalHashes)) {
				$errMsg = 'Duplicate original cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'".';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($arrayOriginalHashes, $originalCabinetNameHash);
			}
		}
	}
}

function validateImportedPaths($importedPathArray, $importedCabinetArray, &$validate){
	foreach($importedPathArray as $path) {
		$distance = $path['distance'];
		$notes = $path['notes'];
		$csvFilename = $path['fileName'];
		$csvLineNumber = $path['line'];
		
		foreach($path['cabinets'] as $cabinet) {
			$cabinetName = $cabinet['name'];
			$cabinetNameHash = $cabinet['nameHash'];
			$column = $cabinet['column'];
			
			// Validate Cabinet Name
			$cabinetNameArray = explode('.', $cabinetName);
			foreach($cabinetNameArray as $cabinetNameFragment) {
				$validate->validateNameText($cabinetNameFragment, ucfirst($column).' name '.$cabinetNameFragment.' on line '.$cabinetLine.' of "'.$cabinetFilename.'".');
			}
			
			// Cabinet Name Must Exist in Imported Cabinet Array
			if(!array_key_exists($cabinetNameHash, $importedCabinetArray)) {
				$errMsg = ucfirst($column).' on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist in Cabinets data.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		// Validate Path Distance
		$validate->validateDistance($distance, 'Path distance on line '.$cabinetLine.' of '.$cabinetFilename);
		
		// Validate Path Notes
		$validate->validateText($notes, 'Path notes on line '.$cabinetLine.' of '.$cabinetFilename);
		
	}
}

function validateImportedCategories($importedCategoryArray, $existingCategoryArray, &$validate){
	$arrayOriginalHashes = array();
	$defaultOptionCount = 0;
	
	foreach($importedCategoryArray as $category) {
		$categoryName = $category['name'];
		$categoryColor = $category['color'];
		$defaultOption = $category['defaultOption'];
		$originalCategoryName = $category['originalCategoryName'];
		$originalCategoryNameHash = $category['originalCategoryNameHash'];
		$csvFilename = $category['fileName'];
		$csvLineNumber = $category['line'];
		
		// Validate Category Name
		$validate->validateNameText($categoryName, 'Category name on line '.$cabinetLine.' of "'.$cabinetFilename.'".');
		
		// Validate Category Color
		$validate->validateCategoryColor($categoryColor);
		
		// Validate Original Category Name
		if($originalCategoryName != '' and !array_key_exists($originalCategoryNameHash, $existingCategoryArray)) {
			$errMsg = 'Original category on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Check for original duplicates
		if($originalCategoryName != '') {
			if(in_array($originalCategoryNameHash, $arrayOriginalHashes)) {
				$errMsg = 'Duplicate original category on line '.$csvLineNumber.' of"'.$csvFilename.'"';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($arrayOriginalHashes, $originalCategoryNameHash);
			}
		}
		
		if($defaultOption) {
			$defaultOptionCount++;
		}
	}
	
	// Validate Default Option
	if($defaultOptionCount != 1) {
		$errMsg = 'Must define 1 category as default in"'.$csvFilename.'"';
		array_push($validate->returnData['error'], $errMsg);
	}
}

function validateImportedTemplates(&$importedTemplateArray, $existingTemplateArray, $importedCategoryArray, &$validate){
	$arrayOriginalHashes = array();
	$templateTypeArray = ['standard', 'insert'];
	$templateFunctionArray = ['passive', 'endpoint'];
	$templateMountConfigArray = ['2-post', '4-post', 'n/a'];
	$validImgFilenames = ['jpg', 'jpeg', 'png', 'gif'];
	
	foreach($importedTemplateArray as &$template) {
		$templateName = $template['templateName'];
		$templateCategoryNameHash = $template['categoryNameHash'];
		$templateOriginalTemplateName = $template['originalTemplateName'];
		$templateOriginalTemplateNameHash = $template['originalTemplateNameHash'];
		$templateType = $template['templateType'];
		$templateFunction = $template['templateFunction'];
		$templateRUSize = $template['templateRUSize'];
		$templateMountConfig = $template['templateMountConfig'];
		$templateStructure = $template['templateStructure'];
		
		$csvFilename = $template['fileName'];
		$csvLineNumber = $template['line'];
		
		// Validate Template Structure JSON
		if($templateStructure !== null) {
			$template['templateEncLayoutX'] = $templateStructure['sizeX'];
			$template['templateEncLayoutY'] = $templateStructure['sizeY'];
			$template['templateHUnits'] = $templateStructure['parentH'];
			$template['templateVUnits'] = $templateStructure['parentV'];
			$template['templateFrontImage'] = $templateStructure['frontImage'];
			$template['templateRearImage'] = $templateStructure['rearImage'];
			$template['templatePartitionData'] = $templateStructure['structure'];
		} else {
			$errMsg = 'Invalid template structure on line '.$csvLineNumber.' of "'.$csvFilename.'".';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Validate Template Name
		$validate->validateNameText($templateName, 'Template name on line '.$csvLineNumber.' of "'.$csvFilename.'".');
		
		// Validate Template Category
		if(!array_key_exists($templateCategoryNameHash, $importedCategoryArray)) {
			$errMsg = 'Category on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist in "Categories.csv".';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Validate Original Template Name
		if($templateOriginalTemplateName != '') {
			if(!array_key_exists($templateOriginalTemplateNameHash, $existingTemplateArray)) {
				$errMsg = 'Original template on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Check for original duplicates
			if(in_array($templateOriginalTemplateNameHash, $arrayOriginalHashes)) {
				$errMsg = 'Duplicate original template on line '.$csvLineNumber.' of "'.$csvFilename.'".';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($arrayOriginalHashes, $templateOriginalTemplateNameHash);
			}
		}
		
		// Validate Template Type
		$validate->validateInArray($templateType, $templateTypeArray, 'template type on line '.$csvLineNumber.' of '.$csvFilename);
		
		// Validate Template Function
		$validate->validateInArray($templateFunction, $templateFunctionArray, 'template function on line '.$csvLineNumber.' of '.$csvFilename);
		
		// Validate Template RU Size
		$validate->validateRUSize($templateRUSize, 'Invalid template RU size on line '.$csvLineNumber.' of '.$csvFilename);
		
		// Validate Template Mount Config
		$validate->validateInArray($templateMountConfig, $templateMountConfigArray, 'template mount config on line '.$csvLineNumber.' of '.$csvFilename);
		
		// Validate Template Structure
		if($templateStructure !== null) {
			$templateDimensionArray = array(
				'Size X' => $template['templateEncLayoutX'],
				'Size Y' => $template['templateEncLayoutY'],
				'Parent H' => $template['templateHUnits'],
				'Parent V' => $template['templateVUnits']
			);
			$templatePartitionData = $template['templatePartitionData'];
			$templateImageArray = array(
				'Front Image' => $template['templateFrontImage'],
				'Rear Image' => $template['templateRearImage']
			);
			
			// Validate Template Dimensions
			foreach($templateDimensionArray as $header => $value) {
				if($templateType == 'standard') {
					if($value) {
						$errMsg = 'Standard templates should not have a '.$header.' value on line '.$csvLineNumber.' of "'.$csvFilename.'"';
						array_push($validate->returnData['error'], $errMsg);
					}
				} else if($templateType == 'insert') {
					if (!preg_match('/^[1-9]$|^[1-9][0-9]?$/', $value)){
						$errMsg = 'Invalid '.$header.' value on line '.$csvLineNumber.' of "'.$csvFilename.'"';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
			}
			
			// Validate Template Partition Data
			$errMsg = 'Invalid template structure on line '.$csvLineNumber.' of "'.$csvFilename.'"';
			$depth = 0;
			if(is_array($templatePartitionData) and (count($templatePartitionData) >= 1 and count($templatePartitionData) <= 2)) {
				foreach ($templatePartitionData as $face) {
					$validate->validateTemplateJSON($face[0], $depth, $errMsg);
				}
			} else {
				array_push($validate->returnData['error'], $errorMsg);
			}
			
			// Validate Template Images
			foreach($templateImageArray as $header => $value) {
				if($value) {
					$imgFilenameArray = explode('.', $value);
					if(!preg_match('/^[a-fA-F0-9]{32}$/', $imgFilenameArray[0]) or !in_array(strtolower($imgFilenameArray[1]), $validImgFilenames)) {
						$errMsg = 'Invalid '.$header.' value on line '.$csvLineNumber.' of"'.$csvFilename.'"';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
			}
		}
	}
}

function validateImportedObjects($importedObjectArray, $existingObjectArray, $importedCabinetArray, $importedTemplateArray, $existingTemplateArray, $existingCabinetOccupancyArray, &$validate){
	$arrayOriginalHashes = array();
	$allowedFaceArray = array('front', 'rear');
	
	foreach($importedObjectArray as $object) {
		$line = $object['line'];
		$fileName = $object['fileName'];
		$objectName = $object['objectName'];
		$objectCabinetName = $object['cabinetName'];
		$objectCabinetNameHash = $object['cabinetNameHash'];
		$objectTemplate = $object['templateName'];
		$objectTemplateHash = $object['templateNameHash'];
		$objectType = $object['type'];
		$objectRU = $object['RU'];
		$objectCabinetFace = $object['cabinetFace'];
		$objectOriginalObjectName = $object['originalObjectName'];
		$objectOriginalObjectNameHash = $object['originalObjectNameHash'];
		$posLeft = $object['posLeft'];
		$posTop = $object['posTop'];
		
		$csvFilename = $object['fileName'];
		$csvLineNumber = $object['line'];
		
		// Validate Name
		$validate->validateNameText($objectName, 'Object name on line '.$csvLineNumber.' of "'.$fileName.'".');
		
		// Validate Cabinet
		if(array_key_exists($objectCabinetNameHash, $importedCabinetArray)) {
			$cabinetRUSize = $importedCabinetArray[$objectCabinetNameHash]['size'];
			$cabinetID = $importedCabinetArray[$objectCabinetNameHash]['id'];
			if($objectType == 'cabinetObject') {
				if($importedCabinetArray[$objectCabinetNameHash]['type'] != 'cabinet') {
					$errMsg = 'Cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'" needs to be of type "Cabinet".';
					array_push($validate->returnData['error'], $errMsg);
				}
			} else if($objectType == 'floorplanObject') {
				if($importedCabinetArray[$objectCabinetNameHash]['type'] != 'floorplan') {
					$errMsg = 'Cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'" needs to be of type "Floorplan".';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		} else {
			$errMsg = 'Cabinet on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist in "Cabinets.csv".';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Cabinet Object Specific Validation
		if($objectType == 'cabinetObject') {
			
			// Validate Template
			if(!array_key_exists($objectTemplateHash, $importedTemplateArray)) {
				$errMsg = 'Template referenced on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				if($importedTemplateArray[$objectTemplateHash]['templateType'] != 'standard') {
					$errMsg = 'Template referenced on line '.$csvLineNumber.' of "'.$csvFilename.'" is not a "Standard" template.';
					array_push($validate->returnData['error'], $errMsg);
				} else {
					$templateRUSize = $importedTemplateArray[$objectTemplateHash]['RUSize'];
					$templateMountConfig = $importedTemplateArray[$objectTemplateHash]['templateMountConfig'];
					//$existingTemplate = $existingTemplateArray[$objectTemplateHash];
				}
			}
			
			// Validate Cabinet Face
			$cabinetFaceValidated = $validate->validateInArray($objectCabinetFace, $allowedFaceArray, 'cabinet face on line '.$csvLineNumber.' of "'.$fileName.'".');
		
			// Validate RU
			if($validate->validateRUSize($objectRU, 'Invalid RU on line '.$csvLineNumber.' of '.$fileName)) {
				if($cabinetRUSize and $templateRUSize) {
					$topRU = $objectRU + ($templateRUSize - 1);
					$bottomRU = $objectRU;
					//$templateMountConfig = $existingTemplate['templateMountConfig'];
					
					if($cabinetFaceValidated) {
						if($templateMountConfig == 0) {
							for($x=$bottomRU; $x<=$topRU; $x++) {
								if(in_array($x, $existingCabinetOccupancyArray[$cabinetID][$cabinetFace])) {
									$errMsg = 'Object on line '.$csvLineNumber.' of "'.$csvFilename.'" collides with another object.';
									array_push($validate->returnData['error'], $errMsg);
									break;
								}
							}
						} else if($templateMountConfig == 1) {
							for($x=$bottomRU; $x<=$topRU; $x++) {
								if(in_array($x, $existingCabinetOccupancyArray[$cabinetID]['front']) or in_array($x, $existingCabinetOccupancyArray[$cabinetID]['rear'])) {
									$errMsg = 'Object on line '.$csvLineNumber.' of "'.$csvFilename.'" collides with another object.';
									array_push($validate->returnData['error'], $errMsg);
									break;
								}
							}
						}
					}
					
					if($topRU > $cabinetRUSize) {
						$errMsg = 'Object on line '.$csvLineNumber.' of "'.$csvFilename.'" extends beyond cabinet size.';
						array_push($validate->returnData['error'], $errMsg);
					}
				}
			}
		} else {
			$validate->validateID($posLeft, 'floorplan object X on line '.$csvLineNumber.' of "'.$csvFilename.'".');
			$validate->validateID($posTop, 'floorplan object Y on line '.$csvLineNumber.' of "'.$csvFilename.'".');
		}
		
		// Validate Original Object
		if($objectOriginalObjectName != '') {
			if(!array_key_exists($objectOriginalObjectNameHash, $existingObjectArray)) {
				$errMsg = 'Original object on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Check for original duplicates
			if(in_array($objectOriginalObjectNameHash, $arrayOriginalHashes)) {
				$errMsg = 'Duplicate original object on line '.$csvLineNumber.' of "'.$csvFilename.'"';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($arrayOriginalHashes, $objectOriginalObjectNameHash);
			}
		}
	}
}

function validateImportedInserts($importedInsertArray, $existingInsertArray, $importedObjectArray, $importedTemplateArray, &$validate){
	$arrayOriginalHashes = array();
	$allowedFaceArray = array('front', 'rear');
	
	foreach($importedInsertArray as $insert) {
		$objectNameHash = $insert['objectNameHash'];
		$objectFace = strtolower($insert['objectFace']);
		$slotID = $insert['slotID'];
		$insertName = $insert['insertName'];
		$originalInsert = $insert['originalInsert'];
		$originalInsertHash = $insert['originalInsertHash'];
		$templateNameHash = $insert['templateNameHash'];
		
		$csvFilename = $insert['fileName'];
		$csvLineNumber = $insert['line'];
		
		// Validate Object
		if(!array_key_exists($objectNameHash, $importedObjectArray)) {
			$errMsg = 'Object referenced on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Validate Object Face
		$objectFaceValidated = $validate->validateInArray($objectFace, $allowedFaceArray, 'cabinet face on line '.$csvLineNumber.' of "'.$csvFilename.'".');
		
		// Validate Slot ID
		$slotIDValidated = $validate->validateSlotID($slotID, 'slot ID on line '.$csvLineNumber.' of "'.$csvFilename.'".');
		
		// Validate Name
		$validate->validateNameText($insertName, 'Insert name on line '.$csvLineNumber.' of "'.$csvFilename.'".');
		
		// Validate Original Insert
		if($originalInsert != '') {
			if(!array_key_exists($originalInsertHash, $existingInsertArray)) {
				$errMsg = 'Original insert on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
			// Check for original duplicates
			if(in_array($originalInsertHash, $arrayOriginalHashes)) {
				$errMsg = 'Duplicate original insert on line '.$csvLineNumber.' of "'.$csvFilename.'".';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($arrayOriginalHashes, $originalInsertHash);
			}
		}
		
		// Validate Template
		if(!array_key_exists($templateNameHash, $importedTemplateArray)) {
			$errMsg = 'Template referenced on line '.$csvLineNumber.' of "'.$csvFilename.'" does not exist.';
			array_push($validate->returnData['error'], $errMsg);
		} else if($importedTemplateArray[$templateNameHash]['templateType'] != 'insert') {
			$errMsg = 'Template referenced on line '.$csvLineNumber.' of "'.$csvFilename.'" is not an insert.';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		// Validate Compatibility
		$parent = $importedObjectArray[$objectNameHash];
		$parentTemplate = $importedTemplateArray[$parent['templateNameHash']];
		$insertTemplate = $importedTemplateArray[$templateNameHash];
		preg_match_all("/\d+|[a-b]+/", $slotID, $matches);
		$depth = $matches[0][0];
		$face = $objectFace == 'front' ? 0 : 1;
		$compatible = true;
		
		if($csvLineNumber == 26) {
			error_log('Debug: face='.$face.' depth='.$depth);
			error_log('Debug: EncPartition='.json_encode($parentTemplate['templatePartitionData']));
		}
		
		if($parentPartition = retrievePartition($parentTemplate['templatePartitionData'][$face], $depth)) {
			if($insertTemplate['templateFunction'] != $parentTemplate['templateFunction']) {
				$compatible = false;
			}
			
			// Check to see if the enclosure "strict" property is present...
			// this is an added feature so it may not be if importing from older versions
			if(isset($parentTemplate['encStrict'])) {
				
				// Is the enclosure configured as "strict"?
				if($parentTemplate['encStrict'] == 'yes') {
					
					// Check that the insert is compatible
					if($insertTemplate['templateEncLayoutX'] != $parentPartition['encLayoutX']) {
						$compatible = false;
					} else if($insertTemplate['templateEncLayoutY'] != $parentPartition['encLayoutY']) {
						$compatible = false;
					} else if($insertTemplate['templateHUnits'] != $parentPartition['hunits']) {
						$compatible = false;
					} else if($insertTemplate['templateVUnits'] != $parentPartition['vunits']) {
						$compatible = false;
					}
				}
			}
		} else {
			$errMsg = 'Could not find partition for insert on line '.$csvLineNumber.' of "'.$csvFilename.'".';
			array_push($validate->returnData['error'], $errMsg);
		}
		
		if(!$compatible) {
			$errMsg = 'Insert on line '.$csvLineNumber.' of "'.$csvFilename.'" is not compatible with slot.';
			array_push($validate->returnData['error'], $errMsg);
		}
	}
}

function validateImportedConnections(&$qls, &$importedConnectionArray, $portArray, $importedObjectArray, $importedTrunkArray, &$validate){
	$cableEndIDArray = array();
	$portNameHashArray = array();
	
	// Iterate through all imported connections
	foreach($importedConnectionArray as &$connection) {
		
		// Check to see if object port exists
		$portNameHash = $connection['portNameHash'];
		if(isset($portArray[$portNameHash]) or isset($importedTrunkArray[$portNameHash])) {
			if(!in_array($portNameHash, $portNameHashArray)) {
				array_push($portNameHashArray, $portNameHash);
				
				if(isset($portArray[$portNameHash])) {
					
					// Object is a normal object and can be found in $portArray
					$port = $portArray[$portNameHash];
					$objID = $port['objID'];
					$face = $port['face'];
					$depth = $port['depth'];
					$portID = $port['portID'];
					
				} else {
					
					// Object port must be a walljack port
					$objectName = $importedTrunkArray[$portNameHash]['name'];
					$trunkPeerPortNameHash = $importedTrunkArray[$portNameHash]['peerPortNameHash'];
					$peerPort = $portArray[$trunkPeerPortNameHash];
					$objectNameHash = md5($objectName);
					$object = $importedObjectArray[$objectNameHash];
					$objID = $object['id'];
					$face = 0;
					$depth = 0;
					$portID = $peerPort['portID'];
					
				}
				
				$connection['objID'] = $objID;
				$connection['face'] = $face;
				$connection['depth'] = $depth;
				$connection['portID'] = $portID;
				
				// Is a peer port specified?
				if($peerPortNameHash = $connection['peerPortNameHash']) {
					if(isset($portArray[$peerPortNameHash]) or isset($importedTrunkArray[$peerPortNameHash])) {
						if(!in_array($peerPortNameHash, $portNameHashArray)) {
							array_push($portNameHashArray, $peerPortNameHash);
							
							if(isset($portArray[$portNameHash])) {
								
								// Object is a normal object and can be found in $portArray
								$peerPort = $portArray[$peerPortNameHash];							
								$peerObjID = $peerPort['objID'];
								$peerFace = $peerPort['face'];
								$peerDepth = $peerPort['depth'];
								$peerPortID = $peerPort['portID'];
								
							} else {
								
								// Object port must be a walljack port
								$objectName = $importedTrunkArray[$peerPortNameHash]['name'];
								$trunkPeerPortNameHash = $importedTrunkArray[$peerPortNameHash]['peerPortNameHash'];
								$peerPort = $portArray[$trunkPeerPortNameHash];
								$objectNameHash = md5($objectName);
								$object = $importedObjectArray[$objectNameHash];
								$peerObjID = $object['id'];
								$peerFace = 0;
								$peerDepth = 0;
								$peerPortID = $peerPort['portID'];
								
							}
							
							$connection['peerObjID'] = $peerObjID;
							$connection['peerFace'] = $peerFace;
							$connection['peerDepth'] = $peerDepth;
							$connection['peerPortID'] = $peerPortID;
						} else {
							$errMsg = 'PortB on line '.$connection['line'].' of file "'.$connection['fileName'].'" is a duplicate.';
							array_push($validate->returnData['error'], $errMsg);
						}
						
					} else {
						if($connection['peerCode39']) {
							$connection['peerObjID'] = 0;
							$connection['peerFace'] = 0;
							$connection['peerDepth'] = 0;
							$connection['peerPortID'] = 0;
						} else if(1 == 2) {
							// Check if peer object is walljack
						} else {
							$errMsg = 'PortB on line '.$connection['line'].' of file "'.$connection['fileName'].'" does not exist.';
							array_push($validate->returnData['error'], $errMsg);
						}
					}
				}
			} else {
				$errMsg = 'PortA on line '.$connection['line'].' of file "'.$connection['fileName'].'" is a duplicate.';
				array_push($validate->returnData['error'], $errMsg);
			}
		} else {
			if($connection['code39']) {
				// Managed cable is not connected to anything.
				$connection['objID'] = 0;
				$connection['face'] = 0;
				$connection['depth'] = 0;
				$connection['portID'] = 0;
				
				$connection['peerObjID'] = 0;
				$connection['peerFace'] = 0;
				$connection['peerDepth'] = 0;
				$connection['peerPortID'] = 0;
			} else {
				$errMsg = 'PortA on line '.$connection['line'].' of file "'.$connection['fileName'].'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		// Check to see if cableA end ID is valid
		if($cableEndID = $connection['code39']) {
			$cableEndID = (int)base_convert($cableEndID, 36, 10);
			if(in_array($cableEndID, $cableEndIDArray)) {
				$errMsg = 'CableA ID on line '.$connection['line'].' of file "'.$connection['fileName'].'" is a duplicate.';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($cableEndIDArray, $cableEndID);
				$connection['cableEndID'] = $cableEndID;
			}
		}
		
		// Check to see if cableB end ID is valid
		if($peerCableEndID = $connection['peerCode39']) {
			$peerCableEndID = (int)base_convert($peerCableEndID, 36, 10);
			if(in_array($peerCableEndID, $cableEndIDArray)) {
				$errMsg = 'CableB ID on line '.$connection['line'].' of file "'.$connection['fileName'].'" is a duplicate.';
				array_push($validate->returnData['error'], $errMsg);
			} else {
				array_push($cableEndIDArray, $peerCableEndID);
				$connection['peerCableEndID'] = $peerCableEndID;
			}
		}
		
		if($cableEndID) {
			// Check to see if cableA connector type is valid
			if($connection['connector']) {
				$connector = $connection['connector'];
				$found = false;
				foreach($qls->App->connectorTypeValueArray as $value => $row) {
					if($connector == strtolower($row['name'])) {
						$connection['connector'] = $value;
						$found = true;
					}
				}
				if(!$found) {
					$errMsg = 'CableA connector type on line '.$connection['line'].' of file "'.$connection['fileName'].'" is invalid.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		}
		
		if($peerCableEndID) {
			// Check to see if cableB connector type is valid
			if($connection['peerConnector']) {
				$peerConnector = $connection['peerConnector'];
				$found = false;
				foreach($qls->App->connectorTypeValueArray as $value => $row) {
					if($connector == strtolower($row['name'])) {
						$connection['peerConnector'] = $value;
						$found = true;
					}
				}
				if(!$found) {
					$errMsg = 'CableB connector type on line '.$connection['line'].' of file "'.$connection['fileName'].'" is invalid.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		}
		
		if($cableEndID or $peerCableEndID) {
			
			// Check to see if media type is valid
			if($connection['mediaType']) {
				$mediaType = $connection['mediaType'];
				$found = false;
				foreach($qls->App->mediaTypeValueArray as $value => $row) {
					if($mediaType == strtolower($row['name'])) {
						$mediaTypeValue = $value;
						$connection['mediaTypeValue'] = $mediaTypeValue;
						$found = true;
					}
				}
				if(!$found) {
					$errMsg = 'Media type on line '.$connection['line'].' of file "'.$connection['fileName'].'" is invalid.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
			
			// Check to see if length is valid
			if($connection['length']) {
				$length = $connection['length'];
				if($connection['mediaType']) {
					$categoryTypeID = $qls->App->mediaTypeValueArray[$mediaTypeValue]['category_type_id'];
					$categoryType = $qls->App->mediaCategoryTypeArray[$categoryTypeID];
					if(preg_match('/\d+/', $length)) {
						preg_match('/\d+/', $length, $match);
						$cableLength = $match[0];
						if($categoryType['unit_of_length'] == 'm.') {
							$connection['length'] = $qls->App->convertMetersToMillimeters($cableLength);
						} else if($categoryType['unit_of_length'] == 'ft.') {
							$connection['length'] = $qls->App->convertFeetToMillimeters($cableLength);
						} else {
							$connection['length'] = 0;
						}
					} else {
						$errMsg = 'Length on line '.$connection['line'].' of file "'.$connection['fileName'].'" is invalid.';
						array_push($validate->returnData['error'], $errMsg);
					}
				} else {
					$errMsg = 'Length on line '.$connection['line'].' of file "'.$connection['fileName'].'" requires media type.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		}
		
		unset($portNameHash);
		unset($cableEndID);
		unset($connector);
		
		unset($peerPortNameHash);
		unset($peerCableEndID);
		unset($peerConnector);
	}
}

function validateImportedTrunks($qls, &$importedTrunkArray, $portArray, $importedObjectArray, &$validate){
	
	foreach($importedTrunkArray as &$trunk) {
		
		$walljackPortIDArray = array();
		$csvLine = $trunk['line'];
		$csvFileName = $trunk['fileName'];
		$portNameHash = $trunk['portNameHash'];
		$peerPortNameHash = $trunk['peerPortNameHash'];
		
		// Trunk entry is not a walljack
		if(isset($portArray[$portNameHash])) {
			
			// Collect trunk object information
			$port = $portArray[$portNameHash];
			$objID = $port['objID'];
			$face = $port['face'];
			$depth = $port['depth'];
			$portID = $port['portID'];
			$obj = $qls->objectArray[$objID];
			$objTemplateID = $obj['template_id'];
			
			// Store the A side Info
			$trunk['aObjID'] = $objID;
			$trunk['aFace'] = $face;
			$trunk['aDepth'] = $depth;
			$trunk['aPortID'] = $portID;
			
		} else {
			
			$nameHash = $trunk['nameHash'];
			if(isset($importedObjectArray[$nameHash])) {
				
				// Collect trunk object information
				$objID = $importedObjectArray[$nameHash]['id'];
				$obj = $qls->App->objectArray[$objID];
				$objTemplateID = $obj['template_id'];
				$objTemplate = $qls->App->templateArray[$objTemplateID];
				$objTemplateType = $objTemplate['templateType'];
				
				// At this point, the trunk object should be a walljack
				if($objTemplateType == 'walljack') {
					
					// Create variable to track walljack portID
					if(!isset($walljackPortIDArray[$portNameHash])) {
						$walljackPortIDArray[$portNameHash] = 0;
					}
					
					// Store the A side Info
					$trunk['aObjID'] = $objID;
					$trunk['aFace'] = 0;
					$trunk['aDepth'] = 0;
					$trunk['aPortID'] = $walljackPortIDArray[$portNameHash]++;
					
				} else {
					$errMsg = 'Port on line '.$csvLine.' of file "'.$csvFileName.'" does not exist.';
					array_push($validate->returnData['error'], $errMsg);
				}
				
			} else {
				$errMsg = 'Object on line '.$csvLine.' of file "'.$csvFileName.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		
		
		
		
		
		// Process the trunk peer
		if(isset($portArray[$peerPortNameHash])) {
			
			// Collect trunk peer information
			$peerPort = $portArray[$peerPortNameHash];
			$peerID = $peerPort['objID'];
			$peerFace = $peerPort['face'];
			$peerDepth = $peerPort['depth'];
			$peerPortID = $peerPort['portID'];
			$peer = $qls->objectArray[$peerID];
			$peerTemplateID = $peer['template_id'];
			
			// Store the B side Info
			$trunk['bObjID'] = $peerID;
			$trunk['bFace'] = $peerFace;
			$trunk['bDepth'] = $peerDepth;
			$trunk['bPortID'] = $peerPortID;
			
		} else {
			
			$peerTrunkEntry = $importedTrunkArray[$peerPortNameHash];
			$nameHash = $peerTrunkEntry['nameHash'];
			if(isset($importedObjectArray[$nameHash])) {
				
				// Collect trunk peer object information
				$objID = $importedObjectArray[$nameHash]['id'];
				$obj = $qls->App->objectArray[$objID];
				$objTemplateID = $obj['template_id'];
				$objTemplate = $qls->App->templateArray[$objTemplateID];
				$objTemplateType = $objTemplate['templateType'];
				
				// At this point, the trunk peer object should be a walljack
				if($objTemplateType == 'walljack') {
					
					// Create variable to track walljack portID
					if(!isset($walljackPortIDArray[$peerPortNameHash])) {
						$walljackPortIDArray[$peerPortNameHash] = 0;
					}
					
					// Store the B side Info
					$trunk['bObjID'] = $objID;
					$trunk['bFace'] = 0;
					$trunk['bDepth'] = 0;
					$trunk['bPortID'] = $walljackPortIDArray[$peerPortNameHash]++;
					
				} else {
					$errMsg = 'Port on line '.$csvLine.' of file "'.$csvFileName.'" does not exist.';
					array_push($validate->returnData['error'], $errMsg);
				}
			} else {
				$errMsg = 'Object on line '.$csvLine.' of file "'.$csvFileName.'" does not exist.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
		
		if($objTemplateID and $peerTemplateID) {
			
			// Gather compatibility info for obj & peer
			$objCompatibility = $qls->compatibilityArray[$objTemplateID][$face][$depth];
			$peerCompatibility = $qls->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
			
			// Gather template type for obj & peer
			$objTemplateType = $objCompatibility['templateType'];
			$peerTemplateType = $peerCompatibility['templateType'];
			
			// Gather media category for obj & peer
			$objMediaCategory = $objCompatibility['mediaCategory'];
			$peerMediaCategory = $peerCompatibility['mediaCategory'];
			
			// Check that portTotal is the same
			if($objTemplateID > 3 and $peerTemplateID > 3) {
				
				$objPortTotal = $objCompatibility['portTotal'];
				$peerPortTotal = $peerCompatibility['portTotal'];
				
				if($objPortTotal != $peerPortTotal) {
					$errMsg = 'Trunk peer port groups on line '.$csvLine.' of file "'.$csvFileName.'" must have the same total number of ports.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
			
			// Check that medaCategory is the same
			if($objMediaCategory != $peerMediaCategory) {
				$errMsg = 'Trunk peer port(s) on line '.$csvLine.' of file "'.$csvFileName.'" must be of the same media type.';
				array_push($validate->returnData['error'], $errMsg);
			}
		}
	}
}

function validateImportedImages($dir, $imageType, &$validate){
	// Validate Images
	$imageExtensionArray = array('png','jpg','gif','jpeg');
	
	// Template Images
	$imageArray = array();
	if ($imageDir = opendir($_SERVER['DOCUMENT_ROOT'].'/userUploads/'.$dir.'/')) {
		while (false !== ($imageFile = readdir($imageDir))) {
			if ($imageFile != "." && $imageFile != "..") {
				$imageFileArray = explode('.', $imageFile);
				$extension = strtolower($imageFileArray[count($imageFileArray)-1]);
				$existingFilename = $_SERVER['DOCUMENT_ROOT'].'/images/'.$dir.'/'.$imageFile;
				$importFilename = $_SERVER['DOCUMENT_ROOT'].'/userUploads/'.$dir.'/'.$imageFile;
				// Do not copy if file already exists
				if (!file_exists($existingFilename)) {
					// Do not copy if file is larger than 1MB
					if (filesize($importFilename) < 1000000) {
						// Do not copy if file extension is not image
						if (in_array($extension, $imageExtensionArray)) {
							array_push($imageArray, $imageFile);
						} else {
							$errMsg = ucfirst($imageType).' image file extension is not valid: '.$imageFile;
							array_push($validate->returnData['error'], $errMsg);
						}
					} else {
						$errMsg = ucfirst($imageType).' image file is too large: '.$imageFile;
						array_push($validate->returnData['error'], $errMsg);
					}
				}
			}
		}
		closedir($imageDir);
	} else {
		$errMsg = 'Could not open '.$imageType.' image directory.';
		array_push($validate->returnData['error'], $errMsg);
	}
	
	return $imageArray;
}


// Cabinet Changes
function findCabinetAdds($importedCabinetArray, $existingCabinetArray){
	$return = array();
	foreach($importedCabinetArray as $cabinet) {
		$cabinetNameHash = $cabinet['nameHash'];
		if($cabinet['originalCabinetName'] == '') {
			$return[$cabinetNameHash] = $cabinet;
		}
	}
	
	return $return;
}

function findCabinetEdits(&$importedCabinetArray, $existingCabinetArray){
	$return = array();
	foreach($importedCabinetArray as &$cabinet) {
		$originalCabinetNameHash = $cabinet['originalCabinetNameHash'];
		if(array_key_exists($originalCabinetNameHash, $existingCabinetArray)) {
			$existingCabinet = $existingCabinetArray[$originalCabinetNameHash];
			$addToArray = false;
			if($existingCabinet['type'] != $cabinet['type']) {
				$addToArray = true;
			} else if($existingCabinet['size'] != $cabinet['size']) {
				$addToArray = true;
			} else if($existingCabinet['left'] != $cabinet['left']) {
				$addToArray = true;
			} else if($existingCabinet['right'] != $cabinet['right']) {
				$addToArray = true;
			}
			if($addToArray) {
				array_push($return, $cabinet);
			}
		}
	}
	
	return $return;
}

function findCabinetDeletes($importedCabinetArray, $existingCabinetArray){
	$return = array();
	foreach($existingCabinetArray as $cabinet) {
		$cabinetNameHash = $cabinet['nameHash'];
		if(!array_key_exists($cabinetNameHash, $importedCabinetArray)) {
			array_push($return, $cabinet);
		}
	}
	
	return $return;
}



// Path Changes
function findPathAdds($importedPathArray, $existingPathArray){
	$return = array();
	foreach($importedPathArray as $path) {
		$pathHash = $path['pathHash'];
		if(!array_key_exists($pathHash, $existingPathArray)) {
			$return[$pathHash] = $path;
		}
	}
	
	return $return;
}

function findPathEdits(&$importedPathArray, $existingPathArray){
	$return = array();
	foreach($importedPathArray as &$path) {
		$pathHash = $path['pathHash'];
		if(array_key_exists($pathHash, $existingPathArray)) {
			$existingPath = $existingPathArray[$pathHash];
			$addToArray = false;
			if($existingPath['distance'] != $path['distance']) {
				$addToArray = true;
			} else if($existingPath['entrance'] != $path['entrance']) {
				$addToArray = true;
			} if($existingPath['notes'] != $path['notes']) {
				$addToArray = true;
			}
			if($addToArray) {
				$path['id'] = $existingPath['id'];
				array_push($return, $path);
			}
		}
	}
	
	return $return;
}

function findPathDeletes($importedPathArray, $existingPathArray){
	$return = array();
	foreach($existingPathArray as $path) {
		$pathHash = $path['pathHash'];
		if(!array_key_exists($pathHash, $importedPathArray)) {
			array_push($return, $path);
		}
	}
	
	return $return;
}



// Object Changes
function findObjectAdds($importedObjectArray){
	$return = array();
	
	foreach($importedObjectArray as $object) {
		if($object['originalObjectName'] == '') {
			array_push($return, $object);
		}
	}
	
	return $return;
}

function findObjectEdits($importedObjectArray, $existingObjectArray){
	$return = array();
	
	foreach($importedObjectArray as $object) {
		if($object['originalObjectName'] != '') {
			$originalObjectNameHash = $object['originalObjectNameHash'];
			$existingObject = $existingObjectArray[$originalObjectNameHash];
			
			$addObject = false;
			if($object['objectName'] != $existingObject['name']) {
				$addObject = true;
			} else if($object['RU'] != $existingObject['RU']) {
				$addObject = true;
			} else if($object['cabinetFace'] != $existingObject['cabinetFace']) {
				$addObject = true;
			} else if($object['env_tree_id'] != $existingObject['env_tree_id']) {
				$addObject = true;
			}
			
			if($addObject) {
				array_push($return, $object);
			}
		}
	}
	
	return $return;
}

function findObjectDeletes($importedObjectArray, $existingObjectArray){
	$return = array();
	
	foreach($existingObjectArray as $existingObject) {
		
		$existingObjectNameHash = $existingObject['nameHash'];
		$addToObjectDeletes = true;
		foreach($importedObjectArray as $importedObject) {
			
			if($importedObject['originalObjectName'] != '') {
				$originalObjectNameHash = $importedObject['originalObjectNameHash'];
				
				if($existingObjectNameHash == $originalObjectNameHash) {
					$addToObjectDeletes = false;
				}
			}
		}
		
		if($addToObjectDeletes) {
			array_push($return, $existingObject);
		}
	}
	
	return $return;
}



// Insert Changes
function findInsertAdds($importedInsertArray){
	$return = array();
	
	foreach($importedInsertArray as $insert) {
		if($insert['originalInsert'] == '') {
			array_push($return, $insert);
		}
	}
	
	return $return;
}

function findInsertEdits($importedInsertArray, $existingInsertArray){
	$return = array();
	
	foreach($importedInsertArray as $insert) {
		$addToInsertEdits = false;
		if($insert['originalInsert'] != '') {
			$originalInsertHash = $insert['originalInsertHash'];
			$existingInsert = $existingInsertArray[$originalInsertHash];
			
			if($insert['objectNameHash'] != $existingInsert['objectNameHash']) {
				$addToInsertEdits = true;
			} else if($insert['parent_face'] != $existingInsert['parent_face']) {
				$addToInsertEdits = true;
			} else if(strtolower($insert['slotID']) != strtolower($existingInsert['slotID'])) {
				$addToInsertEdits = true;
			} else if(strtolower($insert['insertName']) != strtolower($existingInsert['insertName'])) {
				$addToInsertEdits = true;
			}
		}
		
		if($addToInsertEdits) {
			array_push($return, $insert);
		}
	}
	
	return $return;
}

function findInsertDeletes($importedInsertArray, $existingInsertArray){
	$return = array();
	
	foreach($existingInsertArray as $existingInsert) {
		
		$existingInsertNameHash = $existingInsert['insertNameHash'];
		$addToInsertDeletes = true;
		foreach($importedInsertArray as $importedInsert) {
			
			if($importedInsert['originalInsert'] != '') {
				$originalInsertHash = $importedInsert['originalInsertHash'];
				
				if($existingInsertNameHash == $originalInsertHash) {
					$addToInsertDeletes = false;
				}
			}
		}
		
		if($addToInsertDeletes) {
			array_push($return, $existingInsert);
		}
	}
	
	return $return;
}



// Category Changes
function findCategoryAdds($importedCategoryArray){
	$return = array();
	
	foreach($importedCategoryArray as $category) {
		if($category['originalCategoryName'] == '') {
			array_push($return, $category);
		}
	}
	
	return $return;
}

function findCategoryEdits($importedCategoryArray, $existingCategoryArray){
	$return = array();
	
	foreach($importedCategoryArray as $category) {
		if($category['originalCategoryName'] != '') {
			$originalCategoryNameHash = $category['originalCategoryNameHash'];
			$existingCategory = $existingCategoryArray[$originalCategoryNameHash];
			
			$addCategory = false;
			if($category['name'] != $existingCategory['name']) {
				$addCategory = true;
			} else if($category['color'] != $existingCategory['color']) {
				$addCategory = true;
			} else if($category['defaultOption'] != $existingCategory['defaultOption']) {
				$addCategory = true;
			}
			
			if($addCategory) {
				array_push($return, $category);
			}
		}
	}
	
	return $return;
}

function findCategoryDeletes($importedCategoryArray, $existingCategoryArray){
	$return = array();
	
	foreach($existingCategoryArray as $existingCategory) {
		
		$existingCategoryNameHash = $existingCategory['nameHash'];
		$addCategory = true;
		foreach($importedCategoryArray as $importedCategory) {
			
			if($importedCategory['originalCategoryName'] != '') {
				$originalCategoryNameHash = $importedCategory['originalCategoryNameHash'];
				
				if($existingCategoryNameHash == $originalCategoryNameHash) {
					$addCategory = false;
				}
			}
		}
		
		if($addCategory) {
			array_push($return, $existingCategory);
		}
	}
	
	return $return;
}



// Template Changes
function findTemplateAdds($importedTemplateArray){
	$return = array();
	
	foreach($importedTemplateArray as $template) {
		if($template['originalTemplateName'] == '') {
			array_push($return, $template);
		}
	}
	
	return $return;
}

function findTemplateEdits($importedTemplateArray, $existingTemplateArray){
	$return = array();
	
	foreach($importedTemplateArray as $template) {
		if($template['originalTemplateName'] != '') {
			$originalTemplateNameHash = $template['originalTemplateNameHash'];
			$existingTemplate = $existingTemplateArray[$originalTemplateNameHash];
			
			$addTemplate = false;
			if($template['templateName'] != $existingTemplate['templateName']) {
				$addTemplate = true;
			} else if($template['categoryNameHash'] != $existingTemplate['categoryNameHash']) {
				$addTemplate = true;
			}
			
			if($addTemplate) {
				array_push($return, $template);
			}
		}
	}
	
	return $return;
}

function findTemplateDeletes($importedTemplateArray, $existingTemplateArray){
	$return = array();
	
	foreach($existingTemplateArray as $existingTemplate) {
		
		if($existingTemplate['id'] > 3) {
			$existingTemplateNameHash = $existingTemplate['templateNameHash'];
			$addTemplate = true;
			foreach($importedTemplateArray as $importedTemplate) {
				
				if($importedTemplate['originalTemplateName'] != '') {
					$originalTemplateNameHash = $importedTemplate['originalTemplateNameHash'];
					
					if($existingTemplateNameHash == $originalTemplateNameHash) {
						$addTemplate = false;
					}
				}
			}
			
			if($addTemplate) {
				array_push($return, $existingTemplate);
			}
		}
	}
	
	return $return;
}


// Process Cabinets
function insertCabinetAdds(&$qls, $cabinetAdds, &$importedCabinetArray, $existingCabinetArray){
	// Insert Adds
	foreach($cabinetAdds as $cabinet) {
		$name = $cabinet['name'];
		error_log('Debug: cabinetName = '.$name);
		$nameHash = $cabinet['nameHash'];
		$parentNameHash = $cabinet['parentNameHash'];
		if(array_key_exists($parentNameHash, $existingCabinetArray)) {
			$parent = $existingCabinetArray[$parentNameHash]['id'];
		} else if(array_key_exists($parentNameHash, $importedCabinetArray)) {
			$parent = $importedCabinetArray[$parentNameHash]['id'];
		} else {
			$parent = '#';
		}
		$type = $cabinet['type'];
		$size = $type == 'cabinet' ? $cabinet['size'] : 42;
		$floorplanImg = $cabinet['floorplanImg'];
		
		$qls->SQL->insert('app_env_tree', array('name', 'parent', 'type', 'size', 'floorplan_img'), array($name, $parent, $type, $size, $floorplanImg));
		$importedCabinetArray[$nameHash]['id'] = $qls->SQL->insert_id();
	}

	foreach($importedCabinetArray as $cabinet) {
		$rowID = $cabinet['id'];
		$cabinetNameHash = $cabinet['nameHash'];
		$cabinetLeftHash = $cabinet['leftHash'];
		$cabinetRightHash = $cabinet['rightHash'];
		
		if($cabinetLeftHash) {
			$addAdjacency = true;
			
			if(array_key_exists($cabinetLeftHash, $existingCabinetArray)) {
				$leftID = $existingCabinetArray[$cabinetLeftHash]['id'];
			} else if(array_key_exists($cabinetLeftHash, $importedCabinetArray)) {
				$leftID = $importedCabinetArray[$cabinetLeftHash]['id'];
			}
			
			if($addAdjacency) {
				$qls->SQL->insert('app_cabinet_adj', array('left_cabinet_id', 'right_cabinet_id'), array($leftID, $rowID));
			}
		}
		
		if($cabinetRightHash) {
			$addAdjacency = true;
			
			if(array_key_exists($cabinetRightHash, $existingCabinetArray)) {
				$rightID = $existingCabinetArray[$cabinetRightHash]['id'];
			} else if(array_key_exists($cabinetRightHash, $importedCabinetArray)) {
				$rightID = $importedCabinetArray[$cabinetRightHash]['id'];
			}
			
			if($addAdjacency) {
				$qls->SQL->insert('app_cabinet_adj', array('left_cabinet_id', 'right_cabinet_id'), array($rowID, $rightID));
			}
		}
	}
}

function updateCabinetEdits(&$qls, $cabinetEdits, $importedCabinetArray, $existingCabinetArray){
	foreach($cabinetEdits as $cabinet) {
		$cabinetNameHash = $cabinet['nameHash'];
		$rowID = $existingCabinetArray[$cabinetNameHash]['id'];
		$type = $cabinet['type'];
		$size = $type == 'cabinet' ? $cabinet['size'] : 42;
		
		$table = 'app_env_tree';
		$set = array('type'=>$type, 'size'=>$size);
		$where = array('id'=>array('=', $rowID));
		$qls->SQL->update($table, $set, $where);
		
		//Update Adjacencies
		$qls->SQL->delete('app_cabinet_adj', array('left_cabinet_id' => array('=', $rowID), 'OR', 'right_cabinet_id' => array('=', $rowID)));
		
		$adjacencySides = array('left', 'right');
		foreach($adjacencySides as $side) {
			if($cabinet[$side]) {
				$sideHash = md5(strtolower($cabinet[$side]));
				$sideID = $importedCabinetArray[$sideHash]['id'];
				$qls->SQL->insert('app_cabinet_adj', array('left_cabinet_id', 'right_cabinet_id'), array($sideID, $rowID));
			}
		}
	}
}

function deleteCabinetDeletes(&$qls, $cabinetDeletes, $cabinetObjects){
	// Remove Deletes (cabinets)
	foreach($cabinetDeletes as $cabinetDelete) {
		$cabinetID = $cabinetDelete['id'];
		
		$qls->SQL->delete('app_env_tree', array('id' => array('=', $cabinetID)));
		$qls->SQL->delete('app_cabinet_adj', array('left_cabinet_id' => array('=', $cabinetID), 'OR', 'right_cabinet_id' => array('=', $cabinetID)));
		$qls->SQL->delete('app_cable_path', array('cabinet_a_id' => array('=', $cabinetID), 'OR', 'cabinet_b_id' => array('=', $cabinetID)));
		
		// Remove Deletes (objects)
		foreach($cabinetObjects[$cabinetID] as $objectDelete) {
			$objectID = $objectDelete['id'];
			
			$qls->SQL->delete('app_object', array('id' => array('=', $objectID)));
			$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
			
			$query = $qls->SQL->select('*', 'app_inventory', array('a_object_id' => array('=', $objectID), 'OR', 'b_object_id' => array('=', $objectID)));
			while($cable = $qls->SQL->fetch_assoc($query)) {
				$attr = array();
				if($cable['a_object_id'] == $objectID) {
					array_push($attr, 'a');
				}
				if($cable['b_object_id'] == $objectID) {
					array_push($attr, 'b');
				}
				
				if($cable['a_id'] == 0 and $cable['b_id'] == 0) {
					
					// This is not a managed cable, so delete entry
					$qls->SQL->delete('app_inventory', array('id' => array('=', $cable['id'])));
					
					// Mark far end as populated if it does not belong to the object being deleted
					if(count($attr) == 1) {
						$oppositeAttr = $attr[0] == 'a' ? 'b' : 'a';
						$qls->SQL->insert('app_populated_port', array(
								'object_id',
								'object_face',
								'object_depth',
								'port_id'
							), array(
								$cable[$oppositeAttr.'_object_id'],
								$cable[$oppositeAttr.'_object_face'],
								$cable[$oppositeAttr.'_object_depth'],
								$cable[$oppositeAttr.'_port_id']
							)
						);
					}
				} else {
					
					// This is a managed cable, so don't delete... just clear data
					foreach($attr as $cableAttr) {
						$qls->SQL->update('app_inventory', array(
								$cableAttr.'_object_id' => 0,
								$cableAttr.'_object_face' => 0,
								$cableAttr.'_object_depth' => 0,
								$cableAttr.'_port_id' => 0
							), array('id' => array('=', $cable['id']))
						);
					}
				}
			}
			$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $objectID)));
		}
	}
}



// Process Paths
function insertPathAdds(&$qls, &$pathAdds, $importedCabinetArray){
	foreach($pathAdds as &$path) {
		foreach($path['cabinets'] as &$cabinet) {
			$cabinet['id'] = $importedCabinetArray[$cabinet['nameHash']]['id'];
		}
		
		insertPath($qls, $path);
	}
	
	return;
}

function updatePathEdits(&$qls, $pathEdits){
	foreach($pathEdits as $path) {
		deletePath($qls, $path);
		insertPath($qls, $path);
	}
	return;
}

function deletePathDeletes(&$qls, $pathDeletes){
	foreach($pathDeletes as $path) {
		deletePath($qls, $path);
	}
	return;
}

function insertPath(&$qls, $path){
	$cabinetAttributes = array();
	foreach($path['cabinets'] as $cabinet) {
		array_push($cabinetAttributes, array(
			'attribute' => $cabinet['attribute'],
			'id' => $cabinet['id']
		));
	}
	$cabinetAAttribute = $cabinetAttributes[0]['attribute'];
	$cabinetBAttribute = $cabinetAttributes[1]['attribute'];
	$cabinetAID = $cabinetAttributes[0]['id'];
	$cabinetBID = $cabinetAttributes[1]['id'];
	$distance = $path['distance'] * 1000;
	$entrance = $path['entrance'];
	$notes = $path['notes'];
	
	$qls->SQL->insert('app_cable_path', array($cabinetAAttribute, $cabinetBAttribute, 'distance', 'path_entrance_ru', 'notes'), array($cabinetAID, $cabinetBID, $distance, $entrance, $notes));
	
	return;
}

function deletePath(&$qls, $path){
	$rowID = $path['id'];
	
	$qls->SQL->delete('app_cable_path', array('id' => array('=', $rowID)));
	
	return;
}



// Process Objects
function insertObjectAdds(&$qls, $objectAdds, &$importedObjectArray, $importedCabinetArray, $importedTemplateArray){
	$systemTemplateArray = array(
		md5('walljack') => 1,
		md5('wap') => 2,
		md5('device') => 3
	);
	
	foreach($objectAdds as $object) {
		$objectType = $object['type'];
		$cabinetID = $importedCabinetArray[$object['cabinetNameHash']]['id'];
		$name = $object['objectName'];
		$posLeft = $object['posLeft'];
		$posTop = $object['posTop'];
		
		if($objectType == 'floorplanObject') {
			
			// Floorplan object
			$templateID = $systemTemplateArray[$object['templateNameHash']];
			$RUSize = null;
			$mountConfig = null;
			$RU = null;
			$face = null;
			
		} else {
			
			// Cabinet object
			$template = $importedTemplateArray[$object['templateNameHash']];
			$templateID = $template['id'];
			$RUSize = $template['templateRUSize'];
			$mountConfig = $template['templateMountConfig'];
			$objectRU = $object['RU'];
			$RU = $objectRU + ($RUSize - 1);
			$face = $object['cabinetFace'];
			
		}
		
		if($face == 'front') {
			$cabinetFront = 0;
			$cabinetBack = $mountConfig == 1 ? 1 : null;
		} else {
			$cabinetBack = 0;
			$cabinetFront = $mountConfig == 1 ? 1 : null;
		}
		
		$qls->SQL->insert('app_object', array('env_tree_id', 'name', 'template_id', 'RU', 'cabinet_front', 'cabinet_back', 'parent_id', 'parent_face', 'parent_depth', 'insertSlotX', 'insertSlotY', 'position_left', 'position_top'), array($cabinetID, $name, $templateID, $RU, $cabinetFront, $cabinetBack, 0, 0, 0, 0, 0, $posLeft, $posTop));
		
		$importedObjectArray[$object['objectNameHash']]['id'] = $qls->SQL->insert_id();
	}
}

function updateObjectEdits(&$qls, $objectEdits, $importedCabinetArray, $existingTemplateArray){
	
	foreach($objectEdits as $object) {
		$template = $existingTemplateArray[$object['templateNameHash']];
		$cabinetID = $importedCabinetArray[$object['cabinetNameHash']]['id'];
		$name = $object['objectName'];
		$RU = $object['RU'];
		$face = $object['cabinetFace'];
		$mountConfig = $template['templateMountConfig'];
		$objectID = $object['id'];
		
		if($face == 'front') {
			$cabinetFront = 0;
			$cabinetBack = $mountConfig == 1 ? 1 : null;
		} else {
			$cabinetBack = 0;
			$cabinetFront = $mountConfig == 1 ? 1 : null;
		}
		
		$updateArray = array(
			'name' => $name,
			'env_tree_id' => $cabinetID,
			'RU' => $RU,
			'cabinet_front' => $cabinetFront,
			'cabinet_back' => $cabinetBack
		);
		
		$qls->SQL->update('app_object', $updateArray, array('id' => array('=', $objectID)));
	}
}

function deleteObjectDeletes(&$qls, $objectDeletes){
	foreach($objectDeletes as $object) {
		$objectID = $object['id'];
		
		$qls->SQL->delete('app_object', array('id' => array('=', $objectID)));
		$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $objectID), 'OR', 'b_id' => array('=', $objectID)));
		
		$query = $qls->SQL->select('*', 'app_inventory', array('a_object_id' => array('=', $objectID), 'OR', 'b_object_id' => array('=', $objectID)));
		while($cable = $qls->SQL->fetch_assoc($query)) {
			$attr = array();
			if($cable['a_object_id'] == $objectID) {
				array_push($attr, 'a');
			}
			if($cable['b_object_id'] == $objectID) {
				array_push($attr, 'b');
			}
			
			if($cable['a_id'] == 0 and $cable['b_id'] == 0) {
				
				// This is not a managed cable, so delete entry
				$qls->SQL->delete('app_inventory', array('id' => array('=', $cable['id'])));
				
				// Mark far end as populated if it does not belong to the object being deleted
				if(count($attr) == 1) {
					$oppositeAttr = $attr[0] == 'a' ? 'b' : 'a';
					$qls->SQL->insert('app_populated_port', array(
							'object_id',
							'object_face',
							'object_depth',
							'port_id'
						), array(
							$cable[$oppositeAttr.'_object_id'],
							$cable[$oppositeAttr.'_object_face'],
							$cable[$oppositeAttr.'_object_depth'],
							$cable[$oppositeAttr.'_port_id']
						)
					);
				}
			} else {
				
				// This is a managed cable, so don't delete... just clear data
				foreach($attr as $cableAttr) {
					$qls->SQL->update('app_inventory', array(
							$cableAttr.'_object_id' => 0,
							$cableAttr.'_object_face' => 0,
							$cableAttr.'_object_depth' => 0,
							$cableAttr.'_port_id' => 0
						), array('id' => array('=', $cable['id']))
					);
				}
			}
		}
		$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $objectID)));
	}
}



// Process Inserts
function insertInsertAdds(&$qls, $insertAdds, &$importedInsertArray, $importedObjectArray, $importedCabinetArray, $importedTemplateArray){
	
	foreach($insertAdds as $insert) {
		$parent = $importedObjectArray[$insert['objectNameHash']];
		$cabinet = $importedCabinetArray[$parent['cabinetNameHash']];
		$cabinetID = $cabinet['id'];
		$parentID = $parent['id'];
		$template = $importedTemplateArray[$insert['templateNameHash']];
		$templateID = $template['id'];
		
		$name = $insert['insertName'];
		$parentFace = $insert['parent_face'];
		$slotID = $insert['slotID'];
		preg_match_all("/\d+|[a-b]+/", $slotID, $matches);
		$depth = $matches[0][0];
		$slotY = ord($matches[0][1]) - 97;
		$slotX = $matches[0][2] - 1;
		$RU = 0;
		$cabinetFace = $parent['cabinetFace'];
		if($cabinetFace == 'front') {
			$cabinetFront = 0;
			$cabinetBack = null;
		} else {
			$cabinetBack = 0;
			$cabinetFront = null;
		}
		
		$attributes = array(
			'env_tree_id',
			'name',
			'template_id',
			'RU',
			'cabinet_front',
			'cabinet_back',
			'parent_id',
			'parent_face',
			'parent_depth',
			'insertSlotX',
			'insertSlotY'
		);
		
		$values = array(
			$cabinetID,
			$name,
			$templateID,
			$RU,
			$cabinetFront,
			$cabinetBack,
			$parentID,
			$parentFace,
			$depth,
			$slotX,
			$slotY
		);
		
		$qls->SQL->insert('app_object', $attributes, $values);
		
		$importedInsertArray[$insert['insertNameHash']]['id'] = $qls->SQL->insert_id();
	}
}

function updateInsertEdits(&$qls, $insertEdits, $importedObjectArray, $importedCabinetArray){
	
	foreach($insertEdits as $insert) {
		$insertID = $insert['id'];
		$parent = $importedObjectArray[$insert['objectNameHash']];
		$parentID = $parent['id'];
		$cabinet = $importedCabinetArray[$parent['cabinetNameHash']];
		$cabinetID = $cabinet['id'];
		$name = $insert['insertName'];
		$parentFace = $insert['parent_face'];
		$slotID = $insert['slotID'];
		preg_match_all("/\d+|[a-b]+/", $slotID, $matches);
		$depth = $matches[0][0];
		$slotY = ord($matches[0][1]) - 97;
		$slotX = $matches[0][2] - 1;
		$RU = 0;
		$cabinetFace = $parent['cabinetFace'];
		if($cabinetFace == 'front') {
			$cabinetFront = 0;
			$cabinetBack = null;
		} else {
			$cabinetBack = 0;
			$cabinetFront = null;
		}
		
		$updateArray = array(
			'env_tree_id' => $cabinetID,
			'name' => $name,
			'cabinet_front' => $cabinetFront,
			'cabinet_back' => $cabinetBack,
			'parent_id' => $parentID,
			'parent_face' => $parentFace,
			'parent_depth' => $depth,
			'insertSlotX' => $slotX,
			'insertSlotY' => $slotY
		);
		
		$qls->SQL->update('app_object', $updateArray, array('id' => array('=', $insertID)));
	}
}

function deleteInsertDeletes(&$qls, $insertDeletes){
	foreach($insertDeletes as $insert) {
		$insertID = $insert['id'];
		
		$qls->SQL->delete('app_object', array('id' => array('=', $insertID)));
		$qls->SQL->delete('app_object_peer', array('a_id' => array('=', $insertID), 'OR', 'b_id' => array('=', $insertID)));
		
		$query = $qls->SQL->select('*', 'app_inventory', array('a_object_id' => array('=', $insertID), 'OR', 'b_object_id' => array('=', $insertID)));
		while($cable = $qls->SQL->fetch_assoc($query)) {
			$attr = array();
			if($cable['a_object_id'] == $insertID) {
				array_push($attr, 'a');
			}
			if($cable['b_object_id'] == $insertID) {
				array_push($attr, 'b');
			}
			
			if($cable['a_id'] == 0 and $cable['b_id'] == 0) {
				
				// This is not a managed cable, so delete entry
				$qls->SQL->delete('app_inventory', array('id' => array('=', $cable['id'])));
				
				// Mark far end as populated if it does not belong to the object being deleted
				if(count($attr) == 1) {
					$oppositeAttr = $attr[0] == 'a' ? 'b' : 'a';
					$qls->SQL->insert('app_populated_port', array(
							'object_id',
							'object_face',
							'object_depth',
							'port_id'
						), array(
							$cable[$oppositeAttr.'_object_id'],
							$cable[$oppositeAttr.'_object_face'],
							$cable[$oppositeAttr.'_object_depth'],
							$cable[$oppositeAttr.'_port_id']
						)
					);
				}
			} else {
				
				// This is a managed cable, so don't delete... just clear data
				foreach($attr as $cableAttr) {
					$qls->SQL->update('app_inventory', array(
							$cableAttr.'_object_id' => 0,
							$cableAttr.'_object_face' => 0,
							$cableAttr.'_object_depth' => 0,
							$cableAttr.'_port_id' => 0
						), array('id' => array('=', $cable['id']))
					);
				}
			}
		}
		$qls->SQL->delete('app_populated_port', array('object_id' => array('=', $insertID)));
	}
}



// Process Categories
function insertCategoryAdds(&$qls, $categoryAdds, &$importedCategoryArray) {
	foreach($categoryAdds as $category) {
		$categoryNameHash = $category['nameHash'];
		$categoryName = $category['name'];
		$categoryColor = $category['color'];
		$defaultOption = $category['defaultOption'];
		$categoryAttributes = array(
			'name',
			'color',
			'defaultOption'
		);
		
		$categoryValues = array(
			$categoryName,
			$categoryColor,
			$defaultOption
		);
		
		$qls->SQL->insert('app_object_category', $categoryAttributes, $categoryValues);
		
		$importedCategoryArray[$categoryNameHash]['id'] = $qls->SQL->insert_id();
	}
}

function updateCategoryEdits(&$qls, $categoryEdits){
	foreach($categoryEdits as $category) {
		$categoryID = $category['id'];
		$categoryName = $category['name'];
		$categoryColor = $category['color'];
		$categoryDefaultOption = $category['defaultOption'];
		$categoryUpdateArray = array(
			'name' => $categoryName,
			'color' => $categoryColor,
			'defaultOption' => $categoryDefaultOption
		);
		
		$qls->SQL->update('app_object_category', $categoryUpdateArray, array('id' => array('=', $categoryID)));
	}
}

function deleteCategoryDeletes(&$qls, $categoryDeletes){
	foreach($categoryDeletes as $category) {
		$categoryID = $category['id'];
		$qls->SQL->delete('app_object_category', array('id' => array('=', $categoryID)));
	}
}



// Process Templates
function insertTemplateAdds(&$qls, $templateAdds, &$importedTemplateArray, $importedCategoryArray){
	$mediaTypeArray = array();
	$query = $qls->SQL->select('*', 'shared_mediaType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$mediaTypeArray[$row['value']] = $row;
	}
	
	$objectPortTypeArray = array();
	$query = $qls->SQL->select('*', 'shared_object_portType');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$objectPortTypeArray[$row['value']] = $row;
	}
	
	foreach($templateAdds as $template) {
		$templateNameHash = $template['templateNameHash'];
		$categoryNameHash = $template['categoryNameHash'];
		$categoryID = $importedCategoryArray[$categoryNameHash]['id'];
		$mountConfig = $template['templateMountConfig'];
		if($mountConfig == 'n/a') {
			$mountConfig = null;
		} else if($mountConfig == '2-post') {
			$mountConfig = 0;
		} else if($mountConfig == '4-post') {
			$mountConfig = 1;
		}
		
		$templateName = $template['templateName'];
		$templateCategoryID = $categoryID;
		$templateType = ucfirst($template['templateType']);
		$templateRUSize = $template['templateRUSize'];
		$templateFunction = ucfirst($template['templateFunction']);
		$templateMountConfig = $mountConfig;
		$templateEnclLayoutX = $template['templateEncLayoutX'];
		$templateEnclLayoutY = $template['templateEncLayoutY'];
		$templateHUnits = $template['templateHUnits'];
		$templateVUnits = $template['templateVUnits'];
		$templatePartitionData = json_encode($template['templatePartitionData']);
		$frontImage = $template['templateFrontImage'];
		$rearImage = $template['templateFrontImage'];
		
		$templateAttributes = array(
			'templateName',
			'templateCategory_id',
			'templateType',
			'templateRUSize',
			'templateFunction',
			'templateMountConfig',
			'templateEncLayoutX',
			'templateEncLayoutY',
			'templateHUnits',
			'templateVUnits',
			'templatePartitionData',
			'frontImage',
			'rearImage'
		);
		
		$templateValues = array(
			$templateName,
			$templateCategoryID,
			$templateType,
			$templateRUSize,
			$templateFunction,
			$templateMountConfig,
			$templateEnclLayoutX,
			$templateEnclLayoutY,
			$templateHUnits,
			$templateVUnits,
			$templatePartitionData,
			$frontImage,
			$rearImage
		);
		
		$qls->SQL->insert('app_object_templates', $templateAttributes, $templateValues);
		
		$templateID = $importedTemplateArray[$templateNameHash]['id'] = $qls->SQL->insert_id();
		
		// Gather compatibility data
		$compatibilityArray = array();
		foreach($template['templatePartitionData'] as $face){
			array_push($compatibilityArray, getCompatibilityInfo($face));
		}
		
		foreach($compatibilityArray as $side=>$face) {
			foreach($face as $compatibilityRecord) {
				$portType = $compatibilityRecord['portType'];
				$mediaType = $templateFunction == 'Endpoint' ? 8 : $compatibilityRecord['mediaType'];
				$mediaCategory = $templateFunction == 'Endpoint' ? 5 : $mediaTypeArray[$mediaType]['category_id'];
				$mediaCategoryType = $objectPortTypeArray[$portType]['category_type_id'];
				$portTotal = array_key_exists('portX', $compatibilityRecord) ? $compatibilityRecord['portX'] * $compatibilityRecord['portY'] : 0;
				
				$compatibilityAttributes = array(
					'template_id',
					'side',
					'depth',
					'portLayoutX',
					'portLayoutY',
					'portTotal',
					'encLayoutX',
					'encLayoutY',
					'templateType',
					'partitionType',
					'partitionFunction',
					'portOrientation',
					'portType',
					'mediaType',
					'mediaCategory',
					'mediaCategoryType',
					'direction',
					'flex',
					'hUnits',
					'vUnits',
					'portNameFormat'
				);
				
				$compatibilityValues = array(
					$templateID,
					$side,
					$compatibilityRecord['depth'],
					$compatibilityRecord['portX'],
					$compatibilityRecord['portY'],
					$portTotal,
					$compatibilityRecord['encX'],
					$compatibilityRecord['encY'],
					$templateType,
					$compatibilityRecord['partitionType'],
					$templateFunction,
					$compatibilityRecord['portOrientation'],
					$portType,
					$mediaType,
					$mediaCategory,
					$mediaCategoryType,
					$compatibilityRecord['direction'],
					$compatibilityRecord['flex'],
					$compatibilityRecord['hUnits'],
					$compatibilityRecord['vUnits'],
					json_encode($compatibilityRecord['portNameFormat']),
				);
				$qls->SQL->insert('app_object_compatibility', $compatibilityAttributes, $compatibilityValues);
			}
		}
	}
}

function updateTemplateEdits(&$qls, $templateEdits, $importedCategoryArray){
	foreach($templateEdits as $template) {
		$templateID = $template['id'];
		$templateName = $template['templateName'];
		$categoryNameHash = $template['categoryNameHash'];
		$templateCategoryID = $importedCategoryArray[$categoryNameHash]['id'];
		
		$templateUpdateArray = array(
			'templateName' => $templateName,
			'templateCategory_id' => $templateCategoryID
		);
		
		$qls->SQL->update('app_object_templates', $templateUpdateArray, array('id' => array('=', $templateID)));
	}
}

function deleteTemplateDeletes(&$qls, $templateDeletes){
	foreach($templateDeletes as $template) {
		$templateID = $template['id'];
		$qls->SQL->delete('app_object_templates', array('id' => array('=', $templateID)));
		$qls->SQL->delete('app_object_compatibility', array('template_id' => array('=', $templateID)));
	}
}



// Process Connections
function processConnections(&$qls, $importedConnectionArray){
	$query = "TRUNCATE TABLE ".$qls->config['sql_prefix']."app_inventory";
	$qls->SQL->query($query);
	$query = "TRUNCATE TABLE ".$qls->config['sql_prefix']."app_populated_port";
	$qls->SQL->query($query);
	
	foreach($importedConnectionArray as $connection) {
		$cableEndID = $connection['cableEndID'];
		$code39 = $connection['code39'];
		$connector = $connection['connector'];
		$objID = $connection['objID'];
		$face = $connection['face'];
		$depth = $connection['depth'];
		$portID = $connection['portID'];
		
		$peerCableEndID = $connection['peerCableEndID'];
		$peerCode39 = $connection['peerCode39'];
		$peerConnector = $connection['peerConnector'];
		$peerObjID = $connection['peerObjID'];
		$peerFace = $connection['peerFace'];
		$peerDepth = $connection['peerDepth'];
		$peerPortID = $connection['peerPortID'];
		
		$mediaType = $connection['mediaTypeValue'];
		$length = $connection['length'];

		
		$a_id = $cableEndID ? $cableEndID : 0;
		$a_code39 = $cableEndID ? base_convert($cableEndID, 10, 36) : 0;
		$a_connector = $connector ? $connector : 0;
		$a_object_id = $objID ? $objID : 0;
		$a_port_id = $portID ? $portID : 0;
		$a_object_face = $face ? $face : 0;
		$a_object_depth = $depth ? $depth : 0;
		$b_id = $peerCableEndID ? $peerCableEndID : 0;
		$b_code39 = $peerCableEndID ? base_convert($peerCableEndID, 10, 36) : 0;
		$b_connector = $peerConnector ? $peerConnector : 0;
		$b_object_id = $peerObjID ? $peerObjID : 0;
		$b_port_id = $peerPortID ? $peerPortID : 0;
		$b_object_face = $peerFace ? $peerFace : 0;
		$b_object_depth = $peerDepth ? $peerDepth : 0;
		$mediaType = $mediaType ? $mediaType : 0;
		$length = $length ? $length : 0;
		$editable = ($a_code39 and $a_connector and $b_code39 and $b_connector and $mediaType and $length) ? 0 : 1;
		$active = ($a_code39 or $b_code39) ? 1 : 0;
		
		if(($a_object_id and $b_object_id) or ($a_id or $b_id)) {
			// Insert into inventory table
			$tableAttributes = array(
				'a_id',
				'a_code39',
				'a_connector',
				'a_object_id',
				'a_object_face',
				'a_object_depth',
				'a_port_id',
				'b_id',
				'b_code39',
				'b_connector',
				'b_object_id',
				'b_object_face',
				'b_object_depth',
				'b_port_id',
				'mediaType',
				'length',
				'editable',
				'active'
			);
			
			$tableValues = array(
				$a_id,
				$a_code39,
				$a_connector,
				$a_object_id,
				$a_object_face,
				$a_object_depth,
				$a_port_id,
				$b_id,
				$b_code39,
				$b_connector,
				$b_object_id,
				$b_object_face,
				$b_object_depth,
				$b_port_id,
				$mediaType,
				$length,
				$editable,
				$active
			);
			
			$qls->SQL->insert('app_inventory', $tableAttributes, $tableValues);
		} else {
			// Insert into populated port table
			$tableAttributes = array(
				'object_id',
				'object_face',
				'object_depth',
				'port_id',
			);
			
			$tableValues = array(
				$a_object_id,
				$a_object_face,
				$a_object_depth,
				$a_port_id
			);
			
			$qls->SQL->insert('app_populated_port', $tableAttributes, $tableValues);
		}
	}
}




// Process Trunks
function processTrunks($qls, $importedTrunkArray, $importedObjectArray, $portArray){
	$query = "TRUNCATE TABLE ".$qls->config['sql_prefix']."app_object_peer";
	$qls->SQL->query($query);
	$completedArray = array();
	
	foreach($importedTrunkArray as $trunk) {
		
		$aPortNameHash = $trunk['portNameHash'];
		$bPortNameHash = $trunk['peerPortNameHash'];
		
		if(!in_array($aPortNameHash, $completedArray) and !in_array($aPortNameHash, $completedArray)) {
			
			array_push($completedArray, $aPortNameHash);
			array_push($completedArray, $bPortNameHash);
			
			$aObjectNameHash = $trunk['nameHash'];
			$aObject = $importedObjectArray[$aObjectNameHash];
			$aType = $aObject['type'];
			$aID = $aObject['id'];
			$aObj = $qls->objectArray[$aID];
			$aTemplateID = $aObj['template_id'];
			$aTemplate = $qls->templateArray[$aTemplateID];
			$aTemplateFunction = $aTemplate['templateFunction'];
			$aEndpoint = ($aTemplateFunction == 'Endpoint') ? 1 : 0;
			
			$trunkPeer = $importedTrunkArray[$bPortNameHash];
			$bObjectNameHash = $trunkPeer['nameHash'];
			$bObject = $importedObjectArray[$bObjectNameHash];
			$bType = $bObject['type'];
			$bID = $bObject['id'];
			$bObj = $qls->objectArray[$bID];
			$bTemplateID = $bObj['template_id'];
			$bTemplate = $qls->templateArray[$bTemplateID];
			$bTemplateFunction = $bTemplate['templateFunction'];
			$bEndpoint = ($bTemplateFunction == 'Endpoint') ? 1 : 0;
			
			$floorplanPeer = ($aType == 'floorplanObject' or $bType == 'floorplanObject') ? 1 : 0;
			
			// Insert into populated port table
			$tableAttributes = array(
				'a_id',
				'a_face',
				'a_depth',
				'a_port',
				'a_endpoint',
				'b_id',
				'b_face',
				'b_depth',
				'b_port',
				'b_endpoint',
				'floorplan_peer'
			);
			
			$tableValues = array(
				$trunk['aObjID'],
				$trunk['aFace'],
				$trunk['aDepth'],
				$trunk['aPortID'],
				$aEndpoint,
				$trunk['bObjID'],
				$trunk['bFace'],
				$trunk['bDepth'],
				$trunk['bPortID'],
				$bEndpoint,
				$floorplanPeer
			);
			
			$qls->SQL->insert('app_object_peer', $tableAttributes, $tableValues);
		}
	}
}



// Misc.
function populateImportedPathCabinetIDs(&$importedPathArray, $importedCabinetArray){
	foreach($importedPathArray as &$path) {
		foreach($path['cabinets'] as &$cabinet) {
			$cabinet['id'] = $importedCabinetArray[$cabinet['nameHash']]['id'];
		}
	}
	
	return;
}

function populateImportedObjectCabinetIDs(&$importedObjectArray, $importedCabinetArray){
	foreach($importedObjectArray as &$object) {
		$object['env_tree_id'] = $importedCabinetArray[$object['cabinetNameHash']]['id'];
	}
	
	return;
}

function getCompatibilityInfo($face, $dataArray=array(), &$depthCounter=0){
	foreach($face as $element){
		$partitionType = $element['partitionType'];
		if($partitionType == 'Generic') {
			if(isset($element['children'])){
				$depthCounter++;
				$dataArray = getCompatibilityInfo($element['children'], $dataArray, $depthCounter);
			}
			
		} else if($partitionType == 'Connectable') {
			$tempArray = array();
			$tempArray['depth'] = $depthCounter;
			$tempArray['portX'] = $element['portLayoutX'];
			$tempArray['portY'] = $element['portLayoutY'];
			$tempArray['partitionType'] = $element['partitionType'];
			$tempArray['portOrientation'] = $element['portOrientation'];
			$tempArray['portType'] = $element['portType'];
			$tempArray['mediaType'] = $element['mediaType'];
			$tempArray['direction'] = $element['direction'];
			$tempArray['hUnits'] = $element['hunits'];
			$tempArray['vUnits'] = $element['vunits'];
			$tempArray['flex'] = $element['flex'];
			$tempArray['portNameFormat'] = $element['portNameFormat'];
			array_push($dataArray, $tempArray);
		
		} else if($partitionType == 'Enclosure') {
				$tempArray = array();
				$tempArray['depth'] = $depthCounter;
				$tempArray['encX'] = $element['encLayoutX'];
				$tempArray['encY'] = $element['encLayoutY'];
				$tempArray['partitionType'] = $element['partitionType'];
				$tempArray['direction'] = $element['direction'];
				$tempArray['hUnits'] = $element['hunits'];
				$tempArray['vUnits'] = $element['vunits'];
				$tempArray['flex'] = $element['flex'];
				array_push($dataArray, $tempArray);
		}
		$depthCounter++;
	}
	return $dataArray;
}

function retrievePartition($template, $depth, &$depthCounter=0){
	
	foreach($template as $item) {
		if($depthCounter == $depth) {
			return $item;
		} else {
			if(isset($item['children'])) {
				$depthCounter++;
				return retrievePartition($item['children'], $depth, $depthCounter);
			}
		}
		$depthCounter++;
	}
	return false;
}

function buildCompatibilityArray(&$qls){
	$compatibilityArray = array();
	
	$query = $qls->SQL->select('*', 'app_object_compatibility');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$compatibilityArray[$row['template_id']][$row['side']][$row['depth']] = $row;
	}
	
	return $compatibilityArray;
}

function buildObjectArray(&$qls){
	$objectArray = array();
	
	$query = $qls->SQL->select('*', 'app_object');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$objectArray[$row['id']] = $row;
	}
	
	return $objectArray;
}

function buildEnvTreeArray(&$qls){
	$envTreeArray = array();
	$query = $qls->SQL->select('*', 'app_env_tree');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$envTreeArray[$row['id']] = $row;
	}
	
	return $envTreeArray;
}

function buildPortArray(&$qls){
	
	$portArray = array();
	
	foreach($qls->App->objectArray as $objID => $obj) {
		$objName = $obj['nameString'];
		$objTemplateID = $obj['template_id'];
		$template = $qls->App->templateArray[$objTemplateID];
		$templateType = $template['templateType'];
		
		if(isset($qls->App->compatibilityArray[$objTemplateID])) {
			foreach($qls->App->compatibilityArray[$objTemplateID] as $faceID => $faceElement) {
				foreach($faceElement as $depth => $compatibility) {
					$partitionType = $compatibility['partitionType'];
					if($partitionType == 'Connectable') {
						if($templateType == 'walljack') {
							//error_log('here1');
							if(isset($qls->App->peerArray[$objID][$faceID][$depth]['peerArray'])) {
								//error_log('here2');
								foreach($qls->App->peerArray[$objID][$faceID][$depth]['peerArray'] as $peerID => $peer) {
									//error_log('here3');
									foreach($peer as $peerFaceID => $peerFace) {
										//error_log('here4');
										foreach($peerFace as $peerDepth => $partition) {
											//error_log('here5');
											$peerObj = $qls->App->objectArray[$peerID];
											$peerObjName = $peerObj['nameString'];
											$peerTemplateID = $peerObj['template_id'];
											$peerCompatibility = $qls->App->compatibilityArray[$peerTemplateID][$peerFaceID][$peerDepth];
											$peerObjPortNameFormat = json_decode($peerCompatibility['portNameFormat'], true);
											$peerObjPortTotal = $peerCompatibility['portTotal'];
											foreach($partition as $portPair) {
												//error_log('here6');
												$peerPortID = $portPair[1];
												$portID = $portPair[0];
												$peerObjPortName = $qls->App->generatePortName($peerObjPortNameFormat, $peerPortID, $peerObjPortTotal);
												$portNameArray = array($objName, $peerObjPortName, $portID);
												$objPortNameString = implode('.', $portNameArray);
												$portNameStringHash = md5(strtolower($objPortNameString));
												//error_log('Debug: portArray Entry = '.$objPortNameString.' - '.$portNameStringHash);
												$portArray[$portNameStringHash] = array(
													'objID' => $objID,
													'face' => $faceID,
													'depth' => $depth,
													'portID' => $portID,
													'portName' => $peerObjPortName,
													'portNameString' => $objPortNameString
												);
											}
										}
									}
								}
							}
						} else {
							$portLayoutX = $compatibility['portLayoutX'];
							$portLayoutY = $compatibility['portLayoutY'];
							$portTotal = $portLayoutX * $portLayoutY;
							$portNameFormat = json_decode($compatibility['portNameFormat'], true);
							for($portID=0; $portID<$portTotal; $portID++) {
								$portName = $qls->App->generatePortName($portNameFormat, $portID, $portTotal);
								$portNameArray = array($objName, $portName);
								$objPortNameString = implode('.', $portNameArray);
								$portNameStringHash = md5(strtolower($objPortNameString));
								//error_log('Debug: portArray Entry = '.$objPortNameString.' - '.$portNameStringHash);
								$portArray[$portNameStringHash] = array(
									'objID' => $objID,
									'face' => $faceID,
									'depth' => $depth,
									'portID' => $portID,
									'portName' => $portName,
									'portNameString' => $objPortNameString
								);
							}
						}
					}
				}
			}
		}
	}
	
	return $portArray;
}

function clearAppTables(&$qls){
	// Clear app tables if import restore
	$tableArray = array(
		'app_cabinet_adj',
		'app_cable_path',
		'app_env_tree',
		'app_inventory',
		'app_object',
		'app_object_category',
		'app_object_compatibility',
		'app_object_peer',
		'app_object_templates',
		'app_populated_port'
	);
	
	foreach($tableArray as $table) {
		$qls->SQL->query('TRUNCATE TABLE '.$qls->config['sql_prefix'].$table);
	}
	
	// Restore floorplan object templates
	// Floorplan object template values
	$objectTemplateValuesArray = array(
		array('Walljack', 'walljack', 'Passive'),
		array('WAP', 'wap', 'Endpoint'),
		array('Device', 'device', 'Endpoint')
	);
	
	// Object template columns
	$objectTemplateColumns = array(
		'templateName',
		'templateType',
		'templateFunction'
	);
	
	// Add object templates
	foreach($objectTemplateValuesArray as $objectTemplateValues) {
		$qls->SQL->insert('app_object_templates', $objectTemplateColumns, $objectTemplateValues);
	}
	
	// Floorplan object compatibility values
	$objectCompatibilityValuesArray = array(
		array('1', null, null, null, 'walljack', 'Connectable', 'Passive', '1', '8', '1', '1', null),
		array('2', '1', '1', '1', 'wap', 'Connectable', 'Endpoint', '1', '8', '1', '1', '[{\"type\":\"static\",\"value\":\"NIC\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]'),
		array('3', '1', '1', '1', 'device', 'Connectable', 'Endpoint', '1', '8', '1', '1', '[{\"type\":\"static\",\"value\":\"NIC\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]')
	);
	
	// Object compatibility columns
	$objectCompatibilityColumns = array(
		'template_id',
		'portLayoutX',
		'portLayoutY',
		'portTotal',
		'templateType',
		'partitionType',
		'partitionFunction',
		'portType',
		'mediaType',
		'mediaCategory',
		'mediaCategoryType',
		'portNameFormat'
	);
	
	// Add object compatibility
	foreach($objectCompatibilityValuesArray as $objectCompatibilityValues) {
		$qls->SQL->insert('app_object_compatibility', $objectCompatibilityColumns, $objectCompatibilityValues);
	}
}

?>