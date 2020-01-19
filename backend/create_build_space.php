<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

//Retreive name of the cabinet or location
$node_id = $_POST['id'];
$cabinetFace = $_POST['face'];
$cabinetView = $_POST['view'];
$cabinetFace = $cabinetFace == 0 ? 'cabinet_front' : 'cabinet_back';
$node_info = $qls->SQL->select('*', 'app_env_tree', 'id='.$node_id);
$node_info = $qls->SQL->fetch_assoc($node_info);
$node_id = $node_info['id'];
$node_name = $node_info['name'];
$cabinetSize = $node_info['size'];

//Retreive cabinet object info
$object = array();
$insert = array();
$results = $qls->SQL->select('*', 'app_object', 'env_tree_id = '.$node_id.' AND '.$cabinetFace.' IS NOT NULL');

while ($row = $qls->SQL->fetch_assoc($results)){
	$RU = $row['RU'];
	$object[$RU] = $row;
	$object[$RU]['face'] = $row[$cabinetFace];
	if($row['parent_id'] > 0) {
		$insert[$row['parent_id']][$row['parent_face']][$row['parent_depth']][$row['insertSlotX']][$row['insertSlotY']] = $row;
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

$page = basename($_GET['page']);
$cursorClass = ($page == 'explore') ? 'cursorPointer' : 'cursorGrab';

?>

<!--
/////////////////////////////
//Cabinet
/////////////////////////////
-->
	<div id="cabinetHeader" class="cab-height cabinet-border cabinet-end" data-cabinet-id="<?php echo $node_id; ?>"><?php echo $node_name; ?></div>
	<input id="cabinetID" type="hidden" value="<?php echo $node_id; ?>">
	<input id="objectID" type="hidden" value="">
	<table id="cabinetTable" class="cabinet">
	<?php
		$skipCounter = 0;
		for ($cabLoop=50; $cabLoop>0; $cabLoop--){?>
			<tr class="cabinet cabinetRU" <?php if($cabLoop>$cabinetSize){echo 'style="display:none"';}?>>
				<td class="cabinet cabinetRail leftRail"><?php echo $cabLoop;?></td>
				<?php
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
						echo '<td class="droppable" rowspan="'.$RUSize.'" data-cabinetRU="'.$cabLoop.'">';
						if($cabinetView == 'port') {
							
							$objClassArray = array(
								'rackObj',
								$cursorClass,
								'draggable',
								'RU'.$RUSize
							);
							
							echo $qls->App->generateObjContainer($template, $face, $objClassArray, $objectID);
							$rackObj = true;
							echo $qls->App->buildStandard($partitionData[$face], $rackObj, $objectID, $face);
							echo '</div>';
							
						} else if($cabinetView == 'visual') {
							
							$objClassArray = array(
								'rackObj',
								$cursorClass,
								'draggable',
								'RU'.$RUSize
							);
							
							$categoryData = false;
							echo $qls->App->generateObjContainer($template, $face, $objClassArray, $objectID, $categoryData, $cabinetView);
							$rackObj = true;
							echo $qls->App->buildStandard($partitionData[$face], $rackObj, $objectID, $face, $cabinetView);
							echo '</div>';
							
						} else if($cabinetView == 'name') {
							
							echo '<div data-objectID="'.$objectID.'" data-templateID="'.$templateID.'" data-RUSize="'.$RUSize.'" data-objectFace="'.$face.'" class="parent partition category'.$categoryName.' border-black obj-style initialDraggable rackObj selectable"><strong>'.$objName.'</strong></div>';
							
						}
						$skipCounter = $RUSize-1;
					} else {
						if ($skipCounter == 0){
							echo '<td class="droppable" rowspan="1" data-cabinetRU="'.$cabLoop.'">';
						} else {
							echo '<td class="droppable" rowspan="1" data-cabinetRU="'.$cabLoop.'" style="display:none;">';
							$skipCounter--;
						}
					}
					echo '</td>';
				?>
				<td class="cabinet cabinetRail rightRail"></td>
			</tr>
	<?php
	}?>
	</table>
	<div class="cab-height cabinet-end"></div>
	<div class="cab-height cabinet-foot"></div>
	<div class="cab-height cabinet-blank"></div>
	<div class="cab-height cabinet-foot"></div>
