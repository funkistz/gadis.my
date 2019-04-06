jQuery(function ($) {
    var _uploadImage; var debugEnabled = false; var $mobiUploadButton = $("#wooconnector_upload_images_brand_button"); var $mobiRemoveButton = $("#wooconnector_remove_images_brand_button"); var $mobiImageHolder = $("#wooconnector_brand_avatar_holder"); var $mobiAttachment = $("#wooconnector_attachment"); var params = wooconnector_brands_avatar_params; var clearAttachment = function () { $mobiAttachment.val(''); $mobiImageHolder.html(''); }; var wooconnectorbrandAvatar = $.extend(wooconnectorbrandAvatar, {
        options: { holder_max_width: 180 }, debug: function (message) { if (window.console && debugEnabled) { console.log(message); } }, hasbrandImage: function () { return ($mobiAttachment.val() !== ""); }, createPlaceHolder: function (_src) {
            $mobiImageHolder.html('<img id="wooconnector_brand_avatar_image" style="diplay:none;" />'); $("#wooconnector_brand_avatar_image").load(function () {
                var $el = $(this); var width = $el.width(); var height = $el.height(); var ratio = 0; var maxWidth = wooconnectorbrandAvatar.options.holder_max_width; if (width > maxWidth) { ratio = maxWidth / width; $el.width(maxWidth); $el.height(height * ratio); }
                $el.fadeIn('fast');
            }).attr({ src: _src }); $mobiImageHolder.after('<span>* This picture only work on Mobile App</span>');
        }, toggleRemoveButton: function () { wooconnectorbrandAvatar.debug(wooconnectorbrandAvatar.hasbrandImage()); if (!wooconnectorbrandAvatar.hasbrandImage()) { $mobiRemoveButton.css('display', 'none'); } else { $mobiRemoveButton.css('display', 'inline-block'); } }, removePlaceHolder: function () { $mobiImageHolder.html(''); }, events: {
            onClickShowMediaManager: function (e) {
                e.preventDefault(); if (_uploadImage) { _uploadImage.open(); return; }
                var _mediaParams = { title: params.label.title, button: { text: params.label.button }, library: { type: 'image' }, multiple: false }; if (wooconnectorbrandAvatar.hasbrandImage()) { _mediaParams = $.extend(_mediaParams, { editing: true }); }
                _uploadImage = wp.media.frames.file_frame = wp.media(_mediaParams); _uploadImage.on("select", wooconnectorbrandAvatar.events.onSelectAttachmentFromMediaManager); _uploadImage.on("open", wooconnectorbrandAvatar.events.onOpenMediaManager); _uploadImage.open();
            }, onClickRemoveAttachment: function (e) { e.preventDefault(); $mobiAttachment.val(""); wooconnectorbrandAvatar.removePlaceHolder(); wooconnectorbrandAvatar.toggleRemoveButton(); }, onOpenMediaManager: function () { if (wooconnectorbrandAvatar.hasbrandImage()) { var selection = _uploadImage.state().get('selection'); var id = parseInt($mobiAttachment.val()); wooconnectorbrandAvatar.debug(id); var attachment = wp.media.attachment(id); attachment.fetch(); selection.add(attachment ? [attachment] : []); } }, onSelectAttachmentFromMediaManager: function () {
                var _attachment = _uploadImage.state().get('selection').first().toJSON(); wooconnectorbrandAvatar.debug(_attachment); if (_attachment) { wooconnectorbrandAvatar.createPlaceHolder(_attachment.url); $mobiAttachment.val(_attachment.id); }
                wooconnectorbrandAvatar.toggleRemoveButton(); _uploadImage.close();
            }
        }
    }); $mobiUploadButton.on('click', wooconnectorbrandAvatar.events.onClickShowMediaManager); $mobiRemoveButton.on('click', wooconnectorbrandAvatar.events.onClickRemoveAttachment); wooconnectorbrandAvatar.toggleRemoveButton();
});