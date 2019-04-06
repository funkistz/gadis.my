jQuery(function ($) {
    var socialparams = mobiconnector_settings_social_js_params;

    function checkCheckboxRequired(thisBox) {
        var type = thisBox.data('type');
        if (thisBox.is(':checked')) {
            $('.mobi-input-' + type).attr('required', 'required');
        } else {
            $('.mobi-input-' + type).removeAttr('required');
        }
    }
    $('.mobiconnector-checkbox').click(function () {
        checkCheckboxRequired($(this));
    });
    $('.get_link_social').on('click', function () {
        var key = $(this).data('key');
        $(this).parent('.mobi-content').html(socialparams[key]);
    })
});