<?php
$active_tab = isset( $_REQUEST[ 'wootab' ] ) ? $_REQUEST[ 'wootab' ] : 'api';
global $wp_version;
?>
<h2 class="nav-tab-wrapper" <?php if($wp_version < '4.8'){echo 'style="border-bottom: 1px solid #ccc"';}  ?>>
	<a id="wooconnector-onesignal-settings-api" href="?page=woo-notifications&amp;wootab=api" class="nav-tab nav-tab-general<?php echo (($active_tab == 'api') ? ' nav-tab-active' : ''); ?>"><?php echo __('Onesignal API','wooconnector');?></a>
	<a id="wooconnector-onesignal-push-new" href="?page=woo-notifications&amp;wootab=new" class="nav-tab nav-tab-template<?php echo (($active_tab == 'new') ? ' nav-tab-active' : ''); ?>"><?php echo __('New Notification','wooconnector');?></a>
	<a id="wooconnector-onesignal-list-send" href="?page=woo-notifications&amp;wootab=list" class="nav-tab nav-tab-template<?php echo (($active_tab == 'list') ? ' nav-tab-active' : ''); ?>"><?php echo __('Sent Notification','wooconnector');?></a>
	<a id="wooconnector-onesignal-all-users" href="?page=woo-notifications&amp;wootab=player" class="nav-tab nav-tab-template<?php echo (($active_tab == 'player') ? ' nav-tab-active' : ''); ?>"><?php echo __('All Users','wooconnector');?></a>		
</h2>
	
<?php
	$attab = $active_tab;
	if($attab != 'api'){	
		global $wpdb;
		if(get_option('wooconnector_settings-api')){
			$api = get_option('wooconnector_settings-api');
		}else{
			$api = 0;
		}
		if(get_option('wooconnector_settings-restkey')){
			$rest = get_option('wooconnector_settings-restkey');
		}else{
			$rest = 0;
		}		
		$table_name = $wpdb->prefix . "wooconnector_data_api";
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
		}if($attab == 'new'){
			if(empty($api) || $checkapiid === 0){
				mobiconnector_add_notice(__('Your Onesignal Api key is invalid, so you can not use this feature. Please recheck your Onesignal Api Key','wooconnector'),'error');
			}elseif(empty($rest) || $rest === 0){
				mobiconnector_add_notice(__('Your Onesignal Api key is invalid, so you can not use this feature. Please recheck your Onesignal Api Key','wooconnector'),'error');
			}	
		}elseif($attab == 'list'){		
			$table_name = $wpdb->prefix . "wooconnector_data_notification";
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
					$device_type = $list->device_type;
					$language = $list->language;			
					$sdk = $list->sdk;
					$table_name = $wpdb->prefix . "wooconnector_data_player";
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
