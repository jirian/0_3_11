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
		
		$templateTable = $qls->App->templateArray;
		//$templateTable = buildTemplateTable($qls);

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
		
		$envTreeTable = array();
		$parentTable = array();
		$query = $qls->SQL->select('*', 'app_object');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$envTreeTable[$row['id']] = $row['env_tree_id'];
			$parentTable[$row['id']] = $row['parent_id'];
		}

		// Create endpointA object
		// If object is an endpoint
		$aObj = $qls->App->objectArray[$endpointAObjID];
		
		$aObjTemplateID = $aObj['template_id'];
		$aObjTemplate = $qls->App->templateArray[$aObjTemplateID];
		$aObjFunction = $aObjTemplate['templateFunction'];
		
		//$endpointAObjFunction = $qls->App->compatibilityArray[$aObj['template_id']][$endpointAObjFace][$endpointAObjDepth]['partitionFunction'];
		if($aObjFunction == 'Endpoint') {
			// If object is an endpoint and trunked
			$queryPeer = $qls->SQL->select('*', 'app_object_peer', '(a_id = '.$endpointAObjID.' AND a_face = '.$endpointAObjFace.' AND a_depth = '.$endpointAObjDepth.') OR (b_id = '.$endpointAObjID.' AND b_face = '.$endpointAObjFace.' AND b_depth = '.$endpointAObjDepth.')');
			if($qls->SQL->num_rows($queryPeer)) {
				$farEndpointIsTrunked = true;
				// Create object to append to front of path array
				//$nearEndpointObject = getObjectString($templateTable, $qls, $endpointAObjID, $endpointAObjPortID, $endpointAObjFace, $endpointAObjDepth);
				$nearEndpointName = $qls->App->generateObjectPortName($endpointAObjID, $endpointAObjFace, $endpointAObjDepth, $endpointAObjPortID);
				$nearEndpointName = $qls->App->wrapObject($endpointAObjID, $nearEndpointName);
				// Change endpoint to trunk peer
				$peerEntry = $qls->SQL->fetch_assoc($queryPeer);
				$peerAttr = $endpointAObjID == $peerEntry['a_id'] ? 'b' : 'a';
				$queryEndpoint = $qls->SQL->select('*', 'app_object', array('id' => array('=', $peerEntry[$peerAttr.'_id'])));
				$endpointAObj = $qls->SQL->fetch_assoc($queryEndpoint);
				$endpointAObjFace = $peerEntry[$peerAttr.'_face'];
				$endpointAObjDepth = $peerEntry[$peerAttr.'_depth'];
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
		//$endpointBObjFunction = $qls->App->compatibilityArray[$bObj['template_id']][$endpointBObjFace][$endpointBObjDepth]['partitionFunction'];
		if($bObjFunction == 'Endpoint') {
			// If object is an endpoint and trunked
			$queryPeer = $qls->SQL->select('*', 'app_object_peer', '(a_id = '.$endpointBObjID.' AND a_face = '.$endpointBObjFace.' AND a_depth = '.$endpointBObjDepth.') OR (b_id = '.$endpointBObjID.' AND b_face = '.$endpointBObjFace.' AND b_depth = '.$endpointBObjDepth.')');
			if($qls->SQL->num_rows($queryPeer)) {
				$farEndpointIsTrunked = true;
				// Create object to append to front of path array
				//$farEndpointObject = getObjectString($templateTable, $qls, $endpointBObjID, $endpointBObjPortID, $endpointBObjFace, $endpointBObjDepth);
				$farEndpointName = $qls->App->generateObjectPortName($endpointBObjID, $endpointBObjFace, $endpointBObjDepth, $endpointBObjPortID);
				$farEndpointName = $qls->App->wrapObject($endpointBObjID, $farEndpointName);
				// Change endpoint to trunk peer
				$peerEntry = $qls->SQL->fetch_assoc($queryPeer);
				$peerAttr = $endpointBObjID == $peerEntry['a_id'] ? 'b' : 'a';
				$queryEndpoint = $qls->SQL->select('*', 'app_object', array('id' => array('=', $peerEntry[$peerAttr.'_id'])));
				$endpointBObj = $qls->SQL->fetch_assoc($queryEndpoint);
				$endpointBObjFace = $peerEntry[$peerAttr.'_face'];
				$endpointBObjDepth = $peerEntry[$peerAttr.'_depth'];
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

		$endpointAPortType = $qls->App->compatibilityArray[$endpointAObj['template_id']][$endpointAObj['face']][$endpointAObj['depth']]['portType'];
		$endpointAMediaType = $qls->App->compatibilityArray[$endpointAObj['template_id']][$endpointAObj['face']][$endpointAObj['depth']]['mediaType'];
		$endpointAMediaCategory = $qls->App->compatibilityArray[$endpointAObj['template_id']][$endpointAObj['face']][$endpointAObj['depth']]['mediaCategory'];
		$endpointAMediaCategoryType = $qls->App->compatibilityArray[$endpointAObj['template_id']][$endpointAObj['face']][$endpointAObj['depth']]['mediaCategoryType'];
		
		$endpointBPortType = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']]['portType'];
		$endpointBMediaType = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']]['mediaType'];
		$endpointBMediaCategory = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']]['mediaCategory'];
		$endpointBMediaCategoryType = $qls->App->compatibilityArray[$endpointBObj['template_id']][$endpointBObj['face']][$endpointBObj['depth']]['mediaCategoryType'];
		
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
			$workingArray[$row['mediaType']][$row['mediaCategory']][$row['mediaCategoryType']][] = $row['template_id'];
		}
		
		foreach($workingArray as $mediaTypeID => $workingMediaType) {
			$compatibilityType = '';
			$compatibilityType = ($mediaTypeID != 8 and $compatibilityType == '') ? $qls->App->mediaTypeValueArray[$mediaTypeID]['name'] : $compatibilityType;
			foreach($workingMediaType as $mediaCategoryID => $workingMediaCategory) {
				$compatibilityType = ($mediaCategoryID != 5 and $compatibilityType == '') ? $mediaCategoryTable[$mediaCategoryID]['name'] : $compatibilityType;
				foreach($workingMediaCategory as $mediaCategoryTypeID => $workingMediaCategoryTypeArray) {
					foreach($workingMediaCategoryTypeArray as $workingMediaCategoryType) {
						$compatibilityType = $compatibilityType == '' ? $qls->App->mediaCategoryTypeArray[$mediaCategoryTypeID]['name'] : $compatibilityType;
						if(!array_key_exists($compatibilityType, $compatibleTemplateArray)) {
							$compatibleTemplateArray[$compatibilityType] = array();
						}
						array_push($compatibleTemplateArray[$compatibilityType], $workingMediaCategoryType);
					}
				}
			}
		}
		foreach($compatibleTemplateArray as &$compatibleTemplate) {
			$compatibleTemplate = array_unique($compatibleTemplate);
		}

		// Build array containing all cabinets
		$cabinetArray = array();
		$queryCabinets = $qls->SQL->select('*', 'app_env_tree', array('type' => array('=', 'cabinet')));
		while($cabinet = $qls->SQL->fetch_assoc($queryCabinets)) {
			$cabinetArray[$cabinet['id']] = $cabinet;
		}
		
		// Build array containing all peer relationships
		// indexed as $peerArray[<objID>][<objFace>][<objDepth>]
		$peerArray = array();
		$queryPeers = $qls->SQL->select('*', 'app_object_peer');
		while($peer = $qls->SQL->fetch_assoc($queryPeers)) {
			$peerEndpoints = array(array('a','b'), array('b','a'));
			foreach($peerEndpoints as $endpointAttr) {
				$peerAttr = $endpointAttr[1];
				$endpointAttr = $endpointAttr[0];
				$peerArray['-'.$peer[$endpointAttr.'_id']]['-'.$peer[$endpointAttr.'_face']]['-'.$peer[$endpointAttr.'_depth']] = array(
					'peerID' => $peer[$peerAttr.'_id'],
					'peerFace' => $peer[$peerAttr.'_face'],
					'peerDepth' => $peer[$peerAttr.'_depth'],
					'peerIsEndpoint' => $peer[$peerAttr.'_endpoint'] == 1 ? true : false,
					'env_tree_id' => $envTreeTable[$peer[$peerAttr.'_id']],
					'parent_id' => $parentTable[$peer[$peerAttr.'_id']]
				);
			}
		}

		// Build array containing all compatible objects
		$objectArray = array();
		//$queryObjects = $qls->SQL->select('*', 'app_object');
		foreach($compatibleTemplateArray as $compatibilityType => $compatibilityArray) {
			array_push($objectArray, array('pathType' => $compatibilityType, 'compatibleObjects' => array()));
			foreach($qls->App->objectArray as $object) {
				if(in_array($object['template_id'], $compatibilityArray) or $object['id'] == $endpointAObjID or $object['id'] == $endpointBObjID) {
					$objectArray[count($objectArray)-1]['compatibleObjects'][$object['id']] = $object;
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
			array_push($reachableArray, array('pathType' => $objSet['pathType'], 'reachableObjects' => array()));
			foreach($objSet['compatibleObjects'] as $obj) {
				$templateID = $obj['template_id'];
				$template = $qls->App->templateArray[$templateID];
				$templateType = $template['templateType'];
				if($templateType == 'Insert') {
					$objRU = getRU($obj['parent_id'], $qls);
					$objSize = getSize($obj['parent_id'], $templateTable, $qls);
				} else {
					$objRU = $obj['RU'];
					$objSize = $template['templateRUSize'];
				}
				$objID = $obj['id'];
				$objCabinetID = $obj['env_tree_id'];
				$localCabinetArray = array($objCabinetID => array(array('peerID' => $objCabinetID)));
				
				$localObjects = getReachableObjects($qls, $templateTable, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $localCabinetArray, 'local');	
				
				$adjacentObjects = getReachableObjects($qls, $templateTable, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $cabinetAdjacencyArray, 'adjacent');	
				
				$pathObjects = getReachableObjects($qls, $templateTable, $objID, $objRU, $objSize, $objCabinetID, $objSet['compatibleObjects'], $cablePathArray, 'path');	

				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['local'] = $localObjects;
				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['adjacent'] = $adjacentObjects;
				$reachableArray[count($reachableArray)-1]['reachableObjects'][$objID]['path'] = $pathObjects;
			}
		}
		
		foreach($reachableArray as $reachable) {
			findPaths($qls, $reachable['reachableObjects'], $reachable['pathType'], $endpointAObj, $endpointAObj, $endpointBObj);
		}

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

			/*
			if(isset($nearEndpointObject)) {
				array_unshift($path, array(
					'far' => $nearEndpointObject['obj'],
					'farFunction' => $nearEndpointObject['function']
				));
			}
			*/
			
			if($aObjFunction == 'Endpoint') {
				if($nearEndpointIsTrunked) {
					array_push($path, array(
						'far' => $nearEndpointName,
						'farFunction' => 'Endpoint'
					));
				}
			}

			if($bObjFunction == 'Endpoint') {
				if($farEndpointIsTrunked) {
					array_push($path, array(
						'near' => $farEndpointName,
						'nearFunction' => 'Endpoint'
					));
				}
			}
		}
	}
	$validate->returnData['success'] = $pathArray;
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$endpointNameArray = array('endpointA', 'endpointB');
	
	foreach($endpointNameArray as $endpointName) {
		if(array_key_exists($endpointName, $data)) {
			error_log('Debug: '.json_encode($data[$endpointName]));
			foreach($data[$endpointName] as $endpointAttr => $endpointAttrValue) {
				error_log('Debug: '.$endpointAttr);
				$ref = $endpointName.' '.$endpointAttr;
				$validate->validateID($endpointAttrValue, $ref);
			}
		}
	}
	
	return;
}

function findPaths(&$qls, $reachableArray, $mediaType, $target, $endpointAObj, $endpointBObj, $workingArray=array(), $visitedObjs=array(), $visitedCabs=array(), $peerParentArray=array()){
	
	// Explore target trunk peer
	if($target['id'] == $endpointAObj['id'] and !in_array($target['id'], $visitedObjs)) {
		array_push($visitedObjs, $target['id']);
		// Target must have a trunk peer
		if(isset($GLOBALS['peerArray']['-'.$target['id']]['-'.$target['face']]['-'.$target['depth']])) {
			$peerData = $GLOBALS['peerArray']['-'.$target['id']]['-'.$target['face']]['-'.$target['depth']];
			// Get object string of peer just so we know the object function
			$peerID = $peerData['peerID'];
			$peerObj = $qls->App->objectArray[$peerID];
			$peerTemplateID = $peerObj['template_id'];
			$peerTemplate = $qls->App->templateArray[$peerTemplateID];
			$peerFunction = $peerTemplate['templateFunction'];
			//error_log('debug: here1');
			//$peer = getObjectString($GLOBALS['templateTable'], $qls, $peerData['peerID'], $target['port'], $peerData['peerFace'], $peerData['peerDepth']);
			if($peerFunction != 'Endpoint') {
				if($peerData['peerID'] == $endpointBObj['id'] and $peerData['peerFace'] == $endpointBObj['face'] and $peerData['peerDepth'] == $endpointBObj['depth']) {
					// add to pathArray
				} else {
					// Evaluate the peer's cabinet to prevent path feedback loop
					if($peerData['env_tree_id'] == $target['env_tree_id']) {
						$proceed = true;
					} else {
						if(in_array($peerData['env_tree_id'], $visitedCabs)) {
							$proceed = false;
						} else {
							$proceed = true;
							array_push($visitedCabs, $target['env_tree_id']);
						}
					}
					
					if($proceed) {
						$targetID = $target['id'];
						$targetFace = $target['face'];
						$targetDepth = $target['depth'];
						$targetPort = $target['port'];
						$targetObj = $qls->App->objectArray[$targetID];
						$targetTemplateID = $targetObj['template_id'];
						$targetTemplate = $qls->App->templateArray[$targetTemplateID];
						$targetFunction = $targetTemplate['templateFunction'];
						$targetName = $qls->App->generateObjectPortName($targetID, $targetFace, $targetDepth, $targetPort);
						//error_log('debug: here2');
						//$far = getObjectString($GLOBALS['templateTable'], $qls, $target['id'], $target['port'], $target['face'], $target['depth']);

						array_push($workingArray, array(
							'far' => $targetName,
							'farFunction' => $targetFunction
							//'far' =>  $far['obj'],
							//'farFunction' => $far['function']
						));
					
						$newTarget = array(
							'id' => $peerData['peerID'],
							'face' => $peerData['peerFace'],
							'depth' => $peerData['peerDepth'],
							'port' => $target['port'],
							'env_tree_id' => $peerData['env_tree_id']
						);
						
						findPaths($qls, $reachableArray, $mediaType, $newTarget, $endpointAObj, $endpointBObj, $workingArray, $visitedObjs, $visitedCabs, $peerParentArray);
						array_pop($workingArray);
					}
				}
			}
		}
	}

	//error_log('Debug: reachableArray = '.json_encode($reachableArray));
	//error_log('Debug: targetID = '.$target['id']);
	// Explore reachable objects
	if(isset($reachableArray[$target['id']])) {
		foreach($reachableArray[$target['id']] as $reachableCategory => $reachableGroup) {
			
			foreach($reachableGroup as $reachableObj) {
				
				$reachableObjID = $reachableObj['parent_id'] == 0 ? $reachableObj['id'] : $reachableObj['parent_id'];
				if(!isset($peerParentArray[$reachableObjID])) {
					$peerParentArray[$reachableObjID] = array();
				}
				// Reached target endpoint
				if($reachableObj['id'] == $endpointBObj['id']) {
					
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
					$nearPortType = $qls->App->portTypeValueArray[$nearPortTypeValue];
					
					$farObjName = $qls->App->generateObjectPortName($endpointBObj['id'], $endpointBObj['face'], $endpointBObj['depth'], $endpointBObj['port']);
					$farObjName = $qls->App->wrapObject($endpointBObj['id'], $farObjName);
					$farObj = $qls->App->objectArray[$target['id']];
					$farTemplateID = $nearObj['template_id'];
					$farCompatibility = $qls->App->compatibilityArray[$farTemplateID][$endpointBObj['face']][$endpointBObj['depth']];
					$farPortTypeValue = $farCompatibility['portType'];
					$farPortType = $qls->App->portTypeValueArray[$farPortTypeValue];

					array_push($workingArray, array(
						'near' => $nearObjName,
						'nearPortType' => $nearPortType,
						'far' =>  $farObjName,
						'farPortType' => $farPortType,
						'distance' => $reachableObj['dist'],
						'pathType' => $reachableCategory,
						'mediaType' => $mediaType
					));
					array_push($GLOBALS['pathArray'], $workingArray);
					array_pop($workingArray);
				// Reachable object cannot be the starting endpoint or itself
				} else if($reachableObj['id'] != $endpointAObj['id'] and $reachableObj['id'] != $target['id']) {
					
					// Reachable object cannot be one that we've visited already
					if(!in_array($reachableObj['id'], $visitedObjs)) {
						
						array_push($visitedObjs, $reachableObj['id']);
						// Reachable object should have a trunk peer to be considered
						if(isset($GLOBALS['peerArray']['-'.$reachableObj['id']])) {
							
							// Narrow down peer by reachable object face
							foreach($GLOBALS['peerArray']['-'.$reachableObj['id']] as $objFace => $objFaceArray) {
								
								// Narrow down peer by reachable object depth
								foreach($objFaceArray as $objDepth => $peerData) {
									
									$objFace = str_replace('-','',$objFace);
									$objDepth = str_replace('-','',$objDepth);
									
									$peerIsEndpointB = $peerData['peerID'] == $endpointBObj['id'] and $peerData['peerFace'] == $endpointBObj['face'] and $peerData['peerDepth'] == $endpointBObj['depth'];
									
									//$nearPortArray = getAvailablePortArray($target['id'], $target['face'], $target['depth'], $qls);
									$farPortArray = getAvailablePortArray($reachableObj['id'], $objFace, $objDepth, $qls);
									$peerPortArray = getAvailablePortArray($peerData['peerID'], $peerData['peerFace'], $peerData['peerDepth'], $qls);
									
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
									//$nearPort = isset($target['port']) ? $target['port'] : $nearPortArray[0];
									$nearPort = $target['port'];
									error_log('debug: here3');
									$near = getObjectString($GLOBALS['templateTable'], $qls, $target['id'], $nearPort, $target['face'], $target['depth']);
									$nearPortType = getPortType($qls, $GLOBALS['compatibilityTable'], $GLOBALS['portTable'], $target['id'], $target['face'], $target['depth']);
									
									// Far Object
									$farPort = $peerIsEndpointB ? $endpointBObj['port'] : $commonAvailablePort;
									error_log('debug: here4');
									$far = getObjectString($GLOBALS['templateTable'], $qls, $reachableObj['id'], $farPort, $objFace, $objDepth);
									$farPortType = getPortType($qls, $GLOBALS['compatibilityTable'], $GLOBALS['portTable'], $reachableObj['id'], $objFace, $objDepth);
									
									// Reachable object cannot be an endpoint... how are we supposed to patch layer1 through a layer2-4 device?
									if($far['function'] != 'Endpoint') {
										
										// Peer object cannot be one that we've visited already
										if(!in_array($peerData['peerID'], $visitedObjs)) {
											array_push($visitedObjs, $peerData['peerID']);
											array_push($workingArray, array(
												'near' => $near['obj'],
												'nearFunction' => $near['function'],
												'nearPortType' => $nearPortType,
												'far' => $far['obj'],
												'farFunction' => $far['function'],
												'farPortType' => $farPortType,
												'distance' => $reachableObj['dist'],
												'pathType' => $reachableCategory,
												'mediaType' => $mediaType
											));
											
											if($peerIsEndpointB) {
												$farPort = $endpointBObj['port'];
												error_log('debug: here5');
												$far = getObjectString($GLOBALS['templateTable'], $qls, $endpointBObj['id'], $farPort, $endpointBObj['face'], $endpointBObj['depth']);
												$farPortType = getPortType($qls, $GLOBALS['compatibilityTable'], $GLOBALS['portTable'], $endpointBObj['id'], $endpointBObj['face'], $endpointBObj['depth']);									
												array_push($workingArray, array(
													'far' => $far['obj'],
													'farFunction' => $far['function'],
													'farPortType' => $farPortType
												));
												array_push($GLOBALS['pathArray'], $workingArray);
												array_pop($workingArray);
											} else {
												if($peerData['peerIsEndpoint'] != true) {
													// Evaluate the peer's cabinet to prevent path feedback loop
													if($peerData['env_tree_id'] == $target['env_tree_id']) {
														$proceed = true;
													} else {
														if(in_array($peerData['env_tree_id'], $visitedCabs)) {
															$proceed = false;
														} else {
															$proceed = true;
															array_push($visitedCabs, $target['env_tree_id']);
															array_push($visitedCabs, $reachableObj['env_tree_id']);
														}
													}
													
													// Evaluate the peer's parent to prevent path feedback loop
													if($proceed) {
														if(in_array($peerData['parent_id'], $peerParentArray[$reachableObjID])) {
															$proceed = false;
														}
													}
													
													// 
													if($proceed) {
														$proceed = $farPortFound;
													}
													
													if($peerData['parent_id'] != 0 and !in_array($peerData['parent_id'], $peerParentArray[$reachableObjID]) and $farPortFound) {
														array_push($peerParentArray[$reachableObjID], $peerData['parent_id']);
													}
													
													if($proceed) {
														$newTarget = array(
															'id' => $peerData['peerID'],
															'face' => $peerData['peerFace'],
															'depth' => $peerData['peerDepth'],
															'port' => $farPort,
															'env_tree_id' => $peerData['env_tree_id']
														);
														findPaths($qls, $reachableArray, $mediaType, $newTarget, $endpointAObj, $endpointBObj, $workingArray, $visitedObjs, $visitedCabs, $peerParentArray);
													}
												}
											}
											array_pop($workingArray);
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

function getReachableObjects(&$qls, $templateTable, $objID, $objRU, $objSize, $cabinetID, $objectArray, $reachableCabinetArray, $type){
	$reachableObjects = array();
	if(isset($reachableCabinetArray[$cabinetID])) {
		foreach($reachableCabinetArray[$cabinetID] as $reachableCabinet) {
			foreach($objectArray as $reachableObj) {
				if($reachableObj['env_tree_id'] == $reachableCabinet['peerID'] and $reachableObj['id'] != $objID) {
					if($templateTable[$reachableObj['template_id']]['templateType'] == 'Insert') {
						$reachableObjRU = getRU($reachableObj['parent_id'], $qls);
						$reachableObjSize = getSize($reachableObj['parent_id'], $templateTable, $qls);
					} else if($templateTable[$reachableObj['template_id']]['templateType'] == 'Standard') {
						$reachableObjRU = $reachableObj['RU'];
						$reachableObjSize = $templateTable[$reachableObj['template_id']]['templateRUSize'];
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
					$object = array(
						'id' => $reachableObj['id'],
						'parent_id' => $reachableObj['parent_id'],
						'env_tree_id' => $reachableObj['env_tree_id'],
						'dist' => $distance
					);
					array_push($reachableObjects, $object);
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

function getSize($ID, $templateTable, &$qls){
	$query = $qls->SQL->select('*', 'app_object', array('id' => array('=', $ID)));
	if($qls->SQL->num_rows($query)) {
		$parentObj = $qls->SQL->fetch_assoc($query);
		$size = $templateTable[$parentObj['template_id']]['templateRUSize'];
	} else {
		$size = 0;
	}
	return $size;
}

// Debug templates
// file_put_contents('filename.output', json_encode($array));
// error_log('Debug (debugName): '.json_encode($array));
?>
