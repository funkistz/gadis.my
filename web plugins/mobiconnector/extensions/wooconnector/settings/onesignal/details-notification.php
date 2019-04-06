<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.	
	}
	$api = get_option('wooconnector_settings-api');
	$rest = get_option('wooconnector_settings-restkey');
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
	$androidsound = '';
	$url = '';
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
		$table_name = $wpdb->prefix . "wooconnector_data_notification";		
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
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/subtab.php'); ?>
<div class="wrap wooconnector-settings wooconnector-details-notification">	
	<h1><?php echo __('Content Notification','wooconnector') ?></h1>
	
	<label><b><?php echo __('Messeger Title','wooconnector'); ?></b></label>
	<table class="details-notification">
		<?php 
		if(!empty($headings)){		
		foreach($headings as $heading => $value){ ?>
		<tr>
			<td><?php foreach($languages as $language => $val){ if($heading == $language){ echo $val; }} ?></td>
			<td><span><?php echo $value; ?></span></td>
		</tr>
		<?php }} ?>
	</table>
	
	<label><b><?php echo __('Messeger Content','wooconnector'); ?></b></label>
	<table class="details-notification">
		<?php 
		if(!empty($contents)){
		foreach($contents as $content => $value){ ?>
		<tr>
			<td><?php foreach($languages as $language => $val){ if($content == $language){ echo $val; }} ?></td>
			<td><span><?php echo $value; ?></span></td>
		</tr>
		<?php }} ?>
	</table>
	
	<label><b><?php echo __('Segments','wooconnector'); ?></b></label>
	<table class="details-notification">		
		<tr>
			<td> <?php echo __('Send to segments','wooconnector'); ?></td>
			<td><span><?php if(!empty($included_segments)){ echo implode(', ', $included_segments);} ?></span></td>
		</tr>
		<tr>
			<td><?php echo __('Exclude segments','wooconnector'); ?></td>
			<td><span><?php if(!empty($excluded_segments)){ echo implode(', ', $excluded_segments);} ?></span></td>
		</tr>	
	</table>
	<label><b><?php echo __('Recipients','wooconnector'); ?></b></label>
	<table class="details-notification">		
		<tr>
			<td> <?php echo __('Total Number of Recipients','wooconnector'); ?></td>
			<td><span><?php echo $recipients; ?></span></td>
		</tr>		
	</table>	
	<?php 
		if(isset($return->isAndroid) && $return->isAndroid){
	?>
	<img src="<?php echo home_url().'/wp-content/plugins/wooconnector/'.'assets/images/android-default.svg' ?>" class="icon-details-notification"/>	
	<h1><?php echo __('Google Android','wooconnector') ?></h1>
	<table class="details-notification">
		<tr>
			<td><?php echo __('Big Picture ','wooconnector'); ?></td>
			<td><img class="wooconnector-details-notification-images" src="<?php if($bigimage !== ''){ echo $bigimage;}  ?>"/></td>
		</tr>
		<tr>
			<td><?php echo __('Large Icon ','wooconnector'); ?></td>
			<td><img class="wooconnector-details-notification-images" src="<?php if($largeicon !== ''){ echo $largeicon;} ?>"/></td>
		</tr>
		<tr>
			<td><?php echo __('Small Icon ','wooconnector'); ?></td>
			<td><img class="wooconnector-details-notification-images" src="<?php if($smalicon !== ''){ echo $smalicon;} ?>"/></td>
		</tr>
		<tr>
			<td><?php echo __('Sound ','wooconnector'); ?></td>
			<td><span><?php echo $androidsound; ?></span></td>
		</tr>
		<tr>
			<td><?php echo __('Url ','wooconnector'); ?></td>
			<td><span><?php if($url == 'f'){echo '';}else{echo $url;} ?></span></td>
		</tr>
	</table>
	<?php } 
		if(isset($return->isIos) && $return->isIos){
	?>
	<img src="<?php echo home_url().'/wp-content/plugins/wooconnector/'.'assets/images/apple-default.svg' ?>" class="icon-details-notification"/>	
	<h1><?php echo __('Apple iOS','wooconnector') ?></h1>
	<table class="details-notification">		
		<tr>
			<td><?php echo __('Sound ','wooconnector'); ?></td>
			<td><span><?php echo $iossound; ?></span></td>
		</tr>
		<tr>
			<td><?php echo __('Url ','wooconnector'); ?></td>
			<td><span><?php if($url == 'f'){echo '';}else{echo $url;} ?></span></td>
		</tr>
	</table>
	<?php } ?>
</div>