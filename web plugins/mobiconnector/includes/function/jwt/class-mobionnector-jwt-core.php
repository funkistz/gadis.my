<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * MobiConnector JWT Core 
 * 
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 * 
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * 
 */
class BAMobile_JWT_Core{
    /**
     * MobiConnector JWT Core construct
     */
    public function __construct(){
        $this->includes_and_requires();
    }

    /**
	 * Include required core files used in admin and on the frontend.
	 */
    private function includes_and_requires(){
        require_once( __DIR__.'/mobi_jwt/mobiconnectorEE.php');
		require_once( __DIR__.'/mobi_jwt/mobiconnectorBVE.php');
        require_once( __DIR__.'/mobi_jwt/mobiconnectorSIE.php');
        require_once( __DIR__.'/mobiconnector-namespace-jwt.php');
		require_once( __DIR__.'/class-mobiconnector-jwt.php' );
    }
}
$BAMobile_JWT_Core = new BAMobile_JWT_Core();
?>