<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.	
}
	
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>

<div class="wrap mobiconnector-settings">	
	<div id="mobiconnector-sub-menu">
        <?php require_once(MOBICONNECTOR_ABSPATH.'settings/onesignal/mobiconnector-onesignal-subtab.php'); ?>
	</div>
	<h1><?php echo esc_html(__('Sent Notification','mobiconnector')); ?></h1>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=onesignal&actions=notice" id="settings-form">
		<?php bamobile_display_notification(); ?>
	</form>
</div>