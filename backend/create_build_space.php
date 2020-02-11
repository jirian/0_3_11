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
		
		$html = '';

		//Retreive name of the cabinet or location
		$node_id = $data['id'];
		$cabinetFace = $data['face'];
		$cabinetView = $data['view'];
		$page = $data['page'];
		$cabinetFace = $cabinetFace == 0 ? 'cabinet_front' : 'cabinet_back';
		$node_info = $qls->App->envTreeArray[$node_id];
		$ruOrientation = $node_info['ru_orientation'];
		$node_name = $node_info['name'];
		$cabinetSize = $node_info['size'];

		//Retreive cabinet object info
		$object = array();
		$insert = array();
		if(isset($qls->App->objectByCabinetArray[$node_id])) {
			foreach($qls->App->objectByCabinetArray[$node_id] as $objID) {
				$obj = $qls->App->objectArray[$objID];
				if($obj[$cabinetFace] !== null) {
					$templateID = $obj['template_id'];
					$template = $qls->App->templateArray[$templateID];
					if($template['templateType'] == 'Standard') {
						$RU = $obj['RU'];
						$object[$RU] = $obj;
						$object[$RU]['face'] = $obj[$cabinetFace];
					} else {
						$parentID = $obj['parent_id'];
						$parentFace = $obj['parent_face'];
						$parentDepth = $obj['parent_depth'];
						$insertSlotX = $obj['insertSlotX'];
						$insertSlotY = $obj['insertSlotY'];
						$insert[$parentID][$parentFace][$parentDepth][$insertSlotX][$insertSlotY] = $obj;
					}
				}
			}
		}

		//Retreive categories
		$category = array();
		$categoryInfo = $qls->SQL->select('*', 'app_object_category');
		while ($categoryRow = $qls->SQL->fetch_assoc($categoryInfo)){
			$category[$categoryRow['id']]['name'] = $categoryRow['name'];
		}

		//Retreive port orientation
		$portOrientation = array();
		$results = $qls->SQL->select('*', 'shared_object_portOrientation');
		while ($row = $qls->SQL->fetch_assoc($results)){
			$portOrientation[$row['id']]['name'] = $row['name'];
		}

		//Retreive port type
		$portType = array();
		$results = $qls->SQL->select('*', 'shared_object_portType');
		while ($row = $qls->SQL->fetch_assoc($results)){
			$portType[$row['id']]['name'] = $row['name'];
		}

		//Retreive media type
		$mediaType = array();
		$results = $qls->SQL->select('*', 'shared_mediaType');
		while ($row = $qls->SQL->fetch_assoc($results)){
			$mediaType[$row['value']]['name'] = $row['name'];
		}

		//Retreive rackable objects
		$objectTemplate = array();
		$results = $qls->SQL->select('*', 'app_object_templates');
		while ($row = $qls->SQL->fetch_assoc($results)){
			$objectTemplate[$row['id']] = $row;
			$objectTemplate[$row['id']]['partitionData'] = json_decode($row['templatePartitionData'], true);
			$objectTemplate[$row['id']]['categoryName'] = $category[$row['templateCategory_id']]['name'];
			unset($objectTemplate[$row['id']]['templatePartitionData']);
		}

		//Retreive patched ports
		$patchedPortTable = array();
		$query = $qls->SQL->select('*', 'app_inventory');
		while ($row = $qls->SQL->fetch_assoc($query)){
			array_push($patchedPortTable, $row['a_object_id'].'-'.$row['a_object_face'].'-'.$row['a_object_depth'].'-'.$row['a_port_id']);
			array_push($patchedPortTable, $row['b_object_id'].'-'.$row['b_object_face'].'-'.$row['b_object_depth'].'-'.$row['b_port_id']);
		}

		//Retreive populated ports
		$populatedPortTable = array();
		$query = $qls->SQL->select('*', 'app_populated_port');
		while ($row = $qls->SQL->fetch_assoc($query)){
			array_push($populatedPortTable, $row['object_id'].'-'.$row['object_face'].'-'.$row['object_depth'].'-'.$row['port_id']);
		}

		$cursorClass = ($page == 'explore') ? 'cursorPointer' : 'cursorGrab';

		$html .= '<div id="cabinetHeader" class="cab-height cabinet-border cabinet-end" data-cabinet-id="'.$node_id.'" data-ru-orientation="'.$ruOrientation.'">'.$node_name.'</div>';
		$html .= '<input id="cabinetID" type="hidden" value="'.$node_id.'">';
		$html .= '<input id="objectID" type="hidden" value="">';
		$html .= '<table id="cabinetTable" class="cabinet">';
		$skipCounter = 0;
		for ($cabLoop=$cabinetSize; $cabLoop>0; $cabLoop--){
			
			if($ruOrientation == 0) {
				$RUNumber = $cabLoop;
			} else {
				$RUNumber = $cabinetSize - ($cabLoop - 1);
			}
			$html .= '<tr class="cabinet cabinetRU">';
			$html .= '<td class="cabinet cabinetRail leftRail">'.$RUNumber.'</td>';
			if (array_key_exists($cabLoop, $object)){
				$objName = $object[$cabLoop]['name'];
				$face = $object[$cabLoop]['face'];
				$templateID = $object[$cabLoop]['template_id'];
				$template = $qls->App->templateArray[$templateID];
				$partitionData = json_decode($template['templatePartitionData'], true);
				$function = $template['templateFunction'];
				$type = $template['templateType'];
				$mountConfig = $template['templateMountConfig'];
				$objectID = $object[$cabLoop]['id'];
				$RUSize = $template['templateRUSize'];
				$categoryID = $template['templateCategory_id'];
				$categoryName = $qls->App->categoryArray[$categoryID]['name'];
				$html .= '<td class="droppable" rowspan="'.$RUSize.'" data-cabinetRU="'.$cabLoop.'">';
				if($cabinetView == 'port') {
					
					$objClassArray = array(
						'rackObj',
						$cursorClass,
						'draggable',
						'RU'.$RUSize
					);
					
					$html .= $qls->App->generateObjContainer($template, $face, $objClassArray, $objectID);
					$rackObj = true;
					$html .= $qls->App->buildStandard($partitionData[$face], $rackObj, $objectID, $face);
					$html .= '</div>';
					
				} else if($cabinetView == 'visual') {
					
					$objClassArray = array(
						'rackObj',
						$cursorClass,
						'draggable',
						'RU'.$RUSize
					);
					
					$categoryData = false;
					$html .= $qls->App->generateObjContainer($template, $face, $objClassArray, $objectID, $categoryData, $cabinetView);
					$rackObj = true;
					$html .= $qls->App->buildStandard($partitionData[$face], $rackObj, $objectID, $face, $cabinetView);
					$html .= '</div>';
					
				} else if($cabinetView == 'name') {
					
					$html .= '<div data-objectID="'.$objectID.'" data-templateID="'.$templateID.'" data-RUSize="'.$RUSize.'" data-objectFace="'.$face.'" class="parent partition category'.$categoryName.' border-black obj-style initialDraggable rackObj selectable"><strong>'.$objName.'</strong></div>';
					
				}
				$skipCounter = $RUSize-1;
			} else {
				if ($skipCounter == 0){
					$html .= '<td class="droppable" rowspan="1" data-cabinetRU="'.$cabLoop.'">';
				} else {
					echo '<td class="droppable" rowspan="1" data-cabinetRU="'.$cabLoop.'" style="display:none;">';
					$skipCounter--;
				}
			}
			$html .= '</td>';
			$html .= '<td class="cabinet cabinetRail rightRail"></td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$html .= '<div class="cab-height cabinet-end"></div>';
		$html .= '<div class="cab-height cabinet-foot"></div>';
		$html .= '<div class="cab-height cabinet-blank"></div>';
		$html .= '<div class="cab-height cabinet-foot"></div>';
		
		$validate->returnData['success']['html'] = $html;
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	return;
}