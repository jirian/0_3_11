<?php

$path = array();

if($connectorCode39) {
	$connectorID = base_convert($connectorCode39, 36, 10);
	$rootCable = $qls->App->inventoryByIDArray[$connectorID];
	
	$objID = $rootCable['local_object_id'];
	$objFace = $rootCable['local_object_face'];
	$objDepth = $rootCable['local_object_depth'];
	$objPort = $rootCable['local_object_port'];
}

if(isset($qls->App->peerArray[$objID][$objFace][$objDepth])) {
	$peer = $qls->App->peerArray[$objID][$objFace][$objDepth];
	$reverseObjID = $peer['peerID'];
	$reverseObjFace = $peer['peerFace'];
	$reverseObjDepth = $peer['peerDepth'];
	$reversePortID = $objPort;
	
	// trunk
	$workingArray = array(
		'type' => 'trunk',
		'data' => array()
	);
	array_push($path, $workingArray);
	
} else {
	$reverseObjID = 0;
	$reverseObjFace = 0;
	$reverseObjDepth = 0;
	$reversePortID = 0;
}

// Discover path elements
// First look outward from the far end of the cable,
// then look outward from the near end of the cable.
for($x=0; $x<2; $x++){
	
	while($objID){
		
		// Object
		$workingArray = array(
			'type' => 'object',
			'data' => array(
				'id' => $objID,
				'face' => $objFace,
				'depth' => $objDepth,
				'port' => $objPort
			)
		);
		if($x == 0) {
			array_push($path, $workingArray);
		} else {
			array_unshift($path, $workingArray);
		}
		
		// Connection
		if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
			
			$inventory = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
			$inventoryID = $inventory['rowID'];
			$localAttrPrefix = $inventory['localAttrPrefix'];
			$remoteAttrPrefix = $inventory['remoteAttrPrefix'];
			$connection = $qls->App->inventoryAllArray[$inventoryID];
			$mediaTypeID = $connection['mediaType'];
			$length = $connection['length'];
			$includeUnit = true;
			$length = $qls->App->calculateCableLength($mediaTypeID, $length, $includeUnit);
			
			// Local Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => $connection[$localAttrPrefix.'_code39'],
					'connectorType' => $connection[$localAttrPrefix.'_connector']
				)
			);
			if($x == 0) {
				array_push($path, $workingArray);
			} else {
				array_unshift($path, $workingArray);
			}
			
			// Cable
			$workingArray = array(
				'type' => 'cable',
				'data' => array(
					'mediaTypeID' => $mediaTypeID,
					'length' => $length
				)
			);
			if($x == 0) {
				array_push($path, $workingArray);
			} else {
				array_unshift($path, $workingArray);
			}
			
			// Remote Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => $connection[$remoteAttrPrefix.'_code39'],
					'connectorType' => $connection[$remoteAttrPrefix.'_connector']
				)
			);
			if($x == 0) {
				array_push($path, $workingArray);
			} else {
				array_unshift($path, $workingArray);
			}
			
			if($connection[$remoteAttrPrefix.'_object_id'] != 0) {
				
				
				$objID = $connection[$remoteAttrPrefix.'_object_id'];
				$objFace = $connection[$remoteAttrPrefix.'_object_face'];
				$objDepth = $connection[$remoteAttrPrefix.'_object_depth'];
				$objPort = $connection[$remoteAttrPrefix.'_port_id'];
				
				// Remote Object
				$workingArray = array(
					'type' => 'object',
					'data' => array(
						'id' => $objID,
						'face' => $objFace,
						'depth' => $objDepth,
						'port' => $objPort
					)
				);
				if($x == 0) {
					array_push($path, $workingArray);
				} else {
					array_unshift($path, $workingArray);
				}
				
				if(isset($qls->App->peerArray[$objID][$objFace][$objDepth])) {
					
					// Remote Object Peer
					$peer = $qls->App->peerArray[$objID][$objFace][$objDepth];
					$objID = $peer['peerID'];
					$objFace = $peer['peerFace'];
					$objDepth = $peer['peerDepth'];
					
					// Trunk
					$workingArray = array(
						'type' => 'trunk',
						'data' => array()
					);
					if($x == 0) {
						array_push($path, $workingArray);
					} else {
						array_unshift($path, $workingArray);
					}
					
				} else {
					
					// No trunk peer found
					$objID = 0;
				}
			} else {
				
				// No connected object
				$objID = 0;
			}
			
			
		} else if(isset($qls->App-> populatedPortArray[$objID][$objFace][$objDepth][$objPort])) {
			
			// Local Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => 0,
					'connectorType' => 0
				)
			);
			if($x == 0) {
				array_push($path, $workingArray);
			} else {
				array_unshift($path, $workingArray);
			}
			
			// No connected object
			$objID = 0;
			
		} else {
			
			// No connected object
			$objID = 0;
		}
	}
	
	// Now that we've discovered the far side of the scanned cable,
	// let's turn our attention to the near side.
	$objID = $reverseObjID;
	$objFace = $reverseObjFace;
	$objDepth = $reverseObjDepth;
	$objPort = $reversePortID;
}

?>
