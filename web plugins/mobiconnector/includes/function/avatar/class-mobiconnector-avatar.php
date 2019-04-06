<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
/**
 * MobiConnector Avatar
 * 
 * Add or Edit Avatar in admin and App
 * 
 * @class MobiConnectorAvatar
 */
class BAMobileAvatar{
    
    /**
     * MobiConnector Avatar construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){
        global $pagenow;
        add_filter('default_avatar_select', array($this, 'bamobile_mobiconnector_remove_filter_avatar'), 10);
        add_filter('get_avatar', array($this, 'bamobile_get_avatar_mobiconnector_filter'), 10, 5);
        if(self::bamobile_mobiconnector_is_author_or_above()) {
            // Profile functions and scripts
            add_action('show_user_profile', array($this, 'bamobile_mobiconnector_action_show_user_profile'));
            add_action('edit_user_profile', array($this, 'bamobile_mobiconnector_action_show_user_profile'));
            add_action('user_new_form', array($this, 'bamobile_mobiconnector_action_show_user_profile'));
            add_action('personal_options_update', array($this, 'bamobile_mobiconnector_action_process_option_update'));
            add_action('edit_user_profile_update', array($this, 'bamobile_mobiconnector_action_process_option_update'));
            add_action('user_register', array($this, 'bamobile_mobiconnector_action_process_option_update'));
            // Admin scripts
            $pages = array('profile.php', 'options-discussion.php', 'user-edit.php', 'user-new.php','users.php');
            if(in_array($pagenow, $pages)) {
                add_action('admin_enqueue_scripts', array($this, 'bamobile_mobiconnector_media_upload_scripts'));
            }
            // Front pages
           if(!is_admin()) {
                add_action('show_user_profile', array($this, 'bamobile_mobiconnector_media_upload_scripts'));
                add_action('edit_user_profile', array($this, 'bamobile_mobiconnector_media_upload_scripts'));
            }
            if(!self::bamobile_mobiconnector_is_author_or_above()) {
                // Upload errors
                add_action('user_profile_update_errors', array($this, 'bamobile_mobiconnector_upload_errors'), 10, 3);
                // Prefilter upload size
                add_filter('wp_handle_upload_prefilter', array($this, 'bamobile_mobiconnector_handle_upload_prefilter'));
            }
        }
        
        add_action('wp_enqueue_scripts', array($this, 'bamobile_mobiconnector_media_upload_front'));
        add_filter('media_view_settings', array($this, 'bamobile_mobiconnector_media_view_settings'), 10, 1);
    }
    /**
     * Set post id media view settings
     * 
     * @param array $settings   list setting of media
     * 
     * @return array Setting of media view
     */
    public function bamobile_mobiconnector_media_view_settings($settings) {
        global $post;
        $post_id = is_object($post) ? $post->ID : 0;
        $settings['post']['id'] = (is_admin()) ? $post_id : 0;
        return $settings;
    }

    /**
     * Get avatar by mobiconnector
     * 
     * @param string $avatar             link of avatar
     * @param int|string $id_or_email    id or email
     * @param string $size               size of image
     * @param string $default            default of image
     * @param string $alt                alt of image   
     * 
     * @return string   Html of image avatar
     */
    public function bamobile_get_avatar_mobiconnector_filter($avatar, $id_or_email="", $size="", $default="", $alt=""){
        $avatar = str_replace('gravatar_default','',$avatar);
        if(is_object($id_or_email)) {
            if(!empty($id_or_email->comment_author_email)) {
                $avatar = self::bamobile_get_mobiconnector_avatar($id_or_email, $size, $default, $alt);
            } else {
                $avatar = self::bamobile_get_mobiconnector_avatar('unknown@gravatar.com', $size, $default, $alt);
            }
        } else {
            if(self::bamobile_has_mobiconnector_avatar($id_or_email)) {
                $avatar = self::bamobile_get_mobiconnector_avatar($id_or_email, $size, $default, $alt);
            } else{
                if(!empty($avatar)) {
                    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER);
                    $mobiconnector_avatar_image_src = !empty($matches) ? $matches [0] [1] : "";
                }
                $avatar = '<img src="'.$mobiconnector_avatar_image_src.'" alt="'.$alt.'" class="avatar avatar-'.$size.' mobiconnector-avatar mobiconnector-avatar-'.$size.' photo avatar-default" />';

            }    
        }
        return apply_filters('get_avatar_mobiconnector_filter', $avatar, $id_or_email, $size, $default, $alt);
    }

    /**
     * get default avatar with filter
     * 
     * @return string Html of list check default avatar
     */
    public function bamobile_mobiconnector_remove_filter_avatar() {
        remove_filter('get_avatar', array($this, 'bamobile_get_avatar_mobiconnector_filter'));
        $avatar_list = "";
        $avatar_defaults = array(
            'mystery' => __('Mystery Man'),
            'blank' => __('Blank'),
            'gravatar_default' => __('Gravatar Logo'),
            'identicon' => __('Identicon (Generated)'),
            'wavatar' => __('Wavatar (Generated)'),
            'monsterid' => __('MonsterID (Generated)'),
            'retro' => __('Retro (Generated)')
        );
        if(empty($avatar_default)) {
            $avatar_default = 'mystery';
        }
        foreach($avatar_defaults as $default_key => $default_name) {
            $avatar = get_avatar('unknown@gravatar.com', 32, $default_key);
            $selected = ($avatar_default == $default_key) ? 'checked="checked" ' : "";
            $avatar_list .= "\n\t<label><input type='radio' name='avatar_default' id='avatar_{$default_key}' value='".esc_attr($default_key)."' {$selected}/> ";
            $avatar_list .= preg_replace("/src='(.+?)'/", "src='\$1&amp;forcedefault=1'", $avatar);
            $avatar_list .= ' '.$default_name.'</label>';
            $avatar_list .= '<br />';
        }
        return $avatar_list;
    }

    /**
     * Script of mobiconnector avatar
     */
    public static function bamobile_mobiconnector_media_upload_scripts() {
        global $current_user,$pagenow,$post;
        $user = ($pagenow == 'user-edit.php' && isset($_GET['user_id'])) ? get_user_by('id', $_GET['user_id']) : $current_user;
        $avatar_default = self::bamobile_get_gravatar_url($user->user_email);
        wp_register_script('mobiconnector_avatar_js', plugins_url('assets/js/mobiconnector-avatar.js',MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);	
		$setting = array(
			'current_user' => $user,
            'default_avatar' => $avatar_default
		);	
		wp_localize_script( 'mobiconnector_avatar_js', 'mobiconnector_avatar_js_params',  $setting );
		wp_enqueue_script( 'mobiconnector_avatar_js' );
        wp_enqueue_media(array('post' => $post));
        wp_register_style( 'mobiconnector-avatar-style', plugins_url('assets/css/mobiconnector-avatar.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
		wp_enqueue_style( 'mobiconnector-avatar-style' );
       
    }

    /**
     * Style of frontend mobiconnector avatar
     */
    public static function bamobile_mobiconnector_media_upload_front(){
        if(is_home() || is_front_page() || is_singular() || (is_plugin_active('woocommerce/woocommerce.php') && is_shop())){
            wp_register_style( 'mobiconnector-avatar-front-style', plugins_url('assets/css/mobiconnector-avatar-front.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'mobiconnector-avatar-front-style' );
        }
    }
    /**
     * Create Html insert image in profile
     * 
     * @param object $user              user with add mobiconnector to profile
     */
    public static function bamobile_mobiconnector_action_show_user_profile($user) {
        $id_images = get_user_meta(@$user->ID, 'mobiconnector-avatar', true);
        $show_avatars = get_option('show_avatars');
        $posturl = get_post($id_images);
        $url = "";
        if(!empty($posturl)){
            $url = $posturl->guid;
        }
        $has_avatar = self::bamobile_has_mobiconnector_avatar(@$user->ID);
        $hide_images = (bool)$show_avatars == 0 ? "mobiconnector_hide" : "";
        $avatar_default = self::bamobile_get_default_avatar(@$user->ID,"thumbnail");
        $hide_delete = !$has_avatar ? "mobiconnector_hide" : "";
        $avatar_thumbnail = $has_avatar ? self::bamobile_get_mobiconnector_avatar_src(@$user->ID, 96) : $avatar_default;
    ?>
        <div id="mobiconnector-avatar" class="<?php echo $hide_images; ?>">
            <h3><?php esc_html_e(__('MobiConnector Avatar')); ?></h3>
            <div id="mobiconnector-label-avatar">
                <h4><?php esc_html_e(__('Image')); ?></h4>
            </div>
            <div id="mobiconnector-content-avatar">
                <div class="mobiconnector-list-content-avatar">
                    <?php if(!self::bamobile_mobiconnector_is_author_or_above()){ ?>
                        <input name="mobiconnector-file" id="mobiconnector-upload-file" type="file" />
                        <button type="submit" class="button" class="mobiconnector-button-input-image"  name="submit" value="<?php _e('Upload'); ?>"><?php _e('Upload'); ?></button>
                    <?php }else{ ?>
                        <input type="button" class="mobiconnector-button-input-image" id="mobiconnector-button-input-image" value="<?php esc_html_e(__('Choose Images')); ?>">
                    <?php } ?>
                </div>
                <div class="mobiconnector-list-content-avatar">
                    <div id="mobiconnector-images-content">
                        <img src="<?php echo $avatar_thumbnail; ?>" class="avatar avatar-default mobiconnector-avatar mobiconnector-upload photo"/>
                    </div>
                    <span id="mobiconnector-images-after-span"><?php esc_html_e(__('Thumbnail')); ?></span>
                    <input type="hidden" name="mobiconnector-user-avatar" id="mobiconnector-user-avatar" value="<?php echo $id_images; ?>">
                    <input type="hidden" name="mobiconnector-user-avatar-url" id="mobiconnector-user-avatar-url" value="<?php echo $url; ?>" />
                </div>
                <div class="mobiconnector-list-content-avatar">
                    <input type="button" class="mobiconnector-button-input-image <?php echo $hide_delete; ?>" id="mobiconnector-button-delete-image" value="<?php esc_html_e(__('Remove Images')); ?>">
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Process data when the save profile
     * 
     * @param int $user_id       id of user save profile
     */
    public static function bamobile_mobiconnector_action_process_option_update($user_id) {
        global $blog_id, $post, $wpdb;
        if(self::bamobile_mobiconnector_is_author_or_above()) {
            $mobiavatar_id = isset($_POST['mobiconnector-user-avatar']) ? sanitize_text_field(strip_tags($_POST['mobiconnector-user-avatar'])) : "";
            delete_metadata('post', null, '_wp_attachment_mobiconnector_avatar', $user_id, true);
            add_post_meta($mobiavatar_id, '_wp_attachment_mobiconnector_avatar', $user_id);
            update_user_meta($user_id, 'mobiconnector-avatar' , $mobiavatar_id);
        } else {
            // Remove attachment info if avatar is blank
            if(isset($_POST['mobiconnector-user-avatar']) && empty($_POST['mobiconnector-user-avatar'])) {
                $q = array(
                    'author' => $user_id,
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => '-1',
                    'meta_query' => array(
                        array(
                        'key' => '_wp_attachment_mobiconnector_avatar',
                        'value' => "",
                        'compare' => '!='
                        )
                    )
                );
                $avatars_wp_query = new WP_Query($q);
                while($avatars_wp_query->have_posts()) : $avatars_wp_query->the_post();
                    wp_delete_attachment($post->ID);
                endwhile;
                wp_reset_query();
                delete_metadata('post', null, '_wp_attachment_mobiconnector_avatar', $user_id, true);
                update_user_meta($user_id, 'mobiconnector-avatar', "");
            }elseif(isset($_POST['submit']) && $_POST['submit'] && isset($_POST['mobiconnector-user-avatar']) && !empty($_POST['mobiconnector-user-avatar'])) {
                $id_url = sanitize_text_field($_POST['mobiconnector-user-avatar']);
                $posturl = get_post($id_url);
                $url = "";
                if(!empty($posturl)){
                    $url = $posturl->guid;
                }
                $fileurl = str_replace($wp_upload_dir['baseurl'],'',$url);
                $absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$fileurl;
                $path_parts = pathinfo($fileurl);
                $ext = strtolower($path_parts['extension']);
                $basename = strtolower($path_parts['basename']);
                $dirname = strtolower($path_parts['dirname']);
                $filename = strtolower($path_parts['filename']);
                $uploaded_image = wp_get_image_editor($url);
                if(!is_wp_error($uploaded_image)) {
                    $uploaded_image->resize(96, 96, false);
                    $resized_image = $uploaded_image->save($url);
                }
                $size = getimagesize($url);
                $type = $size['mime'];
                $attachment = array(
                    'guid'           => $url,
                    'post_mime_type' => $type,
                    'post_title'     => $filename,
                    'post_content'   => ""
                );
                // This should never be set as it would then overwrite an existing attachment
                if(isset($attachment['ID'])) {
                    unset($attachment['ID']);
                }
                // Save the attachment metadata
                $attachment_id = wp_insert_attachment($attachment, $url);
                if(!is_wp_error($attachment_id)) {
                    // Delete other uploads by user
                    $q = array(
                        'author' => $user_id,
                        'post_type' => 'attachment',
                        'post_status' => 'inherit',
                        'posts_per_page' => '-1',
                        'meta_query' => array(
                            array(
                                'key' => '_wp_attachment_mobiconnector_avatar',
                                'value' => "",
                                'compare' => '!='
                            )
                        )
                    );
                    $avatars_wp_query = new WP_Query($q);
                    while($avatars_wp_query->have_posts()) : $avatars_wp_query->the_post();
                        wp_delete_attachment($post->ID);
                    endwhile;
                    wp_reset_query();
                    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $url));
                    delete_metadata('post', null, '_wp_attachment_mobiconnector_avatar', $user_id, true);
                    update_post_meta($attachment_id, '_wp_attachment_mobiconnector_avatar', $user_id);
                    update_user_meta($user_id, 'mobiconnector-avatar', $attachment_id);
                } 
            }
        }
    }

    /**
     * Get mobiconnector avatar by size
     * 
     * @param int|string $id_or_email       id or email user get avatar
     * @param string     $size              size of image
     * 
     * @return string  Html of avatar
     */
    public static function bamobile_get_mobiconnector_avatar($id_or_email, $size = "thumbnail"){
        $email = "";
        $alt = "";
        if(is_object($id_or_email)) {
            if($id_or_email->user_id != 0) {
                 $email = $id_or_email->user_id;
            }elseif(!empty($id_or_email->comment_author_email)){
                 $user = get_user_by('email', $id_or_email->comment_author_email);
                 $email = !empty($user) ? $user->ID : $id_or_email->comment_author_email;
            }
            $alt = $id_or_email->comment_author;
        }else{
            if(!empty($id_or_email)) {
                $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
            }else{
                $author_name = get_query_var('author_name');
                if(is_author()) {
                    $user = get_user_by('slug', $author_name);
                } else {
                    $user_id = get_the_author_meta('ID');
                    $user = get_user_by('id', $user_id);
                }
            }
            if(!empty($user)) {
                $email = $user->ID;
                $alt = $user->display_name;
            }
        }
        $author_meta = get_the_author_meta('mobiconnector-avatar',$email);
        if(!empty($author_meta) && wp_attachment_is_image($author_meta)){
            $author_images =  wp_get_attachment_image_src($author_meta,$size);
            $avatar = '<img src="'.$author_images[0].'" alt="'.$alt.'" class="avatar avatar-'.$size.' mobiconnector-avatar mobiconnector-avatar-'.$size.' photo" />';
        }else{
            $avatar = get_avatar($email,$size);
            $replace = array('mobiconnector-avatar', ' phone');
            $replacements = array("", " mobiconnector-avatar photo");
            $avatar = str_replace($replace, $replacements, $avatar);
        }
        return $avatar;
    }

    /**
     * Get mobiconnector avatar src by size
     * 
     * @param int|string $id_or_email       id or email user get avatar
     * @param string     $size              size of image  
     *          
     * @return string  Src of avatar
     */
    public static function bamobile_get_mobiconnector_avatar_src($id_or_email="", $size="") {
        $mobiconnector_image_src = "";
        // Gets the avatar img tag
        $mobiconnector_image = self::bamobile_get_mobiconnector_avatar($id_or_email, $size);
        // Takes the img tag, extracts the src
        if(!empty($mobiconnector_image)) {
            $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $mobiconnector_image, $matches, PREG_SET_ORDER);
            $mobiconnector_image_src = !empty($matches) ? $matches [0] [1] : "";
        }
        return $mobiconnector_image_src;
    }

    /**
     * Get default avatar src by size
     * 
     * @param int|string $id_or_email       id or email user get avatar
     * @param string     $size              size of image           
     * 
     * @return string  Html of default avatar
     */
    public static function bamobile_get_default_avatar($id_or_email,$size='thumbnail'){
        $id_default = get_option('avatar_default');
        $mobiconnector_image = get_avatar($id_or_email, $size, $id_default);
        // Takes the img tag, extracts the src
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $mobiconnector_image, $matches, PREG_SET_ORDER);
        $default = !empty($matches) ? $matches [0] [1] : "";
        return $default;
    }

    /**
     * Check permission of user with use mobiconnector avatar
     * 
     * @return boolean
     */
    public static function bamobile_mobiconnector_is_author_or_above() {
        if(strpos($_SERVER['REQUEST_URI'],'consumer_key') !== false || get_current_user_id() > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check mobiconnector avatar exist
     * 
     * @param int|string $id_or_email       id or email user get avatar      
     * 
     * @return boolean
     */
    public static function bamobile_has_mobiconnector_avatar($id_or_email="") {
        $email = "";
        $user = array(); 
        $user_id = "";
        if(!is_object($id_or_email) && !empty($id_or_email)) {
            $user = is_numeric($id_or_email) ? get_user_by('id', $id_or_email) : get_user_by('email', $id_or_email);
            $user_id = !empty($user) ? $user->ID : "";
            $email = !empty($user) ? $user->user_email : "";
        }
        $mobiavatar = get_user_meta($user_id, 'mobiconnector-avatar', true);
        $avatar_default = self::bamobile_get_gravatar_url($email);
        $has_avatar = (!empty($mobiavatar) && $mobiavatar != $avatar_default && wp_attachment_is_image($mobiavatar)) ? true : false;
        return (bool) $has_avatar;
    }

    /**
     * Process with update or insert profile error
     * 
     * @param array $errors       error of update profile      
     * @param boolean $update     update or insert
     * @param object $user        user update or inset
     */
    public static function bamobile_mobiconnector_upload_errors($errors, $update, $user) {
        $upload_size_limit = wp_max_upload_size();
        // Convert to KB
        if($upload_size_limit > 1024) {
            $upload_size_limit /= 1024;
        }
        if($update && !empty($_FILES['mobiconnector-file'])) {
            $size = sanitize_text_field($_FILES['mobiconnector-file']['size']);
            $type = sanitize_text_field($_FILES['mobiconnector-file']['type']);
            $upload_dir = wp_upload_dir();
            // Allow only JPG, GIF, PNG
            if(!empty($type) && !preg_match('/(jpe?g|gif|png)$/i', $type)) {
                $errors->add('mobiconnector_file_type', __('This file is not an image. Please try another.'));
            }
            // Upload size limit
            if(!empty($size) && $size > $upload_size_limit) {
                $errors->add('mobiconnector_file_size', __('Memory exceeded. Please try another smaller file.'));
            }
            // Check if directory is writeable
            if(!is_writeable($upload_dir['path'])) {
                $errors->add('mobiconnector_file_directory', sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?'), $upload_dir['path']));
            }
        }
    }

    /**
     * Check data with update or insert profile
     * 
     * @param array $file      file image upload in avatar 
     * 
     * @return array File just upload
     */
    public function bamobile_mobiconnector_handle_upload_prefilter($file) {
        $upload_size_limit = wp_max_upload_size();
        // Convert to KB
        if($upload_size_limit > 1024) {
            $upload_size_limit /= 1024;
        }
        $size = $file['size'];
        if(!empty($size) && $size > $upload_size_limit) {
            /**
            * Error handling that only appears on front pages
            * @since 1.7
            */
            function bamobile_mobiconnector_file_size_error($errors, $update, $user) {
                $errors->add('mobiconnector_file_size', __('Memory exceeded. Please try another smaller file.'));
            }
            add_action('user_profile_update_errors', 'bamobile_mobiconnector_file_size_error', 10, 3);
            return;
        }
        return $file;
    }

    /**
     * Get gravatar url with email
     * 
     * @param string $email    email get gravatar
     * 
     * @return string link of gravatar
     */
    public static function bamobile_get_gravatar_url( $email ) {
        $id_default = get_option('avatar_default');
        $ratting = strtolower(get_option('avatar_rating'));
        $hash = md5( strtolower( trim ( $email ) ) );
        return 'http://gravatar.com/avatar/' . $hash . '?s=96&d='.$id_default.'&r='.$ratting;
    }
}  
$BAMobileAvatar = new BAMobileAvatar();
?>