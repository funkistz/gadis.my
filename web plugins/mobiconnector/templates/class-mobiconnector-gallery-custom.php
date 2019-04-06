<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create page with type mobi gallery 
 */
class BAMobile_Gallery_Custom{

    /**
     * Mobiconnector_Gallery_Custom construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function init_hooks(){
        add_action( 'add_meta_boxes', array($this,'bamobile_mobiconnector_add_metabox_gallery') );  
        add_action( 'admin_enqueue_scripts',array($this,'bamobile_mobiconnector_add_script_gallaries'));      
    }

    /**
     * Style and Script of gallery
     * 
     * @param string $hook  The current admin page.
     */
    public function bamobile_mobiconnector_add_script_gallaries($hook){
        global $post;
        if (is_admin() && $hook == 'post.php' && !empty($post) && $post->post_type=='mobi_gallery' || is_admin() && $hook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'mobi_gallery' ) {
            wp_register_style( 'mobiconnector-admin-galleries-style', plugins_url('assets/css/mobiconnector-galleries.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'mobiconnector-admin-galleries-style' );		

            wp_enqueue_script(
                'mobiconnector_galleries_js',
                plugins_url('/assets/js/mobiconnector-galleries.js', MOBICONNECTOR_PLUGIN_BASENAME),
                array('jquery'),
                MOBICONNECTOR_VERSION,
                true
            );
            wp_enqueue_media();
        }
    }

    /**
     * Create meta box in details mobi gallery in admin
     */
    public function bamobile_mobiconnector_add_metabox_gallery(){
        add_meta_box( 'mobiconnector_product_fields', __('Photos','mobiconnector'), array($this,'bamobile_mobiconnector_content_metabox_gallery'), 'mobi_gallery', 'normal', 'high' );
    }

    /**
     * Content metabox in details mobi gallery in admin
     */
    public function bamobile_mobiconnector_content_metabox_gallery(){
        global $post;
        ?>
		    <div class="mobiconnector-list-image-galleries">
                <?php $this->bamobile_mobiconnector_get_details_gallery(); ?>
            </div>
        <?php
    }

    /**
     * Html of details meta box
     */
    public function bamobile_mobiconnector_get_details_gallery(){
        global $post;
        $list_galleries = array();
        if(!empty($post)){
            $post_id = $post->ID;
            $list_galleries = get_post_meta($post_id,'mobiconnector-list-galleries',true);
            if(!empty($list_galleries) && is_string($list_galleries)){
                $list_galleries = unserialize($list_galleries);
            }
        }
        if(!empty($list_galleries)){      
            foreach($list_galleries as $gallery_id){
                $images = wp_get_attachment_url($gallery_id);
        ?>
            <div class="mobiconnector-content-galleries">
                <input type="hidden" class="mobiconnector-list-hidden-save-galleries" name="mobiconnector-list-galleries[]" value="<?php echo esc_html($gallery_id); ?>" />
                <img class="mobiconnector-settings-gallary-image" src="<?php echo esc_html($images); ?>" />
                <a class="mobiconnector-delete-gallary-image" ><span class="dashicons dashicons-trash"></span></a>
            </div>
        <?php
            }  
        }
    }
}
$BAMobile_Gallery_Custom = new BAMobile_Gallery_Custom();
?>