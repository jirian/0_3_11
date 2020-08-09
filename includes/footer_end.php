<!-- google analytic code -->


<!-- google analytic code ends here -->

<!-- following js will activate the menu in left side bar based on url -->
<script type="text/javascript">
    // === following js will activate the menu in left side bar based on url ====
    $(document).ready(function () {
        $('.navigation-menu a').each(function () {
            if (this.href == window.location.href) {
                $(this).parent().addClass("active"); // add active to li of the current link
                $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
                $(this).parent().parent().parent().parent().parent().addClass("active"); // add active class to an anchor
            }
        });
		
		$('#autocomplete').autocomplete({
			minLength: 2,
			source: '/backend/process_search.php',
			open: function(){
				$(this).autocomplete('widget').css('z-index', 10000);
				return false;
			},
			select: function(event, ui){
				window.location.href = '/backend/process_search.php?select='+ui.item.value;
			}
		});
		
		$('#searchForm').submit(function(event){
			event.preventDefault();
			$('#searchSubmit').click();
		});
		
		$('#btnAbout').on('click', function(event){
			event.preventDefault();
			$('#aboutModal').modal('show');
		});
		
		$('#btnConfirm').on('click', function(){
			dataConfirmed = $(document).data('confirmData');
			dataConfirmed['confirmed'] = true;
			$(document).data('confirmFunction').call(dataConfirmed);
		});
    });
	
	function displayError(errMsg){
		$('#alertMsg').empty();
		$(errMsg).each(function(index, value){
			var html = '<div class="alert alert-danger" role="alert">';
			html += '<strong>Oops!</strong>  '+value;
			html += '</div>';
			$('#alertMsg').append(html).hide();
			$('#alertMsg').slideDown();
		});
		$("html, body").animate({ scrollTop: 0 }, "slow");
	}
	
	function displaySuccess(successMsg){
		$('#alertMsg').empty();
		var html = '<div class="alert alert-success" role="alert">';
		html += '<strong>Success!</strong>  '+successMsg;
		html += '</div>';
		$('#alertMsg').append(html).hide();
		$('#alertMsg').slideDown();
	}
	
	function displaySuccessElement(successMsg, element){
		$(element).empty();
		var html = '<div class="alert alert-success" role="alert">';
		html += '<strong>Success!</strong>  '+successMsg;
		html += '</div>';
		$(element).append(html).hide();
		$(element).slideDown();
	}
	
	function displayErrorElement(errMsg, element){
		$(element).empty();
		$(errMsg).each(function(index, value){
			var html = '<div class="alert alert-danger" role="alert">';
			html += '<strong>Oops!</strong>  '+value;
			html += '</div>';
			$(element).append(html).hide();
			$(element).slideDown();
		});
		if($(element).closest('.modal').length) {
			$(element).closest('.modal').animate({ scrollTop: 0 }, "slow");
		} else {
			$("html, body").animate({ scrollTop: 0 }, "slow");
		}
	}

</script>

</body>
</html>