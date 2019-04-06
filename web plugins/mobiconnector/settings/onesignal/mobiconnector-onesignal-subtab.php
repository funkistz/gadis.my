<?php
	defined('ABSPATH') or die('Denied');
	$active_tab = isset( $_REQUEST[ 'actions' ] ) ? $_REQUEST[ 'actions' ] : 'settings';
?>
<ul class="subsubsub list_sub_tab_mobiconnector">
	<li><a href="?page=mobiconnector-settings&mtab=onesignal&actions=settings" class="<?php if($active_tab == 'settings') { echo esc_html('current'); }{  echo ''; } ?>" ><?php echo esc_html(__('Onesignal API','mobiconnector')); ?></a> | </li>
	<li><a href="?page=mobiconnector-settings&mtab=onesignal&actions=new" class="<?php if($active_tab == 'new') { echo esc_html('current'); }{ echo ''; } ?>" ><?php echo esc_html(__('New Notification','mobiconnector')); ?></a> | </li>
    <li><a href="?page=mobiconnector-settings&mtab=onesignal&actions=notice"  class="<?php if($active_tab == 'notice' || $active_tab == 'viewnotice') { echo esc_html('current'); }{ echo ''; } ?>" ><?php echo esc_html(__('Sent Notification','mobiconnector')); ?></a> | </li>
    <li><a href="?page=mobiconnector-settings&mtab=onesignal&actions=player"  class="<?php if($active_tab == 'player') { echo esc_html('current'); }{ echo ''; } ?>" ><?php echo esc_html(__('All User','mobiconnector')); ?></a></li>
</ul>

<?php
$attab = $active_tab;
if($attab != 'settings'){	
	global $wpdb;
	$checkapi = get_option('mobiconnector_settings-onesignal-api');
	if($checkapi){
		$api = $checkapi;
	}else{
		$api = 0;
	}
	$checkrest = get_option('mobiconnector_settings-onesignal-restkey');
	if($checkrest){
		$rest = $checkrest;
	}else{
		$rest = 0;
	}		
	$table_name = $wpdb->prefix . "mobiconnector_data_api";
	$checks = $wpdb->get_results(
		"
		SELECT * 
		FROM $table_name
		WHERE api_key = '$api'
		"
	);
	$checkapiid = 0;
	if(!empty($checks) || $checks != array()){
		foreach($checks as $check){
			$checkapiid = $check->api_id;		
		}
	}
	if($attab == 'new'){
		if(empty($checkapi) || $checkapi === 0){
			bamobile_mobiconnector_add_notice(__('Your Onesignal Api key is invalid, so you can not use this feature. Please recheck your Onesignal Api Key','mobiconnector'),'error');
		}elseif(empty($checkrest) || $checkrest === 0){
			bamobile_mobiconnector_add_notice(__('Your Onesignal Api key is invalid, so you can not use this feature. Please recheck your Onesignal Api Key','mobiconnector'),'error');
		}	
	}elseif($attab == 'notice'){		
		$table_name = $wpdb->prefix . "mobiconnector_data_notification";
		$datas = $wpdb->get_results(
			"
			SELECT * 
			FROM $table_name	
			WHERE api_id = $checkapiid
			"
		);	
		if(!empty($datas)){	
			foreach($datas as $data){					
				$notificationId = $data->notification_id;	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications/$notificationId?app_id=$api");
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
											'Authorization: Basic '.$rest));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

				$response = curl_exec($ch);
				curl_close($ch);				
				$return = json_decode( $response);
				$failed = 0;
				if(isset($return->failed)){
					$failed = $return->failed;
				}
				$remaining = 0;
				if(isset($return->remaining)){
					$remaining = $return->remaining;
				}
				$successful = 0;
				if(isset($return->successful)){
					$successful = $return->successful;
				}
				$converted = 0;
				if(isset($return->converted)){
					$converted = $return->converted;
				}
				$total = ($failed + $remaining + $successful);				
				$wpdb->update(
					"$table_name",array(					
						"failed" => $failed,
						"remaining" => $remaining,
						"converted" => $converted,  			
						"successful" => $successful,	
						"total" => $total						
					),
					array(
						'notification_id' => $notificationId
					),
					array( 					
						'%d',
						'%d',
						'%d',
						'%d',
						'%d'					
					) 
				);				
			}
		}
	}
	elseif($attab == 'player'){
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/players?app_id=" . $api); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 
												'Authorization: Basic '.$rest)); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response = curl_exec($ch); 
		curl_close($ch); 
		$return = json_decode( $response);		
		if(!empty($return->players)){
			$lists = $return->players;
			foreach($lists as $list){
				$playerid = $list->id;
				$identifier = $list->invalid_identifier;
				$session_count = $list->session_count;
				$device_model = $list->device_model;
				$device_os = $list->device_os;
				$test_type = $list->test_type;
				if(empty($test_type)){
					$test_type = 0;
				}
				$device_type = $list->device_type;
				$language = $list->language;			
				$sdk = $list->sdk;
				$table_name = $wpdb->prefix . "mobiconnector_data_player";
				$checkuser = $wpdb->get_results(
					"
					SELECT * 
					FROM $table_name
					WHERE player_id = '$playerid' AND api_id = $checkapiid
					"
				);
				if(empty($checkuser)){
					$wpdb->insert(
						"$table_name",array(
							"api_id" => $checkapiid,
							"player_id" => $playerid,
							"identifier" => $identifier,
							"session_count" => $session_count,
							"device_model" => $device_model,
							"device_type" => $device_type,
							"device_os" => $device_os,
							"language" => $language,
							"sdk" => $sdk								
						),
						array( 
							'%s',
							'%s',	
							'%s',
							'%d',
							'%s',
							'%d',
							'%s',
							'%s',
							'%s'
						) 
					);
				}else{
					$wpdb->update(
					"$table_name",array(					
						"identifier" => $identifier,
						"session_count" => $session_count,	
						"device_model" => $device_model,	
						"device_os" => $device_os,
						"device_type" => $device_type,
						"language" => $language,
						"sdk" => $sdk	
					),
					array(
						'player_id' => $playerid
					),
					array( 					
						'%s',
						'%d',
						'%s',
						'%s',
						'%d',
						'%s',
						'%s'					
					) 
				);
				}
			}
		}
	}
}
?>