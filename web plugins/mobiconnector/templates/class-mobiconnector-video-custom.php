<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create page with type mobi video 
 */
class BAMobile_Video_Custom{

    /**
     * Mobiconnector_Video_Custom construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function init_hooks(){
        add_action( 'add_meta_boxes', array($this,'bamobile_mobiconnector_add_metabox_video') );  
        add_action( 'admin_enqueue_scripts',array($this,'bamobile_mobiconnector_add_script_videos'));      
    }

    /**
     * Style and Script of video
     * 
     * @param string $hook  The current admin page.
     */
    public function bamobile_mobiconnector_add_script_videos($hook){
        global $post;
        if (is_admin() && $hook == 'post.php' && !empty($post) && $post->post_type=='mobi_video' || is_admin() && $hook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'mobi_video' ) {
            wp_register_style( 'mobiconnector-admin-videos-style', plugins_url('assets/css/mobiconnector-videos.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'mobiconnector-admin-videos-style' );	

            wp_enqueue_script(
                'mobiconnector_videos_js',
                plugins_url('/assets/js/mobiconnector-videos.js', MOBICONNECTOR_PLUGIN_BASENAME),
                array('jquery'),
                MOBICONNECTOR_VERSION,
                true
            );
            wp_enqueue_media(array('post'=>$post->ID));
        }
    }

    /**
     * Create meta box in details mobi video in admin
     */
    public function bamobile_mobiconnector_add_metabox_video(){
        add_meta_box( 'mobiconnector_product_fields', esc_html(__('Video Files/Links','mobiconnector')), array($this,'bamobile_mobiconnector_content_metabox_video'), 'mobi_video', 'normal', 'high' );
    }

    /**
     * Content metabox in details mobi video in admin
     */
    public function bamobile_mobiconnector_content_metabox_video(){
        global $post;
        ?>
		    <div class="mobiconnector-list-image-videos">
                <?php $this->bamobile_mobiconnector_get_details_video(); ?>
            </div>
        <?php
    }

    /**
     * Html of details meta box
     */
    public function bamobile_mobiconnector_get_details_video(){
        global $post;
        $option = 'animoto';
        $videolink = '';
        $listoptions = array(
            'animoto'       => 'Animoto',
            'blip'          => 'Blip',
            'cloudup'       => 'Cloudup',
            'collegehumor'  => 'CollegeHumor',
            'dailymotion'   => 'DailyMotion',
            'facebook'      => 'Facebook',
            'flickr'        => 'Flickr',
            'funnyordie'    => 'FunnyOrDie.com',
            'hulu'          => 'Hulu',
            'ted'           => 'TED',
            'vimeo'         => 'Vimeo',
            'vine'          => 'Vine',
            'wordpress'     => 'WordPress.tv',
            'youtube'       => 'Youtube',
            'upload'        => __('Upload Video')
        );
        if(!empty($post)){
            $post_id = $post->ID;
            $option = get_post_meta($post_id,'mobiconnector-option-video',true);
            $videolink = get_post_meta($post_id,'mobiconnector-link-video',true);
        }
        ?>
            <div class="mobiconnector-content-videos">
                <div class="mobiconnector-options">
                    <table class="mobiconnector-table-link-video">
                        <tr>
                            <td class="mobi-table-label">
                                <label class="mobiconnector-video-label" for="mobiconnector-videos-select"><?php esc_html_e('Video Options','mobiconnector'); ?></label>
                            </td>
                            <input type="hidden" id="type-hidden" value="<?php echo esc_html($option); ?>"/>
                            <input type="hidden" id="link-hidden" value="<?php echo esc_html($videolink); ?>"/>
                            <td class="mobi-table-input">
                                <select id="mobiconnector-videos-select" name="mobiconnector-video-select">
                                    <?php
                                        foreach($listoptions as $key => $value){
                                    ?>
                                    <option <?php if(!empty($option) && $option == $key){echo 'selected="selected"';} ?> value="<?php echo esc_html($key); ?>"><?php echo esc_html($value); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="mobi-table-label">
                                <label class="mobiconnector-video-label" for="mobiconnector-link-video"><?php esc_html_e('Video','mobiconnector'); ?></label>
                            </td>
                            <td <?php if($option !== 'upload'){ echo 'style="display:block;"'; }else{ echo 'style="display:none;"'; } ?> id="input-link" class="mobi-table-input">
                                <input type="text" class="mobiconnector-video-input" name="mobiconnector-link-video" id="mobiconnector-link-video" value="<?php echo esc_html($videolink); ?>" />
                            </td>
                            
                            <td <?php if($option == 'upload'){ echo 'style="display:block;"'; }else{ echo 'style="display:none;"'; } ?> id="upload-link" class="mobi-table-input">
                                <div id="mobi-upload-video">
                                    <div id="upload-video-option">
                                        <a class="button" id="select-videos"><?php esc_html_e('Upload Video'); ?></a>
                                    </div>
                                    <div id="upload-video">
                                        <input type="text" class="mobiconnector-video-input" readonly="true" name="mobiconnector-link-video-upload" id="mobiconnector-upload-link" value="<?php echo esc_html($videolink); ?>"/>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div> 
            </div>
        <?php         
    }
}
$BAMobile_Video_Custom = new BAMobile_Video_Custom();
?>