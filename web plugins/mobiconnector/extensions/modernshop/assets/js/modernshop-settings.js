jQuery(function ($) {
    var params = modern_settings_script_params;
    function get_action_tooltip_javascript() {
        $('.modern-tooltip-symbol').on('mouseover', function (e) { e.preventDefault(); $(this).parent('.modern-support-input').children('.modern-tooltip-content').fadeIn(200); })
        $(document).on('mouseout', function (event) { if (!$(event.target).closest('.modern-tooltip-symbol').length && !$(event.target).closest('.modern-tooltip-content').length) { if ($('.modern-tooltip-symbol').is(":visible") && $('.modern-tooltip-content').is(":visible")) { $('.modern-tooltip-content').fadeOut(200); } } })
    }
    get_action_tooltip_javascript();
})