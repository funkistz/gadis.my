<?php

/**
 * @package BA Design Billing/Shipping/Register Form for Mobile Application
 */
/**
 * Plugin Name: BA Design Billing/Shipping/Register Form for Mobile Application
 * Plugin URL: https://taydotech.com
 * Description: BA Design Billing/Shipping/Register Form for Mobile Application
 * Version: 1.0.1
 * Author: TayDoTech
 * Author URL: https://taydotech.com
 * Text Domain: ba-mobile-form
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
    exit;
}

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

if (is_plugin_active('woocommerce/woocommerce.php') == false) {
    function sample_admin_notice__success()
    {
        ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong><?php echo __('BA Design Billing/Shipping/Register Form for Mobile Application requires WooCommerce to be activated', 'ba-mobile-form'); ?></strong></p>
            </div>
            <?php

        }
        add_action('admin_notices', 'sample_admin_notice__success');
        return 0;
    }



    define('BA_FORM_PLUGIN_PATH', plugin_dir_path(__FILE__));
    define('BA_FORM_PLUGIN_URL', plugin_dir_url(__FILE__));
    if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
        require_once dirname(__FILE__) . '/vendor/autoload.php';
    }
    function ba_design_form_activate()
    {
        DesignForm\Base\BAFormActivate::baform_activate();
    }
    register_activation_hook(__FILE__, 'ba_design_form_activate');
    function ba_design_form_deactivate()
    {
        DesignForm\Base\BAFormDeactivate::baform_deactivate();
    }
    register_deactivation_hook(__FILE__, 'ba_design_form_deactivate');
    if (class_exists('DesignForm\\BAFormInit')) {
        DesignForm\BAFormInit::ba_register_services();
    }

    function get_country_ba($name_id, $label, $value_country, $required)
    {
        global $woocommerce;
        $countries_obj = new WC_Countries();
        $countries = $countries_obj->__get('countries');
        woocommerce_form_field($name_id, array(
            'type' => 'select',
            'label' => $label,
            'required' => $required,
            'options' => $countries,
            'default' => $value_country,
        ));
    }
    function get_country_ba_profile($name_id, $value_country)
    {
        global $woocommerce;
        $countries_obj = new WC_Countries();
        $countries = $countries_obj->__get('countries');
        woocommerce_form_field($name_id, array(
            'type' => 'select',
            'options' => $countries,
            'default' => $value_country,
        ));
    }
    // if(function_exists('bamobile_register_activation_hook_extensions')){
    // 	bamobile_register_activation_hook_extensions( __FILE__, 'ba_design_form_activate' );
    // }

    $check_option = get_option('baform_checkdata');
    if (empty($check_option)) {
        add_action('woocommerce_init', 'baform_add_option');
    }
    function baform_add_option()
    {
        $locale_fields = array(
            'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_email', 'billing_phone', 'billing_first_name',
        );
        $profile_filed = array(
            'billing_user_login' => array(
                'label' => __('Username', 'ba-mobile-booking'),
                'required' => 0,
                'class' => array(),
                'validate' => array(),
                'type' => 'text'
            ),
            'billing_nicename' => array(
                'label' => __('Nickname (required)', 'ba-mobile-booking'),
                'required' => 1,
                'class' => array(),
                'validate' => array(),
                'type' => 'text'
            ),
            'billing_display_name' => array(
                'label' => __('Display name publicly as', 'ba-mobile-booking'),
                'required' => 1,
                'class' => array(),
                'validate' => array(),
                'type' => 'select'
            ),
            'billing_url' => array(
                'label' => __('Website', 'ba-mobile-booking'),
                'required' => 0,
                'class' => array(),
                'validate' => array(),
                'type' => 'url'
            ),
            'billing_description' => array(
                'label' => __('Biographical Info', 'ba-mobile-booking'),
                'required' => 0,
                'class' => array(),
                'validate' => array(),
                'type' => 'textarea'
            ),
            'billing_password' => array(
                'label' => __('Password', 'ba-mobile-booking'),
                'required' => 0,
                'class' => array(),
                'validate' => array(),
                'type' => 'password'
            ),
            // 'avatar' => array(
            //  'label'=> __('Profile Picture','ba-mobile-booking'),
            //  'required' => false,
            //  'class' => array(),
            //  'validate'=> array(),
            //  'type' => 'avatar'
            // ),
        );
        $required_fields = array('billing_last_name', 'billing_country', 'billing_address_1', 'billing_city', 'billing_email', 'billing_phone', 'billing_first_name');
        $countries = new WC_Countries();
        $fields = $countries->get_address_fields($countries->get_base_country(), 'billing_');
        $fields = array_merge($fields, $profile_filed);
        $i = 0;
        foreach ($fields as $name => $options) :
            //if (in_array($name, $locale_fields)) {
        if (isset($name)) {
            $ba_design[$i]['name_id'] = $name;
        } else {
            $ba_design[$i]['name_id'] = "";
        }
        if (isset($options['type'])) {
            if ($ba_design[$i]['name_id'] === 'billing_last_name' || $ba_design[$i]['name_id'] === 'billing_first_name' || $ba_design[$i]['name_id'] === 'billing_company' || $ba_design[$i]['name_id'] === 'billing_address_1' || $ba_design[$i]['name_id'] === 'billing_address_2' || $ba_design[$i]['name_id'] === 'billing_city' || $ba_design[$i]['name_id'] === 'billing_postcode') {
                $ba_design[$i]['type'] = 'text';
            } else {
                $ba_design[$i]['type'] = $options['type'];
            }
        } else {
            $ba_design[$i]['type'] = 'text';
        }
        if (isset($options['label'])) {
            $ba_design[$i]['label'] = $options['label'];
        } else {
            $ba_design[$i]['label'] = "";
        }
        if (isset($options['required'])) {
            if (($options['required'] == true) && ($ba_design[$i]['type'] !== 'state')) {
                $ba_design[$i]['required_check'] = 1;
            } elseif ($ba_design[$i]['type'] === 'state') {
                $ba_design[$i]['required_check'] = 0;
            } else {
                $ba_design[$i]['required_check'] = 0;
            }
        } else {
            if (in_array($name, $required_fields)) {
                $ba_design[$i]['required_check'] = 1;
            } else {
                $ba_design[$i]['required_check'] = 0;
            }

        }

        if (isset($options['required_billing'])) {
            $ba_design[$i]['required_billing'] = 1;
        } else {
            if ($name !== 'billing_user_login' && $name !== 'billing_nicename' && $name !== 'billing_display_name' && $name !== 'billing_url' && $name !== 'billing_description' && $name !== 'billing_password') {
                $ba_design[$i]['required_billing'] = 1;
            } else {
                $ba_design[$i]['required_billing'] = 0;
            }

        }

        if (isset($options['required_shipping'])) {
            $ba_design[$i]['required_shipping'] = 1;
        } else {
            if ($name !== 'billing_user_login' && $name !== 'billing_nicename' && $name !== 'billing_display_name' && $name !== 'billing_url' && $name !== 'billing_description' && $name !== 'billing_password') {
                $ba_design[$i]['required_shipping'] = 1;
            } else {
                $ba_design[$i]['required_shipping'] = 0;
            }

        }

        if (isset($options['required_register'])) {
            $ba_design[$i]['required_register'] = 0;
        } else {
            if ($name !== 'billing_nicename' && $name !== 'billing_display_name' && $name !== 'billing_url' && $name !== 'billing_description' && $name !== 'billing_company' && $name !== 'billing_country' && $name !== 'billing_address_1' && $name !== 'billing_address_2' && $name !== 'billing_city' && $name !== 'billing_state' && $name !== 'billing_postcode' && $name !== 'billing_phone') {
                $ba_design[$i]['required_register'] = 1;
            } else {
                $ba_design[$i]['required_register'] = 0;
            }
        }
        if (isset($options['required_profile'])) {
            $ba_design[$i]['required_profile'] = 0;
        } else {
            if ($name !== 'billing_last_name' && $name !== 'billing_first_name' && $name !== 'billing_email' && $name !== 'billing_user_login' && $name !== 'billing_nicename' && $name !== 'billing_display_name' && $name !== 'billing_url' && $name !== 'billing_description' && $name !== 'billing_password') {
                $ba_design[$i]['required_profile'] = 0;
            } else {
                $ba_design[$i]['required_profile'] = 1;
            }

        }

        if (isset($options['position_field'])) {
            if ($name === 'billing_first_name') {
                $ba_design[$i]['position_field'] = 'form-row-first';
            } elseif ($name === 'billing_last_name') {
                $ba_design[$i]['position_field'] = 'form-row-last';
            } else {
                $ba_design[$i]['position_field'] = 'form-row-wide';
            }
        } else {
            if ($name === 'billing_first_name') {
                $ba_design[$i]['position_field'] = 'form-row-first';
            } elseif ($name === 'billing_last_name') {
                $ba_design[$i]['position_field'] = 'form-row-last';
            } else {
                $ba_design[$i]['position_field'] = 'form-row-wide';
            }
        }
        $ba_design[$i]['option_field'] = serialize("");
        if ($name === 'billing_state') {
            $ba_design[$i]['country_belong_to'] = 'billing_country';
        } else {
            $ba_design[$i]['country_belong_to'] = '';
        }
        if ($name === 'billing_country') {
            $ba_design[$i]['country_has_state'] = 'billing_state';
        } else {
            $ba_design[$i]['country_has_state'] = '';
        }

        $i++;
            //}
        endforeach;
        $strSerialize = serialize($ba_design);
        update_option('ba_design_form', $strSerialize);
        update_option('baform_checkdata', 1);
    }

    if (file_exists(dirname(__FILE__) . '/functions/functions.php')) {
        require_once dirname(__FILE__) . '/functions/functions.php';
    }

    $lang = BA_FORM_PLUGIN_PATH . '/languages';
    load_theme_textdomain('ba-mobile-form', $lang);
    ?>