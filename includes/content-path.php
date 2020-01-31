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
		array_push($path, $workingArray);
		
		// Connection
		if(isset($qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
			
			$inventory = $qls->App->inventoryArray[$objID][$objFace][$objDepth][$objPort];
			$localConnectionID = $inventory['localEndID'];
			$connection = $qls->App->inventoryByIDArray[$connectorID];
			
			// Local Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => $connection['localEndCode39'],
					'connectorType' => $connection['localConnector']
				)
			);
			array_push($path, $workingArray);
			
			// Cable
			$workingArray = array(
				'type' => 'cable',
				'data' => array(
					'mediaType' => $connection['mediaType'],
					'length' => $connection['length']
				)
			);
			array_push($path, $workingArray);
			
			// Remote Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => $connection['remoteEndCode39'],
					'connectorType' => $connection['remoteConnector']
				)
			);
			array_push($path, $workingArray);
			
			if($connection['remote_object_id'] != 0) {
				
				$objID = $connection['remote_object_id'];
				$objFace = $connection['remote_object_face'];
				$objDepth = $connection['remote_object_depth'];
				$objPort = $connection['remote_object_port'];
				
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
				array_push($path, $workingArray);
				
				if($qls->App->peerArray[$objID][$objFace][$objDepth]) {
					
					// Remote Object Peer
					$peer = $qls->App->peerArray[$objID][$objFace][$objDepth];
					$objID = $peer['peerID'];
					$objFace = $peer['peerFace'];
					$objDepth = $peer['peerDepth'];
				}
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
			array_push($path, $workingArray);
			
			// Cable
			$workingArray = array(
				'type' => 'cable',
				'data' => array(
					'mediaType' => 0,
					'length' => 0
				)
			);
			array_push($path, $workingArray);
			
			// Remote Connection
			$workingArray = array(
				'type' => 'connector',
				'data' => array(
					'code39' => 0,
					'connectorType' => 0
				)
			);
			array_push($path, $workingArray);
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
