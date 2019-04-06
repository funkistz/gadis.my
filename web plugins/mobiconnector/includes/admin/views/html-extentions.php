<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $wp_version;
?>
<div class="wrap mobiconnector-settings">
    <?php if($wp_version < '4.7'){ ?>
        <h1><?php echo esc_html(__('Extensions','mobiconnector')); ?>
            <a id="mobiconnector-extensions-add-new" class="page-title-action" href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload'; ?>"><?php esc_html_e(__('Add New','mobiconnector')); ?></a>
            <a id="mobiconnector-extensions-export" class="page-title-action" href="<?php echo wp_nonce_url('admin.php?page=mobiconnector-extensions&mobile_action=export','mobiconnector-extensions-export'); ?>"><?php esc_html_e(__('Export','mobiconnector')); ?></a>
        </h1>    
    <?php }else{ ?>
    <h1 class="wp-heading-inline"><?php echo esc_html(__('Extensions','mobiconnector')); ?></h1>
    <a id="mobiconnector-extensions-add-new" class="page-title-action" href="<?php echo admin_url().'admin.php?page=mobiconnector-extensions&extension-tab=upload'; ?>"><?php esc_html_e(__('Add New','mobiconnector')); ?></a>
    <a id="mobiconnector-extensions-export" class="page-title-action" href="<?php echo  wp_nonce_url('admin.php?page=mobiconnector-extensions&mobile_action=export','mobiconnector-extensions-export'); ?>"><?php esc_html_e(__('Export','mobiconnector')); ?></a>
    <hr class="wp-header-end">
    <?php } ?>
    <div class="mobi-div"><?php bamobile_mobiconnector_print_notices(); ?></div>
    <?php bamobile_mobiconnector_display_extensions(); ?>
</div>