<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Insert Or Update Views
 * 
 * @since 1.1.5
 * 
 * @param int $post_id               id of post with count view
 * @param string $device             type count view | mobile or website
 * 
 * @return int count view
 */
function bamobile_mobiconnector_insert_or_update_views($post_id,$device){
    global $wpdb;
    $check = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "mobiconnector_views WHERE mc_id = $post_id",OBJECT);
    // If empty post id in database
    if(empty($check)){
        $count =  1;
        // If count view in mobile
        if($device == 'mobile'){
            // Insert post id in database
            $wpdb->insert( 
                $wpdb->prefix . "mobiconnector_views", 
                array( 
                    'mc_id' => $post_id, 
                    'mc_mobile' => $count,
                    'mc_website' => 0,
                    'mc_count' => $count
                ), 
                array( 
                    '%d',
                    '%d',
                    '%d', 
                    '%d' 
                ) 
            );
        }
        // If count view in website
        elseif($device == 'website'){
            // Insert post id in database
            $wpdb->insert( 
                $wpdb->prefix . "mobiconnector_views", 
                array( 
                    'mc_id' => $post_id, 
                    'mc_mobile' => 0,
                    'mc_website' => $count,
                    'mc_count' => $count
                ), 
                array( 
                    '%d',
                    '%d',
                    '%d', 
                    '%d' 
                ) 
            );
        }
    }
    // If not empty post id in database
    else{
        $mobile = 0;
        $website = 0;
        $counin = 0;
        foreach($check as $post){
            $mobile = $post->mc_mobile;
            $website = $post->mc_website;
            $counin = $post->mc_count;
        }
        $inputwebsite = $website+1;
        $inputmobile = $mobile+1;
        $inputcoun = $counin+1;
        // If count view in mobile
        if($device == 'mobile'){
            // Update count view in database
            $wpdb->update( 
                $wpdb->prefix . "mobiconnector_views", 
                array( 
                    'mc_mobile' => $inputmobile,
                    'mc_count' => $inputcoun
                ), 
                array( 'mc_id' => $post_id ), 
                array( 
                    '%d',
                    '%d' 
                ),
                array( '%d' )
            );
        }
        // If count view in website
        elseif($device == 'website'){
            // Update count view in database
            $wpdb->update( 
                $wpdb->prefix . "mobiconnector_views", 
                array( 
                    'mc_website' => $inputwebsite,
                    'mc_count' => $inputcoun
                ), 
                array( 'mc_id' => $post_id ), 
                array( 
                    '%d',
                    '%d' 
                ),
                array( '%d' )
            );
        }
    }
    $countout = $wpdb->get_var(
        $wpdb->prepare( "
            SELECT mc_count
            FROM " . $wpdb->prefix . "mobiconnector_views
            WHERE mc_id = %d ", absint( $post_id )
        )
    );
    // Return number count view
    return $countout;
}

/**
 * Get current user ip
 * 
 * @since 1.1.5
 * 
 * @return string current IP of user
 */
function bamobile_mobiconnector_get_the_user_ip() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && bamobile_mobiconnector_validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (bamobile_mobiconnector_validate_ip($ip))
                    return $ip;
                }
        } else {
            if (bamobile_mobiconnector_validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && bamobile_mobiconnector_validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && bamobile_mobiconnector_validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && bamobile_mobiconnector_validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && bamobile_mobiconnector_validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];

}
		
/**
 * Validate ip
 * 
 * @since 1.1.5
 * 
 * @param string $ip        ip want validate
 * 
 * @return boolean
 */
function bamobile_mobiconnector_validate_ip($ip) {
    if (strtolower($ip) === 'unknown')
        return false;

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647) return false;
        if ($ip >= 167772160 && $ip <= 184549375) return false;
        if ($ip >= 2130706432 && $ip <= 2147483647) return false;
        if ($ip >= 2851995648 && $ip <= 2852061183) return false;
        if ($ip >= 2886729728 && $ip <= 2887778303) return false;
        if ($ip >= 3221225984 && $ip <= 3221226239) return false;
        if ($ip >= 3232235520 && $ip <= 3232301055) return false;
        if ($ip >= 4294967040) return false;
    }
    return true;
}

/**
 * Clear all html tag
 * 
 * @since 1.1.5
 * 
 * @param string $string        String or text with clear
 * @param boolean $keep_image   Clear image or not
 * @param boolean $keep_link    Clear link or not
 * 
 * @return string not special char
 */
function bamobile_mobiconnector_get_plaintext( $string, $keep_image = false, $keep_link = false ){
	// Get image tags
	if( $keep_image ){
		if( preg_match_all( "/\<img[^\>]*src=\"([^\"]*)\"[^\>]*\>/is", $string, $match ) ){
			foreach( $match[0] as $key => $_m )	{
				$textimg = '';
				if( strpos( $match[1][$key], 'data:image/png;base64' ) === false ){
					$textimg = " " . $match[1][$key];
				}
				if( preg_match_all( "/\<img[^\>]*alt=\"([^\"]+)\"[^\>]*\>/is", $_m, $m_alt ) ){
					$textimg .= " " . $m_alt[1][0];
				}
				$string = str_replace( $_m, $textimg, $string );
			}
		}
	}

	// Get link tags
	if( $keep_link ){
		if( preg_match_all( "/\<a[^\>]*href=\"([^\"]+)\"[^\>]*\>(.*)\<\/a\>/isU", $string, $match ) ){
			foreach( $match[0] as $key => $_m )	{
				$string = str_replace( $_m, $match[1][$key] . " " . $match[2][$key], $string );
			}
		}
	}

	$string = str_replace( ' ', ' ', strip_tags( $string ) );
	return preg_replace( '/[ ]+/', ' ', $string );
}

/**
 * Convert language code to iso code
 * 
 * @since 1.1.5
 * 
 * @param string $languageCode   code of languages
 * 
 * @return string isoCode language
 */
function bamobile_mobiconnector_convert_languagesCode_to_isoCode($languageCode){
    $listConvert = array(
		'en' => array(
			'en_US',
			'en_AU',
			'en_CA',
			'en_GB',
			'is_IS',
			'haw_US',
		),
		'vn' => array(
			'vi'
		),
		'zh-Hant' => array(
			'zh_HK',
			'zh_TW'
		),
		'zh-Hans' => array(
			'zh_CN'
		),
		'nl' => array(
			'nl_NL',
			'nl_BE',
			'fy'
		),
		'ka' => array(
			'ka_GE'
		),
		'hi' => array(
			'hi_IN',
			'gu_IN',
			'ml_IN'
		),
		'it' => array(
			'it_IT'
		),
		'ja' => array(
			'ja'
		),
		'ko' => array(
			'ko_KR'
		),
		'lv' => array(
			'lv'
		),
		'lt' => array(
			'lt_LT'
		),
		'fa' => array(
			'fa_IR',
			'fa_AF',
			'haz'
		),
		'sr' => array(
			'sr_RS'
		),
		'th' => array(
			'th'
		),
		'ar' => array(
			'ar'
		),
		'hr' => array(
			'hr',
			'bs_BA'
		), 
		'et' => array(
			'et'
		), 
		'bg' => array(
			'bg_BG'
		), 
		'he' => array(
			'he_IL'
		), 
		'ms' => array(
			'ms_MY'
		),
		'pt' => array(
			'pt_BR',
			'pt_PT'
		), 
		'sk' => array(
			'sk_SK'
		), 
		'tr' => array(
			'tr_TR'
		), 
		'ca' => array(
			'ca',
			'bal'
		), 
		'cs' => array(
			'cs_CZ'
		), 
		'fi' => array(
			'fi'
		), 
		'de' => array(
			'de_DE',
			'de_CH'
		), 
		'hu' => array(
			'hu_HU'
		), 
		'nb' => array(
			'nb_NO'
		), 
		'ro' => array(
			'ro_RO'
		), 
		'es' => array(
			'es_AR',
			'es_CL',
			'es_CO',
			'es_MX',
			'es_PE',
			'es_PR',
			'es_ES',
			'es_VE',
			'gn',
			'gl_ES'
		), 
		'uk' => array(
			'uk'
		), 
		'da' => array(
			'da_DK'
		), 
		'fr' => array(
			'fr_BE',
			'fr_FR',
			'co'
		), 
		'el' => array(
			'el'
		), 
		'id' => array(
			'id_ID',
			'su_ID',
			'jv_ID'
		), 
		'pl' => array(
			'pl_PL'
		), 
		'ru' => array(
			'ru_RU',
			'ru_UA',
			'ky_KY'
		),
		'sv' => array(
			'sv_SE'
		)
	);
	foreach($listConvert as $iso => $languages){
		foreach($languages as $language){
			if($language == $languageCode){
				$isoCode = $iso;
			}
		}
	}
	if(empty($isoCode)){
		return 'en';
	}else{
		return $isoCode;
	}
}

/**
 * Get language enable of qTranstale
 * 
 * @since 1.1.5
 * 
 * @param boolean $reverse
 * 
 * @return array List of languages
 */
function bamobile_mobiconnector_get_qtranslate_enable_languages($reverse = false) {
	if(is_plugin_active('qtranslate-x/qtranslate.php')){
		global $q_config;
		$languages = $q_config['enabled_languages'];
		$clean_languages = array();
		if(!empty($languages)){
			ksort($languages);
			foreach($languages as $lang) {
				$clean_languages[] = $lang;
			}
			if($reverse) krsort($clean_languages);
		}
		return $clean_languages;
	}else{
		return array();
	}
}

/**
* Insert user social to database
* 
* @since 1.1.5
*
* @param array $user User add to database
* 
*/
function bamobile_mobiconnector_insert_user_social($user){
	global $wpdb;
	if(is_array($user)){
		$table = $wpdb->prefix.'mobiconnector_social_users';
		$wpdb->insert( 
			$table, 
			array( 
				'user_id'           => $user['user_id'],
				'user_email'        => $user['user_email'], 
				'user_picture'      => $user['user_picture'], 
				'user_firstname'    => $user['first_name'], 
				'user_lastname'     => $user['last_name'], 
				'user_url'          => $user['user_url'], 
				'user_social_id'    => $user['user_social_id'],
				'social'            => $user['social']
			), 
			array( 
				'%d',
				'%s', 
				'%s', 
				'%s', 
				'%s', 
				'%s', 
				'%s',
				'%s'
			) 
		);
	}
}

/**
* Insert user social to user database
*
* @since 1.1.5
* 
* @param array $user User add to database
* 
*/
function bamobile_mobiconnector_add_user_by_user_social($user){
	$user_id = wp_insert_user($user);
	bamobile_mobiconnector_new_user_social_notification($user_id,$user['user_pass'],null,'both');
	return $user_id;
}

/**
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 *
 *
 * @global wpdb         $wpdb      WordPress database object for queries.
 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
 *
 * @param int    $user_id    User ID.
 * @param string $password   Password auto general
 * @param null   $deprecated Not used (argument deprecated).
 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
 */
function bamobile_mobiconnector_new_user_social_notification( $user_id, $password, $deprecated = null, $notify = '' ) {
	if ( $deprecated !== null ) {
		_deprecated_argument( __FUNCTION__, '4.3.1' );
	}

	global $wpdb, $wp_hasher;
	$user = get_userdata( $user_id );
	
	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	// email to admin
	if ( 'user' !== $notify ) {
		$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";
		$message .= sprintf( __( 'Password: %s' ), $password ) . "\r\n";

		@mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );
	}

	// `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notifcation.
	if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
		return;
	}
	$message = sprintf(__('Sitename: %s'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
	$message .= sprintf(__('Password: %s' ), $password ) . "\r\n";
	// email to customer
	@mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), $message);
	return true;
}

/**
 * Get user social in database
 * 
 * @since 1.1.5
 * 
 * @param int $user_id id of user
 * 
 * @return Array
 * 
 */
function bamobile_mobiconnector_get_user_social($user_id){
    global $wpdb;
    $table = $wpdb->prefix.'mobiconnector_social_users';
    $datas = $wpdb->get_results('SELECT * FROM '.$table.' WHERE user_id = '.$user_id);
    if(!empty($datas)){
        foreach($datas as $data){
            return $data;
        }
    }else{
        return array();
    }
}

/**
 * Get user social in database
 * 
 * @since 1.1.5
 * 
 * @param array $user
 * 
 * @return array data of user
 * 
 */
function bamobile_mobiconnector_get_user_social_by_user($user){
    global $wpdb;
    $table = $wpdb->prefix.'mobiconnector_social_users';
    $datas = $wpdb->get_results('SELECT * FROM '.$table.' WHERE user_social_id = "'.$user['user_social_id'].'" AND social = "'.$user['social'].'"');
    if(!empty($datas)){
        foreach($datas as $data){
            return $data;
        }
    }else{
        return array();
    }
}

/**
 * Cleans up session data - cron callback.
 * 
 * @since 1.1.5
 *
 */
function bamobile_mobiconneector_cleanup_session_data() {
    $session       = new BAMobile_Session();
	if ( is_callable( array( $session, 'bamobile_cleanup_sessions' ) ) ) {
		$session->bamobile_cleanup_sessions();
	}
}
add_action( 'mobiconnector_cleanup_sessions', 'bamobile_mobiconneector_cleanup_session_data' );

/**
 * Check permissions of posts on REST API.
 * 
 * @since 1.1.5
 *
 * @param  WP_REST_Request $request Full details about the request.
 *
 * @return bool
 */
function bamobile_mobiconnector_rest_check_post_permissions( $request ) {
	$params = $request->get_params();
	$mobiconnector_key = isset($params['mobiconnector_key']) ? $params['mobiconnector_key'] : false;
	if(!empty($mobiconnector_key)){
		$keyindatabase = get_option('mobiconnector_api_key_database');
		if(!empty($keyindatabase)){
			if($keyindatabase == $mobiconnector_key){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}		
	}else{
		return false;
	}	
}

/**
 * Clear Mobile Cache
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_clear_mobile_cache(){
	global $wpdb;
	$table = $wpdb->prefix.'mobiconnector_sessions';
	$sql = "DELETE FROM $table WHERE session_key REGEXP ('[^0-9]+')";
	$wpdb->query($sql);
}

/**
 * Get List Languages of WPML if WPML active
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_get_wpml_list_languages(){
	$listlangs = array();
	if('woocommerce-multilingual/wpml-woocommerce.php'){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$prefix."icl_locale_map as local LEFT JOIN ".$prefix."icl_languages as lang ON local.code = lang.code";
		$listlangs = $wpdb->get_results($sql,ARRAY_A);
	}
	return $listlangs;
}

/**
 * Get name of Languages by current languages
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_get_name_wpml_list_languages($language = 'en',$display_language = 'en'){
	$listlangs = array();
	if('woocommerce-multilingual/wpml-woocommerce.php'){
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$prefix."icl_languages_translations WHERE language_code = '$language' AND display_language_code = '$display_language'";
		$listlangs = $wpdb->get_results($sql,ARRAY_A);
	}
	return $listlangs;
}

/**
 * Update player id 
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_update_playerid_with_api(){
	global $wpdb;
	$checkapi = get_option('mobiconnector_settings-onesignal-api');
	$checkrest = get_option('mobiconnector_settings-onesignal-restkey');
	$api = '';
	$rest = '';
	if(!empty($checkapi)){
		$api = $checkapi;
	}
	if(!empty($checkrest)){
		$rest = $checkrest;
	}	
	if(!empty($api) && !empty($rest)){
		$table_name = $wpdb->prefix . "mobiconnector_data_api";
		$checks = $wpdb->get_var(
			"
			SELECT COUNT(*) 
			FROM $table_name
			WHERE api_key = '$api'
			"
		);
		if($checks > 0){
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/players?app_id=" . $api); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 
													'Authorization: Basic '.$rest)); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$response = curl_exec($ch); 
			curl_close($ch); 
			$return = json_decode( $response);		
			if(!empty($return->players)){
				$lists = $return->players;
				foreach($lists as $list){
					$playerid = $list->id;
					$identifier = $list->invalid_identifier;
					$session_count = $list->session_count;
					$device_model = $list->device_model;
					$device_os = $list->device_os;
					$test_type = $list->test_type;
					if(empty($test_type)){
						$test_type = 0;
					}
					$device_type = $list->device_type;
					$language = $list->language;			
					$sdk = $list->sdk;
					$table_name = $wpdb->prefix . "mobiconnector_data_player";
					$checkuser = $wpdb->get_results(
						"
						SELECT * 
						FROM $table_name
						WHERE player_id = '$playerid' AND api_id = '$api'
						"
					);
					if(empty($checkuser)){
						$wpdb->insert(
							"$table_name",array(
								"api_id" => $api,
								"player_id" => $playerid,
								"identifier" => $identifier,
								"session_count" => $session_count,
								"device_model" => $device_model,
								"device_type" => $device_type,
								"device_os" => $device_os,
								"language" => $language,
								"sdk" => $sdk								
							),
							array( 
								'%s',
								'%s',	
								'%s',
								'%d',
								'%s',
								'%d',
								'%s',
								'%s',
								'%s'
							) 
						);
					}else{
						$wpdb->update(
						"$table_name",array(					
							"identifier" => $identifier,
							"session_count" => $session_count,	
							"device_model" => $device_model,	
							"device_os" => $device_os,
							"device_type" => $device_type,
							"language" => $language,
							"sdk" => $sdk	
						),
						array(
							'player_id' => $playerid
						),
						array( 					
							'%s',
							'%d',
							'%s',
							'%s',
							'%d',
							'%s',
							'%s'					
						) 
					);
					}
				}
			}
		}
	}
}

/**
 * Update player id with cron job
 */
function bamobile_mobiconnector_run_update_player_id_with_cron_job(){
	bamobile_mobiconnector_update_playerid_with_api();
}
add_action('mobiconnector_update_player_id','bamobile_mobiconnector_run_update_player_id_with_cron_job');

/**
 * Download file from Url
 * 
 * @since 1.1.5
 * 
 * @param string $url   link to file
 * 
 * @return bool 
 */
function bamobile_mobiconnector_download_images_from_url($url){
	$wp_upload_dir = wp_upload_dir();	
	$save_dir = $wp_upload_dir['path'];
	$url_dir = $wp_upload_dir['url'];
	$filename = basename($url);
	$save_file = $save_dir .'/'. $filename;	
	$guild_file = $url_dir .'/'. $filename;
	
	//Curl get file
	$ch = curl_init($url);	
	$fp = @fopen($save_file, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	@fclose($fp);

	// Update to databse
	$user_id = get_current_user_id();
	$filetype = wp_check_filetype( $filename, null );
	$args = array(
		'post_author' => $user_id,
        'post_content' => '',
        'post_title' => preg_replace( '/\.[^.]+$/', '', $filename ),
        'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => $filetype['type'],
        'comment_status' => 'open',
        'ping_status' => 'closed',
        'post_parent' => 0,
        'menu_order' => 0,
        'guid' => $guild_file,
	);
	$attach_id = wp_insert_attachment($args,$save_file);
	if(is_wp_error($attach_id)){
		return $attach_id;
	}
	$attach_data = wp_generate_attachment_metadata( $attach_id, $save_file );
	wp_update_attachment_metadata( $attach_id,  $attach_data );
	return $attach_id;
}

/**
 * Download file from direct Url
 * 
 * @since 1.1.5
 * 
 * @param string $url   direct link to file
 * 
 * @return bool 
 */
function bamobile_mobiconnector_download_images_from_direct_url($url){
	$wp_upload_dir = wp_upload_dir();	
	$save_dir = $wp_upload_dir['path'];
	$url_dir = $wp_upload_dir['url'];
	$filename = strtolower(md5(date('Y-m-d H:i:s')));
	$save_file = $save_dir .'/'. $filename.'.jpg';	
	$guild_file = $url_dir .'/'. $filename.'.jpg';
	
	//Curl get file
	$ch = curl_init($url);	
	$fp = @fopen($save_file, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	@fclose($fp);

	// Update to databse
	$user_id = get_current_user_id();
	$filetype = wp_check_filetype( $filename, null );
	$args = array(
		'post_author' => $user_id,
        'post_content' => '',
        'post_title' => $filename,
        'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image/jpeg',
        'comment_status' => 'open',
        'ping_status' => 'closed',
        'post_parent' => 0,
        'menu_order' => 0,
        'guid' => $guild_file,
	);
	$attach_id = wp_insert_attachment($args,$save_file);
	if(is_wp_error($attach_id)){
		return $attach_id;
	}
	$attach_data = wp_generate_attachment_metadata( $attach_id, $save_file );
	wp_update_attachment_metadata( $attach_id,  $attach_data );
	return $attach_id;
}

/**
 * Change data from user to data application
 */
function bamobile_filtered_user_to_application($user_id = 0){
	$form = get_option('ba_design_form');
	if(!empty($form) && is_string($form)){
		$forms = unserialize($form);
	}
	$id_user = (int)get_current_user_id();
	$ud_i = ($id_user > 0) ? $id_user : $user_id;
	$user = get_user_by('id',$ud_i);
	$data = array();
	$data_out = array();
	if(is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php'))){
		if(!empty($user)){
			$user_data = (array) $user->data;
			if(!empty($forms)){
				foreach($forms as $field){
					$id_fields = (strpos($field['name_id'],'billing_') !== false) ? substr($field['name_id'],strpos($field['name_id'],'billing_')+8)  : $field['name_id'];
					$key = 'user_'.$id_fields;
					if(!empty($user_data[$key])){
						$data[$id_fields] = $user_data[$key];
					}elseif(!empty($user_data[$id_fields])){
						$data[$id_fields] = $user_data[$id_fields];
					}else{
						continue;
					}
				}
			}		
			$user_out_data = bamobile_array_keys_recursive($data,array_keys($user_data),$user_data);
			$data_out = array_merge($user_out_data,$data);
		}
	}else{
		if(!empty($user)){
			$data_out = $user->data;
			$data_out = (array)$data_out;
		}		
	}
	return $data_out;
}

/**
 * Array key recursive
 */
function bamobile_array_keys_recursive($arrays,$keys,$values){
	$arrays_out = array();
	if(!empty($arrays) && !empty($keys)){
		foreach($keys as $key){
			if(empty($arrays[$key])){
				$arrays_out[$key] = $values[$key];
			}
		}
	}
	return $arrays_out;
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Insert Or Update Views
 * 
 * @param int $post_id               id of post with count view
 * @param string $device             type count view | mobile or website
 * 
 * @return int count view
 */
function mobiconnector_insert_or_update_views($post_id,$device){
	return bamobile_mobiconnector_insert_or_update_views($post_id,$device);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get current user ip
 * 
 * @return string current IP of user
 */
function mobiconnector_get_the_user_ip() {
	return bamobile_mobiconnector_get_the_user_ip();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Validate ip
 * 
 * @param string $ip        ip want validate
 * 
 * @return boolean
 */
function mobiconnector_validate_ip($ip) {
	return bamobile_mobiconnector_validate_ip($ip);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Clear all html tag
 * 
 * @param string $string        String or text with clear
 * @param boolean $keep_image   Clear image or not
 * @param boolean $keep_link    Clear link or not
 * 
 * @return string not special char
 */
function mobiconnector_get_plaintext( $string, $keep_image = false, $keep_link = false ){
	return bamobile_mobiconnector_get_plaintext($string,$keep_image,$keep_link);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Convert language code to iso code
 * 
 * @param string $languageCode   code of languages
 * 
 * @return string isoCode language
 */
function mobiconnector_convert_languagesCode_to_isoCode($languageCode){
	return bamobile_mobiconnector_convert_languagesCode_to_isoCode($languageCode);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get language enable of qTranstale
 * 
 * @param boolean $reverse
 * 
 * @return array List of languages
 */
function mobiconnector_get_qtranslate_enable_languages($reverse = false) {
	return bamobile_mobiconnector_get_qtranslate_enable_languages($reverse);
}

/**
* This function will be removed after Jan 01, 2019
*
* Insert user social to database
* 
* @param array $user User add to database
* 
*/
function mobiconnector_insert_user_social($user){
	return bamobile_mobiconnector_insert_user_social($user);
}

/**
* This function will be removed after Jan 01, 2019
* 
* Insert user social to user database
* 
* @param array $user User add to database
* 
*/
function mobiconnector_add_user_by_user_social($user){
	return bamobile_mobiconnector_add_user_by_user_social($user);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Email login credentials to a newly-registered user.
 *
 * A new user registration notification is also sent to admin email.
 *
 *
 * @global wpdb         $wpdb      WordPress database object for queries.
 * @global PasswordHash $wp_hasher Portable PHP password hashing framework instance.
 *
 * @param int    $user_id    User ID.
 * @param string $password   Password auto general
 * @param null   $deprecated Not used (argument deprecated).
 * @param string $notify     Optional. Type of notification that should happen. Accepts 'admin' or an empty
 *                           string (admin only), 'user', or 'both' (admin and user). Default empty.
 */
function mobiconnector_new_user_social_notification( $user_id, $password, $deprecated = null, $notify = '' ) {
	return bamobile_mobiconnector_new_user_social_notification($user_id,$password,$deprecated,$notify);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get user social in database
 * 
 * @param int $user_id id of user
 * 
 * @return Array
 * 
 */
function mobiconnector_get_user_social($user_id){
	return bamobile_mobiconnector_get_user_social($user_id);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get user social in database
 * 
 * @param array $user
 * 
 * @return array data of user
 * 
 */
function mobiconnector_get_user_social_by_user($user){
	return bamobile_mobiconnector_get_user_social_by_user($user);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Check permissions of posts on REST API.
 *
 * @param  WP_REST_Request $request Full details about the request.
 *
 * @return bool
 */
function mobiconnector_rest_check_post_permissions( $request ) {
	return bamobile_mobiconnector_rest_check_post_permissions($request);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Clear Mobile Cache
 */
function mobiconnector_clear_mobile_cache(){
	return bamobile_mobiconnector_clear_mobile_cache();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get List Languages of WPML if WPML active
 */
function mobiconnector_get_wpml_list_languages(){
	return bamobile_mobiconnector_get_wpml_list_languages();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get name of Languages by current languages
 */
function mobiconnector_get_name_wpml_list_languages($language = 'en',$display_language = 'en'){
	return bamobile_mobiconnector_get_name_wpml_list_languages($language,$display_language);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Update player id 
 */
function mobiconnector_update_playerid_with_api(){
	return bamobile_mobiconnector_update_playerid_with_api();
}
?>