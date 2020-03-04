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
		$pathArray = array();
		$pathArray2 = array();
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
		$aObj = $qls->App->objectArray[$endpointAObjID];
		$aObjTemplateID = $aObj['template_id'];
		$aObjTemplate = $qls->App->templateArray[$aObjTemplateID];
		$aObjFunction = $aObjTemplate['templateFunction'];
		
		if($aObjFunction == 'Endpoint') {
			if(isset($qls->App->peerArray[$endpointAObjID][$endpointAObjFace][$endpointAObjDepth])) {
				
				$peerData = $qls->App->peerArray[$endpointAObjID][$endpointAObjFace][$endpointAObjDepth];
				$farEndpointIsTrunked = true;
				
				$endpointAObj = $qls->App->objectArray[$peerData['peerID']];
				$endpointAObjFace = $peerData['peerFace'];
				$endpointAObjDepth = $peerData['peerDepth'];
			} else {
				$farEndpointIsTrunked = false;
				$endpointAObj = $aObj;
			}
		} else {
			$endpointAObj = $aObj;
		}
		
		// If object is an endpoint
		$bObj = $qls->App->objectArray[$endpointBObjID];
		$bObjTemplateID = $bObj['template_id'];
		$bObjTemplate = $qls->App->templateArray[$bObjTemplateID];
		$bObjFunction = $bObjTemplate['templateFunction'];
		
		if($bObjFunction == 'Endpoint') {
			if(isset($qls->App->peerArray[$endpointBObjID][$endpointBObjFace][$endpointBObjDepth])) {
				
				$peerData = $qls->App->peerArray[$endpointBObjID][$endpointBObjFace][$endpointBObjDepth];
				$farEndpointIsTrunked = true;
				
				$endpointBObj = $qls->App->objectArray[$peerData['peerID']];
				$endpointBObjFace = $peerData['peerFace'];
				$endpointBObjDepth = $peerData['peerDepth'];
			} else {
				$farEndpointIsTrunked = false;
				$endpointBObj = $bObj;
			}
		} else {
			$endpointBObj = $bObj;
		}

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
				'templateDepth' => $row['depth']
			);
		}
error_log('Debug (workingArray): '.json_encode($workingArray));
		foreach($workingArray as $mediaTypeID => $workingMediaType) {
			$compatibilityType = '';
			$compatibilityType = ($mediaTypeID != 8 and $compatibilityType == '') ? $qls->App->mediaTypeValueArray[$mediaTypeID]['name'] : $compatibilityType;
			foreach($workingMediaType as $mediaCategoryID => $workingMediaCategory) {
				$compatibilityType = ($mediaCategoryID != 5 and $compatibilityType == '') ? $mediaCategoryTable[$mediaCategoryID]['name'] : $compatibilityType;
				foreach($workingMediaCategory as $mediaCategoryTypeID => $workingMediaCategoryTypeArray) {
					foreach($workingMediaCategoryTypeArray as $workingMediaCategoryType) {
						$compatibilityType = $compatibilityType == '' ? $qls->App->mediaCategoryTypeArray[$mediaCategoryTypeID]['name'] : $compatibilityType;
						if(!isset($compatibleTemplateArray[$compatibilityType])) {
							$compatibleTemplateArray[$compatibilityType] = array();
						}
						$templateID = $workingMediaCategoryType['templateID'];
						if(!isset($compatibleTemplateArray[$compatibilityType][$templateID])) {
							$compatibleTemplateArray[$compatibilityType][$templateID] = array();
						}
						
						array_push($compatibleTemplateArray[$compatibilityType][$templateID], $workingMediaCategoryType);
					}
				}
			}
		}
error_log('Debug (compatibleTemplateArray): '.json_encode($compatibleTemplateArray));
		// Build array containing all cabinets
		$cabinetArray = array();
		$queryCabinets = $qls->SQL->select('*', 'app_env_tree', array('type' => array('=', 'cabinet')));
		while($cabinet = $qls->SQL->fetch_assoc($queryCabinets)) {
			$cabinetArray[$cabinet['id']] = $cabinet;
		}

		// Build array containing all compatible objects
		$objectArray = array();
		foreach($compatibleTemplateArray as $compatibilityType => $compatiblePartitionArray) {
			array_push($objectArray, array('pathType' => $compatibilityType, 'compatibleObjects' => array()));
			foreach($qls->App->objectArray as $object) {
				$objectID = $object['id'];
				$objectTemplateID = $object['template_id'];
				
				// Add object if template is compatible
				if(isset($compatiblePartitionArray[$objectTemplateID])) {
					foreach($compatiblePartitionArray[$objectTemplateID] as $compatibleTemplatePartition) {
						$compatibleTemplateFace = $compatibleTemplatePartition['templateFace'];
						$compatibleTemplateDepth = $compatibleTemplatePartition['templateDepth'];
						$partitionArray = array(
							'face' => $compatibleTemplateFace,
							'depth' => $compatibleTemplateDepth,
						);
						if(!isset($objectArray[count($objectArray)-1]['compatibleObjects'][$objectID])) {
							$object['partition'] = array();
							$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
						}
						array_push($objectArray[count($objectArray)-1]['compatibleObjects'][$objectID]['partition'], $partitionArray);
					}
					
				// Make sure endpointB is included in objectArray, even if template is not compatible
				} else if($objectID == $endpointBObjID) {
					$object['partition'] = array(
						'face' => $endpointBObjFace,
						'depth' => $endpointBObjDepth
					);
					$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
				} else if($objectID == $endpointAObjID) {
					$object['partition'] = array(
						'face' => $endpointAObjFace,
						'depth' => $endpointAObjDepth
					);
					$objectArray[count($objectArray)-1]['compatibleObjects'][$objectID] = $object;
				}
			}
		}
error_log('Debug (compatibleObjectArray): '.json_encode($objectArray));
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
			array_push($reachableArray, array('pathType' => $objSet['pathType'], 'reachableObjects' => array()));
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
error_log('Debug (reachableArray): '.json_encode($reachableArray));
		foreach($reachableArray as $reachable) {
			findPaths($qls, $reachable['reachableObjects'], $reachable['pathType'], $endpointAObj, $endpointAObj, $endpointBObj);
		}
		
		$finalPathArray = array();
		foreach($reachableArray as $reachable) {
			error_log('Debug: '.json_encode($reachable));
			findPaths2($qls, $reachable, $endpointAObj, $endpointAObj, $endpointBObj, $finalPathArray);
		}
		
error_log('Debug (finalPathArray): '.json_encode($finalPathArray));

		// Port type
		// 0 = meters (SFP)
		// 1 = feet (copper)
		// 2 = meters (fiber)
		$portTypeID = 0;
		$portTypeID = $endpointAPortType > $portTypeID ? $endpointAPortType : $portTypeID;
		$portTypeID = $endpointBPortType > $portTypeID ? $endpointBPortType : $portTypeID;
		
		$mediaCategoryTypeID = $qls->App->portTypeValueArray[$portTypeID]['category_type_id'];
		$lengthUnit = ' '.$qls->App->mediaCategoryTypeArray[$mediaCategoryTypeID]['unit_of_length'];

		foreach($pathArray as &$path) {
			foreach($path as &$pathElementPair) {
				if($pathElementPair['distance'] == 0) {
					$distanceString = 'Unknown';
				} else if($portTypeID == 0 or $portTypeID == 2 or $portTypeID == 3 or $portTypeID == 4) {
					$distance = convertToHighestHalfMeter($pathElementPair['distance']);
					$distanceString = $distance.$lengthUnit;
				} else {
					$distance = convertToHighestHalfFeet($pathElementPair['distance']);
					$distanceString = $distance.$lengthUnit;
				}
				$pathElementPair['distance'] = $distanceString;
			}
		}
	}
	$validate->returnData['success'] = $pathArray;
	echo json_encode($validate->returnData);
}

function findPaths2(&$qls, $reachable, $focus, $endpointAObj, $endpointBObj, &$finalPathArray, $workingArray=array(), $visitedObjArray=array()){
	$pathType = $reachable['pathType'];
	$reachableObjArray = $reachable['reachableObjects'];
	
	// Create pathType array if it doesn't exist
	if(!isset($finalPathArray[$pathType])) {
		$finalPathArray[$pathType] = array();
	}
	
	$focusID = $focus['id'];
	$focusFace = $focus['face'];
	$focusDepth = $focus['depth'];
	$focusPort = $focus['port'];
	$focusObj = $qls->App->objectArray[$focusID];
	$focusTemplateID = $focusObj['template_id'];
	$focusCompatibility = $qls->App->compatibilityArray[$focusTemplateID];
	
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
	
	// If focus is endpoinB, add it to finalPathArray
	if($focusID == $endpointBObj['id'] and $focusFace == $endpointBObj['face'] and $focusDepth == $endpointBObj['depth']) {
		
		// Add working path to finalPathArray
		array_push($finalPathArray[$pathType], $workingArray);
		
		return;
	}
	
	// ######################
	// ## Search trunk
	// ######################
	if(isset($qls->App->peerArray[$focusID][$focusFace][$focusDepth])) {
		
		// Get neighbor peer info
		$peer = $qls->App->peerArray[$focusID][$focusFace][$focusDepth];
		$peerID = $peer['peerID'];
		$peerFace = $peer['peerFace'];
		$peerDepth = $peer['peerDepth'];
		
		// Trunk peer cannot be one we've previously looked at
		if(!in_array($peerID, $visitedObjArray)) {
			
			// Add neighbor to visited objects array
			array_push($visitedObjArray, $neighborID);
			
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
			
			error_log('Debug (trunk newFocus): '.$qls->App->generateObjectName($peerID).' ('.$peerID.')');
			
			findPaths2($qls, $reachable, $newFocus, $endpointAObj, $endpointBObj, $finalPathArray, $workingArray, $visitedObjArray);
			
			// Clear last path branch so we can continue searching
			for($arrayCount=0; $arrayCount<1; $arrayCount++) {
				array_pop($workingArray);
			}
		}
	}
	
	// ######################
	// ## Search reachable objects
	// ######################
	if(isset($reachableObjArray[$focusID])) {
		foreach($reachableObjArray[$focusID] as $neighborType => $neighborArray) {
			foreach($neighborArray as $neighbor) {
				
				$neighborID = $neighbor['id'];
				$neighborTemplateID = $neighbor['template_id'];
				$neighborTemplate = $qls->App->templateArray[$neighborTemplateID];
					
				// Neighbor must not have been previously looked at
				if(!in_array($neighborID, $visitedObjArray)) {
					
					// Add neighbor to visited objects array
					array_push($visitedObjArray, $neighborID);
					
					// Iterate over all compatible partitions
					foreach($neighbor['partition'] as $neighborPartition) {
						
						$neighborFace = $neighborPartition['face'];
						$neighborDepth = $neighborPartition['depth'];
					
						// Set flag to test if available port was found
						$commonAvailablePortFound = false;
						
						// If neighbor is endpointB, set neighbor port to selected endpointB port
						if($neighborID == $endpointBObj['id'] and $neighborFace == $endpointBObj['face'] and $neighborDepth == $endpointBObj['depth']) {
							
							$neighborPort = $endpointBObj['port'];
							$commonAvailablePortFound = true;
							
						} else if(isset($qls->App->peerArray[$neighborID][$neighborFace][$neighborDepth])) {
							
							$neighborPeerData = $qls->App->peerArray[$neighborID][$neighborFace][$neighborDepth];
							
							// Get neighbor peer info
							$peerID = $neighborDepthArray['peerID'];
							$peerFace = $neighborDepthArray['peerFace'];
							$peerDepth = $neighborDepthArray['peerDepth'];
							
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
							
							$neighborCompatibility = $qls->App->compatibilityArray[$neighborTemplateID][$neighborFace][$neighborDepth];
							
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
									'mediaTypeID' => $pathType,
									'length' => $neighbor['dist']
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
							
							error_log('Debug (reachable newFocus): '.$qls->App->generateObjectName($neighborID).' ('.$neighborID.')');
							
							findPaths2($qls, $reachable, $newFocus, $endpointAObj, $endpointBObj, $finalPathArray, $workingArray, $visitedObjArray);
							
							// Clear last path branch so we can continue searching
							for($arrayCount=0; $arrayCount<3; $arrayCount++) {
								array_pop($workingArray);
							}
						}
					}
								
							//}
						//}
					//}
				}
			}
		}
	}
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
	
	return;
}

function findPaths(&$qls, $reachableArray, $mediaType, $target, $endpointAObj, $endpointBObj, $workingArray2=array(), $workingArray=array(), $visitedObjs=array(), $visitedCabs=array(), $peerParentArray=array()){
	
	$targetID = $target['id'];
	$targetFace = $target['face'];
	$targetDepth = $target['depth'];
	$targetPort = $target['port'];
	$targetObj = $qls->App->objectArray[$targetID];
	$targetTemplateID = $targetObj['template_id'];
	$targetTemplate = $qls->App->templateArray[$targetTemplateID];
	$targetFunction = $targetTemplate['templateFunction'];
	$targetName = $qls->App->generateObjectPortName($targetID, $targetFace, $targetDepth, $targetPort);
	$targetName = $qls->App->wrapObject($targetID, $targetName);
	
	array_push($workingArray2, array(
		'type' => 'object',
		'data' => array(
			'id' => $targetID,
			'face' => $targetFace,
			'depth' => $targetDepth,
			'port' => $targetPort,
		)
	));
	
	// Explore target trunk peer
	if($targetID == $endpointAObj['id'] and !in_array($targetID, $visitedObjs)) {
		array_push($visitedObjs, $target['id']);
		
		// Target must have a trunk peer
		if(isset($qls->App->peerArray[$targetID][$targetFace][$targetDepth])) {
			$peerData = $qls->App->peerArray[$targetID][$targetFace][$targetDepth];
			
			// Get object string of peer just so we know the object function
			$peerID = $peerData['peerID'];
			$peerFace = $peerData['peerFace'];
			$peerDepth = $peerData['peerDepth'];
			$peerObj = $qls->App->objectArray[$peerID];
			$peerTemplateID = $peerObj['template_id'];
			$peerTemplate = $qls->App->templateArray[$peerTemplateID];
			$peerFunction = $peerTemplate['templateFunction'];
			$peerCabinetID = $peerObj['env_tree_id'];
			
			if($peerFunction != 'Endpoint') {
				if($peerID == $endpointBObj['id'] and $peerFace == $endpointBObj['face'] and $peerDepth == $endpointBObj['depth']) {
					// Add object to path
				} else {
					// Evaluate the peer's cabinet to prevent path feedback loop
					if($peerObj['env_tree_id'] == $target['env_tree_id']) {
						$proceed = true;
					} else {
						if(in_array($peerCabinetID, $visitedCabs)) {
							$proceed = false;
						} else {
							$proceed = true;
							array_push($visitedCabs, $target['env_tree_id']);
						}
					}
					
					if($proceed) {
						
						array_push($workingArray, array(
							'far' => $targetName,
							'farFunction' => $targetFunction
						));
						
						array_push($workingArray2, array(
							'type' => 'trunk',
							'data' => array()
						));
					
						$newTarget = array(
							'id' => $peerID,
							'face' => $peerFace,
							'depth' => $peerDepth,
							'port' => $target['port'],
							'env_tree_id' => $peerCabinetID
						);
						
						findPaths($qls, $reachableArray, $mediaType, $newTarget, $endpointAObj, $endpointBObj, $workingArray2, $workingArray, $visitedObjs, $visitedCabs, $peerParentArray);
						array_pop($workingArray);
						array_pop($workingArray2);
					}
				}
			}
		}
	}

	// Explore reachable objects
	if(isset($reachableArray[$targetID])) {
		foreach($reachableArray[$targetID] as $reachableCategory => $reachableGroup) {
			
			foreach($reachableGroup as $reachableObj) {
				
				$reachableObjID = $reachableObj['id'];
				$reachableObjParentID = $reachableObj['parent_id'];
				if(!isset($peerParentArray[$reachableObjParentID])) {
					$peerParentArray[$reachableObjParentID] = array();
				}
				
				// Reached target endpoint
				if($reachableObjID == $endpointBObj['id']) {
					
					if(isset($target['port'])) {
						$nearPort = $target['port'];
					} else {
						$nearPortArray = getAvailablePortArray($target['id'], $target['face'], $target['depth'], $qls);
						$nearPort = $nearPortArray[0];
					}
					
					$nearObjName = $qls->App->generateObjectPortName($target['id'], $target['face'], $target['depth'], $nearPort);
					$nearObjName = $qls->App->wrapObject($target['id'], $nearObjName);
					$nearObj = $qls->App->objectArray[$target['id']];
					$nearTemplateID = $nearObj['template_id'];
					$nearCompatibility = $qls->App->compatibilityArray[$nearTemplateID][$target['face']][$target['depth']];
					$nearPortTypeValue = $nearCompatibility['portType'];
					$nearPortType = $qls->App->portTypeValueArray[$nearPortTypeValue]['name'];
					
					$farObjName = $qls->App->generateObjectPortName($endpointBObj['id'], $endpointBObj['face'], $endpointBObj['depth'], $endpointBObj['port']);
					$farObjName = $qls->App->wrapObject($endpointBObj['id'], $farObjName);
					$farObj = $qls->App->objectArray[$endpointBObj['id']];
					$farTemplateID = $farObj['template_id'];
					$farCompatibility = $qls->App->compatibilityArray[$farTemplateID][$endpointBObj['face']][$endpointBObj['depth']];
					$farPortTypeValue = $farCompatibility['portType'];
					$farPortType = $qls->App->portTypeValueArray[$farPortTypeValue]['name'];

					array_push($workingArray, array(
						'near' => $nearObjName,
						'nearPortType' => $nearPortType,
						'far' =>  $farObjName,
						'farPortType' => $farPortType,
						'distance' => $reachableObj['dist'],
						'pathType' => $reachableCategory,
						'mediaType' => $mediaType
					));
					
					array_push($workingArray2, array(
						'type' => 'connector',
						'data' => array(
							'code39' => 0,
							'connectorType' => $nearPortTypeValue
						)
					));
					
					array_push($workingArray2, array(
						'type' => 'cable',
						'data' => array(
							'mediaTypeID' => $mediaType,
							'length' => $reachableObj['dist']
						)
					));
					
					array_push($workingArray2, array(
						'type' => 'connector',
						'data' => array(
							'code39' => 0,
							'connectorType' => $farPortTypeValue
						)
					));
					
					array_push($workingArray2, array(
						'type' => 'object',
						'data' => array(
							'id' => $endpointBObj['id'],
							'face' => $endpointBObj['face'],
							'depth' => $endpointBObj['depth'],
							'port' => $endpointBObj['port'],
						)
					));
					
					array_push($GLOBALS['pathArray'], $workingArray);
					array_push($GLOBALS['pathArray2'], $workingArray2);
					array_pop($workingArray);
					for($popCount = 0; $popCount<3; $popCount++) {
						array_pop($workingArray2);
					}
					
				// Reachable object cannot be the starting endpoint or itself
				} else if($reachableObjID != $endpointAObj['id'] and $reachableObjID != $target['id']) {
					
					// Reachable object cannot be one that we've visited already
					if(!in_array($reachableObjID, $visitedObjs)) {
						
						array_push($visitedObjs, $reachableObjID);
						
						// Reachable object should have a trunk peer to be considered
						if(isset($qls->App->peerArray[$reachableObjID])) {
							
							// Narrow down peer by reachable object face
							foreach($qls->App->peerArray[$reachableObjID] as $objFace => $objFaceArray) {
								
								// Narrow down peer by reachable object depth
								foreach($objFaceArray as $objDepth => $peerData) {
									
									$peerID = $peerData['peerID'];
									$peerFace = $peerData['peerFace'];
									$peerDepth = $peerData['peerDepth'];
									
									$objFace = str_replace('-','',$objFace);
									$objDepth = str_replace('-','',$objDepth);
									
									$peerIsEndpointB = $peerID == $endpointBObj['id'] and $peerFace == $endpointBObj['face'] and $peerDepth == $endpointBObj['depth'];
									
									$farPortArray = getAvailablePortArray($reachableObjID, $objFace, $objDepth, $qls);
									$peerPortArray = getAvailablePortArray($peerID, $peerFace, $peerDepth, $qls);
									
									$commonAvailablePort = 0;
									$farPortFound = false;
									foreach($farPortArray as $farPort) {
										if(in_array($farPort, $peerPortArray)) {
											$commonAvailablePort = $farPort;
											$farPortFound = true;
											break;
										}
									}
									
									// Near Object
									$peerObj = $qls->App->objectArray[$peerID];
									$peerCabinetID = $pearObj['env_tree_id'];
									$nearTemplateID = $peerObj['template_id'];
									$nearCompatibility = $qls->App->compatibilityArray[$nearTemplateID][$peerFace][$peerDepth];
									$nearPortTypeValue = $nearCompatibility['portType'];
									$nearPortType = $qls->App->portTypeValueArray[$nearPortTypeValue]['name'];
									
									// Far Object
									$farPort = $peerIsEndpointB ? $endpointBObj['port'] : $commonAvailablePort;
									$farObj = $qls->App->objectArray[$reachableObjID];
									$farTemplateID = $farObj['template_id'];
									$farTemplate = $qls->App->templateArray[$farTemplateID];
									$farFunction = $farTemplate['templateFunction'];
									$farName = $qls->App->generateObjectPortName($reachableObjID, $objFace, $objDepth, $farPort);
									$farName = $qls->App->wrapObject($reachableObjID, $farName);
									
									$farCompatibility = $qls->App->compatibilityArray[$farTemplateID][$objFace][$objDepth];
									$farPortTypeValue = $farCompatibility['portType'];
									$farPortType = $qls->App->portTypeValueArray[$farPortTypeValue]['name'];
									
									// Reachable object cannot be an endpoint... how are we supposed to patch layer1 through a layer2-4 device?
									if($far['function'] != 'Endpoint') {
										
										// Peer object cannot be one that we've visited already
										if(!in_array($peerID, $visitedObjs)) {
											array_push($visitedObjs, $peerID);
											array_push($workingArray, array(
												'near' => $targetName,
												'nearFunction' => $targetFunction,
												'nearPortType' => $nearPortType,
												'far' => $farName,
												'farFunction' => $farFunction,
												'farPortType' => $farPortType,
												'distance' => $reachableObj['dist'],
												'pathType' => $reachableCategory,
												'mediaType' => $mediaType
											));
											
											array_push($workingArray2, array(
												'type' => 'connector',
												'data' => array(
													'code39' => 0,
													'connectorType' => $nearPortTypeValue
												)
											));
											
											array_push($workingArray2, array(
												'type' => 'cable',
												'data' => array(
													'mediaTypeID' => $mediaType,
													'length' => $reachableObj['dist']
												)
											));
											
											array_push($workingArray2, array(
												'type' => 'connector',
												'data' => array(
													'code39' => 0,
													'connectorType' => $farPortTypeValue
												)
											));
											
											array_push($workingArray2, array(
												'type' => 'object',
												'data' => array(
													'id' => $endpointBObj['id'],
													'face' => $endpointBObj['face'],
													'depth' => $endpointBObj['depth'],
													'port' => $endpointBObj['port'],
												)
											));
											
											if($peerIsEndpointB) {
												
												$peerID = $endpointBObj['id'];
												$peerFace = $endpointBObj['face'];
												$peerDepth = $endpointBObj['depth'];
												$peerPort = $endpointBObj['port'];
												$peerObj = $qls->App->objectArray[$peerID];
												$peerTemplateID = $peerObj['template_id'];
												$peerTemplate = $qls->App->templateArray[$peerTemplateID];
												$peerFunction = $peerTemplate['templateFunction'];
												$peerName = $qls->App->generateObjectPortName($peerID, $peerFace, $peerDepth, $peerPort);
												$peerName = $qls->App->wrapObject($peerID, $peerName);
												$farCompatibility = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']];
												$farPortTypeValue = $farCompatibility['portType'];
												$farPortType = $qls->App->portTypeValueArray[$farPortTypeValue]['name'];
												
												array_push($workingArray, array(
													'far' => $peerName,
													'farFunction' => $peerFunction,
													'farPortType' => $farPortType
												));
												array_push($GLOBALS['pathArray'], $workingArray);
												array_push($GLOBALS['pathArray2'], $workingArray2);
												array_pop($workingArray);
												for($popCount = 0; $popCount<3; $popCount++) {
													array_pop($workingArray2);
												}
											} else {
												if(!$peerData['peerEndpoint']) {
													
													$peerParentID = $nearObj['parent_id'];
													
													// Evaluate the peer's cabinet to prevent path feedback loop
													if($neerCabinetID == $target['env_tree_id']) {
														$proceed = true;
													} else {
														if(in_array($neerCabinetID, $visitedCabs)) {
															$proceed = false;
														} else {
															$proceed = true;
															array_push($visitedCabs, $target['env_tree_id']);
															array_push($visitedCabs, $reachableObj['env_tree_id']);
														}
													}
													
													// Evaluate the peer's parent to prevent path feedback loop
													if($proceed) {
														if(in_array($peerParentID, $peerParentArray[$reachableObjParentID])) {
															$proceed = false;
														}
													}
													
													// 
													if($proceed) {
														$proceed = $farPortFound;
													}
													
													if($peerParentID != 0 and !in_array($peerParentID, $peerParentArray[$reachableObjParentID]) and $farPortFound) {
														array_push($peerParentArray[$reachableObjParentID], $peerParentID);
													}
													
													if($proceed) {
														$newTarget = array(
															'id' => $peerID,
															'face' => $peerFace,
															'depth' => $peerDepth,
															'port' => $farPort,
															'env_tree_id' => $peerObj['env_tree_id']
														);
														findPaths($qls, $reachableArray, $mediaType, $newTarget, $endpointAObj, $endpointBObj, $workingArray2, $workingArray, $visitedObjs, $visitedCabs, $peerParentArray);
													}
												}
											}
											array_pop($workingArray);
											for($popCount = 0; $popCount<3; $popCount++) {
												array_pop($workingArray2);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return;
}

function getReachableObjects(&$qls, $objID, $objRU, $objSize, $cabinetID, $objectArray, $reachableCabinetArray, $type){
	// Cab2 PanelA(35) & PanelB(36)
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
							$distance = getDistance($objRU, $objSize, $reachableObjRU, $reachableObjSize, false);
							break;

						case 'adjacent':
							$distance = getDistance($objRU, $objSize, $reachableObjRU, $reachableObjSize, true);
							break;

						case 'path':
							if($reachableCabinet['distance'] == 0) {
								$distance = 'Unknown';
							} else {
								$cabinetSize = $qls->App->envTreeArray[$cabinetID]['size'];
								$reachableCabinetSize = $qls->App->envTreeArray[$reachableCabinet['peerID']]['size'];
								$distance = getDistance($reachableCabinetSize, 1, $reachableObjRU, $reachableObjSize, true);
								$distance = $distance + getDistance($cabinetSize, 1, $objRU, $objSize, true);
								$distance = $distance + $reachableCabinet['distance'];
							}
							break;
					}
					
					$reachableObj['dist'] = $distance;
					/* $object = array(
						'id' => $reachableObj['id'],
						'face' => $reachableObj['face'],
						'depth' => $reachableObj['depth'],
						'templateID' => $reachableObj['template_id'],
						'parent_id' => $reachableObj['parent_id'],
						'env_tree_id' => $reachableObj['env_tree_id'],
						'dist' => $distance
					); */
					array_push($reachableObjects, $reachableObj);
				}
			}
		}
	}
	return $reachableObjects;
}

function getDistance($objARU, $objASize, $objBRU, $objBSize, $adj){
	// Values are in millimeters
	$rackWidth = 482;
	$RUSize = 44.5;
	$verticalMgmtWidth = $adj ? 152 : 0;

	$elevationDifference = getElevationDifference($objARU, $objASize, $objBRU, $objBSize);
	$elevation = $RUSize*($elevationDifference['max'] - $elevationDifference['min']);
	$distanceInMillimeters = $verticalMgmtWidth+$elevation+($rackWidth*2);
	return $distanceInMillimeters;
}

function getRU($ID, &$qls){
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $ID)));
	if($qls->SQL->num_rows($query)) {
		$parentObj = $qls->SQL->fetch_assoc($query);
		$RU = $parentObj['RU'];
	} else {
		$RU = 0;
	}
	return $RU;
}

function getSize($objID, &$qls){
	if(isset($qls->App->objectArray[$objID])) {
		$obj = $qls->App->objectArray[$objID];
		$objTemplateID = $obj['template_id'];
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
