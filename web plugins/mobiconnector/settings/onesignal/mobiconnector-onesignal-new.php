<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$languages = array('en' => 'English' ,'vn' => 'Vietnamese','zh-Hant' => 'China (Traditional)','nl' => 'Dutch','ka' => 'Georgian','hi' => 'Hindi','it' => 'Italian','ja' => 'Japanese','ko' => 'Korean','lv' => 'Latvian','lt' => 'Lithuanian','fa' => 'Persian', 'sr' => 'Serbian','th' => 'Thai','ar' => 'Arabic', 'hr' => 'Croatian', 'et' => 'Estonian', 'bg' => 'Bulgarian', 'he' => 'Hebrew', 'ms' => 'Malay','pt' => 'Portuguese', 'sk' => 'Slovak', 'tr' => 'Turkish', 'ca' => 'Catalan', 'cs' => 'Czech', 'fi' => 'Finnish', 'de' => 'German', 'hu' => 'Hungarian', 'nb' => 'Norwegian', 'ro' => 'Romanian', 'es' => 'Spanish', 'uk' => 'Ukrainian', 'zh-Hans' => 'Chinese (Simplified)', 'da' => 'Danish', 'fr' => 'French', 'el' => 'Greek', 'id' => 'Indonesian', 'pl' => 'Polish', 'ru' => 'Russian','sv' => 'Swedish');
$urls = array('url-post'=> 'Post Url', 'url-category' => 'Category Url', 'url-about-us' => 'About Us Url', 'url-bookmark' => 'Bookmark Url','url-term-and-conditions' => 'Term And Conditions','url-privacy-policy'=>'Privacy Policy');
$checktitle = get_option('mobiconnector_settings-onesignal-title');
$titles = array();
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
$checkcontent = get_option('mobiconnector_settings-onesignal-content');
$contents = array();
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
$checksubtitle = get_option('mobiconnector_settings-subtitle');
$subtitles = array();
if($checksubtitle){
	$subtitles = $checksubtitle;	
}
else{
	$subtitles = $languages;
}
$icon = get_option('mobiconnector_settings-onesignal-id-icon');
$smicon = get_option('mobiconnector_settings-onesignal-sm-icon');
$bigimages = get_option('mobiconnector_settings-onesignal-bigimages');
$responsetitlecolor = get_option('mobiconnector_settings-onesignal-response-title-color');
$responsecontentcolor = get_option('mobiconnector_settings-onesignal-response-content-color');
$linkdefaultimage = "";
$checkurl = get_option('mobiconnector_settings-onesignal-url-selected');
if($checkurl){
	$selectedurl = $checkurl;
}else{
	$selectedurl = "";
}
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>
<div id="mobiconnector-preview-notification" <?php if(get_option('mobiconnector_settings-onesignal-bgimages')){echo 'style="background-image: url('.get_option('mobiconnector_settings-onesignal-bgimages').')"'; }else{ echo '';}?>>
	<div id="title-small-icon-notification" class="<?php if(get_option('mobiconnector_settings-onesignal-bgimages')){echo esc_html(' bgpreviews'); }else{ echo '';} ?>" <?php if($smicon){echo 'style="display:block"';}else{echo 'style="display:none"';} ?>>
		<img src="<?php if($smicon){echo esc_html($smicon);}else{echo '';} ?>" id="mobiconnector-small-icon"/>
		<span><?php echo esc_html_e('You app title'); ?></span>
		<button id="click-open-big-images" <?php if($bigimages){ echo 'style="display:block"'; }else{ echo 'style="display:none"';} ?>></button>
	</div>
	<div id="mobiconnector-content-notification-preview">
		<div id="mobiconnector-preview-icon-notification" class="<?php if($smicon){echo esc_html('small');}else{echo '';} if(get_option('mobiconnector_settings-onesignal-bgimages')){echo esc_html(' bgpreviews'); }else{ echo '';} if($smicon && empty($bigimages)){echo esc_html(' switchImages');} else{echo '';} if($icon){ echo esc_html(' exicon'); } else { echo '';}?>" >
			<img src="<?php if($icon){ echo esc_html($icon); } else { echo '';}?>" id="mobiconnector-preview-icon"/>
		</div>
		<div id="mobiconnector-preview-text-notification" class="<?php if($smicon){echo esc_html('small');}else{echo '';} if($icon){echo esc_html(' exicon');}else{ echo "";} if($smicon && empty($bigimages)){echo esc_html(' switchImages');} else{echo '';} ?>">
			<div id="mobiconnector-privew-title-text" class="<?php if($smicon){echo esc_html('small');}else{echo '';} ?>" <?php if($responsetitlecolor){echo 'style="color:#'.$responsetitlecolor.'"';}?>>
				<?php if(get_option('mobiconnector_settings-onesignal-title')){echo esc_html($titlepreview);} else{echo esc_html('Preview');} ?>
			</div>
			<div id="mobiconnector-privew-content-text" class="<?php if($smicon){echo esc_html('small');}else{echo '';} ?>" <?php if($responsecontentcolor){echo 'style="color:#'.$responsecontentcolor.'"';}?>>
				<?php if(get_option('mobiconnector_settings-onesignal-content')){echo esc_html($contentpreview);} else{echo '';} ?>
			</div>
		</div>
	</div>	
	<div id="mobiconnector-big-images-previews" class="<?php if($bigimages){echo esc_html('switchImages'); }else{ echo '';}  if(get_option('mobiconnector_settings-onesignal-bgimages')){echo esc_html(' bgpreviews'); }else{ echo '';}?>">
		<img src="<?php if($bigimages){ echo esc_html($bigimages); }else{ echo '';} ?>" id="big-images-preview" />
	</div>
</div>

<div class="wrap mobiconnector-settings">
	<div id="mobiconnector-sub-menu">
		<?php require_once(MOBICONNECTOR_ABSPATH.'settings/onesignal/mobiconnector-onesignal-subtab.php'); ?>
	</div>
	<?php
		$api = get_option('mobiconnector_settings-onesignal-api');
		$rest = get_option('mobiconnector_settings-onesignal-restkey');
	?>
	<h1><?php echo esc_html(__('New push notification','mobiconnector')); ?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<?php
		if(!empty($api) && !empty($rest)){
	?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=onesignal&actions=new" id="settings-form">
		<input type="hidden" name="mtask" value="saveonesignal"/>
		<input type="hidden" name="mstask" value="onesignal"/>
		<input type="hidden" id="mobiconnector-baseurl" name="ajaxbaseurl" value="<?php echo esc_html(ABSPATH); ?>"/>
		<div id="mobiconnector-settings-body">
			<div id="mobiconnector-body" >
				<div id="mobiconnector-body-content">	
					<table id="table-mobiconnector">						
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-content-notification"><?php echo esc_html(__('Title Notification','mobiconnector')); ?></label> * </td>
							<td class="woo-content">
								<?php foreach($titles as $title => $value){ ?>
								<input type="text" style="display:none" class="mobiconnector-web-input-notification mobiconnector-web-title-notification" id="mobiconnector-web-title-notification-<?php echo esc_html($title); ?>"  name="mobiconnector-web-title-notification[<?php echo esc_html($title); ?>]" value="<?php if(get_option('mobiconnector_settings-onesignal-title')){echo esc_html($value); } ?>"  />									
								<?php } ?>
								<select class="select-languages-notification" name="mobiconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo esc_html($language); ?>"><?php echo esc_html($value); ?></option>										
									<?php } ?>
								</select>
							</td>												
						</tr>	
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-content-notification"><?php echo esc_html(__('Content Notification','mobiconnector')); ?></label> * </td>
							<td class="woo-content">
								<?php foreach($contents as $content => $value){ ?>
									<textarea style="display:none" class="mobiconnector-web-input-notification" id="mobiconnector-web-content-notification-<?php echo esc_html($content); ?>"  name="mobiconnector-web-content-notification[<?php echo esc_html($content); ?>]"><?php if(get_option('mobiconnector_settings-onesignal-content')){echo esc_html($value); } ?></textarea>									
								<?php } ?>
								<select class="select-languages-notification" name="mobiconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo esc_html($language); ?>"><?php echo esc_html($value); ?></option>										
									<?php } ?>
								</select>
							</td>												
						</tr>
						<tr style="display:none">							
							<td class="woo-label"><label  for="-web-subtitle-notification"><?php echo esc_html(__('Subtitle Notification','mobiconnector')); ?></label></td>
							<td class="woo-content">
								<?php foreach($subtitles as $subtitle => $value){ ?>
									<input type="text" style="display:none" class="mobiconnector-web-input-notification" id="mobiconnector-web-subtitle-notification-<?php echo esc_html($subtitle); ?>"  name="mobiconnector-web-subtitle-notification[<?php echo esc_html($subtitle); ?>]" value="<?php if(get_option('mobiconnector_settings-subtitle')){echo esc_html($value); } ?>"  />												
								<?php } ?>								
								<select class="select-languages-notification" name="mobiconnector-web-language-notification">
									<?php foreach($languages as $language => $value){ ?>
										<option value="<?php echo esc_html($language); ?>"><?php echo esc_html($value); ?></option>										
									<?php } ?>
								</select>
								<span class="mobiconnector-warning">*<?php echo esc_html(__('Subtitle only for ios 10+','mobiconnector')); ?></span>
							</td>												
						</tr>
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-url-notification"><?php echo esc_html(__('Internal Link Clickable','mobiconnector')); ?> </label></td>
							<td class="woo-content url-change"> 
								<select class="select-url-notification" name="mobiconnector-web-url-select-notification">
									<?php 
										foreach($urls as $url => $value){
									?>
									<option <?php if($selectedurl == $url){echo esc_html('selected'); }else{ echo ""; } ?> value="<?php echo esc_html($url); ?>"><?php echo esc_html($value); ?></option>
									<?php
										}
									?>
								</select>
								<?php 
									foreach($urls as $url => $value){
										if($url == 'url-post' || $url == 'url-category'){	
								?>
								<input type="text" class="mobiconnector-web-input-notification mobiconnector-web-url-notification" placeholder="Input <?php echo esc_html(str_replace("Url","",$value)); ?>name" name="mobiconnector-web-url-notification-<?php echo esc_html($url); ?>" id="mobiconnector-web-url-notification-<?php echo esc_html($url); ?>" value="<?php if(get_option('mobiconnector_settings-onesignal-url') && $selectedurl == $url){echo get_option('mobiconnector_settings-onesignal-url');}?>">
								<?php
										}
									}
								?>
								<div id="list-url-notification"></div>								
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-icon-notification"><?php echo esc_html(__('Small Icon Notification','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="mobiconnector-web-smicon-notification" id="mobiconnector-web-smicon-notification" value="<?php if($smicon){echo esc_html($smicon); }?>">								
								<input type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-input-smfile" value="Choose File">
								<input <?php if($smicon){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-delete-smfile" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="mobiconnector-images-td">							
							<td class="woo-label"></td>
							<td class="woo-content"> 
								<img class="mobiconnector-images-after-choose" id="mobiconnector-smicon-web-browser" src="<?php if($smicon){echo esc_html($smicon); }else{ echo esc_html($linkdefaultimage);}?>"/>
								<span class="mobiconnector-warning">* <?php echo esc_html(__('You should upload a 48x48 image for small icon','mobiconnector')); ?></span>
							</td> 
						</tr>	
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-icon-notification"><?php echo esc_html(__('Icon Notification','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="mobiconnector-web-icon-notification" id="mobiconnector-web-icon-notification" value="<?php if($icon){echo esc_html($icon);}?>">								
								<input type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-input-file" value="Choose File">
								<input <?php if($icon){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-delete-file" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="mobiconnector-images-td">							
							<td class="woo-label"><label ></label></td>
							<td class="woo-content"> 
								<img class="mobiconnector-images-after-choose" id="mobiconnector-icon-web-browser" src="<?php if($icon){echo esc_html($icon); }else{ echo esc_html($linkdefaultimage); }?>"/>
								<span class="mobiconnector-warning">* <?php echo esc_html(__('You should upload a 256x256 image for icon','mobiconnector')); ?></span>
							</td> 
						</tr>
						<tr >							
							<td class="woo-label"><label  for="mobiconnector-web-bigimages-notification"><?php echo esc_html(__('Bigimages Notification','mobiconnector')); ?> </label></td>
							<td class="woo-content"> 
								<input type="hidden" name="mobiconnector-web-bigimages-notification" id="mobiconnector-web-bigimages-notification" value="<?php if($bigimages){echo esc_html($bigimages); }?>">								
								<input type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-input-bigimages" value="Choose File">
								<input <?php if($bigimages){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-delete-bigimages" value="Delete File">
							</td> 							
						</tr>										
						
						<tr class="mobiconnector-images-td">							
							<td class="woo-label"><label ></label></td>
							<td class="woo-content"> 
								<img class="mobiconnector-images-after-choose" id="mobiconnector-bigimages-web-browser" src="<?php if($bigimages){echo esc_html($bigimages); }else{ echo esc_html($linkdefaultimage); }?>"/>
								<span class="mobiconnector-warning">* <?php echo esc_html(__('Use a 2:1 aspect ratio, You should upload a 1024x512 image for big images','mobiconnector')); ?></span>
							</td> 
						</tr>
						<tr id="sound-notification">							
							<td class="woo-label"><label  for="mobiconnector-web-sound-notification"><?php echo esc_html(__('Sound Notification','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="text" name="mobiconnector-web-sound-notification" id="mobiconnector-web-sound-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-sound')){echo get_option('mobiconnector_settings-onesignal-sound');}?>">							
							</td> 							
						</tr>						
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-title-color-notification"><?php echo esc_html(__('Android Title Color','mobiconnector')); ?> </label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="mobiconnector-web-title-color-notification" value="<?php if($responsetitlecolor){echo $responsetitlecolor;}else{echo 'ab2567';}?>">
								<input type="hidden" id="hidden-web-title-color-response-notification" name="mobiconnector-web-title-color-response-notification" value="<?php if($responsetitlecolor){echo esc_html($responsetitlecolor);}?>" />
								<input type="hidden" id="hidden-web-title-color-notification" name="mobiconnector-web-title-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-title-color')){echo get_option('mobiconnector_settings-onesignal-title-color');}?>"/>
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-content-color-notification"><?php echo esc_html(__('Android Content Color','mobiconnector')); ?> </label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="mobiconnector-web-content-color-notification" value="<?php if($responsecontentcolor){echo esc_html($responsecontentcolor);}else{echo esc_html('ab2567');}?>">
								<input type="hidden" id="hidden-web-content-color-response-notification" name="mobiconnector-web-content-color-response-notification" value="<?php if($responsecontentcolor){echo esc_html($responsecontentcolor);}?>" />
								<input type="hidden" id="hidden-web-content-color-notification" name="mobiconnector-web-content-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-content-color')){echo get_option('mobiconnector_settings-onesignal-content-color');}?>"/>
							</td> 
						</tr>
						<tr id="icon-notification">							
							<td class="woo-label"><label  for="mobiconnector-web-bgimages-notification"><?php echo esc_html(__('Android Background Images','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="hidden" name="mobiconnector-web-bgimages-notification" id="mobiconnector-web-bgimages-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-bgimages')){echo get_option('mobiconnector_settings-onesignal-bgimages');}?>">								
								<input type="button" class="mobiconnector-button-input-file" id="mobiconnector-button-input-bgimages" value="Choose File">
								<input type="button" <?php if(get_option('mobiconnector_settings-onesignal-bgimages')){ echo 'style="display:block"';}else{ echo 'style="display:none"'; }?> class="mobiconnector-button-input-file" id="mobiconnector-button-delete-bgimages" value="Delete File">								
							</td> 							
						</tr>						
						
						<tr class="mobiconnector-images-td">							
							<td class="woo-label"></td>
							<td class="woo-content"> 
								<img class="mobiconnector-images-after-choose" id="mobiconnector-bgimages-web-browser" src="<?php if(get_option('mobiconnector_settings-onesignal-bgimages')){echo get_option('mobiconnector_settings-onesignal-bgimages');}else{ echo esc_html($linkdefaultimage); }?>"/>
								<span class="mobiconnector-warning">* <?php echo esc_html(__('You should upload a 17 : 2 image for background','mobiconnector')); ?> </span>
							</td> 
						</tr>
						
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-led-color-notification"><?php echo esc_html(__('Android Led Color','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="mobiconnector-web-led-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-response-led-color')){echo get_option('mobiconnector_settings-onesignal-response-led-color');}else{echo esc_html('ab2567');}?>">
								<input type="hidden" id="hidden-web-led-color-response-notification" name="mobiconnector-web-led-color-response-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-response-led-color')){echo get_option('mobiconnector_settings-onesignal-response-led-color');}?>" />
								<input type="hidden"  id="hidden-web-led-color-notification" name="mobiconnector-web-led-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-led-color')){echo get_option('mobiconnector_settings-onesignal-led-color');}?>"/>
							</td> 
						</tr>
						
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-accent-color-notification"><?php echo esc_html(__('Android Accent Color','mobiconnector')); ?></label></td>
							<td class="woo-content"> 
								<input type="text" class="jscolor" id="mobiconnector-web-accent-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-response-accent-color')){echo get_option('mobiconnector_settings-onesignal-response-accent-color');}else{echo esc_html('ab2567');}?>">
								<input type="hidden" id="hidden-web-accent-color-response-notification" name="mobiconnector-web-accent-color-response-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-response-accent-color')){echo get_option('mobiconnector_settings-onesignal-response-accent-color');}?>" />
								<input type="hidden" id="hidden-web-accent-color-notification" name="mobiconnector-web-accent-color-notification" value="<?php if(get_option('mobiconnector_settings-onesignal-accent-color')){echo get_option('mobiconnector_settings-onesignal-accent-color');}?>"/>
							</td> 
						</tr>
						<tr>							
							<td class="woo-label"><label  for="mobiconnector-web-accent-color-notification"><?php echo esc_html(__('Send this message to?','mobiconnector'));?></label></td>
							<td class="woo-content"> 
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendeveryone" checked="checked" value="sendeveryone"/> <label for="sendeveryone"><?php esc_html_e('Send to Everyone','mobiconnector'); ?></label><br>
								</div>
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendtoparticular" value="sendtoparticular"/> <label for="sendtoparticular"><?php echo esc_html(__('Send to Particular Segment(s)','mobiconnector')); ?></label><br>
									<div class="dropdown-radiosegment" id="dropdown-sendtoparticular">
										<b><?php echo esc_html(__('Send to segments','mobiconnector')); ?></b><br>
										<div class="mobiconnector-dropdown">
											<input type="text" class="list-segment" name="include_segment" autocomplete="off" />
											<div class="mutliSelect">
												<ul>
													<li class="mobiconnector-li-dropdown">
														<input type="checkbox" value="All" id="allsegment" /><label class="mobiconnector-label-dropdown" for="allsegment"><?php echo esc_html(__('All','mobiconnector')); ?></label></li>
													<li class="mobiconnector-li-dropdown">
														<input type="checkbox" value="Engaged Users" id="engagedusers" /><label class="mobiconnector-label-dropdown" for="engagedusers"><?php echo esc_html(__('Engaged Users','mobiconnector')); ?></label></li>
													<li class="mobiconnector-li-dropdown">
														<input type="checkbox" value="Active Users" id="activeusers" /><label class="mobiconnector-label-dropdown" for="activeusers"><?php echo esc_html(__('Active Users','mobiconnector')); ?></label></li>
													<li class="mobiconnector-li-dropdown">
														<input type="checkbox" value="Inactive Users" id="inactiveuser" /><label class="mobiconnector-label-dropdown" for="inactiveuser"><?php echo esc_html(__('Inactive Users','mobiconnector')); ?></label></li>																						
												</ul>
											</div>
										 
										</div>
										<b><?php echo esc_html(__('Exclude users in segments','mobiconnector')); ?></b><br>
										<div class="mobiconnector-dropdown">
											<input type="text" class="list-segment" name="exclude_segment" autocomplete="off" />
											<div class="mutliSelect">
												<ul>
													<li>
														<input type="checkbox" value="All" id="exallsegment" /><label class="mobiconnector-label-dropdown" for="exallsegment"><?php echo esc_html(__('All','mobiconnector')); ?></label></li>
													<li>
														<input type="checkbox" value="Engaged Users" id="exengagedusers" /><label class="mobiconnector-label-dropdown" for="exengagedusers"><?php echo esc_html(__('Engaged Users','mobiconnector')); ?></label></li>
													<li>
														<input type="checkbox" value="Active Users" id="exactiveusers" /><label class="mobiconnector-label-dropdown" for="exactiveusers"><?php echo esc_html(__('Active Users','mobiconnector')); ?></label></li>
													<li>
														<input type="checkbox" value="Inactive Users" id="exinactiveuser" /><label class="mobiconnector-label-dropdown" for="exinactiveuser"><?php echo esc_html(__('Inactive Users','mobiconnector')); ?></label></li>																						
												</ul>
											</div>
										 
										</div>
									</div>
								</div>
								<div class="segment-border-div">
									<input type="radio" class="radiosegment" name="checksegment" id="sendtotest" value="sendtotest"/> <label for="sendtotest"><?php echo esc_html(__('Send to Test Device(s)','mobiconnector')); ?></label><br>
									<div class="dropdown-radiosegment" id="dropdown-sendtotest">
										<?php 
											global $wpdb;
											$table_name = $wpdb->prefix . "mobiconnector_data_player";
											$players = $wpdb->get_results(
												"
												SELECT * 
												FROM $table_name
												WHERE test_type = 1 OR test_type = 2 
												"
											);
											if(empty($players)){
										?>
											<span class="mobiconnector-warning"><?php echo esc_html(__('You have not added any test users yet. You can do this on the List Player page.','mobiconnector')); ?></span>
										<?php
											}else{
											foreach($players as $player){
										?>
											<div class="list-test-player"><input type="checkbox" name="list_test_player[]" class="input-list-test-player" id="test_player_<?php echo esc_html($player->id); ?>" value="<?php echo esc_html($player->player_id); ?>"/><label for="test_player_<?php echo esc_html($player->id); ?>"><?php echo esc_html($player->device_model.' '.$player->device_os); ?></label></div>
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
					<input  type="submit" id="save-notification" name="publish2" class="button button-primary button-large" value="<?php echo esc_html(__('Save','mobiconnector'));?>">					
					<input  type="submit" id="save-and-send-notification" name="saveandsend" class="button button-primary button-large" value="<?php echo esc_html(__('Save And Send','mobiconnector'));?>">					
					<div class="clear"></div>
				</div>				
			</div>
		</div>
	</form>
	<?php } ?>
</div>