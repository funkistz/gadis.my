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
	<h1><?php echo esc_html(__('Onesignal API','mobiconnector')); ?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=onesignal&actions=settings" id="settings-form">
        <input type="hidden" name="mtask" value="saveonesignal"/>	
        <input type="hidden" name="mstask" value="api"/>		
		<div id="mobiconnector-settings-body">
			<div id="mobiconnector-body" >
				<div id="mobiconnector-body-content">	
					<table id="table-mobiconnector">							
						<tr>							
							<td class="woo-label"><label  for="app-id-onesignal"><?php echo esc_html(__('Onesignal APP ID','mobiconnector')); ?> </label></td>
							<td class="woo-content">
								<input type="text" class="app-id-onesignal" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxxx" id="app-id-onesignal" name="mobiconnector-app-id-onesignal" value="<?php echo get_option('mobiconnector_settings-onesignal-api');?>"  required />
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo sprintf(__('Enter your Onesignal Onesignal APP ID. You can find this in App Settings > Keys & IDs. %s','mobiconnector'),'<a target="_blank" href="https://documentation.onesignal.com/docs/accounts-and-keys#section-keys-ids">Read More</a>'); ?>
									</div>
								</div>
							</td> 
						</tr>						
						<tr>							
							<td class="woo-label"><label  for="content-notification"><?php echo esc_html(__('Onesignal APP REST API','mobiconnector')); ?> </label></td>
							<td class="woo-content">
								<input type="text" class="app-id-onesignal" placeholder="" id="rest-api-key-onesignal" name="mobiconnector-rest-api-key-onesignal"  value="<?php echo get_option('mobiconnector_settings-onesignal-restkey');?>" required />
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo sprintf(__('Enter 48 characters of your Onesignal REST API Key. You can find this in App Settings > Keys & IDs. %s','mobiconnector'),'<a target="_blank" href="https://documentation.onesignal.com/docs/accounts-and-keys#section-keys-ids">Read More</a>'); ?>
									</div>
								</div>
							</td> 
						</tr>												
					</table>
				</div>
				<div id="woo-button">
					<input  type="submit" name="publish2" class="button button-primary button-large" value="<?php echo esc_html(__('Save','mobiconnector'));?>">
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</form>
</div>