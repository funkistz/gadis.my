<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooconnectorOrders extends  WP_REST_Controller{
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
	
	
	public function __construct() {
		
		$this->register_routes();		
	}
			
	public function register_routes() {	
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
		add_action( 'save_post' , array($this,'set_save_order_details'));
		add_action( 'woocommerce_order_edit_status' , array($this,'change_order_status_by_ajax'),10,2 );
	}

	public function register_api_hooks() {
		register_rest_field( 'shop_order',
			'wooconnector_line_items',
			array(
				'get_callback'    => array($this, 'get_line_items'),
				'update_callback' => null,
				'schema'          => null,
			)
		);	
	}

	public function change_order_status_by_ajax($order_id,$status){
		$player_id = get_post_meta($order_id,'onesignal_player_id',true);
		$nowtime = date('Y-m-d H:i');		
		if(!empty($player_id)){
			sendWooconnectorMessageByOrderStatus($player_id,$order_id,$nowtime);
		}			
	}

	public function set_save_order_details($post_id){
		$type = sanitize_text_field(@$_POST['post_type']);
		if($type == 'shop_order'){
			$player_id = get_post_meta($post_id,'onesignal_player_id',true);
			$nowtime = date('Y-m-d H:i');		
			if(!empty($player_id)){
				sendWooconnectorMessageByOrderStatus($player_id,$post_id,$nowtime);
			}			
		}
		return true;
	}

	/*
	* Get crop images 
	*/
	public function get_line_items( $object, $field_name, $request) {		
		$line_items = $object['line_items'];
		foreach($line_items as $line_item => $value){			
			if (! empty( $value['product_id'] ) && empty( $value['variation_id'] ) ) {
				$product_id = (int) $value['product_id'];
			} elseif ( ! empty( $value['variation_id'] ) ) {
				$product_id = (int) $value['variation_id'];
			} 
			$product = wc_get_product($product_id);			
			$thumbId = get_post_thumbnail_id($value['product_id']);
			$wp_upload = wp_upload_dir();
			foreach($this->thumnailsX as $key => $val){
				$path = get_post_meta($thumbId, $key, true);
				if(empty($path))
				{
					$limages[$key] = null;
				}
				else{
					$limages[$key] = $wp_upload['baseurl']."/". get_post_meta($thumbId, $key, true);
				}				
			}	
			$value['regular_price'] = $product->get_regular_price();
			$value['sale_price'] = $product->get_sale_price();
			$value['description'] = $product->get_description();
			$value['short_description'] = $product->get_short_description();
			$value['images'] =  $limages;	
			$list[] = $value;
		}
	
		return  $list;
	
	}
	
	
}
$WooconnectorOrders = new WooconnectorOrders();
?>