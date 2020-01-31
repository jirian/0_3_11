<?php

$path = array();
$workingArray = array();

if($connectorCode39) {
	$connectorID = base_convert($connectorCode39, 36, 10) + 0;
	$rootCable = $qls->App->inventoryByIDArray[$connectorID];
	
	$objID = $rootCable['local_object_id'];
	$objPort = $rootCable['local_object_port'];
	$objFace = $rootCable['local_object_face'];
	$objDepth = $rootCable['local_object_depth'];
}

$rootObjID = $objID;
$rootObjFace = $objFace;
$rootObjDepth = $objDepth;
$rootPortID = $objPort;

// Near object
$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
$object['selected'] = true;
array_push($workingArray, $object);

// Cable
$connectionEntry = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
$connectionID = $connectionEntry['rowID'];
$localAttrPrefix = $connectionEntry['localAttrPrefix'];
$remoteAttrPrefix = $connectionEntry['remoteAttrPrefix'];
$connection = $qls->App->inventoryAllArray[$connectionID];

if($connection) {
	$length = $qls->App->calculateCableLength($connection['mediaType'], $connection['length'], true);

	// Add cable to working array
	$cblArray = array($connection[$localAttrPrefix.'_code39'], $connection[$remoteAttrPrefix.'_code39'], $length);
	array_push($workingArray, $cblArray);

	// Build the first far object
	$objID = $connection[$remoteAttrPrefix.'_object_id'];
	$objPort = $connection[$remoteAttrPrefix.'_port_id'];
	$objFace = $connection[$remoteAttrPrefix.'_object_face'];
	$objDepth = $connection[$remoteAttrPrefix.'_object_depth'];

	$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
	array_push($workingArray, $object);
} else {
	// Append empty cable and object
	array_push($workingArray, array(0,0,0));
	array_push($workingArray, array('id' => 0));
	$objID = 0;
}

// Append to the path
array_push($path, $workingArray);

// Discover path elements
// First look outward from the far end of the cable,
// then look outward from the near end of the cable.
for($x=0; $x<2; $x++){
	
	while($objID){
		
		// Clear the working array
		$workingArray = array();
		
		// Use object ID to find trunk peer
		if($peer = $qls->App->findPeer($objID, $objFace, $objDepth, $objPort)) {
			
			$objID = $peer['id'];
			$objPort = $peer['floorplanPeer'] ? $peer['port'] : $objPort;
			$objFace = $peer['face'];
			$objDepth = $peer['depth'];

			// Get peer object
			$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
			
			// Add object to working array
			array_push($workingArray, $object);
			
			// Get cable connected to peer object
			$connectionEntry = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
			$connectionID = $connectionEntry['rowID'];
			$localAttrPrefix = $connectionEntry['localAttrPrefix'];
			$remoteAttrPrefix = $connectionEntry['remoteAttrPrefix'];
			$connection = $qls->App->inventoryAllArray[$connectionID];
			
			$length = $qls->App->calculateCableLength($connection['mediaType'], $connection['length'], true);
			
			// Add cable to working array
			$cblArray = array($connection[$localAttrPrefix.'_code39'], $connection[$remoteAttrPrefix.'_code39']);
			
			if ($x == 1) {
				$cblArray = array_reverse($cblArray);
			}

			array_push($cblArray, $length);
			array_push($workingArray, $cblArray);
			
			// Get object data connected to far end of the cable
			$objID = $connection[$remoteAttrPrefix.'_object_id'];
			$objPort = $connection[$remoteAttrPrefix.'_port_id'];
			$objFace = $connection[$remoteAttrPrefix.'_object_face'];
			$objDepth = $connection[$remoteAttrPrefix.'_object_depth'];
			
			// Get far end object
			$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
			
			// Add object to working array
			array_push($workingArray, $object);
			
			// If we are in the 2nd iteration of the for loop,
			// that means we are discovering the path on the near side of the scanned cable.
			// Mirror the working array and append it to the front of the path.
			// Else, append it to the end of the path.
			if ($x == 1) {
				$workingArray = array_reverse($workingArray);
				array_unshift($path, $workingArray);
			} else {
				array_push($path, $workingArray);
			}
		} else {
			$objID = 0;
		}
	}
	
	// Now that we've discovered the far side of the scanned cable,
	// let's turn our attention to the near side.
	$objID = $rootObjID;
	$objPort = $rootPortID;
	$objFace = $rootObjFace;
	$objDepth = $rootObjDepth;
}

?>
