<?php

/**
 * Process data product in API
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
class WooConnectorDataProducts
{

	/**
	 * Then data of product
	 */
	private function wooconnector_get_data_product($product, $product_id, $clear_builder = false)
	{
		$pattern = '/\\[(\\[?)(vc_row|vc_column|vc_column_text)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)/';
		$content = apply_filters('mobiconnector_languages_content', $product->get_description($product));
		if ($clear_builder === true && strpos($content, '[vc_row') !== false) {
			$content = preg_replace($pattern, '', $content);
		}
		if ($content !== '') {
			$content = wooconnector_get_plaintext($content);
			if (strpos($content, '.') != false) {
				if (strlen($content) > 100) {
					$content = substr($content, 0, strpos($content, '.'));
				}
			}
		}
		$shortcontent = apply_filters('mobiconnector_languages_content', $product->get_short_description($product));
		if ($clear_builder === true && strpos($shortcontent, '[vc_row') !== false) {
			$shortcontent = preg_replace($pattern, '', $shortcontent);
		}
		if ($shortcontent !== '') {
			$shortcontent = wooconnector_get_plaintext($shortcontent);
			if (strpos($shortcontent, '.') != false) {
				if (strlen($shortcontent) > 100) {
					$shortcontent = substr($shortcontent, 0, strpos($shortcontent, '.'));
				}
			}
		}
		if (!$product->is_type('variation')) {
			$product_external = new WC_Product_External($product_id);
			$group = new WC_Product_Grouped($product_id);
		} else {
			$product_external = false;
			$group = false;
		}
		$data = array(
			'id' => $product_id,
			'name' => apply_filters('mobiconnector_languages', $product->get_name()),
			'slug' => $product->get_slug(),
			'permalink' => apply_filters('mobiconnector_languages_link', $product->get_permalink()),
			'date_created' => wc_rest_prepare_date_response($product->get_date_created($product), false),
			'date_created_gmt' => wc_rest_prepare_date_response($product->get_date_created($product)),
			'date_modified' => wc_rest_prepare_date_response($product->get_date_modified($product), false),
			'date_modified_gmt' => wc_rest_prepare_date_response($product->get_date_modified($product)),
			'type' => $product->get_type($product),
			'featured' => $product->get_featured($product),
			'catalog_visibility' => $product->get_catalog_visibility($product),
			'description' => $content,
			'short_description' => $shortcontent,
			'sku' => $product->get_sku($product),
			'price' => $product->get_price($product),
			'regular_price' => $product->get_regular_price($product),
			'sale_price' => $product->get_sale_price($product),
			'date_on_sale_from' => wc_rest_prepare_date_response($product->get_date_on_sale_from($product)),
			'date_on_sale_from_gmt' => wc_rest_prepare_date_response($product->get_date_on_sale_from($product)),
			'date_on_sale_to' => wc_rest_prepare_date_response($product->get_date_on_sale_to($product)),
			'date_on_sale_to_gmt' => wc_rest_prepare_date_response($product->get_date_on_sale_to($product)),
			'price_html' => ($product->get_price($product) !== "") ? $product->get_price_html($product) : "",
			'on_sale' => $product->is_on_sale($product),
			'purchasable' => $product->is_purchasable($product),
			'total_sales' => $product->get_total_sales($product),
			'external_url' => !empty($product_external) ? $product_external->get_product_url() : '',
			'button_text' => !empty($product_external) ? $product_external->get_button_text() : '',
			'manage_stock' => $product->get_manage_stock($product),
			'stock_quantity' => $product->get_stock_quantity($product),
			'in_stock' => $product->is_in_stock($product),
			'backorders' => $product->get_backorders($product),
			'backorders_allowed' => $product->backorders_allowed($product),
			'backordered' => $product->backorders_allowed($product),
			'sold_individually' => $product->get_sold_individually($product),
			'weight' => $product->get_weight($product),
			'average_rating' => $this->get_average_rating($product_id),
			'rating_count' => $product->get_review_count($product),
			'parent_id' => $product->get_parent_id($product),
			'categories' => $this->get_taxonomy_terms($product),
			'tags' => $this->get_taxonomy_terms($product, 'tag'),
			'images' => $this->get_images($product),
			'attributes' => $this->get_attributes($product),
			'default_attributes' => $this->get_default_attributes($product)
		);
		if ($product->is_type('grouped') && $product->has_child()) {
			$data['price_html'] = $group->get_price_html();
		}
		return $data;
	}

	/**
	 * Get data Stores
	 */
	public function wooconnector_data($product_id = 0, $request, $clear_builder = false, $idWPML = array())
	{
		$product = wc_get_product($product_id);
		if (!empty($product) && is_object($product)) {
			$data = $this->wooconnector_get_data_product($product, $product_id, $clear_builder);
			$params = $request->get_params();
			if (isset($params['id']) || isset($params['product_id'])) {
				$id_variation = isset($idWPML['id_variation']) ? $idWPML['id_variation'] : array();
				$id_group = isset($idWPML['id_group']) ? $idWPML['id_group'] : array();
				$data['variations'] = $this->wooconnector_get_variation_data($product, $request, $clear_builder, $id_variation);
				$data['grouped_products'] = $this->wooconnector_get_groupproduct_data($product, $request, $clear_builder, $id_group);
				$pattern = '/\\[(\\[?)(vc_row|vc_column|vc_column_text)(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)/';
				$data['reviews_allowed'] = $product->get_reviews_allowed($product);
				$data['dimensions'] = array(
					'length' => $product->get_length($product),
					'width' => $product->get_width($product),
					'height' => $product->get_height($product),
				);
				$post = get_post($product_id);
				$data['password'] = $post->post_password;
				$content = apply_filters('mobiconnector_languages_content', $product->get_description($product));
				if ($clear_builder === true && strpos($content, '[vc_row') !== false) {
					$content = preg_replace($pattern, '', $content);
				}
				$data['description'] = $content;
				$shortcontent = apply_filters('mobiconnector_languages_content', $product->get_short_description($product));
				if ($clear_builder === true && strpos($shortcontent, '[vc_row') !== false) {
					$shortcontent = preg_replace($pattern, '', $shortcontent);
				}
				$data['short_description'] = $shortcontent;
				if (is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php')) {
					$listaddons = array();
					$listaddonsout = array();
					if (isset($params['woo_currency'])) {
						$currentkey = $params['woo_currency'];
					} else {
						$currentkey = strtolower(get_woocommerce_currency());
					}
					$currencys = WooConnectorGetCurrency($currentkey);
					$ratecurrency = $currencys['rate'];
					$number_of_decimals = $currencys['number_of_decimals'];
					$meta_data = $product->get_meta_data();
					foreach ($meta_data as $meta) {
						if ($meta->key == '_product_addons') {
							$valueaddons = $meta->value;
							foreach ($valueaddons as $val) {
								$listoption = array();
								$options = $val['options'];
								$index = 0;
								foreach ($options as $option) {
									$listoption[] = array(
										'id' => $index,
										'label' => $option['label'],
										'price' => (float)wc_format_decimal(($option['price'] * $ratecurrency), $number_of_decimals),
										'min' => $option['min'],
										'max' => $option['max'],
									);
									$index++;
								}
								$listaddons[] = array(
									'name' => $val['name'],
									'description' => $val['description'],
									'type' => $val['type'],
									'position' => $val['position'],
									'options' => $listoption,
									'required' => $val['required']
								);
							}
							$listaddonsout = array(
								'id' => $meta->id,
								'value' => $listaddons
							);
						}
					}
					$data['wooconnector_addons'] = $listaddonsout;
				}
			}
			return $data;
		}
	}

	/**
	 * Get an individual variation data
	 */
	private function wooconnector_get_variation_data($product, $request, $clear_builder = false, $id_wpml_variations = array())
	{
		$variations = array();
		if ($product->is_type('variable') && $product->has_child()) {
			foreach ($product->get_children() as $child_id) {
				if (is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') && !in_array($child_id, $id_wpml_variations)) {
					continue;
				}
				$variation = wc_get_product($child_id);
				if (!$variation || !$variation->exists()) {
					continue;
				}
				$datavariation = $this->wooconnector_get_data_product($variation, $child_id, $clear_builder);
				$content = apply_filters('mobiconnector_languages_content', $variation->get_description($variation));
				$shortcontent = apply_filters('mobiconnector_languages_content', $variation->get_short_description($variation));
				$images = $this->wooconnector_then_images($variation);
				$datavariation['description'] = $content;
				$datavariation['short_description'] = $shortcontent;
				$datavariation['wooconnector_crop_images'] = $images;
				$variations[] = apply_filters('wooconnector_product_variation', $datavariation, $request);
			}
		}
		return $variations;
	}

	/**
	 * Get an individual group's data.
	 *
	 * @param WC_Product $product Product instance.
	 * @return array
	 */
	private function wooconnector_get_groupproduct_data($product, $request, $clear_builder = false, $id_wpml_group = array())
	{
		$groups = array();
		if ($product->is_type('grouped') && $product->has_child()) {
			foreach ($product->get_children() as $child_id) {
				if (is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') && !in_array($child_id, $id_wpml_group)) {
					continue;
				}
				$group = wc_get_product($child_id);
				if (!$group || !$group->exists()) {
					continue;
				}

				$datagroup = $this->wooconnector_get_data_product($group, $child_id, $clear_builder);
				$content = apply_filters('mobiconnector_languages_content', $group->get_description($group));
				$shortcontent = apply_filters('mobiconnector_languages_content', $group->get_short_description($group));
				$images = $this->wooconnector_then_images($group);
				$datagroup['description'] = $content;
				$datagroup['short_description'] = $shortcontent;
				$datagroup['wooconnector_crop_images'] = $images;
				$datag = apply_filters('wooconnector_product_group', $datagroup, $request);
				$groups[] = $datag;
			}
		}
		return $groups;
	}

	/**
	 * Then images with product
	 */
	private function wooconnector_then_images($product)
	{
		$wp_upload_dir = wp_upload_dir();
		$images = $product->get_image_id();
		$imagelinks = array();
		$image_id = $images;
		$wooconnector_large = get_post_meta($image_id, 'wooconnector_large', true);
		$wooconnector_medium = get_post_meta($image_id, 'wooconnector_medium', true);
		$wooconnector_x_large = get_post_meta($image_id, 'wooconnector_x_large', true);
		$wooconnector_small = get_post_meta($image_id, 'wooconnector_small', true);
		if (empty($wooconnector_large) || empty($wooconnector_medium) || empty($wooconnector_x_large) || empty($wooconnector_small)) {
			$this->update_thumnail_woo($image_id);
		}
		foreach ($this->thumnailsX as $key => $value) {
			$imagesupload = get_post_meta($image_id, $key, true);
			if (empty($imagesupload)) {
				$crop_image[$key] = null;
			} else {
				$crop_image[$key] = $wp_upload_dir['baseurl'] . "/" . $imagesupload;
			}
		}
		$imagelinks[] = $crop_image;
		return $imagelinks;
	}

	/**
	 * Get average ratting
	 */
	private function get_average_rating($product_id)
	{
		$ratting = get_post_meta($product_id, '_wc_average_rating', true);
		if (empty($ratting)) {
			$ratting = 0;
		}
		return $ratting;
	}

	/**
	 * Get taxonomy terms.
	 *
	 * @param WC_Product $product  Product instance.
	 * @param string     $taxonomy Taxonomy slug.
	 * @return array
	 */
	private function get_taxonomy_terms($product, $taxonomy = 'cat')
	{
		$terms = array();
		foreach (wc_get_object_terms($product->get_id($product), 'product_' . $taxonomy) as $term) {
			if ($taxonomy == 'tag') {
				$terms[] = array(
					'id' => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				);
			} else {
				$terms[] = array(
					'id' => $term->term_id,
					'name' => $term->name,
					'slug' => $term->slug,
				);
			}
		}
		return $terms;
	}

	/**
	 * Get the images for a product or product variation.
	 *
	 * @param WC_Product|WC_Product_Variation $product Product instance.
	 * @return array
	 */
	private function get_images($product)
	{
		$images = array();
		$attachment_ids = array();

		// Add featured image.
		if (has_post_thumbnail($product->get_id($product))) {
			$attachment_ids[] = $product->get_image_id($product);
		}

		// Add gallery images.
		$attachment_ids = array_merge($attachment_ids, $product->get_gallery_image_ids($product));

		// Build image data.
		foreach ($attachment_ids as $position => $attachment_id) {
			$attachment_post = get_post($attachment_id);
			if (is_null($attachment_post)) {
				continue;
			}

			$attachment = wp_get_attachment_image_src($attachment_id, 'full');
			if (!is_array($attachment)) {
				continue;
			}

			$images[] = array(
				'id' => (int)$attachment_id,
				'date_created' => wc_rest_prepare_date_response($attachment_post->post_date, false),
				'date_created_gmt' => wc_rest_prepare_date_response(strtotime($attachment_post->post_date_gmt)),
				'date_modified' => wc_rest_prepare_date_response($attachment_post->post_modified, false),
				'date_modified_gmt' => wc_rest_prepare_date_response(strtotime($attachment_post->post_modified_gmt)),
				'src' => current($attachment),
				'name' => get_the_title($attachment_id),
				'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
				'position' => (int)$position,
			);
		}

		// Set a placeholder image if the product has no images set.
		if (empty($images)) {
			$images[] = array(
				'id' => 0,
				'date_created' => wc_rest_prepare_date_response(current_time('mysql'), false), // Default to now.
				'date_created_gmt' => wc_rest_prepare_date_response(current_time('timestamp', true)), // Default to now.
				'date_modified' => wc_rest_prepare_date_response(current_time('mysql'), false),
				'date_modified_gmt' => wc_rest_prepare_date_response(current_time('timestamp', true)),
				'src' => wc_placeholder_img_src(),
				'name' => __('Placeholder', 'woocommerce'),
				'alt' => __('Placeholder', 'woocommerce'),
				'position' => 0,
			);
		}

		return $images;
	}

	/**
	 * Get the attributes for a product or product variation.
	 * @return array
	 */
	private function get_attributes($product)
	{
		$attributes = array();
		if ($product->is_type('variation')) {
			$_product = wc_get_product($product->get_parent_id($product));
			foreach ($product->get_variation_attributes($product) as $attribute_name => $attribute) {
				$name = str_replace('attribute_', '', $attribute_name);

				if (!$attribute) {
					continue;
				}
				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if (strpos($attribute_name, 'attribute_pa_') !== false) {
					$option_term = get_term_by('slug', $attribute, $name);
					$attributes[] = array(
						'id' => wc_attribute_taxonomy_id_by_name($name),
						'name' => apply_filters('mobiconnector_languages', $this->get_attribute_taxonomy_name($name, $_product)),
						'option' => $option_term && !is_wp_error($option_term) ? $option_term->name : (array)$attribute,
						'options_slug' => $option_term && !is_wp_error($option_term) ? $option_term->slug : (array)$attribute,
						'taxanomy' => $attribute_name,
					);
				} else {
					$attributes[] = array(
						'id' => 0,
						'name' => apply_filters('mobiconnector_languages', $this->get_attribute_taxonomy_name($name, $_product)),
						'option' => (array)$attribute,
						'options_slug' => (array)$this->convert_name_to_slug($attribute),
						'taxanomy' => $attribute_name,
					);
				}
			}
		} else {
			foreach ($product->get_attributes($product) as $attribute => $value) {
				$attributes[] = array(
					'id' => $value['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name($value['name']) : 0,
					'name' => $this->get_attribute_taxonomy_name($value['name'], $product),
					'taxanomy' => $attribute,
					'position' => (int)$value['position'],
					'visible' => (bool)$value['is_visible'],
					'variation' => (bool)$value['is_variation'],
					'options' => $this->get_attribute_options($product->get_id($product), $value),
					'options_slug' => $this->get_attribute_options_slug($product->get_id($product), $value)
				);
			}
		}

		return $attributes;
	}

	/**
	 * Get attribute options.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $attribute  Attribute data.
	 * @return array
	 */
	private function get_attribute_options($product_id, $attribute)
	{
		if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
			return wc_get_product_terms($product_id, $attribute['name'], array('fields' => 'names'));
		} elseif (isset($attribute['value'])) {
			return array_map('trim', explode('|', $attribute['value']));
		}

		return array();
	}

	/**
	 * Get attribute options.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $attribute  Attribute data.
	 * @return array
	 */
	private function get_attribute_options_slug($product_id, $attribute)
	{
		if (isset($attribute['is_taxonomy']) && $attribute['is_taxonomy']) {
			return wc_get_product_terms($product_id, $attribute['name'], array('fields' => 'slugs'));
		} elseif (isset($attribute['value'])) {
			$valuecustoms = array_map('trim', explode('|', $attribute['value']));
			$result = array();
			foreach ($valuecustoms as $custom) {
				$result[] = $this->convert_name_to_slug($custom);
			}
			return $result;
		}

		return array();
	}

	/**
	 * Get default attributes.
	 *
	 * @param WC_Product $product Product instance.
	 * @return array
	 */
	private function get_default_attributes($product)
	{
		$default = array();
		if ($product->is_type('variable')) {
			foreach (array_filter((array)$product->get_default_attributes(), 'strlen') as $key => $value) {
				if (0 === strpos($key, 'pa_')) {
					$term = get_term_by('slug', $value, $key);
					$default[] = array(
						'id' => wc_attribute_taxonomy_id_by_name($key),
						'name' => $this->get_attribute_taxonomy_name($key, $product),
						'option' => $term->name,
						'option_slug' => $value
					);
				} else {
					$default[] = array(
						'id' => 0,
						'name' => $this->get_attribute_taxonomy_name($key, $product),
						'option' => $value,
						'option_slug' => $this->convert_name_to_slug($value)
					);
				}
			}
		}
		return $default;
	}

	/**
	 * Get product attribute taxonomy name.
	 *	
	 * @param  string     $slug    Taxonomy name.
	 * @param  WC_Product $product Product data.
	 * @return string
	 */
	protected function get_attribute_taxonomy_name($slug, $product)
	{
		$attributes = $product->get_attributes();

		if (!isset($attributes[$slug])) {
			return str_replace('pa_', '', $slug);
		}

		$attribute = $attributes[$slug];

		// Taxonomy attribute name.
		if ($attribute->is_taxonomy()) {
			$taxonomy = $attribute->get_taxonomy_object();
			return $taxonomy->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	}

	/**
	 * Convert name to slug
	 */
	private function convert_name_to_slug($name)
	{
		$namelow = strtolower($name);
		$namereplace = str_replace(' ', '-', $namelow);
		$nametrim = trim($namereplace, '-');
		$slug = $this->checktermsslug($nametrim);
		return $slug;
	}

	/**
	 * Check term slug exist
	 */
	private function checktermsslug($slug)
	{
		$checkterm = get_term_by('slug', $slug);
		if (!empty($checkterm)) {
			for ($i = 1; $i < 1000; $i++) {
				$slug .= '-' . $i;
				$checkterm2 = get_term_by('slug', $slug);
				if (!empty($checkterm2)) {
					continue;
				}
				return $slug;
			}
		} else {
			return $slug;
		}
	}

	/**
	 * Update images
	 */
	public function update_thumnail_woo($post_thumbnail_id)
	{
		if (empty($post_thumbnail_id))
			return true;

		$post_type = get_post_type($post_thumbnail_id);
		if ($post_type == 'attachment') {
			$post_thumbnail_id = $post_thumbnail_id;
		} elseif ($post_type == 'product') {
			$post_thumbnail_id = get_post_thumbnail_id($post_thumbnail_id);
		}
		/// ki?m tra xem dã t?n t?i thumnail chua
		$wooconnector_large = get_post_meta($post_thumbnail_id, 'wooconnector_large', true);
		$wooconnector_medium = get_post_meta($post_thumbnail_id, 'wooconnector_medium', true);
		$wooconnector_x_large = get_post_meta($post_thumbnail_id, 'wooconnector_x_large', true);
		$wooconnector_small = get_post_meta($post_thumbnail_id, 'wooconnector_small', true);
		if (!empty($wooconnector_medium) && !empty($wooconnector_x_large) && !empty($wooconnector_large) && !empty($wooconnector_small))
			return true; // dã t?n t?i r?i ko t?o n?a
		// l?y thông tin c?a ?nh
		$relative_pathto_file = get_post_meta($post_thumbnail_id, '_wp_attached_file', true);
		$wp_upload_dir = wp_upload_dir();
		$absolute_pathto_file = $wp_upload_dir['basedir'] . '/' . $relative_pathto_file;
		// ki?m tra file g?c có t?n t?i hay không?
		if (!file_exists($absolute_pathto_file))
			return true; // file ko t?n t?i
		////////////////			
		$path_parts = pathinfo($relative_pathto_file);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		// t?o ?nh 
		list($width, $height) = getimagesize($absolute_pathto_file);
		if ($width > $height) {
			foreach ($this->thumnailsX as $key => $value) {
				$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
				$dest = $wp_upload_dir['basedir'] . '/' . $path;
				WooConnectorCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta($post_thumbnail_id, $key, $path);
			}
		} elseif ($width < $height) {
			foreach ($this->thumnailsY as $key => $value) {
				$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
				$dest = $wp_upload_dir['basedir'] . '/' . $path;
				WooConnectorCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta($post_thumbnail_id, $key, $path);
			}
		} elseif ($width == $height) {
			foreach ($this->thumnailsS as $key => $value) {
				$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
				$dest = $wp_upload_dir['basedir'] . '/' . $path;
				WooConnectorCore::resize_image($absolute_pathto_file, $dest, $value['width'], $value['height']);
				// cập nhật post meta for thumnail
				update_post_meta($post_thumbnail_id, $key, $path);
			}
		}
		return true;
	}

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
}
?>