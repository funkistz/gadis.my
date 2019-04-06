<?php defined('ABSPATH') or die('Denied');
$active_tab = isset( $_REQUEST[ 'wootab' ] ) ? $_REQUEST[ 'wootab' ] : 'settings';
global $wp_version;
$debug = 1;
?>
<h2 class="nav-tab-wrapper" <?php if($wp_version < '4.8'){echo 'style="border-bottom: 1px solid #ccc"';}  ?>>
	<a id="wooconnector-general-settings" href="?page=wooconnector&amp;wootab=settings" class="nav-tab nav-tab-general<?php echo (($active_tab == 'settings') ? ' nav-tab-active' : ''); ?>"><?php echo __('Settings','wooconnector');?></a>
	<!-- <?php if($debug == 1){ ?>
		<a id="mobiconnector-settings-design" href="?page=wooconnector&amp;wootab=design" class="nav-tab nav-tab-template<?php echo (($active_tab == 'design') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Design','wooconnector')); ?></a>
	<?php } ?> -->
	<?php
		if(is_plugin_active('cellstore/cellstore.php') || is_plugin_active('olike/olike.php') || bamobile_is_extension_active('cellstore/cellstore.php') || bamobile_is_extension_active('olike/olike.php') ){
	?>
		<a id="wooconnector-settings-currency" href="?page=wooconnector&amp;wootab=currency" class="nav-tab nav-tab-template<?php echo (($active_tab == 'currency') ? ' nav-tab-active' : ''); ?>"><?php echo __('Currency','wooconnector');?></a>	
	<?php
		}
	?>	
</h2>