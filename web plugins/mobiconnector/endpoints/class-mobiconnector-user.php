<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create Api involve with users
 */
class BAMobileUser{

	/**
     * Url of API
     */
	private $rest_url = 'mobiconnector/user';	

	/**
	 * Fields fix wordpress
	 */
	private $wordpress_fields = array('first_name','last_name','email','user_login','nicename','display_name','url','description','password');

	/**
	 * Fields fix WooCommerce
	 */
	private $woocommerce_fields = array('first_name','last_name','company','country','address_1','address_2','city','state','postcode','phone','email');

	/**
     * MobiConnectorUser construct
     */
	public function __construct() {		
		$this->register_routes();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}

	/**
	 * Create Api or add field
	 */
	public function register_api_hooks() {
		
		// register users
		register_rest_route( $this->rest_url, '/register', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_register' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						),
						'password' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'random_password' => array(
							'sanitize_callback' => 'absint'
						),
						'email' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_nicename' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'display_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'nickname' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'billing_phone' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'player_id'	=> array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);

		// register users
		register_rest_route( $this->rest_url, '/register_form', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_register_form' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'player_id'	=> array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);
		
		// forgot password
		register_rest_route( $this->rest_url, '/forgot_password', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_forgot_password' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						),
						'player_id'	=> array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);

		// update user infomation
		register_rest_route( $this->rest_url, '/update_profile', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_update_profile' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'user_pass' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_nicename' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_email' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'display_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'nickname' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_url' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'description' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_profile_picture' => array(							
						),
						'player_id'	=> array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);

		// update user infomation
		register_rest_route( $this->rest_url, '/update_profile_form', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_update_profile_form' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'user_pass' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_nicename' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_email' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'display_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'nickname' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'first_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'last_name' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_url' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'description' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'user_profile_picture' => array(							
						),
						'player_id'	=> array(
							'sanitize_callback' => 'esc_sql'
						)
					),
				)
			) 
		);

		// get infomation of user logged
		register_rest_route( $this->rest_url, '/get_info', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'bamobile_get_info' ),
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),
					'args' => array(
						'username' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
						),
						'player_id'	=> array(
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
	public function bamobile_get_items_permissions_check( $request ) {
		$usekey = get_option('mobiconnector_settings-use-security-key');
		if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
			return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * register a New user from frontend
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return int id of user just register
	 */
	public function bamobile_register( $request ) {
		$parameters = $request->get_params();
		// if random_password exist. unset password
		if(isset($parameters['random_password'])) {
			$random_password = wp_generate_password( 8, false );
			$parameters['password'] = $random_password;
		}		
		// check username or email exist
		$user_id = username_exists( $parameters['username'] );
		
		if($user_id !== false) {
			return new WP_Error( 'username_exists', 'Username already exists', array( 'status' => 401 ) );
		}
		// check email isset and exist
		if(isset($parameters['email']) && email_exists($parameters['email']) != false) {
			return new WP_Error( 'email_exists', 'Email already exists', array( 'status' => 401 ) );
		}
		// check isset password
		if(!isset($parameters['password'])) {
			return new WP_Error( 'password_empty', 'Password required', array( 'status' => 401 ) );
		}
		$user_id = wp_create_user( $parameters['username'], $parameters['password'], @$parameters['email'] );
		// update firstname & lastname
		$new_user = array(
				'ID' 			=> $user_id,
				'user_nicename' => @$parameters['user_nicename'],
				'display_name' 	=> @$parameters['display_name'],
				'nickname' 		=> @$parameters['nickname'],
				'first_name' 	=> @$parameters['first_name'],
				'last_name' 	=> @$parameters['last_name'],
		);
		if(empty($parameters['display_name']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
			$new_user['display_name'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
		}
		if(empty($parameters['user_nicename']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
			$new_user['user_nicename'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
		}
		if(empty($parameters['nickname']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
			$new_user['nickname'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
		}
		$user_id = wp_update_user( $new_user );
		if(isset($parameters['billing_phone']) && !empty($parameters['billing_phone'])){
			if(is_plugin_active('woocommerce/woocommerce.php')){
				$billing['billing_phone'] = $parameters['billing_phone'];
				foreach($billing as $key => $value) {
					update_user_meta( $user_id, $key, $value );
				}
			}	
		}
		// email to admin and user
		$this->bamobile_new_user_notification($user_id,null, 'both');
		return $user_id;
	}

	/**
	 * register a New user from frontend
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return int id of user just register
	 */
	public function bamobile_register_form( $request ) {
		global $wpdb;
		$parameters = $request->get_params();
		$player_id = isset($parameters['player_id']) ? $parameters['player_id'] : false;
		$checkplayer = $wpdb->get_var("SELECT COUNT(*) FROM ". $wpdb->prefix . "mobiconnector_manage_device WHERE player_id = '".$player_id."' AND blocked = '0'");
		if(!empty($player_id) && $checkplayer === "0"){
			return false;
		}
		if(is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php')){
			$form = get_option('ba_design_form');
			$forms = unserialize($form);
			$fixed_field = array();
			$register_field = array();
			$custom_field = array();
			$username = '';
			$password = '';	
			$email = '';		
			foreach($forms as $field){
				$id_fields = (strpos($field['name_id'],'billing_') !== false) ? substr($field['name_id'],strpos($field['name_id'],'billing_')+8)  : $field['name_id'];
				$id_billing = '';
				if(strpos($field['name_id'],'billing_') !== false){
					$id_billing = $field['name_id'];
				}
				$id_shipping = '';
				if($id_billing !== ''){
					$id_shipping = str_replace('billing_','shipping_',$id_billing);
				}
				$required = $field['required_check'];
				$required_register = $field['required_register'];
				$required_billing = $field['required_billing'];
				$required_shipping = $field['required_shipping'];
				$name = $field['label'];
				if($required == 1 && $required_register == 1 && $id_fields !== 'email' && (!isset($parameters[$id_fields]) || $parameters[$id_fields] === '')){
					return new WP_Error('bamobile_register_invalid',$name. __(' is require.' ,'mobiconnector'),array('status' => 400));
				}
				$value = isset($parameters[$id_fields]) ? $parameters[$id_fields] : '';
				if($required_register == 1 && (in_array($id_fields,$this->wordpress_fields) || in_array($id_fields,$this->woocommerce_fields))){
					if($id_fields == 'user_login' || $id_fields == 'password'){
						if($id_fields == 'user_login'){
							$username = $parameters[$id_fields];
							$user_id = username_exists( $username );
							if($user_id !== false) {
								return new WP_Error( 'bamobile_username_exists', __('Username already exists','mobiconnector'), array( 'status' => 400 ) );
							}
						}elseif($id_fields == 'password'){
							$password = $parameters[$id_fields];
						}
					}else{
						if($id_fields == 'email'){
							$email = $parameters[$id_fields];
							if(!is_email($email)) {
								return new WP_Error( 'bamobile_email_exists', __('Email is invalid','mobiconnector'), array( 'status' => 400 ) );
							}
							if(email_exists($email) != false) {
								return new WP_Error( 'bamobile_email_exists', __('Email already exists','mobiconnector'), array( 'status' => 400 ) );
							}
						}
						$register_field[$id_fields] = $value;
						if($required_billing == 1){
							$fixed_field[$id_billing] = $value;
						}
						if($required_shipping == 1){
							$fixed_field[$id_shipping] = $value;
						}
					}
				}elseif($required_register == 1){
					$custom_field['billing_'.$id_fields] = $value;
					if($required_billing == 1){
						$fixed_field[$id_billing] = $value;
					}
					if($required_shipping == 1){ 
						$fixed_field[$id_shipping] = $value;
					}
				}else{
					continue;
				}
			}
			$user_id = wp_create_user( $username, $password, sanitize_email($email) );	
			if(is_wp_error($user_id)){
				return $user_id;
			}	
			if(!empty($register_field)){
				foreach($register_field as $id_rfield => $value){
					update_user_meta($user_id,$id_rfield,$value);
				}
			}
			if(!empty($fixed_field)){	
				update_user_meta( $user_id, 'mobiconnector_address', $fixed_field);
				foreach($register_field as $id_ffield => $value){
					update_user_meta($user_id,$id_ffield,$value);
				}
			}
			if(!empty($custom_field)){
				update_user_meta($user_id,'field_extra_user',$custom_field);
			}
		}else{
			// if random_password exist. unset password
			if(isset($parameters['random_password'])) {
				$random_password = wp_generate_password( 8, false );
				$parameters['password'] = $random_password;
			}		
			// check username or email exist
			$user_id = username_exists( $parameters['username'] );
			
			if($user_id !== false) {
				return new WP_Error( 'username_exists', __('Username already exists','mobiconnector'), array( 'status' => 401 ) );
			}
			// check email isset and exist
			if(isset($parameters['email']) && email_exists($parameters['email']) != false) {
				return new WP_Error( 'email_exists', __('Email already exists','mobiconnector'), array( 'status' => 401 ) );
			}
			// check isset password
			if(!isset($parameters['password'])) {
				return new WP_Error( 'password_empty', __('Password required','mobiconnector'), array( 'status' => 401 ) );
			}
			$user_id = wp_create_user( $parameters['username'], $parameters['password'], sanitize_email(@$parameters['email']) );		
			// update firstname & lastname
			$new_user = array(
				'ID' 			=> $user_id,
				'user_nicename' => @$parameters['user_nicename'],
				'display_name' 	=> @$parameters['display_name'],
				'nickname' 		=> @$parameters['nickname'],
				'first_name' 	=> @$parameters['first_name'],
				'last_name' 	=> @$parameters['last_name'],
			);
			if(empty($parameters['display_name']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
				$new_user['display_name'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
			}
			if(empty($parameters['user_nicename']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
				$new_user['user_nicename'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
			}
			if(empty($parameters['nickname']) && (!empty($parameters['first_name']) || !empty($parameters['last_name']))){
				$new_user['nickname'] = trim(@$parameters['first_name'].' '.@$parameters['last_name']);
			}
			$user_id = wp_update_user( $new_user );
		}
		// email to admin and user
		$this->bamobile_new_user_notification($user_id,null, 'both');
		return $user_id;
	}

	/**
	 * Email login credentials to a newly-registered user.
	 *
	 * A new user registration notification is also sent to admin email.
	 *
	 *
	 * @global wpdb         $wpdb      WordPress database object for queries.
	 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
	 *
	 * @param int    $user_id    User ID.
	 * @param null   $deprecated Not used (argument deprecated).
	 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
	 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
	 */
	public function bamobile_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );
		
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		// email to admin
		if ( 'user' !== $notify ) {
			$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
			$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );
		}

		// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notifcation.
		if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
			return;
		}
		$message = sprintf(__('Sitename: %s'), $blogname) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		// email to customer
		@mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), $message);
		return true;
	}

	/**
	 * Forgot password with username
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 */
	public function bamobile_forgot_password( $request ) {
		global $wpdb;
		$parameters = $request->get_params();
		$player_id = isset($parameters['player_id']) ? $parameters['player_id'] : false;
		$checkplayer = $wpdb->get_var("SELECT COUNT(*) FROM ". $wpdb->prefix . "mobiconnector_manage_device WHERE player_id = '".$player_id."' AND blocked = '0'");
		if(!empty($player_id) && $checkplayer === 0){
			return false;
		}
		// check username or email exist
		$user_id = username_exists( $parameters['username'] );
		
		if($user_id == false) {
			return new WP_Error( 'user_not_exists', 'User is not exist', array( 'status' => 404 ) );
		}
		global $wpdb, $wp_hasher;
		$user = get_userdata( $user_id );
		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		//This action is documented in wp-login.php
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );
		$message = __("Someone requested that the password be reset for the following account:") . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '< ' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login') . " >\r\n\r\n";
		@mail($user->user_email, __('Password Reset'), $message);
		return true;
	}

	/**
	 * Update user profile with param
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	public function bamobile_update_profile($request) {
		$parameters = $request->get_params();
		$id = (int) get_current_user_id();
		if($id === 0){
			return new WP_Error('bamobile_user_invalid',__('You must login to change the information.' ,'mobiconnector'),array('status' => 401));
		}
		unset($parameters['user_login']);
		$user = get_userdata( $id );
		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.' ), array( 'status' => 400 ) );
		}

		if ( isset($parameters['user_email']) && email_exists( $parameters['user_email'] ) && $parameters['user_email'] !== $user->user_email ) {
			return new WP_Error( 'rest_user_invalid_email', __( 'Email address is invalid.' ), array( 'status' => 400 ) );
		}

		if ( isset( $parameters['username'] ) && $parameters['username'] !== $user->user_login ) {
			return new WP_Error( 'rest_user_invalid_argument', __( "Username isn't editable" ), array( 'status' => 400 ) );
		}
		// Ensure we're operating on the same user we already checked
		$new_user = array(
			'ID' 	=> $id
		);
		if ( isset( $parameters['user_pass']) ){
			$new_user['user_pass'] = @$parameters['user_pass'];
		}
		if ( isset( $parameters['user_nicename']) ){
			$new_user['user_nicename'] = @$parameters['user_nicename'];
		}
		if ( isset( $parameters['user_email']) ){
			$new_user['user_email'] = @$parameters['user_email'];
		}
		if ( isset( $parameters['display_name']) ){
			$new_user['display_name'] = @$parameters['display_name'];
		}
		if ( isset( $parameters['nickname']) ){
			$new_user['nickname'] = @$parameters['nickname'];
		}
		if ( isset( $parameters['first_name']) ){
			$new_user['first_name'] = @$parameters['first_name'];
		}
		if ( isset( $parameters['last_name']) ){
			$new_user['last_name'] = @$parameters['last_name'];
		}
		if ( isset( $parameters['user_url']) ){
			$new_user['user_url'] = @$parameters['user_url'];
		}
		if ( isset( $parameters['description']) ){
			$new_user['description'] = @$parameters['description'];
		}
		if ( isset( $parameters['user_profile_picture']) ){
			$new_user['user_profile_picture'] = @$parameters['user_profile_picture'];
		}
		$user_id = wp_update_user( $new_user );
		if ( is_wp_error( $user_id ) ) {
			return new WP_Error( 'rest_user_invalid_argument', __( "There was an error, probably that user doesn't exist." ), array( 'status' => 400 ) );
		}
		if(!empty($new_user['user_profile_picture'])){
			$this->bamobile_update_user_avatar($user_id, $new_user['user_profile_picture']);
		}
		$user = get_userdata( $user_id );
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
		global $blog_id, $wpdb;
		$avatar_key = $this->bamobile_getAvatarMetaKey();
		if(!empty($user_meta[$avatar_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$avatar_key]);
			if(!empty($attachment)){
				$user_meta['wp_user_avatar'] = $attachment;
			}
		}
		$mobiconnector_key = $this->bamobile_getMobiconnectorMetaKey();
		if(!empty($user_meta[$mobiconnector_key])){
			$attachmentmobi = wp_get_attachment_url( (int) $user_meta[$mobiconnector_key]);
			if(!empty($attachmentmobi)){
				$user_meta['mobiconnector_avatar'] = $attachmentmobi;
			}
		}
		$user_meta = array_merge($user_meta, $user);
		
		return $user_meta;
	}
	
	/**
	 * Update user profile with param
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	public function bamobile_update_profile_form($request) {
		global $wpdb;
		$parameters = $request->get_params();
		$player_id = isset($parameters['player_id']) ? $parameters['player_id'] : false;
		$checkplayer = $wpdb->get_var("SELECT COUNT(*) FROM ". $wpdb->prefix . "mobiconnector_manage_device WHERE player_id = '".$player_id."' AND blocked = '0'");
		if(!empty($player_id) && $checkplayer === "0"){
			return false;
		}
		$id = (int) get_current_user_id();
		if($id === 0){
			return new WP_Error('bamobile_user_invalid',__('You must login to change the information.' ,'mobiconnector'),array('status' => 401));
		}
		if(is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php'))){
			$form = get_option('ba_design_form');
			$forms = unserialize($form);
			$fixed_field = array();
			$register_field = array();
			$custom_field = array();
			$username = '';
			$password = '';	
			$email = '';
			$url = '';		
			foreach($forms as $field){
				$id_fields = (strpos($field['name_id'],'billing_') !== false) ? substr($field['name_id'],strpos($field['name_id'],'billing_')+8)  : $field['name_id'];
				$id_billing = '';
				if(strpos($field['name_id'],'billing_') !== false){
					$id_billing = $field['name_id'];
				}
				$id_shipping = '';
				if($id_billing !== ''){
					$id_shipping = str_replace('billing_','shipping_',$id_billing);
				}
				$required = $field['required_check'];
				$required_profile = $field['required_profile'];
				$required_billing = $field['required_billing'];
				$required_shipping = $field['required_shipping'];
				$name = $field['label'];
				if($required == 1 && $required_profile == 1 && (!isset($parameters[$id_fields]) || $parameters[$id_fields] === '')){
					return new WP_Error('bamobile_register_invalid',$name. __(' is require.' ,'mobiconnector'),array('status' => 400));
				}
				$value = isset($parameters[$id_fields]) ? $parameters[$id_fields] : '';
				if($required_profile == 1 && (in_array($id_fields,$this->wordpress_fields) || in_array($id_fields,$this->woocommerce_fields))){
					if($id_fields == 'user_login'){
						continue;
					}else{
						if(strpos($id_fields,'email') !== false){
							$email = $parameters[$id_fields];
							$user = get_user_by('id',$id);
							if(!is_email($email)) {
								return new WP_Error( 'bamobile_email_exists', __('Email is invalid','mobiconnector'), array( 'status' => 400 ) );
							}
							if(email_exists($email) !== false && $parameters[$id_fields] !== $user->user_email) {
								return new WP_Error( 'bamobile_email_exists', __('Email already exists','mobiconnector'), array( 'status' => 400 ) );
							}
						}
						$profile_field[$id_fields] = $value;
						if($required_billing == 1){
							$fixed_field[$id_billing] = $value;
						}
						if($required_shipping == 1){
							$fixed_field[$id_shipping] = $value;
						}
					}
				}elseif($required_profile == 1){
					$custom_field['billing_'.$id_fields] = $value;
					if($required_billing == 1){
						$fixed_field['billing_'.$id_fields] = $value;
					}
					if($required_shipping == 1){
						$fixed_field['shipping_'.$id_fields] = $value;
					}
				}else{
					continue;
				}
			}
			$new_user['ID'] = $id;
			foreach($profile_field as $p_field => $value){
				if($p_field == 'first_name'){
					$new_user[$p_field] = $value;
				}elseif($p_field == 'last_name'){
					$new_user[$p_field] = $value;
				}elseif($p_field == 'display_name'){
					$new_user[$p_field] = $value;
				}elseif($p_field == 'description'){
					$new_user[$p_field] = $value;
				}elseif($p_field == 'password'){
					$new_user['user_pass'] = $value;
				}elseif($p_field == 'description'){
					$new_user['description'] = $value;
				}else{
					$new_user['user_'.$p_field] = $value;
				}				
			}
			
			$mobileaddress = array();
			$addresscheck = array();
			$checkaddress = get_user_meta($id,'mobiconnector_address',true);
			if(!empty($checkaddress) && is_string($checkaddress)){
				$addresscheck = unserialize($checkaddress);
			}elseif(is_array($checkaddress)){
				$addresscheck = $checkaddress;
			}
			if(!empty($addresscheck) && is_array($addresscheck) && !empty($fixed_field) && is_array($fixed_field)){
				$outarray = bamobile_array_keys_recursive($fixed_field,array_keys($addresscheck),$addresscheck);
				$mobileaddress = array_merge($outarray,$fixed_field);
			}elseif(!empty($fixed_field) && is_array($fixed_field)){
				$mobileaddress = $fixed_field;
			}else{
				$mobileaddress = array();
			}
			$user_id = wp_update_user( $new_user );
			$user = get_user_by('id',$user_id);
			update_user_meta($user_id,'mobiconnector_address',$mobileaddress);
			update_user_meta($user_id,'field_extra_user',$custom_field);
			if(!empty($profile_field)){
				foreach($profile_field as $id_pfield => $value){
					update_user_meta($user_id,$id_pfield,$value);
				}
			}
		}else{
			unset($parameters['user_login']);
			$user = get_userdata( $id );
			if ( ! $user ) {
				return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.','mobiconnector' ), array( 'status' => 400 ) );
			}
	
			if ( isset($parameters['user_email']) && email_exists( $parameters['user_email'] ) && $parameters['user_email'] !== $user->user_email ) {
				return new WP_Error( 'rest_user_invalid_email', __( 'Email address is invalid.','mobiconnector' ), array( 'status' => 400 ) );
			}
	
			if ( isset( $parameters['username'] ) && $parameters['username'] !== $user->user_login ) {
				return new WP_Error( 'rest_user_invalid_argument', __( "Username isn't editable",'mobiconnector' ), array( 'status' => 400 ) );
			}
			// Ensure we're operating on the same user we already checked
			$new_user = array(
				'ID' 	=> $id
			);
			if ( isset( $parameters['user_pass']) ){
				$new_user['user_pass'] = @$parameters['user_pass'];
			}
			if ( isset( $parameters['user_nicename']) ){
				$new_user['user_nicename'] = @$parameters['user_nicename'];
			}
			if ( isset( $parameters['user_email']) ){
				$new_user['user_email'] = sanitize_email(@$parameters['user_email']);
			}
			if ( isset( $parameters['display_name']) ){
				$new_user['display_name'] = @$parameters['display_name'];
			}
			if ( isset( $parameters['nickname']) ){
				$new_user['nickname'] = @$parameters['nickname'];
			}
			if ( isset( $parameters['first_name']) ){
				$new_user['first_name'] = @$parameters['first_name'];
			}
			if ( isset( $parameters['last_name']) ){
				$new_user['last_name'] = @$parameters['last_name'];
			}
			if ( isset( $parameters['user_url']) ){
				$new_user['user_url'] = @$parameters['user_url'];
			}
			if ( isset( $parameters['description']) ){
				$new_user['description'] = @$parameters['description'];
			}
			if ( isset( $parameters['user_profile_picture']) ){
				$new_user['user_profile_picture'] = @$parameters['user_profile_picture'];
			}
			$user_id = wp_update_user( $new_user );
			if ( is_wp_error( $user_id ) ) {
				return new WP_Error( 'rest_user_invalid_argument', __( "There was an error, probably that user doesn't exist." ), array( 'status' => 400 ) );
			}
		}
		if(!empty($parameters['user_profile_picture'])){
			$this->bamobile_update_user_avatar($user_id, $parameters['user_profile_picture']);
		}
		$user = get_userdata( $user_id );
		$user = (array) $user->data;
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
		global $blog_id;
		$avatar_key = $this->bamobile_getAvatarMetaKey();
		if(!empty($user_meta[$avatar_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$avatar_key]);
			if(!empty($attachment)){
				$user_meta['wp_user_avatar'] = $attachment;
			}
		}
		$mobiconnector_key = $this->bamobile_getMobiconnectorMetaKey();
		if(!empty($user_meta[$mobiconnector_key])){
			$attachmentmobi = wp_get_attachment_url( (int) $user_meta[$mobiconnector_key]);
			if(!empty($attachmentmobi)){
				$user_meta['mobiconnector_avatar'] = $attachmentmobi;
			}
		}
		$user_info = array();
		if($id > 0){
			$user_info = bamobile_filtered_user_to_application();
			$user_info = (array)$user_info;
		}else{
			$user_info = $user;
		}		
		// if exist mobiconnector avatar
		if(!empty($user_meta[$mobiconnector_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$mobiconnector_key]);
			if(!empty($attachment)){
				$user_meta['mobiconnector_avatar'] = $attachment;
				$user_info['mobiconnector_avatar'] = $attachment;
			}else{
				$user_meta['mobiconnector_avatar'] = null;
				$user_info['mobiconnector_avatar'] = null;
			}
		}else{
			if(!empty($user['user_email'])){
				$user_meta['mobiconnector_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
				$user_info['mobiconnector_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
			}else{
				$user_meta['mobiconnector_avatar'] = $this->bamobile_get_avatar_url($user_id);
				$user_info['mobiconnector_avatar'] = $this->bamobile_get_avatar_url($user_id);
			}
		}		
		//if exist wp user avatar
		if(!empty($user_meta[$avatar_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$avatar_key]);
			if(!empty($attachment)){
				$user_meta['wp_user_avatar'] = $attachment;
				$user_info['wp_user_avatar'] = $attachment;
			}
		}else{
			if(!empty($user['user_email'])){
				$user_meta['wp_user_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
				$user_info['wp_user_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
			}else{
				$user_meta['wp_user_avatar'] = get_avatar_url($user_id);
				$user_info['wp_user_avatar'] = get_avatar_url($user_id);
			}
		}		
		if(isset($user_meta['mobiconnector_address']) && is_string($user_meta['mobiconnector_address'])){
			$user_meta['mobiconnector_address'] = unserialize($user_meta['mobiconnector_address']);
		}else{
			$user_meta['mobiconnector_address'] = array();
		}
		if(isset($user_meta['field_extra_user']) && is_string($user_meta['field_extra_user'])){
			$user_meta['field_extra_user'] = unserialize($user_meta['field_extra_user']);
		}else{
			$user_meta['field_extra_user'] = array();
		}
		$user_meta['mobiconnector_info'] = $user_info;
		$user_meta = array_merge($user_meta, $user);
		$user_meta['mobiconnector_info']['first_name'] = isset($user_meta['first_name']) ? $user_meta['first_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['last_name'] = isset($user_meta['last_name']) ? $user_meta['last_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['description'] = isset($user_meta['description']) ? $user_meta['description'] : '';
		if(isset($user_meta['password'])){
			unset($user_meta['password']);
		}
		return $user_meta;
	}

	/** 
	 * Update avatar for user 
	 * 
	 * @param: image data in base64 encode
	 * 
	 */
	public function bamobile_update_user_avatar($user_id, $image_base64string) { 
		global $blog_id, $wpdb;
		// update avatar
		if(!empty($image_base64string)) {
			list($type, $image_base64string) = explode(';', $image_base64string);
			list(, $type)        = explode(':', $type);
			list(, $type)        = explode('/', $type);
			list(, $image_base64string)      = explode(',', $image_base64string);
			$type = strtolower($type); // get extension of image
			$image_base64string = str_replace(' ','+', $image_base64string);
			$data = base64_decode($image_base64string);
			// folder upload in wordpress
			$wp_upload_dir = wp_upload_dir();	
			$filename = "mobi_avatar_".time().".$type";
			$path_to_file = $wp_upload_dir['path']."/".$filename;
			$filetype = wp_check_filetype( basename( $filename), null );
			// create image in folder
			@file_put_contents($path_to_file, $data);
			// insert to wordpress
			$attachment = array(
				'post_author' => $user_id,
				'post_content' => '',
				'post_content_filtered' => '',
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_excerpt' => '',
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => $filetype['type'],
				'comment_status' => 'open',
				'ping_status' => 'closed',
				'post_password' => '',
				'to_ping' =>  '',
				'pinged' => '',
				'post_parent' => 0,
				'menu_order' => 0,
				'guid' => $wp_upload_dir['url']."/".$filename,
			);
			$wpdb->insert("{$wpdb->prefix}posts", $attachment); 
			$attach_id = $wpdb->insert_id;
			if($attach_id == false) // error when the update
				return false;
			// Generate the metadata for the attachment, and update the database record.
			$attach_data = wp_generate_attachment_metadata( $attach_id, $path_to_file );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			// update _wp_attached_file in postdata
			update_attached_file($attach_id, $path_to_file);
			delete_metadata('post', null, '_wp_attachment_wp_user_avatar', $user_id, true);
			add_post_meta($attach_id, '_wp_attachment_wp_user_avatar', $user_id);
			update_user_meta($user_id, $this->bamobile_getAvatarMetaKey(), $attach_id);
			delete_metadata('post', null, '_wp_attachment_mobiconnector_avatar', $user_id, true);
			add_post_meta($attach_id, '_wp_attachment_mobiconnector_avatar', $user_id);
			update_user_meta($user_id, $this->bamobile_getMobiconnectorMetaKey(), $attach_id);
			
			return true;
		}else{
			delete_metadata('post', null, '_wp_attachment_wp_user_avatar', $user_id, true);
			update_user_meta($user_id, $this->bamobile_getAvatarMetaKey(), 0);
			delete_metadata('post', null, '_wp_attachment_mobiconnector_avatar', $user_id, true);
			update_user_meta($user_id, $this->bamobile_getMobiconnectorMetaKey(), 0);
			
		}
		return false;
	}
	/**
	 * Get infomation of current user
	 * 
	 * @param WP_REST_Request $request  current Request
	 */
	public function bamobile_get_info($request) {
		global $wpdb,$blog_id;
		$parameters = $request->get_params();
		$player_id = isset($parameters['player_id']) ? $parameters['player_id'] : false;
		$checkplayer = $wpdb->get_var("SELECT COUNT(*) FROM ". $wpdb->prefix . "mobiconnector_manage_device WHERE player_id = '".$player_id."' AND blocked = '0'");
		if(!empty($player_id) && $checkplayer === "0"){
			return false;
		}
		// check username or email exist
		$userin = $parameters['username'];

		if(username_exists($userin)) {
			$user = get_user_by('login', $userin );
		}elseif(email_exists($userin)){
			$user = get_user_by('email',$userin);
		}else{
			return new WP_Error( 'username_not_exists', __('Username is not yet exists','mobiconnector'), array( 'status' => 400 ) );
		}				
		
		if ( ! $user ) {
			return new WP_Error( 'rest_user_invalid_id', __( 'Invalid resource id.','mobiconnector' ), array( 'status' => 400 ) );
		}
		
		$user = (array) $user->data;
		$user_id = $user['ID'];
		$user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
		$user_meta['view_private'] = user_can($user_id,'read_private_posts');
		$avatar_key = $this->bamobile_getAvatarMetaKey();
		if(!empty($user_meta[$avatar_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$avatar_key]);
			if(!empty($attachment)){
				$user_meta['wp_user_avatar'] = $attachment;
			}
		}
		$mobiconnector_key = $this->bamobile_getMobiconnectorMetaKey();
		if(!empty($user_meta[$mobiconnector_key])){
			$attachmentmobi = wp_get_attachment_url( (int) $user_meta[$mobiconnector_key]);
			if(!empty($attachmentmobi)){
				$user_meta['mobiconnector_avatar'] = $attachmentmobi;
			}
		}
		$user_info = array();
		$user_info = bamobile_filtered_user_to_application();
		$user_info = (array)$user_info;
		// if exist mobiconnector avatar
		if(!empty($user_meta[$mobiconnector_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$mobiconnector_key]);
			if(!empty($attachment)){
				$user_meta['mobiconnector_avatar'] = $attachment;
				$user_info['mobiconnector_avatar'] = $attachment;
			}else{
				$user_meta['mobiconnector_avatar'] = null;
				$user_info['mobiconnector_avatar'] = null;
			}
		}else{
			if(!empty($user['user_email'])){
				$user_meta['mobiconnector_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
				$user_info['mobiconnector_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
			}else{
				$user_meta['mobiconnector_avatar'] = $this->bamobile_get_avatar_url($user_id);
				$user_info['mobiconnector_avatar'] = $this->bamobile_get_avatar_url($user_id);
			}
		}
		//if exist wp user avatar
		if(!empty($user_meta[$avatar_key])){
			$attachment = wp_get_attachment_url( (int) $user_meta[$avatar_key]);
			if(!empty($attachment)){
				$user_meta['wp_user_avatar'] = $attachment;
				$user_info['wp_user_avatar'] = $attachment;
			}
		}else{
			if(!empty($user['user_email'])){
				$user_meta['wp_user_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
				$user_info['wp_user_avatar'] = $this->bamobile_get_gravatar_url($user['user_email']);
			}else{
				$user_meta['wp_user_avatar'] = get_avatar_url($user_id);
				$user_info['wp_user_avatar'] = get_avatar_url($user_id);
			}
		}		
		if(isset($user_meta['mobiconnector_address']) && is_string($user_meta['mobiconnector_address'])){
			$user_meta['mobiconnector_address'] = unserialize($user_meta['mobiconnector_address']);
		}else{
			$user_meta['mobiconnector_address'] = array();
		}
		if(isset($user_meta['field_extra_user']) && is_string($user_meta['field_extra_user'])){
			$user_meta['field_extra_user'] = unserialize($user_meta['field_extra_user']);
		}else{
			$user_meta['field_extra_user'] = array();
		}
		$user_meta['mobiconnector_info'] = $user_info;
		$user_meta = array_merge($user_meta, $user);
		unset($user_meta['user_pass']);
		unset($user_meta['_woocommerce_persistent_cart_1']);
		unset($user_meta['meta-box-order_dashboard']);
		unset($user_meta['shipping_method']);		
		unset($user_meta['show_admin_bar_front']);
		unset($user_meta['last_update']);
		unset($user_meta['user_registered']);
		unset($user_meta['user_activation_key']);
		unset($user_meta['user_status']);
		unset($user_meta['last_update']);
		unset($user_meta['rich_editing']);
		unset($user_meta['comment_shortcuts']);
		unset($user_meta['admin_color']);
		unset($user_meta['wp_user_level']);
		unset($user_meta['use_ssl']);
		unset($user_meta['description']);
		unset($user_meta['dismissed_wp_pointers']);
		unset($user_meta['show_welcome_panel']);
		unset($user_meta['wp_dashboard_quick_press_last_post_id']);
		unset($user_meta['wp_user-settings']);
		unset($user_meta['wp_user-settings-time']);
		unset($user_meta['managenav-menuscolumnshidden']);
		unset($user_meta['metaboxhidden_nav-menus']);
		unset($user_meta['closedpostboxes_dashboard']);
		unset($user_meta['metaboxhidden_post']);
		$mobile_prefix = $this->bamobile_getprefix();
		if(isset($user_meta[$mobile_prefix.'capabilities'])){
			$user_meta['wp_capabilities'] = unserialize($user_meta[$mobile_prefix.'capabilities']);
		}
		unset($user_meta[$mobile_prefix.'capabilities']);
		// check image of wp user avatar
		if(!empty($user_meta[$avatar_key]) && isset($user_meta['wp_user_avatar']) && !empty($user_meta['wp_user_avatar'])){
			$avatar_is_images = $this->bamobile_is_image($user_meta['wp_user_avatar']);
			if(!$avatar_is_images){
				unset($user_meta['wp_user_avatar']);
			}
		}
		unset($user_meta['nav_menu_recently_edited']);
		unset($user_meta['session_tokens']);
		unset($user_meta['user_url']);
		$user_meta['mobiconnector_info']['first_name'] = isset($user_meta['first_name']) ? $user_meta['first_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['last_name'] = isset($user_meta['last_name']) ? $user_meta['last_name'] : $user_meta['user_login'];
		$user_meta['mobiconnector_info']['description'] = isset($user_meta['description']) ? $user_meta['description'] : '';
		if(isset($user_meta['password'])){
			unset($user_meta['password']);
		}
		return $user_meta;
	}

	/**
	 * Get Database Prefix
	 */
	public function bamobile_getprefix(){
		global $wpdb;
		return $wpdb->prefix;
	}

	/**
	 * Get meta key of wp user avatar
	 */
	public function bamobile_getAvatarMetaKey(){
		global $blog_id, $wpdb;
		return $wpdb->get_blog_prefix($blog_id).'user_avatar';
	}

	/**
	 * Get meta ket of mobiconnector avatar
	 */
	public function bamobile_getMobiconnectorMetaKey(){
		return "mobiconnector-avatar";
	}

	/**
	 * check if images
	 * 
	 * @param string $path  Link of image
	 * 
	 * @return boolean
	 */
	public function bamobile_is_image($path = ''){
		if($path == '' || empty($path) || $path == null){
			return false;
		}
		$checkexisttail = strripos($path,'.');
		if(empty($checkexisttail)){
			return false;
		}
		$ext = substr($path,strripos($path,'.')+1);
		if(empty($ext)){
			return false;
		}
		$image = getimagesize($path);
		if(empty($image)){
			return false;
		}		
		$image_type = $image[2];
		if(empty($image_type)){
			return false;
		}
		if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP))){
			return true;
		}
		return false;
	}

	/**
	 * Get link gravatar
	 * 
	 * @param string $email  Email get avatar
	 * 
	 * @return string  Link image
	 */
	private function bamobile_get_gravatar_url( $email ) {
        $id_default = get_option('avatar_default');
        $ratting = strtolower(get_option('avatar_rating'));
        $hash = md5( strtolower( trim ( $email ) ) );
        return 'http://gravatar.com/avatar/' . $hash . '?s=96&d='.$id_default.'&r='.$ratting;
    }
}
$BAMobileUser = new BAMobileUser();
?>