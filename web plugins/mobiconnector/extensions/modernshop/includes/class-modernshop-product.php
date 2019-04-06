<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!class_exists('WooConnectorProduct')) {
	if (file_exists(WP_PLUGIN_DIR . '/wooconnector/wooconnector.php') && is_plugin_active('wooconnector/wooconnector.php')) {
		require_once(WP_PLUGIN_DIR . '/wooconnector/endpoints/class-wooconnector-products.php');
	} elseif (file_exists(WP_PLUGIN_DIR . '/mobiconnector/extensions/wooconnector/wooconnector.php') && modernshop_is_extension_active('wooconnector/wooconnector.php')) {
		require_once(WP_PLUGIN_DIR . '/mobiconnector/extensions/wooconnector/endpoints/class-wooconnector-products.php');
	} else {
		return false;
	}
}

function modernshop_is_extension_active($extension)
{
	$current = array();
	$current = get_option('mobiconnector_extensions_active');
	if (!empty($current) && is_string($current)) {
		$current = unserialize($current);
		$current = (array)$current;
	}
	if (is_array($current)) {
		return in_array($extension, $current);
	} else {
		return false;
	}
}
class ModernProducts extends WooConnectorProduct
{

	public $thumnails = array(
		'modern_square_small' => array(
			'width' => 150,
			'height' => 150
		),
		'modern_square' => array(
			'width' => 480,
			'height' => 480
		),
	);

	public function __construct()
	{

		$this->register_routes();
		// add active create photo when save Products
		add_action('save_post_product', array($this, 'update_thumnail_moderm'), 10, 3);
		add_action('delete_post', array($this, 'modernshop_delete_post_clear_images'), 10);
		add_filter('wooconnector_product_data', array($this, 'get_crop_images_hook'), 100, 2);
		add_filter('wooconnector_product_group', array($this, 'get_crop_images_hook'), 100, 2);
		add_filter('wooconnector_product_variation', array($this, 'get_crop_images_hook'), 100, 2);
		add_filter('wooconnector_product_variation_id', array($this, 'get_crop_images_hook'), 100, 2);
		add_filter('wooconnector_product_data_id', array($this, 'get_look_crop_image_hook'), 100, 2);
	}

	public function modernshop_delete_post_clear_images($post_id)
	{
		$post_type = get_post_type($post_id);
		$wp_dir = wp_upload_dir();
		if ($post_type == 'attachment') {
			$pti = $post_id;
		} elseif ($post_type == 'product') {
			$pti = get_post_thumbnail_id($post_id);
		} else {
			return true;
		}
		if ($post_type == 'product') {
			$wgp = wc_get_product($post_id);
			$imageids = $wgp->get_gallery_image_ids();
		}
		$thumbId = array($pti);
		$listids = array_merge($imageids, $thumbId);
		$list_ids = array_unique($listids);
		foreach ($list_ids as $id) {
			if (empty($id)) {
				continue;
			}
			$modern_square = get_post_meta($id, 'modern_square', true);
			@unlink($wp_dir['basedir'] . '/' . $modern_square);
			delete_post_meta($id, 'modern_square', $modern_square);
		}
	}

	public function get_crop_images_hook($data, $request)
	{
		if (!empty($data['images'])) {
			$image_ids = array();
			foreach ($data['images'] as $listimages) {
				$image_ids[] = (int)$listimages['id'];
			}
		} else {
			$image_ids = array();
		}
		$list = array();
		if (!empty($image_ids)) {
			foreach ($image_ids as $image_id) {
				if ($image_id === 0) {
					continue;
				}
				// resize image
				$wp_upload_dir = wp_upload_dir();
				$modern_square = get_post_meta($image_id, 'modern_square', true);
				if (empty($modern_square)) { // chua t?n t?i ?nh th� t?o
					$product_ID = $data['id'];
					$product = get_post($product_ID);
					$this->update_thumnail_moderm($product_ID, $product);
				}
				// g?n thumnail m?i
				foreach ($this->thumnails as $key => $value) {
					$imagesupload = get_post_meta($image_id, $key, true);
					if (empty($imagesupload)) {
						$crop_image[$key] = null;
					} else {
						$crop_image[$key] = $wp_upload_dir['baseurl'] . "/" . $imagesupload;
					}
				}
				$list[] = $crop_image;
			}
			$data['modernshop_images'] = $list;
		}
		if (empty($list)) {
			foreach ($image_ids as $image_id) {
				foreach ($this->thumnails as $key => $value) {
					$imagesupload = get_post_meta($image_id, $key, true);
					if (empty($imagesupload)) {
						$crop_image[$key] = null;
					}
				}
				$list[] = $crop_image;
			}
			$data['modernshop_images'] = $list;
		}
		return $data;
	}

	public function get_look_crop_image_hook($data, $request)
	{
		$categories = isset($data['categories']) ? $data['categories'] : false;
		$idcheck = array();
		$params = $request->get_params();
		$post_per_page = $params['post_per_page'];
		$post_num_page = $params['post_num_page'];
		$numlook = 0;
		$look_product = array();
		if (!empty($categories)) {
			$listcatalog = WooConnectorProduct::getproduct_exclude_from_catalog();
			$listsearch = WooConnectorProduct::getproduct_excule_from_search();
			foreach ($categories as $category) {
				$namecat = $category['slug'];
				$argslook = array(
					'posts_per_page' => $post_per_page,
					'paged' => $post_num_page,
					'category' => '',
					'category_name' => '',
					'product_cat' => $namecat,
					'orderby' => 'date',
					'order' => 'DESC',
					'meta_key' => '',
					'meta_value' => '',
					'post_type' => 'product',
					'post_mime_type' => '',
					'post_parent' => '',
					'author' => '',
					'author_name' => '',
					'post_status' => 'publish',
					'suppress_filters' => true,
					'post__not_in' => array($data['id'])
				);
				$pro4s = get_posts($argslook);
				foreach ($pro4s as $pro4) {
					// If Product disable by catalog
					if (!empty($listcatalog) && !isset($params['search'])) {
						if (in_array($pro4->ID, $listcatalog)) {
							continue;
						}
					}
					// If Product disable by search
					if (!empty($listsearch) && isset($params['search'])) {
						if (in_array($pro4->ID, $listsearch)) {
							continue;
						}
					}
					if ($pro4->ID != $data['id']) {
						if (!in_array($pro4->ID, $idcheck)) {
							if ($numlook < $post_per_page) {
								$prova4 = wc_get_product($pro4->ID);
								$thumbId = get_post_thumbnail_id($pro4->ID);
								$wp_upload = wp_upload_dir();
								foreach ($this->thumnails as $key => $value) {
									$path = get_post_meta($thumbId, $key, true);
									if (empty($path)) {
										$limages[$key] = null;
									} else {
										$limages[$key] = $wp_upload['baseurl'] . "/" . get_post_meta($thumbId, $key, true);
									}
								}
								if ($prova4->is_type('grouped') && $prova4->has_child()) {
									$group = new WC_Product_Grouped($pro4->ID);
									$result = $group->get_price_html();
								} else {
									$result = $prova4->get_price_html();
								}
								$products = array(
									'id' => $prova4->get_id(),
									'title' => apply_filters('mobiconnector_languages', $prova4->get_title()),
									'type' => $prova4->get_type(),
									'price' => $prova4->get_price(),
									'regular_price' => $prova4->get_regular_price(),
									'sale_price' => $prova4->get_sale_price(),
									'price_html' => $result,
									'stock_quantity' => $prova4->get_stock_quantity(),
									'in_stock' => $prova4->is_in_stock(),
									'backorders' => $prova4->get_backorders(),
									'backorders_allowed' => $prova4->backorders_allowed(),
									'backordered' => $prova4->backorders_allowed(),
									'sold_individually' => $prova4->get_sold_individually(),
									'weight' => $prova4->get_weight(),
									'images' => $limages,
								);
								if ($prova4->is_type('variable') && $prova4->has_child()) {
									$products['variations'] = $prova4->get_children();
								}
								if ($prova4->is_type('grouped') && $prova4->has_child()) {
									$products['grouped_products'] = $prova4->get_children();
								}
								$look_product[] = $products;
								array_push($idcheck, $pro4->ID);
								$numlook++;
							} else {
								break;
							}
						} else {
							continue;
						}
					} else {
						continue;
					}
				}
			}
		} else {
			$look_product = null;
		}
		$data['modernshop_look_images'] = $look_product;
		return $data;

	}

	public function update_thumnail_moderm($productID, $product)
	{
		$wgp = wc_get_product($productID);
		$imageids = $wgp->get_gallery_image_ids();
		$thumbId = array(get_post_thumbnail_id($productID));
		$listids = array_merge($imageids, $thumbId);
		foreach ($listids as $listid) {
			$post_thumbnail_id = $listid;
			if (empty($post_thumbnail_id))
				return true;
			/// ki?m tra xem d� t?n t?i thumnail chua		
			$moderm_square = get_post_meta($post_thumbnail_id, 'modern_square', true);
			if (!empty($moderm_square))
				continue; // d� t?n t?i r?i ko t?o n?a
			// l?y th�ng tin c?a ?nh
			$relative_pathto_file = get_post_meta($post_thumbnail_id, '_wp_attached_file', true);
			$wp_upload_dir = wp_upload_dir();
			$absolute_pathto_file = $wp_upload_dir['basedir'] . '/' . $relative_pathto_file;
			// ki?m tra file g?c c� t?n t?i hay kh�ng?
			if (!file_exists($absolute_pathto_file))
				return true; // file ko t?n t?i

			$path_parts = pathinfo($relative_pathto_file);
			$ext = strtolower($path_parts['extension']);
			$basename = strtolower($path_parts['basename']);
			$dirname = strtolower($path_parts['dirname']);
			$filename = strtolower($path_parts['filename']);
			list($width, $height) = getimagesize($absolute_pathto_file);
			if ($width == $height) {
				foreach ($this->thumnails as $key => $value) {
					$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
					$dest = $wp_upload_dir['basedir'] . '/' . $path;
					ModernCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
					// c?p nh?t post meta for thumnail
					update_post_meta($post_thumbnail_id, $key, $path);
				}
			} else {
				foreach ($this->thumnails as $key => $value) {
					$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
					$dest = $wp_upload_dir['basedir'] . '/' . $path;
					ModernCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height'], true);
					// c?p nh?t post meta for thumnail
					update_post_meta($post_thumbnail_id, $key, $path);
				}
			}

		}
		return true;
	}

	public function register_routes()
	{
		add_action('rest_api_init', array($this, 'register_api_hooks'));
	}

	public function register_api_hooks()
	{
		register_rest_field(
			'product',
			'modernshop_images',
			array(
				'get_callback' => array($this, 'get_crop_image'),
				'update_callback' => null,
				'schema' => null,
			)
		);

		register_rest_field(
			'product',
			'modernshop_look_images',
			array(
				'get_callback' => array($this, 'get_look_crop_image'),
				'update_callback' => null,
				'schema' => null,
			)
		);

	}

	public function get_crop_image($object, $field_name, $request)
	{
		if (!empty($object['images'])) {
			$image_ids = array();
			foreach ($object['images'] as $listimages) {
				$image_ids[] = (int)$listimages['id'];
			}
		} else {
			return null;
		}
		$list = array();
		foreach ($image_ids as $image_id) {
			// resize image
			$wp_upload_dir = wp_upload_dir();

			$modern_square = get_post_meta($image_id, 'modern_square', true);
			if (empty($modern_square)) { // chua t?n t?i ?nh th� t?o
				$product_ID = $object['id'];
				$product = get_post($product_ID);
				$this->update_thumnail_moderm($product_ID, $product);
			}
				// g?n thumnail m?i
			foreach ($this->thumnails as $key => $value) {
				$imagesupload = get_post_meta($image_id, $key, true);
				if (empty($imagesupload)) {
					$crop_image[$key] = null;
				} else {
					$crop_image[$key] = $wp_upload_dir['baseurl'] . "/" . $imagesupload;
				}
			}
			$list[] = $crop_image;
		}
		return $list;
	}




	public function get_look_crop_image($object, $field_name, $request)
	{
		$categories = isset($object['categories']) ? $object['categories'] : array();
		$idcheck = array();
		$numlook = 0;
		$look_product = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$namecat = $category['slug'];
				$argslook = array(
					'posts_per_page' => 4,
					'paged' => 1,
					'offset' => 0,
					'category' => '',
					'category_name' => '',
					'product_cat' => $namecat,
					'orderby' => 'date',
					'order' => 'DESC',
					'meta_key' => '',
					'meta_value' => '',
					'post_type' => 'product',
					'post_mime_type' => '',
					'post_parent' => '',
					'author' => '',
					'author_name' => '',
					'post_status' => 'publish',
					'suppress_filters' => true
				);
				$pro4s = get_posts($argslook);
				foreach ($pro4s as $pro4) {
					if ($pro4->ID != $object['id']) {
						if (!in_array($pro4->ID, $idcheck)) {
							if ($numlook < 4) {
								$prova4 = wc_get_product($pro4->ID);
								$thumbId = get_post_thumbnail_id($pro4->ID);
								$wp_upload = wp_upload_dir();
								foreach ($this->thumnails as $key => $value) {
									$path = get_post_meta($thumbId, $key, true);
									if (empty($path)) {
										$limages[$key] = null;
									} else {
										$limages[$key] = $wp_upload['baseurl'] . "/" . get_post_meta($thumbId, $key, true);
									}
								}
								if ($prova4->is_type('grouped') && $prova4->has_child()) {
									$group = new WC_Product_Grouped($pro4->ID);
									$result = $group->get_price_html();
								} else {
									$result = $prova4->get_price_html();
								}
								$products = array(
									'id' => $prova4->get_id(),
									'title' => apply_filters('post_title', $prova4->get_title()),
									'type' => $prova4->get_type(),
									'price' => $prova4->get_price(),
									'regular_price' => $prova4->get_regular_price(),
									'sale_price' => $prova4->get_sale_price(),
									'price_html' => $result,
									'images' => $limages,
								);
								if ($prova4->is_type('variable') && $prova4->has_child()) {
									$products['variations'] = $prova4->get_children();
								}
								if ($prova4->is_type('grouped') && $prova4->has_child()) {
									$products['grouped_products'] = $prova4->get_children();
								}
								$look_product[] = $products;
								array_push($idcheck, $pro4->ID);
								$numlook++;
							} else {
								break;
							}
						} else {
							continue;
						}
					} else {
						continue;
					}
				}
			}
		} else {
			$look_product = null;
		}
		return $look_product;
	}


}
$ModernProducts = new ModernProducts();
?>