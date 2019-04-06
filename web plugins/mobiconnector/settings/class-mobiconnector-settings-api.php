<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create API with BAMobile Settings
 */
class BAMobileSettingsApi{

    /**
     * Url of API
     */
    private $rest_url = 'mobiconnector/settings';	
    
    /**
     * BAMobileSettingsApi construct
     */
	public function __construct(){
		$this->register_hooks();		
    }	
    
    /**
	 * Hook into actions and filters.
	 */
	public function register_hooks(){
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
    }
    
    /**
	 * Create Api or add field
	 */
	public function register_api_hooks() {		
        register_rest_route( $this->rest_url, '/usersociallogin', array(
                'methods'         => 'POST',
                'callback'        => array( $this, 'bamobile_mobiconnector_check_and_create_social_user_by_email' ),	
                'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),	
                'args'            => array(		
                    'user_email' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'user_social_id' => array(
                        'required' => true,
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'social' => array(
                        'required' => true,
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'first_name' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'last_name' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'display_name' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'user_url' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'user_picture' => array(
                        'sanitize_callback' => 'esc_sql'
                    ),
                    'player_id' => array(
                        'sanitize_callback' => 'esc_sql'
                    )
                ),					
            ) 
        );

        register_rest_route( $this->rest_url, '/updateplayerid', array(
                'methods'         => 'POST',
                'callback'        => array( $this, 'bamobile_updateplayerid' ),	
                'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),	
                'args'            => array(		
                    'player_id' => array(
                        'sanitize_callback' => 'esc_sql'
                    )
                ),					
            ) 
        );

        register_rest_route( $this->rest_url, '/gettextstatic', array(
                'methods'         => 'GET',
                'callback'        => array( $this, 'bamobile_getstatictext' ),	
                'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),	
                'args'            => array(		
                ),					
            ) 
        );

        register_rest_route($this->rest_url,'/getfirstloadapp', array(
                'methods'         => 'GET',
                'callback'        => array( $this, 'bamobile_getfirstloadapp' ),	
                'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),	
                'args'            => array(		
                ),			
            )
        );
    }

    /**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function bamobile_get_items_permissions_check( $request ) {
		$usekey = get_option('mobiconnector_settings-use-security-key');
		if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
			return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

    /**
	 * Get all Settings with first load app
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return array settings first load app
	 */
    public function bamobile_getfirstloadapp($request){   
        // require function of tranlation  
        require_once( MOBICONNECTOR_ADMIN_PATH . 'includes/translation-install.php' ); 

        //get settings in wordpress
        $gganalytics = get_option('mobiconnector_settings-google-analytics');      
        $dateformat = get_option('mobiconnector_settings-date-format');       
        $applicationLanguage = get_option('mobiconnector_settings-show-languages');        
        $displaymode = get_option('mobiconnector_settings-display-mode');       
        $bannerandoid = get_option('mobiconnector_settings-banner-aa');     
        $interstitialandroid = get_option('mobiconnector_settings-interstitial-aa');      
        $bannerios = get_option('mobiconnector_settings-banner-ia');       
        $interstitialios = get_option('mobiconnector_settings-interstitial-ia');
        $main = get_option('mobiconnector_settings-maintainmode');
        $maintain = false;
        if($main == 1){
            $maintain = true;
        }
        //get list languages of wordpress
        $listlangauges = wp_get_available_translations();
        $lang = array();

        // if settings languages is empty
        if($applicationLanguage == ''){
            $lang = array(
                'language' => 'en',
                'name'     => 'English'
            );
        }else{
            foreach($listlangauges as $la => $value){
                // if settings language coincides with one languages in list languages of wordpress
                if($applicationLanguage == $la){
                    $keylang = $value['language'];
                    if(strpos($keylang,'_') !== false ){
                        $keylang = substr($keylang,0,strpos($keylang,'_'));
                        $keylang = trim($keylang);
                    }
                    $langname = $value['native_name'];
                    if(strpos($langname,'(') !== false){
                        $langname = substr($langname,0,strpos($langname,'('));
                        $langname = trim($langname);
                    }
                    $lang = array(
                        'language' => $keylang,
                        'name'     => $langname
                    );
                }
            }
        }
        $lang = (array)$lang;
        $url_more = 0;
        $qlanguages = array();
        $languages = array();
        $default_languages = array();    
        $hide_default = null;    
        if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
            $listlangs = bamobile_mobiconnector_get_wpml_list_languages();
            $languagesdisplaymode = get_option('mobiconnector_settings-languages-wpml-display-mode');
            $defaultlanguages = array();
            $defaultlan = get_option('icl_sitepress_settings');
            $defaultlang = $defaultlan['default_language'];
            $current_language = sanitize_text_field(@$_COOKIE['_icl_current_language']);
            foreach($listlangs as $wplang){
                $selectwp = '';
                $selectwpdefault = '';
                if(!empty($languagesdisplaymode)){
                    if(isset($languagesdisplaymode[$wplang['code']])){
                        $selectwp = $languagesdisplaymode[$wplang['code']];
                    }                
                }
                if(empty($selectwp) || $selectwp == ''){
                    $selectwp = 'ltr';
                }
                $nameofcurrentlang = bamobile_mobiconnector_get_name_wpml_list_languages($wplang['code'],$current_language);
                $languages[] = array(
                    'language' => $wplang['code'],
                    'name'     => $nameofcurrentlang[0]['name'],
                    'display_mode' => $selectwp
                );
                if($wplang['code'] == $defaultlang){
                    $defaultlanguages = array(
                        'code' => $wplang['code'],
                        'name' => $nameofcurrentlang[0]['name']
                    );
                }
            }
            if(isset($languagesdisplaymode[$defaultlang])){
                $selectwpdefault = $languagesdisplaymode[$defaultlang];
            }
            if(empty($selectwpdefault) || $selectwpdefault == ''){
                $selectwpdefault = 'ltr';
            }
            if(!empty($defaultlanguages['code']) && $defaultlanguages['code'] == $defaultlang){
                $default_languages = array(
                    'language' => $defaultlang,
                    'name'     => $defaultlanguages['name'],
                    'display_mode' => $selectwpdefault
                );
            }
        }elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
            $languagesdisplaymode = get_option('mobiconnector_settings-languages-display-mode');
            global $q_config;
            $urlinfo = $q_config['url_mode'];
            $url_more = $urlinfo;
            $langq = bamobile_mobiconnector_get_qtranslate_enable_languages();    
            if(empty($langq)){
                $qlanguages = array();
            }
            $listlanguages = qtranxf_default_language_name();
            foreach($langq as $la){
                $select = '';
                if(!empty($languagesdisplaymode)){
                    if(isset($languagesdisplaymode[$la])){
                        $select = $languagesdisplaymode[$la];
                    }                
                }
                if(empty($select) || $select == ''){
                    $select = 'ltr';
                }
                $qlanguages[] = array(
                    'language' => $la,
                    'name'     => $listlanguages[$la],
                    'display_mode' => $select
                );
            }
            $defaultqlan = get_option('qtranslate_default_language');
            if(!empty($languagesdisplaymode)){
                if(isset($languagesdisplaymode[$defaultqlan])){
                    $selectdefaultq = $languagesdisplaymode[$defaultqlan];
                }
            }
            if(empty($selectdefaultq) || $selectdefaultq == ''){
                $selectdefaultq = 'ltr';
            }
            if(!empty($listlanguages[$defaultqlan])){
                $default_languages = array(
                    'language' => $defaultqlan,
                    'name'     => $listlanguages[$defaultqlan],
                    'display_mode' => $selectdefaultq
                );
            }
            $hide_default = $q_config['hide_default_language'];
        }
        $google_api = get_option('mobiconnector_settings-google-api-key');
        $returnsocials = get_option('mobiconnector_settings-socials-login');
        $list = array(
            'google_analytics' => $gganalytics,
            'date_format'      => $dateformat,
            'application_language' => isset($lang['language']) ? $lang['language'] : 'en',
            'application_language_name' => isset($lang['name']) ? $lang['name'] : 'English',
            'dir' => $displaymode,
            'admob_android_banner' => $bannerandoid,
            'admob_android_interstitial' => $interstitialandroid,
            'admob_ios_banner' => $bannerios,
            'admob_ios_interstitial' => $interstitialios,
            'maintain' => $maintain,
            'url_mode' => $url_more,
            'google_api_key' => $google_api,
            'socials_login' => $returnsocials
        );
        if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
            $list['qtranslate_languages'] = $languages;
            $list['default_languages'] = $default_languages; 
        }elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
            $list['qtranslate_languages'] = $qlanguages;
            $list['default_languages'] = $default_languages;
            $list['hide_default'] = $hide_default;
        }   
        $checkversion = get_option('wooconnector_database_version');
        if((is_plugin_active('wooconnector/wooconnector.php') || bamobile_is_extension_active('wooconnector/wooconnector.php')) && $checkversion >= '1.0.19'){
            $design = get_option('wooconnector_settings-design');
            $list['wooconnector_design'] = unserialize($design);
        }     
        return $list;
    }
    
    /**
	 * Get text static of mobiconnector
	 * 
	 * @param WP_REST_Request $request  current Request
	 * 
	 * @return array text static
	 */
    public function bamobile_getstatictext($request){
        $core = get_option('mobiconnector_settings-text-core');
        $core = unserialize($core);
        $checkallowRegister = get_option('users_can_register');
        $check = false;
        $list['text_static'] = $core;
        if($checkallowRegister){
            $check = true;
        }
        $list['register'] = $check;        
        return $list;
    }

     /**
     * Create token by username and password
     * 
     * @param WP_REST_Request  $request   current Request
     * 
     * @return array data of Token
     */
    public function bamobile_generate_token($userId) {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        /** First thing, check the secret key if not exist return a error*/
        if (!$secret_key) {
            return new WP_Error(
                'mobiconnector_jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'wp-api-jwt-auth'),
                array(
                    'status' => 403,
                )
            );
        }
        /** Try to authenticate the user with the passed credentials*/
        $user = get_user_by('id',$userId);       
        if(empty($user)){
            return new WP_Error(
                'mobiconnector_jwt_auth',
                '',
                array(
                    'status' => 403,
                )
            );
        }
        /** If the authentication fails return a error*/
        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();
            return new WP_Error(
                'mobiconnector_jwt_auth '.$error_code,
                $user->get_error_message($error_code),
                array(
                    'status' => 403,
                )
            );
        }

        /** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters('mobiconnector_jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('mobiconnector_jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $user->data->ID,
                ),
            ),
        );

        /** Let the user modify the token data before the sign. */
        $token = BAMobile_JWT::encode(apply_filters('mobiconnector_jwt_auth_token_before_sign', $token, $user), $secret_key);

        /** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
            'token' => $token,
            'user_email' => $user->data->user_email,
            'user_nicename' => $user->data->user_nicename,
            'user_display_name' => $user->data->display_name,
        );

        /** Let the user modify the data before send it back */
        return apply_filters('mobiconnector_jwt_auth_token_before_dispatch', $data, $user);
    }
    
    /**
     * Check Login by Social in App
     */
    public function bamobile_mobiconnector_check_and_create_social_user_by_email($request){
        global $wpdb;
        $params = $request->get_params();
        $email = isset($params['user_email']) ? $params['user_email'] : false;
        $user_social_id = $params['user_social_id'];
        $firstname = isset($params['first_name']) ? $params['first_name'] : '';
        $lastname = isset($params['last_name']) ? $params['last_name'] : '';
        $display_name = isset($params['display_name']) ? $params['display_name'] : false;
        $user_url = isset($params['user_url']) ? $params['user_url'] : '';
        $images = isset($params['user_picture']) ? $params['user_picture'] : ''; 
        $social = $params['social'];
        $player_id = isset($params['player_id']) ? $params['player_id'] : false;
        $blocked = $wpdb->get_row("SELECT blocked FROM ". $wpdb->prefix . "mobiconnector_manage_device WHERE player_id = '".$player_id."'",ARRAY_N);
        if($blocked[0] !== '0'){
            return false;
        }
        $checkplayer = count($blocked);
        if(!empty($email)){
            if(email_exists($email) !== false && $checkplayer > 0){
                $user = get_user_by('email',$email);
                $user = (array) $user->data;
                $userId = $user['ID'];
                $usermeta = $this->bamobile_get_details_user_by_id( $userId );
                return $usermeta;
            }elseif(username_exists($email) !== false && $checkplayer > 0){
                $user = get_user_by('login',$email);
                $user = (array) $user->data;
                $userId = $user['ID'];
                $usermeta = $this->bamobile_get_details_user_by_id( $userId );
                return $usermeta;
            }else{
                if($checkplayer > 0){
                    $image_id = 0;
                    if($social == 'facebook' && (!empty($images) || $images !== '')){
                        $image_id = bamobile_mobiconnector_download_images_from_direct_url($images);
                    }elseif(!empty($images) || $images !== ''){
                        $image_id = bamobile_mobiconnector_download_images_from_url($images);
                    }
                    $password = wp_generate_password();
                    $user_id = wp_create_user($email,$password,$email);
                    if(is_wp_error($user_id)){
                        return $user_id;
                    }
                    $userSocial = array(
                        'user_id'         => $user_id,
                        'user_social_id'  => $user_social_id,
                        'user_login'      => $email,
                        'first_name' 	  => $firstname,
                        'last_name' 	  => $lastname,
                        'user_url'        => $user_url,
                        'user_pass'       => $password,
                        'user_picture'    => $images,
                        'user_email'      => $email,
                        'social'          => $social
                    );
                    bamobile_mobiconnector_insert_user_social($userSocial);
                    $insertuser = array(
                        'ID'              => $user_id,
                        'first_name' 	  => $firstname,
                        'last_name' 	  => $lastname,
                        'display_name'    => (!empty($display_name)) ? $display_name : trim($firstname.' '.$lastname)
                    );
                    $user_id = wp_update_user( $insertuser );     
                    update_user_meta($user_id,$this->bamobile_getMobiconnectorMetaKey(),$image_id);               
                    bamobile_mobiconnector_new_user_social_notification($user_id,$password,null,'both');
                    $usermeta = $this->bamobile_get_details_user_by_id( $user_id );
                    return $usermeta;
                }else{
                    return false;
                }
            }
        }elseif(!empty($user_social_id)){
            if(username_exists($user_social_id) !== false && $checkplayer > 0){
                $user = get_user_by('login',$user_social_id);
                $user = (array) $user->data;
                $userId = $user['ID'];
                $usermeta = $this->bamobile_get_details_user_by_id( $userId );
                return $usermeta;
            }else{
                if($checkplayer > 0){
                    $image_id = 0;
                    if($social == 'facebook' && (!empty($images) || $images !== '')){
                        $image_id = bamobile_mobiconnector_download_images_from_direct_url($images);
                    }elseif(!empty($images) || $images !== ''){
                        $image_id = bamobile_mobiconnector_download_images_from_url($images);
                    }
                    $password = wp_generate_password();
                    $user_id = wp_create_user($user_social_id,$password);
                    if(is_wp_error($user_id)){
                        return $user_id;
                    }
                    $userSocial = array(
                        'user_id'         => $user_id,
                        'user_social_id'  => $user_social_id,
                        'user_login'      => $user_social_id,
                        'first_name' 	  => $firstname,
                        'last_name' 	  => $lastname,
                        'user_url'        => $user_url,
                        'user_pass'       => $password,
                        'user_picture'    => $images,
                        'user_email'      => '',
                        'social'          => $social
                    );
                    bamobile_mobiconnector_insert_user_social($userSocial);
                    $insertuser = array(
                        'ID'              => $user_id,
                        'first_name' 	  => $firstname,
                        'last_name' 	  => $lastname,
                        'display_name'    => (!empty($display_name)) ? $display_name : trim($firstname.' '.$lastname)
                    );
                    $user_id = wp_update_user( $insertuser );
                    update_user_meta($user_id,$this->bamobile_getMobiconnectorMetaKey(),$image_id);     
                    bamobile_mobiconnector_new_user_social_notification($user_id,$password,null,'both');
                    $usermeta = $this->bamobile_get_details_user_by_id( $user_id );
                    return $usermeta;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * Update player id in app
     */
    public function bamobile_updateplayerid($request){
        global $wpdb;
        $params = $request->get_params();
        $player_id = isset($params['player_id']) ? $params['player_id'] : false;
        if(empty($player_id)){
            return new WP_Error('rest_player_id_error',__('Player ID is Require','mobiconnector'),array('status' => 400 ));
        }
        $currentIP = bamobile_mobiconnector_get_the_user_ip();
        $nowtime = gmdate('Y-m-d H:i:s');
        $table = $wpdb->prefix. 'mobiconnector_manage_device';
        $checkexist = $wpdb->get_var("SELECT COUNT(*) FROM ". $table . " WHERE player_id = '".$player_id."'");
        if($checkexist > 0){
            $check = $wpdb->update(
                $table,
                array(
                    'player_ip'     => $currentIP,
                    'date_update'   => $nowtime
                ),
                array('player_id' => $player_id),
                array(
                    '%s',
                    '%s'
                ),
                array('%s')
            );
            if($check === false){
                return false;
            }
            return true;
        }else{
            $wpdb->insert(
                $table,
                array(
                    'player_id'     => $player_id,
                    'player_ip'     => $currentIP,
                    'date_create'   => $nowtime,
                    'date_update'   => $nowtime
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );
            $checkinsert = $wpdb->insert_id;
            if($checkinsert !== false){
                return true;
            }
            return false;
        }
    }

    /**
     * Get detail user with user id
     * 
     * @param $userId    id of user
     */
    public function bamobile_get_details_user_by_id($userId){
        $usermeta = get_user_meta( $userId );
        $user = get_user_by('id',$userId);
        $user = (array) $user->data;
        $token = $this->bamobile_generate_token($userId);
        $usersocial = bamobile_mobiconnector_get_user_social($userId);            
        $user_meta = array_map( function( $a ){ return $a[0]; }, $usermeta );
        $user_meta['view_private'] = user_can($userId,'read_private_posts');
        $user_meta1 = array();
        $user_meta2 = array();
        $user_meta3 = array();
        if(!empty($user)){
            $user_meta1 = array_merge($user_meta, $user);
        }
        if(!empty($user_meta1) && !empty($usersocial)){
            $user_meta2 = array_merge($user_meta1,(array)$usersocial);
        }else{
            $user_meta2 = $user_meta1;
        }
        if(!empty($user_meta2) && !empty($token)){
            $user_meta3 = array_merge($user_meta2,(array)$token);
        }else{
            $user_meta3 = $user_meta2;
        }      
        $user_info = bamobile_filtered_user_to_application($userId); 
        $user_info = (array) $user_info;
        if(!empty($user_meta3[$this->bamobile_getMobiconnectorMetaKey()])){
			$attachmentmobi = wp_get_attachment_url( (int) $user_meta3[$this->bamobile_getMobiconnectorMetaKey()]);
			if(!empty($attachmentmobi)){
                $user_meta3['mobiconnector_avatar'] = $attachmentmobi;
				$user_info['mobiconnector_avatar'] = $attachmentmobi;
			}
        }
        if(isset($user_meta3['mobiconnector_address']) && is_string($user_meta3['mobiconnector_address'])){
			$user_meta3['mobiconnector_address'] = unserialize($user_meta3['mobiconnector_address']);
		}else{
			$user_meta3['mobiconnector_address'] = array();
		}
		if(isset($user_meta3['field_extra_user']) && is_string($user_meta3['field_extra_user'])){
			$user_meta3['field_extra_user'] = unserialize($user_meta3['field_extra_user']);
		}else{
			$user_meta3['field_extra_user'] = array();
		}
        $user_meta3['mobiconnector_info'] = $user_info;
        $user_meta3['mobiconnector_info']['first_name'] = isset($user_meta3['first_name']) ? $user_meta3['first_name'] : $user_meta3['user_login'];
        $user_meta3['mobiconnector_info']['last_name'] = isset($user_meta3['last_name']) ? $user_meta3['last_name'] : $user_meta3['user_login'];
        $user_meta3['mobiconnector_info']['description'] = isset($user_meta3['description']) ? $user_meta3['description'] : '';
		if(isset($user_meta3['password'])){
			unset($user_meta3['password']);
		}
        return $user_meta3;
    }

    /**
	 * Get meta ket of mobiconnector avatar
	 */
	public function bamobile_getMobiconnectorMetaKey(){
		return "mobiconnector-avatar";
	}
}
$BAMobileSettingsApi = new BAMobileSettingsApi();
?>