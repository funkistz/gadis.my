<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Process Intro plugin
 */
class BAMobileIntroPlugin{

    /**
     * Construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function init_hooks(){
        $checkMobiManage = isset($_COOKIE['mobiconnector_check_mobisettings_manage']) ? sanitize_text_field($_COOKIE['mobiconnector_check_mobisettings_manage']) : 0;
        $checkMobiExtension = isset($_COOKIE['mobiconnector_check_mobisettings_extension']) ? sanitize_text_field($_COOKIE['mobiconnector_check_mobisettings_extension']) : 0;
        $checkWooSettings = isset($_COOKIE['mobiconnector_check_woosettings_manage']) ? sanitize_text_field($_COOKIE['mobiconnector_check_woosettings_manage']) : 0;
        $checkWooNotification = isset($_COOKIE['mobiconnector_check_woosettings_notification']) ? sanitize_text_field($_COOKIE['mobiconnector_check_woosettings_notification']) : 0;
        if(is_admin()){
            add_action('admin_init',array($this,'bamobile_custom_menu_class') );
            add_action('admin_enqueue_scripts',array($this,'bamobile_intro_style'));
            add_action('admin_enqueue_scripts',array($this,'bamobile_intro_script'));
        }
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'mobiconnector-settings' && ($checkMobiManage === 0 || empty($checkMobiManage))){ 
            add_action('admin_enqueue_scripts',array($this,'bamobile_pointers_wordpress_mobile_settings'));
            self::bamobile_set_cookie_for_intro('mobiconnector_check_mobisettings_manage');
        }
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'wooconnector' && ($checkWooSettings === 0 || empty($checkWooSettings))){   
            add_action('admin_enqueue_scripts',array($this,'bamobile_pointers_wordpress_woo_settings'));  
            self::bamobile_set_cookie_for_intro('mobiconnector_check_woosettings_manage');        
        }
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'woo-notifications' && ($checkWooNotification === 0 || empty($checkWooNotification))){ 
            add_action('admin_enqueue_scripts',array($this,'bamobile_pointers_wordpress_woo_notification'));   
            self::bamobile_set_cookie_for_intro('mobiconnector_check_woosettings_notification');         
        }
        add_action('wp_ajax_bamobile_disable_menu_intros',array($this,'bamobile_disable_menu_intro'));
        add_action('wp_ajax_bamobile_disable_tutorial_intros',array($this,'bamobile_disable_tutorial_intro'));
        register_deactivation_hook(MOBICONNECTOR_PLUGIN_FILE,array(__CLASS__,'bamobile_delete_cookie_for_intro'));
        
    }    

    /**
     * Setcookie for intro
     * 
     * @since 1.1.5
     * 
     * @param string $cookieName        name of cookie
     * 
     */
    public static function bamobile_set_cookie_for_intro($cookieName){
        setcookie($cookieName, '1', time() + 311040000);
    }

    /**
     * Delete all cookie on intro
     * 
     * @since 1.1.5
     * 
     */
    public static function bamobile_delete_cookie_for_intro(){
        $listCookie = ['mobiconnector_check_admin_intro','mobiconnector_check_mobisettings_manage','mobiconnector_check_mobisettings_extension','mobiconnector_check_woosettings_manage','mobiconnector_check_woosettings_notification'];
        foreach($listCookie as $cookieName){
            setcookie($cookieName, '0', time() - 311040000);
        }
    }

    /**
     * Disable Tutorial Intro
     */
    public function bamobile_disable_tutorial_intro(){
        check_ajax_referer( 'bamobile-clear-intros', 'security' );
        update_option('mobiconnector_check_mobisettings_intro',1);
        wp_die();
    }

    /**
     * Disable Menu intro
     */
    public function bamobile_disable_menu_intro(){
        check_ajax_referer( 'bamobile-clear-menu-intros', 'security' );
        update_option('mobiconnector_check_admin_intro',1);
        wp_die();
    }

    /**
     * Intro Woo Notifications
     */
    public function bamobile_pointers_wordpress_woo_notification(){
        $pointers = array(
            array(
                'id'       => 'pointer8',
                'screen'   => 'mobile-app_page_woo-notifications',
                'target'   => '#wooconnector-onesignal-settings-api',
                'title'    => __('Onesignal API Settings for Ecommerce App','mobiconnector'),
                'content'  => sprintf(__('%1$s %2$sSettings Onesignal API for an ecommerce application, No News application  %3$s %4$sYou can use an Onesignal API setting like Mobile Connector >> Manage >> Blog Notification %5$s %6$s','mobiconnector'),'<ul class="mobiconnector-intro-ul">','<li>','</li>','<li>','</li>','</ul>'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer9',
                'screen'   => 'mobile-app_page_woo-notifications',
                'target'   => '#wooconnector-onesignal-push-new',
                'title'    => __('Tab New Notification','mobiconnector'),
                'content'  =>sprintf(__('%1$s %2$sYou must setup Onesignal API before use this option  %3$s %4$sDelivery a notification to customer which installed your mobile application %5$s %6$s','mobiconnector'),'<ul class="mobiconnector-intro-ul">','<li>','</li>','<li>','</li>','</ul>'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer10',
                'screen'   => 'mobile-app_page_woo-notifications',
                'target'   => '#wooconnector-onesignal-list-send',
                'title'    => __('Manage Sent Notifications in Ecommerce App','mobiconnector'),
                'content'  => __('Manage, track, view, report about all notifications sent to customer','mobiconnector'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer11',
                'screen'   => 'mobile-app_page_woo-notifications',
                'target'   => '#wooconnector-onesignal-all-users',
                'title'    => __('Users Installed Ecommerce App','mobiconnector'),
                'content'  => __('Manage devices which installed your Ecommerce  application','mobiconnector'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
        );
        new BAMobileIntroPluginCore( $pointers );
    }

    /**
     * Intro Woo Settings
     */
    public function bamobile_pointers_wordpress_woo_settings(){
        $pointers = array(
            array(
                'id'       => 'pointer6',
                'screen'   => 'toplevel_page_wooconnector',
                'target'   => '#wooconnector-general-settings',
                'title'    => __("Ecommerce App's Settings",'mobiconnector'),
                'content'  => sprintf(__('%1$s %2$sSetup Email received in Contact page, Countries/States in Billing/Shipping form, Search page %3$s %4$sThere settings only work if an ecommerce app have there features %5$s %6$s','mobiconnector'),'<ul class="mobiconnector-intro-ul">','<li>','</li>','<li>','</li>','</ul>'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer7',
                'screen'   => 'toplevel_page_wooconnector',
                'target'   => '#wooconnector-settings-currency',
                'title'    => __('Multiple Currencies','mobiconnector'),
                'content'  => sprintf(__('%1$s %2$sManage Currencies: Rate, format of a currency.  %3$s %4$sThis feature only works if an ecommerce app inclued multiple Currencies feature %5$s %6$s','mobiconnector'),'<ul class="mobiconnector-intro-ul">','<li>','</li>','<li>','</li>','</ul>'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
        );
        new BAMobileIntroPluginCore( $pointers );
    }

    /**
     * Add pointer to admin
     */
    public function bamobile_pointers_wordpress_mobile_settings(){
        $pointers = array(
            array(
                'id'       => 'pointer1',
                'screen'   => 'toplevel_page_mobiconnector-settings',
                'target'   => '#mobiconnector-general-settings',
                'title'    => __('General Settings','mobiconnector'),
                'content'  => __('Setting Custom Post type, Google Analytics, Date format, Default Language (RTL or LTL), Admob, Maintenance Mode, Google Map, Social Login. Some settings only work if the application included this option','mobiconnector'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer2',
                'screen'   => 'toplevel_page_mobiconnector-settings',
                'target'   => '#mobiconnector-settings-onsignal',
                'title'    => __('Notification for Blog/News application','mobiconnector'),
                'content'  => __("Notifications Manager, Onesignal's Settings, Sent Notification for Blog/News application",'mobiconnector'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            ),
            array(
                'id'       => 'pointer3',
                'screen'   => 'toplevel_page_mobiconnector-settings',
                'target'   => '#mobiconnector-settings-cache',
                'title'    => __('Tab Cache','mobiconnector'),
                'content'  => sprintf(__('%1$s %2$sSetting Cache on Mobile Application. %3$s %4$sThis feature works with every application which is using Wordpres Rest Api %5$s %6$s','mobiconnector'),'<ul class="mobiconnector-intro-ul">','<li>','</li>','<li>','</li>','</ul>'),
                'position' => array(
                    'edge'  => 'top',
                    'align' => 'top'
                ),
                'bamobile_type' => 'menu_top'
            )   
        );
        new BAMobileIntroPluginCore( $pointers );
    }

    /**
     * Add custom class to Admin menu
     */
    public function bamobile_custom_menu_class(){
        global $menu;
        if(!empty($menu)){
            foreach( $menu as $key => $value ){            
                if( 'Mobile Connector' == $value[0] ){
                    $menu[$key][4] .= " bamobile-mobiconnector-intro-js bamobile_message_mobiconnector";
                }elseif( 'Mobile App' == $value[0] ){
                    $menu[$key][4] .= " bamobile-mobiconnector-intro-js bamobile_message_wooconnector";
                }
            }
        }
    }

    /**
     * Style Intro
     */
    public function bamobile_intro_style(){
        $screen = get_current_screen();
        $checkAdmin = isset($_COOKIE['mobiconnector_check_admin_intro']) ? sanitize_text_field($_COOKIE['mobiconnector_check_admin_intro']) : 0;
        if(is_admin() && isset($screen->id) && ($screen->id == 'dashboard' || $screen->id == 'plugins') && ($checkAdmin === 0 || empty($checkAdmin))){
            wp_register_style( 'mobiconnector-intro-style', plugins_url('assets/css/mobiconnector-intro.css',MOBICONNECTOR_PLUGIN_FILE), array(), MOBICONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'mobiconnector-intro-style' );
        }
    }

    /**
     * Script Intro
     */
    public function bamobile_intro_script(){
        $screen = get_current_screen();
        $checkAdmin = isset($_COOKIE['mobiconnector_check_admin_intro']) ? sanitize_text_field($_COOKIE['mobiconnector_check_admin_intro']) : 0;
        if(is_admin() && isset($screen->id) && ($screen->id == 'dashboard' || $screen->id == 'plugins') && ($checkAdmin === 0 || empty($checkAdmin))){
            $nonce = wp_create_nonce('bamobile-clear-menu-intros');
            wp_register_script('mobiconnector_intro_js', plugins_url('assets/js/mobiconnector-custom-intro.js',MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);	
            $settings = array(
                'ajax_url' => MOBICONNECTOR_AJAX_URL,
                'bamobile_check_message' => array(
                    'admin.php?page=azstore-settings' => 'azstore',
                    'admin.php?page=cellstore-settings' => 'cellstore',
                    'admin.php?page=modernshop-settings' => 'modernshop',
                    'admin.php?page=oli-settings' => 'olike',
                    'admin.php?page=mobiconnector-settings' => 'mobile_manage',
                    'admin.php?page=mobiconnector-extensions' => 'mobile_extensions',
                    'admin.php?page=mobi-language' => 'mobile_languages',
                    'admin.php?page=wooconnector' => 'woo_settings',
                    'admin.php?page=woo-notifications' => 'woo_notice',
                    'admin.php?page=brand' => 'woo_brands',
                    'admin.php?page=popup' => 'woo_popup',
                    'admin.php?page=woo-slider' => 'woo_slider',
                    'admin.php?page=woo-language' => 'woo_languages'
                ),
                'security'  => $nonce,
                'bamobile_message_mobiconnector' => array(
                    'heading' => __("Setup Mobile Application's Core",'mobiconnector'),
                    'message' => sprintf(__('%1$s %2$sSetting Custom Post type, Google Analytics, Date format, Default Language (RTL or LTL), Admob, Maintenance Mode, Google Map, Social Login.%3$s %4$s'."Notifications Manager, Onesignal's Settings, Sent Notification for Blog/News application".'%5$s %6$s Setting Cache on Mobile Application. %7$s %8$s Email received when user flagged item, product, comment. %9$s %10$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                    'children'  => array(
                        'mobile_manage' => array(
                            'heading' => __('General Settings','mobiconnector'),
                            'message' => __('Setting Custom Post type, Google Analytics, Date format, Default Language (RTL or LTL), Admob, Maintenance Mode, Google Map, Social Login for all mobile application','mobiconnector'),
                        ),
                        'mobile_extensions' => array(
                            'heading' => __('Extensions Manager','mobiconnector'),
                            'message' => __('Manage extensions which used to extend features, functions on a mobile application. The developer can develope their plugins based our document on https://taydoapp.com','mobiconnector'),
                        ),
                        'mobile_languages' => array(
                            'heading' => __('Multiple Languages Settings','mobiconnector'),
                            'message' => sprintf(__('%1$s %2$sAdd, delete, deactive, setting languages which you want use them on your mobile application. %3$s %4$sThis option work with %7$swmpl%8$s and %9$sq-translate%10$s in ecommerce application, No News application %5$s %6$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','</ul>','<b>','</b>','<b>','</b>'),
                        )
                    )
                ),
                'bamobile_message_wooconnector' => array(
                    'heading' => __('Customize Mobile application','mobiconnector'),
                    'message' => sprintf(__('%1$s %2$sAllow to change any Text, Images, Brand, Icons, Content on mobile application. %3$s %4$s'."Notifications Manager, Onesignal's Settings, Sent Notification.".' %5$s %6$sThere Settings only work for ecommerce application, No News application %7$s %8$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                    'children'  => array(
                        'woo_settings' => array(
                            'heading' => __("Setup Ecommerce App's Core",'mobiconnector'),
                            'message' => __('Setup Email received in Contact page, Countries/States in Billing/Shipping form, Search page Manage Multiple Currencies','mobiconnector'),
                        ),
                        'woo_notice' => array(
                            'heading' => __("Ecommerce App's Notifications ",'mobiconnector'),
                            'message' => sprintf(__('%1$s %2$s'."Onesignal's Settings, Sent Notification, Notifications Manager for an Ecommerce application".' %3$s %4$sManage devices which installed your application in "All Users" section %5$s %6$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','</ul>')
                        ),
                        'woo_brands' => array(
                            'heading' => __('Brands Manager','mobiconnector'),
                            'message' => __('Add, delete, deactive Brands in a mobile application if the Ecommerce application have this feature','mobiconnector'),
                        ),
                        'woo_popup' => array(
                            'heading' => __('Popup on Homepage','mobiconnector'),
                            'message' => __('Change Image, Link, Time for Popup in Homepage if the Ecommerce application have this feature','mobiconnector'),
                        ),
                        'woo_slider' => array(
                            'heading' => __('Slideshow on Homepage','mobiconnector'),
                            'message' => __('Add, edit, delete Images, Link for slideshow in Homepage','mobiconnector'),
                        ),
                        'woo_languages' => array(
                            'heading' => __('Multiple Languages Settings','mobiconnector'),
                            'message' => sprintf(__('It is linked to %1$s Mobile Connector >> Languages %2$s menu','mobiconnector'),'<b>','</b>'),
                        ),
                        'azstore' => array(
                            'heading' => __('Customize AZStore Application','mobiconnector'),
                            'message' => sprintf(__('%1$s %2$sChange any TEXT in AZStore application. %3$s %4$sContain content of Terms of use,  Privacy policy, About us, Contact Us page %5$s %6$sShare App link, Social link %7$s %8$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                        ),
                        'modernshop' => array(
                            'heading' => __('Customize Modernshop Application','mobiconnector'),
                            'message' => sprintf(__('%1$s %2$sChange any TEXT in Modernshop application. %3$s %4$sContain content of Terms of use,  Privacy policy, About us, Contact Us page %5$s %6$sShare App link, Social link %7$s %8$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                        ),
                        'cellstore' => array(
                            'heading' => __('Customize CellStore Application','mobiconnector'),
                            'message' => sprintf(__('%1$s %2$sChange any TEXT in CellStore application. %3$s %4$sContain content of Terms of use,  Privacy policy, About us, Contact Us page %5$s %6$sShare App link, Store Location %7$s %8$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                        ),
                        'olike' => array(
                            'heading' => __('Customize Olike Application','mobiconnector'),
                            'message' => sprintf(__('%1$s %2$sChange any TEXT in Olike application. %3$s %4$sContain content of Terms of use,  Privacy policy, About us, Contact Us page %5$s %6$sShare App link, Store Location %7$s %8$s','mobiconnector'),'<ul>','<li>','</li>','<li>','</li>','<li>','</li>','</ul>'),
                        )
                    )
                )
            );
            $settings = apply_filters('mobiconnector_intros_messages',$settings);	
            wp_localize_script( 'mobiconnector_intro_js', 'mobiconnector_intro_js_params',  $settings  );
            wp_enqueue_script( 'mobiconnector_intro_js' );
            self::bamobile_set_cookie_for_intro('mobiconnector_check_admin_intro');
        }
    }
}
$BAMobileIntroPlugin = new BAMobileIntroPlugin();
?>