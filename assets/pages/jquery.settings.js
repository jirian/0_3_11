/**
 * Admin
 * This page allows administrators to manage users
 */

$( document ).ready(function() {

	// Timezone
	$('#selectTimezone').on('change', function(){
		var value = $(this).val();
		
		var data = {
			property: 'timezone',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
	// Scan method
	$('.radioScanMethod').on('change', function(){
		var value = $('.radioScanMethod:checked').val();
		
		var data = {
			property: 'scanMethod',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
	// Template scroll lock
	$('#checkboxTemplateScroll').on('change', function(){
		var value = $(this).is(':checked');
		
		var data = {
			property: 'scrollLock',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
	// Connection Style
	$('.radioConnectionStyle').on('change', function(){
		var value = $('.radioConnectionStyle:checked').val();
		
		var data = {
			property: 'connectionStyle',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
	// Path Orientation
	$('.radioPathOrientation').on('change', function(){
		var value = $('.radioPathOrientation:checked').val();
		
		var data = {
			property: 'pathOrientation',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
	// Path Orientation Global
	$('#checkboxGlobalPathOrientation').on('change', function(){
		var value = $(this).is(':checked');
		
		var data = {
			property: 'globalPathOrientation',
			value: value
		};
		data = JSON.stringify(data);
		
		$.post('backend/process_settings.php', {data:data}, function(response){
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
	
});
