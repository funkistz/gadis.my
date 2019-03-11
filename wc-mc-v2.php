<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: Origin, X-Requested-With,Content-Type, Accept, Authorization");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function isID($string)
{
    if ($string == 'vendor') {
        return true;
    }

    if (strpos($string, 'id') !== false) {
        return true;
    } else {
        return false;
    }
}

function api($header)
{

    $authorization = explode(':', $header['Authorization']);

    $woocommerce = new Client(
        'https://www.gadis.my',
        $authorization[0],
        $authorization[1],
        [
            'wp_api'  => true,
            'version' => $authorization[2],
            'timeout' => 30,
	    'query_string_auth' => true
        ]
    );

    $data = json_decode(file_get_contents('php://input'), true);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $data = $_REQUEST;
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
    }

    // var_dump($data);
    // $data = json_encode($data);
    // $data = json_decode($data);

    foreach ($data as $key => $value) {

        // echo $value;

        if (isJson($value)) {
            $data[$key] = json_decode($data[$key]);
        }

        if (!isID($key) && is_numeric($value)) {

            $data[$key] = strval($value);
        }
    }

    if (!empty($data['images'])) {

        foreach ($data['images'] as $key => $value) {
            $value->position = $key;
        }

    }

    // var_dump($data);
    // exit;

    $response = [];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	
	$response = $woocommerce->get($authorization[3], $data);

    }if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (empty($authorization[4])) {
            echo json_encode(['error' => 'missing method type']);
            exit;
        }

        if (strcasecmp($authorization[4], 'post') == 0) {
            $response = $woocommerce->post($authorization[3], $data);

        } else
        if (strcasecmp($authorization[4], 'put') == 0) {
            $response = $woocommerce->put($authorization[3], $data);

        } else
        if (strcasecmp($authorization[4], 'delete') == 0) {
            $response = $woocommerce->delete($authorization[3], $data);

        }

    }if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

        // echo json_encode($data);

        $response = $woocommerce->put($authorization[3], $data);

    }if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

        $response = $woocommerce->delete($authorization[3], $data);

    }

    // var_dump($response);
    if( isJson($response) ){
   	 echo json_encode($response);
    }else{
	echo 'Error in API';
    }

}

function checkHeaderAndRequest()
{
    if (!empty(getallheaders())) {

        $header = getallheaders();
        $header2 = apache_request_headers();
        
        $authorization = explode(':', $header['Authorization']);
        
//        echo $header['Authorization'] . '<br>  ';
//        echo $header['authorization'] . '<br>  ';
//        echo $header2['Authorization'] . '<br>  ';
//        echo $header2['authorization'];
        //echo $header['x-api-key'] . '<br>  ';
	
        //echo print_r($header['authorization']). '<br>  ';
        //echo print_r($header['x-api-key']);
        //exit;
        
        if( empty($header['Authorization']) ){
            $header['Authorization'] = $header['authorization'];
        }
	
        if( empty($header['Authorization']) ){
            $header['Authorization'] = $header['x-api-key'];
        }
	
        if (
            !empty($header['Authorization'])
        ) {

            api($header);

        } else {

            throw new Exception("Missing some required Header, please check if you already send all the required Header.");

        }

    }

}

try {

    checkHeaderAndRequest();

} catch (Exception $e) {

    echo $e->getMessage();

}
