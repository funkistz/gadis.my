<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$filename = isset($_GET['extension-file']) ? $_GET['extension-file'] : '';
$path = isset($_GET['extension-path']) ? $_GET['extension-path'] : '';
$exist_path = '';
if(is_dir(MOBICONNECTOR_EXTENSIONS_PATH.$path)){
    $exist_path = MOBICONNECTOR_EXTENSIONS_PATH.$path;
}elseif(is_dir(WP_PLUGIN_DIR.'/'.$path)){
    $exist_path = WP_PLUGIN_DIR.'/'.$path;
}
?>
<div class="wrap">
<h1><?php echo esc_html(__('Installing Extension from uploaded file')); ?> : <?php echo $filename; ?></h1>
<p><?php echo esc_html(__('Unpacking the package'));?>…</p>
<p><?php echo esc_html(__('Installing the extension')); ?>…</p>
<p><?php echo esc_html(__('Destination folder already exists')); ?>. <?php echo $exist_path; ?></p>
<p><?php echo esc_html(__('Extension installation failed')); ?>.</p>
<p>
    <a href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions'; ?>"><?php echo esc_html(__('Return to Extensions List'));  ?></a>
</p>
</div>