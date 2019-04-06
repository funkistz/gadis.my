<?php
/**
 * Meta Box in Post
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Meta Box By Box
 */
class BAMobile_Meta_Box_Post{
    
   /**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
    public static function output($post){
        if(!empty($post)){
			$valueproduct = $post->post_title;
		}else{
			$valueproduct = '';
        }
        wp_nonce_field( 'mobiconnector_save_data', 'mobiconnector_meta_box_nonce' );
        include_once( 'views/html-post-meta-notification.php' );
    }
}
?>