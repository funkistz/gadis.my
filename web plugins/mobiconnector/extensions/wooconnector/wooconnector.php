<?php

/**
 * Plugin Name: WooCommerce Connector
 * Plugin URI: https://buy-addons.com/
 * Description: Intergrated to Woocommerce API
 * Version: 1.0.22
 * Author: buy-addons
 * Author URI: https://buy-addons.com
 * Requires at least: 2.0
 * Tested up to: 4.5
 * Compatibility with the REST API v2
 *
 * Text Domain: wooconnector
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.	
}
if (!class_exists("WooConnector")) {

	/**
	 * Main Classs WooConnector
	 */
	final class WooConnector
	{

		/**
		 * WooConnector Version
		 * 
		 * @var string
		 */
		public $version = '1.0.22';

		/**
		 * WooConnector construct
		 */
		public function __construct()
		{
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');	
			// Because this plugin supports for WooCommerce, if WooCommerce is deactivated, our plugin will be disabled
			if (is_plugin_active('woocommerce/woocommerce.php') == false || !self::wooconnector_check_version_woocommerce()) {
				return 0;
			}
			if (is_plugin_active('mobiconnector/mobiconnector.php') == false) {
				return 0;
			}

			$this->define_constants();
			$this->init_hooks();
			$this->includes_and_requires();
		}

		/**
		 * Check version WooCommerce
		 */
		public static function wooconnector_check_version_woocommerce($version = '3.0')
		{
			if (class_exists('WooCommerce')) {
				global $woocommerce;
				if (version_compare($woocommerce->version, $version, ">=")) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Define Mobile Connector Constants.
		 */
		private function define_constants()
		{
			define('WOOCONNECTOR_PLUGIN_FILE', __FILE__);
			define('WOOCONNECTOR_ABSPATH', dirname(__FILE__) . '/');
			define('WOOCONNECTOR_PLUGIN_BASENAME', plugin_basename(__FILE__));
			define('WOOCONNECTOR_VERSION', $this->version);
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks()
		{
			if (!extension_loaded('gd') && !function_exists('gd_info')) {
				add_action('admin_notices', array($this, 'admin_notice_error'));
			}
			add_action('init', array($this, 'wooconnector_languages_init'));
			add_action('admin_enqueue_scripts', array($this, 'wooconnector_admin_style'));
			add_action('wp_enqueue_scripts', array($this, 'wooconnector_script'));

			$checkchangequantity = get_option('wooconnector_settings-change-quantity');
			if ($checkchangequantity == 1) {
				remove_filter('woocommerce_stock_amount', 'intval');
				add_filter('woocommerce_stock_amount', 'floatval');
				add_filter('woocommerce_quantity_input_args', array($this, 'wooconnector_quantity_input_args'), 10, 2);
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes_and_requires()
		{
			
			// Core
			require_once('hooks/wooconnector-core.php');
			require_once('includes/class.core.php');
			require_once('settings/class-wooconnector-settings.php');	
	
			// Support
			require_once('hooks/class-wooconnector-mobile-detect.php');
			require_once('hooks/pushnotification.php');
			require_once('includes/datastore/class-wooconnector-data-products.php');
			
			// Add function to admin Wordpress
			require_once('hooks/class-wooconnector-brands-images.php');
			require_once('hooks/class-wooconnector-hook-currency.php');
			require_once('settings/brands/class-wooconnector-brands.php');
			require_once('settings/popuphomepage/class-wooconnector-popup.php');
			require_once('settings/slider/class-wooconnector-slider.php');				
			
			// Include to WooCommerce REST API
			require_once('includes/class-wooconnector-category.php');
			require_once('includes/class-wooconnector-product.php');
			require_once('includes/class-wooconnector-order.php');
			require_once('includes/class-wooconnector-variation.php');						
	
			// Create new API
			require_once('endpoints/class-wooconnector-cart.php');
			require_once('endpoints/class-wooconnector-checkout.php');
			require_once('endpoints/class-wooconnector-coupon.php');
			require_once('endpoints/class-wooconnector-contactus.php');
			require_once('endpoints/class-wooconnector-order.php');
			require_once('endpoints/class-wooconnector-products.php');
			require_once('endpoints/class-wooconnector-user.php');
			
			// Support for new API
			require_once('hooks/class-wooconnector-hook-categories.php');
			require_once('hooks/class-wooconnector-hook-product.php');		
	
			// Support for Checkout templates
			require_once('templates/class-wooconnector-checkout-functions.php');
			require_once('templates/class-wooconnector-checkout-scripts.php');
	
			// Settings Languages
			require_once('languages/wooconnector-settings-languages.php');
		}

		/**
		 * Load Localisation files.
		 */
		public function wooconnector_languages_init()
		{
			$plugin_dir = plugin_basename(dirname(__FILE__)) . 'languages';
			load_plugin_textdomain('wooconnector', false, $plugin_dir);
		}

		/**
		 * Change default quantity settings of woocommerce
		 */
		public function wooconnector_quantity_input_args($args, $product)
		{
			$args['input_value'] = 1; // Starting value
			$args['max_value'] = 1000; // Maximum value
			$args['min_value'] = 1; // Minimum value
			$args['step'] = 1; // Quantity steps
			return $args;
		}

		/**
		 * Script of WooConnector
		 */
		public function wooconnector_script()
		{	
			//Script of checkout
			wp_register_script('wooconnector_remove_page_by_mobile', plugins_url('assets/js/removepage.js', WOOCONNECTOR_PLUGIN_FILE), array('jquery'), WOOCONNECTOR_VERSION);
			$remove = array();
			wp_localize_script('wooconnector_remove_page_by_mobile', 'wooconnector_remove_page_by_mobile_params', $remove);
			if (is_order_received_page()) {
				$key = @$_GET['key'];
				if (!empty($key)) {
					$order_id = wc_get_order_id_by_order_key($key);
					if (!empty($order_id)) {
						$check = get_post_meta($order_id, 'check_wooconnector', true);
						$order = wc_get_order($order_id);
						if (!empty($order) && !empty($check)) {
							$detect = new Wooconnector_Detect;
							$method = $order->get_payment_method();
							$userAgent = get_post_meta($order_id, 'wooconnector_check_user_agent', true);
							$listdetailt = $detect->output($userAgent);
							$device = $listdetailt['device'];
							if ($method != 'payuindia' && $method != 'razorpay' && $method != 'bankmellat' && strpos($device, 'iPhone') !== false) {
								$user_id = get_current_user_id();
								if ($user_id > 0) {
									$sessions = WP_Session_Tokens::get_instance($user_id);
									$sessions->destroy_all();
								}
								wp_enqueue_script('wooconnector_remove_page_by_mobile');
							}
						}
					}
				}
			}

			wp_register_script('wooconnector_hidden_stripe_order_pay', plugins_url('assets/js/wooconnector-stripe-orderpay.js', WOOCONNECTOR_PLUGIN_FILE), array('jquery'), WOOCONNECTOR_VERSION);
			$remove = array();
			wp_localize_script('wooconnector_hidden_stripe_order_pay', 'wooconnector_hidden_stripe_order_pay_params', $remove);

			wp_register_style('wooconnector-hidden-stripe-orderpay-style', plugins_url('assets/css/wooconnector-order-pay-stripe.css', WOOCONNECTOR_PLUGIN_FILE), array(), WOOCONNECTOR_VERSION, 'all');
			if (is_wc_endpoint_url('order-pay')) {
				$key = @$_GET['key'];
				if (!empty($key)) {
					$order_id = wc_get_order_id_by_order_key($key);
					if (!empty($order_id)) {
						$check = get_post_meta($order_id, 'check_wooconnector', true);
						$order = wc_get_order($order_id);
						if (!empty($check) && !empty($order)) {
							$method = $order->get_payment_method();
							if ($method == 'stripe') {
								$custom_css_page = "#page{display: none !important;}";
								wp_add_inline_style('wooconnector-hidden-stripe-orderpay-style', $custom_css_page);
								$custom_css_wpadminbar = "#wpadminbar{display: none !important;}";
								wp_add_inline_style('wooconnector-hidden-stripe-orderpay-style', $custom_css_wpadminbar);
								wp_enqueue_style('wooconnector-hidden-stripe-orderpay-style');
								wp_enqueue_script('wooconnector_hidden_stripe_order_pay');
							}
						}
					}
				}
			}
		}

		/**
		 * Style Admin of WooConnector
		 */
		public function wooconnector_admin_style()
		{
			if (is_admin()) {
				wp_register_style('wooconnector-admin-style', plugins_url('assets/css/wooconnector-admin-style.css', WOOCONNECTOR_PLUGIN_FILE), array(), WOOCONNECTOR_VERSION, 'all');
				wp_enqueue_style('wooconnector-admin-style');
			}		
			//Script of post push notice
			if (is_admin() && isset($_GET['action']) && $_GET['action'] == 'edit' || is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'product') {
				wp_register_script('wooconnector_postpushnotice_script', plugins_url('assets/js/postpushnotice.js', WOOCONNECTOR_PLUGIN_FILE), array('jquery'), WOOCONNECTOR_VERSION);
				$remove = array(
					'base_url' => ABSPATH
				);
				wp_localize_script('wooconnector_postpushnotice_script', 'wooconnector_postpushnotice_params', $remove);
				wp_enqueue_script('wooconnector_postpushnotice_script');
			}
	
			// Enable Media popup
			if (is_admin() && isset($_GET['page']) && ($_GET['page'] == 'woo-notifications' || $_GET['page'] == 'wooconnector')) {
				wp_enqueue_media();
			}
		}

		/** 
		 * Add notices to admin with Host no library GD
		 */
		public function admin_notice_error()
		{
			$class = 'notice notice-error is-dismissible';
			$message = __('Your PHP have not installed the GD library . Please install the GD library to avoid some errors when using the REST API. ', 'wooconnector');
			$link = __('Install GD library', 'wooconnector');
			printf('<div class="%1$s"><p>%2$s<a href="http://php.net/manual/en/image.installation.php">%3$s</a></p></div>', esc_attr($class), esc_html($message), esc_html($link));
		}
	}
	$WooConnector = new WooConnector();
}
?>