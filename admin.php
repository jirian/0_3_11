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
			<h4 class="header-title m-t-0 m-b-30">SMTP Settings</h4>
			<div class="radio radio-inline">
				<input class="mailMethod" type="radio" name="mailMethod" id="mailMethodSendmail" value="sendmail" <?php echo $qls->config['mail_method'] == 'sendmail' ? 'checked' : ''; ?>>
				<label for="mailMethodSendmail">Sendmail</label>
			</div>
			<div class="radio radio-inline">
				<input class="mailMethod" type="radio" name="mailMethod" id="mailMethodSMTP" value="smtp" <?php echo $qls->config['mail_method'] == 'smtp' ? 'checked' : ''; ?>>
				<label for="mailMethodSMTP">SMTP</label>
			</div>
			<form>
				<fieldset class="form-group">
					
					<label for="smtpFromEmail">From Email</label>
					<input id="smtpFromEmail" type="text" class="form-control" value="<?php echo $qls->config['from_email']; ?>" placeholder="no-reply@example.com">
					<label for="smtpFromName">From Name</label>
					<input id="smtpFromName" type="text" class="form-control" value="<?php echo $qls->config['from_name']; ?>" placeholder="No Reply">
					<div id="fieldsSMTP">
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
			<h4 class="header-title m-t-0 m-b-30">Organization Name</h4>
			<a href="#" id="inline-orgName" data-type="text" data-pk="1" data-title="Enter organization name"><?php echo $qls->org_info['name']; ?></a>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 col-xl-6">
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
					<tbody>
					<?php
						$groupArray = array();
						$query = $qls->SQL->select('*', 'groups');
						while($groupRow = $qls->SQL->fetch_assoc($query)) {
							$groupArray[$groupRow['id']] = $groupRow;
						}
						
						$query = $qls->SQL->select('*', 'users');
						while($row = $qls->SQL->fetch_assoc($query)) {
							echo '<tr>';
							echo '<td>'.$row['username'].'</td>';
							echo '<td>Active</td>';
							if($row['mfa']) {
								echo '<td>Yes</td>';
							} else {
								echo '<td>No</td>';
							}
							if($row['id'] == $qls->user_info['id']) {
								echo '<td>'.$groupArray[$row['group_id']]['name'].'</td>';
							} else {
								echo '<td><a class="editableUserRole" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['group_id'].'" data-userType="active"></a></td>';
							}
							
							echo '<td>';
							if($row['id'] != $qls->user_info['id']) {
								echo '<button class="buttonRemoveUser btn btn-sm waves-effect waves-light btn-danger" data-userType="active" data-userID="'.$row['id'].'" type="button" title="Remove user">';
								echo '<i class="fa fa-remove"></i>';
								echo '</button>';
							}
							echo '</td>';
							echo '</tr>';
						}
						
						$query = $qls->SQL->select('*', 'invitations', array('used' => array('=', 0)));
						while($row = $qls->SQL->fetch_assoc($query)) {
							echo '<tr>';
							echo '<td>'.$row['email'].'</td>';
							echo '<td>Pending</td>';
							echo '<td><a class="editableUserRole" href="#" data-type="select" data-pk="'.$row['id'].'" data-value="'.$row['group_id'].'" data-userType="invitation"></a></td>';
							echo '<td>';
								echo '<button class="buttonRemoveUser btn btn-sm waves-effect waves-light btn-danger" data-userType="invitation" data-userID="'.$row['id'].'" type="button" title="Remove user">';
								echo '<i class="fa fa-remove"></i>';
								echo '</button>';
							echo '</td>';
							echo '</tr>';
						}
					?>
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
