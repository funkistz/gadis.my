<?php

/**
 * Push Notice
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/**
 * Push notification default
 */
function bamobile_sendMobiconnectorMessage()
{
	$checkfirst = get_option('mobiconnector_first_push_notification');
	if ($checkfirst == 1) {
		$inclu = array('All');
	} else {
		$inclu = array('Engaged Users', 'Inactive Users', 'Active Users');
	}
	$fields = array(
		'app_id' => get_option('mobiconnector_settings-onesignal-api'),
		'included_segments' => $inclu,
		'data' => array("foo" => "bar"),
		'headings' => get_option('mobiconnector_settings-onesignal-title'),
		'contents' => get_option('mobiconnector_settings-onesignal-content'),
		'subtitle' => get_option('mobiconnector_settings-onesignal-subtitle'),
		'url' => get_option('mobiconnector_settings-onesignal-push-url'),
		'big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'chrome_web_image' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'adm_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'chrome_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_web_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'adm_large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'firefox_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'adm_small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'ios_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'adm_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_wns_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_led_color' => get_option('mobiconnector_settings-onesignal-led-color'),
		'android_accent_color' => get_option('mobiconnector_settings-onesignal-accent-color'),
		'android_background_layout' => array(
			'image' => get_option('mobiconnector_notification_onesignal_background'),
			'headings_color' => get_option('mobiconnector_settings-onesignal-title-color'),
			'contents_color' => get_option('mobiconnector_settings-onesignal-content-color')
		)
	);

	$fields = json_encode($fields);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Authorization: Basic ' . get_option('mobiconnector_settings-onesignal-restkey')
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);

	update_option('mobiconnector_first_push_notification', 1);
	return $response;
}

/**
 * Push notification with Segment and Exsegment
 * 
 * @param array $segments    list segments
 * @param array $exsegment   list exsegment
 */
function bamobile_sendMobiconnectorMessageBySegment($segments, $exsegment)
{
	$fields = array(
		'app_id' => get_option('mobiconnector_settings-onesignal-api'),
		'included_segments' => $segments,
		'excluded_segments' => $exsegment,
		'data' => array("foo" => "bar"),
		'headings' => get_option('mobiconnector_settings-onesignal-title'),
		'contents' => get_option('mobiconnector_settings-onesignal-content'),
		'subtitle' => get_option('mobiconnector_settings-onesignal-subtitle'),
		'url' => get_option('mobiconnector_settings-onesignal-push-url'),
		'chrome_web_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_web_image' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'adm_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'chrome_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'adm_small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'adm_large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'firefox_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'ios_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'adm_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_wns_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_led_color' => get_option('mobiconnector_settings-onesignal-led-color'),
		'android_accent_color' => get_option('mobiconnector_settings-onesignal-accent-color'),
		'android_background_layout' => array(
			'image' => get_option('mobiconnector_notification_onesignal_background'),
			'headings_color' => get_option('mobiconnector_settings-onesignal-title-color'),
			'contents_color' => get_option('mobiconnector_settings-onesignal-content-color')
		)
	);

	$fields = json_encode($fields);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Authorization: Basic ' . get_option('mobiconnector_settings-onesignal-restkey')
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);
	update_option('mobiconnector_first_push_notification', 1);
	return $response;
}

/**
 * Push notification with player id
 * 
 * @param array $players   list player id
 */
function bamobile_sendMobiconnectorMessageByPlayer($players)
{
	$fields = array(
		'app_id' => get_option('mobiconnector_settings-onesignal-api'),
		'include_player_ids' => $players,
		'data' => array("foo" => "bar"),
		'headings' => get_option('mobiconnector_settings-onesignal-title'),
		'contents' => get_option('mobiconnector_settings-onesignal-content'),
		'subtitle' => get_option('mobiconnector_settings-onesignal-subtitle'),
		'url' => get_option('mobiconnector_settings-onesignal-push-url'),
		'chrome_web_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_web_image' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'adm_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'chrome_big_picture' => get_option('mobiconnector_notification_onesignal_bigimages'),
		'adm_small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'adm_large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'firefox_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'chrome_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'small_icon' => get_option('mobiconnector_notification_onesignal_icon_small'),
		'large_icon' => get_option('mobiconnector_notification_onesignal_icon'),
		'ios_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'adm_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'wp_wns_sound' => get_option('mobiconnector_settings-onesignal-sound'),
		'android_led_color' => get_option('mobiconnector_settings-onesignal-led-color'),
		'android_accent_color' => get_option('mobiconnector_settings-onesignal-accent-color'),
		'android_background_layout' => array(
			'image' => get_option('mobiconnector_notification_onesignal_background'),
			'headings_color' => get_option('mobiconnector_settings-onesignal-title-color'),
			'contents_color' => get_option('mobiconnector_settings-onesignal-content-color')
		)
	);

	$fields = json_encode($fields);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Authorization: Basic ' . get_option('mobiconnector_settings-onesignal-restkey')
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);
	update_option('mobiconnector_first_push_notification', 1);
	return $response;
}

/**
 * Push notification in post edit
 * 
 * @param int    $postid    Id of post
 * @param string $title     Title of notification
 * @param string $content   Content of notification
 */
function bamobile_sendMobiconnectorMessageOnPost($postid, $title, $content)
{
	$type = get_post_type($postid);
	$newurl = '';
	if (!empty($postid)) {
		if ($type === 'post') {
			$newurl = "link://post/" . $postid;
		}
	}
	$imagesid = get_post_thumbnail_id($postid);
	bamobile_mobiconnector_update_icon_save_post($imagesid);
	$image = get_post_meta($imagesid, 'mobiconnector_notification_onesignal_icon', true);
	$wp_upload_dir = wp_upload_dir();
	$image = $wp_upload_dir['baseurl'] . "/" . $image;
	$languageCode = get_locale();
	$language = bamobile_mobiconnector_convert_languagesCode_to_isoCode($languageCode);
	if ($language != 'en') {
		$head = array("en" => $title, $language => $title);
		$cont = array("en" => $content, $language => $content);
	} else {
		$head = array("en" => $title);
		$cont = array("en" => $content);
	}
	$fields = array(
		'app_id' => get_option('mobiconnector_settings-onesignal-api'),
		'included_segments' => array('All'),
		'data' => array("foo" => "bar"),
		'headings' => $head,
		'contents' => $cont,
		'url' => $newurl,
		'chrome_web_icon' => $image,
		'adm_large_icon' => $image,
		'firefox_icon' => $image,
		'chrome_icon' => $image,
		'large_icon' => $image
	);
	$fields = json_encode($fields);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=utf-8',
		'Authorization: Basic ' . get_option('mobiconnector_settings-onesignal-restkey')
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);
	update_option('mobiconnector_first_push_notification', 1);
	return $response;
}

/**
 * Crop Images when the push notification
 * 
 * @param int $imagesid    Id of thumbnail
 */
function bamobile_mobiconnector_update_icon_save_post($imagesid)
{
	$thumnails = array(
		'mobiconnector_notification_onesignal_icon' => array(
			'width' => 256,
			'height' => 256
		)
	);
	$wp_upload_dir = wp_upload_dir();
	$image = get_post_meta($imagesid, '_wp_attached_file', true);
	if (!empty($image)) {
		$absolute_pathto_file = $wp_upload_dir['basedir'] . '/' . $image;
		$path_parts = pathinfo($image);
		$ext = strtolower($path_parts['extension']);
		$basename = strtolower($path_parts['basename']);
		$dirname = strtolower($path_parts['dirname']);
		$filename = strtolower($path_parts['filename']);
		foreach ($thumnails as $key => $value) {
			$path = $dirname . '/' . $filename . '_' . $key . '_' . $value['width'] . '_' . $value['height'] . '.' . $ext;
			$dest = $wp_upload_dir['basedir'] . '/' . $path;
			BAMobileCore::bamobile_resize_image($absolute_pathto_file, $dest, $value['width'], $value['height'], true);
			update_post_meta($imagesid, $key, $path);
		}
	}
}

/**
 * Get detailt notification with notification Id
 * 
 * @param string $notificationId  Id of notification
 */
function bamobile_MobiconnectorgetNotificationById($notificationId)
{
	$api = get_option('mobiconnector_settings-onesignal-api');
	$rest = get_option('mobiconnector_settings-onesignal-restkey');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications/$notificationId?app_id=$api");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Basic ' . $rest
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$response = curl_exec($ch);
	curl_close($ch);
	$return = json_decode($response);
	return $return;
}
?>