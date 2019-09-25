<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('administrator.php');
?>

<?php require 'includes/header_start.php'; ?>

<!-- Jquery filer css -->
<link href="assets/plugins/jquery.filer/css/jquery.filer.css" rel="stylesheet" />
<link href="assets/plugins/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css" rel="stylesheet" />
	
<?php require 'includes/header_end.php'; ?>

<div id="importModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="importModalLabel">Import</h4>
            </div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgImport"></div>
							</div>
						</div>
				</div>
			</div>
            <div class="p-20">
				<div class="form-group clearfix">
					<div class="col-sm-12 padding-left-0 padding-right-0">
						<input type="file" name="files[]" id="fileDataImport" multiple="multiple">
					</div>
				</div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Admin - Integration</h4>
    </div>
</div>

<div class="row">
	
	<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6 col-xl-4">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Manage Data</h4>
			<button id="buttonDataExport" type="button" class="btn btn-success waves-effect waves-light">
				<span class="btn-label"><i class="fa fa-upload"></i>
				</span>Export
			</button>
			<button id="buttonDataImport" type="button" class="btn btn-danger waves-effect waves-light" data-toggle="modal" data-target="#importModal">
				<span class="btn-label"><i class="fa fa-download"></i>
				</span>Import
			</button>
			<fieldset class="form-group m-t-10">
				<div class="inputBlock">
					<div class="radio">
						<input class="importTypeRadio" type="radio" name="importType" id="importEdit" value="Edit" checked>
							<label for="importEdit"><span>Import - Edit</span></label>
					</div>
					<div class="radio">
						<input class="importTypeRadio" type="radio" name="importType" id="importRestore" value="Restore">
							<label for="importRestore"><span>Import - Restore</span></label>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<!-- Jquery filer js -->
<script src="assets/plugins/jquery.filer/js/jquery.filer.min.js"></script>

<script src="assets/pages/jquery.admin-integration.js"></script>

<?php require 'includes/footer_end.php' ?>
