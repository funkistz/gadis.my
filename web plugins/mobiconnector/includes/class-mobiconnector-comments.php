<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add new line or edit data response in REST API comments
 */
class BAMobileComments{

	/**
	 * MobiConnector Comment construct
	 */	
	public function __construct() {		
		$this->register_routes();
	}

	/**
	 * Active Guest Reviews
	 */
	public function bamobile_mobiconnector_rest_allow_anonymous_comments() {
		$active = get_option('mobiconnector_settings-guest-reviews');
		if($active == 1){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action('rest_api_init', array( $this, 'bamobile_register_api_hooks'));
		add_filter('rest_prepare_comment',array($this,'bamobile_prepare_restful_comments'), 10, 3);
		add_filter('rest_allow_anonymous_comments',array($this,'bamobile_mobiconnector_rest_allow_anonymous_comments'));		
	}

	/**
	 * Prepare date of response categories and change it
	 * 
	 * @param WP_REST_Response $response  Result to send to the client.
     * @param WP_Post          $item      Post
     * @param WP_REST_Request  $request   Request used to generate the response.
	 * 
	 * @return WP_REST_Response Response object.
	 */
	public function bamobile_prepare_restful_comments($response, $item, $request){
		unset($response->data['post']);
		unset($response->data['parent']);
		unset($response->data['author_url']);
		unset($response->data['status']);
		unset($response->data['type']);
		unset($response->data['link']);
		$links = $response->get_links();
		foreach($links as $rel => $set){
			if ( isset( $set['href'] ) ) {
				$set = array( $set );
			}
			foreach ( $set as $attributes ) {
				$response->remove_link( $rel, $attributes['href'], $attributes );
			}
		}
		return $response;
	}

	/**
	 * Register link API or field in REST API
	 */
	public function bamobile_register_api_hooks() {
		// add to user object: mobiconnector_local_avatar : get avatar based https://wordpress.org/plugins/wp-user-avatar/screenshots/
		register_rest_field( 'comment',
			'mobiconnector_local_avatar',
			array(
				'get_callback'    => array($this, 'bamobile_get_local_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	/**
	 * Add data of line mobiconnector_local_avatar
	 * 
	 * @param array    			$object     one data in list data of REST API categories
	 * @param string   		    $field      field name
	 * @param WP_REST_Request   $request    Request used to generate the response.
	 * 
	 * @return mixed
	 */
	public function bamobile_get_local_avatar( $object, $field_name, $request) {
		$avatar = get_avatar( $object["author"], 96); 
		if(!empty($avatar)) {
          $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER);
          $avatar = !empty($matches) ? $matches [0] [1] : "";
        }
		return $avatar;
	}
}
$BAMobileComments = new BAMobileComments();
?>