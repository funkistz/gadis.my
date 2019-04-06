<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$popup =  get_option('wooconnector-popup-homepage');
$link = get_option('wooconnector-popup-homepage-link');
$check = get_option('wooconnector-popup-homepage-check');
$from = get_option('wooconnector-popup-homepage-date-from');
$to = get_option('wooconnector-popup-homepage-date-to');
?>
<div class="wrap woopopup">
    <h1><?php echo __('WooConnector Popup','wooconnector')?></h1>
    <?php
		bamobile_mobiconnector_print_notices();
	?>
    <form accept-charset="UTF-8" action="<?php echo admin_url().'/admin-post.php?action=woo_update_popup'; ?>" class="form-horizontal" method="post" id="settings_popup">
        <input type="hidden" name="action" value="woo_update_popup">
        <?php wp_nonce_field( 'woo_update_popup' ); ?>
        <hr>        
        <div id='poststuff'>
            <div id='post-body' class=''>
                <div id='post-body-content'>

                    <div class='wooconnector-hold'>
                        <div id="woo-content-popup" class="<?php if(!empty($popup)){echo '';}else{echo 'woo-hidden';} ?>">
                            <div id="overlay-popup"></div>
                            <div id="content-popup-div"><div id="woo-content-popup-close"><span class="woo-content-close-button"></span></div><img id="content-popup" src="<?php if(!empty($popup)){echo $popup; }else{echo '';} ?>"/></div>
                            <input type="hidden" name="wooconnector-popup-url" id="wooconnector-popup-url" value="<?php if(!empty($popup)){echo $popup; }else{echo '';} ?>"/>
                        </div>
                    </div><!-- 'wooconnector-hold' -->
                </div>
            </div>
        </div>
        <div id="postbox-container-2" class="postbox-container">
            <div class="right">
                <div class="woopopup_configuration">
                    <h3 id="wooconnector-settings-popup"><?php _e('Popup Settings','wooconnector'); ?></h3>
                    <hr>
                    <div class="woopopup_settings_field">
                        <div class="woo-form-control" id="popup-options">
                            <a id="woo-add-popup" class="button"><span style="background:url('<?php echo admin_url( '/images/media-button.png') ?>') no-repeat top left;" class="wp-media-buttons-icon"></span> <?php _e('Add Photo','wooconnector'); ?></a>
                            <a id="woo-delete-popup" class="button <?php if(!empty($popup)){echo '';}else{echo 'woo-hidden';} ?>"><?php _e('Delete Photo','wooconnector'); ?></a>
                        </div>
                        <div class="woo-form-control">
                            <label for="wooconnector_check_popup_link" id="wooconnector_check_popup_link_label"><?php _e('Link','wooconnector'); ?></label>
                            <input type="text" name="wooconnector_popup_link" id="wooconnector_check_popup_link" value="<?php echo $link; ?>"/>
                        </div>
                        <div class="woo-form-control">                            
                            <label for="wooconnector_check_popup_datetime"><?php _e('Use available period','wooconnector'); ?></label>
                            <input type="checkbox" name="wooconnector_check_popup_datetime" id="wooconnector_check_popup_datetime" <?php if(!empty($check) && $check == 1){ echo 'checked="checked"';}else{echo '';}  ?> value="1" />
                            <div id="wooconnector_datetime_picker" class="<?php if(!empty($check) && $check == 1){ echo 'slide-hidden open';}else{echo 'slide-hidden';}?>">
                                <div class="woo-form-content">
                                    <label for="wooconnector_datetimepicker_from"><?php _e('From','wooconnector'); ?> :</label>
                                    <input type='text' name="wooconnector_popup_datepicker_from" id='wooconnector_datetimepicker_from' value="<?php echo $from; ?>" />
                                    <a class="woo-clear-button" data-type="from" id="woo-clear-from"><?php _e('Clear'); ?></a>
                                </div>
                                <div class="woo-form-content">
                                    <label for="wooconnector_datetimepicker_to"><?php _e('To','wooconnector'); ?> :</label>
                                    <input type='text' name="wooconnector_popup_datepicker_to" id='wooconnector_datetimepicker_to' value="<?php echo $to; ?>" />
                                    <a class="woo-clear-button" data-type="to" id="woo-clear-from"><?php _e('Clear'); ?></a>
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery('#wooconnector_datetimepicker_from').datetimepicker({
                                    timeFormat: "hh:mm TT",
                                    dateFormat : 'yy-mm-dd'
                                });

                                jQuery('#wooconnector_datetimepicker_to').datetimepicker({
                                    timeFormat: "hh:mm TT",
                                    dateFormat : 'yy-mm-dd'
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <div id="postbox-container-1" class="postbox-container">
            <div class='right'>
                <div class="woopopup_configuration">
                    <h3 id="wooconnector-settings-popup"><?php _e('Publish','wooconnector'); ?></h3>
                    <hr>
                    <div class="woopopup_publish">
                        <div class="misc-pub-section misc-pub-post-status"><?php _e( "Status", "wooconnector" ) ?>: <span id="post-status-display"><?php _e( "Published", "wooconnector" ) ?></span></div>
                        <div class="misc-pub-section misc-pub-visibility" id="visibility"><?php _e( "Visibility", "wooconnector" ) ?>: <span id="post-visibility-display"><?php _e( "Public", "wooconnector" ) ?></span></div>										
                        <div class="misc-pub-section" id="catalog-visibility"><?php _e( "Catalog visibility", "wooconnector" ) ?>: <strong id="catalog-visibility-display"><?php _e( "Visible", "wooconnector" ) ?></strong></div>
                    </div>
                    <div class='woopopup-configuration'>
                        <input class='alignright button button-primary' type='submit' name='save' id='ms-save' value='<?php _e( "Save", "wooconnector" ) ?>' />								
                        <span class="spinner"></span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>