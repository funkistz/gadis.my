<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class ModernCategories extends  WP_REST_Controller{
	
	public $thumnails = array(	
		'modern_square' => array(
			'width' => 480,
			'height' => 480
		),
	);	
	
	public function __construct() {
		add_filter( 'wooconnector_product_categories_data', array($this,'get_crop_images_hook') ,100,1);
		add_filter( 'wooconnector_product_brands_data', array($this,'get_crop_images_brands_hook') , 100, 1);
		$this->register_routes();
		
	}
	
	public function update_thumnail_woo($imagesID ) {
		$post_thumbnail_id = $imagesID;
		if(empty($post_thumbnail_id))
			return true;
		/// ki?m tra xem d� t?n t?i thumnail chua
		$modern_square = get_post_meta($post_thumbnail_id, 'modern_square', true);
		if(!empty($modern_square))
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
		
		foreach($this->thumnails as $key => $value){
			$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
			$dest = $wp_upload_dir['basedir'].'/'.$path;
			ModernCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height'],true);
			// c?p nh?t post meta for thumnail
			update_post_meta ($post_thumbnail_id, $key, $path);
		}
		
		
		return true;
	}

	
	public function register_routes() {	
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	public function register_api_hooks() {
		register_rest_field( 'product_cat',
			'modernshop_images_categories',
			array(
				'get_callback'    => array($this, 'get_crop_image'),
				'update_callback' => null,
				'schema'          => null,
			)
		);	
	}
	
	public function get_crop_images_hook($data){
		$images = $data['image'];
		if(!empty($images)){	
			$image_id = $images['id'];
			$wp_upload_dir = wp_upload_dir();	
			$modern_square = get_post_meta($image_id, 'modern_square', true);
			if(empty($modern_square)) { // chua t?n t?i ?nh th� t?o
				$this->update_thumnail_woo($image_id);
			}
			// g?n thumnail m?i
			foreach($this->thumnails as $key => $value){
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
		$data['modernshop_images_categories'] = $crop_image;
		return $data;
	}

	public function get_crop_images_brands_hook($data){
		$images = $data['image'];
		if(!empty($images)){	
			$image_id = $images['id'];
			$wp_upload_dir = wp_upload_dir();	
			$modern_square = get_post_meta($image_id, 'modern_square', true);
			if(empty($modern_square)) { // chua t?n t?i ?nh th� t?o
				$this->update_thumnail_woo($image_id);
			}
			// g?n thumnail m?i
			foreach($this->thumnails as $key => $value){
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
		$data['modernshop_images_brands'] = $crop_image;
		return $data;
	}
	
	
	public function get_crop_image( $object, $field_name, $request) {
		$images = $object['image'];
		if(!empty($images)){	
			$image_id = $images['id'];
			$wp_upload_dir = wp_upload_dir();	
			$modern_square = get_post_meta($image_id, 'modern_square', true);
			if(empty($modern_square)) { // chua t?n t?i ?nh th� t?o
				$this->update_thumnail_woo($image_id);
			}
			// g?n thumnail m?i
			foreach($this->thumnails as $key => $value){
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
$ModernCategories = new ModernCategories();
?>