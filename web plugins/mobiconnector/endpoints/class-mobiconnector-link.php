<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Get languages if qTranslate install
 */
class BAMobileLink{

    /**
     * Url of API
     */
    private $rest_url = "mobiconnector/link";

    /**
     * BAMobileLink construct
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
    public function register_api_hooks(){
        register_rest_route( $this->rest_url, '/predeeplink', array(
                    'methods'         => 'POST',
                    'callback'        => array( $this, 'bamobile_deeplink_pre' ),	
                    'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),			
                    'args'            => array(		
                        'url'         => array(
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
	public function bamobile_get_items_permissions_check( $request ) {
		$usekey = get_option('mobiconnector_settings-use-security-key');
		if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
			return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

    /**
     * Pre deep link to url in app
     * 
     * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
     */
    public function bamobile_deeplink_pre($request){
        $params = $request->get_params();
        $url = $params['url'];
        $urlpre = str_replace("localhost.localhost", 'localhost', $url);
        if(home_url() == $url){
            return array(
                'link'  => $url,
                'type'  => 'browser'
            );
        }
        if(strpos($urlpre, 'link://') !== false){
            $post_id = url_to_postid($url);
            if(!empty($post_id)){            
                $type = get_post_type($post_id);
                $outurl = '';
                if($type == 'post'){
                    $outurl = str_replace($url, 'link://post/'.$post_id, $url);
                }elseif($type == 'product'){
                    $outurl = str_replace($url, 'link://product/'.$post_id, $url);
                }
                return array(
                    'link'  => $outurl,
                    'type'  => 'internal'
                );
            }else{
                $old_url = $url;
                $url = str_replace( '&amp;', '&', $url );
                $url_split = explode('#', $url);
                $url = $url_split[0];          
                $url_split = explode('?', $url);
                $url = $url_split[0];
                $scheme = parse_url( home_url(), PHP_URL_SCHEME );
                $url = set_url_scheme( $url, $scheme );
                if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') ){
                    $url = str_replace('://', '://www.', $url);
                }         
                if ( false === strpos(home_url(), '://www.') ){
                    $url = str_replace('://www.', '://', $url);
                }     
                $url = trim($url, "/");
                $slugs = explode('/', $url);
                $category = get_category_by_slug('/'.end($slugs));
                $product_category = $this->bamobile_get_product_category_by_slug('/'.end($slugs));
                $product_brand = $this->bamobile_get_product_brand_by_slug('/'.end($slugs));
                if(!empty($category)) {
                    $out_url = 'link://category/'.$category->term_id;
                    return array(
                        'link'  => $out_url,
                        'type'  => 'internal'
                    );
                }elseif(!empty($product_category)){
                    $out_url = 'link://product-category/'.$product_category->term_id;
                    return array(
                        'link'  => $out_url,
                        'type'  => 'internal'
                    );
                }elseif(!empty($product_brand)){
                    $out_url = 'link://brand/'.$product_brand->term_id;
                    return array(
                        'link'  => $out_url,
                        'type'  => 'internal'
                    );
                }else{
                    return array(
                        'link'  => $old_url,
                        'type'  => 'browser'
                    ); 
                }
            }
        }else{
            return array(
                'link'  => $url,
                'type'  => 'browser'
            );
        }
    }

    /**
     * Get Product Category by Slug term
     * 
     * @param string $slug       slug of term
     * 
     * @return array category
     */
    public function bamobile_get_product_category_by_slug( $slug  ) {
		$category = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $category )
			_make_cat_compat( $category );
	 
		return $category;
	}

    /**
     * Get Product Category by Slug term
     * 
     * @param string $slug       slug of term
     * 
     * @return array category
     */
    public function bamobile_get_product_brand_by_slug( $slug  ) {
		$category = get_term_by( 'slug', $slug, 'wooconnector_product_brand' );
		if ( $category )
			_make_cat_compat( $category );
	 
		return $category;
	}
}
$BAMobileLink = new BAMobileLink();
?>