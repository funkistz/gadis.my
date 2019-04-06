<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$languages = array('en' => 'English' ,'vn' => 'Vietnamese','zh-Hant' => 'China (Traditional)','nl' => 'Dutch','ka' => 'Georgian','hi' => 'Hindi','it' => 'Italian','ja' => 'Japanese','ko' => 'Korean','lv' => 'Latvian','lt' => 'Lithuanian','fa' => 'Persian', 'sr' => 'Serbian','th' => 'Thai','ar' => 'Arabic', 'hr' => 'Croatian', 'et' => 'Estonian', 'bg' => 'Bulgarian', 'he' => 'Hebrew', 'ms' => 'Malay','pt' => 'Portuguese', 'sk' => 'Slovak', 'tr' => 'Turkish', 'ca' => 'Catalan', 'cs' => 'Czech', 'fi' => 'Finnish', 'de' => 'German', 'hu' => 'Hungarian', 'nb' => 'Norwegian', 'ro' => 'Romanian', 'es' => 'Spanish', 'uk' => 'Ukrainian', 'zh-Hans' => 'Chinese (Simplified)', 'da' => 'Danish', 'fr' => 'French', 'el' => 'Greek', 'id' => 'Indonesian', 'pl' => 'Polish', 'ru' => 'Russian','sv' => 'Swedish');
$urls = array('url-product'=> 'Product Url', 'url-category' => 'Category Url', 'url-about-us' => 'About Us Url', 'url-bookmark' => 'Bookmark Url','url-term-and-conditions' => 'Term And Conditions','url-privacy-policy'=>'Privacy Policy');
$checktitle = get_option('wooconnector_settings-title');
if($checktitle){
	$titles = $checktitle;
	foreach($titles as $title => $value){
		if($title == 'en'){
			$titlepreview = $value;
		}
	}
}
else{
	$titles = $languages;
}
$checkcontent = get_option('wooconnector_settings-content');
if($checkcontent){
	$contents = $checkcontent;	
	foreach($contents as $content => $value){
		if($content == 'en'){
			$contentpreview = $value;
		}
	}
}
else{
	$contents = $languages;
}
$checksubtitle = get_option('wooconnector_settings-subtitle');
if($checksubtitle){
	$subtitles = $checksubtitle;	
}
else{
	$subtitles = $languages;
}
$icon = get_option('wooconnector_settings-id-icon');
$smicon = get_option('wooconnector_settings-sm-icon');
$bigimages = get_option('wooconnector_settings-bigimages');
$responsetitlecolor = get_option('wooconnector_settings-response-title-color');
$responsecontentcolor = get_option('wooconnector_settings-response-content-color');
$linkdefaultimage = "";//get_bloginfo('url').'/wp-content/plugins/wooconnector/assets/images/default.jpg';
if(get_option('wooconnector_settings-url-selected')){
	$selectedurl = get_option('wooconnector_settings-url-selected');
}else{
	$selectedurl = "";
}
?>
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/subtab.php'); ?>
<div id="wooconnector-preview-notification" <?php if(get_option('wooconnector_settings-bgimages')){echo 'style="background-image: url('.get_option('wooconnector_settings-bgimages').')"'; }else{ echo '';}?>>
	<div id="title-small-icon-notification" class="<?php if(get_option('wooconnector_settings-bgimages')){echo ' bgpreviews'; }else{ echo '';} ?>" <?php if($smicon){echo 'style="display:block"';}else{echo 'style="display:none"';} ?>>
		<img src="<?php if($smicon){echo $smicon;}else{echo '';} ?>" id="wooconnector-small-icon"/>
		<span>You app title</span>
		<button id="click-open-big-images" <?php if($bigimages){ echo 'style="display:block"'; }else{ echo 'style="display:none"';} ?>></button>
	</div>
	<div id="wooconnector-content-notification-preview">
		<div id="wooconnector-preview-icon-notification" class="<?php if($smicon){echo 'small';}else{echo '';} if(get_option('wooconnector_settings-bgimages')){echo ' bgpreviews'; }else{ echo '';} if($smicon && empty($bigimages)){echo ' switchImages';} else{echo '';} if($icon){ echo ' exicon'; } else { echo '';}?>" >
			<img src="<?php if($icon){ echo $icon; } else { echo '';}?>" id="wooconnector-preview-icon"/>
		</div>
		<div id="wooconnector-preview-text-notification" class="<?php if($smicon){echo 'small';}else{echo '';} if($icon){echo ' exicon';}else{ echo "";} if($smicon && empty($bigimages)){echo ' switchImages';} else{echo '';} ?>">
			<div id="wooconnector-privew-title-text" class="<?php if($smicon){echo 'small';}else{echo '';} ?>" <?php if($responsetitlecolor){echo 'style="color:#'.$responsetitlecolor.'"';}?>>
				<?php if(get_option('wooconnector_settings-title')){echo $titlepreview;} else{echo 'Preview';} ?>
			</div>
			<div id="wooconnector-privew-content-text" class="<?php if($smicon){echo 'small';}else{echo '';} ?>" <?php if($responsecontentcolor){echo 'style="color:#'.$responsecontentcolor.'"';}?>>
				<?php if(get_option('wooconnector_settings-content')){echo $contentpreview;} else{echo '';} ?>
			</div>
		</div>
	</div>	
	<div id="wooconnector-big-images-previews" class="<?php if($bigimages){echo 'switchImages'; }else{ echo '';}  if(get_option('wooconnector_settings-bgimages')){echo ' bgpreviews'; }else{ echo '';}?>">
		<img src="<?php if($bigimages){ echo $bigimages; }else{ echo '';} ?>" id="big-images-preview" />
	</div>
</div>

<div class="wrap wooconnector-settings">
	<?php
		$api = get_option('wooconnector_settings-api');
		$rest = get_option('wooconnector_settings-restkey');
	?>
	<h1><?php echo __('New push notification','wooconnector')?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<?php
		if(!empty($api) && !empty($rest)){
	?>
	<form method="POST" class="wooconnector-setting-form" action="?page=woo-notifications&wootab=new" id="settings-form">
		<input type="hidden" name="wootask" value="saveonesignal-content"/>
		<input type="hidden" id="wooconnector-baseurl" name="ajaxbaseurl" value="<?php echo ABSPATH; ?>"/>
		<div id="wooconnector-settings-body">
			<div id="wooconnector-body" >
				<div id="wooconnector-body-content">	
					<table id="table-wooconnector">						
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-content-notification"><?php echo __('Title Notification','wooconnector')?></label> * </td>
							<td class="woo-content">
								<?php foreach($titles as $title => $value){ ?>
								<input type="text" style="display:none" class="wooconnector-web-input-notification wooconnector-web-title-notification" id="wooconnector-web-title-notification-<?php echo $title;?>"  name="wooconnector-web-title-notification[<?php echo $title;?>]" value="<?php if(get_option('wooconnector_settings-title')){echo $value;} ?>"  />									
								<?php } ?>
								<select class="select-languages-notification" name="wooconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo $language; ?>"><?php echo $value; ?></option>										
									<?php } ?>
								</select>
							</td>												
						</tr>	
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-content-notification"><?php echo __('Content Notification','wooconnector')?></label> * </td>
							<td class="woo-content">
								<?php foreach($contents as $content => $value){ ?>
									<textarea style="display:none" class="wooconnector-web-input-notification" id="wooconnector-web-content-notification-<?php echo $content;?>"  name="wooconnector-web-content-notification[<?php echo $content;?>]"><?php if(get_option('wooconnector_settings-content')){echo $value;} ?></textarea>									
								<?php } ?>
								<select class="select-languages-notification" name="wooconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo $language; ?>"><?php echo $value; ?></option>										
									<?php } ?>
								</select>
							</td>												
						</tr>
						<tr style="display:none">							
							<td class="woo-label"><label  for="wooconnector-web-subtitle-notification"><?php echo __('Subtitle Notification','wooconnector')?></label></td>
							<td class="woo-content">
								<?php foreach($subtitles as $subtitle => $value){ ?>
									<input type="text" style="display:none" class="wooconnector-web-input-notification" id="wooconnector-web-subtitle-notification-<?php echo $subtitle;?>"  name="wooconnector-web-subtitle-notification[<?php echo $subtitle;?>]" value="<?php if(get_option('wooconnector_settings-subtitle')){echo $value;} ?>"  />												
								<?php } ?>								
								<select class="select-languages-notification" name="wooconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo $language; ?>"><?php echo $value; ?></option>										
									<?php } ?>
								</select>
								<span class="wooconnector-warning">*<?php echo __('Subtitle only for ios 10+','wooconnector')?></span>
							</td>												
						</tr>
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-url-notification"><?php echo __('Internal Link Clickable','wooconnector')?> </label></td>
							<td class="woo-content url-change"> 
								<select class="select-url-notification" name="wooconnector-web-url-select-notification">
									<?php 
										foreach($urls as $url => $value){
									?>
									<option <?php if($selectedurl == $url){echo 'selected'; }else{ echo ""; } ?> value="<?php echo $url; ?>"><?php echo $value; ?></option>
									<?php
										}
									?>
								</select>
								<?php 
									foreach($urls as $url => $value){
										if($url == 'url-product' || $url == 'url-category'){	
								?>
								<input type="text" class="wooconnector-web-input-notification wooconnector-web-url-notification" placeholder="Input <?php echo str_replace("Url","",$value); ?>name" name="wooconnector-web-url-notification-<?php echo $url; ?>" id="wooconnector-web-url-notification-<?php echo $url; ?>" value="<?php if(get_option('wooconnector_settings-url') && $selectedurl == $url){echo get_option('wooconnector_settings-url');}?>">
								<?php
										}
									}
								?>
								<div id="list-url-notification"></div>								
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-icon-notification"><?php echo __('Small Icon Notification','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="wooconnector-web-smicon-notification" id="wooconnector-web-smicon-notification" value="<?php if($smicon){echo $smicon;}?>">								
								<input type="button" class="wooconnector-button-input-file" id="wooconnector-button-input-smfile" value="Choose File">
								<input <?php if($smicon){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="wooconnector-button-input-file" id="wooconnector-button-delete-smfile" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="wooconnector-images-td">							
							<td class="woo-label"></td>
							<td class="woo-content"> 
								<img class="wooconnector-images-after-choose" id="wooconnector-smicon-web-browser" src="<?php if($smicon){echo $smicon;}else{ echo $linkdefaultimage;}?>"/>
								<span class="wooconnector-warning">* <?php echo __('You should upload a 48x48 image for small icon','wooconnector')?></span>
							</td> 
						</tr>	
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-icon-notification"><?php echo __('Icon Notification','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="wooconnector-web-icon-notification" id="wooconnector-web-icon-notification" value="<?php if($icon){echo $icon;}?>">								
								<input type="button" class="wooconnector-button-input-file" id="wooconnector-button-input-file" value="Choose File">
								<input <?php if($icon){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="wooconnector-button-input-file" id="wooconnector-button-delete-file" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="wooconnector-images-td">							
							<td class="woo-label"><label ></label></td>
							<td class="woo-content"> 
								<img class="wooconnector-images-after-choose" id="wooconnector-icon-web-browser" src="<?php if($icon){echo $icon;}else{ echo $linkdefaultimage;}?>"/>
								<span class="wooconnector-warning">* <?php echo __('You should upload a 256x256 image for icon','wooconnector')?></span>
							</td> 
						</tr>
						<tr >							
							<td class="woo-label"><label  for="wooconnector-web-bigimages-notification"><?php echo __('Bigimages Notification','wooconnector')?> </label></td>
							<td class="woo-content"> 
								<input type="hidden" name="wooconnector-web-bigimages-notification" id="wooconnector-web-bigimages-notification" value="<?php if($bigimages){echo $bigimages;}?>">								
								<input type="button" class="wooconnector-button-input-file" id="wooconnector-button-input-bigimages" value="Choose File">
								<input <?php if($bigimages){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="wooconnector-button-input-file" id="wooconnector-button-delete-bigimages" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="wooconnector-images-td">							
							<td class="woo-label"><label ></label></td>
							<td class="woo-content"> 
								<img class="wooconnector-images-after-choose" id="wooconnector-bigimages-web-browser" src="<?php if($bigimages){echo $bigimages;}else{ echo $linkdefaultimage;}?>"/>
								<span class="wooconnector-warning">* <?php echo __('Use a 2:1 aspect ratio, You should upload a 1024x512 image for big images','wooconnector')?></span>
							</td> 
						</tr>
						<tr id="sound-notification">							
							<td class="woo-label"><label  for="wooconnector-web-sound-notification"><?php echo __('Sound Notification','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="text" name="wooconnector-web-sound-notification" id="wooconnector-web-sound-notification" value="<?php if(get_option('wooconnector_settings-sound')){echo get_option('wooconnector_settings-sound');}?>">							
							</td> 							
						</tr>	
						<tr id="icon-notification">							
							<td class="woo-label"><label  for="wooconnector-web-bgimages-notification"><?php echo __('Android Background Images','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="wooconnector-web-bgimages-notification" id="wooconnector-web-bgimages-notification" value="<?php if(get_option('wooconnector_settings-bgimages')){echo get_option('wooconnector_settings-bgimages');}?>">								
								<input type="button" class="wooconnector-button-input-file" id="wooconnector-button-input-bgimages" value="Choose File">
								<input type="button" <?php if(get_option('wooconnector_settings-bgimages')){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> class="wooconnector-button-input-file" id="wooconnector-button-delete-bgimages" value="Delete File">								
							</td> 							
						</tr>	
						<tr class="wooconnector-images-td">							
							<td class="woo-label"></td>
							<td class="woo-content"> 
								<img class="wooconnector-images-after-choose" id="wooconnector-bgimages-web-browser" src="<?php if(get_option('wooconnector_settings-bgimages')){echo get_option('wooconnector_settings-bgimages');}else{ echo $linkdefaultimage;}?>"/>
								<span class="wooconnector-warning">* <?php echo __('You should upload a 17 : 2 image for background','wooconnector')?> </span>
							</td> 
						</tr>					
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-title-color-notification"><?php echo __('Android Title Color','wooconnector')?> </label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="wooconnector-web-title-color-notification" value="<?php if($responsetitlecolor){echo $responsetitlecolor;}else{echo 'ab2567';}?>">
								<input type="hidden" id="hidden-web-title-color-response-notification" name="wooconnector-web-title-color-response-notification" value="<?php if($responsetitlecolor){echo $responsetitlecolor;}?>" />
								<input type="hidden" id="hidden-web-title-color-notification" name="wooconnector-web-title-color-notification" value="<?php if(get_option('wooconnector_settings-title-color')){echo get_option('wooconnector_settings-title-color');}?>"/>
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-content-color-notification"><?php echo __('Android Content Color','wooconnector')?> </label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="wooconnector-web-content-color-notification" value="<?php if($responsecontentcolor){echo $responsecontentcolor;}else{echo 'ab2567';}?>">
								<input type="hidden" id="hidden-web-content-color-response-notification" name="wooconnector-web-content-color-response-notification" value="<?php if($responsecontentcolor){echo $responsecontentcolor;}?>" />
								<input type="hidden" id="hidden-web-content-color-notification" name="wooconnector-web-content-color-notification" value="<?php if(get_option('wooconnector_settings-content-color')){echo get_option('wooconnector_settings-content-color');}?>"/>
							</td> 
						</tr>
						<tr class="wooconnector-images-td">							
							<td class="woo-label"></td>
							<td class="woo-content"> 
								<span class="wooconnector-warning">* <?php echo __('The title color or content color only changes when you use background images','wooconnector')?> </span>
							</td> 
						</tr>	
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-led-color-notification"><?php echo __('Android Led Color','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="wooconnector-web-led-color-notification" value="<?php if(get_option('wooconnector_settings-response-led-color')){echo get_option('wooconnector_settings-response-led-color');}else{echo 'ab2567';}?>">
								<input type="hidden" id="hidden-web-led-color-response-notification" name="wooconnector-web-led-color-response-notification" value="<?php if(get_option('wooconnector_settings-response-led-color')){echo get_option('wooconnector_settings-response-led-color');}?>" />
								<input type="hidden"  id="hidden-web-led-color-notification" name="wooconnector-web-led-color-notification" value="<?php if(get_option('wooconnector_settings-led-color')){echo get_option('wooconnector_settings-led-color');}?>"/>
							</td> 
						</tr>
						
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-accent-color-notification"><?php echo __('Android Accent Color','wooconnector')?></label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="wooconnector-web-accent-color-notification" value="<?php if(get_option('wooconnector_settings-response-accent-color')){echo get_option('wooconnector_settings-response-accent-color');}else{echo 'ab2567';}?>">
								<input type="hidden" id="hidden-web-accent-color-response-notification" name="wooconnector-web-accent-color-response-notification" value="<?php if(get_option('wooconnector_settings-response-accent-color')){echo get_option('wooconnector_settings-response-accent-color');}?>" />
								<input type="hidden" id="hidden-web-accent-color-notification" name="wooconnector-web-accent-color-notification" value="<?php if(get_option('wooconnector_settings-accent-color')){echo get_option('wooconnector_settings-accent-color');}?>"/>
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="wooconnector-web-accent-color-notification"><?php echo __('Send this message to?','wooconnector')?></label></td>
							<td class="woo-content"> 
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendeveryone" checked="checked" value="sendeveryone"/> <label for="sendeveryone">Send to Everyone</label><br>
								</div>
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendtoparticular" value="sendtoparticular"/> <label for="sendtoparticular"><?php echo __('Send to Particular Segment(s)','wooconnector')?></label><br>
									<div class="dropdown-radiosegment" id="dropdown-sendtoparticular">
										<b><?php echo __('Send to segments','wooconnector')?></b><br>
										<div class="wooconnector-dropdown">
											<input type="text" class="list-segment" name="include_segment" autocomplete="off" />
											<div class="mutliSelect">
												<ul>
													<li class="wooconnector-li-dropdown">
														<input type="checkbox" value="All" id="allsegment" /><label class="wooconnector-label-dropdown" for="allsegment"><?php echo __('All','wooconnector')?></label></li>
													<li class="wooconnector-li-dropdown">
														<input type="checkbox" value="Engaged Users" id="engagedusers" /><label class="wooconnector-label-dropdown" for="engagedusers"><?php echo __('Engaged Users','wooconnector')?></label></li>
													<li class="wooconnector-li-dropdown">
														<input type="checkbox" value="Active Users" id="activeusers" /><label class="wooconnector-label-dropdown" for="activeusers"><?php echo __('Active Users','wooconnector')?></label></li>
													<li class="wooconnector-li-dropdown">
														<input type="checkbox" value="Inactive Users" id="inactiveuser" /><label class="wooconnector-label-dropdown" for="inactiveuser"><?php echo __('Inactive Users','wooconnector')?></label></li>																						
												</ul>
											</div>
										 
										</div>
										<b><?php echo __('Exclude users in segments','wooconnector')?></b><br>
										<div class="wooconnector-dropdown">
											<input type="text" class="list-segment" name="exclude_segment" autocomplete="off" />
											<div class="mutliSelect">
												<ul>
													<li>
														<input type="checkbox" value="All" id="exallsegment" /><label class="wooconnector-label-dropdown" for="exallsegment"><?php echo __('All','wooconnector')?></label></li>
													<li>
														<input type="checkbox" value="Engaged Users" id="exengagedusers" /><label class="wooconnector-label-dropdown" for="exengagedusers"><?php echo __('Engaged Users','wooconnector')?></label></li>
													<li>
														<input type="checkbox" value="Active Users" id="exactiveusers" /><label class="wooconnector-label-dropdown" for="exactiveusers"><?php echo __('Active Users','wooconnector')?></label></li>
													<li>
														<input type="checkbox" value="Inactive Users" id="exinactiveuser" /><label class="wooconnector-label-dropdown" for="exinactiveuser"><?php echo __('Inactive Users','wooconnector')?></label></li>																						
												</ul>
											</div>
										 
										</div>
									</div>
								</div>
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendtotest" value="sendtotest"/> <label for="sendtotest"><?php echo __('Send to Test Device(s)','wooconnector')?></label><br>
									<div class="dropdown-radiosegment" id="dropdown-sendtotest">
										<?php 
											global $wpdb;
											$table_name = $wpdb->prefix . "wooconnector_data_player";
											$players = $wpdb->get_results(
												"
												SELECT * 
												FROM $table_name
												WHERE test_type = 1 OR test_type = 2 
												"
											);
											if(empty($players)){
										?>
											<span class="wooconnector-warning"><?php echo __('You have not added any test users yet. You can do this on the List Player page.','wooconnector')?></span>
										<?php
											}else{
											foreach($players as $player){
										?>
											<div class="list-test-player"><input type="checkbox" name="list_test_player[]" class="input-list-test-player" id="test_player_<?php echo $player->id; ?>" value="<?php echo $player->player_id ?>"/><label for="test_player_<?php echo $player->id; ?>"><?php echo $player->device_model.' '.$player->device_os ?></label></div>
										<?php
											}}
										?>
									</div>
								</div>
							</td> 
						</tr>
					</table>
				</div>
				<div id="woo-button">
					<input  type="submit" id="save-notification" name="publish2" class="button button-primary button-large" value="<?php echo __('Save','wooconnector');?>">					
					<input  type="submit" id="save-and-send-notification" name="saveandsend" class="button button-primary button-large" value="<?php echo __('Save And Send','wooconnector');?>">					
					<div class="clear"></div>
				</div>				
			</div>
		</div>
	</form>
	<?php
		}
	?>
</div>