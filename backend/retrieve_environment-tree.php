<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	if(isset($_GET['scope'])) {
		require_once '../includes/path_functions.php';
		$treeArray = array();
		$scope = $_GET['scope'];
		
		if($scope == 'partition') {
			$objectID = $_GET['objectID'];
			$objectFace = $_GET['objectFace'];
			$objectDepth = $_GET['objectDepth'];
			
			$treeLocations = buildTreeLocation($qls);
			$treeArray = array_merge($treeArray, $treeLocations);
			
			foreach($treeLocations as $location) {
				if($location['type'] == 'cabinet') {
					$locationValue = explode('-', $location['data']['globalID']);
					$cabinetID = $locationValue[1];
					
					$treeObjects = buildTreeObjects($qls, $cabinetID);
					$treeArray = array_merge($treeArray, $treeObjects);
					
					foreach($treeObjects as $object) {
						if($object['type'] == 'object' and $object['data']['objectID'] != $objectID) {
							$objectValue = explode('-', $object['data']['globalID']);
							$elementID = $objectValue[1];
							
							$treePortGroups = buildTreePortGroups($qls, $objectID, $objectFace, $objectDepth, $elementID);
							$treeArray = array_merge($treeArray, $treePortGroups);
						}
					}
				}
			}
		} else if($scope == 'portScan') {
			$connectorID = $_GET['connectorID'];
			
			$cable = $qls->App->inventoryByIDArray[$connectorID];
			$cablePortType = $cable['localConnector'];
			$cableMediaType = $cable['mediaType'];
			
			$treeLocations = $qls->App->buildTreeLocation();
			$treeArray = array_merge($treeArray, $treeLocations);
			
			foreach($treeLocations as $location) {
				if($location['type'] == 'cabinet' or $location['type'] == 'floorplan') {
					$locationValue = explode('-', $location['data']['globalID']);
					$cabinetID = $locationValue[1];
					
					$treeObjects = $qls->App->buildTreeObjects($cabinetID);
					$treeArray = array_merge($treeArray, $treeObjects);
					
					foreach($treeObjects as $object) {
						if($object['objectType'] != 'wap') {
							$objectValue = explode('-', $object['data']['globalID']);
							$nodeID = $objectValue[1];
							
							$treePorts = $qls->App->buildTreePorts($nodeID, false, $cablePortType, $cableMediaType);
							$treeArray = array_merge($treeArray, $treePorts);
						}
					}
				}
			}
			
		} else if($scope == 'portExplore' or $scope == 'portExplorePathFinder') {
			$objID = $_GET['objID'];
			$objFace = $_GET['objFace'];
			$objDepth = $_GET['objDepth'];
			$objPort = $_GET['objPort'];
			
			$object = $qls->App->objectArray[$objID];
			
			$templateID = $object['template_id'];
			$objectCabinetID = $object['env_tree_id'];
			$objectLocationType = $qls->App->EnvTreeArray[$objectCabinetID]['type'];
			
			$objectCompatibility = $qls->App->compatibilityArray[$templateID][$objFace][$objDepth];
			
			$objectType = $objectCompatibility['templateType'];
			$objectPortType = $objectCompatibility['portType'];
			$objectPartitionFunction = $objectCompatibility['partitionFunction'];
			
			$treeLocations = $qls->App->buildTreeLocation();
			$treeArray = array_merge($treeArray, $treeLocations);
			
			foreach($treeLocations as $location) {
				if($location['type'] == 'cabinet' or $location['type'] == 'floorplan') {
					$locationValue = explode('-', $location['data']['globalID']);
					$cabinetID = $locationValue[1];
					
					$treeObjects = $qls->App->buildTreeObjects($cabinetID);
					$treeArray = array_merge($treeArray, $treeObjects);
					
					foreach($treeObjects as $node) {
						$nodeValue = explode('-', $node['data']['globalID']);
						$nodeID = $nodeValue[1];
						
						$node = $qls->App->objectArray[$nodeID];
						$nodeTemplateID = $node['template_id'];
						$nodeTemplate = $qls->App->templateArray[$nodeTemplateID];
						
						// Limit ports to only those that are relevant
						$includePorts = true;
						if($nodeTemplate['templateType'] == 'wap') {
							$includePorts = false;
						}
						if($scope == 'portExplore') {
							if($objectLocationType == 'floorplan') {
								if($cabinetID != $objectCabinetID) {
									$includePorts = false;
								}
							}
						}
						
						if($includePorts) {
							$treePorts = $qls->App->buildTreePorts($nodeID, $objectCompatibility, false, false);
							$treeArray = array_merge($treeArray, $treePorts);
						}
					}
				}
			}
		} else if($scope == 'floorplanObject') {
			
			$objID = $_GET['objID'];
			$objFace = $_GET['objFace'];
			$objDepth = $_GET['objDepth'];
			$objPort = $_GET['objPort'];
			
			$object = $qls->App->objectArray[$objID];
			
			$templateID = $object['template_id'];
			
			//$objectCompatibility = $qls->App->compatibilityArray[$templateID][$objFace][$objDepth];
			$objectCompatibility = $qls->App->compatibilityArray[1][0][0];
			
			$objectPortType = 1;
			$objectPartitionFunction = 'Passive';
			
			$treeLocations = $qls->App->buildTreeLocation();
			$treeArray = array_merge($treeArray, $treeLocations);
			
			foreach($treeLocations as $location) {
				if($location['type'] == 'cabinet') {
					$locationValue = explode('-', $location['data']['globalID']);
					$cabinetID = $locationValue[1];
					
					$treeObjects = $qls->App->buildTreeObjects($cabinetID);
					$treeArray = array_merge($treeArray, $treeObjects);
					
					foreach($treeObjects as $node) {
						if($node['type'] == 'object') {
							$nodeValue = explode('-', $node['data']['globalID']);
							$nodeID = $nodeValue[1];
							
							$treePorts = $qls->App->buildTreePorts($nodeID, $objectCompatibility, false, false, true);
							$treeArray = array_merge($treeArray, $treePorts);
						}
					}
				}
			}
		}
	}

	header ('Content-Type: application/json');
	echo json_encode($treeArray);
}

?>
