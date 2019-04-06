<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Get languages if qTranslate install
 */
class BAMobileLanguage{

    /**
     * Url of API
     */
    private $rest_url = "mobiconnector/languages";

    /**
     * BAMobileLanguage construct
     */
    public function __construct(){
        $this->register_hooks();       
        self::bamobile_mobiconnector_remove_page_in_qtranslatex();
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
    public function register_api_hooks(){
        register_rest_route( $this->rest_url, '/getlanguages', array(
                    'methods'         => 'GET',
                    'callback'        => array( $this, 'bamobile_getlanguages' ),	
                    'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),			
                    'args'            => array(		
                    ),					
            ) 
        );
    }

    /**
     * Remove type page to settings qtranslate if qtranslate active
     */
    public static function bamobile_mobiconnector_remove_page_in_qtranslatex(){
        if(is_plugin_active('qtranslate-x/qtranslate.php')){          
            $option = get_option('qtranslate_post_type_excluded');
            $type = array('page');
            if(empty($option)){
                $option = array();
            }else{
                if(in_array('page',$option)){
                    return true;
                }
            }		
            $addoption = array_merge($option,$type);
            update_option('qtranslate_post_type_excluded',$addoption);
        }
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
     * Get all language qTranslate support
     * 
     * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
     */
    public function bamobile_getlanguages($request){
        $languages = array();            
        if(is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php')){               
            $listlangs = bamobile_mobiconnector_get_wpml_list_languages();
            $languagesdisplaymode = get_option('mobiconnector_settings-languages-wpml-display-mode');
            $current_language = '';
            if(isset($_GET['mobile_lang']) && !isset($_GET['lang'])){
                $current_language = (isset($_GET['mobile_lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['mobile_lang']))) ?  sanitize_text_field($_GET['mobile_lang']) : bamobile_mobiconnector_get_default_wpml_languages();
            }elseif(!isset($_GET['mobile_lang']) && isset($_GET['lang'])){
                $current_language = (isset($_GET['lang']) && bamobile_mobiconnector_is_languages_enable(sanitize_text_field($_GET['lang']))) ?  sanitize_text_field($_GET['lang']) : bamobile_mobiconnector_get_default_wpml_languages();
            }else{
                $current_language = bamobile_mobiconnector_get_default_wpml_languages();
            }
            foreach($listlangs as $wplang){
                $selectwp = '';
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
            }
        }elseif(is_plugin_active('qtranslate-x/qtranslate.php')){
            $languagesdisplaymode = get_option('mobiconnector_settings-languages-display-mode');
            $lang = bamobile_mobiconnector_get_qtranslate_enable_languages();   
            if(empty($lang)){
                return array();
            }
            $listlanguages = qtranxf_default_language_name();
            foreach($lang as $la){
                $select = '';
                if(!empty($languagesdisplaymode)){
                    if(isset($languagesdisplaymode[$la])){
                        $select = $languagesdisplaymode[$la];
                    }                
                }
                if(empty($select) || $select == ''){
                    $select = 'ltr';
                }
                $languages[] = array(
                    'language' => $la,
                    'name'     => $listlanguages[$la],
                    'display_mode' => $select
                );
            } 
        }else{
            return array();
        }       
        return $languages;
    }
}
$BAMobileLanguage = new BAMobileLanguage();
?>