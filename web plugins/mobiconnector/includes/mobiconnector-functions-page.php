<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check missing param and invalid params
 * 
 * @since 1.1.5
 * 
 * @param WP_REST_Response  $response Result to send to the client.
 * 
 * @return bool
 */
function bamobile_mobiconnector_is_missing_and_invalid_params($response){
    global $wp_version;
    if($wp_version < '4.8.1'){
        $data = $response->get_data();
    }else{
        $data = $response;
    }
    if(isset($data['code']) && (strpos($data['code'],'rest_missing_callback_param') !== false || strpos($data['code'],'rest_invalid_param') !== false)){
        return true;
    }
    if(isset($data['code']) && strpos($data['code'],'rest_no_route') !== false){
        return true;
    }
    return false;
}

/**
 * Check if calculator page
 * 
 * @since 1.1.5
 * 
 * @return bool
 */
function bamobile_mobiconnector_is_wooconnector_pages_calculator(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || isset($aUrl[1]) &&  $aUrl[1] != 'wooconnector' || isset($aUrl[2]) && $aUrl[2] != 'calculator'){
        return false;
    }    
    $lUrl = array_pop($aUrl); 
    if($lUrl == 'getall' || $lUrl == 'addcoupons' || $lUrl == 'getshipping' || $lUrl == 'gettotal' || $lUrl == 'getpayment'){
        return true;
    }else{
        return false;
    }
}

/**
 * Check if checkout page
 * 
 * @since 1.1.5
 * 
 * @return bool
 */
function bamobile_mobiconnector_is_wooconnector_pages_checkout(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || isset($aUrl[1]) &&  $aUrl[1] != 'wooconnector' || isset($aUrl[2]) && $aUrl[2] != 'checkout'){
        return false;
    }    
    $lUrl = array_pop($aUrl); 
    if($lUrl == 'processcheckout'){
        return true;
    }else{
        return false;
    }
}

/**
 * Check if orders page
 * 
 * @since 1.1.5
 * 
 * @return bool
 */
function bamobile_mobiconnector_is_wooconnector_pages_orders(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || isset($aUrl[1]) &&  $aUrl[1] != 'wooconnector' || isset($aUrl[2]) && $aUrl[2] != 'order'){
        return false;
    }    
    $lUrl = array_pop($aUrl); 
    if($lUrl == 'getorderbyterm' || $lUrl == 'getorderbyid'){
        return true;
    }else{
        return false;
    }
}

/**
 * Check if request is Rest post
 * 
 * @since 1.1.5
 * 
 * @return boolean
 */
function bamobile_mobiconnector_is_rest_api(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(bamobile_mobiconnector_is_wooconnector_pages_calculator()){
        return false;
    }
    if(bamobile_mobiconnector_is_wooconnector_pages_orders()){
        return false;
    }
    if(bamobile_mobiconnector_is_wooconnector_pages_checkout()){
        return false;
    }
    if(!isset($aUrl[0]) || isset($aUrl[0]) && $aUrl[0] != 'wp-json' || !isset($aUrl[1]) || $_SERVER['REQUEST_METHOD'] !== 'GET'){
        return false;
    }
    return true;    
}

/**
 * Check if request is Rest post
 * 
 * @since 1.1.5
 * 
 * @return boolean
 */
function bamobile_mobiconnector_is_rest_posts(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || isset($aUrl[1]) &&  $aUrl[1] != 'wp' || isset($aUrl[2]) &&  $aUrl[2] != 'v2'){
        return false;
    }
    $lUrl = array_pop($aUrl); 
    if($lUrl == 'posts'){
        return true;
    }else{
        return false;
    }
}

/**
 * Check if request is Rest post details
 * 
 * @since 1.1.5
 * 
 * @return boolean
 */
function bamobile_mobiconnector_is_rest_posts_detail(){
    $url = $_SERVER['REQUEST_URI'];
    $sUrl = substr($url,strpos($url,'wp-json'));
    $oUrl = $sUrl;
    if(strpos($sUrl,'?') != false){
        $oUrl = substr($sUrl,0,strpos($sUrl,'?'));
    }
    $tUrl = trim($oUrl,'/');
    $aUrl = explode('/',$tUrl);
    if(isset($aUrl[0]) && $aUrl[0] != 'wp-json' || isset($aUrl[1]) && $aUrl[1] != 'wp' || isset($aUrl[2]) &&  $aUrl[2] != 'v2' || isset($aUrl[3]) &&  $aUrl[3] != 'posts'){
        return false;
    }
    $lUrl = array_pop($aUrl);
    if(is_numeric($lUrl) && (int)$lUrl > 0){
        return true;
    }else{
        return false;
    }
}

/**
 * Display template table notice
 * 
 * @since 1.1.5
 */
function bamobile_display_notification(){
	require_once( MOBICONNECTOR_ABSPATH . 'settings/onesignal/class-mobiconnector-table-notice.php');	
	$notification = new BAMobile_MobiconnectorTableNotice();
	echo $notification->current_action();
	$notification->prepare_items();
	$notification->display();
}

/**
 * Display template table player
 * 
 * @since 1.1.5
 */
function bamobile_display_player(){
	require_once( MOBICONNECTOR_ABSPATH . 'settings/onesignal/class-mobiconnector-table-player.php');	
	$player = new BAMobile_MobiconnectorTablePlayer();
	$title = __("All Users","woocommerce");
	?>
	<div class="wrap">
		<h1>
			<?php echo esc_html( $title );?>				
		</h1>
	</div>		
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=onesignal&actions=player" id="settings-form-player">
	<?php
		$player->views();
		echo $player->current_action();
		$player->prepare_items();
		$player->display();
	?>
	</form>
	<?php
}

/**
 * Display extension templates
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_display_extensions(){
    require_once( MOBICONNECTOR_ABSPATH . 'includes/admin/tables/class-mobiconnector-table-extentios.php');	
    if(class_exists('BAMobile_Table_Extentions')){
        $extensions = new BAMobile_Table_Extentions();
?>
<form class="search-form search-extensions" action="admin.php?page=mobiconnector-extensions" method="get">
<?php
        $extensions->views();
        echo $extensions->current_action();
        $extensions->prepare_items();
        $extensions->then_params_method_get();
        $extensions->search_box('Search','mobiconnector-extensions-search');
?>
</form>
<form method="post" class="mobiconnector-setting-form" id="bulk-action-form">
<?php 
        $extensions->display();
    }
?>
</form>
<?php
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check missing param and invalid params
 * 
 * @param WP_REST_Response  $response Result to send to the client.
 * 
 * @return bool
 */
function mobiconnector_is_missing_and_invalid_params($response){
    return bamobile_mobiconnector_is_missing_and_invalid_params($response);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if calculator page
 * 
 * @return bool
 */
function mobiconnector_is_wooconnector_pages_calculator(){
    return bamobile_mobiconnector_is_wooconnector_pages_calculator();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if checkout page
 * 
 * @return bool
 */
function mobiconnector_is_wooconnector_pages_checkout(){
    return bamobile_mobiconnector_is_wooconnector_pages_checkout();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if orders page
 * 
 * @return bool
 */
function mobiconnector_is_wooconnector_pages_orders(){
    return bamobile_mobiconnector_is_wooconnector_pages_orders();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if request is Rest post
 * 
 * @return boolean
 */
function mobiconnector_is_rest_api(){
    return bamobile_mobiconnector_is_rest_api();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if request is Rest post
 * 
 * @return boolean
 */
function mobiconnector_is_rest_posts(){
    return bamobile_mobiconnector_is_rest_posts();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check if request is Rest post details
 * 
 * @return boolean
 */
function mobiconnector_is_rest_posts_detail(){
    return bamobile_mobiconnector_is_rest_posts_detail();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Display template table notice
 * 
 */
function MobiconnectordisplayNotification(){
    bamobile_display_notification();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Display template table player
 */
function MobiconnectordisplayPlayer(){
    bamobile_display_player();
}
?>