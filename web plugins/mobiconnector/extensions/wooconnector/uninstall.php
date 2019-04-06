<?php
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}
	global $wpdb;
	wp_clear_scheduled_hook( 'wooconnector_update_rate_currency' );
	wp_trash_post( get_option( 'wooconnector_checkout_page_id' ) );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnector_data" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnector_data_api" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnector_data_notification" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wooconnector_data_player" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wooconnector\_%';" );
	wp_cache_flush();
?>