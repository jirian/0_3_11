<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    App.class.php
* @start   October 10th, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.0.1
* @link    http://www.quadodo.net
*** *** *** *** *** ***
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*** *** *** *** *** ***
* Comments are always before the code they are commenting.
*** *** *** *** *** ***/
if (!defined('QUADODO_IN_SYSTEM')) {
    exit;
}

/**
 * Contains application functions which are globally usable
 */
class App {

/**
 * @var object $qls - Will contain everything else
 */
var $qls;

	/**
	 * Constructs the class
	 * @param object $qls - Reference to the rest of the program
	 * @return void
	 */
	function __construct(&$qls) {
	    $this->qls = &$qls;
		
		// Generate environment tree object
		$this->envTreeArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree', false, array('name', 'ASC'));
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->envTreeArray[$row['id']] = $row;
		}
		
		// Add full path names for each environment tree object
		foreach($this->envTreeArray as &$entry) {
			$parentID = $entry['parent'];
			$nameString = $entry['name'];
			while($parentID != '#') {
				$nameString = $this->envTreeArray[$parentID]['name'].'.'.$nameString;
				$parentID = $this->envTreeArray[$parentID]['parent'];
			}
			$entry['nameString'] = $nameString;
		}
		
		$this->insertArray = array();
		$query = $this->qls->SQL->select('*', 'app_object', array('parent_id' => array('<>', 0)));
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			if(!isset($this->insertArray[$row['parent_id']])) {
				$this->insertArray[$row['parent_id']] = array();
			}
			array_push($this->insertArray[$row['parent_id']], $row);
		}
		
		$this->templateArray = array();
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->templateArray[$row['id']] = $row;
		}
		
		// Generate object... object...
		$this->objectArray = array();
		$query = $this->qls->SQL->select('*', 'app_object');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->objectArray[$row['id']] = $row;
		}
		
		// Add full path names for each object... this is dependant on envTreeArray, templateArray, and objectArray
		foreach($this->objectArray as &$object) {
			$nameString = $this->generateObjectName($object['id']);
			$object['nameString'] = $nameString;
		}
		
		$this->categoryArray = array();
		$query = $this->qls->SQL->select('*', 'app_object_category');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->categoryArray[$row['id']] = $row;
		}
		
		$this->cablePathArray = array();
		$query = $this->qls->SQL->select('*', 'app_cable_path');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->cablePathArray[$row['id']] = $row;
		}
		
		$this->cabinetAdjacencyArray = array();
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->cabinetAdjacencyArray[$row['left_cabinet_id']] = $row;
			$this->cabinetAdjacencyArray[$row['right_cabinet_id']] = $row;
		}
		
		$this->compatibilityArray = array();
		$query = $this->qls->SQL->select('*', 'app_object_compatibility');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			$this->compatibilityArray[$row['template_id']][$row['side']][$row['depth']] = $row;
		}
		
		$this->connectorTypeArray = array();
		$this->connectorTypeValueArray = array();
		$query = $qls->SQL->select('*', 'shared_cable_connectorType');
		while ($row = $qls->SQL->fetch_assoc($query)) {
			if(strtolower($row['name']) != 'label') {
				$this->connectorTypeArray[$row['id']] = $row['name'];
				$this->connectorTypeValueArray[$row['value']] = $row;
			}
		}
		
		$this->mediaTypeArray = array();
		$query = $qls->SQL->select('*', 'shared_mediaType', array('display' => array('=', 1)));
		while($row = $qls->SQL->fetch_assoc($query)) {
			array_push($this->mediaTypeArray, $row);
		}
		
		$this->mediaTypeValueArray = array();
		$query = $qls->SQL->select('*', 'shared_mediaType');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$this->mediaTypeValueArray[$row['value']] = $row;
		}
		
		$this->mediaCategoryTypeArray = array();
		$query = $qls->SQL->select('*', 'shared_mediaCategoryType');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$this->mediaCategoryTypeArray[$row['value']] = $row;
		}
		
		$this->inventoryArray = array();
		$this->inventoryAllArray = array();
		$this->inventoryByIDArray = array();
		$query = $this->qls->SQL->select('*', 'app_inventory');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			array_push($this->inventoryAllArray, $row);
			if($row['a_object_id'] != 0) {
				$this->inventoryArray[$row['a_object_id']][$row['a_object_face']][$row['a_object_depth']][$row['a_port_id']] = array(
					'rowID' => $row['id'],
					'id' => $row['b_object_id'],
					'face' => $row['b_object_face'],
					'depth' => $row['b_object_depth'],
					'port' => $row['b_port_id'],
					'localEndID' => $row['a_id'],
					'localAttrPrefix' => 'a',
					'remoteEndID' => $row['b_id'],
					'remoteAttrPrefix' => 'b'
				);
			}
			if($row['b_object_id'] != 0) {
				$this->inventoryArray[$row['b_object_id']][$row['b_object_face']][$row['b_object_depth']][$row['b_port_id']] = array(
					'rowID' => $row['id'],
					'id' => $row['a_object_id'],
					'face' => $row['a_object_face'],
					'depth' => $row['a_object_depth'],
					'port' => $row['a_port_id'],
					'localEndID' => $row['b_id'],
					'localAttrPrefix' => 'b',
					'remoteEndID' => $row['a_id'],
					'remoteAttrPrefix' => 'a'
				);
			}
			if($row['a_id'] != 0) {
				$this->inventoryByIDArray[$row['a_id']] = array(
					'rowID' => $row['id'],
					'local_object_id' => $row['a_object_id'],
					'local_object_face' => $row['a_object_face'],
					'local_object_depth' => $row['a_object_depth'],
					'local_object_port' => $row['a_port_id'],
					'remote_object_id' => $row['b_object_id'],
					'remote_object_face' => $row['b_object_face'],
					'remote_object_depth' => $row['b_object_depth'],
					'remote_object_port' => $row['b_port_id'],
					'localEndID' => $row['a_id'],
					'localEndCode39' => $row['a_code39'],
					'localConnector' => $row['a_connector'],
					'localAttrPrefix' => 'a',
					'remoteEndID' => $row['b_id'],
					'remoteEndCode39' => $row['b_code39'],
					'remoteConnector' => $row['b_connector'],
					'remoteAttrPrefix' => 'b',
					'mediaType' => $row['mediaType'],
					'length' => $row['length'],
					'editable' => $row['editable']
				);
			}
			if($row['b_id'] != 0) {
				$this->inventoryByIDArray[$row['b_id']] = array(
					'rowID' => $row['id'],
					'local_object_id' => $row['b_object_id'],
					'local_object_face' => $row['b_object_face'],
					'local_object_depth' => $row['b_object_depth'],
					'local_object_port' => $row['b_port_id'],
					'remote_object_id' => $row['a_object_id'],
					'remote_object_face' => $row['a_object_face'],
					'remote_object_depth' => $row['a_object_depth'],
					'remote_object_port' => $row['a_port_id'],
					'localEndID' => $row['b_id'],
					'localEndCode39' => $row['b_code39'],
					'localConnector' => $row['b_connector'],
					'localAttrPrefix' => 'b',
					'remoteEndID' => $row['a_id'],
					'remoteEndCode39' => $row['a_code39'],
					'remoteConnector' => $row['a_connector'],
					'remoteAttrPrefix' => 'a',
					'mediaType' => $row['mediaType'],
					'length' => $row['length'],
					'editable' => $row['editable']
				);
			}
		}
		
		$this->populatedPortArray = array();
		$this->populatedPortAllArray = array();
		$query = $this->qls->SQL->select('*', 'app_populated_port');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			array_push($this->populatedPortAllArray, $row);
			$this->populatedPortArray[$row['object_id']][$row['object_face']][$row['object_depth']][$row['port_id']] = array(
				'rowID' => $row['id']
			);
		}
		
		$this->peerArrayStandard = array();
		$this->peerArray = array();
		$this->peerArrayStandardFloorplan = array();
		$this->peerArrayWalljack = array();
		$this->peerArrayWalljackEntry = array();
		$query = $this->qls->SQL->select('*', 'app_object_peer');
		while($row = $this->qls->SQL->fetch_assoc($query)) {
			if(!isset($this->peerArray[$row['a_id']][$row['a_face']][$row['a_depth']])) {
				$this->peerArray[$row['a_id']][$row['a_face']][$row['a_depth']] = array(
					'id' => $row['id'],
					'selfPort' => $row['a_port'],
					'selfEndpoint' => $row['a_endpoint'],
					'peerID' => $row['b_id'],
					'peerFace' => $row['b_face'],
					'peerDepth' => $row['b_depth'],
					'peerEndpoint' => $row['b_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
			}
			
			if($row['floorplan_peer']) {
				if(!isset($this->peerArray[$row['a_id']][$row['a_face']][$row['a_depth']]['peerArray'][$row['b_id']][$row['b_face']][$row['b_depth']])) {
					$this->peerArray[$row['a_id']][$row['a_face']][$row['a_depth']]['peerArray'][$row['b_id']][$row['b_face']][$row['b_depth']] = array();
				}
				array_push($this->peerArray[$row['a_id']][$row['a_face']][$row['a_depth']]['peerArray'][$row['b_id']][$row['b_face']][$row['b_depth']], array((int)$row['a_port'], (int)$row['b_port']));
			}
			
			if(!isset($this->peerArray[$row['b_id']][$row['b_face']][$row['b_depth']])) {
				$this->peerArray[$row['b_id']][$row['b_face']][$row['b_depth']] = array(
					'id' => $row['id'],
					'selfPort' => $row['b_port'],
					'selfEndpoint' => $row['b_endpoint'],
					'peerID' => $row['a_id'],
					'peerFace' => $row['a_face'],
					'peerDepth' => $row['a_depth'],
					'peerEndpoint' => $row['a_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
			}
			
			if($row['floorplan_peer']) {
				if(!isset($this->peerArray[$row['b_id']][$row['b_face']][$row['b_depth']]['peerArray'][$row['a_id']][$row['a_face']][$row['a_depth']])) {
					$this->peerArray[$row['b_id']][$row['b_face']][$row['b_depth']]['peerArray'][$row['a_id']][$row['a_face']][$row['a_depth']] = array();
				}
				array_push($this->peerArray[$row['b_id']][$row['b_face']][$row['b_depth']]['peerArray'][$row['a_id']][$row['a_face']][$row['a_depth']], array((int)$row['b_port'], (int)$row['a_port']));
			}
			
			if(!$row['floorplan_peer']) {
				$this->peerArrayStandard[$row['a_id']][$row['a_face']][$row['a_depth']] = array(
					'rowID' => $row['id'],
					'selfEndpoint' => $row['a_endpoint'],
					'id' => $row['b_id'],
					'face' => $row['b_face'],
					'depth' => $row['b_depth'],
					'port' => false,
					'endpoint' => $row['b_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
				$this->peerArrayStandard[$row['b_id']][$row['b_face']][$row['b_depth']] = array(
					'rowID' => $row['id'],
					'selfEndpoint' => $row['b_endpoint'],
					'id' => $row['a_id'],
					'face' => $row['a_face'],
					'depth' => $row['a_depth'],
					'port' => false,
					'endpoint' => $row['a_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
			} else {
				if(!isset($this->peerArrayWalljack[$row['a_id']])) {
					$this->peerArrayWalljack[$row['a_id']] = array();
				}
				array_push($this->peerArrayWalljack[$row['a_id']], array(
					'rowID' => $row['id'],
					'selfID' => $row['a_id'],
					'selfPortID' => $row['a_port'],
					'id' => $row['b_id'],
					'face' => $row['b_face'],
					'depth' => $row['b_depth'],
					'port' => $row['b_port'],
					'endpoint' => $row['b_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				));
				
				$this->peerArrayWalljackEntry[$row['a_id']][$row['a_face']][$row['a_depth']][$row['a_port']] = array(
					'rowID' => $row['id'],
					'id' => $row['b_id'],
					'face' => $row['b_face'],
					'depth' => $row['b_depth'],
					'port' => $row['b_port'],
					'endpoint' => $row['b_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
				
				$this->peerArrayStandardFloorplan[$row['b_id']][$row['b_face']][$row['b_depth']][$row['b_port']] = array(
					'rowID' => $row['id'],
					'id' => $row['a_id'],
					'face' => $row['a_face'],
					'depth' => $row['a_depth'],
					'port' => $row['a_port'],
					'endpoint' => $row['a_endpoint'],
					'floorplanPeer' => $row['floorplan_peer']
				);
			}
		}
		
		
		// History Action Type
		$this->historyActionTypeArray = array();
		$query = $qls->SQL->select('*', 'shared_history_action_type');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$this->historyActionTypeArray[$row['value']] = $row;
		}
		
		// History Function
		$this->historyFunctionArray = array();
		$query = $qls->SQL->select('*', 'shared_history_function');
		while($row = $qls->SQL->fetch_assoc($query)) {
			$this->historyFunctionArray[$row['value']] = $row;
		}
	}
	
	function generateObjectPortName($objID, $objFace, $objDepth, $objPort) {
		$obj = $this->objectArray[$objID];
		$objName = $obj['nameString'];
		$objTemplateID = $obj['template_id'];
		$objCompatibility = $this->compatibilityArray[$objTemplateID][$objFace][$objDepth];
		if($objCompatibility['templateType'] == 'walljack') {
			if(isset($this->peerArray[$objID][$objFace][$objDepth]['peerArray'])) {
				$peerData = $this->peerArray[$objID][$objFace][$objDepth]['peerArray'];
				foreach($peerData as $peerID => $peer) {
					$peerObj = $this->objectArray[$peerID];
					$peerTemplateID = $peerObj['template_id'];
					foreach($peer as $peerFace => $partition) {
						foreach($partition as $peerDepth => $portPair) {
							foreach($portPair as $port) {
								if($port[0] == $objPort) {
									$peerCompatibility = $this->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
									$peerPortNameFormat = json_decode($peerCompatibility['portNameFormat'], true);
									$peerPortTotal = $peerCompatibility['portTotal'];
									$peerPortID = $port[1];
									$peerPortName = $this->generatePortName($peerPortNameFormat, $peerPortID, $peerPortTotal);
									$objPortNameArray = array($objName, $peerPortName);
									$objectPortName = implode('.', $objPortNameArray);
									$objectPortName = $objectPortName.'('.$objPort.')';
								}
							}
						}
					}
				}
			}
		} else {
			$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
			$portTotal = $objCompatibility['portTotal'];
			$objPortName = $this->generatePortName($portNameFormat, $objPort, $portTotal);
			$objPortNameArray = array($objName, $objPortName);
			$objectPortName = implode('.', $objPortNameArray);
		}
		
		return $objectPortName;
	}

	function generateObjectName($objID) {
		$object = $this->objectArray[$objID];
		$envTreeID = $object['env_tree_id'];
		$objectTemplateID = $object['template_id'];
		$template = $this->templateArray[$objectTemplateID];
		$templateType = $template['templateType'];
		$locationArray = array();
		
		// Save object name separately if it's an insert
		if($templateType == 'Insert') {
			array_unshift($locationArray, $object['name']);
			$parentID = $object['parent_id'];
			$object = $this->objectArray[$parentID];
		}
		array_unshift($locationArray, $object['name']);
		
		//Locations
		$rootTreeNode = false;
		while(!$rootTreeNode) {
			$node = $this->envTreeArray[$envTreeID];
			array_unshift($locationArray, $node['name']);
			$envTreeID = $node['parent'];
			$rootTreeNode = $envTreeID == '#' ? true : false;
		}
		
		return implode('.', $locationArray);
	}
	
	/**
	 * Generates human readable port name
	 * @return string
	 */
	function generatePortName($portNameFormat, $index, $portTotal) {
		
		$portString = '';
		$incrementalCount = 0;
		$lowercaseIncrementArray = array();
		$uppercaseIncrementArray = array();
		for($x=97; $x<=122; $x++) {
			array_push($lowercaseIncrementArray, chr($x));
		}
		for($x=65; $x<=90; $x++) {
			array_push($uppercaseIncrementArray, chr($x));
		}
		
		foreach($portNameFormat as &$itemA) {
			$type = $itemA['type'];
			
			if($type == 'incremental' or $type == 'series') {
				$incrementalCount++;
				if($itemA['count'] == 0) {
					$itemA['count'] = $portTotal;
				}
			}
		}
		
		foreach($portNameFormat as $itemB) {
			$type = $itemB['type'];
			$value = $itemB['value'];
			$order = $itemB['order'];
			$count = $itemB['count'];
			$numerator = 0;
			
			if($type == 'static') {
				$portString = $portString.$value;
			} else if($type == 'incremental' or $type == 'series') {
				
				if($order == $incrementalCount) {
					$numerator = 1;
				} else if($order < $incrementalCount) {
					foreach($portNameFormat as $itemC) {
						$typeC = $itemC['type'];
						$orderC = $itemC['order'];
						$countC = $itemC['count'];
						
						if($typeC == 'incremental' or $typeC == 'series') {
							if($order < $orderC) {
								$numerator += $countC;
							}
						}
					}
				}
				
				$howMuchToIncrement = floor($index / $numerator);
				if($howMuchToIncrement >= $count) {
					$rollOver = floor($howMuchToIncrement / $count);
					$howMuchToIncrement = $howMuchToIncrement - ($rollOver * $count);
				}
				
				if($type == 'incremental') {
					if(is_numeric($value)) {
						$value = $value + $howMuchToIncrement;
						$portString = $portString.$value;
					} else {
						$asciiValue = ord($value);
						$asciiIndex = $asciiValue + $howMuchToIncrement;
						if($asciiValue >= 65 && $asciiValue <= 90) {
							// Uppercase
							
							while($asciiIndex > 90) {
								$portString = $portString.$uppercaseIncrementArray[0];
								$asciiIndex -= 26;
							}
							$portString = $portString.$uppercaseIncrementArray[$asciiIndex-65];
						} else if($asciiValue >= 97 && $asciiValue <= 122) {
							// Lowercase
							while($asciiIndex > 122) {
								$portString = $portString.$lowercaseIncrementArray[0];
								$asciiIndex -= 26;
							}
							$portString = $portString.$lowercaseIncrementArray[$asciiIndex-97];
						}
					}
					
				} else if($type == 'series') {
					$portString = $portString.$value[$howMuchToIncrement];
				}
			}
		}
			
		return $portString;
	}

	/**
	 * Generates unique object name
	 * @return string
	 */
	function findUniqueName($parentID, $nameType, $name=false){
		for($count=0; $count<10; $count++) {
			$uniqueNameValue = $this->generateUniqueNameValue();
			
			// Search for duplicate name
			if($nameType == 'object') {
				$uniqueName = NEW_OBJECT_PREFIX.$uniqueNameValue;
				$query = $this->qls->SQL->select('*', 'app_object', array('env_tree_id' => array('=', $parentID), 'AND', 'name' => array('=', $uniqueName)));
			} else if($nameType == 'template') {
				$uniqueName = $name.'_'.$uniqueNameValue;
				$query = $this->qls->SQL->select('*', 'app_object_templates', array('templateName' => array('=', $uniqueName)));
			} else {
				if($nameType == 'location') {
					$uniqueName = NEW_LOCATION_PREFIX.$uniqueNameValue;
				} else if($nameType == 'pod') {
					$uniqueName = NEW_POD_PREFIX.$uniqueNameValue;
				} else if($nameType == 'cabinet') {
					$uniqueName = NEW_CABINET_PREFIX.$uniqueNameValue;
				} else {
					return false;
				}
				$query = $this->qls->SQL->select('*', 'app_env_tree', array('parent' => array('=', $parentID), 'AND', 'name' => array('=', $uniqueName)));
			}
			if(!$this->qls->SQL->num_rows($query)) {
				return $uniqueName;
			}
		}
		return false;
	}
	
	/**
	 * Generates unique name value
	 * @return string
	 */
	function generateUniqueNameValue(){
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$length = 4;
		$charactersLength = strlen($characters);
		$uniqueNameValue = '';
		for($i = 0; $i < $length; $i++) {
			$uniqueNameValue .= $characters[rand(0, $charactersLength - 1)];
		}
		return $uniqueNameValue;
	}
	
	/**
	 * Builds information about an object
	 * @return array
	 */
	function getObject($objID, $portID=0, $objFace=0, $objDepth=0, $incPortName=true){
		$return = array(
			'obj' => array(),
			'function' => '',
			'id' => $objID,
			'selected' => false,
			'nameString' => ''
		);
		
		//Build the object
		if(isset($this->objectArray[$objID])) {
			$obj = $this->objectArray[$objID];
		} else {
			$return['id'] = 0;
			return $return;
		}
		
		$templateID = $obj['template_id'];
		$categoryID = $this->templateArray[$templateID]['templateCategory_id'];
		$return['categoryID'] = $categoryID;

		// Retrieve port info
		$objCompatibility = $this->compatibilityArray[$templateID][$objFace][$objDepth];
		$templateType = $objCompatibility['templateType'];
		
		if($templateType == 'walljack') {
			$peerEntry = $this->peerArrayWalljackEntry[$objID][$objFace][$objDepth][$portID];
			$portID = $peerEntry['port'];
			$peerObj = $this->objectArray[$peerEntry['id']];
			$peerTemplateID = $peerObj['template_id'];
			$objCompatibility = $this->compatibilityArray[$peerTemplateID][$peerEntry['face']][$peerEntry['depth']];
		} else if($templateType == 'wap' or $templateType == 'device'){
			$portName = 'NIC1';
		}
		
		$return['function'] = $objCompatibility['partitionFunction'];
		$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
		$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
		$portName = $this->generatePortName($portNameFormat, $portID, $portTotal);
		$templateType = $objCompatibility['templateType'];
		
		if($templateType == 'Insert') {
			$separator = $return['function'] == 'Passive' ? '.' : ''; 
			$portName = $obj['name'] == '' ? $portName : $obj['name'].$separator.$portName;
			
			$query = $this->qls->SQL->select('*', 'app_object', array('id' => array('=', $obj['parent_id'])));
			$obj = $this->qls->SQL->fetch_assoc($query);
		}
		
		if($incPortName) {
			array_unshift($return['obj'], $portName);
		}
		
		$side = '';
		if($this->templateArrray[$obj['template_id']]['templateMountConfig'] == 1){
			$side = $objFace == 0 ? '(front)' : '(back)';
		}
		//array_unshift($return['obj'], $obj['name'].$side);
		array_unshift($return['obj'], $obj['name']);
		
		$objParentID = $obj['env_tree_id'];
		
		while($objParentID != '#'){
			$obj = $this->envTreeArray[$objParentID];
			array_unshift($return['obj'], $obj['name']);
			$objParentID = $obj['parent'];
		}
		
		foreach($return['obj'] as $index => $element) {
			if($index < (count($return['obj'])-1)) {
				$return['nameString'] .= $element.'.';
			} else {
				$return['nameString'] .= $element;
			}
		}
		
		return $return;
	}
	
	function getPortNameString($objID, $objFace, $objDepth, $portID){
		
		$nameArray = array();
		$objectArray = $this->objectArray;
		$compatibilityArray = $this->compatibilityArray;
		$templateArray = $this->templateArray;
		$envTreeArray = $this->envTreeArray;
		$peerArrayWalljackEntry = $this->peerArrayWalljackEntry;
		
		$obj = $objectArray[$objID];
		$templateID = $obj['template_id'];

		// Retrieve port info
		$objCompatibility = $compatibilityArray[$templateID][$objFace][$objDepth];
		$templateType = $objCompatibility['templateType'];
		
		if($templateType == 'walljack') {
			$peerEntry = $peerArrayWalljackEntry[$objID][$objFace][$objDepth][$portID];
			$portID = $peerEntry['port'];
			$peerObj = $objectArray[$peerEntry['id']];
			$peerTemplateID = $peerObj['template_id'];
			$objCompatibility = $compatibilityArray[$peerTemplateID][$peerEntry['face']][$peerEntry['depth']];
		} else if($templateType == 'wap' or $templateType == 'device'){
			$portName = 'NIC1';
		}
		
		$return['function'] = $objCompatibility['partitionFunction'];
		$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
		
		$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
		$portName = $this->generatePortName($portNameFormat, $portID, $portTotal);
		$templateType = $objCompatibility['templateType'];
		
		if($templateType == 'Insert') {
			$separator = $return['function'] == 'Passive' ? '.' : ''; 
			$portName = $obj['name'] == '' ? $portName : $obj['name'].$separator.$portName;
			
			$obj = $objectArray[$obj['parent_id']];
		}
		
		array_unshift($nameArray, $portName);
		
		$side = '';
		if($templateArray[$obj['template_id']]['templateMountConfig'] == 1){
			$side = $objFace == 0 ? '(front)' : '(back)';
		}
		
		array_unshift($nameArray, $obj['name']);
		
		$objParentID = $obj['env_tree_id'];
		
		while($objParentID != '#'){
			$obj = $envTreeArray[$objParentID];
			array_unshift($nameArray, $obj['name']);
			$objParentID = $obj['parent'];
		}
		
		foreach($nameArray as $index => $element) {
			if($index < (count($nameArray)-1)) {
				$portNameString .= $element.'.';
			} else {
				$portNameString .= $element;
			}
		}
		
		return $portNameString;
	}
	
	/**
	 * Find object peer
	 * @return array
	 */
	function findPeer($objID, $objFace, $objDepth, $objPort){
		$obj = $this->objectArray[$objID];
		$objTemplate = $this->templateArray[$obj['template_id']];
		
		// Find peer or return if nothing found
		if($objTemplate['templateType'] == 'walljack' or $objTemplate['templateType'] == 'wap') {
			return $this->peerArrayWalljackEntry[$objID][$objFace][$objDepth][$objPort];
		} else {
			if(isset($this->peerArrayStandard[$objID][$objFace][$objDepth])) {
				$peer = $this->peerArrayStandard[$objID][$objFace][$objDepth];
			} else if(isset($this->peerArrayStandardFloorplan[$objID][$objFace][$objDepth][$objPort])) {
				$peer = $this->peerArrayStandardFloorplan[$objID][$objFace][$objDepth][$objPort];
			}
			return $peer;
		}
	}

	function getCable($objID, $portID, $objFace, $objDepth){
		//Build the cable
		$cbl = $this->qls->SQL->select('*', 'app_inventory', '(a_object_id = '.$objID.' AND a_port_id = '.$portID.' AND a_object_face = '.$objFace.' AND a_object_depth = '.$objDepth.') OR (b_object_id = '.$objID.' AND b_port_id = '.$portID.' AND b_object_face = '.$objFace.' AND b_object_depth = '.$objDepth.')');
		
		if($this->qls->SQL->num_rows($cbl)>0){
			$cbl = $this->qls->SQL->fetch_assoc($cbl);
			if($cbl['a_object_id'] == $objID and $cbl['a_port_id'] == $portID) {
				$cbl['nearEnd'] = 'a';
				$cbl['farEnd'] = 'b';
			} else {
				$cbl['nearEnd'] = 'b';
				$cbl['farEnd'] = 'a';
			}
		} else {
			return 0;
		}
		
		return $cbl;
	}
	
	function retrievePorts($objID, $objFace, $objDepth, $objPort) {
		$obj = $this->objectArray[$objID];
		$templateID = $obj['template_id'];
		$template = $this->templateArray[$templateID];
		$objType = $template['templateType'];
		$portOptions = '';
		
		if($objType == 'walljack') {
			foreach($this->peerArrayWalljack[$objID] as $peerEntry) {
				$peerID = $peerEntry['id'];
				$peerFace = $peerEntry['face'];
				$peerDepth = $peerEntry['depth'];
				$peerPortID = $peerEntry['port'];
				$selfPortID = $peerEntry['selfPortID'];
				$walljackPortID = $peerEntry['selfPortID'];
				$peerObj = $this->objectArray[$peerID];
				$objName = $peerObj['name'];
				$peerTemplateID = $peerObj['template_id'];
				$peerTemplate = $this->templateArray[$peerTemplateID];
				$objType = $peerTemplate['templateType'];
				$objCompatibility = $this->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
				$partitionFunction = $objCompatibility['partitionFunction'];
				$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
				$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
				
				$portFlags = $this->getPortFlags($objID, $objFace, $objDepth, $selfPortID);
				$selected = $walljackPortID == $objPort ? ' selected' : '';
				$portName = $this->generatePortName($portNameFormat, $peerPortID, $portTotal);
				$portString = ($objType == 'Insert' and $partitionFunction == 'Endpoint') ? $objName.$portName : $portName;
				$portOptions .= '<option value="'.$walljackPortID.'"'.$selected.'>'.$portString.$portFlags.'</option>';
			}
		} else {
			$objName = $obj['name'];
			$objCompatibility = $this->compatibilityArray[$templateID][$objFace][$objDepth];
			$partitionFunction = $objCompatibility['partitionFunction'];
			$portNameFormat = json_decode($objCompatibility['portNameFormat'], true);
			$portTotal = $objCompatibility['portLayoutX']*$objCompatibility['portLayoutY'];
			
			for($x=0; $x<$portTotal; $x++) {
				$portFlags = $this->getPortFlags($objID, $objFace, $objDepth, $x);
				$selected = $x == $objPort ? ' selected' : '';
				$portName = $this->generatePortName($portNameFormat, $x, $portTotal);
				$portString = ($objType == 'Insert' and $partitionFunction == 'Endpoint') ? $objName.$portName : $portName;
				$portOptions .= '<option value="'.$x.'"'.$selected.'>'.$portString.$portFlags.'</option>';
			}
		}
		
		return $portOptions;
	}

	function buildTreePathString($nodeID){
		$node = $this->envTreeArray[$nodeID];
		$nodeName = $node['name'];
		$nodeParentID = $node['parent'];
		$treePathArray = array($nodeName);
		
		while($nodeParentID != '#') {
			$node = $this->envTreeArray[$nodeParentID];
			$nodeName = $node['name'];
			$nodeParentID = $node['parent'];
			array_unshift($treePathArray, $nodeName);
		}
		
		$treePath = implode('.', $treePathArray);
		return $treePath;
	}
	
	function buildTreeLocation(){
		$treeArray = array();
		
		foreach($this->envTreeArray as $envNode) {
			
			if($envNode['type'] == 'location' || $envNode['type'] == 'pod') {
				$elementType = 0;
			} else if($envNode['type'] == 'cabinet' || $envNode['type'] == 'floorplan') {
				$elementType = 1;
			}
			
			$value = array($elementType, $envNode['id'], 0, 0, 0);
			$value = implode('-', $value);
			
			array_push($treeArray, array(
				'id' => $envNode['id'],
				'text' => $envNode['name'],
				'parent' => $envNode['parent'],
				'type' => $envNode['type'],
				'data' => array('globalID' => $value)
			));
		}
		
		return $treeArray;
	}
	
	function buildTreeObjects($cabinetID){
		$treeArray = array();
		
		foreach($this->objectArray as $objectNode) {
			if($objectNode['env_tree_id'] == $cabinetID and $objectNode['parent_id'] == 0) {
				$objectID = $objectNode['id'];
				$objectName = $objectNode['name'];
				$objectTemplateID = $objectNode['template_id'];
				$objectTemplate = $this->templateArray[$objectTemplateID];
				$objectType = $objectTemplate['templateType'];
				
				$value = array(2, $objectID, 0, 0, 0);
				$value = implode('-', $value);
				
				array_push($treeArray, array(
					'id' => 'O'.$objectNode['id'],
					'text' => $objectNode['name'],
					'parent' => $cabinetID,
					'type' => 'object',
					'objectType' => $objectType,
					'data' => array('globalID' => $value, 'objectID' => $objectID)
				));
			}
		}
		
		return $treeArray;
	}
	
	function buildTreePorts($nodeID, $objectPortType, $objectPartitionFunction, $cablePortType, $cableMediaType, $forTrunk=false){
		$treeArray = array();
		$element = $this->objectArray[$nodeID];
		
		if(!$forTrunk) {
			$whereArray = array('template_id' => array('=', $element['template_id']));
		} else {
			$whereArray = array('template_id' => array('=', $element['template_id']), 'AND', 'partitionFunction' => array('<>', 'Endpoint'));
		}
		
		// Retrieve selected object partitions
		$query = $this->qls->SQL->select('*',
			'app_object_compatibility',
			$whereArray
		);
		
		$elementArray = array();
		while($row = $this->qls->SQL->fetch_assoc($query)){
			
			if($row['partitionType'] == 'Enclosure') {
				$queryInsertObject = $this->qls->SQL->select(
					'*',
					'app_object',
					array(
						'parent_id' => array(
							'=',
							$nodeID
						),
						'AND',
						'parent_face' => array(
							'=',
							$row['side']
						),
						'AND',
						'parent_depth' => array(
							'=',
							$row['depth']
						)
					)
				);
				
				while($rowInsertObject = $this->qls->SQL->fetch_assoc($queryInsertObject)) {
					$queryInsertPartition = $this->qls->SQL->select('*', 'app_object_compatibility', array('template_id' => array('=', $rowInsertObject['template_id'])));
					while($rowInsertPartition = $this->qls->SQL->fetch_assoc($queryInsertPartition)) {
						// Cannot be a trunked endpoint
						if(!$this->peerArrayStandard[$rowInsertObject['id']][0][$rowInsertPartition['depth']]['selfEndpoint']) {
							$separator = $rowInsertPartition['partitionFunction'] == 'Endpoint' ? '' : '.';
							$rowInsertPartition['objectID'] = $rowInsertObject['id'];
							$rowInsertPartition['portNamePrefix'] = $rowInsertObject['name'] == '' ? '' : $rowInsertObject['name'].$separator;
							array_push($elementArray, $rowInsertPartition);
						}
					}
				}
			} else if($row['templateType'] == 'Insert') {
				// Cannot be a trunked endpoint
				if(!$this->peerArrayStandard[$nodeID][$row['side']][$row['depth']]['selfEndpoint']) {
					$separator = $row['partitionFunction'] == 'Endpoint' ? '' : '.';
					$rowPartitionElement = $row;
					$rowPartitionElement['objectID'] = $nodeID;
					$rowPartitionElement['portNamePrefix'] = $element['name'] == '' ? '' : $element['name'].$separator;
					array_push($elementArray, $rowPartitionElement);
				}
			} else {
				// Cannot be a trunked endpoint
				if(!$this->peerArrayStandard[$nodeID][$row['side']][$row['depth']]['selfEndpoint']) {
					$rowPartitionElement = $row;
					$rowPartitionElement['objectID'] = $nodeID;
					$rowPartitionElement['portNamePrefix'] = '';
					array_push($elementArray, $rowPartitionElement);
				}
			}
		}
		
		foreach($elementArray as $elementItem) {
			$elementPortType = $elementItem['portType'];
			$elementMediaCategory = $mediaTypeArray[$elementItem['mediaType']]['category_id'];
			$elementPartitionFunction = $elementItem['partitionFunction'];
			
			if($cablePortType) {
				$mediaTypeArray = array();
				$query = $this->qls->SQL->select('*', 'shared_mediaType');
				while($row = $this->qls->SQL->fetch_assoc($query)) {
					$mediaTypeArray[$row['value']] = $row;
				}
				
				$cableMediaCategory = $mediaTypeArray[$cableMediaType]['category_id'];
				
				$isCompatible = ($elementPortType == $cablePortType or $elementPortType == 4) and ($elementMediaCategory == $cableMediaCategory or $elementPartitionFunction == 'Endpoint') ? true : false;
			} else if($objectPortType) {
				
				$isCompatible = ($elementPortType == $objectPortType or $elementPortType == 4 or $objectPortType == 4) and ($elementMediaCategory == $objectMediaCategory or $elementPartitionFunction == 'Endpoint' or $objectPartitionFunction == 'Endpoint') ? true : false;
			}
			
			if($forTrunk and isset($this->peerArrayStandard[$nodeID][$elementItem['side']][$elementItem['depth']])) {
				$isCompatible = false;
			}
			
			if($isCompatible) {
				if($elementItem['templateType'] == 'walljack') {
					if(isset($this->peerArrayWalljack[$nodeID])) {
						foreach($this->peerArrayWalljack[$nodeID] as $peerEntry) {
							$peerID = $peerEntry['id'];
							$peerFace = $peerEntry['face'];
							$peerDepth = $peerEntry['depth'];
							$peerPort = $peerEntry['port'];
							$selfPortID = $peerEntry['selfPortID'];
							$peerObject = $this->objectArray[$peerID];
							$peerTemplateID = $peerObject['template_id'];
							$peerCompatibility = $this->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
							$portTotal = $peerCompatibility['portLayoutX'] * $peerCompatibility['portLayoutY'];
							$portNameFormat = json_decode($peerCompatibility['portNameFormat'], true);
							
							$flagString = $this->getPortFlags($nodeID, 0, 0, $selfPortID);
							$portName = $this->generatePortName($portNameFormat, $peerPort, $portTotal);
							$portName = $portName.$flagString;
							
							$value = array(
								4,
								$nodeID,
								0,
								0,
								$selfPortID
							);
							$value = implode('-', $value);
							
							array_push($treeArray, array(
								'id' => $value,
								'text' => $portName,
								'parent' => 'O'.$nodeID,
								'type' => 'port',
								'data' => array('globalID' => $value)
							));
						}
					}
				} else {
					$portNameFormat = json_decode($elementItem['portNameFormat'], true);
					$portTotal = $elementItem['portLayoutX']*$elementItem['portLayoutY'];
					
					for($x=0; $x<$portTotal; $x++) {
						$flagString = $this->getPortFlags($elementItem['objectID'], $elementItem['side'], $elementItem['depth'], $x);
						$portName = $this->generatePortName($portNameFormat, $x, $portTotal);
						$portName = $elementItem['portNamePrefix'].$portName.$flagString;
						
						$value = array(
							4,
							$elementItem['objectID'],
							$elementItem['side'],
							$elementItem['depth'],
							$x
						);
						$value = implode('-', $value);
						
						array_push($treeArray, array(
							'id' => $value,
							'text' => $portName,
							'parent' => 'O'.$nodeID,
							'type' => 'port',
							'data' => array('globalID' => $value)
						));
					}
				}
				
			}
		}
		
		return $treeArray;
	}
	
	function getPortFlags($objID, $objFace, $objDepth, $objPort) {
		$object = $this->objectArray[$objID];
		$objectTemplateID = $object['template_id'];
		$template = $this->templateArray[$objectTemplateID];
		$objectFunction = $template['templateFunction'];
		$flagsArray = array();
		$flagString = '';
		if(isset($this->inventoryArray[$objID][$objFace][$objDepth][$objPort])) {
			array_push($flagsArray, 'C');
		}
		//if($objectFunction == 'Endpoint') {
			if(isset($this->peerArrayStandard[$objID][$objFace][$objDepth]) or isset($this->peerArrayStandardFloorplan[$objID][$objFace][$objDepth][$objPort]) or isset($this->peerArrayWalljackEntry[$objID][$objFace][$objDepth][$objPort])) {
				array_push($flagsArray, 'T');
			}
		//}
		if(isset($this->populatedPortArray[$objID][$objFace][$objDepth][$objPort])) {
			array_push($flagsArray, 'P');
		}
		if(count($flagsArray)) {
			$flagString .= ' [';
			foreach($flagsArray as $index => $flag) {
				if(($index+1) == count($flagsArray)) {
					$flagString .= $flag;
				} else {
					$flagString .= $flag.',';
				}
			}
			$flagString .= ']';
		}
		return $flagString;
	}
	
	function buildConnectorFlatPath($cable, $connectorEnd){
		$returnArray = array();
		
		if($cable[$connectorEnd.'_object_id']) {
			// Input variables
			$objectID = $cable[$connectorEnd.'_object_id'];
			$objectFace = $cable[$connectorEnd.'_object_face'];
			$objectDepth = $cable[$connectorEnd.'_object_depth'];
			$objectPortID = $cable[$connectorEnd.'_object_port'];
			
			// Object variables
			$object = $this->objectArray[$objectID];
			$objectName = $object['name'];
			
			// Partition variables
			$partitionCompatibility = $this->compatibilityArray[$object['template_id']][$objectFace][$objectDepth];
			$templateType = $partitionCompatibility['templateType'];
			if($templateType == 'walljack') {
				$peerEntry = $this->peerArrayWalljackEntry[$objectID][$objectFace][$objectDepth][$objectPortID];
				$peerID = $peerEntry['id'];
				$peerFace = $peerEntry['face'];
				$peerDepth = $peerEntry['depth'];
				$objectPortID = $peerEntry['port'];
				$peer = $this->objectArray[$peerID];
				$peerTemplateID = $peer['template_id'];
				$partitionCompatibility = $this->compatibilityArray[$peerTemplateID][$peerFace][$peerDepth];
			}
			$partitionFunction = $partitionCompatibility['partitionFunction'];
			$portLayoutX = $partitionCompatibility['portLayoutX'];
			$portLayoutY = $partitionCompatibility['portLayoutY'];
			$portTotal = $portLayoutX * $portLayoutY;
			$portNameFormat = json_decode($partitionCompatibility['portNameFormat'],true);
			$portName = $this->generatePortName($portNameFormat, $objectPortID, $portTotal);
			
			// Port
			if($templateType == 'Insert') {
				if($partitionFunction == 'Endpoint') {
					$portString = $objectName.$portNumber;
				} else {
					$portString = '.&#8203;'.$objectName.'.&#8203;'.$portName;
				}
			} else {
				$portString = '.&#8203;'.$portName;
			}
			
			// Object
			if($templateType == 'Insert') {
				$parentID = $object['parent_id'];
				$object = $this->objectArray[$parentID];
			}
			$objectString = $object['name'];
			
			//Locations
			$locationString = '';
			$envNodeID = $object['env_tree_id'];
			$rootEnvNode = false;
			while(!$rootEnvNode) {
				$envNode = $this->envTreeArray[$envNodeID];
				$envNodeID = $envNode['parent'];
				$rootEnvNode = $envNodeID == '#' or !isset($this->envTreeArray[$envNodeID]) ? true : false;
				$locationString = $envNode['name'].'.&#8203;'.$locationString;
			}
			
			$flatPath = $locationString.$objectString.$portString;
		} else {
			$flatPath = 'None';
		}
		
		return $flatPath;
	}
	
	function getCableUnitOfLength($mediaTypeID){
		$mediaCategoryTypeID = $this->mediaTypeValueArray[$mediaTypeID]['category_type_id'];
		return $this->mediaCategoryTypeArray[$mediaCategoryTypeID]['unit_of_length'];
	}
	
	function clearInventoryTable($objID, $objFace, $objDepth, $objPort){
		if($inventoryEntry = $this->inventoryArray[$objID][$objFace][$objDepth][$objPort]) {
			$rowID = $inventoryEntry['rowID'];
			if($inventoryEntry['localEndID'] === 0 and $inventoryEntry['remoteEndID'] === 0) {
				// If this is an unmanaged connection, delete the entry
				$this->qls->SQL->delete('app_inventory', array('id' => array('=', $rowID)));
			} else {
				// If this is a managed connection, just clear the data
				$attrPrefix = $inventoryEntry['localAttrPrefix'];
				$set = array(
					$attrPrefix.'_object_id' => 0,
					$attrPrefix.'_object_face' => 0,
					$attrPrefix.'_object_depth' => 0,
					$attrPrefix.'_port_id' => 0
				);
				$this->qls->SQL->update('app_inventory', $set, array('id' => array('=', $rowID)));
				if(isset($this->inventoryArray[$inventoryEntry['id']][$inventoryEntry['face']][$inventoryEntry['depth']][$inventoryEntry['port']])) {
					$this->inventoryArray[$inventoryEntry['id']][$inventoryEntry['face']][$inventoryEntry['depth']][$inventoryEntry['port']]['id'] = 0;
					$this->inventoryArray[$inventoryEntry['id']][$inventoryEntry['face']][$inventoryEntry['depth']][$inventoryEntry['port']]['face'] = 0;
					$this->inventoryArray[$inventoryEntry['id']][$inventoryEntry['face']][$inventoryEntry['depth']][$inventoryEntry['port']]['depth'] = 0;
					$this->inventoryArray[$inventoryEntry['id']][$inventoryEntry['face']][$inventoryEntry['depth']][$inventoryEntry['port']]['port'] = 0;
				}
			}
			unset($this->inventoryArray[$objID][$objFace][$objDepth][$objPort]);
		}
	}
	
	function clearPopulatedTable($objID, $objFace, $objDepth, $objPort){
		if($populatedPortEntry = $this->populatedPortArray[$objID][$objFace][$objDepth][$objPort]) {
			$rowID = $populatedPortEntry['rowID'];
			$this->qls->SQL->delete('app_populated_port', array('id' => array('=', $rowID)));
			unset($this->populatedPortArray[$objID][$objFace][$objDepth][$objPort]);
		}
	}
	
	// Not sure if this is useful :/
	function clearPeerTable($rowID){
		//$qls->SQL->delete('app_object_peer', array('id' => array('=', $rowID)));
		//unset($qls->App->peerArrayWalljack[$entry['selfID']]);
	}

	function calculateCableLength($mediaType, $length, $includeUnit=true) {

		// Collect details about the cable to help us calculate length
		$mediaCategoryTypeID = $this->mediaTypeValueArray[$mediaType]['category_type_id'];
		$mediaCategoryType = $this->mediaCategoryTypeArray[$mediaCategoryTypeID];
		$mediaCategoryTypeName = strtolower($mediaCategoryType['name']);
		
		if($length == 0) {
			$lengthString = 'unknown';
			$includeUnit = false;
		} else if($mediaCategoryTypeName == 'copper') {
			// Convert to feet
			$lengthString = $this->convertToHighestHalfFeet($length);
		} else if($mediaCategoryTypeName == 'fiber') {
			// Convert to meters
			$lengthString = $this->convertToHighestHalfMeter($length);
		} else {
			$lengthString = $length;
		}
		
		if($includeUnit) {
			$lengthString = $lengthString.' '.$mediaCategoryType['unit_of_length'];
		}
		
		return $lengthString;
	}
	
	function convertToHighestHalfMeter($millimeter){
		$meters = $millimeter * 0.001;
		return round($meters * 2) / 2;
	}

	function convertToHighestHalfFeet($millimeter){
		$feet = $millimeter * 0.00328084;
		return round($feet * 2) / 2;
	}

	function convertFeetToMillimeters($cableLength){
		$meters = $cableLength * 0.3048;
		$millimeters = $meters * 1000;

		return $millimeters;
	}

	function convertMetersToMillimeters($cableLength){
		$millimeters = $cableLength * 1000;

		return $millimeters;
	}
	
	function logAction($function, $actionType, $actionString){
		$columns = array('date', 'function', 'action_type', 'user_id', 'action');
		$values = array(time(), $function, $actionType, $this->qls->user_info['id'], $actionString);
		$this->qls->SQL->insert('app_history', $columns, $values);
	}
	
	function buildPathFull($path){
		$htmlPathFull = '';
		$htmlPathFull .= '<table>';
		foreach($path as $objectIndex => $object) {
			
			// First path object
			if($objectIndex == 0) {
				if($object[1][0] != '') {
					$htmlPathFull .= '<tr>';
					$htmlPathFull .= $this->buildObject($object[0]);
					$htmlPathFull .= $this->buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
					$htmlPathFull .= '</tr>';
					$htmlPathFull .= '<tr>';
					$htmlPathFull .= $this->buildObject($object[2]);
					$htmlPathFull .= '</tr>';
				} else {
					$firstObject = count($path) == 1 ? $object[0] : $object[2];
					$htmlPathFull .= '<tr>';
					$htmlPathFull .= $this->buildObject($firstObject);
					$htmlPathFull .= '</tr>';
				}
			// Last path object
			} else if($objectIndex == count($path)-1) {
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= $this->buildObject($object[0]);
				if($object[1][0] != '') {
					$htmlPathFull .= $this->buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
					$htmlPathFull .= '</tr>';
					$htmlPathFull .= '<tr>';
					$htmlPathFull .= $this->buildObject($object[2]);
					$htmlPathFull .= '</tr>';
				} else {
					$htmlPathFull .= '</tr>';
				}
			// Neither first nor last path object
			} else {
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= $this->buildObject($object[0]);
				$htmlPathFull .= $this->buildCable($object[1][0], $object[1][1], $connectorCode39, $object[1][2]);
				$htmlPathFull .= '</tr>';
				$htmlPathFull .= '<tr>';
				$htmlPathFull .= $this->buildObject($object[2]);
				$htmlPathFull .= '</tr>';
			}
			if ($objectIndex < count($path)-1) {
				$htmlPathFull .= '<tr>';
					$htmlPathFull .= '<td style="text-align:center;">';
					$htmlPathFull .= $this->displayTrunk();
					$htmlPathFull .= '</td>';
				$htmlPathFull .= '</tr>';
			}
		}
		$htmlPathFull .= '</table>';
		
		return $htmlPathFull;
	}
	
	function buildObject($obj){
		$objectID = $obj['id'];
		$objectElements = $obj['obj'];
		$function = $obj['function'];
		$categoryID = $obj['categoryID'];
		$objSelected = $obj['selected'];
		$return = '';
		$return .= '<td>';
			if ($objectID != 0) {
				$buttonClass = $function == 'Endpoint' ? 'btn-success' : 'btn-purple';
				$return .= '<button id="'.$objectID.'" type="button" class="btn btn-block btn-sm '.$buttonClass.' waves-effect waves-light">';
				$return .= $objSelected ? '<i class="ion-location"></i>&nbsp' : '';			
				foreach($objectElements as $elementIndex => $element){
					$delimiter = $elementIndex < count($objectElements)-1 ? '.' : '';
					$return .= $element.$delimiter;
				}
				$return .= '</button>';
			} else {
				$return .= '<button id="'.$objectID.'" type="button" class="btn btn-block btn-sm btn-danger waves-effect waves-light">';
				$return .= 'None';
				$return .= '</button>';
			}
		$return .= '</td>';
		return $return;
	}
	
	function buildCable($topCode39, $btmCode39, $connectorCode39, $length){
		$return = '';
		$return .= '<td rowspan="2" style="vertical-align:middle;">';
			$scanned = $topCode39 == $connectorCode39 ? true : false;
			$return .= $this->displayArrow('top', $scanned, $topCode39);
			$return .= $length;
			$scanned = $btmCode39 == $connectorCode39 ? true : false;
			$return .= $this->displayArrow('btm', $scanned, $btmCode39);
		$return .= '</td>';
		return $return;
	}
	
	function displayArrow($orientation, $scanned, $code39){
		$fill = $scanned ? '#039cfd' : '#ffffff';
		$top = '<path stroke="#000000" fill="'.$fill.'" id="'.$code39.'" transform="rotate(-180 10,10)" d="m12.34666,15.4034l0.12924,-1.39058l-1.52092,-0.242c-3.85063,-0.61265 -7.62511,-3.21056 -9.7267,-6.69472c-0.37705,-0.62509 -0.62941,-1.22733 -0.56081,-1.33833c0.15736,-0.25462 3.99179,-2.28172 4.31605,-2.28172c0.13228,0 0.45004,0.37281 0.70613,0.82847c1.09221,1.9433 3.91879,3.97018 5.9089,4.2371l0.80686,0.10823l-0.13873,-1.2018c-0.14402,-1.24763 -0.10351,-1.50961 0.23337,-1.50961c0.21542,0 6.64622,4.79111 6.83006,5.08858c0.13947,0.22565 -0.74504,1.06278 -3.91187,3.70233c-1.37559,1.14654 -2.65852,2.08463 -2.85095,2.08463c-0.308,0 -0.33441,-0.16643 -0.22064,-1.39058l0,0l0,0l0,-0.00001z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null"/>';
		$btm = '<path stroke="#000000" fill="'.$fill.'" id="'.$code39.'" transform="rotate(-180 10,10)" stroke-dasharray="null" stroke-linejoin="null" stroke-linecap="null" d="m12.34666,4.88458l0.12924,1.38058l-1.52092,0.24026c-3.85063,0.60825 -7.62511,3.18748 -9.7267,6.64659c-0.37705,0.6206 -0.62941,1.21851 -0.56081,1.32871c0.15736,0.25279 3.99179,2.26532 4.31605,2.26532c0.13228,0 0.45004,-0.37013 0.70613,-0.82251c1.09221,-1.92933 3.91879,-3.94164 5.9089,-4.20664l0.80686,-0.10745l-0.13873,1.19316c-0.14402,1.23866 -0.10351,1.49876 0.23337,1.49876c0.21542,0 6.64622,-4.75667 6.83006,-5.052c0.13947,-0.22403 -0.74504,-1.05514 -3.91187,-3.67571c-1.37559,-1.1383 -2.65852,-2.06964 -2.85095,-2.06964c-0.308,0 -0.33441,0.16523 -0.22064,1.38058l0,0l0,0l0,0.00001l0.00001,-0.00001z"/>';
		
		$arrow = '<div class="cableArrow" data-code39="'.$code39.'" title="'.$code39.'">';
		$arrow .= '<svg width="20" height="20" style="display:block;">';
		$arrow .= '<g>';
		$arrow .= $orientation == 'top' ? $top : $btm;
		$arrow .= '</g>';
		$arrow .= '</svg>';
		$arrow .= '</div>';
		
		return $arrow;
	}
	
	function displayTrunk(){
		$trunk = '';
		$trunk .= '<svg width="20" height="40">';
		$trunk .= '<g>';
		$trunk .= '<path stroke="#000000" fill="#ffffff" transform="rotate(-90 10,20)" d="m-6.92393,20.00586l9.84279,-8.53669l0,4.26834l14.26478,0l0,-4.26834l9.84279,8.53669l-9.84279,8.53665l0,-4.26832l-14.26478,0l0,4.26832l-9.84279,-8.53665z" stroke-linecap="null" stroke-linejoin="null" stroke-dasharray="null" stroke-width="null"/>';
		$trunk .= '</g>';
		$trunk .= '</svg>';
		return $trunk;
	}
	
	function checkEntitlement($feature, $count){
		$returnArray = array(
			'success' => true,
			'response' => true
		);
		$entitlementID = $this->qls->org_info['entitlement_id'];
		
		// POST Request
		$data = array(
			'feature': $feature,
			'count': $count,
			'entitlementID': $entitlementID
		);
		$dataJSON = json_encode($data);
		$POSTData = array('data' => $dataJSON);
		
		$ch = curl_init('https://patchcablemgr.com/public/check-entitlement.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");
		
		// Submit the POST request
		$response = curl_exec($ch);
		
		// Collect POST response
		$response = json_decode($response, true);
		
		//Check for request errors.
		if(curl_errno($ch)) {
			$responseArray['success'] = false;
			$responseArray['response'] = curl_error($ch);
		} else {
			$responseArray['response'] = $response;
		}
		
		// Close cURL session handle
		curl_close($ch);
		
		return $responseArray;
	}
}