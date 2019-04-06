<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('WP_REST_Controller')) {
	require_once(ABSPATH . 'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooConnectorOrder extends WP_REST_Controller
{

	private $rest_url = 'wooconnector/order';

	protected $request = array();

	public function __construct()
	{
		$this->register_routes();
	}

	public function register_routes()
	{
		add_action('rest_api_init', array($this, 'register_api_hooks'));
	}
	
	// get total and shipping method : wp-json/wooconnector/calculator/gettotal
	public function register_api_hooks()
	{
		register_rest_route($this->rest_url, '/getorderbyterm', array(
			'methods' => 'GET',
			'callback' => array($this, 'getorderbyterm'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => array(
				'post_per_page' => array(
					'default' => 10,
					'sanitize_callback' => 'absint',
				),
				'post_num_page' => array(
					'default' => 1,
					'sanitize_callback' => 'absint',
				)
			),
		));

		register_rest_route($this->rest_url, '/changestatus', array(
			'methods' => 'POST',
			'callback' => array($this, 'changestatus'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => array(
				'order' => array(
					'required' => true,
					'sanitize_callback' => 'absint'
				)
			),
		));

		register_rest_route($this->rest_url, '/getorderbyid', array(
			'methods' => 'GET',
			'callback' => array($this, 'getorderbyid'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => array(
				'order' => array(
					'required' => true,
					'sanitize_callback' => 'absint'
				)
			),
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

	/** 
	 * Get all order of current user
	 */
	public function getorderbyterm($request)
	{
		$parameters = $request->get_params();
		$auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
		if (!$auth) {
			$auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}
		if (wooconnector_check_user_login_by_token($auth)) {
			$post_per_page = $parameters['post_per_page'];
			$post_num_page = $parameters['post_num_page'];
			$customer_orders = get_posts(array(
				'posts_per_page' => $post_per_page,
				'paged' => $post_num_page,
				'meta_key' => '_customer_user',
				'meta_value' => get_current_user_id(),
				'post_type' => wc_get_order_types(),
				'post_status' => array_keys(wc_get_order_statuses()),
			));
			$format_decimal = array('total');
			$format_date = array('date_created', 'date_modified', 'date_completed', 'date_paid');
			$this->request = $request;
			$list = array();
			foreach ($customer_orders as $customer_order) {
				$id = $customer_order->ID;
				$order = new WC_Order($id);
				$data = $order->get_data();
				$currency = $data['currency'];
				$currencys = WooConnectorGetCurrency(strtolower($currency));
				$number_of_decimals = $currencys['number_of_decimals'];
				$currenty_symbol = $currencys['symbol'];
				$ratecurrency = $currencys['rate'];
				$currency_position = $currencys['position'];
				$thousand_separator = $currencys['thousand_separator'];
				$decimal_separator = $currencys['decimal_separator'];
				$result = array(
					'id' => $id,
					'billings' => $this->getBillings($order),
					'payment_method' => $order->get_payment_method(),
					'payment_method_title' => $order->get_payment_method_title(),
					'date_completed' => $order->get_date_completed(),
					'date_paid' => $order->get_date_paid(),
					'date_created' => $order->get_date_created(),
					'date_modified' => $order->get_date_modified(),
					'line_items' => $this->getOrderItems($order),
					'total' => $order->get_total(),
					'status' => $order->get_status()
				);
				foreach ($format_date as $key) {
					$datetime = $result[$key];
					$result[$key] = wc_rest_prepare_date_response($datetime, false);
					$result[$key . '_gmt'] = wc_rest_prepare_date_response($datetime);
				}
				foreach ($format_decimal as $key) {
					if (!empty($result[$key])) {
						if ($currency_position == 'left') {
							$result[$key] = $currenty_symbol . (wc_format_decimal($result[$key], $number_of_decimals));
						} elseif ($currency_position == 'right') {
							$result[$key] = (wc_format_decimal($result[$key], $number_of_decimals)) . $currenty_symbol;
						} elseif ($currency_position == 'left_space') {
							$result[$key] = $currenty_symbol . ' ' . (wc_format_decimal($result[$key], $number_of_decimals));
						} elseif ($currency_position == 'right_space') {
							$result[$key] = (wc_format_decimal($result[$key], $number_of_decimals)) . ' ' . $currenty_symbol;
						}
					}
				}
				$list[] = $result;
			}
			return $list;
		} else {
			return new WP_Error('rest_order_error', __('Sorry, User not logged in.', 'wooconnector'), array('status' => 400));
		}
	}

	private function getOrderItems($order)
	{
		$result = array();
		$data = $order->get_data();
		$currency = $data['currency'];
		$currencys = WooConnectorGetCurrency(strtolower($currency));
		$number_of_decimals = $currencys['number_of_decimals'];
		$currenty_symbol = $currencys['symbol'];
		$ratecurrency = $currencys['rate'];
		$currency_position = $currencys['position'];
		$thousand_separator = $currencys['thousand_separator'];
		$decimal_separator = $currencys['decimal_separator'];
		$listout = array();
		$format_decimal = array('line_subtotal', 'line_subtotal_tax', 'line_total', 'line_total_tax');
		foreach ($order->get_items() as $item_key => $item_values) {
			$item_data = $item_values->get_data();
			$product = wc_get_product($item_values->get_product_id());
			$images = 0;
			if (!empty($product)) {
				$images = $product->get_image_id();
			}
			$result = array(
				'item_id' => $item_values->get_id(),
				'item_name' => apply_filters('mobiconnector_languages', $item_values->get_name()),
				'item_type' => $item_values->get_type(),
				'images' => $images,
				'product_name' => apply_filters('mobiconnector_languages', $item_data['name']),
				'product_id' => $item_data['product_id'],
				'variation_id' => $item_data['variation_id'],
				'quantity' => $item_data['quantity'],
				'tax_class' => $item_data['tax_class'],
				'line_subtotal' => $item_data['subtotal'],
				'line_subtotal_tax' => $item_data['subtotal_tax'],
				'line_total' => $item_data['total'],
				'line_total_tax' => $item_data['total_tax'],
			);
			foreach ($format_decimal as $key) {
				if (!empty($result[$key])) {
					if ($currency_position == 'left') {
						$result[$key] = $currenty_symbol . (wc_format_decimal($result[$key], $number_of_decimals));
					} elseif ($currency_position == 'right') {
						$result[$key] = (wc_format_decimal($result[$key], $number_of_decimals)) . $currenty_symbol;
					} elseif ($currency_position == 'left_space') {
						$result[$key] = $currenty_symbol . ' ' . (wc_format_decimal($result[$key], $number_of_decimals));
					} elseif ($currency_position == 'right_space') {
						$result[$key] = (wc_format_decimal($result[$key], $number_of_decimals)) . ' ' . $currenty_symbol;
					}
				}
			}
			$listout[] = $result;
		}

		return $listout;
	}

	private function getBillings($order)
	{
		$result = array(
			'billing_first_name' => $order->get_billing_first_name(),
			'billing_last_name' => $order->get_billing_last_name(),
			'billing_company' => $order->get_billing_company(),
			'billing_address_1' => $order->get_billing_address_1(),
			'billing_address_2' => $order->get_billing_address_2(),
			'billing_city' => $order->get_billing_city(),
			'billing_state' => $order->get_billing_state(),
			'billing_postcode' => $order->get_billing_postcode(),
			'billing_country' => $order->get_billing_country(),
			'billing_email' => $order->get_billing_email(),
			'billing_phone' => $order->get_billing_phone()
		);
		return $result;
	}

	public function getorderbyid($request)
	{
		$parameters = $request->get_params();
		$orderid = $parameters['order'];
		$order = new WC_Order($orderid);
		$auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
		if (!$auth) {
			$auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}
		$customer = $order->get_customer_id();
		$currentuser = get_current_user_id();
		if (wooconnector_check_user_login_by_token($auth) && $customer == $currentuser) {
			$data = $order->get_data();
			$currency = $data['currency'];
			$currencys = WooConnectorGetCurrency(strtolower($currency));
			$number_of_decimals = $currencys['number_of_decimals'];
			$currenty_symbol = $currencys['symbol'];
			$ratecurrency = $currencys['rate'];
			$currency_position = $currencys['position'];
			$thousand_separator = $currencys['thousand_separator'];
			$decimal_separator = $currencys['decimal_separator'];
			$format_decimal = array('discount', 'discount_total', 'discount_tax', 'shipping_total', 'shipping_tax', 'subtotal', 'shipping_tax', 'cart_tax', 'total', 'total_tax', 'prices_include_tax');
			$format_date = array('date_created', 'date_modified', 'date_completed', 'date_paid');
			$format_line_items = array('line_items', 'tax_lines', 'shipping_lines', 'fee_lines', 'coupon_lines');

			$items = $order->get_items();
			$subtotal = 0;
			foreach ($items as $item) {
				$subtotal += $item->get_subtotal();
			}
			$data['subtotal'] = $subtotal;
			// Format decimal values.
			foreach ($format_decimal as $key) {
				if (!empty($data[$key])) {
					if ($currency_position == 'left') {
						$data[$key] = $currenty_symbol . (wc_format_decimal($data[$key], $number_of_decimals));
					} elseif ($currency_position == 'right') {
						$data[$key] = (wc_format_decimal($data[$key], $number_of_decimals)) . $currenty_symbol;
					} elseif ($currency_position == 'left_space') {
						$data[$key] = $currenty_symbol . ' ' . (wc_format_decimal($data[$key], $number_of_decimals));
					} elseif ($currency_position == 'right_space') {
						$data[$key] = (wc_format_decimal($data[$key], $number_of_decimals)) . ' ' . $currenty_symbol;
					}
				}
			}

			// Format date values.
			foreach ($format_date as $key) {
				$datetime = $data[$key];
				$data[$key] = wc_rest_prepare_date_response($datetime, false);
				$data[$key . '_gmt'] = wc_rest_prepare_date_response($datetime);
			}

			// Format the order status.
			$data['status'] = 'wc-' === substr($data['status'], 0, 3) ? substr($data['status'], 3) : $data['status'];

			// Format line items.
			foreach ($format_line_items as $key) {
				$data[$key] = array_values(array_map(array($this, 'get_order_item_data'), $data[$key]));
			}

			if (!empty($data['coupon_lines'])) {
				foreach ($data['coupon_lines'] as $coupon_lines) {
					foreach ($format_decimal as $key) {
						if (!empty($coupon_lines[$key])) {
							if ($currency_position == 'left') {
								$coupon_lines[$key] = $currenty_symbol . (wc_format_decimal($coupon_lines[$key], $number_of_decimals));
							} elseif ($currency_position == 'right') {
								$coupon_lines[$key] = (wc_format_decimal($coupon_lines[$key], $number_of_decimals)) . $currenty_symbol;
							} elseif ($currency_position == 'left_space') {
								$coupon_lines[$key] = $currenty_symbol . ' ' . (wc_format_decimal($coupon_lines[$key], $number_of_decimals));
							} elseif ($currency_position == 'right_space') {
								$coupon_lines[$key] = (wc_format_decimal($coupon_lines[$key], $number_of_decimals)) . ' ' . $currenty_symbol;
							}
						}
					}
					$listout[] = array(
						'id' => $coupon_lines['id'],
						'code' => $coupon_lines['code'],
						'discount' => $coupon_lines['discount'],
						'discount_tax' => $coupon_lines['discount_tax'],
						'meta_data' => $coupon_lines['meta_data'],
					);
				}
				$data['coupon_lines'] = $listout;
			}	

			// Refunds.
			$data['refunds'] = array();
			foreach ($order->get_refunds() as $refund) {
				if ($currency_position == 'left') {
					$data['refunds'][] = array(
						'id' => $refund->get_id(),
						'refund' => $refund->get_reason() ? $refund->get_reason() : '',
						'total' => '-' . $currenty_symbol . (wc_format_decimal($refund->get_amount(), $number_of_decimals)),
					);
				} elseif ($currency_position == 'right') {
					$data['refunds'][] = array(
						'id' => $refund->get_id(),
						'refund' => $refund->get_reason() ? $refund->get_reason() : '',
						'total' => '-' . (wc_format_decimal($refund->get_amount(), $number_of_decimals)) . $currenty_symbol,
					);
				} elseif ($currency_position == 'left_space') {
					$data['refunds'][] = array(
						'id' => $refund->get_id(),
						'refund' => $refund->get_reason() ? $refund->get_reason() : '',
						'total' => '-' . $currenty_symbol . ' ' . (wc_format_decimal($refund->get_amount(), $number_of_decimals)),
					);
				} elseif ($currency_position == 'right_space') {
					$data['refunds'][] = array(
						'id' => $refund->get_id(),
						'refund' => $refund->get_reason() ? $refund->get_reason() : '',
						'total' => '-' . (wc_format_decimal($refund->get_amount(), $number_of_decimals)) . ' ' . $currenty_symbol,
					);
				}
			}
			$paymentdes = '';
			$gateways = WC()->payment_gateways->payment_gateways();
			foreach ($gateways as $gateway) {
				if ($gateway->id == $data['payment_method']) {
					$paymentdes = $gateway->description;
				}
			}
			$result = array(
				'id' => $order->get_id(),
				'parent_id' => $data['parent_id'],
				'number' => $data['number'],
				'order_key' => $data['order_key'],
				'created_via' => $data['created_via'],
				'version' => $data['version'],
				'status' => $data['status'],
				'currency' => $data['currency'],
				'date_created' => $data['date_created'],
				'date_created_gmt' => $data['date_created_gmt'],
				'date_modified' => $data['date_modified'],
				'date_modified_gmt' => $data['date_modified_gmt'],
				'discount_total' => $data['discount_total'],
				'discount_tax' => $data['discount_tax'],
				'shipping_total' => $data['shipping_total'],
				'shipping_tax' => $data['shipping_tax'],
				'cart_tax' => $data['cart_tax'],
				'total' => $data['total'],
				'subtotal' => $data['subtotal'],
				'total_tax' => $data['total_tax'],
				'prices_include_tax' => $data['prices_include_tax'],
				'customer_id' => $data['customer_id'],
				'customer_ip_address' => $data['customer_ip_address'],
				'customer_user_agent' => $data['customer_user_agent'],
				'customer_note' => $data['customer_note'],
				'billing' => $data['billing'],
				'shipping' => $data['shipping'],
				'payment_method' => $data['payment_method'],
				'payment_method_title' => $data['payment_method_title'],
				'payment_method_description' => $paymentdes,
				'transaction_id' => $data['transaction_id'],
				'date_paid' => $data['date_paid'],
				'date_paid_gmt' => $data['date_paid_gmt'],
				'date_completed' => $data['date_completed'],
				'date_completed_gmt' => $data['date_completed_gmt'],
				'cart_hash' => $data['cart_hash'],
				'meta_data' => $data['meta_data'],
				'line_items' => $data['line_items'],
				'tax_lines' => $data['tax_lines'],
				'shipping_lines' => $data['shipping_lines'],
				'fee_lines' => $data['fee_lines'],
				'coupon_lines' => $data['coupon_lines'],
				'refunds' => $data['refunds'],
			);
			$laccs = array();
			if ($data['payment_method'] == 'bacs') {
				$accounts = get_option('woocommerce_bacs_accounts');
				if (!empty($accounts)) {
					foreach ($accounts as $account) {
						$laccs[] = array(
							'account_name' => $account['account_name'],
							'account_number' => $account['account_number'],
							'bank_name' => $account['bank_name'],
							'sort_code' => $account['sort_code'],
							'iban' => $account['iban'],
							'bic' => $account['bic'],
						);
					}
				}
				if (!empty($laccs)) {
					$result['bacs_accounts'] = $laccs;
				} else {
					$result['bacs_accounts'] = array();
				}
			}
			return apply_filters('wooconnector_get_order_by_id', $result);
		} else {
			return null;
		}
	}

	private function get_order_item_data($item)
	{
		$data = $item->get_data();
		$orderid = $data['order_id'];
		$order = new WC_Order($orderid);
		$dataorder = $order->get_data();
		$currency = $dataorder['currency'];
		$currencys = WooConnectorGetCurrency(strtolower($currency));
		$number_of_decimals = $currencys['number_of_decimals'];
		$currenty_symbol = $currencys['symbol'];
		$ratecurrency = $currencys['rate'];
		$currency_position = $currencys['position'];
		$thousand_separator = $currencys['thousand_separator'];
		$decimal_separator = $currencys['decimal_separator'];
		$format_decimal = array('subtotal', 'subtotal_tax', 'total', 'total_tax', 'tax_total', 'shipping_tax_total');

		// Format decimal values.
		foreach ($format_decimal as $key) {
			if (isset($data[$key])) {
				if ($currency_position == 'left') {
					$data[$key] = $currenty_symbol . (wc_format_decimal($data[$key], $number_of_decimals));
				} elseif ($currency_position == 'right') {
					$data[$key] = (wc_format_decimal($data[$key], $number_of_decimals)) . $currenty_symbol;
				} elseif ($currency_position == 'left_space') {
					$data[$key] = $currenty_symbol . ' ' . (wc_format_decimal($data[$key], $number_of_decimals));
				} elseif ($currency_position == 'right_space') {
					$data[$key] = (wc_format_decimal($data[$key], $number_of_decimals)) . ' ' . $currenty_symbol;
				}
			}
		}

		// Add SKU and PRICE to products.
		if (is_callable(array($item, 'get_product'))) {
			$data['sku'] = $item->get_product() ? $item->get_product()->get_sku() : null;
			$productid = $item->get_product()->get_id();
			if ($currency_position == 'left') {
				$data['price'] = $currenty_symbol . (wc_format_decimal($item->get_total() / max(1, $item->get_quantity()), $number_of_decimals));
				$data['sale_price'] = $currenty_symbol . (wc_format_decimal($item->get_subtotal() / max(1, $item->get_quantity()), $number_of_decimals));
			} elseif ($currency_position == 'right') {
				$data['price'] = (wc_format_decimal($item->get_total() / max(1, $item->get_quantity()), $number_of_decimals)) . $currenty_symbol;
				$data['sale_price'] = (wc_format_decimal($item->get_subtotal() / max(1, $item->get_quantity()), $number_of_decimals)) . $currenty_symbol;
			} elseif ($currency_position == 'left_space') {
				$data['price'] = $currenty_symbol . ' ' . (wc_format_decimal($item->get_total() / max(1, $item->get_quantity()), $number_of_decimals));
				$data['sale_price'] = $currenty_symbol . ' ' . (wc_format_decimal($item->get_subtotal() / max(1, $item->get_quantity()), $number_of_decimals));
			} elseif ($currency_position == 'right_space') {
				$data['price'] = (wc_format_decimal($item->get_total() / max(1, $item->get_quantity()), $number_of_decimals)) . ' ' . $currenty_symbol;
				$data['sale_price'] = (wc_format_decimal($item->get_subtotal() / max(1, $item->get_quantity()), $number_of_decimals)) . ' ' . $currenty_symbol;
			}
			$product = wc_get_product($productid);
			$attributes = $this->get_attributes($product);
			$data['attributes'] = $attributes;

			$images = $item->get_product()->get_image_id();
			if (!empty($images)) {
				$wp_upload_dir = wp_upload_dir();
				$wooconnector_large = get_post_meta($images, 'wooconnector_large', true);
				$wooconnector_medium = get_post_meta($images, 'wooconnector_medium', true);
				$wooconnector_x_large = get_post_meta($images, 'wooconnector_x_large', true);
				$wooconnector_small = get_post_meta($images, 'wooconnector_small', true);
				if (empty($wooconnector_large) || empty($wooconnector_medium) || empty($wooconnector_x_large) || empty($wooconnector_small)) {

					$this->update_thumnail_woo($images);
				}
				// g?n thumnail m?i
				foreach ($this->thumnailsX as $key => $value) {
					$imagesupload = get_post_meta($images, $key, true);
					if (empty($imagesupload)) {
						$crop_image[$key] = null;
					} else {
						$crop_image[$key] = $wp_upload_dir['baseurl'] . "/" . $imagesupload;
					}
				}
				$data['images'] = $crop_image;
			} else {
				$data['images'] = array(
					'wooconnector_large' => null,
					'wooconnector_medium' => null,
					'wooconnector_x_large' => null,
					'wooconnector_small' => null
				);
			}
		}
		
		// Format taxes.
		if (!empty($data['taxes']['total'])) {
			$taxes = array();

			foreach ($data['taxes']['total'] as $tax_rate_id => $tax) {
				if ($currency_position == 'left') {
					$taxes[] = array(
						'id' => $tax_rate_id,
						'total' => $currenty_symbol . $tax,
						'subtotal' => (isset($data['taxes']['subtotal'][$tax_rate_id]) ? $currenty_symbol . $data['taxes']['subtotal'][$tax_rate_id] : ''),
					);
				} elseif ($currency_position == 'right') {
					$taxes[] = array(
						'id' => $tax_rate_id,
						'total' => $tax . $currenty_symbol,
						'subtotal' => (isset($data['taxes']['subtotal'][$tax_rate_id]) ? $data['taxes']['subtotal'][$tax_rate_id] . $currenty_symbol : ''),
					);
				} elseif ($currency_position == 'left_space') {
					$taxes[] = array(
						'id' => $tax_rate_id,
						'total' => $currenty_symbol . ' ' . $tax,
						'subtotal' => (isset($data['taxes']['subtotal'][$tax_rate_id]) ? $currenty_symbol . ' ' . $data['taxes']['subtotal'][$tax_rate_id] : ''),
					);
				} elseif ($currency_position == 'right_space') {
					$taxes[] = array(
						'id' => $tax_rate_id,
						'total' => $tax . ' ' . $currenty_symbol,
						'subtotal' => (isset($data['taxes']['subtotal'][$tax_rate_id]) ? $data['taxes']['subtotal'][$tax_rate_id] . ' ' . $currenty_symbol : ''),
					);
				}
			}
			$data['taxes'] = $taxes;
		} elseif (isset($data['taxes'])) {
			$data['taxes'] = array();
		}

		$data['name'] = apply_filters('mobiconnector_languages', $data['name']);

		// Remove names for coupons, taxes and shipping.
		if (isset($data['code']) || isset($data['rate_code']) || isset($data['method_title'])) {
			unset($data['name']);
		}

		// Remove props we don't want to expose.
		unset($data['type']);
		return $data;
	}
	
	/*
	 * Change Order status Processing to Cancelled by 
	 * Param Order id
	 */

	private function get_attributes($product)
	{
		$attributes = array();

		if ($product->is_type('variation')) {
			$_product = wc_get_product($product->get_parent_id());
			foreach ($product->get_variation_attributes() as $attribute_name => $attribute) {
				$name = str_replace('attribute_', '', $attribute_name);

				if (!$attribute) {
					continue;
				}

				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if (0 === strpos($attribute_name, 'attribute_pa_')) {
					$option_term = get_term_by('slug', $attribute, $name);
					$attributes[] = array(
						'id' => wc_attribute_taxonomy_id_by_name($name),
						'name' => apply_filters('mobiconnector_languages', $this->get_attribute_taxonomy_name($name, $_product)),
						'option' => $option_term && !is_wp_error($option_term) ? $option_term->name : (array)$attribute,
						'taxanomy' => $attribute_name,
					);
				} else {
					$attributes[] = array(
						'id' => 0,
						'name' => apply_filters('mobiconnector_languages', $this->get_attribute_taxonomy_name($name, $_product)),
						'option' => (array)$attribute,
						'taxanomy' => $attribute_name,
					);
				}
			}
		} else {
			foreach ($product->get_attributes() as $attribute => $value) {
				$attributes[] = array(
					'id' => $value['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($value['name']) : 0,
					'name' => apply_filters('mobiconnector_languages', $this->get_attribute_taxonomy_name($value['name'], $product)),
					'taxanomy' => $attribute,
					'position' => (int)$value['position'],
					'visible' => (bool)$value['is_visible'],
					'variation' => (bool)$value['is_variation'],
					'options' => $this->get_attribute_options($product->get_id(), $value),
				);
			}
		}

		return $attributes;
	}

	private function get_attribute_options($product_id, $attribute)
	{
		if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
			return wc_get_product_terms($product_id, $attribute['name'], array('fields' => 'names'));
		} elseif (isset($attribute['value'])) {
			return array_map('trim', explode('|', $attribute['value']));
		}

		return array();
	}

	private function get_attribute_taxonomy_name($slug, $product)
	{
		$attributes = $product->get_attributes();

		if (!isset($attributes[$slug])) {
			return str_replace('pa_', '', $slug);
		}

		$attribute = $attributes[$slug];

		// Taxonomy attribute name.
		if ($attribute->is_taxonomy()) {
			$taxonomy = $attribute->get_taxonomy_object();
			return $taxonomy->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	}

	/**
	 * Change status
	 */
	public function changestatus($request)
	{
		$parameters = $request->get_params();
		$orderid = $parameters['order'];
		$checkorder = get_post($orderid);
		if ($checkorder->post_status == "wc-on-hold") {
			$order = new WC_Order($orderid);
			$order->update_status('cancelled');
			$data_order = $order->get_data();
			if (!empty($data_order['coupon_lines'])) {
				foreach ($data_order['coupon_lines'] as $coupon) {
					$coupon = new WC_Coupon($coupon['code']);
					$usege_count = $coupon->get_usage_count();
					$coupon->set_usage_count($usege_count - 1);
				}
			}
			$result = array(
				'result' => 'success',
				'message' => 'Order status changed to Cancelled'
			);
		} elseif ($checkorder->post_status == "wc-pending") {
			$order = new WC_Order($orderid);
			$order->update_status('cancelled');
			$data_order = $order->get_data();
			if (!empty($data_order['coupon_lines'])) {
				foreach ($data_order['coupon_lines'] as $coupon) {
					$coupon = new WC_Coupon($coupon['code']);
					$usege_count = $coupon->get_usage_count();
					$coupon->set_usage_count($usege_count - 1);
				}
			}
			$result = array(
				'result' => 'success',
				'message' => 'Order status changed to Cancelled'
			);
		} else {
			$result = array(
				'result' => 'fail',
				'message' => 'This Order status not is On-Hold or Pending'
			);
		}
		return $result;
	}

	public function update_thumnail_woo($imagesID)
	{
		$post_thumbnail_id = $imagesID;
		if (empty($post_thumbnail_id))
			return true;
		/// ki?m tra xem d� t?n t?i thumnail chua
		$wooconnector_large = get_post_meta($post_thumbnail_id, 'wooconnector_large', true);
		$wooconnector_medium = get_post_meta($post_thumbnail_id, 'wooconnector_medium', true);
		$wooconnector_x_large = get_post_meta($post_thumbnail_id, 'wooconnector_x_large', true);
		$wooconnector_small = get_post_meta($post_thumbnail_id, 'wooconnector_small', true);
		if (!empty($wooconnector_medium) && !empty($wooconnector_x_large) && !empty($wooconnector_large) && !empty($wooconnector_small))
			return true; // d� t?n t?i r?i ko t?o n?a
		// l?y th�ng tin c?a ?nh
		$relative_pathto_file = get_post_meta($post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'] . '/' . $relative_pathto_file;
		// ki?m tra file g?c c� t?n t?i hay kh�ng?
		if (!file_exists($absolute_pathto_file))
			return true; // file ko t?n t?i
		////////////////

		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// t?o ?nh 
		list($width, $height) = getimagesize($absolute_pathto_file);
		if ($width > $height) {
			foreach ($this->thumnailsX as $key => $value) {
				$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
				$dest = $wp_upload_dir['basedir'] . '/' . $path;
				WooConnectorCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// c?p nh?t post meta for thumnail
				update_post_meta($post_thumbnail_id, $key, $path);
			}
		} else {
			foreach ($this->thumnailsY as $key => $value) {
				$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
				$dest = $wp_upload_dir['basedir'] . '/' . $path;
				WooConnectorCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// c?p nh?t post meta for thumnail
				update_post_meta($post_thumbnail_id, $key, $path);
			}
		}

		return true;
	}

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
}
$WooConnectorOrder = new WooConnectorOrder();
?>