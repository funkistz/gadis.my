<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/subtab.php'); ?>
<?php
	$checksuccess = get_option('wooconnector_settings-api-success');
	if($checksuccess == 1){
?>
	<div class="notti-settings-onsignal setting-success"><?php echo __('Save API successfully','wooconnector')?></div>
<?php
	}
?>
<div class="wrap wooconnector-settings">
	<h1><?php echo __('Onesignal API','wooconnector')?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<form method="POST" class="wooconnector-setting-form" action="?page=woo-notifications&wootab=api" id="settings-form">
		<input type="hidden" name="wootask" value="saveonesignal"/>			
		<div id="wooconnector-settings-body">
			<div id="wooconnector-body" >
				<div id="wooconnector-body-content">	
					<table id="table-wooconnector">							
						<tr>							
							<td class="woo-label"><label  for="app-id-onesignal"><?php echo __('Onesignal APP ID','wooconnector')?> </label></td>
							<td class="woo-content">
								<input type="text" class="app-id-onesignal" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxxx" id="app-id-onesignal" name="wooconnector-app-id-onesignal" value="<?php echo get_option('wooconnector_settings-api');?>"  required />
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
							<td class="woo-label"><label  for="content-notification"><?php echo __('Onesignal APP REST API','wooconnector')?> </label></td>
							<td class="woo-content">
								<input type="text" class="app-id-onesignal" placeholder="" id="rest-api-key-onesignal" name="wooconnector-rest-api-key-onesignal"  value="<?php echo get_option('wooconnector_settings-restkey');?>" required />
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
					<input  type="submit" name="publish2" class="button button-primary button-large" value="<?php echo __('Save','wooconnector');?>">
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</form>
</div>