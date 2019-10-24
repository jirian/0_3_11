<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('operator.php');
?>

<?php require 'includes/header_start.php'; ?>

    <!-- DataTables -->
    <link href="assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <!-- Responsive datatable examples -->
    <link href="assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

<?php require 'includes/header_end.php'; ?>


<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Cable Inventory</h4>
    </div>
</div>

<div class="row">
	<div class="col-xs-12">
			<div class="row">
				<div class="col-xs-12">
					<div class="card-box">
						<h4 class="header-title m-t-0">Initialized Cables</h4>
						<p class="text-muted m-b-30 font-13">
                            Cables that have been initialized or purchased through PatchCableMgr.
                        </p>
						<div class="table-responsive">
							<?php require_once './includes/content-cable_inventory.php';?>
						</div>
					</div>
					<div class="card-box">
						<h4 class="header-title m-t-0">Uninitialized Cable End IDs</h4>
						<p class="text-muted m-b-30 font-13">
                            Cable end IDs that are available to be applied to any patch cable and scanned into your PatchCableMgr inventory.
                        </p>
						
						<div class="table-responsive">
							<?php require_once './includes/content-available_user_ids.php';?>
						</div>
					</div>
				</div>
			</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>

<!-- Required datatable js -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Responsive examples -->
<script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

<script src="assets/plugins/barcode/jquery.barcode.min.js"></script>
<script src="assets/pages/jquery.cable_manager.js"></script>

<?php require 'includes/footer_end.php' ?>
