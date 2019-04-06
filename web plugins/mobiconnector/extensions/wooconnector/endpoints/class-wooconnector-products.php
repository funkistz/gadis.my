<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooConnectorProduct extends WP_REST_Controller{

	private $rest_url = 'wooconnector/product';	
	private static $id_with_product = array();	
	private static $id_with_product_variation = array();	
	private static $id_with_categories = array();	
	private static $id_with_brands = array();
	private static $default_lang = '';

	private static function wooconnector_is_rest_api(){
		$url = $_SERVER['REQUEST_URI'];
		$sUrl = substr($url,strpos($url,'wp-json'));
		$oUrl = $sUrl;
		if(strpos($sUrl,'?') != false){
			$oUrl = substr($sUrl,0,strpos($sUrl,'?'));
		}
		$tUrl = trim($oUrl,'/');
		$aUrl = explode('/',$tUrl);
		if(!isset($aUrl[0]) || isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || !isset($aUrl[1]) || isset($aUrl[1]) &&  !in_array($aUrl[1],array('wooconnector','mobiconnector','cellstore','modernshop'))){
			return false;
		}
		return true;
	}
	
	public function __construct() {
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			self::$default_lang = self::get_default_wpml_languages();
			self::checkproductwithWPML();
			self::checkproductvariationwithWPML();
			self::checkcategorieswithWPML();
			self::checkbrandswithWPML();
			if(self::wooconnector_is_rest_api()){
				remove_all_filters('posts_where');
				remove_all_filters('get_terms_args');
			    remove_all_filters('terms_clauses');
			    remove_all_filters('get_terms');
			    remove_all_filters('get_term');
			}
		}
		$this->register_routes();	
	}

	public function register_routes() {		
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
		add_filter( 'posts_join', array($this,'wooconnector_joins_posts'), 10, 2 );
		add_filter( 'posts_orderby', array($this,'wooconnector_orderby_posts'), 10, 2);	
	}	

	public function wooconnector_is_rest_get_product(){
		$url = $_SERVER['REQUEST_URI'];
		$sUrl = substr($url,strpos($url,'wp-json'));
		$oUrl = $sUrl;
		if(strpos($sUrl,'?') != false){
			$oUrl = substr($sUrl,0,strpos($sUrl,'?'));
		}
		$tUrl = trim($oUrl,'/');
		$aUrl = explode('/',$tUrl);
		if(isset($aUrl[0]) &&  $aUrl[0] != 'wp-json' || isset($aUrl[1]) &&  $aUrl[1] != 'wooconnector' || isset($aUrl[2]) &&  $aUrl[2] != 'product'){
			return false;
		}
		$lUrl = array_pop($aUrl); 
		if($lUrl == 'getproduct' || $lUrl == 'getproductbycategory' || $lUrl == 'getproductbyattribute'){
			return true;
		}else{
			return false;
		}
	}

	public function wooconnector_joins_posts($join, $query){
		$checkpopu = (isset($_REQUEST['popularity_sort'])) ? sanitize_text_field(@$_REQUEST['popularity_sort']) : false;
		$checkprice = (isset($_REQUEST['price_sort'])) ? sanitize_text_field(@$_REQUEST['price_sort']) : false;
		if ($this->wooconnector_is_rest_get_product() && !empty($checkpopu) && $checkpopu == 1){
			global $wpdb;
			$join .= " LEFT JOIN " . $wpdb->prefix . "mobiconnector_views AS mobiv ON mobiv.mc_id = " . $wpdb->prefix . "posts.ID ";
		}elseif($this->wooconnector_is_rest_get_product() && !empty($checkprice) && $checkprice == 1){
			global $wpdb;
			$join .= " INNER JOIN (
				SELECT post_id, min( meta_value+0 ) as price
				FROM $wpdb->postmeta WHERE meta_key='_price' GROUP BY post_id ) as price_query ON $wpdb->posts.ID = price_query.post_id ";
		}
		return $join;
	}

	public function wooconnector_orderby_posts( $orderby, $query ) {
		$checkpopu = (isset($_REQUEST['popularity_sort'])) ? sanitize_text_field(@$_REQUEST['popularity_sort']) : false;
		$checkprice = (isset($_REQUEST['price_sort'])) ? sanitize_text_field(@$_REQUEST['price_sort']) : false;
		if ( $this->wooconnector_is_rest_get_product()&& !empty($checkpopu) && $checkpopu == 1) {
			global $wpdb;
			$order = sanitize_text_field(@$_REQUEST['post_order_page']);
			$orderby =   'mobiv.mc_count'  . ' ' . $order;
		}elseif($this->wooconnector_is_rest_get_product() && !empty($checkprice) && $checkprice == 1){
			global $wpdb;
			$order = sanitize_text_field(@$_REQUEST['post_order_page']);
			$orderby =  "price_query.price  $order, $wpdb->posts.ID $order";
		}
		return $orderby;
	}
	
	public function register_api_hooks() {

		register_rest_route( $this->rest_url, '/getproduct', array(
				'methods'         => 'GET',
				'callback'        => array( $this, 'getproduct' ),	
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'post_per_page' => array(
						'default' => 10,
						'sanitize_callback' => 'absint',
					),
					'post_num_page' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					),
					'post_order_by' => array(
						'default' => 'date',
						'validate_callback' => array($this,'validate_post_order_by'),
					),
					'post_order_page' => array(
						'default' => 'DESC',
						'validate_callback' => array($this,'validate_post_order_page'),
					),
					'post_category' => array(
						'sanitize_callback' => 'absint',
					),
					'product_id' => array(
						'sanitize_callback' => 'absint',
					),
					'search' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'include' => array(
					),
					'exclude' => array(
					),
					'parent' => array(
						'sanitize_callback' => 'absint',
					),
					'slug' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'status' => array(
						'default' => 'publish',
						'validate_callback' => array($this,'validate_post_status'),
					),
					'sku' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'in_stock' => array(
						'sanitize_callback' => 'absint',
					),
					'on_sale' => array(
						'sanitize_callback' => 'absint',
					),
					'shipping_class' => array(
						'sanitize_callback' => 'absint',
					),
					'tax_class' => array(
						'validate_callback' => array($this,'validate_post_tax_class'),
					),
					'min_price' => array(							
						'sanitize_callback' => 'absint',
					),
					'max_price' => array(							
						'sanitize_callback' => 'absint',
					),
					'brand' => array(
						'sanitize_callback' => 'absint',
					),
					'price_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'arrival' => array(
						'sanitize_callback' => 'absint',
					),
					'array_cat' => array(						
					),
					'name_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'popularity_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'rating_sort' => array(
						'sanitize_callback' => 'absint',
					)
				),					
			) 
		);

		register_rest_route( $this->rest_url, '/getproduct/(?P<id>\d+)', array(
				'methods'         => 'GET',
				'callback'        => array( $this, 'getproducturlid' ),	
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(					
					'post_per_page' => array(
						'default' => 4,
						'sanitize_callback' => 'absint',
					),
					'post_num_page' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					)
				)
			)
		);

		register_rest_route( $this->rest_url, '/getcategories', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getcategories' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'cat_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'cat_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
						'term_id' => array(
							'sanitize_callback' => 'absint',
						),
						'parent' => array(							
							'sanitize_callback' => 'absint',
						),
						'cat_order_page' => array(
							'default' => 'ASC',
							'validate_callback' => array($this,'validate_cat_order_page'),
						),
						'cat_order_by' => array(
							'default' => 'name',
							'validate_callback' => array($this,'validate_cat_order_by'),
						),
						'include' => array(
						),
						'exclude' => array(
						),
						'search' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'slug' => array(
							'sanitize_callback' => 'esc_sql'
						),
						'product' => array(
							'sanitize_callback' => 'absint',
						),
						'menu' => array(
							'sanitize_callback' => 'absint'
						)
					),					
			) 
		);

		register_rest_route( $this->rest_url, '/getcategories/(?P<id>\d+)', array(
				'methods'         => 'GET',
				'callback'        => array( $this, 'getcategoryurlid' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),	
				'args'            => array(
				
				),					
			) 
		);

		register_rest_route( $this->rest_url, '/getbrands', array(
			'methods'         => 'GET',
			'callback'        => array( $this, 'getbrands' ),	
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args'            => array(
					'cat_per_page' => array(
						'default' => 10,
						'sanitize_callback' => 'absint',
					),
					'cat_num_page' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					),
					'term_id' => array(
						'sanitize_callback' => 'absint',
					),
					'parent' => array(
						'sanitize_callback' => 'absint',
					),
					'cat_order_page' => array(
						'default' => 'ASC',
						'validate_callback' => array($this,'validate_cat_order_page'),
					),
					'cat_order_by' => array(
						'default' => 'name',
						'validate_callback' => array($this,'validate_cat_order_by'),
					),
					'include' => array(
					),
					'exclude' => array(
					),
					'search' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'slug' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'product' => array(
						'sanitize_callback' => 'absint',
					)
				),					
			) 
		);

		register_rest_route( $this->rest_url, '/getbrands/(?P<id>\d+)', array(
				'methods'         => 'GET',
				'callback'        => array( $this, 'getbrandurlid' ),	
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
				
				),					
			) 
		);

		register_rest_route( $this->rest_url, '/getproductbycategory', array(
				'methods'         => 'GET',
				'callback'        => array( $this, 'getproductbycategory' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(
					'post_per_page' => array(
						'default' => 10,
						'sanitize_callback' => 'absint',
					),
					'post_num_page' => array(
						'default' => 1,
						'sanitize_callback' => 'absint',
					),					
					'post_order_page' => array(
						'default' => 'DESC',
						'validate_callback' => array($this,'validate_post_order_page'),
					),
					'post_order_by' => array(
						'default' => 'date',
						'validate_callback' => array($this,'validate_post_order_by'),
					),
					'post_category' => array(
						'sanitize_callback' => 'absint',
					),
					'cat_per_page' => array(
						'default' => 10,
						'sanitize_callback' => 'absint',
					),
					'search' => array(
						'sanitize_callback' => 'esc_sql',
					),
					'price_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'arrival' => array(
						'sanitize_callback' => 'absint',
					),
					'array_cat' => array(						
					),
					'name_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'popularity_sort' => array(
						'sanitize_callback' => 'absint',
					),
					'rating_sort' => array(
						'sanitize_callback' => 'absint',
					)
				),
			) 
		);
		
		register_rest_route( $this->rest_url, '/getproductbyattribute', array(
					'methods'         => 'GET',
					'callback'        => array( $this,'getproductbyattribute' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'attribute' => array(													
						),
						'post_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),						
						'min_price' => array(							
							'sanitize_callback' => 'absint',
						),
						'max_price' => array(							
							'sanitize_callback' => 'absint',
						),
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						),
						'post_order_by' => array(
							'default' => 'date',
							'validate_callback' => array($this,'validate_post_order_by'),
						),
						'post_category' => array(
							'sanitize_callback' => 'absint',
						),
						'in_stock' => array(
							'sanitize_callback' => 'absint',
						),
						'on_sale' => array(
							'sanitize_callback' => 'absint',
						),
						'search' => array(
							'sanitize_callback' => 'esc_sql',
						),
						'brand' => array(
							'sanitize_callback' => 'esc_sql',
						),
						'price_sort' => array(
							'sanitize_callback' => 'absint',
						),
						'arrival' => array(
							'sanitize_callback' => 'absint',
						),
						'array_cat' => array(						
						),
						'name_sort' => array(
							'sanitize_callback' => 'absint',
						),
						'popularity_sort' => array(
							'sanitize_callback' => 'absint',
						),
						'rating_sort' => array(
							'sanitize_callback' => 'absint',
						)						
					),					
			) 
		);

		register_rest_route( $this->rest_url, '/getbestviews', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getbestviews' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),	
					'args'            => array(
						'post_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),					
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						)
					),					
			) 
		);
		register_rest_route( $this->rest_url, '/getbestsales', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getbestsales' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'post_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),						
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						),
					),					
			) 
		);
		register_rest_route($this->rest_url,'/getbestrating', array(
					'methods' 	=> 'GET',
					'callback'	=> array($this,'getbestrating'),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'		=> array(
						'post_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),					
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						),
					)
		));
		register_rest_route( $this->rest_url, '/getproductonsale', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getproductonsale' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),	
					'args'            => array(
						'post_per_page' => array(
							'default' => 10,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),						
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						)					
					),					
			) 
		);
		register_rest_route( $this->rest_url, '/getattribute', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getattribute' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),	
					'args'            => array(			
						'post_category' => array(
							'sanitize_callback' => 'absint',
						)	
					),					
			) 
		);
				
		register_rest_route( $this->rest_url, '/postreviews', array(
				'methods' => 'POST',
				'callback' => array( $this, 'postreviews' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args' => array(
					'product' => array(
						'required' => true,
						'sanitize_callback' => 'absint'
					),
					'comment' => array(
						'required' => true,
						'sanitize_callback' => 'esc_sql'
					),
					'ratestar' => array(
						'required' => true,
						'sanitize_callback' => 'absint'
					),
					'namecustomer' => array(
						'sanitize_callback' => 'esc_sql'
					),
					'emailcustomer' => array(
						'sanitize_callback' => 'esc_sql'
					)
				),
			) 
		);
		register_rest_route( $this->rest_url, '/getcurrency', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getcurrency' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						
					),					
			) 
		);
		register_rest_route( $this->rest_url, '/getdealofday', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getdealofday' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'post_per_page' => array(
							'default' => 2,
							'sanitize_callback' => 'absint',
						),
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),							
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						)						
					),					
			) 
		);	
		register_rest_route( $this->rest_url, '/getnewcomment', array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'getnewcomment' ),	
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'            => array(
						'product_id'    => array(
							'sanitize_callback' => 'absint'
						),
						'post_per_page' => array(
							'default' => 3,
							'sanitize_callback' => 'absint',
						),						
						'post_num_page' => array(
							'default' => 1,
							'sanitize_callback' => 'absint',
						),
						'post_order_page' => array(
							'default' => 'DESC',
							'validate_callback' => array($this,'validate_post_order_page'),
						)					
					),					
			) 
		);
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if(is_plugin_active('mobiconnector/mobiconnector.php')){
			$usekey = get_option('mobiconnector_settings-use-security-key');
			if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
				return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}
		return true;
	}

	/** 
	* Get product
	*/	
	public function getproduct($request){
		$parameters = $request->get_params();
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$post_order_by = $parameters['post_order_by'];
		$post_category = isset($parameters['post_category']) ? $parameters['post_category'] : false;
		$brand = isset($parameters['brand']) ? $parameters['brand'] : false;
		$product_id = isset($parameters['product_id']) ? $parameters['product_id'] : false;
		$search = isset($parameters['search']) ? $parameters['search'] : false;
		$include = isset($parameters['include']) ? $parameters['include'] : false;
		$exclude = isset($parameters['exclude']) ? $parameters['exclude'] : false;
		$parent = isset($parameters['parent']) ? $parameters['parent'] : false;
		$slug = isset($parameters['slug']) ? $parameters['slug'] : false;
		$status = isset($parameters['status']) ? $parameters['status'] : 'publish';
		$sku = isset($parameters['sku']) ? $parameters['sku'] : false;
		$in_stock = isset($parameters['in_stock']) ? $parameters['in_stock'] : false;
		$on_sale = isset($parameters['on_sale']) ? $parameters['on_sale'] : false;
		$min_price = isset($parameters['min_price']) ? $parameters['min_price'] : false;
		$max_price = isset($parameters['max_price']) ? $parameters['max_price'] : false;
		$shipping_class =  isset($parameters['shipping_class']) ? $parameters['shipping_class'] : false;
		$tax_class =  isset($parameters['tax_class']) ? $parameters['tax_class'] : false;
		$arrival = isset($parameters['arrival']) ? $parameters['arrival'] : false;
		$array_cat = isset($parameters['array_cat']) ? $parameters['array_cat'] : false;
		$name_sort = isset($parameters['name_sort']) ? $parameters['name_sort'] : false;
		$popularity_sort = isset($parameters['popularity_sort']) ? $parameters['popularity_sort'] : false;
		$rating_sort = isset($parameters['rating_sort']) ? $parameters['rating_sort'] : false;
		$price_sort = isset($parameters['price_sort']) ? $parameters['price_sort'] : false;
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'            => $post_num_page,					
			'category'         => '',
			'category_name'    => '',
			'orderby'          => $post_order_by,
			'order'            => $post_order_page,
			'meta_key'         => '',
			'meta_value'       => '',
			'meta_query'       => array(),
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'author'	       => '',
			'author_name'	   => '',
			'tax_query'        => array(),
			'post_status'      => $status,
			'suppress_filters' => true 
		);	
		if(!empty($post_category)){
			$categorie = get_term($post_category,'product_cat');
			if(empty($categorie)){
				return array();
			}					
			$args['product_cat'] = $categorie->slug;
		}	
		if(!empty($brand)){
			array_push($args['tax_query'],array(
				'taxonomy' => 'wooconnector_product_brand',
				'field' => 'term_id',
				'terms' =>  $brand
			));
		}
		if(!empty($shipping_class)){
			$terms = get_term_by('name',$shipping_class,'product_shipping_class');
			array_push($args['tax_query'],array(
				'taxonomy' => 'product_shipping_class',
				'field' => 'term_id',
				'terms' =>  $terms->term_id				
			));
		}
		$countmeta = count($args['tax_query']);	
		if($countmeta > 1){
			$args['tax_query']['relation'] = 'AND';			
		}
		if(!empty($rating_sort)){
			$args['meta_key'] = '_wc_average_rating';
			$args['orderby'] = 'meta_value';
		}
		if(!empty($min_price) && $min_price != null){
			array_push($args['meta_query'],array(
					'key' => '_price',
					'value' => $min_price,
					'type'    => 'numeric',
					'compare' => '>='
				)
			);
		}
		if(!empty($max_price) && $max_price != null){
			array_push($args['meta_query'],array(
					'key' => '_price',
					'value' => $max_price,
					'type'    => 'numeric',
					'compare' => '<='
				)
			);
		}
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}
		if(!empty($sku)){
			array_push($args['meta_query'],array(
					'key' => '_sku',
					'value' => $sku,
					'compare' => '='
				)
			);
		}
		if(!empty($in_stock)){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				)
			);
		}
		if(!empty($on_sale)){
			array_push($args['meta_query'],array(
					'key' => '_sale_price',
					'value' => 0,
					'compare' => '>'
				)
			);
		}
		if(!empty($tax_class)){
			array_push($args['meta_query'],array(
					'key' => '_tax_class',
					'value' => $tax_class,
					'compare' => '='
				)
			);
		}
		$countmeta = count($args['meta_query']);	
		if($countmeta > 1){
			$args['meta_query']['relation'] = 'AND';			
		}
		if(!empty($include)){
			$in = explode(",",$include);
			$args['include'] = $in;
		}
		if(!empty($exclude)){
			$ex = explode(",",$exclude);
			$args['exclude'] = $ex;
		}	
		if(!empty($slug)){
			$args['slug'] = $slug;
		}	
		if(!empty($parent) || $parent === 0){
			$args['post_parent'] = $parent;
		}	
		if(!empty($array_cat)){
			$ids = $this->getproductbyarraycat($request);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$args['post__in'] = array_merge($args['post__in'],$ids);
			}else{
				$args['post__in'] = $ids;
			}
		}			
		if(!empty($arrival)){
			$ids = $this->getproductarrival($request);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		if(!empty($search) && $search != null){
			$ids = $this->WooConnectorSearchOnProduct($search,$status);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		if(!empty($search) || !empty($arrival) || !empty($array_cat)){
			if(empty($args['post__in'])){
				return array();
			}
		}
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if(!empty($listexcatalog)){			
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		$query = new WP_Query($args);			
		$products = $query->posts;			
		if(!empty($product_id)){
			$product = get_post($product_id);
			if(!empty($product) && is_object($product) && ($product->post_type=='product' || $product->post_type=='product_variation')){
				$result = array();
				if($hideoutofstock == 'yes'){
					$wcp = wc_get_product($product_id);
					$stock = $wcp->is_in_stock();
					if(!$stock){
						return array();
					}else{
						$result = $this->get_data($product,$request);
						if(empty($result)){
							return array();
						}
					}
				}else{
					$result = $this->get_data($product,$request);
				}		
				return $result;
			}else{
				return array();
			}
		}
		if(!empty($name_sort)){
			$products = $this->sortproductbyname($args);
		}
		if(!empty($products)){
			foreach($products as $product){
				$list[] = $this->get_data($product,$request);
			}
			if(empty($list)){
				return array();
			}
			return $list;
		}else{
			return array();
		}
	}

	/**
	 * Check languages enable
	 */
	private static function wooconnector_is_languages_enable($lang){
		global $wpdb;
		if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){ 		
			$enable = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'icl_locale_map WHERE code = "'.$lang.'"');
			if($enable > 0){
				return true;
			}
			return false;
		}elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
			$listenable = get_option('qtranslate_enabled_languages');
			if(in_array($lang,$listenable)){
				return true;
			}
			return false;
		}else{
			return true;
		}		
	}

	private static function checkproductwithWPML(){
		global $wpdb;
		$current_lang = '';
		if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
			$current_lang = (isset($_GET['mobile_lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : self::$default_lang;
		}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
			$current_lang = (isset($_GET['lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : self::$default_lang;
		}else{
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$current_lang = self::get_default_wpml_languages();
			}else{
				$current_lang = 'en';
			}
		}		
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$prefix."posts.* FROM ".$prefix."posts INNER JOIN ".$prefix."icl_translations AS t ON ".$prefix."posts.ID = t.element_id AND t.element_type = CONCAT('post_', ".$prefix."posts.post_type) WHERE 1=1 AND ".$prefix."posts.post_type = 'product' AND ".$prefix."posts.post_status = 'publish' AND  t.language_code = '".$current_lang."'";
		$listposts = $wpdb->get_results($sql,ARRAY_A);
		$listId = array();
		if(!empty($listposts)){
			foreach($listposts as $post){
				$listId[] = $post['ID'];
			}
		}		
		self::$id_with_product = $listId;
		return $listId;
	}

	private static function checkproductvariationwithWPML(){
		global $wpdb;
		$current_lang = '';
		if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
			$current_lang = (isset($_GET['mobile_lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : self::$default_lang;
		}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
			$current_lang = (isset($_GET['lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : self::$default_lang;
		}else{
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$current_lang = self::get_default_wpml_languages();
			}else{
				$current_lang = 'en';
			}
		}
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$prefix."posts.* FROM ".$prefix."posts INNER JOIN ".$prefix."icl_translations AS t ON ".$prefix."posts.ID = t.element_id AND t.element_type = CONCAT('post_', ".$prefix."posts.post_type) WHERE 1=1 AND ".$prefix."posts.post_type = 'product_variation' AND ".$prefix."posts.post_status = 'publish' AND  t.language_code = '".$current_lang."'";
		$listposts = $wpdb->get_results($sql,ARRAY_A);
		$listId = array();
		if(!empty($listposts)){
			foreach($listposts as $post){
				$listId[] = $post['ID'];
			}
		}	
		self::$id_with_product_variation = $listId;
		return $listId;
	}

	private static function checkcategorieswithWPML(){
		global $wpdb;
		$current_lang = '';
		if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
			$current_lang = (isset($_GET['mobile_lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : self::$default_lang;
		}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
			$current_lang = (isset($_GET['lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : self::$default_lang;
		}else{
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$current_lang = self::get_default_wpml_languages();
			}else{
				$current_lang = 'en';
			}
		}
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$prefix."term_taxonomy.* FROM ".$prefix."term_taxonomy INNER JOIN ".$prefix."icl_translations AS t ON ".$prefix."term_taxonomy.term_id = t.element_id AND t.element_type = CONCAT('tax_', ".$prefix."term_taxonomy.taxonomy) WHERE 1=1 AND ".$prefix."term_taxonomy.taxonomy = 'product_cat' AND  t.language_code = '".$current_lang."'";
		$listterms = $wpdb->get_results($sql,ARRAY_A);
		$listId = array();
		if(!empty($listterms)){
			foreach($listterms as $term){
				$listId[] = $term['term_id'];
			}
		}		
		self::$id_with_categories = $listId;
		return $listId;
	}

	private static function checkbrandswithWPML(){
		global $wpdb;
		$current_lang = '';
		if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
			$current_lang = (isset($_GET['mobile_lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : self::$default_lang;
		}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
			$current_lang = (isset($_GET['lang']) && self::wooconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : self::$default_lang;
		}else{
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$current_lang = self::get_default_wpml_languages();
			}else{
				$current_lang = 'en';
			}
		}
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$prefix."term_taxonomy.* FROM ".$prefix."term_taxonomy INNER JOIN ".$prefix."icl_translations AS t ON ".$prefix."term_taxonomy.term_id = t.element_id AND t.element_type = CONCAT('tax_', ".$prefix."term_taxonomy.taxonomy) WHERE 1=1 AND ".$prefix."term_taxonomy.taxonomy = 'wooconnector_product_brand' AND  t.language_code = '".$current_lang."'";
		$listterms = $wpdb->get_results($sql,ARRAY_A);
		$listId = array();
		if(!empty($listterms)){
			foreach($listterms as $term){
				$listId[] = $term['term_id'];
			}
		}		
		self::$id_with_brands = $listId;
		return $listId;
	}

	private function get_qtranslate_default_language(){
	   $default = get_option('qtranslate_default_language');
	   if(is_plugin_active('qtranslate-x/qtranslate.php') && !empty($default)){
		   return $default;
	   }else{
		   return 'en';
	   }
   }

	private static function get_default_wpml_languages(){
		$settings = get_option('icl_sitepress_settings');
		if(!empty($settings)){
			return $settings['default_language'];
		}else{
			return 'en';
		}
	}

	public function getproduct_exclude_from_catalog(){
		$args = array(
			'posts_per_page'   => -1,
			'paged'            => 0,					
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'author'	       => '',
			'author_name'	   => '',
			'tax_query'        => array(
				array(
					'taxonomy' => 'product_visibility',
					'field' => 'slug',
					'terms' =>  'exclude-from-catalog'
				)
			),
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);	
		$posts = get_posts($args);
		$ids = array();
		foreach($posts as $post){
			$ids[] = $post->ID;
		}
		return $ids;
	}

	public function getproduct_excule_from_search(){
		$args = array(
			'posts_per_page'   => -1,
			'paged'            => 0,					
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'author'	       => '',
			'author_name'	   => '',
			'tax_query'        => array(
				array(
					'taxonomy' => 'product_visibility',
					'field' => 'slug',
					'terms' =>  'exclude-from-search'
				)
			),
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);	
		$posts = get_posts($args);
		$ids = array();
		foreach($posts as $post){
			$ids[] = $post->ID;
		}
		return $ids;
	}

	public function sort_u_cmp($a,$b){
		$params1 = apply_filters('mobiconnector_languages',$a["post_title"]);
		$params2 = apply_filters('mobiconnector_languages',$b["post_title"]);
		return strnatcasecmp($params1, $params2);
	}

	public function sort_u_r_cmp($a,$b){
		$params1 = apply_filters('mobiconnector_languages',$a["post_title"]);
		$params2 = apply_filters('mobiconnector_languages',$b["post_title"]);
		return strnatcasecmp($params2, $params1);
	}	

	public function sortproductbyname($args){
		$post_per_page = $args['posts_per_page'];
		$post_num_page = $args['paged'];
		$post_order_page = $args['order'];
		$args['posts_per_page'] = -1;
		$posts = get_posts($args);
		foreach($posts as $product){
			$listtrans[] = array(
				'ID' => $product->ID,
				'post_author' => $product->post_author,
				'post_date' => $product->post_date,
				'post_date_gmt' => $product->post_date_gmt,
				'post_content' => $product->post_content,
				'post_title' => ucfirst(apply_filters('mobiconnector_languages',$product->post_title)),
				'post_excerpt' => $product->post_excerpt,
				'post_status' => $product->post_status,
				'comment_status' => $product->comment_status,
				'ping_status' => $product->ping_status,
				'post_name' => $product->post_name,
				'to_ping' => $product->to_ping,
				'pinged' => $product->pinged,
				'post_modified' => $product->post_modified,
				'post_modified_gmt' => $product->post_modified_gmt,
				'post_content_filtered' => $product->post_content_filtered,
				'post_parent' => $product->post_parent,
				'guid' => $product->guid,
				'menu_order' => $product->menu_order,
				'post_type' => $product->post_type,
				'post_mime_type' => $product->post_mime_type,
				'comment_count' => $product->comment_count,
			);
		}
		if($post_order_page == 'ASC'){
			usort($listtrans,array($this,'sort_u_cmp'));
		}elseif($post_order_page == 'DESC'){
			usort($listtrans,array($this,'sort_u_r_cmp'));
		}
		$start = ($post_num_page - 1) * $post_per_page;
		$count = $post_per_page * $post_num_page;
		if($count > count($listtrans)){
			$count = count($listtrans);
		}
		$outlist = array();
		for($i = $start; $i < $count; $i++ ){
			$outlist[] = (object)$listtrans[$i];
		}		
		return $outlist;
	}

	public function getproductarrival($request){
		global $wpdb;
		$sql = "SELECT * 
		FROM ".$wpdb->posts."
		WHERE post_modified_gmt >= UTC_TIMESTAMP() - INTERVAL 1 DAY AND post_type = 'product' AND post_status = 'publish'";
		$results = $wpdb->get_results($sql,ARRAY_A);
		$list_id = array();
		if(!empty($results)){
			foreach($results as $product){
				$list_id[] = (int)$product['ID'];
			}
		}
		return $list_id;
	}

	public function getproductbyarraycat($request){
		global $wpdb;
		$params = $request->get_params();
		$array_cats = $params['array_cat'];
		$array_cats = json_decode($array_cats);
		$listcat = '';
		foreach($array_cats as $cat_id){
			$listcat .= "'".$cat_id."',";
		}
		$listcat = trim($listcat,',');
		$sql = "SELECT * FROM ".$wpdb->posts." as p INNER JOIN ".$wpdb->prefix."term_relationships as tr ON p.ID = tr.object_id INNER JOIN ".$wpdb->prefix."term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.term_id IN(".$listcat.") AND p.post_type = 'product' AND p.post_status = 'publish' AND tt.taxonomy = 'product_cat'"; 
		$results = $wpdb->get_results($sql,ARRAY_A);
		$list_id = array();
		if(!empty($results)){
			foreach($results as $product){
				$list_id[] = (int)$product['ID'];
			}
		}
		return $list_id;
	}

	public function getproducturlid($request){
		$parameters = $request->get_params();
		$id = isset($parameters['id']) ? $parameters['id'] : false;
		if(!empty($id)){
			$product = get_post($id);
			if(!empty($product) && is_object($product) && ($product->post_type=='product' || $product->post_type=='product_variation')){	
				$result = $this->get_data($product,$request);				
				return $result;
			}else{
				return array();
			}
		}else{
			return array();
		}
	}	

	private function WooConnectorSearchOnProduct($search,$status){
		global $wpdb;
		$searchactives = get_option('wooconnector_settings-search');
		$searchactives = unserialize($searchactives);
		$checkcate = false;
		$checktag = false;
		$checkdes = false;
		$listexsearch = $this->getproduct_excule_from_search();
		if(!empty($searchactives)){
			foreach($searchactives as $sea => $value){
				if($sea == 'category' && $value == 1){
					$checkcate = true;
				}elseif($sea == 'category' && $value != 1) {
					$checkcate = false;
				}
				if($sea == 'tag' && $value == 1){
					$checktag = true;
				}elseif($sea == 'tag' && $value != 1){
					$checktag = false;
				}
				if($sea == 'description' && $value == 1){
					$checkdes = true;
				}elseif($sea == 'description' && $value != 1){
					$checkdes = false;
				}
			}
		}
		$listterms = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."terms WHERE name LIKE '%".esc_sql($search)."%'",ARRAY_A);
		if(!empty($listterms) && (!empty($checkcate) || !empty($checktag)) ){
			foreach($listterms as $term => $values){
				$terms[] = $values['term_id'];
			}
			$args_seacrch = array(
				'posts_per_page'   => -1,
				'post_type'        => 'product',
				'post_status'      => $status,
				'tax_query' => array(
				)
			);
			if($checkcate){
				array_push($args_seacrch['tax_query'],array(
					'taxonomy' => 'product_cat',
					'field' => 'id',
					'terms' =>  $terms,
					'operator' => 'IN'
				));
			}
			if($checktag){
				array_push($args_seacrch['tax_query'],array(
					'taxonomy' => 'product_tag',
					'field' => 'id',
					'terms' =>  $terms,
					'operator' => 'IN'
				));
			}
			if(count($args_seacrch['tax_query']) > 1){
				$args_seacrch['tax_query']['relation'] = 'OR';
			}
			$id_search = array();
			$products_search = get_posts($args_seacrch);
			foreach($products_search as $product){
				if(!in_array($product->ID,$listexsearch)){
					$id_search[] = $product->ID;
				}
			}
		}
		$status = esc_sql($status);
		$no_unicode_search = remove_accents($search);
		$lowcase_search = mb_strtolower($search);
		if($checkdes){
			if(is_plugin_active('qtranslate-x/qtranslate.php')){
				$langcodedes =  'en';
				if(isset($_REQUEST['mobile_lang'])){
					$langcodedes = $_REQUEST['mobile_lang'];
				}elseif(isset($_REQUEST['lang'])){
					$langcodedes = $_REQUEST['lang'];
				}
				$regexdes = '/(\[\:'.$langcodedes.'\].*('.esc_sql($lowcase_search).')+)/';
				$regexdes_unicode = '/(\[\:'.$langcodedes.'\].*('.esc_sql(strtolower($no_unicode_search)).')+)/';
				$search_querydes = "
				SELECT ID,post_content FROM ".$wpdb->posts." 
				WHERE post_type = 'product' AND post_status = '$status' 
				AND (LCASE(post_content) LIKE '%".esc_sql($lowcase_search)."%' OR LCASE(post_content) LIKE '%".esc_sql(strtolower($no_unicode_search))."%')";
				$results_first_des = $wpdb->get_results($search_querydes, ARRAY_A);
				if(!empty($results_first_des)){
					foreach($results_first_des as $first_des => $value_first_des){
						if(strpos($value_first_des['post_content'],"[:en]") !== false){
							$value_first_des_unicode = strtolower(remove_accents($value_first_des['post_content']));					
							if(preg_match($regexdes_unicode,$value_first_des_unicode) != false || preg_match($regexdes,mb_strtolower($value_first_des['post_content'])) != false){
								$resultsdes[] = array('ID' => $value_first_des['ID']);
							}
						}else{
							$resultsdes[] = array('ID' => $value_first_des['ID']);
						}
					}
				}			
			}else{
				$search_querydes = "
				SELECT ID FROM ".$wpdb->posts." 
				WHERE post_type = 'product' AND post_status = '$status' 
				AND (LCASE(post_content) LIKE '%".esc_sql($lowcase_search)."%' OR LCASE(post_content) LIKE '%".esc_sql(strtolower($no_unicode_search))."%')";
				$resultsdes = $wpdb->get_results($search_querydes, ARRAY_A);
			}			
		}
		if(!empty($resultsdes)){
			$seacrh_id_des = array();
			foreach($resultsdes as $product){
				if(!in_array($product['ID'],$listexsearch)){
					$seacrh_id_des[] = (int)$product['ID'];
				}
			}
		}
		$results = array();		
		$search_slug = sanitize_title($search);
		if(is_plugin_active('qtranslate-x/qtranslate.php')){
			$langcode =  'en';
			if(isset($_REQUEST['mobile_lang'])){
				$langcode = $_REQUEST['mobile_lang'];
			}elseif(isset($_REQUEST['lang'])){
				$langcode = $_REQUEST['lang'];
			}
			$regex = '/(\[\:'.$langcode.'\].*('.esc_sql($lowcase_search).')+)/';
			$regex_unicode = '/(\[\:'.$langcode.'\].*('.esc_sql(strtolower($no_unicode_search)).')+)/';
			$sql_first_by_like = "
				SELECT ID,post_title FROM ".$wpdb->posts." 
				WHERE post_type = 'product' AND post_status = '".$status."' 
				AND ((LCASE(post_title) LIKE '%".esc_sql($lowcase_search)."%' OR LCASE(post_title) LIKE '%".esc_sql(strtolower($no_unicode_search))."%')
				OR post_name LIKE '%".$search_slug."%')
			";
			$result_first_by_like = $wpdb->get_results($sql_first_by_like,ARRAY_A);			
			if(!empty($result_first_by_like)){
				foreach($result_first_by_like as $first_by_like => $value_first){
					if(strpos($value_first['post_title'],"[:en]") !== false){
						$value_first_unicode = strtolower(remove_accents($value_first['post_title']));
						if(preg_match($regex_unicode,$value_first_unicode) || preg_match($regex,mb_strtolower($value_first['post_title']))){
							$results[] = array('ID' => $value_first['ID']);
						}
					}else{
						$results[] = array('ID' => $value_first['ID']);
					}
				}
			}	
		}else{
			$search_query = "
				SELECT ID FROM ".$wpdb->posts." 
				WHERE post_type = 'product' AND post_status = '$status' 
				AND ((LCASE(post_title) LIKE '%".esc_sql($lowcase_search)."%' OR LCASE(post_title) LIKE '%".esc_sql(strtolower($no_unicode_search))."%') 
				OR (post_name LIKE '%".$search_slug."%'))";
			$results = $wpdb->get_results($search_query, ARRAY_A);
		}
		if(!empty($results)){
			$seacrh_id = array();
			foreach($results as $product){
				if(!in_array($product['ID'],$listexsearch)){
					$seacrh_id[] = (int)$product['ID'];
				}
			}
		}
		$ids = array();
		if(!empty($id_search)){
			$ids = array_merge($ids,$id_search);
		}
		if(!empty($seacrh_id)){
			$ids = array_merge($ids,$seacrh_id);
		}
		if(!empty($seacrh_id_des)){
			$ids = array_merge($ids,$seacrh_id_des);
		}
		if(empty($id_search) && empty($seacrh_id) && empty($seacrh_id_des)){
			$ids = array();
		}
		$ids = array_unique($ids);		
		return $ids;
	}

	/*
	*Get categories
	*/	
	public function getcategories($request){
		$parameters = $request->get_params();
		$cat_per_page = $parameters['cat_per_page'];
		$num_page = $parameters['cat_num_page'];
		$cat_num_page = ($num_page - 1)*$cat_per_page;
		$termid = isset($parameters['term_id']) ? $parameters['term_id'] : false;
		$id = isset($parameters['id']) ? $parameters['id'] : false;
		$parent = isset($parameters['parent']) ? $parameters['parent'] : false;
		$cat_order_page = $parameters['cat_order_page'];
		$cat_order_by = $parameters['cat_order_by'];
		$include = isset($parameters['include']) ? $parameters['include'] : false;
		$exclude = isset($parameters['exclude']) ? $parameters['exclude'] : false;
		$search = isset($parameters['search']) ? $parameters['search'] : false;
		$slug = isset($parameters['slug']) ? $parameters['slug'] : false;
		$product = isset($parameters['product']) ? $parameters['product'] : false;
		$device = isset($parameters['device']) ? $parameters['device'] : false;
		$args = array(
			'taxonomy' => 'product_cat',
			'orderby' => 'id',
			'hide_empty'=> false,
			'number' => $cat_per_page,
			'offset' => $cat_num_page,
			'orderby' => $cat_order_by,
			'order'  => $cat_order_page,
		);
		if(!empty($termid)){
			$term = get_term($termid,'product_cat');
			if(!empty($term)){
				$out = $this->getTheCategories($term,$request);
				return $out;
			}else{
				return array();
			}
		}
		if(!empty($parent) || $parent === 0){
			$args['parent'] = $parent;
		}
		if(!empty($include)){
			$in = explode(",",$include);
			if(!empty($args['include'])){
				$list_term_id = array();
				foreach($in as $term_id){
					if(in_array($term_id,$args['include'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['include'] = $list_term_id;
			}else{
				$args['include'] = $in;
			}
		}
		if(!empty($exclude)){
			$ex = explode(",",$exclude);
			if(!empty($args['exclude'])){
				$list_term_id = array();
				foreach($ex as $term_id){
					if(in_array($term_id,$args['exclude'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['exclude'] = $list_term_id;
			}else{
				$args['exclude'] = $ex;
			}
		}	
		if(!empty($search)){
			$args['search'] = $search;
		}
		if(!empty($slug)){
			$args['slug'] = $slug;
		}
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_categories;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['include'])){
				$list_term_id = array();
				foreach($ids as $term_id){
					if(in_array($term_id,$args['include'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['include'] = $list_term_id;
			}else{
				$args['include'] = $ids;
			}
		}		
		$categories = get_terms($args);
		if(!empty($categories)){
			if(!empty($product)){
				$products = wc_get_product($product);
				$productcats = $products->get_category_ids();
				foreach($categories as $category){
					foreach($productcats as $productcat){
						if($category->term_id == $productcat){
							$result[] = $this->getTheCategories($category,$request);
						}
					}
				}
			}else{
				foreach($categories as $category){
					$result[] = $this->getTheCategories($category,$request);
				}
			}				
			return $result;
		}
		return array();
	}

	public function getcategoryurlid($request){
		$parameters = $request->get_params();		
		$id = isset($parameters['id']) ? $parameters['id'] : false;
		if(!empty($id)){
			$term = get_term($id,'product_cat');
			if(!empty($term)){
				$out = $this->getTheCategories($term,$request);				
				return $out;
			}else{
				return array();
			}
		}else{
			return array();
		}
	}

	/*
	*Get categories
	*/	
	public function getbrands($request){
		$parameters = $request->get_params();		
		$cat_per_page = $parameters['cat_per_page'];
		$num_page = $parameters['cat_num_page'];
		$cat_num_page = ($num_page - 1)*$cat_per_page;
		$termid = isset($parameters['term_id']) ? $parameters['term_id'] : false;
		$id = isset($parameters['id']) ? $parameters['id'] : false;
		$parent = isset($parameters['parent']) ? $parameters['parent'] : false;
		$cat_order_page = $parameters['cat_order_page'];
		$cat_order_by = $parameters['cat_order_by'];
		$include = isset($parameters['include']) ? $parameters['include'] : false;
		$exclude = isset($parameters['exclude']) ? $parameters['exclude'] : false;
		$search = isset($parameters['search']) ? $parameters['search'] : false;
		$slug = isset($parameters['slug']) ? $parameters['slug'] : false;
		$product = isset($parameters['product']) ? $parameters['product'] : false;
		$args = array(
			'taxonomy' => 'wooconnector_product_brand',
			'orderby' => 'id',
			'hide_empty'=> false,
			'number' => $cat_per_page,
			'offset' => $cat_num_page,
			'orderby' => $cat_order_by,
			'order'  => $cat_order_page,
		);
		if(!empty($termid)){
			$term = get_term($termid,'wooconnector_product_cat');
			if(!empty($term)){
				$out = $this->getTheBrands($term);
				return $out;
			}else{
				return array();
			}
		}
		if(!empty($parent) || $parent === 0){
			$args['parent'] = $parent;
		}
		if(!empty($include)){
			$in = explode(",",$include);
			if(!empty($args['include'])){
				$list_term_id = array();
				foreach($in as $term_id){
					if(in_array($term_id,$args['include'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['include'] = $list_term_id;
			}else{
				$args['include'] = $in;
			}
		}
		if(!empty($exclude)){
			$ex = explode(",",$exclude);
			if(!empty($args['exclude'])){
				$list_term_id = array();
				foreach($ex as $term_id){
					if(in_array($term_id,$args['exclude'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['exclude'] = $list_term_id;
			}else{
				$args['exclude'] = $ex;
			}
		}	
		if(!empty($search)){
			$args['search'] = $search;
		}
		if(!empty($slug)){
			$args['slug'] = $slug;
		}
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_categories;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['include'])){
				$list_term_id = array();
				foreach($ids as $term_id){
					if(in_array($term_id,$args['include'])){
						$list_term_id[] = $term_id;
					}
				}
				$args['include'] = $list_term_id;
			}else{
				$args['include'] = $ids;
			}
		}
		$categories = get_categories($args);
		if(!empty($categories)){
			if(!empty($product)){
				$products = wc_get_product($product);
				$productcats = $products->get_category_ids();
				foreach($categories as $category){
					foreach($productcats as $productcat){
						if($category->term_id == $productcat){
							$result[] = $this->getTheBrands($category);
						}
					}
				}
			}else{
				foreach($categories as $category){
					$result[] = $this->getTheBrands($category);
				}
			}
			return $result;
		}
		return array();
	}

	public function getbrandurlid($request){
		$parameters = $request->get_params();
		$id = isset($parameters['id']) ? $parameters['id'] : false;
		if(!empty($id)){
			$term = get_term($id,'wooconnector_product_brand');
			if(!empty($term)){
				$out = $this->getTheBrands($term);
				return $out;
			}else{
				return array();
			}
		}else{
			return array();
		}
	}

	/*
	* Get product by category
	* Param post_per_page post in one page
	* Param post_num_page number page
	* Param post_order_by 
	* Param post_order_page
	* Param post_category id category
	* Param cat_per_page
	*/
	public function getproductbycategory($request){	
		$parameters = $request->get_params();
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$post_order_by = $parameters['post_order_by'];
		$search = isset($parameters['search']) ? $parameters['search'] : false;
		$price_sort = isset($parameters['price_sort']) ? $parameters['price_sort'] : false;
		$arrival = isset($parameters['arrival']) ? $parameters['arrival'] : false;
		$array_cat = isset($parameters['array_cat']) ? $parameters['array_cat'] : false;
		$name_sort = isset($parameters['name_sort']) ? $parameters['name_sort'] : false;
		$popularity_sort = isset($parameters['popularity_sort']) ? $parameters['popularity_sort'] : false;
		$rating_sort = isset($parameters['rating_sort']) ? $parameters['rating_sort'] : false;
		$wp_upload = wp_upload_dir();	
		$list = array();
		if(isset($parameters['post_category'])){
			$categorie = get_term($parameters['post_category'],'product_cat');	
			$args = array(
				'posts_per_page'   => $post_per_page,
				'paged'            => $post_num_page,					
				'category'         => '',
				'category_name'    => '',
				'product_cat'      => $categorie->slug,
				'orderby'          => $post_order_by,
				'order'            => $post_order_page,
				'meta_key'         => '',
				'meta_value'       => '',
				'meta_query'       => array(),
				'post_type'        => 'product',
				'post_mime_type'   => '',
				'author'	       => '',
				'author_name'	   => '',
				'post_status'      => 'publish',
				'suppress_filters' => false 
			);	
			$category['id'] = $categorie->term_id;
			$category['name'] = $categorie->name;
			$thumbnail_id = get_woocommerce_term_meta( $categorie->term_id, 'thumbnail_id', true );					
			$category['images'] =  wp_get_attachment_url( $thumbnail_id );
			if(!empty($rating_sort)){
				$args['meta_key'] = '_wc_average_rating';
				$args['orderby'] = 'meta_value';
			}
			$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
			if($hideoutofstock == 'yes'){
				array_push($args['meta_query'],array(
						'key' => '_stock_status',
						'value' => 'outofstock',
						'compare' => '!='
					)
				);
			}
			if(!empty($array_cat)){
				$ids = $this->getproductbyarraycat($request);
				if(empty($ids)){
					$result =  array(
						'category' => $category,
						'products' => array(),	
					);					
					return $result;	
				}
				if(!empty($args['post__in'])){
					$args['post__in'] = array_merge($args['post__in'],$ids);
				}else{
					$args['post__in'] = $ids;
				}
			}
			if(!empty($arrival)){
				$ids = $this->getproductarrival($request);
				if(empty($ids)){
					$result =  array(
						'category' => $category,
						'products' => array(),	
					);						
					return $result;	
				}
				if(!empty($args['post__in'])){
					$list_post_id = array();
					foreach($ids as $post_id){
						if(in_array($post_id,$args['post__in'])){
							$list_post_id[] = $post_id;
						}
					}
					$args['post__in'] = $list_post_id;
				}else{
					$args['post__in'] = $ids;
				}
			}
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$ids = self::$id_with_product;
				if(empty($ids)){
					return array();
				}
				if(!empty($args['post__in'])){
					$list_post_id = array();
					foreach($ids as $post_id){
						if(in_array($post_id,$args['post__in'])){
							$list_post_id[] = $post_id;
						}
					}
					$args['post__in'] = $list_post_id;
				}else{
					$args['post__in'] = $ids;
				}
			}
			if(!empty($search) && $search != null){
				$ids = $this->WooConnectorSearchOnProduct($search,'publish');
				if(empty($ids)){
					$result =  array(
						'category' => $category,
						'products' => array(),	
					);						
					return $result;	
				}
				if(!empty($args['post__in'])){
					$list_post_id = array();
					foreach($ids as $post_id){
						if(in_array($post_id,$args['post__in'])){
							$list_post_id[] = $post_id;
						}
					}
					$args['post__in'] = $list_post_id;
				}else{
					$args['post__in'] = $ids;
				}
			}	
			if(!empty($search) || !empty($arrival) || !empty($array_cat)){
				if(empty($args['post__in'])){
					$result =  array(
						'category' => $category,
						'products' => array(),	
					);						
					return $result;	
				}
			}
			$listexcatalog = $this->getproduct_exclude_from_catalog();
			if(!empty($listexcatalog)){
				if(empty($args['post__in'])){				
					$args['post__not_in'] = $listexcatalog;
				}else{
					$include = array_diff($args['post__in'],$listexcatalog);
					$args['post__in'] = $include;
				}
			}				
			$query = new WP_Query($args);
			$products = $query->posts;
			if(!empty($name_sort)){
				$products = $this->sortproductbyname($args);
			}
			$list = array();				
			foreach($products as $product){
				$list[] = $this->get_data($product,$request);
			}
			if(empty($list)){
				$list = array();
			}
			$result =  array(
				'category' => $category,
				'products' => $list,	
			);			
		}
		else{
			$cat_per_page = $parameters['cat_per_page'];
			$args = array(
				'taxonomy' => 'product_cat',
				'orderby' => 'id',
				'hide_empty'=> 0,
				'number' => $cat_per_page
			);
			$categories = get_categories($args);
			foreach($categories as $categorie){
				$post_args = array(
					'posts_per_page'   => $post_per_page,
					'paged'            => $post_num_page,						
					'category'         => '',
					'category_name'    => '',
					'product_cat'      => $categorie->slug,
					'orderby'          => $post_order_by,
					'order'            => $post_order_page,
					'meta_key'         => '',
					'meta_value'       => '',
					'post_type'        => 'product',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'author'	       => '',
					'author_name'	   => '',
					'post_status'      => 'publish',
					'meta_query' 	   => array(),
					'suppress_filters' => true 
				);
				$category['id'] = $categorie->term_id;
				$category['name'] = $categorie->name;
				$thumbnail_id = get_woocommerce_term_meta( $categorie->term_id, 'thumbnail_id', true );			
				$category['images'] =  wp_get_attachment_url( $thumbnail_id );
				$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
				if($hideoutofstock == 'yes'){
					array_push($post_args['meta_query'],array(
							'key' => '_stock_status',
							'value' => 'outofstock',
							'compare' => '!='
						)
					);
				}
				if(!empty($rating_sort)){
					$post_args['meta_key'] = '_wc_average_rating';
					$post_args['orderby'] = 'meta_value';
				}
				if(!empty($array_cat)){
					$ids = $this->getproductbyarraycat($request);
					if(empty($ids)){
						$result =  array(
							'category' => $category,
							'products' => array(),	
						);							
						return $result;	
					}
					if(!empty($post_args['post__in'])){
						$post_args['post__in'] = array_merge($post_args['post__in'],$ids);
					}else{
						$post_args['post__in'] = $ids;
					}
				}
				if(!empty($arrival)){
					$ids = $this->getproductarrival($request);
					if(empty($ids)){
						$result =  array(
							'category' => $category,
							'products' => array(),	
						);						
						return $result;	
					}
					if(!empty($post_args['post__in'])){
						$list_post_id = array();
						foreach($ids as $post_id){
							if(in_array($post_id,$post_args['post__in'])){
								$list_post_id[] = $post_id;
							}
						}
						$post_args['post__in'] = $list_post_id;
					}else{
						$post_args['post__in'] = $ids;
					}
				}
				if(!empty($search) && $search != null){
					$ids = $this->WooConnectorSearchOnProduct($search,'publish');
					if(empty($ids)){
						$result =  array(
							'category' => $category,
							'products' => array(),	
						);						
						return $result;	
					}
					if(!empty($post_args['post__in'])){
						$list_post_id = array();
						foreach($ids as $post_id){
							if(in_array($post_id,$post_args['post__in'])){
								$list_post_id[] = $post_id;
							}
						}
						$post_args['post__in'] = $list_post_id;
					}else{
						$post_args['post__in'] = $ids;
					}
				}
				if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
					$ids = self::$id_with_product;
					if(empty($ids)){
						return array();
					}
					if(!empty($post_args['post__in'])){
						$list_post_id = array();
						foreach($ids as $post_id){
							if(in_array($post_id,$post_args['post__in'])){
								$list_post_id[] = $post_id;
							}
						}
						$post_args['post__in'] = $list_post_id;
					}else{
						$post_args['post__in'] = $ids;
					}
				}
				if(!empty($search) || !empty($arrival) || !empty($array_cat)){
					if(empty($post_args['post__in'])){
						$result =  array(
							'category' => $category,
							'products' => array(),	
						);							
						return $result;	
					}
				}
				$listexcatalog = $this->getproduct_exclude_from_catalog();
				if(!empty($listexcatalog)){
					if(empty($args['post__in'])){				
						$post_args['post__not_in'] = $listexcatalog;
					}else{
						$include = array_diff($args['post__in'],$listexcatalog);
						$post_args['post__in'] = $include;
					}
				}					
				$query = new WP_Query($post_args);
				$products = $query->posts;
				if(!empty($name_sort)){
					$products = $this->sortproductbyname($args);
				}
				$list = array();
				foreach($products as $product){
					$list[] = $this->get_data($product,$request);
				}
				if(empty($list)){
					$list = array();
				}
				$result[] =  array(
					'category' => $category,
					'products' => $list,	
				);
			}
		}		
		return $result;
	}
	
	/*
	* Validate post order by
	*/
	function validate_post_order_by($param, $request, $key) {
		$parameters = $request->get_params();
		if($parameters['post_order_by'] == 'ID' || $parameters['post_order_by'] == 'author' 
		|| $parameters['post_order_by'] == 'title' || $parameters['post_order_by'] == 'date' 
		|| $parameters['post_order_by'] == 'modified' || $parameters['post_order_by'] == 'parent' 
		|| $parameters['post_order_by'] == 'rand' || $parameters['post_order_by'] == 'comment_count' 
		|| $parameters['post_order_by'] == 'menu_order' || $parameters['post_order_by'] == 'meta_value' 
		|| $parameters['post_order_by'] == 'meta_value_num' || $parameters['post_order_by'] == 'post__in') 
		{
			return $parameters['post_order_by'];
		}
		else{
			return 'date';
		}
	}
	/*
	* Validate post order page
	*/
	public function validate_post_order_page($param, $request, $key){
		$parameters = $request->get_params();
		if($parameters['post_order_page'] == 'DESC' || $parameters['post_order_page'] == 'ASC')
		{
			return $parameters['post_order_page'];
		}
		else{
			return 'DESC';
		}
	}

	/*
	* Validate post status
	*/
	public function validate_post_status($param, $request, $key) {
		$parameters = $request->get_params();
		if($parameters['status'] == 'any' || $parameters['status'] == 'draft' 
		|| $parameters['status'] == 'pending' || $parameters['status'] == 'private' 
		|| $parameters['status'] == 'publish'){
			return $parameters['status'];
		}
		else{
			return 'publish';
		}
	}

	/*
	* Validate post tax class
	*/
	public function validate_post_type($param, $request, $key) {
		$parameters = $request->get_params();
		if($param['type'] == 'standard' || $parameters['type'] == 'reduced-rate' 
		|| $parameters['type'] == 'zero-rate' || $parameters['type'] == 'testin'){
			return $parameters['type'];
		}else{
			return 'standard';
		}
	}

	/*
	* Validate cat order by
	*/
	public function validate_cat_order_by($param, $request, $key) {
		$parameters = $request->get_params();
		if($parameters['cat_order_by'] == 'id' || $parameters['cat_order_by'] == 'include' 
		|| $parameters['cat_order_by'] == 'name' || $parameters['cat_order_by'] == 'slug' 
		|| $parameters['cat_order_by'] == 'term_group' || $parameters['cat_order_by'] == 'description' 
		|| $parameters['cat_order_by'] == 'count'){
			return $parameters['cat_order_by'];
		}
		else{
			return 'name';
		}
	}
	
	/*
	* Validate cat order page
	*/
	public function validate_cat_order_page($param, $request, $key){
		$parameters = $request->get_params();
		if($parameters['cat_order_page'] == 'DESC' || $parameters['cat_order_page'] == 'ASC'){
			return $parameters['cat_order_page'];
		}else{
			return 'ASC';
		}
	}
	
	/*
	* Get product top views
	* Param post_per_page
	*/
	public function getbestviews($request){
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = isset($parameters['post_num_page']) ? $parameters['post_num_page'] : 1;
		$rownum = ($post_num_page - 1) * $post_per_page;
		$post_order_page = $parameters['post_order_page'];
		global $wpdb;
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		$ids = '';
		if(!empty($listexcatalog)){				
			foreach($listexcatalog as $list){
				$ids .= $list.',';
			}
			$ids = trim($ids,',');				
		}			
		if(empty($ids)){
			$ids = 0;
		}
		$countlang = 1;
		$listcheckids = array();
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$listlang = $this->get_all_langs_names();
			$countlang = count($listlang);
			$listcheckids = self::$id_with_product;
		}			
		$products = $wpdb->get_results($wpdb->prepare( "SELECT posts.*, IFNULL( mv.mc_count, 0 ) AS mobi_views
		FROM " . $wpdb->prefix . "posts AS posts 
		LEFT JOIN " . $wpdb->prefix . "mobiconnector_views AS mv 
		ON posts.ID =  mv.mc_id
		WHERE post_type = 'product' AND post_status = 'publish' AND posts.ID NOT IN (".$ids.")
		ORDER BY mobi_views $post_order_page LIMIT $rownum,%d",$post_per_page*$countlang));
		$list = array();
		if($hideoutofstock == 'yes'){
			$products = $wpdb->get_results($wpdb->prepare( "SELECT posts.*, IFNULL( mv.mc_count, 0 ) AS mobi_views
			FROM " . $wpdb->prefix . "posts AS posts INNER JOIN (SELECT post_id,meta_value FROM ". $wpdb->prefix ."postmeta WHERE meta_key = '_stock_status') AS mtvlue ON mtvlue.post_id = posts.ID
			LEFT JOIN " . $wpdb->prefix . "mobiconnector_views AS mv ON posts.ID =  mv.mc_id WHERE post_type = 'product' AND post_status = 'publish' AND posts.ID NOT IN (".$ids.") HAVING mtvlue.meta_value <> 'outofstock' ORDER BY mobi_views $post_order_page LIMIT $rownum,%d",$post_per_page*$countlang));
		}
		foreach($products as $product){
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				if(!empty($listcheckids)){
					if(in_array($product->ID,$listcheckids)){
						$list[] = $this->get_data($product,$request);
					}
				}else{
					return array();
				}		
			}else{
				$list[] = $this->get_data($product,$request);
			}						
		}
		if(empty($list)){
			return array();
		}		
		return $list;
	}

	public function get_all_langs_names($lang='en'){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$listposts = array();
		$sql = "SELECT SQL_CALC_FOUND_ROWS ".$prefix."icl_locale_map.* FROM ".$prefix."icl_locale_map";
		$listposts = $wpdb->get_results($sql,ARRAY_A);
		return $listposts;
	}

	/*
	* Get product top sales
	* Param post_per_page
	* Param post_num_page
	* Param post_order_page
	*/
	public function getbestsales($request){
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');	
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'            => $post_num_page,				
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value_num',
			'order'            => $post_order_page,
			'meta_key'         => 'total_sales',
			'meta_value'       => '',
			'post_type'        => 'product',
			'meta_query'	   => array(),
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	       => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);		
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if($listexcatalog){
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		$query = new WP_Query($args);
		$products = $query->posts;
		$list = array();
		foreach($products as $product){
			$list[] = $this->get_data($product,$request);
		}
		if(empty($list)){
			return array();
		}		
		return $list;
	}
	/**
	 * GET product by count ratting
	 */
	public function getbestrating($request){
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'            => $post_num_page,				
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value_num',
			'order'            => $post_order_page,
			'meta_key'         => '_wc_average_rating',
			'meta_value'       => '',
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'meta_query'	   => array(),
			'post_parent'      => '',
			'author'	       => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);		
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if(!empty($listexcatalog)){
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		$query = new WP_Query($args);
		$products = $query->posts;
		$list = array();
		if(!empty($products)){
			foreach($products as $product){
				$list[] = $this->get_data($product,$request);
			}
			if(empty($list)){
				return array();
			}			
		}
		return $list;
	}
	/*
	* Get product if onsale = true
	* Param post_per_page
	*/
	public function getproductonsale($request){
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'            => $post_num_page,				
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'meta_value',
			'order'            => $post_order_page,
			'meta_query'       => array(
				array(
					'key' => '_sale_price',
					'value' => 0,
					'type'    => 'numeric',
					'compare' => '>'
				)
			),
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	       => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);	
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}
		$countmeta = count($args['meta_query']);
		if($countmeta > 1){
			$args['meta_query']['relation'] = 'AND';
		}
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if(!empty($listexcatalog)){
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		$query = new WP_Query($args);
		$products = $query->posts;
		$list = array();	
		if(!empty($products)){
			foreach($products as $product){
				$list[] = $this->get_data($product,$request);
			}
			if(empty($list)){
				return array();
			}			
		}
		return $list;			
	}
	/*
	* Get all attribute an value attribute
	*/
	public function getattribute($request){	
		$params = $request->get_params();	
		$post_category = isset($params['post_category']) ? $params['post_category'] : false;
		$listidpro = array();
		if(!empty($post_category)){
			$term = get_term_by('id',$post_category,'product_cat');
			$args = array(
				'posts_per_page'   => -1,
				'paged'            => 1,
				'product_cat'      => $term->slug,
				'category_name'    => '',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => 'product',
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	       => '',
				'author_name'	   => '',
				'post_status'      => 'publish',
				'suppress_filters' => true 
			);	
			$listexcatalog = $this->getproduct_exclude_from_catalog();
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
			if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
				$ids = self::$id_with_product;
				if(empty($ids)){
					return array();
				}
				if(!empty($args['post__in'])){
					$list_post_id = array();
					foreach($ids as $post_id){
						if(in_array($post_id,$args['post__in'])){
							$list_post_id[] = $post_id;
						}
					}
					$args['post__in'] = $list_post_id;
				}else{
					$args['post__in'] = $ids;
				}
			}
			$prices = get_posts($args);
			$listidpro = array();
			foreach($prices as $post){
				$listidpro[] = $post->ID;
			}
		}
		$checkcustom = get_option('wooconnector_settings-custom-attribute');
		if($checkcustom == 1){
			global $wpdb;
			$attributes = $wpdb->get_results("
				SELECT DISTINCT * FROM ". $wpdb->prefix . "postmeta
				WHERE meta_key = '_product_attributes'	
			");
			foreach($attributes as $attribute){
				if(!empty($post_category)){
					if(!in_array($attribute->post_id,$listidpro)){
						continue;
					}
				}
				$attrslist = unserialize($attribute->meta_value);
				foreach($attrslist as $attrlist => $values){
					if($values['is_taxonomy'] === 0){
						$listattrs[] = $values;
					}
				}	
			}
			$custom_attributes = array();
			$listout = array();
			if(!empty($listattrs)){
				foreach($listattrs as $listattr){
					$value = $listattr['value'];
					$values = explode('|',$value);
					$listoutattrs = array();
					$attr_name = trim($listattr['name'],' ');
					$index = strtolower($attr_name);
					if (!isset($custom_attributes[$index])){
						$custom_attributes[$index] = array(
							'name' => ucfirst($attr_name),
							'slug' => $index,
							'term' => array(),
						);
					}									
					if (!empty($values)){						
						foreach($values as $item){
							$item = trim(strtolower($item));
							$list['name'] = ucfirst($item);
							$list['slug'] = $item;
							$list['taxonomy'] = $index;	
							$custom_attributes[$index]['term'][$item] = $list;											
						}						
					}
				}
			}else{
				$custom_attributes = array();
			}		
			$totallist['custom'] = $custom_attributes;
		}
		$attrs = wc_get_attribute_taxonomies();
		$options = array('hide_empty' => true);
		$list = array();
		foreach($attrs as $attr){
			$slugattr = wc_attribute_taxonomy_name($attr->attribute_name);
			if(!empty($post_category)){
				$check = $this->checkExistProductInAttribute($slugattr,$listidpro);
				if(!$check){
					continue;
				}
			}
			$terms = get_terms($slugattr, $options);
			$list[] = array(
				'id' => $attr->attribute_id,
				'name' => $attr->attribute_label,
				'slug' => $slugattr,
				'type' => $attr->attribute_type,
				'order_by' => $attr->attribute_orderby,	
				'term' => $terms
			);				
		}		
		$totallist['attributes'] = $list;
		global $wpdb;
		$prices = $wpdb->get_results("SELECT MIN(CAST(IFNULL(mt.meta_value, 0) AS DECIMAL)) as minprice,MAX(CAST(IFNULL(mt.meta_value, 0) AS DECIMAL)) as maxprice 
			FROM ".$wpdb->prefix."posts as p INNER JOIN ".$wpdb->prefix."postmeta as mt
			ON p.ID = mt.post_id
			WHERE meta_key = '_price'
		",ARRAY_A);
		$totallist['min_price'] = $prices[0]['minprice'];
		$totallist['max_price'] = $prices[0]['maxprice'];		
		return $totallist;
	}

	public function checkExistProductInAttribute($attribute,$listidpost){
		$taxonomies = array($attribute);
		$terms = get_terms($taxonomies);
		$termids = '';
		foreach($terms as $term){
			$termids .= $term->term_id.',';
		}
		$idpost = implode($listidpost,',');
		$idpost = trim($idpost,',');
		$termids = trim($termids,',');
		global $wpdb;
		$sql = "SELECT COUNT(p.ID) FROM ". $wpdb->prefix. "posts as p INNER JOIN ".$wpdb->prefix."term_relationships as tr ON p.ID = tr.object_id WHERE p.post_type='product' AND p.post_status='publish' AND p.ID IN (".$idpost.") AND tr.term_taxonomy_id IN (".$termids.")";
		$count = $wpdb->get_var($sql);
		if($count > 0){
			return true;
		}else{
			return false;
		}
	}
	/*
	* Get product by attribute
	* Param attribute term_id
	* Param post_per_page
	* Param post_num_page
	* Param post_order_page
	* Param post_order_by
	*/
	public function getproductbyattribute($request){	
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');
		$requestattr = isset($parameters['attribute']) ? $parameters['attribute'] : false;
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$post_order_by = $parameters['post_order_by'];
		$post_category = isset($parameters['post_category']) ? $parameters['post_category'] : false;
		$min_price = isset($parameters['min_price']) ? $parameters['min_price'] : false;			
		$max_price = isset($parameters['max_price']) ? $parameters['max_price'] : false;
		$in_stock = isset($parameters['in_stock']) ? $parameters['in_stock'] : false;
		$on_sale = isset($parameters['on_sale']) ? $parameters['on_sale'] : false;
		$search = isset($parameters['search']) ? $parameters['search'] : false;	
		$price_sort = isset($parameters['price_sort']) ? $parameters['price_sort'] : false;
		$arrival = isset($parameters['arrival']) ? $parameters['arrival'] : false;		
		$brand = isset($parameters['brand']) ? $parameters['brand'] : false;	
		$array_cat = isset($parameters['array_cat']) ? $parameters['array_cat'] : false;	
		$name_sort = isset($parameters['name_sort']) ? $parameters['name_sort'] : false;
		$popularity_sort = isset($parameters['popularity_sort']) ? $parameters['popularity_sort'] : false;
		$rating_sort = isset($parameters['rating_sort']) ? $parameters['rating_sort'] : false;
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'            => $post_num_page,				
			'category'         => '',
			'category_name'    => '',
			'orderby'          => $post_order_by,
			'order'            => $post_order_page,
			'tax_query'        => array(),
			'meta_query'       => array(),
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	       => '',
			'author_name'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		if(!empty($requestattr) || $requestattr != null){
			$idattrs = json_decode($requestattr);							
			foreach($idattrs as $idattr => $val){							
				$key = $val->keyattr;
				$value = $val->valattr;
				$type = $val->type;
				if($type == 'attributes'){
					$listtypeattr[] = $val;																				
					array_push($args['tax_query'],array(
							'taxonomy' => $key,
							'field' => 'slug',
							'terms' => $value
						)
					); 							
				}
				if($type == 'custom'){										
					array_push($args['meta_query'],	array(
							'key' => '_product_attributes',								
							'value' => '.*"'.$key.'";s:[0-9]+:"value";s:[0-9]+:"[^"]*'.$value.'[^"]*"',						
							'compare' => 'REGEXP'
						)
					);			
				}						
			}					
		}	
		if(!empty($rating_sort)){
			$args['meta_key'] = '_wc_average_rating';
			$args['orderby'] = 'meta_value';
		}
		if(!empty($post_category) || $post_category != null ){
			$ids = $this->get_product_attribute_by_categories($post_category);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$args['post__in'] = array_merge($args['post__in'],$ids);
			}else{
				$args['post__in'] = $ids;
			}
		}	
		if(!empty($brand)){
			array_push($args['tax_query'],array(
				'taxonomy' => 'wooconnector_product_brand',
				'field' => 'term_id',
				'terms' =>  $brand,
			));
		}	
		if(!empty($min_price) && $min_price != null){
			array_push($args['meta_query'],array(
					'key' => '_price',
					'value' => $min_price,
					'type'    => 'numeric',
					'compare' => '>='
				)
			);
		}
		if(!empty($max_price) && $max_price != null){
			array_push($args['meta_query'],array(
					'key' => '_price',
					'value' => $max_price,
					'type'    => 'numeric',
					'compare' => '<='
				)
			);
		}
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}
		if(!empty($in_stock)){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'instock',
					'compare' => '='
				)
			);
		}
		if(!empty($on_sale)){
			array_push($args['meta_query'],array(
					'key' => '_sale_price',
					'value' => 0,
					'compare' => '>'
				)
			);
		}
		$countmeta = count($args['meta_query']);	
		if($countmeta > 1){
			$args['meta_query']['relation'] = 'AND';			
		}
		if(count($args['tax_query']) > 1){

			$args['tax_query']['relation'] = 'OR';					
		}
		if(!empty($array_cat)){
			$ids = $this->getproductbyarraycat($request);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$args['post__in'] = array_merge($args['post__in'],$ids);
			}else{
				$args['post__in'] = $ids;
			}
		}
		if(!empty($arrival)){
			$ids = $this->getproductarrival($request);
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		if(!empty($search) && $search != null){
			$ids = $this->WooConnectorSearchOnProduct($search,'publish');
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}	
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}	
		if(!empty($search) || !empty($arrival) || !empty($array_cat)){
			if(empty($args['post__in'])){
				return array();	
			}
		}
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if(!empty($listexcatalog)){
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		$query = new WP_Query($args);
		$products = $query->posts;
		if(!empty($name_sort)){
			$products = $this->sortproductbyname($args);
		}
		$list = array();
		if(!empty($products)){	
			foreach($products as $product){		
				$list[] = $this->get_data($product,$request);
			}
		}
		if(empty($list)){
			return array();
		}		
		return $list;
	}

	private function get_product_attribute_by_categories($product_id){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT p.ID FROM ".$prefix."posts AS p 
				INNER JOIN ".$prefix."term_relationships AS tr ON p.ID = tr.object_id 
				INNER JOIN ".$prefix."term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE p.post_type = 'product' AND p.post_status = 'publish' AND tt.taxonomy = 'product_cat' AND tt.term_id = $product_id
		";
		$results = $wpdb->get_results($sql,ARRAY_A);
		$list_id = array();
		if(!empty($results)){
			foreach($results as $product){
				$list_id[] = (int)$product['ID'];
			}
		}
		return $list_id;

	}

	/*
	* Insert review to product
	* Param product id
	* Param comment
	* Param ratestar
	* Param namecustomer
	* Param emailcustomer
	*/
		
	public function postreviews($request){
		$parameters = $request->get_params();
		$productid = $parameters['product'];
		$product = wc_get_product($productid);
		$reviews_allowed = $product->get_reviews_allowed();
		$auth = isset($_SERVER['HTTP_AUTHORIZATION']) ?  $_SERVER['HTTP_AUTHORIZATION'] : false;
		if (!$auth) {
            $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ?  $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
        }		
		if($reviews_allowed){
			$IPauthor = $this->get_the_user_ip();
			$current_user_id = get_current_user_id();
			if(!user_can($current_user_id,'editor')){
				if(isset($_SESSION[$IPauthor][$current_user_id]) && (time() - $_SESSION[$IPauthor][$current_user_id]) < 10){
					return new WP_Error( 'rest_comment_error_speed', __( 'You are posting comments too quickly. Slow down.','wooconnector' ), 401 );
				}
			}			
			global $wpdb;
			$checkapproved = get_option('comment_moderation');
			$checkwhitelist = get_option('comment_whitelist');
			$listmoderation = get_option('moderation_keys');
			$blacklist_keys = get_option('blacklist_keys');
			$comment_max_links = get_option('comment_max_links');
			$comments_notify = get_option('comments_notify');
			$moderation_notify = get_option('moderation_notify');
			$contentcomment = $parameters['comment'];
			$checkcontent = str_replace(" ","",$contentcomment);
			$lengcontent = strlen($checkcontent);
			$ratestar = $parameters['ratestar'];
			$rs = floatval($ratestar);
			if ( $ratestar < 1 || $ratestar > 5 ) {
				return new WP_Error( 'require_valid_ratestar', __( 'Error, Please input 1 to 5 in ratestar.','wooconnector' ), 401 );
			}
			if ( $lengcontent == 0 ) {
				return new WP_Error( 'require_valid_comment', __( 'Error, Please type a comment.','wooconnector' ), 401 );
			}			
			$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
			$time = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
			$admins = false;
			$approved = 1;
			$user = false;
			$datapushnotice = array();
			$checkmode = 0;
			$checkblack = 0;
			if(wooconnector_check_user_login_by_token($auth)){
				$user = wp_get_current_user();
				$userid = $user->ID;					
				$author = $user->data;
				$authorcap = $user->caps;
				if(!empty($authorcap['administrator']) && $authorcap['administrator'] == true){
					$admins = true;
				}
				if($admins){
					$approved = 1;
				}else{
					if($checkapproved == 1){
						$approved = 0;
					}else{
						if($checkwhitelist == 1){
							if(!empty($author->user_email)){
								$email = $author->user_email;
								$email = esc_sql(sanitize_email($email));
								global $wpdb;
								$table = $wpdb->prefix.'comments';
								$checks = $wpdb->get_results("SELECT * FROM $table WHERE comment_author_email = '$email' AND comment_approved = 1",OBJECT);
								if(!empty($checks)){
									$approved = 1;
								}else{
									$approved = 0;
								}
							}else{
								$approved = 0;
							}
						}else{
							$approved = 1;		
						}
					}
				}
				$idauthor = $author->ID;
				$nameauthor = $author->display_name;
				$emailauthor = $author->user_email;
				$urlauthor = $author->user_url;	
				if(!user_can($userid,'editor')){
					if(!empty($listmoderation)){
						$listkeys = array();
						$listsmode = explode("\n",$listmoderation);
						foreach($listsmode as $mode){
							if(strpos($contentcomment,$mode) !== false){
								$listkeys[] = $mode;
								$checkmode++;
							}
							if(strpos($user->display_name,$mode) !== false){
								$listkeys[] = $mode;
								$checkmode++;
							}
							if(strpos($user->user_email,$mode) !== false){
								$listkeys[] = $mode;
								$checkmode++;
							}
							if(strpos($IPauthor,$mode) !== false){
								$listkeys[] = $mode;
								$checkmode++;
							}
						}
						if($checkmode > 0){
							$sqlcheckmaxappro = "SELECT COUNT(*) FROM ".$wpdb->prefix."comments WHERE comment_approved = 0";
							$countcomentappro = $wpdb->get_var($sqlcheckmaxappro);
							$datapushnotice = array(
								'comment_author' => $nameauthor,
								'comment_author_email' => $emailauthor,
								'comment_author_url' => $urlauthor,
								'comment_content' => $contentcomment,
								'comment_author_IP' => $IPauthor,
								'comment_count_approve' => $countcomentappro
							);							
							$approved = 0;
						}
					}
					if(!empty($blacklist_keys)){
						$listsblacks = explode("\n",$blacklist_keys);
						foreach($listsblacks as $black){
							if(strpos($contentcomment,$black) !== false){
								$checkblack++;
							}
							if(strpos($user->display_name,$black) !== false){
								$checkblack++;
							}
							if(strpos($user->user_email,$black) !== false){
								$checkblack++;
							}
							if(strpos($IPauthor,$black) !== false){
								$checkblack++;
							}
						}
						if($checkblack > 0){
							$approved = 'trash';
						}
					}	
				}				
				$sqlcheckmax = "SELECT COUNT(*) FROM ".$wpdb->prefix."comments WHERE comment_content = '$contentcomment' AND comment_author = '$nameauthor' AND comment_author_email = '$emailauthor' AND comment_author_url = '$urlauthor'";				
				$countcoment = $wpdb->get_var($sqlcheckmax);
				if($countcoment + 1 >=  $comment_max_links){
					return new WP_Error( 'rest_comment_error', __( 'Duplicate comment detected; it looks as though you’ve already said that!','wooconnector' ), array( 'status' => 401 ) );
				}
				$data = array(
					'comment_post_ID' => $productid,
					'comment_author' => $nameauthor,
					'comment_author_email' => $emailauthor,
					'comment_author_url' => $urlauthor,
					'comment_content' => $contentcomment,
					'comment_type' => '',
					'comment_karma' => 1,
					'comment_parent' => 0,
					'user_id' => $userid,
					'comment_author_IP' => $IPauthor,
					'comment_agent' => $agent,
					'comment_date' => $time,
					'comment_approved' => $approved,
					'comment_meta' => array(
						'rating' => $rs,
						'verified' => 1
					)
				);
			}else{
				$nameauthor = '';
				$emailauthor = '';
				$requiredlogin = get_option( 'comment_registration');
				if ( $requiredlogin == 1 ){
					return new WP_Error( 'rest_comment_login_required', __( 'Sorry, you must be logged in to comment.','wooconnector' ), array( 'status' => 401 ) );
				}		
				$nameemailrequired = get_option('require_name_email');
				if ( $nameemailrequired == 1 ) {
					$nameauthor = isset($parameters['namecustomer'])? $parameters['namecustomer'] : false;
					$emailauthor = isset($parameters['emailcustomer'])? $parameters['emailcustomer'] : false;
					if ( empty( $nameauthor ) || empty( $emailauthor ) ) {
						return new WP_Error( 'rest_comment_author_data_required', __( 'Creating a comment requires valid author name and email values.','wooconnector' ), array( 'status' => 401 ) );
					}	
					if(!is_email($emailauthor)){
						return new WP_Error( 'rest_email_error', __( 'Sorry, is not a email.','wooconnector' ), array( 'status' => 401 ) );
					}				
				}
				if($checkapproved == 1){
					$approved = 0;
				}else{
					if($checkwhitelist == 1){
						if(!empty($emailauthor)){
							$emailauthor = esc_sql($emailauthor);
							global $wpdb;
							$table = $wpdb->prefix.'comments';
							$checks = $wpdb->get_results("SELECT * FROM $table WHERE comment_author_email = '$emailauthor' AND comment_approved = 1",OBJECT);
							if(!empty($checks)){
								$approved = 1;
							}else{
								$approved = 0;
							}
						}else{
							$approved = 0;
						}
					}else{
						$approved = 1;
					}
				}
				if(!empty($listmoderation)){
					$listkeys = array();
					$listsmode = explode("\n",$listmoderation);
					foreach($listsmode as $mode){
						if(strpos($contentcomment,$mode) !== false){
							$listkeys[] = $mode;
							$checkmode++;
						}
						if(strpos($nameauthor,$mode) !== false){
							$listkeys[] = $mode;
							$checkmode++;
						}
						if(strpos($emailauthor,$mode) !== false){
							$listkeys[] = $mode;
							$checkmode++;
						}
						if(strpos($IPauthor,$mode) !== false){
							$listkeys[] = $mode;
							$checkmode++;
						}
					}
					if($checkmode > 0){	
						$sqlcheckmaxappro = "SELECT COUNT(*) FROM ".$wpdb->prefix."comments WHERE comment_approved = 0";
						$countcomentappro = $wpdb->get_var($sqlcheckmaxappro);					
						$datapushnotice = array(
							'comment_author' => $nameauthor,
							'comment_author_email' => $emailauthor,
							'comment_author_url' => '',
							'comment_content' => $contentcomment,
							'comment_author_IP' => $IPauthor,
							'comment_count_approve' => $countcomentappro
						);
						$approved = 0;	
					}
				}
				if(!empty($blacklist_keys)){
					$listsblacks = explode("\n",$blacklist_keys);
					foreach($listsblacks as $black){
						if(strpos($contentcomment,$black) !== false){
							$checkblack++;
						}
						if(strpos($nameauthor,$black) !== false){
							$checkblack++;
						}
						if(strpos($emailauthor,$black) !== false){
							$checkblack++;
						}
						if(strpos($IPauthor,$black) !== false){
							$checkblack++;
						}
					}
					if($checkblack > 0){
						$approved = 'trash';
					}
				}
				$sqlcheckmax = "SELECT COUNT(*) FROM ".$wpdb->prefix."comments WHERE comment_content = '$contentcomment' AND comment_author = '$nameauthor' AND comment_author_email = '$emailauthor'";
				$countcoment = $wpdb->get_var($sqlcheckmax);
				if($countcoment + 1 >=  $comment_max_links){
					return new WP_Error( 'rest_comment_error', __( 'Duplicate comment detected; it looks as though you’ve already said that!','wooconnector' ), array( 'status' => 401 ) );
				}
				$data = array(
					'comment_post_ID' => $productid,
					'comment_author' => $nameauthor,
					'comment_author_email' => $emailauthor,
					'comment_author_url' => '',
					'comment_content' => $contentcomment,
					'comment_type' => '',
					'comment_parent' => 0,
					'comment_karma' => 1,
					'user_id' => '0',
					'comment_author_IP' => $IPauthor,
					'comment_agent' => $agent,
					'comment_date' => $time,
					'comment_approved' => $approved,
					'comment_meta' => array(
						'rating' => $rs,
						'verified' => 0
					)
				);
			}			
			$comment_id = wp_insert_comment($data);
			$_SESSION[$IPauthor][$current_user_id] = time();
			$post = get_post($productid);
			$create_post_user_id = $post->post_author;			
			if($checkmode > 0 && $checkblack == 0 && $moderation_notify == 1 && $userid != $create_post_user_id && user_can($create_post_user_id,'administrator')){
				$this->send_mail_with_comment($comment_id,(object)$datapushnotice,$post,true);
			}elseif($comments_notify == 1 && $current_user_id != $create_post_user_id && user_can($create_post_user_id,'administrator')){
				$this->send_mail_with_comment($comment_id,(object)$data,$post);
			}
			$mes = '';
			if($approved == 1){
				$mes = 'approved';
			}elseif($approved === 0){
				$mes = 'unapproved';
			}elseif($approved == 'trash'){
				$mes = 'trash';
			}
			return array(
				'result' 	=> 'success',
				'status' 	=> $mes,
				'message' 	=> __('Add a comment successfully','wooconnector')
			);
		}else{
			return array(
				'result' => 'failed',
				'message' => __('Products are not allowed to comment','wooconnector')
			);
		}
	}

	/**
	 * Send mail when post of administrator comment
	 * 
	 * @param object $comment    details comment
	 */
	public function send_mail_with_comment($comment_id,$comment,$post,$moderation = false){
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$post_link = get_permalink($post);
		$comment_link = get_comment_link($comment_id);
		$approve_url = admin_url( 'comment.php' ).'?action=approve&c='.$comment_id.'#wpbody-content';
		$trash_url = admin_url( 'comment.php' ).'?action=trash&c='.$comment_id.'#wpbody-content';
		$spam_url = admin_url( 'comment.php' ).'?action=spam&c='.$comment_id.'#wpbody-content';
		$curentlycommenturl = admin_url( 'edit-comments.php').'comment_status=moderated#wpbody-content';
		$author = $comment->comment_author . " (IP address: ". $comment->comment_author_IP .", ". $comment->comment_author_IP .")";
		if($moderation == false){
			$message  = sprintf( __( 'New comment on your post "%s"' ), $post->post_title ) . "\r\n";
			$message .= sprintf( __( 'Author: %s' ), $author ) . "\r\n";
			$message .= sprintf( __( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
			$message .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
			$message .= sprintf( __( 'Comment: %1$s%2$s' ), "\r\n", $comment->comment_content ) . "\r\n\r\n";
			$message .= sprintf( __( 'You can see all comments on this post here: %1$s%2$s' ), "\r\n", $post_link ) . "\r\n";
			$message .= sprintf( __( 'Permalink: %s ' ), $comment_link ) . "\r\n";
			$message .= sprintf( __( 'Trash it: %s ' ), $trash_url ) . "\r\n";
			$message .= sprintf( __( 'Spam it: %s ' ), $spam_url ) . "\r\n";
			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%1$s] Comment: "%2$s"' ), $blogname , $post->post_title ), $message );
		}else{
			$message  = sprintf( __( 'A new comment on the post "%s" is waiting for your approval' ), $post->post_title ) . "\r\n";
			$message .= sprintf( __( '%s' ), $post_link ) . "\r\n\r\n";
			$message .= sprintf( __( 'Author: %s' ), $author ) . "\r\n";
			$message .= sprintf( __( 'Email: %s' ), $comment->comment_author_email ) . "\r\n";
			$message .= sprintf( __( 'URL: %s' ), $comment->comment_author_url ) . "\r\n";
			$message .= sprintf( __( 'Comment: %1$s%2$s' ), "\r\n", $comment->comment_content ) . "\r\n\r\n";
			$message .= sprintf( __( 'Approve it: %s' ), $approve_url ) . "\r\n";
			$message .= sprintf( __( 'Trash it: %s ' ), $trash_url ) . "\r\n";
			$message .= sprintf( __( 'Spam it: %s ' ), $spam_url ) . "\r\n";
			$message .= sprintf( __( 'Currently %3$d comments are waiting for approval. Please visit the moderation panel: %1$s%2$s' ), "\r\n", $curentlycommenturl, $comment->comment_count_approve ) . "\r\n\r\n";
			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%1$s] Please moderate: "%2$s"' ), $blogname , $post->post_title ), $message );
		}
	}

	/*
	* Get current user ip
	*/
	public function get_the_user_ip() {
		// check for shared internet/ISP IP
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// check for IPs passing through proxies
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// check if multiple ips exist in var
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
				$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($iplist as $ip) {
					if ($this->validate_ip($ip))
						return $ip;
					}
			} else {
				if ($this->validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
			return $_SERVER['HTTP_X_FORWARDED'];
		if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
			return $_SERVER['HTTP_FORWARDED_FOR'];
		if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
			return $_SERVER['HTTP_FORWARDED'];

		// return unreliable ip since all else failed
		return $_SERVER['REMOTE_ADDR'];

	}
		
	/*
	* Validate ip
	*/
	public function validate_ip($ip) {
		if (strtolower($ip) === 'unknown')
			return false;

		// generate ipv4 network address
		$ip = ip2long($ip);

		// if the ip is set and not equivalent to 255.255.255.255
		if ($ip !== false && $ip !== -1) {
			// make sure to get unsigned long representation of ip
			// due to discrepancies between 32 and 64 bit OSes and
			// signed numbers (ints default to signed in PHP)
			$ip = sprintf('%u', $ip);
			// do private network range checking
			if ($ip >= 0 && $ip <= 50331647) return false;
			if ($ip >= 167772160 && $ip <= 184549375) return false;
			if ($ip >= 2130706432 && $ip <= 2147483647) return false;
			if ($ip >= 2851995648 && $ip <= 2852061183) return false;
			if ($ip >= 2886729728 && $ip <= 2887778303) return false;
			if ($ip >= 3221225984 && $ip <= 3221226239) return false;
			if ($ip >= 3232235520 && $ip <= 3232301055) return false;
			if ($ip >= 4294967040) return false;
		}
		return true;
	}
	
	/*
	* Get currency
	*/
	public function getcurrency($request){
		$list = array(
			'currency'              => get_woocommerce_currency(),
			'currency_symbol'       => get_woocommerce_currency_symbol(),
			'currency_position'     => get_option( 'woocommerce_currency_pos' ),
			'thousand_separator'    => wc_get_price_thousand_separator(),
			'decimal_separator'     => wc_get_price_decimal_separator(),
			'number_of_decimals'    => wc_get_price_decimals(),
		);
		return $list;
	}
		
	/*
	* Get getdealofday
	*/
	public function getdealofday($request){		
		$parameters = $request->get_params();
		$hideoutofstock = get_option('woocommerce_hide_out_of_stock_items');	
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];		
		$post_order_page = $parameters['post_order_page'];						
		$args = array(
			'posts_per_page'   => $post_per_page,
			'paged'   		   => $post_num_page,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'post_modified',
			'order'            => $post_order_page,
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '_wooconnector_data-dod',
			'meta_value'       => 1,
			'post_type'        => 'product',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   	   => '',
			'author_name'	   => '',
			'meta_query'	   => array(),
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);	
		if($hideoutofstock == 'yes'){
			array_push($args['meta_query'],array(
					'key' => '_stock_status',
					'value' => 'outofstock',
					'compare' => '!='
				)
			);
		}	
		$listexcatalog = $this->getproduct_exclude_from_catalog();
		if(!empty($listexcatalog)){
			if(empty($args['post__in'])){				
				$args['post__not_in'] = $listexcatalog;
			}else{
				$include = array_diff($args['post__in'],$listexcatalog);
				$args['post__in'] = $include;
			}
		}			
		if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			$ids = self::$id_with_product;
			if(empty($ids)){
				return array();
			}
			if(!empty($args['post__in'])){
				$list_post_id = array();
				foreach($ids as $post_id){
					if(in_array($post_id,$args['post__in'])){
						$list_post_id[] = $post_id;
					}
				}
				$args['post__in'] = $list_post_id;
			}else{
				$args['post__in'] = $ids;
			}
		}
		$query = new WP_Query($args);
		$products = $query->posts;
		$list = array();
		foreach($products as $product){		
			$list[] = $this->get_data($product,$request);
		}
		if(empty($list)){
			return array();
		}		
		return $list;
	}
		
	public function getnewcomment($request){
		$parameters = $request->get_params();	
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$rownum = ($post_num_page - 1) * $post_per_page;		
		$post_order_page = $parameters['post_order_page'];			
		$argscomment = array(			
			'status' => 'approve',
			'orderby' => 'comment_date_gmt',
			'order' => $post_order_page,
			'number' => $post_per_page,
			'offset' => $rownum
		);
		$product_id = isset($parameters['product_id']) ? $parameters['product_id'] : false;
		$total_reviews = false;
		if(!empty($product_id)){
			$product = wc_get_product($product_id);
			$getpost = get_post($product_id);
			if(!is_object($product) || !empty($product) && $product->get_id() <= 0 || empty($product) || !empty($getpost) && $getpost->post_status == 'trash'){
				return array();
			}else{
				$argscomment['post_id'] = $product_id;
				$total_reviews = $product->get_review_count($product);
			}
		}
		$checkuser = '';
		//lay tat ca gia tri comment
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( $argscomment );
		if( !empty($comments)){
		$listcomment = array();
			$a = array();
			foreach($comments as $comment){					
				$cmentId = $comment->comment_ID;
				$rating = get_comment_meta( $cmentId, 'rating', true );
				$email = $comment->comment_author_email;
				$name = $comment->comment_author;
				$userid = $comment->user_id;
				$parent = $comment->comment_parent;
				$daten = new DateTime($comment->comment_date);
				$dategmtn = new DateTime($comment->comment_date_gmt);
				$date = $daten->format('Y-m-d\TH:i:s');			
				$gmt = $dategmtn->format('Y-m-d\TH:i:s');	
				if($userid > 0){
					$avatar = get_user_meta($userid,'mobiconnector-avatar',true);
					$avatar = wp_get_attachment_url($avatar);
					if(empty($avatar) && !empty($email)){
						$avatar = $this->get_gravatar_url($email);
					}elseif(empty($avatar) && empty($email)){
						$avatar = get_avatar_url($userid);
					}
					$listcomment[] = array(
						'user' => $name,
						'link_avatar' => $avatar,
						'comment_content' => stripslashes(html_entity_decode($comment->comment_content)),
						'comment_date' => $date,
						'comment_parent' => $parent,
						'comment_date_gmt' => $gmt,
						'rating' => $rating,
						'total_comments' => $total_reviews
					);
				}else{
					if($name !== '' || !empty($name)){
						$listcomment[] = array(
							'user' => $name,
							'link_avatar' => $this->get_gravatar_url($email),
							'comment_content' => stripslashes(html_entity_decode($comment->comment_content)),
							'comment_date' => $date,
							'comment_parent' => $parent,
							'comment_date_gmt' => $gmt,
							'rating' => $rating,
							'total_comments' => $total_reviews								
						);
					}else{
						$listcomment[] = array(
							'user' => 'Anonymous',
							'link_avatar' => $this->get_gravatar_url($email),
							'comment_content' => stripslashes(html_entity_decode($comment->comment_content)),
							'comment_date' => $date,
							'comment_parent' => $parent,
							'comment_date_gmt' => $gmt,
							'rating' => $rating,
							'total_comments' => $total_reviews									
						);
					}
				}
			}	
		}else{
			return array();
		}	
		return $listcomment;
	}	

	/**
     * Get gravatar url with email
     * 
     * @param string $email    email get gravatar
     * 
     * @return string link of gravatar
     */
    private function get_gravatar_url( $email ) {
        $id_default = get_option('avatar_default');
        $ratting = strtolower(get_option('avatar_rating'));
        $hash = md5( strtolower( trim ( $email ) ) );
        return 'http://gravatar.com/avatar/' . $hash . '?s=96&d='.$id_default.'&r='.$ratting;
    }
	
	protected function get_data($product,$request){
		$wpml_product_ids = self::$id_with_product;
		$wpml_product_variation_ids = self::$id_with_product_variation;
		$wpml_ids_variation = self::$id_with_product_variation;
		$wpml_ids_group = array_merge($wpml_product_ids,$wpml_product_variation_ids);
		$idWPML = array(
			'id_variation' => $wpml_ids_variation,
			'id_group' => $wpml_ids_group
		);
		$params = $request->get_params();
		$id = $product->ID;
		$datastore = new WooConnectorDataProducts();
		$result = $datastore->wooconnector_data($id,$request,false,$idWPML);
		$result = apply_filters('wooconnector_product_data',$result,$request);
		if(isset($params['id']) || isset($params['product_id'])){
			$result = apply_filters('wooconnector_product_data_id',$result,$request);
		}
		return $result;
	}

	protected function getTheCategories($item,$request){
		$display_type = get_woocommerce_term_meta( $item->term_id, 'display_type' );

		// Get category order.
		$menu_order = get_woocommerce_term_meta( $item->term_id, 'order' );

		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => apply_filters( 'mobiconnector_languages',$item->name),
			'slug'        => $item->slug,
			'parent'      => (int) $item->parent,
			'description' => apply_filters( 'mobiconnector_languages_content',$item->description),
			'display'     => $display_type ? $display_type : 'default',
			'image'       => array(),
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $item->count,
		);

		// Get category image.
		if ( $image_id = get_woocommerce_term_meta( $item->term_id, 'thumbnail_id' ) ) {
			$attachment = get_post( $image_id );

			$data['image'] = array(
				'id'                => (int) $image_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment->post_date ),
				'date_created_gmt'  => wc_rest_prepare_date_response( $attachment->post_date_gmt ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment->post_modified ),
				'date_modified_gmt' => wc_rest_prepare_date_response( $attachment->post_modified_gmt ),
				'src'               => wp_get_attachment_url( $image_id ),
				'title'             => get_the_title( $attachment ),
				'alt'               => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
			);
		}
		$data = apply_filters('wooconnector_product_categories_data',$data);	
		$params = $request->get_params();
		if(isset($params['menu']) && $params['menu'] === 1 && isset($params['parent']) && $params['parent'] === 0){
			$data = apply_filters('wooconnector_product_categories_data_details',$data,$request);
		}
		return $data;
	}

	protected function getTheBrands($item){
		$display_type = get_woocommerce_term_meta( $item->term_id, 'display_type' );

		// Get category order.
		$menu_order = get_woocommerce_term_meta( $item->term_id, 'order' );

		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => apply_filters( 'mobiconnector_languages',$item->name),
			'slug'        => $item->slug,
			'parent'      => (int) $item->parent,
			'description' => apply_filters( 'mobiconnector_languages_content',$item->description),
			'display'     => $display_type ? $display_type : 'default',
			'image'       => array(),
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $item->count,
		);

		// Get category image.
		if ( $image_id = get_woocommerce_term_meta( $item->term_id, 'wooconnector_brand_avatar' ) ) {
			$attachment = get_post( $image_id );

			$data['image'] = array(
				'id'                => (int) $image_id,
				'date_created'      => wc_rest_prepare_date_response( $attachment->post_date ),
				'date_created_gmt'  => wc_rest_prepare_date_response( $attachment->post_date_gmt ),
				'date_modified'     => wc_rest_prepare_date_response( $attachment->post_modified ),
				'date_modified_gmt' => wc_rest_prepare_date_response( $attachment->post_modified_gmt ),
				'src'               => wp_get_attachment_url( $image_id ),
				'title'             => get_the_title( $attachment ),
				'alt'               => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
			);
		}
		$data = apply_filters('wooconnector_product_brands_data',$data);	
		return $data;
	}
}
$WooConnectorProduct = new WooConnectorProduct();
?>