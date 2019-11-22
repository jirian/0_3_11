
<!--
/////////////////////////////
//Placeable objects
/////////////////////////////
-->
<?php
$templateCatalog = false;
include($_SERVER['DOCUMENT_ROOT'].'/includes/content-build-objectData.php');
$page = basename($_SERVER['PHP_SELF']);
$cursorClass = (($page == 'templates.php') or ($page == 'retrieve_build-objects.php')) ? 'cursorPointer' : 'cursorGrab';

for ($x=0; $x<2; $x++){
	$display = $x==0 ? '' : ' style="display:none;"';
	echo '<div id="availableContainer'.$x.'"'.$display.'>';
	foreach($templates as $category => $categoryTemplate) {
		echo '<div class="categoryContainerEntire">';
			echo '<h4 class="categoryTitle cursorPointer" data-category-name="'.$category.'"><i class="fa fa-caret-right"></i>'.$category.'</h4>';
			echo '<div class="category'.$category.'Container categoryContainer" style="display:none;">';
			foreach ($categoryTemplate as $template) {
				if (isset($template['partitionData'][$x])) {
					
					$templateID = $template['id'];
					$templateOrganic = $qls->App->templateArray[$templateID];
					$templateName = $templateOrganic['templateName'];
					$partitionData = json_decode($templateOrganic['templatePartitionData'], true);
					$partitionData = $partitionData[$x];
					$type = $templateOrganic['templateType'];
					$RUSize = $templateOrganic['templateRUSize'];
					$function = $templateOrganic['templateFunction'];
					$mountConfig = $templateOrganic['templateMountConfig'];
					$categoryID = $templateOrganic['templateCategory_id'];
					
					echo '<div class="object-wrapper object'.$templateID.'" data-template-name="'.$templateName.'">';
					echo '<h4 class="templateName'.$templateID.' header-title m-t-0 m-b-15">'.$templateName.'</h4>';
					
					$objAttrArray = array(
						'data-template-type' => $type,
						'data-template-id' => $templateID,
						'data-object-face' => $x,
						'data-object-mount-config' => $mountConfig,
						'data-ru-size' => $RUSize,
						'data-template-function' => '"'.$function.'"',
						'data-template-category-id' => $categoryID,
						'data-template-category-name' => $category
					);
					
					if ($type == 'Standard'){
						$objClassArray = array(
							'stockObj',
							$cursorClass,
							'draggable'
						);
						echo $qls->App->generateTemplateStandardContainer($templateOrganic, $x, $objClassArray, $objAttrArray);
						$rackObj = false;
						$objClassArray = array();
						echo $qls->App->buildStandard($partitionData, $rackObj, $category, $objClassArray, $objAttrArray);
						echo '</div>';
					} else {
						$objClassArray = array(
							'stockObj',
							$cursorClass,
							'insertDraggable'
						);
						array_push($objClassArray, 'insertDraggable');
						$flexWidth = $partitionData[0]['hUnits']/10;
						$flexHeight = 1/$templateOrganic['templateEncLayoutY'];
						$parentHUnits = $templateOrganic['templateHUnits'];
						$parentVUnits = $templateOrganic['templateVUnits'];
						$parentEncLayoutX = $templateOrganic['templateEncLayoutX'];
						$parentEncLayoutY = $templateOrganic['templateEncLayoutY'];
						
						// Object type specific data
						$objAttrArray['data-template-type'] = '"Insert"';
						$objAttrArray['data-object-mount-config'] = 0;
						$objAttrArray['data-parent-h-units'] = $parentHUnits;
						$objAttrArray['data-parent-v-units'] = $parentVUnits;
						$objAttrArray['data-h-units'] = $parentHUnits;
						$objAttrArray['data-v-units'] = $parentVUnits;
						$objAttrArray['data-parent-enc-layout-x'] = $parentEncLayoutX;
						$objAttrArray['data-parent-enc-layout-y'] = $parentEncLayoutY;
						
						// Generate data attribute string
						$objAttrWorkingArray = array();
						foreach($objAttrArray as $attr => $value) {
							array_push($objAttrWorkingArray, $attr.'='.$value);
						}
						$objAttr = implode(' ', $objAttrWorkingArray);
						
							// Flex Container
							echo '<div class="RU'.$RUSize.'" style="flex-direction:row;">';
								// Partition Width
								echo '<div class="flex-container" style="flex-direction:column;flex:'.$flexWidth.';">';
									// Partition Height
									echo '<div class="flex-container" style="flex:'.$flexHeight.';">';
										echo '<div class="tableRow">';
										for($encX=0; $encX<$templateOrganic['templateEncLayoutX']; $encX++) {
											echo '<div class="tableCol">';
											if($encX == 0) {
												$encInsert = false;
												echo $qls->App->buildInsert($partitionData, $templateOrganic, $categoryName, $objClassArray, $objAttrArray, $encInsert, false);
											}
											echo '</div>';
										}
										echo '</div>';
									echo '</div>';
								echo '</div>';
							echo '</div>';
					}
					echo '</div>';
				}
			}
			echo '</div>';
		echo '</div>';
	}
	echo '</div>';
}
?>