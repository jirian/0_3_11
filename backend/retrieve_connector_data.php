<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once('../includes/Validate.class.php');
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate);
	
	if (!count($validate->returnData['error'])){
		require_once '../includes/path_functions.php';
		
		$connectorCode39 = strtoupper($data['connectorCode39']);
		$connectorID = base_convert($connectorCode39, 36, 10);
		
		$mediaTypeArray = $qls->App->mediaTypeArray;
		$cableMediaTypeValueTable = $qls->App->mediaTypeValueArray;
		$cableMediaCategoryTypeTable = $qls->App->mediaCategoryTypeArray;
		
		// Does cable exist?
		if($cable = $qls->App->inventoryByIDArray[$connectorID]) {
			$validate->returnData['success'] = array();
			
			$validate->returnData['success']['connectorTypeInfo'] = $qls->App->connectorTypeArray;
			$validate->returnData['success']['cableMediaTypeInfo'] = getCableMediaTypeInfo($mediaTypeArray);
			
			$validate->returnData['success']['cable'] = $cable;
			$localAttrPrefix = $cable['localAttrPrefix'];
			$remoteAttrPrefix = $cable['remoteAttrPrefix'];
			
			// Retrieve local connector path
			$localObjID = $cable['local_object_id'];
			$localObjFace = $cable['local_object_face'];
			$localObjDepth = $cable['local_object_depth'];
			$localObjPort = $cable['local_object_port'];
			$localConnectorFlatPath = $qls->App->generateObjectPortName($localObjID, $localObjFace, $localObjDepth, $localObjPort);
			$validate->returnData['success']['localConnectorFlatPath'] = $localConnectorFlatPath;
			
			// Retrieve unit of length
			$mediaTypeID = $cable['mediaType'];
			if($mediaTypeID != 0) {
				$unitOfLength = $qls->App->getCableUnitOfLength($mediaTypeID);
			} else {
				$unitOfLength = 'm./ft.';
			}
			
			$length = calculateCableLength($cableMediaTypeValueTable, $cableMediaCategoryTypeTable, $cable, false);
			
			$validate->returnData['success']['cable']['length'] = $length;
			$validate->returnData['success']['cable']['unitOfLength'] = $unitOfLength;
			
			// Is the remote end initialized?
			if($cable['remoteEndID'] > 0) {
				//Verify remote connector
				if(isset($data['verifyCode39'])) {
					$verifyCode39 = strtoupper($data['verifyCode39']);
					if ($verifyCode39 == $cable['remoteEndCode39']) {
						$validate->returnData['success']['verified'] = 'yes';
					} else {
						$validate->returnData['success']['verified'] = 'no';
					}
				} else {
					$validate->returnData['success']['verified'] = 'unknown';
				}
				
				// Retrieve remote connector path
				$remoteObjID = $cable['remote_object_id'];
				$remoteObjFace = $cable['remote_object_face'];
				$remoteObjDepth = $cable['remote_object_depth'];
				$remoteObjPort = $cable['remote_object_port'];
				$remoteConnectorFlatPath = $qls->App->generateObjectPortName($remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort);
				$validate->returnData['success']['remoteConnectorFlatPath'] = $remoteConnectorFlatPath;
				
			// Has the remote end been scanned for initialization?
			} else if(isset($data['initializeCode39'])){
				$initializeCode39 = strtoupper($data['initializeCode39']);
				$initializeID = base_convert($initializeCode39, 36, 10);
				
				if($initializeCode39 != $connectorCode39) {
					// Is initializeID already initialized?
					if($initializedExisting = $qls->App->inventoryByIDArray[$initializeID]) {
						$initializedExisting = $qls->SQL->fetch_assoc($query);
						if($initializedExisting['localEndID'] == 0 or $initializedExisting['remoteEndID'] == 0) {
							$qls->SQL->update(
								'app_inventory',
								array(
									$remoteAttrPrefix.'_id' => $initializedExisting['localEndID'],
									$remoteAttrPrefix.'_code39' => $initializedExisting['localEndCode39'],
									$remoteAttrPrefix.'_connector' => $initializedExisting['localConnector'],
									$remoteAttrPrefix.'_object_id' => $initializedExisting['local_object_id'],
									$remoteAttrPrefix.'_port_id' => $initializedExisting['local_object_port'],
									$remoteAttrPrefix.'_object_face' => $initializedExisting['local_object_face'],
									$remoteAttrPrefix.'_object_depth' => $initializedExisting['local_object_depth']
								),
								array(
									$localAttrPrefix.'_id' => array('=', $connectorID)
								)
							);
							
							$qls->SQL->delete('app_inventory', array('id' => array('=', $initializedExisting['rowID'])));
							
							// Update cable object
							$qls->App->inventoryByIDArray[$connectorID]['remoteEndID'] = $initializedExisting['localEndID'];
							$qls->App->inventoryByIDArray[$connectorID]['remoteEndCode39'] = $initializedExisting['localEndCode39'];
							$qls->App->inventoryByIDArray[$connectorID]['remoteConnector'] = $initializedExisting['localConnector'];
							$qls->App->inventoryByIDArray[$connectorID]['remote_object_id'] = $initializedExisting['local_object_id'];
							$qls->App->inventoryByIDArray[$connectorID]['remote_object_port'] = $initializedExisting['local_object_port'];
							$qls->App->inventoryByIDArray[$connectorID]['remote_object_face'] = $initializedExisting['local_object_face'];
							$qls->App->inventoryByIDArray[$connectorID]['remote_object_depth'] = $initializedExisting['local_object_depth'];
							$cable = $qls->App->inventoryByIDArray[$connectorID];
							$validate->returnData['success']['cable'] = $cable;
							
							// Retrieve initialized remote connector path
							$remoteObjID = $cable['remote_object_id'];
							$remoteObjFace = $cable['remote_object_face'];
							$remoteObjDepth = $cable['remote_object_depth'];
							$remoteObjPort = $cable['remote_object_port'];
							$remoteConnectorFlatPath = $qls->App->generateObjectPortName($remoteObjID, $remoteObjFace, $remoteObjDepth, $remoteObjPort);
							$validate->returnData['success']['remoteConnectorFlatPath'] = $remoteConnectorFlatPath;
						} else {
							$errorMsg = 'Invalid connector ID: '.$initializeID;
							array_push($qls->App->returnData['error'], $errorMsg);
						}
					} else {
						$qls->SQL->update(
							'app_inventory',
							array(
								$remoteAttrPrefix.'_id' => $initializeID,
								$remoteAttrPrefix.'_code39' => $initializeCode39,
							),
							array(
								$localAttrPrefix.'_id' => array('=', $connectorID)
							)
						);
						
						// Update cable object
						$qls->App->inventoryByIDArray[$connectorID]['remoteEndID'] = $initializeID;
						$qls->App->inventoryByIDArray[$connectorID]['remoteEndCode39'] = $initializeCode39;
						$cable = $qls->App->inventoryByIDArray[$connectorID];
						$validate->returnData['success']['cable'] = $cable;
						
						// Reset length stuff... this is a hack
						$validate->returnData['success']['cable']['length'] = $length;
						$validate->returnData['success']['cable']['unitOfLength'] = $unitOfLength;
						$validate->returnData['success']['remoteConnectorFlatPath'] = 'None';
					}
				} else {
					$errMsg = 'Cannot initialize remote end with same ID as local end.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
		// Cable doesn't exist
		} else {
			$qls->SQL->insert(
				'app_inventory',
				array(
					'a_id',
					'a_code39'
				),
				array(
					$connectorID,
					$connectorCode39
				)
			);
			$rowID = $qls->SQL->insert_id();
			
			$validate->returnData['success']['connectorTypeInfo'] = $qls->App->connectorTypeArray;
			$validate->returnData['success']['cableMediaTypeInfo'] = getCableMediaTypeInfo($mediaTypeArray);
			
			$qls->App->inventoryByIDArray[$connectorID] = array(
				'rowID' => $rowID,
				'local_object_id' => 0,
				'local_object_face' => 0,
				'local_object_depth' => 0,
				'local_object_port' => 0,
				'remote_object_id' => 0,
				'remote_object_face' => 0,
				'remote_object_depth' => 0,
				'remote_object_port' => 0,
				'localEndID' => $connectorID,
				'localEndCode39' => $connectorCode39,
				'localConnector' => 0,
				'localAttrPrefix' => 'a',
				'remoteEndID' => 0,
				'remoteEndCode39' => 0,
				'remoteConnector' => 0,
				'remoteAttrPrefix' => 'b',
				'mediaType' => 0,
				'length' => 1,
				'editable' => 1
			);
			
			$cable = $validate->returnData['success']['cable'] = $qls->App->inventoryByIDArray[$connectorID];
			$mediaTypeID = $validate->returnData['success']['cable']['mediaType'];
			if($mediaTypeID != 0) {
				$unitOfLength = $qls->App->getCableUnitOfLength($mediaTypeID);
			} else {
				$unitOfLength = 'm./ft.';
			}
			
			$length = calculateCableLength($cableMediaTypeValueTable, $cableMediaCategoryTypeTable, $cable, false);
			$validate->returnData['success']['cable']['length'] = $length;
			$validate->returnData['success']['cable']['unitOfLength'] = $unitOfLength;
			$validate->returnData['success']['localConnectorFlatPath'] = 'None';
		}
	}
	echo json_encode($validate->returnData);
	return;
}

function validate($data, &$validate){
	//Validate scanned Code39
	$validate->validateCode39($data['connectorCode39'], 'scanned Code39');
	
	//Validate verify Code39
	if (isset($data['verifyCode39'])){
		$validate->validateCode39($data['verifyCode39'], 'verifying Code39');
	}
	
	//Validate initialize Code39
	if (isset($data['initializeCode39'])){
		$validate->validateCode39($data['initializeCode39'], 'initializing Code39');
	}

	return;
}

function getCableMediaTypeInfo($cableMediaTypeTable){
	$cableMediaTypeInfo = array();
	foreach($cableMediaTypeTable as $row) {
		$cableMediaTypeInfo[$row['value']] = $row['name'];
	}
	return $cableMediaTypeInfo;
}
?>
