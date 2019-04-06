jQuery(function ($) {
	var params = wooconnector_postpushnotice_params; if (jQuery('input[value="check_wooconnector"] ').length) { jQuery('input[value="check_wooconnector"] ').parent('td').parent('tr').css('display', 'none'); }
	if (jQuery('input[value="wooconnector_device_checkout"] ').length) { jQuery('input[value="wooconnector_device_checkout"] ').parent('td').parent('tr').css('display', 'none'); }
	$('#title').keyup(function () { var val = $(this).val(); $('#woo_push_notification_title').val(val); })
	$('#woo_push_notification').click(function () { if ($(this).is(':checked')) { $('#woo_push_notification_title').attr("required", "true"); $('#woo_push_notification_content').attr("required", "true"); $('.wooconnector-after-checked-notification').slideDown(200); } else { $('#woo_push_notification_title').removeAttr("required"); $('#woo_push_notification_content').removeAttr("required"); $('.wooconnector-after-checked-notification').slideUp(200); } })
})