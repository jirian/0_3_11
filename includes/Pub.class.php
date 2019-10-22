<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    Pub.class.php
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
class Pub {

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
	}
	
	function sendProxyEmail($type, $recipient, $msgData){
		$returnData = array(
			'success' => true
		);
		
		// POST Request
		$data = array(
			'action' => 'create',
			'type' => $type,
			'recipient' => $recipient,
			'msgData' => $msgData
		);
		
		$dataJSON = json_encode($data);
		$POSTData = array('data' => $dataJSON);
		
		$ch = curl_init('https://patchcablemgr.com/public/process_proxy-email.php');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: BACKDOOR=yes'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, "/etc/ssl/certs/");
		
		// Submit the POST request
		$responseJSON = curl_exec($ch);
		
		//Check for request errors.
		if(curl_errno($ch)) {
			$returnData['success'] = false;
			$returnData['msg'] = 'Error when submitting email data to proxy server.';
		} else {
			error_log('Debug: response = '.$responseJSON);
			$response = json_decode($responseJSON, true);
		}
	}
}
?>