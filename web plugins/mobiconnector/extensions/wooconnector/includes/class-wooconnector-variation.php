<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooconnectorVariations extends  WP_REST_Controller{
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
		register_rest_field( 'product',
			'variations',
			array(
				'get_callback'    => array($this, 'get_images_variations'),
				'update_callback' => null,
				'schema'          => null,
			)
		);	
		
	}
	/*
	* Get crop images 
	*/
	public function get_images_variations( $object, $field_name, $request) {
		
		$idproduct = $object['id'];
		$product = wc_get_product($idproduct);
		if($product->get_type = 'variable')
		{
			$variations = $product->get_children();
			$wp_upload_dir = wp_upload_dir();
			if(!empty($variations))
			{
				foreach($variations as $varia)
				{
					$variation = wc_get_product($varia);					
					$images = $variation->get_image_id();
					$imagelinks = array();					
					$image_id = $images;					
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
					$imagelinks[] = $crop_image;
					
					$list[] = array(
						'id' => $variation->get_id(),
						'date_created'       => wc_rest_prepare_date_response( $variation->get_date_created() ),
						'date_modified'      => wc_rest_prepare_date_response( $variation->get_date_modified() ),
						'permalink'          => $variation->get_permalink(),
						'sku'                => $variation->get_sku(),
						'price'              => $variation->get_price(),
						'regular_price'      => $variation->get_regular_price(),
						'sale_price'         => $variation->get_sale_price(),
						'date_on_sale_from'  => $variation->get_date_on_sale_from() ? date( 'Y-m-d', $variation->get_date_on_sale_from()->getTimestamp() ) : '',
						'date_on_sale_to'    => $variation->get_date_on_sale_to() ? date( 'Y-m-d', $variation->get_date_on_sale_to()->getTimestamp() ) : '',
						'on_sale'            => $variation->is_on_sale(),
						'purchasable'        => $variation->is_purchasable(),
						'visible'            => $variation->is_visible(),
						'virtual'            => $variation->is_virtual(),
						'downloadable'       => $variation->is_downloadable(),
						'downloads'          => $this->get_downloads( $variation ),
						'download_limit'     => '' !== $variation->get_download_limit() ? (int) $variation->get_download_limit() : -1,
						'download_expiry'    => '' !== $variation->get_download_expiry() ? (int) $variation->get_download_expiry() : -1,
						'tax_status'         => $variation->get_tax_status(),
						'tax_class'          => $variation->get_tax_class(),
						'manage_stock'       => $variation->managing_stock(),
						'stock_quantity'     => $variation->get_stock_quantity(),
						'in_stock'           => $variation->is_in_stock(),
						'backorders'         => $variation->get_backorders(),
						'backorders_allowed' => $variation->backorders_allowed(),
						'backordered'        => $variation->is_on_backorder(),
						'weight'             => $variation->get_weight(),
						'dimensions'         => array(
							'length' => $variation->get_length(),
							'width'  => $variation->get_width(),
							'height' => $variation->get_height(),
						),
						'shipping_class'     => $variation->get_shipping_class(),
						'shipping_class_id'  => $variation->get_shipping_class_id(),					
						'attributes'         => $this->get_attributes( $variation ),
						'wooconnector_variation_description' => $variation->get_description(),
						'wooconnector_crop_images' => $imagelinks					
					);
				}
			}
			else{
				$list = array();
			}
		}
		else{
			$list = array();
		}
		
		return $list;		
	
	}
	
	/**
	 * Get the attributes for a product or product variation.
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) ) {
			$_product = wc_get_product( $product->get_parent_id() );
			foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
				$name = str_replace( 'attribute_', '', $attribute_name );

				if ( ! $attribute ) {
					continue;
				}

				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
					$option_term = get_term_by( 'slug', $attribute, $name );
					$attributes[] = array(
						'id'     => wc_attribute_taxonomy_id_by_name( $name ),
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => $option_term && ! is_wp_error( $option_term ) ? $option_term->name : $attribute,
					);
				} else {
					$attributes[] = array(
						'id'     => 0,
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => $attribute,
					);
				}
			}
		} else {
			foreach ( $product->get_attributes() as $attribute ) {
				$attributes[] = array(
					'id'        => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name( $attribute['name'] ) : 0,
					'name'      => $this->get_attribute_taxonomy_name( $attribute['name'], $product ),
					'position'  => (int) $attribute['position'],
					'visible'   => (bool) $attribute['is_visible'],
					'variation' => (bool) $attribute['is_variation'],
					'options'   => $this->get_attribute_options( $product->get_id(), $attribute ),
				);
			}
		}

		return $attributes;
	}
	
	protected function get_downloads( $product ) {
		$downloads = array();

		if ( $product->is_downloadable() ) {
			foreach ( $product->get_downloads() as $file_id => $file ) {
				$downloads[] = array(
					'id'   => $file_id, // MD5 hash.
					'name' => $file['name'],
					'file' => $file['file'],
				);
			}
		}

		return $downloads;
	}
	
	protected function get_attribute_taxonomy_name( $slug, $product ) {
		$attributes = $product->get_attributes();

		if ( ! isset( $attributes[ $slug ] ) ) {
			return str_replace( 'pa_', '', $slug );
		}

		$attribute = $attributes[ $slug ];

		// Taxonomy attribute name.
		if ( $attribute->is_taxonomy() ) {
			$taxonomy = $attribute->get_taxonomy_object();
			return $taxonomy->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	}
	
	protected function get_attribute_options( $product_id, $attribute ) {
		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
			return wc_get_product_terms( $product_id, $attribute['name'], array( 'fields' => 'names' ) );
		} elseif ( isset( $attribute['value'] ) ) {
			return array_map( 'trim', explode( '|', $attribute['value'] ) );
		}

		return array();
	}
	
}
$WooconnectorVariations = new WooconnectorVariations();
?>