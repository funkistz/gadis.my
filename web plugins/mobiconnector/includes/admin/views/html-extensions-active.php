<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$filename = isset($_GET['extension-file']) ? $_GET['extension-file'] : '';
$extension_file = isset($_GET['extension-path']) ? $_GET['extension-path'] : '';
?>
<div class="wrap">
<h1><?php echo esc_html(__('Installing Extension from uploaded file')); ?> : <?php echo $filename; ?></h1>
<p><?php echo esc_html(__('Unpacking the package'));?>…</p>
<p><?php echo esc_html(__('Installing the extension')); ?>…</p>
<p><?php echo esc_html(__('Extension installed successfully')); ?>.</p>
<p>
    <a class="button button-primary" href="<?php echo  wp_nonce_url( 'admin.php?page=mobiconnector-extensions&mobile_action=activate&amp;extension=' . urlencode( $extension_file ), 'activate-extension_' . $extension_file ); ?>" target="_parent"><?php echo esc_html(__('Activate Extension')); ?></a> 
    <a href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions'; ?>"><?php echo esc_html(__('Return to Extensions List'));  ?></a>
</p>
</div>