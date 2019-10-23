<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

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
		$requestedData = $data['requestedData'];
		if($requestedData == 'initial') {
			$connectorValue = '1-1';
			$mediaValue = 1;
			
			$validate->returnData['success']['utilizationTable'] = buildUtilizationTable($qls);
			$validate->returnData['success']['historyTable'] = buildHistoryTable($qls);
			$validate->returnData['success']['donutData'] = buildInventoryData($connectorValue, $mediaValue, $qls);
		} else if($requestedData == 'inventory') {
			$connectorValue = $data['connectorValue'];
			$mediaValue = $data['mediaValue'];
			
			$validate->returnData['success']['donutData'] = buildInventoryData($connectorValue, $mediaValue, $qls);
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
}

function buildInventoryData($connectorValue, $mediaValue, &$qls){
	
	if(strpos($connectorValue, '-') !== false) {
		
		$connectorArray = explode('-', $connectorValue);
		
		$available = 0;
		$unavailable = 0;
		$deadWood = 0;
		$inTransit = 0;
		
		if($connectorArray[0] == 4) {
			
			$query = $qls->SQL->select('*', 'app_inventory', array('order_id' => array('>', 0), 'AND', 'b_id' => array('=', 0), 'AND', 'active' => array('=', 1)));
			$available = $qls->SQL->num_rows($query);
			
			$query = $qls->SQL->select('*', 'app_inventory', array('order_id' => array('>', 0), 'AND', 'b_id' => array('=', 0), 'AND', 'active' => array('=', 0)));
			$inTransit = $qls->SQL->num_rows($query);
			
		} else {
			
			$queryString = '((a_connector = '.$connectorArray[0].' AND b_connector = '.$connectorArray[1].') OR (a_connector = '.$connectorArray[1].' AND b_connector = '.$connectorArray[0].')) AND mediaType = '.$mediaValue;
			
			$query = $qls->SQL->select('*', 'app_inventory', $queryString);
			
			while($row = $qls->SQL->fetch_assoc($query)) {
				if($row['active'] == 0) {
					$inTransit += 1;
				}else if($row['a_object_id'] != 0 and $row['b_object_id'] != 0) {
					$unavailable += 1;
				} else if(($row['a_object_id'] == 0 and $row['b_object_id'] != 0) or ($row['a_object_id'] != 0 and $row['b_object_id'] == 0)) {
					$deadWood += 1;
				} else {
					$available += 1;
				}
			}
		}
	}
	
	return array(
		array('label' => 'In-Use', 'value' => $unavailable),
		array('label' => 'Not In-Use', 'value' => $available),
		array('label' => 'Pending Delivery', 'value' => $inTransit),
		array('label' => 'Dead Wood', 'value' => $deadWood)
	);
}

function buildUtilizationTable(&$qls){
	
	// Get Env Tree
	$envTreeArray = array();
	$query = $qls->SQL->select('*', 'app_env_tree');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$envTreeArray[$row['id']] = $row;
	}
	
	// Generate nameString and nameHash for Environment Tree
	foreach($envTreeArray as &$envTree) {
		$parentID = $envTree['parent'];
		$name = $envTree['name'];
		while($parentID != '#') {
			$name = $envTreeArray[$parentID]['name'].'.'.$name;
			$parentID = $envTreeArray[$parentID]['parent'];
		}
		$envTree['nameString'] = $name;
	}

	$populatedPortArray = array();
	$query = $qls->SQL->select('*', 'app_populated_port');
	while($row = $qls->SQL->fetch_assoc($query)) {
		if(!array_key_exists($row['object_id'], $populatedPortArray)) {
			$populatedPortArray[$row['object_id']] = 0;
		}
		
		$populatedPortArray[$row['object_id']] += 1;
	}
	
	$query = $qls->SQL->select('*', 'app_inventory');
	while($row = $qls->SQL->fetch_assoc($query)) {
		if(!array_key_exists($row['a_object_id'], $populatedPortArray)) {
			$populatedPortArray[$row['a_object_id']] = 0;
		}
		if(!array_key_exists($row['b_object_id'], $populatedPortArray)) {
			$populatedPortArray[$row['b_object_id']] = 0;
		}
		
		$populatedPortArray[$row['a_object_id']] += 1;
		$populatedPortArray[$row['b_object_id']] += 1;
	}
	
	$objectArray = array();
	$query = $qls->SQL->select('*', 'app_object');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$objectArray[$row['id']] = $row;
		$objectArray[$row['id']]['portTotal'] = 0;
		$objectArray[$row['id']]['nameString'] = $envTreeArray[$row['env_tree_id']]['nameString'].'.'.$row['name'];
		$objectArray[$row['id']]['portPopulated'] = 0;
	}
	
	$templateCompatibilityArray = array();
	$query = $qls->SQL->select('*', 'app_object_compatibility');
	while($row = $qls->SQL->fetch_assoc($query)) {
		if(!array_key_exists($row['template_id'], $templateCompatibilityArray)) {
			$templateCompatibilityArray[$row['template_id']] = array();
		}
		
		array_push($templateCompatibilityArray[$row['template_id']], $row);
	}
	
	foreach($objectArray as $object) {
		if($object['parent_id']) {
			$objectRef = &$objectArray[$object['parent_id']];
		} else {
			$objectRef = &$objectArray[$object['id']];
		}
		
		$objectRef['portPopulated'] += $populatedPortArray[$object['id']];
		
		if(array_key_exists($object['template_id'], $templateCompatibilityArray)) {
			foreach($templateCompatibilityArray[$object['template_id']] as $templateCompatibility) {
				$portX = $templateCompatibility['portLayoutX'];
				$portY = $templateCompatibility['portLayoutY'];
				if($portX and $portY) {
					$portSum = $portX * $portY;
					$objectRef['portTotal'] += $portSum;
				}
			}
		}
	}
	
	
	
	$table = '';
	
	foreach($objectArray as $object) {
		$portTotal = $object['portTotal'];
		if($portTotal > 0) {
			$name = $object['nameString'];
			$portPopulated = $object['portPopulated'];
			$ratioPopulated = $portPopulated / $portTotal;
			$percentPopulated = round($ratioPopulated * 100);
			
			if($ratioPopulated < 0.8) {
				$pillCategory = 'label-success';
			} else if($ratioPopulated < 0.9) {
				$pillCategory = 'label-warning';
			} else {
				$pillCategory = 'label-danger';
			}
			
			if($portTotal != 0) {
				$table .= '<tr>';
				$table .= '<td>'.$name.'</td>';
				$table .= '<td>'.$portTotal.'</td>';
				$table .= '<td>'.$portPopulated.'</td>';
				$table .= '<td><span class="label label-pill '.$pillCategory.'">'.$percentPopulated.'%</span></td>';
				$table .= '</tr>';
			}
		}
	}
	
	return $table;
}

function buildHistoryTable(&$qls){
	
	// Get History
	$historyArray = array();
	$query = $qls->SQL->select('*', 'app_history');
	while($row = $qls->SQL->fetch_assoc($query)) {
		$historyArray[$row['id']] = $row;
	}
	
	// Build History Table
	$table = '';
	foreach($historyArray as $history) {
		$date = $history['date'];
		$function = $history['function'];
		$actionType = $history['action_type'];
		$userID = $history['user_id'];
		$action = $history['action'];
		
		$function = $qls->App->historyFunctionArray[$function]['name'];
		$actionType = $qls->App->historyActionTypeArray[$actionType]['name'];
		$username = $qls->id_to_username($userID);
		$action = $history['action'];
		$dateFormatted = $qls->App->formatTime($date);
		
		$table .= '<tr>';
		$table .= '<td>'.$dateFormatted.'</td>';
		$table .= '<td>'.$function.'</td>';
		$table .= '<td>'.$actionType.'</td>';
		$table .= '<td>'.$username.'</td>';
		$table .= '<td>'.$action.'</td>';
		$table .= '</tr>';
	}
	
	//$table = '<tr><td>test</td><td>test</td><td>test</td><td>test</td><td>test</td></tr>';
	return $table;
}

?>
