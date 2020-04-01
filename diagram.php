<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('user.php');
?>

<?php require 'includes/header_start.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/themes/default/style.min.css" />
<link href="assets/css/style-cabinet.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/style-object.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/style-templates.css" rel="stylesheet" type="text/css"/>

<?php require 'includes/header_end.php'; ?>

<!-- object tree modal -->
<div id="objectTreeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="objectTreeModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="objectTreeModalLabel"></h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgObjTree" class="m-t-15"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div id="objTree" class="navTree"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="buttonObjectTreeModalCancel" type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
				<button id="buttonObjectTreeModalAdd" type="button" class="btn btn-primary waves-effect waves-light">Add</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Make server data available to client via hidden inputs -->
<?php include_once('includes/content-build-serverData.php'); ?>
<canvas id="canvasBuildSpace" style="z-index:1000;position:absolute; pointer-events:none;"></canvas>

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Explore - Diagram</h4>
    </div>
</div>

<div class="row">
	<div class="col-xs-12">
		<div class="col-md-12">
			<div class="card-box">
			
				<h4 class="header-title m-t-0 m-b-20">Diagram
				<div class="btn-group pull-right">
					<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
					<div class="dropdown-menu">
						<a id="btnAddCabinet" class="dropdown-item" href="#"><i class="fa fa-plus"></i> Add Cabinet</a>
					</div>
				</div>
				</h4>
				<!--
				<button id="btnAddCabinet" type="button" class="m-b-10 btn btn-sm btn-primary waves-effect waves-light">
				<span class="btn-label"><i class="fa fa-plus"></i>
				</span>Add Cabinet</button>
				-->
				<div id="buildSpaceContent"></div>
			</div>
		</div><!-- end col -->
		
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<!-- jsTree Plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js"></script>

<script src="assets/pages/jquery.diagram.js"></script>

<!-- draw connections functions -->
<script src="assets/pages/jquery.drawConnections.js"></script>
	
<?php require 'includes/footer_end.php' ?>
