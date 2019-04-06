jQuery(function ($) {
    $(window).load(function () {
        var val = $('#mobiconnector-select-languages-displaymode').val();
        $('#mobiconnector-languages-displaymode-' + val).css('display', 'block');
    })
    $('#mobiconnector-select-languages-displaymode').change(function () {
        var val = $(this).val();
        $('.mobiconnector-languages-displaymode').css('display', 'none');
        $('#mobiconnector-languages-displaymode-' + val).css('display', 'block');
    })
    $('.mobiconnector-tooltip-symbol').on('mouseover', function (e) {
        e.preventDefault();
        $(this).parent('.mobiconnector-support-input').children('.mobiconnector-tooltip-content').fadeIn(200);
    })
    $(document).on('mouseout', function (event) {
        if (!$(event.target).closest('.mobiconnector-tooltip-symbol').length && !$(event.target).closest('.mobiconnector-tooltip-content').length) {
            if ($('.mobiconnector-tooltip-symbol').is(":visible") && $('.mobiconnector-tooltip-content').is(":visible")) {
                $('.mobiconnector-tooltip-content').fadeOut(200);
            }
        }
    })
    $('#mobiconnector_application_languages').on('change', function (e) {
        e.preventDefault();
        var lang = $(this).find(':selected').attr('lang');
        var value = $(this).val();
        $('#mobiconnector-show-application-languages').val(value);
        $('#mobiconnector_settings-application-languages').val(lang);
    })
    $(window).load(function (e) {
        e.preventDefault();
        var lang = $('#mobiconnector_application_languages').find(':selected').attr('lang');
        var value = $('#mobiconnector_application_languages').val();
        $('#mobiconnector-show-application-languages').val(value);
        $('#mobiconnector_settings-application-languages').val(lang);
    })
})