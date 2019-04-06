<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add new line or edit data response in REST API categories
 */
class BAMobileCategory{
	
	/**
	 * MobiConnector Category construct
	 */
	public function __construct() {		
		$this->register_routes();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'bamobile_register_api_hooks'));
		add_filter( 'rest_prepare_category',array($this,'bamobile_prepare_restful_categories'), 10, 3);
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
	public function bamobile_prepare_restful_categories($response, $item, $request) {
		$parameters = $request->get_params();
		if(!isset($parameters['id'])){
			unset($response->data['description']);
		}
		unset($response->data['link']);
		unset($response->data['taxonomy']);
		unset($response->data['meta']);
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
		register_rest_field( 'category',
			'mobiconnector_avatar',
			array(
				'get_callback'    => array($this, 'bamobile_get_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		register_rest_field( 'category',
			'mobiconnector_plugin_avatar',
			array(
				'get_callback'    => array($this, 'bamobile_get_plugin_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		register_rest_field( 'category',
			'mobiconnector_last_post',
			array(
				'get_callback'    => array($this, 'bamobile_get_post_category'),
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
	public function bamobile_get_avatar( $object, $field_name, $request) {		
		require_once( MOBICONNECTOR_ADMIN_PATH . 'includes/plugin.php' );
		if(is_plugin_active('wpcustom-category-image/load.php') == false)
			return null;
			require_once(WP_CONTENT_DIR.'/plugins/wpcustom-category-image/WPCustomCategoryImage.php');
		$attr = array(
			'term_id' => $object['id'],
		);
		return WPCustomCategoryImage::get_category_image($attr, true);		
	}

	/**
	 * Add data of line mobiconnector_plugin_avatar
	 * 
	 * @param array    			$object     one data in list data of REST API categories
	 * @param string   		    $field_name field name
	 * @param WP_REST_Request   $request    Request used to generate the response.
	 * 
	 * @return mixed
	 */
	public function bamobile_get_plugin_avatar($object, $field_name, $request){
		$attr = array(
			'term_id' => $object['id'],
		);
		if (function_exists( 'bamobile_mobiconnector_category_image_src' ) ) {
			return bamobile_mobiconnector_category_image_src($attr);
		}else{
			return null;
		}
	}

	/**
	 * Add data of line mobiconnector_last_post
	 * 
	 * @param array    			$object     one data in list data of REST API categories
	 * @param string   		    $field_name field name
	 * @param WP_REST_Request   $request    Request used to generate the response.
	 * 
	 * @return mixed
	 */
	public function bamobile_get_post_category($object, $field_name, $request){
		$id = $object['id'];
		$args = array(
			'posts_per_page'   => 1,
			'page'             => 1,
			'offset'           => 0,
			'category'         => $id,
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'post',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	       => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$result =  get_posts($args);
		if($result != null || !empty($result))
		{
			foreach($result as $post)
			{
				$daten = new DateTime($post->post_date);
				$date = $daten->format('Y-m-d\TH:i:s');	
				$dategmtn = new DateTime($post->post_date_gmt);
				$dategmt = $dategmtn->format('Y-m-d\TH:i:s');	
				$modin = new DateTime($post->post_modified);
				$modi = $modin->format('Y-m-d\TH:i:s');	
				$modigmtn = new DateTime($post->post_modified_gmt);
				$modigmt = $modigmtn->format('Y-m-d\TH:i:s');	
				$postcate['ID'] = $post->ID;
				$postcate['post_date'] = $date;
				$postcate['post_date_gmt'] = $dategmt;
				$content = $post->post_content;
				if($content !==  '' && ($post->post_format != 'image' || $post->post_format != 'video' || $post->post_format != 'gallery' || $post->post_format != 'link')){
					$content = bamobile_mobiconnector_get_plaintext($content);
					if(strpos($content,'.') != false){
						if(count($content) > 100){
							$content = substr($content,0,strpos($content,'.'));
						}
					}
				}
				$postcate['post_content'] = $content;
				$postcate['post_title'] = $post->post_title;
				$postcate['post_name'] = $post->post_name;
				$postcate['post_parent'] = $post->post_parent;
				$thumbnailId = get_post_thumbnail_id($post->ID);
				$postcate['mobiconnector_feature_image']['source_url'] = wp_get_attachment_url( $thumbnailId );
				$wp_upload_dir = wp_upload_dir();		
				foreach($this->thumnails as $key => $value)	{			
					$postmeta = get_post_meta($thumbnailId,$key,true);
					if(!empty($postmeta)){
						$postcate[$key] = $wp_upload_dir['baseurl']."/".$postmeta;					
					}
					else{
						$postcate[$key] = null;			
					}
				}
				$postcate['mobiconnector_feature_image']['feature_image_small']= $postcate['mobiconnector_small'];
				$postcate['mobiconnector_feature_image']['feature_image_medium'] = $postcate['mobiconnector_medium'];
				$postcate['mobiconnector_feature_image']['feature_image_large'] = $postcate['mobiconnector_large'];
				$postcate['mobiconnector_feature_image']['feature_image_x_large'] = $postcate['mobiconnector_x_large'];
				unset($postcate['mobiconnector_small']);
				unset($postcate['mobiconnector_medium']);
				unset($postcate['mobiconnector_large']);
				unset($postcate['mobiconnector_x_large']);
				return $postcate;
			}
		}
		else{
			return null;
		}
	}

	/**
	 * Size of thumbnail
	 */
	public $thumnails = array(
		'mobiconnector_small' => array(
			'width' => 320,
			'height' => 240
		),
		'mobiconnector_medium' => array(
			'width' => 480,
			'height' => 360
		),
		'mobiconnector_large' => array(
			'width' => 752,
			'height' => 564
		),
		'mobiconnector_x_large' => array(
			'width' => 1080,
			'height' => 810
		),
	);	

}
$BAMobileCategory = new BAMobileCategory();
?>