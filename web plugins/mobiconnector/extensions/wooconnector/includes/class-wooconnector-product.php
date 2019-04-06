<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooconnectorProducts extends  WP_REST_Controller{
	/*
	*@ Size of thumbnail
	*/
	public $thumnailsX = array(
		'wooconnector_small' => array(
			'width' => 320,
			'height' => 240
		),
		'wooconnector_medium' => array(
			'width' => 480,
			'height' => 360
		),
		'wooconnector_large' => array(
			'width' => 752,
			'height' => 564
		),
		'wooconnector_x_large' => array(
			'width' => 1080,
			'height' => 810
		),
	);

	public $thumnailsS = array(
		'wooconnector_small' => array(
			'width' => 320,
			'height' => 320
		),
		'wooconnector_medium' => array(
			'width' => 480,
			'height' => 480
		),
		'wooconnector_large' => array(
			'width' => 752,
			'height' => 752
		),
		'wooconnector_x_large' => array(
			'width' => 1080,
			'height' => 1080
		),
	);	
	
	public $thumnailsY = array(
		'wooconnector_small' => array(
			'width' => 240,
			'height' => 320
		),
		'wooconnector_medium' => array(
			'width' => 360,
			'height' => 480
		),
		'wooconnector_large' => array(
			'width' => 564,
			'height' => 752
		),
		'wooconnector_x_large' => array(
			'width' => 810,
			'height' => 1080
		),
	);	
	
	public function __construct() {		
		$this->register_routes();
		// add active create photo when save Products
		add_action('save_post', array( $this, 'update_thumnail_woo'), 10, 3);				
		// add filter for wordpress 4.7+
		add_filter( 'rest_post_query', array($this, 'add_filer_param_rest_post_query'), 100, 2);	
	}
	
	public function add_filer_param_rest_post_query($args, $request) {
		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}
		return $args;
	}
	
	/*
	* Crop images save 
	*/
	public function update_thumnail_woo($productID , $product) {
		$post_type = get_post_type($productID);
		if($post_type == 'product'){
			$wgp = wc_get_product($productID);
			$imageids = $wgp->get_gallery_image_ids();
			$thumbId = array(get_post_thumbnail_id($productID));
			$listids = array_merge($imageids,$thumbId);		
			foreach($listids as $listid ){
				$post_thumbnail_id = $listid;
				if(empty($post_thumbnail_id))
					continue;
				/// kiểm tra xem đã tồn tại thumnail chưa
				$wooconnector_large = get_post_meta($post_thumbnail_id, 'wooconnector_large', true);
				$wooconnector_medium = get_post_meta($post_thumbnail_id, 'wooconnector_medium', true);
				$wooconnector_x_large = get_post_meta($post_thumbnail_id, 'wooconnector_x_large', true);
				$wooconnector_small = get_post_meta($post_thumbnail_id, 'wooconnector_small', true);
				if(!empty($wooconnector_medium) && !empty($wooconnector_x_large) && !empty($wooconnector_large) && !empty($wooconnector_small))
					//return true; // đã tồn tại rồi ko tạo nữa
					continue;
				// lấy thông tin của ảnh
				$relative_pathto_file = get_post_meta( $post_thumbnail_id, '_wp_attached_file', true);
				$wp_upload_dir = wp_upload_dir();
				$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$relative_pathto_file;
				// kiểm tra file gốc có tồn tại hay không?
				if(!file_exists($absolute_pathto_file))
					//return true; // file ko tồn tại
					continue;
				////////////////
				
				$path_parts = pathinfo($relative_pathto_file);
				$ext = strtolower($path_parts['extension']);
				$basename = strtolower($path_parts['basename']);
				$dirname = strtolower($path_parts['dirname']);
				$filename = strtolower($path_parts['filename']);
				// tạo ảnh 
				list($width, $height) = getimagesize($absolute_pathto_file);
				if($width > $height){
					foreach($this->thumnailsX as $key => $value){
						$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
						$dest = $wp_upload_dir['basedir'].'/'.$path;
						WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
						// cập nhật post meta for thumnail
						update_post_meta ($post_thumbnail_id, $key, $path);
					}
				}elseif($width < $height){
					foreach($this->thumnailsY as $key => $value){
						$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
						$dest = $wp_upload_dir['basedir'].'/'.$path;
						WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
						// cập nhật post meta for thumnail
						update_post_meta ($post_thumbnail_id, $key, $path);
					}
				}elseif($width == $height){
					foreach($this->thumnailsS as $key => $value){
						$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
						$dest = $wp_upload_dir['basedir'].'/'.$path;
						WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
						// cập nhật post meta for thumnail
						update_post_meta ($post_thumbnail_id, $key, $path);
					}
				}
			}
		}
		return true;
	}
	
	public function update_thumnail_woo_object($imageID ) {		
		$post_thumbnail_id = $imageID;
		if(empty($post_thumbnail_id))
			return true;
		/// kiểm tra xem đã tồn tại thumnail chưa
		$wooconnector_large = get_post_meta($post_thumbnail_id, 'wooconnector_large', true);
		$wooconnector_medium = get_post_meta($post_thumbnail_id, 'wooconnector_medium', true);
		$wooconnector_x_large = get_post_meta($post_thumbnail_id, 'wooconnector_x_large', true);
		$wooconnector_small = get_post_meta($post_thumbnail_id, 'wooconnector_small', true);
		if(!empty($wooconnector_medium) && !empty($wooconnector_x_large) && !empty($wooconnector_large) && !empty($wooconnector_small))
			return true; // đã tồn tại rồi ko tạo nữa
		// lấy thông tin của ảnh
		$relative_pathto_file = get_post_meta( $post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'].'/'.$relative_pathto_file;
		// kiểm tra file gốc có tồn tại hay không?
		if(!file_exists($absolute_pathto_file))
			return true; // file ko tồn tại
		////////////////		
		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// tạo ảnh 
		list($width, $height) = getimagesize($absolute_pathto_file);
		if($width > $height){
			foreach($this->thumnailsX as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}elseif($width < $height){
			foreach($this->thumnailsY as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}elseif($width == $height){
			foreach($this->thumnailsS as $key => $value){
				$path = $dirname.'/'.$filename.'_'.$key.'_'.$value['width'].'_'.$value['height'].'.'.$ext;
				$dest = $wp_upload_dir['basedir'].'/'.$path;
				WooConnectorCore:: resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta ($post_thumbnail_id, $key, $path);
			}
		}
		return true;
	}
	
	public function register_routes() {	
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	public function register_api_hooks() {
		register_rest_field( 'product',
			'wooconnector_crop_images',
			array(
				'get_callback'    => array($this, 'get_crop_image'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	
		register_rest_field( 'product',
			'wooconnector_look_product',
			array(
				'get_callback'    => array($this, 'get_look_product'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	
		register_rest_field( 'product',
			'wooconnector_total_views',
			array(
				'get_callback'    => array($this, 'get_viewcount_product'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		register_rest_field( 'product',
			'wooconnector_reviews',
			array(
				'get_callback'    => array($this, 'get_comment_avatar'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
			
		register_rest_field( 'product',
			'grouped_products',
			array(
				'get_callback'    => array($this, 'get_grouped_product'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		register_rest_field( 'product',
			'rating_count',
			array(
				'get_callback'    => array($this, 'get_rating_count'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		register_rest_field( 'product',
			'average_rating',
			array(
				'get_callback'    => array($this, 'get_average_rating'),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		register_rest_field( 'product',
			'description',
			array(
				'get_callback'    => array($this, 'get_description'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		register_rest_field( 'product',
			'wooconnector_previous_product',
			array(
				'get_callback'    => array($this, 'get_previous'),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		register_rest_field( 'product',
			'wooconnector_next_product',
			array(
				'get_callback'    => array($this, 'get_next'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		
		register_rest_field( 'product',
			'price_html',
			array(
				'get_callback'    => array($this, 'get_price_html'),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	/*
	* Get crop images 
	*/
	public function get_crop_image( $object, $field_name, $request) {		
		// Only proceed if the post has a featured image.
		if ( ! empty( $object['images'] ) ) {
			$image_ids = array();
			foreach($object['images'] as $listimages)
			{
				$image_ids[] = (int)$listimages['id'];
			}			
		} else {
			return null;
		}
		foreach($image_ids as $image_id){
			$image = get_post( $image_id );

			if ( ! $image ) {
				return null;
			}

			// This is taken from WP_REST_Attachments_Controller::prepare_item_for_response().
			$crop_image['id']            = $image_id;
			$crop_image['alt_text']      = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$crop_image['caption']       = $image->post_excerpt;
			$crop_image['description']   = $image->post_content;
			$crop_image['media_type']    = wp_attachment_is_image( $image_id ) ? 'image' : 'file';			
			$crop_image['product']          = ! empty( $image->post_parent ) ? (int) $image->post_parent : null;
			$crop_image['source_url']    = wp_get_attachment_url( $image_id );
			// attached more thumnail
			
			// resize image
			$wp_upload_dir = wp_upload_dir();	

			// kiểm tra xem có ảnh thumnails cho product chưa?
			$wooconnector_large = get_post_meta($image_id, 'wooconnector_large', true);
			$wooconnector_medium = get_post_meta($image_id, 'wooconnector_medium', true);
			$wooconnector_x_large = get_post_meta($image_id, 'wooconnector_x_large', true);
			$wooconnector_small = get_post_meta($image_id, 'wooconnector_small', true);
			if( empty($wooconnector_large) || empty($wooconnector_medium) || empty($wooconnector_x_large) || empty($wooconnector_small)) { // chưa tồn tại ảnh thì tạo
				
				$this->update_thumnail_woo_object($image_id);
			}
			// gắn thumnail mới
			foreach($this->thumnailsX as $key => $value){
				$imagesupload = get_post_meta($image_id, $key, true);
				if(empty($imagesupload))
				{
					$crop_image[$key] = null;
				}
				else{
					$crop_image[$key] = $wp_upload_dir['baseurl']."/". $imagesupload;
				}
			}			
			$listcrop[] = $crop_image;
		}
		return apply_filters('wooconnector_rest_api_crop_images',$listcrop);
	}
	
	/*
	* Get look product by object category
	*/
	
	public function get_look_product( $object, $field_name, $request){		
		if(empty($object['categories'])){
			return null;
		}else{
			$categories = $object['categories'];		
			$idcheck = array();
			$numlook = 0;
			if(!empty($categories)){
				foreach($categories as $category){
					$namecat = $category['slug'];
					$argslook = array(
						'posts_per_page'   => 4,
						'paged'            => 1,
						'offset'           => 0,
						'category'         => '',
						'category_name'    => '',
						'product_cat'      => $namecat,
						'orderby'          => 'date',
						'order'            => 'DESC',
						'meta_key'         => '',
						'meta_value'       => '',
						'post_type'        => 'product',
						'post_mime_type'   => '',
						'post_parent'      => '',
						'author'	       => '',
						'author_name'	   => '',
						'post_status'      => 'publish',
						'suppress_filters' => true 
					);				
					$pro4s = get_posts($argslook);				
					foreach($pro4s as $pro4){	
						if($pro4->ID != $object['id']){					
							if(!in_array($pro4->ID,$idcheck)){
								if($numlook < 4){
									$prova4 = wc_get_product( $pro4->ID );							
									$thumbId = get_post_thumbnail_id($pro4->ID);
									$wp_upload = wp_upload_dir();
									foreach($this->thumnailsX as $key => $value){
										$path = get_post_meta($thumbId, $key, true);
										if(empty($path))
										{
											$limages[$key] = null;
										}
										else{
											$limages[$key] = $wp_upload['baseurl']."/". get_post_meta($thumbId, $key, true);
										}						
									}	

									if($prova4->is_type( 'grouped' ) && $prova4->has_child()){
										$group = new WC_Product_Grouped($pro4->ID);
										$result = $group->get_price_html();
									}else{
										$result = $prova4->get_price_html();
									}
									$products = array(
										'id'    => $prova4->get_id(),
										'title' => $prova4->get_title(),
										'price' => $prova4->get_price(),
										'type'  => $prova4->get_type(),
										'regular_price' => $prova4->get_regular_price(),
										'sale_price' => $prova4->get_sale_price(),
										'price_html' => $result,
										'avatar' => $limages,
									);
									if($prova4->is_type( 'variable' ) && $prova4->has_child()){
										$products['variations'] = $prova4->get_children();
									}
									if($prova4->is_type( 'grouped' ) && $prova4->has_child()){
										$products['grouped_products'] = $prova4->get_children();
									}
									$look_product[] = $products;
									array_push($idcheck,$pro4->ID);
									$numlook++;
								}
								else{
									break;
								}
							}
							else{
								continue;
							}
						}
						else{
							continue;
						}
					}
				}
			}else{
				$look_product = null;
			}	
			return $look_product;
		}
	}
	
	/*
	* Get review count of product
	*/
	public function get_viewcount_product( $object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 				
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
	
	/*
	* Get comment author avatar if isset comment
	*/
	public function get_comment_avatar($object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			$parameter = $request->get_params();			
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 			
			if(is_plugin_active('wp-user-avatar/wp-user-avatar.php') == false)
				return null;
			if(isset($parameter['id']))
			{
				$argscomment = array(
					'post_id' => $object['id'],
					'status' => 'approve',
					'offset' => 1						
				);				
				//lay tat ca gia tri comment
				$comments_query = new WP_Comment_Query;
				$comments = $comments_query->query( $argscomment );	
				$wp_upload_dir = wp_upload_dir();
				if( !empty($comments))
				{
					foreach($comments as $comment)
					{
						$cmentId = $comment->comment_ID;
						$rating = get_comment_meta( $cmentId, 'rating', true );
						$email = $comment->comment_author_email;
						$userid = $comment->user_id;
						$daten = new DateTime($comment->comment_date);
						$dategmtn = new DateTime($comment->comment_date_gmt);
						$date = $daten->format('Y-m-d\TH:i:s');			
						$gmt = $dategmtn->format('Y-m-d\TH:i:s');
						$idavatar = get_user_meta($userid, 'wp_user_avatar', true);
						$linkimage = wp_get_attachment_image_src($idavatar,'thumbnail',true);
						$images = $linkimage[0];
						if($userid != '' || $userid != null){
							if($userid > 0){
								if($idavatar != ''){
									$listcomment[] = array(
										'user' => $comment->comment_author,
										'link_avatar' => $images,
										'comment_content' => $comment->comment_content,
										'comment_date' => $date,
										'comment_date_gmt' => $gmt,
										'rating' => $rating
									);					
								}else{
									$avatar = $this->get_gravatar_url($email);
									$listcomment[] = array(
										'user' => $comment->comment_author,
										'link_avatar' => $avatar,
										'comment_content' => $comment->comment_content,
										'comment_date' => $date,
										'comment_date_gmt' => $gmt,
										'rating' => $rating
									);
								}					
							}
							else{
								$avatar = $this->get_gravatar_url($email);
								$listcomment[] = array(
									'user' => $comment->comment_author,
									'link_avatar' => $avatar,
									'comment_content' => $comment->comment_content,
									'comment_date' => $date,
									'comment_date_gmt' => $gmt,
									'rating' => $rating
								);
							}
						}
						else{
							$avatar = $this->get_gravatar_url($email);							
							$listcomment[] = array(
								'user' => $comment->comment_author,
								'link_avatar' => $avatar,
								'comment_content' => $comment->comment_content,
								'comment_date' => $date,
								'comment_date_gmt' => $gmt,
								'rating' => $rating											
							);								
						}
					}	
				}
				else{
					$listcomment = null;
				}					
				return $listcomment;
			}
		}		
	}

	private function get_gravatar_url( $email ) {
        $id_default = get_option('avatar_default');
        $ratting = strtolower(get_option('avatar_rating'));
        $hash = md5( strtolower( trim ( $email ) ) );
        return 'http://gravatar.com/avatar/' . $hash . '?s=96&d='.$id_default.'&r='.$ratting;
    }
	
	public function get_grouped_product($object, $field_name, $request){
		if(empty($object['grouped_products'])){
			return null;
		}else{	
			$groups = $object['grouped_products'];
			$wp_upload = wp_upload_dir();
			$list = array();	
			foreach($groups as $group => $value)
			{
				$id = $value;
				$product = wc_get_product($id);
				$thumbId = get_post_thumbnail_id($id);
				$external_url = '';
				$buttontext = '';
				if($product->get_type() == 'external')
				{
					$proex = new WC_Product_External($id);
					$external_url = $proex->get_product_url();
					$buttontext = $proex->get_button_text();
				}
				$limages['wooconnector_medium'] = $wp_upload['baseurl']."/". get_post_meta($thumbId, 'wooconnector_medium', true);
				
				$list[] = array(
					'id' => $product->get_id(),
					'title' => $product->get_name(),
					'price' => $product->get_price(),
					'regular_price' => $product->get_regular_price(),
					'sale_price' => $product->get_sale_price(),
					'backorders' => $product->get_backorders(),
					'backorders_allowed' => $product->backorders_allowed(),
					'backordered' => $product->is_on_backorder(),
					'type' => $product->get_type(),
					'in_stock' => $product->is_in_stock(),
					'stock' => $product->get_stock_status(),
					'manage_stock' => $product->get_manage_stock(),
					'stock_quantity' => $product->get_stock_quantity(),				
					'sold_individually' => $product->get_sold_individually(),
					'external_url' => $external_url,
					'button_text' => $buttontext,				
					'wooconnector_crop_images' => $limages
						
				);
			}			
			return $list;
		}
	}
	
	public function get_rating_count($object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			$product_id = $object['id'];		
			$product = wc_get_product($product_id);
			$rating = $product->get_review_count();
			return $rating;
		}
		
	}
	
	public function get_average_rating($object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			$argscomment = array(
				'post_id' => $object['id'],
				'status' => 'approve',
				'offset' => 0			
			);	
			$avgrating = "";
			$comments = get_comments( $argscomment );
			if(!empty($comments))
			{
				$numcomment = 0;
				$ratings = 0;
				foreach($comments as $comment)
				{
					$cmentId = $comment->comment_ID;
					$rating = get_comment_meta( $cmentId, 'rating', true );
					$ratings += $rating;
					$numcomment++;
				}
				$avgrating = floatval($ratings / $numcomment);	
			}
			else{
				$avgrating = "";
			}			
			return $avgrating;
		}	
	}
	
	public function get_description($object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			$product = wc_get_product($object['id']);			
			$description = wpautop( do_shortcode( $product->get_description()) );
			return $description;
		}	
	}
	
	public function get_previous($object, $field_name, $request){
		$categories = isset($object['categories']) ? $object['categories'] : array();
		$previous_post_id = null;
		if(!empty($categories)){
			foreach($categories as $category)
			{
				$catid = $category['id'];
				global $wpdb;				
				$sql = "
					SELECT * FROM ".$wpdb->prefix."posts as post
					INNER JOIN ".$wpdb->prefix."term_relationships as rela ON post.ID = rela.object_id
					WHERE post_type = 'product' AND post_status = 'publish' AND term_taxonomy_id = $catid
				";				
				$products = $wpdb->get_results($sql);	
			}
			if(!empty($products)){
				$ids = array();				
				foreach($products as $product){
					$ids[] = $product->ID;
				}
				$listid = array_unique( $ids );
				$thisindex = array_search($object['id'], $listid);
				$checkindex = count($products);
				if($thisindex < ($checkindex-1)){
					$previous_post_id = $listid[$thisindex+1];
				}else{
					$previous_post_id = null;
				}
			}else{
				$previous_post_id = null;
			}
		}else{
			$previous_post_id = null;
		}
		return $previous_post_id;
	}
	
	public function get_next($object, $field_name, $request){
		$categories = isset($object['categories']) ? $object['categories'] : array();
		$next_post_id = null;
		if(!empty($categories)){
			foreach($categories as $category){
				$catid = $category['id'];
				global $wpdb;				
				$sql = "
					SELECT * FROM ".$wpdb->prefix."posts as post
					INNER JOIN ".$wpdb->prefix."term_relationships as rela ON post.ID = rela.object_id
					WHERE post_type = 'product' AND post_status = 'publish' AND term_taxonomy_id = $catid
				";				
				$products = $wpdb->get_results($sql);	
			}
			if(!empty($products)){
				$ids = array();				
				foreach($products as $product){
					$ids[] = $product->ID;
				}			
				$listid = array_unique( $ids );
				$thisindex = array_search($object['id'], $listid);
				if($thisindex !== 0){
					$next_post_id = $listid[$thisindex - 1];
				}else{
					$next_post_id = null;
				}				
			}else{
				$next_post_id = null;
			}
		}else{
			$next_post_id = null;
		}
		return $next_post_id;
	}

	public function get_price_html($object, $field_name, $request){
		if(empty($object['id'])){
			return null;
		}else{
			$products = wc_get_product($object['id']);
			if($products->is_type( 'grouped' ) && $products->has_child()){
				$group = new WC_Product_Grouped($object['id']);
				$result = $group->get_price_html();
			}else{
				$result = $products->get_price_html();
			}
			return $result;
		}
	}
}
$WooconnectorProducts = new WooconnectorProducts();
?>