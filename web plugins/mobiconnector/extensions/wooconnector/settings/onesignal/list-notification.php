<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.	
}
	
?>
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/subtab.php'); ?>

<div class="wrap wooconnector-settings">	
	<h1><?php echo __('Sent Notification','wooconnector')?></h1>
	<form method="POST" class="wooconnector-setting-form" action="?page=woo-notifications&wootab=list" id="settings-form">
		<?php WooconnectordisplayNotification(); ?>
	</form>
</div>