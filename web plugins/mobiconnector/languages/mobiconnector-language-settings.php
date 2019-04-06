<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * If qTranslate install add field of mobiconnector static to settings
 * 
 * @param array $page_configs  list config of qTranslate
 */
if(is_plugin_active('qtranslate-x/qtranslate.php')){
	function bamobile_mobiconnector_add_admin_page_config($page_configs)
	{
		$linkxml = MOBICONNECTOR_ABSPATH."xml/static.xml";
		{
			$page_config = array();
			$page_config['pages'] = array( 'post.php' => '', 'post-new.php' => '');
			//$page_config['anchors'] = array( 'titlediv'  );
	
			$page_config['forms'] = array();
	
			$f = array();
			$f['form'] = array( 'id' => 'post' );
	
			$f['fields'] = array();
			$fields = &$f['fields']; // shorthand
			$fields[] = array( 'id' => 'mobi_push_notification_title' );
			$fields[] = array( 'id' => 'mobi_push_notification_content' );
			$page_config['forms'][] = $f;
			$page_configs[] = $page_config;
		}	
		{
			$page_config = array();
			$page_config['pages'] = array( 'admin.php' => 'page=mobiconnector-settings&mtab=textapp');
			//$page_config['anchors'] = array( 'titlediv'  );
	
			$page_config['forms'] = array();
			
			$f = array();
			$f['form'] = array( 'id' => 'settings-form' );

			$f['fields'] = array();
			$fields = &$f['fields']; // shorthand
			$checkname = array();
			require_once(MOBICONNECTOR_ABSPATH."xml/mobiconnector-static.php");
			$xmls = bamobile_mobiconnector_get_static();
			if(!empty($xmls)){
				foreach($xmls as $xm){	
					$xml = (object)$xm;	
					if($xml->type == 'editor'){
							$id =  str_replace("-", "_", $xml->id);
					}else{
							$id = $xml->id ;
					}
					$fields[] = array( 'id' => $id );
				}               
				$page_config['forms'][] = $f;
				$page_configs[] = $page_config;
			}
		}	
		
		return $page_configs;
	}
	add_filter('qtranslate_load_admin_page_config','bamobile_mobiconnector_add_admin_page_config');
}

/**
 * Create Menu if qTranstale active
 */
function bamobile_mobiconnector_create_menu_language(){
	if(is_plugin_active('qtranslate-x/qtranslate.php')){
		$parent_slug = 'mobiconnector-settings';
		add_submenu_page(
			$parent_slug,
			__('Languages'),
			__('Languages'),
			'manage_options',
			'mobi-language',
			'bamobile_mobiconnector_action_create_menu_language'
		);
	}
}
add_action( 'admin_menu','bamobile_mobiconnector_create_menu_language',30);

/**
 * Redirect to settings language
 */
function bamobile_mobiconnector_action_create_menu_language(){
	wp_redirect(admin_url().'options-general.php?page=qtranslate-x');
}
?>