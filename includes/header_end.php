    <!-- App CSS -->
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- Modernizr js -->
    <script src="assets/js/modernizr.min.js"></script>

</head>

<body>

<?php require 'topbar.php'; ?>
	
<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
		<div class="row">
			<div class="col-lg-12 col-sm-12 col-xs-12 col-md-12 col-xl-12">
				<div id="alertMsg" class="m-t-15"></div>
			</div>
		</div>
	</div>
</div>

<div id="notificationsModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="notificationsModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="notificationsModalLabel">Notifications</h4>
            </div>
            <div id="notificationsModalBody" class="modal-body"></div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div id="messagesModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="messagesModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<div title="Close">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
						<i class="zmdi zmdi-close"></i>
					</button>
				</div>
                <h4 class="modal-title" id="messagesModalLabel">Messages</h4>
            </div>
            <div class="modal-body">
				<div id="messagesModalBodyTable"></div>
				<div id="messagesModalBodyMessage" style="display:none;">
					<button id="messageModalButtonBack" type="button" class="btn btn-sm btn-secondary waves-effect">Back</button>
					&nbsp
					<div class="btn-group">
						<button type="button" class="btn btn-sm btn-secondary waves-effect">Prev</button>
						<button type="button" class="btn btn-sm btn-secondary waves-effect">Next</button>
					</div>
					&nbsp
					<button type="button" class="btn btn-sm btn-danger waves-effect waves-light">Delete</button>
					<hr>
					<div id="messagesModalBodyMessageContent"></div>
				</div>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

