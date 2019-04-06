<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
/**
 * Main class install
 */
class BAMobileInstall
{

    /**
     * Hook in tabs.
     */
    public static function bamobile_init()
    {
        if (!extension_loaded('gd') && !function_exists('gd_info')) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_error'));
        }
        add_filter('wpmu_drop_tables', array(__class__, 'bamobile_wpmu_drop_tables'));
        self::bamobile_create_tables();
        add_filter('plugin_action_links_' . MOBICONNECTOR_PLUGIN_BASENAME, array(__class__, 'bamobile_add_settings_link_mobiconnector'), 10, 2);
        add_action('admin_enqueue_scripts', array(__class__, 'bamobile_mobiconnector_script_admin'));
        $supports = array('mobiconnector-settings', 'wooconnector', 'woo-notifications', 'popup', 'woo-slider', 'azstore-settings', 'cellstore-settings', 'modernshop-settings', 'oli-settings', 'mobiconnector-extensions');
        $supporttypes = array('mobi_gallery', 'mobi_video', 'product');
        if (is_admin() && (isset($_GET['page']) && in_array($_GET['page'], $supports) || isset($_GET['post']) && in_array(get_post_type($_GET['post']), $supporttypes) || isset($_GET['post_type']) && in_array($_GET['post_type'], $supporttypes))) {
            add_filter('media_library_months_with_files', array(__class__, 'bamobile_mobiconnector_fix_error_locale'));
        }
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
        $checkcreatefile = get_option('mobiconnector_settings-error-create-file');
        if (!file_exists(MOBICONNECTOR_ABSPATH . 'includes/cache/api.php') && !empty($checkcreatefile)) {
            self::bamobile_mobiconnector_then_file_api();
        }
        $checkcreate = get_option('mobiconnector_settings-error-create-htaccess');
        if (!empty($checkcreate)) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_create_file_error'));
        }
        if (!empty($checkcreatefile)) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_create_file_error_api'));
        }
        $checkcopyfile = get_option('mobiconnector_settings-error-copy-file');
        if (!empty($checkcopyfile)) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_create_file_error_copy_api'));
        }
        $errors_deactive = get_option('mobiconnector_extension_error_deactive');
        if (!empty($errors_deactive)) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_update_plugin'));
        }
        $error_config = get_option('mobiconnector_settings-error-create-config');
        if (!empty($error_config)) {
            add_action('admin_notices', array(__class__, 'bamobile_admin_notice_create_config_error'));
        }
        add_action('admin_init', array(__class__, 'bamobile_admin_init_check_plugins'));
        add_action('delete_post', array(__class__, 'bamobile_delete_post_clear_images'), 10);
    }

    /**
     * Clear images with delete posts
     */
    public static function bamobile_delete_post_clear_images($post_id)
    {
        $post_type = get_post_type($post_id);
        $wp_dir = wp_upload_dir();
        if ($post_type == 'attachment') {
            $pti = $post_id;
        } elseif ($post_type == 'post') {
            $pti = get_post_thumbnail_id($post_id);
        } else {
            return true;
        }
        $thumbId = array($pti);
        $listids = array_merge($imageids, $thumbId);
        $list_ids = array_unique($listids);
        foreach ($list_ids as $id) {
            if (empty($id)) {
                continue;
            }
            $mobiconnector_large = get_post_meta($id, 'mobiconnector_large', true);
            @unlink($wp_dir['basedir'] . '/' . $mobiconnector_large);
            $mobiconnector_medium = get_post_meta($id, 'mobiconnector_medium', true);
            @unlink($wp_dir['basedir'] . '/' . $mobiconnector_medium);
            $mobiconnector_x_large = get_post_meta($id, 'mobiconnector_x_large', true);
            @unlink($wp_dir['basedir'] . '/' . $mobiconnector_x_large);
            $mobiconnector_small = get_post_meta($id, 'mobiconnector_small', true);
            @unlink($wp_dir['basedir'] . '/' . $mobiconnector_small);
            delete_post_meta($id, 'mobiconnector_large', $mobiconnector_large);
            delete_post_meta($id, 'mobiconnector_medium', $mobiconnector_medium);
            delete_post_meta($id, 'mobiconnector_x_large', $mobiconnector_x_large);
            delete_post_meta($id, 'mobiconnector_small', $mobiconnector_small);
        }
    }

    /**
     * Check plugins 
     */
    public static function bamobile_admin_init_check_plugins()
    {
        $listextensions = self::bamobile_mobiconnector_get_extension();
        $errors = get_option('mobiconnector_extension_error_deactive', array());
        $change = 0;
        if (!empty($listextensions)) {
            foreach ($listextensions as $extension => $name) {
                if (is_plugin_active($extension)) {
                    if (is_array($errors) && !in_array($name, $errors)) {
                        array_push($errors, $name);
                        update_option('mobiconnector_extension_error_deactive', $errors);
                        $change++;
                    }
                } else {
                    if (is_array($errors)) {
                        $key = array_search($name, $errors);
                        if ($key !== false) {
                            unset($errors[$key]);
                            update_option('mobiconnector_extension_error_deactive', $errors);
                            $change++;
                        }
                    }
                }
            }
            if ($change > 0) {
                $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                wp_redirect($actual_link);
            }
        }
    }

    /**
     * Then Script Admin
     */
    public static function bamobile_mobiconnector_script_admin()
    {
        if (is_admin()) {
            wp_register_script('mobiconnector_admin_js', plugins_url('assets/js/mobiconnector-admin.js', MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);
            $settings = array();
            wp_localize_script('mobiconnector_admin_js', 'mobiconnector_admin_js_params', $settings);
            wp_enqueue_script('mobiconnector_admin_js');
        }
    }

    /**
     * Fix notice locale
     */
    public static function bamobile_mobiconnector_fix_error_locale($object)
    {
        $data = array(
            'year' => date('Y'),
            'month' => date('m')
        );
        $object = array((object)$data);
        return $object;
    }

    /** 
     * Add notices to admin with Host not permission create file api
     */
    public static function bamobile_admin_notice_update_plugin()
    {
        $errors = get_option('mobiconnector_extension_error_deactive');
        $errors = @implode('<br> - ', $errors);
        $class = 'notice notice-warning';
        $message = sprintf(__('Since %1$s version 1.1.5+, you %2$s deactive some old plugins: %3$s', 'mobiconnector'), "<b>Mobile Connector's</b>", '<b style="color:#FF0000;">must</b>', '<br> - ' . $errors);
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }

    /** 
     * Add notices to admin with Host not permission create file api
     */
    public static function bamobile_admin_notice_create_file_error_copy_api()
    {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(__('%1$s Can not copy from %2$s<br>Please %3$s file %4$s<br>And %5$s for this file', 'mobiconnector'), '<b>Permission denied:</b>', '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/api.php</i> <b>to</b> <i>' . ABSPATH . 'api.php</i>', '<b>manual copy</b>', '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/api.php</i> <b>to</b> <i>' . ABSPATH . 'api.php</i>', '<b>chmod 777</b>');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }

    /** 
     * Add notices to admin with Host not permission create file api
     */
    public static function bamobile_admin_notice_create_file_error_api()
    {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(__('The Directory %1$s is %2$s writable, you should chmod 777 this directory to writable, so your mobile application can use Cache features.', 'mobiconnector'), '<i>' . MOBICONNECTOR_ABSPATH . 'includes/cache/</i>', '<b>NOT</b>');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }

    /** 
     * Add notices to admin with Host not permission create file
     */
    public static function bamobile_admin_notice_create_file_error()
    {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(__('The plugins %1$s file, Please access %2$s to manual copy source and write it to .htaccess file', 'mobiconnector'), '<b>can not write setting to .htaccess</b>', '<b>MobiConnector > Settings > Htaccess</b>');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }

    /** 
     * Add notices to admin with Host not permission create config
     */
    public static function bamobile_admin_notice_create_config_error()
    {
        $class = 'notice notice-error is-dismissible';
        $message = sprintf(__('The plugins %1$s, Please access %2$s to manual copy source and write it to wp-config.php file', 'mobiconnector'), '<b>can not write setting to wp-config.php</b>', '<b>MobiConnector > Settings > Config</b>');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
    }

    /**
     * Show notice with Server not support GD libarary
     */
    public static function bamobile_admin_notice_error()
    {
        $class = 'notice notice-error is-dismissible';
        $message = __('Your PHP have not installed the GD library . Please install the GD library to avoid some errors when using the REST API. ', 'mobiconnector');
        $link = __('Install GD library', 'mobiconnector');
        printf('<div class="%1$s"><p>%2$s<a href="http://php.net/manual/en/image.installation.php">%3$s</a></p></div>', esc_attr($class), esc_html($message), esc_html($link));
    }

    /**
     * Add Link redirect to Settings MobiConnector
     * 
     * @param array $links   List link of plugin
     * @param string file    path of plugin
     * 
     * @return mixed
     */
    public static function bamobile_add_settings_link_mobiconnector($links, $file)
    {
        if (strpos($file, 'mobiconnector/mobiconnector.php') !== false) {
            $new_links = array(
                'settingsmod' => '<a href="' . get_admin_url() . '/admin.php?page=mobiconnector-settings" class="settings_link">' . __('Settings', 'mobiconnector') . '</a>'
            );
            $links = array_merge($links, $new_links);
        }
        return $links;
    }

    /**
     * Install Mobiconnector
     * 
     */
    public static function bamobile_install()
    {
        // Check if we are not already running this routine.
        if ('yes' === get_transient('mobiconnector_installing')) {
            return;
        }
        set_transient('mobiconnector_installing', 'yes', MINUTE_IN_SECONDS * 10);
        self::bamobile_create_cron_jobs();
        self::bamobile_mobiconnector_then_file_api();
        self::bamobile_mobiconnector_then_htaccess_info();
        self::bamobile_mobiconnector_then_wpconfig_file();
        self::bamobile_mobiconnector_update_session_expiry();
        self::bamobile_loaded_add_textstatic();
        self::bamobile_mobiconnector_loaded_settings_first_app();
        self::bamobile_mobiconnector_loaded_email_report();
        self::bamobile_mobiconnector_loaded_get_post_type();
        self::bamobile_update_db_version();
        self::bamobile_first_active_plugin();
        delete_transient('mobiconnector_installing');
    }

    /**
     * Active extension when the plugin active
     */
    private static function bamobile_first_active_plugin()
    {
        $listextensions = self::bamobile_mobiconnector_get_extension();
        $list_extensions = @file_get_contents(MOBICONNECTOR_ABSPATH . 'extensions/extensions.mobiconnector');
        $active_plugins = get_option('active_plugins', array());
        $list_active_extensions = array();
        if (!empty($list_extensions)) {
            $list_extensions = trim($list_extensions, '#');
            $list_active_extensions = @explode('#', $list_extensions);
        }
        if (!empty($listextensions)) {
            foreach ($listextensions as $extension => $name) {
                if (!empty($list_active_extensions)) {
                    if (in_array($extension, $list_active_extensions)) {
                        if (in_array($extension, $active_plugins)) {
                            $key = array_search($extension, $active_plugins);
                            unset($active_plugins[$key]);
                            update_option('active_plugins', $active_plugins);
                        }
                        $reactive_plugins = get_option('active_plugins', array());
                        if (in_array($extension, $reactive_plugins)) {
                            $errors = get_option('mobiconnector_extension_error_deactive', array());
                            array_push($errors, $name);
                            update_option('mobiconnector_extension_error_deactive', $errors);
                        }
                        self::bamobile_mobiconnector_active_extension($extension);
                    }
                } elseif ($extension == 'wooconnector/wooconnector.php' || $extension == 'cellstore/cellstore.php' || $extension == 'modernshop/modernshop.php' || $extension == 'oli/oli.php') {
                    if (in_array($extension, $active_plugins)) {
                        $key = array_search($extension, $active_plugins);
                        unset($active_plugins[$key]);
                        update_option('active_plugins', $active_plugins);
                    }
                    $reactive_plugins = get_option('active_plugins', array());
                    if (in_array($extension, $reactive_plugins)) {
                        $errors = get_option('mobiconnector_extension_error_deactive', array());
                        array_push($errors, $name);
                        update_option('mobiconnector_extension_error_deactive', $errors);
                    }
                    if (!self::bamobile_is_extension_active($extension)) {
                        self::bamobile_mobiconnector_active_extension($extension);
                    }
                }
            }
        }
    }

    /**
     * Update session expiry.
     */
    private static function bamobile_mobiconnector_update_session_expiry()
    {
        delete_option('mobiconnector_settings-session-expiry');
        update_option('mobiconnector_settings-session-expiry', 86400);
        $checkcreatefile = get_option('mobiconnector_settings-error-create-file');
        $checkcreate = get_option('mobiconnector_settings-error-create-htaccess');
        $checkcopyfile = get_option('mobiconnector_settings-error-copy-file');
        if (empty($checkcreatefile) && empty($checkcreate) && empty($checkcopyfile)) {
            update_option('mobiconnector_settings-use-cache-mobile', 1);
        } else {
            update_option('mobiconnector_settings-use-cache-mobile', 0);
        }
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
     * Loaded add default value in email report
     */
    private static function bamobile_mobiconnector_loaded_email_report()
    {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->prefix . "users AS u INNER JOIN " . $wpdb->prefix . "usermeta AS um ON u.ID = um.user_id WHERE um.meta_value LIKE '%administrator%' ORDER BY u.ID ASC LIMIT 1";
        $user = $wpdb->get_results($sql, ARRAY_A);
        $user = $user[0]['user_email'];
        $checkemail = get_option('mobiconnector_settings-mail');
        if (empty($checkemail) || $checkemail == '') {
            update_option('mobiconnector_settings-mail', $user);
        }
    }

    /**
     * Update DB version to current.
     *
     */
    private static function bamobile_update_db_version($version = null)
    {
        delete_option('mobiconnector_db_version');
        update_option('mobiconnector_db_version', is_null($version) ? MOBICONNECTOR_VERSION : $version);
    }

    /**
     * Uninstall tables when MU blog is deleted.
     *
     * @param  array $tables List of tables that will be deleted by WP.
     * @return string[]
     */
    public static function bamobile_wpmu_drop_tables($tables)
    {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'mobiconnector_sessions';
        $tables[] = $wpdb->prefix . 'mobiconnector_social_users';
        $tables[] = $wpdb->prefix . 'mobiconnector_data_api';
        $tables[] = $wpdb->prefix . 'mobiconnector_data_notification';
        $tables[] = $wpdb->prefix . 'mobiconnector_data_player';
        $tables[] = $wpdb->prefix . 'mobiconnector_views';
        $tables[] = $wpdb->prefix . 'mobiconnector_manage_device';
        return $tables;
    }

    /**
     * Create cron job
     */
    private static function bamobile_create_cron_jobs()
    {
        wp_clear_scheduled_hook('mobiconnector_update_player_id');
        wp_schedule_event(time(), 'hourly', 'mobiconnector_update_player_id');
    }

    /**
     * Set up the database tables which the plugin needs to function.
     */
    private static function bamobile_create_tables()
    {
        global $wpdb;  
              
        //$wpdb->hide_errors();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $listtables = self::bamobile_wpmu_drop_tables(array());
        $checkdb = 0;
        foreach ($listtables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                dbDelta(self::bamobile_get_schema($table));
            }
        }
    }

    /**
     * Update all post_type to list post type support of mobiconnector when the loaded
     */
    private static function bamobile_mobiconnector_loaded_get_post_type()
    {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $db_options = $prefix . 'options';
        $sql_query = 'SELECT * FROM ' . $db_options . ' WHERE option_name = "mobiconnector_settings-post_type"';

        $results = $wpdb->get_results($sql_query, OBJECT);
        if (count($results) === 0) {
            $sql = "SELECT DISTINCT post_type FROM " . $wpdb->prefix . "posts";
            $listposttype = $wpdb->get_results($sql, ARRAY_N);
            $list = array();
            foreach ($listposttype as $posttype) {
                if ($posttype[0] == 'woo-slider' || $posttype[0] == 'attachment' || $posttype[0] == 'revision') {
                    continue;
                }
                if ($posttype[0] == 'post' || $posttype[0] == 'mobi_gallery' || $posttype[0] == 'mobi_video') {
                    $value = 1;
                } else {
                    $value = 0;
                }
                $list["mobi-" . $posttype[0]] = $value;
            }
            update_option('mobiconnector_settings-post_type', serialize($list));
        } else {
            $list = array();
            $listoption = get_option('mobiconnector_settings-post_type');
            $list = unserialize($listoption);
            $sql = "SELECT DISTINCT post_type FROM " . $wpdb->prefix . "posts";
            $listposttype = $wpdb->get_results($sql, ARRAY_N);
            foreach ($listposttype as $posttype) {
                if ($posttype[0] == 'woo-slider' || $posttype[0] == 'attachment' || $posttype[0] == 'revision') {
                    continue;
                }
                if (!empty($list["mobi-" . $posttype[0]])) {
                    continue;
                }
                if ($posttype[0] == 'post' || $posttype[0] == 'mobi_gallery' || $posttype[0] == 'mobi_video') {
                    $list["mobi-" . $posttype[0]] = 1;
                }
                $list["mobi-" . $posttype[0]] = 0;
            }
            update_option('mobiconnector_settings-post_type', serialize($list));
        }
    }

    /**
     * Add default first app
     */
    private static function bamobile_mobiconnector_loaded_settings_first_app()
    {
        $gganalytics = '';
        update_option('mobiconnector_settings-google-analytics', $gganalytics);
        $dateformat = 'hh:mm a, dd/MM/yyyy';
        update_option('mobiconnector_settings-date-format', $dateformat);
        $applicationLanguage = '';
        update_option('mobiconnector_settings-application-languages', $applicationLanguage);
        $displaymode = 'ltr';
        update_option('mobiconnector_settings-display-mode', $displaymode);
        $bannerandoid = '';
        update_option('mobiconnector_settings-banner-aa', $bannerandoid);
        $interstitialandroid = '';
        update_option('mobiconnector_settings-interstitial-aa', $interstitialandroid);
        $bannerios = '';
        update_option('mobiconnector_settings-banner-ia', $bannerios);
        $interstitialios = '';
        update_option('mobiconnector_settings-interstitial-ia', $interstitialios);
        update_option('mobiconnector_first_settings_first_app', 1);
        $returnsocials['facebook'] = true;
        $returnsocials['google'] = true;
        update_option('mobiconnector_settings-socials-login', $returnsocials);
    }

    /**
     * Add default text static
     */
    private static function bamobile_loaded_add_textstatic()
    {
        require_once(MOBICONNECTOR_ABSPATH . "xml/mobiconnector-static.php");
        $xmls = bamobile_mobiconnector_get_static();
        $checkcore = get_option('mobiconnector_settings_checkcore');
        if ($checkcore != 1) {
            delete_option('mobiconnector_settings_checkcore');
        }
        global $wpdb;
        $prefix = $wpdb->prefix;
        $db_options = $prefix . 'options';
        $sql_query = 'SELECT * FROM ' . $db_options . ' WHERE option_name = "mobiconnector_settings-text-core"';

        $results = $wpdb->get_results($sql_query, OBJECT);

        if (count($results) === 0) {
            $checkname = array();

            foreach ($xmls as $xm) {
                $xml = (object)$xm;
                if (!in_array($xml->name, $checkname)) {
                    $oldname = $xml->name;
                    $name = str_replace("-", "_", $oldname);
                    $values = $xml->defaultValue;
                    if (empty($xml->defaultValue)) {
                        $value = '';
                    } elseif ($xml->type == 'text') {
                        $value = $values;
                    } elseif ($xml->type == 'editor') {
                        $valuee = $values;
                        $value = wpautop(stripslashes(html_entity_decode($valuee)));
                    } elseif ($xml->type == 'textarea' && is_plugin_active('qtranslate-x/qtranslate.php')) {
                        $value = stripslashes($values);
                    } elseif ($xml->type == 'textarea' && !is_plugin_active('qtranslate-x/qtranslate.php')) {
                        $value = stripslashes(nl2br($values, false));
                    }
                    $list["$name"] = $value;
                    array_push($checkname, $xml->name);
                }
            }
            update_option('mobiconnector_settings-text-core', serialize($list));
            update_option('mobiconnector_settings_checkcore', '1');
        }
    }

    /**
     * Get Table schema.
     */
    private static function bamobile_get_schema($table)
    {
        global $wpdb;
        $tableprefix = $wpdb->prefix;
        $table_session = $tableprefix . "mobiconnector_sessions";
        $table_socical_users = $tableprefix . "mobiconnector_social_users";
        $table_data_api = $tableprefix . "mobiconnector_data_api";
        $table_notification = $tableprefix . "mobiconnector_data_notification";
        $table_player = $tableprefix . "mobiconnector_data_player";
        $table_views = $tableprefix . "mobiconnector_views";
        $table_manage_device = $tableprefix . "mobiconnector_manage_device";
        $table_post = $tableprefix . "posts";
        $sql = "SHOW TABLE STATUS WHERE Name = '" . $table_post . "'";
        $result = $wpdb->get_row($sql);
        $engine = $result->Engine;
        $collation = $result->Collation;
        $charset = trim(substr($collation, 0, strpos($collation, '_')));;
        if ($table == $table_session) {
            $tables = "
            CREATE TABLE " . $table_session . " (
                session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                session_key varchar(100) DEFAULT '' NOT NULL,
                session_value longtext DEFAULT '' NOT NULL,
                session_expiry BIGINT NOT NULL,
                PRIMARY KEY (session_id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";         
            ";
            return $tables;
        } elseif ($table == $table_socical_users) {
            $tables = "
            CREATE TABLE " . $table_socical_users . " (
                id BIGINT NOT NULL AUTO_INCREMENT,
                user_id BIGINT NOT NULL,
                user_email varchar(255) NULL DEFAULT '',
                user_picture varchar(500) NULL DEFAULT '',
                user_firstname varchar(100) NULL DEFAULT '',
                user_lastname varchar(100) NULL DEFAULT '',
                user_url varchar(255) NULL DEFAULT '',
                user_social_id varchar(255) NULL DEFAULT '',
                social varchar(100) NULL DEFAULT '',
                PRIMARY KEY (id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";         
            ";
            return $tables;
        } elseif ($table == $table_data_api) {
            $tables = "
            CREATE TABLE " . $table_data_api . " (
                api_id INT NOT NULL AUTO_INCREMENT,
                api_key varchar(255) NOT NULL,
                rest_api varchar(255) NOT NULL,					
                PRIMARY KEY (api_id),
                UNIQUE KEY api_id (api_id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";
            ";
            return $tables;
        } elseif ($table == $table_notification) {
            $tables = "
            CREATE TABLE " . $table_notification . " (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                api_id INT NOT NULL,
                notification_id varchar(100) NOT NULL,			  
                recipients int NOT NULL,
                converted int NOT NULL,
                failed int NOT NULL,
                remaining int NOT NULL,	
                successful int NOT NULL,
                total int NOT NULL,
                create_date datetime NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";
            ";
            return $tables;
        } elseif ($table == $table_player) {
            $tables = "
            CREATE TABLE " . $table_player . " (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                api_id INT NOT NULL,
                player_id varchar(100) NOT NULL,
                identifier varchar(500) NULL,	
                session_count int NOT NULL,				
                test_type varchar(10) NULL,
                device_model varchar(250) NULL,
                device_os varchar(100) NULL, 
                device_type varchar(100) NOT NULL,
                language varchar(100) NULL,
                sdk varchar(100) NULL,	
                PRIMARY KEY (id)				
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";
            ";
            return $tables;
        } elseif ($table == $table_views) {
            $tables = "
            CREATE TABLE " . $table_views . " (
                mc_id BIGINT NOT NULL,
                mc_mobile BIGINT NOT NULL DEFAULT 0,
                mc_website BIGINT NOT NULL DEFAULT 0,
                mc_count BIGINT NOT NULL DEFAULT 0,
                PRIMARY KEY (mc_id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";
            ";
            return $tables;
        } elseif ($table == $table_manage_device) {
            $tables = "
            CREATE TABLE " . $table_manage_device . " (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                player_id varchar(100) DEFAULT '' NOT NULL,
                player_ip varchar(100) DEFAULT '' NOT NULL,
                date_create DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                date_update DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                blocked  TINYINT DEFAULT 0 NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=" . $engine . " DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";
            ";
            return $tables;
        }
    }

    /**
     * Get list extension uninstall
     */
    private static function bamobile_mobiconnector_get_extension($per_page = 100, $page = 1)
    {
        $list_item = array();
        $extensions = @scandir(MOBICONNECTOR_EXTENSIONS_PATH, 1);
        if (is_array($extensions)) {
            $list_extensions = array_diff(@scandir(MOBICONNECTOR_EXTENSIONS_PATH, 1), array('..', '.'));
            @sort($list_extensions);
            $numrows = ($page - 1) * $per_page;
            $count = count($list_extensions);
            if ($per_page < $count) {
                $count = $per_page;
            }
            for ($i = $numrows; $i < $count; $i++) {
                if (is_dir(MOBICONNECTOR_EXTENSIONS_PATH . $list_extensions[$i])) {
                    $contentfolders = array_diff(@scandir(MOBICONNECTOR_EXTENSIONS_PATH . $list_extensions[$i], 1), array('..', '.'));
                    foreach ($contentfolders as $content) {
                        $file = MOBICONNECTOR_EXTENSIONS_PATH . $list_extensions[$i] . '/' . $content;
                        if (is_file($file)) {
                            $item = @file_get_contents($file);
                            if (strlen($item) > 0) {
                                $data = get_plugin_data($file);
                                if (empty($data['Name'])) {
                                    continue;
                                }
                                $extension = $list_extensions[$i] . '/' . $content;
                                $list_item[$extension] = $data['Name'];
                            }
                        }
                    }
                }
            }
        }
        return $list_item;
    }

    /**
     * Check extension active
     */
    private static function bamobile_is_extension_active($extension)
    {
        $current = array();
        $current = get_option('mobiconnector_extensions_active');
        if (!empty($current) && is_string($current)) {
            $current = unserialize($current);
            $current = (array)$current;
        }
        if (is_array($current)) {
            return in_array($extension, $current);
        } else {
            return false;
        }
    }

    /**
     * Activate extension
     */
    private static function bamobile_mobiconnector_active_extension($extension)
    {
        $extension = trim($extension);
        $current = get_option('mobiconnector_extensions_active');
        if (!empty($current) && is_string($current)) {
            $current = unserialize($current);
            $current = (array)$current;
        }
        if (!empty($current) && in_array($extension, $current)) {
            return true;
        }
        $current[] = $extension;
        sort($current);
        update_option('mobiconnector_extensions_active', serialize($current));
    }
}
BAMobileInstall::bamobile_init();
?>