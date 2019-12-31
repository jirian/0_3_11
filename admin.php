<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('administrator.php');
?>

<?php require 'includes/header_start.php'; ?>

<!-- X-editable css -->
<link type="text/css" href="assets/plugins/x-editable/css/bootstrap-editable.css" rel="stylesheet">

<?php require 'includes/header_end.php'; ?>

<div id="removeUserModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="removeUserModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="removeUserModalLabel">Remove User</h4>
            </div>
            <div class="modal-body">
                Delete: username?
            </div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary waves-effect waves-light">Ok</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="cancelEntitlementModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="cancelEntitlementModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="cancelEntitlementModalLabel">Cancel Entitlement</h4>
            </div>
            <div class="modal-body">
                Confirm cancelation.
            </div>
			<div class="modal-footer">
				<button id="confirmEntitlementCancellation" type="button" class="btn btn-secondary btn-danger waves-effect" data-toggle="modal" data-target="#cancelEntitlementModal">Confirm</button>
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
			</div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Admin - General</h4>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
		<div class="row">
			<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
				<div id="alertMsg"></div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-3">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Invite User</h4>
			<form>
				<fieldset class="form-group">
					<label for="invitationEmail">Email address</label>
					<input id="invitationEmail" type="email" class="form-control" placeholder="first.last@example.com">
				</fieldset>
				<button id="invitationSubmit" type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
	</div>
	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-3">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Email Settings</h4>
			<div class="radio radio-inline">
				<input class="mailMethod" type="radio" name="mailMethod" id="mailMethodProxy" value="proxy" <?php echo $qls->config['mail_method'] == 'proxy' ? 'checked' : ''; ?>>
				<label for="mailMethodProxy">PCM Proxy</label>
			</div>
			<div class="radio radio-inline">
				<input class="mailMethod" type="radio" name="mailMethod" id="mailMethodSMTP" value="smtp" <?php echo $qls->config['mail_method'] == 'smtp' ? 'checked' : ''; ?>>
				<label for="mailMethodSMTP">SMTP</label>
			</div>
			<form>
				<fieldset class="form-group">
					<div id="fieldsSMTP">
						<label for="smtpFromEmail">From Email</label>
						<input id="smtpFromEmail" type="text" class="form-control" value="<?php echo $qls->config['from_email']; ?>" placeholder="no-reply@example.com">
						<label for="smtpFromName">From Name</label>
						<input id="smtpFromName" type="text" class="form-control" value="<?php echo $qls->config['from_name']; ?>" placeholder="No Reply">
						<label for="smtpServer">Server</label>
						<input id="smtpServer" type="text" class="form-control" value="<?php echo $qls->config['smtp_server']; ?>" placeholder="smtp.example.com">
						<label for="smtpPort">Port</label>
						<input id="smtpPort" type="text" class="form-control" value="<?php echo $qls->config['smtp_port']; ?>" placeholder="25">
						<input id="smtpAuthentication" type="checkbox" name="smtpAuthentication" <?php echo $qls->config['smtp_auth'] == 'yes' ? 'checked' : ''; ?>>
						<label for="smtpAuthentication">SMTP Authentication</label>
						<div id="fieldsSMTPAuth">
							<label for="smtpUsername">Username</label>
							<input id="smtpUsername" type="text" class="form-control" value="<?php echo $qls->config['smtp_username']; ?>" placeholder="smtp.user@example.com">
							<label for="smtpPassword">Password</label>
							<input id="smtpPassword" type="password" class="form-control" placeholder="">
						</div>
					</div>
				</fieldset>
				<button id="smtpSubmit" type="submit" class="btn btn-primary">Submit</button>
			</form>
		</div>
	</div>

	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-3">
		<div class="card-box">
			
			<h4 class="header-title m-t-0 m-b-30">App Settings</h4>
			<fieldset class="form-group">
				<label>Organization Name</label>
				<div>
					<a href="#" id="inline-orgName" data-type="text" data-pk="1"><?php echo $qls->org_info['name']; ?></a>
				</div>
			</fieldset>
			<fieldset class="form-group">
				<label>Server Name</label>
				<div>
					<a href="#" id="inline-serverName" data-type="text" data-pk="1"><?php echo $qls->config['cookie_domain']; ?></a>
				</div>
			</fieldset>
			<fieldset class="form-group">
				<mark class="m-t-15"><strong>Warning:</strong>
				<br>Server Name must be a DNS resolvable hostname or IP address that can be used to navigate to the app server.
				<br>If this is incorrect, you may be locked out.  Contact support@patchcablemgr.com.
				</mark>
			</fieldset>
		</div>
	</div>
	
	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-3">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Entitlement</h4>
			<fieldset class="form-group">
				<label>Entitlement ID</label>
				<div style="overflow:hidden;">
					<a href="#" id="inline-entitlement" data-type="text" data-pk="1" data-title="Enter entitlement ID"><?php echo $qls->App->entitlementArray['id']; ?></a>
				</div>
			</fieldset>
			<fieldset class="form-group">
				<dl class="dl-horizontal row">
					<dt class="col-sm-6 text-truncate">Last Checked:</dt>
					<dd class="col-sm-6" id="entitlementLastChecked"><?php echo $qls->App->entitlementArray['lastCheckedFormatted'];?></dd>
					<dt class="col-sm-6 text-truncate">Expires:</dt>
					<dd class="col-sm-6" id="entitlementExpiration"><?php echo $qls->App->entitlementArray['expirationFormatted'];?></dd>
					<dt class="col-sm-6">Status:</dt>
					<dd class="col-sm-6" id="entitlementStatus"><?php echo $qls->App->entitlementArray['status'];?></dd>
					<div id="entitlementEntitlementData">
					<?php
						foreach($qls->App->entitlementArray['data'] as $attribute) {
							print('<dt class="col-sm-6">'.$attribute['friendlyName'].':</dt>');
							$count = $attribute['count'] ? $attribute['count'] : 'Unl.';
							print('<dd class="col-sm-6">'.$count.' ('.$attribute['used'].' used)</dd>');
						}
					?>
					</div>
				</dl>
			</fieldset>
			
			<div>
				<button id="entitlementCheck" type="button" class="btn btn-sm btn-primary waves-effect waves-light m-t-10">
					<span class="btn-label"><i class="fa fa-check"></i></span>Check
				</button>
			</div>
			
			<div>
				<button id="entitlementCancel" type="button" class="btn btn-sm btn-danger waves-effect waves-light m-t-10" data-toggle="modal" data-target="#cancelEntitlementModal" >
					<span class="btn-label"><i class="fa fa-times"></i></span>Cancel
				</button>
			</div>
			
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 col-xl-12">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Manage Users</h4>
			<div class="p-20">
				<table class="table table-sm">
					<thead>
					<tr>
						<th>User</th>
						<th>Status</th>
						<th>2FA</th>
						<th>Role</th>
						<th></th>
					</tr>
					</thead>
					<tbody id="tableInvitations">
					
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<script src="assets/pages/jquery.admin.js"></script>

<!-- Modal-Effect -->
<script src="assets/plugins/custombox/js/custombox.min.js"></script>
<script src="assets/plugins/custombox/js/legacy.min.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<?php require 'includes/footer_end.php' ?>
