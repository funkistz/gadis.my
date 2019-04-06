<?php

/**
 * Plugin Name: Mobile Connector
 * Plugin URI: https://buy-addons.com/
 * Description: Intergrated to Wordpress Rest API
 * Version: 2.0.7
 * Author: buy-addons
 * Author URI: https://buy-addons.com
 * Requires at least: 2.0
 * Tested up to: 4.5
 * Compatibility with the REST API v2
 *
 * Text Domain: mobiconnector
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Main BAMobile
 * 
 * @class BAMobile
 */
final class BAMobile
{

	/**
	 * Mobile Connector version.
	 *
	 * @var string
	 */
	public $version = '2.0.7';

	/**
	 * The single instance of the class.
	 *
	 */
	protected static $_instance = null;

	/**
	 * Session instance.
	 */
	public $session = null;

	/**
	 * Main BAMobile Instance.
	 *
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * MobiConnector Construct
	 */
	public function __construct()
	{
		$this->define_constants();
		$this->includes_and_requires();
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks()
	{
		register_activation_hook(MOBICONNECTOR_PLUGIN_FILE, array('BAMobileInstall', 'bamobile_install'));
		add_action('init', array($this, 'init'), 0);
	}

	/**
	 * Define Mobile Connector Constants.
	 */
	private function define_constants()
	{
		define('MOBICONNECTOR_PLUGIN_FILE', __FILE__);
		define('MOBICONNECTOR_ABSPATH', dirname(__FILE__) . '/');
		define('MOBICONNECTOR_PLUGIN_BASENAME', plugin_basename(__FILE__));
		define('MOBICONNECTOR_VERSION', $this->version);
		define('MOBICONNECTOR_ADMIN_PATH', ABSPATH . 'wp-admin/');
		define('MOBICONNECTOR_EXTENSIONS_PATH', dirname(__FILE__) . '/extensions/');
		define('MOBICONNECTOR_AJAX_URL', admin_url('admin-ajax.php'));
	}

	/**
	 * Include required core files used in admin.
	 */
	private function includes_and_requires()
	{
		global $wp_version;		
		
		// Support library		
		require_once(ABSPATH . WPINC . '/pluggable.php');
		require_once(MOBICONNECTOR_ADMIN_PATH . 'includes/image.php');
		require_once(MOBICONNECTOR_ADMIN_PATH . 'includes/plugin.php');		
		
		//Install
		if ($wp_version > '4.6.11' || is_plugin_active('rest-api/plugin.php')) {
			require_once('includes/class-mobiconnector-posttype.php');
		}
		require_once('includes/class-mobiconnector-install.php');
		require_once('includes/session/class-mobiconnector-session.php');
		require_once('includes/admin/class-mobiconnector-intro-core.php');
		require_once('includes/admin/class-mobiconnector-intro.php');
		
		// Core		
		require_once('includes/mobiconnector-functions.php');
		require_once('includes/mobiconnector-functions-notices.php');
		require_once('includes/mobiconnector-functions-file.php');
		require_once('includes/mobiconnector-functions-page.php');
		require_once('includes/mobiconnector-functions-templates.php');
		require_once('includes/mobiconnector-functions-languages.php');
		require_once('includes/class.core.php');
		require_once('includes/cache/class-mobiconnector-core-cache.php');
		require_once('settings/class-mobiconnector-settings.php');
		if ($wp_version > '4.6.11' || is_plugin_active('rest-api/plugin.php')) {
			require_once('includes/function/addposttypetorest/class-mobiconnector-posttype-core.php');
		}
		require_once('includes/cache/class-mobiconnector-cache.php');

		if (is_admin()) {
			require_once('includes/admin/class-mobiconnector-meta-box.php');
			require_once('includes/admin/meta-boxs/class-mobiconnector-metabox-post.php');
			require_once('includes/admin/class-mobiconnector-extensions.php');
		}
		
		// Add function to admin Wordpress		
		require_once('includes/function/postviewcounter/class-mobiconnector-postviewcounter.php');
		require_once('includes/function/jwt/class-mobionnector-jwt-core.php');
		require_once('includes/function/disablecategories/class-mobiconnector-disable-categories.php');
		require_once('includes/function/avatar/class-mobiconnector-avatar.php');
		require_once('includes/function/categoriesavatar/class-mobiconnector-categories-avatar.php');
		require_once('templates/class-mobiconnector-gallery-custom.php');
		require_once('templates/class-mobiconnector-video-custom.php');
		
		// Include to REST API		
		if ($wp_version > '4.6.11' || is_plugin_active('rest-api/plugin.php')) {
			require_once('includes/class-mobiconnector-posts.php');
			require_once('includes/class-mobiconnector-category.php');
			require_once('includes/class-mobiconnector-comments.php');
			require_once('includes/class-mobiconnector-users.php');
			require_once('settings/class-mobiconnector-settings-api.php');
			if (is_plugin_active('qtranslate-x/qtranslate.php')) {
				require_once('languages/mobiconnector-language-settings.php');
			}
		}
		
		// Create new API		
		require_once('endpoints/class-mobiconnector-report.php');
		require_once('endpoints/class-mobiconnector-user.php');
		require_once('endpoints/class-mobiconnector-post.php');
		require_once('endpoints/class-mobiconnector-language.php');
		
		// Push notice by API onesignal		
		require_once('settings/onesignal/pushnotification.php');
	}

	/**
	 * Init MobiConnector when WordPress Initialises.
	 */
	public function init()
	{
		do_action('before_mobiconnector_init');
		if (is_admin()) {
			$this->session = new BAMobile_Session();
			$this->session->bamobile_init();
		}
		do_action('mobiconnector_init');
	}
}

/**
 * Call Main Class
 */
function mc()
{
	return BAMobile::instance();
}

/**
 * Run Main Class
 */
mc();

if (!class_exists('WooCommerce')) {
	if (file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')) {
		require_once(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
	}
}

//Extensions
$current = get_option('mobiconnector_extensions_active');
if (!empty($current)) {
	if (is_string($current)) {
		$current = unserialize($current);
	}
	foreach ($current as $file) {
		if (file_exists(MOBICONNECTOR_EXTENSIONS_PATH . $file) && !is_plugin_active($file)) {
			require_once(MOBICONNECTOR_EXTENSIONS_PATH . $file);
		}
	}
}
?>