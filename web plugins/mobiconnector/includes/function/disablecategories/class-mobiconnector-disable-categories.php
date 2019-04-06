<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * MobiConnector Disable Categories
 * 
 * Add function disable categories in admin with checkbox
 * 
 * @class 		MobiConnector_Disable_Categories
 */
class BAMobile_Disable_Categories{
    
    /**
     * MobiConnector Disable Categories construct
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
        $taxonomies = (array)$taxonomies;
        array_push($taxonomies,'product_cat');
        foreach ($taxonomies  as $taxonomy) {
            $this->bamobile_add_custom_column_fields($taxonomy);
        }
        add_action( 'edit_term', array($this,'bamobile_mobiconnector_save_taxonomy_fields'), 10, 2 ); 
        add_action( 'create_term', array($this,'bamobile_mobiconnector_save_taxonomy_fields'), 10, 2 );
        add_filter( 'rest_category_query', array($this,'bamobile_mobiconnector_unregister_categories'),10,2 );
        add_filter( 'get_the_terms',array($this,'bamobile_mobiconnector_unset_categories_in_get_the_terms'),9999, 3);
        add_filter( 'get_terms',array($this,'bamobile_mobiconnector_unset_categories_in_get_terms'),9999, 4);
        add_filter( 'get_term',array($this,'bamobile_mobiconnector_unset_categories_in_get_term'),9999, 2);
    }

    /**
     * Add field to create and edit by taxanomy
     * 
     * @param string $taxanomy      taxanomy with create field
     */
    public function bamobile_add_custom_column_fields($taxonomy){
        add_action("{$taxonomy}_add_form_fields", array($this, 'bamobile_add_taxonomy_field'));
        add_action("{$taxonomy}_edit_form_fields", array($this, 'bamobile_edit_taxonomy_field'));
        add_filter("manage_edit-{$taxonomy}_columns", array($this, 'bamobile_manage_category_columns'));
        add_filter("manage_{$taxonomy}_custom_column", array($this, 'bamobile_manage_category_columns_fields'), 10, 3);
    }    

    /**
     * Create column
     * 
     * @param array $columms     list columns
     * 
     * @return array List of columns
     */
    public function bamobile_manage_category_columns($columns){
        $columns['mobiconnector_category_disable'] = __('Disable');
        return $columns;
    }

    /**
     * Create data in column
     * 
     * @param string $deprecated        deprecated of image
     * @param string $column_name       name of columns
     * @param int $term_id              id of term
     */
    public function bamobile_manage_category_columns_fields($deprecated, $column_name, $term_id){
        $check = get_term_meta($term_id,'mobiconnector_disable',true);
        if ($column_name == 'mobiconnector_category_disable' && $check == 1) {
            echo '<span class="mobiconnector-icon dashicons dashicons-yes"></span>';
        }
    }

    /**
     * Process Save taxanomy
     * 
     * @param int $term_id            id of term
     */
    public function bamobile_mobiconnector_save_taxonomy_fields( $term_id ) {
        if ( isset( $_POST['mobiconnector_disable_categories'] ) ) {
            update_term_meta($term_id,'mobiconnector_disable', esc_sql(sanitize_text_field($_POST['mobiconnector_disable_categories'])));
        }else{
            update_term_meta($term_id,'mobiconnector_disable', 0);
        }
    } 
    
    /**
     * Create field with add taxanomy
     * 
     * @param string $taxanomy           name of taxanomy
     */
    public function bamobile_add_taxonomy_field($taxonomy){
        echo $this->bamobile_taxonomy_field('mobiconnector-add-checkbox-categories', $taxonomy);
    }

    /**
     * Create field with edit taxanomy
     * 
     * @param string $taxanomy           name of taxanomy
     */
    public function bamobile_edit_taxonomy_field($taxonomy){
        echo $this->bamobile_taxonomy_field('mobiconnector-edit-checkbox-categories', $taxonomy);
    }

    /**
     * Print field in taxanomy
     * 
     * @param string $template           name of template
     * @param string $taxanomy           name of taxanomy 
     * 
     * @return Templates
     */
    public function bamobile_taxonomy_field($template, $taxonomy){
        // label of field
        $params = array(
            'label'  => array(
                'id'    => 'mobiconnector-disable-categories',
                'name'  => __('Disable on Mobile App'),
            )
        );
        // if taxanomy not empty and database not empty disable
        if (isset($taxonomy->term_id)) {
            $check = get_term_meta($taxonomy->term_id,'mobiconnector_disable');
            $params = array_replace_recursive($params, array(
                'mobiconnector_disable_categories'  => $check
            ));
        }
        // return template
        return bamobile_mobiconnector_get_category_template($template, $params, false);
    }

    /**
     * Unregister categories with taxanomy is category
     * 
     * @param array $prepared_args $list args of term
     * @param WP_REST_Request $request Request data to check.
     * 
     * @return array Args when the get categories
     */
    public function bamobile_mobiconnector_unregister_categories($prepared_args, $request) {
        global $wpdb;
        $sql = "SELECT * FROM ".$wpdb->prefix."terms AS t INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = 'category'";
        $terms = $wpdb->get_results($sql,ARRAY_A);
        $list = '';
        foreach($terms as $term){
            $term_id = $term['term_id'];
            $check = get_term_meta($term_id,'mobiconnector_disable',true);
            if($check == 1){    
                $list .= $term_id.',';
            }
        }
        $list = trim($list,',');
        $prepared_args['exclude'] = $list;
        return $prepared_args;
    }

    /**
     * Disable categories in get_the_term
     * 
     * @param array   $terms     List of attached terms
     * @param int     $postID    Post ID.
     * @param string  $taxonomy  Name of the taxonomy.
     * 
     * @return array  List term
     */
    public function bamobile_mobiconnector_unset_categories_in_get_the_terms($terms, $postID, $taxonomy){
        if( strpos($_SERVER['REQUEST_URI'],'wp-json') != false ){
            $list = array();
            if(!empty($terms)){
                foreach($terms as $term){
                    $term_id = 0;
                    if(is_object($term)){
                        $term_id = $term->term_id;
                        $check = get_term_meta($term_id,'mobiconnector_disable',true);
                        if($check != 1){
                            $list[] = $term;
                        }
                    }else{
                        $list[] = $term;
                    }
                }
                $terms = $list;
            }
        }
        return $terms;
    }

    /**
     * Disable categories in get_terms
     * 
     * @param array   $terms         Array of found terms.
     * @param array   $taxonomies    An array of taxonomies.
     * @param array   $args          An array of get_terms() arguments.
     * @param WP_Term_Query $term_query The WP_Term_Query object.
     * 
     * @return array  List term
     */
    public function bamobile_mobiconnector_unset_categories_in_get_terms($terms, $taxonomies, $args , $term_query){
        if( strpos($_SERVER['REQUEST_URI'],'wp-json') != false ){
            $list = array();
            if(!empty($terms)){
                foreach($terms as $term){
                    $term_id = 0;
                    if(is_object($term)){
                        $term_id = $term->term_id;
                        $check = get_term_meta($term_id,'mobiconnector_disable',true);
                        if($check != 1){
                            $list[] = $term;
                        }
                    }else{
                        $list[] = $term;
                    }
                }
                $terms = $list;
            }
        }
        return $terms;
    }

    /**
     * Disable categories in get_term
     * 
     * @param int|WP_Term $term     Term object or ID
     * @param string      $taxonomy The taxonomy slug.
     * 
     * @return object  Term
     */
    public function bamobile_mobiconnector_unset_categories_in_get_term($term, $taxonomy){
        if( strpos($_SERVER['REQUEST_URI'],'wp-json') != false ){
            if(!empty($term)){
                $term_id = 0;
                if(is_object($term)){
                    $term_id = $term->term_id;
                }else{
                    return $term;
                }
                $check = get_term_meta($term_id,'mobiconnector_disable',true);
                if($check != 1){
                    return $term;
                }
            }
        }
        return $term;
    }
}
$BAMobile_Disable_Categories = new BAMobile_Disable_Categories();
?>