<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php');
$timeexpiry = get_option('mobiconnector_settings-session-expiry');
$minutes = $timeexpiry/60;
$checkuse = get_option('mobiconnector_settings-use-cache-mobile');
$checkeduser = '';
if($checkuse == 1){
    $checkeduser = 'checked="checked"';
}
?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('Setting Mobile Cache','mobiconnector')); ?></h1>
    <?php bamobile_mobiconnector_print_notices(); ?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=cache" id="settings-form">
        <input type="hidden" name="mtask" value="savesettingcache"/>				
		<div id="mobiconnector-settings-body">
            <div id="mobi-body-content">					
            <div class="form-group">
                <hr>
                <div class="form-element">							
                    <div class="mobi-label"><label for="mobiconnector_use_cache_mobile"><?php echo esc_html(__('Enable Mobile Cache','mobiconnector'));?></label></div>
                    <div class="mobi-content">
                        <input style="float:left;" type="checkbox" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" id="mobiconnector_use_cache_mobile" name="mobiconnector_use_cache_mobile" <?php echo $checkeduser; ?> value="1"   />
                        <div class="mobiconnector-support-input">
                            <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                <span style="margin-left:1px;">?</span>
                            </div>
                            <div class="mobiconnector-tooltip-content" style="top:30px;">
                                <?php echo __('Speed up your mobile application based Cache solution from api.php.','mobiconnector'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-element">							
                    <div class="mobi-label"><label><?php echo esc_html(__('Clear Mobile Cache','mobiconnector'));?></label></div>
                    <div class="mobi-content">
                        <a class="button" id="mobiconnector_button_clear_cache"><?php echo esc_html(__('Clear Cache','mobiconnector'));  ?></a>
                    </div>
                </div>
                <div class="form-element">							
                    <div class="mobi-label"><label><?php echo esc_html(__('Cache Expired (minutes)','mobiconnector'));?></label></div>
                    <div class="mobi-content">
                        <input type="text" style="width:25%;" class="mobi-input" id="mobiconnector_settings-session-expiry" name="mobiconnector_settings-session-expiry" value="<?php echo esc_html($minutes);  ?>"   />
                    </div>
                </div>
                <div class="form-element">							
                    <div class="mobi-label"><input type="submit" class="mobi-submit" value="<?php echo esc_html(__('Save','mobiconnector'));  ?>"   /></label></div>                   
                </div>
            </div>
        </div>
	</form>
</div>