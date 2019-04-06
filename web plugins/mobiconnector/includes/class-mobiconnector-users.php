<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add new line or edit data response in REST API users
 */
class BAMobileUsers{

	/**
	 * MobiConnector User construct
	 */	
	public function __construct() {		
		$this->register_routes();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'bamobile_register_api_hooks'));
		add_action( 'wp_authenticate',array($this, 'bamobile_mobiconnector_login_with_email_address'));
	}

	/**
	 * Enable login by Email
	 * 
	 * @param string $username   Email enable login
	 * 
	 * @return string  username
	 */
	public function bamobile_mobiconnector_login_with_email_address($username) {
		$user = get_user_by('email',$username);
		if(!empty($user->user_login))
			$username = $user->user_login;
		return $username;
	}	

	/**
	 * Register link API or field in REST API
	 */
	public function bamobile_register_api_hooks() {
		// add to user object: mobiconnector_local_avatar : get avatar based https://wordpress.org/plugins/wp-user-avatar/screenshots/
		register_rest_field( 'user',
			'mobiconnector_local_avatar',
			array(
				'get_callback'    => array($this, 'bamobile_get_local_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	/**
	 * Add data of line mobiconnector_avatar
	 * 
	 * @param array    			$object     one data in list data of REST API categories
	 * @param string   		    $field_name field name
	 * @param WP_REST_Request   $request    Request used to generate the response.
	 * 
	 * @return mixed
	 */
	public function bamobile_get_local_avatar( $object, $field_name, $request) {
		$avatar = get_avatar( $object["id"], 96); 
		if(!empty($avatar)) {
          $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER);
          $avatar = !empty($matches) ? $matches [0] [1] : "";
        }
		return $avatar;
	}
}
$BAMobileUsers = new BAMobileUsers();
?>