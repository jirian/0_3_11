<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('operator.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	$validate = new Validate($qls);
	
	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		$cableProperty = $data['property'];
		switch($cableProperty){
			case 'connectorType':
				$connectorTypeID = $data['value'];
				$cableEndID = $data['id'];
				$query = $qls->SQL->select('*', 'app_inventory', array('a_id' => array('=', $cableEndID), 'OR', 'b_id' => array('=', $cableEndID)));
				$cable = $qls->SQL->fetch_assoc($query);
				$cableAttrPrefix = $cable['a_id'] == $cableEndID ? 'a' : 'b';
				
				$qls->SQL->update('app_inventory', array($cableAttrPrefix.'_connector' => $connectorTypeID), array($cableAttrPrefix.'_id' => array('=', $cableEndID)));
				break;
				
			case 'cableLength':
				$cableLength = $data['value'];
				$cableID = $data['id'];
				
				$query = $qls->SQL->select('*', 'app_inventory', array('id' => array('=', $cableID)));
				$cable = $qls->SQL->fetch_assoc($query);
				$mediaTypeID = $cable['mediaType'];
				
				$query = $qls->SQL->select('*', 'shared_mediaType', array('value' => array('=', $mediaTypeID)));
				$mediaType = $cable = $qls->SQL->fetch_assoc($query);
				$mediaCategoryTypeID = $mediaType['category_type_id'];
				
				if($mediaCategoryTypeID == 1) {
					$cableLength = $qls->App->convertFeetToMillimeters($cableLength);
				} else {
					$cableLength = $qls->App->convertMetersToMillimeters($cableLength);
				}
				
				$qls->SQL->update('app_inventory', array('length' => $cableLength), array('id' => array('=', $cableID)));
				break;
				
			case 'cableMediaType':
				$mediaTypeID = $data['value'];
				$cableID = $data['id'];
				
				$qls->SQL->update('app_inventory', array('mediaType' => $mediaTypeID), array('id' => array('=', $cableID)));
				$query = $qls->SQL->select('*', 'shared_mediaType', array('value' => array('=', $mediaTypeID)));
				$mediaType = $qls->SQL->fetch_assoc($query);
				$query = $qls->SQL->select('*', 'shared_mediaCategoryType', array('value' => array('=', $mediaType['category_id'])));
				$mediaCategoryType = $qls->SQL->fetch_assoc($query);;
				$validate->returnData['success'] = $mediaCategoryType['unit_of_length'];
				break;
				
			case 'cableEditable':
				$cableID = $data['id'];
				
				$qls->SQL->update('app_inventory', array('editable' => 0), array('id' => array('=', $cableID)));
				break;
				
			case 'connectionScan':
				require_once '../includes/path_functions.php';
				$validate->returnData['success'] = array();
				$value = $data['value'];
				if($value == 'clear') {
					$elementID = $elementFace = $elementDepth = $elementPort = 0;
				} else {
					$valueArray = explode('-', $value);
					$elementID = (int)$valueArray[1];
					$elementFace = (int)$valueArray[2];
					$elementDepth = (int)$valueArray[3];
					$elementPort = (int)$valueArray[4];
				}
				$connectorID = $data['connectorID'];
				//$query = $qls->SQL->select('*', 'app_inventory', array('a_id' => array('=', $connectorID), 'OR', 'b_id' => array('=', $connectorID)));
				//$cable = $qls->SQL->fetch_assoc($query);
				$cable = $qls->App->inventoryByIDArray[$connectorID];
				//$connectorAttributePrefix = $cable['a_id'] == $connectorID ? 'a' : 'b';
				//$inverseConnectorAttributePrefix = $connectorAttributePrefix == 'a' ? 'b' : 'a';
				//$peerID = $cable[$inverseConnectorAttributePrefix.'_object_id'];
				//$peerFace = $cable[$inverseConnectorAttributePrefix.'_object_face'];
				//$peerDepth = $cable[$inverseConnectorAttributePrefix.'_object_depth'];
				//$peerPort = $cable[$inverseConnectorAttributePrefix.'_port_id'];
				$peerID = $cable['remote_object_id'];
				$peerFace = $cable['remote_object_face'];
				$peerDepth = $cable['remote_object_depth'];
				$peerPort = $cable['remote_object_port'];
				$localAttrPrefix = $cable['localAttrPrefix'];
				
				if(loopDetected($qls, $peerID, $peerFace, $peerDepth, $peerPort, $elementID, $elementFace, $elementDepth, $elementPort)) {
					$errMsg = 'Loop detected.';
					array_push($validate->returnData['error'], $errMsg);
				} else {
					
					// Update connection in database
					$qls->SQL->update(
						'app_inventory',
						array(
							$localAttrPrefix.'_object_id' => $elementID,
							$localAttrPrefix.'_port_id' => $elementPort,
							$localAttrPrefix.'_object_face' => $elementFace,
							$localAttrPrefix.'_object_depth' => $elementDepth
						),
						array(
							'id' => array('=', $cable['rowID'])
						)
					);
					
					$qls->App->inventoryByIDArray[$connectorID]['local_object_id'] = $elementID;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_face'] = $elementFace;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_depth'] = $elementDepth;
					$qls->App->inventoryByIDArray[$connectorID]['local_object_port'] = $elementPort;
					$cable = $qls->App->inventoryByIDArray[$connectorID];
					
					// Remove any populated port entries that may exist
					$qls->SQL->delete(
						'app_populated_port',
						array(
							'object_id' => array('=', $elementID),
							'AND',
							'object_face' => array('=', $elementFace),
							'AND',
							'object_depth' => array('=', $elementDepth),
							'AND',
							'port_id' => array('=', $elementPort)
						)
					);
					
					// Retrieve connector path
					$connectorFlatPath = $qls->App->buildConnectorFlatPath($cable, 'local');
					$validate->returnData['success']['connectorFlatPath'] = $connectorFlatPath;
				}
				break;
				
			case 'connectionExplore':
				require_once '../includes/path_functions.php';
				$validate->returnData['success'] = array();
				$value = $data['value'];
				$clear = $value == 'clear' ? true : false;
				$peerPortID = '';
				
				if($clear) {
					$elementID = $elementFace = $elementDepth = $elementPort = 0;
				} else {
					$valueArray = explode('-', $value);
					$elementID = $valueArray[1];
					$elementFace = $valueArray[2];
					$elementDepth = $valueArray[3];
					$elementPort = $valueArray[4];
				}
				
				$objID = $data['objID'];
				$objFace = $data['objFace'];
				$objDepth = $data['objDepth'];
				$objPort = $data['objPort'];
				
				if(loopDetected($qls, $objID, $objFace, $objDepth, $objPort, $elementID, $elementFace, $elementDepth, $elementPort)) {
					$errMsg = 'Loop detected.';
					array_push($validate->returnData['error'], $errMsg);
				} else {
					
					$query = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$objID.' AND a_object_face = '.$objFace.' AND a_object_depth = '.$objDepth.' AND a_port_id = '.$objPort.') OR (b_object_id = '.$objID.' AND b_object_face = '.$objFace.' AND b_object_depth = '.$objDepth.' AND b_port_id = '.$objPort.')');
					$objEntry = $qls->SQL->num_rows($query) ? $qls->SQL->fetch_assoc($query) : false;
					
					if($clear) {
						$elementEntry = false;
						if($objEntry) {
							$elementAttr = ($objEntry['a_object_id'] == $objID and $objEntry['a_object_face'] == $objFace and $objEntry['a_object_depth'] == $objDepth and $objEntry['a_port_id'] == $objPort) ? 'b' : 'a';
							$peerPortID = 'port-'.$objEntry[$elementAttr.'_object_id'].'-'.$objEntry[$elementAttr.'_object_face'].'-'.$objEntry[$elementAttr.'_object_depth'].'-'.$objEntry[$elementAttr.'_port_id'];
						} else {
							$peerPortID = 'port-0-0-0-0';
						}
					} else {
						$query = $qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$elementID.' AND a_object_face = '.$elementFace.' AND a_object_depth = '.$elementDepth.' AND a_port_id = '.$elementPort.') OR (b_object_id = '.$elementID.' AND b_object_face = '.$elementFace.' AND b_object_depth = '.$elementDepth.' AND b_port_id = '.$elementPort.')');
						$elementEntry = $qls->SQL->num_rows($query) ? $qls->SQL->fetch_assoc($query) : false;
						$peerPortID = 'port-'.$elementID.'-'.$elementFace.'-'.$elementDepth.'-'.$elementPort;
					}
					
					// Find which ports are already connected
					if($objEntry and $elementEntry) {
						$objAttr = $objEntry['a_object_id'] == $objID and $objEntry['a_object_face'] == $objFace and $objEntry['a_object_depth'] == $objDepth and $objEntry['a_port_id'] == $objPort ? 'a' : 'b';
						$elementAttr = $elementEntry['a_object_id'] == $elementID ? 'a' : 'b';
						
						// Are the ports connected to each other?
						if($objEntry['id'] == $elementEntry['id']) {
							$entryID = $objEntry['id'];
							if($objEntry['a_id'] or $objEntry['b_id']) {
								clearTableInventory($qls, 'a', $entryID);
								clearTableInventory($qls, 'b', $entryID);
							} else {
								$qls->SQL->delete('app_inventory', array('id' => array('=', $entryID)));
							}
						} else {
							if($objEntry['a_id'] or $objEntry['b_id']) {
								clearTableInventory($qls, $objAttr, $objEntry['id']);
							} else {
								$qls->SQL->delete('app_inventory', array('id' => array('=', $objEntry['id'])));
							}
							
							if($elementEntry['a_id'] or $elementEntry['b_id']) {
								clearTableInventory($qls, $elementAttr, $elementEntry['id']);
							} else {
								$qls->SQL->delete('app_inventory', array('id' => array('=', $elementEntry['id'])));
							}
						}
					} else if($objEntry) {
						$objAttr = $objEntry['a_object_id'] == $objID and $objEntry['a_object_face'] == $objFace and $objEntry['a_object_depth'] == $objDepth and $objEntry['a_port_id'] == $objPort ? 'a' : 'b';
						
						if($objEntry['a_id'] or $objEntry['b_id']) {
							clearTableInventory($qls, $objAttr, $objEntry['id']);
						} else {
							$qls->SQL->delete('app_inventory', array('id' => array('=', $objEntry['id'])));
						}
						
					} else if($elementEntry) {
						$elementAttr = $elementEntry['a_object_id'] == $elementID and $elementEntry['a_object_face'] == $elementFace and $elementEntry['a_object_depth'] == $elementDepth and $elementEntry['a_port_id'] == $elementPort ? 'a' : 'b';
						
						if($elementEntry['a_id'] or $elementEntry['b_id']) {
							clearTableInventory($qls, $elementAttr, $elementEntry['id']);
						} else {
							$qls->SQL->delete('app_inventory', array('id' => array('=', $elementEntry['id'])));
						}
					}
					
					clearTablePopulated($qls, $objID, $objFace, $objDepth, $objPort);
					clearTablePopulated($qls, $elementID, $elementFace, $elementDepth, $elementPort);
					
					if(!$clear) {
						insertTableInventory($qls, $objID, $objFace, $objDepth, $objPort, $elementID, $elementFace, $elementDepth, $elementPort);
					}
					
					include_once './includes/content_port_path.php';
				
					$validate->returnData['success']['pathFull'] = buildPathFull($path);
					$validate->returnData['success']['peerPortID'] = $peerPortID;
				}

				break;
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$propertiesArray = array('connectorType', 'cableLength', 'cableMediaType', 'cableEditable', 'connectionScan', 'connectionExplore');
	
	//Validate property
	if($validate->validateInArray($data['property'], $propertiesArray, 'property type')) {
	
		if ($data['property'] == 'cableLength') {
			//Validate cable ID
			$validate->validateCableID($data['id'], $qls);
			
			//Validate cable length
			$validate->validateCableLength($data['value']);
			
		} else if ($data['property'] == 'cableMediaType') {
			//Validate cable ID
			$validate->validateCableID($data['id'], $qls);
		
			//Validate cable media type
			$validate->validateCableMediaType($data['value'], $qls);
			
		} else if ($data['property'] == 'connectorType') {
			//Validate connector ID
			$validate->validateConnectorID($data['id'], $qls);
			
			//Validate connector type
			$validate->validateCableConnectorType($data['value'], $qls);
			
		} else if ($data['property'] == 'connectionScan') {
			
		} else if ($data['property'] == 'connectionExplore') {
			
		}
		
	}
	return;
}

function clearTableInventory(&$qls, $attr, $id){
	$qls->SQL->update(
		'app_inventory',
		array(
			$attr.'_object_id' => 0,
			$attr.'_object_face' => 0,
			$attr.'_object_depth' => 0,
			$attr.'_port_id' => 0
		),
		array(
			'id' => array('=', $id)
		)
	);
}

function insertTableInventory(&$qls, $objID, $objFace, $objDepth, $objPort, $elementID, $elementFace, $elementDepth, $elementPort){
	$qls->SQL->insert(
		'app_inventory',
		array(
			'a_object_id',
			'a_object_face',
			'a_object_depth',
			'a_port_id',
			'b_object_id',
			'b_object_face',
			'b_object_depth',
			'b_port_id',
			'length',
			'editable',
			'order_id',
			'active'
		),
		array(
			$objID,
			$objFace,
			$objDepth,
			$objPort,
			$elementID,
			$elementFace,
			$elementDepth,
			$elementPort,
			0,
			0,
			0,
			0
		)
	);
}

function clearTablePopulated(&$qls, $objID, $objFace, $objDepth, $objPort){
	$qls->SQL->delete(
		'app_populated_port',
		array(
			'object_id' => array('=', $objID),
			'AND',
			'object_face' => array('=', $objFace),
			'AND',
			'object_depth' => array('=', $objDepth),
			'AND',
			'port_id' => array('=', $objPort)
		)
	);
}
?>
