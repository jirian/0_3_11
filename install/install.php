<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    install.php
* @start   August 1st, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.1.3
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
define('IN_INSTALL', true);
define('QUADODO_IN_SYSTEM', true);

if (!version_compare('5.5.0', PHP_VERSION, '<=')) {
    die('You currently have PHP version ' . PHP_VERSION . ' installed. The minimum requirement is PHP 5.5.0. Please visit the <a href="http://www.quadodo.net/s.php">support forum</a> for more information.');
}

// Check if system is already installed
include '/app/database_info.php';
if (SYSTEM_INSTALLED === true) {
	die('System is already installed.  To reinstall, delete the database_info.php file in <b>/app/</b>.');
}

// Report all errors except E_NOTICE, because it screws things up...
error_reporting(E_ALL ^ E_NOTICE);

// Get the installation class
require_once('Install.class.php');
$install = new Install();

// Check if app is hosted
if(array_key_exists('PCM_Hosted', getallheaders())) {
	die('This is a hosted application.  You may only run the installer on your locally hosted installations.');
}

if (isset($_POST['process'])) {
	// Install the system
	if (!$install->install_system()) {
		foreach($install->install_error as $errMsg) {
			echo "<li>".$errMsg."</li>";
		}
	}
}
else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

	<head>
		<title>Install</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="author" content="Douglas Rennehan" />
		<meta name="robots" content="none" />
	</head>

	<body>
		<form action="install.php" method="post">
		<input type="hidden" name="process" value="yes" />
			<fieldset style="width: 50%;<?php if (getenv('MYSQL_PASSWORD')) { echo ' display: none;'; }?>">
				<legend>
					SQL Setup
				</legend>
				<table border="0">
					<tr style="display: none;">
						<td>
							Table Prefix:
						</td>
						<td>
							<input type="text" name="database_prefix" maxlength="254" value="<?php if (isset($_SESSION['database_prefix'])) { echo $_SESSION['database_prefix']; } else { echo 'qls_'; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Database Type:
						</td>
						<td>
							<select name="database_type">
<?php

	if (extension_loaded('mysqli')) {
?>
								<option value="MySQLi" selected="selected">MySQLi</option>
<?php
	}
    else {
?>
                                <option value="null">CONTACT SUPPORT</option>
<?php
    }
?>
							</select>
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Database Port:
						</td>
						<td>
							<input type="text" name="database_port" maxlength="255" value="3306" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Database Server Name:
						</td>
						<td>
							<input type="text" name="database_server_name" maxlength="255" value="<?php if (isset($_SESSION['database_server_name'])) { echo $_SESSION['database_server_name']; } else { echo 'database'; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Database Name:
						</td>
						<td>
							<input type="text" name="database_name" maxlength="512" value="<?php if (isset($_SESSION['database_name'])) { echo $_SESSION['database_name']; } else { echo 'pcm'; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Database Username:
						</td>
						<td>
							<input type="text" name="database_username" maxlength="512" value="<?php if (isset($_SESSION['database_username'])) { echo $_SESSION['database_username']; } else { echo 'pcmuser'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Database Password (<small>default is <strong>ChangeMe7492#&!</strong></small>):
						</td>
						<td>
							<input type="text" name="database_password" maxlength="512" value="<?php if (isset($_SESSION['database_password'])) { echo $_SESSION['database_password']; } else { echo (getenv('MYSQL_PASSWORD')) ? getenv('MYSQL_PASSWORD') : ''; } ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
			<br>
			<fieldset style="width: 50%; display: none;">
				<legend>
					Cookie Information
				</legend>
				<table border="0">
					<tr style="display: none;">
						<td>
							Cookie Prefix:
						</td>
						<td>
							<input type="text" name="cookie_prefix" maxlength="254" value="<?php if (isset($_SESSION['cookie_prefix'])) { echo $_SESSION['cookie_prefix']; } else { echo 'qls3_'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Cookie Path:
						</td>
						<td>
							<input type="text" name="cookie_path" maxlength="255" value="<?php if (isset($_SESSION['cookie_path'])) { echo $_SESSION['cookie_path']; } else { echo str_replace('install/install.php', '', $_SERVER['REQUEST_URI']); } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Cookie Secure:
						</td>
						<td>
							<select name="cookie_secure">
								<option value="0"<?php if ($_SESSION['cookie_secure'] == '0') { ?> selected="selected"<?php } ?>>No</option>
								<option value="1"<?php if ($_SESSION['cookie_secure'] == '1') { ?> selected="selected"<?php } ?>>Yes</option>
							</select>
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Cookie Length:
						</td>
						<td>
							<input type="text" name="cookie_length" maxlength="11" value="<?php if (isset($_SESSION['cookie_length'])) { echo $_SESSION['cookie_length']; } else { echo '1209600'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Cookie Domain:
						</td>
						<td>
							<input type="text" name="cookie_domain" maxlength="255" value="<?php if (isset($_SESSION['cookie_domain'])) { echo $_SESSION['cookie_domain']; } else { echo $_SERVER['HTTP_HOST']; } ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
			<!--br />
			<br /-->
			<fieldset style="display: none; width: 50%;">
				<legend>
					Security Information
				</legend>
				<table border="0">
					<tr>
						<td>
							Maximum Login Attempts:
						</td>
						<td>
							<input type="text" name="max_tries" maxlength="2" value="<?php if (isset($_SESSION['max_tries'])) { echo $_SESSION['max_tries']; } else { echo '5'; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td colspan="2">
							<input type="hidden" name="security_image" value="no" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Maximum Upload Size:
						</td>
						<td>
							<input type="text" name="max_upload_size" maxlength="11" value="<?php if (isset($_SESSION['max_upload_size'])) { echo $_SESSION['max_upload_size']; } else { echo '1048576'; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Public Registrations:
						</td>
						<td>
							<select name="auth_registration">
								<option value="1"<?php if ($_SESSION['auth_registration'] == 1) { ?> selected="selected"<?php } ?>>Yes</option>
								<option value="0"<?php if ($_SESSION['auth_registration'] == 0) { ?> selected="selected"<?php } ?>>No</option>
							</select>
						</td>
					</tr>
				</table>
			</fieldset>
			<!--br />
			<br /-->
			<fieldset style="display: none; width: 50%;">
				<legend>
					User Settings
				</legend>
				<table border="0">
					<tr>
						<td>
							Maximum Username Length:
						</td>
						<td>
							<input type="text" name="max_username" maxlength="2" value="<?php if (isset($_SESSION['max_username'])) { echo $_SESSION['max_username']; } else { echo '99'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Minimum Username Length:
						</td>
						<td>
							<input type="text" name="min_username" maxlength="2" value="<?php if (isset($_SESSION['min_username'])) { echo $_SESSION['min_username']; } else { echo '5'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Maximum Password Length:
						</td>
						<td>
							<input type="text" name="max_password" maxlength="2" value="<?php if (isset($_SESSION['max_password'])) { echo $_SESSION['max_password']; } else { echo '15'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Minimum Password Length:
						</td>
						<td>
							<input type="text" name="min_password" maxlength="2" value="<?php if (isset($_SESSION['min_password'])) { echo $_SESSION['min_password']; } else { echo '4'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Activation Type:
						</td>
						<td>
							<select name="activation_type">
								<option value="0"<?php if ($_SESSION['activation_type'] == '0') { ?> selected="selected"<?php } ?>>None</option>
								<option value="1"<?php if ($_SESSION['activation_type'] == '1') { ?> selected="selected"<?php } ?>>User</option>
								<option value="2"<?php if ($_SESSION['activation_type'] == '2') { ?> selected="selected"<?php } ?>>Administrator</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							User Regex:
						</td>
						<td>
							<input type="text" name="user_regex" maxlength="255" value="<?php if (isset($_SESSION['user_regex'])) { echo $_SESSION['user_regex']; } else { echo '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Login Redirect URL:
						</td>
						<td>
							<input type="text" name="login_redirect" maxlength="255" value="<?php if (isset($_SESSION['login_redirect'])) { echo $_SESSION['login_redirect']; } else { echo 'index.php'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Logout Redirect URL:
						</td>
						<td>
							<input type="text" name="logout_redirect" maxlength="255" value="<?php if (isset($_SESSION['logout_redirect'])) { echo $_SESSION['logout_redirect']; } else { echo 'index.php'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Default Group Name:
						</td>
						<td>
							<input type="text" name="default_group_name" maxlength="255" value="<?php if (isset($_SESSION['default_group_name'])) { echo $_SESSION['default_group_name']; } else { echo 'Default'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Mask Name for Default Group:
						</td>
						<td>
							<input type="text" name="default_mask_name" maxlength="255" value="<?php if (isset($_SESSION['default_mask_name'])) { echo $_SESSION['default_mask_name']; } else { echo 'Default'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Redirect Type:
						</td>
						<td>
							<select name="redirect_type">
								<option value="1"<?php if ($_SESSION['redirect_type'] == '1') { ?> selected="selected"<?php } ?>>PHP (Recommended)</option>
								<option value="2"<?php if ($_SESSION['redirect_type'] == '2') { ?> selected="selected"<?php } ?>>HTML meta Tag</option>
								<option value="3"<?php if ($_SESSION['redirect_type'] == '3') { ?> selected="selected"<?php } ?>>JavaScript</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Online Users Format:
						</td>
						<td>
							<input type="text" name="online_users_format" maxlength="255" value="<?php if (isset($_SESSION['online_users_format'])) { echo $_SESSION['online_users_format']; } else { echo '{username}'; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Online Users Separator:
						</td>
						<td>
							<input type="text" name="online_users_separator" maxlength="255" value="<?php if (isset($_SESSION['online_users_separator'])) { echo $_SESSION['online_users_separator']; } else { echo ','; } ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
			<br>
			<fieldset style="width: 50%;">
				<legend>
					Administrator User Information
				</legend>
				<table border="0">
					<tr style="display: none;">
						<td>
							Username:
						</td>
						<td>
							<input type="text" name="username" maxlength="255" value="<?php if (isset($_SESSION['username'])) { echo $_SESSION['username']; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Email Address:
						</td>
						<td>
							<input type="text" name="email" maxlength="100" value="<?php if (isset($_SESSION['email'])) { echo $_SESSION['email']; } ?>" />
						</td>
					</tr>
					<tr style="display: none;">
						<td>
							Confirm:
						</td>
						<td>
							<input type="text" name="email_confirm" maxlength="100" value="<?php if (isset($_SESSION['email_confirm'])) { echo $_SESSION['email_confirm']; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Password:
						</td>
						<td>
							<input type="password" name="password" maxlength="255" value="<?php if (isset($_SESSION['password'])) { echo $_SESSION['password']; } ?>" />
						</td>
					</tr>
					<tr>
						<td>
							Confirm:
						</td>
						<td>
							<input type="password" name="password_confirm" maxlength="255" value="<?php if (isset($_SESSION['password_confirm'])) { echo $_SESSION['password_confirm']; } ?>" />
						</td>
					</tr>
				</table>
			</fieldset>
		<br>
		<div align="left">
			The installation will take a minute or two.  You will be automatically redirected to the login page upon completion.
		</div>
		<br>
		<input type="submit" value="Install" />
		</form>
	</body>

</html>
<?php
}
?>