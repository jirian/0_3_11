<?php
//define('QUADODO_IN_SYSTEM', true);
//require_once('includes/header.php');
//$qls->Security->check_auth_registration();

$code = isset($_GET['code']) ? $_GET['code'] : '';

$query = $qls->SQL->select('*', 'invitations', array('code' => array('=', $code)));
if($qls->SQL->num_rows($query)) {
	$invitation = $qls->SQL->fetch_assoc($query);
	$email = $invitation['email'];
} else {
	$email = '';
}
?>
<?php require 'includes/header_account.php'; ?>
    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">

        <div class="account-bg">
            <div class="card-box m-b-0">
                <div class="text-xs-center m-t-20">
                    <a href="https://patchcablemger.com" class="logo">
                        <i class="zmdi zmdi-group-work icon-c-logo"></i>
                        <span>PatchCableMgr</span>
                    </a>
                </div>
                <div class="m-t-10 p-20">
                    <div class="row">
                        <div class="col-xs-12 text-xs-center">
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Sign Up</h6>
                        </div>
                    </div>
                    <form class="m-t-20" action="register.php" method="post">
						<input type="hidden" name="code" value="<?php echo $code; ?>" />
						<input type="hidden" name="process" value="true" />
						<input type="hidden" name="random_id" value="<?php echo $random_id; ?>" />
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" name="email" type="email" maxlength="100" required="" placeholder="<?php echo EMAIL_LABEL; ?>" maxlength="100" value="<?php if (isset($_SESSION[$qls->config['cookie_prefix'] . 'registration_email'])) { echo $_SESSION[$qls->config['cookie_prefix'] . 'registration_email']; } else { echo $email; } ?>">
                            </div>
                        </div>
						<!--
						<div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" name="email_c" type="email" maxlength="100" required="" placeholder="<?php echo EMAIL_CONFIRM_LABEL; ?>" maxlength="100" value="<?php if (isset($_SESSION[$qls->config['cookie_prefix'] . 'registration_email_confirm'])) { echo $_SESSION[$qls->config['cookie_prefix'] . 'registration_email_confirm']; } ?>">
                            </div>
                        </div>
						
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" name="username" type="text" required="" placeholder="<?php echo USERNAME_LABEL; ?>">
                            </div>
                        </div>
						-->
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" name="password" type="password" maxlength="<?php echo $qls->config['max_password']; ?>" required="" placeholder="<?php echo PASSWORD_LABEL; ?>">
                            </div>
                        </div>
						
						<div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" name="password_c" type="password" maxlength="<?php echo $qls->config['max_password']; ?>" required="" placeholder="<?php echo PASSWORD_CONFIRM_LABEL; ?>">
                            </div>
                        </div>
						
						<?php
						/* START SECURITY IMAGE */
						if ($qls->config['security_image'] == 'yes') {
						?>
							<div class="form-group row">
								<div class="col-xs-12">
									<img src="security_image.php?id=<?php echo $random_id; ?>" border="0" alt="Security Image" />
								</div>
							</div>
						
							<div class="form-group row">
								<div class="col-xs-12">
									<input class="form-control" name="security_code" type="text" maxlength="8" required="" placeholder="<?php echo SECURITY_CODE_LABEL; ?>">
								</div>
							</div>
						<?php
						}
						/* END SECURITY IMAGE */
						?>
						
						<div class="form-group row">
                            <div class="col-xs-12">
                                <div class="checkbox checkbox-primary">
                                    <input id="checkbox-signup" type="checkbox" checked="checked">
                                    <label for="checkbox-signup">I accept <a href="/user-agreement.php">Terms and Conditions</a></label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row text-center m-t-10">
                            <div class="col-xs-12">
                                <button id="buttonSubmit" class="btn btn-success btn-block waves-effect waves-light" type="submit">Join Now</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
        <!-- end card-box-->

        <div class="m-t-20">
            <div class="text-xs-center">
                <p class="text-white">Already have account? <a href="login.php" class="text-white m-l-5"><b>Sign In</b> </a></p>
            </div>
        </div>

    </div>
    <!-- end wrapper page -->



<?php require 'includes/footer_account.php'; ?>

<script type="text/javascript">
	$( document ).ready(function() {
		$('#checkbox-signup').on('change', function(){
			if($(this).is(":checked")) {
				$('#buttonSubmit').prop("disabled", false);
			} else {
				$('#buttonSubmit').prop("disabled", true);
			}
		});
	});
</script>