jQuery(function ($) {
    function addButtonUpload() { $('#post-body-content').append('<button data-name="mobiconnector-list-galleries" class="button mobiconnector-click-open-upload-multiple" id="mobiconnector-add-galleries"><span class="dashicons dashicons-admin-media"></span> Add Photo</button>'); $('#mobiconnector-add-galleries').on('click', function (event) { var thisTr = $('.mobiconnector-list-image-galleries'); var thisName = $(this).data('name'); showUploadMultipe(event, thisTr, thisName); }); }
    function showUploadMultipe(event, thisTr, thisName) {
        var file_frame, i; event.preventDefault(); if (file_frame) { file_frame.open(); return; }
        file_frame = wp.media.frames.file_frame = wp.media({ title: "Save Images", button: { text: "Save Images", }, multiple: true }); file_frame.on('select', function () {
            var selections = file_frame.state().get('selection').map(function (selection) { selection.toJSON(); return selection; }); for (i = 0; i < selections.length; ++i) {
                thisTr.prepend('<div class="mobiconnector-content-galleries"><input type="hidden" class="mobiconnector-list-hidden-save-galleries" name="' + thisName + '[]" value="' +
                    selections[i].attributes.id + '" /><img class="mobiconnector-settings-gallary-image" src="' +
                    selections[i].attributes.url + '" /><a class="mobiconnector-delete-gallary-image" ><span class="dashicons dashicons-trash"></span></a></div>'); $('.mobiconnector-delete-gallary-image').click(function (e) { var thisClick = $(this); deleteImagesMutuple(thisClick, e); })
            }
        }); file_frame.open(); return false;
    }
    function deleteImagesMutuple(thisClick, e) { e.preventDefault(); thisClick.parent('.mobiconnector-content-galleries').remove(); return; }
    $(window).load(function () { addButtonUpload(); })
    $('#mobiconnector-add-galleries').on('click', function (event) { var thisTr = $('.mobiconnector-list-image-galleries'); var thisName = $(this).data('name'); showUploadMultipe(event, thisTr, thisName); }); $('.mobiconnector-delete-gallary-image').click(function (e) { var thisClick = $(this); deleteImagesMutuple(thisClick, e); })
})