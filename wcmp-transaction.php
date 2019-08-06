<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Origin, X-Requested-With,Content-Type, Accept, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once './wp-load.php';

// $title = $wcmp->update_page_title('super new');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $post = $_GET;
}else{
    $post = json_decode(file_get_contents('php://input'), true);
}

try {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if($post['withdrawal']){
            getWithdrawal($post['vendor_id'], $post['status']);
        }else{
            getTransaction($post['vendor_id']);
        }

    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $response = requestWithdrawal($post['vendor_id'], $post['commission_id']);

        echo json_encode($response);
    }


} catch (Exception $e) {

    echo json_encode([
        'status'  => 'error',
        'message' => $e,
    ]);
}

function getWithdrawal($vendor_id, $status = 'all')
{

    global $WCMp;

    $vendor = get_wcmp_vendor($vendor_id);

    $requestData = $_REQUEST;

    $meta_query['meta_query'] = array(

        array(

            'key' => '_paid_status',

            'value' => 'unpaid',

            'compare' => '='

        ),

        array(

            'key' => '_commission_vendor',

            'value' => absint($vendor->term_id),

            'compare' => '='

        )

    );

    $vendor_unpaid_total_orders = $vendor->get_orders(false, false, $meta_query);

    // if (isset($requestData['start']) && isset($requestData['length'])) {

        $vendor_unpaid_orders = $vendor->get_orders(false, false, $meta_query);

    // }

    $data = array();

    $commission_threshold_time = isset($WCMp->vendor_caps->payment_cap['commission_threshold_time']) && !empty($WCMp->vendor_caps->payment_cap['commission_threshold_time']) ? $WCMp->vendor_caps->payment_cap['commission_threshold_time'] : 0;

    if ($vendor_unpaid_orders) {

        foreach ($vendor_unpaid_orders as $commission_id => $order_id) {

            $order = wc_get_order($order_id);

            $vendor_share = get_wcmp_vendor_order_amount(array('vendor_id' => $vendor->id, 'order_id' => $order->get_id()));

            if (!isset($vendor_share['total'])) {

                $vendor_share['total'] = 0;

            }

            $commission_create_date = get_the_date('U', $commission_id);

            $current_date = date('U');

            $diff = intval(($current_date - $commission_create_date) / (3600 * 24));

            if ($diff < $commission_threshold_time) {
                continue;
            }

            if (is_commission_requested_for_withdrawals($commission_id)) {
                $disabled_reqested_withdrawals = 'disabled';
            } else {
                $disabled_reqested_withdrawals = '';
            }

            //skip withdrawal for COD order and vendor end shipping
            if($order->get_payment_method() == 'cod' && $vendor->is_shipping_enable()) continue;

            if($status != 'all'){

                if($status == 'pending'){

                    if (is_commission_requested_for_withdrawals($commission_id)) {

                        continue;
                    }

                }

                if($status == 'requested'){

                    if (!is_commission_requested_for_withdrawals($commission_id)) {
                        continue;
                    }
                    
                }

            }

            $row = array();

            $row ['withdrawable'] = $disabled_reqested_withdrawals;

            $row ['commission_id'] = $commission_id;

            $row ['order_id'] = $order->get_id();

            $row ['commission_amount'] = $vendor_share['commission_amount'];

            $row ['shipping_amount'] = $vendor_share['shipping_amount'];

            $row ['tax_amount'] = $vendor_share['tax_amount'];

            $row ['total'] = $vendor_share['total'];

            $data[] = apply_filters('wcmp_vendor_withdrawal_list_row_data', $row, $commission_id);

        }

    }
    
    echo json_encode($data);

}   

function getTransaction($vendor_id)
{
    global $WCMp;

    $total_amount = 0;
    $transaction_display_array = array();
    $vendor = get_wcmp_vendor($vendor_id);
    $requestData = $_REQUEST;

    $vendor = apply_filters('wcmp_transaction_vendor', $vendor);

    $transaction_details = $WCMp->transaction->get_transactions($vendor->term_id);
    $unpaid_orders = get_wcmp_vendor_order_amount(array('commission_status' => 'unpaid'), $vendor->id);
    $count = 0; // varible for counting 5 transaction details

    foreach ($transaction_details as $transaction_id => $details) {
        $count++;
        if ($count <= 5) {
            //$transaction_display_array[$transaction_id] = $details['total_amount'];
            //$transaction_display_array['id'] = $transaction_id;

            array_push($transaction_display_array, [
                'id' => $details['id'],
                'transaction_date' => wcmp_date($details['post_date']),
                'total_amount' => $details['total_amount'],
            ]);
        }

        $total_amount = $total_amount + $details['total_amount'];
    }

    $data = [
        'total_amount' => $total_amount,
        'transactions' => $transaction_display_array,
    ];
    
    echo json_encode($data);

} 

function requestWithdrawal($vendor_id, $commissions) {
    global $WCMp;

    $vendor = get_wcmp_vendor($vendor_id);

    if (!empty($commissions)) {

        $payment_method = get_user_meta($vendor->id, '_vendor_payment_mode', true);
        
        if ($payment_method) {
            if (array_key_exists($payment_method, $WCMp->payment_gateway->payment_gateways)) {
                $response = $WCMp->payment_gateway->payment_gateways[$payment_method]->process_payment($vendor, $commissions, 'manual');
                if ($response) {
                    if (isset($response['transaction_id'])) {

                        // $notice = $this->get_wcmp_transaction_notice($response['transaction_id']);

                        if (isset($notice['type'])) {
                            return [
                                'status' => 'success',
                                'message' => $notice['message']
                            ]; 
                        }
                        return [
                            'status' => 'success',
                            'message' => 'Successfully Requested'
                        ]; 
                    } else {
                        foreach ($response as $message) {

                            return $response;

                            return [
                                'status' => 'error',
                                'message' => $message['message']
                            ];
                        }
                    }
                } else {
                    return [
                        'status' => 'error',
                        'message' => 'Oops! Something went wrong please try again later',
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Invalid payment method',
                ];
            }
        } else {
            return [
                'status' => 'error',
                'message' => 'No payment method has been selected for commission withdrawal'
            ];
        }
    } else {
        
        return [
            'status' => 'error',
            'message' => 'Please select atleast one or more commission'
        ];

    }
}