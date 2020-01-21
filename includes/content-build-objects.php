
<!--
/////////////////////////////
//Placeable objects
/////////////////////////////
-->

<?php
$page = basename($_SERVER['PHP_SELF']);
$cursorClass = (($page == 'templates.php') or ($page == 'retrieve_build-objects.php') or ($page == 'retrieve_template-catalog.php')) ? 'cursorPointer' : 'cursorGrab';
$faceCount = ($page == 'retrieve_template-catalog.php') ? 1 : 2;

for ($x=0; $x<$faceCount; $x++){
	
	$display = $x==0 ? '' : ' style="display:none;"';
	$availableContainerID = ($page == 'retrieve_template-catalog.php') ? 'templateCatalogAvailableContainer' : 'availableContainer'.$x;
	echo '<div id="'.$availableContainerID.'"'.$display.'>';
	
	foreach($templates as $category => $categoryTemplate) {

		echo '<div class="categoryContainerEntire">';
			echo '<h4 class="categoryTitle cursorPointer" data-category-name="'.$category.'"><i class="fa fa-caret-right"></i>'.$category.'</h4>';
			echo '<div class="category'.$category.'Container categoryContainer" style="display:none;">';
			foreach ($categoryTemplate as $templateID => $templateOrganic) {
				if (isset($templateOrganic['templatePartitionData'][$x])) {
					
					
					$templateName = $templateOrganic['templateName'];
					$partitionData = $templateOrganic['templatePartitionData'][$x];
					$type = $templateOrganic['templateType'];
					$RUSize = $templateOrganic['templateRUSize'];
					$function = $templateOrganic['templateFunction'];
					$mountConfig = $templateOrganic['templateMountConfig'];
					$categoryID = $templateOrganic['templateCategory_id'];
					$categoryData = isset($templateOrganic['categoryData']) ? $templateOrganic['categoryData'] : false;
					
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
							'draggable',
							'RU'.$RUSize
						);
						$objID = false;
						echo $qls->App->generateObjContainer($templateOrganic, $x, $objClassArray, $objID, $categoryData);
						$rackObj = false;
						echo $qls->App->buildStandard($partitionData, $rackObj);
						echo '</div>';
					} else {
						$objClassArray = array(
							'stockObj',
							$cursorClass,
							'insertDraggable'
						);
						
						$hUnits = $partitionData[0]['hUnits'];
						$vUnits = $partitionData[0]['vUnits'];
						$minRUSize = ceil($vUnits/2);
						$totalVUnits = $minRUSize * 2;
						$heightNumerator = $vUnits/$totalVUnits;
						$flexWidth = $hUnits/24;
						$flexHeight = $heightNumerator/$templateOrganic['templateEncLayoutY'];
						$minRUSize = ceil($vUnits/2);
						
						// Generate data attribute string
						$objAttrWorkingArray = array();
						foreach($objAttrArray as $attr => $value) {
							array_push($objAttrWorkingArray, $attr.'='.$value);
						}
						$objAttr = implode(' ', $objAttrWorkingArray);
						
							// Flex Container
							echo '<div class="RU'.$minRUSize.'" style="display:flex;flex-direction:row;">';
								// Partition Width
								echo '<div class="flex-container" style="flex-direction:column;flex:'.$flexWidth.';">';
									// Partition Height
									echo '<div class="flex-container" style="flex:'.$flexHeight.';">';
										echo '<div class="tableRow">';
										for($encX=0; $encX<$templateOrganic['templateEncLayoutX']; $encX++) {
											echo '<div class="tableCol">';
											if($encX == 0) {
												$objClassArray = array(
													'stockObj',
													$cursorClass,
													'insertDraggable'
												);
												$templateFace = 0;
												$objID = false;
												echo $qls->App->generateObjContainer($templateOrganic, $templateFace, $objClassArray, $objID, $categoryData);
												$rackObj = false;
												echo $qls->App->buildStandard($partitionData, $rackObj);
												echo '</div>';
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