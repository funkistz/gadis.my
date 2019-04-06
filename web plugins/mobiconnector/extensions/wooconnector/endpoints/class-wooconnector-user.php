<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooConnectorUser extends  WP_REST_Controller{
	private $rest_url = 'wooconnector/user';	
	public function __construct() {
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		register_rest_route( $this->rest_url, '/update_profile_form', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'update_profile_form' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args' => array(
						'billing_country' => array(
							'required'			=> true,
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_country' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'ship_to_different_address' => array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);

		register_rest_route( $this->rest_url, '/update_profile', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'update_profile' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args' => array(
						'billing_address_1' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_address_2' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_city' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_company' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_country' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_phone' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_postcode' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_state' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_address_1' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_address_2' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_city' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_company' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_country' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_postcode' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'shipping_state' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_email' => array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
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

	public function update_profile( $request ) {
		$parameters = $request->get_params();

		$id = (int) get_current_user_id();
		if($id === 0){
			return new WP_Error('bamobile_user_invalid',__('You must login to change the information.' ,'mobiconnector'),array('status' => 401));
		}
		unset($parameters['user_login']);

		$user = get_user_by('id', $id );

		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.','wooconnector' ), array( 'status' => 400 ) );
		}

		if ( isset($parameters['user_email']) && $parameters['user_email'] != $user->user_email && email_exists( $parameters['user_email'] )) {
			return new WP_Error( 'rest_user_invalid_email', __( 'Email address is invalid.','wooconnector' ), array( 'status' => 400 ) );
		}

		if ( isset( $parameters['username'] ) && $parameters['username'] !== $user->user_login ) {
			return new WP_Error( 'rest_user_invalid_argument', __( "Username isn't editable",'wooconnector' ), array( 'status' => 400 ) );
		}
		// Ensure we're operating on the same user we already checked
		$validation = new WC_Validation();
		$new_user = array();
		if ( isset( $parameters['billing_address_1']) ){
			$new_user['billing_address_1'] = @$parameters['billing_address_1'];
		}
		if ( isset( $parameters['billing_address_2']) ){
			$new_user['billing_address_2'] = @$parameters['billing_address_2'];
		}
		if ( isset( $parameters['billing_city']) ){
			$new_user['billing_city'] = @$parameters['billing_city'];
		}
		if ( isset( $parameters['billing_company']) ){
			$new_user['billing_company'] = @$parameters['billing_company'];
		}
		if ( isset( $parameters['billing_country']) ){
			$new_user['billing_country'] = @$parameters['billing_country'];
		}
		if ( isset( $parameters['billing_first_name']) ){
			$new_user['billing_first_name'] = @$parameters['billing_first_name'];
		}
		if ( isset( $parameters['billing_last_name']) ){
			$new_user['billing_last_name'] = @$parameters['billing_last_name'];
		}
		if ( isset( $parameters['billing_phone']) ){
			if(!$validation->is_phone($parameters['billing_phone'])){
				return new WP_Error( 'rest_phone_error', __( 'Sorry, Incorrect phone number.','wooconnector' ), array( 'status' => 401 ) );
			}
			$new_user['billing_phone'] = @$parameters['billing_phone'];
		}
		if ( isset( $parameters['billing_postcode']) ){
			if(!$validation->is_postcode($parameters['billing_postcode'],$parameters['billing_country'])){
				return new WP_Error( 'rest_postcode_error', __( 'Sorry, Incorrect billing postcode.','wooconnector' ), array( 'status' => 401 ) );
			}	
			$new_user['billing_postcode'] = @$parameters['billing_postcode'];
		}
		if ( isset( $parameters['billing_state']) ){
			$new_user['billing_state'] = @$parameters['billing_state'];
		}
		if ( isset( $parameters['shipping_address_1']) ){
			$new_user['shipping_address_1'] = @$parameters['shipping_address_1'];
		}
		if ( isset( $parameters['shipping_address_2']) ){
			$new_user['shipping_address_2'] = @$parameters['shipping_address_2'];
		}
		if ( isset( $parameters['shipping_city']) ){
			$new_user['shipping_city'] = @$parameters['shipping_city'];
		}
		if ( isset( $parameters['shipping_company']) ){
			$new_user['shipping_company'] = @$parameters['shipping_company'];
		}
		if ( isset( $parameters['shipping_country']) ){
			$new_user['shipping_country'] = @$parameters['shipping_country'];
		}
		if ( isset( $parameters['shipping_first_name']) ){
			$new_user['shipping_first_name'] = @$parameters['shipping_first_name'];
		}
		if ( isset( $parameters['shipping_last_name']) ){
			$new_user['shipping_last_name'] = @$parameters['shipping_last_name'];
		}
		if ( isset( $parameters['shipping_postcode']) ){
			if(!$validation->is_postcode($parameters['shipping_postcode'],$parameters['shipping_country'])){
				return new WP_Error( 'rest_postcode_error', __( 'Sorry, Incorrect shipping postcode.','wooconnector' ), array( 'status' => 401 ) );
			}	
			$new_user['shipping_postcode'] = @$parameters['shipping_postcode'];
		}
		if ( isset( $parameters['shipping_state']) ){
			$new_user['shipping_state'] = @$parameters['shipping_state'];
		}
		$user_id = 0;
		if ( isset( $parameters['user_email']) ){
			if(!$validation->is_email($parameters['user_email'])){
				return new WP_Error( 'rest_email_error', __( 'Sorry, Incorrect email.','wooconnector' ), array( 'status' => 401 ) );
			}
			$user_update = array();
			$user_update['ID'] = $id;
			$user_update['user_email'] = @$parameters['user_email'];
			$user_id = wp_update_user( $user_update );
		}
		foreach($new_user as $key => $value) {
			update_user_meta( $id, $key, $value );
		}
		if($user_id > 0){
			$user = get_userdata( $user_id );
		}
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $id ) );
		global $blog_id, $wpdb;
		if(!empty($user_meta[$wpdb->get_blog_prefix($blog_id).'user_avatar'])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$wpdb->get_blog_prefix($blog_id).'user_avatar']);
			$user_meta['wp_user_avatar'] = $attachment;
		}	
		if(!empty($user_meta['mobiconnector-avatar'])){
			$attachment = wp_get_attachment_url( (int) $user_meta['mobiconnector-avatar']);
			$user_meta['mobiconnector_avatar'] = $attachment;
		}	
		$user_meta = array_merge($user_meta, $user);
		
		return $user_meta;
	}

	public function update_profile_form( $request ) {
		$parameters = $request->get_params();
		$countries = new WC_Countries();
		$billing_country = $parameters['billing_country'];
		$shipping_country = isset($parameters['shipping_country']) ? $parameters['shipping_country'] : '';
		$validation = new WC_Validation();
		$billing_address = $countries->get_address_fields($billing_country,'billing_');
		$shipping_address = $countries->get_address_fields($shipping_country,'shipping_');
		$ship_to_different_address = isset($parameters['ship_to_different_address']) ? $parameters['ship_to_different_address'] : false;

		$id = (int) get_current_user_id();
		if($id === 0){
			return new WP_Error('bamobile_user_invalid',__('You must login to change the information.' ,'mobiconnector'),array('status' => 401));
		}
		unset($parameters['user_login']);

		$user = get_user_by('id', $id );

		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.','wooconnector' ), array( 'status' => 400 ) );
		}
		$new_user = array();
		if(is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php'))){
			// Ensure we're operating on the same user we already checked
			foreach($billing_address as $field){
				foreach($billing_address as $keyb => $valb){
					if($valb['required'] == true && !isset($parameters[$keyb])){
						return new WP_Error('rest_field_require',sprintf(__('Sorry, Billing %s is required','wooconnector'),$valb['label']),array( 'status' => 400 ));
					}	
					if(isset($parameters[$keyb])){
						if($keyb == 'billing_phone' && !$validation->is_phone($parameters[$keyb])){
							return new WP_Error( 'rest_phone_error', __( 'Sorry, Incorrect phone number.','wooconnector' ), array( 'status' => 400 ) );
						}elseif($keyb == 'billing_email' && !$validation->is_email($parameters[$keyb])){
							return new WP_Error( 'rest_email_error', __( 'Sorry, Incorrect email.','wooconnector' ), array( 'status' => 400 ) );
						}elseif($keyb == 'billing_postcode' && !$validation->is_postcode($parameters[$keyb],$billing_country)){
							return new WP_Error( 'rest_postcode_error', __( 'Sorry, Incorrect postcode.','wooconnector' ), array( 'status' => 400 ) );
						}elseif($keyb == 'billing_email' && $parameters[$keyb] != $user->user_email && email_exists($parameters[$keyb])){
							return new WP_Error( 'rest_user_invalid_email', __( 'Email address is invalid.','wooconnector' ), array( 'status' => 400 ) );
						}else{
							$valb['value'] = @$parameters[$keyb];
						}
					}
					$new_user[$keyb] = $valb;
				}
			}
			if($ship_to_different_address == 1){
				if(!empty($shipping_address)){
					foreach($shipping_address as $keys => $vals){
						if($vals['required'] == true && !isset($parameters[$keys])){
							return new WP_Error('rest_field_require',sprintf(__('Sorry, Shipping %s is required','wooconnector'),$vals['label']),array( 'status' => 400 ));
						}	
						if(isset($parameters[$keys])){
							if($keys == 'shipping_postcode' && !$validation->is_postcode($parameters[$keys],$shipping_country)){
								return new WP_Error( 'rest_postcode_error', __( 'Sorry, Incorrect postcode.', 'wooconnector' ), array( 'status' => 400 ) );
							}else{
								$vals['value'] = @$parameters[$keys];
							}
						}
						$new_user[$keys] = $vals;
					}
				}
			}else{
				if(!empty($shipping_address)){
					foreach($shipping_address as $keys => $vals){
						$keyGetBil = str_replace('shipping_','billing_',$keys);
						$vals['value'] = isset($new_user[$keyGetBil]['value']) ? $new_user[$keyGetBil]['value'] : '';
						$new_user[$keys] = $vals;
					}
				}
			}
		}else{
			if ( isset( $parameters['billing_address_1']) ){
				$new_user['billing_address_1'] = @$parameters['billing_address_1'];
			}
			if ( isset( $parameters['billing_address_2']) ){
				$new_user['billing_address_2'] = @$parameters['billing_address_2'];
			}
			if ( isset( $parameters['billing_city']) ){
				$new_user['billing_city'] = @$parameters['billing_city'];
			}
			if ( isset( $parameters['billing_company']) ){
				$new_user['billing_company'] = @$parameters['billing_company'];
			}
			if ( isset( $parameters['billing_country']) ){
				$new_user['billing_country'] = @$parameters['billing_country'];
			}
			if ( isset( $parameters['billing_first_name']) ){
				$new_user['billing_first_name'] = @$parameters['billing_first_name'];
			}
			if ( isset( $parameters['billing_last_name']) ){
				$new_user['billing_last_name'] = @$parameters['billing_last_name'];
			}
			if ( isset( $parameters['billing_phone']) ){
				$new_user['billing_phone'] = @$parameters['billing_phone'];
			}
			if ( isset( $parameters['billing_postcode']) ){
				$new_user['billing_postcode'] = @$parameters['billing_postcode'];
			}
			if ( isset( $parameters['billing_state']) ){
				$new_user['billing_state'] = @$parameters['billing_state'];
			}
			if ( isset( $parameters['shipping_address_1']) ){
				$new_user['shipping_address_1'] = @$parameters['shipping_address_1'];
			}
			if ( isset( $parameters['shipping_address_2']) ){
				$new_user['shipping_address_2'] = @$parameters['shipping_address_2'];
			}
			if ( isset( $parameters['shipping_city']) ){
				$new_user['shipping_city'] = @$parameters['shipping_city'];
			}
			if ( isset( $parameters['shipping_company']) ){
				$new_user['shipping_company'] = @$parameters['shipping_company'];
			}
			if ( isset( $parameters['shipping_country']) ){
				$new_user['shipping_country'] = @$parameters['shipping_country'];
			}
			if ( isset( $parameters['shipping_first_name']) ){
				$new_user['shipping_first_name'] = @$parameters['shipping_first_name'];
			}
			if ( isset( $parameters['shipping_last_name']) ){
				$new_user['shipping_last_name'] = @$parameters['shipping_last_name'];
			}
			if ( isset( $parameters['shipping_postcode']) ){
				$new_user['shipping_postcode'] = @$parameters['shipping_postcode'];
			}
			if ( isset( $parameters['shipping_state']) ){
				$new_user['shipping_state'] = @$parameters['shipping_state'];
			}
		}
		$user_id = 0;
		if ( isset( $parameters['billing_email']) ){
			$user_update = array();
			$user_update['ID'] = $id;
			$user_update['user_email'] = @$parameters['billing_email'];
			$user_id = wp_update_user( $user_update );
		}
		$user_address = array();
		foreach($new_user as $key => $value) {
			$outvalue = isset($value['value']) ? $value['value'] : '';			
			$user_address[$key] = $outvalue;
		}
		update_user_meta( $id, 'mobiconnector_address', $user_address);
		if($user_id > 0){
			$user = get_userdata( $user_id );
		}
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $id ) );
		global $blog_id, $wpdb;
		$user_info = array();
		$user_info = bamobile_filtered_user_to_application($user_id);
		$user_info = (array)$user_info;
		if(!empty($user_meta[$wpdb->get_blog_prefix($blog_id).'user_avatar'])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$wpdb->get_blog_prefix($blog_id).'user_avatar']);
			$user_meta['mobiconnector_avatar'] = $attachment;
			$user_info['wp_user_avatar'] = $attachment;
		}		
		if(!empty($user_meta['mobiconnector-avatar'])){
			$attachment = wp_get_attachment_url( (int) $user_meta['mobiconnector-avatar']);
			$user_meta['mobiconnector_avatar'] = $attachment;
			$user_info['mobiconnector_avatar'] = $attachment;
		}
		$user_meta = array_merge($user_meta, $user);
		if(isset($user_meta['mobiconnector_address']) && is_string($user_meta['mobiconnector_address'])){
			$user_meta['mobiconnector_address'] = unserialize($user_meta['mobiconnector_address']);
		}
		$user_meta['mobiconnector_info'] = $user_info;
		$user_meta['mobiconnector_info']['first_name'] = isset($user_meta['first_name']) ? $user_meta['first_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['last_name'] = isset($user_meta['last_name']) ? $user_meta['last_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['description'] = isset($user_meta['description']) ? $user_meta['description'] : '';
		if(isset($user_meta['password'])){
			unset($user_meta['password']);
		}
		return $user_meta;
	}
}
$WooConnectorUser = new WooConnectorUser();
?>