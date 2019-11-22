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
							$objAttrArray = array(
								'data-template-object-id' => $objectID,
								'data-template-type' => $type,
								'data-template-id' => $templateID,
								'data-object-face' => $face,
								'data-object-mount-config' => $mountConfig,
								'data-ru-size' => $RUSize,
								'data-template-function' => '"'.$function.'"',
								'data-template-category-id' => $categoryID,
								'data-template-category-name' => $categoryName
							);
							
							$objClassArray = array(
								'rackObj',
								$cursorClass,
								'draggable'
							);
							//echo '<div data-object-id="'.$objectID.'" data-template-id="'.$templateID.'" data-ru-size="'.$RUSize.'" data-object-face="'.$face.'" class="parent partition category'.$categoryName.' border-black obj-style initialDraggable rackObj selectable">';
							//echo buildPortPartitions($objectTemplate[$templateID]['partitionData'][$face], $objectID, $face, $qls, $function, $objName);
							echo $qls->App->generateTemplateStandardContainer($template, $face, $objClassArray, $objAttrArray);
							$rackObj = true;
							$objClassArray = array();
							echo $qls->App->buildStandard($partitionData[$face], $rackObj, $categoryName, $objClassArray, $objAttrArray, $objectID, $face);
							echo '</div>';
						} else if($cabinetView == 'visual') {
							$templateImgAttr = $face == 0 ? 'frontImage' : 'rearImage';
							$templateImgPath = '/images/templateImages/'.$objectTemplate[$templateID][$templateImgAttr];
							echo '<div style="background-image: url('.$templateImgPath.'); background-size: 100% 100%" data-objectID="'.$objectID.'" data-templateID="'.$templateID.'" data-RUSize="'.$RUSize.'" data-objectFace="'.$face.'" class="parent partition category'.$categoryName.' border-black obj-style initialDraggable rackObj selectable">';
							echo buildVisualPartitions($objectTemplate[$templateID]['partitionData'][$face], $objectID, $face, $qls, $function, $objName);
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

<?php

function buildVisualPartitions($data, $objectID, $face, &$qls, $function, $objName, &$depthCounter=0){
	$html = '';
	foreach($data as $element){
		$flexDirection = $element['direction'];
		$flex = $element['flex'];
		$flexClass = $depthCounter == 0 ? 'flex-container-parent' : 'flex-container';
		
		switch($element['partitionType']){
			case 'Generic':
				$html .= '<div class="'.$flexClass.'" style="flex:'.$flex.'; flex-direction:'.$flexDirection.';">';
				if(isset($element['children'])){
					$depthCounter++;
					$html .= buildVisualPartitions($element['children'], $objectID, $face, $qls, $function, $objName, $depthCounter);
				}
				break;
				
			case 'Connectable':
				$html .= '<div class="'.$flexClass.' partition" style="flex:'.$flex.'; flex-direction:'.$flexDirection.';" data-depth="'.$depthCounter.'">';
				break;
				
			case 'Enclosure';
				$html .= '<div class="'.$flexClass.' partition selectable" style="flex:'.$flex.'; flex-direction:'.$flexDirection.';" data-depth="'.$depthCounter.'">';
				
				$encX = $element['encLayoutX'];
				$encY = $element['encLayoutY'];
				$html .= '<table class="enclosure border-black" style="border-collapse: collapse;height:100%;width:100%;">';
					for ($y = 0; $y < $encY; $y++){
						$html .= '<tr style="width:100%;height:'.(100/$encY).'%;">';
						for ($x = 0; $x < $encX; $x++){
							$html .= '<td class="enclosureTable insertDroppable" style="width:'.(100/$encX).'%;height:'.(100/$encY).'%;" data-encX="'.$x.'" data-encY="'.$y.'">';
							if(isset($GLOBALS['insert'][$objectID][$face][$depthCounter][$x][$y])) {
								$insertObject = $GLOBALS['insert'][$objectID][$face][$depthCounter][$x][$y];
								$insertID = $insertObject['id'];
								$insertTemplate = $GLOBALS['objectTemplate'][$insertObject['template_id']];
								$insertData = $insertTemplate['partitionData'][0];
								$categoryName = $GLOBALS['category'][$insertTemplate['templateCategory_id']]['name'];
								$templateImgPath = '/images/templateImages/'.$insertTemplate['frontImage'];
								$html .= '<div style="background-image: url('.$templateImgPath.'); background-size: 100% 100%;height:100%;" class="category'.$categoryName.' rackObj selectable" data-objectid="'.$insertID.'" data-objectFace="0" data-depth="0"></div>';
							}
							$html .= '</td>';
						}
						$html .= "</tr>";
					}
				$html .= '</table>';
				
				break;
		}
		$html .= '</div>';
		$depthCounter++;
	}
	return $html;
}

function buildPortInsert($insertName, $insertFunction, $data, $depthCounter, $insertID, $endpointTrunked, $categoryName=''){
	$html = '';
	foreach($data as $element){
		$flexDirection = $element['direction'];
		if($depthCounter == 0) {
			$flex = $flexDirection == 'column' ? $element['hunits']/10 : $element['vunits']*0.5;
			$class = ' category'.$categoryName.' parent partition rackObj obj-style obj-border insertDraggable selectable';
			$dataAttr = ' data-objectid="'.$insertID.'"';
		} else {
			$flex = $element['flex'];
			$class = ' partition';
			$dataAttr = '';
		}
		$selectable = $element['partitionType'] == 'Generic' ? '' : ' selectable';
		$html .= '<div class="insert '.$selectable.$class.'" style="display:flex;flex:'.$flex.'; flex-direction:'.$flexDirection.';"'.$dataAttr.' data-objectFace="0" data-depth="'.$depthCounter.'">';
		switch($element['partitionType']){
			case 'Generic':
				if(isset($element['children'])){
					$depthCounter++;
					$html .= buildPortInsert($insertName, $insertFunction, $element['children'], $depthCounter, $insertID, $endpointTrunked);
				}
				break;
				
			case 'Connectable':
				$portX = $element['portLayoutX'];
				$portY = $element['portLayoutY'];
				$html .= '<table class="border-black portTable" style="border-collapse: collapse;height:100%;width:100%;">';
					for ($y = 0; $y < $portY; $y++){
						$html .= '<tr>';
						for ($x = 0; $x < $portX; $x++){
							$html .= createPort($element, $x, $y, $depthCounter, true, $endpointTrunked, 0, $insertID, $insertName, $insertFunction);
						}
						$html .= "</tr>";
					}
				$html .= '</table>';
				break;
		}
		$html .= '</div>';
		$depthCounter++;
	}
	return $html;
}

function getPortIndex($orientation, $x, $y, $portX, $portY){
	$portTotal = $portX * $portY;
	if($orientation == 1) {
		$portIndex = ($y * $portX) + $x;
	} else if($orientation == 2) {
		$portIndex = ($x * $portY) + $y;
	} else if($orientation == 3) {
		$portIndex = ($y * $portX) + (($portX - $x) - 1);
	} else if($orientation == 4) {
		$portIndex = ($portTotal - ($y * $portX)) - ($portX - $x);
	}
	return $portIndex;
}

function createPort($obj, $x, $y, $depth, $insert, $endpointTrunked, $objFace, $objID, $insertName='', $insertFunction=''){
	$layoutX = $obj['portLayoutX'];
	$layoutY = $obj['portLayoutY'];
	$portIndex = getPortIndex($obj['portOrientation'], $x, $y, $layoutX, $layoutY);
	$portHeight = 100/$layoutY;
	$portWidth = 100/$layoutX;
	$populatedClass = 'unpopulated';
	if(in_array($objID.'-'.$objFace.'-'.$depth.'-'.$portIndex, $GLOBALS['patchedPortTable'])) {
		$populatedClass = 'populated';
	}
	if(in_array($objID.'-'.$objFace.'-'.$depth.'-'.$portIndex, $GLOBALS['populatedPortTable'])) {
		$populatedClass = 'populated';
	}
	$endpointTrunkedClass = $endpointTrunked ? 'endpointTrunked' : '';	
	$populatedCode39 = isset($populatedPorts[$depth][$portIndex]) ? $populatedPorts[$depth][$portIndex] : '';
	$portSeparator = $insertFunction == 'Passive' ? '.' : '';
	$portPrefix = $insert ? $insertName.$portSeparator.$obj['portPrefix'] : $obj['portPrefix'];
	$html .= '<td style="width:'.$portWidth.'%;height:'.$portHeight.'%;">';
	$html .= '<div id="port-'.$objID.'-'.$objFace.'-'.$depth.'-'.$portIndex.'" class="port '.$populatedClass.' '.$endpointTrunkedClass.'" data-Code39="'.$populatedCode39.'" data-portIndex="'.$portIndex.'" title="'.$portPrefix.($obj['portNumber']+$portIndex).'"></div>';
	$html .= '</td>';
	return $html;
}
?>
