<?php
define( 'DOING_AJAX', true );
if ( ! defined( 'WP_ADMIN' ) ) {
    define( 'WP_ADMIN', true );
}	

if (isset($_REQUEST['baseurl']) && isset($_REQUEST['attachment_id']) && isset($_REQUEST['type']) && $_REQUEST['type'] == 'addslide') {
    $attachment_id = $_REQUEST['attachment_id'];
    $baseurl = $_REQUEST['baseurl'];
    /** Load WordPress Bootstrap */
    require_once( $baseurl . '/wp-load.php' );
    /** Allow for cross-domain requests (from the front end). */
    send_origin_headers();	
    /** Load WordPress Administration APIs */
    require_once( $baseurl . 'wp-admin/includes/admin.php' );

    /** Load Ajax Handlers for WordPress Core */
    require_once( $baseurl . 'wp-admin/includes/ajax-actions.php' );
    @header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
    @header( 'X-Robots-Tag: noindex' );

    send_nosniff_header();
    nocache_headers();
    global $wpdb;
    $prefix = $wpdb->prefix;
    $now = new DateTime();
    $datetime = $now->format('Y-m-d H:i:s');
    $gmtdatetime = gmdate("Y-m-d H:i:s");
    $wpdb->insert( 
        $prefix.'posts', 
        array( 
            'post_title' => 'Simple - '. $attachment_id .' - image', 
            'post_name' => 'simple-'. $attachment_id .'-image',
            'post_status' => 'publish',
            'post_type'  => 'wooslide',
            'post_date'  => $datetime,
            'post_date_gmt' => $gmtdatetime
        ), 
        array( 
            '%s', 
            '%s',
            '%s', 
            '%s', 
            '%s', 
            '%s'
        ) 
    );
    $post_id =  $wpdb->insert_id;
    update_post_meta($post_id,'_thumbnail_id',$attachment_id);
    update_post_meta($post_id,'_wp_attachment_image_alt','');
    $attachment = wp_get_attachment_image_src($attachment_id,'thumbnail');
    $deletelink = wp_nonce_url(admin_url('admin-post.php??action=woo_delete_slide&slide_id='.$post_id),'woo_delete_slide');
    ?>
    <tr class="woo-slide">
        <td class="col-1 ui-sortable-handle">
            <div class="thumb" style="background-image: url(<?php echo $attachment[0]; ?>)">
                <a title="Delete slide" class="tipsy-tooltip-top delete-slide dashicons dashicons-trash" href="<?php echo $deletelink; ?>">Delete slide</a>				
            </div>
        </td>
        <td class="col-2">
            <ul class="tabs"><li class="selected" rel="tab-0"><?php _e('General','wooconnector'); ?></li></ul><div class="tabs-content"><div class="tab tab-0"><textarea name="attachment[<?php echo $post_id; ?>][post_excerpt]" placeholder="Caption"></textarea>
            <input class="url" type="text" name="attachment[<?php echo $post_id; ?>][url]" placeholder="URL" value="">
            <input type="hidden" name="resize_slide_id" data-slide_id="<?php echo $post_id; ?>" data-width="700" data-height="300">
        </td>
    </tr>
    <?php
}
?>