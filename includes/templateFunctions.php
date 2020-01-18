<?php

function buildInsert($data, $portType, $RUSize, $class='', $dataAttr='', $style='', $depthCounter=0){
	$html = '';
	foreach($data as $element){
		$flexDirection = $element['direction'];
		if($depthCounter == 0) {
			//$flex = $flexDirection == 'column' ? $element['hunits']/10 : $element['vunits']/($RUSize*2);
			$flex = $flexDirection == 'column' ? $element['hunits']/10 : $element['vunits']*0.5;
			$dataAttr = $dataAttr;
		} else {
			$flex = $element['flex'];
			$dataAttr = '';
		}
		$selectable = $element['partitionType'] == 'Generic' ? '' : ' selectable';
		$html .= '<div class="flex-container'.$selectable.$class.'" style="flex:'.$flex.'; flex-direction:'.$flexDirection.';'.$style.'" data-depth="'.$depthCounter.'"'.$dataAttr.'>';
		switch($element['partitionType']){
			case 'Generic':
				if(isset($element['children'])){
					$depthCounter++;
					$html .= buildInsert($element['children'], $portType, $RUSize, '', '', '', $depthCounter);
				}
				break;
				
			case 'Connectable':
				$portX = $element['portLayoutX'];
				$portY = $element['portLayoutY'];
				$html .= '<table class="border-black" style="border-collapse: collapse;height:100%;width:100%;">';
					for ($y = 0; $y < $portY; $y++){
						//$html .= '<tr style="width:100%;height:'.(100/$portY).'%;">';
						$html .= '<tr>';
						for ($x = 0; $x < $portX; $x++){
							//$html .= '<td style="width:'.(100/$portX).'%;height:'.(100/$portY).'%;">';
							$html .= '<td>';
							$html .= '<div class="port '.$portType[$element['portType']]['name'].'"></div>';
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

function buildStandard($data, $flexClass, $portType, &$depthCounter=0){
	$html = '';
	foreach($data as $element){
		$flexDirection = $element['direction'];
		$flex = $element['flex'];
		$hUnits = $element['hunits'];
		$vUnits = $element['vunits'];
		$selectable = $element['partitionType'] == 'Generic' ? '' : ' selectable';
		$html .= '<div class="'.$flexClass.$selectable.'" style="flex:'.$flex.'; flex-direction:'.$flexDirection.';" data-depth="'.$depthCounter.'" data-hunits="'.$hUnits.'" data-vunits="'.$vUnits.'">';
		switch($element['partitionType']){
			case 'Generic':
				if(isset($element['children'])){
					$depthCounter++;
					$html .= buildStandard($element['children'], 'flex-container', $portType, $depthCounter);
				}
				break;
				
			case 'Connectable':
				$portX = $element['portLayoutX'];
				$portY = $element['portLayoutY'];
				$html .= '<table class="border-black" style="border-collapse: collapse;height:100%;width:100%;">';
					for ($y = 0; $y < $portY; $y++){
						//$html .= '<tr style="width:100%;height:'.(100/$portY).'%;">';
						$html .= '<tr>';
						for ($x = 0; $x < $portX; $x++){
							//$html .= '<td style="width:'.(100/$portX).'%;height:'.(100/$portY).'%;">';
							$html .= '<td>';
							$html .= '<div class="port '.$portType[$element['portType']]['name'].'"></div>';
							$html .= '</td>';
						}
						$html .= "</tr>";
					}
				$html .= '</table>';
				break;
				
			case 'Enclosure':
				$encX = $element['encLayoutX'];
				$encY = $element['encLayoutY'];
				
				$html .= '<div class="flex-container-parent enclosure" data-encLayoutX="'.$encX.'" data-encLayoutY="'.$encY.'">';
				for ($y = 0; $y < $encY; $y++){
					$rowBorderClass = ($y == $encY) ? 'borderBottom' : 'borderTop';
					$html .= '<div class="'.$rowBorderClass.' tableRow">';
						for ($x = 0; $x < $encX; $x++){
							$colBorderClass = ($x == $encX) ? 'borderRight' : 'borderLeft';
							$html .= '<div class="'.$colBorderClass.' tableCol enclosureTable insertDroppable"></div>';
						}
					$html .= '</div>';
				}
				$html .= '</div>';
				
				break;
				
			case 'Insert':
				break;
		}
		$html .= '</div>';
		$depthCounter++;
	}
	return $html;
}
?>