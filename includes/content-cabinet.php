<!--
/////////////////////////////
//left Cabinet
/////////////////////////////
-->
		<?php 
			for ($x=0; $x<2; $x++) {
				if($x==1){
					$display = 'style="display:none;"';
				}
				echo '<div id="cabinetContainer'.$x.'" '.$display.'>';
				echo '<div class="cab-height cabinet-border cabinet-end"></div>';
				echo '<table class="cabinet">';

				$cabinetSize = 5;
				for ($cabLoop=$cabinetSize; $cabLoop>0; $cabLoop--){
					echo '<tr class="cabinet">';
					echo '<td class="cabinetRailRU cabinetRailLeft cabinet">';
					echo $cabLoop;
					echo '</td>';
					echo '<td class="RackUnit'.$x.'">';
					if ($cabLoop == $cabinetSize) {
						$activeObj = ($x==0) ? 'activeObj' : '';
						echo '<div id="previewObj'.$x.'" class="'.$activeObj.' objBaseline" data-h-units="24" data-v-units="2"></div>';
					}
					echo '</td>';
					echo '<td class="cabinetRailRU cabinetRailRight cabinet"></td>';
					echo '</tr>';
				}
				echo '</table>';
				echo '<div class="cab-height cabinet-end"></div>';
				echo '<div class="cab-height cabinet-foot"></div>';
				echo '<div class="cab-height cabinet-blank"></div>';
				echo '<div class="cab-height cabinet-foot"></div>';
			echo '</div>';
			}
		?>
