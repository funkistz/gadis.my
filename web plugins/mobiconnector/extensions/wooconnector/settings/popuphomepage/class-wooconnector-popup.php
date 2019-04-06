<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class WooConnectorPopup{
    private $rest_url = 'wooconnector/popup';

    public function __construct(){
        add_action( 'admin_menu',array($this,'wooconnector_create_submenu_popup'));       
        add_action( 'admin_enqueue_scripts', array( $this , 'register_admin_scripts' ));
		add_action( 'admin_post_woo_update_popup', array( $this, 'update_popup' ) );
        $this->register_routes();
    }

    public function update_popup(){
        global $wpdb;
		check_admin_referer( "woo_update_popup" );
		$capability = apply_filters( 'woopopup_capability', 'edit_others_posts' );
		if ( ! current_user_can( $capability ) ) {
			return;
        }
        $link = @$_POST['wooconnector_popup_link'];
        if(!empty($link)){
            update_option('wooconnector-popup-homepage-link',$link);
        }else{
            update_option('wooconnector-popup-homepage-link','');
        }
        $check = @$_POST['wooconnector_check_popup_datetime'];
        if(!empty($check) && $check == 1){
            update_option('wooconnector-popup-homepage-check',$check);
            $from = @$_POST['wooconnector_popup_datepicker_from'];
            $to = @$_POST['wooconnector_popup_datepicker_to'];
            update_option('wooconnector-popup-homepage-date-from',$from);
            update_option('wooconnector-popup-homepage-date-to',$to);
        }else{
            update_option('wooconnector-popup-homepage-check','');
            update_option('wooconnector-popup-homepage-date-from','');
            update_option('wooconnector-popup-homepage-date-to','');
        }
        $attachments = @$_POST['wooconnector-popup-url'];
        if(!empty($attachments)){
            update_option('wooconnector-popup-homepage',$attachments);
        }else{
            update_option('wooconnector-popup-homepage','');
        }
        bamobile_mobiconnector_add_notice(__('Successfully Update','mobiconnector'));   
		wp_redirect( admin_url( "admin.php?page=popup" ) );
    }

    public function wooconnector_create_submenu_popup(){
        $parent_slug = 'wooconnector';
        add_submenu_page(
            $parent_slug,
            __('Popup Homepage'),
            __('Popup Homepage'),
            'manage_options',
            'popup',
            array($this,'wooconnector_action_popup')
        );
    }

    public function wooconnector_action_popup(){
        $task = isset($_REQUEST['wootask']) ? $_REQUEST['wootask'] : '';			
		require_once(WOOCONNECTOR_ABSPATH.'/settings/popuphomepage/wooconnector-popup.php');		
		if(!empty($task) || $task != ''){				
			$this->wooconnector_save_popup();		
		}
    }

    public function wooconnector_save_popup(){        
    }

    public function register_admin_scripts(){
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == 'popup'){
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-timepicker-addon',plugins_url('assets/js/jquery-ui-timepicker-addon.js',WOOCONNECTOR_PLUGIN_FILE),array(),WOOCONNECTOR_VERSION,true);
            wp_enqueue_style('jquery-ui-timepicker-addon-style',plugins_url('assets/css/jquery-ui-timepicker-addon.css',WOOCONNECTOR_PLUGIN_FILE),array(),WOOCONNECTOR_VERSION,'all');
            wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');     
            
            wp_enqueue_script( 'wooconnector-popup-script', plugins_url('assets/js/wooconnector-popup.js',WOOCONNECTOR_PLUGIN_FILE), array( 'jquery' ), WOOCONNECTOR_VERSION, true );
            $script = array(
				'domain' => site_url(),
			);
            wp_localize_script( 'wooconnector-popup-script', 'wooconnector_popup_script_params',  $script  );
            wp_enqueue_script( 'wooconnector-popup-script' );
            
            wp_register_style( 'wooconnector-admin-popup-style', plugins_url('assets/css/wooconnector-admin-popup.css',WOOCONNECTOR_PLUGIN_FILE), array(), WOOCONNECTOR_VERSION, 'all' );
            wp_enqueue_style( 'wooconnector-admin-popup-style' );	
            wp_enqueue_media();
        }
    }

    public function register_routes(){
        add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
    }

    public function register_api_hooks(){
        register_rest_route( $this->rest_url, '/getpopuphomepage', array(
                    'methods'         => 'GET',
                    'callback'        => array( $this, 'getpopuphomepage' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),	
                    'args'            => array(
                        'datetime' => array(
                            'required' => true,
                            'sanitize_callback' => 'esc_sql'
                        )
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
	public function get_items_permissions_check( $request ) {
		if(is_plugin_active('mobiconnector/mobiconnector.php')){
			$usekey = get_option('mobiconnector_settings-use-security-key');
			if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
				return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}
		return true;
	}

    public function getpopuphomepage($request){
        $params = $request->get_params();
        $datetime = isset($params['datetime']) ? $params['datetime'] : '';
        $popup =  get_option('wooconnector-popup-homepage');
        $url = get_option('wooconnector-popup-homepage-link');
        $newurl = '';
        if(isset($url) && $url != ''){            
            $product_id = url_to_postid($url);
            if(!empty($product_id)) {
                $newurl =  str_replace($url, 'link://product/'.$product_id, $url);
            }elseif(strpos($url,'link://') !== false){
                $newurl = $url;
            }elseif(strpos($url,'product-category') !== false){
                $url_split = explode('#', $url);
                $url = $url_split[0];
                        // Get rid of URL ?query=string
                $url_split = explode('?', $url);
                $url = $url_split[0];
                        
                $scheme = parse_url( home_url(), PHP_URL_SCHEME );
                $url = set_url_scheme( $url, $scheme );
                            
                if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
                $url = str_replace('://', '://www.', $url);
                            
                if ( false === strpos(home_url(), '://www.') )
                $url = str_replace('://www.', '://', $url);
                            
                $url = trim($url, "/");
                $slugs = explode('/', $url);				
                $category = $this->get_product_category_by_slug('/'.end($slugs));
                if(!empty($category)){
                    $newurl =  'link://product-category/'.$category->term_id;
                }
            }
            elseif(strpos($url,'about-us') != false){
                $newurl = 'link://about-us';
            }elseif(strpos($url,'bookmark') != false){
                $newurl = 'link://bookmark';
            }elseif(strpos($url,'term-and-conditions') != false){
                $newurl = 'link://term-and-conditions';
            }elseif(strpos($url,'privacy-policy') != false){
                $newurl = 'link://privacy-policy';
            }elseif(strpos($url,'contact-us') != false){
                $newurl = 'link://contact-us';
            }else{
                $newurl = $url;
            }
        }
        $check = get_option('wooconnector-popup-homepage-check');
        $dt = false;
        if(!empty($check) && $check == 1){
            $dt = true;
            
        }
        $GMT = new DateTimeZone("GMT");
        $gmtoffset = get_option('gmt_offset');
        $userInterval = DateInterval::createFromDateString((string)$gmtoffset . 'hours');
        $from = get_option('wooconnector-popup-homepage-date-from');
        $datefrom = '';
        $fromdate = '';
        if(!empty($from) || $from !== ''){
            $from = date('Y-m-d H:i:s',strtotime($from));
            $datefrom = new DateTime( $from, $GMT );
            $datefrom->add($userInterval);
            $fromdate = $datefrom->format('Y-m-d H:i:s'); 
        }
        $to = get_option('wooconnector-popup-homepage-date-to');
        $dateto = '';
        $todate = '';
        if(!empty($to) || $to !== ''){
            $to = date('Y-m-d H:i:s',strtotime($to));
            $dateto = new DateTime( $to, $GMT );
            $dateto->add($userInterval);
            $todate = $dateto->format('Y-m-d H:i:s');
        }      
        $return = array(
            'popup' => $popup,
            'link_popup' => $newurl,
            'use_datetime' => $dt,
            'datetime_from' => $from,
            'datetime_to' => $to
        );
        if(!empty($popup)){
            if($dt){
                if(empty($fromdate) && empty($todate)){
                    return null;
                }elseif(!empty($fromdate) && empty($todate)){
                    if(strtotime($datetime) < strtotime($fromdate)){
                        return null;
                    }
                }elseif(!empty($todate) && empty($fromdate)){
                    if(strtotime($datetime) > strtotime($todate)){
                        return null;
                    }
                }elseif(!empty($fromdate) && !empty($todate)){
                    if(strtotime($datetime) < strtotime($fromdate) || strtotime($datetime) > strtotime($todate)){
                        return null;
                    }
                }
            }           
            return $return;
        }else{
            return null;
        }
    }

    public function get_product_category_by_slug( $slug  ) {
		$category = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $category )
			_make_cat_compat( $category );
	 
		return $category;
	}  
}
$WooConnectorPopup = new WooConnectorPopup();
?>