<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.	
}
	
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>

<div class="wrap wooconnector-settings">	
	<div id="wooconnector-sub-menu">
        <?php require_once(MOBICONNECTOR_ABSPATH.'settings/onesignal/mobiconnector-onesignal-subtab.php'); ?>
	</div>
	<?php bamobile_display_player(); ?>
</div>
