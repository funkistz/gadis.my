<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * MobiConnector Post Type Core
 * 
 * Add data post type to Rest Api
 * 
 * @class MobiConnector_PostType_Core
 */
class BAMobile_PostType_Core{
    /**
     * MobiConnector Post Type Core construct
     */
    public function __construct(){
        $this->init_hooks();
        $this->includes_and_requires();
    }

    /**
	 * Include required core files used in admin and on the frontend.
	 */
    private function includes_and_requires(){
        require_once( __DIR__.'/class-mobiconnector-pre-post-custom.php');
		require_once( __DIR__.'/class-mobiconnector-hooks-posts-custom.php' );
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){       
        add_filter('parse_query',array($this,'bamobile_mobiconnector_add_type_to_posts_rest_api'));
        add_action('init',array($this,'bamobile_mobiconnector_add_show_in_rest_to_post_type'), 25);
        add_filter('rest_request_after_callbacks',array($this,'bamobile_mobiconnector_change_response_post_details'), 10, 3 );
        add_filter('rest_request_after_callbacks',array($this, 'bamobile_change_error_max_page'), 10, 3 );	
    }

    /**
     * Add post type to list post type in query Wordpress
     * 
     * @param WP_Query $query Query of sql in request.
     */
    public function bamobile_mobiconnector_add_type_to_posts_rest_api($query){
        $qv =& $query->query_vars;
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                    $post_type .= $name.',';
                }
            }
            $post_type = trim($post_type,',');
            $post_type = explode(',',$post_type);
            if(bamobile_mobiconnector_is_rest_posts() || bamobile_mobiconnector_is_rest_posts_detail()){
                $qv['post_type'] = $post_type;
            }
        }
    }

    /**
     * Add show in rest to list post type active
     */
    public function bamobile_mobiconnector_add_show_in_rest_to_post_type() {
        global $wp_post_types;
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                    if( isset( $wp_post_types[$name] ) ) {
                        $wp_post_types[$name]->show_in_rest = true;
                    }
                }
            }
        }
    }

    /**
     * Change rest post details
     * 
     * @param WP_REST_Response $response Result to send to the client.
     * @param WP_REST_Server $handler ResponseHandler instance.
     * @param WP_REST_Request $request Request used to generate the response.
     * 
     * @return WP_REST_Response Response object.
     */
    public function bamobile_mobiconnector_change_response_post_details( $response, $handler, $request ) {
        $params = $request->get_params();
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        if(bamobile_mobiconnector_is_rest_posts_detail()){
            if(is_wp_error($response)){
                $codeerror = $response->get_error_code();
                if($codeerror == 'rest_post_invalid_id'){
                    $id = $params['id'];
                    $post = get_post($id);
                    if( !empty( $post ) || !empty( $post->ID )){
                        $type = $post->post_type;
                        if(!empty($listoptionscheckbox)){
                            foreach($listoptionscheckbox as $posttype => $value){
                                $name = str_replace('mobi-','',$posttype); 
                                if(class_exists('BAMobile_Pre_Post_Custom')){
                                    $pre_post = new BAMobile_Pre_Post_Custom($name);
                                    if($value == 1){
                                        if($type == $name){
                                            $post = $pre_post->bamobile_mobiconnector_pre_post($post,$request);
                                            $datapost = $post->data;
                                            $response = apply_filters('bamobile_mobiconnector_custom_post_type_detail',$datapost,$request);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }    

    /**
	 * Change error with max page
	 * 
	 * @param WP_REST_Response $response Result to send to the client.
     * @param WP_REST_Server $handler ResponseHandler instance.
     * @param WP_REST_Request $request Request used to generate the response.
	 */
	public function bamobile_change_error_max_page($response, $handler, $request){
		if(is_wp_error($response)){			
			$codeerror = $response->get_error_code();
			if($codeerror == 'rest_post_invalid_page_number'){
				$response = array();
			}
		}
		return $response;		
	}
}
$BAMobile_PostType_Core = new BAMobile_PostType_Core();
?>