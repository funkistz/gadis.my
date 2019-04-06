<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(is_plugin_active('qtranslate-x/qtranslate.php')){
	/**
	 * Create Menu if qTranstale active
	 */
	function wooconnector_create_menu_language(){
		$parent_slug = 'wooconnector';
		add_submenu_page(
			$parent_slug,
			__('Languages'),
			__('Languages'),
			'manage_options',
			'woo-language',
			'wooconnector_action_create_menu_language'
		);
	}
	add_action( 'admin_menu','wooconnector_create_menu_language',20);

	/**
	 * Redirect to settings language
	 */
	function wooconnector_action_create_menu_language(){
		wp_redirect(admin_url().'options-general.php?page=qtranslate-x');
	}

	/**
     * Change position submenu wooconnector
     */
	function wooconnector_submenu_order( $menu_order ) {
		# Get submenu key location based on slug
		global $submenu;
		if(isset($submenu['wooconnector'])){
			$settings = $submenu['wooconnector'];
			$index = 0;
			$checkisset = 0;
			foreach ( $settings as $key => $details ) {
				if ( $details[2] == 'woo-language' ) {
					$index = $key;
					$checkisset++;
				}
			}
			if($checkisset > 0){
				$settings = $submenu['wooconnector'][$index];
				unset( $submenu['wooconnector'][$index] );
				array_push($submenu['wooconnector'],$settings);
				ksort( $submenu['wooconnector'] );
			}
		}        
		return $menu_order;
	}
	add_filter( 'custom_menu_order','wooconnector_submenu_order');
}
?>