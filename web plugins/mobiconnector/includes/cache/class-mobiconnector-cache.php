<?php

/**
 * MobiConnector Cache
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.	
}
class BAMobileCacheAPI
{

    /**
     * MobiConnector Cache API construct
     */
    public function __construct()
    {
        $this->init_hooks();
        self::bamobile_process_clear_cache_admin();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks()
    {
        global $wp_version;
        if ($wp_version < '4.8.1') {
            add_filter('rest_pre_serve_request', array($this, 'bamobile_mobiconnector_cache_rest_api'), 10, 4);
        } else {
            add_filter('rest_pre_echo_response', array($this, 'bamobile_mobiconnector_cache_rest_api_echo'), 10, 3);
        }

        add_action('admin_bar_menu', array($this, 'bamobile_mobiconnector_link_to_wp_admin_bar'), 990);
        add_action('admin_enqueue_scripts', array($this, 'bamobile_add_script_cache'));
        add_action('wp_ajax_mobiconnector_clear_mobile_cache', array($this, 'bamobile_mobiconnector_process_clear_cache_ajax'));
    }

    /**
     * Add script to page
     */
    public function bamobile_add_script_cache()
    {
        if (is_admin() && isset($_GET['page']) && $_GET['page'] == 'mobiconnector-settings') {
            wp_register_script('mobiconnector_settings_cache_js', plugins_url('assets/js/mobiconnector-cache.js', MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);
            $ajax_nonce = wp_create_nonce("mobiconnector-clear-mobile-cache");
            $settings = array(
                'ajax_url' => MOBICONNECTOR_AJAX_URL,
                'security' => $ajax_nonce,
                'message' => __('Clear Cache successfully', 'mobiconnector')
            );
            wp_localize_script('mobiconnector_settings_cache_js', 'mobiconnector_settings_cache_js_params', $settings);
            wp_enqueue_script('mobiconnector_settings_cache_js');
        }
    }

    /**
     * Process clear cache by ajax
     */
    public function bamobile_mobiconnector_process_clear_cache_ajax()
    {
        check_ajax_referer('mobiconnector-clear-mobile-cache', 'security');
        bamobile_mobiconnector_clear_mobile_cache();
        wp_die();
    }

    /**
     * Clear Cache with wp admin bar
     */
    public static function bamobile_process_clear_cache_admin()
    {
        if (isset($_REQUEST['mobile_action']) && 'mobile_clear_cache' === sanitize_text_field($_REQUEST['mobile_action'])) {
            $nonce = isset($_REQUEST['bamobile_cache_none']) ? esc_attr($_REQUEST['bamobile_cache_none']) : '';
            if (!wp_verify_nonce($nonce, 'mobiconnector_clear_mobile_cache')) {
                return false;
            } else {
                bamobile_mobiconnector_clear_mobile_cache();
                if (isset($_SERVER['HTTP_REFERER'])) {
                    if (wp_redirect($_SERVER['HTTP_REFERER'])) {
                        exit;
                    }
                } else {
                    if (wp_redirect(admin_url())) {
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Create menu cache in wp admin bar
     */
    public function bamobile_mobiconnector_link_to_wp_admin_bar($wp_admin_bar)
    {
        $user_id = get_current_user_id();
        if (is_super_admin($user_id)) {
            $args = array(
                'id' => 'mobile_cache',
                'title' => __('Mobile App Cache', 'mobiconnector'),
                'meta' => array('class' => 'mobiconnector-cache')
            );
            $wp_admin_bar->add_node($args);

            $args = array();
            $clear_cache_nonce = wp_create_nonce('mobiconnector_clear_mobile_cache');
            $host = sanitize_text_field($_SERVER['HTTP_HOST']);
            $requesturi = sanitize_text_field($_SERVER['REQUEST_URI']);
            $url = $host . $requesturi;
            $args = array(
                'mobile_action' => 'mobile_clear_cache',
                'bamobile_cache_none' => $clear_cache_nonce
            );
            $urladd = add_query_arg($args, $url);
            array_push($args, array(
                'id' => 'mobile_clear_cache',
                'title' => __('Clear Cache', 'mobiconnector'),
                'href' => $urladd,
                'parent' => 'mobile_cache',
                'meta' => array(
                    'class' => 'mobiconnector-cache-children'
                ),
            ));

            array_push($args, array(
                'id' => 'mobile_settings_cache',
                'title' => __('Setting Cache', 'mobiconnector'),
                'href' => get_admin_url() . '?page=mobiconnector-settings&mtab=mobile-cache',
                'parent' => 'mobile_cache',
                'meta' => array('class' => 'mobiconnector-cache-children'),
            ));
            sort($args);
            foreach ($args as $each_arg) {
                $wp_admin_bar->add_node($each_arg);
            }
        }
    }

    /**
     * Use Cache Of MobiConnector
     * 
     * @param bool              $served  Whether the request has already been served.
     * @param WP_REST_Response  $response Result to send to the client.
     * @param WP_REST_Request   $request Request used to generate the response.
     * @param WP_REST_Server    $handler ResponseHandler instance.     
     * 
     * @return WP_REST_Response Response object.
     */
    public function bamobile_mobiconnector_cache_rest_api($served, $result, $request, $server)
    {
        if (bamobile_mobiconnector_is_rest_api()) {
            $redirecturi = '';
            $request_url = $_SERVER["REQUEST_URI"];
            $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : false;
            $querystring = trim($querystring, '&');
            $querystring = trim($querystring, '?');
            $redirecturi = str_replace($querystring, "", $request_url);
            $listparams = array();
            if (strpos($querystring, '&') !== false) {
                $listparams = explode('&', $querystring);
                $indexparams = '';
                foreach ($listparams as $key => $params) {
                    if (strpos($params, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
                        $param_name = substr($params, 0, strpos($params, '='));
                        if ($param_name === 'time' || $param_name === 'mobile_path') {
                            $indexparams = $key;
                        }
                    }
                }
                if ($indexparams !== '') {
                    unset($listparams[$indexparams]);
                }
                $querystring = implode('&', $listparams);
            } else {
                if (strpos($querystring, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
                    $param_name = substr($querystring, 0, strpos($querystring, '='));
                    if ($param_name === 'time' || $param_name === 'mobile_path') {
                        $querystring = false;
                    }
                }
            }
            if (strpos($redirecturi, '?') !== false) {
                $redirecturi = substr($redirecturi, 0, strpos($redirecturi, '?'));
            }
            $key = $redirecturi;
            $key = trim($key, '&');
            $key = trim($key, '?');
            if (!empty($querystring)) {
                $key = $redirecturi . '?' . $querystring;
            }
            $key = md5($key);
            $use_cache = get_option('mobiconnector_settings-use-cache-mobile');
            $core = new BAMobileCoreCacheAPI();
            $checkexpired = $core->bamobile_check_expired_data_in_database($key);
            if (empty($checkexpired) && $use_cache == 1) {
                if (!is_wp_error($result) && !bamobile_mobiconnector_is_missing_and_invalid_params($result)) {
                    if ($core->bamobile_check_data_in_database($key)) {
                        $core->bamobile_update_item_to_database($key, base64_encode(serialize($result->get_data())));
                    } else {
                        $core->bamobile_add_item_to_database($key, base64_encode(serialize($result->get_data())));
                    }
                }
                return $served;
            }
            return $served;
        } else {
            return $served;
        }
    }

    /**
     * Use Cache Of MobiConnector
     * 
     * @param WP_REST_Response  $response Result to send to the client.
     * @param WP_REST_Server    $handler ResponseHandler instance.
     * @param WP_REST_Request   $request Request used to generate the response.
     * 
     * @return WP_REST_Response Response object.
     */
    public function bamobile_mobiconnector_cache_rest_api_echo($result, $server, $request)
    {
        if (bamobile_mobiconnector_is_rest_api()) {
            $redirecturi = '';
            $request_url = $_SERVER["REQUEST_URI"];
            $querystring = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : false;
            $querystring = trim($querystring, '&');
            $querystring = trim($querystring, '?');
            $redirecturi = str_replace($querystring, "", $request_url);
            $listparams = array();
            if (strpos($querystring, '&') !== false) {
                $listparams = explode('&', $querystring);
                $indexparams = '';
                foreach ($listparams as $key => $params) {
                    if (strpos($params, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
                        $param_name = substr($params, 0, strpos($params, '='));
                        if ($param_name === 'time' || $param_name === 'mobile_path') {
                            $indexparams = $key;
                        }
                    }
                }
                if ($indexparams !== '') {
                    unset($listparams[$indexparams]);
                }
                $querystring = implode('&', $listparams);
            } else {
                if (strpos($querystring, 'time') !== false || strpos($querystring, 'mobile_path') !== false) {
                    $param_name = substr($querystring, 0, strpos($querystring, '='));
                    if ($param_name === 'time' || $param_name === 'mobile_path') {
                        $querystring = false;
                    }
                }
            }
            if (strpos($redirecturi, '?') !== false) {
                $redirecturi = substr($redirecturi, 0, strpos($redirecturi, '?'));
            }
            $key = $redirecturi;
            $key = trim($key, '&');
            $key = trim($key, '?');
            if (!empty($querystring)) {
                $key = $redirecturi . '?' . $querystring;
            }
            $key = md5($key);
            $use_cache = get_option('mobiconnector_settings-use-cache-mobile');
            $core = new BAMobileCoreCacheAPI();
            $checkexpired = $core->bamobile_check_expired_data_in_database($key);
            if (empty($checkexpired) && $use_cache == 1) {
                if (!is_wp_error($result) && !bamobile_mobiconnector_is_missing_and_invalid_params($result)) {
                    if ($core->bamobile_check_data_in_database($key)) {
                        $core->bamobile_update_item_to_database($key, base64_encode(serialize($result)));
                    } else {
                        $core->bamobile_add_item_to_database($key, base64_encode(serialize($result)));
                    }
                }
                return $result;
            }
            return $result;
        } else {
            return $result;
        }
    }
}
$BAMobileCacheAPI = new BAMobileCacheAPI();
?>