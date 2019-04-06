<?php
/**
 * MobiConnector Meta Box
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Meta Box
 */
class BAMobile_Meta_Box{

    /**
     * MobiConnector Construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){
        add_action( 'add_meta_boxes', array($this,'bamobile_mobiconnector_add_meta_boxes_product' ));
		add_action( 'save_post', array( $this, 'bamobile_mobiconnector_save_post' ), 1, 2 );
		add_action( 'admin_enqueue_scripts',array(__CLASS__,'bamobile_mobiconnector_admin_style'));
	}
	
	/**
     * Add Style to admin
     * 
     * @param string $hook  The current admin page.
     */
    public static function bamobile_mobiconnector_admin_style($hook){       
        // Style admin    
        if(is_admin()){
            wp_register_style( 'mobiconnector-admin-style', plugins_url('assets/css/mobiconnector-admin.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'mobiconnector-admin-style' );			
        }	
    
        // Style metabox post notification in post
        if (is_admin() && $hook == 'post.php' || $hook == 'post-new.php') {
            wp_register_script('mobiconnector_settings_notifi_js', plugins_url('assets/js/mobiconnector-post-notification.js',MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);	
            $settings = array();	
            wp_localize_script( 'mobiconnector_settings_notifi_js', 'mobiconnector_settings_notifi_js_params',  $settings  );
            wp_enqueue_script( 'mobiconnector_settings_notifi_js' );
        }
    }    

    /**
     * Add metabox notification in post
     */
	public function bamobile_mobiconnector_add_meta_boxes_product(){
		add_meta_box( 'mobiconnector_product_fields', __('Mobile App Data','mobiconnector'), 'BAMobile_Meta_Box_Post::output', 'post', 'side', 'high' );				
    }
    
    /**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
    public function bamobile_mobiconnector_save_post($post_id, $post ){
        // $post_id and $post are required
		if ( empty( $post_id ) || empty( $post )) {
			return;
        }
		
		//Post type is post
		$post_type = $post->post_type;			
		if($post_type !== 'post'){
			return;
		}   
		
        // Dont' save meta boxes for revisions or autosaves
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
        }
        
        // Check the nonce
		if ( empty( $_POST['mobiconnector_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['mobiconnector_meta_box_nonce'], 'mobiconnector_save_data' ) ) {
			return;
        }
        
        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
        }
        
        // Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
        }
        
        global $wpdb;	
		
		//Push notification if check checkbox
		if(isset($_POST['mobiconnector_data-push-notification']) && isset($_POST['hidden_post_status']) && sanitize_text_field($_POST['hidden_post_status']) == 'publish'){
			$api = get_option('mobiconnector_settings-onesignal-api'); // Settings API
			$rest = get_option('mobiconnector_settings-onesignal-restkey'); //Settings REST KEY
			if(empty($api)){
				print("<div class='notti-settings-onsignal setting-error'>". __('Please input your api key!','mobiconnector') ."</div>");
				exit;				
			}
			elseif(empty($rest)){
				print("<div class='notti-settings-onsignal setting-error'>". __('Please input your rest api key!','mobiconnector') ."</div>");
				exit;			
			}
			$table_name = $wpdb->prefix . "mobiconnector_data_api";
			$api = esc_sql($api);
			$datas = $wpdb->get_results(
				"
				SELECT * 
				FROM $table_name
				WHERE api_key = '$api'
				"
			);
			//If API not empty in database
			if(!empty($datas)){
				foreach($datas as $data){
					$idmobiconnectorapi = $data->api_id;
				}	
				$title = sanitize_text_field(@$_POST['mobiconnector_data-push-notification-title']);
				$title = apply_filters( 'post_title',stripslashes(strip_tags($title)));
				$content = sanitize_text_field(@$_POST['mobiconnector_data-push-notification-content']);
				$content = apply_filters( 'post_title',stripslashes(strip_tags($content)));
				$notification = bamobile_sendMobiconnectorMessageOnPost($post_id,$title,$content); // Push notification
				$noti = json_decode($notification);					
				$errornoti = $noti->errors;	// If push notification error				
				if(!empty($errornoti)){
					$invalids = $errornoti->invalid_player_ids;				
					if(!empty($invalids)){
						foreach($invalids as $invalid){
							$iderrors[] = $invalid;
						}
						$iderror = implode(',',$iderrors);
						$iderror = trim($iderror,',');
						print("<div class='notti-settings-onsignal setting-error'>". sprintf(__("Invalid player ids %s", 'mobiconnector'),$iderror) ."</div>");
						exit;
					}else{
						print("<div class='notti-settings-onsignal setting-error'>".__('All included players are not subscribed','mobiconnector')."</div>");
						exit;				
					}				
				}			
				$notificationId = $noti->id;
				$notificationRecipients = $noti->recipients;						
				$return = bamobile_MobiconnectorgetNotificationById($notificationId); // Get notification by API onesignal		
				$failed = $return->failed;
				$remaining = $return->remaining;
				$successful = $return->successful;
				$total = ($failed + $remaining + $successful);
				$converted = $return->converted;
				$datenow = new DateTime();
				$date = $datenow->format('Y-m-d H:i:s');			
				$table_name = $wpdb->prefix . "mobiconnector_data_notification";	
				// Insert notification to database		
				$wpdb->insert(
					"$table_name",array(
						"notification_id" => $notificationId,
						"api_id" => $idmobiconnectorapi,
						"recipients" => $notificationRecipients,
						"failed" => $failed,
						"remaining" => $remaining,
						"converted" => $converted,  	
						"successful" => $successful,	
						"total" => $total,
						"create_date" => $date	
					),
					array( 
						'%s',
						'%d',	
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%s'	
					) 
				);
			}	
		}
    }
}
$BAMobile_Meta_Box = new BAMobile_Meta_Box();
?>