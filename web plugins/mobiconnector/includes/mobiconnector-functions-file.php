<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Function for Extensions
 * 
 * @since 1.1.5
 */

/**
 * Unlink a file, which handles symlinks.
 * @param string $filename The file path to the file to delete.
 * @return boolean Whether the file has been removed or not.
 */
function bamobile_mobiconnector_unlink( $filename ) {
    // try to force symlinks
    if ( is_link ($filename) ) {
        $sym = @readlink ($filename);
        if ( $sym ) {
            return is_writable ($filename) && @unlink ($filename);
        }
    }

    // try to use real path
    if ( realpath ($filename) && realpath ($filename) !== $filename ) {
        return is_writable ($filename) && @unlink (realpath ($filename));
    }

    // default unlink
    return is_writable ($filename) && @unlink ($filename);
}

/**
 * Zip a file to .zip
 */
function bamobile_mobiconnector_zip_file(){
	// Check method zip exist
	if(class_exists('ZipArchive')){
		$folder_to_zip = basename(MOBICONNECTOR_ABSPATH);
		$fileRoot = WP_PLUGIN_DIR.'/'.$folder_to_zip;
		//$zip_file = 'mobiconnector.zip';
		$root_path = realpath( $fileRoot );
		$root_path = str_replace("\\",'/' ,$root_path);
		$zipFile = $root_path.'.zip';
		// Initialize archive object

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$zip->addEmptyDir($folder_to_zip);
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($root_path),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$filePath = '';
		$relativePath = '';		
		foreach ($files as $name => $file){			
			// Skip directories (they would be added automatically)
			if (!$file->isDir()){
				// Get real and relative path for current file
				$filePath = $file->getRealPath();	
				$filePath = str_replace("\\",'/' ,$filePath);					
				$relativePath = $folder_to_zip.'/'.substr($filePath, strlen($root_path) + 1);	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
			else{
				$filePath = $file->getRealPath();	
				$filePath = str_replace("\\",'/' ,$filePath);			
				$relativePath = $folder_to_zip.'/'.substr($filePath, strlen($root_path) + 1);	
				$zip->addEmptyDir($relativePath);
			}
		}		
		
		// Zip archive will be created only after closing object
		$zip->close();

		return $zipFile;
	}
}

/**
 * Unzips a specified ZIP file to a location on the Extentions
 */
function bamobile_mobiconnector_unzip_file($file){
	// Check method zip exist
	if(class_exists('ZipArchive')){

		// Break if file not exist
		if(!file_exists($file)){
			return false;
		}

		$namefile = '';
		$checkfile = '';
		$list_files = array();

		// Initialize archive object
		$zip = new ZipArchive;		
		$zip->open($file);
		for($i = 0; $i < $zip->numFiles; $i++){   
			$list_files[] = $zip->getNameIndex($i); // List file of zip
			$checkfile = $zip->getNameIndex(0); // File first load
		}
		// Check extension exist
		if(is_dir(WP_PLUGIN_DIR.'/'.$checkfile) || is_dir(MOBICONNECTOR_EXTENSIONS_PATH.$checkfile)){
			// Close zip
			$zip->close();
			// Remove file and send message
			bamobile_mobiconnector_unlink($file);
			return array("extension_exist" => $checkfile);
		}
		$zip->extractTo(MOBICONNECTOR_EXTENSIONS_PATH); // Unzip
		$zip->close();	// Close zip
		bamobile_mobiconnector_unlink($file); // Remove file
		// Check file after unzip
		if(!empty($list_files)){
			foreach($list_files as $file_open){
				// Skip file
				if(is_file(MOBICONNECTOR_EXTENSIONS_PATH.$file_open)){
					// Get content of file
					$item = @file_get_contents(MOBICONNECTOR_EXTENSIONS_PATH.$file_open);
					// Check leng of file
					if(strlen($item) > 0){
						$data = get_plugin_data(MOBICONNECTOR_EXTENSIONS_PATH.$file_open);
						// If file in first run of plugin
						if ( empty ( $data['Name'] ) ){
							continue;
						}else{
							$namefile = $file_open;
						}
					}else{
						continue;
					}
				}else{
					continue;
				}
			}
		}
		return $namefile;
	}else{
		return "not_exist_ziparchive";
	}
}

/**
 * Get All Extensions 
 */
function bamobile_mobiconnector_get_extensions($per_page = 100,$page = 1){
	$list_item = array();
	$folder_extensions = @scandir(MOBICONNECTOR_EXTENSIONS_PATH,1);
	if(is_array($folder_extensions)){
		$list_extensions = array_diff($folder_extensions,array('..', '.'));
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
							$list_item[$extension] = array(
								'cb'            => $extension,
								'name'          => $data['Name'],
								'slug'          => (isset($data['slug'])) ? $data['slug'] : "",
								'pluginurl'     => (isset($data['PluginURI'])) ? $data['PluginURI'] : "",
								'version'       => (isset($data['Version'])) ? $data['Version'] : "",
								'description'   => (isset($data['Description'])) ? $data['Description'] : "",
								'author'        => (isset($data['Author'])) ? $data['Author'] : "",
								'authorurl'     => (isset($data['AuthorURI'])) ? $data['AuthorURI'] : "",
								'textdomain'    => (isset($data['TextDomain'])) ? $data['TextDomain'] : "",
								'domainpath'    => (isset($data['DomainPath'])) ? $data['DomainPath'] : "",
								'network'       => (isset($data['Network'])) ? $data['Network'] : "",
								'update'        => (isset($data['update'])) ? $data['update'] : ""
							);
						}
					}
				}
			}
		}
		uasort( $list_item, 'bamobile_mobiconnector_sort_uname_callback' );
	}	
	return $list_item;
}

/**
 * Callback to sort array by a 'Name' key.

 * @access private
 */
function bamobile_mobiconnector_sort_uname_callback( $a, $b ) {
	return strnatcasecmp( $a['name'], $b['name'] );
}

/**
 * Activate extension
 */
function bamobile_mobiconnector_active_extension($extension){
	$extension = trim( $extension );
	if(bamobile_is_extension_active($extension)){
		return new WP_Error('extension_not_deactive',sprintf(__('Extensions %s.','mobiconnector'),'<b>'.__('activated','mobiconnector').'</b>'),array('status' => 400));
	}
	$valid = bamobile_mobiconnector_validate_extension($extension);
	if ( is_wp_error($valid) )
		return $valid;
	$current = get_option('mobiconnector_extensions_active');
	if(!empty($current) && is_string($current)){
		$current = unserialize($current);
		$current = (array)$current;
	}
	if(!empty($current) && in_array($extension,$current)){
		return true;
	}
	$current[] = $extension;
	sort($current);
	update_option('mobiconnector_extensions_active', serialize($current));
	bamobile_mobiconnector_extension_push_to_file($extension,'active');
	/**
	 * After active extension
	 * 
	 * @since 1.1.4
	 * 
	 * @param string $extension       Path to the main extension file from extensions directory.
	 */
	do_action( "activated_{$extension}", $extension );
}

/**
 * Deactive extension
 */
function bamobile_mobiconnector_deactive_extension($extension){
	if(!bamobile_is_extension_active($extension)){
		return new WP_Error('extension_not_active',sprintf(__('Extensions %s.','mobiconnector'),'<b>'.__('deactivated','mobiconnector').'</b>'),array('status' => 400));
	}
	$current = get_option('mobiconnector_extensions_active');
	if(!empty($current) && is_string($current)){
		$current = unserialize($current);
		$current = (array)$current;
	}
	$key = array_search( $extension, $current );	
	unset( $current[ $key ] );

	/**
	 * Fires after a extensions is deactivated.
	 * 
	 * @since 1.1.4
	 * 
	 * @param string $extension  Path to the main extension file from extensions directory.
	 */
	do_action( "deactivated_{$extension}", $extension);

	update_option('mobiconnector_extensions_active', serialize($current));	
	bamobile_mobiconnector_extension_push_to_file($extension,'deactive');
}

/**
 * Delete extension
 */
function bamobile_mobiconnector_delete_extension($extension){
	if(bamobile_is_extension_active($extension)){
		return new WP_Error('extension_not_deactive',sprintf(__('Extensions %s.','mobiconnector'),'<b>'.__('deleted','mobiconnector').'</b>'),array('status' => 400));
	}
	if(!is_writable(MOBICONNECTOR_EXTENSIONS_PATH)){
		return new WP_Error('extension_permission',sprintf(__('The Directory %1$s is %2$s writable, you should chmod 777 this directory to writable', 'mobiconnector' ),'<i>'.MOBICONNECTOR_EXTENSIONS_PATH.'/</i>','<b>NOT</b>'),array('status' => 400));
	}
	if(bamobile_mobiconnector_is_uninstallable_extension($extension)){
		bamobile_mobiconnector_uninstall_extensions($extension);
	}
	/**
	 * Fires immediately before a extension deletion attempt.
	 *
	 * @param string $extension Extension file name.
	 */
	do_action( 'delete_extension', $extension );

	$this_extension_dir = MOBICONNECTOR_EXTENSIONS_PATH.trailingslashit( dirname($extension));
	$deleted = bamobile_mobiconnector_deleteDir($this_extension_dir);
	if(is_wp_error($deleted)){
		return  $deleted;
	}
	bamobile_mobiconnector_extension_push_to_file($extension,'delete');
	return true;
}

/**
 * Delete dir
 */
function bamobile_mobiconnector_deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        return new WP_Error('extension_not_deactive',sprintf(__('%s is not Directory.','mobiconnector'),$dirPath),array('status' => 400));
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = @glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            bamobile_mobiconnector_deleteDir($file);
        } else {
            @unlink($file);
        }
    }
    @rmdir($dirPath);
}

/**
 * Whether the extension can be uninstalled.
 *
 */
function bamobile_mobiconnector_is_uninstallable_extension($extension) {
	$file = bamobile_mobiconnector_extension_basename($extension);
	$uninstallable_extensions = (array) get_option('mobiconnector_uninstall_extensions');
	if ( isset( $uninstallable_extensions[$file] ) || file_exists( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) .'/'.'uninstall.php' ) )
		return true;

	return false;
}

/**
 * run uninstall when the delete
 */
function bamobile_mobiconnector_uninstall_extensions($extension){
	$file = bamobile_mobiconnector_extension_basename($extension);
	$uninstallable_extensions = (array) get_option('mobiconnector_uninstall_extensions');
	if ( file_exists( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) .'/'. 'uninstall.php' ) ) {
		if ( isset( $uninstallable_extensions[$file] ) ) {
			unset($uninstallable_extensions[$file]);
			update_option('mobiconnector_uninstall_extensions', $uninstallable_extensions);
		}
		unset($uninstallable_extensions);

		define('WP_UNINSTALL_PLUGIN', $file);
		include( MOBICONNECTOR_EXTENSIONS_PATH . dirname($file) .'/'. 'uninstall.php' );

		return true;
	}
	if ( isset( $uninstallable_extensions[$file] ) ) {
		$callable = $uninstallable_extensions[$file];
		unset($uninstallable_extensions[$file]);
		update_option('mobiconnector_uninstall_extensions', $uninstallable_extensions);
		unset($uninstallable_extensions);
	
		include( MOBICONNECTOR_EXTENSIONS_PATH . $file );

		add_action( "uninstall_{$file}", $callable );

		/**
		 * Fires in mobiconnector_uninstall_extensions() once the extension has been uninstalled.
		 *
		 * The action concatenates the 'uninstall_' prefix with the basename of the
		 * Extension passed to mobiconnector_uninstall_extensions() to create a dynamically-named action.
		 *
		 */
		do_action( "uninstall_{$file}" );
	}
}

/**
 * Check extension active
 */
function bamobile_is_extension_active( $extension ) {
	$current = array();
	$current = get_option('mobiconnector_extensions_active');
	if(!empty($current) && is_string($current)){
		$current = unserialize($current);
		$current = (array)$current;
	}
	if(is_array($current)){
		return in_array( $extension, $current );
	}else{
		return false;
	}
}

/**
 * Mobiconnector Validate extensions
 */
function bamobile_mobiconnector_validate_extension($extension){
	if ( validate_file($extension) )
		return new WP_Error('extension_invalid', sprintf(__('The Path %1$s is %2$s','mobiconnector'),'[<i>'.$extension.'</i>]','<b>'.__('invalid','mobiconnector').'</b>'),array('status'=>400));
	if ( ! file_exists(MOBICONNECTOR_EXTENSIONS_PATH . $extension) )
		return new WP_Error('extension_not_found', sprintf(__('The %1$s file does %2$s exist','mobiconnector'),'[<i>'.$extension.'</i>]','<b>'.__('not','mobiconnector').'</b>'),array('status'=>400));
		
	$installed_extensions = bamobile_mobiconnector_get_extensions();
	if ( ! isset($installed_extensions[$extension]) )
		return new WP_Error('no_extension_header', __('The extension does not have a valid header.'));
	return 0;
}

/**
 * Get extension basename
 */
function bamobile_mobiconnector_extension_basename( $file ) {
	$file = wp_normalize_path( $file );
	
    $extension_dir = wp_normalize_path( MOBICONNECTOR_EXTENSIONS_PATH );
 
    $file = preg_replace('#^' . preg_quote($extension_dir, '#').'/'.'#','',$file); // get relative path from extensions dir
    $file = trim($file, '/');
    return $file;
}

/**
 * Push extensions status to file 
 */
function bamobile_mobiconnector_extension_push_to_file($extension,$status = 'active'){
	$file = MOBICONNECTOR_ABSPATH.'extensions/extensions.mobiconnector';
	$content_file = '';
	$content_file = @file_get_contents($file);
	if($status === 'active'){
		if(!empty($content_file)){
			if(strpos($content_file,$extension) === false){
				$content_file .= $extension.'#';
			}
		}else{
			$content_file .= $extension.'#';
		}
	}else{
		if(!empty($content_file)){
			if(strpos($content_file,$extension) !== false){
				$content_file = str_replace($extension.'#','',$content_file);
			}
		}
	}
	@file_put_contents($file,$content_file);
}

/**
 * Download file by admin
 */
function bamobile_mobiconnector_download_file($file) { // $file = include path 
	if(file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		header('Set-Cookie:fileLoading=true');
		ob_clean();
		flush();
		if(readfile($file)){
			ignore_user_abort(true);
			bamobile_mobiconnector_unlink($file);
		}
		exit;
	}
}
?>