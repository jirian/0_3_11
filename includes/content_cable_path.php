<?php
// Requires $connectorCode39

$path = array();
$workingArray = array();

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

// Get cable.
$query = $qls->SQL->select('*', 'app_inventory', array('a_code39' => array('=', $connectorCode39), 'OR', 'b_code39' => array('=', $connectorCode39)));

if($qls->SQL->num_rows($query)>0){
	$rootCable = $qls->SQL->fetch_assoc($query);
	$nearCblAttrPrefix = $rootCable['a_code39'] == $connectorCode39 ? 'a' : 'b';
	$farCblAttrPrefix = $rootCable['a_code39'] == $connectorCode39 ? 'b' : 'a';
} else {
	return false;
}

// Build the first near object
$objID = $rootCable[$nearCblAttrPrefix.'_object_id'];
$objPort = $rootCable[$nearCblAttrPrefix.'_port_id'];
$objFace = $rootCable[$nearCblAttrPrefix.'_object_face'];
$objDepth = $rootCable[$nearCblAttrPrefix.'_object_depth'];

// Near object
$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
array_push($workingArray, $object);

$length = calculateCableLength($mediaTypeTable, $mediaCategoryTypeTable, $rootCable);

// Cable
$cblArray = array($rootCable[$nearCblAttrPrefix.'_code39'], $rootCable[$farCblAttrPrefix.'_code39'], $length);
array_push($workingArray, $cblArray);

// Build the first far object
$objID = $rootCable[$farCblAttrPrefix.'_object_id'];
$objPort = $rootCable[$farCblAttrPrefix.'_port_id'];
$objFace = $rootCable[$farCblAttrPrefix.'_object_face'];
$objDepth = $rootCable[$farCblAttrPrefix.'_object_depth'];

// Far object
$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
array_push($workingArray, $object);

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
		$peer = $qls->App->findPeer($objID, $objFace, $objDepth, $objPort);
		if($peer) {
			$objID = $peer['id'];
			$objFace = $peer['face'];
			$objDepth = $peer['depth'];
		} else {
			$objID = 0;
		}
		
		if($objID) {
			// Get peer object
			$object = $qls->App->getObject($objID, $objPort, $objFace, $objDepth);
			
			// Add object to working array
			array_push($workingArray, $object);
			
			// Get cable connected to peer object
			$cableID = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort]['localEndID'];
			$cable = $qls->App->inventoryByIDArray[$cableID];
			
			// Add cable to working array
			$cblArray = array($cable['remoteEndCode39'], $cable['remoteEndCode39']);
			
			if ($x == 1) {
				$cblArray = array_reverse($cblArray);
			}
			
			$length = $qls->App->calculateCableLength($cable['mediaType'], $cable['mediaType']);
			
			array_push($cblArray, $length);
			array_push($workingArray, $cblArray);
			
			// Get object data connected to far end of the cable
			$objID = $cbl['remoteEndID'];
			$objFace = $cbl['remoteEndFace'];
			$objDepth = $cbl['remoteEndDepth'];
			$objPort = $cbl['remoteEndPort'];
			
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
		}
	}
	
	// Now that we've discovered the far side of the scanned cable,
	// let's turn our attention to the near side.
	$objID = $rootCable[$nearCblAttrPrefix.'_object_id'];
	$objPort = $rootCable[$nearCblAttrPrefix.'_port_id'];
	$objFace = $rootCable[$nearCblAttrPrefix.'_object_face'];
	$objDepth = $rootCable[$nearCblAttrPrefix.'_object_depth'];
}

?>
