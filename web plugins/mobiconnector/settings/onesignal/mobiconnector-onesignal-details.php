<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.	
	}
	$api = get_option('mobiconnector_settings-onesignal-api');
	$rest = get_option('mobiconnector_settings-onesignal-restkey');
	$idnoti = $_GET['notification'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications/$idnoti?app_id=$api");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type application/json',
								   'Authorization Basic '.$rest));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($ch);
	curl_close($ch);				
	$return = json_decode( $response);	
	$bigimage = '';
	$largeicon = '';
	$smalicon = '';
	$iossound = '';
	$url = '';
	$androidsound = '';
	$headings = array();
	$contents = array();
	$included_segments = array();
	$excluded_segments = array();
	$recipients = 0;
	if(!empty($return) && empty($return->errors)){
		$languages = array('en' => 'English' ,'vn' => 'Vietnamese','zh-Hant' => 'China (Traditional)','nl' => 'Dutch','ka' => 'Georgian','hi' => 'Hindi','it' => 'Italian','ja' => 'Japanese','ko' => 'Korean','lv' => 'Latvian','lt' => 'Lithuanian','fa' => 'Persian', 'sr' => 'Serbian','th' => 'Thai','ar' => 'Arabic', 'hr' => 'Croatian', 'et' => 'Estonian', 'bg' => 'Bulgarian', 'he' => 'Hebrew', 'ms' => 'Malay','pt' => 'Portuguese', 'sk' => 'Slovak', 'tr' => 'Turkish', 'ca' => 'Catalan', 'cs' => 'Czech', 'fi' => 'Finnish', 'de' => 'German', 'hu' => 'Hungarian', 'nb' => 'Norwegian', 'ro' => 'Romanian', 'es' => 'Spanish', 'uk' => 'Ukrainian', 'zh-Hans' => 'Chinese (Simplified)', 'da' => 'Danish', 'fr' => 'French', 'el' => 'Greek', 'id' => 'Indonesian', 'pl' => 'Polish', 'ru' => 'Russian','sv' => 'Swedish');
		$headings = isset($return->headings) ? $return->headings : array();
		$contents = isset($return->contents) ? $return->contents : array();
		$included_segments = isset($return->included_segments) ? $return->included_segments : array();
		$excluded_segments = isset($return->excluded_segments) ? $return->excluded_segments : array();		
		global $wpdb;
		$table_name = $wpdb->prefix . "mobiconnector_data_notification";		
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name
			WHERE notification_id = '$idnoti'
			"
		);
		foreach($datas as $data)
		{
			$recipients = $data->recipients;
		}
		$bigimage = isset($return->big_picture) ? $return->big_picture : "";
		$largeicon = isset($return->large_icon) ? $return->large_icon : "";
		$smalicon = isset($return->small_icon) ? $return->small_icon : "";
		$iossound = isset($return->ios_sound) ? $return->ios_sound : "";
		$androidsound = isset($return->android_sound) ? $return->android_sound : "";
		$url = isset($return->url) ? $return->url : "";		
	}
?>
	<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>
<div class="wrap mobiconnector-settings mobiconnector-details-notification">	
	<div id="mobiconnector-sub-menu">
    <?php require_once(MOBICONNECTOR_ABSPATH.'settings/onesignal/mobiconnector-onesignal-subtab.php'); ?>
	</div>
	<h1><?php echo esc_html(__('Content Notification','mobiconnector')); ?></h1>
	
	<label><b><?php echo esc_html(__('Messeger Title','mobiconnector')); ?></b></label>
	<table class="details-notification">
		<?php if(!empty($headings)){		
		foreach($headings as $heading => $value){ ?>
		<tr>
			<td><?php foreach($languages as $language => $val){ if($heading == $language){ echo esc_html($val); }} ?></td>
			<td><span><?php echo esc_html($value); ?></span></td>
		</tr>
		<?php }} ?>
	</table>
	
	<label><b><?php echo esc_html(__('Messeger Content','mobiconnector')); ?></b></label>
	<table class="details-notification">
		<?php if(!empty($contents)){
		foreach($contents as $content => $value){ ?>
		<tr>
			<td><?php foreach($languages as $language => $val){ if($content == $language){ echo esc_html($val); }} ?></td>
			<td><span><?php echo esc_html($value); ?></span></td>
		</tr>
		<?php }} ?>
	</table>
	
	<label><b><?php echo esc_html(__('Segments','mobiconnector')); ?></b></label>
	<table class="details-notification">		
		<tr>
			<td> <?php echo esc_html(__('Send to segments','mobiconnector')); ?></td>
			<td><span><?php if(!empty($included_segments)){ echo esc_html(implode(', ', $included_segments));} ?></span></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Exclude segments','mobiconnector')); ?></td>
			<td><span><?php if(!empty($excluded_segments)){ echo esc_html(implode(', ', $excluded_segments));} ?></span></td>
		</tr>	
	</table>
	<label><b><?php echo esc_html(__('Recipients','mobiconnector')); ?></b></label>
	<table class="details-notification">		
		<tr>
			<td> <?php echo esc_html(__('Total Number of Recipients','mobiconnector')); ?></td>
			<td><span><?php echo esc_html($recipients); ?></span></td>
		</tr>		
	</table>	
	<?php 
		if(isset($return->isAndroid) && $return->isAndroid){
	?>
	<img src="<?php echo plugin_dir_url('mobiconnector/mobiconnector.php').'/assets/images/android-default.svg' ?>" class="icon-details-notification"/>	
	<h1><?php echo __('Google Android','mobiconnector') ?></h1>
	<table class="details-notification">
		<tr>
			<td><?php echo esc_html(__('Big Picture ','mobiconnector')); ?></td>
			<td><img class="mobiconnector-details-notification-images" src="<?php if($bigimage !== ''){ echo esc_html($bigimage); } ?>"/></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Large Icon ','mobiconnector')); ?></td>
			<td><img class="mobiconnector-details-notification-images" src="<?php if($largeicon !== ''){ echo esc_html($largeicon); } ?>"/></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Small Icon ','mobiconnector')); ?></td>
			<td><img class="mobiconnector-details-notification-images" src="<?php if($smalicon !== ''){ echo esc_html($smalicon); } ?>"/></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Sound ','mobiconnector')); ?></td>
			<td><span><?php echo esc_html($androidsound); ?></span></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Url ','mobiconnector')); ?></td>
			<td><span><?php if($url == 'f'){echo '';}else{echo esc_html($url);} ?></span></td>
		</tr>
	</table>
	<?php } 
		if(isset($return->isIos) && $return->isIos){
	?>
	<img src="<?php echo plugin_dir_url('mobiconnector/mobiconnector.php').'/assets/images/apple-default.svg' ?>" class="icon-details-notification"/>	
	<h1><?php echo esc_html(__('Apple iOS','mobiconnector')); ?></h1>
	<table class="details-notification">		
		<tr>
			<td><?php echo esc_html(__('Sound ','mobiconnector')); ?></td>
			<td><span><?php echo esc_html($return->ios_sound); ?></span></td>
		</tr>
		<tr>
			<td><?php echo esc_html(__('Url ','mobiconnector')); ?></td>
			<td><span><?php if($url == 'f'){echo '';}else{echo esc_html($url);} ?></span></td>
		</tr>
	</table>
	<?php } ?>
</div>