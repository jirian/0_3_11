<?php

$path = array();
$workingArray = array();

if($connectorCode39) {
	$connectorID = base_convert($connectorCode39, 36, 10) + 0;
	error_log('Debug: '.$connectorID);
	$rootCable = $qls->App->inventoryByID[$connectorID];
	
	$objID = $rootCable['local_object_id'];
	$objPort = $rootCable['local_object_port'];
	$objFace = $rootCable['local_object_face'];
	$objDepth = $rootCable['local_object_depth'];
} else {
	
}

$rootObjID = $objID;
$rootObjFace = $objFace;
$rootObjDepth = $objDepth;
$rootPortID = $objPort;

$mediaTypeTable = array();
$query = $qls->SQL->select('*', 'shared_mediaType');
while($row = $qls->SQL->fetch_assoc($query)) {
	$mediaTypeTable[$row['value']] = $row;
}

$mediaCategoryTypeTable = array();
$query = $qls->SQL->select('*', 'shared_mediaCategoryType');
while($row = $qls->SQL->fetch_assoc($query)) {
	$mediaCategoryTypeTable[$row['value']] = $row;
}

// Near object
//error_log('getObject(24): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
$object['selected'] = true;
array_push($workingArray, $object);

// Cable
error_log('getCable(31): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
$cbl = $qls->App->getCable($objID, $objPort, $objFace, $objDepth);
if($cbl) {
	$workingNearCblAttrPrefix = $cbl['nearEnd'];
	$workingFarCblAttrPrefix = $cbl['farEnd'];

	$length = calculateCableLength($mediaTypeTable, $mediaCategoryTypeTable, $cbl);

	// Add cable to working array
	$cblArray = array($cbl[$workingNearCblAttrPrefix.'_code39'], $cbl[$workingFarCblAttrPrefix.'_code39'], $length);
	
	array_push($workingArray, $cblArray);

	// Build the first far object
	$objID = $cbl[$workingFarCblAttrPrefix.'_object_id'];
	$objPort = $cbl[$workingFarCblAttrPrefix.'_port_id'];
	$objFace = $cbl[$workingFarCblAttrPrefix.'_object_face'];
	$objDepth = $cbl[$workingFarCblAttrPrefix.'_object_depth'];

	//error_log('getObject(50): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
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
	//error_log($objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
	while($objID){
		
		// Clear the working array
		$workingArray = array();
		
		// Use object ID to find trunk peer
		error_log('getPeer(73): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
		if($peer = $qls->App->findPeer($objID, $objFace, $objDepth, $objPort)) {
			$objID = $peer['id'];
			$objPort = $peer['floorplanPeer'] ? $peer['port'] : $objPort;
			$objFace = $peer['face'];
			$objDepth = $peer['depth'];

			// Get peer object
			//error_log('getObject(81): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
			$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
			
			// Add object to working array
			array_push($workingArray, $object);
			
			// Get cable connected to peer object
			error_log('getCable(89): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
			$cbl = $qls->App->getCable($objID, $objPort, $objFace, $objDepth);
			$workingNearCblAttrPrefix = $cbl['nearEnd'];
			$workingFarCblAttrPrefix = $cbl['farEnd'];
			
			// Add cable to working array
			$cblArray = array($cbl[$workingNearCblAttrPrefix.'_code39'], $cbl[$workingFarCblAttrPrefix.'_code39']);
			
			if ($x == 1) {
				$cblArray = array_reverse($cblArray);
			}

			$length = calculateCableLength($mediaTypeTable, $mediaCategoryTypeTable, $cbl);

			array_push($cblArray, $length);
			array_push($workingArray, $cblArray);
			
			// Get object data connected to far end of the cable
			$objID = $cbl[$workingFarCblAttrPrefix.'_object_id'];
			$objPort = $cbl[$workingFarCblAttrPrefix.'_port_id'];
			$objFace = $cbl[$workingFarCblAttrPrefix.'_object_face'];
			$objDepth = $cbl[$workingFarCblAttrPrefix.'_object_depth'];
			
			// Get far end object
			//error_log('getObject(113): '.$objID.'-'.$objFace.'-'.$objDepth.'-'.$objPort);
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
