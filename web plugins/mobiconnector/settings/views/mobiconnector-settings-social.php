<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} 
$checkloginface = '';
$checkfacebooklogin = get_option('mobiconnector_settings-login-facebook');
if($checkfacebooklogin == 1){
    $checkloginface = 'checked="checked"';
}else{
    $checkloginface = '';
}
$checklogingoogle = '';
$checkgooglelogin = get_option('mobiconnector_settings-login-google');
if($checkgooglelogin == 1){
    $checklogingoogle = 'checked="checked"';
}else{
    $checklogingoogle = '';
}
$checkloginlinkedin = '';
$checklinkedinlogin = get_option('mobiconnector_settings-login-linkedin');
if($checklinkedinlogin == 1){
    $checkloginlinkedin = 'checked="checked"';
}else{
    $checkloginlinkedin = '';
}
$checklogintwiter = '';
$checktwitterlogin = get_option('mobiconnector_settings-login-twitter');
if($checktwitterlogin == 1){
    $checklogintwiter = 'checked="checked"';
}else{
    $checklogintwiter = '';
}
$checklogininstagram = '';
$checkinstagramlogin = get_option('mobiconnector_settings-login-instagram');
if($checkinstagramlogin == 1){
    $checklogininstagram = 'checked="checked"';
}else{
    $checklogininstagram = '';
}
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php');?>
<div class="wrap mobiconnector-settings">
    <div class="mobi-div"><?php bamobile_mobiconnector_print_notices(); ?></div>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings" id="settings-form">
		<input type="hidden" name="mtask" value="savesettingsocial"/>		
		<div id="mobi-settings-body">
            <div id="mobi-body-content">					
                <div class="form-group">
                    <h2><?php echo esc_html(__('Facebook Login Settings','mobiconnector')); ?></h2>
                    <div class="form-element">							
                        <div class="mobi-label"><label for="mobiconnector_active_login_facebook"><?php echo esc_html(__('Enable Login by Facebook','mobiconnector'));?></label></div>
                        <div class="mobi-content">
                            <input type="checkbox" style="float:left;" data-type="facecbook" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" data-id="facebook-login" id="mobiconnector_active_login_facebook" name="mobiconnector_active_login_facebook" <?php echo $checkloginface; ?> value="1"   />
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:30px;">
                                    <?php echo sprintf(__('Allow user can log in your mobile application from their Facebook account. %s','mobiconnector'),'<a target="_blank" href="https://developers.facebook.com/docs/apps/register/">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-facebook-appid"><?php echo esc_html(__('Facebook App ID','mobiconnector'));?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkfacebooklogin == 1){echo 'required="required"';} ?> placeholder="xxxxxx" type="text" class="mobi-input mobi-input-facebook" id="mobiconnector-facebook-appid" name="mobiconnector-facebook-appid" value="<?php echo get_option('mobiconnector_settings-facebook-appid'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-facebook-appsecret"><?php echo esc_html(__('Facebook App Secret','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkfacebooklogin == 1){echo 'required="required"';} ?> placeholder="xxxxxxx" type="password" class="mobi-input mobi-input-facebook" id="mobiconnector-facebook-appsecret" name="mobiconnector-facebook-appsecret" value="<?php echo get_option('mobiconnector_settings-facebook-appsecret'); ?>"/>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <h2><?php echo esc_html(__('Google Login Settings','mobiconnector')); ?></h2>
                    <div class="form-element">							
                        <div class="mobi-label"><label for="mobiconnector_active_login_google"><?php echo esc_html(__('Enable Login by Google','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="checkbox" style="float:left;" data-type="google" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" data-id="google-login" id="mobiconnector_active_login_google" name="mobiconnector_active_login_google" <?php echo $checklogingoogle; ?> value="1"   />
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:30px;">
                                    <?php echo sprintf(__('Allow user can log in your mobile application from their Google account. %s','mobiconnector'),'<a target="_blank" href="https://cloud.google.com/video-intelligence/docs/common/auth">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-google-clienid"><?php echo esc_html(__('Google Client ID','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                        <input <?php if($checkgooglelogin == 1){echo 'required="required"';} ?> placeholder="xxxxx-xxxxxxx.apps.googleusercontent.com" type="text" class="mobi-input mobi-input-google" id="mobiconnector-google-clienid" name="mobiconnector-google-clienid" value="<?php echo get_option('mobiconnector_settings-google-clienid'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-google-cliensecret"><?php echo esc_html(__('Google Client Secret','mobiconnector'));?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkgooglelogin == 1){echo 'required="required"';} ?> placeholder="xxxx" type="password" class="mobi-input mobi-input-google" id="mobiconnector-google-cliensecret" name="mobiconnector-google-cliensecret" value="<?php echo get_option('mobiconnector_settings-google-cliensecret'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-google-apikey"><?php echo esc_html(__('Google Api Key','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkgooglelogin == 1){echo 'required="required"';} ?> placeholder="xxxxxxx" type="text" class="mobi-input mobi-input-google" id="mobiconnector-google-apikey" name="mobiconnector-google-apikey" value="<?php echo get_option('mobiconnector_settings-google-apikey'); ?>"/>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <h2><?php echo esc_html(__('LinkedIn Login Settings','mobiconnector')); ?></h2>
                    <div class="form-element">							
                        <div class="mobi-label"><label for="mobiconnector_active_login_linkedin"><?php echo esc_html(__('Enable Login by LinkedIn','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="checkbox" style="float:left;" data-type="linkedin" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" data-id="linkedin-login" id="mobiconnector_active_login_linkedin" name="mobiconnector_active_login_linkedin" <?php echo $checkloginlinkedin; ?> value="1"   />
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:30px;">
                                    <?php echo sprintf(__('Allow user can log in your mobile application from their LinkedIn account. %s','mobiconnector'),'<a target="_blank" href="https://developer.linkedin.com/docs/oauth2">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-linkedin-clientid"><?php echo esc_html(__('LinkedIn Client ID','mobiconnector'));?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checklinkedinlogin == 1){echo 'required="required"';} ?> placeholder="xxxxx" type="text" class="mobi-input mobi-input-linkedin" id="mobiconnector-linkedin-clientid" name="mobiconnector-linkedin-clientid" value="<?php echo get_option('mobiconnector_settings-linkedin-clientid'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-linkedin-clientsecret"><?php echo esc_html(__('Linkedin Client Secret','mobiconnector'));?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checklinkedinlogin == 1){echo 'required="required"';} ?> placeholder="xxxxxx" type="password" class="mobi-input mobi-input-linkedin" id="mobiconnector-linkedin-clientsecret" name="mobiconnector-linkedin-clientsecret" value="<?php echo get_option('mobiconnector_settings-linkedin-clientsecret'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                    <div class="mobi-label"><label class="mobiconnector-dropdown-label"><?php echo esc_html(__('LinkedIn Authorized redirect URIs','mobiconnector')); ?></label></div>                  
                </div>
                </div>
                <hr>
                <div class="form-group">
                    <h2><?php echo esc_html(__('Twitter Login Settings','mobiconnector')); ?></h2>
                    <div class="form-element">							
                        <div class="mobi-label"><label for="mobiconnector_active_login_twitter"><?php echo esc_html(__('Enable Login by Twitter','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input style="float:left;" type="checkbox" data-type="twitter" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" data-id="twitter-login" id="mobiconnector_active_login_twitter" name="mobiconnector_active_login_twitter" <?php echo $checklogintwiter; ?> value="1"   />
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:30px;">
                                    <?php echo sprintf(__('Allow user can log in your mobile application from their Twitter account. %s','mobiconnector'),'<a target="_blank" href="https://developer.twitter.com/en/docs/basics/authentication/guides/access-tokens.html">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-twitter-consumerkey"><?php echo esc_html(__('Twitter Consumer Key','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checktwitterlogin == 1){echo 'required="required"';} ?> placeholder="xxxx" type="text" class="mobi-input mobi-input-twitter" id="mobiconnector-twitter-consumerkey" name="mobiconnector-twitter-consumerkey" value="<?php echo get_option('mobiconnector_settings-twitter-consumerkey'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-twitter-consumersecret"><?php echo esc_html(__('Twitter Consumer Secret','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checktwitterlogin == 1){echo 'required="required"';} ?> placeholder="xxxxx" type="password" class="mobi-input mobi-input-twitter" id="mobiconnector-twitter-consumersecret" name="mobiconnector-twitter-consumersecret" value="<?php echo get_option('mobiconnector_settings-twitter-consumersecret'); ?>"/>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <h2><?php echo esc_html(__('Instagram Login Settings','mobiconnector')); ?></h2>
                    <div class="form-element">							
                        <div class="mobi-label"><label for="mobiconnector_active_login_instagram"><?php echo esc_html(__('Enable Instagram Login','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input style="float:left;" type="checkbox" data-type="instagram" class="mobiconnector-checkbox mobiconnector-checkbox-dropdown" data-id="instagram-login" id="mobiconnector_active_login_instagram" name="mobiconnector_active_login_instagram" <?php echo $checklogininstagram; ?> value="1"   />
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-2px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:30px;">
                                    <?php echo sprintf(__('Allow user can log in your mobile application from their Instagram account. %s','mobiconnector'),'<a target="_blank" href="https://developer.twitter.com/en/docs/basics/authentication/guides/access-tokens.html">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-instagram-clientid"><?php echo esc_html(__('Instagram Client ID','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkinstagramlogin == 1){echo 'required="required"';} ?> placeholder="xxxxx" type="text" class="mobi-input mobi-input-instagram" id="mobiconnector-instagram-clientid" name="mobiconnector-instagram-clientid" value="<?php echo get_option('mobiconnector_settings-instagram-clientid'); ?>"/>
                        </div>
                    </div>
                    <div class="form-element">							
                        <div class="mobi-label"><label class="mobiconnector-dropdown-label" for="mobiconnector-instagram-clientsecret"><?php echo esc_html(__('Instagram Client Secret','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input <?php if($checkinstagramlogin == 1){echo 'required="required"';} ?> placeholder="xxxxx" type="password" class="mobi-input mobi-input-instagram" id="mobiconnector-instagram-clientsecret" name="mobiconnector-instagram-clientsecret" value="<?php echo get_option('mobiconnector_settings-instagram-clientsecret'); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <input type="submit" class="mobi-submit" value="<?php echo esc_html(__('Save','mobiconnector')); ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>