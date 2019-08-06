<?php

require_once './wp-load.php';

// $title = $wcmp->update_page_title('super new');

// var_dump($title);

$post = json_decode(file_get_contents('php://input'), true);

if(empty($post)){
    $post = $_GET;
}

if (isset($post['id'])) {

    try {

        sendVerificationEmail($post['id']);
        echo json_encode([
            'status'  => 'success',
            'message' => 'Email successfully send',
        ]);

    } catch (Exception $e) {

        echo json_encode([
            'status'  => 'error',
            'message' => $e,
        ]);
    }

} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'please provide an id',
    ]);
}

function sendVerificationEmail($user_id)
{
    $wcev = new XLWUEV_Woocommerce_Confirmation_Email_Public();

    $wcev->new_user_registration($user_id);
}

// function getVendor($user_id)
