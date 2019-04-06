
jQuery(document).ready(function($) {
	var params = ba_checkout_fields_params;
	// Frontend Chosen selects
	if ( $().select2 ) {
		$( 'select.checkout_chosen_select:not(.old_chosen), .form-row .select:not(.old_chosen)' ).filter( ':not(.enhanced)' ).each( function() {
			$( this ).select2( {
				minimumResultsForSearch: 10,
				allowClear:  true,
				placeholder: $( this ).data( 'placeholder' )
			} ).addClass( 'enhanced' );
		});
	}

	$('.ba-date').datepicker({
		dateFormat: params.date_format,
		numberOfMonths: 1,
		showButtonPanel: true,
		changeMonth: true,
      	changeYear: true,
		yearRange: "-100:+1"
	});

	// Check ip address
	var pattern = /\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/;
		x = 46;
		$('input.ip_address').keypress(function (e) {
		    if (e.which != 8 && e.which != 0 && e.which != x && (e.which < 48 || e.which > 57)) {
		        console.log(e.which);
		        return false;
		    }
		}).keyup(function () {
		    var this1 = $(this);
		    if (!pattern.test(this1.val())) {
		        $('#validate_ip').text('Not Valid IP');
		        while (this1.val().indexOf("..") !== -1) {
		            this1.val(this1.val().replace('..', '.'));
		        }
		        x = 46;
		    } else {
		        x = 0;
		        var lastChar = this1.val().substr(this1.val().length - 1);
		        if (lastChar == '.') {
		            this1.val(this1.val().slice(0, -1));
		        }
		        var ip = this1.val().split('.');
		        if (ip.length == 4) {
		            $('#validate_ip').text('');
		        }
		    }
		});
});