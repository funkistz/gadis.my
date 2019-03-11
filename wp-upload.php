<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Origin, X-Requested-With,Content-Type, Accept, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once './wp-load.php';

// var_dump($_FILES['image']);
// echo $_FILES['image']['tmp_name'];

if (!empty($_FILES['file']['tmp_name'])) {

    $files = $_FILES['file'];

} else {
    $error = ['status' => 'error', 'message' => 'File not exist!'];
    echo json_encode($error);
    exit;
}

$file = array(
    'name'     => $files['name'],
    'type'     => $files['type'],
    'tmp_name' => $files['tmp_name'],
    'error'    => $files['error'],
    'size'     => $files['size'],
);

// var_dump($file);

$newupload = my_handle_attachment($file);

if (!empty($newupload['file'])) {

    echo json_encode($newupload);
    exit;

} else {
    $error = ['status' => 'error', 'message' => 'Upload failed!'];
    echo json_encode($error);
    exit;
}

function my_handle_attachment($file_handler)
{
    // require_once ABSPATH . "wp-admin" . '/includes/image.php';
    // require_once ABSPATH . "wp-admin" . '/includes/file.php';
    // require_once ABSPATH . "wp-admin" . '/includes/media.php';

    $file_uploaded = wp_upload_bits($file_handler['name'], null, file_get_contents($file_handler['tmp_name']));

    if (!$file_uploaded['error']) {
        $wp_filetype = wp_check_filetype($file_handler['name'], null);
        $attachment  = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent'    => 0,
            'post_title'     => preg_replace('/\.[^.]+$/', '', $file_handler['name']),
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
        $attachment_id = wp_insert_attachment($attachment, $file_uploaded['file'], 0);

        if (!is_wp_error($attachment_id)) {

            require_once ABSPATH . "wp-admin" . '/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_uploaded['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            $file_uploaded['id'] = $attachment_id;

        } else {
            $file_uploaded['id'] = 0;
        }
    }

    return $file_uploaded;
}
