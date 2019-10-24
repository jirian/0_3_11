<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
?>



<?php
if ($qls->User->check_password_code()) {
	if (isset($_POST['process'])) {
		if ($qls->User->change_password()) {
		    echo CHANGE_PASSWORD_SUCCESS;
		} else {
		    printf($qls->User->change_password_error . CHANGE_PASSWORD_TRY_AGAIN, htmlentities(strip_tags($_GET['code']), ENT_QUOTES));
		}
	} else {
	    require_once('html/change_password_form.php');
	}
} else {
	// Are we just sending the email?
	if (!isset($_GET['code'])) {
		if (isset($_POST['process'])) {
			if($change_link = $qls->User->get_password_reset_link()) {
				error_log('Debug: changeLink = '.$change_link);
				$recipientEmail = $qls->Security->make_safe($_POST['username']);
				$qls->Pub->sendProxyEmail('password_reset', $recipientEmail, array('change_link' => $change_link));
				
				$submitResponse = "A password recovery link has been sent to the email provided.";
				
			} else {
				$submitResponse = $qls->User->send_password_email_error . SEND_PASSWORD_EMAIL_TRY_AGAIN;
			}
		}
		require_once('html/request_password_change_form.php');
	} else {
	    echo CHANGE_PASSWORD_INVALID_CODE;
	}
}
?>
