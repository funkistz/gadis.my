<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * MobiConnector Hooks Post Custom
 * 
 * Add data of rest in get details REST API
 * 
 * @class MobiConnector_Hooks_Post_Custom
 */
class BAMobile_Hooks_Post_Custom extends WP_REST_Controller{
	/**
     * MobiConnector Hooks Post Custom construct
     */
    public function __construct(){
        $this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){
		add_filter('bamobile_mobiconnector_custom_post_type_detail',array($this,'bamobile_get_details_rest_api_post_type'),10,2);
	}

	/**
	 * Add data line to get details post in REST API
	 * 
	 * @param object $object $object to check against.
	 * @param WP_REST_Request $request Request data to check.
	 * 
	 * @return object Details post when the add new line
	 */
	public function bamobile_get_details_rest_api_post_type($object, $request){
		$wp_rest_additional_fields = $this->get_additional_fields('post');
		foreach ( $wp_rest_additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['get_callback'] ) {
				continue;
			}
			$object[ $field_name ] = call_user_func( $field_options['get_callback'], $object, $field_name, $request, $this->get_object_type() );
		}
		return $object;
	}
}
$BAMobile_Hooks_Post_Custom = new BAMobile_Hooks_Post_Custom();
?>