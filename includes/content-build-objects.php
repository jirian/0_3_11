
<!--
/////////////////////////////
//Placeable objects
/////////////////////////////
-->

<?php
$page = basename($_SERVER['PHP_SELF']);
$cursorClass = (($page == 'templates.php') or ($page == 'retrieve_build-objects.php') or ($page == 'retrieve_template-catalog.php')) ? 'cursorPointer' : 'cursorGrab';
$faceCount = ($page == 'retrieve_template-catalog.php') ? 1 : 2;

$categoryArray = ($page == 'retrieve_template-catalog.php') ? $categoryArray : $qls->App->categoryArray;
$templateCategoryArray = ($page == 'retrieve_template-catalog.php') ? $templateCategoryArray : $qls->App->templateCategoryArray;
$templateArray = ($page == 'retrieve_template-catalog.php') ? $templateArray : $qls->App->templateArray;

for ($x=0; $x<$faceCount; $x++){
	
	$display = $x==0 ? '' : ' style="display:none;"';
	$availableContainerID = ($page == 'retrieve_template-catalog.php') ? 'templateCatalogAvailableContainer' : 'availableContainer'.$x;
	echo '<div id="'.$availableContainerID.'"'.$display.'>';
	
	foreach ($categoryArray as $categoryID => $category) {
		$categoryName = $category['name'];

		echo '<div class="categoryContainerEntire">';
			echo '<h4 class="categoryTitle cursorPointer" data-category-name="'.$categoryName.'"><i class="fa fa-caret-right"></i>'.$categoryName.'</h4>';
			echo '<div class="category'.$categoryName.'Container categoryContainer" style="display:none;">';
			foreach ($templateCategoryArray[$categoryID] as $templateName => $templateDetails) {
				
				$templateType = $templateDetails['type'];
				$templateID = $templateDetailsID = $templateDetails['id'];
				
				if($templateType == 'regular') {
					$templateOrganic = $templateArray[$templateID];
					$templateIcon = '';
					$isCombinedTemplate = false;
				} else {
					$combinedTemplate = $qls->App->combinedTemplateArray[$templateID];
					$templateID = $combinedTemplate['template_id'];
					$childTemplateData = json_decode($combinedTemplate['childTemplateData'], true);
					$templateOrganic = $templateArray[$templateID];
					$templateIcon = '<i class="fa fa-object-group cursorPointer iconCombinedTemplate"></i> ';
					$isCombinedTemplate = $childTemplateData;
				}
				$templateOrganic['templatePartitionData'] = json_decode($templateOrganic['templatePartitionData'], true);
				
				if (isset($templateOrganic['templatePartitionData'][$x])) {
					
					$partitionData = $templateOrganic['templatePartitionData'][$x];
					$type = $templateOrganic['templateType'];
					$RUSize = $templateOrganic['templateRUSize'];
					$function = $templateOrganic['templateFunction'];
					$mountConfig = $templateOrganic['templateMountConfig'];
					$categoryData = isset($templateOrganic['categoryData']) ? $templateOrganic['categoryData'] : false;
					$objID = false;
					
					echo '<div class="object-wrapper object'.$templateDetailsID.' '.$templateType.'" data-template-id="'.$templateDetailsID.'" data-template-name="'.$templateName.'">';
					echo '<h4 class="header-title m-t-0 m-b-15">'.$templateIcon.'<div id="templateName'.$templateDetailsID.'" class="'.$templateType.'" style="display:inline;">'.$templateName.'</div></h4>';
					
					if ($type == 'Standard'){
						$objClassArray = array(
							'stockObj',
							$cursorClass,
							'draggable',
							'RU'.$RUSize
						);
						$objID = false;
						echo $qls->App->generateObjContainer($templateOrganic, $x, $objClassArray, $isCombinedTemplate, $objID, $categoryData);
						echo $qls->App->buildStandard($partitionData, $isCombinedTemplate, $objID, $x);
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
												echo $qls->App->generateObjContainer($templateOrganic, $templateFace, $objClassArray, $isCombinedTemplate, $objID, $categoryData);
												echo $qls->App->buildStandard($partitionData, $isCombinedTemplate);
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