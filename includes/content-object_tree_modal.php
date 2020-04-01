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
				<button id="buttonObjectTreeModalSave" type="button" class="btn btn-primary waves-effect waves-light">Save</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->