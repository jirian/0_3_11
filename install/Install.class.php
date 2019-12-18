<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    Install.class.php
* @start   August 3rd, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.1.6
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
if (!defined('IN_INSTALL')) {
    exit;
}

/**
 * Contains all the necessary components for an installation
 */
class Install {

/**
 * @var string $system_version - The version of the system
 */
var $system_version = '3.1.11';
var $app_version = '0.2.0';

/**
 * @var string $install_error - Contains the installation error
 */
var $install_error = array();

	/**
	 * Construct the class
	 *	@return void but will output error if found
	 */
	function __construct() {
        $this->install_directory = dirname(__FILE__);
        session_start();
        header('Content-Type: text/html; charset=iso-8859-1');

		// Check the version
		if (!version_compare('5.5.0', PHP_VERSION, '<=')) {
		    die('Minimum PHP version required to run this system is: <b>PHP 5.5.0</b>');
		}
		
		// Check if mysql schema is readable
		if (!is_readable($this->install_directory . '/schemas/mysql.sql')) {
		    die('All the files in the <b>install/schemas</b> directory must be CHMOD to 755.');
		}

		// Get rid of the slashes if it's turned on
		if (get_magic_quotes_gpc()) {
			// POST Method
			foreach ($_POST as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						if (is_array($value2)) {
							foreach ($value2 as $key3 => $value3) {
								if (is_array($value3)) {
									foreach ($value3 as $key4 => $value4) {
										// Can't go any deeper
										if (is_array($value4)) {
										    $_POST[$key][$key2][$key3][$key4] = $value4;
										}
										else {
										    $_POST[$key][$key2][$key3][$key4] = stripslashes($value4);
										}
									}
								}
								else {
								    $_POST[$key][$key2][$key3] = stripslashes($value3);
								}
							}
						}
						else {
						    $_POST[$key][$key2] = stripslashes($value2);
						}
					}
				}
				else {
				    $_POST[$key] = stripslashes($value);
				}
			}

			// GET Method
			foreach ($_GET as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						if (is_array($value2)) {
							foreach ($value2 as $key3 => $value3) {
								if (is_array($value3)) {
									foreach ($value3 as $key4 => $value4) {
										// Can't go any deeper
										if (is_array($value4)) {
										    $_GET[$key][$key2][$key3][$key4] = $value4;
										}
										else {
										    $_GET[$key][$key2][$key3][$key4] = stripslashes($value4);
										}
									}
								}
								else {
								    $_GET[$key][$key2][$key3] = stripslashes($value3);
								}
							}
						}
						else {
						    $_GET[$key][$key2] = stripslashes($value2);
						}
					}
				}
				else {
				    $_GET[$key] = stripslashes($value);
				}
			}

			// COOKIE Method
			foreach ($_COOKIE as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						if (is_array($value2)) {
							foreach ($value2 as $key3 => $value3) {
								if (is_array($value3)) {
									foreach ($value3 as $key4 => $value4) {
										// Can't go any deeper
										if (is_array($value4)) {
										    $_COOKIE[$key][$key2][$key3][$key4] = $value4;
										}
										else {
										    $_COOKIE[$key][$key2][$key3][$key4] = stripslashes($value4);
										}
									}
								}
								else {
								    $_COOKIE[$key][$key2][$key3] = stripslashes($value3);
								}
							}
						}
						else {
						    $_COOKIE[$key][$key2] = stripslashes($value2);
						}
					}
				}
				else {
				    $_COOKIE[$key] = stripslashes($value);
				}
			}
		}
	}

	/**
	 * Make the input safe, same as in Security.class.php
	 * @param string  $input - The input text
	 * @param boolean $html  - Whether to use htmlentities() or not
	 * @return clean string
	 */
	function make_safe($input, $html = true) {
		/**
		 * Loops through to a certain depth and uses the addslashes()
		 * or htmlentities() functions to make it safe.
		 */
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						if (is_array($value2)) {
							foreach ($value2 as $key3 => $value3) {
								if (is_array($value3)) {
									foreach ($value3 as $key4 => $value4) {
										// This is as far as we go
										if (is_array($value4)) {
									    	$input[$key][$key2][$key3][$key4] = $value4;
										}
										else {
											if ($html === false) {
											    $input[$key][$key2][$key3][$key4] = addslashes($value4);
											}
											else {
											    $input[$key][$key2][$key3][$key4] = htmlentities($value4, ENT_QUOTES);
											}
										}
									}
								}
								else {
									if ($html === false) {
									    $input[$key][$key2][$key3] = addslashes($value3);
									}
									else {
									    $input[$key][$key2][$key3] = htmlentities($value3, ENT_QUOTES);
									}
								}
							}
						}
						else {
							if ($html === false) {
							    $input[$key][$key2] = addslashes($value2);
							}
							else {
							    $input[$key][$key2] = htmlentities($value2, ENT_QUOTES);
							}
						}
					}
				}
				else {
					if ($html === false) {
				    	$input[$key] = addslashes($value);
					}
					else {
					    $input[$key] = htmlentities($value, ENT_QUOTES);
					}
				}
			}

		    return $input;
		}
		else {
			if ($html === false) {
			    return addslashes($input);
			}
			else {
			    return htmlentities($input, ENT_QUOTES);
			}
		}
	}

	/**
	 * Installs the system
	 *	@return true on success, false on failure
	 */
	function install_system() {

        // Get the user browser information
        $browser = strtolower($_SERVER['HTTP_USER_AGENT']);
        $another_mime = (strpos($browser, 'msie') === true || strpos($browser, 'opera') === true) ? 'application/octetstream' : 'application/octet-stream';
        $errors = null;

        // Check all the input data
        $database_prefix = (preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]{0,254}$/", $_POST['database_prefix']) || $_POST['database_prefix'] == '') ? $this->make_safe($_POST['database_prefix']) : false;
        $database_type = (isset($_POST['database_type'])) ? $_POST['database_type'] : false;
        $database_server_name = (isset($_POST['database_server_name'])) ? $_POST['database_server_name'] : false;
        $database_username = (isset($_POST['database_username'])) ? $_POST['database_username'] : false;
        $database_password = (isset($_POST['database_password'])) ? $_POST['database_password'] : false;
        $database_name = (isset($_POST['database_name'])) ? $_POST['database_name'] : false;
        $database_port = (isset($_POST['database_port']) && is_numeric($_POST['database_port']) && $_POST['database_port'] > 0) ? $_POST['database_port'] : false;
        $cookie_prefix = (preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]{0,254}$/", $_POST['cookie_prefix']) || $_POST['cookie_prefix'] == '') ? $this->make_safe($_POST['cookie_prefix'], false) : false;
        $max_username = (is_numeric($_POST['max_username']) && strlen($_POST['max_username']) < 3 && strlen($_POST['max_username']) > 0) ? $this->make_safe($_POST['max_username']) : false;
        $min_username = (is_numeric($_POST['min_username']) && strlen($_POST['min_username']) < 3 && strlen($_POST['min_username']) > 0) ? $this->make_safe($_POST['min_username']) : false;
        $max_password = (is_numeric($_POST['max_password']) && strlen($_POST['max_password']) < 3 && strlen($_POST['max_password']) > 0) ? $this->make_safe($_POST['max_password']) : false;
        $min_password = (is_numeric($_POST['min_password']) && strlen($_POST['min_password']) < 3 && strlen($_POST['min_password']) > 0) ? $this->make_safe($_POST['min_password']) : false;
        $cookie_path = (preg_match('/^\/.*?$/', $_POST['cookie_path'])) ? $this->make_safe($_POST['cookie_path']) : false;
        $cookie_secure = ($_POST['cookie_secure'] == 0 || $_POST['cookie_secure'] == 1) ? $this->make_safe($_POST['cookie_secure']) : false;
        $cookie_length = (is_numeric($_POST['cookie_length']) && strlen($_POST['cookie_length']) < 8 && strlen($_POST['cookie_length']) > 0) ? $this->make_safe($_POST['cookie_length']) : false;
        $cookie_domain = (isset($_POST['cookie_domain'])) ? $this->make_safe($_POST['cookie_domain']) : false;
        $max_tries = (is_numeric($_POST['max_tries']) && strlen($_POST['max_tries']) < 3 && strlen($_POST['max_tries']) > 0) ? $this->make_safe($_POST['max_tries']) : false;
        $user_regex = (isset($_POST['user_regex']) && strlen($_POST['user_regex']) <= 255) ? $this->make_safe($_POST['user_regex'], false) : false;
        $security_image = ($_POST['security_image'] == 'yes' || $_POST['security_image'] == 'no') ? $this->make_safe($_POST['security_image']) : false;
        $max_upload_size = (isset($_POST['max_upload_size']) && $_POST['max_upload_size'] > -1) ? $this->make_safe($_POST['max_upload_size']) : '1048576';
        $auth_registration = ($_POST['auth_registration'] == 1 || $_POST['auth_registration'] == 0) ? $this->make_safe($_POST['auth_registration']) : '1';
        $activation_type = ($_POST['activation_type'] == 0 || $_POST['activation_type'] == 1 || $_POST['activation_type'] == 2) ? $this->make_safe($_POST['activation_type']) : false;
        $login_redirect = (isset($_POST['login_redirect']) && strlen($_POST['login_redirect']) <= 255 && strlen($_POST['login_redirect']) > 0) ? $this->make_safe($_POST['login_redirect'], false) : false;
        $logout_redirect = (isset($_POST['logout_redirect']) && strlen($_POST['logout_redirect']) <= 255 && strlen($_POST['logout_redirect']) > 0) ? $this->make_safe($_POST['logout_redirect'], false) : false;
        $default_group_name = (isset($_POST['default_group_name']) && strlen($_POST['default_group_name']) > 0 && strlen($_POST['default_group_name']) <= 255) ? $this->make_safe($_POST['default_group_name']) : 'Default';
        $default_mask_name = (isset($_POST['default_mask_name']) && strlen($_POST['default_mask_name']) > 0 && strlen($_POST['default_mask_name']) <= 255) ? $this->make_safe($_POST['default_mask_name']) : 'Default';
        $redirect_type = ($_POST['redirect_type'] == '1' || $_POST['redirect_type'] == '2' || $_POST['redirect_type'] == '3') ? $this->make_safe($_POST['redirect_type']) : '1';
        $online_users_format = (isset($_POST['online_users_format']) && strlen($_POST['online_users_format']) <= 255) ? $this->make_safe($_POST['online_users_format'], false) : '{username}';
        $online_users_separator = (isset($_POST['online_users_separator']) && strlen($_POST['online_users_separator']) <= 255) ? $this->make_safe($_POST['online_users_separator'], false) : ',';
        $email = (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) && strlen($_POST['email']) <= 255) ? $_POST['email'] : false;
        $email_confirm = $email;
		$username = $email;
        $password = (isset($_POST['password']) && strlen($_POST['password']) >= $min_password && strlen($_POST['password']) <= $max_password) ? $this->make_safe($_POST['password']) : false;
        $password_confirm = (isset($_POST['password_confirm']) && $password == $this->make_safe($_POST['password_confirm'])) ? true : false;

		// Did they fail? If so add to the $errors variable
		if ($database_prefix === false) {
		    $errors[] = 'The database table prefix you entered was not a valid format.';
		}

		if ($cookie_prefix === false) {
		    $errors[] = 'The cookie prefix you entered was not valid.';
		}

		if ($max_username === false) {
	    	$errors[] = 'The max. username length you entered was not valid.';
		}

		if ($min_username === false) {
		    $errors[] = 'The min. username length you entered was not valid.';
		}

		if ($max_password === false) {
	    	$errors[] = 'The max. password length you entered was not valid.';
		}

		if ($min_password === false) {
		    $errors[] = 'The min. password length you entered was not valid.';
		}

		if ($cookie_path === false) {
		    $errors[] = 'The cookie path you specified was not a valid format.';
		}

		if ($cookie_secure === false) {
		    $errors[] = 'The cookie secure choice you selected was not a valid format.';
		}

		if ($cookie_length === false) {
		    $errors[] = 'The cookie length you specified was not valid.';
		}

		if ($cookie_domain === false) {
		    $errors[] = 'The cookie domain you entered was not valid.';
		}

		if ($max_tries === false) {
		    $errors[] = 'The max. login tries you entered was not valid.';
		}

		if ($user_regex === false) {
		    $errors[] = 'The user regex you entered was not valid.';
		}

		if ($security_image === false) {
		    $errors[] = 'The security image choice was not a valid format.';
		}

		if ($activation_type === false) {
		    $errors[] = 'The activation type you entered was not valid.';
		}

		if ($login_redirect === false) {
		    $errors[] = 'The login redirect URL you specified was not valid.';
		}

		if ($logout_redirect === false) {
		    $errors[] = 'The logout redirect URL you specified was not valid.';
		}

		if ($username === false) {
		    $errors[] = 'The username you entered was not valid according to the user regex and lengths you specified.';
		}

		if ($password === false || $password_confirm === false) {
		    $errors[] = 'Either the password you entered was not valid, or the two passwords did not match.';
		}

		if ($email === false || $email_confirm === false) {
		    $errors[] = 'Either the email address you entered was not valid, or the two emails did not match.';
		}

		// Do we have some errors?
		if ($errors !== null) {
            // Make sure the values are saved
            $_SESSION['database_prefix'] = stripslashes($database_prefix);
            $_SESSION['cookie_prefix'] = stripslashes($cookie_prefix);
            $_SESSION['max_username'] = stripslashes($max_username);
            $_SESSION['min_username'] = stripslashes($min_username);
            $_SESSION['max_password'] = stripslashes($max_password);
            $_SESSION['min_password'] = stripslashes($min_password);
            $_SESSION['cookie_path'] = stripslashes($cookie_path);
            $_SESSION['cookie_secure'] = stripslashes($cookie_secure);
            $_SESSION['cookie_length'] = stripslashes($cookie_length);
            $_SESSION['cookie_domain'] = stripslashes($cookie_domain);
            $_SESSION['max_tries'] = stripslashes($max_tries);
            $_SESSION['user_regex'] = stripslashes($user_regex);
            $_SESSION['security_image'] = stripslashes($security_image);
            $_SESSION['max_upload_size'] = stripslashes($max_upload_size);
            $_SESSION['auth_registration'] = stripslashes($auth_registration);
            $_SESSION['activation_type'] = stripslashes($activation_type);
            $_SESSION['login_redirect'] = stripslashes($login_redirect);
            $_SESSION['logout_redirect'] = stripslashes($logout_redirect);
            $_SESSION['default_group_name'] = html_entity_decode(stripslashes($default_group_name));
            $_SESSION['default_mask_name'] = html_entity_decode(stripslashes($default_mask_name));
            $_SESSION['redirect_type'] = stripslashes($redirect_type);
            $_SESSION['online_users_format'] = stripslashes($online_users_format);
            $_SESSION['online_users_separator'] = stripslashes($online_users_separator);
            $_SESSION['username'] = stripslashes($username);
            $_SESSION['password'] = stripslashes($password);
            $_SESSION['password_confirm'] = stripslashes($password_confirm);
            $_SESSION['email'] = stripslashes($email);
            $_SESSION['email_confirm'] = stripslashes($email_confirm);
            $error_count = count($errors);

            // Create the HTML and return false
            $this->install_error = 'The following errors occured while trying to process the information you entered:<br /><ul>';

			for ($x = 0; $x < $error_count; $x++) {
				array_push($this->install_error, $errors[$x]);
			}

            $this->install_error .= '</ul><br /><br />Please <a href="install.php">go back</a> and try again.';
            return false;
		} else {
			
            // Get the Test class
            require_once('Test.class.php');
            $this->test = new Test(
				$database_server_name,				 
                $database_username,
                $database_password,
                $database_name,
                $database_port,
                $database_type
            );

            // Test the connection and create necessary tables
            $this->test->test_connection();
            $this->test->create_system_tables($database_prefix);

            // Code generation
            $c_hash[] = md5($username . $password . md5($email));
            $c_hash[] = sha1($c_hash[0] . $c_hash[0]) . md5(sha1(sha1($email) . sha1($password)) . md5($username));
            $c_hash[] = sha1(sha1(sha1(sha1(md5(md5('   	') . sha1(' 	'))) . sha1($password . $username))));
            $c_hash[] = sha1($c_hash[0] . $c_hash[1] . $c_hash[2]) . sha1($c_hash[2] . $c_hash[0] . $c_hash[1]);
            $c_hash[] = sha1($username);
            $c_hash[] = sha1($password);
            $c_hash[] = md5(md5($email) . md5($password));
            $hash_count = count($c_hash);

			for ($x = 0; $x < $hash_count; $x++) {
			    $random_hash = rand(0, $hash_count);
			    $c_hash[] = sha1($c_hash[$x]) . sha1($password) . sha1($c_hash[$random_hash] . $username);
			}

            $user_code = sha1(sha1($c_hash[0] . $c_hash[1] . $c_hash[2] . $c_hash[3]) . sha1($c_hash[4] . $c_hash[5]) . md5($c_hash[6] . $c_hash[7] . $c_hash[8] . sha1($c_hash[9])) . $password . $email);

            // Password generation
            $hash[] = md5($password);
            $hash[] = md5($password . $user_code);
            $hash[] = md5($password) . sha1($user_code . $password) . md5(md5($password));
            $hash[] = sha1($password . $user_code . $password);
            $hash[] = md5($hash[3] . $hash[0] . $hash[1] . $hash[2] . sha1($hash[3] . $hash[2]));
            $hash[] = sha1($hash[0] . $hash[1] . $hash[2] . $hash[3]) . md5($hash[4] . $hash[4]) . sha1($user_code);
            $final_hash = sha1($hash[0] . $hash[1] . $hash[2] . $hash[3] . $hash[4] . $hash[5] . md5($user_code));

			$masks = array(
				"'Admin',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1",
				"'{$default_mask_name}',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0",
				"'Administrator',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,1,1,1",
				"'Operator',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1",
				"'User',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1"
			);
			
			// The permission masks
			foreach($masks as $masks_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}masks` (`name`,`auth_admin`,`auth_admin_phpinfo`,`auth_admin_configuration`,`auth_admin_add_user`,`auth_admin_user_list`,`auth_admin_remove_user`,`auth_admin_edit_user`,`auth_admin_add_page`,`auth_admin_page_list`,`auth_admin_remove_page`,`auth_admin_edit_page`,`auth_admin_page_stats`,`auth_admin_add_mask`,`auth_admin_list_masks`,`auth_admin_remove_mask`,`auth_admin_edit_mask`,`auth_admin_add_group`,`auth_admin_list_groups`,`auth_admin_remove_group`,`auth_admin_edit_group`, `auth_admin_activate_account`,`auth_admin_send_invite`,`auth_356a192b7913b04c54574d18c28d46e6395428ab`, `auth_da4b9237bacccdf19c0760cab7aec4a8359010b0`, `auth_77de68daecd823babbb58edb1c8e14d7106e83bb`, `auth_1b6453892473a467d07372d45eb05abc2031647a`) VALUES({$masks_item})")) {
					$this->test->output_error();
				}
			}

			$groups = array(
				"'Admin',1,0,1",
				"'{$default_group_name}',2,1,1",
				"'Administrator',3,1,1",
				"'Operator',4,1,1",
				"'User',5,1,1"
			);
			
			// The groups
			foreach($groups as $groups_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}groups` (`name`,`mask_id`,`is_public`,`leader`) VALUES({$groups_item})")) {
					$this->test->output_error();
				}
			}

			$pages = array(
				"1,'admin.php',0",
				"2,'administrator.php',0",
				"3,'operator.php',0",
				"4,'user.php',0"
			);
			
			// The pages
			foreach($pages as $pages_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}pages` (`id`,`name`,`hits`) VALUES({$pages_item})")) {
					$this->test->output_error();
				}
			}

			// Add administrator
			if (!$this->test->query("INSERT INTO `{$database_prefix}users` (`username`,`password`,`code`,`active`,`last_login`,`last_session`,`blocked`,`tries`,`last_try`,`email`,`mask_id`,`group_id`) VALUES('{$username}','{$final_hash}','{$user_code}','yes','0','0','no','0','0','{$email}',3,3)")) {
			    $this->test->output_error();
			}
			
			$env_tree = array(
				"'Location', '#', 'location', 42, NULL",
				"'Sub-Location', '1', 'location', 42, NULL",
				"'Pod', '2', 'pod', 42, NULL",
				"'Cab1', '3', 'cabinet', 42, NULL",
				"'Cab2', '3', 'cabinet', 42, NULL",
				"'Cab3', '3', 'cabinet', 42, NULL"
			);
			
			// Add environment tree data
			foreach($env_tree as $env_tree_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}app_env_tree` (`name`, `parent`, `type`, `size`, `floorplan_img`) VALUES({$env_tree_item})")) {
					$this->test->output_error();
				}
			}
			
			$cabinet_adj = array(
				"4,5,0",
				"5,6,0"
			);
			
			// Add cabinet adjacency
			foreach($cabinet_adj as $cabinet_adj_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}app_cabinet_adj` (`left_cabinet_id`, `right_cabinet_id`, `entrance_ru`) VALUES({$cabinet_adj_item})")) {
					$this->test->output_error();
				}
			}
			
			$object_category = array(
				"1, 'Switch', '#d581d6', 0",
				"2, 'Router', '#d6819f', 0",
				"3, 'Server', '#d68d8d', 0",
				"4, 'Module', '#e59881', 0",
				"5, 'Linecard', '#81d6a1', 0",
				"6, 'Patch_Panel', '#a9a9a9', 1",
				"7, 'Cable_Mgmt', '#d3d3d3', 0",
				"8, 'Enclosure', '#95d681', 0",
				"9, 'MM_Fiber_Insert', '#81d6ce', 0",
				"10, 'SM_Fiber_Insert', '#d6d678', 0"
			);
			
			// Add object category
			foreach($object_category as $object_category_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}app_object_category` (`id`, `name`, `color`, `defaultOption`) VALUES({$object_category_item})")) {
					$this->test->output_error();
				}
			}
			
			$object_compatibility = array(
				"1, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, 'walljack', 'Connectable', 'Passive', NULL, 1, 8, 1, 1, NULL, NULL, NULL, NULL, '', NULL",
				"2, 2, 0, 0, 1, 1, 1, NULL, NULL, 'wap', 'Connectable', 'Endpoint', NULL, 1, 8, 1, 1, NULL, NULL, NULL, NULL, '[{\"type\":\"static\",\"value\":\"NIC\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"3, 3, 0, 0, 1, 1, 1, NULL, NULL, 'device', 'Connectable', 'Endpoint', NULL, 1, 8, 1, 1, NULL, NULL, NULL, NULL, '[{\"type\":\"static\",\"value\":\"NIC\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"4, 5, 0, 0, 24, 1, 24, NULL, NULL, 'Standard', 'Connectable', 'Passive', 1, 1, 1, 1, 1, 'column', 0, 10, 2, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"5, 6, 0, 0, 24, 1, 24, NULL, NULL, 'Standard', 'Connectable', 'Passive', 1, 1, 2, 1, 1, 'column', 0, 10, 2, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"6, 8, 0, 0, 24, 2, 48, NULL, NULL, 'Standard', 'Connectable', 'Passive', 1, 1, 1, 1, 1, 'column', 0, 10, 4, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"7, 9, 0, 0, 24, 2, 48, NULL, NULL, 'Standard', 'Connectable', 'Passive', 1, 1, 2, 1, 1, 'column', 0, 10, 4, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"8, 12, 0, 0, 1, 6, 6, NULL, NULL, 'Insert', 'Connectable', 'Passive', 2, 2, 5, 4, 2, 'row', 0, 10, 8, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"9, 13, 0, 0, 1, 6, 6, NULL, NULL, 'Insert', 'Connectable', 'Passive', 2, 2, 6, 2, 2, 'row', 0, 10, 8, '[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"10, 10, 0, 0, 4, 1, 4, NULL, NULL, 'Insert', 'Connectable', 'Endpoint', 1, 4, 8, 5, 4, 'row', 0, 2, 2, '[{\"type\":\"static\",\"value\":\"\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"11, 11, 0, 1, 6, 2, 12, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 2, 1, 8, 5, 1, 'column', 0.2, 2, 2, '[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"12, 11, 0, 2, 6, 2, 12, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 2, 1, 8, 5, 1, 'column', 0.2, 2, 2, '[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"13\",\"count\":0,\"order\":1}]', NULL",
				"13, 11, 0, 3, 6, 2, 12, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 2, 1, 8, 5, 1, 'column', 0.2, 2, 2, '[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"25\",\"count\":0,\"order\":1}]', NULL",
				"14, 11, 0, 4, 6, 2, 12, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 2, 1, 8, 5, 1, 'column', 0.2, 2, 2, '[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"37\",\"count\":0,\"order\":1}]', NULL",
				"15, 11, 0, 5, NULL, NULL, 0, 1, 1, 'Standard', 'Enclosure', 'Endpoint', NULL, NULL, 8, 5, NULL, 'column', 0.2, 2, 2, NULL, 'Loose'",
				"16, 11, 1, 2, 1, 1, 1, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 1, 1, 8, 5, 1, 'row', 0.5, 1, 1, '[{\"type\":\"static\",\"value\":\"Con\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"17, 11, 1, 3, 1, 1, 1, NULL, NULL, 'Standard', 'Connectable', 'Endpoint', 1, 1, 8, 5, 1, 'row', 0.5, 1, 1, '[{\"type\":\"static\",\"value\":\"Mgmt\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}]', NULL",
				"18, 14, 0, 0, NULL, NULL, 0, 12, 1, 'Standard', 'Enclosure', 'Passive', NULL, NULL, NULL, NULL, NULL, 'column', 0, 10, 8, NULL, 'Loose'"
			);

			// Add object compatibility
			foreach($object_compatibility as $object_compatibility_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}app_object_compatibility` (`id`, `template_id`, `side`, `depth`, `portLayoutX`, `portLayoutY`, `portTotal`, `encLayoutX`, `encLayoutY`, `templateType`, `partitionType`, `partitionFunction`, `portOrientation`, `portType`, `mediaType`, `mediaCategory`, `mediaCategoryType`, `direction`, `flex`, `hUnits`, `vUnits`, `portNameFormat`, `encTolerance`) VALUES({$object_compatibility_item})")) {
					$this->test->output_error();
				}
			}
			
			$object_templates = array(
				"1, 'Walljack', NULL, 'walljack', NULL, 'Passive', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL",
				"2, 'WAP', NULL, 'wap', NULL, 'Endpoint', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL",
				"3, 'Device', NULL, 'device', NULL, 'Endpoint', NULL, NULL, NULL, NULL, NULL, '', 'NULL', NULL",
				"4, '1RU_Cable_Mgmt', 7, 'Standard', 1, 'Endpoint', 0, NULL, NULL, NULL, NULL, '[[{\"portLayoutX\":0,\"portLayoutY\":0,\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Generic\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0\",\"vUnits\":2,\"hUnits\":10}]]', 'c4e7eb2d860f17b94042199509ca4b28.jpg', '1803d57fbe466a9ad297138d8f0e5adb.jpg'",
				"5, '24P_RJ45_CAT5E', 6, 'Standard', 1, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"24\",\"valueY\":\"1\",\"vUnits\":2,\"hUnits\":10}]]', '47618b55d38fcaf9ad73be0ead312d68.jpg', '47618b55d38fcaf9ad73be0ead312d68.jpg'",
				"6, '24P_RJ45_CAT6', 6, 'Standard', 1, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":\"2\",\"direction\":\"column\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"24\",\"valueY\":\"1\",\"vUnits\":2,\"hUnits\":10}]]', '47618b55d38fcaf9ad73be0ead312d68.jpg', '257aeaa17021b2cf1433c5600b6afbd9.jpg'",
				"7, '2RU_Cable_Mgmt', 7, 'Standard', 2, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"portLayoutX\":0,\"portLayoutY\":0,\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Generic\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0\",\"vUnits\":4,\"hUnits\":10}]]', 'c4e7eb2d860f17b94042199509ca4b28.jpg', '777d0ce548d86a09597c611df7c176c5.jpg'",
				"8, '48p_RJ45_Cat5e', 6, 'Standard', 2, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"24\",\"valueY\":\"2\",\"vUnits\":4,\"hUnits\":10}]]', '0e1b15f076088230505a14c5a6e010c0.jpg', 'dcc818257812e63aa373e870624f0026.jpg'",
				"9, '48p_RJ45_Cat6', 6, 'Standard', 2, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":\"2\",\"direction\":\"column\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"24\",\"valueY\":\"2\",\"vUnits\":4,\"hUnits\":10}]]', '0e1b15f076088230505a14c5a6e010c0.jpg', '6398f2f394d353853304afdecae57a38.jpg'",
				"12, '6P_LC_SM', 10, 'Insert', 4, 'Passive', NULL, 12, 1, 10, 8, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":\"2\",\"mediaType\":\"5\",\"direction\":\"row\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"1\",\"valueY\":\"6\",\"vUnits\":8,\"hUnits\":10}]]', '86257b172c9a706aa704abfdad4de53c.jpg', '6a9cb6747ea927516970a96390b8d913.jpg'",
				"13, '6p_LC_OM4', 9, 'Insert', 4, 'Passive', NULL, 12, 1, 10, 8, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":\"2\",\"mediaType\":\"6\",\"direction\":\"row\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Port\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"1\",\"valueY\":\"6\",\"vUnits\":8,\"hUnits\":10}]]', 'f782af8c83a096a188ba333d0039aaf7.jpg', '91e69930de177114222ba8be2c5a4859.jpg'",
				"10, 'C3850-NM-4-1G', 4, 'Insert', 1, 'Endpoint', NULL, 1, 1, 2, 2, '[[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":\"4\",\"mediaType\":1,\"direction\":\"row\",\"flex\":\"0\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"4\",\"valueY\":\"1\",\"vUnits\":2,\"hUnits\":2}]]', '7ae4f0a4040a7fe8f3a823b167952f26.jpg', NULL",
				"11, 'C3850_48p', 1, 'Standard', 1, 'Endpoint', 1, NULL, NULL, NULL, NULL, '[[{\"portLayoutX\":0,\"portLayoutY\":0,\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Generic\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"row\",\"flex\":\"0\",\"children\":[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.2\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"6\",\"valueY\":\"2\",\"vUnits\":2,\"hUnits\":2},{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.2\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"13\",\"count\":0,\"order\":1}],\"valueX\":\"6\",\"valueY\":\"2\",\"vUnits\":2,\"hUnits\":2},{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.2\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"25\",\"count\":0,\"order\":1}],\"valueX\":\"6\",\"valueY\":\"2\",\"vUnits\":2,\"hUnits\":2},{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":\"2\",\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.2\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Gi1/0/\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"37\",\"count\":0,\"order\":1}],\"valueX\":\"6\",\"valueY\":\"2\",\"vUnits\":2,\"hUnits\":2},{\"portLayoutX\":0,\"portLayoutY\":0,\"partitionType\":\"Enclosure\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.2\",\"valueX\":1,\"valueY\":1,\"vUnits\":2,\"hUnits\":2}],\"vUnits\":2,\"hUnits\":10,\"encTolerance\":\"Loose\"}],[{\"portLayoutX\":0,\"portLayoutY\":0,\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Generic\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"row\",\"flex\":\"0\",\"children\":[{\"portLayoutX\":0,\"portLayoutY\":0,\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Generic\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0.1\",\"children\":[{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"row\",\"flex\":\"0.5\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Con\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"1\",\"valueY\":\"1\",\"vUnits\":1,\"hUnits\":1},{\"encLayoutX\":1,\"encLayoutY\":1,\"partitionType\":\"Connectable\",\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"row\",\"flex\":\"0.5\",\"portNameFormat\":[{\"type\":\"static\",\"value\":\"Mgmt\",\"count\":0,\"order\":0},{\"type\":\"incremental\",\"value\":\"1\",\"count\":0,\"order\":1}],\"valueX\":\"1\",\"valueY\":\"1\",\"vUnits\":1,\"hUnits\":1}],\"vUnits\":2,\"hUnits\":1}],\"vUnits\":2,\"hUnits\":10}]]', 'ab97c853ba94a8ebb9d950f6867601de.jpg', 'fc78fde147cccc7597b959a8841c895d.jpg'",
				"14, 'Fiber_Enclosure', 8, 'Standard', 4, 'Passive', 0, NULL, NULL, NULL, NULL, '[[{\"portLayoutX\":0,\"portLayoutY\":0,\"partitionType\":\"Enclosure\",\"portPrefix\":\"Port\",\"portNumber\":1,\"portOrientation\":1,\"portType\":1,\"mediaType\":1,\"direction\":\"column\",\"flex\":\"0\",\"valueX\":\"12\",\"valueY\":\"1\",\"vUnits\":8,\"hUnits\":10,\"encTolerance\":\"Loose\"}]]', 'NULL', NULL"
			);

			// Add object templates
			foreach($object_templates as $object_templates_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}app_object_templates` (`id`, `templateName`, `templateCategory_id`, `templateType`, `templateRUSize`, `templateFunction`, `templateMountConfig`, `templateEncLayoutX`, `templateEncLayoutY`, `templateHUnits`, `templateVUnits`, `templatePartitionData`, `frontImage`, `rearImage`) VALUES({$object_templates_item})")) {
					$this->test->output_error();
				}
			}
			
			// Add organization data
			$entitlementLastChecked = 0;
			$entitlementDataArray = array('cabinetCount' => 5, 'objectCount' => 20, 'connectionCount' => 40, 'userCount' => 2);
			$entitlementData = json_encode($entitlementDataArray);
			if (!$this->test->query("INSERT INTO `{$database_prefix}app_organization_data` (`name`, `version`, `entitlement_id`, `entitlement_last_checked`, `entitlement_data`, `entitlement_comment`) VALUES('Acme, Inc.', '".$this->app_version."', 'None', ".$entitlementLastChecked.", '".$entitlementData."', 'Never checked.')")) {
				$this->test->output_error();
			}
			
			$cable_color = array(
				"1, 'Blue', 'blu', 1",
				"2, 'White', 'wht', 0",
				"3, 'Yellow', 'ylw', 0",
				"4, 'Red', 'red', 0",
				"5, 'Black', 'blk', 0"
			);
			
			// Add cable color
			foreach($cable_color as $cable_color_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_cable_color` (`value`, `name`, `short_name`, `defaultOption`) VALUES({$cable_color_item})")) {
					$this->test->output_error();
				}
			}
			
			$cable_connectorOptions = array(
				"'2-2', 'LC-LC', 0, 2",
				"'2-3', 'LC-SC', 0, 2",
				"'3-3', 'SC-SC', 0, 2",
				"'1-1', 'RJ45', 1, 1",
				"'4-4', 'Label', 0, 3"
			);
			
			// Add cable connector options
			foreach($cable_connectorOptions as $cable_connectorOptions_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_cable_connectorOptions` (`value`, `name`, `defaultOption`, `category_type_id`) VALUES({$cable_connectorOptions_item})")) {
					$this->test->output_error();
				}
			}
			
			$cable_connectorType = array(
				"1, 'RJ45', 1",
				"2, 'LC', 0",
				"3, 'SC', 0",
				"4, 'Label', 0"
			);
			
			// Add cable connector type
			foreach($cable_connectorType as $cable_connectorType_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_cable_connectorType` (`value`, `name`, `defaultOption`) VALUES({$cable_connectorType_item})")) {
					$this->test->output_error();
				}
			}
			
			$cable_length = array(
				"305, '1', 1",
				"610, '2', 1",
				"914, '3', 1",
				"1524, '5', 1",
				"3048, '10', 1",
				"152, '0.5', 1",
				"500, '0.5', 2",
				"1000, '1', 2",
				"2000, '2', 2",
				"3000, '3', 2",
				"5000, '5', 2"
			);
			
			// Add cable length
			foreach($cable_length as $cable_length_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_cable_length` (`value`, `name`, `category_type_id`) VALUES({$cable_length_item})")) {
					$this->test->output_error();
				}
			}
			
			$history_action_type = array(
				"1, 'Add'",
				"2, 'Change'",
				"3, 'Delete'"
			);
			
			// Add history action type
			foreach($history_action_type as $history_action_type_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_history_action_type` (`value`, `name`) VALUES({$history_action_type_item})")) {
					$this->test->output_error();
				}
			}
			
			$history_function = array(
				"1, 'Build->Templates'",
				"2, 'Build->Cabinets'",
				"3, 'Explore'",
				"4, 'Scan'",
				"5, 'Inventory'",
				"6, 'Admin->General'",
				"7, 'Admin->Integration'"
			);
			
			// Add history function
			foreach($history_function as $history_function_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_history_function` (`value`, `name`) VALUES({$history_function_item})")) {
					$this->test->output_error();
				}
			}
			
			$mediaCategory = array(
				"1, 'Copper', 1",
				"2, 'Multimode Fiber', 2",
				"3, 'Label', 3",
				"4, 'Singlemode Fiber', 2",
				"5, 'Unspecified', 4"
			);
			
			// Add media category
			foreach($mediaCategory as $mediaCategory_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_mediaCategory` (`value`, `name`, `category_type_id`) VALUES({$mediaCategory_item})")) {
					$this->test->output_error();
				}
			}
			
			$mediaCategoryType = array(
				"1, 'Copper', 'ft.'",
				"2, 'Fiber', 'm.'",
				"3, 'Label', ''",
				"4, 'Unspecified', 'm.'"
			);
			
			// Add media category type
			foreach($mediaCategoryType as $mediaCategoryType_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_mediaCategoryType` (`value`, `name`, `unit_of_length`) VALUES({$mediaCategoryType_item})")) {
					$this->test->output_error();
				}
			}
			
			$mediaType = array(
				"1, 'Cat5e', '1', 1, 1, 1",
				"2, 'Cat6', '1', 1, 0, 1",
				"3, 'Cat6a', '1', 1, 0, 1",
				"5, 'SM-OS1', '4', 2, 0, 1",
				"6, 'MM-OM4', '2', 2, 0, 1",
				"7, 'MM-OM3', '2', 2, 0, 1",
				"8, 'Unspecified', '5', 4, 0, 0"
			);
			
			// Add media type
			foreach($mediaType as $mediaType_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_mediaType` (`value`, `name`, `category_id`, `category_type_id`, `defaultOption`, `display`) VALUES({$mediaType_item})")) {
					$this->test->output_error();
				}
			}
			
			$object_portOrientation = array(
				"1, 'Left-Right', 1",
				"2, 'Top-Bottom', 0",
				"3, 'Right-Left', 0",
				"4, 'BottomLeft-Right', 0"
			);
			
			// Add object port orientation
			foreach($object_portOrientation as $object_portOrientation_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_object_portOrientation` (`value`, `name`, `defaultOption`) VALUES({$object_portOrientation_item})")) {
					$this->test->output_error();
				}
			}
			
			$object_portType = array(
				"1, 'RJ45', 1, 1",
				"2, 'LC', 2, 0",
				"3, 'SC', 2, 0",
				"4, 'SFP', 4, 0"
			);
			
			// Add object port type
			foreach($object_portType as $object_portType_item) {
				if (!$this->test->query("INSERT INTO `{$database_prefix}shared_object_portType` (`value`, `name`, `category_type_id`, `defaultOption`) VALUES({$object_portType_item})")) {
					$this->test->output_error();
				}
			}

            // Configuration information to be inserted
            $sql_start = "INSERT INTO `{$database_prefix}config` (`name`,`value`) VALUES(";
            $sql_end = ")";
            $sql[] = "'cookie_prefix','{$cookie_prefix}'";
            $sql[] = "'max_username','{$max_username}'";
            $sql[] = "'min_username','{$min_username}'";
            $sql[] = "'max_password','{$max_password}'";
            $sql[] = "'min_password','{$min_password}'";
            $sql[] = "'cookie_path','{$cookie_path}'";
            $sql[] = "'cookie_secure','{$cookie_secure}'";
            $sql[] = "'cookie_length','{$cookie_length}'";
            $sql[] = "'cookie_domain','{$cookie_domain}'";
            $sql[] = "'max_tries','{$max_tries}'";
            $sql[] = "'user_regex','{$user_regex}'";
            $sql[] = "'security_image','{$security_image}'";
            $sql[] = "'activation_type','{$activation_type}'";
            $sql[] = "'login_redirect','{$login_redirect}'";
            $sql[] = "'logout_redirect','{$logout_redirect}'";
            $sql[] = "'max_upload_size','{$max_upload_size}'";
            $sql[] = "'auth_registration','{$auth_registration}'";
            $sql[] = "'current_version','{$this->system_version}'";
            $sql[] = "'redirect_type','{$redirect_type}'";
            $sql[] = "'online_users_format','{$online_users_format}'";
            $sql[] = "'online_users_separator','{$online_users_separator}'";
			$sql[] = "'mail_method','proxy'";
			$sql[] = "'from_email','no-reply@example.com'";
			$sql[] = "'from_name','No Reply'";
			$sql[] = "'smtp_server',''";
			$sql[] = "'smtp_port',''";
			$sql[] = "'smtp_auth',''";
			$sql[] = "'smtp_username',''";
			$sql[] = "'smtp_password',''";
            $sql_count = count($sql);

            // Insert the config data
            $this->test->query('BEGIN');

			for ($x = 0; $x < $sql_count; $x++) {
				if (!$this->test->query($sql_start . $sql[$x] . $sql_end)) {
                    $this->test->query('ROLLBACK');
                    $this->test->output_error();
                    return false;
				}
			}

		    $this->test->query('COMMIT');

            // Make sure the port shows up false if it's false
            $database_port = ($database_port === false) ? 'false' : $database_port;

            // We don't need these anymore
            unset($_SESSION['database_prefix'],
                $_SESSION['cookie_prefix'],
                $_SESSION['max_username'],
                $_SESSION['min_username'],
                $_SESSION['max_password'],
                $_SESSION['min_password'],
                $_SESSION['cookie_path'],
                $_SESSION['cookie_secure'],
                $_SESSION['cookie_length'],
                $_SESSION['cookie_domain'],
                $_SESSION['max_tries'],
                $_SESSION['user_regex'],
                $_SESSION['security_image'],
                $_SESSION['max_upload_size'],
                $_SESSION['auth_registration'],
                $_SESSION['activation_type'],
                $_SESSION['login_redirect'],
                $_SESSION['logout_redirect'],
                $_SESSION['default_group_name'],
                $_SESSION['default_mask_name'],
                $_SESSION['redirect_type'],
                $_SESSION['online_users_format'],
                $_SESSION['online_users_separator'],
                $_SESSION['username'],
                $_SESSION['password'],
                $_SESSION['password_confirm'],
                $_SESSION['email'],
                $_SESSION['email_confirm']
            );

            // The database_info.php file
            $time = date('F jS, Y', time());
            $database_info = <<<DATABASE_INFO
<?php
/*** *** *** *** *** ***
* @package   Quadodo Login Script
* @file      database_info.php
* @author    Douglas Rennehan
* @generated {$time}
* @link      http://www.quadodo.net
*** *** *** *** *** ***
* Comments are always before the code they are commenting
*** *** *** *** *** ***/
if (!defined('QUADODO_IN_SYSTEM')) {
exit;
}

define('SYSTEM_INSTALLED', true);
\$database_prefix = '{$database_prefix}';
\$database_type = '{$database_type}';
\$database_server_name = '{$database_server_name}';
\$database_username = '{$database_username}';
\$database_password = '{$database_password}';
\$database_name = '{$database_name}';
\$database_port = {$database_port};

/**
 * Use persistent connections?
 * Change to true if you have a high load
 * on your server, but it's not really needed.
 */
\$database_persistent = false;
?>
DATABASE_INFO;

			//if (is_writable($_SERVER['DOCUMENT_ROOT'].'/includes')) {
			if (is_writable('/app')) {
				//if ($file_handle = fopen($_SERVER['DOCUMENT_ROOT'].'/includes/database_info.php', 'w')) {
				if ($file_handle = fopen('/app/database_info.php', 'w')) {
                    fwrite($file_handle, $database_info);
                    fclose($file_handle);
                    //die('You have successfully installed the system! Please move/rename/remove this directory and then you can access all of your pages!');
					header('Location: '.$cookie_path);
				}
				else {
                    // Prepare for download, then send the information
                    header('Content-Type: application/x-php');
                    header("Content-Type: {$another_mime}");
                    header('Content-Disposition: attachment; filename=database_info.php');
                    header('Content-Length: ' . strlen($database_info));

					if (strpos($browser, 'msie 6.0') === true) {
					    header('Expires: -1');
					}
					else {
					    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3144900));
					}

                    // Print out the database_info.php file
                    echo $database_info;
                    exit;
				}
			}
			else {
                // Prepare for download, then send the information
                header('Content-Type: application/x-php');
                header("Content-Type: {$another_mime}");
                header('Content-Disposition: attachment; filename=database_info.php');
                header('Content-Length: ' . strlen($database_info));

				if (strpos($browser, 'msie 6.0') === true) {
				    header('Expires: -1');
				}
				else {
				    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 3144900));
				}

                // Print out the database_info.php file
                echo $database_info;
                exit;
			}
		}
	}
}