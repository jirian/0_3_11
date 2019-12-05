<?php
define('QUADODO_IN_SYSTEM', true);
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';
$qls->Security->check_auth_page('user.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/content-build-objectData.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/content-build-objects.php');
?>
