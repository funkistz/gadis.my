<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooconnectorCategories extends  WP_REST_Controller{
	/*
	*@ Size of thumbnail
	*/
	public $thumnailsX = array(
		'wooconnector_small' => array(
			'width' => 320,
			'height' => 240
		),
		'wooconnector_medium' => array(
			'width' => 480,
			'height' => 360
		),
		'wooconnector_large' => array(
			'width' => 752,
			'height' => 564
		),
		'wooconnector_x_large' => array(
			'width' => 1080,
			'height' => 810
		),
	);	

	public $thumnailsS = array(
		'wooconnector_small' => array(
			'width' => 320,
			'height' => 320
		),
		'wooconnector_medium' => array(
			'width' => 480,
			'height' => 480
		),
		'wooconnector_large' => array(
			'width' => 752,
			'height' => 752
		),
		'wooconnector_x_large' => array(
			'width' => 1080,
			'height' => 1080
		),
	);	
	
	public $thumnailsY = array(
		'wooconnector_small' => array(
			'width' => 240,
			'height' => 320
		),
		'wooconnector_medium' => array(
			'width' => 360,
			'height' => 480
		),
		'wooconnector_large' => array(
			'width' => 564,
			'height' => 752
		),
		'wooconnector_x_large' => array(
			'width' => 810,
			'height' => 1080
		),
	);	
	
	public function __construct() {
		
		$this->register_routes();		
	}
	
	public function update_thumnail_woo($imagesID ) {
		$post_thumbnail_id = $imagesID;
		if(empty($post_thumbnail_id))
			return true;
		/// ki?m tra xem d� t?n t?i thumnail chua
		$wooconnector_large = get_post_meta($post_thumbnail_id, 'wooconnector_large', true);
		$wooconnector_medium = get_post_meta($post_thumbnail_id, 'wooconnector_medium', true);
		$wooconnector_x_large = get_post_meta($post_thumbnail_id, 'wooconnector_x_large', true);
		$wooconnector_small = get_post_meta($post_thumbnail_id, 'wooconnector_small', true);
		if(!empty($wooconnector_medium) && !empty($wooconnector_x_large) && !empty($wooconnector_large) && !empty($wooconnector_small))
			return true; // d� t?n t?i r?i ko t?o n?a
		// l?y th�ng tin c?a ?nh
		$relative_pathto_file = get_post_meta( $post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$relative_pathto_file;
		// ki?m tra file g?c c� t?n t?i hay kh�ng?
		if(!file_exists($absolute_pathto_file))
			return true; // file ko t?n t?i
		////////////////
			
		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// t?o ?nh 
		list($width, $height) = getimagesize($absolute_pathto_file);
		if($width > $height){
			foreach($this->thumnailsX as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}elseif($width < $height){
			foreach($this->thumnailsY as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}elseif($width == $height){
			foreach($this->thumnailsS as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}
		return true;
	}
			
	public function register_routes() {	
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	public function register_api_hooks() {
		register_rest_field( 'product_cat',
			'wooconnector_images_categories',
			array(
				'get_callback'    => array($this, 'get_images_categories'),
				'update_callback' => null,
				'schema'          => null,
			)
		);	
		
	}
	/*
	* Get crop images 
	*/
	public function get_images_categories( $object, $field_name, $request) {		
		$images = $object['image'];
		if(!empty($images)){	
			$image_id = $images['id'];
			$wp_upload_dir = wp_upload_dir();	
			$wooconnector_large = get_post_meta($image_id, 'wooconnector_large', true);
			$wooconnector_medium = get_post_meta($image_id, 'wooconnector_medium', true);
			$wooconnector_x_large = get_post_meta($image_id, 'wooconnector_x_large', true);
			$wooconnector_small = get_post_meta($image_id, 'wooconnector_small', true);
			if( empty($wooconnector_large) || empty($wooconnector_medium) || empty($wooconnector_x_large) || empty($wooconnector_small)) { 
					
				$this->update_thumnail_woo($image_id);
			}
			// g?n thumnail m?i
			foreach($this->thumnailsX as $key => $value){
				$imagesupload = get_post_meta($image_id, $key, true);
				if(empty($imagesupload))
				{
					$crop_image[$key] = null;
				}
				else{
					$crop_image[$key] = $wp_upload_dir['baseurl']."/". $imagesupload;
				}
			}
		}else{
			$crop_image = null;
		}
		return $crop_image;		
		
	}
	
	
}
$WooconnectorCategories = new WooconnectorCategories();
?>