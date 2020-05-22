<?php
/* DO NOT REMOVE */
if (!defined('QUADODO_IN_SYSTEM')) {
exit;
}
/*****************/
?>
<?php require 'includes/header_account.php'; ?>

    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">

        <div class="account-bg">
            <div class="card-box m-b-0">
                <div class="text-xs-center m-t-20">
                    <a href="https://patchcablemgr.com" class="logo">
						<img src="/assets/images/logo.png" style="display:inline;" height="37" width="50">
                        <!--i class="zmdi zmdi-group-work icon-c-logo"></i-->
                        <span>PatchCableMgr</span>
                    </a>
                </div>
                <div class="m-t-10 p-20">
                    <div class="row">
                        <div class="col-xs-12 text-xs-center">
                            <h6 class="text-muted text-uppercase m-b-0 m-t-0">Sign In</h6>
							<?php
							if(isset($_GET['d'])) {
							?>
							<br>
							<p style="text-align:left;" class="m-b-0">
								<strong>Username:</strong>&nbsp;&nbsp;demo@patchcablemgr.com
								<br>
								<strong>Password:</strong>&nbsp;&nbsp;demo
							</p>
							<?php
							}
							?>
                        </div>
                    </div>
                    <form class="m-t-20" action="login_process.php" method="post">
						<input type="hidden" name="process" value="true" />

                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" name="username" maxlength="<?php echo $qls->config['max_username']; ?>" placeholder="<?php echo USERNAME_LABEL; ?>" autofocus>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" required="" name="password" maxlength="<?php echo $qls->config['max_password']; ?>" placeholder="<?php echo PASSWORD_LABEL; ?>">
                            </div>
                        </div>
						
                        <div class="form-group text-center row m-t-10">
                            <div class="col-xs-12">
									<button class="btn btn-success btn-block waves-effect waves-light" type="submit">Log In</button>
                            </div>
                        </div>

                        <div class="form-group row m-t-30 m-b-0">
                            <div class="col-sm-12">
                                <a href="change_password.php" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your password?</a>
                            </div>
                        </div>
						
						<?php
						if(isset($_GET['f'])) {
						?>
								<br />
								<span style="color:#ff524a;">
						<?php
							switch ($_GET['f']) {
								default:
									break;
								case 0:
									echo LOGIN_NOT_ACTIVE_USER;
									break;
								case 1:
									echo LOGIN_USER_BLOCKED;
									break;
								case 2:
									echo LOGIN_PASSWORDS_NOT_MATCHED;
									break;
								case 3:
									echo LOGIN_NO_TRIES;
									break;
								case 4:
									echo LOGIN_USER_INFO_MISSING;
									break;
								case 5:
									echo LOGIN_NOT_ACTIVE_ADMIN;
									break;
								case 6:
									echo LOGIN_NOT_ACTIVE_ADMIN;
									break;
								case 7:
									echo LOGIN_MFA_FAILURE_ADMIN;
									break;
							}
						?>
								</span>
						<?php
						} else if(isset($_GET['s'])) {
						?>
								<br />
								<span style="color:#52ff4a;">
						<?php
							switch ($_GET['s']) {
								default:
									break;
								case 0:
									echo REGISTER_SUCCESS;
									break;
							}
						?>
								</span>
						<?php
						}
						?>
                    </form>

                </div>

                <div class="clearfix"></div>
            </div>
        </div>
        <!-- end card-box-->

    </div>
    <!-- end wrapper page -->


<?php require 'includes/footer_account.php'; ?>
