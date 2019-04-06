<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Get template to categories
 * 
 * @since 1.1.5
 * 
 * @param string $name          name of file include
 * @param array $params         params when the print
 * @param boolean $echo_html    return html or not
 * 
 */
function bamobile_mobiconnector_get_category_template($name, $params = array(), $echo_html = true){
    $filename = MOBICONNECTOR_ABSPATH . 'hooks/templates/' . $name . '.php';

    if (! file_exists($filename)) {
        return;
    }
    
    foreach ($params as $param => $value) {
        $$param = $value;
    }

    ob_start();
    include_once($filename);
    $html = ob_get_contents();
    ob_end_clean();

    if (! $echo_html) {
        return $html;
    }

    echo $html;
}
/**
 * Get image html by categories
 * 
 * @since 1.1.5
 * 
 * @param array $param            params of images
 * @param boolean $echo           return images or not
 */
function bamobile_mobiconnector_category_image($params = array(), $echo = false){
    if(class_exists('Mobiconnector_Categories_Avatar')){
        $image_header = BAMobile_Categories_Avatar::bamobile_get_category_image($params);

        if (!$echo) {
            return $image_header;
        }

        echo $image_header;
    }
}
/**
 * Get image src by categories
 * 
 * @since 1.1.5
 * 
 * @param array $param            params of images
 * @param boolean $echo           return images or not
 * 
 */
function bamobile_mobiconnector_category_image_src($params = array(), $echo = false){
    if(class_exists('Mobiconnector_Categories_Avatar')){
        $image_header = BAMobile_Categories_Avatar::bamobile_get_category_image($params, true);
        
        if (!$echo) {
            return $image_header;
        }
    
        echo $image_header;
    }
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 * 
 * @since 1.1.5
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function bamobile_mobiconnector_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine
	}

	$located = bamobile_mobiconnector_locate_template( $template_name, $template_path, $default_path );	

	if ( ! file_exists( $located ) ) {
		/* translators: %s template */
		bamobile_mobiconnector_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'lformlogin' ), '<code>' . $located . '</code>' ), '2.1' );
		return;
	}
    
	include_once($located);
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 * 
 * @since 1.1.5
 *
 * @access public
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function bamobile_mobiconnector_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = 'mobiconnector/';
	}

	if ( ! $default_path ) {
		$default_path = untrailingslashit( plugin_dir_path( MOBICONNECTOR_PLUGIN_FILE ) ) .'/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);
	
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}
    
	// Return what we found.
	return  $template;
}

/**
* Wrapper for mobiconnector_doing_it_wrong.
*
* @since 1.1.5
*
* @param string $function Function used.
* @param string $message Message to log.
* @param string $version Version the message was added in.
*/
function bamobile_mobiconnector_doing_it_wrong( $function, $message, $version ) {
   // @codingStandardsIgnoreStart
   $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

   if ( is_ajax() ) {
	   do_action( 'doing_it_wrong_run', $function, $message, $version );
	   error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
   } else {
	   _doing_it_wrong( $function, $message, $version );
   }
   // @codingStandardsIgnoreEnd
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get template to categories
 * 
 * @param string $name          name of file include
 * @param array $params         params when the print
 * @param boolean $echo_html    return html or not
 * 
 */
function mobiconnector_get_category_template($name, $params = array(), $echo_html = true){
    bamobile_mobiconnector_get_category_template($name, $params, $echo_html);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get image html by categories
 * 
 * @param array $param            params of images
 * @param boolean $echo           return images or not
 */
function mobiconnector_category_image($params = array(), $echo = false){
    bamobile_mobiconnector_category_image($params, $echo);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get image src by categories
 * 
 * @param array $param            params of images
 * @param boolean $echo           return images or not
 * 
 */
function mobiconnector_category_image_src($params = array(), $echo = false){
    bamobile_mobiconnector_category_image_src($params,$echo);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function mobiconnector_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    bamobile_mobiconnector_get_template($template_name,$args,$template_path,$default_path);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * @access public
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function mobiconnector_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    return bamobile_mobiconnector_locate_template($template_name,$template_path,$default_path);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Wrapper for mobiconnector_doing_it_wrong.    
 *    
 * @param string $function Function used.
 * @param string $message Message to log.
 * @param string $version Version the message was added in.
*/
function mobiconnector_doing_it_wrong( $function, $message, $version ) {
    bamobile_mobiconnector_doing_it_wrong($function, $message, $version);
}
?>