<?php
/**
 * Content metabox notification in post
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mobiconnector-meta-box">
    <input style="margin-top:0px;" type="checkbox" id="mobi_push_notification" name="mobiconnector_data-push-notification" value="1"  />
    <label for="mobi_push_notification"><?php _e('Notice this post to customer','mobiconnector'); ?></label><br>
    <div class="mobiconnector-after-checked-notification">
    <label for="mobi_push_notification_title"> <?php _e('Title','mobiconnector'); ?> : </label>
        <input type="text" id="mobi_push_notification_title" name="mobiconnector_data-push-notification-title" value="<?php echo $valueproduct; ?>"  />
        <label for="mobi_push_notification_content"> <?php _e('Content','mobiconnector'); ?> : </label>
        <textarea id="mobi_push_notification_content" name="mobiconnector_data-push-notification-content"></textarea>
        <span class="notti-images-notifaction" style="color:#666">* <?php _e('Feature image used to notification','mobiconnector'); ?></span>
    </div>
</div>