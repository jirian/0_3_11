<?php

if (!defined('QUADODO_IN_SYSTEM')) {
exit;	
}

class Validate {
	var $qls;
	
	function __construct(&$qls) {
		$this->qls = &$qls;
		$active = $this->qls->user_info['username'] != '' ? 'active' : 'inactive';
		$this->returnData = array(
			'active' => $active,
			'error' => array(),
			'success' => ''
		);
		$this->nameRegEx = '/^[a-zA-Z0-9-\/\\\_]{0,250}$/';
		$this->textRegEx = '/^[a-zA-Z0-9\/\\\-\_\s]{0,250}$/';
		$this->IDRegEx = '/^[0-9]$|^[1-9][0-9]+$/';
		$this->portIDRegEx = '/^[0-9]+$/';
		$this->portNameFieldIncrementalRegEx = '/^[a-zA-Z]$|^[0-9]$|^[1-9][0-9]+$/';
		$this->portNameFieldSeriesRegEx = '/^[a-zA-Z0-9\/\\\_]{0,250}$/';
		$this->md5RegEx = '/^[a-f0-9]{32}$/';
		$this->shaRegEx = '/^[a-f0-9]{40}$/';
		$this->domainRegEx = '/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';
		$this->orgNameRegEx = '/^[a-zA-Z0-9\/\\\-\_\s\.\,]{0,250}$/';
	}
	
	//
	// Handle fatal errors
	//
	
	function validateAction($input) {
		$validInputArray = array('add', 'delete', 'edit');
		if (!in_array($input, $validInputArray)) {
			$errorMsg = 'Invalid action: '.$input;
			array_push($this->returnData['error'], $errorMsg);
			return false;
		}
		return true;
	}

	function validateDBResult($query, $errMsg, &$qls) {
		if(!$qls->SQL->num_rows($query)) {
			$errMsg = 'Internal error when searching for compatibility.';
			array_push($this->returnData['error'], $errMsg);
			return false;
		} else {
			return true;
		}
	}
	
	//
	// Handle input errors
	//
	
	function validateEmail($input) {
		if (!isset($input)){
			$errorMsg = 'Email is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!(strlen($input) > 6 && strlen($input) < 256 && filter_var($input, FILTER_VALIDATE_EMAIL))){
				$errorMsg = 'Invalid email.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateCategoryID($input) {
		if (!isset($input)){
			$errorMsg = 'Category is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = 'Invalid category selection.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateCategoryName($input, $isEdit=false) {
		if (!isset($input)){
			$errorMsg = 'Category name is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = 'Category name may only contain alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\\).';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			} else if(!$isEdit) {
				$results = $this->qls->SQL->select('*', 'app_object_category', array('name' => array('=', $input)));
				if($this->qls->SQL->num_rows($results)) {
					$errorMsg = 'A category with that name already exists.  Category names must be unique.';
					array_push($this->returnData['error'], $errorMsg);
					return false;
				}
			}
		}
		return true;
	}
	
	function validateCategoryColor($input) {
		if (!isset($input)){
			$errorMsg = 'Category color is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^#[a-zA-Z0-9]{6}$/', $input)){
				$errorMsg = 'Invalid category color.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateTemplateName($input) {
		if (!isset($input)){
			$errorMsg = 'Template name is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = 'Template name may only contain alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\\).';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			} else {
				$results = $this->qls->SQL->select('*', 'app_object_templates', array('templateName' => array('=', $input)));
				if($this->qls->SQL->num_rows($results)) {
					$errorMsg = 'A template with that name already exists.  Template names must be unique.';
					array_push($this->returnData['error'], $errorMsg);
					return false;
				}
			}
		}
		return true;
	}
	
	function validateObjectType($input) {
		if (!isset($input)){
			$errorMsg = 'Object type ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if ($input != 'Standard' and $input != 'Insert'){
				$errorMsg = 'Invalid object type ID.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateRUSize($input, $errMsg=false) {
		if (!isset($input)){
			$errorMsg = $errMsg ? $errMsg : 'RU is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $errMsg ? $errMsg : 'Invalid RU.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateMD5($input, $errMsg=false) {
		if (!isset($input)){
			$errorMsg = $errMsg ? $errMsg : 'Value is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->md5RegEx, $input)){
				$errorMsg = $errMsg ? $errMsg : 'Invalid value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateSHA($input, $errMsg=false) {
		if (!isset($input)){
			$errorMsg = $errMsg ? $errMsg : 'Value is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->shaRegEx, $input)){
				$errorMsg = $errMsg ? $errMsg : 'Invalid value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateObjectFunction($input) {
		if (!isset($input)){
			$errorMsg = 'Object function is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if ($input != 'Endpoint' and $input != 'Passive'){
				$errorMsg = 'Invalid object function.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateMountConfig($input) {
		if (!isset($input)){
			$errorMsg = 'Mounting configuration is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-1]$/', $input)){
				$errorMsg = 'Invalid mounting configuration.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateBinaryValue($input) {
		if (!isset($input)){
			$errorMsg = 'Missing required binary value.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-1]$/', $input)){
				$errorMsg = 'Invalid binary value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateLayoutAxis($input, $reference=false) {
		$reference = $reference ? $reference.' (layout axis)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Layout axis is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid layout axis value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortLayoutX($input, $reference=false) {
		$reference = $reference ? $reference.' (portLayoutX)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port layout X is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid port layout X value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortLayoutY($input, $reference=false) {
		$reference = $reference ? $reference.' (portLayoutY)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port layout Y is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid port layout Y value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateEnclosureLayoutX($input, $reference=false) {
		$reference = $reference ? $reference.' (enclosureLayoutX)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Enclosure layout X is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid enclosure layout X value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateEnclosureLayoutY($input, $reference=false) {
		$reference = $reference ? $reference.' (enclosureLayoutY)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Enclosure layout Y is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid enclosure layout Y value.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePartitionType($input, $reference=false) {
		$reference = $reference ? $reference.' (partitionType)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Partition type is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if ($input != 'Generic' and $input != 'Connectable' and $input != 'Enclosure'){
				$errorMsg = $reference ? $reference : 'Invalid partition type.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortOrientation($input, $reference=false) {
		$reference = $reference ? $reference.' (portOrientation)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port orientation is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid port orientation.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validatePortType($input, $reference=false) {
		$reference = $reference ? $reference.' (portType)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port type is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid port type.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validatePortNameFieldStatic($input, $reference=false) {
		$reference = $reference ? $reference.' (static port name field)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port name field value is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = $reference ? $reference : 'Static port name fields may only contain alphanumeric characters as well as the following characters  -_\\/';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortNameFieldIncremental($input, $reference=false) {
		$reference = $reference ? $reference.' (incremental port name field)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port name field value is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->portNameFieldIncrementalRegEx, $input)){
				error_log('Failed Incremental Validation: '.$input);
				$errorMsg = $reference ? $reference : 'Incremental port name fields may only contain a single alphanumeric character.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortNameFieldSeries($input, $reference=false) {
		$reference = $reference ? $reference.' (series port name field)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Port name field value is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if(is_array($input) and (count($input) >= 1 and count($input) <= 100)) {
				$itemError = false;
				foreach($input as $item) {
					if (!preg_match($this->portNameFieldSeriesRegEx, $item)){
						$itemError = true;
					}
				}
				
				if ($itemError) {
					$errorMsg = $reference ? $reference : 'Serial port name fields must consist of a comma separated list of strings which may only contain alphanumeric characters as well as the following characters  _\\/';
					array_push($this->returnData['error'], $errorMsg);
					return false;
				}
			} else {
				$errorMsg = $reference ? $reference : 'Invalid port name field.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortPrefix($input, $reference=false) {
		$reference = $reference ? $reference.' (portPrefix)' : $reference;
		if ($input === null){
			$errorMsg = $reference ? $reference : 'Port prefix is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = $reference ? $reference : $input.'  Port prefix may only contain alphanumeric characters as well as the following characters  -_\\/';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validatePortTotal($input) {
		if (!isset($input)){
			$errorMsg = 'Port total is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^[0-9]|[1-9][0-9]?$/', $input)){
				$errorMsg = 'Invalid port total.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortNumber($input, $reference=false) {
		$reference = $reference ? $reference.' (portNumber)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Beginning port number is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]|[1-9][0-9]?$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid beginning port number.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateMediaType($input, $reference=false) {
		$reference = $reference ? $reference.' (mediaType)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Media type is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid media type.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateFlexDirection($input, $reference=false) {
		$reference = $reference ? $reference.' (flexDirection)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Flex direction is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if ($input != 'column' and $input != 'row'){
				$errorMsg = $reference ? $reference : 'Invalid flex direction.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateFlexUnits($input, $reference=false) {
		$reference = $reference ? $reference.' (flexUnits)' : $reference;
		if (!isset($input)){
			$errorMsg = $reference ? $reference : 'Flex unit value is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = $reference ? $reference : 'Invalid flex unit.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateObjectID($input) {
		if (!isset($input)){
			$errorMsg = 'Object ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9]+$/', $input)){
				$errorMsg = 'Invalid object ID.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateObjectName($input, $reference) {
		if (!isset($input)){
			$errorMsg = 'Object Name is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = 'Invalid object Name.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			} else {
				
			}
		}
		return true;
	}
	
	function validateObjectFace($input) {
		if (!isset($input)){
			$errorMsg = 'Object face is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-1]$/', $input)){
				$errorMsg = 'Invalid object face.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validatePartitionDepth($input) {
		if (!isset($input)){
			$errorMsg = 'Partition depth is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^\d+$/', $input)){
				$errorMsg = 'Invalid partition depth.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validatePageName($input) {
		if (!isset($input)){
			$errorMsg = 'Page name is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if ($input != 'build' && $input != 'editor'){
				$errorMsg = 'Invalid page name.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateCableID($input, &$qls) {
		if (!isset($input)){
			$errorMsg = 'Cable ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[1-9]|[0-9]+$/', $input)){
				$errorMsg = 'Invalid cable ID.';
				array_push($this->returnData['error'], $errorMsg);
			} else {
				$result = $qls->SQL->select('*', 'app_inventory', array('id' => array('=', $input)));
				if ($qls->SQL->num_rows($result) == 0) {
					$errorMsg = 'Cable ID does not exist.';
					array_push($this->returnData['error'], $errorMsg);
				}
			}
		}
		return true;
	}
	
	function validateConnectorID($input, &$qls) {
		if (!isset($input)){
			$errorMsg = 'Connector ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[1-9]|[0-9]+$/', $input)){
				$errorMsg = 'Invalid connector ID.';
				array_push($this->returnData['error'], $errorMsg);
			} else {
				$result = $qls->SQL->select('*', 'app_inventory', array('a_id' => array('=', $input), 'OR', 'b_id' => array('=', $input)));
				if ($qls->SQL->num_rows($result) == 0) {
					$errorMsg = 'Connector ID does not exist.';
					array_push($this->returnData['error'], $errorMsg);
				}
			}
		}
		return true;
	}
	
	function validateCableLength($input) {
		if (!isset($input)){
			$errorMsg = 'Cable length is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[1-9]|[0-9]+$/', $input)){
				$errorMsg = 'Invalid cable length.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateCableConnectorType($input, &$qls) {
		if (!isset($input)){
			$errorMsg = 'Cable connector ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[1-9]|[0-9]+$/', $input)){
				$errorMsg = 'Invalid cable connector ID.';
				array_push($this->returnData['error'], $errorMsg);
			} else {
				$result = $qls->SQL->select('*', 'shared_cable_connectorType', array('value' => array('=', $input)));
				if ($qls->SQL->num_rows($result) == 0) {
					$errorMsg = 'Cable connector ID does not exist.';
					array_push($this->returnData['error'], $errorMsg);
				}
			}
		}
		return true;
	}
	
	function validateCableMediaType($input, &$qls) {
		if (!isset($input)){
			$errorMsg = 'Cable media type is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[1-9]|[0-9]+$/', $input)){
				$errorMsg = 'Invalid cable media type.';
				array_push($this->returnData['error'], $errorMsg);
			} else {
				$result = $qls->SQL->select('*', 'shared_mediaType', array('value' => array('=', $input)));
				if ($qls->SQL->num_rows($result) == 0) {
					$errorMsg = 'Cable media type does not exist.';
					array_push($this->returnData['error'], $errorMsg);
				}
			}
		}
		return true;
	}
	
	function validateTimezone($input, &$qls) {
		if (!isset($input)){
			$errorMsg = 'Timezone is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			$regions = array(
				'Africa' => DateTimeZone::AFRICA,
				'America' => DateTimeZone::AMERICA,
				'Antarctica' => DateTimeZone::ANTARCTICA,
				'Aisa' => DateTimeZone::ASIA,
				'Atlantic' => DateTimeZone::ATLANTIC,
				'Europe' => DateTimeZone::EUROPE,
				'Indian' => DateTimeZone::INDIAN,
				'Pacific' => DateTimeZone::PACIFIC
			);
			$validTimezone = false;
			$timezones = array();
			
			foreach ($regions as $name => $mask) {
				$zones = DateTimeZone::listIdentifiers($mask);
				foreach($zones as $timezone) {
					if($timezone == $input) {
						$validTimezone = true;
					}
				}
			}
			if(!$validTimezone) {
				$errorMsg = 'Invalid timezone.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	// Flexible validations
	
	function validateID($input, $reference) {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->IDRegEx, $input)){
				$errorMsg = 'Invalid '.$reference.'.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortID($input, $reference="port ID") {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match($this->portIDRegEx, $input)){
				$errorMsg = 'Invalid '.$reference.'.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateTreeID($input) {
		if (!isset($input)){
			$errorMsg = 'Environment tree ID is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^#$|^[1-9]$|^[1-9]{1}[0-9]+$/', $input)){
				$errorMsg = 'Invalid environment tree ID.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateNameText($input, $reference) {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->nameRegEx, $input)){
				$errorMsg = ucfirst($reference).' may only contain alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\\).';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateDistance($input, $reference) {
		if (!isset($input)){
			$errorMsg = $reference.' is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if(!is_numeric($input)) {
				$errorMsg = $reference.' must be a number.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			} else {
				$inputMultiplied = $input * 10;
				if(is_float($inputMultiplied)) {
					$errorMsg = $reference.' must be a number in 0.5 increments.';
					array_push($this->returnData['error'], $errorMsg);
					return false;
				} else {
					if($inputMultiplied % 5) {
						$errorMsg = $reference.' must be a number in 0.5 increments.';
						array_push($this->returnData['error'], $errorMsg);
						return false;
					}
				}
			}
		}
		return true;
	}
	
	function validateText($input, $reference) {
		if (!preg_match($this->textRegEx, $input)){
			$errorMsg = ucfirst($reference).' may only contain 250 alphanumeric characters as well as the following characters  -_\\/';
			array_push($this->returnData['error'], $errorMsg);
		}
		return true;
	}
	
	function validateOrderItem($input, $validationArray, $reference) {
		if (!in_array($input, $validationArray)){
			$errorMsg = 'Invalid '.$reference.' selection.';
			array_push($this->returnData['error'], $errorMsg);
		}
		return true;
	}
	
	function validateCode39($input, $reference) {
		if (!preg_match('/^[a-zA-Z0-9]{1,6}$/', $input) or $input === '0'){
			$errorMsg = 'Invalid '.$reference.'.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		}
		return true;
	}
	
	function validateInArray($input, $validationArray, $reference) {
		if (!in_array($input, $validationArray)){
			$errorMsg = 'Invalid '.$reference;
			array_push($this->returnData['error'], $errorMsg);
			return false;
		}
		return true;
	}
	
	function validateAddressText($input, $reference) {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9a-zA-Z -.]+$/', $input)){
				$errorMsg = 'Invalid '.$reference.'.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateDiscountCode($input) {
		if (!isset($input)){
			$errorMsg = 'Discount code cannot be blank.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-9a-zA-Z]+$/', $input)){
				$errorMsg = 'Invalid discount code.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateElementValue($value) {
		if (!isset($value)){
			$errorMsg = 'Element data is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			$dataValue = explode('-', $value);
			if (count($dataValue) != 5){
				$errorMsg = 'Invalid data value.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
	}
	
	function validateTrueFalse($input, $reference) {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!is_bool($input)){
				$errorMsg = 'Invalid '.$reference.'.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateGlobalID($input) {
		if (!isset($input)){
			$errorMsg = 'Global ID is required.';
			array_push($this->returnData['error'], $errorMsg);
		} else {
			if (!preg_match('/^[0-4]-[0-9]+-[0-1]-[0-9]+-[0-9]+$/', $input)){
				$errorMsg = 'Invalid Global ID.';
				array_push($this->returnData['error'], $errorMsg);
			}
		}
		return true;
	}
	
	function validateSlotID($input, $reference) {
		if (!isset($input)){
			$errorMsg = ucfirst($reference).' is required.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match('/^enc([0-9]|[1-9][0-9])+slot[a-z]([0-9]|[1-9][0-9]+)$/', $input)){
				$errorMsg = 'Invalid '.$reference.'.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validatePortNameFormat($input, $portTotal, $reference=false) {
		$portNameData = $input;
		$success = true;
		if(is_array($portNameData) and (count($portNameData) >=1 and count($portNameData) <= 5)) {
			
			$fieldLength = 1;
			$hasIncremental = false;
			$hasInfiniteIncremental = false;
			
			foreach($portNameData as $portNameField) {
				$type = $portNameField['type'];
				if($type == 'static') {
					if(!$this->validatePortNameFieldStatic($portNameField['value'], $reference)) {
						$success = false;
					}
				} else if($type == 'incremental') {
					$hasIncremental = true;
					if(!$this->validatePortNameFieldIncremental($portNameField['value'], $reference)) {
						$success = false;
					} else {
						$fieldLength *= $portNameField['count'];
						if($portNameField['count'] == 0) {
							$hasInfiniteIncremental = true;
						}
					}
				} else if($type == 'series') {
					$hasIncremental = true;
					if(!$this->validatePortNameFieldSeries($portNameField['value'], $reference)) {
						$success = false;
					} else {
						$fieldLength *= count($portNameField['value']);
					}
				} else {
					$errorMsg = 'Invalid port name field type.';
					array_push($this->returnData['error'], $errorMsg);
					$success = false;
				}
			}
			
			//
			if($portTotal > 1) {
				if($hasIncremental) {
					if(!$hasInfiniteIncremental) {
						if($fieldLength < $portTotal) {
							$errorMsg = $reference ? $reference.' (Duplicate port IDs found)' : 'Duplicate port IDs found.  Try adding an incremental field with a "0" count.';
							array_push($this->returnData['error'], $errorMsg);
							$success = false;
						}
					}
				} else {
					$errorMsg = $reference ? $reference.' (Duplicate port IDs found)' : 'Duplicate port IDs found.  Try adding an incremental field.';
					array_push($this->returnData['error'], $errorMsg);
					$success = false;
				}
			}
		} else {
			$errorMsg = $reference ? $reference : 'Invalid port name data.';
			array_push($this->returnData['error'], $errorMsg);
			$success = false;
		}
		return $success;
	}
	
	function validateTemplateJSON($input, $depth=0, $reference=false) {
		$success = true;
		if($depth < 20) {
			//$depth++;
			
			//Validate partition type
			if($this->validatePartitionType($input['partitionType'], $reference)) {
				$partitionType = $input['partitionType'];
				
				if($partitionType == 'Connectable') {
					
					//Validate partition port layout X
					if(!$this->validateLayoutAxis($input['valueX'], 'Invalid port layout X')) {
						$success = false;
					}
					
					//Validate partition port layout Y
					if(!$this->validateLayoutAxis($input['valueY'], 'Invalid port layout Y')) {
						$success = false;
					}
					
					//Validate port orientation
					if(!$this->validatePortOrientation($input['portOrientation'], $reference)) {
						$success = false;
					}
					
					//Validate port type
					if(!$this->validatePortType($input['portType'], $reference)) {
						$success = false;
					}
					
					// Validate port name format
					if($input['partitionType'] == 'Connectable') {
						if($success) {
							$portTotal = $input['valueX'] * $input['valueY'];
							if(!$this->validatePortNameFormat($input['portNameFormat'], $portTotal, $reference)) {
								$success = false;
							}
						}
					}
					
					// Validate media type
					if(!$this->validateMediaType($input['mediaType'], $reference)) {
						$success = false;
					}
				
				} else if($partitionType == 'Enclosure') {
					
					//Validate partition enclosure layout X
					if(!$this->validateLayoutAxis($input['valueX'], 'Invalid enclosure layout X')) {
						$success = false;
					}
					
					//Validate partition enclosure layout Y
					if(!$this->validateLayoutAxis($input['valueY'], 'Invalid enclosure layout Y')) {
						$success = false;
					}
					
				}
			} else {
				$success = false;
			}
			
			// Validate partition flex direction
			if(!$this->validateFlexDirection($input['direction'], $reference)) {
				$success = false;
			}
			
			// Validate partition flex units
			if(!$this->validateFlexUnits($input['hUnits'], $reference)) {
				$success = false;
			}
			if(!$this->validateFlexUnits($input['vUnits'], $reference)) {
				$success = false;
			}
			
			if (isset($input['children'])) {
				foreach ($input['children'] as $children) {
					$depth++;
					if(!$this->validateTemplateJSON($children, $depth, $reference)) {
						$success = false;
					}
				}
			}
		} else {
			$errorMsg = $reference ? $reference : 'Template structure is too large.';
			array_push($this->returnData['error'], $errorMsg);
			$success = false;
		}
		
		return $success;
	}

	function validateDuplicate($table, $where, $errorMsg) {
		$results = $this->qls->SQL->select('*', $table, $where);
		if($this->qls->SQL->num_rows($results)) {
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			return true;
		}
	}
	
	function validateExistenceInDB($table, $where, $errorMsg) {
		$results = $this->qls->SQL->select('*', $table, $where);
		if(!$this->qls->SQL->num_rows($results)) {
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			$object = $this->qls->SQL->fetch_assoc($results);
			return $object;
		}
	}
	
	function validateServerName($input) {
		if (!isset($input)){
			$errorMsg = 'Server name cannot be blank.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->domainRegEx, $input) and !filter_var($input, FILTER_VALIDATE_IP)){
				$errorMsg = 'Invalid server name.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
	
	function validateOrgName($input) {
		if (!isset($input)){
			$errorMsg = 'Organization name cannot be blank.';
			array_push($this->returnData['error'], $errorMsg);
			return false;
		} else {
			if (!preg_match($this->orgNameRegEx, $input)){
				$errorMsg = 'Invalid organization name.';
				array_push($this->returnData['error'], $errorMsg);
				return false;
			}
		}
		return true;
	}
}

?>
