<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( MOBICONNECTOR_ADMIN_PATH . 'includes/class-wp-list-table.php' );
}
/**
 * Table Extensions
 */
class BAMobile_Table_Extentions extends WP_List_Table{

    /**
     * Extentions Table construct
     */
    public function __construct(){
        global $status, $search;
        $args = array(
			'singular' => __( 'Extention','mobiconnector'), //singular name of the listed records
			'plural'   => __( 'Extentions','mobiconnector'), //plural name of the listed records
        );
        $status = 'all';
		if ( isset( $_REQUEST['extension-status'] ) && in_array( $_REQUEST['extension-status'], array( 'active', 'inactive', 'upgrade', 'search' ) ) )
			$status = $_REQUEST['extension-status'];

        if ( isset($_REQUEST['extension-s']) )
            $search = wp_unslash($_REQUEST['extension-s']);

		parent::__construct($args);
    }

    /**
	 * Handles data query and filter, sorting, and pagination.
	 */
    public function prepare_items(){
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);        
        $per_page     = $this->get_items_per_page( 'mobiconnector_extensions_per_page', 5 );
        $current_page = $this->get_pagenum();
        $this->process_action_extensions();
        $total_items  = self::record_count();
        $this->set_pagination_args( array(
                'total_items' => $total_items, //WE have to calculate the total number of items
                'per_page'    => $per_page //WE have to determine how many items to show on a page
            ) 
        );

        $this->items = self::get_list_item();
    }

    /**
     * Then params for method get
     */
    public function then_params_method_get(){
        global $status;
        $html = '<input type="hidden" name="page" value="mobiconnector-extensions" />';
        $html .= '<input type="hidden" name="extension-status" value="'.$status.'" />';
        echo $html;
    } 

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        $count = count(self::get_list_item());
        return $count;
    }

    /**
     * Process Action Extension
     */
    public function process_action_extensions(){
        $nonce = isset($_REQUEST['_wpnonce']) ? esc_attr( $_REQUEST['_wpnonce']) : '';
        $checkeds = isset($_REQUEST['checked']) ? $_REQUEST['checked'] : '';
        $checknonce = 'bulk-'.$this->_args['plural'];
        $extension = isset($_REQUEST['extension']) ? $_REQUEST['extension'] : '';
        if ( isset($_REQUEST['mobile_action']) && 'activate' === sanitize_text_field($_REQUEST['mobile_action']) ) {
            $nonce = isset($_REQUEST['_wpnonce']) ? esc_attr( $_REQUEST['_wpnonce']) : '';
            if ( ! wp_verify_nonce( $nonce, 'activate-extension_'.$extension ) ) {
                return false;
            }else {
                $actived = bamobile_mobiconnector_active_extension($extension);
                if(is_wp_error($actived)){
                    $key = key($actived->errors);
                    $message = $actived->errors[$key][0];
                    if($key == 'extension_not_deactive'){
                        bamobile_mobiconnector_add_notice($message);
                    }else{
                        bamobile_mobiconnector_add_notice($message,'error');
                    }
                }else{
                    bamobile_mobiconnector_add_notice(sprintf(__('Extension %s.','mobiconnector'),'<b>'.__('activated','mobiconnector').'</b>'));
                }
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }elseif( isset($_REQUEST['mobile_action']) && 'deactivate' === sanitize_text_field($_REQUEST['mobile_action']) ){
            $nonce = isset($_REQUEST['_wpnonce']) ? esc_attr( $_REQUEST['_wpnonce']) : '';
            if ( ! wp_verify_nonce( $nonce, 'deactivate-extension_'.$extension ) ) {
                return false;
            }else {
                $deactived = bamobile_mobiconnector_deactive_extension($extension);
                if(is_wp_error($deactived)){
                    $key = key($deactived->errors);
                    $message = $deactived->errors[$key][0];
                    if($key == 'extension_not_active'){
                        bamobile_mobiconnector_add_notice($message);
                    }else{
                        bamobile_mobiconnector_add_notice($message,'error');
                    }
                }else{
                    bamobile_mobiconnector_add_notice(sprintf(__('Extension %s.','mobiconnector'),'<b>'.__('deactivated','mobiconnector').'</b>'));
                }
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }elseif( isset($_REQUEST['mobile_action']) && 'delete' === sanitize_text_field($_REQUEST['mobile_action']) ){
            $nonce = isset($_REQUEST['_wpnonce']) ? esc_attr( $_REQUEST['_wpnonce']) : '';
            if ( ! wp_verify_nonce( $nonce, 'delete-extension_'. $extension ) ) {
                return false;
            }else {
                $deleted = bamobile_mobiconnector_delete_extension($extension);
                if(is_wp_error($deleted)){
                    $key = key($deleted->errors);
                    $message = $deleted->errors[$key][0];
                    if($key == 'extension_not_deactive'){
                        bamobile_mobiconnector_add_notice($message);
                    }else{
                        bamobile_mobiconnector_add_notice($message,'error');
                    }
                }else{
                    bamobile_mobiconnector_add_notice(__('Successful deletion','mobiconnector'));
                }              
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }elseif( isset($_REQUEST['action']) && 'activate-extension-selected' === sanitize_text_field($_REQUEST['action']) || 
        isset($_REQUEST['action2']) && 'activate-extension-selected' === sanitize_text_field($_REQUEST['action2'])){
            if ( ! wp_verify_nonce( $nonce, $checknonce ) ) {
                return false;
            }else{          
                $error = 0;   
                foreach($checkeds as $item){
                    $actived = bamobile_mobiconnector_active_extension($item);
                    if(is_wp_error($actived)){
                        $error++;
                        $key = key($actived->errors);                        
                        $message = $actived->errors[$key][0];
                        if($key == 'extension_not_deactive'){
                            bamobile_mobiconnector_add_notice($message);
                        }else{
                            bamobile_mobiconnector_add_notice($message,'error');
                        }
                        break;
                    }
                }
                if($error === 0){
                    bamobile_mobiconnector_add_notice(sprintf(__('Extensions %s.','mobiconnector'),'<b>'.__('activated','mobiconnector').'</b>'));
                }
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }elseif( isset($_REQUEST['action']) && 'deactivate-extension-selected' === sanitize_text_field($_REQUEST['action']) ||
        isset($_REQUEST['action2']) && 'deactivate-extension-selected' === sanitize_text_field($_REQUEST['action2']) ){
            if ( ! wp_verify_nonce( $nonce, $checknonce ) ) {
                return false;
            }else{
                $error = 0;
                foreach($checkeds as $item){
                    $deactive = bamobile_mobiconnector_deactive_extension($item);
                    if(is_wp_error($deactive)){
                        $error++;
                        $key = key($deactive->errors);
                        $message = $deactive->errors[$key][0];
                        if($key == 'extension_not_active'){
                            bamobile_mobiconnector_add_notice($message);
                        }else{
                            bamobile_mobiconnector_add_notice($message,'error');
                        }
                        break;
                    }
                }
                if($error === 0){
                    bamobile_mobiconnector_add_notice(sprintf(__('Extension %s.','mobiconnector'),'<b>'.__('deactivated','mobiconnector').'</b>'));
                }
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }elseif( isset($_REQUEST['action']) && 'delete-extension-selected' === sanitize_text_field($_REQUEST['action'])||
        isset($_REQUEST['action2']) && 'delete-extension-selected' === sanitize_text_field($_REQUEST['action2']) ){
            if ( ! wp_verify_nonce( $nonce, $checknonce ) ) {
                return false;
            }else{
                $error = 0;
                foreach($checkeds as $item){
                    $deleted = bamobile_mobiconnector_delete_extension($item);
                    if(is_wp_error($deleted)){
                        $error++;
                        $key = key($deleted->errors);
                        $message = $deleted->errors[$key][0];
                        if($key == 'extension_not_deactive'){
                            bamobile_mobiconnector_add_notice($message);
                        }else{
                            bamobile_mobiconnector_add_notice($message,'error');
                        }
                        break;
                    }
                }
                if($error === 0){
                    bamobile_mobiconnector_add_notice(sprintf(__('Extensions %s.','mobiconnector'),'<b>'.__('deleted','mobiconnector').'</b>'));
                }
                if(wp_redirect(admin_url().'admin.php?page=mobiconnector-extensions')){
                    exit;
                }
            }
        }
    }

    /**
     * Get item for table Extensions
     * 
     * @param int $per_page  Number item in 1 page
     * @param int $page      Number page
     * 
     * @return array         List item
     */
    public static function get_list_item($per_page = 20,$page = 1){
        global $status, $search;
        $list_items = bamobile_mobiconnector_get_extensions($per_page,$page);
        $list = array();
        foreach($list_items as $key => $item){
            if($status == 'active'){
                if(is_extension_active($key)){
                    $list[$key] = $item;
                }else{
                    continue;
                }
            }elseif($status == 'inactive'){
                if(!is_extension_active($key)){
                    $list[$key] = $item;
                }else{
                    continue;
                }
            }elseif($status == 'upgrade'){
                if(isset($item['update']) && $item['update'] == 1){
                    $list[$key] = $item;
                }else{
                    continue;
                }
            }else{
                $list[$key] = $item;
            }
        }  
        if(!empty($search)){
            $list = array_filter( $list_items, array( __CLASS__, '_search_callback' ) );
        }
        return $list;
    }   

    /**
     * Get total Extensions
     */
    private function get_totals_extensions(){
        $items = bamobile_mobiconnector_get_extensions(200,1);
        $all = count($items);
        $active = 0;
        $deactive = 0;
        $upgrade = 0;
        foreach($items as $item => $data){
            if(bamobile_is_extension_active($item)){
                $active++;
            }else{
                $deactive++;
            }
            if(isset($data['update']) && $data['update'] == 1){
                $upgrade++;
            }
        }
        $totals['all'] = $all;
        $totals['active'] = $active;
        $totals['inactive'] = $deactive;
        $totals['upgrade'] = $upgrade;
        return $totals;
    }

    /**
	 * @return array
	 */
	protected function get_table_classes() {
        $args = array( 'widefat', 'plugins',$this->_args['plural'] );
		return $args;
	}

    /**
	 *
	 * @global array $totals
	 * @global string $status
	 * @return array
	 */
	protected function get_views() {
        global $status;
        $totals = $this->get_totals_extensions();
		$status_links = array();
		foreach ( $totals as $type => $count ) {
			if ( !$count )
				continue;

			switch ( $type ) {
				case 'all':
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'plugins' );
					break;
				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count );
					break;
				case 'inactive':
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count );
					break;
				case 'upgrade':
					$text = _n( 'Update Available <span class="count">(%s)</span>', 'Update Available <span class="count">(%s)</span>', $count );
					break;
			}

			if ( 'search' !== $type ) {
				$status_links[$type] = sprintf( "<a href='%s'%s>%s</a>",
					add_query_arg('extension-status', $type, 'admin.php?page=mobiconnector-extensions'),
					( $type === $status && !isset($_REQUEST['extension-s'])) ? ' class="current" aria-current="page"' : '',
					sprintf( $text, number_format_i18n( $count ) )
					);
			}
		}

		return $status_links;
    }    

    /**
	 * Displays the search box.
	 *
	 * @since 1.1.4
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['extension-s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input style="margin: 0; width: 280px;font-size: 16px;font-weight: 300;line-height: 1.5;padding: 3px 5px; height: 32px;" type="search" id="<?php echo esc_attr( $input_id ); ?>" class="wp-filter-search" name="extension-s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search installed extensions...' ); ?>"/>
			<?php submit_button( $text, 'hide-if-js', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
    }
    
    /**
	 * @global string $search URL encoded search term.
	 *
	 * @param array $extension
	 * @return bool
	 */
	public static function _search_callback( $extension ) {
		global $search;
		foreach ( $extension as $value ) {
			if ( is_string( $value ) && false !== stripos( strip_tags( $value ), urldecode( $search ) ) ) {
				return true;
			}
		}

		return false;
	}

    /**
     * Get columns for table
     */
    public function get_columns(){        
        $columns = array(
			'cb'            => '<input type="checkbox" />',
			'name'          => __( 'Extension', 'mobiconnector' ),
			'description'   => __( 'Description', 'mobiconnector' )
	    );
	    return $columns;
    }

    /**
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
    }
    
    /**
	 *
	 * @global string $status
	 * @return array
	 */
	protected function get_bulk_actions() {
		$actions = array();		
		$actions['activate-extension-selected'] = __( 'Activate' );
		$actions['deactivate-extension-selected'] = __( 'Deactivate' );	
	    $actions['delete-extension-selected'] = __( 'Delete' );
		return $actions;
    }
    
    /**
	 * @global string $status
	 * @param string $which
	 */
	public function bulk_actions( $which = '' ) {
		global $status;

		if ( in_array( $status, array( 'mustuse', 'dropins' ) ) )
			return;

		parent::bulk_actions( $which );
    }

    /**
	 *
	 * @global string $status
	 */
	public function display_rows() {
		foreach ( $this->items as $extension_file => $extension_data )
			$this->single_row( array( $extension_file, $extension_data ) );
    }
    
    public function single_row( $item ) {
        global $status;
        list( $extension_file, $extension_data ) = $item;
        $actions = array(
			'deactivate' => '',
			'activate' => '',
			'details' => '',
			'delete' => '',
        );
        $is_active = bamobile_is_extension_active($extension_file);
        if ( $is_active ) {
            $actions['deactivate'] = '<a href="' . wp_nonce_url( 'admin.php?page=mobiconnector-extensions&mobile_action=deactivate&amp;extension=' . urlencode( $extension_file ), 'deactivate-extension_' . $extension_file ) . '" aria-label="' . esc_attr( sprintf( _x( 'Deactivate %s', 'plugin' ), $extension_data['name'] ) ) . '">' . __( 'Deactivate' ) . '</a>';      
        } else {
            $actions['activate'] = '<a href="' . wp_nonce_url( 'admin.php?page=mobiconnector-extensions&mobile_action=activate&amp;extension=' . urlencode( $extension_file ), 'activate-extension_' . $extension_file ) . '" class="edit" aria-label="' . esc_attr( sprintf( _x( 'Activate %s', 'plugin' ), $extension_data['name'] ) ) . '">' . __( 'Activate' ) . '</a>';
            $actions['delete'] = '<a href="' . wp_nonce_url( 'admin.php?page=mobiconnector-extensions&mobile_action=delete&amp;extension=' . urlencode( $extension_file ), 'delete-extension_'. $extension_file ). '" aria-label="' . esc_attr( sprintf( _x( 'Delete %s', 'plugin' ), $extension_data['name'] ) ) . '">' . __( 'Delete' ) . '</a>';
        } 
        $actions = array_filter( $actions );
        /**
         *  Filters the list of action links displayed for a specific extension in the Extensions list table.
         * 
         * @since 1.1.4
         * 
         * @param array  $actions     An array of extension action links. By default this can include 'activate',
		 *                            'deactivate', and 'delete'.
         * @param string $extension_file Path to the extension file relative to the extensions directory.
         * @param array  $extension_data An array of extension data.
         */
        $actions = apply_filters("mobiconnector_extensions_action_links_mobiconnector/extensions/{$extension_file}",$actions,$extension_file,$extension_data,$status);
        $class = $is_active ? 'active' : 'inactive';
        //Checkbox Id
        $checkbox_id =  "checkbox_" . md5($extension_data['name']);
        //Checkbox
        $checkbox = "<label class='screen-reader-text' for='" . $checkbox_id . "' >" . sprintf( __( 'Select %s' ), $extension_data['name'] ) . "</label>"
        . "<input type='checkbox' name='checked[]' value='" . esc_attr( $extension_file ) . "' id='" . $checkbox_id . "' />";
        //Description
        $description = '<p>' . ( $extension_data['description'] ? $extension_data['description'] : '&nbsp;' ) . '</p>';
        //Name
        $extension_name = $extension_data['name'];
        if(! empty( $extension_data['update'] ) )
            $class .= ' update';
        
        $extension_slug = (isset( $extension_data['slug'] ) && $extension_data['slug'] !== '' ) ? $extension_data['slug'] : sanitize_title( $extension_name );
        printf( '<tr class="%s" data-slug="%s" data-plugin="%s">',
            esc_attr( $class ),
            esc_attr( $extension_slug ),
            esc_attr( $extension_file )
        );
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
        foreach ( $columns as $column_name => $column_display_name ) {
			$extra_classes = '';
			if ( in_array( $column_name, $hidden ) ) {
				$extra_classes = ' hidden';
			}
			switch ( $column_name ) {
				case 'cb':
					echo "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'name':
					echo "<td class='plugin-title column-primary'><strong>$extension_name</strong>";
					echo $this->row_actions( $actions, true );
					echo "</td>";
					break;
				case 'description':
					$classes = 'column-description desc';

					echo "<td class='$classes{$extra_classes}'>
						<div class='plugin-description'>$description</div>
						<div class='$class second plugin-version-author-uri'>";

					$extension_meta = array();
					if ( !empty( $extension_data['version'] ) )
						$extension_meta[] = sprintf( __( 'Version %s' ), $extension_data['version'] );
					if ( !empty( $extension_data['author'] ) ) {
						$author = $extension_data['author'];
						if ( !empty( $extension_data['authorurl'] ) )
							$author = '<a href="' . $extension_data['authorurl'] . '">' . $extension_data['author'] . '</a>';
						$extension_meta[] = sprintf( __( 'By %s' ), $author );
					}

					// Details link using API info, if available
					if ( isset( $extension_data['slug'] ) && current_user_can( 'install_plugins' ) ) {
						$extension_meta[] = sprintf( '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
							esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $extension_data['slug'] .
								'&TB_iframe=true&width=600&height=550' ) ),
							esc_attr( sprintf( __( 'More information about %s' ), $extension_name ) ),
							esc_attr( $extension_name ),
							__( 'View details' )
						);
					} elseif ( ! empty( $extension_data['authorurl'] ) ) {
						$extension_meta[] = sprintf( '<a href="%s">%s</a>',
							esc_url( $extension_data['pluginurl'] ),
							__( 'Visit plugin site' )
						);
					}

					/**
					 * Filters the array of row meta for each plugin in the Extensions list table.
					 *
					 * @since 1.1.4
					 *
					 * @param array  $extension_meta An array of the plugin's metadata,
					 *                            including the version, author,
					 *                            author URI, and plugin URI.
					 * @param string $extension_file Path to the plugin file, relative to the plugins directory.
					 * @param array  $extension_data An array of plugin data.
					 *
					 */
					$extension_meta = apply_filters( 'extensions_row_meta', $extension_meta, $extension_file, $extension_data );
					echo implode( ' | ', $extension_meta );

					echo "</div></td>";
					break;
				default:
					$classes = "$column_name column-$column_name $class";

					echo "<td class='$classes{$extra_classes}'>";

					/**
					 * Fires inside each custom column of the Extensions list table.
					 *
					 * @since 1.1.4
					 *
					 * @param string $column_name Name of the column.
					 * @param string $extension_file Path to the extension file.
					 * @param array  $extension_data An array of extension data.
					 */
					do_action( 'manage_extensions_custom_column', $column_name, $extension_file, $extension_data );

					echo "</td>";
			}
		}

		echo "</tr>";

    }

    /**
     * No item in table
	 */
	public function no_items() {
        if ( ! empty( $_REQUEST['extension-s'] ) ) {
			$s = esc_html( wp_unslash( $_REQUEST['extension-s'] ) );
			printf( __( 'No extensions found for &#8220;%s&#8221;.','mobiconnector' ), $s );
		} 
		else
			_e( 'No extensions found','mobiconnector' );
	}
}
?>