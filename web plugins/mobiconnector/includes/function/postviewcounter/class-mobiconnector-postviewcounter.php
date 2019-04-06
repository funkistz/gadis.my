<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Mobiconnector Post view counter
 * 
 * Count views
 * 
 * Create database and add query to select
 * 
 * @class 		MobiConnector_PostViewCounter
 */
class BAMobile_PostViewCounter{

    /**
     * MobiConnector_PostViewCounter construct
     */
    public function __construct(){
        $this->init_hooks();
    }

    /**
	 * Hook into actions and filters.
	 */
    private function init_hooks(){
        add_action( 'admin_init',  array($this,'bamobile_mobiconnector_register_new_column' ));
        add_action( 'post_submitbox_misc_actions', array( $this, 'bamobile_submitbox_views' ) );
        add_action( 'pre_get_posts',array($this,'bamobile_mobiconnector_extend_pre_query'), 1);
        add_filter( 'query_vars', array($this,'bamobile_mobiconnector_query_var'));
        add_filter( 'posts_join', array($this,'bamobile_mobiconnector_joins_posts'), 10, 2 );
        add_filter( 'posts_orderby', array($this,'bamobile_mobiconnector_orderby_posts'), 10, 2);
        add_filter( 'posts_fields', array($this,'bamobile_mobiconnector_fields_posts'), 10, 2 );
        add_filter( 'the_posts', array($this,'bamobile_mobiconnector_the_posts'), 10, 2 );
        add_action( 'deleted_post',array($this,'bamobile_mobiconnector_delete_post_views'));
        add_action( 'loop_start',array($this,'bamobile_mobiconnector_views_website'));
        add_action( 'save_post', array( $this, 'bamobile_save_post_add_views' ), 1, 2 );
    }

    /**
	 * Output post views for single post.
	 * 
	 * @global object $post
	 * @return mixed 
	 */
    public function bamobile_submitbox_views(){
        global $post;
        $checkinpost = 0;
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
		$listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                    if($post->post_type == $name || $post->post_type == 'product'){
                        $checkinpost++;
                    }
                }
            }
        }

        // Check if in posttype
        if($checkinpost === 0){
            return;
        }

        // break if current user can't edit this post
        if ( ! current_user_can( 'edit_post', $post->ID ) ){
            return;
        }    

        $count = $this->bamobile_mobiconnector_get_views($post->ID);

        ?>        
            <div class="misc-pub-section" id="mobiconnector-post-views">    
                <?php wp_nonce_field( 'mobiconnector_view_count', 'mobiconnector_views_nonce' ); ?>    
                <span id="mobiconnector-post-views-display">
                    <input type="hidden" name="mobiconnector_post_views" id="mobiconnector-views-current" value="<?php echo (int) $count; ?>" />
                    <?php echo __( 'Mobile Views', 'mobiconnector' ) . ': <b>' . number_format_i18n( (int) $count ) . '</b>'; ?>
                </span>
            </div>
        <?php
    }

    /**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
    public function bamobile_save_post_add_views($post_id, $post){

        // $post_id and $post are required
		if ( empty( $post_id ) || empty( $post )) {
			return;
        }

        // break if doing autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
            return $post_id;
        }       

        // break if current user can't edit this post
        if ( ! current_user_can( 'edit_post', $post_id ) ){
            return;
        }            

        // is post views set			
        if ( ! isset( $_POST['mobiconnector_post_views'] ) ){
            return;
        }  

        // Check the nonce
        if ( ! isset( $_POST['mobiconnector_views_nonce'] ) || ! wp_verify_nonce( $_POST['mobiconnector_views_nonce'], 'mobiconnector_view_count' ) ){
            return;
        }

        global $wpdb;	
        $type = get_post_type($post_id);
        $checkinpost = 0;
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
		$listoptionscheckbox = unserialize($listoptionscheckbox);
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                    if($type == $name || $type == 'product'){
                        $checkinpost++;
                    }
                }
            }
        }
		//Create postviews if in database empty
		if($checkinpost > 0){
			$check = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "mobiconnector_views WHERE mc_id = $post_id");
			// If empty in database
			if(empty($check)){
				$count =  0;
				$wpdb->insert( 
					$wpdb->prefix . "mobiconnector_views", 
					array( 
						'mc_id' => $post_id, 
						'mc_mobile' => $count,
						'mc_website' => $count,
						'mc_count' => $count
					), 
					array( 
						'%d',
						'%d',
						'%d', 
						'%d' 
					) 
				);
			}
		}
    }
    
    /*
    * Extend query with mobi_views orderby parameter.
    */
    public function bamobile_mobiconnector_extend_pre_query( $query ) {
        if ( isset( $query->query_vars['orderby'] ) && $query->query_vars['orderby'] === 'mobi_views' )
            $query->mobi_orderby = true;
    }

    /*
    * Register query vars
    */
    public function bamobile_mobiconnector_query_var($query_vars){
        $query_vars[] = 'mobiconnector_views_query';
        return $query_vars;
    }

    /*
    * Modify the db query to use mobi_views parameter.
    */
    public function bamobile_mobiconnector_joins_posts($join, $query){
        if ( ( ! isset( $query->query['fields'] ) || $query->query['fields'] === '' ) && ( ( isset( $query->mobi_orderby ) && $query->mobi_orderby ) ) ){
            global $wpdb;
            $join .= " LEFT JOIN " . $wpdb->prefix . "mobiconnector_views AS mobiv ON mobiv.mc_id = " . $wpdb->prefix . "posts.ID ";
        }
        return $join;
    }

    /*
    * Order posts by post views.
    */
    public function bamobile_mobiconnector_orderby_posts( $orderby, $query ) {
        // is it sorted by post views?
        if ( ( isset( $query->mobi_orderby ) && $query->mobi_orderby ) ) {
            global $wpdb;
            $order = $query->get( 'order' );
            $orderby = ( ! isset( $query->query['mobiconnector_views_query']['hide_empty'] ) || $query->query['mobiconnector_views_query']['hide_empty'] === true ? 'mobi_views' : 'mobiv.mc_count' ) . ' ' . $order . ', ' . $wpdb->prefix . 'posts.ID ' . $order;
        }
        return $orderby;
    }

    /*
    * Add field to posts object
    */
    public function bamobile_mobiconnector_fields_posts( $fields, $query ) {
        if ( ( ! isset( $query->query['fields'] ) || $query->query['fields'] === '' ) && ( ( isset( $query->mobi_orderby ) && $query->mobi_orderby ) ) ){
            $fields = $fields . ', IFNULL( mobiv.mc_count, 0 ) AS mobi_views';
        }
        return $fields;
    }

    /*
    * Extend query object with total post views.
    */
    public function bamobile_mobiconnector_the_posts( $posts, $query ) {
        if ( ( isset( $query->mobi_orderby ) && $query->mobi_orderby ) ) {
            $sum = 0;
            // any posts found?
            if ( ! empty( $posts ) ) {
                foreach ( $posts as $post ) {
                    if ( ! empty( $post->mobi_views ) )
                        $sum += (int) $post->mobi_views;
                }
            }
            // pass total views
            $query->total_views = $sum;
        }
        return $posts;
    }

    /*
    * Get post views for a post or array of posts.
    */
    public function bamobile_mobiconnector_get_views( $post_id = 0 ) {
        if ( empty( $post_id ) )
            $post_id = get_the_ID();

        if ( is_array( $post_id ) )
            $post_id = implode( ',', array_map( 'intval', $post_id ) );
        else
            $post_id = (int) $post_id;

        global $wpdb;

        $query = "SELECT SUM(mc_count) AS views
        FROM " . $wpdb->prefix . "mobiconnector_views WHERE mc_id = $post_id
        ";

        $post_views = (int) $wpdb->get_var( $query );
        
        return $post_views;
    }

    /*
    * Remove post views from database when post is deleted.
    */
    public function bamobile_mobiconnector_delete_post_views( $post_id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'mobiconnector_views', array( 'mc_id' => $post_id ), array( '%d' ) );
    }

    /*
    * Register post views column for specific post types
    */
    public function bamobile_mobiconnector_register_new_column() {
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        $listtypes = array();
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                   $listtypes[] = $name;
                }
            }
        }

        //if not type product
        if(!in_array('product',$listtypes)){
            array_push($listtypes,'product');
        }

        foreach($listtypes as $type){
            add_action( 'manage_'.$type.'s_custom_column', array($this,'bamobile_mobiconnector_add_new_column_content') , 10, 2 );
            // filters
            add_filter( 'manage_edit-'.$type.'_columns',  array($this,'bamobile_mobiconnector_add_new_column' ));
            add_filter( 'manage_edit-'.$type.'_sortable_columns',array($this,'bamobile_mobiconnector_register_sortable_custom_column' ));
        }
    }

    /*
    * Add post views column content.
    */
    public function bamobile_mobiconnector_add_new_column_content( $column_name, $id ) {
        if ( $column_name === 'mobi_views' ) {
            // get total post views
            $count = $this->bamobile_mobiconnector_get_views( $id );

            echo $count;
        }
    }

    /*
    * Register sortable post views column.
    */
    public function bamobile_mobiconnector_register_sortable_custom_column( $columns ) {
        // add new sortable column
        $columns['mobi_views'] = 'mobi_views';
        return $columns;
    }

    /*
    * Add post views column.
    */
    public function bamobile_mobiconnector_add_new_column( $columns ) {
        $offset = 0;

        if ( isset( $columns['date'] ) )
            $offset ++;

        if ( isset( $columns['comments'] ) )
            $offset ++;

        if ( $offset > 0 ) {
            $date = array_slice( $columns, -$offset, $offset, true );

            foreach ( $date as $column => $name ) {
                unset( $columns[$column] );
            }

            $columns['mobi_views'] = '<span class="mobiconnector-views-preview" title="' . __( 'Mobile Views') . '"></span>';

            foreach ( $date as $column => $name ) {
                $columns[$column] = $name;
            }
        } else
            $columns['mobi_views'] = '<span class="mobiconnector-views-preview" title="' . __( 'Mobile Views') . '"></span>';

        return $columns;
    }

    /**
     * Count View with click to Post Details
     */
    public function bamobile_mobiconnector_views_website(){
        $listoptionscheckbox = get_option('mobiconnector_settings-post_type');
        $listoptionscheckbox = unserialize($listoptionscheckbox);
        $listtypes = array();
        if(!empty($listoptionscheckbox)){
            $post_type = '';
            foreach($listoptionscheckbox as $posttype => $value){  
                $name = str_replace('mobi-','',$posttype); 
                if($value == 1){
                   $listtypes[] = $name;
                }
            }
        }

        //if not type product
        if(!in_array('product',$listtypes)){
            array_push($listtypes,'product');
        }

        foreach($listtypes as $post){
            if(is_singular($post)){
                $currentip = bamobile_mobiconnector_get_the_user_ip();
                $postid = get_the_ID();
                $device = 'website';
                if(!isset($_SESSION[$currentip][$postid])){
                    $count = bamobile_mobiconnector_insert_or_update_views($postid,$device);
                    $_SESSION[$currentip][$postid] = time();
                }elseif((time() - $_SESSION[$currentip][$postid]) > 1800){
                    $count = bamobile_mobiconnector_insert_or_update_views($postid,$device);
                    $_SESSION[$currentip][$postid] = time();
                }else{
                    return true;
                }
            }
        }        
    }   
}
$BAMobile_PostViewCounter = new BAMobile_PostViewCounter();
?>