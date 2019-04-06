<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $wp_version;
?>
<div class="wrap mobiconnector-settings">
    <?php if($wp_version < '4.7'){ ?>
        <h1><?php echo esc_html(__('Add Extensions','mobiconnector')); ?>
        <a id="mobiconnector-come-back-list-extensions" class="page-title-action" href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=list'; ?>"><?php esc_html_e(__('List Extensions','mobiconnector')); ?></a>
        </h1>    
    <?php }else{ ?>
    <h1 class="wp-heading-inline"><?php echo esc_html(__('Add Extensions','mobiconnector')); ?></h1>
    <a id="mobiconnector-come-back-list-extensions" class="page-title-action" href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=list'; ?>"><?php esc_html_e(__('List Extensions','mobiconnector')); ?></a>
    <hr class="wp-header-end">
    <?php } ?>
    <div class="mobi-div"><?php bamobile_mobiconnector_print_notices(); ?></div>
    <div class="upload-plugin" style="display:block">
        <p class="install-help"><?php esc_html_e(__('If you have a extension in a .zip format, you may install it by uploading it here.')); ?></p>
        <form id="upload-extension-form" method="post" enctype="multipart/form-data" class="wp-upload-form">
            <?php wp_nonce_field( 'mobiconnector-upload-extension' ); ?>
            <input type="hidden" name="extension-task" value="upload-file"/>
            <label class="screen-reader-text" for="extensionzip"><?php esc_html_e(__('Extension zip file')); ?></label>
            <input type="file" id="extensionzip" name="extensionzip">
            <input type="submit" name="install-plugin-submit" id="install-plugin-submit" class="button" value="<?php esc_html_e(__('Install Now')); ?>" disabled="">
        </form>
    </div>
</div>