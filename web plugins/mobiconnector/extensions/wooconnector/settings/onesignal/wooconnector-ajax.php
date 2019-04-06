<?php
	define( 'DOING_AJAX', true );
	if ( ! defined( 'WP_ADMIN' ) ) {
		define( 'WP_ADMIN', true );
	}	
	
	if (isset($_REQUEST['wooconnectorvalue']) && isset($_REQUEST['wooconnectorselected']) && isset($_REQUEST['baseurl'])) {
			
		$value = $_REQUEST['wooconnectorvalue'];
		$selected = $_REQUEST['wooconnectorselected'];
		$baseurl = $_REQUEST['baseurl'];
		/** Load WordPress Bootstrap */
		require_once( $baseurl . '/wp-load.php' );

		/** Allow for cross-domain requests (from the front end). */
		send_origin_headers();	
		/** Load WordPress Administration APIs */
		require_once( $baseurl . 'wp-admin/includes/admin.php' );

		/** Load Ajax Handlers for WordPress Core */
		require_once( $baseurl . 'wp-admin/includes/ajax-actions.php' );
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );

		send_nosniff_header();
		nocache_headers();
		if($selected == 'url-product'){
			$table_name = $wpdb->prefix . "posts";		
			$datas = $wpdb->get_results(
				"
				SELECT * 
				FROM $table_name
				WHERE post_title LIKE '%$value%' AND post_type = 'product' AND post_status = 'publish'
				"
			);
			if(!empty($datas))
			{
				$wp_upload_dir = wp_upload_dir();	
				foreach($datas as $data)
				{				
					$imagelink = get_the_post_thumbnail_url($data->ID,'post-thumbnail');
					$title = apply_filters('post_title',$data->post_title);
					$product = wc_get_product($data->ID);
					$price = $product->get_price();
					$link = get_permalink($data->ID);

			?>
			<div class="ajax-url-notification">				
				<div class="content-url-notification">
					<h4 class="ajax-title-url-notification" ><?php echo $title." (#".$data->ID.")";?></h4>					
					<input type="hidden" class="ajax-link-hidden-url" value="<?php echo $link;?>"/>
				</div>
			</div>
			<?php } ?>
	
		<?php 
			}else{	
		?>
		<div id="comtent-list-url-notification">		
			<div id="content-url-notification">
				<span><?php echo __('Sorry' . $value.' not exist, Please try again...','wooconnector'); ?></span>
			</div>
		</div>
<?php
			}
		}elseif($selected == 'url-category'){
			$table_name = $wpdb->prefix . "terms";
			$inner_name = $wpdb->prefix . "term_taxonomy";
			$datas = $wpdb->get_results(
				"
				SELECT $table_name.term_id AS ID,name,slug,description  
				FROM $table_name INNER JOIN $inner_name ON $table_name.term_id = $inner_name.term_id
				WHERE name LIKE '%$value%' AND taxonomy = 'product_cat'
				"
			);
			if(!empty($datas))
			{
				$wp_upload_dir = wp_upload_dir();	
				foreach($datas as $data)
				{			
					$title = apply_filters('post_title',$data->name);
					$description = $data->description;
					$link = get_term_link((int)$data->ID,'product_cat');

			?>
			<div class="ajax-url-notification">				
				<div class="content-url-notification">
					<h4 class="ajax-title-url-notification" ><?php echo $title." (#".$data->ID.")";?></h4>					
					<input type="hidden" class="ajax-link-hidden-url" value="<?php echo $link;?>"/>
				</div>
			</div>
			<?php
				}
			}else{
			?>
			<div id="comtent-list-url-notification">		
				<div id="content-url-notification">
					<span><?php echo __('Sorry' . $value.' not exist, Please try again...','wooconnector'); ?> </span>
				</div>
			</div>
			<?php
			}				
		}
	}	
	
?>