jQuery(function ($) {

    var params = wooconnector_settings_design_js_params;
    var typingTimer;
    var doneTypingInterval = 1000;

    /**
     * Show upload popup
     */
    function bamobile_display_popup_upload(thisClick, showImage, inputImage, deleteClick) {
        var uploader;
        if (uploader) {
            uploader.open();
            return;
        }

        uploader = wp.media.frames.file_frame = wp.media({
            title: 'Save Image',
            button: {
                text: 'Save Image'
            },
            multiple: false
        });

        uploader.on('select', function () {
            var selection = uploader.state().get('selection');
            selection.map(function (attachment) {
                attachment = attachment.toJSON();
                var url = attachment.url;
                var checkimage = url.split('.').pop().toUpperCase();
                var typeClick = thisClick.data('type-value');
                if (checkimage.length < 1) {
                    return false;
                } else if (checkimage != "PNG" && checkimage != "JPG" && checkimage != "GIF" && checkimage != "JPEG") {
                    alert("Invalid extension " + checkimage);
                    return false;
                } else {
                    inputImage.val(url);
                    showImage.attr('src', url);
                    deleteClick.css('display', 'inline');
                }
            });
        });
        uploader.open();
        return false;
    }

    /**
     * Show content when select option
     */
    function bamobile_show_content_when_load(thisSelect, thisSelected, data) {
        $('#' + data).remove();
        var html;
        html = bamobile_show_content(html, thisSelect, data);
        thisSelect.parent('.woo-content').parent('.wooconnector-selected-form').after(html);
        $('#' + data + '-choose-file').on('click', function (e) {
            e.preventDefault();
            bamobile_display_popup_upload($('#' + data + '-choose-file'), $('#' + data + '-preview'), $('#' + data + '-hidden-data-input'), $('#' + data + '-delete-file'));
        })
        $('#' + data + '-delete-file').on('click', function (e) {
            e.preventDefault();
            var thisClick = $(this);
            var inputImage = $('#' + data + '-hidden-data-input');
            if (confirm("Are you sure?")) {
                $('#' + data + '-preview').attr("src", "");
                inputImage.val("");
            }
            thisClick.css('display', 'none');
        })
        bamobile_select_color_change_value(data);
    }

    /**
     * Process action change color
     */
    function bamobile_select_color_change_value(data) {
        $('.' + data + '-color.select-value-color').on('change', function (e) {
            e.preventDefault();
            var value = $(this).val();
            $('.' + data + '-color.select-value-color-text').val(value);
        })
        $('.' + data + '-color.select-value-color-text').on('keyup', function (e) {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(bamobile_done_typing($(this), data), doneTypingInterval);
        })
        $('.' + data + '-color.select-value-color-text').on('keyup', function (e) {
            clearTimeout(typingTimer);
        })
    }

    /**
    * At the end of typing
    */
    function bamobile_done_typing(input, data) {
        var value = input.val();
        $('.' + data + '-color.select-value-color').val(value);
    }

    /**
     * Show form content
     */
    function bamobile_show_content(html, thisSelect, data) {
        var valueOption = ["border-box", "content-box", "padding-box", "initial", "inherit", "unset"];
        var colorValue, imageValue, jsonParams, display;
        var dataValue = thisSelect.parent('.woo-content').children('#' + data + '-hidden-data').val();
        var keyData = thisSelect.parent('.woo-content').children('#' + data + '-key').val();
        if (dataValue !== '') {
            if (typeof ($.parseJSON(dataValue)) != "undefined") {
                jsonParams = $.parseJSON(dataValue);
            }
        }
        if (typeof (jsonParams) == 'object' && typeof (jsonParams.color) != "undefined") {
            colorValue = jsonParams.color;
        } else {
            colorValue = '#FFFFFF';
        }
        if (typeof (jsonParams) == 'object' && typeof (jsonParams.image) != "undefined") {
            imageValue = jsonParams.image;
        } else {
            imageValue = '';
        }
        var patt = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
        if (patt.test(imageValue)) {
            display = 'display:inline';
        } else {
            display = 'display:none';
        }
        html = '<tr id="' + data + '">';
        html += '<td class="woo-label"></td>';
        html += '<td class="woo-content">';
        html += '<input type="color" class="wooconnector-select-value select-value-color ' + data + ' ' + data + '-color" name="wooconnector_settings-design[' + keyData + '][' + data + '][color]" value="' + colorValue + '"/>';
        html += '<input type="text" class="wooconnector-select-value select-value-color-text ' + data + ' ' + data + '-color" value="' + colorValue + '"/>';
        html += '<input type="hidden" class="wooconnector-select-value ' + data + ' ' + data + '-image" id="' + data + '-hidden-data-input" name="wooconnector_settings-design[' + keyData + '][' + data + '][image]" value="' + imageValue + '"/>';
        html += '<div class="wooconnector-select-value wooconnector-box-image-preview ' + data + ' ' + data + '-image"><img src="' + imageValue + '" id="' + data + '-preview" class="wooconnector-selected-preview-image" />';
        html += '<div class="wooconnector-selected-options">';
        html += '<input type="button" class="button wooconnector-button-selected" id="' + data + '-choose-file" value="' + params.button_name.choose + '" />';
        html += '<input type="button" style="' + display + '" class="button wooconnector-button-selected" id="' + data + '-delete-file" value="' + params.button_name.delete + '" />';
        html += '</div>';
        html += '</div>';
        html += '</td>';
        html += '</tr>';
        return html;
    }

    /**
     * Process typing
     * @param {*} input 
     */
    function setTimeoutTyping(input) {
        typingTimer = setTimeout(function () { bamobile_done_typing_big(input) }, doneTypingInterval);
    }

    /**
    * At the end of typing
    */
    function bamobile_done_typing_big(input) {
        var value = input.val();
        var patt = /(\#){1}([a-fA-F]|[0-9]){6}/;
        if (patt.test(value)) {
            input.parent('.woo-content').children('.wooconnector-design-color.change-value-color').val(value);
        } else {
            input.val("#000000");
            input.parent('.woo-content').children('.wooconnector-design-color.change-value-color').val("#000000");
        }
    }

    /**
     * Process select value
     */
    function bamobile_selected_change_value(data, dataOption) {
        $('.' + data).css('display', 'none');
        $('.' + dataOption).css('display', 'inline');
    }

    /**
     * Process click hidden tab
     */
    function bamobile_hidden_tab_button_click(thisClick) {
        if (thisClick.hasClass('button-up-arrow')) {
            thisClick.css('background', 'url(' + params.button_name.downarrow + ')');
            thisClick.removeClass('button-up-arrow');
            thisClick.addClass('button-down-arrow');
            thisClick.parent('.wooconnector-fields').children('.wooconnector-design').slideUp(200);
        } else if (thisClick.hasClass('button-down-arrow')) {
            thisClick.css('background', 'url(' + params.button_name.uparrow + ')');
            thisClick.removeClass('button-down-arrow');
            thisClick.addClass('button-up-arrow');
            thisClick.parent('.wooconnector-fields').children('.wooconnector-design').slideDown(200);
        }
    }

    /**
     * Process choose file
     */
    $('.wooconnector-choose-file').on('click', function (e) {
        e.preventDefault();
        var thisClick = $(this);
        var inputImage = thisClick.parent('.woo-content').children('.wooconnector-images-hidden-input');
        var data = thisClick.data('type-value');
        var showImage = $('#' + data);
        var deleteClick = thisClick.parent('.woo-content').children('.wooconnector-delete-file');
        bamobile_display_popup_upload(thisClick, showImage, inputImage, deleteClick);
    });

    /**
     * Process delete file
     */
    $('.wooconnector-delete-file').on('click', function (e) {
        e.preventDefault();
        var thisClick = $(this);
        var inputImage = thisClick.parent('.woo-content').children('.wooconnector-images-hidden-input');
        var data = thisClick.data('type-value');
        if (confirm("Are you sure?")) {
            $('#' + data).attr("src", "");
            inputImage.val("");
        }
        thisClick.css('display', 'none');
    })

    /**
     * Process input number
     */
    $('.wooconnector-design-number-match').on('keypress', function (event) {
        if (event.which != 8 && isNaN(String.fromCharCode(event.which))) {
            event.preventDefault(); //stop character from entering input
        }
    })

    /**
     * Process input number px
     */
    $('.wooconnector-design-number-pixel').on('keyup', function (event) {
        var val = $(this).val();
        if (event.which == 8) {
            var count = val.length;
            if (count > 1) {
                val = val.substring(0, count - 1);
            }
        }
        if (val == '') {
            val = '0';
        }
        var valin = val + 'px';
        $(this).parent('.woo-content').children('.wooconnector-design-input-hidden').val(valin);
    })

    /**
     * Check if empty
     * @param {*} string
     */
    function bamobile_is_empty(string) {
        str = string.trim();
        return (!str || 0 === str.length);
    }


    /**
     * Process change color
     */
    $('.wooconnector-design-color.change-value-color').on('change', function (e) {
        e.preventDefault();
        var value = $(this).val();
        $(this).parent('.woo-content').children('.wooconnector-design-color.change-value-color-text').val(value);
    })

    /**
     * Start typing
     */
    $('.wooconnector-design-color.change-value-color-text').on('keyup', function () {
        clearTimeout(typingTimer);
        setTimeoutTyping($(this));
    })

    /**
     * End typing
     */
    $('.wooconnector-design-color.change-value-color-text').on('keydown', function (e) {
        clearTimeout(typingTimer);
    })

    /**
     * Process Select
     */
    $('.wooconnector-select-background-type-design').on('change', function (e) {
        e.preventDefault();
        var data = $(this).data('type-value');
        var dataOption = $(this).find('option:selected').data('value');
        bamobile_selected_change_value(data, dataOption);
    })

    /**
     * Hidden tab
     */
    $('.wooconnector-hidden-tab-button').on('click', function (e) {
        e.preventDefault();
        bamobile_hidden_tab_button_click($(this));
    })

    /**
     * On load
     */
    $(window).load(function () {
        $.each($('.wooconnector-select-background-type-design'), function (key, val) {
            var thisSelect = $(this);
            var thisSelected = $(this).val();
            var data = $(this).data('type-value');
            if (typeof (data) !== "undefined") {
                bamobile_show_content_when_load(thisSelect, thisSelected, data);
            }
        })
        $.each($('.wooconnector-warning'), function (key, val) {
            var valueWarring = $(this).html();
            if (bamobile_is_empty(valueWarring)) {
                $(this).css('display', 'none');
            }
        })
        $.each($('.wooconnector-images-hidden-input'), function (key, val) {
            var idShow = $(this).data('id-show');
            var valueImages = $(this).val();
            $('#' + idShow).attr('src', valueImages);
        })
    })
})