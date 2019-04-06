<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooConnector_Frontend_Scripts Class.
 */
class Wooconnector_Scripts {
	
	public function __construct() {			
		add_filter( 'woocommerce_is_checkout', array($this, 'woocommerce_is_checkout'), 10 , 1);
		add_action( 'wp_enqueue_scripts', array( $this , 'load_style' ) );	
	}
	public function woocommerce_is_checkout(){
		if(is_wooconnector_checkout()){
			return true;
		}
	}	
	public function load_style(){
		if(is_wooconnector_checkout()){
			wp_register_style( 'wooconnector-checkout-style', plugins_url('templates/style.css',WOOCONNECTOR_PLUGIN_FILE), array(), WOOCONNECTOR_VERSION, 'all' );
			wp_enqueue_style( 'wooconnector-checkout-style' );
		}
		
	}
	
}

$Wooconnector_Scripts = new Wooconnector_Scripts(); 
?>