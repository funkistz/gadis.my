<?php

if(!defined('ABSPATH'))exit;
add_filter('qtranslate_load_admin_page_config','modernshop_admin_add_page_config');
function modernshop_admin_add_page_config($page_configs){
        $linkxml = MODERN_ABSPATH."xml/settings.xml";
	{//post.php //since 1.0.1
                $page_config = array();
                $page_config['pages'] = array( 'admin.php' => 'page=modernshop-settings');
                //$page_config['anchors'] = array( 'titlediv'  );

                $page_config['forms'] = array();

                $f = array();
                $f['form'] = array( 'id' => 'settings-form' );

                $f['fields'] = array();
                $fields = &$f['fields']; // shorthand
                $checkname = array();
                require_once(MODERN_ABSPATH."xml/modernshop-static.php");
		$xmls = modernshop_get_static();	
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
                $page_config['forms'][] = $f;
                $page_configs[] = $page_config;
	}
	return $page_configs;
}
?>