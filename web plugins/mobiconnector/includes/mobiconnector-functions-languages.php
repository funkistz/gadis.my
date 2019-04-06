<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Check languages enable
 * 
 * @since 1.1.5
 * 
 * @param string $lang iso lang want check
 * 
 * @return bool
 */
function bamobile_mobiconnector_is_languages_enable($lang){
	global $wpdb;
	if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){ 		
		$enable = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'icl_locale_map WHERE code = "'.$lang.'"');
		if($enable > 0){
			return true;
		}
		return false;
	}elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
		$listenable = get_option('qtranslate_enabled_languages');
		if(in_array($lang,$listenable)){
			return true;
		}
		return false;
	}else{
		return true;
	}	
}

/**
 * Use multilanguages in title
 * 
 * @since 1.1.5
 * 
 * @param string $text Text want pre with translate
 * 
 * @return string Text after qtrans
 * 
 */
function bamobile_mobiconnector_use_languages_in_title($text){    	
    if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
		$current_lang = (isset($_GET['mobile_lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : bamobile_mobiconnector_get_default_multilang();
	}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
		$current_lang = (isset($_GET['lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : bamobile_mobiconnector_get_default_multilang();
	}else{
		$current_lang = bamobile_mobiconnector_get_default_multilang();
	}
	return bamobile_mobiconnector_pre_content_qtrans($current_lang,$text);
}
add_filter('mobiconnector_languages','bamobile_mobiconnector_use_languages_in_title');

/**
 * Use multilanguages in content
 * 
 * @since 1.1.5
 * 
 * @param string $text Text want pre with translate
 * 
 * @return string Text after qtrans
 */
function bamobile_mobiconnector_use_languages_in_content($text){    
    if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
		$current_lang = (isset($_GET['mobile_lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : bamobile_mobiconnector_get_default_multilang();
	}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
		$current_lang = (isset($_GET['lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : bamobile_mobiconnector_get_default_multilang();
	}else{
		$current_lang = bamobile_mobiconnector_get_default_multilang();
	}
    return bamobile_mobiconnector_pre_content_qtrans($current_lang,$text,true);
}
add_filter('mobiconnector_languages_content','bamobile_mobiconnector_use_languages_in_content');

/**
 * Get default Multi lang
 * 
 * @since 1.1.5
 * 
 * @return string default languages
 */
function bamobile_mobiconnector_get_default_multilang(){
	if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){ 	
		$settings = get_option('icl_sitepress_settings');
		if(!empty($settings)){
			return $settings['default_language'];
		}else{
			return 'en';
		}
	}elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
		$default = get_option('qtranslate_default_language');
		if(!empty($default)){
			return $default;
		}else{
			return 'en';
		}
	}else{
		return 'en';
	}
}

/**
 * Get default qtranslate languages
 * 
 * @since 1.1.5
 * 
 * @return string default of qtranslate
 */
function bamobile_mobiconnector_get_qtranslate_default_language(){
	$default = get_option('qtranslate_default_language');
	if(is_plugin_active('qtranslate-x/qtranslate.php') && !empty($default)){
		return $default;
	}else{
		return 'en';
	}
}

/**
 * Get default WPML languages
 * 
 * @since 1.1.5
 * 
 * @return string default of WPML
 */
function bamobile_mobiconnector_get_default_wpml_languages(){
	$settings = get_option('icl_sitepress_settings');
	if(!empty($settings)){
		return $settings['default_language'];
	}else{
		return 'en';
	}
}

/**
 * Pre data in database if use qtranslate
 * 
 * @since 1.1.5
 * 
 * @param string                $lang   languages you want translate
 * @param string|array|object   $text   text want translate
 * 
 * @return string text translated
 */
function bamobile_mobiconnector_pre_content_qtrans($lang, $text, $content = false) {
    if(isset($_GET['mobile_lang'])){
        if(is_array($text)) {
            // handle arrays recursively
            foreach($text as $key => $t) {
                $text[$key] = bamobile_mobiconnector_pre_content_qtrans($lang,$t,$content);
            }
            return $text;
        }
    
        if( is_object($text) || $text instanceof __PHP_Incomplete_Class ) {//since 3.2-b1 instead of @get_class($text) == '__PHP_Incomplete_Class'
            foreach(get_object_vars($text) as $key => $t) {
                if(!isset($text->$key)) continue;
                $text->$key = bamobile_mobiconnector_pre_content_qtrans($lang,$t,$content);
            }
            return $text;
        }
    
        // prevent filtering weird data types and save some resources
        if(!is_string($text) || empty($text))//|| $text) == ''
            return $text;
    
        return bamobile_mobiconnector_use_language($lang, $text, $content);
    }else{
		if($content){
			return apply_filters('the_content',$text);
		}else{
			return apply_filters('post_title',$text);
		}
	}
}

/**
 * split text at all language comments and quick tags
 * 
 * @since 1.1.5
 * 
 * @param string $text text want blocks
 * 
 * @return array 
 */
function bamobile_mobiconnector_get_language_blocks($text) {
	$split_regex = "#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism";
	return preg_split($split_regex, $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
}

/** 
 * When $text is already known to be string 
 * 
 * @since 1.1.5
 * 
 * @param string                $lang       languages you want translate
 * @param string|array|object   $text       text want translate
 * 
 * @return string text with block
 */
function bamobile_mobiconnector_use_language($lang, $text) {
	$blocks = bamobile_mobiconnector_get_language_blocks($text);
	if(count($blocks)<=1){
		return $text;
	}//no language is encoded in the $text, the most frequent case
	return bamobile_mobiconnector_use_block($lang, $blocks);
}

/** 
 * When $text use block
 * 
 * @since 1.1.5
 * 
 * @param string                $lang       languages you want translate
 * @param string|array|object   $text       text want translate
 * 
 * @return string text with block
 */
function bamobile_mobiconnector_use_block($lang, $blocks) {
	$available_langs = array();
	$content = bamobile_mobiconnector_split_blocks($blocks,$available_langs);
	return bamobile_mobiconnector_get_content_with_qtrans($lang, $content, $available_langs);
}

/** 
 * Get content with lang
 * 
 * @since 1.1.5
 * 
 * @param string                $lang      languages you want translate
 * @param string|array|object   $content   text want translate
 * 
 * @return string text translated
 */
function bamobile_mobiconnector_get_content_with_qtrans($lang, $content, $available_langs) {
    if(!empty($available_langs[$lang])){
		return $content[$lang];
	}
	return '';
}

/**
 * $found added
 * 
 * @since 1.1.5
 * 
 * @param array $blocks list content in database when the use block
*/
function bamobile_mobiconnector_split_blocks($blocks, &$found = array()) {
    $result = array();
    $languages = bamobile_mobiconnector_get_qtranslate_enable_languages();
	foreach($languages as $language) {
		$result[$language] = '';
	}
	$current_language = false;
	foreach($blocks as $block) {
		// detect c-tags
		if(preg_match("#^<!--:([a-z]{2})-->$#ism", $block, $matches)) {
			$current_language = $matches[1];
			continue;
		// detect b-tags
		}elseif(preg_match("#^\[:([a-z]{2})\]$#ism", $block, $matches)) {
			$current_language = $matches[1];
			continue;
		// detect s-tags @since 3.3.6 swirly bracket encoding added
		}elseif(preg_match("#^\{:([a-z]{2})\}$#ism", $block, $matches)) {
			$current_language = $matches[1];
			continue;
		}
		switch($block){
			case '[:]':
			case '{:}':
			case '<!--:-->':
				$current_language = false;
				break;
			default:
				// correctly categorize text block
				if($current_language){
					if(!isset($result[$current_language])) $result[$current_language]='';
					$result[$current_language] .= $block;
					$found[$current_language] = true;
					$current_language = false;
				}else{
					foreach($languages as $language) {
						$result[$language] .= $block;
					}
				}
			break;
		}
	}
	//it gets trimmed later in qtranxf_use() anyway, better to do it here
	foreach($result as $lang => $text){
		$result[$lang]=trim($text);
	}
	return $result;
}

/**
 * Term multilanguages
 * 
 * @since 1.1.5
 * 
 * @param string $lang lang want changee
 * @param object $obj  list term want change
 * 
 * @return object
 */
function bamobile_mobiconnector_use_term_for_languages($lang, $obj) {
	$terms_name = get_option('qtranslate_term_name');
	if(empty($terms_name)){
		return $obj;
	}
	if(is_array($obj)) {
		// handle arrays recursively
		foreach($obj as $key => $t) {
			$obj[$key] = bamobile_mobiconnector_use_term_for_languages($lang, $obj[$key]);
		}
		return $obj;
	}
	if(is_object($obj)) {
		// object conversion
		if(isset($terms_name[$obj->name][$lang])) {
			$obj->name = $terms_name[$obj->name][$lang];
		}
	} elseif(isset($terms_name[$obj][$lang])) {
		$obj = $terms_name[$obj][$lang];
	}
	return $obj;
}

/**
 * Hook to get terms and then terms by languages with multilanguages
 * 
 * @since 1.1.5
 * 
 * @param object $obj  list term want change
 * 
 * @return object list term after change
 */
function bamobile_mobiconnector_use_languages_for_terms($obj) {
	if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
		$current_lang = (isset($_GET['mobile_lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : bamobile_mobiconnector_get_default_multilang();
	}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
		$current_lang = (isset($_GET['lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : bamobile_mobiconnector_get_default_multilang();
	}else{
		$current_lang = bamobile_mobiconnector_get_default_multilang();
	}
	return bamobile_mobiconnector_use_term_for_languages($current_lang, $obj);
}

foreach ( array( 'cat_row', 'cat_rows', 'wp_get_object_terms', 'single_tag_title', 'single_cat_title', 'the_category', 'get_term', 'get_terms', 'get_category' ) as $hook ){	
	$url = $_SERVER['REQUEST_URI'];
	if(!is_admin() && strpos($url,'wp-json') !== false){
		remove_filter($hook,'qtranxf_useTermLib',0);
		add_filter($hook, 'bamobile_mobiconnector_use_languages_for_terms',0);
	}
}

foreach ( array('widget_title','the_title','term_name','get_comment_author','the_author','tml_title','widget_text','category_description','list_cats','wp_dropdown_cats','term_description') as $hook ){	
	$url = $_SERVER['REQUEST_URI'];
	if(!is_admin() && strpos($url,'wp-json') !== false){
		remove_filter($hook,'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage');
		add_filter($hook, 'bamobile_mobiconnector_use_languages_in_title',20);
	}
}

/**
 * Filter all options for language tags
 */
function bamobile_mobiconnector_filter_options(){ 
	global $q_config, $wpdb;
	$where;
	$where=' WHERE autoload=\'yes\' AND (option_value LIKE \'%![:__!]%\' ESCAPE \'!\' OR option_value LIKE \'%{:__}%\' OR option_value LIKE \'%<!--:__-->%\')';
	if(isset($_GET['page']) && ($_GET['page'] == 'oli-settings' || $_GET['page'] == 'modernshop-settings')){
		$where .= ' AND option_name NOT IN ("oli_settings-core","modern_settings-core")';
	}
	$result = $wpdb->get_results('SELECT option_name FROM '.$wpdb->options.$where);
	if(!$result) return;
	foreach($result as $row) {
		$option = $row->option_name;
		add_filter('option_'.$option,'bamobile_mobiconnector_translate_option',5);
	}
}
bamobile_mobiconnector_filter_options();

/**
 * Used to filter option values
 * 
 * @since 1.1.5
 * 
 * @return string option after change
 */
function bamobile_mobiconnector_translate_option($value, $current_lang=null){
	if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
		$current_lang = (isset($_GET['mobile_lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : bamobile_mobiconnector_get_default_multilang();
	}elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
		$current_lang = (isset($_GET['lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : bamobile_mobiconnector_get_default_multilang();
	}else{
		$current_lang = bamobile_mobiconnector_get_default_multilang();
	}
	return bamobile_mobiconnector_translate_deep($value,$current_lang);
}

/**
 * @since 1.1.5
 * @param (mixed) $value to translate, which may be array, object or string
 *  and may have serialized parts with embedded multilingual values.
 */
function bamobile_mobiconnector_translate_deep($value,$lang){	
	if(is_string($value)){
		if(is_serialized( $value )){
			$value = unserialize($value);
			$value = bamobile_mobiconnector_translate_deep($value,$lang);//recursive call
			return serialize($value);
		}
		$lang_value =  bamobile_mobiconnector_use_language($lang,$value);
		return $lang_value;
	}elseif(is_array($value)){
		foreach($value as $k => $v){
			$value[$k] = bamobile_mobiconnector_translate_deep($v,$lang);
		}
	}elseif(is_object($value) || $value instanceof __PHP_Incomplete_Class){
		foreach(get_object_vars($value) as $k => $v) {
			if(!isset($value->$k)) continue;
			$value->$k = bamobile_mobiconnector_translate_deep($v,$lang);
		}
	}
	return $value;
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check languages enable
 * 
 * @param string $lang iso lang want check
 * 
 * @return bool
 */
function mobiconnector_is_languages_enable($lang){
	return bamobile_mobiconnector_is_languages_enable($lang);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Use multilanguages in title
 * 
 * @param string $text Text want pre with translate
 * 
 * @return string Text after qtrans
 * 
 */
function mobiconnector_use_languages_in_title($text){   
	return bamobile_mobiconnector_use_languages_in_title($text);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Use multilanguages in content
 * 
 * @since 1.1.5
 * 
 * @param string $text Text want pre with translate
 * 
 * @return string Text after qtrans
 */
function mobiconnector_use_languages_in_content($text){
	return bamobile_mobiconnector_use_languages_in_content();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get default Multi lang
 * 
 * @return string default languages
 */
function mobiconnector_get_default_multilang(){
	return bamobile_mobiconnector_get_default_multilang();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get default qtranslate languages
 * 
 * @return string default of qtranslate
 */
function mobiconnector_get_qtranslate_default_language(){
	return bamobile_mobiconnector_get_qtranslate_default_language();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get default WPML languages
 * 
 * @return string default of WPML
 */
function mobiconnector_get_default_wpml_languages(){
	return bamobile_mobiconnector_get_default_wpml_languages();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Pre data in database if use qtranslate
 * 
 * @param string                $lang   languages you want translate
 * @param string|array|object   $text   text want translate
 * 
 * @return string text translated
 */
function mobiconnector_pre_content_qtrans($lang, $text, $content = false) {
	return bamobile_mobiconnector_pre_content_qtrans($lang, $text, $content);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * split text at all language comments and quick tags
 * 
 * @param string $text text want blocks
 * 
 * @return array 
 */
function mobiconnector_get_language_blocks($text) {
	return bamobile_mobiconnector_get_language_blocks($text);
}

/** 
 * This function will be removed after Jan 01, 2019
 * 
 * When $text is already known to be string 
 * 
 * @param string                $lang       languages you want translate
 * @param string|array|object   $text       text want translate
 * 
 * @return string text with block
 */
function mobiconnector_use_language($lang, $text) {
	return bamobile_mobiconnector_use_language($lang, $text);
}

/** 
 * This function will be removed after Jan 01, 2019
 * 
 * When $text use block
 * 
 * @param string                $lang       languages you want translate
 * @param string|array|object   $text       text want translate
 * 
 * @return string text with block
 */
function mobiconnector_use_block($lang, $blocks) {
	return bamobile_mobiconnector_use_block($lang, $blocks);
}

/** 
 * This function will be removed after Jan 01, 2019
 * 
 * Get content with lang
 * 
 * @param string                $lang      languages you want translate
 * @param string|array|object   $content   text want translate
 * 
 * @return string text translated
 */
function mobiconnector_get_content_with_qtrans($lang, $content, $available_langs) {
	return bamobile_mobiconnector_get_content_with_qtrans($lang, $content, $available_langs);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * $found added
 * 
 * @param array $blocks list content in database when the use block
*/
function mobiconnector_split_blocks($blocks, &$found = array()) {
	return bamobile_mobiconnector_split_blocks($blocks, $found);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Term multilanguages
 * 
 * @param string $lang lang want changee
 * @param object $obj  list term want change
 * 
 * @return object
 */
function mobiconnector_use_term_for_languages($lang, $obj) {
	return bamobile_mobiconnector_use_term_for_languages($lang, $obj);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Hook to get terms and then terms by languages with multilanguages
 * 
 * @param object $obj  list term want change
 * 
 * @return object list term after change
 */
function mobiconnector_use_languages_for_terms($obj) {
	return bamobile_mobiconnector_use_languages_for_terms($obj);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Used to filter option values
 * 
 * @return string option after change
 */
function mobiconnector_translate_option($value, $current_lang=null){
	return bamobile_mobiconnector_translate_option($value, $current_lang);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * @since 1.1.5
 * @param (mixed) $value to translate, which may be array, object or string
 *  and may have serialized parts with embedded multilingual values.
 */
function mobiconnector_translate_deep($value,$lang){	
	return bamobile_mobiconnector_translate_deep($value,$lang);
}
?>