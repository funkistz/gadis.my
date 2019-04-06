<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add new line or edit data response in REST API posts
 */
class BAMobilePostType{

    /**
	 * BAMobilePostType construct
	 */
    public function __construct(){
        $this->register_routes();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function register_routes() {
        /*add_action( 'init', array( __CLASS__, 'bamobile_register_post_types' ), 5 );
        add_filter( 'post_updated_messnitages', array($this,'bamobile_mobiconnector_recipe_updated_messages') );
        add_action( 'rest_api_init', array( $this, 'bamobile_register_api_hooks'));
        add_action( 'save_post',array($this,'bamobile_mobiconnector_save_post_customs'));*/        
        add_filter( 'custom_menu_order',array($this,'bamobile_mobiconnector_submenu_order'));
    }    

    /**
     * Change position submenu mobiconnector
     */
    public function bamobile_mobiconnector_submenu_order( $menu_order ) {
        # Get submenu key location based on slug
        global $submenu;
        if(isset($submenu['mobiconnector-settings'])){
            $settings = $submenu['mobiconnector-settings'];
            $index = 0;
            $indexapp = 0;
            foreach ( $settings as $key => $details ) {
                if ( $details[2] == 'mobiconnector-settings' ) {
                    $index = $key;
                }
                if ( $details[2] == 'mobiconnector-application' ) {
                    $indexapp = $key;
                }
            }
            $settings = $submenu['mobiconnector-settings'][$index];
            unset( $submenu['mobiconnector-settings'][$index] );
            array_unshift($submenu['mobiconnector-settings'],$settings);
            $settingsapp = $submenu['mobiconnector-settings'][$indexapp];
            unset( $submenu['mobiconnector-settings'][$indexapp] );
            array_push($submenu['mobiconnector-settings'],$settingsapp);
            ksort( $submenu['mobiconnector-settings'] );
        }        
        return $menu_order;
    }
    
    /**
     * Change notice when the save post
     * 
     * @param array $messages   List message when the save post
     * 
     * @return mixed
     */  
    public function bamobile_mobiconnector_recipe_updated_messages( $messages ) {
        global $post, $post_ID;
        $messages['mobi_gallery'] = array(
            0 => '', 
            1 => sprintf( __( 'Gallery updated. <a href="%s">View gallery</a>', 'mobiconnector' ), esc_url( get_permalink( $post_ID ) ) ),
            2 => '',
            3 => '',
            4 => __( 'Gallery updated.', 'mobiconnector' ),
            5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'mobiconnector' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( 'Gallery published. <a href="%s">View recipe</a>', 'mobiconnector' ), esc_url( get_permalink( $post_ID ) ) ),
            7 => __( 'Gallery saved.', 'mobiconnector' ),
            8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview gallery</a>', 'mobiconnector' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview recipe</a>', 'mobiconnector' ), date_i18n( __( 'M j, Y @ G:i', 'mobiconnector' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
            10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview gallery</a>', 'mobiconnector' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        );
        $messages['mobi_video'] = array(
            0 => '', 
            1 => sprintf( __( 'Video updated. <a href="%s">View video</a>', 'mobiconnector' ), esc_url( get_permalink( $post_ID ) ) ),
            2 => '',
            3 => '',
            4 => __( 'Video updated.', 'mobiconnector' ),
            5 => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'mobiconnector' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( 'Video published. <a href="%s">View recipe</a>', 'mobiconnector' ), esc_url( get_permalink( $post_ID ) ) ),
            7 => __( 'Video saved.', 'mobiconnector' ),
            8 => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview video</a>', 'mobiconnector' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            9 => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview recipe</a>', 'mobiconnector' ), date_i18n( __( 'M j, Y @ G:i', 'mobiconnector' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
            10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview video</a>', 'mobiconnector' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        );
        return $messages;
    }
    
    /**
	 * Register link API or field in REST API
	 */
    public function bamobile_register_api_hooks() {
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                    register_rest_field( $name,
                        'mobiconnector_galleries',
                        array(
                            'get_callback'    => array($this, 'bamobile_get_galleries'),
                            'update_callback' => null,
                            'schema'          => null,
                        )
                    );
                    register_rest_field( $name,
                        'mobiconnector_videos',
                        array(
                            'get_callback'    => array($this, 'bamobile_get_videos'),
                            'update_callback' => null,
                            'schema'          => null,
                        )
                    );
                }
            }
        }
    }

    /**
	 * Get all Mobi Gallery of post
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
    public function bamobile_get_galleries($object, $field_name, $request){    
        if(empty($object['id'])){
            return array();
        }
        $list_images = array();
        $post_type = $object['type'];
        if($post_type == 'mobi_gallery'){
            $post_id = $object['id'];
            $list_galleries = get_post_meta($post_id,'mobiconnector-list-galleries',true);
            if(!empty($list_galleries) && is_string($list_galleries)){
                $list_galleries = unserialize($list_galleries);
                if(!empty($list_galleries)){      
                    foreach($list_galleries as $gallery_id){
                        $list_images[] = wp_get_attachment_url($gallery_id);
                    }
                }
            }
        }
        return $list_images;
    }

    /**
	 * Get all Mobi Video of post
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
    public function bamobile_get_videos($object, $field_name, $request){
        if(empty($object['id'])){
            return array();
        }
        $post_type = $object['type'];
        if($post_type == 'mobi_video'){
            $post_id = $object['id'];
            $link = get_post_meta($post_id,'mobiconnector-link-video',true);
            $type = get_post_meta($post_id,'mobiconnector-option-video',true);
        }else{
            $link = '';
            $type = '';
        }
        return array(
            'type' => $type,
            'link' => $link
        );
    }

    /**
	 * Process when the save post
	 * 
	 * @param int $post_id id of post
	 * 
	 */
    public function bamobile_mobiconnector_save_post_customs($post_id){
        $post_type = sanitize_text_field(@$_POST['post_type']);
        if($post_type == 'mobi_gallery'){
            $mobiconnector_list_galleries = @$_POST['mobiconnector-list-galleries'];
            if(is_array($mobiconnector_list_galleries)){
                $mobiconnector_list_galleries = serialize($mobiconnector_list_galleries);            
                update_post_meta($post_id,'mobiconnector-list-galleries',$mobiconnector_list_galleries);
            }            
        }elseif($post_type == 'mobi_video'){
            $mobiconnector_select = sanitize_text_field(@$_POST['mobiconnector-video-select']);
            $linkvideo = sanitize_text_field(@$_POST['mobiconnector-link-video']);
            if($mobiconnector_select == 'upload'){
                $linkvideo = sanitize_text_field(@$_POST['mobiconnector-link-video-upload']);
            }
            update_post_meta($post_id,'mobiconnector-option-video',$mobiconnector_select);
            update_post_meta($post_id,'mobiconnector-link-video',$linkvideo);           
        }
    }

    /**
     * Create new post type
     */
    public static function bamobile_register_post_types() {
        // post type mobi_gallery
        $label = array(
            'name'  => __( 'Gallery','mobiconnector' ),
            'singular_name' => __( 'Gallery','mobiconnector' ),
            'menu_name' => __( 'Gallery','mobiconnector' ),
            'all_items' => __( 'Gallery','mobiconnector' ),
            'edit_item' => __( 'Edit Gallery','mobiconnector' ),
            'view_item' => __( 'View Gallery','mobiconnector' ),
            'update_item' => __( 'Update Gallery','mobiconnector' ),
            'add_new_item' => __( 'Add New Gallery','mobiconnector' ),
            'new_item_name' => __( 'New Gallery Name','mobiconnector' ),
            'search_items' => __( 'Search Galleries','mobiconnector' ),
            'parent_item'       => __( 'Parent Gallery', 'mobiconnector' ),
            'parent_item_colon' => __( 'Parent Gallery:', 'mobiconnector' ),
            'not_found'  => __( 'No galleries found.', 'mobiconnector' ),
            'choose_from_most_used'      => __( 'Choose from the most used galleries', 'mobiconnector' ),
            'separate_items_with_commas' => __( 'Separate galleries with commas', 'mobiconnector' ),
        );
        register_post_type( 'mobi_gallery',
            array(
                'labels' => $label, 
                'description' => 'List Gallery of mobile connector',
                'supports' => array(
                    'title',
                    'thumbnail',
                    'comments',
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_menu' => 'mobiconnector-settings',
                'hierarchical' => true,
                'taxonomies' => array( 'post_tag', 'category' ),
                'show_in_rest' => true,
                'rest_base' => 'galleries'
            )
        );
        // post type mobi_video
        $labelvideo = array(
            'name'  => __( 'Videos','mobiconnector' ),
            'singular_name' => __( 'Video','mobiconnector' ),
            'menu_name' => __( 'Videos','mobiconnector' ),
            'all_items' => __( 'Videos','mobiconnector' ),
            'edit_item' => __( 'Edit Video','mobiconnector' ),
            'view_item' => __( 'View Video','mobiconnector' ),
            'update_item' => __( 'Update Video','mobiconnector' ),
            'add_new_item' => __( 'Add New Video','mobiconnector' ),
            'new_item_name' => __( 'New Video Name','mobiconnector' ),
            'search_items' => __( 'Search Videos','mobiconnector' ),
            'parent_item'       => __( 'Parent Video', 'mobiconnector' ),
            'parent_item_colon' => __( 'Parent Video:', 'mobiconnector' ),
            'not_found'  => __( 'No videos found.', 'mobiconnector' ),
            'choose_from_most_used'      => __( 'Choose from the most used videos', 'mobiconnector' ),
            'separate_items_with_commas' => __( 'Separate valleries with commas', 'mobiconnector' ),
        );
        register_post_type( 'mobi_video',
            array(
                'labels' => $labelvideo, 
                'description' => 'List video of mobile connector',
                'supports' => array(
                    'title',                   
                    'thumbnail',
                    'comments',
                ),
                'public' => true,
                'has_archive' => true,
                'show_in_menu' => 'mobiconnector-settings',
                'hierarchical' => true,
                'taxonomies' => array( 'post_tag', 'category' ),
                'show_in_rest' => true,
                'rest_base' => 'videos'
            )
        );
    }   
}
$BAMobilePostType = new BAMobilePostType();
?>