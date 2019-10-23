<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('user.php');
?>

<?php require './includes/header_start.php'; ?>
<!-- DataTables -->
<link href="assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
<!--Morris Chart CSS -->
<link rel="stylesheet" href="assets/plugins/morris/morris.css">
<?php require './includes/header_end.php'; ?>


<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Dashboard</h4>
    </div>
</div>

<div class="row">
	<div class="col-xs-12 col-lg-12 col-xl-4">
        <div class="card-box">

            <h4 class="header-title m-t-0 m-b-30">Cable Inventory</h4>
			<?php
				$mediaCategoryTypeArray = array();
				$query = $qls->SQL->select('*', 'shared_mediaCategory');
				while($row = $qls->SQL->fetch_assoc($query)) {
					$mediaCategoryTypeArray[$row['value']] = $row;
				}
			?>
			<div class="form-inline">
			<select class="form-control" id="inventorySelectConnectorType">
				<?php
					$query = $qls->SQL->select('*', 'shared_cable_connectorOptions', false, array('value', 'ASC'));
					while($row = $qls->SQL->fetch_assoc($query)) {
						$selected = $row['defaultOption'] == 1 ? 'selected' : '';
						echo '<option value="'.$row['value'].'" data-categoryType="categoryType'.$mediaCategoryTypeArray[$row['category_type_id']]['value'].'" '.$selected.'>'.$row['name'].'</option>';
					}
				?>
			</select>
			
			<select class="form-control" id="inventorySelectMediaType">
				<?php
					$query = $qls->SQL->select('*', 'shared_mediaType', false, array('value', 'ASC'));
					while($row = $qls->SQL->fetch_assoc($query)) {
						$selected = $row['defaultOption'] == 1 ? 'selected' : '';
						echo '<option value="'.$row['value'].'" class="categoryType'.$mediaCategoryTypeArray[$row['category_type_id']]['value'].'" '.$selected.'>'.$row['name'].'</option>';
					}
				?>
			</select>
			</div>
            <div id="inventory-donut" style="height: 300px;"></div>

            <div class="text-xs-center">
                <ul class="list-inline chart-detail-list m-b-0">
                    <li class="list-inline-item">
                        <h6 style="color: #007bff;"><i class="zmdi zmdi-square-o m-r-5"></i>In-Use</h6>
                    </li>
                    <li class="list-inline-item">
                        <h6 style="color: #28a745;"><i class="zmdi zmdi-circle-o m-r-5"></i>Not In-Use</h6>
                    </li>
                    <li class="list-inline-item">
                        <h6 style="color: #ffc107;"><i class="zmdi zmdi-truck m-r-5"></i>Pending Delivery</h6>
                    </li>
					<li class="list-inline-item">
                        <h6 style="color: #dc3545;"><i class="zmdi zmdi-close m-r-5"></i>Dead Wood</h6>
                    </li>
                </ul>
            </div>

        </div>
    </div><!-- end col-->
	<div class="col-xs-12 col-lg-12 col-xl-8">
        <div class="card-box">

            <h4 class="header-title m-t-0 m-b-30">Port Utilization</h4>
			<div class="table-responsive">
			<table id="tableUtilization" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Object</th>
						<th>Total Ports</th>
						<th>Populated Ports</th>
						<th>Populated %</th>
					</tr>
				</thead>
				<tbody id="tableUtilizationBody">
				</tbody>
			</table>
			</div>
		</div>
    </div><!-- end col-->
</div><!-- end row -->

<div class="row">
	<div class="col-xs-12 col-lg-12 col-xl-12">
        <div class="card-box">

            <h4 class="header-title m-t-0 m-b-30">History</h4>
			<div class="table-responsive">
			<table id="tableHistory" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Datetime</th>
						<th>Function</th>
						<th>Action Type</th>
						<th>User</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody id="tableHistoryBody">
				</tbody>
			</table>
			</div>
		</div>
    </div><!-- end col-->
</div><!-- end row -->

<?php require 'includes/footer_start.php' ?>
<!-- Required datatable js -->
<script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Responsive examples -->
<script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>

<!--Morris Chart-->
<script src="assets/plugins/morris/morris.min.js"></script>
<script src="assets/plugins/raphael/raphael-min.js"></script>


<!-- Page specific js -->
<script src="assets/pages/jquery.dashboard.js"></script>
<?php require './includes/footer_end.php' ?>
