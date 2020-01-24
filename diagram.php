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

<!-- X-editable css -->
<link type="text/css" href="assets/plugins/x-editable/css/bootstrap-editable.css" rel="stylesheet">

<!-- DataTables -->
<link href="assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<style>
.dataTables_wrapper .dataTables_filter {
float: right;
text-align: left;
}
</style>

<!-- Responsive datatable examples -->
<link href="assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

<?php require 'includes/header_end.php'; ?>
<?php require_once './includes/content-object_tree_modal.php'; ?>

<!-- sample modal content -->
<div id="modalPathFinder" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="myModalLabel">Find Path</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgModal"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<h4 id="pathFinderModalTitle" class="header-title m-t-0 m-b-30">Endpoints</h4>
							<div id="pathFinderTree" class="m-b-30"></div>
							<div title="Run">
								<button id="buttonPathFinderRun" class="btn btn-sm waves-effect waves-light btn-primary" type="button" disabled>
									<span class="btn-label"><i class="fa fa-cogs"></i></span>
									Find Paths
								</button>
							</div>
						</div>
					</div>
					
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Results</h4>
							<div class="table-responsive">
								<table id="cablePathTable" class="table table-striped table-bordered">
									<thead>
									<tr>
										<th>MediaType</th>
										<th>Local</th>
										<th>Adj.</th>
										<th>Path</th>
										<th>Total</th>
										<!--th></th-->
									</tr>
									</thead>
									<tbody id="cablePathTableBody">
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Path</h4>
							<div id="containerCablePath"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Make server data available to client via hidden inputs -->
<?php include_once('includes/content-build-serverData.php'); ?>

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
				<h4 class="header-title m-t-0 m-b-20">Diagram</h4>
			</div>
		</div><!-- end col -->
		
	</div>
</div>

<?php require 'includes/footer_start.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js"></script>
<script src="assets/pages/jquery.diagram.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<!-- PathSelector Plugin -->
<script type="text/javascript" src="assets/plugins/pathSelector/jquery.pathSelector.js"></script>

<!-- Required datatable js -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>

<!-- panZoom Plugin -->
<script src="assets/plugins/panzoom/jquery.panzoom.min.js"></script>
	
<?php require 'includes/footer_end.php' ?>
