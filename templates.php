<?php
define('QUADODO_IN_SYSTEM', true);
require_once './includes/header.php';
require_once './includes/redirectToLogin.php';
$qls->Security->check_auth_page('operator.php');
require_once './includes/content-templates.php';
?>

<?php require 'includes/header_start.php'; ?>

<link href="assets/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css" rel="stylesheet"/>
<link href="assets/css/style-templates.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/style-cabinet.css" rel="stylesheet" type="text/css"/>
<link href="assets/css/style-object.css" rel="stylesheet" type="text/css"/>
<link href="assets/plugins/spectrum/css/spectrum.css" rel="stylesheet">
<link type="text/css" href="assets/plugins/x-editable/css/bootstrap-editable.css" rel="stylesheet">

<!-- Jquery filer css -->
<link href="assets/plugins/jquery.filer/css/jquery.filer.css" rel="stylesheet" />
<link href="assets/plugins/jquery.filer/css/themes/jquery.filer-dragdropbox-theme.css" rel="stylesheet" />

<?php require 'includes/header_end.php'; ?>

<!-- port name modal -->
<div id="portNameModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="portNameModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title">Port ID</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgPortName" class="m-t-15"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
						
							<fieldset class="m-b-20">
								<label>Port ID Fields:</label>
								<div id="portNameFieldContainer" class="row"></div>
							</fieldset>
							
							<fieldset class="m-b-20">
								<button id="buttonAddPortNameField" type="button" class="btn btn-success waves-effect waves-light">
								   <span class="btn-label"><i class="fa fa-plus"></i>
								   </span>Add Field
							   </button>
							   <button id="buttonDeletePortNameField" type="button" class="btn btn-danger waves-effect waves-light">
								   <span class="btn-label"><i class="fa fa-minus"></i>
								   </span>Remove Field
							   </button>
						   </fieldset>
						   
							<fieldset class="m-b-20">
								<label>Field Properties:</label>
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameType">
												<select id="selectPortNameFieldType" class="form-control">
													<option value="static">Static</option>
													<option value="incremental">Incremental</option>
													<option value="series">Series</option>
												</select>
											</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Count:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameCount">
												<input  id="inputPortNameFieldCount" class="form-control" type="number" min="0">
											</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Order:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailPortNameOrder">
												<select  id="selectPortNameFieldOrder" class="form-control">
													<option value="1">1st</option>
													<option value="2">2nd</option>
												</select>
											</span>
										</td>
									</tr>
								</table>
							</fieldset>
							
							<fieldset class="m-b-20">
								<label>Results:</label><br>
								<div id="portNameDisplayConfig"></div>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="buttonPortNameModalClose" type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- template category modal -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modelLabelTemplateCategory" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modelLabelTemplateCategory">Manage Categories</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgCategory"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Create/Edit</h4>
							<form id="manageCategories-Form">
								<input id="inputCategoryID" type="hidden" name="id" value="0">
								<input type="hidden" name="action" value="add">
								<fieldset class="m-b-20">
									<label>Name</label>
									<input id="inputCategoryName" type="text" name="name" placeholder="Category Name" class="form-control">
								</fieldset>
								<fieldset class="m-b-20">
									<label>Category Color</label>
									<br><input id="color-picker" type='text' name='color'>
								</fieldset>
								<fieldset class="m-b-20">
									<div class="checkbox">
										<input id="inputCategoryDefault" type="checkbox" name="defaultOption">
										<label for="inputCategoryDefault">
											Default
										</label>
									</div>
								</fieldset>
								<fieldset>
									<button id="manageCategories-Save" type="button" class="btn btn-success waves-effect waves-light">
										<span class="btn-label"><i class="fa fa-check"></i></span>
										Save
									</button>
								</fieldset>
							</form>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Current</h4>
							<form id="manageCategoriesCurrent-Form">
								<input id="inputCategoryCurrentID" type="hidden" name="id" value="0">
								<input type="hidden" name="action" value="delete">
								<fieldset>
									<div id="categoryList">
									<?php echo $categoryList; ?>
									</div>
								</fieldset>
								<fieldset class="m-t-20">
									<button id="manageCategories-Delete" type="button" class="btn btn-danger waves-effect waves-light">
										<span class="btn-label"><i class="fa fa-times"></i></span>
										Delete
									</button>
								</fieldset>
							</form>
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

<!-- template catalog modal -->
<div id="modalTemplateCatalog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelTemplateCatalog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelTemplateCatalog">Template Catalog</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
						<div class="row">
							<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
								<div id="alertMsgCatalog"></div>
							</div>
						</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Available Templates</h4>
							
							<div id="templateCatalogAvailableContainer">
								<h6>Name Filter:</h6>
								<select id="templateCatalogFilter" multiple data-role="tagsinput"></select>
								<div id="containerTemplateCatalog"></div>
							</div>
							<p class="m-t-10">
								<mark>Don't see what you're looking for?</mark><br>
								Email <ins>support@patchcablemgr.com</ins> with a link or description<br>
								of the item and we'll add it to the catalog.
							</p>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6">
						<div class="card-box">
							<h4 class="header-title m-t-0 m-b-30">Selected Template</h4>
							<button id="buttonTemplateCatalogImport" type="button" class="btn btn-primary waves-effect waves-light m-b-10" disabled>
								<span class="btn-label"><i class="fa fa-plus"></i>
							</span>Import</button>
							<div id="detailsContainer">
								<table>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Object Name:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjName" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Template Name:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogTemplateName" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Category:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogCategory" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight" valign="top">
											<strong>Trunked To:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogTrunkedTo" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Function:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogObjFunction" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>RU Size:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogRUSize" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Mount Config:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogMountConfig" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Port Range:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogPortRange" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Port Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogPortType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Media Type:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogMediaType" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
									<tr>
										<td class="objectDetailAlignRight">
											<strong>Image:&nbsp&nbsp</strong>
										</td>
										<td>
											<span id="detailTemplateCatalogImage" class="objTemplateCatalogDetail">-</span>
										</td>
									</tr>
								</table>
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

<!-- image upload modal -->
<div id="modalImageUpload" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelImageUpload" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelImageUpload">Template Image</h4>
			</div>
			<div class="row">
				<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
					<div class="row">
						<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
							<div id="alertMsgImageUpload"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="card-box">
							<div class="p-20">
								<div class="form-group clearfix">
									<div id="containerTemplateImage" class="col-sm-12 padding-left-0 padding-right-0">
										<input type="file" name="files[]" id="fileTemplateImage" multiple="multiple">
									</div>
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

<!-- delete template modal -->
<div id="modalTemplateDeleteConfirm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabelTemplateDeleteConfirm" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLabelTemplateDeleteConfirm">Delete Template</h4>
			</div>
			<div class="modal-body">
				Delete <strong id="deleteTemplateName"></strong>?
			</div>
			<div class="modal-footer">
				<button id="confirmObjDelete" type="button" class="btn btn-secondary btn-danger waves-effect" data-toggle="modal" data-target="#modalTemplateDeleteConfirm">Confirm</button>
				<button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Cancel</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- image upload modal -->
<div id="modalTemplateWhereUsed" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLableTemplateWhereUsed" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
				<h4 class="modal-title" id="modalLableTemplateWhereUsed">Where Used</h4>
			</div>
			<div class="modal-body col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
				<h4 id="whereUsedTemplateName"></h4>
				<div id="whereUsedResults" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-xl-6"></div>
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
        <h4 class="page-title">Build - Templates</h4>
    </div>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="card-box">
			<h4 class="header-title m-t-0 m-b-30">Properties</h4>
			<input id="inputSideCount" type="hidden" name="sideCount" value="0">
			<input id="inputFrontImage" type="hidden" name="frontImage" value="">
			<input id="inputRearImage" type="hidden" name="frontImage" value="">
		
			<!-- Name -->
			<fieldset class="form-group">
				<label>Name <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Alphanumeric characters as well as hyphens (-), underscores (_), forward slashes (\/), and backslashes (\)."></i></label>
				<input id="inputName" class="form-control" type="text" name="name" placeholder="New_Template" value="New_Template">
			</fieldset>
			
			<!-- Category -->
			<fieldset class="form-group">
				<label>Category <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Group and color code objects."></i></label>
				<div class="input-group" style="display:flex;">
					<!-- Category select input -->
					<div>
					<select id="inputCategory" name="category" class="form-control">
						<?php $qls->App->generateCategoryOptions(); ?>
					</select>
					</div>
					
					<!-- Category edit button -->
					<div style="margin:auto 0px auto 5px">
						<button class="btn btn-sm waves-effect waves-light btn-primary" data-toggle="modal" type="button" data-target="#myModal">
							<i class="zmdi zmdi-edit"></i>
						</button>
					</div>
				</div>
				
			</fieldset>
			
			<!-- Object Type -->
			<fieldset id="objectType" class="form-group">
				<label>Template Type <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" data-html="true" title="Standard: Typical 19&quot; rack mounted equipment (switch, router, server, etc.) Insert: Modular template nested inside of standard template enclosure partitions (network module, fiber cassette, etc.)"></i></label>
				<div class="inputBlock">
					<div class="radio">
						<input class="objectType" type="radio" name="objectTypeRadio" id="objectTypeStandard" value="Standard" checked>
							<label for="objectTypeStandard">Standard</label>
					</div>
					<div class="radio">
						<input class="objectType" type="radio" name="objectTypeRadio" id="objectTypeInsert" value="Insert">
							<label for="objectTypeInsert">Insert</label>
					</div>
				</div>
			</fieldset>
			
			<!-- RU Size -->
			<fieldset id="objectRUSize" class="dependantField objectType standard form-group">
				<label>Template Size <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Size of template in rack units."></i></label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">RU:</div>
					<input style="position:absolute; left:40px;" id="inputRU" name="RUSize" type="number" min="1" max="25" value="1"/>
				</div>
			</fieldset>
			
			<!-- Object Function -->
			<fieldset id="objectFunction" class="dependantField objectType standard form-group">
				<label>Template Function <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Endpoint: Terminates layer1 (switch, router, server, etc.) Passive: Layer1 only (patch panel, fiber cassette, etc.)"></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="objectFunction" type="radio" name="objectFunction" id="inputObjectFunctionPassive" value="Endpoint" checked>
						<label for="inputObjectFunctionPassive">Endpoint</label>
					</div>
					<div class="radio">
						<input class="objectFunction" type="radio" name="objectFunction" id="inputObjectFunctionEndpoint" value="Passive">
						<label for="inputObjectFunctionEndpoint">Passive</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Mounting Configuration -->
			<fieldset id="objectMountConfig" class="dependantField objectType standard form-group">
				<label>Mounting Configuration <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Template depth."></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="sideCount" type="radio" name="sideCount" id="inputSideCount2Post" value="0" checked>
						<label for="inputSideCount2Post">2-Post</label>
					</div>
					<div class="radio">
						<input class="sideCount" type="radio" name="sideCount" id="inputSideCount4Post" value="1">
						<label for="inputSideCount4Post">4-Post</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Partition Type -->
			<fieldset id="objectPartitionType" class="form-group">
				<label>Partition Type <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Generic: Serves to space and size neighboring and nested partitions.  Connectable: Contains ports and interfaces.  Enclosure: Contains insert objects."></i></label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeGeneric" value="Generic" checked>
						<label for="inputPartitionTypeGeneric">Generic</label>
					</div>
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeConnectable" value="Connectable">
						<label for="inputPartitionTypeConnectable">Connectable</label>
					</div>
					<div class="radio">
						<input class="partitionType" type="radio" name="partitionType" id="inputPartitionTypeEnclosure" value="Enclosure">
						<label for="inputPartitionTypeEnclosure">Enclosure</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Custom Add/Remove Partition -->
			<fieldset id="objectPartitionAddRemove" class="form-group">
				<label>Add/Remove Partition <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Horizontal: Adds a partition inside the one selected spanning the entire width and grows down.  Vertical: Adds a partition inside the one selected spanning the entire height and grows to the right."></i></label>
				<div class="inputBlock">
					<div class="radio">
						<input type="radio" id="partitionH" class="partitionAxis" value="h" name="customPartitionAxis">
						<label for="partitionH"> Horizontal </label>
					</div>
					<div class="radio">
						<input type="radio" id="partitionV" class="partitionAxis" value="v" name="customPartitionAxis" checked>
						<label for="partitionV"> Vertical </label>
					</div>
					<button id="customPartitionAdd" class="btn btn-sm waves-effect waves-light btn-primary" type="button"> <i class="fa fa-plus"></i> </button>
					<button id="customPartitionRemove" class="btn btn-sm waves-effect waves-light btn-danger disabled" type="button" disabled> <i class="fa fa-remove"></i> </button>
				</div>
			</fieldset>
			
			<!-- Custom Partition Size -->
			<fieldset id="objectPartitionSize" class="form-group">
				<label>Partition Size <i class="ion-help-circled" data-toggle="tooltip" data-placement="right" title="Horizontal partitions are sized in half RU increments.  Vertical partitions are sized in 1/24th increments."></i></label>
				<div class="inputBlock" >
				<input id="inputCustomPartitionSize" name="customSectionSize" type="number" step="0.5" min="0.5" max="1" value="0.5" disabled />
				</div>
			</fieldset>
			
			<!-- Enclosure Layout -->
			<fieldset id="objectEnclosureLayout" class="dependantField partitionType enclosureField form-group">
				<label>Enclosure Layout</label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">Col:</div>
					<input style="position:absolute; left:40px;" id="inputEnclosureLayoutX" name="encLayoutX" type="number" min="1" max="12" value="1"/>
				</div>
				<div class="inputBlock" >
					<div style="display:inline;">Row:</div>
					<input style="position:absolute; left:40px;" id="inputEnclosureLayoutY" name="encLayoutY" type="number" min="1" max="12" value="1"/>
				</div>
			</fieldset>
			
			<!-- Enclosure Insert Fitment -->
			<fieldset id="objectEnclosureTolerance" class="dependantField partitionType enclosureField form-group">
				<label>Enclosure Tolerance</label>
				<div class="inputBlock" >
					<div class="radio">
						<input class="enclosureTolerance" type="radio" name="enclosureTolerance" id="inputEnclosureToleranceStrict" value="Strict" checked>
						<label for="inputEnclosureToleranceStrict">Strict</label>
					</div>
					<div class="radio">
						<input class="enclosureTolerance" type="radio" name="enclosureTolerance" id="inputEnclosureToleranceLoose" value="Loose">
						<label for="inputEnclosureToleranceLoose">Loose</label>
					</div>
				</div>
			</fieldset>
			
			<!-- Port Numbering -->
			<fieldset id="objectPortPrefix" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port ID</label><br>
				<div id="portNameDisplay"></div>
				<button id="buttonPortNameConfigure" class="btn btn-sm waves-effect waves-light btn-primary" data-port-name-action="add" data-toggle="modal" data-target="#portNameModal" type="button">
					<span class="btn-label">
						<i class="zmdi zmdi-edit"></i>
					</span>
					Configure
				</button>
			</fieldset>
			
			<!-- Port Layout -->
			<fieldset id="objectPortLayout" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Layout</label>
				<div class="inputBlock" style="margin-bottom:10px;">
					<div style="display:inline;">Col:</div>
					<input style="position:absolute; left:40px;" id="inputPortLayoutX" name="portLayoutX" type="number" min="0" max="48" value="0"/>
				</div>
				<div class="inputBlock">
					<div style="display:inline;">Row:</div>
					<input style="position:absolute; left:40px;" id="inputPortLayoutY" name="portLayoutY" type="number" min="0" max="6" value="0"/>
				</div>
			</fieldset>
			
			<!-- Port Orientation -->
			<fieldset id="objectPortOrientation" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Orientation</label>
				<div class="inputBlock">
					<?php echo generateOrientation($qls); ?>
				</div>
			</fieldset>
			
			<!-- Port Type -->
			<fieldset id="objectPortType" class="dependantField partitionType connectable endpoint passive form-group">
				<label>Port Type</label>
				<select id="inputPortType" name="portType" class="form-control">
					<?php echo generatePortType($qls); ?>
				</select>
			</fieldset>
			
			<!-- Media Type -->
			<fieldset id="objectMediaType" class="dependantField partitionType connectable passive form-group">
				<label>Media Type</label>
				<select id="inputMediaType" name="mediaType" class="form-control">
					<?php echo generateMediaType($qls); ?>
				</select>
			</fieldset>
			
			<button id="objectEditor-Submit" type="submit" class="btn btn-success waves-effect waves-light">
				<span class="btn-label"><i class="fa fa-check"></i></span>
				Submit
			</button>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
		<div id="cabinetContainer" style="padding-bottom:50px;" class="card-box">
			<div style="display:flex;">
			<h4 class="header-title m-t-0 m-b-30">Preview</h4>
			<div class="checkbox" style="margin-left: 15px;">
				<?php
					if($qls->user_info['scrollLock']) {
						$scrollLockState = 'checked';
						$scrollLockBool = true;
					} else {
						$scrollLockState = '';
						$scrollLockBool = false;
					}
				?>
				<input id="checkboxScrollLock" type="checkbox" <?php echo $scrollLockState; ?>>
				<label for="checkboxScrollLock">
					Lock <small>(set to <span id="scrollState"></span>)</small>
				</label>
			</div>
			</div>
			<div class="m-t-0 m-b-30"  style="display:flex;">
				<div class="radio radio-inline">
					<input class="sideSelector" type="radio" name="sideSelector" id="sideSelectorFront" value="0" checked disabled>
					<label for="sideSelectorFront">Front</label>
				</div>
				<div class="radio radio-inline">
					<input class="sideSelector" type="radio" name="sideSelector" id="sideSelectorBack" value="1" disabled>
					<label for="sideSelectorBack">Back</label>
				</div>
				
			</div>
			<?php include_once('includes/content-cabinet.php'); ?>
		</div>
	</div>

	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-4 col-xl-4">
		<div class="card-box">
			<h4 class="header-title m-t-0">Template Details</h4>
			
			<div id="objectCardBox" class="card">
				<div class="card-header">Selected Template
					<span>
						<div class="btn-group pull-right">
							<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
							<div class="dropdown-menu">
								<a id="objFind" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalTemplateWhereUsed"><i class="ion-map"></i> Where Used</a>
								<a id="objClone" class="dropdown-item disabled" href="#" ><i class="fa fa-copy"></i></span> Clone</a>
								<a id="objDelete" class="dropdown-item disabled" href="#" data-toggle="modal" data-target="#modalTemplateDeleteConfirm"><i class="fa fa-times"></i></span> Delete</a>
							</div>
						</div>
					</span>
				</div>
				<div class="card-block">
					<blockquote class="card-blockquote">
						<input id="selectedObjectID" type="hidden">
						<input id="selectedObjectFace" type="hidden">
						<input id="selectedPartitionDepth" type="hidden">
						<?php include_once('./includes/content-build-objectDetails.php'); ?>
					</blockquote>
				</div>
			</div>
			<div id="availableObjects">
				<div class="card">
				
					<div class="card-header">Available Templates
						<span>
							<div class="btn-group pull-right">
								<button type="button" class="btn btn-sm btn-custom dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">Actions <span class="m-l-5"><i class="fa fa-cog"></i></span></button>
								<div class="dropdown-menu">
									<a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalTemplateCatalog"><i class="ti-import"></i> Import</a>
								</div>
							</div>
						</span>
					</div>
					
					<div class="m-t-15 m-l-5">
						<div class="radio radio-inline">
							<input class="sideSelectorDetail" type="radio" name="sideSelectorDetails" id="sideSelectorDetailsFront" value="0" checked>
							<label for="sideSelectorDetailsFront">Front</label>
						</div>
						<div class="radio radio-inline">
							<input class="sideSelectorDetail" type="radio" name="sideSelectorDetails" id="sideSelectorDetailsBack" value="1">
							<label for="sideSelectorDetailsBack">Back</label>
						</div>
					</div>
					
					<div id="availableContainer" class="card-block">
						<h6>Name Filter:</h6>
						<select id="templateFilter" multiple data-role="tagsinput"></select>
						<div id="templateContainer">
							<?php
								include_once('./includes/content-build-objects.php');
							?>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>

<?php require 'includes/footer_start.php' ?>
<script src="assets/pages/jquery.templates.js"></script>

<!-- Tags Input -->
<script src="assets/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js"></script>

<!-- XEditable Plugin -->
<script src="assets/plugins/moment/moment.js"></script>
<script type="text/javascript" src="assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

<!-- Spectrum Colorpicker -->
<script src="assets/plugins/spectrum/js/spectrum.js"></script>

<!-- Modal-Effect -->
<script src="assets/plugins/custombox/js/custombox.min.js"></script>
<script src="assets/plugins/custombox/js/legacy.min.js"></script>

<!-- Jquery filer js -->
<script src="assets/plugins/jquery.filer/js/jquery.filer.min.js"></script>
<?php require 'includes/footer_end.php' ?>
