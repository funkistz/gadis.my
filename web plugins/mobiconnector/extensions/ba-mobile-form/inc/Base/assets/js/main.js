
jQuery(function($){
	$('tbody.ui-sortable').sortable({
	    items:'tr',
		cursor:'move',
		axis:'y',
		handle: 'td',
		scrollSensitivity:40,
		helper:function(e,ui){
			ui.children().each(function(){
				$(this).width($(this).width());
			});
			ui.css('left', '0');
			return ui;
		},
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
			
		}
	});
	$(".ngothoai-delete").click(function(event){
		var attr = $(this).attr('disabled');
		if (typeof attr !== typeof undefined && attr !== false) {
			event.preventDefault();
			alert("Can not delete this, it required in checkout form");
		}else{
			return confirm( "Are you sure you want to delete this item?" );	
		}
	});
	$( "select#type_design_add" ).change(function() {
		$( "select#type_design_add" ).each(function() {
			if($( "select#type_design_add" ).val() === 'select' || $( "select#type_design_add" ).val() === 'multicheckbox' || $( "select#type_design_add" ).val() === 'multiselect' || $( "select#type_design_add" ).val() === 'multicheckbox' ||  $( "select#type_design_add" ).val() === 'radio'){
				$("tr.option-fields").show();
				$("tr#country_belong_to_tr").hide();
				$("tr#country_has_state_tr").hide();
			} else if($( "select#type_design_add" ).val() === 'state'){
				$("tr.option-fields").hide();
				$("tr#country_has_state_tr").hide();
				$(".option-field").val("");
				$("tr#country_belong_to_tr").show();
			} else if($( "select#type_design_add" ).val() === 'country'){
				$("tr#country_belong_to_tr").hide();
				$("tr.option-fields").hide();
				$(".option-field").val("");
				$("tr#country_has_state_tr").show();
			} else {
				$("tr.option-fields").hide();
				$(".option-field").val("");
				$("tr#country_belong_to_tr").hide();
				$("tr#country_has_state_tr").hide();
			}
      	}); 
	});
	
	$( "select#type_design_add" ).each(function() {
		if($( "select#type_design_add" ).val() === 'select' || $( "select#type_design_add" ).val() === 'multicheckbox' || $( "select#type_design_add" ).val() === 'multiselect' || $( "select#type_design_add" ).val() === 'multicheckbox' ||  $( "select#type_design_add" ).val() === 'radio'){
			$("tr.option-fields").show();
			$("tr#country_belong_to_tr").hide();
			$("tr#country_has_state_tr").hide();
		} else if($( "select#type_design_add" ).val() === 'state'){
			$("tr.option-fields").hide();
			$("tr#country_has_state_tr").hide();
			$(".option-field").val("");
			$("tr#country_belong_to_tr").show();
		} else if($( "select#type_design_add" ).val() === 'country'){
			$("tr#country_belong_to_tr").hide();
			$("tr.option-fields").hide();
			$(".option-field").val("");
			$("tr#country_has_state_tr").show();
		} else {
			$("tr.option-fields").hide();
			$(".option-field").val("");
			$("tr#country_belong_to_tr").hide();
			$("tr#country_has_state_tr").hide();
		}
  	});
});

jQuery(document).ready(function($) {
	if ( $().select2 ) {
		$( 'select.checkout_chosen_select' ).filter( ':not(.enhanced)' ).each( function() {
			$( this ).select2( {
				minimumResultsForSearch: 10,
				allowClear:  true,
				placeholder: $( this ).data( 'placeholder' )
			} ).addClass( 'enhanced' );
		});
	}
	var params = ba_date_fields_params;
	$('.ba-date').datepicker({
		dateFormat: params.date_format,
		numberOfMonths: 1,
		showButtonPanel: true,
		changeMonth: true,
      	changeYear: true,
		yearRange: "-100:+1"
	});

	/*IP address*/
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
	$(document).ready(function(){
    //get it if Status key found
	    if(localStorage.getItem("Status"))
	    {
	        $("#baform_update_message").html("<div id='message' class='updated notice notice-success'><p>Updated successfully</p></div>");
	        localStorage.clear();
	    }
	    // if(localStorage.getItem("Edit"))
	    // {
	    //     $("#baform_update_message").html('<div id="message" class="updated notice notice-success"><p>This field is updated</p></div>');
	    //     localStorage.clear();
	    // }
	});
	/*Change check required on page list design form*/
	$(document).ready(function () {
	   $('#ba_checkout_fields .required_check > a').on('click', function() {
         localStorage.setItem("Status",$(this));
	    });
	});

	/*Change check shipping on page list design form*/
	$(document).ready(function () {
	   $('#ba_checkout_fields .shipping_check > a').on('click', function() {
    		localStorage.setItem("Status",$(this));
	    });
	});
	/*Change check billing on page list design form*/
	$(document).ready(function () {
	   $('#ba_checkout_fields .billing_check > a').on('click', function() {
    	localStorage.setItem("Status",$(this));
	    });
	});
	/*Change check register on page list design form*/
	$(document).ready(function () {
	    $('#ba_checkout_fields .register_check > a').on('click', function() {
	    	localStorage.setItem("Status",$(this));
	    });
	});

	/*Change check profile on page list design form*/
	$(document).ready(function () {
	   $('#ba_checkout_fields .profile_check > a').on('click', function() {
    		localStorage.setItem("Status",$(this));
	    });
	});
	$(document).ready(function(){
		$('.status.default_not_click > a').off("click");
		$('.status.default_not_click > a').on('click', function() {
			alert("Can not disable this setting, it is required");
			return false;
		});
		/*Notice disable shipping*/
		$('.status.shipping_not_click > a').off("click");
		$('.status.shipping_not_click > a').on('click', function(e) {
			alert("Can not disable this, it required in Shipping form");
			e.preventDefault();
		});
		/*Notice disable billing*/
		$('.status.billing_not_click > a').off("click");
		$('.status.billing_not_click > a').on('click', function(e) {
			alert("Can not disable this, it required in Billing form");
			e.preventDefault();
		});

		/*Notice disable profile*/
		$('.status.profile_not_click > a').off("click");
		$('.status.profile_not_click > a').on('click', function(e) {
			alert("Can not change this, it does not change in Profile form");
			e.preventDefault();
		});
		$('.status.register_not_click > a').off("click");
		$('.status.register_not_click > a').on('click', function(e) {
			alert("Can not change this, it does not change in Register form");
			e.preventDefault();
		});
	});
});


jQuery(function($) {
	var out = [];
	var $input = $(".ba-add-option"),
	     $list = $(".option-fields ul.tagchecklist");
	$(document).ready(function(){

	    myVar = $(".ba-add-option").val();
		inputdata = $('.inputdata').val();
		if('myVar' == typeof( myVar ) || '' === myVar){
			itemdata = inputdata.split( ',' ),
			$.each( itemdata, function( key, val ) {
				val = $.trim( val );
				if ( val ) {
					out.push( val );
				}
			});
		}
	
	  	function addListItem(e) {
		    if(e.type==="keydown" && e.which !== 13) return;
		    e.preventDefault(); // Don't submit form
		    if ('myVar' == typeof( myVar ) || '' === myVar ){
		    	if ( $.trim( $input.val()) == ''){
		    		alert("Pls not null");
		    	} else {
		    		out.push($input.val());
			    	$list.append('<li><button type="button" class="ntdelbutton"><span class="remove-tag-icon" aria-hidden="true"></span></button>&nbsp'+ $input.val()) + '<li>';
			    	$(".option-fields ul.tagchecklist li" ).each(function() {
						$(document).on('click', '.ntdelbutton', function(e) {
							item_remove = $(this).parent("li").text();
							item_remove = $.trim( item_remove );
							var index = out.indexOf(item_remove);
							if (index !== -1) out.splice(index, 1);
							$(this).parent("li").remove();
							$('.inputdata').val(out);
						});
					});
		    	}
		    }
	    	$input.val(""); // Reset input field
	 	 }
		$(".optionadd").click(function(e){
			addListItem(e);
			$('.inputdata').val(out);
		});
		$(".ba-add-option").keydown(function(e){
			addListItem(e);
			$('.inputdata').val(out);
		});
		$(".option-fields ul.tagchecklist li" ).each(function() {
			$(document).on('click', '.ntdelbutton', function(e) {
				item_remove = $(this).parent("li").text();
				item_remove = $.trim( item_remove );
				var index = out.indexOf(item_remove);
				if (index !== -1) out.splice(index, 1);
				$(this).parent("li").remove();
				$('.inputdata').val(out);
			});
		});
	});

});