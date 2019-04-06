<?php
	if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		exit;
	}
	define( 'MOBICONNECTOR_EXTENSIONS_PATH', dirname( __FILE__ ) . '/extensions/');

	/**
	 * Get extension basename
	 */
	function bamobile_mobiconnector_extension_basename( $file ) {
		$file = wp_normalize_path( $file );		
		$extension_dir = wp_normalize_path( MOBICONNECTOR_EXTENSIONS_PATH );	
		$file = preg_replace('#^' . preg_quote($extension_dir, '#').'/#','',$file); // get relative path from extensions dir
		$file = trim($file, '/');
		return $file;
	}

	/**
	 * Get list extension uninstall
	 */
	function bamobile_mobiconnector_get_uninstall_extension($per_page = 100,$page = 1){
		$list_extensions = array_diff(@scandir(MOBICONNECTOR_EXTENSIONS_PATH,1),array('..', '.'));
		@sort($list_extensions);
		$numrows = ($page - 1)  * $per_page;
		$count = count($list_extensions);
		if($per_page < $count){
			$count = $per_page;
		}
		$list_item = array();
		for($i = $numrows; $i < $count; $i++){
			if(is_dir(MOBICONNECTOR_EXTENSIONS_PATH.$list_extensions[$i])){
				$contentfolders = array_diff(@scandir(MOBICONNECTOR_EXTENSIONS_PATH.$list_extensions[$i],1),array('..', '.'));
				foreach($contentfolders as $content){
					$file = MOBICONNECTOR_EXTENSIONS_PATH.$list_extensions[$i].'/'.$content;
					if(is_file($file)){
						$item = @file_get_contents($file);
						if(strlen($item) > 0){
							$data = get_plugin_data($file);
							if ( empty ( $data['Name'] ) ){
								continue;
							}
							$extension = $list_extensions[$i].'/'.$content;
							$list_item[$extension] = $extension;
						}
					}
				}
			}
		}
		return $list_item;
	}

	/**
	 * run uninstall when the delete
	 */
	function bamobile_mobiconnector_uninstall_extensions_un($extension){
		$file = bamobile_mobiconnector_extension_basename($extension);
		$uninstallable_extensions = (array) get_option('mobiconnector_uninstall_extensions');
		if ( file_exists( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) . '/uninstall.php' ) ) {
			if ( isset( $uninstallable_extensions[$file] ) ) {
				unset($uninstallable_extensions[$file]);
				update_option('mobiconnector_uninstall_extensions', $uninstallable_extensions);
			}
			unset($uninstallable_extensions);
	
			define('WP_UNINSTALL_PLUGIN', $file);
			include_once( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) . '/uninstall.php' );
	
			return true;
		}
		if ( isset( $uninstallable_extensions[$file] ) ) {
			$callable = $uninstallable_extensions[$file];
			unset($uninstallable_extensions[$file]);
			update_option('mobiconnector_uninstall_extensions', $uninstallable_extensions);
			unset($uninstallable_extensions);
		
			include_once( MOBICONNECTOR_EXTENSIONS_PATH . $file );
		}
	}
	
	/**
	 * Whether the extension can be uninstalled.
	 *
	 */
	function bamobile_mobiconnector_is_uninstallable_extension_un($extension) {
		$file = bamobile_mobiconnector_extension_basename($extension);
		$uninstallable_extensions = (array) get_option('mobiconnector_uninstall_extensions');
		if ( isset( $uninstallable_extensions[$file] ) || file_exists( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) . '/uninstall.php' ) )
			return true;

		return false;
	}

	/**
     * Check Config exist in htaccess
     * 
     * @param string $filename   path to file htaccess
     * @param string $marker     name to determine confix exist
     * 
     * @return boolean
     */
	function bamobile_check_if_isset_htaccess( $filename, $marker ) {
		$result = array ();	 
		if ( ! file_exists( $filename ) ) {
			return $result;
		}
	 
		$markerdata = explode( "\n", implode( '', file( $filename ) ) );
		$checkint = 0;
		$state = false;
		foreach ( $markerdata as $markerline ) {
			if ( false !== strpos( $markerline, '# BEGIN ' . $marker )) {
				$checkint++;
			}
			if( false !== strpos( $markerline, '# END ' . $marker )){
				$checkint++;
			}
		}
		if($checkint > 1){
			return true;
		}
		return false;
	}

	/**
	 * Delete Htaccess
	 */
	function bamobile_mobiconnector_delete_htaccess(){
		$home_path = ABSPATH;	
		$home_path = trailingslashit( $home_path );
		if($old_data = @file_get_contents($home_path.'.htaccess')){
			$checkpermissionthenhtaccess = is_writable($home_path.'.htaccess');
            if($checkpermissionthenhtaccess){
				if(bamobile_check_if_isset_htaccess( $home_path.'.htaccess', 'MobiConnector' ) ){
					$headhtaceess = substr($old_data,0,strpos($old_data,'# BEGIN MobiConnector'));
                    $bothtaccess = substr($old_data,strpos($old_data,'# END MobiConnector'));
                    $bothtaccess = str_replace('# END MobiConnector','',$bothtaccess);
                    $bothtaccess = trim($bothtaccess);
                    $htaccessnotmobi = $headhtaceess.$bothtaccess;
                    $contenthtaccess = $content.$htaccessnotmobi;
                    @file_put_contents($home_path.'.htaccess','');
                    @file_put_contents($home_path.'.htaccess',$contenthtaccess);    
				}
			}
		}
	}

	/**
	 * Delete File Api
	 */
	function bamobile_mobiconnector_delete_file_api(){
		$home_path = ABSPATH;	
		$home_path = trailingslashit( $home_path );
		if(file_exists($home_path.'api.php')){
			@unlink($home_path.'api.php');
		}
	}

	$listexs = bamobile_mobiconnector_get_uninstall_extension();
	foreach($listexs as $key => $value){
		if(bamobile_mobiconnector_is_uninstallable_extension_un($key)){
			bamobile_mobiconnector_uninstall_extensions_un($key);
		}
	}

	wp_clear_scheduled_hook( 'mobiconnector_update_player_id' );
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_views" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_data_api" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_data_notification" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_data_player" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_manage_device" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_social_users" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mobiconnector_sessions" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%mobiconnector\_%';" );
	wp_cache_flush();
	bamobile_mobiconnector_delete_htaccess();
	bamobile_mobiconnector_delete_file_api();
?>