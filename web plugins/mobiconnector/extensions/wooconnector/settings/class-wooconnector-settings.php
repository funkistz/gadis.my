<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class WooConnectorSettings{
	
	private $rest_url = 'wooconnector/settings';

	public function __construct(){
		$this->register_hooks();	
		$checkreload = get_option('wooconnector_reload_remove_notice');
        if(empty($checkreload)){
            update_option('wooconnector_reload_remove_notice',0);
        }
        $pageRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && ($_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0' ||  $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache'); 
        if($pageRefreshed == 1){
            $checkmessage = get_option('wooconnector_reload_check_message');
            if($checkmessage != '' && $checkmessage == 'yes'){
                $reload = get_option('wooconnector_reload_remove_notice');
                if($reload < 2){
                    $reload = $reload + 1;
                    update_option('wooconnector_reload_remove_notice',$reload);
                }
            }
        }
	}

	public function register_hooks(){
		add_action('admin_menu',array($this,'create_menu_to_admin'),10);
		add_action('rest_api_init', array( $this, 'register_api_hooks'));
		add_action( 'wp_ajax_wooconnector_get_rate_by_ajax', array($this,'convertcurrency' ));
	}
	
	public function register_api_hooks() {
		register_rest_route( $this->rest_url, '/getactivelocaltion', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getactivelocaltion' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						
					),					
			) 
		);
		register_rest_route( $this->rest_url, '/deletecurrency', array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'deleteCurrency' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'currencykey' => array(
							'sanitize_callback' => 'esc_sql'
						)
					),					
			) 
		);
		register_rest_route( $this->rest_url, '/getsettingscurrency', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getsettingscurrency' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
					),					
			) 
		);
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if(is_plugin_active('mobiconnector/mobiconnector.php')){
			$usekey = get_option('mobiconnector_settings-use-security-key');
			if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
				return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}
		return true;
	}

	public function convertcurrency(){
		check_ajax_referer( 'wooconnector-security-get-rates-ajax', 'security' );
		$from = sanitize_text_field($_POST['from']);       
        $to = sanitize_text_field($_POST['to']);
		$rate = WooConnectorConvertRateOnGoogle($from,$to);
		echo $rate;
		wp_die(); 
	}

	public function getsettingscurrency($request){		
		$currencys = get_option('wooconnector_currency_settings');
		$currencys = unserialize($currencys);
		$currency_code_options = get_woocommerce_currencies();
		$listcurrency = array();
		$basecurrency = WooConnectorGetBaseCurrency();
		$basecurrencyout = array();
		if(!empty($currencys)){
			foreach ( $currency_code_options as $code => $name ) {
				foreach($currencys as $currency){
					if(strtoupper($currency['currency']) == $code && $code == $basecurrency){
						$basecurrencyout = array(
							'code' => strtolower($currency['currency']),
							'name' => $name,
							'rate' => $currency['rate'],
							'symbol' => $currency['symbol'],
							'position' => $currency['position'],
							'thousand_separator' => $currency['thousand_separator'],
							'decimal_separator' => $currency['decimal_separator'],
							'number_of_decimals' => $currency['number_of_decimals']
						);
					}
				}
			}
			foreach ( $currency_code_options as $code => $name ) {
				foreach($currencys as $currency){
					if(strtoupper($currency['currency']) == $code){
						$listout[] = array(
							'code' => strtolower($currency['currency']),
							'name' => $name,
							'rate' => $currency['rate'],
							'symbol' => $currency['symbol'],
							'position' => $currency['position'],
							'thousand_separator' => $currency['thousand_separator'],
							'decimal_separator' => $currency['decimal_separator'],
							'number_of_decimals' => $currency['number_of_decimals']
						);
					}
				}
			}
			$listcurrency = array(
				'basecurrency' => $basecurrencyout,
				'listcurrency' => $listout
			);
		}else{
			foreach ( $currency_code_options as $code => $name ) {
				if(get_woocommerce_currency() == $code && $code == $basecurrency){
					$basecurrencyout = array(
						'code' => strtolower(get_woocommerce_currency()),
						'name' => $name,
						'rate' => 1,
						'symbol' => get_woocommerce_currency_symbol(),
						'position' => get_option( 'woocommerce_currency_pos' ),
						'thousand_separator' => wc_get_price_thousand_separator(),
						'decimal_separator' => wc_get_price_decimal_separator(),
						'number_of_decimals' => wc_get_price_decimals()
					);
				}
			}
			foreach ( $currency_code_options as $code => $name ) {
				if(get_woocommerce_currency() == $code){
					$listout[] = array(
						'code' => strtolower(get_woocommerce_currency()),
						'name' => $name,
						'rate' => 1,
						'symbol' => get_woocommerce_currency_symbol(),
						'position' => get_option( 'woocommerce_currency_pos' ),
						'thousand_separator' => wc_get_price_thousand_separator(),
						'decimal_separator' => wc_get_price_decimal_separator(),
						'number_of_decimals' => wc_get_price_decimals()
					);
				}
			}
			$listcurrency = array(
				'basecurrency' => $basecurrencyout,
				'listcurrency' => $listout
			);			
		}
		return $listcurrency;
	}

	public function getactivelocaltion($request){
		$countries = get_option('wooconnector_settings-countries');
		$listcountry = array();
		$woocommerce_allowed_countries = get_option('woocommerce_allowed_countries');
		if($woocommerce_allowed_countries == 'specific'){
			$woocommerce_specific_allowed_countries = get_option('woocommerce_specific_allowed_countries');
			if(!empty($woocommerce_specific_allowed_countries)){
				if(is_string($countries)){
					foreach(unserialize($countries) as $country => $value){
						foreach($woocommerce_specific_allowed_countries as $specific){
							if($value['value'] == $specific){
								$listcountry[] = array(
									'value' => $value['value'],
									'name'  => $value['name']
								);
							}
						}
					}
				}else{
					foreach($countries as $country => $value){
						foreach($woocommerce_specific_allowed_countries as $specific){
							if($value['value'] == $specific){
								$listcountry[] = array(
									'value' => $value['value'],
									'name'  => $value['name']
								);
							}
						}
					}
				}
			}
		}elseif($woocommerce_allowed_countries == 'all_except'){
			$woocommerce_all_except_countries = get_option('woocommerce_all_except_countries');
			if(!empty($woocommerce_all_except_countries)){
				if(is_string($countries)){
					foreach(unserialize($countries) as $country => $value){
						foreach($woocommerce_all_except_countries as $allex){
							if($value['value'] != $allex){
								$listcountry[] = array(
									'value' => $value['value'],
									'name'  => $value['name']
								);
							}
						}
					}
				}else{
					foreach($countries as $country => $value){
						foreach($woocommerce_all_except_countries as $allex){
							if($value['value'] != $allex){
								$listcountry[] = array(
									'value' => $value['value'],
									'name'  => $value['name']
								);
							}
						}
					}
				}
			}
		}else{
			if(is_string($countries)){					
				$listcountry = unserialize($countries);
			}else{
				$listcountry = $countries;
			}
		}				
		$states = get_option('wooconnector_settings-states');
		if(is_string($states)){
			$states = unserialize($states);
		}
		$list = array();
		if(!empty($listcountry)){
			$list = array(
				'countries' => $listcountry,
				'states' => $states
			);
		}		
		return $list;
	}

	public function deleteCurrency($request){
		$params = $request->get_params();
		$key = $params['currencykey'];
		$currencys = get_option('wooconnector_currency_settings');
		$currencys = unserialize($currencys);
		if(!empty($currencys[$key])){
			unset($currencys[$key]);
			update_option('wooconnector_currency_settings',serialize($currencys));
			$_SESSION['wooconnector_session_notice_type'] = 'success';
			$_SESSION['wooconnector_session_notice_message'] = __('Successful deletion','wooconnector');
			update_option('wooconnector_reload_remove_notice',0);
			update_option('wooconnector_reload_check_message','yes');
			wp_redirect( '?page=wooconnector&wootab=currency' );
		}else{
			return false;
		}
	}

	public function create_menu_to_admin(){
		add_menu_page(
			__('Mobile App','wooconnector'),
			__('Mobile App','wooconnector'),
			'manage_options',
			'wooconnector',
			'',
			plugins_url( 'wooconnector/assets/images/smartphone.svg', WOOCONNECTOR_ABSPATH ),
            35
		);
		$parent_slug = 'wooconnector';
        add_submenu_page(
            $parent_slug,
            __('Settings'),
            __('Settings'),
            'manage_options',
            'wooconnector',
            array($this,'action_create_menu')
		);
		$parent_slug = 'wooconnector';
        add_submenu_page(
            $parent_slug,
            __('Notifications'),
            __('Notifications'),
            'manage_options',
            'woo-notifications',
            array($this,'action_onesignal_menu')
        );
	}

	public function action_onesignal_menu(){
		$task = isset($_REQUEST['wootask']) ? $_REQUEST['wootask'] : '';
		$tab = isset($_REQUEST['wootab']) ? $_REQUEST['wootab'] : 'settings';
		if($tab == 'api'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/onesignal-form.php');
		}elseif($tab == 'new'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/content-setting.php');
		}elseif($tab == 'list'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/list-notification.php');
		}elseif($tab == 'player'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/list-user.php');
		}elseif($tab == 'viewnotification'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/details-notification.php');
		}else{
			require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/onesignal-form.php');
		}	
		if(!empty($task) || $task != ''){	
			switch($task){
				case 'saveonesignal':
				$this->Woo_SaveOnesignal();
				break;
				case 'saveonesignal-content':
					$this->Woo_SaveContent();
				break;
				case 'changeTesttype':
					$nonce = esc_attr( $_REQUEST['_wpnonce'] );
					if ( ! wp_verify_nonce( $nonce, 'wooconnector_change_testtype' ) ) {
					die( 'Go get a life script kiddies' );
					}
					else {
						$player = $_REQUEST['player'];
						$device = $_REQUEST['device'];
						$section = 	$_REQUEST['section'];
						$this->update_type_player($player,$device,$section);
					}
				break;
			}
		}			
	}

	public function action_create_menu(){
		$task = isset($_REQUEST['wootask']) ? $_REQUEST['wootask'] : '';
		$tab = isset($_REQUEST['wootab']) ? $_REQUEST['wootab'] : 'settings';
		if($tab == 'settings'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/views/settings-form.php');
		}elseif($tab == 'currency'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/currency/settingscurrency.php');
		}elseif($tab == 'design'){
			require_once(WOOCONNECTOR_ABSPATH.'settings/views/design-form.php');
		}else{
			require_once(WOOCONNECTOR_ABSPATH.'settings/views/settings-form.php');
		}
		if(!empty($task) || $task != ''){	
			switch($task){
				case 'savesetting':
					$this->Woo_saveSetting();
				break;
				case 'saveapplicationdesign':
					$this->Woo_SaveDesign();
				break;
				case 'savesettingcurrency':
					$this->Woo_SaveSetiingsCurrency();
				break;
				case 'changeTesttype':
					$nonce = esc_attr( $_REQUEST['_wpnonce'] );
					if ( ! wp_verify_nonce( $nonce, 'wooconnector_change_testtype' ) ) {
					  die( 'Go get a life script kiddies' );
					}
					else {
						$player = $_REQUEST['player'];
						$device = $_REQUEST['device'];
						$section = 	$_REQUEST['section'];
						$this->update_type_player($player,$device,$section);
					}
				break;
			}
		}
	}	

	private function Woo_SaveDesign(){
		$action = isset( $_POST['action'] ) ? $_POST['action'] : bamobile_get_first_menu_in_design();
		$current_tab = isset( $_REQUEST[ 'current_tab' ] ) ? $_REQUEST[ 'current_tab' ] : 'homepage';
		$result['wooconnector_settings-design'][$current_tab] = @$_POST['wooconnector_settings-design'];
		$db_result = get_option('wooconnector_settings-design');
		if(!empty($db_result) && is_string($db_result)){
			$db_result = unserialize($db_result);
		}elseif(!empty($db_result) && is_array($db_result)){
			$db_result = $db_result;
		}else{
			$db_result = array();
		}
		$db_result[$action] = $result;
		update_option('wooconnector_settings-design', serialize($db_result));
		bamobile_mobiconnector_add_notice(__('Successfully Update','wooconnector')); 
        wp_redirect( '?page=wooconnector&wootab=design&action='.$action );
	}
	
	private function Woo_saveSetting(){		
		update_option('wooconnector_settings-mail',esc_sql(@$_POST['wooconnector_settings-mail']));	
		update_option('wooconnector_settings-custom-attribute',esc_sql(@$_POST['wooconnector_settings-custom-attribute']));
		$defaultsearch = array(
			'name' => '1'
		);
		if(!empty($_POST['wooconnector_settings-search'])){
			$listactivesearch = array_merge($_POST['wooconnector_settings-search'],$defaultsearch);
		}else{
			$listactivesearch = $defaultsearch;
		}
		$listactivesearch = serialize($listactivesearch);
		update_option('wooconnector_settings-search',$listactivesearch);
		update_option('wooconnector_settings-change-price',esc_sql(@$_POST['wooconnector_settings-change-price']));	
		$firstvalues = @$_POST['wooconnector_settings_countries'];
		$oldvalues = trim($firstvalues,',');
		$values = explode(',',$oldvalues);
		$ct = new WC_Countries();
		$coutries = $ct->get_countries();
		foreach($values as $val){								
			foreach($coutries as $country => $value){							
				if($val == $country){
					$list = array(
						'value' => $country,
						'name' => $value
					);
				}
			}
			$out[] = $list;
		}	
		$out = serialize($out);	
		update_option('wooconnector_settings-countries',$out);
		
		$firststates = @$_POST['wooconnector_settings_states'];
		update_option('wooconnector_settings-first-states',$firststates);
		$oldstates = trim($firststates,',');		
		$valuestates = explode(',',$oldstates);
		$states = $ct->get_states();
		foreach($states as $sta => $values){
			$listst = array();
			foreach($valuestates as $valuestate){
				$country = substr($valuestate,0,strpos($valuestate,'-'));
				$state = substr($valuestate,strpos($valuestate,'-')+1);		
				if($country == $sta && !empty($values)){					
					foreach($values as $key => $val){				
						if($state == $key){
							$listst[] = array(
								'value' => $key,
								'name' => $val
							);							
						}								
					}
					$allstates[$sta] = $listst;	
				}					
			}
		}	
		$allstates = serialize($allstates);	
		update_option('wooconnector_settings-states', $allstates);
		bamobile_mobiconnector_add_notice(__('Successfully Update','wooconnector')); 
		wp_redirect( '?page=wooconnector&wootab=settings' );
		
	}

	private function Woo_SaveSetiingsCurrency(){
		$currencys = @$_POST["wooconnector_currency_settings"];
		$listout = array();
		$symbols = WooConnectorListSymbolCurrency();
		$listcheckcurrencys = WooConnectorListCurrency();
		$listcurrencys = array();
		foreach($currencys as $currency => $value){
			$value['symbol'] = get_woocommerce_currency_symbol($value['currency']);
			$listout[$currency] = $value;
			if (!preg_match ('/[a-zA-Z]/', $currency)) {
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('Currency Code is invalid.','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			if(in_array($currency,$listcurrencys)){
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('The currencies is duplicated.','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			if(!in_array(strtoupper($currency),$listcheckcurrencys)){
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('Currency Code is invalid.','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			if(!is_numeric($value['rate'])){
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('Rate is invalid.','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			if($value['rate'] == ''){
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('Rate is required.','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			if(!preg_match ('/[0-9]/', $value['number_of_decimals'])){
				$_SESSION['wooconnector_session_notice_type'] = 'error';
				$_SESSION['wooconnector_session_notice_message'] = __('Number of decimals is invalid. Please specify a valid number [0-9].','wooconnector');
				update_option('wooconnector_reload_remove_notice',0);
				update_option('wooconnector_reload_check_message','yes');
				wp_redirect( '?page=wooconnector&wootab=currency' );
				return false;
			}
			array_push($listcurrencys,$currency);
		}
		update_option('wooconnector_currency_settings',serialize($listout));
		$_SESSION['wooconnector_session_notice_type'] = 'success';
		$_SESSION['wooconnector_session_notice_message'] = __('Successfully Update','wooconnector');
		update_option('wooconnector_reload_remove_notice',0);
		update_option('wooconnector_reload_check_message','yes');
		wp_redirect( '?page=wooconnector&wootab=currency' );
	}

	private function Woo_SaveOnesignal(){
		$apiid = esc_sql(@$_POST["wooconnector-app-id-onesignal"]);
		$restapikey = esc_sql(@$_POST["wooconnector-rest-api-key-onesignal"]);
		if(strlen($apiid) != 36){
			bamobile_mobiconnector_add_notice(__('Your APP ID must be 36 characters. Please retype','wooconnector'),'error'); 
			wp_redirect( '?page=woo-notifications&wootab=api' );
			return true;
		}
		if(strlen($restapikey ) != 48){
			bamobile_mobiconnector_add_notice(__('Your REST API KEY must be 48 characters. Please retype','wooconnector'),'error'); 
			wp_redirect( '?page=woo-notifications&wootab=api' );
			return true;
		}
		$apiid = trim($apiid);   
		$restapikey = trim($restapikey);
		update_option('wooconnector_settings-api',$apiid);
		update_option('wooconnector_settings-restkey',$restapikey);
		$mobiapi = get_option('mobiconnector_settings-onesignal-api');
		$mobirest = get_option('mobiconnector_settings-onesignal-restkey');
		if(empty($mobiapi) && empty($mobirest)){
			update_option('mobiconnector_settings-onesignal-api',$apiid);
			update_option('mobiconnector_settings-onesignal-restkey',$restapikey);
		}
		global $wpdb;
		$table_name = $wpdb->prefix . "wooconnector_data_api";
		$table_mobi_name = $wpdb->prefix . "mobiconnector_data_api";
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name
			WHERE api_key = '$apiid'
			"
		);
		$checkdata = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_mobi_name
			WHERE api_key = '$apiid'
			"
		);
		if(empty($datas)){
			$table_name = $wpdb->prefix . "wooconnector_data_api";			
			$wpdb->insert(
				"$table_name",array(
					"api_key" => $apiid,
					"rest_api" => $restapikey,				
				),
				array( 
					'%s', 
					'%s'
				) 
			);
		}	
		if(empty($checkdata)){              	
			$wpdb->insert(
				"$table_mobi_name",array(
					"api_key" => $apiid,
					"rest_api" => $restapikey,				
				),
				array( 
					'%s', 
					'%s'
				) 
			);
		}	
		bamobile_mobiconnector_add_notice(__('Successfully Update','mobiconnector')); 
		wp_redirect( '?page=woo-notifications&wootab=api' );
	}

	private function Woo_SaveContent(){		
	
		$title = esc_sql(@$_POST['wooconnector-web-title-notification']);
		update_option('wooconnector_settings-title',$title);
		
		$content = esc_sql(@$_POST['wooconnector-web-content-notification']);
		update_option('wooconnector_settings-content',$content);
		
		$images = @$_POST['wooconnector-web-icon-notification'];	
		if(isset($images) && $images != ''){	
			$this->update_thumnail_wooconnector($images,'wooconnector_notification_icon');
			update_option('wooconnector_settings-id-icon',$images);
		}else{
			update_option('wooconnector_settings-id-icon',$images);
			update_option('wooconnector_notification_icon','');
		}	
		
		$small = @$_POST['wooconnector-web-smicon-notification'];
		if(isset($small) && $small != ''){
			$this->update_thumnail_wooconnector($small,'wooconnector_notification_icon_small');
			update_option('wooconnector_settings-sm-icon',$small);
		}else{
			update_option('wooconnector_notification_icon_small','');
			update_option('wooconnector_settings-sm-icon',$small);
		}		
				
		$selectedurl = @$_POST['wooconnector-web-url-select-notification'];
		update_option('wooconnector_settings-url-selected',$selectedurl);
		if($selectedurl == 'url-product'){
			$url = @$_POST['wooconnector-web-url-notification-url-product'];			
			if(isset($url) && $url != ''){
				if(strpos($url,'link://') !== false){
					update_option('wooconnector_settings-push-url',$url);				
				}else{
					update_option('wooconnector_settings-url',$url);
					$product_id = url_to_postid($url);
					if(!empty($product_id)) {
						$newurl =  str_replace($url, 'link://product/'.$product_id, $url);
						update_option('wooconnector_settings-push-url',$newurl);
					}else{
						bamobile_mobiconnector_add_notice(__('Your URL is not Product URL. Please retype','wooconnector'),'error'); 
						wp_redirect( '?page=woo-notifications&wootab=api' );
						return true;
					}
				}
			}
		}elseif($selectedurl == 'url-category'){
			$url = @$_POST['wooconnector-web-url-notification-url-category'];
			if(isset($url) && $url != ''){
				update_option('wooconnector_settings-url',$url);
				if(strpos($url,'link://') !== false){
					update_option('wooconnector_settings-push-url',$url);
				}elseif(strpos($url,'product-category') !== false){
					$url_split = explode('#', $url);
					$url = $url_split[0];

							// Get rid of URL ?query=string
					$url_split = explode('?', $url);
					$url = $url_split[0];
							
					$scheme = parse_url( home_url(), PHP_URL_SCHEME );
					$url = set_url_scheme( $url, $scheme );
								
					if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
					$url = str_replace('://', '://www.', $url);
								
					if ( false === strpos(home_url(), '://www.') )
					$url = str_replace('://www.', '://', $url);
								
					$url = trim($url, "/");
					$slugs = explode('/', $url);				
					$category = $this->get_product_category_by_slug('/'.end($slugs));
					if(!empty($category)){
						$newurl =  'link://product-category/'.$category->term_id;
						update_option('wooconnector_settings-push-url',$newurl);	
					}
					else{
						bamobile_mobiconnector_add_notice(__('Your URL is not Product Category URL. Please retype','wooconnector'),'error');
						wp_redirect( '?page=woo-notifications&wootab=api' );
						return true;
					}
				}else{
					bamobile_mobiconnector_add_notice(__('Your URL is not Product Category URL. Please retype','wooconnector'),'error');
					wp_redirect( '?page=woo-notifications&wootab=api' );
					return true;
				}
			}
		}elseif($selectedurl == 'url-about-us'){			
			$newurl = 'link://about-us';
			update_option('wooconnector_settings-push-url',$newurl);	
		}elseif($selectedurl == 'url-bookmark'){			
			$newurl = 'link://bookmark';
			update_option('wooconnector_settings-push-url',$newurl);	
		}elseif($selectedurl == 'url-term-and-conditions'){			
			$newurl = 'link://term-and-conditions';
			update_option('wooconnector_settings-push-url',$newurl);	
		}elseif($selectedurl == 'url-privacy-policy'){			
			$newurl = 'link://privacy-policy';
			update_option('wooconnector_settings-push-url',$newurl);	
		}elseif($selectedurl == 'url-contact-us'){			
			$newurl = 'link://contact-us';
			update_option('wooconnector_settings-push-url',$newurl);
		}
		$subtitle = @$_POST['wooconnector-web-subtitle-notification'];
		update_option('wooconnector_settings-subtitle',$subtitle);
		
		$sound = @$_POST['wooconnector-web-sound-notification'];
		update_option('wooconnector_settings-sound',$sound);
		
		$bigimage = @$_POST['wooconnector-web-bigimages-notification'];
		if(isset($bigimage) && $bigimage != ''){
			$this->update_thumnail_wooconnector($bigimage,'wooconnector_notification_bigimages');		
			update_option('wooconnector_settings-bigimages',$bigimage);
		}else{
			update_option('wooconnector_notification_bigimages','');
			update_option('wooconnector_settings-bigimages',$bigimage);
		}
		
		$responsecolortitle = @$_POST['wooconnector-web-title-color-response-notification'];
		update_option('wooconnector_settings-response-title-color',$responsecolortitle);
		
		$colortitle = @$_POST['wooconnector-web-title-color-notification'];
		update_option('wooconnector_settings-title-color',$colortitle);
		
		$responsecolorcontent = @$_POST['wooconnector-web-content-color-response-notification'];
		update_option('wooconnector_settings-response-content-color',$responsecolorcontent);
		
		$colorcontent = @$_POST['wooconnector-web-content-color-notification'];
		update_option('wooconnector_settings-content-color',$colorcontent);
		
		$bgimage = @$_POST['wooconnector-web-bgimages-notification'];
		if(isset($bgimage) && $bgimage != ''){
			$this->update_thumnail_wooconnector($bgimage,'wooconnector_notification_background');
			update_option('wooconnector_settings-bgimages',$bgimage);
		}else{
			update_option('wooconnector_notification_background','');
			update_option('wooconnector_settings-bgimages',$bgimage);
		}
		
		$responsecolorled = @$_POST['wooconnector-web-led-color-response-notification'];
		update_option('wooconnector_settings-response-led-color',$responsecolorled);
		
		$colorled = @$_POST['wooconnector-web-led-color-notification'];
		update_option('wooconnector_settings-led-color',$colorled);
		
		$responsecoloraccent = @$_POST['wooconnector-web-accent-color-response-notification'];
		update_option('wooconnector_settings-response-accent-color',$responsecoloraccent);
		
		$coloraccent = @$_POST['wooconnector-web-accent-color-notification'];
		update_option('wooconnector_settings-accent-color',$coloraccent);
		
		if(isset($_POST['saveandsend'])){
			$api = get_option('wooconnector_settings-api');
			$rest = get_option('wooconnector_settings-restkey');
			if(empty($api)){
				bamobile_mobiconnector_add_notice(__('Please input your api key!','wooconnector'),'error');
				wp_redirect( '?page=woo-notifications&wootab=api' );
				return true;			
			}
			elseif(empty($rest)){
				bamobile_mobiconnector_add_notice(__('Please input your rest api key!','wooconnector'),'error');
				wp_redirect( '?page=woo-notifications&wootab=api' );
				return true;			
			}
			global $wpdb;
			$table_name = $wpdb->prefix . "wooconnector_data_api";
			$datas = $wpdb->get_results(
				"
				SELECT * 
				FROM $table_name
				WHERE api_key = '$api'
				"
			);
			foreach($datas as $data){
				$idwooconnectorapi = $data->api_id;
			}
			if(isset($_POST['checksegment']) && $_POST['checksegment'] == 'sendeveryone' ){
				$notification = sendWooconnectorMessage();				
			}elseif(isset($_POST['checksegment']) && $_POST['checksegment'] == 'sendtoparticular'){
				if(empty($_POST['include_segment'])){
					bamobile_mobiconnector_add_notice(__('Send to segments empty!','wooconnector'),'error');
					wp_redirect( '?page=woo-notifications&wootab=api' );
					return true;				
				}
				$segment = explode(',',trim($_POST['include_segment'],','));
				$exsegment = explode(',',trim($_POST['exclude_segment'],','));
				$notification = sendWooconnectorMessageBySegment($segment,$exsegment);
			}elseif(isset($_POST['checksegment']) && $_POST['checksegment'] == 'sendtotest'){
				if(empty($_POST['list_test_player'])){
					bamobile_mobiconnector_add_notice(__('List test player empty!','wooconnector'),'error');
					wp_redirect( '?page=woo-notifications&wootab=api' );
					return true;				
				}
				$players = $_POST['list_test_player'];
				$notification = sendWooconnectorMessageByPlayer($players);
			}				
			$noti = json_decode($notification);
			if(!empty($noti->errors)){
				$errornoti = $noti->errors;
				$invalids = $errornoti->invalid_player_ids;				
				if(!empty($invalids)){
					foreach($invalids as $invalid){
						$iderrors[] = $invalid;
					}
					$iderror = implode(',',$iderrors);
					$iderror = trim($iderror,',');
					bamobile_mobiconnector_add_notice(__('Invalid player ids','wooconnector'),'error');
					wp_redirect( '?page=woo-notifications&wootab=api' );
					return true;
				}else{
					bamobile_mobiconnector_add_notice(__('All included players are not subscribed','wooconnector'),'error');
					wp_redirect( '?page=woo-notifications&wootab=api' );
					return true;					
				}				
			}
			$notificationId = $noti->id;
			$notificationRecipients = $noti->recipients;						
			$return = getNotificationById($notificationId);			
			$failed = $return->failed;
			$remaining = $return->remaining;
			$successful = $return->successful;
			$total = ($failed + $remaining + $successful);
			$converted = $return->converted;
			$datenow = new DateTime();
			$date = $datenow->format('Y-m-d H:i:s');			
			$table_name = $wpdb->prefix . "wooconnector_data_notification";			
			$wpdb->insert(
				"$table_name",array(
					"notification_id" => $notificationId,
					"api_id" => $idwooconnectorapi,
					"recipients" => $notificationRecipients,
					"failed" => $failed,
					"remaining" => $remaining,
					"converted" => $converted,  	
					"successful" => $successful,	
					"total" => $total,
					"create_date" => $date	
				),
				array( 
					'%s',
					'%d',	
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%s'	
				) 
			);
			bamobile_mobiconnector_add_notice(__('Successfully Send Notice','mobiconnector')); 
			wp_redirect( '?page=woo-notifications&wootab=new' );	
			return true;
		}
		bamobile_mobiconnector_add_notice(__('Successfully Save Notice','mobiconnector')); 
		wp_redirect( '?page=woo-notifications&wootab=new' );
		return true;		
	}	

	public function get_product_category_by_slug( $slug  ) {
		$category = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $category )
			_make_cat_compat( $category );
	 
		return $category;
	}
	
	public function update_type_player($player,$device,$section){	
		if($section == 'addtotest'){
			preg_match("/iPhone|iPad|iPod|webOS/", $device, $matches);
			$os = current($matches);
			if($os){
				$testtype = 2;
			}else{
				$testtype = 1;
			}
		}elseif($section == 'deletetotest'){
			$testtype = 0;
		}
		$api = get_option('wooconnector_settings-api');
		global $wpdb;
		$table_name = $wpdb->prefix . "wooconnector_data_api";
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name
			WHERE api_key = '$api'
			"
		);
		$idwooconnectorapi = 0;
		if(!empty($datas)){
			foreach($datas as $data){
				$idwooconnectorapi = $data->api_id;
			}	
		}
		$table_update = $wpdb->prefix . "wooconnector_data_player";
		$wpdb->update( 
			$table_update, 
			array( 				
				'test_type' => 	$testtype
			), 
			array( 
				'api_id' => $idwooconnectorapi,
				'player_id' => $player
		 	), 
			array( 				
				'%s'	
			), 
			array( 
				'%d', 
				'%s'
			) 
		);
		wp_redirect( '?page=woo-notifications&wootab=player' );
	}
	
	public function update_thumnail_wooconnector($url,$type) {					
		$wp_upload_dir = wp_upload_dir();	
		if(!empty($url) || $url != ''){
			$fileurl = str_replace($wp_upload_dir['baseurl'],'',$url);
			$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$fileurl;
			$path_parts = pathinfo($fileurl);
			$ext = strtolower($path_parts['extension']);
			$basename = strtolower($path_parts['basename']);
			$dirname = strtolower($path_parts['dirname']);
			$filename = strtolower($path_parts['filename']);				
			foreach($this->thumnails as $key => $value){
				if($key == $type){
					if($key == 'wooconnector_notification_bigimages'){
						list($width, $height) = getimagesize($absolute_pathto_file);
						if($width < 512 || $height < 256){
							$path = $dirname.'/'.$filename.'_'.$key.'_512_256.'.$ext;
							$dest = $wp_upload_dir['basedir'].'/'.$path;
							if(!file_exists($dest)){
								WooConnectorCore:: resize_image($absolute_pathto_file, $dest, 512, 256);
							}					
							update_option($key, $wp_upload_dir['baseurl'].$path);
						}elseif($width > 2048 || $height > 1024){
							$path = $dirname.'/'.$filename.'_'.$key.'_2048_1024.'.$ext;
							$dest = $wp_upload_dir['basedir'].'/'.$path;
							if(!file_exists($dest)){
								WooConnectorCore:: resize_image($absolute_pathto_file, $dest, 2048, 1024);		
							}			
							update_option($key, $wp_upload_dir['baseurl'].$path);
						}else{
							update_option($key, $url);
						}
					}
					else{						
						$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
						$dest = $wp_upload_dir['basedir'].'/'.$path;
						if(!file_exists($dest)){
							WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);		
						}			
						update_option($key, $wp_upload_dir['baseurl'].$path);						
					}			
						
				}
			}
		}else{
			foreach($this->thumnails as $key){
				if($key == $type){
					update_option($key,'');
				}
			}
		}
		
		return true;
	}

	public function wooconnector_admin_notice__success() {
		$class = 'notice notice-success notice-wooconnector';
		$success = get_option('wooconnector_save_currency_success_message');
		if($success != ''){
			$message = $success;
		}
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}

	public function wooconnector_admin_notice__error() {
		$class = 'notice notice-error notice-wooconnector';
		$error = get_option('wooconnector_save_currency_error_message');
		if($error != ''){
			$message = $error;
		}
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
	}

	public $thumnails = array(
		'wooconnector_notification_icon_small' => array(
			'width' => 48,
			'height' => 48
		),
		'wooconnector_notification_icon' => array(
			'width' => 256,
			'height' => 256
		),
		'wooconnector_notification_bigimages' => array(
			'width' => 1024,
			'height' => 512
		),
		'wooconnector_notification_background' => array(
			'width' => 2176,
			'height' => 256
		)
	);	

}
$WooConnectorSettings = new WooConnectorSettings();
?>