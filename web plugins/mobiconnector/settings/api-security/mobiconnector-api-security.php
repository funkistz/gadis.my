<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php');
$securitykey = get_option('mobiconnector_api_key_database');
$minikey = '';
if(!empty($securitykey)){
	$leng = strlen($securitykey);
	$passchar =  '';
	for($i =  0; $i <= $leng-5;  $i++){
		$passchar .= '•';
	}
	$minikey = $passchar.substr($securitykey,-5);
}
$usekey = get_option('mobiconnector_settings-use-security-key');
$checked = '';
if(!empty($usekey)){
	$checked = 'checked="checked"';
}
$options = get_option('qtranslate_admin_config');
?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('Setting Api key for Mobile App','mobiconnector')); ?></h1>
	<?php bamobile_mobiconnector_print_notices(); ?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=api" id="settings-form">
        <input type="hidden" name="mtask" value="savesettingapi"/>				
		<div id="mobiconnector-settings-body">
			<div id="mobiconnector-body" >
				<div id="mobiconnector-body-content">								
					<?php
						if(current_user_can('administrator')){
					?>
					<div class="mobiconnector_content-security-key">
						<?php 
							if(!empty($securitykey)){
						?>
							<div class="mobiconnector_content_left-security-key">
								<label for="mobiconnector_settings-use-security-key"><?php _e('Use Api Key on Mobile App','mobiconnector'); ?></label>
							</div>		
							<div class="mobiconnector_content_right-security-key">
								<input <?php echo $checked; ?> id="mobiconnector_settings-use-security-key" type="checkbox" class="mobiconnector-checkbox" name="mobiconnector_settings-use-security-key" value="1"/>							
							</div>
							<div class="mobiconnector_content_left-security-key">
								<label><?php _e('Api key','mobiconnector'); ?></label>
							</div>
							<div class="mobiconnector_content_right-security-key">
								<input readonly="true" class="mobiconnector_security_key" type="text" value="<?php echo $securitykey ?>"/>	
							</div>
							<div class="mobiconnector_content_left-security-key">
								<input type="hidden" name="mstask" value="save_settings_key"/>	
								<button class="mobi-submit"><?php _e('Save','mobiconnector'); ?></button>
							</div>
							<div class="mobiconnector_content_right-security-key">
							</div>
						<?php
							}else{
						?>
							<div class="mobiconnector_content_left-security-key">
								<label><?php _e('Create Security Key','mobiconnector'); ?></label>
							</div>
							<div class="mobiconnector_content_right-security-key">
								<input type="hidden" name="mstask" value="create_key"/>	
								<button class="mobi-submit"><?php _e('Create','mobiconnector'); ?></button>
							</div>
						<?php
							}
						?>
					</div>
					<?php
						}else{
					?>
						<div class="mobiconnector_content_left-security-key">
							<label><?php _e('Api key','mobiconnector'); ?> : </label>
						</div>
						<div class="mobiconnector_content_right-security-key">
							<input readonly="true" class="mobiconnector_security_key" type="text" value="<?php echo $minikey ?>"/>	
						</div>
					<?php
						}
					?>
				</div>
			</div>
		</div>
	</form>
</div>