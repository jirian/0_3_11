<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    User.class.php
* @start   July 15th, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.1.5
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
 * Contains all update functions
 */
class Update {

/**
 * @var object $qls - Will contain everything else
 */
var $qls;

	/**
	 * Construct class
	 * @param object $qls - Contains all other classes
	 * @return void
	 */
	function __construct(&$qls) {
	    $this->qls = &$qls;
		
		// Store current and running versions
		$this->currentVersion = $this->getVersion();
		$this->runningVersion = PCM_VERSION;
	}

	/**
	 * Determines what update needs to be applied and applies it
	 * @return Boolean
	 */
	function determineUpdate() {
		if($this->currentVersion == '0.1.0') {
			$this->update_010_to_011();
		} else if($this->currentVersion == '0.1.1') {
			$this->update_011_to_012();
		} else if($this->currentVersion == '0.1.2') {
			$this->update_012_to_013();
		} else if($this->currentVersion == '0.1.3') {
			$this->update_013_to_020();
		} else if($this->currentVersion == '0.2.0') {
			$this->update_020_to_021();
		} else if($this->currentVersion == '0.2.1') {
			$this->update_021_to_022();
		} else {
			return true;
		}
		$this->currentVersion = $this->getVersion();
		return false;
	}
	
	/**
	 * Update from version 0.2.1 to 0.2.2
	 * @return Boolean
	 */
	function update_021_to_022() {
		$incrementalVersion = '0.2.2';
		
		// Set app version to 0.2.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Fix port name format
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if($row['templatePartitionData']) {
				
				$rowID = $row['id'];
				$partitionDataJSON = $row['templatePartitionData'];
				$partitionData = json_decode($partitionDataJSON, true);
				
				// Fix port name format
				foreach($partitionData as &$face) {
					$this->fixPortNameFormat($face);
				}
				
				// Update object templates table
				$partitionDataJSON = json_encode($partitionData);
				$this->qls->SQL->update('app_object_templates', array('templatePartitionData' => $partitionDataJSON), array('id' => array('=', $rowID)));
				
				// Update object compatibility
				foreach($partitionData as $side => $face) {
					$this->updateObjectCompatibility($face, $rowID, $side);
				}
			}
		}
		
	}
	
	/**
	 * Update from version 0.2.0 to 0.2.1
	 * @return Boolean
	 */
	function update_020_to_021() {
		$incrementalVersion = '0.2.1';
		
		// Set app version to 0.2.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "entitlement_expiration" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_expiration', 'int(40)', false, 0);
		
		// Allow Administrator role to remove users
		$this->qls->SQL->update('masks', array('auth_admin_remove_user' => 1), array('name' => array('=', 'Administrator')));
	}
	
	/**
	 * Update from version 0.1.3 to 0.2.0
	 * @return Boolean
	 */
	function update_013_to_020() {
		$incrementalVersion = '0.2.0';
		
		// Set app version to 0.2.0
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "encTolerance" column to "object_compatibility" table
		$this->qls->SQL->alter('app_object_compatibility', 'add', 'encTolerance', 'varchar(255)', true);
		
		// Add "scrollLock" column to "users" table
		$this->qls->SQL->alter('users', 'add', 'scrollLock', 'tinyint(4)', false, 1);
		
		// Rename "portLayoutX/Y" and "encLayoutX/Y" in partition data to "valueX/Y"
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if($row['templatePartitionData']) {
				$rowID = $row['id'];
				$partitionDataJSON = $row['templatePartitionData'];
				$partitionData = json_decode($partitionDataJSON, true);
				foreach($partitionData as &$face) {
					$this->alterTemplatePartitionDataLayoutName($face);
					$this->alterTemplatePartitionDataDimensionUnits($face);
				}
				$partitionDataJSON = json_encode($partitionData);
				$this->qls->SQL->update('app_object_templates', array('templatePartitionData' => $partitionDataJSON), array('id' => array('=', $rowID)));
			}
		}
		
		// Create /app directories
		if(!is_dir('/app/images/')) {
			$mkdirSuccess = mkdir('/app/images/', 0755);
		}
		if(!is_dir('/app/images/templateImages/')) {
			$mkdirSuccess = mkdir('/app/images/templateImages/', 0755);
		}
		if(!is_dir('/app/images/floorplanImages/')) {
			$mkdirSuccess = mkdir('/app/images/floorplanImages/', 0755);
		}
		
		// Create symbolic link
		if(!is_dir('/app/public/images')) {
			symlink('/app/images/', '/app/public/images');
		}
	}
	
	/**
	 * Update from version 0.1.2 to 0.1.3
	 * @return Boolean
	 */
	function update_012_to_013() {
		$incrementalVersion = '0.1.3';
		
		// Set app version to 0.1.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
	}
	
	/**
	 * Update from version 0.1.1 to 0.1.2
	 * @return Boolean
	 */
	function update_011_to_012() {
		$incrementalVersion = '0.1.2';
		
		// Set app version to 0.1.2
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
	}
	
	/**
	 * Update from version 0.1.0 to 0.1.1
	 * @return Boolean
	 */
	function update_010_to_011() {
		$incrementalVersion = '0.1.1';
		
		// Add bottomLeft-Right port orientation
		$this->qls->SQL->insert('shared_object_portOrientation', array('value', 'name', 'defaultOption'), array(4, 'BottomLeft-Right', 0));
		
		// Change mail method from sendmail to proxy
		$query = $this->qls->SQL->select('value', 'config', array('name' => array('=', 'mail_method')));
		$result = $row = $this->qls->SQL->fetch_assoc($query);
		$mailMethod = $result['value'];
		if($mailMethod == 'sendmail') {
			$this->qls->SQL->update('config', array('value' => 'proxy'), array('name' => array('=', 'mail_method')));
		}
		
		// Add "version" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'version', 'VARCHAR(15)');
		
		// Set app version to 0.1.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "entitlement_id" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_id', 'VARCHAR(40)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_last_checked', 'int(11)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_data', 'VARCHAR(255)');
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_comment', 'VARCHAR(10000)');
		
		$entitlementDataArray = array('cabinetCount' => 5, 'objectCount' => 20, 'connectionCount' => 40, 'userCount' => 2);
		$entitlementData = json_encode($entitlementDataArray);
		$updateValues = array(
			'entitlement_id' => 'None',
			'entitlement_last_checked' => 0,
			'entitlement_data' => $entitlementData,
			'entitlement_comment' => 'Never Checked.'
		);
		$this->qls->SQL->update('app_organization_data', $updateValues, array('id' => array('=', 1)));
		
		
		
		//
		// Correct duplicate template names
		//
		$foundArray = array();
		$query = $this->qls->SQL->select('*', 'app_inventory');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$rowID = $row['id'];
			
			$aID = $row['a_object_id'];
			$aFace = $row['a_object_face'];
			$aDepth = $row['a_object_depth'];
			$aPort = $row['a_port_id'];
			
			$bID = $row['b_object_id'];
			$bFace = $row['b_object_face'];
			$bDepth = $row['b_object_depth'];
			$bPort = $row['b_port_id'];
			
			if($aID == $bID and $aFace == $bFace and $aDepth == $bDepth and $aPort == $bPort) {
				if($aID != 0) {
					if($row['a_id'] != 0 or $row['b_id'] != 0) {
						$updateValues = array(
							'a_object_id' => 0,
							'a_object_face' => 0,
							'a_object_depth' => 0,
							'a_port_id' => 0,
							'b_object_id' => 0,
							'b_object_face' => 0,
							'b_object_depth' => 0,
							'b_port_id' => 0
						);
						$this->qls->SQL->update('app_inventory', $updateValues, array('id' => array('=', $rowID)));
					} else {
						$this->qls->SQL->delete('app_inventory', array('id' => array('=', $rowID)));
					}
				}
			}
		}
		
		
		
		//
		// Correct duplicate template names
		//
		$templateNameArray = array();
		$query = $this->qls->SQL->select('*', 'app_object_templates');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$templateID = $row['id'];
			$templateName = $row['templateName'];
			if(in_array($templateName, $templateNameArray)) {
				$newTemplateName = $templateName.'_'.$this->generateUniqueNameValue();
				$this->qls->SQL->update('app_object_templates', array('templateName' => $newTemplateName), array('id' => array('=', $templateID)));
			}
			array_push($templateNameArray, $templateName);
		}
		
		
		
		//
		// Correct duplicate location names
		//
		$envTreeArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			if(!isset($envTreeArray[$row['parent']])) {
				$envTreeArray[$row['parent']] = array();
			}
			$workingArray = array($row['id'], $row['name']);
			array_push($envTreeArray[$row['parent']], $workingArray);
		}
		
		foreach($envTreeArray as $parentID => $parent) {
			$nameArray = array();
			foreach($parent as $child) {
				$nodeID = $child[0];
				$nodeName = $child[1];
				if(in_array($nodeName, $nameArray)) {
					$uniqueValue = $this->generateUniqueNameValue();
					$uniqueName = $nodeName.'_'.$uniqueValue;
					$this->qls->SQL->update('app_env_tree', array('name' => $uniqueName), array('id' => array('=', $nodeID)));
				}
				array_push($nameArray, $child[1]);
			}
		}
		
		
		
		//
		// Clear out orphaned cabinet adjacency entries
		//
		$envTreeIDArray = array();
		$query = $this->qls->SQL->select('*', 'app_env_tree');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			array_push($envTreeIDArray, $row['id']);
		}
		
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			// Gather entry details
			$rowID = $row['id'];
			$leftCabinetID = $row['left_cabinet_id'];
			$rightCabinetID = $row['right_cabinet_id'];
			
			// Delete entry if either of the cabinets does not exist
			if(!in_array($leftCabinetID, $envTreeIDArray) or !in_array($rightCabinetID, $envTreeIDArray)) {
				$this->qls->SQL->delete('app_cabinet_adj', array('id' => array('=', $rowID)));
			}
		}
		
		
		
		//
		// Clear out orphaned cable path entries
		//
		$query = $this->qls->SQL->select('*', 'app_cable_path');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			// Gather entry details
			$rowID = $row['id'];
			$cabinetAID = $row['cabinet_a_id'];
			$cabinetBID = $row['cabinet_b_id'];
			
			// Delete entry if either of the cabinets does not exist
			if(!isset($this->qls->envTreeArray[$cabinetAID]) or !isset($this->qls->envTreeArray[$cabinetBID])) {
				$this->qls->SQL->delete('app_cable_path', array('id' => array('=', $rowID)));
			}
		}
		
		
		
		//
		// Resolve duplicate cabinet adjacencies
		//
		$leftArray = array();
		$rightArray = array();
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			$rowID = $row['id'];
			$leftCabinetID = $row['left_cabinet_id'];
			$rightCabinetID = $row['right_cabinet_id'];
			
			if(in_array($leftCabinetID, $leftArray) or in_array($rightCabinetID, $rightArray)) {
				$this->qls->SQL->delete('app_cabinet_adj', array('id' => array('=', $rowID)));
			}
			
			array_push($leftArray, $leftCabinetID);
			array_push($rightArray, $rightCabinetID);
		}
		
		
		
		// Update current version
		$this->currentVersion = $incrementalVersion;
		return true;
	}

	/**
	 * Retrieves currently running version number from database
	 * @return string
	 */
	function getVersion() {
		$query = $this->qls->SQL->select('*', 'app_organization_data');
		$row = $this->qls->SQL->fetch_array($query);
		if(isset($row['version'])) {
			return $row['version'];
		} else {
			// Assume version is 0.1.0 if not set
			return '0.1.0';
		}
	}
	
	/**
	 * Generates unique string to prevent duplicate names
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
	 * 0.2.2 - Fix static port name field
	 * @return string
	 */
	function fixPortNameFieldStatic(&$data){
		$nameRegEx = '/^[a-zA-Z0-9-\/\\\_]$/';
		$fieldValueArray = explode($data);
		
		foreach($fieldValueArray as &$fieldValueCharacter) {
			if(!preg_match($nameRegEx, $fieldValueCharacter)){
				$fieldValueCharacter = '_';
			}
		}
		
		$data = implode('', $fieldValueArray);
	}
	
	/**
	 * 0.2.2 - Fix incremental port name field
	 * @return string
	 */
	function fixPortNameFieldIncremental(&$data){
		$portNameFieldIncrementalRegEx = '/^[a-zA-Z]$|^[0-9]$|^[1-9][0-9]+$/';
		
		if(!preg_match($portNameFieldIncrementalRegEx, $data)){
			$data = 1;
		}
		
	}
	
	/**
	 * 0.2.2 - Fix series port name field
	 * @return string
	 */
	function fixPortNameFieldSeries(&$data){
		$portNameFieldSeriesRegEx = '/^[a-zA-Z0-9\/\\\_]{0,250}$/';
		
		if(is_array($data) and (count($data) >= 1 and count($data) <= 100)) {
			foreach($data as &$item) {
				if (!preg_match($portNameFieldSeriesRegEx, $item)){
					$item = '_';
				}
			}
		}
		
	}
	
	/**
	 * 0.2.2 - Fix port name format
	 * @return string
	 */
	function fixPortNameFormat(&$data){
		foreach($data as &$partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable') {
				
				$portTotal = $data['valueX'] * $data['valueY'];
				$portNameData = &$partition['portNameFormat'];
				$success = true;
				$fieldLength = 1;
				$hasIncremental = false;
				$hasInfiniteIncremental = false;
				$incrementalCount = 0;
				
				foreach($portNameData as &$portNameField) {
					$type = $portNameField['type'];
					if($type == 'static') {
						
						$this->fixPortNameFieldStatic($portNameField['value']);
						
					} else if($type == 'incremental') {
						
						$incrementalCount++;
						$hasIncremental = true;
						$fieldLength *= $portNameField['count'];
						
						$this->fixPortNameFieldIncremental($portNameField['value']);
						
						if($portNameField['count'] == 0) {
							$hasInfiniteIncremental = true;
						}
						
					} else if($type == 'series') {
						
						$incrementalCount++;
						$hasIncremental = true;
						$fieldLength *= count($portNameField['value']);
						
						$this->fixPortNameFieldSeries($portNameField['value']);
						
					}
				}
				
				// Check for duplicate port IDs
				if($portTotal > 1) {
					if($hasIncremental) {
						if(!$hasInfiniteIncremental) {
							if($fieldLength < $portTotal) {
								$success = false;
							} else {
								// ... Could still be duplicates, better check 'em all.
								$workingArray = array();
								for($x = 0; $x < $portTotal; $x++) {
									$portName = $this->qls->App->generatePortName($portNameData, $x, $portTotal);
									if(in_array($portName, $workingArray)) {
										$success = false;
									}
									array_push($workingArray, $portName);
								}
							}
						}
					} else {
						$success = false;
					}
				}
				
				if(!$success) {
					$newOrder = $incrementalCount + 1;
					
					$staticField = array(
						'type' => 'static',
						'value' => '_',
						'count' => 0,
						'order' => 0
					);
					
					$incrementalField = array(
						'type' => 'incremental',
						'value' => 1,
						'count' => 0,
						'order' => $newOrder
					);
					
					array_push($portNameData, $staticField);
					array_push($portNameData, $incrementalField);
				}
				
			}
			
			if(isset($partition['children'])) {
				$this->fixPortNameFormat($partition['children']);
			}
		}
		return true;
	}
	
	function updateObjectCompatibility($data, $templateID, $side, &$depthCounter=0){
		foreach($data as $partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable') {
				
				// Update object templates table
				$portNameFormatJSON = json_encode($partition['portNameFormat']);
				$this->qls->SQL->update('app_object_compatibility', array('portNameFormat' => $portNameFormatJSON), array('template_id' => array('=', $templateID), 'AND', 'side' => array('=', $side), 'AND', 'depth' => array('=', $depthCounter)));
				
				$depthCounter++;
			} else {
				
				$depthCounter++;
				if(isset($partition['children'])) {
					$this->updateObjectCompatibility($partition['children'], $templateID, $side, $depthCounter);
				}
				
			}
		}
	}
	
	function alterTemplatePartitionDataLayoutName(&$data){
		foreach($data as &$partition) {
			$partitionType = $partition['partitionType'];
			if($partitionType == 'Connectable' or $partitionType == 'Enclosure') {
				$layoutPrefix = ($partitionType == 'Connectable') ? 'port' : 'enc';
				
				// Change 'LayoutX' to 'valueX'
				if(isset($partition[$layoutPrefix.'LayoutX'])) {
					$valueX = $partition[$layoutPrefix.'LayoutX'];
					$partition['valueX'] = $valueX;
					unset($partition[$layoutPrefix.'LayoutX']);
				}
				
				// Change 'LayoutY' to 'valueY'
				if(isset($partition[$layoutPrefix.'LayoutY'])) {
					$valueY = $partition[$layoutPrefix.'LayoutY'];
					$partition['valueY'] = $valueY;
					unset($partition[$layoutPrefix.'LayoutY']);
				}
			}
			
			if(isset($partition['children'])) {
				$this->alterTemplatePartitionDataLayoutName($partition['children']);
			}
		}
		return true;
	}
	
	function alterTemplatePartitionDataDimensionUnits(&$data){
		foreach($data as &$partition) {
			
			// Change 'vunits' to 'vUnits'
			if(isset($partition['vunits'])) {
				$vUnitValue = $partition['vunits'];
				$partition['vUnits'] = $vUnitValue;
				unset($partition['vunits']);
			}
			
			// Change 'hunits' to 'hUnits'
			if(isset($partition['hunits'])) {
				$hUnitValue = $partition['hunits'];
				$partition['hUnits'] = $hUnitValue;
				unset($partition['hunits']);
			}
			
			if(isset($partition['children'])) {
				$this->alterTemplatePartitionDataDimensionUnits($partition['children']);
			}
		}
		return true;
	}
}
