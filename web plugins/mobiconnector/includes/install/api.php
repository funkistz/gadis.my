<?php
$request_uri = '';
if (isset($_REQUEST["mobile_path"])) {
    $request_uri = $_REQUEST["mobile_path"];
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['PATH_INFO'] = $request_uri;
}
define('DB_NAME', 'databasenamewithyou');

/** MySQL database username */
define('DB_USER', 'databaseuserwithyou');

/** MySQL database password */
define('DB_PASSWORD', 'databasepasswithyou');

/** MySQL hostname */
define('DB_HOST', 'databasehostwithyou');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'databaseutfwithyou');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', 'databasecollatewithyou');

$table_prefix = 'databaseprefixwithyou';

/**      PHP      */
// Connect Database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    require_once(__DIR__ . '/index.php');
}
// Set charset
mysqli_set_charset($conn, DB_CHARSET);
// Table core
$table = $table_prefix . 'mobiconnector_sessions';
$tableoption = $table_prefix . 'options';
$use_cache = mysqli_query($conn, "SELECT option_value FROM $tableoption WHERE option_name = 'mobiconnector_settings-use-cache-mobile'");
if (!empty($use_cache) && $use_cache->num_rows !== 0) {
    $rows_use_cache = mysqli_fetch_assoc($use_cache);
    $cache = $rows_use_cache['option_value'];
    if (empty($cache)) {
        require_once(__DIR__ . '/index.php');
    }
}
$redirecturi = '';
$request_url = $_SERVER["REQUEST_URI"];
$querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : false;
$querystring = trim($querystring, '&');
$querystring = trim($querystring, '?');
$redirecturi = str_replace($querystring, "", $request_url);
$listparams = array();
if (strpos($querystring, '&') !== false) {
    $listparams = explode('&', $querystring);
    $indexparams = '';
    foreach ($listparams as $key => $params) {
        if (strpos($params, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
            $param_name = substr($params, 0, strpos($params, '='));
            if ($param_name === 'time' || $param_name === 'mobile_path') {
                $indexparams = $key;
            }
        }
    }
    if ($indexparams !== '') {
        unset($listparams[$indexparams]);
    }
    $querystring = implode('&', $listparams);
} else {
    if (strpos($querystring, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
        $param_name = substr($querystring, 0, strpos($querystring, '='));
        if ($param_name === 'time' || $param_name === 'mobile_path') {
            $querystring = false;
        }
    }
}
if (strpos($redirecturi, '?') !== false) {
    $redirecturi = substr($redirecturi, 0, strpos($redirecturi, '?'));
}
$key = $redirecturi;
$key = trim($key, '&');
$key = trim($key, '?');
if (!empty($querystring)) {
    $key = $redirecturi . '?' . $querystring;
}
$key = md5($key);
date_default_timezone_set('Etc/GMT0');
$nowgmt = date('Y-m-d H:i:s', time());

/** Get expiry */
$timeexpiry = 86400;
$timecache = mysqli_query($conn, "SELECT option_value FROM $tableoption WHERE option_name = 'mobiconnector_settings-session-expiry'");
if (!empty($timecache) && $timecache->num_rows !== 0) {
    $rowscache = mysqli_fetch_assoc($timecache);
    $timeexpiry = $rowscache['option_value'];

}
/** Delete */
$timenow = strtotime($nowgmt);
$sqldelete = "DELETE FROM $table WHERE ($timenow - session_expiry) > $timeexpiry";
mysqli_query($conn, $sqldelete);

/** Process */
$sql = "SELECT * FROM $table WHERE session_key = '$key'";
$result = mysqli_query($conn, $sql);
if (!empty($result)) {
    if ($result->num_rows !== 0) {
        $data = array();
        $rows = mysqli_fetch_assoc($result);
        $data = base64_decode($rows['session_value']);
        $data = unserialize($data);
        $time = $rows['session_expiry'];
        if (strtotime($nowgmt) - $time > $timeexpiry) {
            require_once(__DIR__ . '/index.php');
        } else {
            header("Content-type:application/json");
            echo json_encode($data);
            mysqli_free_result($result);
            mysqli_close($conn);
        }
    } else {
        require_once(__DIR__ . '/index.php');
    }
} else {
    require_once(__DIR__ . '/index.php');
}
mysqli_close($conn);
?>