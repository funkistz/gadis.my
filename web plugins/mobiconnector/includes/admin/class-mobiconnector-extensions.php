<?php
@ob_start();
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Manage for Extensions
 */
class BAMobile_Extensions{

    /**
     * Construct for extensions
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){
        add_action( 'admin_menu',array($this,'bamobile_create_menu_in_admin'));
        add_action( 'admin_enqueue_scripts', array( $this, 'bamobile_script_upload' ));
    }

    /**
     * Script upload
     */
    public function bamobile_script_upload(){
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'mobiconnector-extensions' && isset($_GET['extension-tab']) && $_GET['extension-tab'] == 'upload'){
            wp_register_script('mobiconnector_upload_extension_js', plugins_url('assets/js/mobiconnector-extensions.js',MOBICONNECTOR_PLUGIN_FILE), array('jquery'), MOBICONNECTOR_VERSION);	
            $ajax_nonce = wp_create_nonce( "mobiconnector-upload-extesnion-ajax" );           
            $setting = array(
                'post_url'       => admin_url().'admin.php?page=mobiconnector-extensions',
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'security'       => $ajax_nonce
            );	
            wp_localize_script( 'mobiconnector_upload_extension_js', 'mobiconnector_upload_extension_js_params',  $setting  );
            wp_enqueue_script( 'mobiconnector_upload_extension_js' );	
        }
    }

    /**
     * Create menu in Admins
     */
    public function bamobile_create_menu_in_admin(){
        add_submenu_page(
            'mobiconnector-settings',
            __('Extentions'),
            __('Extentions'),
            'manage_options',
            'mobiconnector-extensions',
            array($this,'bamobile_action_create_menu')
        );
    }

    /**
     * Action of menu Mobile in admin wordpress
     */
    public function bamobile_action_create_menu(){
        self::bamobile_process_export();
        $list_page = apply_filters('mobiconnector_custom_page_extension_valid',array());
        $tab = isset($_REQUEST['extension-tab']) ? $_REQUEST['extension-tab'] : 'list';
        $task = isset($_REQUEST['extension-task']) ? $_REQUEST['extension-task'] : false;
        if($tab == 'list'){
            $argsl = array();
            bamobile_mobiconnector_get_template('html-extentions.php',$argsl,__DIR__,__DIR__.'/views/');
        }elseif($tab == 'upload'){
            $argsu = array();
            bamobile_mobiconnector_get_template('html-extentions-upload.php',$argsu,__DIR__,__DIR__.'/views/');
        }elseif($tab == 'active'){
            $argsa = array();
            bamobile_mobiconnector_get_template('html-extensions-active.php',$argsa,__DIR__,__DIR__.'/views/');
        }elseif($tab == 'exist'){
            $argsa = array();
            bamobile_mobiconnector_get_template('html-extensions-exist.php',$argsa,__DIR__,__DIR__.'/views/');
        }elseif($tab !== 'list' && is_array($list_page) && in_array($tab,$list_page)){
            do_action("mobiconnector_process_custom_page_{$tab}");
        }else{
            $argso = array();
            bamobile_mobiconnector_get_template('html-extentions.php',$argso,__DIR__,__DIR__.'/views/');
        }
        if($task == 'upload-file'){
            $this->bamobile_process_upload_file();
        }
    }

    /**
     * Process upload file
     */
    public function bamobile_process_upload_file(){
        $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : false;       
        if ( wp_verify_nonce( $nonce, 'mobiconnector-upload-extension')){
            if (isset($_FILES['extensionzip'])){
                if ($_FILES['extensionzip']['error'] > 0){
                    bamobile_mobiconnector_add_notice(__('Error with your upload, please try again','mobiconnector'),'error');
                    wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
                }elseif($_FILES['extensionzip']['type'] !== 'application/x-zip-compressed'){
                    bamobile_mobiconnector_add_notice(sprintf(__('You must upload a %s file','mobiconnector'),'<b>'.__('*.zip','mobiconnector').'</b>'),'error');
                    wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
                }elseif($_FILES['extensionzip']['size'] > (((float)@ini_get('upload_max_filesize'))*pow(1024,2))) {
                    bamobile_mobiconnector_add_notice(sprintf(__('Maximum upload file size: %d MB'),'error'),(float)@ini_get('upload_max_filesize'),'error'); 
                    wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
                }else{
                    if(@is_writable(MOBICONNECTOR_EXTENSIONS_PATH)){
                        @move_uploaded_file($_FILES['extensionzip']['tmp_name'], MOBICONNECTOR_EXTENSIONS_PATH.$_FILES['extensionzip']['name']);
                        $path = bamobile_mobiconnector_unzip_file(MOBICONNECTOR_EXTENSIONS_PATH.$_FILES['extensionzip']['name']);
                        if($path == "not_exist_ziparchive"){
                            bamobile_mobiconnector_add_notice(sprintf(__('%1$s has %2$s installed. Please contact your server administrator and ask them to enable it, %3$s', 'mobiconnector' ),'<b>ZipArchive</b>','<b>not</b>','<a target="_blank" href="https://secure.php.net/manual/en/ziparchive.open.php">read more</a>'),'error'); 
                            wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
                        }elseif(is_array($path) && key($path) == 'extension_exist'){
                            $value = $path['extension_exist'];   
                            $url = admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=exist&extension-file='.$_FILES['extensionzip']['name'].'&extension-path='.$value;
                            wp_redirect($url);
                        }else{
                            $url = admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=active&extension-file='.$_FILES['extensionzip']['name'].'&extension-path='.$path;
                            wp_redirect($url);
                        } 
                    }else{
                        bamobile_mobiconnector_add_notice(sprintf(__('The Directory %1$s is %2$s writable, you should chmod 777 this directory to writable', 'mobiconnector' ),'<i>'.MOBICONNECTOR_EXTENSIONS_PATH.'/</i>','<b>NOT</b>'),'error'); 
                        wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
                    }
                }
            }
        }else{
            wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload');
        }
    }

    /**
     * Process Export
     */
    public static function bamobile_process_export(){
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : false;       
        if(isset($_GET['mobile_action']) && sanitize_text_field($_GET['mobile_action']) == 'export'){
            if(class_exists('ZipArchive')){
                if ( wp_verify_nonce( $nonce, 'mobiconnector-extensions-export')){                   
                    $zip_file = bamobile_mobiconnector_zip_file();                
                    bamobile_mobiconnector_download_file($zip_file);
                    wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions');
                }else{
                    wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions');
                }
            }else{
                bamobile_mobiconnector_add_notice(sprintf(__('%1$s has %2$s installed. Please contact your server administrator and ask them to enable it, %3$s', 'mobiconnector' ),'<b>ZipArchive</b>','<b>not</b>','<a target="_blank" href="https://secure.php.net/manual/en/ziparchive.open.php">read more</a>'),'error'); 
                wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions');
            }
        }        
    }
}
$BAMobile_Extensions = new BAMobile_Extensions();
@ob_flush();
?>