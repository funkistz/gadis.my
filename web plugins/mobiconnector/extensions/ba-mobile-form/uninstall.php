<?php
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}
	global $wpdb;
	delete_option('baform_checkdata');
	delete_option('ba_design_form');
	$table_name = $wpdb->prefix . 'usermeta';
	// $sql = "SELECT DISTINCT p.ID
	// FROM `'.$wpdb->prefix.'users` p
	// LEFT JOIN `'.$wpdb->prefix.'usermeta` m2 ON m2.post_id = p.ID 
	// AND m2.meta_key = 'field_extra_user'
	// WHERE m2.meta_key ='field_extra_user'";
	// $result_meta = $wpdb->get_results($sql);
	$wpdb->delete( $table_name, array( 'meta_key' => 'field_extra_user' ) );
	

