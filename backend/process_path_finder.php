<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');
require_once '../includes/path_functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		
		$maxResults = $data['results'];
		$maxDepth = $data['depth'];
		
		$visitedObjs = array();
		$visitedCabs = array();
		
		$endpointAObjID = $data['endpointA']['objID'];
		$endpointAObjFace = $data['endpointA']['objFace'];
		$endpointAObjDepth = $data['endpointA']['objDepth'];
		$endpointAObjPortID = $data['endpointA']['objPortID'];

		$endpointBObjID = $data['endpointB']['objID'];
		$endpointBObjFace = $data['endpointB']['objFace'];
		$endpointBObjDepth = $data['endpointB']['objDepth'];
		$endpointBObjPortID = $data['endpointB']['objPortID'];

		$portTable = array();
		$query = $qls->SQL->select('*', 'shared_object_portType');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$portTable[$row['value']] = $row;
		}
		
		$mediaCategoryTable = array();
		$query = $qls->SQL->select('*', 'shared_mediaCategory');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$mediaCategoryTable[$row['value']] = $row;
		}

		// Create endpointA object
		// If object is an endpoint
		$endpointAObj = $qls->App->objectArray[$endpointAObjID];
		
		// If object is an endpoint
		$endpointBObj = $qls->App->objectArray[$endpointBObjID];

		$endpointAObj['face'] = $endpointAObjFace;
		$endpointAObj['depth'] = $endpointAObjDepth;
		$endpointAObj['port'] = $endpointAObjPortID;
		
		$endpointBObj['face'] = $endpointBObjFace;
		$endpointBObj['depth'] = $endpointBObjDepth;
		$endpointBObj['port'] = $endpointBObjPortID;

		$endpointACompatibility = $qls->App->compatibilityArray[$endpointAObj['template_id']][$endpointAObj['face']][$endpointAObj['depth']];
		$endpointAPortType = $endpointACompatibility['portType'];
		$endpointAMediaType = $endpointACompatibility['mediaType'];
		$endpointAMediaCategory = $endpointACompatibility['mediaCategory'];
		$endpointAMediaCategoryType = $endpointACompatibility['mediaCategoryType'];
		
		$endpointBCompatibility = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']];
		$endpointBPortType = $endpointBCompatibility['portType'];
		$endpointBMediaType = $endpointBCompatibility['mediaType'];
		$endpointBMediaCategory = $endpointBCompatibility['mediaCategory'];
		$endpointBMediaCategoryType = $endpointBCompatibility['mediaCategoryType'];
		
		// Build an array of queries to find compatible partitions
		// depending on the selected endpoints.
		if($endpointAMediaType == 8) {
			if($endpointAMediaCategory == 5) {
				if($endpointAMediaCategoryType == 4) {
					if($endpointBMediaType == 8) {
						if($endpointBMediaCategory == 5) {
							if($endpointBMediaCategoryType == 4) {
								$compatibilityQuery = array('partitionType' => array('=', 'connectable'));
							} else {
								$compatibilityQuery = array('mediaCategoryType' => array('=', $endpointBMediaCategoryType));
							}
						} else {
							$compatibilityQuery = array('mediaCategory' => array('=', $endpointBMediaCategory));
						}
					} else {
						$compatibilityQuery = array('mediaType' => array('=', $endpointBMediaType));
					}
				} else {
					$compatibilityQuery = array('mediaCategoryType' => array('=', $endpointAMediaCategoryType));
				}
			} else {
				$compatibilityQuery = array('mediaCategory' => array('=', $endpointAMediaCategoryType));
			}
		} else {
			$compatibilityQuery = array('mediaType' => array('=', $endpointAMediaType));
		}
		
		// Categorize all template partitions by media type from most to least specific: mediaType(MM-OM4) to mediaCategoryType(fiber)
		$compatibleTemplateArray = array();
		
		$query = $qls->SQL->select('*', 'app_object_compatibility', $compatibilityQuery);
		$workingArray = array();
		while($row = $qls->SQL->fetch_assoc($query)) {
			$workingArray[$row['mediaType']][$row['mediaCategory']][$row['mediaCategoryType']][] = array(
				'templateID' => $row['template_id'],
				'templateFace' => $row['side'],
				'templateDepth' => $row['depth'],
				'mediaCategoryType' => $row['mediaCategoryType']
			);
		}
		
		foreach($workingArray as $mediaTypeID => $workingMediaType) {
			$compatibilityType = '';
			$compatibilityType = ($mediaTypeID != 8 and $compatibilityType == '') ? $qls->App->mediaTypeValueArray[$mediaTypeID]['name'] : $compatibilityType;
			foreach($workingMediaType as $mediaCategoryID => $workingMediaCategory) {
				$compatibilityType = ($mediaCategoryID != 5 and $compatibilityType == '') ? $mediaCategoryTable[$mediaCategoryID]['name'] : $compatibilityType;
				foreach($workingMediaCategory as $mediaCategoryTypeID => $workingMediaCategoryTypeArray) {
					foreach($workingMediaCategoryTypeArray as $workingMediaCategoryType) {
						$compatibilityType = $compatibilityType == '' ? $qls->App->mediaCategoryTypeArray[$mediaCategoryTypeID]['name'] : $compatibilityType;
						if(!isset($compatibleTemplateArray[$compatibilityType])) {
							$compatibleTemplateArray[$compatibilityType] = array(
								'mediaCategoryTypeID' => $mediaCategoryTypeID,
								'mediaTypeID' => $mediaTypeID,
								'template' => array()
							);
						}
						$templateID = $workingMediaCategoryType['templateID'];
						if(!isset($compatibleTemplateArray[$compatibilityType]['template'][$templateID])) {
							$compatibleTemplateArray[$compatibilityType]['template'][$templateID] = array();
						}
						
						array_push($compatibleTemplateArray[$compatibilityType]['template'][$templateID], $workingMediaCategoryType);
					}
				}
			}
		}
		
		// Build array containing all cabinets
		$cabinetArray = array();
		$queryCabinets = $qls->SQL->select('*', 'app_env_tree', array('type' => array('=', 'cabinet')));
		while($cabinet = $qls->SQL->fetch_assoc($queryCabinets)) {
			$cabinetArray[$cabinet['id']] = $cabinet;
		}

		// Build array containing all compatible objects
		$objectArray = array();
		foreach($compatibleTemplateArray as $compatibilityType => $compatiblePartitionArray) {
			$mediaCategoryTypeID = $compatiblePartitionArray['mediaCategoryTypeID'];
			array_push($objectArray, array('pathType' => $compatibilityType, 'mediaTypeID' => $mediaTypeID, 'mediaCategoryTypeID' => $mediaCategoryTypeID, 'compatibleObjects' => array()));
			foreach($qls->App->objectArray as $object) {
				$objectID = $object['id'];
				$objectTemplateID = $object['template_id'];
				
				// Add object if template is compatible
				if(isset($compatiblePartitionArray['template'][$objectTemplateID])) {
					
					foreach($compatiblePartitionArray['template'][$objectTemplateID] as $compatibleTemplatePartition) {
						$compatibleTemplateFace = $compatibleTemplatePartition['templateFace'];
						$compatibleTemplateDepth = $compatibleTemplatePartition['templateDepth'];
						$partitionArray = array(
							'face' => $compatibleTemplateFace,
							'depth' => $compatibleTemplateDepth
						);
						if(!isset($objectArray[count($objectArray)-1]['compatibleObjects'][$objectID])) {
							$object['partition'] = array();
							$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
						}
						array_push($objectArray[count($objectArray)-1]['compatibleObjects'][$objectID]['partition'], $partitionArray);
					}
					
				// Make sure endpointB is included in objectArray, even if template is not compatible
				} else if($objectID == $endpointBObjID) {
					$object['partition'] = array(array(
						'face' => $endpointBObjFace,
						'depth' => $endpointBObjDepth
					));
					$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
				} else if($objectID == $endpointAObjID) {
					$object['partition'] = array(array(
						'face' => $endpointAObjFace,
						'depth' => $endpointAObjDepth
					));
					$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
				}
			}
		}

		// Build array containing all cabinet adjacencies
		// indexed as $cabinetAdjacencyArray[<cabinetID >]
		$cabinetAdjacencyArray = array();
		$queryCabinetAdjacencies = $qls->SQL->select('*', 'app_cabinet_adj');
		while($cabinetAdjacency = $qls->SQL->fetch_assoc($queryCabinetAdjacencies)) {
			$peerEndpoints = array(array('left', 'right'), array('right', 'left'));
			foreach($peerEndpoints as $endpointAttr) {
				$peerAttr = $endpointAttr[1];
				$endpointAttr = $endpointAttr[0];
				if(!isset($cabinetAdjacencyArray[$cabinetAdjacency[$endpointAttr.'_cabinet_id']])) {
					$cabinetAdjacencyArray[$cabinetAdjacency[$endpointAttr.'_cabinet_id']] = array();
				}
				array_push($cabinetAdjacencyArray[$cabinetAdjacency[$endpointAttr.'_cabinet_id']], array(
					'peerID' => $cabinetAdjacency[$peerAttr.'_cabinet_id']
				));
			}
		}
		
		// Build array containing all cable paths
		// indexed as $cablePathArray[<cabinetID >]
		$cablePathArray = array();
		$queryCablePaths = $qls->SQL->select('*', 'app_cable_path');
		while($cablePath = $qls->SQL->fetch_assoc($queryCablePaths)) {
			$peerEndpoints = array(array('a','b'), array('b','a'));
			foreach($peerEndpoints as $endpointAttr) {
				$peerAttr = $endpointAttr[1];
				$endpointAttr = $endpointAttr[0];
				if(!isset($cablePathArray['cabinet_'.$endpointAttr.'_id'])) {
					$cablePathArray[$cablePath['cabinet_'.$endpointAttr.'_id']] = array();
				}
				array_push($cablePathArray[$cablePath['cabinet_'.$endpointAttr.'_id']], array(
					'peerID' => $cablePath['cabinet_'.$peerAttr.'_id'],
					'distance' => $cablePath['distance']
				));
			}
		}
		
		// Include pod neighbors in cable path array
		// indexed as $cablePathArray[<cabinetID >]
		$queryPods = $qls->SQL->select('*', 'app_env_tree', array('type' => array('=', 'pod')));
		while($pod = $qls->SQL->fetch_assoc($queryPods)) {
			
			$queryPodNeighbors = $qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $pod['id'])));
			$podNeighbors = array();
			while($row = $qls->SQL->fetch_assoc($queryPodNeighbors)){
				array_push($podNeighbors, $row);
			}
			
			foreach($podNeighbors as $neighborA) {
				foreach($podNeighbors as $neighborB) {
					$addPath = $neighborA['id'] != $neighborB['id'] ? true : false;
					$createArray = true;
					
					// Check to see if reachability exists in path array
					if($addPath) {
						if(isset($cablePathArray[$neighborA['id']])) {
							$createArray = false;
							foreach($cablePathArray[$neighborA['id']] as $existing) {
								$addPath = $existing['peerID'] == $neighborB['id'] ? false : true;
							}
						}
					}
					
					// Check to see if reachability exists in adjacency array
					if($addPath) {
						if(isset($cabinetAdjacencyArray[$neighborA['id']])) {
							foreach($cabinetAdjacencyArray[$neighborA['id']] as $existing) {
								$addPath = $existing['peerID'] == $neighborB['id'] ? false : true;
							}
						}
					}
					
					// Add to path array if reachability does not exist
					if($addPath) {
						if($createArray) {
							$cablePathArray[$neighborA['id']] = array();
						}
						
						array_push($cablePathArray[$neighborA['id']], array(
							'peerID' => $neighborB['id'],
							'distance' => 0
						));
					}
				}
			}
		}

		$reachableArray = array();
		foreach($objectArray as $objSet) {
			array_push($reachableArray, array('pathType' => $objSet['pathType'], 'mediaTypeID' => $objSet['mediaTypeID'], 'mediaCategoryTypeID' => $objSet['mediaCategoryTypeID'], 'reachableObjects' => array()));
			foreach($objSet['compatibleObjects'] as $obj) {
				$objID = $obj['id'];
				$objCabinetID = $obj['env_tree_id'];
				$templateID = $obj['template_id'];
				$template = $qls->App->templateArray[$templateID];
				$templateType = $template['templateType'];
				
				if($templateType == 'Insert') {
					$objRU = getRU($obj['parent_id'], $qls);
					$objSize = getSize($obj['parent_id'], $qls);
				} else {
					$objRU = $obj['RU'];
					$objSize = $template['templateRUSize'];
				}
				
				$localCabinetArray = array($objCabinetID => array(array('peerID' => $objCabinetID)));
				
				$localObjects = getReachableObjects($qls, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $localCabinetArray, 'local');	
				
				$adjacentObjects = getReachableObjects($qls, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $cabinetAdjacencyArray, 'adjacent');	
				
				$pathObjects = getReachableObjects($qls, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $cablePathArray, 'path');	

				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['local'] = $localObjects;
				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['adjacent'] = $adjacentObjects;
				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['path'] = $pathObjects;
			}
		}
		
		// Determine previous path type to begin with
		$endpointATemplateID = $endpointAObj['template_id'];
		$endpointATemplate = $qls->App->templateArray[$endpointATemplateID];
		$endpointAFunction = $endpointATemplate['templateFunction'];
		$previousPathType = ($endpointAFunction == 'Endpoint' and isset($qls->App->peerArray[$endpointAObjID][$endpointAObjFace][$endpointAObjDepth])) ? 2 : 0;
		
		$finalPathArray = array();
		
		foreach($reachableArray as $reachable) {
			findPaths2($qls, $maxResults, $maxDepth, $reachable, $endpointAObj, $endpointBObj, $finalPathArray, $previousPathType);
		}
		
		foreach($finalPathArray as $mediaType => &$pathData) {
			foreach($pathData as &$path) {
				$path['pathHTML'] = $qls->App->buildPathFull($path['pathArray'], null);
			}
		}
	}
	
	$validate->returnData['success'] = $finalPathArray;
	echo json_encode($validate->returnData);
}

function findPaths2(&$qls, &$maxResults, &$maxDepth, &$reachable, &$focus, &$endpointBObj, &$finalPathArray, &$previousPathType, $workingArray=array(), $visitedObjArray=array(), $reachableTypeArray=array('local'=>0,'adjacent'=>0,'path'=>0)){
	
	// Path type signals which path should be searched,
	// trunk or reachable.
	$trunkPathType = 1;
	$reachablePathType = 2;
	
	$pathType = $reachable['pathType'];
	$mediaTypeID = $reachable['mediaTypeID'];
	$mediaCategoryTypeID = $reachable['mediaCategoryTypeID'];
	$reachableObjArray = &$reachable['reachableObjects'];
	
	// Create pathType array if it doesn't exist
	if(!isset($finalPathArray[$pathType])) {
		$finalPathArray[$pathType] = array();
	}
	
	// Enforce maximum result constraints
	if(count($finalPathArray[$pathType]) >= $maxResults) {
		return;
	}
	
	// Enforce maximum depth constraints
	foreach($reachableTypeArray as $reachableType) {
		$reachableCount = $reachableCount + $reachableType;
	}
	if($reachableCount > $maxDepth) {
		return;
	}
	
	$focusID = $focus['id'];
	$focusFace = $focus['face'];
	$focusDepth = $focus['depth'];
	$focusPort = $focus['port'];
	$focusObj = &$qls->App->objectArray[$focusID];
	$focusTemplateID = $focusObj['template_id'];
	$focusCompatibility = &$qls->App->compatibilityArray[$focusTemplateID][$focusFace][$focusDepth];
	
	array_push($visitedObjArray, $focusID);
	
	array_push($workingArray, array(
		'type' => 'object',
		'data' => array(
			'id' => $focusID,
			'face' => $focusFace,
			'depth' => $focusDepth,
			'port' => $focusPort,
			'selected' => false
		)
	));
	
	// If focus is endpointB, add it to finalPathArray
	if($focusID == $endpointBObj['id'] and $focusFace == $endpointBObj['face'] and $focusDepth == $endpointBObj['depth']) {
		
		// Add working path to finalPathArray
		array_push($finalPathArray[$pathType], array(
			'pathTypeCountArray' => $reachableTypeArray,
			'pathArray' => $workingArray
		));
		
		$workingArray = null;
		unset($workingArray);
		
		return;
	}
	
	// ######################
	// ## Search trunk
	// ######################
	if($previousPathType == 0 or $previousPathType != $trunkPathType) {
		if(isset($qls->App->peerArray[$focusID][$focusFace][$focusDepth])) {
			
			// Get neighbor peer info
			$peer = &$qls->App->peerArray[$focusID][$focusFace][$focusDepth];
			$peerID = $peer['peerID'];
			$peerFace = $peer['peerFace'];
			$peerDepth = $peer['peerDepth'];
			
			// Trunk peer cannot be one we've previously looked at
			if(!in_array($peerID, $visitedObjArray)) {
				
				// Add trunk
				array_push($workingArray, array(
					'type' => 'trunk',
					'data' => array()
				));
				
				$newFocus = array(
					'id' => $peerID,
					'face' => $peerFace,
					'depth' => $peerDepth,
					'port' => $focusPort
				);
				
				findPaths2($qls, $maxResults, $maxDepth, $reachable, $newFocus, $endpointBObj, $finalPathArray, $trunkPathType, $workingArray, $visitedObjArray, $reachableTypeArray);
				
				// Clear last path branch so we can continue searching
				for($arrayCount=0; $arrayCount<1; $arrayCount++) {
					$workingArray[count($workingArray) - 1] = null;
					array_pop($workingArray);
				}
			}
		}
	}
	
	// ######################
	// ## Search reachable objects
	// ######################
	if($previousPathType == 0 or $previousPathType != $reachablePathType) {
		if(isset($reachableObjArray[$focusID])) {
			foreach($reachableObjArray[$focusID] as $reachableType => $neighborArray) {
				foreach($neighborArray as $neighbor) {
					
					$neighborID = $neighbor['id'];
					$neighborTemplateID = $neighbor['template_id'];
					$neighborTemplate = &$qls->App->templateArray[$neighborTemplateID];
						
					// Neighbor must not have been previously looked at
					if(!in_array($neighborID, $visitedObjArray)) {
						
						// Iterate over all compatible partitions
						foreach($neighbor['partition'] as $neighborPartition) {
							
							$neighborFace = intval($neighborPartition['face']);
							$neighborDepth = intval($neighborPartition['depth']);
						
							// Set flag to test if available port was found
							$commonAvailablePortFound = false;
							
							// Identify first available port
							if($neighborID == $endpointBObj['id'] and $neighborFace == $endpointBObj['face'] and $neighborDepth == $endpointBObj['depth']) {
								// Neighbor is endpointB, set neighbor port to selected endpointB port
								
								$neighborPort = $endpointBObj['port'];
								$commonAvailablePortFound = true;
								
							} else if(isset($qls->App->peerArray[$neighborID][$neighborFace][$neighborDepth])) {
								
								$neighborPeerData = &$qls->App->peerArray[$neighborID][$neighborFace][$neighborDepth];
								
								// Get neighbor peer info
								$peerID = $neighborPeerData['peerID'];
								$peerFace = $neighborPeerData['peerFace'];
								$peerDepth = $neighborPeerData['peerDepth'];
								
								// Get array of available neighbor and peer ports
								$neighborPortArray = $qls->App->getAvailablePortArray($neighborID, $neighborFace, $neighborDepth);
								$peerPortArray = $qls->App->getAvailablePortArray($peerID, $peerFace, $peerDepth);
								
								// Find first available port
								foreach($neighborPortArray as $neighborPort) {
									if(in_array($neighborPort, $peerPortArray)) {
										$commonAvailablePortFound = true;
										break;
									}
								}
								
							}
							
							// If an available port was found, add it to the path
							if($commonAvailablePortFound) {
								
								$neighborCompatibility = &$qls->App->compatibilityArray[$neighborTemplateID][$neighborFace][$neighborDepth];
								$neighborDistRaw = intVal($neighbor['dist']);
								$length = $qls->App->calculateCableLength($mediaTypeID, $neighborDistRaw, $includeUnit=true);
								
								array_push($workingArray, array(
									'type' => 'connector',
									'data' => array(
										'code39' => 0,
										'connectorType' => $focusCompatibility['portType']
									)
								));
								
								array_push($workingArray, array(
									'type' => 'cable',
									'data' => array(
										'mediaTypeID' => $mediaTypeID,
										'length' => $length
									)
								));
								
								array_push($workingArray, array(
									'type' => 'connector',
									'data' => array(
										'code39' => 0,
										'connectorType' => $neighborCompatibility['portType']
									)
								));
								
								$newFocus = array(
									'id' => $neighborID,
									'face' => $neighborFace,
									'depth' => $neighborDepth,
									'port' => $neighborPort
								);
								
								// Increment reachableTypeCount
								$reachableTypeArray[$reachableType]++;
								
								findPaths2($qls, $maxResults, $maxDepth, $reachable, $newFocus, $endpointBObj, $finalPathArray, $reachablePathType, $workingArray, $visitedObjArray, $reachableTypeArray);
								
								// Clear last path branch so we can continue searching
								for($arrayCount=0; $arrayCount<3; $arrayCount++) {
									$workingArray[count($workingArray) - 1] = null;
									array_pop($workingArray);
								}
								
								// Decrement reachableTypeCount
								$reachableTypeArray[$reachableType]--;
							}
						}
						$neighborPartition = null;
						unset($neighborPartition);
					}
				}
				$neighbor = null;
				unset($neighbor);
			}
			$neighborArray = null;
			unset($neighborArray);
		}
	}
	
	$workingArray = null;
	$visitedObjArray = null;
	$reachableTypeArray = null;
	unset($workingArray);
	unset($visitedObjArray);
	unset($reachableTypeArray);
}

function validate($data, &$validate, &$qls){
	$endpointNameArray = array('endpointA', 'endpointB');
	
	foreach($endpointNameArray as $endpointName) {
		if(array_key_exists($endpointName, $data)) {
			foreach($data[$endpointName] as $endpointAttr => $endpointAttrValue) {
				$ref = $endpointName.' '.$endpointAttr;
				$validate->validateID($endpointAttrValue, $ref);
			}
		}
	}
	
	if(isset($data['results'])) {
		$maxResults = $data['results'];
		if(is_int($maxResults)) {
			if($maxResults < 1 or $maxResults > PATH_FINDER_MAX_RESULTS) {
				$errMsg = 'Max results is outside of allowed range.';
				array_push($validate->returnData['error'], $errorMsg);
			}
		} else {
			$errMsg = 'Max results is invalid.';
			array_push($validate->returnData['error'], $errorMsg);
		}
	} else {
		$errMsg = 'Max results is required.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	
	if(isset($data['depth'])) {
		$maxResults = $data['depth'];
		if(is_int($maxResults)) {
			if($maxResults < 1 or $maxResults > PATH_FINDER_MAX_RESULTS) {
				$errMsg = 'Max depth is outside of allowed range.';
				array_push($validate->returnData['error'], $errorMsg);
			}
		} else {
			$errMsg = 'Max depth is invalid.';
			array_push($validate->returnData['error'], $errorMsg);
		}
	} else {
		$errMsg = 'Max depth is required.';
		array_push($validate->returnData['error'], $errorMsg);
	}
	
	return;
}

function getReachableObjects(&$qls, $objID, $objRU, $objSize, $cabinetID, $objectArray, $reachableCabinetArray, $type){
	
	$reachableObjects = array();
	if(isset($reachableCabinetArray[$cabinetID])) {
		foreach($reachableCabinetArray[$cabinetID] as $reachableCabinet) {
			foreach($objectArray as $reachableObj) {
				if($reachableObj['env_tree_id'] == $reachableCabinet['peerID'] and $reachableObj['id'] != $objID) {
					if($qls->App->templateArray[$reachableObj['template_id']]['templateType'] == 'Insert') {
						$reachableObjRU = getRU($reachableObj['parent_id'], $qls);
						$reachableObjSize = getSize($reachableObj['parent_id'], $qls);
					} else if($qls->App->templateArray[$reachableObj['template_id']]['templateType'] == 'Standard') {
						$reachableObjRU = $reachableObj['RU'];
						$reachableObjSize = $qls->App->templateArray[$reachableObj['template_id']]['templateRUSize'];
					}
					switch($type){
						case 'local':
							$distance = getDistance($qls, $objRU, $objSize, $reachableObjRU, $reachableObjSize, false);
							break;

						case 'adjacent':
							$distance = getDistance($qls, $objRU, $objSize, $reachableObjRU, $reachableObjSize, true);
							break;

						case 'path':
							if($reachableCabinet['distance'] == 0) {
								$distance = 'Unknown';
							} else {
								$cabinetSize = $qls->App->envTreeArray[$cabinetID]['size'];
								$reachableCabinetSize = $qls->App->envTreeArray[$reachableCabinet['peerID']]['size'];
								$distance = getDistance($qls, $reachableCabinetSize, 1, $reachableObjRU, $reachableObjSize, true);
								$distance = $distance + getDistance($qls, $cabinetSize, 1, $objRU, $objSize, true);
								$distance = $distance + $reachableCabinet['distance'];
							}
							break;
					}
					
					$reachableObj['dist'] = $distance;
					
					array_push($reachableObjects, $reachableObj);
				}
			}
			$reachableObj = null;
			unset($reachableObj);
		}
		$reachableCabinet = null;
		unset($reachableCabinet);
	}
	return $reachableObjects;
}

function getDistance(&$qls, $objARU, $objASize, $objBRU, $objBSize, $adj){
	// Values are in millimeters
	$rackWidth = 482;
	$RUSize = 44.5;
	$verticalMgmtWidth = $adj ? 152 : 0;

	$elevationDifference = $qls->App->getElevationDifference($objARU, $objASize, $objBRU, $objBSize);
	$elevation = $RUSize*($elevationDifference['max'] - $elevationDifference['min']);
	$distanceInMillimeters = $verticalMgmtWidth+$elevation+($rackWidth*2);
	return $distanceInMillimeters;
}

function getRU($ID, &$qls){
	if(isset($qls->App->objectArray[$ID])) {
		$RU = $qls->App->objectArray[$ID]['RU'];
	} else {
		$RU = 0;
	}
	return $RU;
}

function getSize($objID, &$qls){
	if(isset($qls->App->objectArray[$objID])) {
		$objTemplateID = $qls->App->objectArray[$objID]['template_id'];
		$size = $qls->App->templateArray[$objTemplateID]['templateRUSize'];
	} else {
		$size = 0;
	}
	return $size;
}

// Debug templates
// file_put_contents('filename.output', json_encode($array));
// error_log('Debug (debugName): '.json_encode($array));
?>
