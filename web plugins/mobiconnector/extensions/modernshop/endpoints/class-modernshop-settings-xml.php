<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ModernSettingsXML{
	private $rest_url = 'modernshop/static';	
	public function __construct(){
		$this->register_hooks();		
	}	
	public function register_hooks()
	{
		add_action('admin_menu',array($this,'create_menu_to_wooconnector'),20);
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ));
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	public function register_api_hooks() {
		register_rest_route( $this->rest_url, '/gettextstatic', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'gettextstatic' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(		
						'jwt_token' => array(),
						'textstatic' => array(
							'default'=> true
						),
						'include_text' => array(),
						'exclude_text' => array(),
						'currency' => array(
							'default' => true
						),
						'include_currency' => array(),
						'exclude_currency' => array(),
						'token' => array(
							'default' => true
						),
						'https' => array(
							'default' => true
						),
						'login' => array(
							'default' => true
						)
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
	
	public function gettextstatic($request){
		$parameters = $request->get_params();
		$include_text = isset($parameters['include_text']) ? $parameters['include_text'] : false;
		$exclude_text = isset($parameters['exclude_text']) ? $parameters['exclude_text'] : false;
		$include_currency = isset($parameters['include_currency']) ? $parameters['include_currency'] : false;
		$exclude_currency = isset($parameters['exclude_currency']) ? $parameters['exclude_currency'] : false;
		$textsta = $parameters['textstatic'];
		$curren = $parameters['currency'];
		$tokenc = $parameters['token'];
		$httpsc = $parameters['https'];
		$loginc = $parameters['login'];
		$core = get_option('modern_settings-core');
		$core = unserialize($core);
		$currency = array(
				'currency'              => get_woocommerce_currency(),
				'currency_symbol'       => get_woocommerce_currency_symbol(),
				'currency_position'     => get_option( 'woocommerce_currency_pos' ),
				'thousand_separator'    => wc_get_price_thousand_separator(),
				'decimal_separator'     => wc_get_price_decimal_separator(),
				'number_of_decimals'    => wc_get_price_decimals(),
		);				
		$list = array(
			'text_static' => $core,
			'currency' => $currency
		);	
		if(empty($textsta)){
			unset($list['text_static']);
		}
		if(!empty($include_text) && !empty($textsta)){
			$cores = $list['text_static'];
			$fields = json_decode($include_text);
			$listout = array();
			foreach($cores as $core => $values){
				foreach($fields as $field){
					if($field == $core){
						$listout[$field] = $values;
					}
				}
			}
			$list['text_static'] = $listout;
		}
		if(!empty($exclude_text) && !empty($textsta)){
			$cores = $list['text_static'];
			$fields = json_decode($exclude_text);
			foreach($fields as $field){
				if(!empty($cores[$field])){
					unset($cores[$field]);
				}
			}
		}
		if(empty($curren)){
			unset($list['currency']);
		}
		if(!empty($include_currency) && !empty($curren)){
			$currencys = $list['currency'];
			$fields = json_decode($include_currency);
			$listoutc = array();
			foreach($currencys as $currency => $values){
				foreach($fields as $field){
					if($field == $currency){
						$listoutc[$field] = $values;
					}
				}
			}
			$list['currency'] = $listoutc;
		}
		if(!empty($exclude_currency) && !empty($curren)){
			$currencys = $list['currency'];
			$fields = json_decode($exclude_currency);
			foreach($fields as $field){
				if(!empty($currencys[$field])){
					unset($currencys[$field]);
				}
			}
		}
		if($httpsc){
			if ( false !== strpos(home_url(), 'https:') ){
				$list['check_https'] = true;
			}else{
				$list['check_https'] = false;
			}	
		}
		if($loginc){
			$guest = get_option('woocommerce_enable_guest_checkout');
			if($guest == 'yes'){
				$list['required_login'] = false;
			}else{
				if(modernshop_check_user_login_by_token($authen)){
					$list['required_login'] = false;
				}else{
					$list['required_login'] = true;
				}	
			}
		}
		return apply_filters('modernshop_return_static',$list);		
	}		

	
	public function create_menu_to_wooconnector(){
		$parent_slug = 'wooconnector';
		add_submenu_page(
			$parent_slug,
			__('Modernshop Settings','modernshop'),
			__('Modernshop Settings','modernshop'),
			'manage_options',
			'modernshop-settings',
			array($this,'action_create_menu_mordernshop')
		);
	}
	
	public function admin_style(){
		if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'modernshop-settings'){
			wp_register_style( 'modernshop-admin-style', plugins_url('assets/css/modernshop-admin-style.css',MODERN_PLUGIN_FILE), array(), MODERN_VERSION, 'all' );
			wp_enqueue_style( 'modernshop-admin-style' );	

			wp_register_script( 'modern_settings_script', plugins_url('assets/js/modernshop-settings.js',MODERN_PLUGIN_FILE), array( 'jquery' ), MODERN_VERSION );		
			$params = array(								
			);	
			wp_localize_script( 'modern_settings_script', 'modern_settings_script_params',  $params  );
			wp_enqueue_script( 'modern_settings_script' );
			wp_enqueue_media();		
		}	
	}
	
	public function action_create_menu_mordernshop(){				
		$task = isset($_REQUEST['moderntask']) ? $_REQUEST['moderntask'] : '';			
		require_once(MODERN_ABSPATH.'views/settings-form.php');		
		if(!empty($task) || $task != ''){				
			$this->ModernSaveSettings();		
		}	
	}
	
	public function ModernSaveSettings(){
		wp_cache_delete ( 'alloptions', 'options' );	
		require_once(MODERN_ABSPATH."xml/modernshop-static.php");
		$xmls = modernshop_get_static();
		$checkname = array();
		foreach($xmls as $xm){	
			$xml = (object)$xm;									
			if(!in_array($xml->name, $checkname)){		
				$oldname = $xml->name;	
				$name = str_replace("-", "_", $oldname);
				if($xml->type == 'editor'){
					$value = wpautop(stripslashes($_POST["$oldname"]));
				}
				elseif($xml->type == 'textarea' && is_plugin_active('qtranslate-x/qtranslate.php') ){
					$value = stripslashes($_POST["$oldname"]);
				}
				elseif($xml->type == 'textarea' && !is_plugin_active('qtranslate-x/qtranslate.php')){
					$value = stripslashes(nl2br($_POST["$oldname"],false));
				}
				else{
					$value = stripslashes($_POST["$oldname"]); 
				}		
				$list["$name"] = $value;
				array_push($checkname, $xml->name);
			}			
		}
		update_option('modern_settings-core',serialize($list));
		bamobile_mobiconnector_add_notice(__('Setting updated','mobiconnector')); 
		wp_redirect( esc_url('?page=modernshop-settings') );
	}	
	
}
$ModernSettingsXML = new ModernSettingsXML();
?>