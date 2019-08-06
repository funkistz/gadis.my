<?php

require_once './wp-load.php';

// $title = $wcmp->update_page_title('super new');

$post = json_decode(file_get_contents('php://input'), true);

// var_dump($post);

if (isset($post['order_id']) && isset($post['vendor_id'])) {

    try {

        if(isset($post['track_number']) && isset($post['url'])){

            updateOrderShipped($post['vendor_id'], $post['order_id'], $post['track_number'], $post['url']);

        }


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

function updateOrderShipped($vendor_id, $order_id, $tracking_id, $tracking_url)
{


    $vendor = get_wcmp_vendor($vendor_id);

    $vendor->set_order_shipped($order_id, $tracking_id, $tracking_url);

    header('Content-Type: application/json');
    
    echo json_encode([
        'status'  => 'success',
        'message' => 'Order successfully mark as shipped',
    ]);

}    

function updateVendor($user_id, $data)
{
    $wcmp = new WCMp_Vendor($user_id);

    if (isset($data['title'])) {
        $wcmp->update_page_title($data['title']);
        update_user_meta($user_id, '_vendor_page_title', $data['title']);
    }

    if (isset($data['slug'])) {
        $wcmp->update_page_title($data['slug']);
    }

    if (isset($data['image'])) {
        update_user_meta($user_id, '_vendor_image', $data['image']);
    }

    if (isset($data['banner'])) {
        update_user_meta($user_id, '_vendor_banner', $data['banner']);
    }

    if (isset($data['description'])) {
        update_user_meta($user_id, '_vendor_description', $data['description']);
    }

    if (isset($data['message_to_buyers'])) {
        update_user_meta($user_id, '_vendor_message_to_buyers', $data['message_to_buyers']);
    }

    if (isset($data['phone'])) {
        update_user_meta($user_id, '_vendor_phone', $data['phone']);
    }

    if (isset($data['address_1'])) {
        update_user_meta($user_id, '_vendor_address_1', $data['address_1']);
    }

    if (isset($data['address_2'])) {
        update_user_meta($user_id, '_vendor_address_2', $data['address_2']);
    }

    if (isset($data['city'])) {
        update_user_meta($user_id, '_vendor_city', $data['city']);
    }

    if (isset($data['postcode'])) {
        update_user_meta($user_id, '_vendor_postcode', $data['postcode']);
    }

    if (isset($data['state'])) {
        update_user_meta($user_id, '_vendor_state', $data['state']);
    }

    if (isset($data['country'])) {
        update_user_meta($user_id, '_vendor_country', $data['country']);
    }

    if (isset($data['facebook'])) {
        update_user_meta($user_id, '_vendor_fb_profile', $data['facebook']);
    }

    if (isset($data['twitter'])) {
        update_user_meta($user_id, '_vendor_twitter_profile', $data['twitter']);
    }

    if (isset($data['google_plus'])) {
        update_user_meta($user_id, '_vendor_google_plus_profile', $data['google_plus']);
    }

    if (isset($data['linkdin'])) {
        update_user_meta($user_id, '_vendor_linkdin_profile', $data['linkdin']);
    }

    if (isset($data['youtube'])) {
        update_user_meta($user_id, '_vendor_youtube', $data['youtube']);
    }

    if (isset($data['instagram'])) {
        update_user_meta($user_id, '_vendor_instagram', $data['instagram']);
    }
}

// function getVendor($user_id)
