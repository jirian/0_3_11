<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$qls->Security->check_auth_page('operator.php');
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
		$operation = $data['operation'];
		
		if ($operation == 'create_node') {
			
			$order = count($qls->App->envTreeArray) + 1;
			$parentID = $data['parent'];
			$nodeType = $data['type'];
			$nodeName = $data['name'];
			
			$attrArray = array('order', 'parent', 'name', 'type');
			$valueArray = array($order, $parentID, $nodeName, $nodeType);
			
			// Add column and value if floorplan object
			if($nodeType == 'floorplan') {
				array_push($attrArray, 'floorplan_img');
				array_push($valueArray, DEFAULT_FLOORPLAN_IMG);
			}
			
			// Insert new node into env_tree table
			$qls->SQL->insert('app_env_tree', $attrArray, $valueArray);
			
			// Ajax response with auto-incremented node.id so jsTree can replace default 'j1_1' node.id
			$validate->returnData['success']['nodeID'] = $qls->SQL->insert_id();
			$validate->returnData['success']['nodeName'] = $nodeName;
			
			// Log history
			$parentName = $qls->App->envTreeArray[$parentID]['nameString'];
			$actionString = 'Added '.$nodeType.': <strong>'.$parentName.'.'.$nodeName.'</strong>';
			$qls->App->logAction(2, 1, $actionString);
			
		} else if ($operation == 'rename_node') {
			
			$nodeID = $data['id'];
			$nodeName = $data['name'];
			
			$qls->SQL->update('app_env_tree', array('name'=>$nodeName), 'id='.$nodeID);
			
			// Log history
			$nodeType = $qls->App->envTreeArray[$nodeID]['type'];
			$originalNodeName = $qls->App->envTreeArray[$nodeID]['nameString'];
			$actionString = 'Renamed '.$nodeType.': From <strong>'.$originalNodeName.'</strong> to <strong>'.$nodeName.'</strong>';
			$qls->App->logAction(2, 2, $actionString);
			
		} else if ($operation == 'move_node') {
			
			$nodeID = $data['id'];
			$parentID = $data['parent'];
			$newOrder = $data['order'];
			$node = $qls->App->envTreeArray[$nodeID];
			
			$permitted = true;
			if($node['type'] == 'floorplan') {
				if($parentID != '#') {
					$parent = $qls->App->envTreeArray[$parentID];
					$parentType = $parent['type'];
					if($parentType == 'floorplan' or $parentType == 'pod' or $parentType == 'cabinet') {
						$permitted = false;
					}
				}
			} else if($node['type'] == 'cabinet') {
				if($parentID != '#') {
					$parent = $qls->App->envTreeArray[$parentID];
					$parentType = $parent['type'];
					if($parentType == 'cabinet' or $parentType == 'floorplan') {
						$permitted = false;
					}
				}
			}
			
			if($permitted) {
				
				$origOrder = $node['order'];
				$ordDiff = $newOrder - $origOrder;
				
				// Set the user defined order
				foreach($qls->App->envTreeArray as $envTreeNode) {
					$envTreeNodeID = $envTreeNode['id'];
					$envTreeNodeOrder = $envTreeNode['order'];
					
					if($envTreeNodeID != $nodeID) {
						if($ordDiff < 0) {
							if($envTreeNodeOrder >= $newOrder and $envTreeNodeOrder <= $origOrder) {
								$newEnvTreeNodeOrder = $envTreeNodeOrder + 1;
								$qls->SQL->update('app_env_tree', array('order'=>$newEnvTreeNodeOrder), array('id'=>array('=', $envTreeNodeID)));
							}
						} else if($ordDiff > 0) {
							if($envTreeNodeOrder <= $newOrder and $envTreeNodeOrder >= $origOrder) {
								$newEnvTreeNodeOrder = $envTreeNodeOrder - 1;
								$qls->SQL->update('app_env_tree', array('order'=>$newEnvTreeNodeOrder), array('id'=>array('=', $envTreeNodeID)));
							}
						}
					}
				}
				
				$qls->SQL->update('app_env_tree', array('parent'=>$parentID, 'order'=>$newOrder), 'id='.$nodeID);
				
				// Log history
				$nodeType = $qls->App->envTreeArray[$nodeID]['type'];
				$nodeName = $qls->App->envTreeArray[$nodeID]['name'];
				$originalParentID = $qls->App->envTreeArray[$nodeID]['parent'];
				$originalParentName = ($originalParentID == '#') ? 'Root' : $qls->App->envTreeArray[$originalParentID]['nameString'];
				$newParentName = ($parentID == '#') ? 'Root' : $qls->App->envTreeArray[$parentID]['nameString'];
				$actionString = 'Moved '.$nodeType.': From <strong>'.$originalParentName.'.'.$nodeName.'</strong> to <strong>'.$newParentName.'.'.$nodeName.'</strong>';
				$qls->App->logAction(2, 2, $actionString);
				
			} else {
				$errMsg = 'Invalid node move.';
				array_push($validate->returnData['error'], $errMsg);
			}
			
		} else if ($operation == 'delete_node') {
			
			$nodeID = $data['id'];
			$occupiedArray = array();
			
			$envTree = array();
			$query = $qls->SQL->select('*', 'app_env_tree');
			while($row = $qls->SQL->fetch_assoc($query)) {
				$envTree[$row['id']] = $row;
			}
			
			// Get the number of nodes that will be deleted
			$nodeCount = getNodeCount($nodeID, $qls);
			
			// Will there be anymore nodes left after deletion?
			if($nodeCount - count($envTree) == 0) {
				$errorMsg = 'Cannot delete all environment nodes.';
				array_push($validate->returnData['error'], $errorMsg);
			}
			
			// Does this node's children contain any objects?
			canDeleteNode($nodeID, $occupiedArray, $envTree, $qls);
			if(count($occupiedArray)) {
				$occupiedCabinetList = '';
				foreach($occupiedArray as $index => $occupiedNode) {
					$separator = $index == 0 ? '' : ', ';
					$occupiedCabinetList = $occupiedCabinetList.$separator.'<strong>'.$occupiedNode['name'].'</strong>';
				}
				$errorMsg = 'Cannot delete environment node.  The following cabinets are occupied by objects: '.$occupiedCabinetList;
				array_push($validate->returnData['error'], $errorMsg);
			}
			
			// If no errors, delete the environment node.
			if (!count($validate->returnData['error'])){
				deleteNodes($nodeID, $qls);
				
				// Reorder nodes
				$counter = 1;
				$query = $qls->SQL->select('*', 'app_env_tree', false, array('order', 'ASC'));
				while($row = $qls->SQL->fetch_assoc($query)) {
					$rowID = $row['id'];
					$rowOrder = $row['order'];
					if ($rowOrder != $counter) {
						$qls->SQL->update('app_env_tree', array('order'=>$counter), array('id'=>array('=', $rowID)));
					}
					$counter++;
				}
				
				// Log history
				$nodeType = $qls->App->envTreeArray[$nodeID]['type'];
				$nodeName = $qls->App->envTreeArray[$nodeID]['nameString'];
				$actionString = 'Deleted '.$nodeType.': <strong>'.$nodeName.'</strong>';
				$qls->App->logAction(2, 3, $actionString);
				
			}
			
		}
	}
	echo json_encode($validate->returnData);
}

function validate(&$data, &$validate, &$qls){
	$operationArray = array('create_node', 'rename_node', 'move_node', 'delete_node');
	
	// Validate the operation command
	if ($validate->validateInArray($data['operation'], $operationArray, 'operation.  Cannot delete the only remaining environment node.')) {
		$operation = $data['operation'];
		
		if ($operation == 'create_node') {
			
			$type = $data['type'];
			$parentID = $data['parent'];
			$typeArray = array('location', 'pod', 'cabinet', 'floorplan');
		
			// Validate node type
			if($validate->validateInArray($type, $typeArray, 'node type.')) {
				
				// Validate cabinet ID
				if($validate->validateTreeID($parentID, 'cabinet ID')) {
					
					// Generate unique name
					$name = $qls->App->findUniqueName($parentID, $type);
					if($name === false) {
						$errMsg = 'Unable to find unique name.';
						array_push($validate->returnData['error'], $errMsg);
					} else {
						$data['name'] = $name;
					}
				}
			}
			
			// Validate entitlement
			if($type == 'cabinet') {
				$query = $qls->SQL->select('id', 'app_env_tree', array('type' => array('=', 'cabinet')));
				$cabNum = $qls->SQL->num_rows($query) + 1;
				
				if(!$qls->App->checkEntitlement('cabinet', $cabNum)) {
					$errMsg = 'Exceeded entitled cabinet count.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
				
		} else if ($operation == 'rename_node') {
			
			$nodeName = $data['name'];
			$nodeID = $data['id'];
			
			$validName = $validate->validateNameText($nodeName, 'environment node name');
			$validNodeID = $validate->validateTreeID($nodeID);
			
			// Validate node name is not a duplicate
			if($validName and $validNodeID) {
				if(isset($qls->App->envTreeArray[$nodeID])) {
					$parentID = $qls->App->envTreeArray[$nodeID]['parent'];
					$table = 'app_env_tree';
					$where = array('parent' => array('=', $parentID), 'AND', 'name' => array('=', $nodeName), 'AND', 'id' => array('!=', $nodeID));
					$errMsg = 'Duplicate node name.';
					$validate->validateDuplicate($table, $where, $errMsg);
				} else {
					$errMsg = 'Parent node does not exist.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
			
		} else if ($operation == 'move_node') {
			
			$parentID = $data['parent'];
			$nodeID = $data['id'];
			$nodeOrder = $data['order'];
			
			$validParentID = $validate->validateTreeID($parentID);
			$validNodeID = $validate->validateTreeID($nodeID);
			$validNodeOrder = $validate->validateID($nodeOrder, 'node order');
			
			// Node Order
			if ($validNodeOrder) {
				if ($nodeOrder > count($qls->App->envTreeArray) or $nodeOrder < 1) {
					$errMsg = 'Invalid node order.';
					array_push($validate->returnData['error'], $errMsg);
				}
			}
			
			// Same name
			if($validParentID and $validNodeID) {
				$nodeName = $qls->App->envTreeArray[$nodeID]['name'];
				foreach($qls->App->envTreeArray as $envTreeNode) {
					if($envTreeNode['parent'] == $parentID and $envTreeNode['id'] != $nodeID) {
						if(strtolower($nodeName) == strtolower($envTreeNode['name'])) {
							$errMsg = 'Duplicate node name.';
							array_push($validate->returnData['error'], $errMsg);
						}
					}
				}
			}
			
		} else if ($operation == 'delete_node') {
			
			$nodeID = $data['id'];
			
			$validate->validateTreeID($nodeID);
			
		}
	}
	
	return;
}

function canDeleteNode($id, &$occupiedArray, &$envTree, &$qls){

	$query = $qls->SQL->select('id', 'app_object', array('env_tree_id' => array('=', $id)));
	if($row = $qls->SQL->num_rows($query)) {
		array_push($occupiedArray, array('id' => $id, 'name' => $envTree[$id]['name']));
	}

	$query = $qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $id)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		canDeleteNode($row['id'], $occupiedArray, $envTree, $qls);
	}
	return;
}

function deleteNodes($id, &$qls){
	$query = $qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $id)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		deleteNodes($row['id'], $qls);
	}
	$qls->SQL->delete('app_env_tree', array('id' => array('=', $id)));
	$qls->SQL->delete('app_cable_path', array('cabinet_a_id' => array('=', $id), 'OR', 'cabinet_b_id' => array('=', $id)));
	$qls->SQL->delete('app_cabinet_adj', array('left_cabinet_id' => array('=', $id), 'OR', 'right_cabinet_id' => array('=', $id)));
	return;
}

function getNodeCount($id, &$qls, &$count=0){
	$query = $qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $id)));
	while($row = $qls->SQL->fetch_assoc($query)) {
		getNodeCount($row['id'], $qls, $count);
	}
	$count = $count + 1;
	return $count;
}

?>
