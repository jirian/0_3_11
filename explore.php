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
<link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

<!-- ION Slider -->
<link href="assets/plugins/ion-rangeslider/ion.rangeSlider.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/ion-rangeslider/ion.rangeSlider.skinModern.css" rel="stylesheet" type="text/css"/>

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
							<div class="row">
								<div class="col-sm-6">
									<dl class="dl-horizontal row">
										<dt class="col-sm-3">Local Port:</dt>
										<dd id="pathFinderLocalPort" class="col-sm-9"></dd>
										<dt class="col-sm-3">Remote Port:</dt>
										<dd class="col-sm-9"><div id="pathFinderRemotePort"></div><div id="pathFinderTree" class="navTree m-b-30"></div></dd>
									</dl>
								</div>
								<div class="col-sm-6">
									<?php
									echo '<input id="pathFinderMaxResults" type="hidden" value="'.PATH_FINDER_MAX_RESULTS.'">';
									echo '<input id="pathFinderMaxResultsDefault" type="hidden" value="'.PATH_FINDER_MAX_RESULTS_DEFAULT.'">';
									echo '<input id="pathFinderMaxDepth" type="hidden" value="'.PATH_FINDER_MAX_DEPTH.'">';
									echo '<input id="pathFinderMaxDepthDefault" type="hidden" value="'.PATH_FINDER_MAX_DEPTH_DEFAULT.'">';
									?>
									<form class="form-horizontal">
										<div class="form-group row">
											<label for="rangeResults" class="col-sm-2 control-label"><b>Max Results</b><i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Maximum number of paths to return for each media type."></i></label>
											<div class="col-sm-10">
												<input type="text" id="rangeResults">
											</div>
										</div>
										<div class="form-group row">
											<label for="rangeDepth" class="col-sm-2 control-label"><b>Max Depth</b><i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Maximum number of cable jumpers for each path returned."></i></label>
											<div class="col-sm-10">
												<input type="text" id="rangeDepth">
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="row">
								<div title="Run">
									<button id="buttonPathFinderRun" class="btn btn-sm waves-effect waves-light btn-primary" type="button" disabled>
										<span class="btn-label"><i class="fa fa-cogs"></i></span>
										Find Paths
									</button>
								</div>
							</div>
						</div>
					</div>
					
				</div>
				<div class="row">
					<div class="col-sm-6">
						<div class="card-box">
							<div class="card">
								<div class="card-header">Path
								</div>
								<div class="card-block">
									<blockquote class="card-blockquote">
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
									</blockquote>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="card-box">
							<div class="card">
								<div class="card-header">Path
									<span>
										<div class="btn-group pull-right">
											<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
											<div class="dropdown-menu">
												<a id="printPathFinder" class="dropdown-item" href="#" ><i class="ion-map"></i></span> Print</a>
											</div>
										</div>
									</span>
								</div>
								<div class="card-block">
									<blockquote class="card-blockquote">
										<div id="containerCablePath"></div>
									</blockquote>
								</div>
							</div>
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
<canvas id="canvasBuildSpace" style="z-index:1000;position:absolute; pointer-events:none;"></canvas>

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <h4 class="page-title">Explore - Cabinet</h4>
    </div>
</div>

<div class="row">
	<div class="col-xs-12">
		<div class="col-md-4">
			<div class="card-box">
				<h4 class="header-title m-t-0 m-b-20">Locations and Cabinets</h4>
				<div class="card">
					<div class="card-header">Location Tree</div>
					<div class="card-block">
						<div class="card-blockquote">
							<div id="ajaxTree" class="navTree"></div>
						</div>
					</div>
				</div>
				
				<div id="floorplanDetails" style="display:none;">
					<div class="card">
						<div class="card-header">Object</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Name:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="floorplanDetailName">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="floorplanDetailType">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Trunked:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="floorplanDetailTrunkedTo">-</span>
										</td>
									</tr>
								</table>
							</blockquote>
						</div>
					</div>
					<div id="portAndPathContainerFloorplan">
					</div>
					<div class="card">
						<div class="card-header">Object List</div>
						<div class="card-block">
							<blockquote class="card-blockquote">
								<div id="floorplanObjectTableContainer"></div>
							</blockquote>
						</div>
					</div>
				</div>
			</div>
		</div><!-- end col -->
		
		
		<div class="col-md-8">
			<div id="rowFloorplan" class="row" style="display: none;">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<div class="card-box" style="min-height:500px;">
						<h4 class="header-title m-t-0">Floorplan</h4>
						<div class="noSelect">
							<i id="btnZoomOut" class="fa fa-search-minus fa-2x cursorPointer"></i>
							<i id="btnZoomIn" class="fa fa-search-plus fa-2x cursorPointer"></i>
							<i id="btnZoomReset" class="fa fa-refresh fa-2x cursorPointer"></i>
							<img id="imgDummy" style="display:none;"></img>
							<div id="floorplanWindow">
								<div id="floorplanContainer" style="position:relative;background-repeat:no-repeat"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="rowCabinet" class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
					<div class="card-box" style="min-height:500px;">
						<h4 class="header-title m-t-0">Cabinet</h4>
						<div class="form-inline m-t-0 m-b-30">
							<div class="radio radio-inline">
								<input class="sideSelectorCabinet" type="radio" name="sideSelectorCabinet" id="sideSelectorCabinetFront" value="0" checked>
								<label for="sideSelectorCabinetFront">Front</label>
							</div>
							<div class="radio radio-inline">
								<input class="sideSelectorCabinet" type="radio" name="sideSelectorCabinet" id="sideSelectorCabinetBack" value="1">
								<label for="sideSelectorCabinetBack">Back</label>
							</div>
							<div class="pull-right">
								<label>View:</label>
								<select id="selectCabinetView" class="form-control">
									<option value="name">Name</option>
									<option value="port" selected>Port</option>
									<option value="visual">Visual</option>
								</select>
							</div>
						</div>
						
						<div id="buildSpaceContent">Please select a cabinet from the Environment Tree.</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
					<div class="card-box">
						<h4 class="header-title m-t-0">Selection Details</h4>
						<div id="objectCardBox" class="card">
							<div class="card-header">Object</div>
							<div class="card-block">
								<blockquote class="card-blockquote">
									<div id="detailsContainer">
										<table>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Object Name:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailObjName" class="objDetail"><a href="#" id="inline-objName" data-type="text">-</a></span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Template Name:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailTemplateName" class="objDetail"><a href="#" id="inline-templateName" data-type="text">-</a></span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Category:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailCategory" class="objDetail"><a href="#" id="inline-category" data-type="select">-</a></span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight" valign="top">
													<strong>Trunked To:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailTrunkedTo" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Type:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailObjType" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Function:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailObjFunction" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>RU Size:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailRUSize" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Mount Config:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailMountConfig" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Port Range:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailPortRange" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Port Orientation:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailPortOrientation" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Port Type:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailPortType" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight">
													<strong>Media Type:&nbsp&nbsp</strong>
												</td>
												<td>
													<span id="detailMediaType" class="objDetail">-</span>
												</td>
											</tr>
											<tr>
												<td class="objectDetailAlignRight" valign="top">
													<strong>Image:&nbsp&nbsp</strong>
												</td>
												<td width="100%">
													<span id="detailTemplateImage" class="objDetail">-</span>
												</td>
											</tr>
										</table>
									</div>
								</blockquote>
							</div>
						</div>
						<div id="portAndPathContainerCabinet">
							<div id="portAndPath">
								<div id="portCardBox" class="card">
									<div class="card-header">Port
										<span>
											<div class="btn-group pull-right">
												<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
												<div class="dropdown-menu">
													<?php if($qls->user_info['group_id'] <= 4) { ?>
													<a id="buttonPortConnector" class="dropdown-item disabled" href="#" data-modalTitle="Connect Ports"><i class="zmdi zmdi-my-location"></i> Connect Port</a>
													<a id="buttonObjectTreeModalClear" class="dropdown-item disabled" href="#"><i class="fa fa-ban"></i> Clear Port</a>
													<?php } ?>
													<a id="buttonPathFinder" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalPathFinder"><i class="ion-map"></i> Find Path</a>
												</div>
											</div>
										</span>
									</div>
									<div class="card-block">
										<blockquote class="card-blockquote">
											
											<select class="form-control m-b-10" id="selectPort" disabled></select>
											<div class="checkbox">
												<input id="checkboxPopulated" type="checkbox" disabled>
												<label for="checkboxPopulated">Populated</label>
											</div>
											
										</blockquote>
									</div>
								</div>
								<div id="pathCardBox" class="card">
									<div class="card-header">Path
										<span>
											<div class="btn-group pull-right">
												<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
												<div class="dropdown-menu">
													<a id="printFullPath" class="dropdown-item" href="#"><i class="ion-map"></i> Print</a>
												</div>
											</div>
										</span>
									</div>
									<div class="card-block">
										<blockquote class="card-blockquote">
											<div class="row">
												<div id="containerFullPath"></div>
											</div>
										</blockquote>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js"></script>
<script src="assets/pages/jquery.explore.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<!-- Required datatable js -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js"></script>

<!-- panZoom Plugin -->
<script src='assets/plugins/panzoom2/panzoom.js'></script>

<!-- printThis Plugin -->
<script src="assets/plugins/printThis/printThis.js"></script>

<!-- draw connections functions -->
<script src="assets/pages/jquery.drawConnections.js"></script>

<!-- range slider js -->
<script src="assets/plugins/ion-rangeslider/ion.rangeSlider.min.js"></script>
<script src="assets/pages/jquery.ui-sliders.js"></script>
	
<?php require 'includes/footer_end.php' ?>
