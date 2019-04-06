<?php

/**
 * Plugin Name: ModernShop
 * Plugin URI: https://buy-addons.com/
 * Description: Intergrated to Woocommerce API
 * Version: 1.0.9
 * Author: buy-addons
 * Author URI: https://buy-addons.com
 * Requires at least: 2.0
 * Tested up to: 4.5
 * Compatibility with the REST API v2
 *
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.	
}
if (!class_exists("Modernshop")) {
	class Modernshop
	{
		public $version = '1.0.9';
		public function __construct()
		{
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			if (is_plugin_active('mobiconnector/mobiconnector.php') == false) {
				return false;
			}
			if (is_plugin_active('wooconnector/wooconnector.php') == false && $this->bamobile_is_extension_active('wooconnector/wooconnector.php') == false) {
				return false;
			}
			if (is_plugin_active('woocommerce/woocommerce.php') == false || !self::modernshop_check_version_woocommerce()) {
				return false;
			}
			define('MODERN_PLUGIN_FILE', __FILE__);
			define('MODERN_ABSPATH', dirname(__FILE__) . '/');
			define('MODERN_PLUGIN_BASENAME', plugin_basename(__FILE__));
			define('MODERN_VERSION', $this->version);
			require_once('hooks/class.core.php');
			require_once('hooks/modernshop-loaded.php');
			if (is_plugin_active('wooconnector/wooconnector.php') || $this->bamobile_is_extension_active('wooconnector/wooconnector.php')) {
				require_once('includes/class-modernshop-product.php');
			}
			require_once('includes/class-modernshop-category.php');
			require_once('endpoints/class-modernshop-settings-xml.php');
		}

		/**
		 * Check version WooCommerce
		 */
		public static function modernshop_check_version_woocommerce($version = '3.0')
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
		 * Check extension active
		 */
		public function bamobile_is_extension_active($extension)
		{
			$current = array();
			$current = get_option('mobiconnector_extensions_active');
			if (!empty($current) && is_string($current)) {
				$current = unserialize($current);
				$current = (array)$current;
			}
			if (is_array($current)) {
				return in_array($extension, $current);
			} else {
				return false;
			}
		}
	}
	$Modernshop = new Modernshop();
}
?>