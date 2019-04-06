<?php defined('ABSPATH') or die('Denied');
$active_tab = isset($_REQUEST['mtab']) ? $_REQUEST['mtab'] : 'settings';
global $wp_version;
$debug = 0;
?>
<h2 class="nav-tab-wrapper" <?php if ($wp_version < '4.8') {
																												echo 'style="border-bottom: 1px solid #ccc"';
																											} ?>>
	<?php if (is_plugin_active('modernshop/modernshop.php') || is_plugin_active('cellstore/cellstore.php') || is_plugin_active('olike/olike.php') || is_plugin_active('azstore/azstore.php') || bamobile_is_extension_active('modernshop/modernshop.php') || bamobile_is_extension_active('cellstore/cellstore.php') || bamobile_is_extension_active('olike/olike.php') || bamobile_is_extension_active('azstore/azstore.php')) { ?>
	<a id="mobiconnector-general-settings" href="?page=mobiconnector-settings&amp;mtab=settings" class="nav-tab nav-tab-general<?php echo (($active_tab == 'settings') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Settings', 'mobiconnector')); ?></a>
	<?php 
} ?>
	<?php if (!is_plugin_active('wooconnector/wooconnector.php') && !bamobile_is_extension_active('wooconnector/wooconnector.php')) { ?><a id="mobiconnector-static-text" href="?page=mobiconnector-settings&amp;mtab=textapp" class="nav-tab nav-tab-template<?php echo (($active_tab == 'textapp') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Texts', 'mobiconnector')); ?></a><?php 
																																																																																																																																																																																																																																																																																																																																																																																								} ?>
	<a id="mobiconnector-settings-onsignal" href="?page=mobiconnector-settings&amp;mtab=onesignal" class="nav-tab nav-tab-template<?php echo (($active_tab == 'onesignal') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Blog Notification', 'mobiconnector')); ?></a>
	<?php if ($debug == 1) { ?>
	<a id="mobiconnector-settings-api" href="?page=mobiconnector-settings&amp;mtab=api" class="nav-tab nav-tab-template<?php echo (($active_tab == 'api') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('API', 'mobiconnector')); ?></a>
	<?php 
} ?>
	<a id="mobiconnector-settings-cache" href="?page=mobiconnector-settings&amp;mtab=mobile-cache" class="nav-tab nav-tab-template<?php echo (($active_tab == 'mobile-cache') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Cache', 'mobiconnector')); ?></a>
	<?php 
$checkerror = get_option('mobiconnector_settings-error-create-htaccess');
if (!empty($checkerror)) {
	?>
	<a id="mobiconnector-settings-htaccess" href="?page=mobiconnector-settings&amp;mtab=htaccess" class="nav-tab nav-tab-template<?php echo (($active_tab == 'htaccess') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Htaccess', 'mobiconnector')); ?></a>
	<?php

}
?>
	<?php 
$checkerrorconfig = get_option('mobiconnector_settings-error-create-config');
if (!empty($checkerrorconfig)) {
	?>
	<a id="mobiconnector-settings-config" href="?page=mobiconnector-settings&amp;mtab=config" class="nav-tab nav-tab-template<?php echo (($active_tab == 'config') ? ' nav-tab-active' : ''); ?>"><?php echo esc_html(__('Config', 'mobiconnector')); ?></a>
	<?php

}
?>
</h2>