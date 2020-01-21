/**
 * Admin
 * This page allows administrators to manage users
 */
 
 function toggleSMTPFields(){
	var mailMethod = $('input[name="mailMethod"]:checked').val();
	if(mailMethod == 'smtp') {
		$('#fieldsSMTP').show();
	} else {
		$('#fieldsSMTP').hide();
	}
 }
 
 function toggleSMTPAuthFields() {
	if($('#smtpAuthentication').is(':checked')) {
		$('#fieldsSMTPAuth').show();
	} else {
		$('#fieldsSMTPAuth').hide();
	}
 }
 
 function updateEntitlementData(entitlementData) {
	$('#entitlementLastChecked').html(entitlementData.lastCheckedFormatted);
	$('#entitlementExpiration').html(entitlementData.expirationFormatted);
	$('#entitlementStatus').html(entitlementData.status);
	$('#entitlementEntitlementData').empty();
	
	$.each(entitlementData.data, function(index, value){
		var attribute = '';
		if(value.count == 0) {
			var count = 'Unl.';
		} else {
			var count = value.count;
		}
		
		attribute += '<dt class="col-sm-6">'+value.friendlyName+':</dt>';
		attribute += '<dd class="col-sm-6">'+count+' ('+value.used+' used)</dd>';
		$('#entitlementEntitlementData').append(attribute);
	});
 }
 
 function loadUserTable() {
	 $('#tableInvitations').empty();
	 
	// Retrieve user table
	$.post("backend/retrieve_user-table.php", function(response){
		var responseJSON = JSON.parse(response);
		if (responseJSON.active == 'inactive'){
			window.location.replace("/");
		} else if ($(responseJSON.error).size() > 0){
			displayError(responseJSON.error);
		} else {
			
			// Create user table
			$('#tableInvitations').html(responseJSON.success);
			
			// Remove user button action
			$('.buttonRemoveUser').on('click', function(){
				var userTableRow = $(this).parents('tr');
				var userID = $(this).attr('data-userID');
				var userType = $(this).attr('data-userType');
				//Collect object data
				var data = {
					userID: userID,
					userType: userType,
					action: 'delete'
				};
				data = JSON.stringify(data);
				
				// Process remove user
				$.post('backend/process_admin_edit-user.php', {data:data}, function(response){
					var responseJSON = JSON.parse(response);
					if (responseJSON.active == 'inactive'){
						window.location.replace('/');
					} else if ($(responseJSON.error).size() > 0){
						displayError(responseJSON.error);
					} else {
						loadUserTable();
					}
				});
			});

			// User roles
			var roleData = [
				{'value':3,'text':'Administrator'},
				{'value':4,'text':'Operator'},
				{'value':5,'text':'User'}
			];

			// Make user role editable
			$('.editableUserRole').editable({
				showbuttons: false,
				mode: 'inline',
				source: roleData,
				params: function(params){
					var data = {
						action: 'role',
						userID: params.pk,
						groupID: params.value,
						userType: $(this).attr('data-userType')
					};
					params.data = JSON.stringify(data);
					return params;
				},
				url: 'backend/process_admin_edit-user.php'
			});
			
			// User status
			var roleData = [
				{'value':'yes','text':'Blocked'},
				{'value':'no','text':'Unblocked'}
			];

			// Make user status editable
			$('.editableUserStatus').editable({
				showbuttons: false,
				mode: 'inline',
				source: roleData,
				params: function(params){
					var data = {
						action: 'status',
						userID: params.pk,
						status: params.value,
						userType: 'active'
					};
					params.data = JSON.stringify(data);
					return params;
				},
				url: 'backend/process_admin_edit-user.php'
			});
			
			// User MFA
			var mfaData = [
				{'value':0,'text':'No'},
				{'value':1,'text':'Yes'}
			];

			// Make user MFA editable
			$('.editableUserMFA').editable({
				showbuttons: false,
				mode: 'inline',
				source: mfaData,
				params: function(params){
					var data = {
						action: 'mfa',
						userID: params.pk,
						state: params.value,
						userType: 'active'
					};
					params.data = JSON.stringify(data);
					return params;
				},
				url: 'backend/process_admin_edit-user.php'
			});
		}
	});
 }
 
$( document ).ready(function() {
	//modify buttons style
    $.fn.editableform.buttons = 
    '<button type="submit" class="btn btn-primary editable-submit waves-effect waves-light"><i class="zmdi zmdi-check"></i></button>' +
    '<button type="button" class="btn editable-cancel btn-secondary waves-effect"><i class="zmdi zmdi-close"></i></button>';

	loadUserTable();
	toggleSMTPFields();
	toggleSMTPAuthFields();
	
	$('.mailMethod').on('change', function(){
		toggleSMTPFields();
	});
	
	$('#smtpAuthentication').on('change', function(){
		toggleSMTPAuthFields();
	});
	
	$('#smtpSubmit').on('click', function(event){
		event.preventDefault();
		
		var mailMethod = $('input[name="mailMethod"]:checked').val();
		var data = {};
		
		data['mailMethod'] = mailMethod;
		data['fromEmail'] = $('#smtpFromEmail').val();
		data['fromName'] = $('#smtpFromName').val();
		
		if(mailMethod == 'smtp') {
			data['smtpServer'] = $('#smtpServer').val();
			data['smtpPort'] = $('#smtpPort').val();
			if($('#smtpAuthentication').is(':checked')) {
				data['smtpAuth'] = 'yes';
				data['smtpUsername'] = $('#smtpUsername').val();
				data['smtpPassword'] = $('#smtpPassword').val();
			} else {
				data['smtpAuth'] = 'no';
			}
		}
		
		data = JSON.stringify(data);
		
		// Process mail settings
		$.post("backend/process_mail_settings.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				displaySuccess(responseJSON.success);
			}
		});
	});
	
	$('#invitationSubmit').on('click', function(event){
		event.preventDefault();
		var invitationButton = $(this);
		$(invitationButton).html('<i class="fa fa-spin fa-spinner"></i>Sending');
		var email = $('#invitationEmail').val();

		//Collect object data
		var data = {
			email: email,
			action: 'create'
			};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/process_invitation.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
				$(invitationButton).html('Submit');
			} else {
				$(invitationButton).html('Submit');
				displaySuccess(responseJSON.success);
				loadUserTable();
			}
		});
	});
	
	$('#entitlementCheck').on('click', function(event){
		event.preventDefault();
		var entitlementButton = $(this);
		var entitlementButtonHTML = $(entitlementButton).html();
		$(entitlementButton).html('<i class="fa fa-spin fa-spinner"></i>Sending');
		var entitlementID = $('#inline-entitlement').editable('getValue')['inline-entitlement'];

		//Collect object data
		var data = {
			entitlementID: entitlementID,
			action: 'check'
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/process_entitlement.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.comment);
			} else {
				updateEntitlementData(responseJSON.success);
			}
			$(entitlementButton).html(entitlementButtonHTML);
		});
	});
	
	$('#confirmEntitlementCancellation').on('click', function(event){
		event.preventDefault();
		var entitlementID = $('#inline-entitlement').editable('getValue')['inline-entitlement'];

		//Collect object data
		var data = {
			entitlementID: entitlementID,
			action: 'cancel'
		};
		data = JSON.stringify(data);
		
		//Retrieve object details
		$.post("backend/process_entitlement.php", {data:data}, function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.comment);
			} else {
				updateEntitlementData(responseJSON.success);
			}
		});
	});

	$('#inline-orgName').editable({
		showbuttons: false,
		mode: 'inline',
		params: function(params){
			var data = {
				'value':params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		url: 'backend/process_organization-name.php',
		success: function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				$('#orgName').html(responseJSON.success);
			}
		}
	});
	
	$('#inline-serverName').editable({
		showbuttons: false,
		mode: 'inline',
		params: function(params){
			var data = {
				'value':params.value
			};
			params.data = JSON.stringify(data);
			return params;
		},
		url: 'backend/process_server-name.php',
		success: function(response){
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			}
		}
	});
	
	$('#inline-entitlement').editable({
		showbuttons: false,
		mode: 'inline',
		params: function(params){
			var data = {
				'entitlementID':params.value,
				'action':'update'
			};
			params.data = JSON.stringify(data);
			return params;
		},
		url: 'backend/process_entitlement.php',
		success: function(response){
			var alertMsg = '';
			var responseJSON = JSON.parse(response);
			if (responseJSON.active == 'inactive'){
				window.location.replace("/");
			} else if ($(responseJSON.error).size() > 0){
				displayError(responseJSON.error);
			} else {
				updateEntitlementData(responseJSON.success);
			}
		}
	});

});
