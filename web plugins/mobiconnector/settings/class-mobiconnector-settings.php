<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.	
}
/**
 * Process Seettings BAMobile Settings
 */
class BAMobileSettings
{

    /**
     * MobiConnectorSettings construct
     */
    public function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Hook into actions and filters.
     */
    public function init_hooks()
    {
        add_action('admin_menu', array($this, 'bamobile_create_menu_to_wordpress'));
        add_action('admin_enqueue_scripts', array($this, 'bamobile_admin_style'));
        add_action('wp_ajax_mobiconnector_show_url', array($this, 'bamobile_mobiconnector_process_show_url_function'));
        self::bamobile_mobiconnector_clear_config();
    }

    /**
     * Process Ajax get Url
     */
    public function bamobile_mobiconnector_process_show_url_function()
    {
        check_ajax_referer('mobiconnector-onesignal-ajax', 'security');
        $value = sanitize_text_field($_POST['mobiconnectorvalue']);
        $selected = sanitize_text_field($_POST['mobiconnectorselect']);
        $html = '';
        global $wpdb;
        if ($selected == 'url-post') {
            $table_name = $wpdb->prefix . "posts";
            $datas = $wpdb->get_results(
                "
				SELECT * 
				FROM $table_name
				WHERE post_title LIKE '%$value%' AND post_type = 'post' AND post_status = 'publish'
				"
            );
            if (!empty($datas)) {
                foreach ($datas as $data) {
                    $title = apply_filters('post_title', $data->post_title);
                    $link = get_permalink($data->ID);

                    $html .= '<div class="ajax-url-notification">				
                        <div class="content-url-notification">
                            <h4 class="ajax-title-url-notification" >' . esc_html($title) . '(#' . esc_html($data->ID) . ')</h4>					
                            <input type="hidden" class="ajax-link-hidden-url" value="' . esc_html($link) . '"/>
                        </div>
                    </div>';
                }

            } else {
                $html = '<div id="comtent-list-url-notification">		
                    <div id="content-url-notification">
                        <span>' . __('Sorry ' . esc_html($value) . ' not exist, Please try again...', 'mobiconnector') . '</span>
                    </div>
                </div>';
            }
        } elseif ($selected == 'url-category') {
            $table_name = $wpdb->prefix . "terms";
            $inner_name = $wpdb->prefix . "term_taxonomy";
            $datas = $wpdb->get_results(
                "
				SELECT $table_name.term_id AS ID,name,slug,description  
				FROM $table_name INNER JOIN $inner_name ON $table_name.term_id = $inner_name.term_id
				WHERE name LIKE '%$value%' AND taxonomy = 'category'
				"
            );
            if (!empty($datas)) {
                foreach ($datas as $data) {
                    $title = apply_filters('post_title', $data->name);
                    $link = get_term_link((int)$data->ID, 'category');
                    $html .= '<div class="ajax-url-notification">				
                        <div class="content-url-notification">
                            <h4 class="ajax-title-url-notification" >' . esc_html($title) . ' (#' . esc_html($data->ID) . ')</h4>					
                            <input type="hidden" class="ajax-link-hidden-url" value="' . esc_html($link) . '"/>
                        </div>
                    </div>';
                }
            } else {
                $html = '<div id="comtent-list-url-notification">		
                    <div id="content-url-notification">
                        <span>' . __('Sorry ' . esc_html($value) . ' not exist, Please try again...', 'mobiconnector') . '</span>
                    </div>
                </div>';
            }
        } else {
            wp_die();
        }
        echo $html;
        wp_die();
    }

    /**
     * Create menu in admin wordpress
     */
    public function bamobile_create_menu_to_wordpress()
    {
        add_menu_page(
            __('Mobile Connector', 'mobiconnector'),
            __('Mobile Connector', 'mobiconnector'),
            'manage_options',
            'mobiconnector-settings',
            '',
            '',
            25
        );
        add_submenu_page(
            'mobiconnector-settings',
            __('Manage', 'mobiconnector'),
            __('Manage', 'mobiconnector'),
            'manage_options',
            'mobiconnector-settings',
            array($this, 'bamobile_action_create_menu')
        );
        if (is_plugin_active('wooconnector/wooconnector.php') || bamobile_is_extension_active('wooconnector/wooconnector.php')) {
            add_submenu_page(
                'mobiconnector-settings',
                __('Apps', 'mobiconnector'),
                __('Apps', 'mobiconnector'),
                'manage_options',
                'mobiconnector-application',
                array($this, 'bamobile_redirect_to_application')
            );
        }
    }

    /**
     * Redirect to application
     */
    public function bamobile_redirect_to_application()
    {
        wp_redirect(admin_url() . 'admin.php?page=wooconnector');
    }

    /**
     * Action of menu Mobile in admin wordpress
     */
    public function bamobile_action_create_menu()
    {
        $tab = isset($_REQUEST['mtab']) ? $_REQUEST['mtab'] : 'settings'; // tab of settings
        $task = isset($_REQUEST['mtask']) ? $_REQUEST['mtask'] : ''; // action of settings
        $action = isset($_REQUEST['actions']) ? $_REQUEST['actions'] : 'settings'; // subtab onesignal
        $mstask = isset($_REQUEST['mstask']) ? $_REQUEST['mstask'] : ''; // subaction of subtab
        if ($tab == 'settings') {
            require_once(MOBICONNECTOR_ABSPATH . 'settings/views/mobiconnector-views-settings.php');
        } elseif ($tab == 'textapp') {
            require_once(MOBICONNECTOR_ABSPATH . 'settings/textapp/mobiconnector-settingtext.php');
        } elseif ($tab == 'mobile-cache') {
            require_once(MOBICONNECTOR_ABSPATH . 'settings/cache/mobiconnector-cache.php');
        } elseif ($tab == 'htaccess') {
            $checkerror = get_option('mobiconnector_settings-error-create-htaccess');
            if (!empty($checkerror)) {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/htaccess/mobiconnector-htaccess.php');
            } else {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/views/mobiconnector-views-settings.php');
            }
        } elseif ($tab == 'config') {
            $checkerror = get_option('mobiconnector_settings-error-create-config');
            if (!empty($checkerror)) {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/config/mobiconnector-config.php');
            } else {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/views/mobiconnector-views-settings.php');
            }
        } elseif ($tab == 'api') {
            require_once(MOBICONNECTOR_ABSPATH . 'settings/api-security/mobiconnector-api-security.php');
        } elseif ($tab == 'onesignal') {
            if ($action == 'settings') {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-settings.php');
            } elseif ($action == 'new') {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-new.php');
            } elseif ($action == 'notice') {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-listnoti.php');
            } elseif ($action == 'player') {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-listuser.php');
            } elseif ($action == 'viewnotice') {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-details.php');
            } else {
                require_once(MOBICONNECTOR_ABSPATH . 'settings/onesignal/mobiconnector-onesignal-settings.php');
            }
        }
        if ($task == 'savesetting') {
            $this->bamobile_save_settings();
        } elseif ($task == 'savesettingtext') {
            $this->bamobile_save_textapp();
        } elseif ($task == 'savesettingcache') {
            $this->bamobile_save_cache();
        } elseif ($task == 'savesettingapi') {
            $this->bamobile_save_api($mstask);
        } elseif ($task == 'saveonesignal') {
            $this->bamobile_save_onesignal($mstask);
        } elseif ($task == 'changeTesttype') {
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'mobiconnector_change_testtype')) {
                die('Go get a life script kiddies');
            } else {
                $player = sanitize_text_field($_REQUEST['player']);
                $device = sanitize_text_field($_REQUEST['device']);
                $section = sanitize_text_field($_REQUEST['sections']);
                $this->bamobile_update_type_player($player, $device, $section);
            }
        }
    }

    /**
     * Style and Script of Mobiconnector Settings
     */
    public function bamobile_admin_style()
    {
        if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'mobiconnector-settings') {
            if (!did_action('wp_enqueue_media')) {
                wp_enqueue_media();
            }
            wp_register_style('mobiconnector-settings-style', plugins_url('assets/css/mobiconnector-settings.css', MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all');
            wp_enqueue_style('mobiconnector-settings-style');
            wp_register_script('mobiconnector_settings_js', plugins_url('assets/js/mobiconnector-settings.js', MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);
            $settings = array();
            wp_localize_script('mobiconnector_settings_js', 'mobiconnector_settings_js_params', $settings);
            wp_enqueue_script('mobiconnector_settings_js');
        }
        if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'mobiconnector-settings' && isset($_GET['mtab']) && $_GET['mtab'] == 'onesignal') {
            wp_register_style('mobiconnector-onesignal-style', plugins_url('assets/css/mobiconnector-onesignal.css', MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all');
            wp_enqueue_style('mobiconnector-onesignal-style');

            wp_register_script('mobiconnector_settings_onesignal_js', plugins_url('assets/js/mobiconnector-onesignal.js', MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);
            $ajax_nonce = wp_create_nonce("mobiconnector-onesignal-ajax");
            $setting = array(
                'baseplugin_url' => plugin_dir_url('mobiconnector/mobiconnector.php'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => $ajax_nonce
            );
            wp_localize_script('mobiconnector_settings_onesignal_js', 'mobiconnector_settings_onesignal_js_params', $setting);
            wp_enqueue_script('mobiconnector_settings_onesignal_js');

            wp_register_script('mobiconnector_colorpicker_js', plugins_url('assets/js/mobiconnector-color-picker.js', MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);
            $color = array();
            wp_localize_script('mobiconnector_colorpicker_js', 'mobiconnector_colorpicker_js_params', $color);
            wp_enqueue_script('mobiconnector_colorpicker_js');
        }
    }

    /**
     * Process save cache
     */
    private function bamobile_save_cache()
    {
        $checkusecache = sanitize_text_field(@$_POST['mobiconnector_use_cache_mobile']);
        if ($checkusecache == 1) {
            self::bamobile_mobiconnector_then_file_api();
            self::bamobile_mobiconnector_then_htaccess_info();
            self::bamobile_mobiconnector_then_wpconfig_file();
            $checkcreatefile = get_option('mobiconnector_settings-error-create-file');
            $checkcreate = get_option('mobiconnector_settings-error-create-htaccess');
            $checkcopyfile = get_option('mobiconnector_settings-error-copy-file');
            $checkcreatconfig = get_option('mobiconnector_settings-error-create-config');
            if (empty($checkcreatefile) && empty($checkcreate) && empty($checkcopyfile) && empty($checkcreatconfig)) {
                self::bamobile_mobiconnector_show_notice_when_save_cache();
                update_option('mobiconnector_settings-use-cache-mobile', 1);
            } else {
                self::bamobile_mobiconnector_show_notice_when_save_cache();
                update_option('mobiconnector_settings-use-cache-mobile', 0);
                wp_redirect('?page=mobiconnector-settings&mtab=mobile-cache');
                return true;
            }
        } else {
            update_option('mobiconnector_settings-use-cache-mobile', 0);
        }
        $minutes = sanitize_text_field(@$_POST['mobiconnector_settings-session-expiry']);
        if (empty($minutes) || $minutes === 0) {
            $minutes = 1440;
        }
        $timeexpiry = $minutes * 60;
        update_option('mobiconnector_settings-session-expiry', $timeexpiry);
        bamobile_mobiconnector_add_notice(__('Successfully Update', 'mobiconnector'));
        wp_redirect('?page=mobiconnector-settings&mtab=mobile-cache');
    }

    /**
     * Send notice when the save cache
     */
    public static function bamobile_mobiconnector_show_notice_when_save_cache()
    {
        if (file_exists(ABSPATH . 'api.php') && is_writable(ABSPATH . 'api.php')) {
            update_option('mobiconnector_settings-error-copy-file', 0);
        }
        $server_software = strtolower($_SERVER['SERVER_SOFTWARE']);
        $checkhtaccessexist = self::bamobile_check_if_isset_htaccess(ABSPATH . '.htaccess', 'MobiConnector');
        if ($checkhtaccessexist || strpos($server_software, 'nginx') !== false) {
            update_option('mobiconnector_settings-error-create-htaccess', 0);
        }
        $checkconfigexist = self::bamobile_check_if_isset_config();
        if ($checkconfigexist) {
            update_option('mobiconnector_settings-error-create-config', 0);
        }
        $checkcreate = get_option('mobiconnector_settings-error-create-htaccess');
        if (!empty($checkcreate)) {
            $messagehtaccess = sprintf(__('The plugins %1$s file, Please access %2$s to manual copy source and write it to .htaccess file', 'mobiconnector'), '<b>can not write setting to .htaccess</b>', '<b>MobiConnector > Settings > Htaccess</b>');
            bamobile_mobiconnector_add_notice($messagecreate, 'error');
        }
        $checkcreatefile = get_option('mobiconnector_settings-error-create-file');
        if (!empty($checkcreatefile)) {
            $messagecreate = sprintf(__('The Directory %1$s is %2$s writable, you should chmod 777 this directory to writable, so your mobile application can use Cache features.', 'mobiconnector'), '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/</i>', '<b>NOT</b>');
            bamobile_mobiconnector_add_notice($messagecreate, 'error');
        }
        $checkcopyfile = get_option('mobiconnector_settings-error-copy-file');
        if (!empty($checkcopyfile)) {
            $messagecopy = sprintf(__('%1$s Can not copy from %2$s<br>Please %3$s file %4$s<br>And %5$s for this file', 'mobiconnector'), '<b>Permission denied:</b>', '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/api.php</i> <b>to</b> <i>' . ABSPATH . 'api.php</i>', '<b>manual copy</b>', '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/api.php</i> <b>to</b> <i>' . ABSPATH . 'api.php</i>', '<b>chmod 777</b>');
            bamobile_mobiconnector_add_notice($messagecopy, 'error');
        }
        $error_config = get_option('mobiconnector_settings-error-create-config');
        if (!empty($error_config)) {
            $messageconfig = sprintf(__('The plugins %1$s, Please access %2$s to manual copy source and write it to wp-config.php file', 'mobiconnector'), '<b>can not write setting to wp-config.php</b>', '<b>MobiConnector > Settings > Config</b>');
            bamobile_mobiconnector_add_notice($messageconfig, 'error');
        }
    }

    /**
     * Process save api
     */
    public function bamobile_save_api($mstask)
    {
        if ($mstask == 'create_key') {
            $key = $this->bamobile_generate_api();
            update_option('mobiconnector_api_key_database', $key);
            bamobile_mobiconnector_add_notice(__('Create Security key successfully', 'mobiconnector'));
        } elseif ($mstask == 'save_settings_key') {
            $use_sercurity = sanitize_text_field(@$_POST['mobiconnector_settings-use-security-key']);
            update_option('mobiconnector_settings-use-security-key', $use_sercurity);
            bamobile_mobiconnector_add_notice(__('Successfully Update', 'mobiconnector'));
        }
        wp_redirect('?page=mobiconnector-settings&mtab=api');
    }

    /**
     * Generate Api Key
     */
    public function bamobile_generate_api()
    {
        $rand = rand();
        $key = str_replace('=', '', strtr(base64_encode($rand), '+/', '-_'));
        $key = md5($key);
        $key = sha1($key);
        return $key;
    }

    /**
     * Process save mobiconnector static
     */
    public function bamobile_save_textapp()
    {
        wp_cache_delete('alloptions', 'options');
        require_once(MOBICONNECTOR_ABSPATH . "xml/mobiconnector-static.php");
        $xmls = bamobile_mobiconnector_get_static();
        $checkname = array();
        foreach ($xmls as $xm) {
            $xml = (object)$xm;
            if (!in_array($xml->name, $checkname)) {
                $oldname = $xml->name;
                $name = str_replace("-", "_", $oldname);
                if ($xml->type == 'editor') {
                    $value = wpautop(stripslashes(sanitize_text_field($_POST["$oldname"])));
                } elseif ($xml->type == 'textarea' && is_plugin_active('qtranslate-x/qtranslate.php')) {
                    $value = stripslashes(sanitize_text_field($_POST["$oldname"]));
                } elseif ($xml->type == 'textarea' && !is_plugin_active('qtranslate-x/qtranslate.php')) {
                    $value = stripslashes(nl2br(sanitize_text_field($_POST["$oldname"]), false));
                } else {
                    $value = stripslashes(sanitize_text_field($_POST["$oldname"]));
                }
                $list["$name"] = $value;
                array_push($checkname, $xml->name);
            }
        }
        update_option('mobiconnector_settings-text-core', serialize($list));
        bamobile_mobiconnector_add_notice(__('Successfully Update', 'mobiconnector'));
        wp_redirect(esc_url('?page=mobiconnector-settings&mtab=textapp'));
    }

    /**
     * Process save onesignal
     * 
     * @param int $mstask  action in page
     */
    public function bamobile_save_onesignal($mstask)
    {
        //action tab api
        if ($mstask == 'api') {
            $apiid = esc_sql(sanitize_text_field(@$_POST["mobiconnector-app-id-onesignal"]));
            $restapikey = esc_sql(sanitize_text_field(@$_POST["mobiconnector-rest-api-key-onesignal"]));
            if (strlen($apiid) != 36) {
                bamobile_mobiconnector_add_notice(__('Your APP ID must be 36 characters. Please retype', 'mobiconnector'), 'error');
                wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=settings');
                return true;
            }
            if (strlen($restapikey) != 48) {
                bamobile_mobiconnector_add_notice(__('Your REST API KEY must be 48 characters. Please retype', 'mobiconnector'), 'error');
                wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=settings');
                return true;
            }
            $apiid = trim($apiid);
            $restapikey = trim($restapikey);
            update_option('mobiconnector_settings-onesignal-api', $apiid);
            update_option('mobiconnector_settings-onesignal-restkey', $restapikey);
            $wooapi = get_option('wooconnector_settings-api');
            $woorest = get_option('wooconnector_settings-restkey');
            if (empty($wooapi) && empty($woorest)) {
                update_option('wooconnector_settings-api', $apiid);
                update_option('wooconnector_settings-restkey', $restapikey);
            }
            global $wpdb;
            $table_name = $wpdb->prefix . "mobiconnector_data_api";
            $table_woo_name = $wpdb->prefix . "wooconnector_data_api";
            $datas = $wpdb->get_results(
                "
                SELECT * 
                FROM $table_name
                WHERE api_key = '$apiid'
                "
            );
            $checkdata = $wpdb->get_results(
                "
                SELECT * 
                FROM $table_woo_name
                WHERE api_key = '$apiid'
                "
            );
            if (empty($datas)) {
                $wpdb->insert(
                    "$table_name",
                    array(
                        "api_key" => $apiid,
                        "rest_api" => $restapikey,
                    ),
                    array(
                        '%s',
                        '%s'
                    )
                );
            }
            if (empty($checkdata)) {
                $wpdb->insert(
                    "$table_woo_name",
                    array(
                        "api_key" => $apiid,
                        "rest_api" => $restapikey,
                    ),
                    array(
                        '%s',
                        '%s'
                    )
                );
            }
            bamobile_mobiconnector_add_notice(__('Successfully Update', 'mobiconnector'));
            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=settings');
            return true;
        }
        // action tab new notice
        elseif ($mstask == 'onesignal') {
            $title = esc_sql(@$_POST['mobiconnector-web-title-notification']);
            update_option('mobiconnector_settings-onesignal-title', $title);

            $content = esc_sql(@$_POST['mobiconnector-web-content-notification']);
            update_option('mobiconnector_settings-onesignal-content', $content);

            $images = sanitize_text_field(@$_POST['mobiconnector-web-icon-notification']);
            if (isset($images) && $images != '') {
                $this->bamobile_update_thumnail_mobiconnector($images, 'mobiconnector_notification_onesignal_icon');
                update_option('mobiconnector_settings-onesignal-id-icon', $images);
            } else {
                update_option('mobiconnector_settings-onesignal-id-icon', $images);
                update_option('mobiconnector_notification_onesignal_icon', '');
            }

            $small = sanitize_text_field(@$_POST['mobiconnector-web-smicon-notification']);
            if (isset($small) && $small != '') {
                $this->bamobile_update_thumnail_mobiconnector($small, 'mobiconnector_notification_onesginal_icon_small');
                update_option('mobiconnector_settings-onesignal-sm-icon', $small);
            } else {
                update_option('mobiconnector_notification_onesignal_icon_small', '');
                update_option('mobiconnector_settings-onesignal-sm-icon', $small);
            }

            $selectedurl = sanitize_text_field(@$_POST['mobiconnector-web-url-select-notification']);
            update_option('mobiconnector_settings-onesignal-url-selected', $selectedurl);
            if ($selectedurl == 'url-post') {
                $url = sanitize_text_field(@$_POST['mobiconnector-web-url-notification-url-post']);
                if (isset($url) && $url != '') {
                    if (strpos($url, 'link://') !== false) {
                        update_option('wooconnector_settings-push-url', $url);
                    } else {
                        update_option('mobiconnector_settings-onesignal-url', $url);
                        $post_id = url_to_postid($url);
                        if (!empty($post_id)) {
                            $newurl = str_replace($url, 'link://post/' . $post_id, $url);
                            update_option('mobiconnector_settings-onesignal-push-url', $newurl);
                        } else {
                            bamobile_mobiconnector_add_notice(__('Your URL is not post URL. Please retype', 'mobiconnector'), 'error');
                            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                            return true;
                        }
                    }
                }
            } elseif ($selectedurl == 'url-category') {
                $url = sanitize_text_field(@$_POST['mobiconnector-web-url-notification-url-category']);
                if (isset($url) && $url != '') {
                    update_option('mobiconnector_settings-onesignal-url', $url);
                    if (strpos($url, 'link://') !== false) {
                        update_option('wooconnector_settings-push-url', $url);
                    } elseif (strpos($url, 'category') != false) {
                        $url_split = explode('#', $url);
                        $url = $url_split[0];
    
                                // Get rid of URL ?query=string
                        $url_split = explode('?', $url);
                        $url = $url_split[0];

                        $scheme = parse_url(home_url(), PHP_URL_SCHEME);
                        $url = set_url_scheme($url, $scheme);

                        if (false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.'))
                            $url = str_replace('://', '://www.', $url);

                        if (false === strpos(home_url(), '://www.'))
                            $url = str_replace('://www.', '://', $url);

                        $url = trim($url, "/");
                        $slugs = explode('/', $url);
                        $category = get_category_by_slug('/' . end($slugs));
                        if (!empty($category)) {
                            $newurl = 'link://category/' . $category->term_id;
                            update_option('mobiconnector_settings-onesignal-push-url', $newurl);
                        } else {
                            bamobile_mobiconnector_add_notice(__('Your URL is not post Category URL. Please retype', 'mobiconnector'), 'error');
                            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                            return true;
                        }
                    } else {
                        bamobile_mobiconnector_add_notice(__('Your URL is not post Category URL. Please retype', 'mobiconnector'), 'error');
                        wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                        return true;
                    }
                }
            } elseif ($selectedurl == 'url-about-us') {
                $newurl = 'link://about-us';
                update_option('mobiconnector_settings-onesginal-push-url', $newurl);
            } elseif ($selectedurl == 'url-bookmark') {
                $newurl = 'link://bookmark';
                update_option('mobiconnector_settings-onesignal-push-url', $newurl);
            } elseif ($selectedurl == 'url-term-and-conditions') {
                $newurl = 'link://term-and-conditions';
                update_option('mobiconnector_settings-onesignal-push-url', $newurl);
            } elseif ($selectedurl == 'url-privacy-policy') {
                $newurl = 'link://privacy-policy';
                update_option('mobiconnector_settings-onesginal-push-url', $newurl);
            } elseif ($selectedurl == 'url-contact-us') {
                $newurl = 'link://contact-us';
                update_option('mobiconnector_settings-onseginal-push-url', $newurl);
            }
            $subtitle = @$_POST['mobiconnector-web-subtitle-notification'];
            update_option('mobiconnector_settings-onesignal-subtitle', $subtitle);

            $sound = sanitize_text_field(@$_POST['mobiconnector-web-sound-notification']);
            update_option('mobiconnector_settings-onesignal-sound', $sound);

            $bigimage = sanitize_text_field(@$_POST['mobiconnector-web-bigimages-notification']);
            if (isset($bigimage) && $bigimage != '') {
                $this->bamobile_update_thumnail_mobiconnector($bigimage, 'mobiconnector_notification_onesignal_bigimages');
                update_option('mobiconnector_settings-onesignal-bigimages', $bigimage);
            } else {
                update_option('mobiconnector_notification_onesignal_bigimages', '');
                update_option('mobiconnector_settings-onesignal-bigimages', $bigimage);
            }

            $responsecolortitle = sanitize_text_field(@$_POST['mobiconnector-web-title-color-response-notification']);
            update_option('mobiconnector_settings-onesignal-response-title-color', $responsecolortitle);

            $colortitle = sanitize_text_field(@$_POST['mobiconnector-web-title-color-notification']);
            update_option('mobiconnector_settings-onesignal-title-color', $colortitle);

            $responsecolorcontent = sanitize_text_field(@$_POST['mobiconnector-web-content-color-response-notification']);
            update_option('mobiconnector_settings-onesignal-response-content-color', $responsecolorcontent);

            $colorcontent = sanitize_text_field(@$_POST['mobiconnector-web-content-color-notification']);
            update_option('mobiconnector_settings-onesignal-content-color', $colorcontent);

            $bgimage = sanitize_text_field(@$_POST['mobiconnector-web-bgimages-notification']);
            if (isset($bgimage) && $bgimage != '') {
                $this->bamobile_update_thumnail_mobiconnector($bgimage, 'mobiconnector_notification_onesignal_background');
                update_option('mobiconnector_settings-onesignal-bgimages', $bgimage);
            } else {
                update_option('mobiconnector_notification_onesignal_background', '');
                update_option('mobiconnector_settings-onesignal-bgimages', $bgimage);
            }

            $responsecolorled = sanitize_text_field(@$_POST['mobiconnector-web-led-color-response-notification']);
            update_option('mobiconnector_settings-onesignal-response-led-color', $responsecolorled);

            $colorled = sanitize_text_field(@$_POST['mobiconnector-web-led-color-notification']);
            update_option('mobiconnector_settings-onesignal-led-color', $colorled);

            $responsecoloraccent = sanitize_text_field(@$_POST['mobiconnector-web-accent-color-response-notification']);
            update_option('mobiconnector_settings-onesignal-response-accent-color', $responsecoloraccent);

            $coloraccent = sanitize_text_field(@$_POST['mobiconnector-web-accent-color-notification']);
            update_option('mobiconnector_settings-onesignal-accent-color', $coloraccent);

            if (isset($_POST['saveandsend'])) {
                $api = get_option('mobiconnector_settings-onesignal-api');
                $rest = get_option('mobiconnector_settings-onesignal-restkey');
                if (empty($api)) {
                    bamobile_mobiconnector_add_notice(__('Please input your api key!', 'mobiconnector'), 'error');
                    wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                    return true;
                } elseif (empty($rest)) {
                    bamobile_mobiconnector_add_notice(__('Please input your rest api key!', 'mobiconnector'), 'error');
                    wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                    return true;
                }
                global $wpdb;
                $table_name = $wpdb->prefix . "mobiconnector_data_api";
                $datas = $wpdb->get_results(
                    "
                    SELECT * 
                    FROM $table_name
                    WHERE api_key = '$api'
                    "
                );
                foreach ($datas as $data) {
                    $idmobiconnectorapi = $data->api_id;
                }
                if (isset($_POST['checksegment']) && sanitize_text_field($_POST['checksegment']) == 'sendeveryone') {
                    $notification = bamobile_sendMobiconnectorMessage();
                } elseif (isset($_POST['checksegment']) && sanitize_text_field($_POST['checksegment']) == 'sendtoparticular') {
                    if (empty($_POST['include_segment'])) {
                        bamobile_mobiconnector_add_notice(__('Send to segments not empty!', 'mobiconnector'), 'error');
                        wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                        return true;
                    }
                    $segment = explode(',', trim($_POST['include_segment'], ','));
                    $exsegment = explode(',', trim($_POST['exclude_segment'], ','));
                    $notification = bamobile_sendMobiconnectorMessageBySegment($segment, $exsegment);
                } elseif (isset($_POST['checksegment']) && sanitize_text_field($_POST['checksegment']) == 'sendtotest') {
                    if (empty($_POST['list_test_player'])) {
                        bamobile_mobiconnector_add_notice(__('List test player not empty!', 'mobiconnector'), 'error');
                        wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                        return true;
                    }
                    $players = $_POST['list_test_player'];
                    $notification = bamobile_sendMobiconnectorMessageByPlayer($players);
                }
                $noti = json_decode($notification);
                if (!empty($noti->errors)) {
                    $errornoti = $noti->errors;
                    if (is_object($errornoti)) {
                        $invalids = $errornoti->invalid_player_ids;
                        if (!empty($invalids)) {
                            foreach ($invalids as $invalid) {
                                $iderrors[] = $invalid;
                            }
                            $iderror = implode(',', $iderrors);
                            $iderror = trim($iderror, ',');
                            bamobile_mobiconnector_add_notice(__('Invalid player ids', 'mobiconnector'), 'error');
                            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                            return true;
                        } else {
                            bamobile_mobiconnector_add_notice(__('All included players are not subscribed', 'mobiconnector'), 'error');
                            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                            return true;
                        }
                    } else {
                        $errormess = $errornoti[0];
                        bamobile_mobiconnector_add_notice($errormess, 'error');
                        wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                        return true;
                    }
                }
                $notificationId = $noti->id;
                $notificationRecipients = $noti->recipients;
                $return = bamobile_MobiconnectorgetNotificationById($notificationId);
                $failed = $return->failed;
                $remaining = $return->remaining;
                $successful = $return->successful;
                $total = ($failed + $remaining + $successful);
                $converted = $return->converted;
                $datenow = new DateTime();
                $date = $datenow->format('Y-m-d H:i:s');
                $table_name = $wpdb->prefix . "mobiconnector_data_notification";
                $wpdb->insert(
                    "$table_name",
                    array(
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
                bamobile_mobiconnector_add_notice(__('Sent Notification successfully', 'mobiconnector'));
                wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
                return true;
            }
            bamobile_mobiconnector_add_notice(__('Save Notification successfully', 'mobiconnector'));
            wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=new');
            return true;
        }
    }

    /**
     * Update type of player
     * 
     * @param string $player    player_id when the update
     * @param string $device    model device when the update
     * @param string $section   action of call
     * 
     * @return mixed 
     */
    public function bamobile_update_type_player($player, $device, $section)
    {
        if ($section == 'addtotest') {
            preg_match("/iPhone|iPad|iPod|webOS/", $device, $matches);
            $os = current($matches);
            if ($os) {
                $testtype = 2;
            } else {
                $testtype = 1;
            }
        } elseif ($section == 'deletetotest') {
            $testtype = 0;
        }
        $api = get_option('mobiconnector_settings-onesignal-api');
        global $wpdb;
        $table_name = $wpdb->prefix . "mobiconnector_data_api";
        $datas = $wpdb->get_results(
            "
			SELECT * 
			FROM $table_name
			WHERE api_key = '$api'
			"
        );
        $idmobiconnectorapi = 0;
        if (!empty($datas)) {
            foreach ($datas as $data) {
                $idmobiconnectorapi = $data->api_id;
            }
        }
        $table_update = $wpdb->prefix . "mobiconnector_data_player";
        $wpdb->update(
            $table_update,
            array(
                'test_type' => $testtype
            ),
            array(
                'api_id' => $idmobiconnectorapi,
                'player_id' => $player
            ),
            array(
                '%s'
            ),
            array(
                '%d',
                '%s'
            )
        );
        wp_redirect('?page=mobiconnector-settings&mtab=onesignal&actions=player');
    }

    /**
     * Process save settings
     */
    private function bamobile_save_settings()
    {
        global $wpdb;
        $emails = sanitize_text_field(@$_POST['mobiconnector_settings-mail']);
        $oldemail = get_option('mobiconnector_settings-mail');
        if ($emails == '') {
            update_option('mobiconnector_settings-mail', $oldemail);
        } else {
            if (is_email($emails)) {
                update_option('mobiconnector_settings-mail', $emails);
            } else {
                bamobile_mobiconnector_add_notice(__('Email is invalid', 'mobiconnector'), 'error');
                wp_redirect(esc_url('?page=mobiconnector-settings'));
                return false;
            }
        }
        $sql = "SELECT DISTINCT post_type FROM " . $wpdb->prefix . "posts";
        $listposttype = $wpdb->get_results($sql, ARRAY_N);
        $value = 0;
        foreach ($listposttype as $posttype) {
            $value = absint(sanitize_text_field(@$_POST["mobi-" . $posttype[0]]));
            $list["mobi-" . $posttype[0] . ""] = $value;
        }
        $list["mobi-post"] = 1;
        $list["mobi-mobi_gallery"] = 1;
        $list["mobi-mobi_video"] = 1;
        update_option('mobiconnector_settings-post_type', serialize($list));
        $guestreview = sanitize_text_field(@$_POST['mobiconnector_settings-guest-reviews']);
        update_option('mobiconnector_settings-guest-reviews', $guestreview);
        $dmlanguages = @$_POST['mobiconnector_settings-languages-display-mode'];
        update_option('mobiconnector_settings-languages-display-mode', $dmlanguages);
        $wpmldmlanguages = sanitize_text_field(@$_POST['mobiconnector_settings-languages-wpml-display-mode']);
        update_option('mobiconnector_settings-languages-wpml-display-mode', $wpmldmlanguages);
        $gganalytics = sanitize_text_field(@$_POST['mobiconnector_settings-google-analytics']);
        update_option('mobiconnector_settings-google-analytics', $gganalytics);
        $dateformat = sanitize_text_field(@$_POST['mobiconnector_settings-date-format']);
        update_option('mobiconnector_settings-date-format', $dateformat);
        $applicationLanguage = sanitize_text_field(@$_POST['mobiconnector_settings-application-languages']);
        update_option('mobiconnector_settings-application-languages', $applicationLanguage);
        $showLanguage = sanitize_text_field(@$_POST['mobiconnector-show-application-languages']);
        update_option('mobiconnector_settings-show-languages', $showLanguage);
        $displaymode = sanitize_text_field(@$_POST['mobiconnector_settings-display-mode']);
        update_option('mobiconnector_settings-display-mode', $displaymode);
        $bannerandoid = sanitize_text_field(@$_POST['mobiconnector_settings-banner-aa']);
        update_option('mobiconnector_settings-banner-aa', $bannerandoid);
        $interstitialandroid = sanitize_text_field(@$_POST['mobiconnector_settings-interstitial-aa']);
        update_option('mobiconnector_settings-interstitial-aa', $interstitialandroid);
        $bannerios = sanitize_text_field(@$_POST['mobiconnector_settings-banner-ia']);
        update_option('mobiconnector_settings-banner-ia', $bannerios);
        $interstitialios = sanitize_text_field(@$_POST['mobiconnector_settings-interstitial-ia']);
        update_option('mobiconnector_settings-interstitial-ia', $interstitialios);
        $main = sanitize_text_field(@$_POST['mobiconnector_settings-maintainmode']);
        update_option('mobiconnector_settings-maintainmode', $main);
        $googleapikey = sanitize_text_field(@$_POST['mobiconnector_settings-google-api-key']);
        update_option('mobiconnector_settings-google-api-key', $googleapikey);
        $socials = @$_POST['mobiconnector_settings-socials'];
        $returnsocials['facebook'] = false;
        $returnsocials['google'] = false;
        foreach ($socials as $social) {
            if ($social == 'facebook') {
                $returnsocials['facebook'] = true;
            } elseif ($social == 'google') {
                $returnsocials['google'] = true;
            }
        }
        update_option('mobiconnector_settings-socials-login', $returnsocials);
        bamobile_mobiconnector_add_notice(__('Successfully Update', 'mobiconnector'));
        wp_redirect(esc_url('?page=mobiconnector-settings'));
    }

    /**
     * Then api.php when the first load
     */
    private static function bamobile_mobiconnector_then_file_api()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $home_path = MOBICONNECTOR_ABSPATH;
        $content = '';
        $content = @file_get_contents(MOBICONNECTOR_ABSPATH . 'includes/install/api.php');
        $content = str_replace('databasenamewithyou', DB_NAME, $content);
        $content = str_replace('databaseuserwithyou', DB_USER, $content);
        $content = str_replace('databasepasswithyou', DB_PASSWORD, $content);
        $content = str_replace('databasehostwithyou', DB_HOST, $content);
        $content = str_replace('databaseutfwithyou', DB_CHARSET, $content);
        $content = str_replace('databasecollatewithyou', DB_COLLATE, $content);
        $content = str_replace('databaseprefixwithyou', $prefix, $content);
        if (!file_exists($home_path . 'includes/cache/api.php')) {
            $checkpermissioncreatefile = is_writable($home_path . 'includes/cache');
            if ($checkpermissioncreatefile) {
                $fp = null;
                $fp = @fopen($home_path . 'includes/cache/api.php', 'w');
                if (!$fp) {
                    update_option('mobiconnector_settings-error-create-file', 1);
                } else {
                    @fclose($fp);
                }
                @file_put_contents($home_path . 'includes/cache/api.php', $content);
                update_option('mobiconnector_settings-error-create-file', 0);
                if (@copy($home_path . 'includes/cache/api.php', ABSPATH . 'api.php')) {
                    update_option('mobiconnector_settings-error-copy-file', 0);
                } else {
                    update_option('mobiconnector_settings-error-copy-file', 1);
                }
            } else {
                update_option('mobiconnector_settings-error-create-file', 1);
            }
        } else {
            @file_put_contents($home_path . 'includes/cache/api.php', "");
            @file_put_contents($home_path . 'includes/cache/api.php', $content);
            update_option('mobiconnector_settings-error-create-file', 0);
            if (@copy($home_path . 'includes/cache/api.php', ABSPATH . 'api.php')) {
                update_option('mobiconnector_settings-error-copy-file', 0);
            } else {
                update_option('mobiconnector_settings-error-copy-file', 1);
            }
        }
    }

    /**
     * Then Htaccess when the first load
     */
    private static function bamobile_mobiconnector_then_htaccess_info()
    {
        $server_software = strtolower($_SERVER['SERVER_SOFTWARE']);
        if (strpos($server_software, 'nginx') === false) {
            $home_path = ABSPATH;
            $base_url = get_bloginfo('url');
            $base_host = $_SERVER['HTTP_HOST'];
            $trimhttpurl = (strpos($base_url, 'https') !== false) ? str_replace('https://', '', $base_url) : str_replace('http://', '', $base_url);
            $trimurl = (strpos($trimhttpurl, 'www') !== false) ? str_replace('www.', '', $trimhttpurl) : $trimhttpurl;
            $childhost = str_replace($base_host, '', $trimurl);
            $home_path = trailingslashit($home_path);
            if ($old_data = @file_get_contents($home_path . '.htaccess')) {
                $content = "# BEGIN MobiConnector\n";
                $content .= "# Cache Browser\n";
                $content .= "<IfModule mod_expires.c>\n";
                $content .= "ExpiresActive On\n";
                $content .= "ExpiresDefault \"access plus 1 month\"\n";
                $content .= "# Images\n";
                $content .= "ExpiresByType image/gif \"access plus 1 month\"\n";
                $content .= "ExpiresByType image/png \"access plus 1 month\"\n";
                $content .= "ExpiresByType image/jpg \"access plus 1 month\"\n";
                $content .= "ExpiresByType image/jpeg \"access plus 1 month\"\n";
                $content .= "</IfModule>\n";
                $content .= "<IfModule mod_rewrite.c>\n";
                $content .= "RewriteEngine On\n";
                $content .= "RewriteRule . - [E=REWRITEBASE:" . $childhost . "]\n";
                $content .= "RewriteRule ^(.*\/)?wp-json/?(.*)$ %{ENV:REWRITEBASE}/api.php [QSA,L]\n";
                $content .= "RewriteCond %{HTTP:Authorization} ^(.*)\n";
                $content .= "RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]\n";
                $content .= "</IfModule>\n";
                $content .= "# Set Authorization\n";
                $content .= "SetEnvIf Authorization \"(.*)\" HTTP_AUTHORIZATION=$1\n";
                $content .= "<IfModule mod_headers.c>\n";
                $content .= 'Header always set Access-Control-Allow-Origin "http://localhost:8080"' . "\n";
                $content .= 'Header always set Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept, Authorization"' . "\n";
                $content .= "</IfModule>\n";
                $content .= "# END MobiConnector\n";
                update_option('mobiconnector_settings-htaccess-data', $content);
                $checkpermissionthenhtaccess = is_writable($home_path . '.htaccess');
                if ($checkpermissionthenhtaccess) {
                    if (!self::bamobile_check_if_isset_htaccess($home_path . '.htaccess', 'MobiConnector')) {
                        @file_put_contents($home_path . '.htaccess', '');
                        $contenthtaccess = $content . $old_data;
                        @file_put_contents($home_path . '.htaccess', $contenthtaccess);
                        update_option('mobiconnector_settings-error-create-htaccess', 0);
                    } else {
                        $headhtaceess = substr($old_data, 0, strpos($old_data, '# BEGIN MobiConnector'));
                        $bothtaccess = substr($old_data, strpos($old_data, '# END MobiConnector'));
                        $bothtaccess = str_replace('# END MobiConnector', '', $bothtaccess);
                        $bothtaccess = trim($bothtaccess);
                        $htaccessnotmobi = $headhtaceess . $bothtaccess;
                        $contenthtaccess = $content . $htaccessnotmobi;
                        @file_put_contents($home_path . '.htaccess', '');
                        @file_put_contents($home_path . '.htaccess', $contenthtaccess);
                        update_option('mobiconnector_settings-error-create-htaccess', 0);
                    }
                } else {
                    update_option('mobiconnector_settings-error-create-htaccess', 1);
                }
            } else {
                update_option('mobiconnector_settings-error-create-htaccess', 1);
            }
        } else {
            update_option('mobiconnector_settings-error-create-htaccess', 0);
        }
    }

    /**
     * Then Wp Config File
     */
    private static function bamobile_mobiconnector_then_wpconfig_file()
    {
        $home_path = ABSPATH;
        $home_path = trailingslashit($home_path);
        $home_url = get_bloginfo('url');
        $keyPlugin = md5("MobileConnector");
        $key = $keyPlugin . '-' . md5($home_path) . '-' . md5($home_url);
        $data = "//* BEGIN MobileConnector *//\n";
        $data .= 'define("JWT_AUTH_SECRET_KEY","' . $key . '");' . "\n";
        $data .= 'define("JWT_AUTH_CORS_ENABLE",true);' . "\n";
        $data .= "//* END MobileConnector *//\n";
        if ($old_data = @file_get_contents($home_path . 'wp-config.php')) {
            if (is_writable($home_path . 'wp-config.php')) {
                if (!strpos($old_data, 'BEGIN MobileConnector')) {
                    $old_data = self::bamobile_mobiconnector_clear_config($old_data);
                    @file_put_contents($home_path . 'wp-config.php', '');
                    $contentconfig = $data . $old_data;
                    $contentconfig = str_replace("<?php", "", $contentconfig);
                    $content = "<?php\n" . $contentconfig;
                    @file_put_contents($home_path . 'wp-config.php', $content);
                    update_option('mobiconnector_settings-error-create-config', 0);
                } else {
                    $headconfig = substr($old_data, 0, strpos($old_data, '//* BEGIN MobileConnector *//'));
                    $footconfig = substr($old_data, strpos($old_data, '//* END MobileConnector *//'));
                    $footconfig = str_replace('//* END MobileConnector *//', '', $footconfig);
                    $footconfig = trim($footconfig);
                    $confignotmobi = $headconfig . $footconfig;
                    $contentconfig = $data . $confignotmobi;
                    $contentconfig = str_replace("<?php", "", $contentconfig);
                    $content = "<?php\n" . $contentconfig;
                    @file_put_contents($home_path . 'wp-config.php', '');
                    @file_put_contents($home_path . 'wp-config.php', $content);
                    update_option('mobiconnector_settings-error-create-config', 0);
                }
            } else {
                update_option('mobiconnector_settings-error-create-config', 1);
            }
        }
    }

    /**
     * Process clear config
     */
    private static function bamobile_mobiconnector_clear_config($string = '')
    {
        $pattern = '/(define)(\()(\"|\')(JWT_AUTH_SECRET_KEY)(\"|\')(\,)(.*)(\"|\')(.+)(\"|\')(\))(\;)/i';
        $pattern2 = '/(define)(\()(\"|\')(JWT_AUTH_CORS_ENABLE)(\"|\')(\,)(.*)(true)(\))(\;)/i';
        $string = preg_replace($pattern, "", $string);
        $string = preg_replace($pattern2, "", $string);
        return $string;
    }

    /**
     * Check Config exist in htaccess
     * 
     * @param string $filename   path to file htaccess
     * @param string $marker     name to determine confix exist
     * 
     * @return boolean
     */
    public static function bamobile_check_if_isset_htaccess($filename, $marker)
    {
        $result = array();
        if (!file_exists($filename)) {
            return $result;
        }

        $markerdata = explode("\n", implode('', file($filename)));
        $checkint = 0;
        $state = false;
        foreach ($markerdata as $markerline) {
            if (false !== strpos($markerline, '# BEGIN ' . $marker)) {
                $checkint++;
            }
            if (false !== strpos($markerline, '# END ' . $marker)) {
                $checkint++;
            }
        }
        if ($checkint > 1) {
            return true;
        }
        return false;
    }

    /**
     * Check isset config
     */
    private static function bamobile_check_if_isset_config()
    {
        $file = ABSPATH . "wp-config.php";
        $marker = "MobileConnector";
        $markerdata = explode("\n", implode('', file($file)));
        $checkint = 0;
        $state = false;
        foreach ($markerdata as $markerline) {
            if (false !== strpos($markerline, '//* BEGIN ' . $marker)) {
                $checkint++;
            }
            if (false !== strpos($markerline, '//* END ' . $marker)) {
                $checkint++;
            }
        }
        if ($checkint > 1) {
            return true;
        }
        return false;
    }

    /**
     * Crop or Resize image
     */
    public function bamobile_update_thumnail_mobiconnector($url, $type)
    {
        $wp_upload_dir = wp_upload_dir();
        if (!empty($url) || $url != '') {
            $fileurl = str_replace($wp_upload_dir['baseurl'], '', $url);
            $absolute_pathto_file = $wp_upload_dir['basedir'] . '/' . $fileurl;
            $path_parts = pathinfo($fileurl);
            $ext = strtolower($path_parts['extension']);
            $basename = strtolower($path_parts['basename']);
            $dirname = strtolower($path_parts['dirname']);
            $filename = strtolower($path_parts['filename']);
            foreach ($this->thumnails as $key => $value) {
                if ($key == $type) {
                    if ($key == 'mobiconnector_notification_onesignal_bigimages') {
                        list($width, $height) = getimagesize($absolute_pathto_file);
                        if ($width < 512 || $height < 256) {
                            $path = $dirname . '/' . $filename . '_' . $key . '_512_256.' . $ext;
                            $dest = $wp_upload_dir['basedir'] . '/' . $path;
                            if (!file_exists($dest)) {
                                BAMobileCore::bamobile_resize_image($absolute_pathto_file, $dest, 512, 256);
                            }
                            update_option($key, $wp_upload_dir['baseurl'] . $path);
                        } elseif ($width > 2048 || $height > 1024) {
                            $path = $dirname . '/' . $filename . '_' . $key . '_2048_1024.' . $ext;
                            $dest = $wp_upload_dir['basedir'] . '/' . $path;
                            if (!file_exists($dest)) {
                                BAMobileCore::bamobile_resize_image($absolute_pathto_file, $dest, 2048, 1024);
                            }
                            update_option($key, $wp_upload_dir['baseurl'] . $path);
                        } else {
                            update_option($key, $url);
                        }
                    } else {
                        $path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
                        $dest = $wp_upload_dir['basedir'] . '/' . $path;
                        if (!file_exists($dest)) {
                            BAMobileCore::bamobile_resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
                        }
                        update_option($key, $wp_upload_dir['baseurl'] . $path);
                    }

                }
            }
        } else {
            foreach ($this->thumnails as $key) {
                if ($key == $type) {
                    update_option($key, '');
                }
            }
        }

        return true;
    }

    /**
     * Size of thumbnail
     */
    public $thumnails = array(
        'mobiconnector_notification_onesignal_icon_small' => array(
            'width' => 48,
            'height' => 48
        ),
        'mobiconnector_notification_onesignal_icon' => array(
            'width' => 256,
            'height' => 256
        ),
        'mobiconnector_notification_onesignal_bigimages' => array(
            'width' => 1024,
            'height' => 512
        ),
        'mobiconnector_notification_onesignal_background' => array(
            'width' => 2176,
            'height' => 256
        )
    );
}
$BAMobileSettings = new BAMobileSettings();
?>