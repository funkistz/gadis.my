<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create API involve with Post
 */
class BAMobilePost{
	
	/**
     * Url of API
     */
	private $rest_url = 'mobiconnector/post';	

	/**
     * BAMobilePost construct
     */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}

	/**
	 * Create Api or add field
	 */
	public function register_api_hooks() {
		
		// register users
		register_rest_route( $this->rest_url, '/counter_view', array(
				array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'bamobile_counter_view' ),		
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),			
					'args' => array(
						'post_id' => array(
							'required' => true,
							'sanitize_callback' => 'absint'
						)
						
					),					
				)
			) 
		);
		
		// lấy bài biết đọc nhiều nhất với query: wp-json/wp/v2/posts?filter[orderby]=post_views&filter[order]=asc
		
		register_rest_route( $this->rest_url, '/getpostcategory', array(
			'methods' => 'GET',
			'callback' => array( $this, 'bamobile_get_post_by_category' ),	
			'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),		
			'args'            => array(
				'post_per_page' => array(
					'default' => 3,
					'sanitize_callback' => 'absint',
				),
				'post_num_page' => array(
					'default' => 1,
					'sanitize_callback' => 'absint',
				),
				'post_order_page' => array(
					'default' => 'DESC',
					'validate_callback' => array($this,'bamobile_validate_post_order_page'),
				),
				'post_order_by' => array(
					'default' => 'date',
					'validate_callback' => array ($this,'bamobile_validate_post_order_by'),
				),
				'post_category' => array(
					'sanitize_callback' => 'absint',
				),				
			),
		) );

		register_rest_route( $this->rest_url, '/plugin_view', array(
				array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'bamobile_up_plugins_views' ),	
					'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),				
					'args' => array(
						'post_id' => array(
							'required' => true,
							'sanitize_callback' => 'absint'
						)						
					)					
				)
			) 
		);
		// Lay 3 bai viet theo category theo wp-json/mobiconnector/post/getpostcategory
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
	 * Up view of post view counter when the run api
	 * 
	 * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
	 */
	public function bamobile_counter_view( $request ) {
		require_once( MOBICONNECTOR_ADMIN_PATH . 'includes/plugin.php' );
		if ( !is_plugin_active( 'post-views-counter/post-views-counter.php' ) ) {
		  return new WP_Error( 'post-views-counter_deactive', __( 'Post Views Counter Deactive' ), array( 'status' => 400 ) );
		}
		// require plugin
		if ( is_plugin_active( 'post-views-counter/post-views-counter.php' ) ) {
			require_once( plugin_dir_path('post-views-counter/post-views-counter.php') . '/includes/functions.php' );
			$parameters = $request->get_params();
			pvc_view_post($parameters["post_id"]); // update post view
			return pvc_get_post_views($parameters["post_id"]); // get view of Post
		}else{
			return 0;
		}
	}

	/**
	 * Up view of mobiconnector when the run api
	 * 
	 * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
	 */
	public function bamobile_up_plugins_views( $request ) {
		$parameters = $request->get_params();
		$currentip = bamobile_mobiconnector_get_the_user_ip();
		$postid = $parameters["post_id"];
        $device = 'mobile';
        if(!isset($_SESSION[$currentip][$postid])){
            $count = bamobile_mobiconnector_insert_or_update_views($postid,$device);
			$_SESSION[$currentip][$postid]['time'] = time();
			$_SESSION[$currentip][$postid]['view'] = $count;
        }elseif((time() - $_SESSION[$currentip][$postid]['time']) > 900){
            $count = bamobile_mobiconnector_insert_or_update_views($postid,$device);
			$_SESSION[$currentip][$postid]['time'] = time();
			$_SESSION[$currentip][$postid]['view'] = $count;
        }else{
            $count = $_SESSION[$currentip][$postid]['view'];
        }
		return $count;
	}
	
	/**
	 * Get 3 new posts by category
	 * 
	 * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
	 */
	public function bamobile_get_post_by_category($request){
		$parameters = $request->get_params();
		$post_per_page = $parameters['post_per_page'];
		$post_num_page = $parameters['post_num_page'];
		$post_order_page = $parameters['post_order_page'];
		$post_order_by = $parameters['post_order_by'];		
		global $wpdb;
		$args = array(
			'orderby' => 'id',
			'hide_empty'=> 0,
		);
		
		$categories = get_categories($args);
		$result = "";
		$wp_upload_dir = wp_upload_dir();
		if(isset($parameters['post_category'])){
			$post_category = $parameters['post_category'];
			foreach($categories as $c){			
				$args = array(
					'posts_per_page'   => $post_per_page,
					'paged'            => $post_num_page,
					'category'         => $post_category,
					'category_name'    => '',
					'orderby'          => $post_order_by,
					'order'            => $post_order_page,
					'meta_key'         => '',
					'meta_value'       => '',
					'post_type'        => 'post',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'author'	       => '',
					'author_name'	   => '',
					'post_status'      => 'publish',
					'suppress_filters' => true 
				);
				$postitems = get_posts($args);
				$list = array();
				foreach($postitems as $postitem){	
					$daten = new DateTime($postitem->post_date);
					$date = $daten->format('Y-m-d\TH:i:s');	
					$dategmtn = new DateTime($postitem->post_date_gmt);
					$dategmt = $dategmtn->format('Y-m-d\TH:i:s');	
					$modin = new DateTime($postitem->post_modified);
					$modi = $modin->format('Y-m-d\TH:i:s');	
					$modigmtn = new DateTime($postitem->post_modified_gmt);
					$modigmt = $modigmtn->format('Y-m-d\TH:i:s');	
					$comments=(array) wp_count_comments( $postitem->ID);
					unset($comments['spam']);
					unset($comments['trash']);
					unset($comments['post-trashed']);
					$count = $wpdb->get_var(
						$wpdb->prepare( "
							SELECT count
							FROM " . $wpdb->prefix . "post_views
							WHERE id = %d AND type = 4", absint( $postitem->ID )
						)
					);
					$thumbnailId = get_post_thumbnail_id($postitem->ID);
					foreach($this->thumnails as $key => $value)
					{			
						$listimages = get_post_meta($thumbnailId,$key,true);
						if(!empty($listimages)){
							$listimages[$key] = $wp_upload_dir['baseurl']."/".$listimages;					
						}
						else{
							$listimages[$key] = null;			
						}
					}
					$listimages['feature_image_small']= $listimages['mobiconnector_small'];
					$listimages['feature_image_medium'] = $listimages['mobiconnector_medium'];
					$listimages['feature_image_large'] = $listimages['mobiconnector_large'];
					$listimages['feature_image_x_large'] = $listimages['mobiconnector_x_large'];
					unset($listimages['mobiconnector_small']);
					unset($listimages['mobiconnector_medium']);
					unset($listimages['mobiconnector_large']);
					unset($listimages['mobiconnector_x_large']);
					$content = apply_filters('the_content',$postitem->post_content);
					if($content !==  ''){
						$content = bamobile_mobiconnector_get_plaintext($content);
						$content = substr($content,0,strpos($content,'.'));
					}
					$list[] = array(
						'post_id' => $postitem->ID,
						'post_author' => $postitem->post_author,
						'post_date' => $date,
						'post_date_gmt' => $dategmt,
						'post_content' => $content,
						'post_title' => $postitem->post_title,
						'post_modified' => $modi,
						'post_modified_gmt' => $modigmt,
						'post_type' => $postitem->post_type,
						'comment_count' => $postitem->comment_count,
						'images_link' => $listimages,
						'mobiconnector_total_comments' => $comments,
						'mobiconnector_total_views' => $count,
						'mobiconnector_format' => get_post_format($postitem->ID)? : 'standard',
					);
				}
				$result[] = array( 'name' => $c->name, 'term_id' => $c->term_id  ,'object' => $list);		
			}
		}else{
			foreach($categories as $c){			
				$args = array(
					'posts_per_page'   => $post_per_page,
					'paged'            => $post_num_page,
					'category'         => $c->term_id,
					'category_name'    => '',
					'orderby'          => $post_order_by,
					'order'            => $post_order_page,
					'meta_key'         => '',
					'meta_value'       => '',
					'post_type'        => 'post',
					'post_mime_type'   => '',
					'post_parent'      => '',
					'author'	       => '',
					'author_name'	   => '',
					'post_status'      => 'publish',
					'suppress_filters' => true 
				);
				$postitems = get_posts($args);
				$list = array();
				foreach($postitems as $postitem){
					$daten = new DateTime($postitem->post_date);
					$date = $daten->format('Y-m-d\TH:i:s');	
					$dategmtn = new DateTime($postitem->post_date_gmt);
					$dategmt = $dategmtn->format('Y-m-d\TH:i:s');
					$modin = new DateTime($postitem->post_modified);
					$modi = $modin->format('Y-m-d\TH:i:s');	
					$modigmtn = new DateTime($postitem->post_modified_gmt);
					$modigmt = $modigmtn->format('Y-m-d\TH:i:s');	
					$comments=(array) wp_count_comments( $postitem->ID);
					unset($comments['spam']);
					unset($comments['trash']);
					unset($comments['post-trashed']);
					$count = $wpdb->get_var(
						$wpdb->prepare( "
							SELECT mc_count
							FROM " . $wpdb->prefix . "mobiconnector_views
							WHERE mc_id = %d", absint( $postitem->ID )
						)
					);

					$thumbnailId = get_post_thumbnail_id($postitem->ID);
					foreach($this->thumnails as $key => $value)	{			
						$images = get_post_meta($thumbnailId,$key,true);
						if(!empty($images)){
							$listimages[$key] = $wp_upload_dir['baseurl']."/".$images;					
						}
						else{
							$listimages[$key] = null;			
						}
					}
					$listimages['feature_image_small']= $listimages['mobiconnector_small'];
					$listimages['feature_image_medium'] = $listimages['mobiconnector_medium'];
					$listimages['feature_image_large'] = $listimages['mobiconnector_large'];
					$listimages['feature_image_x_large'] = $listimages['mobiconnector_x_large'];
					unset($listimages['mobiconnector_small']);
					unset($listimages['mobiconnector_medium']);
					unset($listimages['mobiconnector_large']);
					unset($listimages['mobiconnector_x_large']);
					$content = apply_filters('the_content',$postitem->post_content);
					if($content !==  ''){
						$content = bamobile_mobiconnector_get_plaintext($content);
						$content = substr($content,0,strpos($content,'.'));
					}
					$list[] = array(
						'post_id' => $postitem->ID,
						'post_author' => $postitem->post_author,
						'post_date' => $date,
						'post_date_gmt' => $dategmt,
						'post_content' => $content,
						'post_title' => $postitem->post_title,
						'post_modified' => $modi,
						'post_modified_gmt' => $modigmt,
						'post_type' => $postitem->post_type,
						'comment_count' => $postitem->comment_count,
						'images_link' => $listimages,
						'mobiconnector_total_comments' => $comments,
						'mobiconnector_total_views' => $count,
						'mobiconnector_format' => get_post_format($postitem->ID)? : 'standard',
					);
				}
				$result[] = array( 'name' => $c->name, 'term_id' => $c->term_id, 'object' => $list);		
			}
		}		
		return $result;
	}

	/**
	 * Validate order by input
	 * 
	 * @param string $param   value of params
	 * @param WP_REST_Request $request  current Request
	 * @param string $key     key of params
     * 
     * @return string value order by
	 */
	public function bamobile_validate_post_order_by($param, $request, $key) {
		$parameters = $request->get_params();
		if($parameters['post_order_by'] == 'ID' || $parameters['post_order_by'] == 'author' 
		|| $parameters['post_order_by'] == 'title' || $parameters['post_order_by'] == 'date' 
		|| $parameters['post_order_by'] == 'modified' || $parameters['post_order_by'] == 'parent' 
		|| $parameters['post_order_by'] == 'rand' || $parameters['post_order_by'] == 'comment_count' 
		|| $parameters['post_order_by'] == 'menu_order' || $parameters['post_order_by'] == 'meta_value' 
		|| $parameters['post_order_by'] == 'meta_value_num' || $parameters['post_order_by'] == 'post__in') 
		{
			return $parameters['post_order_by'];
		}
		else{
			return 'date';
		}
	}

	/**
	 * Validate order page input
	 * 
	 * @param string $param   value of params
	 * @param WP_REST_Request $request  current Request
	 * @param string $key     key of params
     * 
     * @return string value order page
	 */
	public function bamobile_validate_post_order_page($param, $request, $key){		
		$parameters = $request->get_params();
		if($parameters['post_order_page'] == 'DESC' || $parameters['post_order_page'] == 'ASC')
		{
			return $parameters['post_order_page'];
		}
		else{
			return 'DESC';
		}
	}

	/**
	 * Size of thumbnail
	 */
	public $thumnails = array(
		'mobiconnector_small' => array(
			'width' => 320,
			'height' => 240
		),
		'mobiconnector_medium' => array(
			'width' => 480,
			'height' => 360
		),
		'mobiconnector_large' => array(
			'width' => 752,
			'height' => 564
		),
		'mobiconnector_x_large' => array(
			'width' => 1080,
			'height' => 810
		),
	);
}
$BAMobilePost = new BAMobilePost();
?>