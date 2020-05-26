<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

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
		
		$cabinetView = $data['view'];
		$page = $data['page'];
		
		foreach($data['cabinetArray'] as $cabinetData) {
		
			$html = '';

			//Retreive name of the cabinet or location
			$elementID = $cabinetData['id'];
			$elementFace = $cabinetData['face'];
			$elementType = $cabinetData['type'];
			if($elementType == 'cabinet') {
				$cabinetID = $elementID;
				$cabinetFace = $elementFace;
			} else {
				$object = $qls->App->objectArray[$elementID];
				$cabinetID = $object['env_tree_id'];
				$cabinetFace = ($object['cabinet_front'] == $elementFace) ? 0 : 1;
			}
			
			$cabinetFace = $cabinetFace == 0 ? 'cabinet_front' : 'cabinet_back';
			$cabinet = $qls->App->envTreeArray[$cabinetID];
			$cabinetType = $cabinet['type'];
			$cabinetParentID = $cabinet['parent'];
			$ruOrientation = $cabinet['ru_orientation'];
			$cabinetName = $cabinet['name'];
			$cabinetSize = $cabinet['size'];
			
			// Retreive ancestor info
			$locationID = $cabinetParentID;
			$ancestorIDArray = array();
			while($locationID != '#') {
				$location = $qls->App->envTreeArray[$locationID];
				$locationName = $location['name'];
				$parentID = $location['parent'];
				$workingArray = array(
					'id' => $locationID,
					'parentID' => $parentID,
					'name' => $locationName
				);
				array_unshift($ancestorIDArray, $workingArray);
				$locationID = $parentID;
			}

			//Retreive cabinet object info
			$object = array();
			$insert = array();
			if(isset($qls->App->objectByCabinetArray[$cabinetID])) {
				foreach($qls->App->objectByCabinetArray[$cabinetID] as $objID) {
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

			//Retreive rackable objects
			$objectTemplate = array();
			$results = $qls->SQL->select('*', 'app_object_templates');
			while ($row = $qls->SQL->fetch_assoc($results)){
				$objectTemplate[$row['id']] = $row;
				$objectTemplate[$row['id']]['partitionData'] = json_decode($row['templatePartitionData'], true);
				$objectTemplate[$row['id']]['categoryName'] = $qls->App->categoryArray[$row['templateCategory_id']]['name'];
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

			$cursorClass = ($page == 'explore' or $page == 'diagram') ? 'cursorPointer' : 'cursorGrab';

			$html .= '<div class="cabinetContainer" data-cabinet-id="'.$cabinetID.'">';
			if($page == 'diagram') {
				$headerContent = '';
				$headerContent .= '<div>';
				$headerContent .= '<i class="fa fa-angle-left cabMoveArrow cursorPointer m-l-10"  data-cab-move-direction="left" title="Move Left"></i>';
				$headerContent .= '<i class="fa fa-angle-right cabMoveArrow cursorPointer m-l-10"  data-cab-move-direction="right" title="Move Right"></i>';
				$headerContent .= '</div>';
				$headerContent .= '<div>'.$cabinetName.'</div>';
				$headerContent .= '<div>';
				$headerContent .= '<i class="fa fa-times cabClose cursorPointer m-r-10" title="Close"></i>';
				$headerContent .= '</div>';
			} else {
				$headerContent = '<div></div><div>'.$cabinetName.'</div><div></div>';
			}
			$html .= '<div id="cabinetHeader" class="cab-height cabinet-border cabinet-end" data-cabinet-id="'.$cabinetID.'" data-ru-orientation="'.$ruOrientation.'" style="display:flex; justify-content:space-between;">'.$headerContent.'</div>';
			$html .= '<input id="cabinetID" type="hidden" value="'.$cabinetID.'">';
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
					$objClassArray = array(
						'rackObj',
						$cursorClass,
						'draggable',
						'object',
						'RU'.$RUSize
					);
					
					$html .= '<td class="droppable" rowspan="'.$RUSize.'" data-cabinetRU="'.$cabLoop.'">';
					if($cabinetView == 'port') {
						
						$html .= $qls->App->generateObjContainer($template, $face, $objClassArray, $objectID);
						$rackObj = true;
						$html .= $qls->App->buildStandard($partitionData[$face], $rackObj, $objectID, $face);
						$html .= '</div>';
						
					} else if($cabinetView == 'visual') {
						
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
						$html .= '<td class="droppable" rowspan="1" data-cabinetRU="'.$cabLoop.'" style="display:none;">';
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
			$html .= '</div>';
			
			$workingArray = array(
				'locationID' => $cabinetParentID,
				'ancestorIDArray' => $ancestorIDArray,
				'html' => $html
			);
			array_push($validate->returnData['data'], $workingArray);
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	
	$pageArray = array('explore', 'environment', 'diagram');
	$validate->validateInArray($data['page'], $pageArray, 'page');
	
	$viewArray = array('port', 'name', 'visual');
	$validate->validateInArray($data['view'], $viewArray, 'view');
	
	if(is_array($data['cabinetArray'])) {
		if(count($data['cabinetArray']) < 100) {
			foreach($data['cabinetArray'] as $cabinet) {
				$validID = $validate->validateID($cabinet['id'], 'element ID');
				
				$faceArray = array(0, 1);
				$validate->validateInArray($cabinet['face'], $faceArray, 'element face');
				
				$typeArray = array('cabinet', 'object');
				$validType = $validate->validateInArray($cabinet['type'], $typeArray, 'element type');
				
				if($validID and $validType) {
					if($cabinet['type'] == 'cabinet') {
						$cabinetID = $cabinet['id'];
						$cabinetType = $qls->App->envTreeArray[$cabinetID]['type'];
						if($cabinetType != 'cabinet') {
							$errMsg = 'Invalid location type.';
							array_push($validate->returnData['error'], $errMsg);
						}
					}
				}
			}
		} else {
			$errMsg = 'Cabinet array too large.';
			array_push($validate->returnData['error'], $errMsg);
		}
	} else {
		$errMsg = 'Invalid cabinet array.';
		array_push($validate->returnData['error'], $errMsg);
	}
	return;
}