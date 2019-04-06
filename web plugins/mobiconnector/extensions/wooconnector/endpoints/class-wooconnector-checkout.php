<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('WP_REST_Controller')) {
	require_once(ABSPATH . 'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooConnectorCheckOut extends WP_REST_Controller
{
	private $rest_url = 'wooconnector/checkout';

	public function __construct()
	{
		$this->register_routes();
	}

	public function register_routes()
	{
		add_action('rest_api_init', array($this, 'register_api_hooks'));
	}

	public function register_api_hooks()
	{
		register_rest_route($this->rest_url, '/processcheckoutform', array(
			'methods' => 'POST',
			'callback' => array($this, 'process_checkout_form'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => array(),
		));
		register_rest_route($this->rest_url, '/processcheckout', array(
			'methods' => 'POST',
			'callback' => array($this, 'process_checkout'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => $this->getParams(),
		));
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check($request)
	{
		if (is_plugin_active('mobiconnector/mobiconnector.php')) {
			$usekey = get_option('mobiconnector_settings-use-security-key');
			if ($usekey == 1 && !bamobile_mobiconnector_rest_check_post_permissions($request)) {
				return new WP_Error('mobiconnector_rest_cannot_view', __('Sorry, you cannot list resources.', 'mobiconnector'), array('status' => rest_authorization_required_code()));
			}
		}
		return true;
	}

	public function process_checkout_form($request)
	{
		$parameters = $request->get_params();
		$pro = $parameters['products'];
		$products = json_decode($pro);
		foreach ($products as $product) {
			$product_id = absint($product->product_id);
			$quantity = $product->quantity;
			$variation_id = isset($product->variation_id) ? absint($product->variation_id) : null;
			$attributes = isset($prod->attributes) ? $prod->attributes : array();
			$addons = isset($product->addons) ? $product->addons : array();
			$getpro = wc_get_product($product_id);
			if (!is_object($getpro) || !empty($getpro) && $getpro->get_id() <= 0 || empty($getpro)) {
				return new WP_Error('rest_product_error', __('Sorry, Product not exits.', 'wooconnector'), array('status' => 400));
			}
			$parent = $getpro->get_parent_id();
			$post = get_post($product_id);
			$type = $post->post_type;
			if ($parent !== 0 || $type != 'product') {
				return new WP_Error('rest_product_error', __('Sorry, This is not product.', 'wooconnector'), array('status' => 400));
			}
			$checkorder = $getpro;
			if ($variation_id != 0) {
				$checkorder = wc_get_product($variation_id);
			}
			if (get_option('woocommerce_hold_stock_minutes') > 0 && !$getpro->backorders_allowed() && $checkorder->get_manage_stock() !== false) {
				global $wpdb;
				$order_id = isset(WC()->session->order_awaiting_payment) ? absint(WC()->session->order_awaiting_payment) : 0;
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
						AND 	posts.post_type            IN ( '" . implode("','", wc_get_order_types()) . "' )
						AND 	posts.post_status          = 'wc-pending'
						AND		posts.ID                   != %d;",
						'variation' === get_post_type($getpro->get_stock_managed_by_id()) ? '_variation_id' : '_product_id',
						$getpro->get_stock_managed_by_id(),
						$order_id
					)
				);
				if ($getpro->get_stock_quantity() < ($held_stock + $quantity)) {
					return $this->returnErrorInput('rest_order_stock_error', sprintf(__('Sorry, we do not have enough %1$s in stock to fulfill your order right now. Please try again in %2$d minutes or edit your cart and try again. We apologize for any inconvenience caused.', 'wooconnector'), apply_filters('mobiconnector_languages', $getpro->get_name()), get_option('woocommerce_hold_stock_minutes')), $product_id, array('status' => 400));
				}
			}
			if ($quantity <= 0) {
				return new WP_Error('rest_quantity_error', __('Sorry,Your Quantity Quantity must be greater than 0.', 'wooconnector'), array('status' => 400));
			}
			if ($variation_id != 0) {
				$checkids = $getpro->get_children();
				if (!in_array($variation_id, $checkids)) {
					return new WP_Error('rest_variation_error', __('Sorry, Variation does not belong to Products.', 'wooconnector'), array('status' => 400));
				}
			}
			$sold_individually = $getpro->get_sold_individually();
			if ($sold_individually) {
				if ($quantity != 1) {
					return new WP_Error('rest_quantity_error', __('Sorry, Product only sells quantity 1.', 'wooconnector'), array('status' => 400));
				}
			}
			$stock = 'outofstock';
			$stockquantity = 0;
			$backorderAllow = false;
			$backorders = 'no';
			$managestock = false;
			$iderror = 0;
			if ($getpro->is_type('variable') && $getpro->has_child()) {
				$variation = wc_get_product($variation_id);
				$stock = $variation->get_stock_status();
				$stockquantity = $variation->get_stock_quantity();
				$managestock = $variation->get_manage_stock();
				$backorders = $variation->get_backorders();
				$backorderAllow = $variation->backorders_allowed();
				$iderror = $variation_id;
			} else {
				$stock = $getpro->get_stock_status();
				$stockquantity = $getpro->get_stock_quantity();
				$managestock = $getpro->get_manage_stock();
				$backorders = $getpro->get_backorders();
				$backorderAllow = $getpro->backorders_allowed();
				$iderror = $product_id;
			}
			if ($stock == 'outofstock' && $managestock == false) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product out of stock.', 'wooconnector'), $iderror, array('status' => 400));
			}
			if ($stock == 'outofstock' && $managestock == true && $backorders == 'no' && $backorderAllow == false) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product out of stock.', 'wooconnector'), $iderror, array('status' => 400));
			}
			if ($stock == 'instock' && $managestock == true && $quantity > $stockquantity && ($backorders == 'no' || $backorderAllow == false)) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product not enough.', 'wooconnector'), $iderror, array('status' => 400));
			}
		}
		$validation = new WC_Validation();
		if (is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php'))) {
			$countries = new WC_Countries();
			$billing_country = $parameters['billing_country'];
			$shipping_country = isset($parameters['shipping_country']) ? $parameters['shipping_country'] : '';
			$billing_address = $countries->get_address_fields($billing_country, 'billing_');
			$shipping_address = $countries->get_address_fields($shipping_country, 'shipping_');
			$ship_to_different_address = isset($parameters['ship_to_different_address']) ? $parameters['ship_to_different_address'] : false;
			$data = array();
			if (!empty($billing_address)) {
				foreach ($billing_address as $keyb => $valb) {
					if (isset($valb['required']) && $valb['required'] == true && !isset($parameters[$keyb])) {
						return new WP_Error('rest_field_require', sprintf(__('Sorry, %s is required', 'wooconnector'), $valb['label']), array('status' => 400));
					}
					if (isset($parameters[$keyb])) {
						if ($keyb == 'billing_phone' && !$validation->is_phone($parameters[$keyb])) {
							return new WP_Error('rest_phone_error', __('Sorry, Incorrect phone number.', 'wooconnector'), array('status' => 400));
						} elseif ($keyb == 'billing_email' && !$validation->is_email($parameters[$keyb])) {
							return new WP_Error('rest_email_error', __('Sorry, Incorrect email.', 'wooconnector'), array('status' => 400));
						} elseif ($keyb == 'billing_postcode' && !$validation->is_postcode($parameters[$keyb], $billing_country)) {
							return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 400));
						} else {
							$valb['value'] = $parameters[$keyb];
						}
					}
					$data[$keyb] = $valb;
				}
			}
			if ($ship_to_different_address == 1) {
				if (!empty($shipping_address)) {
					foreach ($shipping_address as $keys => $vals) {
						if (isset($vals['required']) && $vals['required'] == true && !isset($parameters[$keys])) {
							return new WP_Error('rest_field_require', sprintf(__('Sorry, %s is required', 'wooconnector'), $vals['label']), array('status' => 400));
						}
						if (isset($parameters[$keys])) {
							if ($keys == 'shipping_postcode' && !$validation->is_postcode($parameters[$keys], $shipping_country)) {
								return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 400));
							} else {
								$vals['value'] = $parameters[$keys];
							}
						}
						$data[$keys] = $vals;
					}
				}
			}
			$data['use_form'] = 1;
		} else {
			$billing_first_name = $parameters['billing_first_name'];
			$billing_last_name = $parameters['billing_last_name'];
			$billing_company = isset($parameters['billing_company']) ? $parameters['billing_company'] : '';
			$billing_country = $parameters['billing_country'];
			$billing_address_1 = $parameters['billing_address_1'];
			$billing_address_2 = isset($parameters['billing_address_2']) ? $parameters['billing_address_2'] : '';
			$billing_state = isset($parameters['billing_state']) ? $parameters['billing_state'] : '';
			$billing_city = $parameters['billing_city'];
			$billing_phone = $validation->format_phone($parameters['billing_phone']);
			if (!$validation->is_phone($billing_phone)) {
				return new WP_Error('rest_phone_error', __('Sorry, Incorrect phone number.', 'wooconnector'), array('status' => 401));
			}
			$billing_email = $parameters['billing_email'];
			if (!$validation->is_email($billing_email)) {
				return new WP_Error('rest_email_error', __('Sorry, Incorrect email.', 'wooconnector'), array('status' => 401));
			}
			$billing_postcode = $validation->format_postcode($parameters['billing_postcode'], $billing_country);
			if (!$validation->is_postcode($billing_postcode, $billing_country)) {
				return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 401));
			}
			$shipping_first_name = isset($parameters['shipping_first_name']) ? $parameters['shipping_first_name'] : '';
			$shipping_last_name = isset($parameters['shipping_last_name']) ? $parameters['shipping_last_name'] : '';
			$shipping_company = isset($parameters['shipping_company']) ? $parameters['shipping_company'] : '';
			$shipping_country = isset($parameters['shipping_country']) ? $parameters['shipping_country'] : '';
			$shipping_address_1 = isset($parameters['shipping_address_1']) ? $parameters['shipping_address_1'] : '';
			$shipping_address_2 = isset($parameters['shipping_address_2']) ? $parameters['shipping_address_2'] : '';
			$shipping_city = isset($parameters['shipping_city']) ? $parameters['shipping_city'] : '';
			$shipping_postcode = isset($parameters['shipping_postcode']) ? $validation->format_postcode($parameters['shipping_postcode'], $shipping_country) : '';
			if (!$validation->is_postcode($shipping_postcode, $shipping_country)) {
				return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 401));
			}
			$shipping_state = isset($parameters['shipping_state']) ? $parameters['shipping_state'] : '';
			$data = array(
				'billing_first_name' => $billing_first_name,
				'billing_last_name' => $billing_last_name,
				'billing_company' => $billing_company,
				'billing_country' => $billing_country,
				'billing_address_1' => $billing_address_1,
				'billing_address_2' => $billing_address_2,
				'billing_state' => $billing_state,
				'billing_city' => $billing_city,
				'billing_phone' => $billing_phone,
				'billing_email' => $billing_email,
				'billing_postcode' => $billing_postcode,
				'shipping_first_name' => $shipping_first_name,
				'shipping_last_name' => $shipping_last_name,
				'shipping_company' => $shipping_company,
				'shipping_country' => $shipping_country,
				'shipping_address_1' => $shipping_address_1,
				'shipping_address_2' => $shipping_address_2,
				'shipping_city' => $shipping_city,
				'shipping_postcode' => $shipping_postcode,
				'shipping_state' => $shipping_state,
			);
		}
		$ship = get_option('woocommerce_ship_to_countries');
		$shipping_method = isset($parameters['shipping_method']) ? $parameters['shipping_method'] : '';
		if ($ship != 'disabled' && $shipping_method == '' || $ship != 'disabled' && empty($shipping_method)) {
			return new WP_Error('rest_params_error', __('Sorry, You must choose a Shipping.', 'wooconnector'), array('status' => 400));
		}
		$payment_method = $parameters['payment_method'];
		$stripecheckout = '';
		$settingsstrip = get_option('woocommerce_stripe_settings');
		if (!empty($settingsstrip)) {
			if (!empty($settingsstrip['stripe_checkout'])) {
				$stripecheckout = $settingsstrip['stripe_checkout'];
				if ($payment_method != 'stripe' || $payment_method == 'stripe' && $stripecheckout != '' && $stripecheckout != 'yes') {
					$_SESSION['current_payment_method'] = $payment_method;
				}
			}
		}
		$order_comments = isset($parameters['order_comments']) ? $parameters['order_comments'] : '';
		$onesignal_player_id = isset($parameters['onesignal_player_id']) ? $parameters['onesignal_player_id'] : false;
		$userid = get_current_user_id();
		$data['ship_to_different_address'] = $ship_to_different_address;
		$data['onesignal_player_id'] = $onesignal_player_id;
		$data['order_comments'] = $order_comments;
		$data['shipping_method'] = $shipping_method;
		$data['payment_method'] = $payment_method;
		$data['session_key'] = $userid;
		$data['products'] = $pro;
		$data['stripecheckout'] = $stripecheckout;
		if (isset($parameters['coupons']) || !empty($parameters['coupons'])) {
			$basecoupon = $parameters['coupons'];
			$coupons = json_decode($basecoupon);
			$checkcoupon = array();
			$checkidden = 0;
			if (is_null($coupons)) {
				return new WP_Error('rest_product_error', __('Sorry, Coupon not exits .', 'wooconnector'), array('status' => 400));
			}
			if (is_user_logged_in()) {
				$currentuser = $userid;
			} else {
				$currentuser = $data['billing_email']['value'];
			}
			foreach ($coupons as $coupon) {
				$cp = new WC_Coupon($coupon);
				$datacp = $cp->get_data();
				$error = $this->validateCoupon($coupon, $datacp, $currentuser, $data['billing_email']['value']);
				if (!empty($error)) {
					return $error;
				}
				if (!in_array($coupon, $checkcoupon)) {
					if (!empty($checkcoupon)) {
						foreach ($checkcoupon as $ckcoup) {
							$ckcp = new WC_Coupon($ckcoup);
							$checkdata = $ckcp->get_data();
							if ($checkdata['individual_use'] == true && $checkidden > 0) {
								return new WP_Error('rest_coupon_error_exists', __('Sorry, Coupon only one.', 'wooconnector'), array('status' => 400));
							}
						}
					}
					if ($datacp['individual_use'] == true && $checkidden > 0) {
						return new WP_Error('rest_coupon_error_delete', __('Sorry, Coupon only one.', 'wooconnector'), array('status' => 400));
					}
					array_push($checkcoupon, $coupon);
					$checkidden++;
				} else {
					return new WP_Error('rest_coupon_error', __('Sorry, Coupon already applied!.', 'wooconnector'), array('status' => 400));
				}
			}
			$data['coupons'] = $basecoupon;
		}
		$datainput = serialize($data);
		$datetime = date_create('now')->format('Y-m-d H:i:s');
		$firstcode = md5($datetime);
		require_once(ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash(8, false);
		$newcode = md5($hasher->get_random_bytes(32));
		$code = $firstcode . $newcode;
		global $wpdb;
		$table_name = $wpdb->prefix . "wooconnector_data";
		$code = esc_sql($code);
		$datas = $wpdb->get_results("
			SELECT data 
			FROM $table_name
			WHERE data_key = '$code'
		");
		if (empty($datas)) {
			$wpdb->insert(
				"$table_name",
				array(
					"data_key" => $code,
					"create_time" => $datetime,
					"data" => $datainput
				),
				array(
					'%s',
					'%s',
					'%s'
				)
			);
			return $code;
		} else {
			return new WP_Error('rest_coupon_error', __('Sorry, Please try again.', 'wooconnector'), array('status' => 400));
		}
	}

	public function process_checkout($request)
	{
		$parameters = $request->get_params();
		$pro = $parameters['products'];
		$products = json_decode($pro);
		foreach ($products as $product) {
			$product_id = absint($product->product_id);
			$quantity = $product->quantity;
			$variation_id = isset($product->variation_id) ? absint($product->variation_id) : null;
			$addons = isset($product->addons) ? $product->addons : array();
			$getpro = wc_get_product($product_id);
			if (!is_object($getpro) || !empty($getpro) && $getpro->get_id() <= 0 || empty($getpro)) {
				return new WP_Error('rest_product_error', __('Sorry, Product not exits.', 'wooconnector'), array('status' => 401));
			}
			$parent = $getpro->get_parent_id();
			$post = get_post($product_id);
			$type = $post->post_type;
			if ($parent !== 0 || $type != 'product') {
				return new WP_Error('rest_product_error', __('Sorry, This is not product.', 'wooconnector'), array('status' => 401));
			}
			$checkorder = $getpro;
			if ($variation_id != 0) {
				$checkorder = wc_get_product($variation_id);
			}
			if (get_option('woocommerce_hold_stock_minutes') > 0 && !$getpro->backorders_allowed() && $checkorder->get_manage_stock() !== false) {
				global $wpdb;
				$order_id = isset(WC()->session->order_awaiting_payment) ? absint(WC()->session->order_awaiting_payment) : 0;
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
						AND 	posts.post_type            IN ( '" . implode("','", wc_get_order_types()) . "' )
						AND 	posts.post_status          = 'wc-pending'
						AND		posts.ID                   != %d;",
						'variation' === get_post_type($getpro->get_stock_managed_by_id()) ? '_variation_id' : '_product_id',
						$getpro->get_stock_managed_by_id(),
						$order_id
					)
				);

				if ($getpro->get_stock_quantity() < ($held_stock + $quantity)) {
					return $this->returnErrorInput('rest_order_stock_error', sprintf(__('Sorry, we do not have enough %1$s in stock to fulfill your order right now. Please try again in %2$d minutes or edit your cart and try again. We apologize for any inconvenience caused.', 'wooconnector'), apply_filters('mobiconnector_languages', $getpro->get_name()), get_option('woocommerce_hold_stock_minutes')), $product_id, array('status' => 401));
				}
			}
			if ($quantity <= 0) {
				return new WP_Error('rest_quantity_error', __('Sorry,Your Quantity Quantity must be greater than 0.', 'wooconnector'), array('status' => 401));
			}
			if ($variation_id != 0) {
				$checkids = $getpro->get_children();
				if (!in_array($variation_id, $checkids)) {
					return new WP_Error('rest_variation_error', __('Sorry, Variation does not belong to Products.', 'wooconnector'), array('status' => 401));
				}
			}
			$sold_individually = $getpro->get_sold_individually();
			if ($sold_individually) {
				if ($quantity != 1) {
					return new WP_Error('rest_quantity_error', __('Sorry, Product only sells quantity 1.', 'wooconnector'), array('status' => 401));
				}
			}
			$stock = 'outofstock';
			$stockquantity = 0;
			$backorderAllow = false;
			$backorders = 'no';
			$managestock = false;
			$iderror = 0;
			if ($getpro->is_type('variable') && $getpro->has_child()) {
				$variation = wc_get_product($variation_id);
				$stock = $variation->get_stock_status();
				$stockquantity = $variation->get_stock_quantity();
				$managestock = $variation->get_manage_stock();
				$backorders = $variation->get_backorders();
				$backorderAllow = $variation->backorders_allowed();
				$iderror = $variation_id;
			} else {
				$stock = $getpro->get_stock_status();
				$stockquantity = $getpro->get_stock_quantity();
				$managestock = $getpro->get_manage_stock();
				$backorders = $getpro->get_backorders();
				$backorderAllow = $getpro->backorders_allowed();
				$iderror = $product_id;
			}
			if ($stock == 'outofstock' && $managestock == false) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product out of stock.', 'wooconnector'), $iderror, array('status' => 401));
			}
			if ($stock == 'outofstock' && $managestock == true && $backorders == 'no' && $backorderAllow == false) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product out of stock.', 'wooconnector'), $iderror, array('status' => 401));
			}
			if ($stock == 'instock' && $managestock == true && $quantity > $stockquantity && ($backorders == 'no' || $backorderAllow == false)) {
				return $this->returnErrorInput('rest_stock_error', __('Sorry, Product not enough.', 'wooconnector'), $iderror, array('status' => 401));
			}
		}
		$validation = new WC_Validation();
		$billing_first_name = $parameters['billing_first_name'];
		$billing_last_name = $parameters['billing_last_name'];
		$billing_company = isset($parameters['billing_company']) ? $parameters['billing_company'] : '';
		$billing_country = $parameters['billing_country'];
		$billing_address_1 = $parameters['billing_address_1'];
		$billing_address_2 = isset($parameters['billing_address_2']) ? $parameters['billing_address_2'] : '';
		$billing_state = isset($parameters['billing_state']) ? $parameters['billing_state'] : '';
		$billing_city = $parameters['billing_city'];
		$billing_phone = $validation->format_phone($parameters['billing_phone']);
		if (!$validation->is_phone($billing_phone)) {
			return new WP_Error('rest_phone_error', __('Sorry, Incorrect phone number.', 'wooconnector'), array('status' => 401));
		}
		$billing_email = $parameters['billing_email'];
		if (!$validation->is_email($billing_email)) {
			return new WP_Error('rest_email_error', __('Sorry, Incorrect email.', 'wooconnector'), array('status' => 401));
		}
		$billing_postcode = $validation->format_postcode($parameters['billing_postcode'], $billing_country);
		if (!$validation->is_postcode($billing_postcode, $billing_country)) {
			return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 401));
		}
		$ship = get_option('woocommerce_ship_to_countries');
		$shipping_method = isset($parameters['shipping_method']) ? $parameters['shipping_method'] : '';
		if ($ship != 'disabled' && $shipping_method == '' || $ship != 'disabled' && empty($shipping_method)) {
			return new WP_Error('rest_params_error', __('Sorry, You must choose a Shipping.', 'wooconnector'), array('status' => 401));
		}
		$payment_method = $parameters['payment_method'];
		$stripecheckout = '';
		$settingsstrip = get_option('woocommerce_stripe_settings');
		if (!empty($settingsstrip)) {
			if (!empty($settingsstrip['stripe_checkout'])) {
				$stripecheckout = $settingsstrip['stripe_checkout'];
				if ($payment_method != 'stripe' || $payment_method == 'stripe' && $stripecheckout != '' && $stripecheckout != 'yes') {
					$_SESSION['current_payment_method'] = $payment_method;
				}
			}
		}
		$ship_to_different_address = isset($parameters['ship_to_different_address']) ? $parameters['ship_to_different_address'] : false;
		$shipping_first_name = isset($parameters['shipping_first_name']) ? $parameters['shipping_first_name'] : '';
		$shipping_last_name = isset($parameters['shipping_last_name']) ? $parameters['shipping_last_name'] : '';
		$shipping_company = isset($parameters['shipping_company']) ? $parameters['shipping_company'] : '';
		$shipping_country = isset($parameters['shipping_country']) ? $parameters['shipping_country'] : '';
		$shipping_address_1 = isset($parameters['shipping_address_1']) ? $parameters['shipping_address_1'] : '';
		$shipping_address_2 = isset($parameters['shipping_address_2']) ? $parameters['shipping_address_2'] : '';
		$shipping_city = isset($parameters['shipping_city']) ? $parameters['shipping_city'] : '';
		$shipping_postcode = isset($parameters['shipping_postcode']) ? $validation->format_postcode($parameters['shipping_postcode'], $shipping_country) : '';
		if (!$validation->is_postcode($shipping_postcode, $shipping_country)) {
			return new WP_Error('rest_postcode_error', __('Sorry, Incorrect postcode.', 'wooconnector'), array('status' => 401));
		}
		$shipping_state = isset($parameters['shipping_state']) ? $parameters['shipping_state'] : '';
		$order_comments = isset($parameters['order_comments']) ? $parameters['order_comments'] : '';
		$onesignal_player_id = isset($parameters['onesignal_player_id']) ? $parameters['onesignal_player_id'] : false;
		$userid = get_current_user_id();
		$data = array(
			'session_key' => $userid,
			'products' => $pro,
			'billing_first_name' => $billing_first_name,
			'billing_last_name' => $billing_last_name,
			'billing_company' => $billing_company,
			'billing_country' => $billing_country,
			'billing_address_1' => $billing_address_1,
			'billing_address_2' => $billing_address_2,
			'billing_state' => $billing_state,
			'billing_city' => $billing_city,
			'billing_phone' => $billing_phone,
			'billing_email' => $billing_email,
			'billing_postcode' => $billing_postcode,
			'shipping_method' => $shipping_method,
			'payment_method' => $payment_method,
			'ship_to_different_address' => $ship_to_different_address,
			'shipping_first_name' => $shipping_first_name,
			'shipping_last_name' => $shipping_last_name,
			'shipping_company' => $shipping_company,
			'shipping_country' => $shipping_country,
			'shipping_address_1' => $shipping_address_1,
			'shipping_address_2' => $shipping_address_2,
			'shipping_city' => $shipping_city,
			'shipping_postcode' => $shipping_postcode,
			'shipping_state' => $shipping_state,
			'order_comments' => $order_comments,
			'stripecheckout' => $stripecheckout,
			'onesignal_player_id' => $onesignal_player_id
		);
		if (isset($parameters['coupons']) || !empty($parameters['coupons'])) {
			$basecoupon = $parameters['coupons'];
			$coupons = json_decode($basecoupon);
			$checkcoupon = array();
			$checkidden = 0;
			if (is_null($coupons)) {
				return new WP_Error('rest_product_error', __('Sorry, Coupon not exits .', 'wooconnector'), array('status' => 401));
			}
			if (is_user_logged_in()) {
				$currentuser = $userid;
			} else {
				$currentuser = $billing_email;
			}
			foreach ($coupons as $coupon) {
				$cp = new WC_Coupon($coupon);
				$datacp = $cp->get_data();
				$error = $this->validateCoupon($coupon, $datacp, $currentuser, $billing_email);
				if (!empty($error)) {
					return $error;
				}
				if (!in_array($coupon, $checkcoupon)) {
					if (!empty($checkcoupon)) {
						foreach ($checkcoupon as $ckcoup) {
							$ckcp = new WC_Coupon($ckcoup);
							$checkdata = $ckcp->get_data();
							if ($checkdata['individual_use'] == true && $checkidden > 0) {
								return new WP_Error('rest_coupon_error_exists', __('Sorry, Coupon only one.', 'wooconnector'), array('status' => 401));
							}
						}
					}
					if ($datacp['individual_use'] == true && $checkidden > 0) {
						return new WP_Error('rest_coupon_error_delete', __('Sorry, Coupon only one.', 'wooconnector'), array('status' => 401));
					}
					array_push($checkcoupon, $coupon);
					$checkidden++;
				} else {
					return new WP_Error('rest_coupon_error', __('Sorry, Coupon already applied!.', 'wooconnector'), array('status' => 401));
				}
			}
			$data['coupons'] = $basecoupon;
		}
		$datainput = serialize($data);
		$datetime = date_create('now')->format('Y-m-d H:i:s');
		$firstcode = md5($datetime);
		require_once(ABSPATH . 'wp-includes/class-phpass.php');
		$hasher = new PasswordHash(8, false);
		$newcode = md5($hasher->get_random_bytes(32));
		$code = $firstcode . $newcode;
		global $wpdb;
		$table_name = $wpdb->prefix . "wooconnector_data";
		$code = esc_sql($code);
		$datas = $wpdb->get_results("
			SELECT data 
			FROM $table_name
			WHERE data_key = '$code'
		");
		if (empty($datas)) {
			$wpdb->insert(
				"$table_name",
				array(
					"data_key" => $code,
					"create_time" => $datetime,
					"data" => $datainput
				),
				array(
					'%s',
					'%s',
					'%s'
				)
			);
			return $code;
		} else {
			return new WP_Error('rest_coupon_error', __('Sorry, Please try again.', 'wooconnector'), array('status' => 401));
		}
	}

	private function returnErrorInput($code, $message, $productid, $status)
	{
		return array(
			'code' => $code,
			'message' => $message,
			'product_name' => apply_filters('mobiconnector_languages', get_the_title($productid)),
			'product' => $productid,
			'data' => $status
		);
	}

	private function validateCoupon($coupon, $listdata, $currentuser, $billing_email)
	{
		if ($listdata['id'] === 0) {
			return new WP_Error('rest_coupon_error', __('Sorry, Coupon not exist.', 'wooconnector'), array('status' => 400));
		}
		if ($listdata['usage_limit'] > 0 && $listdata['usage_count'] >= $listdata['usage_limit']) {
			return new WP_Error('rest_coupon_error', __('Sorry, Coupon usage limit has been reached.', 'wooconnector'), array('status' => 400));
		}
		if ($listdata['usage_limit_per_user'] > 0) {
			$userby = $listdata['used_by'];
			if (!empty($userby) && is_array($userby)) {
				$count = array_count_values($userby);
				if (!empty($count)) {
					if (!empty($count[$currentuser])) {
						$countuser = $count[$currentuser];
						if ($countuser >= $listdata['usage_limit_per_user']) {
							return new WP_Error('rest_coupon_error', __('Sorry, Coupon usage limit per user has been reached.', 'wooconnector'), array('status' => 400));
						}
					}
				}
			}
		}
		if (!empty($listdata['date_expires']) || $listdata['date_expires'] != '') {
			$date = $listdata['date_expires'];
			$currentdate = date('Y-m-dTH:i:s');
			if ($currentdate > $date) {
				return new WP_Error('rest_coupon_error', __('Sorry, Coupon has expired.', 'wooconnector'), array('status' => 400));
			}
		}
		if (!empty($listdata['email_restrictions'])) {
			if (!in_array($billing_email, $listdata['email_restrictions'])) {
				return array(
					'code' => 'rest_coupon_email_error',
					'message' => 'Sorry, it seems the coupon is not yours.',
					'coupon_error' => $coupon,
					'data' => array('status' => 400)
				);
			}
		}
	}

	public function getParams()
	{
		$data = array(
			'products' => array(
				'required' => true,
			),
			'coupons' => array(),
			'billing_first_name' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_last_name' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_company' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'billing_country' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_address_1' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_address_2' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'billing_city' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_postcode' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_phone' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_email' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'billing_state' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_method' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'payment_method' => array(
				'required' => true,
				'sanitize_callback' => 'esc_sql'
			),
			'ship_to_different_address' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_first_name' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_last_name' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_company' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_country' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_address_1' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_address_2' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_city' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_postcode' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'shipping_state' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'addons' => array(),
			'order_comments' => array(
				'sanitize_callback' => 'esc_sql'
			),
			'onesignal_player_id' => array(
				'sanitize_callback' => 'esc_sql'
			)
		);
		return $data;
	}
}
$WooConnectorCheckOut = new WooConnectorCheckOut();
?>