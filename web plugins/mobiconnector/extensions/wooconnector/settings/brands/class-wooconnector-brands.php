<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Create Brands of WooConnector
 */
class WooConnectorBrands{

    /**
     * WooConnnectorBrands construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){

        //Create menu in admin
        add_action('admin_menu',array($this,'createMenuInAdmin'));

        //Create taxonomy
        add_action( 'init', array($this,'addCustomTaxonomy' ));

        //Process ajax in brands page
        if(is_admin()){
            add_action("wp_ajax_save-brands", array($this,"saveBrands"));
        }

        add_filter( 'term_updated_messages', array($this,'wooconnector_change_message_brand') );
    }

    public function wooconnector_change_message_brand($messages){
        $messages['wooconnector_product_brand'] = array(
            0 => '',
            1 => __( 'Brand added.', 'wooconnector' ),
            2 => __( 'Brand deleted.', 'wooconnector' ),
            3 => __( 'Brand updated.', 'wooconnector' ),
            4 => __( 'Brand not added.', 'wooconnector' ),
            5 => __( 'Brand not updated.', 'wooconnector' ),
            6 => __( 'Brand deleted.', 'wooconnector' ),
            );
                                        
        return $messages;
    }

    public function addCustomTaxonomy(){
        // create a new taxonomy
        $labels = array(
            'name'  => __( 'Brands','wooconnector' ),
            'singular_name' => __( 'Brand','wooconnector' ),
            'menu_name' => __( 'Brands','wooconnector' ),
            'all_items' => __( 'All Brands','wooconnector' ),
            'edit_item' => __( 'Edit Brand','wooconnector' ),
            'view_item' => __( 'View Brand','wooconnector' ),
            'update_item' => __( 'Update Brand','wooconnector' ),
            'add_new_item' => __( 'Add New Brand','wooconnector' ),
            'new_item_name' => __( 'New Brand Name','wooconnector' ),
            'search_items' => __( 'Search Brands','wooconnector' ),
            'parent_item'       => __( 'Parent Brand', 'wooconnector' ),
            'parent_item_colon' => __( 'Parent Brand:', 'wooconnector' ),
            'not_found'  => __( 'No brands found.', 'wooconnector' ),
            'choose_from_most_used'      => __( 'Choose from the most used brands', 'wooconnector' ),
            'separate_items_with_commas' => __( 'Separate brands with commas', 'wooconnector' ),
        );
        register_taxonomy(
            'wooconnector_product_brand',
            'product',
            array(
                'labels' => $labels,
                'sort' => true,
                'show_in_rest' => true,
                'show_in_menu' => 'wooconnector',
                'rest_base' => 'woobrands',
                'hierarchical' => true,
                'show_ui'               => true,
                'args' => array( 'orderby' => 'term_order' ),
                'rewrite' => array( 'slug' => 'wooconnector_product_brand' )
            )
        );       
    }

    /**
     * Craete menu in admin
     */
    public function createMenuInAdmin(){
        $parent_slug = 'wooconnector';
        add_submenu_page(
            $parent_slug,
            __('Brands'),
            __('Brands'),
            'manage_options',
            'brand',
            array($this,'actionBrands')
        );
    }

    /**
     * action of page menu
     */
    public function actionBrands(){
		wp_redirect(admin_url().'edit-tags.php?taxonomy=wooconnector_product_brand&post_type=product');
    }
}
$WooConnectorBrands = new WooConnectorBrands();
?>