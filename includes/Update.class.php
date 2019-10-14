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
		
		// If version is not set, assume version 0.1.0
		if($this->currentVersion == 'none') {
			$this->currentVersion = '0.1.0';
		}
	}

	/**
	 * Determines what update needs to be applied and applies it
	 * @return Boolean
	 */
	function determineUpdate() {
		if($this->currentVersion == '0.1.0') {
			$this->update_010_to_011();
		} else {
			return true;
		}
		return false;
	}
	
	/**
	 * Update from version 0.1.0 to 0.1.1
	 * @return Boolean
	 */
	function update_010_to_011() {
		$incrementalVersion = '0.1.1';
		
		// Add "version" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'version', 'VARCHAR(15)');
		
		// Set app version to 0.1.1
		$this->qls->SQL->update('app_organization_data', array('version' => $incrementalVersion), array('id' => array('=', 1)));
		
		// Add "entitlement_id" column to "app_organization_data" table
		$this->qls->SQL->alter('app_organization_data', 'add', 'entitlement_id', 'VARCHAR(40)');
		
		// Generate entitlementID and store in DB
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$entitlementIDSalt .= $characters[rand(0, $charactersLength - 1)];
		}
		$entitlementID = sha1(time().$entitlementIDSalt);
		$this->qls->SQL->update('app_organization_data', array('entitlement_id' => $entitlementID), array('id' => array('=', 1)));
		
		// Clear out orphaned cabinet adjacency entries
		$query = $this->qls->SQL->select('*', 'app_cabinet_adj');
		while ($row = $this->qls->SQL->fetch_assoc($query)){
			
			// Gather entry details
			$rowID = $row['id'];
			$leftCabinetID = $row['left_cabinet_id'];
			$rightCabinetID = $row['right_cabinet_id'];
			
			// Delete entry if either of the cabinets does not exist
			if(!isset($this->qls->envTreeArray[$leftCabinetID]) or !isset($this->qls->envTreeArray[$rightCabinetID])) {
				$this->qls->SQL->delete('app_cabinet_adj', array('id' => array('=', $rowID)));
			}
		}
		
		// Clear out orphaned cable path entries
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
			return 'none';
		}
	}
}
