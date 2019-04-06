<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * MobiConnector Categories Avatar
 * 
 * Add function add categories in admin 
 * 
 * @class 	Mobiconnector_Categories_Avatar
 */
class BAMobile_Categories_Avatar{
     /**
     * MobiConnector Categories Avatar construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){        
        add_filter('manage_edit-category_columns', array($this, 'bamobile_manage_category_columns'));
        add_filter('manage_category_custom_column', array($this, 'bamobile_manage_category_columns_fields'), 10, 3);
        $taxonomies = get_taxonomies();
        foreach ((array)$taxonomies  as $taxonomy) {
            $this->bamobile_add_custom_column_fields($taxonomy);
        }
        add_action('edit_term', array($this, 'bamobile_save_image'));
        add_action('create_term', array($this, 'bamobile_save_image'));
        add_action( 'admin_enqueue_scripts',array($this,'bamobile_add_styles'));
    }

    /**
     * Add style of categories avatar
     */
    public function bamobile_add_styles($hook){         
        // Style categories avatar
        if (is_admin() && $hook == 'edit-tags.php' || $hook == 'term.php') {   
            
            wp_enqueue_media();
            
            wp_enqueue_script(
                'mobiconnector_categories_avatar_js',
                plugins_url('/assets/js/mobiconnector-categories-avatar.js', MOBICONNECTOR_PLUGIN_BASENAME),
                array('jquery'),
                MOBICONNECTOR_VERSION,
                true
            );

            wp_localize_script(
                'mobiconnector_categories_avatar_js',
                'mobiconnector_categories_avatar_params',
                array(
                    'label'      => array(
                        'title'  => __('Choose Category Image'),
                        'button' => __('Choose Image')
                    )
                )
            );
        }
    }

    /**
     * Add field to create and edit by taxanomy
     * 
     * @param string $taxanomy      taxanomy with create field
     */
    public function bamobile_add_custom_column_fields($taxonomy){
        add_action("{$taxonomy}_add_form_fields", array($this, 'bamobile_add_taxonomy_field'));
        add_action("{$taxonomy}_edit_form_fields", array($this, 'bamobile_edit_taxonomy_field'));

        // Add custom columns to custom taxonomies
        add_filter("manage_edit-{$taxonomy}_columns", array($this, 'bamobile_manage_category_columns'));
        add_filter("manage_{$taxonomy}_custom_column", array($this, 'bamobile_manage_category_columns_fields'), 10, 3);
    }

    /**
     * Get image categories with param
     * 
     * @param array $atts       param with get categories (size,term_id,alt)
     * @param boolean $onlysrc  get src or image html
     * 
     * @return array|string avatar
     */
    public static function bamobile_get_category_image($atts = array(), $onlysrc = false){
        $params = array_merge(array(
                'size'    => 'full',
                'term_id' => null,
                'alt'     => null
        ), $atts);

        $term_id = $params['term_id'];
        $size    = $params['size'];

        if (! $term_id) {
            if (is_category()) {
                $term_id = get_query_var('cat');
            } elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        if (!$term_id) {
            return;
        }

        $attachment_id   = get_option('mobiconnector_category_'.$term_id.'_avatar');

        $attachment_meta = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $attachment_alt  = trim(strip_tags($attachment_meta));

        $attr = array(
            'alt'=> (is_null($params['alt']) ?  $attachment_alt : $params['alt'])
        );

        if ($onlysrc == true) {
            $src = wp_get_attachment_image_src($attachment_id, $size, false);
            return is_array($src) ? $src[0] : null;
        }

        return wp_get_attachment_image($attachment_id, $size, false, $attr);
    }

    /**
     * Create column
     * 
     * @param array $columms     list columns
     * 
     * @return array List columns
     */
    public function bamobile_manage_category_columns($columns){
        $columns['mobiconnector_category_image'] = __('Mobi Image');
        return $columns;
    }

    /**
     * Create data in column
     * 
     * @param string $column_name       name of columns
     * @param int $term_id              id of term
     */
    public function bamobile_manage_category_columns_fields($deprecated, $column_name, $term_id){
        if ($column_name == 'mobiconnector_category_image' && $this->bamobile_has_image($term_id)) {
            echo self::bamobile_get_category_image(array(
                'term_id' => $term_id,
                'size'    => 'thumbnail',
            ));
        }
    }

    /**
     * Process Save taxanomy
     * 
     * @param int $term_id            id of term
     */
    public function bamobile_save_image($term_id){
        $attachment_id = isset($_POST['mobiconnector_attachment']) ? (int) sanitize_text_field($_POST['mobiconnector_attachment']) : null;
        if (! is_null($attachment_id) && $attachment_id > 0 && !empty($attachment_id)) {
            update_option('mobiconnector_category_'.$term_id.'_avatar', $attachment_id);
        }else{
            delete_option('mobiconnector_category_'.$term_id.'_avatar');
        }
    }

    /**
     * Create field with add taxanomy
     * 
     * @param string $taxanomy           name of taxanomy
     * 
     * @return int Id of attachment
     */
    public function bamobile_get_attachment_id($term_id){
        return get_option('mobiconnector_category_'.$term_id.'_avatar');
    }

    /**
     * Check image exist
     * 
     * @param int $term_id           id of term
     * 
     * @return boolean
     */
    public function bamobile_has_image($term_id){
        return ($this->bamobile_get_attachment_id($term_id) !== false);
    }

    /**
     * Create field with add taxanomy
     * 
     * @param string $taxanomy           name of taxanomy
     */
    public function bamobile_add_taxonomy_field($taxonomy){
        echo $this->bamobile_taxonomy_field('mobiconnector-add-field-category', $taxonomy);
    }

    /**
     * Create field with edit taxanomy
     * 
     * @param string $taxanomy           name of taxanomy
     */
    public function bamobile_edit_taxonomy_field($taxonomy){
        echo $this->bamobile_taxonomy_field('mobiconnector-edit-field-category', $taxonomy);
    }

    /**
     * Print field in taxanomy
     * 
     * @param string $template           name of template
     * @param string $taxanomy           name of taxanomy 
     * 
     * @return Template
     */
    public function bamobile_taxonomy_field($template, $taxonomy){
        $params = array(
            'label'  => array(
                'image'        => __('Mobile App Images'),
                'upload_image' => __('Upload/Edit Image'),
                'remove_image' => __('Remove image'),
                'note'         => __('* This picture only work on Mobile App')
            ),
            'mobiconnector_attachment' => null
        );


        if (isset($taxonomy->term_id) && $this->bamobile_has_image($taxonomy->term_id)) {
            $image = self::bamobile_get_category_image(array(
                'term_id' => $taxonomy->term_id
            ), true);
            
            $attachment_id = $this->bamobile_get_attachment_id($taxonomy->term_id);

            $params = array_replace_recursive($params, array(
                'mobiconnector_category_avatar'  => $image,
                'mobiconnector_attachment' => $attachment_id,
            ));
        }

        return bamobile_mobiconnector_get_category_template($template, $params, false);
    }

}
$BAMobile_Categories_Avatar = new BAMobile_Categories_Avatar();
?>