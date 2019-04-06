jQuery(function ($) {
    var custom_uploader; var type = $('#type-hidden').val(); var link = $('#link-hidden').val(); $('#mobiconnector-videos-select').on('change', function (e) {
        var value = $(this).val(); if (value == type) { if (value != 'upload') { $('#mobiconnector-link-video').val(link); } else { $('#mobiconnector-upload-link').val(link); } } else { if (value != 'upload') { $('#mobiconnector-link-video').val(''); } else { $('#mobiconnector-upload-link').val(''); } }
        if (value == 'upload') { $('#input-link').hide(); $('#upload-link').show(); } else { $('#input-link').show(); $('#upload-link').hide(); }
    })
    $('#select-videos').on('click', function (e) {
        var type_video_input = $('#mobiconnector-upload-video-type'); var video_input = $('#mobiconnector-upload-link'); e.preventDefault(); if (custom_uploader) { custom_uploader.open(); return; }
        custom_uploader = wp.media.frames.file_frame = wp.media({ title: 'Save Video', button: { text: 'Save Video' }, multiple: false }); custom_uploader.on('select', function () { var selection = custom_uploader.state().get('selection'); selection.map(function (attachment) { attachment = attachment.toJSON(); var urlsm = attachment.url; var checkvideo = urlsm.split('.').pop().toUpperCase(); if (checkvideo.length < 1) { return false; } else if (checkvideo != "MP4" && checkvideo != "AVI" && checkvideo != "MOV" && checkvideo != "MPEG" && checkvideo != '3GP' && checkvideo != 'ASF' && checkvideo != 'RM' && checkvideo != 'WMV') { alert("invalid extension " + checkvideo); return false; } else { video_input.val(urlsm); } }); }); custom_uploader.open(); return false;
    })
})