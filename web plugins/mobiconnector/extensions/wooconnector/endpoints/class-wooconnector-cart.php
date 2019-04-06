<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Main Class of Cart in Application
 */
class WooConnectorCalculator{

	/**
	 * Total of cart
	 */
	private $total = array();

	/**
	 * Shipping of cart
	 */
	private $shipping = array();

	/**
	 * Counpon of cart
	 */
	private $coupons = array();

	/**
	 * Url of API
	 */
	private $rest_url = 'wooconnector/calculator';
	
	/**
	 * WooConnectorCalculator construct
	 */
	public function __construct() {
		$this->register_routes(); 
	}
	
	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	/**
	 * Create API for cart in Application
	 */
	public function register_api_hooks(){

		// Create API for get all information of checkout
		register_rest_route( $this->rest_url, '/getall', array(
			'methods' => 'GET',
			'callback' => array( $this, 'getall' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args' => array(
				'products' =>array(
					'required' => true
				),
				'coupons' => array(						
				),
				'address' => array(
					'sanitize_callback' => 'esc_sql'
				),
				'city' => array(
					'sanitize_callback' => 'esc_sql'
				),
				'country' => array(
					'required' => true,
					'sanitize_callback' => 'esc_sql'
				),
				'postcode' => array(
					'sanitize_callback' => 'esc_sql'
				),
				'states' => array(
					'sanitize_callback' => 'esc_sql'
				)
			),
		) );

		// Create API for get total of cart
		register_rest_route( $this->rest_url, '/gettotal', array(
			'methods' => 'GET',
			'callback' => array( $this, 'gettotal' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args' => array(
				'products' =>array(
					'required' => true
				),					
			),
		) );

		// Create API for get shipping of cart
		register_rest_route( $this->rest_url, '/getshipping', array(
			'methods' => 'GET',
			'callback' => array( $this, 'getshipping' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args' => array(					
				'postcode' => array(
					'sanitize_callback' => 'esc_sql'
				),
				'country' => array(
					'sanitize_callback' => 'esc_sql'
				),
				'states' => array(
					'sanitize_callback' => 'esc_sql'
				)
			),
		) );

		// Create API for get payment of checkout
		register_rest_route( $this->rest_url, '/getpayment', array(
			'methods' => 'GET',
			'callback' => array( $this, 'getpayment' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args' => array(					
			),
		) );
		
		// Create API for add coupons in cart
		register_rest_route( $this->rest_url, '/addcoupons', array(
			'methods' => 'POST',
			'callback' => array( $this, 'add_coupons' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
			'args' => array(
				'products' =>array(
					'required' => true
				),
				'coupons' => array(
				
				)
			),
		) );			
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
	 * GET total of cart in Application
	 * 
	 * @param WP_REST_Request $request  current Request
	 *                                  products is required in Request
	 * 
	 * @return mixed
	 */
	public function gettotal($request){
		$parameters = $request->get_params();
		$pro = $parameters['products'];	
		$list = array();
		if(!empty($pro)){
			$product = json_decode($pro);	
			foreach($product as $post){	
				$product_id = absint($post->product_id);			
				$quantity = $post->quantity;		
				$variation_id = !empty($post->variation_id) ? absint($post->variation_id) : false;
				$addons = !empty($post->addons) ? $post->addons : array();
				$error = $this->validateInput($product_id,$variation_id,$quantity);	
				if(!empty($error)){
					if($error['code'] !== 'rest_order_stock_error'){
						continue;
					}
				}
				$list[] = $this->getTotalnoCoupon($post,$request,$addons);	
			}
		}		
		return $list;
	}

	/**
	 * Get all payment method
	 * Support with WooCommerce COD Advandce plugin
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	public function getpayment($request){
		$gateways = WC()->payment_gateways->payment_gateways();
		$enable_for_methods = '';
		$params = $request->get_params();
		$products = array();
		$total = 0;
		$result = array();
		if(isset($params['products']) && !empty($params['products'])){
			$products = json_decode($params['products']);
			$totals = $this->coupons;
			if(isset($params['coupons']) && !empty($params['coupons'])){
				if(is_array($totals) && isset($totals['total']) && is_numeric($totals['total'])){
					$total = $totals['total'];
				}elseif(is_array($totals) && isset($totals['total']) && is_array($totals['total'])){
					$ts = $totals['total'];
					foreach($ts as $t){
						$total += $t['total'];
					}
				}else{
					return null;
				}
			}else{
				if(is_array($totals) && isset($totals['total'])){
					foreach($totals['total'] as $t){
						$total += $t['total'];
					}
				}else{
					return null;
				}
			}
		}	
		$shippings = $this->shipping;	
		$country = isset($params['country']) ? $params['country'] : false;
		$state = isset($params['states']) ? $params['states'] : false;
		$states = isset($state) ? $country.'_'.$state : false;
		$postcode = isset($params['postcode']) ? $params['postcode'] : false;
		$city = isset($params['city']) ? $params['city'] : false;
		foreach($gateways as $gateway){				
			$id = $gateway->id;
			if($id == 'eh_amazon_pay'){
				continue;
			}	
			if($gateway->enabled !== "yes"){
				continue;
			}	
			if(is_plugin_active('woocommerce-cod-advanced/woocommerce_advanced_cod.php') && $id == 'cod'){
				$settings = get_option('woocommerce_cod_settings');
				if($settings['disable_cod_adv'] == 'no'){
					$min_cod_amount = $settings['min_amount'];
					if(!empty($min_cod_amount) && !empty($total)){
						if($total < $min_cod_amount){
							continue;
						}
					}
					$max_cod_amount = $settings['max_amount'];
					if(!empty($max_cod_amount) && !empty($total)){
						if($total > $max_cod_amount){
							continue;
						}
					}
					$exclude_cats = $settings['exclude_cats'];
					if(!empty($exclude_cats) && !empty($products)){
						$terms = array();
						$checkterm = 0;
						foreach($products as $product){
							$pro = wc_get_product($product->product_id);
							$cats = wc_get_object_terms($pro->get_id(), 'product_cat');
							foreach($cats as $term){
								$terms[] = array(
									'id'   => $term->term_id,
									'name' => apply_filters('wp_list_categories',$term->name),
									'slug' => $term->slug,
								);
							}
						}
						foreach($terms as $term){
							if(in_array($term['id'],$exclude_cats)){
								$checkterm++;
							}
						}
						if($checkterm > 0){
							continue;
						}
					}
					$exclude_country = $settings['country'];
					$in_ex_country = $settings['in_ex_country'];
					if(!empty($exclude_country) && !empty($params['country'])){
						if($in_ex_country=='include'){
							$checkin = 0;
							if(in_array($params['country'],$exclude_country)){
								$checkin++;
							}
							if($checkin === 0){
								continue;
							}
						}elseif($in_ex_country == 'exclude'){
							if(in_array($params['country'],$exclude_country)){
								continue;
							}
						}
					}
					$exclude_states = $settings['states'];
					$in_ex_states = $settings['in_ex_states'];
					if(!empty($exclude_states) && !empty($params['states'])){
						if($in_ex_states=='include'){
							$checkin = 0;
							if(in_array($params['states'],$exclude_states)){
								$checkin++;
							}
							if($checkin === 0){
								continue;
							}
						}elseif($in_ex_states == 'exclude'){
							if(in_array($params['states'],$exclude_states)){
								continue;
							}
						}
					}
					$exclude_city = trim($settings['city']);
					$in_ex_city = $settings['in_ex_city'];
					if(!empty($exclude_city) && !empty($params['city'])){
						$exclude_city = explode(',',$exclude_city);
						if($in_ex_city=='include'){
							$checkin = 0;
							if(in_array($params['city'],$exclude_city)){
								$checkin++;
							}
							if($checkin === 0){
								continue;
							}
						}elseif($in_ex_city == 'exclude'){
							if(in_array($params['city'],$exclude_city)){
								continue;
							}
						}
					}
					$cod_pincodes = trim($settings['cod_pincodes']);
					$in_ex_pincode = $settings['in_ex_pincode'];
					if(!empty($cod_pincodes) && !empty($params['postcode'])){
						$cod_pincodes = explode(',',$cod_pincodes);
						if($in_ex_pincode=='include'){
							$checkin = 0;
							if(in_array($params['postcode'],$cod_pincodes)){
								$checkin++;
							}
							if($checkin === 0){
								continue;
							}
						}elseif($in_ex_pincode == 'exclude'){
							if(in_array($params['postcode'],$cod_pincodes)){
								continue;
							}
						}
					}
					$title = $gateway->title;
					$method = $gateway->method_title;
					$description = $gateway->description;
					$method_description = $gateway->method_description;
					if($id == 'cod'){
						$enable_for_methods = $gateway->enable_for_methods;
					}else{
						$enable_for_methods = null;
					}
					$result[] = array(
						'id' => $id,
						'title' => $title,
						'method_title' => $method,
						'description' => $description,
						'method_description' => $method_description,
						'enable_for_methods' => $enable_for_methods
					);
				}else{
					$title = $gateway->title;
					$method = $gateway->method_title;
					$description = $gateway->description;
					$method_description = $gateway->method_description;
					if($id == 'cod'){
						$enable_for_methods = $gateway->enable_for_methods;
					}else{
						$enable_for_methods = null;
					}
					$result[] = array(
						'id' => $id,
						'title' => $title,
						'method_title' => $method,
						'description' => $description,
						'method_description' => $method_description,
						'enable_for_methods' => $enable_for_methods
					);
				}
			}elseif(is_plugin_active('wc-smart-cod/wc-smart-cod.php') && $id == 'cod'){
				$settings = get_option('woocommerce_cod_settings');
				$restriction_settings = json_decode($settings['restriction_settings']);
				$fee_settings = json_decode($settings['fee_settings']);
				$country_restrictions = $settings['country_restrictions'];
				$shipping_zone_restrictions = $settings['shipping_zone_restrictions'];
				if($restriction_settings->shipping_zone_restrictions == 1){
					if(!empty($shipping_zone_restrictions)){
						$checkshipping = 0;
						if(!empty($shippings)){
							foreach($shippings as $shipping){
								if(!in_array($shipping['zone_id'],$shipping_zone_restrictions)){
									$checkshipping++;
								}
							}
							if($checkshipping > 0){
								continue;
							}
						}
					}
				}else{
					if(!empty($shipping_zone_restrictions)){
						$checkshipping = 0;
						if(!empty($shippings)){
							foreach($shippings as $shipping){
								if(!in_array($shipping['zone_id'],$shipping_zone_restrictions)){
									$checkshipping++;
								}
							}
							if($checkshipping === 0){
								continue;
							}
						}
					}
				}
				$country_restrictions = $settings['country_restrictions'];
				if($restriction_settings->country_restrictions == 1){
					if(!empty($country_restrictions) && !empty($country) && !in_array($country,$country_restrictions)){						
						continue;
					}
				}else{
					if(!empty($country_restrictions) && !empty($country) && in_array($country,$country_restrictions)){
						continue;
					}
				}
				$state_restrictions = $settings['state_restrictions'];
				if($restriction_settings->state_restrictions == 1){
					if(!empty($state_restrictions) && !empty($state) && !in_array($states,$state_restrictions)){
						continue;
					}
				}else{
					if(!empty($state_restrictions) && !empty($state) && in_array($states,$state_restrictions)){
						continue;
					}
				}
				$restrict_postals = $settings['restrict_postals'];
				if($restriction_settings->restrict_postals == 1){
					if(!empty($restrict_postals)){
						if(!empty($postcde) && !$this->checkpostcodePaymentSmart($restrict_postals,$postcode)){
							continue;
						}
					}
				}else{
					if(!empty($restrict_postals)){
						if(!empty($postcde) && $this->checkpostcodePaymentSmart($restrict_postals,$postcode)){
							continue;
						}
					}
				}
				$city_restrictions = $settings['city_restrictions'];
				if($restriction_settings->city_restrictions == 1){
					if(!empty($city_restrictions)){
						$city_restrictions = trim($city_restrictions,',');
						$city_restrictions = explode(',',$city_restrictions);
						if(!empty($city) && !empty($city_restrictions) && !in_array($city,$city_restrictions)){
							continue;
						}
					}
				}else{
					if(!empty($city_restrictions)){
						$city_restrictions = trim($city_restrictions,',');
						$city_restrictions = explode(',',$city_restrictions);
						if(!empty($city) && !empty($city_restrictions) && in_array($city,$city_restrictions)){
							continue;
						}
					}
				}
				$cart_amount_restriction = $settings['cart_amount_restriction'];
				if($restriction_settings->cart_amount_restriction == 1){
					if(!empty($cart_amount_restriction)){
						if($total < $cart_amount_restriction){
							continue;
						}
					}
				}else{
					if(!empty($cart_amount_restriction)){
						if($total > $cart_amount_restriction){
							continue;
						}
					}					
				}
				$user_role_restriction = $settings['user_role_restriction'];
				$current_user_id = get_current_user_id();
				$user_info = get_userdata($current_user_id);
				$user_roles = 'guest';
				if(!empty($user_info)){
					$user_roles = implode(', ',$user_info->roles);
				}
				if($restriction_settings->user_role_restriction == 1){
					$checkcan = 0;
					if(!empty($user_role_restriction)){
						if(strpos($user_roles,',') !== false){
							$checkroles = 0;
							foreach($user_roles as $r){
								if(in_array($r,$user_role_restriction)){
									$checkroles++;
								}
							}
							if($checkroles == 0){
								$checkcan++;
							}
						}else{
							if(!in_array($user_roles,$user_role_restriction)){
								$checkcan++;
							}
						}
						if($checkcan > 0){
							continue;
						}
					}
				}else{
					$checkcan = 0;
					if(!empty($user_role_restriction)){
						if(strpos($user_roles,',') !== false){
							$checkroles = 0;
							foreach($user_roles as $r){
								if(in_array($r,$user_role_restriction)){
									$checkroles++;
								}
							}
							if($checkroles != 0){
								$checkcan++;
							}
						}else{
							if(in_array($user_roles,$user_role_restriction)){
								$checkcan++;
							}
						}
						if($checkcan > 0){
							continue;
						}
					}
				}
				$category_restriction_mode = $settings['category_restriction_mode'];
				$category_restriction = $settings['category_restriction'];
				if($restriction_settings->category_restriction == 1){
					if(!empty($category_restriction)){
						$checkcancat = 0;
						$categories = $this->get_categories_with_products($products);
						if(!empty($categories)){
							foreach($categories as $cat){
								if(!in_array($cat,$category_restriction)){
									$checkcancat++;
								}
							}
							if($category_restriction_mode == 'one_product'){
								if($checkcancat > 0){
									continue;
								}
							}elseif($category_restriction_mode == 'all_product'){
								if($checkcancat == count($categories)){
									continue;
								}
							}
						}
					}	
				}else{
					if(!empty($category_restriction)){
						$checkcancat = 0;
						$categories = $this->get_categories_with_products($products);
						if(!empty($categories)){
							foreach($categories as $cat){
								if(in_array($cat,$category_restriction)){
									$checkcancat++;
								}
							}
							if($category_restriction_mode == 'one_product'){
								if($checkcancat > 0){
									continue;
								}
							}elseif($category_restriction_mode == 'all_products'){
								if($checkcancat == count($categories)){
									continue;
								}
							}
						}
					}	
				}
				$product_restriction_mode = $settings['product_restriction_mode'];
				$product_restriction = $settings['product_restriction'];
				if($restriction_settings->product_restriction == 1){
					if(!empty($product_restriction)){
						if($product_restriction_mode == 'one_product'){
							if(!$this->check_if_product_exclude($products,$product_restriction,false)){
								continue;
							}
						}elseif($product_restriction_mode == 'all_products'){
							if(!$this->check_if_product_exclude($products,$product_restriction)){
								continue;
							}
						}
					}
				}else{
					if(!empty($product_restriction)){
						if($product_restriction_mode == 'one_product'){
							if($this->check_if_product_exclude($products,$product_restriction,false)){
								continue;
							}
						}elseif($product_restriction_mode == 'all_products'){
							if($this->check_if_product_exclude($products,$product_restriction)){
								continue;
							}
						}
					}
				}
				$shipping_class_restriction_mode = $settings['shipping_class_restriction_mode'];
				$shipping_class_restriction = $settings['shipping_class_restriction'];
				if($restriction_settings->shipping_class_restriction == 1){
					if(!empty($shipping_class_restriction)){
						if($shipping_class_restriction_mode == 'one_product'){
							if(!$this->check_if_product_exclude($products,$shipping_class_restriction,false)){
								continue;
							}
						}elseif($shipping_class_restriction_mode == 'all_products'){
							if(!$this->check_if_product_exclude($products,$shipping_class_restriction)){
								continue;
							}
						}
					}
				}else{
					if(!empty($shipping_class_restriction)){
						if($shipping_class_restriction_mode == 'one_product'){
							if($this->check_if_product_exclude($products,$shipping_class_restriction,false)){
								continue;
							}
						}elseif($shipping_class_restriction_mode == 'all_products'){
							if($this->check_if_product_exclude($products,$shipping_class_restriction)){
								continue;
							}
						}
					}
				}
				$shipping_zone_method_restriction = $settings['shipping_zone_method_restriction'];
				if($restriction_settings->shipping_zone_method_restriction == 1){
					if(!empty($shipping_zone_method_restriction)){
						$checkshipping = 0;
						if(!empty($shippings)){
							foreach($shippings as $shipping){
								if(!in_array($shipping['zone_method'],$shipping_zone_method_restriction)){
									$checkshipping++;
								}
							}
							if($checkshipping > 0){
								continue;
							}
						}
					}
				}else{
					if(!empty($shipping_zone_method_restriction)){
						$checkshipping = 0;
						if(!empty($shippings)){
							foreach($shippings as $shipping){
								if(in_array($shipping['zone_method'],$shipping_zone_method_restriction)){
									$checkshipping++;
								}
							}
							if($checkshipping > 0){
								continue;
							}
						}
					}
				}
				$title = $gateway->title;
				$method = $gateway->method_title;
				$description = $gateway->description;
				$method_description = $gateway->method_description;
				if($id == 'cod'){
					$enable_for_methods = $gateway->enable_for_methods;
				}else{
					$enable_for_methods = null;
				}
				$result[] = array(
					'id' => $id,
					'title' => $title,
					'method_title' => $method,
					'description' => $description,
					'method_description' => $method_description,
					'enable_for_methods' => $enable_for_methods
				);
			}else{
				$title = $gateway->title;
				$method = $gateway->method_title;
				$description = $gateway->description;
				$method_description = $gateway->method_description;
				if($id == 'cod'){
					$enable_for_methods = $gateway->enable_for_methods;
				}else{
					$enable_for_methods = null;
				}
				$result[] = array(
					'id' => $id,
					'title' => $title,
					'method_title' => $method,
					'description' => $description,
					'method_description' => $method_description,
					'enable_for_methods' => $enable_for_methods
				);
			}				
		}
		return $result;
	}

	/**
	 * Get All categories with product input
	 * 
	 * @param array $product   list products
	 */
	public function get_categories_with_products($products){
		if(!empty($products)){
			$listcategories = array();
			foreach($products as $product){
				$pros = wc_get_product($product->product_id);
				$listcategories[] = implode(', ',$pros->get_category_ids());				
			}			
			$categories = array_unique($listcategories);
			return $categories;
		}
		return array();
	}

	/**
	 * Check if products exclude
	 * 
	 * @param array $product   list products
	 * @param array $product_restriction list check
	 */
	public function check_if_product_exclude($products,$product_restriction,$all = true){
		if(!empty($products)){
			$checked = 0;
			foreach($products as $product){
				$product_id = $product->product_id;
				$variation_id = $product->variation_id;
				if(in_array($product_id,$product_restriction) || in_array($variation_id,$product_restriction)){
					$checked++;
				}
				if(!$all && $checked > 0){
					return true;
				}
			}
			if($all && $checked == count($products)){
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Check if shipping class exclude
	 * 
	 * @param array $product   list products
	 * @param array $shipping_class_restriction list check
	 */
	public function check_if_shippingclass_exclude($products,$shipping_class_restriction,$all = true){
		if(!empty($products)){
			$checked = 0;
			foreach($products as $product){
				$class_shipping_id = $product->class_shipping_id;
				if(in_array($class_shipping_id,$shipping_class_restriction)){
					$checked++;
				}
				if(!$all && $checked > 0){
					return true;
				}
			}
			if($all && $checked == count($products)){
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Check postcode Payment Smart COD
	 * 
	 * @param string $restrict_postals  list postcode
	 * @param string $postcode   postcode
	 * @param string $head       head of list
	 * @param string $foot       end of list
	 */
	public function checkpostcodePaymentSmart($restrict_postals,$postcode,$head,$foot){
		$head = substr($restrict_postals,0,strpos($restrict_postals,"..."));
		$foot = substr($restrict_postals,strpos($restrict_postals,"...")+3);
		if(strpos($head,',') !== false){
			$listheads = trim($head,',');
			$listheads = implode(',',$head);
			if(in_array($postcode,$listheads)){
				return true;
			}
		}elseif(is_numeric($head) && is_numeric($foot)){
			if($postcode >= $head && $postcode <= $foot){
				return true;			
			}
		}
	}

	/**
	 * Get all total, shipping method, payment method
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	public function getall($request){
		try
		{
			$params = $request->get_params();
			$country = $params['country'];
			$ct = new WC_Countries();
			$coutries = $ct->get_countries();
			$woocommerce_ship_to_countries = get_option('woocommerce_ship_to_countries');
			if($woocommerce_ship_to_countries == 'specific'){
				$woocommerce_specific_ship_to_countries = get_option('woocommerce_specific_ship_to_countries');
				if(!empty($woocommerce_specific_ship_to_countries)){
					if(!in_array($country,$woocommerce_specific_ship_to_countries)){
						$coun = $coutries[$country];
						$error = new WP_Error( 'rest_country_error', sprintf(__( 'Sorry, Unfortunately we do not ship to %s. Please enter an alternative shipping address.','wooconnector' ),$coun), array( 'status' => 401 ));
						return $error;
					}
				}
			}
			$total = $this->gettotal($request);
			$this->total = $total;
			$coupon = $this->add_coupons($request);	
			$this->coupons = $coupon;
			$shipping = $this->getShippingPriceAndTax($request);
			$this->shipping = $shipping;
			$payment = $this->getpayment($request);
			$listall = array(
				'total' => $coupon,
				'shipping' => $shipping,
				'payment' => $payment					
			);			
			return apply_filters('wooconnector_get_all_in_checkout',$listall,$coupon,$shipping,$payment);		
		}
		catch(Exception $e){
			return new WP_Error( 'calculator-error', $e->getMessage() );
		}
	}

	/**
	 * GET all shipping if appropriate requirements
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 	 
	 * @return mixed
	 */
	public function getshipping($request){
		$parameters = $request->get_params();
		$country = (isset($parameters['country'])) ? strtoupper($parameters['country']) : false;		
		$postcode = (isset($parameters['postcode'])) ? $parameters['postcode'] : false;
		$states = (isset($parameters['states'])) ? $parameters['country']. ":". $parameters['states'] : false;
		$checkwooshipping = get_option('woocommerce_ship_to_countries');		
		$checkwoospecials = get_option('woocommerce_specific_ship_to_countries');
		$checkspecial = 0;
		if($checkwooshipping == 'specific' && empty($checkwoospecials)){			
			return null;
		}elseif($checkwooshipping == 'specific' && !empty($checkwoospecials)){
			foreach($checkwoospecials as $special){
				if($special == $country){
					$checkspecial++;
				}
			}
		}
		if($checkwooshipping == 'specific' && $checkspecial === 0){
			return null;
		}
		//get all zones shipping in woocommerce
		$zones = WC_Shipping_Zones::get_zones();
		if(!empty($zones)){
			krsort($zones);
			$count = count($zones);
			$listmethods = array();
			$listzone_id = array();
			$checknotexist = array();		
			foreach($zones as $zone){	
				if(empty($zone)){
					continue;
				}
				$ship = $zone['formatted_zone_location'];
				$zonelocations = $zone['zone_locations'];
				$listzone = array();
				$listpost = array();
				$liststate = array();
				$listcontinent = array();
				if(empty($zonelocations) && empty($ship)){
					continue;
				}	
				foreach($zonelocations as $zonelocation){
					if($zonelocation->type == 'country'){
						$listzone[] = $zonelocation->code;
					}
					if($zonelocation->type == 'postcode'){
						$listpost[] = $zonelocation->code;
					}	
					if($zonelocation->type == 'state'){
						$liststate[] = $zonelocation->code;
					}	
					if($zonelocation->type == 'continent'){
						$listcontinent[] = $zonelocation->code;
					}
				}	
				$name = $zone['zone_name'];	
				$zoneid = $zone['zone_id'];	
				if(isset($parameters['country'])){	
					if(!empty($listzone)){
						if (in_array($country,$listzone)){							
							$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
							$listmethods = $shippingaftermethod['listmethods'];
							$checknotexist = $shippingaftermethod['checknotexist'];
							$listzone_id = $shippingaftermethod['listzone_id'];						
						}elseif(!empty($listpost) && !empty($liststate)){
							if(empty($states) || empty($postcode)){
								continue;
							}
							if(in_array($states,$liststate) && $this->checkInPostcode($listpost,$postcode)){
								$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
								$listmethods = $shippingaftermethod['listmethods'];
								$checknotexist = $shippingaftermethod['checknotexist'];
								$listzone_id = $shippingaftermethod['listzone_id'];
							}else{
								continue;
							}
						}elseif(!empty($liststate) && empty($listpost)){
							if(empty($states)){
								continue;
							}
							if(in_array($states,$liststate)){
								$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
								$listmethods = $shippingaftermethod['listmethods'];
								$checknotexist = $shippingaftermethod['checknotexist'];
								$listzone_id = $shippingaftermethod['listzone_id'];
							}else{
								continue;
							}
						}elseif(!empty($listpost) && empty($liststate)){
							if(empty($postcode)){
								continue;
							}			
							if($this->checkInPostcode($listpost,$postcode)){
								$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id,$postcode,$listpost,true);
								$listmethods = $shippingaftermethod['listmethods'];
								$checknotexist = $shippingaftermethod['checknotexist'];
								$listzone_id = $shippingaftermethod['listzone_id'];
							}else{
								continue;
							}		
						}else{
							continue;
						}
					}elseif(empty($listzone) && !empty($listpost) && !empty($liststate)){
						if(empty($states) || empty($postcode)){
							continue;
						}
						if(in_array($states,$liststate) && $this->checkInPostcode($listpost,$postcode)){	
							$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);							
							$listmethods = $shippingaftermethod['listmethods'];
							$checknotexist = $shippingaftermethod['checknotexist'];
							$listzone_id = $shippingaftermethod['listzone_id'];
						}else{
							continue;
						}			
					}elseif(empty($listzone) && !empty($liststate) && empty($listpost)){
						if(empty($states)){
							continue;
						}
						if(in_array($states,$liststate)){
							$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
							$listmethods = $shippingaftermethod['listmethods'];
							$checknotexist = $shippingaftermethod['checknotexist'];
							$listzone_id = $shippingaftermethod['listzone_id'];
						}else{
							continue;
						}
					}elseif(empty($listzone) && !empty($listpost) && empty($liststate)){	
						if(empty($postcode)){
							continue;
						}						
						if($this->checkInPostcode($listpost,$postcode)){	
							$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id,$postcode,$listpost,true);
							$listmethods = $shippingaftermethod['listmethods'];
							$checknotexist = $shippingaftermethod['checknotexist'];
							$listzone_id = $shippingaftermethod['listzone_id'];
						}else{
							continue;
						}
					}else{
						$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
						$listmethods = $shippingaftermethod['listmethods'];
						$checknotexist = $shippingaftermethod['checknotexist'];
						$listzone_id = $shippingaftermethod['listzone_id'];
					}
				}else{
					
					$shippingaftermethod = $this->getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id);
					$listmethods = $shippingaftermethod['listmethods'];
					$checknotexist = $shippingaftermethod['checknotexist'];
					$listzone_id = $shippingaftermethod['listzone_id'];
				}
			}
			global $wpdb;
			$table = $wpdb->prefix."terms";	
			$intable = $wpdb->prefix."term_taxonomy";			
			$shippingsclass = $wpdb->get_results("
				SELECT * FROM $table as t INNER JOIN $intable as taxo ON t.term_id = taxo.term_id  WHERE taxonomy = 'product_shipping_class'
			");	
			if(empty($listmethods)){
				$default = new WC_Shipping_Zone(0);
				$default_name = $default->get_zone_name();
				$default_methods = $default->get_shipping_methods();
				foreach($default_methods as $default_method){				
					$instance_settings = $default_method->instance_settings;
					$id = $default_method->id;
					$idinstance = $default_method->instance_id;
					$listmethods[] = array(
						'id' => $id.':'.$idinstance,
						'country' => array('default'),
						'states' => array('default'),
						'format_zone' => 'default',
						'postcode' => array('default'),
						'name' => $default_name,
						'enabled' => $default_method->enabled,
						'instance_settings' => $instance_settings,
						'zone_id' => 0,
						'zone_method' => '0_0'
					);	
					
				}
			}
			if(!empty($listmethods)){
				$list = array();
				$instance_settings = array();
				foreach($listmethods as $listmethod){
					if(isset($listmethod['instance_settings'])){
						$instance_settings = $listmethod['instance_settings'];
						$id = $listmethod['id'];
						$name = $listmethod['name'];								
						if(strpos($id, 'flat_rate')!== false || strpos($id, 'local_pickup')!== false){
							$coststring = $instance_settings['cost'];				
							$cost = $coststring;				
						}
						else{
							$cost = 0;
						}	
						$classshipping = array();
						if(!empty($shippingsclass)){
							foreach($shippingsclass as $shippingclass){
								$index = 'class_cost_'.$shippingclass->term_id;				
								if(isset($instance_settings[$index])){
									$classshipping[$shippingclass->term_id] = $instance_settings[$index];
								}
							}	
						}				
						$list[] = array(
							'id' => $id,
							'name' => $name,
							'enabled' => $listmethod['enabled'],
							'country' => $listmethod['country'],
							'states' => $listmethod['states'],
							'postcode' => $listmethod['postcode'],
							'format_zone' => $listmethod['format_zone'],
							'title' => isset($instance_settings['title']) ? $instance_settings['title'] : "",					
							'class_shipping' => !empty($classshipping) ? $classshipping : "",
							'no_class_cost' => isset($instance_settings['no_class_cost']) ? $instance_settings['no_class_cost'] : "",
							'tax_status' => isset($instance_settings['tax_status']) ? $instance_settings['tax_status'] : "",
							'cost' => isset($instance_settings['cost']) ? $cost : 0,
							'requires'=> isset($instance_settings['requires']) ? $instance_settings['requires'] : "",
							'min_amount'=> isset($instance_settings['min_amount']) ? $instance_settings['min_amount'] : "",
							'zone_id' => $listmethod['zone_id'],
							'zone_method' => $listmethod['zone_method']
						);
					}else{
						continue;
					}				
				}			
				return apply_filters('wooconnector_get_shipping',$list,$country,$postcode,$states);	
			}
		}else{
			return null;
		}
	}

	/**
	 * Get detail shipping in method
	 * 
	 * @param array   $zone           zone of shipping
	 * @param array   $listmethods    list result of shipping
	 * @param array   $checknotexist  check shipping method duplicate
	 * @param array   $listzone_id    get method of one zone
	 * @param string  $postcode       postcode when check, entered by the user
	 * @param array   $listpost       list condition postcode of zone
	 * @param boolean $checkpost      check postcode or not
	 * 
	 * @return array list result shipping, list check shipping method duplicate, list exist zone
	 */
	private function getShippingbyMethod($zone,$listmethods,$checknotexist,$listzone_id,$postcode = '',$listpost = array(),$checkpost = false){
		$zoneid = $zone['zone_id'];
		if($checkpost){
			$listshippingafterpostcode = $this->getShippingbyPostcode($zone,$listmethods,$listpost,$postcode,$checknotexist,$listzone_id);
			$listmethods = $listshippingafterpostcode['listmethods'];
			$checknotexist = $listshippingafterpostcode['checknotexist'];
			$listzone_id = $listshippingafterpostcode['listzone_id'];
		}else{
			if(empty($listmethods)){
				$methods = $zone['shipping_methods'];
				$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
				$listmethods = $methodsafter['listmethods'];
				$checknotexist = $methodsafter['checknotexist'];
				array_push($listzone_id,$zoneid);
			}else{
				if(in_array($zoneid,$listzone_id)){
					$methods = $zone['shipping_methods'];
					$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
					$listmethods = $methodsafter['listmethods'];
					$checknotexist = $methodsafter['checknotexist'];
				}
			}
		}	
		$content = 	array(
			'listmethods' => $listmethods,
			'checknotexist' => $checknotexist,
			'listzone_id' => $listzone_id
		);
		return apply_filters('wooconnector_get_shipping_method',$content,$zone,$postcode,$listpost);
	}

	/**
	 * Check postcode in listpostcode or no
	 * 
	 * @param array   $listpost       list condition postcode of zone
	 * @param string  $postcode       postcode when check, entered by the user
	 * 
	 * @return boolean
	 */
	private function checkInPostcode($listpost,$postcode){
		$checkisset = 0;
		foreach($listpost as $postc){
			if($this->checkPostcodespecialstar($postc,$postcode)){
				$checkisset++;
			}elseif($this->checkPostcodeAboutNumber($postc,$postcode)){
				$checkisset++;
			}elseif($this->checkPostcodeAboutChar($postc,$postcode)){
				$checkisset++;
			}elseif($postcode == $postc){
				$checkisset++;
			}else{
				continue;
			}
		}
		$result = false;
		if($checkisset > 0){
			$result = true;
		}
		return apply_filters('wooconnector_check_postcode',$result,$listpost,$postcode);
	}

	/**
	 * Get all shipping by postcode entered by the user
	 * 
	 * @param array   $zone           zone of shipping
	 * @param array   $listmethods    list result of shipping
	 * @param array   $listpost       list condition postcode of zone
	 * @param string  $postcode       postcode when check, entered by the user
	 * @param array   $checknotexist  check shipping method duplicate
	 * @param array   $listzone_id    get method of one zone
	 * 
	 * @return array list result shipping, list check shipping method duplicate, list exist zone with postcode
	 */
	private function getShippingbyPostcode($zone,$listmethods,$listpost,$postcode,$checknotexist,$listzone_id){
		$zoneid = $zone['zone_id'];
		foreach($listpost as $postc){
			if($this->checkPostcodespecialstar($postc,$postcode)){
				if(empty($listmethods)){
					$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
					$listmethods = $methodsafter['listmethods'];
					$checknotexist = $methodsafter['checknotexist'];
					array_push($listzone_id,$zoneid);
				}else{
					if(in_array($zoneid,$listzone_id)){
						$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
						$listmethods = $methodsafter['listmethods'];
						$checknotexist = $methodsafter['checknotexist'];
					}
				}
			}elseif($this->checkPostcodeAboutNumber($postc,$postcode)){
				if(empty($listmethods)){
					$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
					$listmethods = $methodsafter['listmethods'];
					$checknotexist = $methodsafter['checknotexist'];
					array_push($listzone_id,$zoneid);
				}else{
					if(in_array($zoneid,$listzone_id)){
						$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
						$listmethods = $methodsafter['listmethods'];
						$checknotexist = $methodsafter['checknotexist'];
					}
				}
			}elseif($this->checkPostcodeAboutChar($postc,$postcode)){
				if(empty($listmethods)){				
					$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
					$listmethods = $methodsafter['listmethods'];
					$checknotexist = $methodsafter['checknotexist'];
					array_push($listzone_id,$zoneid);
				}else{
					if(in_array($zoneid,$listzone_id)){
						$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
						$listmethods = $methodsafter['listmethods'];
						$checknotexist = $methodsafter['checknotexist'];
					}
				}
			}elseif($postcode == $postc){
				if(empty($listmethods)){
					$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
					$listmethods = $methodsafter['listmethods'];
					$checknotexist = $methodsafter['checknotexist'];
					array_push($listzone_id,$zoneid);
				}else{
					if(in_array($zoneid,$listzone_id)){
						$methodsafter = $this->getShippingContentByMethod($zone,$checknotexist);
						$listmethods = $methodsafter['listmethods'];
						$checknotexist = $methodsafter['checknotexist'];
					}
				}
			}else{
				continue;
			}
		}
		$result = array(
			'listmethods' => $listmethods,
			'checknotexist' => $checknotexist,
			'listzone_id' => $listzone_id
		);
		return apply_filters('wooconnector_get_shipping_by_postcode',$result);
	}

	/**
	 * Get details shipping method
	 * 
	 * @param array $zone           zone of shipping method
	 * @param array $checknotexist  check shipping method duplicate
	 * 
	 * @return array list result shipping details, list check shipping method duplicate
	 */
	private function getShippingContentByMethod($zone,$checknotexist){
		$name = $zone['zone_name'];	
		$methods = $zone['shipping_methods'];
		$zonelocations = $zone['zone_locations'];
		$ship = $zone['formatted_zone_location'];
		$listzone = array();
		$listpost = array();
		$liststate = array();
		foreach($zonelocations as $zonelocation){
			if($zonelocation->type == 'country'){
				$listzone[] = $zonelocation->code;
			}
			if($zonelocation->type == 'postcode'){
				$listpost[] = $zonelocation->code;
			}	
			if($zonelocation->type == 'state'){
				$liststate[] = $zonelocation->code;
			}		
		}
		$listmethods = array();
		foreach($methods as $method){
			$id = $method->id;
			$idinstance = $method->instance_id;
			$idshipping = $id.':'.$idinstance;
			if(!in_array($idshipping,$checknotexist)){
				$instance_settings = $method->instance_settings;				
				$listmethods[] = array(
					'id' => $idshipping,
					'name' => $name,
					'country' => $listzone,
					'states' => $liststate,
					'format_zone' => $ship,
					'postcode' => $listpost,
					'enabled' => $method->enabled,
					'instance_settings' => $instance_settings,
					'zone_id' => $zone['zone_id'],
					'zone_method' => $zone['zone_id'].'_'.$idinstance
				);	
				array_push($checknotexist,$idshipping);				
			}
		}
		$content = array(
			'listmethods' => $listmethods,
			'checknotexist' => $checknotexist
		);
		return apply_filters('wooconnector_get_content_shipping',$content,$zone);
	}

	/**
	 * Check postcode with post code exist special char *
	 * 
	 * @param string $postc     condition of postcode
	 * @param string $postcode  postcode entered by the user
	 * 
	 * @return boolean
	 */
	private function checkPostcodespecialstar($postc,$postcode){
		if(strpos($postc,"*") !== false){
			$headpost = substr($postc,0,strpos($postc,"*"));
			if(preg_match('/^('.$headpost.')([a-zA-Z0-9 ]+)$/',$postcode)){			
				return true;			
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Check postcode with post code exist special char ... and codition is number
	 * 
	 * @param string $postc     condition of postcode
	 * @param string $postcode  postcode entered by the user
	 * 
	 * @return boolean
	 */
	private function checkPostcodeAboutNumber($postc,$postcode,$head = '',$foot = ''){
		$head = substr($postc,0,strpos($postc,"..."));
		$foot = substr($postc,strpos($postc,"...")+3);
		if(is_numeric($head) && is_numeric($foot)){
			if($postcode >= $head && $postcode <= $foot){
				return true;			
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Check postcode with post code exist special char ... and codition is char
	 * 
	 * @param string $postc     condition of postcode
	 * @param string $postcode  postcode entered by the user
	 * 
	 * @return boolean
	 */
	private function checkPostcodeAboutChar($postc,$postcode,$head = '',$foot = ''){
		$head = substr($postc,0,strpos($postc,"..."));
		$foot = substr($postc,strpos($postc,"...")+3);
		$regex = '/^[a-zA-Z0-9]+$/';
		$regexcheck = '/[0-9]+/';
		$regexreplace = '/[a-zA-Z]+/';
		if (preg_match($regex, $head) && preg_match($regex, $foot) && preg_match($regex, $postcode)) {
			$checkhead = trim(preg_replace($regexcheck,'',$head));
			$checkfoot = trim(preg_replace($regexcheck,'',$foot));
			$checkcode = trim(preg_replace($regexcheck,'',$postcode));
			if($checkhead == $checkfoot && $checkhead == $checkcode){
				$head = trim(preg_replace($regexreplace,'',$head));
				$foot = trim(preg_replace($regexreplace,'',$foot));
				$postcode = trim(preg_replace($regexreplace,'',$postcode));
				if($postcode >= $head && $postcode <= $foot){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * Get Price and Tax of Shipping in request
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	private function getShippingPriceAndTax($request){
		$parameters = $request->get_params();
		if(isset($parameters['woo_currency'])){
			$currentkey = $parameters['woo_currency'];					
		}else{
			$currentkey = strtolower(get_woocommerce_currency());
		}
		$country = isset($parameters['country']) ? $parameters['country'] : '';
		$states = isset($parameters['states']) ? $parameters['states'] : '';
		$postcode = isset($parameters['postcode']) ? $parameters['postcode'] : '';
		$city = isset($parameters['city']) ? $parameters['city'] : '';
		$address = isset($parameters['address']) ? $parameters['address'] : '';
		$address2 = isset($parameters['address_2']) ? $parameters['address_2'] : '';
		$currencys = WooConnectorGetCurrency($currentkey);
		$number_of_decimals = $currencys['number_of_decimals'];
		$ratecurrency = $currencys['rate'];
		$pro = $parameters['products'];				
		$product = json_decode($pro);
		$listproducts = $product;
		$checkship = get_option('woocommerce_ship_to_countries');
		// if enable shipping
		if($checkship != 'disabled'){
			WC()->cart->empty_cart();
			$attrs = array();
			foreach($listproducts as $prod){			
				$product_id = absint($prod->product_id);			
				$quantity = $prod->quantity;									
				$variation_id = isset($prod->variation_id) ? absint($prod->variation_id) : 0;	
				$attributes = isset($prod->attributes) ? $prod->attributes : array();	
				$addons = isset($prod->addons) ? $prod->addons : array();		
				if(!empty($variation_id) || $variation_id != '' || $variation_id != null || $variation_id !== 0){				
					if(!empty($attributes)){
						foreach($attributes as $key => $val){
							$attrs[$key] = $val;
						}
						WC()->cart->add_to_cart($product_id,$quantity,$variation_id,$attrs);
					}else{
						$wcvp = new WC_Product_Variable($product_id);
						$gavs = $wcvp->get_available_variations();
						foreach($gavs as $gav => $value){							
							if($value['variation_id'] == $variation_id){
								$attrs = $value['attributes'];						
								WC()->cart->add_to_cart($product_id,$quantity,$variation_id,$attrs);							
							}
						}
					}
				}				
				else{		
					WC()->cart->add_to_cart($product_id,$quantity,$variation_id,$attributes);						
				}	
			}
			if(!empty($parameters['coupons'])){
				$cou = $parameters['coupons'];
				$coupons = json_decode($cou);					
				foreach($coupons as $coupon){
					WC()->cart->add_discount($coupon);
				}	
			}
			$oldpackages = WC()->cart->get_shipping_packages();
			$packages = $oldpackages[0];
			$packages['destination']['country'] = $country;
			$packages['destination']['state'] = $states;
			$packages['destination']['postcode'] = $postcode;
			$packages['destination']['city'] = !empty($city) ? $city : $packages['destination']['city'];
			$packages['destination']['address'] = !empty($address) ? $address : $packages['destination']['address'];
			$packages['destination']['address_2'] = !empty($address2) ? $address2 : $packages['destination']['address_2'];
			$loading_packages = WC()->shipping->load_shipping_methods($packages);	
			
			$rates = array();
			foreach($loading_packages as $shipping_method){
				if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() ) {
					$rates[] = $shipping_method->get_rates_for_package( $packages ); 	
				}
			}
			$listshipping_add = array();
			if(!empty($rates)){
				foreach($rates as $key => $rate){
					if(empty($rate)){
						continue;
					}
					if(strpos($key,"flat_rate") !== false || strpos($key,"local_pickup") !== false || strpos($key,"free_shipping") !== false){
						continue;
					}else{
						$listshipping_add[$key] = $rate;
					}
				}
			}
			if(!empty($listshipping_add) && $listshipping_add != null){
				$taxs_shipping = new WC_Tax();
				$rates_shipping = $taxs_shipping->get_shipping_tax_rates();
				$label_shipping = 'Tax';				
				$keytax = '1';
				if(!empty($rates_shipping)){
					foreach($rates_shipping as $keytsp => $rate_shipping){
						$label_shipping =  $rate_shipping['label'];
						$keytax = $keytsp;
					}	
				}
				$list_add = array();
				foreach($listshipping_add as $keyshipping => $shipping_add){
					foreach($shipping_add as $key => $value){
						$listtax= array();
						$taxes = (WC_VERSION > '3.2.0') ? $value->get_taxes() : $value->taxes;
						$listtax[] = array(
							"code" => $label_shipping,
							"value" => (isset($taxes[$keytax])) ? (float)wc_format_decimal($taxes[$keytax]*$ratecurrency,$number_of_decimals) : 0
						);
						$instance_id = (WC_VERSION > '3.2.0') ? $value->get_instance_id() : $this->get_instance_id($value);
						$datazone = WC_Shipping_Zones::get_zone_by('instance_id',$instance_id);
						$zone_id = $datazone->get_id();
						if(empty($zone_id)){
							$zone_id = 0;
						}
						$cost = (WC_VERSION > '3.2.0') ? $value->get_cost() : $value->cost;
						$list_add[] = array(
							'id' => (WC_VERSION > '3.2.0') ? $value->get_id() : $value->id,
							'name' => (WC_VERSION > '3.2.0') ? $value->get_label() : $value->label,
							'title' => (WC_VERSION > '3.2.0') ? $value->get_label() : $value->label,
							'tax' => $listtax,
							'price' => (float)wc_format_decimal($cost*$ratecurrency,$number_of_decimals),
							'zone_id' => $zone_id,
							'zone_method' => $zone_id.'_'.$instance_id
						);
					}
				}
				WC()->cart->empty_cart();
				return apply_filters('wooconnector_shipping_price_and_tax',$list_add,$packages);
			}else{
				return null;
			}
		}else{
			return null;
		}
	}

	/**
	 * Get Instance with Shipping zone
	 */
	public function get_instance_id($shipping_zone){
		$method = $shipping_zone->method_id;
		$len = strlen($method);
		$id = $shipping_zone->id;
		$first = substr($id,strpos($id,$method) + $len + 1);
		if(is_numeric($first)){
			return $first;
		}else{
			$middle = substr($first,0,4);
			if(is_numeric($middle)){
				return $middle;
			}elseif(strpos($middle,':') !== false){
				$last = substr($middle,0,strpos($middle,':'));
				return $last;
			}elseif(strpos($middle,'_') !== false){
				$last = substr($middle,0,strpos($middle,'_'));
				return $last;
			}else{
				return "0";
			}
		}
	}
	
	/**
	 * Get total in cart with coupons
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return mixed
	 */
	public function add_coupons($request){
		$parameters = $request->get_params();
		$pro = $parameters['products'];	
		if(isset($parameters['woo_currency'])){
			$currentkey = $parameters['woo_currency'];					
		}else{
			$currentkey = strtolower(get_woocommerce_currency());
		}
		$currencys = WooConnectorGetCurrency($currentkey);
		$number_of_decimals   = $currencys['number_of_decimals'];
		$currencysymbol       = $currencys['symbol'];
		$ratecurrency         = $currencys['rate'];
		$currency_position    = $currencys['position'];
		$thousand_separator   = $currencys['thousand_separator'];
		$decimal_separator    = $currencys['decimal_separator'];
		$coupons = isset($parameters['coupons']) ? json_decode($parameters['coupons']) : false;			
		$product = json_decode($pro);
		$product = $this->sorttotalsbyquantityobject($product);
		$list = array();
		$listdiscount = array();		
		foreach($product as $post){	
			$product_id = absint($post->product_id);			
			$quantity = $post->quantity;					
			$variation_id = !empty($post->variation_id) ? absint($post->variation_id) : null;
			$addons = !empty($post->addons) ? $post->addons : array();
			$error = $this->validateInput($product_id,$variation_id,$quantity);			
			if(!empty($error)){
				$list['error'][$product_id] = $error;
				if($error['code'] !== 'rest_order_stock_error'){
					continue;
				}
			}
			$list['product'][] = $this->getTotalnoCoupon($post,$request,$addons);						
		}	
		if(!empty($list['error'])){
			foreach($list['error'] as $postid => $listerror){
				$listdiscount['errors'][$postid] = $listerror;
			}
		}
		if(empty($list['product'])){
			return $listdiscount;
		}else{
			$totals = $list['product'];
		}
		$totalsubprice = 0;	
		$totaltax = 0;
		$totalquantity = 0;
		$basetotal = 0;
		$basetax = 0;
		$quantitytotals = 0;
		foreach($totals as $total){			
			$totalsubprice += $total['subtotal'];
			$basetotal += $total['subtotal'];				
			$totaltax += $total['tax'];	
			$basetax += $total['tax'];					
			$totalquantity += $total['quantity'];
			$quantitytotals++;							
		}	
		$baselisttotals = $totals;			
		$checkidden = 0;
		$checkcoupon = array();	
		if(!empty($coupons)){
			$totalcoupons = 0;	
			foreach($coupons as $coupon){
				$totalcoupons++;		
			}
		}
		$totalsafterdiscount = array();						
		if(empty($coupons))	{
			$listdiscount['total'] = $this->gettotal($request);
		}
		else{	
			$checkerrorcoupons = array();	
			$listbeforetotal = array();		
			$listtotal = array();		
			foreach($coupons as $coupon){
				$cp = new WC_Coupon( $coupon );	
				$data = $cp->get_data();										
				$format_decimal = array( 'amount', 'minimum_amount', 'maximum_amount' );
				foreach ( $format_decimal as $key ) {
					$data[ $key ] = (float)wc_format_decimal( $data[ $key ], $number_of_decimals );
				}					
				$checkerrorcoupons = $this->validateCoupon($data,$basetotal);
				if(empty($checkerrorcoupons)){
					$checkerrorcoupons['resulterror'] = 0;
				}
				if(empty($checkerrorcoupons['typeerror'])){
					$checkerrorcoupons['typeerror'] = 'default';
				}
				if(empty($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']])){
					$checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] = 0;
				}
				$checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] = $checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] + $checkerrorcoupons['resulterror'];
				if($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] <= $totalcoupons && !empty($checkerrorcoupons['typeerror']) && $checkerrorcoupons['typeerror'] == 'notexist'){
					$listdiscount['errors'][$data['code']] =  new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not exist.','wooconnector' ), array( 'status' => 401 ));
					continue;
				}elseif($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] <= $totalcoupons && !empty($checkerrorcoupons['typeerror']) && $checkerrorcoupons['typeerror'] == 'minmax'){
					$listdiscount['errors'][$data['code']] =  new WP_Error( 'rest_coupon_error', __( 'Sorry, Amount not suitable for Coupon. '.$coupon ,'wooconnector' ), array( 'status' => 401 ) );
					continue;
				}elseif($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] <= $totalcoupons && !empty($checkerrorcoupons['typeerror']) && $checkerrorcoupons['typeerror'] == 'expires'){
					$listdiscount['errors'][$data['code']] =  new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon has expired.','wooconnector' ), array( 'status' => 401 ) );
					continue;
				}elseif($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] <= $totalcoupons && !empty($checkerrorcoupons['typeerror']) && $checkerrorcoupons['typeerror'] == 'usagelimit'){
					$listdiscount['errors'][$data['code']] =  new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon usage limit has been reached.','wooconnector' ), array( 'status' => 401 ) );
					continue;
				}elseif($checkerrorcoupons[$data['code']][$checkerrorcoupons['typeerror']] <= $totalcoupons && !empty($checkerrorcoupons['typeerror']) && $checkerrorcoupons['typeerror'] == 'usagelimitperuser'){
					$listdiscount['errors'][$data['code']] = new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon usage limit per user has been reached.','wooconnector' ), array( 'status' => 401 ) );
					continue;
				}
				if(!in_array($coupon,$checkcoupon)){
					if(!empty($checkcoupon)){
						$checkerroridden = 0;
						foreach($checkcoupon as $ckcoup){
							$ckcp = new WC_Coupon( $ckcoup );									
							$checkdata = $ckcp->get_data();												
							if($checkdata['individual_use'] == true && $checkidden > 0)	{
								$checkerroridden++;
							}
						}
						if($checkerroridden > 0){
							$listdiscount['errors'][$data['code']] = new WP_Error( 'rest_coupon_error_exists', __( 'Sorry, Coupon only one.','wooconnector' ), array( 'status' => 401 ) );
							continue;
						}	
					}																										
					if($data['individual_use'] == true && $checkidden > 0)	{
						$listdiscount['errors'][$data['code']] = new WP_Error( 'rest_coupon_error_delete', __( 'Sorry, Coupon only one.','wooconnector' ), array( 'status' => 401 ) );
						continue;
					}						
					array_push($checkcoupon,$coupon);
					$checkidden++;												
				}
				else{				
					$listdiscount['errors'][$data['code']] = new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon already applied!.','wooconnector' ), array( 'status' => 401 ) );
					continue;			
				}
				if($data['discount_type'] === 'percent' ){						
					if($data['limit_usage_to_x_items'] > 0 && $totalquantity > $data['limit_usage_to_x_items'])	{
						$totalperafterchangequantity = array();
						$calculatorDetails = $this->getProductXquantity($totals,$data,$checkerrorcoupons,$listdiscount,0,$totalsubprice,$listtotal,$listbeforetotal,$ratecurrency,$totalsafterdiscount,$quantitytotals,$request,$baselisttotals,$basetotal);	
						$totalperafterchangequantity = $calculatorDetails['totalperafterchangequantity'];
						$listtotal = $calculatorDetails['listtotal'];										
						$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
						$totalsubprice = $calculatorDetails['totalsubprice'];
						$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
						$listdiscount = $calculatorDetails['listdiscount'];	
						if(!empty($totalperafterchangequantity)){
							$checktotals = ($basetotal*$data['amount'])/100;
							$calculatorDetails = $this->calculatorDetailPercentCoupon($listtotal,$data,$totalperafterchangequantity,$totalsubprice,$basetotal,$ratecurrency,$checktotals,$baselisttotals,$quantitytotals,$totalsafterdiscount,$listbeforetotal,$listdiscount,$checkerrorcoupons);
							$listtotal = $calculatorDetails['listtotal'];										
							$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
							$totalsubprice = $calculatorDetails['totalsubprice'];
							$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
							$listdiscount = $calculatorDetails['listdiscount'];
						}						
					}
					else{	
						$checktotals = ($basetotal*$data['amount'])/100;
						$calculatorDetails = $this->calculatorDetailPercentCoupon($listtotal,$data,$baselisttotals,$totalsubprice,$basetotal,$ratecurrency,$checktotals,$baselisttotals,$quantitytotals,$totalsafterdiscount,$listbeforetotal,$listdiscount,$checkerrorcoupons);																																	
						$listtotal = $calculatorDetails['listtotal'];										
						$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
						$totalsubprice = $calculatorDetails['totalsubprice'];
						$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
						$listdiscount = $calculatorDetails['listdiscount'];		
					}						
				}elseif($data['discount_type'] === 'fixed_cart'){					
					$checktotals = $data['amount'];
					$calculatorDetails = $this->calculatorDetailCartCoupon($listtotal,$data,$baselisttotals,$totalsubprice,$basetotal,$ratecurrency,$checktotals,$baselisttotals,$quantitytotals,$totalsafterdiscount,$listbeforetotal,$listdiscount,$checkerrorcoupons);																													
					$listtotal = $calculatorDetails['listtotal'];										
					$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
					$totalsubprice = $calculatorDetails['totalsubprice'];
					$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
					$listdiscount = $calculatorDetails['listdiscount'];																					
				}elseif($data['discount_type'] === 'fixed_product'){
					if($data['limit_usage_to_x_items'] > 0 && $totalquantity > $data['limit_usage_to_x_items'])	{	
						$totalproafterchangequantity = array();
						$calculatorDetails = $this->getProductXquantity($totals,$data,$checkerrorcoupons,$listdiscount,0,$totalsubprice,$listtotal,$listbeforetotal,$ratecurrency,$totalsafterdiscount,$quantitytotals,$request,$baselisttotals,$basetotal);
						$totalproafterchangequantity = $calculatorDetails['totalperafterchangequantity'];
						$listtotal = $calculatorDetails['listtotal'];										
						$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
						$totalsubprice = $calculatorDetails['totalsubprice'];
						$listdiscount = $calculatorDetails['listdiscount'];	
						$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
						if(!empty($totalproafterchangequantity)){
							$calculatorDetails = $this->calculatorDetailProductCoupon($listtotal,$totalproafterchangequantity,$data,$listbeforetotal,$totalsubprice,$ratecurrency,$baselisttotals,$quantitytotals,$totalsafterdiscount,$listdiscount,$checkerrorcoupons);					
							$listtotal = $calculatorDetails['listtotal'];																	
							$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
							$totalsubprice = $calculatorDetails['totalsubprice'];
							$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
							$listdiscount = $calculatorDetails['listdiscount'];	
						}
					}else{
						$calculatorDetails = $this->calculatorDetailProductCoupon($listtotal,$baselisttotals,$data,$listbeforetotal,$totalsubprice,$ratecurrency,$baselisttotals,$quantitytotals,$totalsafterdiscount,$listdiscount,$checkerrorcoupons);					
						$listtotal = $calculatorDetails['listtotal'];																	
						$totalsafterdiscount = $calculatorDetails['totalsafterdiscount'];	
						$totalsubprice = $calculatorDetails['totalsubprice'];
						$checkerrorcoupons = $calculatorDetails['checkerrorcoupons'];
						$listdiscount = $calculatorDetails['listdiscount'];
					}										
				}					
			}								
			$checkcode = array();
			$totaldiscountaftercalcu = 0;
			if(empty($listtotal)){
				$listdiscount['total'] = $this->gettotal($request);
				return $listdiscount;
			}else{
				foreach($listtotal as $code){
					$checkcode[] = $code['couponcode'];
				}
			}			
			$codes = array_unique( $checkcode );									
			foreach($codes as $code){
				$totaldiscount = 0;
				$totaltaxdiscount = 0;				
				foreach($listtotal as $calcu){
					if($calcu['couponcode'] == $code){							
						$totaldiscount += $calcu['totaldiscount'];														
						$output[] = (float)$totaldiscount;
						$outputpop = array_pop($output);
					}					
				}
				$listdiscountoutput[$code] = $outputpop;	
			}
			$outdiscounts = array();
			foreach($listdiscountoutput as $outdiscount => $values){
				foreach($coupons as $coupon){
					$cp = new WC_Coupon($coupon);
					$data = $cp->get_data();
					if($data['code'] == $outdiscount){
						$listout = array(
							'code' => $outdiscount,
							'value' => number_format(floatval($values), $number_of_decimals, $decimal_separator, $thousand_separator)
						);
						if($data['discount_type'] == 'percent'){
							$listout['amount'] = $data['amount'].'%';
						}else{
							$listout['amount'] = number_format(floatval($data['amount']*$ratecurrency), $number_of_decimals, $decimal_separator, $thousand_separator);
						}
						$outdiscounts[] = $listout;
					}
				}
			}		
			foreach($listdiscountoutput as $calcudiscount){
				$totaldiscountaftercalcu += $calcudiscount;
			}
			$totalafteraddcoupon = $basetotal - $totaldiscountaftercalcu;
			$taxs = new WC_Tax();
			$listrates = array();				
			foreach($totalsafterdiscount as $afterpost){	
				$product_id = absint($afterpost['product_id']);
				$variation_id = !empty($afterpost['variation_id']) ? absint($afterpost['variation_id']) : null;					
				$subtotal = $afterpost['subtotal'];
				if(!empty($variation_id) || $variation_id != null){
					$pros = wc_get_product($product_id);
					$taxstatus = $pros->get_tax_status();
					$provari = wc_get_product($variation_id);
					$taxclass = $provari->get_tax_class();
					if($taxstatus == 'taxable'){
						$rates = $taxs->get_rates($taxclass);
						$listrates[] = $taxs->get_rates($taxclass);
						$variations = $pros->get_available_variations();
						foreach($variations as $variation => $value){
							if($value['variation_id'] == $variation_id){
								$tax[] = $taxs->calc_tax($subtotal,$rates);
							}
						}
					}
				}else{
					$pros = wc_get_product($product_id);
					$taxclass = $pros->get_tax_class();
					$taxstatus = $pros->get_tax_status();
					if($taxstatus == 'taxable'){
						$rates = $taxs->get_rates($taxclass);
						$listrates[] = $taxs->get_rates($taxclass);
						$tax[] = $taxs->calc_tax($subtotal,$rates);
					}
					
				}								
			}
			$listratesafter = array();
			foreach($listrates as $rate => $vals){
				foreach($vals as $val => $v){
					$listratesafter[$val] = $v;
				}					
			}
			$listtaxouts = array();	
			$totalstaxaftercoupon = 0;
			if(!empty($tax)){
				foreach($tax as $ttax => $values){
					foreach($values as $totalstax => $valx){
						$label = $taxs->get_rate_label($totalstax);
						if(!empty($listtaxouts[$label])){
							$listtaxouts[$label] += $valx;
						}else{
							$listtaxouts[$label] = $valx;
						}
						$totalstaxaftercoupon += $valx;
					}
				}	
				$outtax = array();
				foreach($listtaxouts as $outtaxs => $values){
					$outtax[] = array(
						'code' => $outtaxs,
						'value' => (float)wc_format_decimal($values,$number_of_decimals)
					);
				}
			}else{
				$outtax = null;
			}
			$formatcurrencyafterdiscount = array();
			$baseitems = $this->getListproduct($request);			
			foreach($totalsafterdiscount as $total){					
				$formatcurrencyafterdiscount[] = array(
					'product_id' => $total['product_id'],
					'quantity' => $total['quantity'],
					'variation_id' => $total['variation_id'],
					'subtotal' => (float)wc_format_decimal($total['subtotal'], $number_of_decimals),
					'class_shipping_id' => $total['class_shipping_id']
				);					
			}
			$totalsaftercou = $totalafteraddcoupon + $totalstaxaftercoupon;
			$listdiscount['baseitem'] = $baseitems;
			$listdiscount['subtotal'] = (float)wc_format_decimal($basetotal,$number_of_decimals);				
			$listdiscount['discount'] = $outdiscounts;
			$listdiscount['baseitemaftercoupon'] = $formatcurrencyafterdiscount;
			$listdiscount['subtotalaftercoupon'] = (float)wc_format_decimal($totalafteraddcoupon,$number_of_decimals);	
			$listdiscount['tax'] = $outtax;
			$listdiscount['total'] = (float)wc_format_decimal($totalsaftercou,$number_of_decimals);
		}		
		return apply_filters('wooconnector_add_coupons',$listdiscount,$product,$coupons);
	}

	private function getProductXquantity($totals,$data,$checkerrorcoupons,$listdiscount,$checktotals = 0,$totalsubprice,$listtotal,$listbeforetotal = array(),$ratecurrency,$totalsafterdiscount,$quantitytotals,$request,$baselisttotals,$basetotal){
		$totals = $this->sorttotals($totals);
		$totals = $this->sorttotalsbyquantity($totals);	
		$totalquan = 0;
		$totalperafterchangequantity = array();
		$j = 0;
		for($i = 0; $i < count($totals); $i++ )	{
			$check = $this->checkUsagerestriction($data,$totals[$j]);
			if(empty($check['typeerror'])){
				$check['typeerror'] = 'default';
			}
			if(empty($check['resulterror'])){
				$check['resulterror'] = 0;
			}
			if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
				$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
			}
			$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
			if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
				$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);		
				if(!empty($error)){
					$listdiscount['errors'][$data['code']] = $error;
				}
				if(empty($listdiscount)){
					$check['resulterror'] = 0;
				}
				if($j < count($totals)){
					$j++;
				}
				continue;
			}						
			if($totals[$j]['quantity'] >= $data['limit_usage_to_x_items']){
				$checktotals = ($totals[$j]['subtotal']*$data['amount'])/100;
				$x = $totals[$j]['quantity'] - $data['limit_usage_to_x_items'];
				$quanx = $totals[$j]['quantity'] - $x;
				$variationj = !empty($totals[$j]['variation_id']) ? $totals[$j]['variation_id'] : array();
				$addonsj = !empty($totals[$j]['addons']) ? $totals[$j]['addons'] : array();
				$list = array(
					'product_id' => absint($totals[$j]['product_id']),
					'quantity' => $quanx,
					'variation_id' => $variationj,
				);						
				$convertlist = json_decode(json_encode($list));
				$totalperafterchangequantityfirst = $this->getTotalnoCoupon($convertlist,$request,$addonsj);
				if(!empty($totalsafterdiscount)){
					if($totalsubprice >= $checktotals){	
						$keys = $this->getKeysDiscount($totals[$j]);								
						$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($totalperafterchangequantityfirst,$data,$basetotal,1,$ratecurrency);
						foreach($totalsafterdiscount as $total => $values){
							$key = $this->getKeysDiscount($values);
							if($values['variation_id'] != null){
								$product = wc_get_product($values['variation_id']);		
							}else{
								$product = wc_get_product($values['product_id']);
							}
							$shippingid = $product->get_shipping_class_id();
							if(empty($listbeforetotal[$key])){
								$subtotal['product_id'] = $values['product_id'];
								$subtotal['quantity'] = $values['quantity'];
								$subtotal['variation_id'] = $values['variation_id'];
								$subtotal['subtotal'] = $values['subtotal'];
								$subtotal['class_shipping_id'] = $shippingid;
								$totalsafterdiscount[$total] = $subtotal;
							}else{
								$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$key],array(),$ratecurrency);
							}
						}
						$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];
						$listtotal[] = $listbeforetotal[$keys];
						break;
					}else{
						$keys = $this->getKeysDiscount($totals[$j]);	
						$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($totalperafterchangequantityfirst,$data,$totalsubprice,2,$ratecurrency);
						foreach($totalsafterdiscount as $total => $values){
							$key = $this->getKeysDiscount($values);
							if($values['variation_id'] != null){
								$product = wc_get_product($values['variation_id']);		
							}else{
								$product = wc_get_product($values['product_id']);
							}
							$shippingid = $product->get_shipping_class_id();
							if(empty($listbeforetotal[$key])){
								$subtotal['product_id'] = $values['product_id'];
								$subtotal['quantity'] = $values['quantity'];
								$subtotal['variation_id'] = $values['variation_id'];
								$subtotal['subtotal'] = $values['subtotal'];
								$subtotal['class_shipping_id'] = $shippingid;
								$totalsafterdiscount[$total] = $subtotal;
							}else{
								$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$key],array(),$ratecurrency);
							}	
						}
						$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];
						$listtotal[] = $listbeforetotal[$keys];
						break;										
					}
				}else{
					$keys = $this->getKeysDiscount($totals[$j]);
					$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($totalperafterchangequantityfirst,$data,$basetotal,1,$ratecurrency);
					foreach($baselisttotals as $total => $values){
						$key = $this->getKeysDiscount($values);
						if($values['variation_id'] != null){
							$product = wc_get_product($values['variation_id']);		
						}else{
							$product = wc_get_product($values['product_id']);
						}
						$shippingid = $product->get_shipping_class_id();
						if(empty($listbeforetotal[$key])){
							$subtotal['product_id'] = $values['product_id'];
							$subtotal['quantity'] = $values['quantity'];
							$subtotal['variation_id'] = $values['variation_id'];
							$subtotal['subtotal'] = $values['subtotal'];
							$subtotal['class_shipping_id'] = $shippingid;
							$totalsafterdiscount[$total] = $subtotal;
						}else{
							$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$key],array(),$ratecurrency);
						}
					}
					$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];
					$listtotal[] = $listbeforetotal[$keys];	
					break;
				}	
			}else{								
				$totalquan += $totals[$i]['quantity'];									
				if($totalquan > $data['limit_usage_to_x_items']){
					$x = $totalquan - $data['limit_usage_to_x_items'];
					$quanx = $totals[$i]['quantity'] - $x;									
				}else{
					$quanx = $totals[$i]['quantity'];									
				}
				if($quanx < 0 ){
					$quanx = 0;
				}
				$list = array(
					'product_id' => absint($totals[$i]['product_id']),
					'quantity' => $quanx,
					'variation_id' => absint($totals[$i]['variation_id'])
				);
				$convertlist = json_decode(json_encode($list));
				$totalperafterchangequantity[] = $this->getTotalnoCoupon($convertlist,$request);											
			}					
		}
		$after = array(
			'totalperafterchangequantity' => $totalperafterchangequantity,
			'totalsafterdiscount' => $totalsafterdiscount,
			'totalsubprice' => $totalsubprice,
			'listtotal'     => $listtotal,
			'listdiscount' => $listdiscount,
			'checkerrorcoupons' => $checkerrorcoupons
		);
		return $after;
	}

	private function calculatorDetailPercentCoupon($listtotal,$data,$totalsproductcoupons,$totalsubprice,$basetotal,$ratecurrency,$checktotals,$baselisttotals = array(),$quantitytotals,$totalsafterdiscount = array(),$listbeforetotal = array(),$listdiscount,$checkerrorcoupons){
		if(!empty($totalsafterdiscount)){	
			if($totalsubprice >= $checktotals){						
				foreach($totalsproductcoupons as $total => $values ){	
					$keys = $this->getKeysDiscount($values);									
					$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($values,$data,$basetotal,1,$ratecurrency);
				}
				foreach($totalsafterdiscount as $total => $values){
					$keys = $this->getKeysDiscount($values);	
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$keys],array(),$ratecurrency);
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}										
					$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];	
					$listtotal[] = $listbeforetotal[$keys];
				}
			}else{								
				foreach($totalsproductcoupons as $total => $values){
					$keys = $this->getKeysDiscount($values);
					$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($values,$data,$totalsubprice,2,$ratecurrency);
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$keys],array(),$ratecurrency);
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}									
					$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];
					$listtotal[] = $listbeforetotal[$keys];
				}
			}
		}else{
			foreach($totalsproductcoupons as $total => $values){
				$keys = $this->getKeysDiscount($values);												
				$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($values,$data,$basetotal,1,$ratecurrency);
			}
			foreach($baselisttotals as $total => $values){				
				$keys = $this->getKeysDiscount($values);
				if(!empty($listbeforetotal[$keys])){
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$keys],array(),$ratecurrency);
					$check = $this->checkUsagerestriction($data,$values);
					
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}										
					$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];
					$listtotal[] = $listbeforetotal[$keys];
				}
			}									
		}
		$result = array(
			'listtotal' => $listtotal,
			'totalsubprice' => $totalsubprice,
			'totalsafterdiscount' => $totalsafterdiscount,
			'checkerrorcoupons' => $checkerrorcoupons,
			'listdiscount' => $listdiscount
		);
		return $result;
	}

	private function calculatorDetailCartCoupon($listtotal,$data,$totalsproductcoupons,$totalsubprice,$basetotal,$ratecurrency,$checktotals,$baselisttotals = array(),$quantitytotals,$totalsafterdiscount = array(),$listbeforetotal = array(),$listdiscount,$checkerrorcoupons){
		if(!empty($totalsafterdiscount)){
			$totalcheck = 0;
			foreach($totalsafterdiscount as $totalchecks){
				$totalcheck += $totalchecks['subtotal'];
			}
			if(($data['amount']*$ratecurrency) >= $totalcheck){
				foreach($totalsafterdiscount as $total => $values){
					$listbeforetotal = $this->calculatorMoneyCoupon($values,$data,0,0,$ratecurrency);
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal,$data,$ratecurrency);	
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}	
					$totalsubprice = $totalsubprice - $listbeforetotal['totaldiscount'];		
					$listtotal[] = $listbeforetotal;
				}
			}else{
				$totalsubpriceafter = 0;
				foreach($totalsafterdiscount as $totalsproafter){
					$totalsubpriceafter += $totalsproafter['subtotal'];
				}
				foreach($totalsafterdiscount as $total => $values){
					$listbeforetotal = $this->calculatorMoneyCoupon($values,$data,$totalsubpriceafter,1,$ratecurrency);
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal,$data,$ratecurrency);	
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}
					$totalsubprice = $totalsubprice - $listbeforetotal['totaldiscount'];
					$listtotal[] = $listbeforetotal;
				}
			}
		}else{
			$totalcheck = 0;
			foreach($baselisttotals as $totalchecks){
				$totalcheck += $totalchecks['subtotal'];
			}
			if(($data['amount']*$ratecurrency) >= $totalcheck){
				foreach($baselisttotals as $total => $values){
					$listbeforetotal = $this->calculatorMoneyCoupon($values,$data,0,0,$ratecurrency);
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal,$data,$ratecurrency);
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){						
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);						
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}	
					$totalsubprice = $totalsubprice - $listbeforetotal['totaldiscount'];
					$listtotal[] = $listbeforetotal;
				}
			}else{
				$totalsubpriceafter = 0;
				foreach($baselisttotals as $totalsproafter){
					$totalsubpriceafter += $totalsproafter['subtotal'];
				}
				foreach($baselisttotals as $total => $values){
					$listbeforetotal = $this->calculatorMoneyCoupon($values,$data,$totalsubpriceafter,1,$ratecurrency);
					$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal,$data,$ratecurrency);
					$check = $this->checkUsagerestriction($data,$values);
					if(empty($check)){
						$check['resulterror'] = 0;
					}
					if(empty($check['typeerror'])){
						$check['typeerror'] = 'default';
					}
					if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
						$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
					}
					$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
					if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
						$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
						if(!empty($error)){
							$listdiscount['errors'][$data['code']] = $error;
						}
						continue;
					}	
					$totalsubprice = $totalsubprice - $listbeforetotal['totaldiscount'];
					$listtotal[] = $listbeforetotal;
				}
			}
		}
		return array(
			'listtotal' => $listtotal,
			'totalsubprice' => $totalsubprice,
			'totalsafterdiscount' => $totalsafterdiscount,
			'checkerrorcoupons' => $checkerrorcoupons,
			'listdiscount' => $listdiscount
		);
	}

	private function calculatorDetailProductCoupon($listtotal,$calculatorkey,$data,$listbeforetotal,$totalsubprice,$ratecurrency,$baselisttotals = array(),$quantitytotals,$totalsafterdiscount = array(),$listdiscount,$checkerrorcoupons){
		$totalsubpriceafter = 0;
		foreach($calculatorkey as $totalsproafter){
			$totalsubpriceafter += $totalsproafter['subtotal'];
			$keys = $this->getKeysDiscount($totalsproafter);									
			$listbeforetotal[$keys] = $this->calculatorMoneyCoupon($totalsproafter,$data,0,0,$ratecurrency);
		}
		if(!empty($totalsafterdiscount)){
			foreach($totalsafterdiscount as $total => $values){
				$keys = $this->getKeysDiscount($values);	
				$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$keys],$data,$ratecurrency);
				$check = $this->checkUsagerestriction($data,$values);
				if(empty($check)){
					$check['resulterror'] = 0;
				}
				if(empty($check['typeerror'])){
					$check['typeerror'] = 'default';
				}
				if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
					$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
				}
				$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
				if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
					$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
					if(!empty($error)){
						$listdiscount['errors'][$data['code']] = $error;
					}
					continue;
				}	
				$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];	
				$listtotal[] = $listbeforetotal[$keys];
			}
		}else{
			foreach($baselisttotals as $total => $values){
				$keys = $this->getKeysDiscount($values);	
				$totalsafterdiscount[$total] = $this->getTotalsAfterDiscount($values,$listbeforetotal[$keys],$data,$ratecurrency);
				$check = $this->checkUsagerestriction($data,$values);
				if(empty($check)){
					$check['resulterror'] = 0;
				}
				if(empty($check['typeerror'])){
					$check['typeerror'] = 'default';
				}
				if(empty($checkerrorcoupons[$data['code']][$check['typeerror']])){
					$checkerrorcoupons[$data['code']][$check['typeerror']] = 0;
				}
				$checkerrorcoupons[$data['code']][$check['typeerror']] = $checkerrorcoupons[$data['code']][$check['typeerror']] + $check['resulterror'];
				if($checkerrorcoupons[$data['code']][$check['typeerror']] > 0 && $checkerrorcoupons[$data['code']][$check['typeerror']] <= $quantitytotals){
					$error = $this->getMessageError($checkerrorcoupons[$data['code']][$check['typeerror']],$quantitytotals,$check,$data['code']);
					if(!empty($error)){
						$listdiscount['errors'][$data['code']] = $error;
					}
					continue;
				}	
				$totalsubprice = $totalsubprice - $listbeforetotal[$keys]['totaldiscount'];	
				$listtotal[] = $listbeforetotal[$keys];
			}
		}
		return array(
			'listtotal' => $listtotal,
			'totalsubprice' => $totalsubprice,
			'totalsafterdiscount' => $totalsafterdiscount,
			'checkerrorcoupons' => $checkerrorcoupons,
			'listdiscount' => $listdiscount
		);
	}

	private function getMessageError($totalcheck,$totalquantity,$check,$coupon){
		$cp = new WC_Coupon($coupon);
		$data = $cp->get_data();
		if($data['discount_type'] !== 'fixed_cart'){
			if($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'sale'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not for sale products.','wooconnector' ), array( 'status' => 401 ));
			}elseif($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'products'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these products.','wooconnector' ), array( 'status' => 401 ));
			}elseif($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'exproducts'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these products.','wooconnector' ), array( 'status' => 401 ));
			}elseif($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'categories'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these categories.','wooconnector' ), array( 'status' => 401 ));
			}elseif($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'excategories'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these categories.','wooconnector' ), array( 'status' => 401 ));
			}elseif($totalcheck == $totalquantity && !empty($check['typeerror']) && $check['typeerror'] == 'email_res'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, It looks like this coupon code is not yours.','wooconnector' ), array( 'status' => 401 ));
			}			
		}else{
			if( !empty($check['typeerror']) && $check['typeerror'] == 'sale'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not for sale products.','wooconnector' ), array( 'status' => 401 ));
			}elseif(!empty($check['typeerror']) && $check['typeerror'] == 'products'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these products.','wooconnector' ), array( 'status' => 401 ));
			}elseif(!empty($check['typeerror']) && $check['typeerror'] == 'exproducts'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these products.','wooconnector' ), array( 'status' => 401 ));
			}elseif(!empty($check['typeerror']) && $check['typeerror'] == 'categories'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these categories.','wooconnector' ), array( 'status' => 401 ));
			}elseif(!empty($check['typeerror']) && $check['typeerror'] == 'excategories'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, Coupon not supported with these categories.','wooconnector' ), array( 'status' => 401 ));
			}elseif(!empty($check['typeerror']) && $check['typeerror'] == 'email_res'){
				return new WP_Error( 'rest_coupon_error', __( 'Sorry, It looks like this coupon code is not yours.','wooconnector' ), array( 'status' => 401 ));
			}
		}
	}
	
	private function getListproduct($request){
		$parameters = $request->get_params();
		$pro = $parameters['products'];	
		$product = json_decode($pro);
		$totals = array();
		foreach($product as $post){
			$totals[] = $this->getTotalnoCoupon($post,$request);
		}
		$result = array();
		if(isset($parameters['woo_currency'])){
			$currentkey = $parameters['woo_currency'];					
		}else{
			$currentkey = strtolower(get_woocommerce_currency());
		}
		$currencys = WooConnectorGetCurrency($currentkey);
		$number_of_decimals   = $currencys['number_of_decimals'];
		foreach($product as $post){	
			$product_id = absint($post->product_id);			
			$quantity = $post->quantity;					
			$variation_id = !empty($post->variation_id) ? absint($post->variation_id) : null;
			if(!empty($variation_id) && $variation_id != null){
				$product = wc_get_product($variation_id);
			}else{
				$product = wc_get_product($product_id);
			}	
			$shippingid = $product->get_shipping_class_id();
			foreach($totals as $total){
				$totalvari = !empty($total['variation_id']) ? absint($total['variation_id']) : null;
				if($product_id == $total['product_id'] && $quantity == $total['quantity'] && $variation_id == $totalvari){
					$result[] = array(
						'product_id' => $product_id,
						'quantity' => $quantity,
						'variation_id' => $variation_id,
						'subtotal' => (float)wc_format_decimal($total['subtotal'],$number_of_decimals),
						'class_shipping_id' => $shippingid
					);	
				}
			}		
		}
		return $result;		
	}
	private function getKeysDiscountObject($object){
		if(!empty($object->variation_id)){
			$keys = $object->product_id.'_'.$object->variation_id;
		}else{
			$keys = $object->product_id;
		}
		return $keys;
	}

	private function getKeysDiscount($values){
		if(!empty($values['variation_id'])){
			$keys = $values['product_id'].'_'.$values['variation_id'];
		}else{
			$keys = $values['product_id'];
		}
		return $keys;
	}

	private function getTotalsAfterDiscount($total,$discount,$data = array(),$ratecurrency){
		if($total['product_id'] == $discount['product_id'] && $total['variation_id'] == $discount['variation_id']){
			if(!empty($data)){
				$check = $this->getCheckUsageRestriction($data,$total);
				if($check == 1){
					$subtotal = $total['subtotal'];
				}else{
					$subtotal = $total['subtotal'] - $discount['totaldiscount'];
				}
			}else{
				$subtotal = $total['subtotal'] - $discount['totaldiscount'];
			}
			if($total['variation_id'] != null){
				$product = wc_get_product($total['variation_id']);		
			}else{
				$product = wc_get_product($total['product_id']);
			}
			$shippingid = $product->get_shipping_class_id();
			$result = array(
				'product_id' => $total['product_id'],
				'quantity' => $total['quantity'],
				'variation_id' => $total['variation_id'],
				'subtotal' => $subtotal,
				'class_shipping_id' => $shippingid
			);
		}
		return $result;
	}	

	private function sorttotals($totals){
		$x = array();
		for($i = 0; $i < count($totals) - 1; $i++){
			for($j = count($totals) -1 ; $j > $i; $j-- ){
				if($totals[$j -1]['subtotal'] < $totals[$j]['subtotal']){
					$x = $totals[$j - 1];
					$totals[$j - 1] = $totals[$j];
					$totals[$j] = $x;
				}
			}
		}
		return $totals;
	}

	private function sorttotalsbyquantity($totals){
		$x = array();
		for($i = 0; $i < count($totals) - 1; $i++){
			for($j = count($totals) - 1; $j > $i; $j-- ){
				if($totals[$i]['quantity'] < $totals[$j]['quantity']){
					$x = $totals[$j - 1];
					$totals[$j - 1] = $totals[$j];
					$totals[$j] = $x;
				}
			}
		}
		return $totals;
	}

	private function sorttotalsbyquantityobject($totals){
		$x = array();
		for($i = 0; $i < count($totals) - 1; $i++){
			for($j = count($totals) - 1; $j > $i; $j-- ){
				if($totals[$i]->quantity < $totals[$j]->quantity){
					$x = $totals[$j - 1];
					$totals[$j - 1] = $totals[$j];
					$totals[$j] = $x;
				}
			}
		}
		return $totals;
	}

	private function getRates($product_id){
		$useTax = get_option('woocommerce_calc_taxes');
		$ratec = 0;
		if($useTax !== 'no'){
			$taxs = new WC_Tax();
			$pros = wc_get_product($product_id);						
			$taxclass = $pros->get_tax_class();
			$rates = $taxs->get_rates($taxclass);
			if($rates == null){
				$ratec = 0;
			}
			else{
				foreach($rates as $rate){
					$ratec = $rate['rate'];
				}
			}
		}
		return $ratec;
	}

	private function getBaseRates(){
		$useTax = get_option('woocommerce_calc_taxes');
		$ratec = 0;
		if($useTax !== 'no'){
			$taxs = new WC_Tax();
			$rates = $taxs->get_base_tax_rates();
			if($rates == null){
				$ratec = 0;
			}
			else{
				foreach($rates as $rate){
					$ratec = $rate['rate'];
				}
			}
		}
		return $ratec;
	}

	private function validateCoupon($data,$basetotal){
		$result = array();
		if(!empty($data['minimum_amount']) && $basetotal < $data['minimum_amount'] || !empty($data['maximum_amount']) && $data['maximum_amount'] !== '0.00' && $basetotal > $data['maximum_amount']){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'minmax';
			return $result;
		}	
		if(!empty($data['date_expires']) || $data['date_expires'] != ''){
			$date = $data['date_expires'];
			$currentdate = date('Y-m-dTH:i:s');
			if($currentdate > $date){
				$result['resulterror'] = 1;
				$result['typeerror'] = 'expires';
				return $result;
			}	
		}
		if($data['id'] === 0){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'notexist';
			return $result;
		}
		if($data['usage_limit'] > 0 && $data['usage_count'] >= $data['usage_limit']){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'usagelimit';
			return $result;
		}
		if($data['usage_limit_per_user'] > 0){
			$userid = get_current_user_id();
			$jwtpuclib = new BAMobile_JWT_Auth();
			$currentuser = $jwtpuclib->bamobile_determine_current_user($userid);
			if($currentuser > 0){
				$currentdata = get_user_by('id',$currentuser);
				$currentdata = (array)$currentdata->data;
				$emailcurrent = $currentdata['user_email'];
				$userby = $data['used_by'];
				if(!empty($userby) && is_array($userby)){
					$count = array_count_values($userby);
					if(!empty($count)){
						if(!empty($count[$currentuser])){
							$countuser = $count[$currentuser];
							if($countuser >= $data['usage_limit_per_user']){
								$result['resulterror'] = 1;
								$result['typeerror'] = 'usagelimitperuser';
								return $result;
							}
						}
						if(!empty($count[$emailcurrent])){
							$countemail = $count[$emailcurrent];
							if($countemail >= $data['usage_limit_per_user']){
								$result['resulterror'] = 1;
								$result['typeerror'] = 'usagelimitperuser';
								return $result;
							}
						}
					}
				}
			}
		}	
	}

	private function checkUsagerestriction($data,$total){
		$productcheck = wc_get_product($total['product_id']);
		$categories = $productcheck->get_category_ids();		
		$type = $productcheck->get_type();
		$result = array();		
		$totalpro = 0;
		$totalerror = 0;
		if(!empty($data['product_ids'])){
			foreach($data['product_ids'] as $checkinproduct){
				$productdatacheck  = wc_get_product($checkinproduct);
				if(!$productcheck->is_type('variable') && $total['product_id'] != $checkinproduct || $productdatacheck->is_type('variable') && $total['product_id'] != $checkinproduct || $productdatacheck->is_type('variation') && isset($total['variation_id']) && $total['variation_id'] != $checkinproduct ){
					$totalerror++;						
				}
				$totalpro++;
			}
		}
		if(!empty($data['product_ids']) && $totalerror == $totalpro){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'products';
			return $result;		
		}
		if(!empty($data['excluded_product_ids'])){							
			foreach($data['excluded_product_ids'] as $checkexproduct){
				$productdatacheck  = wc_get_product($checkexproduct);
				if(!$productcheck->is_type('variable') && $total['product_id'] == $checkexproduct || $productdatacheck->is_type('variable') && $total['product_id'] == $checkexproduct || $productdatacheck->is_type('variation') && isset($total['variation_id']) && $total['variation_id'] == $checkexproduct ){
					$result['resulterror'] = 1;
					$result['typeerror'] = 'exproducts';
					return $result;
				}
			}							
		}
		$checktotalcate = 0;
		if(!empty($data['product_categories'])){						
			foreach($categories as $checkcate){
				if(in_array($checkcate,$data['product_categories'])){
					$checktotalcate++;
				}
			}
		}
		if(!empty($data['product_categories']) && $checktotalcate === 0){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'categories';
			return $result;		
		}
		$checktotalcateex = 0;
		if(!empty($data['excluded_product_categories'])){
			foreach($categories as $checkcate){
				if(in_array($checkcate,$data['excluded_product_categories'])){
					$checktotalcateex++;
				}
			}
		}
		if(!empty($data['excluded_product_categories']) && $checktotalcateex > 0){
			$result['resulterror'] = 1;
			$result['typeerror'] = 'excategories';
			return $result;			
		}
		if($data['exclude_sale_items']){
			if($productcheck->is_on_sale()){
				$result['resulterror'] = 1;
				$result['typeerror'] = 'sale';
				return $result;
			}
		}	
		if(!empty($data['email_restrictions'])){
			$userid = get_current_user_id();
			$jwtpuclib = new BAMobile_JWT_Auth();
			$currentuser = $jwtpuclib->bamobile_determine_current_user($userid);
			if($currentuser > 0){
				$currentdata = get_user_by('id',$currentuser);
				$currentdata = (array)$currentdata->data;
				$emailcurrent = $currentdata['user_email'];
				if($emailcurrent != $data['email_restrictions'][0]){
					$result['resulterror'] = 1;
					$result['typeerror'] = 'email_res';
					return $result;
				}
			}else{
				$result['resulterror'] = 1;
				$result['typeerror'] = 'email_res';
				return $result;
			}
		}
	}

	private function getCheckUsageRestriction($data,$total){
		$productcheck = wc_get_product($total['product_id']);
		$categories = $productcheck->get_category_ids();
		$totalpro = 0;
		$totalerror = 0;
		if(!empty($data['product_ids'])){
			foreach($data['product_ids'] as $checkinproduct){
				$productdatacheck  = wc_get_product($checkinproduct);
				if(!$productcheck->is_type('variable') && $total['product_id'] != $checkinproduct || $productdatacheck->is_type('variable') && $total['product_id'] != $checkinproduct || $productdatacheck->is_type('variation') && isset($total['variation_id']) && $total['variation_id'] != $checkinproduct ){
					$totalerror++;
				}
				$totalpro++;
			}
		}
		if(!empty($data['product_ids']) && $totalerror == $totalpro){
			return 1;
		}
		if(!empty($data['excluded_product_ids'])){							
			foreach($data['excluded_product_ids'] as $checkexproduct){
				$productdatacheck  = wc_get_product($checkexproduct);
				if(!$productcheck->is_type('variable') && $total['product_id'] == $checkexproduct || $productdatacheck->is_type('variable') && $total['product_id'] == $checkexproduct || $productdatacheck->is_type('variation') && isset($total['variation_id']) && $total['variation_id'] == $checkexproduct ){
					return 1;
				}
			}							
		}
		$checktotalcate = 0;
		if(!empty($data['product_categories'])){	
			foreach($categories as $checkcate){
				if(in_array($checkcate,$data['product_categories'])){
					$checktotalcate++;
				}
			}
		}
		if(!empty($data['product_categories']) && $checktotalcate === 0){
			return 1;
		}
		$checktotalcateex = 0;
		if(!empty($data['excluded_product_categories'])){
			foreach($categories as $checkcate){
				if(in_array($checkcate,$data['excluded_product_categories'])){
					$checktotalcateex++;
				}
			}
		}
		if(!empty($data['excluded_product_categories']) && $checktotalcateex > 0){
			return 1;
		}
		if($data['exclude_sale_items']){
			if($productcheck->is_on_sale()){
				return 1;
			}
		}	
		if(!empty($data['email_restrictions'])){
			$userid = get_current_user_id();
			$jwtpuclib = new BAMobile_JWT_Auth();
			$currentuser = $jwtpuclib->bamobile_determine_current_user($userid);
			if($currentuser > 0){
				$currentdata = get_user_by('id',$currentuser);
				$currentdata = (array)$currentdata->data;
				$emailcurrent = $currentdata['user_email'];
				if($emailcurrent != $data['email_restrictions'][0]){
					return 1;
				}
			}else{
				return 1;
			}
		}
	}
	
	private function calculatorMoneyCoupon($total,$data,$subprice = 0,$checkver = 0,$ratecurrency){				
		if($data['discount_type'] === 'percent' && $checkver === 1){
			$numberdiscount = $data['amount'];
			$subtotalindis = $this->getSubtotalbyCouponPercent($data,$total['subtotal'],$numberdiscount);				
			$totaldiscount = $total['subtotal'] - $subtotalindis;											
		}
		elseif($data['discount_type'] === 'percent' && $checkver === 2){
			$totaldiscount = $subprice;						
		}	
		elseif($data['discount_type'] === 'fixed_cart' && $checkver === 0){					
			$subtotalindis = $this->getSubtotalbyCouponCart($data,$subprice,$ratecurrency);							
			if($total['subtotal'] === 0){
				$totaldiscount = 0;
			}else{
				if(($data['amount']*$ratecurrency) >= $total['subtotal']){								
					$totaldiscount = $total['subtotal'];													
				}						
				else{				
					$totaldiscount = ($data['amount']*$ratecurrency);										
				}
			}			
		}elseif($data['discount_type'] === 'fixed_cart' && $checkver === 1){
			$one = $subprice/100;
			$rate = $total['subtotal']/$one;
			$totaldiscount = (($data['amount']*$ratecurrency)*$rate)/100;
		}
		elseif($data['discount_type'] === 'fixed_product' && $checkver === 0){		
			if(is_object($total)){
				$subtotal = $total->subtotal;
			}else{
				$subtotal = $total['subtotal'];
			}
			$subtotalindis = $this->getSubtotalbyCouponProduct($data,$subtotal,$ratecurrency);			
			if($subtotal === 0){
				$totaldiscount = 0;
			}else{
				if(($data['amount']*$ratecurrency) >= $subtotal){							
					$totaldiscount = $subtotal;													
				}						
				else{					
					$totaldiscount = ($data['amount']*$ratecurrency);					
				}
			}												
		}
		$listtotal = array(
			'couponcode' => $data['code'],								
			'totaldiscount'  => $totaldiscount,
			'product_id' => $total['product_id'],
			'quantity' => $total['quantity'],
			'variation_id' => $total['variation_id']	
		);
		return $listtotal;
	}	
	
	private function getTotalnoCoupon($post,$request,$addons = array()){
		$params = $request->get_params();
		if(isset($params['woo_currency'])){
			$currentkey = $params['woo_currency'];					
		}else{
			$currentkey = strtolower(get_woocommerce_currency());
		}
		$currencys = WooConnectorGetCurrency($currentkey);
		$ratecurrency = $currencys['rate'];
		$number_of_decimals = $currencys['number_of_decimals'];
		$postid = absint($post->product_id);
		$quantity = $post->quantity;
		$idvar = !empty($post->variation_id) ? absint($post->variation_id) : null;
		$pros = wc_get_product($postid);
		$status = $pros->get_tax_status();
		$pro_v = new WC_Product_Variable($postid);
		$productvari = $pro_v->get_available_variations();
		if($idvar != null || !empty($idvar)){
			$ratec = $this->getRates($idvar);	
			$provari = 	wc_get_product($idvar);
			$shippingid = $provari->get_shipping_class_id();
			foreach($productvari as $value => $val){
				if($idvar == $val['variation_id']){
					$subtotal = 0;
					$tax = 0;
					if(WC_VERSION < 3.1){
						$subtotal = ($val['price'])*$quantity;
						if($status == 'taxable'){					
							$tax = (($val['price'])*$ratec/100)*$quantity;
						}else{
							$tax = 0;
						}
					}else{
						$subtotal = ($val['display_price'])*$quantity;
						if($status == 'taxable'){					
						$tax = (($val['display_price'])*$ratec/100)*$quantity;
						}else{
							$tax = 0;
						}
					}		
					$total = $subtotal + $tax;		
					$list = array(
						'subtotal' => (float)wc_format_decimal($subtotal,$number_of_decimals),
						'tax'      => (float)wc_format_decimal($tax,$number_of_decimals),
						'total'    => (float)wc_format_decimal($total,$number_of_decimals),
						'product_id'   => $postid,
						'quantity'   => $quantity,
						'variation_id'   => $idvar,
						'class_shipping_id' => $shippingid
					);
				}else{
					continue;
				}	
			}
		}
		else{
			$shippingid = $pros->get_shipping_class_id();
			$ratec = $this->getRates($postid);	
			$subtotal = ($pros->get_price())*$quantity;
			if($status == 'taxable'){
				$tax = (($pros->get_price())*$ratec/100)*$quantity;
			}else{
				$tax = 0;
			}
			$total = $subtotal + $tax;
			$list = array(
				'subtotal' => (float)wc_format_decimal($subtotal,$number_of_decimals),
				'tax'      => (float)wc_format_decimal($tax,$number_of_decimals),
				'total'    => (float)wc_format_decimal($total,$number_of_decimals),
				'product_id'   => $postid,
				'quantity'   => $quantity,
				'variation_id'   => $idvar,
				'class_shipping_id' => $shippingid
			);
		}
		if(!empty($addons) && is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php')){
			$i = 0;
			$idaddons = $addons->id;
			$positionaddons = $addons->positions;
			$meta_data = $pros->get_meta_data();
			$listoption = array();
			$optionprices = 0;
			foreach($meta_data as $meta){
				if($meta->id == $idaddons && $meta->key == '_product_addons'){
					$optionprice = 0;
					$valueaddons = $meta->value;
					foreach($positionaddons as $position){
						$positionindex = $position->position;
						$positionvalues = $position->value;
						foreach($valueaddons as $val){
							if($val['position'] == $positionindex){
								$options = $val['options'];
								for($i; $i < count($options); $i++){
									foreach($positionvalues as $valuep){
										if($i === $valuep){
											$price = (float)wc_format_decimal( ($options[$i]['price'] * $ratecurrency), $number_of_decimals );
											$optionprice += $price;
											$listoption[] = array(
												'name'  => $val['name'],
												'value' => $options[$i]['label'],
												'price' => $price,
											);
										}else{
											continue;
										}
									}
								}
							}
						}
					}
					$optionprices += $optionprice;
				}
			}
			$list['subtotal'] = $list['subtotal'] + $optionprices;
			if($idvar != null || !empty($idvar)){
				$ratec = $this->getRates($idvar);
			}else{
				$ratec = $this->getRates($postid);
			}
			if($status == 'taxable'){					
				$list['tax'] = $list['subtotal']*($ratec/100);
			}else{
				$tax = 0;
			}
			$list['total'] = $list['subtotal'] + $list['tax'];
			$list['addons'] = $listoption;
		}
		return apply_filters('wooconnector_get_total',$list,$post,$addons);
	}		
	
	private function validateInput($product_id,$variation_id,$quantity){
		$getpro = wc_get_product($product_id);
		$getpost = get_post($product_id);
		if(!is_object($getpro) || !empty($getpro) && $getpro->get_id() <= 0 || empty($getpro) || !empty($getpost) && $getpost->post_status == 'trash'){
			return $this->returnErrorInput( 'rest_product_error', __( 'Sorry, Product not exits.','wooconnector' ),$product_id, array( 'status' => 401 ) );
		}	
		$checkorder = $getpro;
		if($variation_id != 0){
			$checkorder = wc_get_product($variation_id);
		}
		if ( get_option( 'woocommerce_hold_stock_minutes' ) > 0 && ! $checkorder->backorders_allowed() && $checkorder->get_manage_stock() !== false) {
			global $wpdb;
			$order_id   = isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0;
			$held_stock = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT SUM( order_item_meta.meta_value ) AS held_qty
					FROM {$wpdb->posts} AS posts
					LEFT JOIN {$wpdb->prefix}woocommerce_order_items as order_items ON posts.ID = order_items.order_id
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
					LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta2 ON order_items.order_item_id = order_item_meta2.order_item_id
					WHERE 	order_item_meta.meta_key   = '_qty'
					AND 	order_item_meta2.meta_key  = %s AND order_item_meta2.meta_value  = %d
					AND 	posts.post_type            IN ( '" . implode( "','", wc_get_order_types() ) . "' )
					AND 	posts.post_status          = 'wc-pending'
					AND		posts.ID                   != %d;",
					'variation' === get_post_type( $checkorder->get_stock_managed_by_id() ) ? '_variation_id' : '_product_id',
					$checkorder->get_stock_managed_by_id(),
					$order_id
				)
			);

			if ( $checkorder->get_stock_quantity() < ( $held_stock + $quantity ) ) {
				return $this->returnErrorInput( 'rest_order_stock_error', sprintf( __( 'Sorry, we do not have enough %1$s in stock to fulfill your order right now. Please try again in %2$d minutes or edit your cart and try again. We apologize for any inconvenience caused.', 'wooconnector' ),  apply_filters('mobiconnector_languages',$checkorder->get_name()), get_option( 'woocommerce_hold_stock_minutes' ) ),$product_id, array( 'status' => 401 ) );
			}
		}		
		$name = apply_filters('mobiconnector_languages',$getpro->get_name());
		$parent = $getpro->get_parent_id();
		$post = get_post($product_id);
		$type = $post->post_type;
		if($parent !== 0 || $type != 'product'){
			return $this->returnErrorInput( 'rest_product_error', __( 'Sorry, This is not product.','wooconnector' ),$product_id, array( 'status' => 401 ) );
		}	
		if($quantity <= 0){
			return $this->returnErrorInput( 'rest_quantity_error', sprintf(__( 'Sorry, Product %s quantity for must be greater than 0.','wooconnector' ),$name),$product_id, array( 'status' => 401 ) );
		}	
		$product_vari = null;
		$namevariation = '';	
		if($variation_id != 0){
			$product_vari = wc_get_product($variation_id);
			$namevariation = apply_filters('post_title',$product_vari->get_name());
			$checkids = $getpro->get_children();
			if(!in_array($variation_id,$checkids)){
				return $this->returnErrorInput( 'rest_variation_error', sprintf(__( 'Sorry, Variation %1$s does not belong to Products %2$s.','wooconnector' ),$namevariation,$name),$product_id, array( 'status' => 401 ) );
			}
		}
		if($variation_id != 0){
			$sold_individually = $product_vari->get_sold_individually();
			if($sold_individually){
				if($quantity != 1){
					return $this->returnErrorInput( 'rest_quantity_error', sprintf(__( 'Sorry, Product %s only sells quantity 1 .','wooconnector' ),$namevariation),$product_id, array( 'status' => 401 ) );
				}
			}
		}else{
			$sold_individually = $getpro->get_sold_individually();
			if($sold_individually){
				if($quantity != 1){
					return $this->returnErrorInput( 'rest_quantity_error', sprintf(__( 'Sorry, Product %s only sells quantity 1 .','wooconnector' ),$name),$product_id, array( 'status' => 401 ) );
				}
			}
		}
		$stock = 'outofstock';
		$stockquantity = 0;
		$backorderAllow = false;
		$backorders = 'no';
		$managestock = false;
		$iderror = 0;
		$nameout = '';
		if($getpro->is_type( 'variable' ) && $getpro->has_child()){
			$variation = wc_get_product($variation_id);
			$stock = $variation->get_stock_status();
			$stockquantity = $variation->get_stock_quantity();
			$managestock = $variation->get_manage_stock();
			$backorders = $variation->get_backorders();
			$backorderAllow = $variation->backorders_allowed();
			$iderror = $variation_id;
			$nameout = apply_filters('post_title',$variation->get_name());
		}else{
			$stock = $getpro->get_stock_status();
			$stockquantity = $getpro->get_stock_quantity();
			$managestock = $getpro->get_manage_stock();
			$backorders = $getpro->get_backorders();
			$backorderAllow = $getpro->backorders_allowed();	
			$iderror = $product_id;		
			$nameout = apply_filters('post_title',$getpro->get_name());
		}
		if($stock == 'outofstock' && $managestock == false){
			return $this->returnErrorInput( 'rest_stock_error', sprintf(__( 'Sorry, Product %s out of stock.', 'wooconnector' ),$nameout),$iderror, array( 'status' => 401 ) );
		}
		if($stock == 'outofstock' && $managestock == true && $backorders == 'no' && $backorderAllow == false){
			return $this->returnErrorInput( 'rest_stock_error', sprintf(__( 'Sorry, Product %s out of stock.', 'wooconnector' ),$nameout),$iderror, array( 'status' => 401 ) );
		}
		if($stock == 'instock' && $managestock == true && $quantity > $stockquantity && ($backorders == 'no' || $backorderAllow == false)){
			return $this->returnErrorInput( 'rest_stock_error', sprintf(__( 'Sorry, Product not enough.', 'wooconnector' ),$nameout),$iderror, array( 'status' => 401 ) );
		}
	}	
	
	private function returnErrorInput($code,$message,$productid,$status){
		return array(
			'code' => $code,
			'message' => $message,
			'product_name' => apply_filters('post_title',get_the_title($productid)),
			'product' => $productid,
			'data' => $status
		);
	}

	private function getSubtotalbyCouponPercent($data,$subtotal,$discount){		
		$pricediscount = ($subtotal*$discount)/100;
		$subtotalindis = $subtotal - $pricediscount;
		return $subtotalindis;
	}

	private function getSubtotalbyCouponCart($data,$subtotal,$ratecurrency){		
		$subtotalindis = ($data['amount']*$ratecurrency) - $subtotal;
		if(($data['amount']*$ratecurrency) >= $subtotal){
			$result = 0; 
		}
		else{
			$result = ($data['amount']*$ratecurrency);
			$subtotalindis = 0;
		}								
		return $result;						
	}

	private function getSubtotalbyCouponProduct($data,$subtotal,$ratecurrency){			
		$subtotalindis = ($data['amount']*$ratecurrency) - $subtotal;
		if(($data['amount']*$ratecurrency) >= $subtotal){
			$result = 0; 
		}
		else{
			$result = ($data['amount']*$ratecurrency);
			$subtotalindis = 0;
		}								
		return $result;						
	}

	
}	
$WooConnectorCalculator = new WooConnectorCalculator();
?>