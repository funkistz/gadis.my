<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add new line or edit data response in REST API posts
 */
class BAMobilePosts{

	/**
	 * BAMobile Posts construct
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Hook into actions and filters.
	 */
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
		// add active create photo when save Posts
		add_action('wp_insert_post', array( $this, 'bamobile_update_thumnail_mobile'), 100, 2);
		// add filter for wordpress 4.7+
		add_filter( 'rest_post_query', array($this, 'bamobile_add_filer_param_rest_post_query'), 100, 2);
		// change internal link to deeplink in wordpress
		if( array_key_exists( 'wooconnector_product_data' , $GLOBALS['wp_filter']) ) {
			add_filter( 'wooconnector_product_data', array($this,'bamobile_get_total_product_views') , 10, 1);
		}
		$listoptionscheckbox = get_option('mobiconnector_settings-post_type');
		$listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
					add_filter( 'rest_prepare_'.$name, array($this, 'bamobile_change_deeplink_in_post'), 101, 3);
				}
			}
		}
	}

	/**
	 * Register link API or field in REST API
	 */
	public function register_api_hooks() {
		$listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
					// thêm trường ảnh tiêu biểu
					register_rest_field( $name,
						'mobiconnector_feature_image',
						array(
							'get_callback'    => array($this, 'bamobile_get_feature_image'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					// lấy tổng số comment mỗi bài
					register_rest_field( $name,
						'mobiconnector_total_comments',
						array(
							'get_callback'    => array($this, 'bamobile_get_total_comments'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					// lấy tên tác giả
					register_rest_field( $name,
						'mobiconnector_author_name',
						array(
							'get_callback'    => array($this, 'bamobile_get_author_name'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					// đếm tổng số lượt view, sử dụng plugin: https://wordpress.org/plugins/post-views-counter
					// lấy tên tác giả
					register_rest_field( $name,
						'mobiconnector_total_views',
						array(
							'get_callback'    => array($this, 'bamobile_get_total_views'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					// lấy tất cả comments của 1 bài Post
					register_rest_field( $name,
						'mobiconnector_comments',
						array(
							'get_callback'    => array($this, 'bamobile_get_comments'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					// lấy 10 bài viết cùng category với Post
					register_rest_field( $name,
						'mobiconnector_posts_incategory',
						array(
							'get_callback'    => array($this, 'bamobile_get_posts_in_the_same_category'),
							'update_callback' => null,
							'schema'          => null,
						)
					);
					/*Ngay 22/3/2017 Code By Nguyen Hong Linh */
					// Lay full category voi Post
					register_rest_field($name,'mobiconnector_full_category',array(
						'get_callback' => array($this, 'bamobile_get_full_category_post'),
						'update_callback' => null,
						'schema'          => null,
					));
					
					register_rest_field($name,'mobiconnector_next_id',array(
						'get_callback' => array($this, 'bamobile_get_next_id'),
						'update_callback' => null,
						'schema'          => null,
					));
					register_rest_field($name,'mobiconnector_previous_id',array(
						'get_callback' => array($this, 'bamobile_get_previous_id'),
						'update_callback' => null,
						'schema'          => null,
					));
					register_rest_field($name,'mobiconnector_categories',array(
						'get_callback' => array($this, 'bamobile_changecategories'),
						'update_callback' => null,
						'schema'          => null,
					));
					register_rest_field($name,'mobiconnector_plugins_views',array(
						'get_callback' => array($this, 'bamobile_get_mobiconnector_views'),
						'update_callback' => null,
						'schema'          => null,
					));
				}
			}
		}
	}


	/**
	 * convert all Internal link to a post, a category to deeplink
	 * 
	 * @param WP_REST_Response $response  Result to send to the client.
     * @param WP_Post          $post      Post
     * @param WP_REST_Request  $request   Request used to generate the response.
	 * 
	 * @return WP_REST_Response Response object.
	 */	
	public function bamobile_change_deeplink_in_post($response, $post, $request) {
		$parameters = $request->get_params();
		unset($response->data['featured_media']);
		unset($response->data['guid']);
		unset($response->data['excerpt']);
		unset($response->data['ping_status']);
		$links = $response->get_links();
		foreach($links as $rel => $set){
			if ( isset( $set['href'] ) ) {
				$set = array( $set );
			}
			foreach ( $set as $attributes ) {
				$response->remove_link( $rel, $attributes['href'], $attributes );
			}
		}
		if(!isset($parameters['id'])){
			if(isset($response->data['content']['rendered'])){
				$content = $response->data['content']['rendered'];
				if($content !==  '' && ($response->data['format'] != 'image' || $response->data['format'] != 'video' || $response->data['format'] != 'gallery' || $response->data['format'] != 'link')){
					$content = bamobile_mobiconnector_get_plaintext($content);
					if(strpos($content,'.') != false){
						if(count($content) > 100){
							$content = substr($content,0,strpos($content,'.'));
						}
					}
					$response->data['content']['rendered'] = $content;
				}
			}
		}else{
			if(isset($response->data['content']['rendered'])){
				$content = $response->data['content']['rendered'];				
				$content = str_replace("localhost", 'localhost.localhost', $content);
				$links = wp_extract_urls($content);
				if(!empty($links)) {
					foreach($links as $url){
						$url = str_replace("localhost.localhost", 'localhost', $url);
						if(strpos($url, 'link://') ===0)
							continue;// nếu bắt đầu bởi url này thì bỏ qua
						/////////////////////////
						// tìm POST theo URL
						$post_id = url_to_postid($url);
						//var_dump($post_id);
						if(home_url() == $url){
							$response->data['content']['rendered'] = $response->data['content']['rendered'];
							$response->data['content']['rendered'] = $response->data['content']['rendered'];
						}
						elseif(!empty($post_id)) {
							$response->data['content']['rendered'] = str_replace($url.'"', 'link://posts/'.$post_id.'"', $response->data['content']['rendered']);
							$response->data['content']['rendered'] = str_replace($url."'", 'link://posts/'.$post_id."'", $response->data['content']['rendered']);
						} else { // kiểm tra category hay link ngoài
							$old_url = $url;
							// tìm link đến category
							$url = str_replace( '&amp;', '&', $url );
							// Get rid of the #anchor
							$url_split = explode('#', $url);
							$url = $url_split[0];

							// Get rid of URL ?query=string
							$url_split = explode('?', $url);
							$url = $url_split[0];

							// Set the correct URL scheme.
							$scheme = parse_url( home_url(), PHP_URL_SCHEME );
							$url = set_url_scheme( $url, $scheme );

							// Add 'www.' if it is absent and should be there
							if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
								$url = str_replace('://', '://www.', $url);

							// Strip 'www.' if it is present and shouldn't be
							if ( false === strpos(home_url(), '://www.') )
								$url = str_replace('://www.', '://', $url);
							
							$url = trim($url, "/");
							$slugs = explode('/', $url);
							$category = get_category_by_slug('/'.end($slugs));
							if(!empty($category)) {
								$response->data['content']['rendered'] = str_replace($old_url.'"', 'link://category/'.$category->term_id.'"', $response->data['content']['rendered']);
								$response->data['content']['rendered'] = str_replace($old_url."'", 'link://category/'.$category->term_id."'", $response->data['content']['rendered']);
							}
							////////////////
						}
					}
				}
			}
		}
		return $response;
	}

	/**
	 * If website exist WooCommerce and istall WooConnector Plugin
	 * Function add line wooconnector_plugins_views to list data
	 * 
	 * @param array $data      one data of list data
	 * 
	 * @return mixed
	 */
	public function bamobile_get_total_product_views($data){
		global $wpdb;
		$postid = $data['id'];
		$count = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT mc_count
				FROM " . $wpdb->prefix . "mobiconnector_views
				WHERE mc_id = %d ", absint( $postid )
			)
		);
		if(empty($count)){
			$count = 0;
		}
		$data['wooconnector_plugins_views'] = $count;
		return $data;
	}
	
	/**
	 *  Accept filter from request URL  
	 * 
	 * @param array            $args      list param of rest post
	 * @param WP_REST_Request  $request   Request used to generate the response.
	 * 
	 * @return mixed
	 */
	public function bamobile_add_filer_param_rest_post_query($args, $request) {
		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}
		return $args;
	}

	/** 
	 * Create thumbnail to post
	 * 
	 * @param int      $post_ID    id of post
	 * @param WP_Post  $post   	   Post
	 */
	public function bamobile_update_thumnail_mobile($post_ID, $post) {
		$post_thumbnail_id = get_post_thumbnail_id( $post );
		if(empty($post_thumbnail_id))
			return true;
		/// check exist thumbnail
		$mobiconnector_large = get_post_meta($post_thumbnail_id, 'mobiconnector_large', true);
		$mobiconnector_medium = get_post_meta($post_thumbnail_id, 'mobiconnector_medium', true);
		$mobiconnector_x_large = get_post_meta($post_thumbnail_id, 'mobiconnector_x_large', true);
		$mobiconnector_small = get_post_meta($post_thumbnail_id, 'mobiconnector_small', true);
		if(!empty($mobiconnector_medium) && !empty($mobiconnector_x_large) && !empty($mobiconnector_large) && !empty($mobiconnector_small))
			return true; // if exist
		// get data of image
		$relative_pathto_file = get_post_meta( $post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$relative_pathto_file;
		// check original file exist
		if(!file_exists($absolute_pathto_file))
			return true; // file not exist
		////////////////
		
		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// create image
		foreach($this->thumnails as $key => $value){
			$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
			$dest = $wp_upload_dir['basedir'].'/'.$path;
			BAMobileCore:: bamobile_resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
			// update post meta for thumnail
			update_post_meta ($post_thumbnail_id, $key, $path);
		}
		return true;
	}
	
	/**
	 * Handler for getting custom field data.
	 *
	 *
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 *
	 * @return mixed
	 */
	public function bamobile_get_feature_image( $object, $field_name, $request) {
		
		// Only proceed if the post has a featured image.
		if ( ! empty( $object['featured_media'] ) ) {
			$image_id = (int)$object['featured_media'];
		} elseif ( ! empty( $object['featured_image'] ) ) {
			// This was added for backwards compatibility with < WP REST API v2 Beta 11.
			$image_id = (int)$object['featured_image'];
		} else {
			return null;
		}

		$image = get_post( $image_id );

		if ( ! $image ) {
			return null;
		}
		$featured_image['source_url']    = wp_get_attachment_url( $image_id );
		// resize image
		$wp_upload_dir = wp_upload_dir();			

		// kiểm tra xem có ảnh thumnails cho Post chưa?
		$mobiconnector_large = get_post_meta($image_id, 'mobiconnector_large', true);
		$mobiconnector_medium = get_post_meta($image_id, 'mobiconnector_medium', true);
		$mobiconnector_x_large = get_post_meta($image_id, 'mobiconnector_x_large', true);
		$mobiconnector_small = get_post_meta($image_id, 'mobiconnector_small', true);
		if( empty($mobiconnector_large) || empty($mobiconnector_medium) || empty($mobiconnector_x_large) || empty($mobiconnector_small)) { // chưa tồn tại ảnh thì tạo
			$post_ID = $object['id'];
			$post = get_post($post_ID);
			$this->bamobile_update_thumnail_mobile($post_ID, $post);
		}
		// gắn thumnail mới
		foreach($this->thumnails as $key => $value){
			$featured_image[$key] = $wp_upload_dir['baseurl']."/". get_post_meta($image_id, $key, true);
			//$featured_image[$key.'_base64'] = get_post_meta($image_id,$key.'_base64', true);
		}

		return apply_filters( 'mobiconnector_rest_api_featured_image', $featured_image, $image_id );
	}

	/**
	 * Get total comments
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_total_comments($object, $field_name, $request) {
		$comments=(array) wp_count_comments( $object['id']);
		// chuyen thanh số nguyên
		if(!empty($comments)) {
			foreach($comments as &$item) {
				$item = absint($item);
			}
		}
		unset($comments['spam']);
		unset($comments['trash']);
		unset($comments['post-trashed']);
		unset($comments['moderated']);
		return $comments;
	}

	/**
	 * Get author name
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_author_name ($object, $field_name, $request) {
		$author = array();
		if(isset($object['author'])){
			$author = get_the_author_meta('display_name',$object['author']);
		}
		return $author;
	}

	/**
	 * Get total views
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_total_views($object, $field_name, $request) {
		if(empty($object['id'])){
			return null;
		}else{
			include_once( MOBICONNECTOR_ADMIN_PATH . 'includes/plugin.php' ); 				
			if(is_plugin_active('post-views-counter/post-views-counter.php') == false)
				return 0;
			global $wpdb;			
			$totalviews = $wpdb->get_var(
				$wpdb->prepare( "
					SELECT count
					FROM " . $wpdb->prefix . "post_views
					WHERE id = %d AND type = 4", absint( $object['id'] )
				)
			);				
			return $totalviews;
		}
	}

	/**
	 * Get all comments of one posts
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_comments($object, $field_name, $request) {
		$params = $request->get_params();
		if(isset($params['id'])){
			$args = array(
				'author_email' => '',
				'author__in' => '',
				'author__not_in' => '',
				'include_unapproved' => '',
				'fields' => '',
				'ID' => '',
				'comment__in' => '',
				'comment__not_in' => '',
				'karma' => '',
				'number' => '',
				'offset' => '',
				'orderby' => 'comment_date',
				'order' => 'DESC',
				'parent' => '',
				'post_author__in' => '',
				'post_author__not_in' => '',
				'post_ID' => '', // ignored (use post_id instead)
				'post_id' => absint( $object['id'] ),
				'post__in' => '',
				'post__not_in' => '',
				'post_author' => '',
				'post_name' => '',
				'post_parent' => '',
				'post_status' => '',
				'post_type' => '',
				'status' => 'approve',
				'type' => '',
					'type__in' => '',
					'type__not_in' => '',
				'user_id' => '',
				'search' => '',
				'count' => false,
				'meta_key' => '',
				'meta_value' => '',
				'meta_query' => '',
				'date_query' => null, // See WP_Date_Query
			);
			$comments = get_comments( $args );
			$list = array();
			foreach($comments as $comment)
			{
				$daten = new DateTime($comment->comment_date);
				$date = $daten->format('Y-m-d\TH:i:s');	
				$dategmtn = new DateTime($comment->comment_date_gmt);
				$dategmt = $dategmtn->format('Y-m-d\TH:i:s');	
				$list[] = array(
					'comment_ID' => $comment->comment_ID,
					'comment_author' => $comment->comment_author,
					'comment_author_email' => $comment->comment_author_email,
					'comment_date' => $date,
					'comment_date_gmt' => $dategmt,
					'comment_content' => $comment->comment_content,
					'comment_approved' => $comment->comment_approved,
					'comment_type' => $comment->comment_type,
					'user_id' => $comment->user_id
				);
			}
			return $list;
		}else{
			return null;
		}	
	}

	/**
	 * Get 10 posts same category
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_posts_in_the_same_category($object, $field_name, $request) {
		$parameters = $request->get_params();
		if(isset($parameters['id'])){
			$post_category_per_page = isset($parameters['post_category_per_page']) ? absint($parameters['post_category_per_page']): 10;
			$post_category_page = isset($parameters['post_category_page']) ? absint($parameters['post_category_page']): 1;
			$checkcategory = wp_get_post_categories($object['id']);
			if(empty($checkcategory)){
				return array();
			}
			$args = array(
				'paged'            => $post_category_page,
				'posts_per_page'   => $post_category_per_page,
				'category'         => implode(',', $checkcategory),
				'orderby'          => 'date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => $object['id'],
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => 'post',
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'	   => '',
				'author_name'	   => '',
				'post_status'      => 'publish',
				'suppress_filters' => true 
			);
			$posts_array = get_posts( $args );
			if(!empty($posts_array)) {
				foreach($posts_array as &$post) {
					$content = $post->post_content;
					if($content !==  '' && ($post->post_format != 'image' || $post->post_format != 'video' || $post->post_format != 'gallery' || $post->post_format != 'link')){
						$content = bamobile_mobiconnector_get_plaintext($content);
						if(strpos($content,'.') != false){
							if(count($content) > 100){
								$content = substr($content,0,strpos($content,'.'));
							}
						}
						$post->post_content = $content;
					}
					unset($post->post_author);
					unset($post->post_excerpt);
					unset($post->post_status);
					unset($post->ping_status);
					unset($post->post_password);
					unset($post->to_ping);
					unset($post->pinged);
					unset($post->content_filtered);
					unset($post->post_parent);
					unset($post->guid);
					unset($post->menu_order);
					unset($post->post_mime_type);
					unset($post->filter);
					$daten = new DateTime($post->post_date);
					$date = $daten->format('Y-m-d\TH:i:s');	
					$dategmtn = new DateTime($post->post_date_gmt);
					$dategmt = $dategmtn->format('Y-m-d\TH:i:s');	
					$featured_media = get_post_thumbnail_id($post->ID);
					$post->mobiconnector_feature_image = $this->bamobile_get_feature_image(array('id'=>$post->ID, 'featured_media'=>$featured_media),'mobiconnector_feature_image', $request);
					$post->post_date = $date;
					$post->post_date_gmt = $dategmt;
				}
			}
			return $posts_array;
		}else{
			return null;
		}
	}

	/* 22-3-2017 Code by Nguyen Hong Linh*/

	/**
	 * Get full category
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_full_category_post($object,$field_name,$request){
		$terms = get_terms( array(
			'taxonomy' => 'category',
			'hide_empty' => false,
			'number' => 10
		) );
		$listout = array();
		foreach($terms as $term){
			$listout[] = array(
				'term_id' => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
				'term_taxonomy_id' => $term->term_taxonomy_id,
				'taxonomy' => $term->taxonomy,
				'parent' => $term->parent,
				'count' => $term->count,
			);
		}
		return $listout;
	}
	
	/**
	 * Get next post
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_next_id($object,$field_name,$request){
		$format = get_post_format($object['id']);		
		if($format == false){
			$myposts = new WP_Query( array(
				'tax_query' => array(
					array(                
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => array( 
							'post-format-aside',
							'post-format-audio',
							'post-format-chat',
							'post-format-gallery',
							'post-format-image',
							'post-format-link',
							'post-format-quote',
							'post-format-status',
							'post-format-video'
						),
						'operator' => 'NOT IN'
					)
				)
			) );
			$posts = $myposts->posts;
			$ids = array();
			if(!empty($posts)){
				foreach($posts as $post){
					$ids[] = $post->ID;
				}
				$listid = array_unique( $ids );
				$thisindex = array_search($object['id'], $listid);
				if(!empty($thisindex) && $thisindex > 0){
					$next_post_id = $listid[$thisindex-1];
				}else{
					$next_post_id = null;
				}
			}else{
				$next_post_id = null;
			}
		}else{
			$nextpost = get_next_post(true,'','post_format');
			if(!empty($nextpost) && is_object($nextpost)){
				$next_post_id = $nextpost->ID;	
			}else{
				$next_post_id = null;
			}
			
		}	
			
		return $next_post_id;
	}

	/**
	 * Get previous post
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_previous_id($object,$field_name,$request){
		$format = get_post_format($object['id']);		
		if($format == false){
			$myposts = new WP_Query( array(
				'tax_query' => array(
					array(                
						'taxonomy' => 'post_format',
						'field' => 'slug',
						'terms' => array( 
							'post-format-aside',
							'post-format-audio',
							'post-format-chat',
							'post-format-gallery',
							'post-format-image',
							'post-format-link',
							'post-format-quote',
							'post-format-status',
							'post-format-video'
						),
						'operator' => 'NOT IN'
					)
				)
			) );
			$posts = $myposts->posts;
			$ids = array();
			if(!empty($posts)){
				foreach($posts as $post){
					$ids[] = $post->ID;
				}
				$listid = array_unique( $ids );
				$thisindex = array_search($object['id'], $listid);
				$checkindex = count($posts);
				if(!empty($thisindex) && $thisindex >= 0 && $thisindex < ($checkindex-1)){
					$previous_post_id = $listid[$thisindex+1];
				}else{
					$previous_post_id = null;
				}
			}else{
				$previous_post_id = null;
			}
		}else{
			$previouspost = get_previous_post(true,'','post_format');
			if(!empty($previouspost) && is_object($previouspost)){
				$previous_post_id = $previouspost->ID;
			}else{
				$previous_post_id = null;
			}			
		}	
		return $previous_post_id;
	}

	/**
	 * Get categories special
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_changecategories($object,$field_name,$request){
		$categories = get_the_category($object['id']);
		if(!empty($categories)){
			foreach($categories as $category){
				$listout[] = array(
					'cat_ID' => $category->cat_ID,
					'cat_name' => $category->cat_name,
					'cat_slug' => $category->category_nicename,
					'cat_count' => $category->category_count,
					'cat_parent' => $category->category_parent
				);
			}
		}else{
			$listout = array();
		}
		return $listout;
	}

	/**
	 * Get views of mobiconnector
	 * 
	 * @param array $object The object from the response
	 * @param string $field_name Name of field
	 * @param WP_REST_Request $request Current request
	 * 
	 * @return mixed
	 */
	public function bamobile_get_mobiconnector_views($object,$field_name,$request){
		global $wpdb;
		$postid = $object['id'];
		$count = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT mc_count
				FROM " . $wpdb->prefix . "mobiconnector_views
				WHERE mc_id = %d ", absint( $postid )
			)
		);
		if(empty($count)){
			$count = 0;
		}
		return $count;
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
$BAMobilePosts = new BAMobilePosts();
?>