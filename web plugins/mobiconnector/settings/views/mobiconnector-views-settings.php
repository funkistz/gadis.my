<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} 
global $wpdb;
$sql = "SELECT DISTINCT post_type FROM ". $wpdb->prefix."posts";
$listposttype = $wpdb->get_results($sql,ARRAY_N);
foreach($listposttype as $posttype){
    if($posttype[0] == 'post'){
        continue;
    }
    $listcheckboxdatabase['mobi-'.$posttype[0]] = 0; 
}
$listoptionscheckbox = get_option('mobiconnector_settings-post_type');
$listoptionscheckbox = unserialize($listoptionscheckbox);
$types = array();
if(!empty($listoptionscheckbox)){
    $types = $listoptionscheckbox;
}else{
    $types = $listcheckboxdatabase;
}
$listqlanguages = array();
$languagesdisplaymode = array();
$listwpmllanguages = array();
$languageswpmldisplaymode = array();
if(is_plugin_active('qtranslate-x/qtranslate.php')){
    $lang = bamobile_mobiconnector_get_qtranslate_enable_languages();
    if(empty($lang)){
        $listqlanguages = array();
    }
    $languages = qtranxf_default_language_name();
    foreach($lang as $la){
        $listqlanguages[] = array(
            'language' => $la,
            'name'     => $languages[$la]
        );
    } 
    $languagesdisplaymode = get_option('mobiconnector_settings-languages-display-mode');
}elseif(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){         
    $listwpmllanguages = bamobile_mobiconnector_get_wpml_list_languages();
    $languageswpmldisplaymode = get_option('mobiconnector_settings-languages-wpml-display-mode');
}
$maintain = get_option('mobiconnector_settings-maintainmode');
$returnsocials = get_option('mobiconnector_settings-socials-login');
$socialfacebook = $returnsocials['facebook'];
$socialgoogle = $returnsocials['google'];
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('MobiConnector Settings','mobiconnector')); ?></h1>
    <?php bamobile_mobiconnector_print_notices(); ?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings" id="settings-form">
		<input type="hidden" name="mtask" value="savesetting"/>		
		<div id="mobi-settings-body">
            <div id="mobi-body-content">					
                <div class="form-group">
                    <div class="form-element">							
                        <div class="mobi-label"><label><?php echo esc_html(__('Custom Post Type as Post','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <div class="mobi-list-checkbox">
                                <?php
                                    foreach($types as $posttype => $value){  
                                        if($posttype == 'mobi-post' || $posttype == 'mobi-woo-slider' || $posttype == 'mobi-attachment' || $posttype == 'mobi-revision'){
                                            continue;
                                        }
                                        $name = str_replace('mobi-','',$posttype); 
                                        $check = ''; 
                                        if($value == 1){
                                            $check = "checked='checked'";
                                        }else{
                                            $check = '';
                                        }
                                ?>
                                    <div class="mobi-content-list-checkbox">
                                        <input type="checkbox" id="<?php echo __($posttype,'mobiconnector'); ?>" class="mobi-checkbox" name="<?php echo esc_html(__($posttype,'mobiconnector')); ?>" <?php echo esc_html($check); ?> value="1" />
                                        <label class="mobi-checkout-label" for="<?php echo esc_html(__($posttype,'mobiconnector')); ?>"><?php echo esc_html(__(ucfirst($name),'mobiconnector'));?></label>
                                    </div>
                                 <?php  } ?>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Email received when user flagged item','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="someone@domain.com" id="mobiconnector_settings_mail" name="mobiconnector_settings-mail" value="<?php echo get_option('mobiconnector_settings-mail'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo esc_html(__('Email get notifications when someone report a product, an article or a comment from your mobile application.','mobiconnector')); ?>
                                </div>
                            </div>            
                        </div>
                    </div>
                </div>
                <div class="form-group" style="display:none;">
                    <div class="form-element">
                        <div class="mobi-label"><label for="mobiconnector_settings_guest_reviews"><?php echo esc_html(__('Allow Guest Comments','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="checkbox" style="float:left;margin-top:0px;" <?php if($maintain == 1){echo 'checked="checked"';}?> class="mobi-checkbox" id="mobiconnector_settings_guest_reviews" name="mobiconnector_settings-guest-reviews" value="1"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-1px;">
                                    <span style="margin-left:1px;">?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:31px;">
                                    <?php echo esc_html(__("When this mode is enabled, an User open the application on their mobile, they only see Maintenance Page and can not use any functions from your application.
But an administrator's account can login to use mobile application as normally",'mobiconnector')); ?>
                                </div>
                            </div>            
                        </div>
                    </div>
                </div>
                <?php
                    if(is_plugin_active('qtranslate-x/qtranslate.php') && !empty($listqlanguages)){
                ?>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Languages Display Mode','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <select id="mobiconnector-select-languages-displaymode" class="mobiconnector-select" name="mobiconnector-languages-display-mode">
                                <?php
                                    foreach($listqlanguages as $lang){
                                ?>
                                <option value="<?php echo esc_html($lang['language']); ?>"><?php echo esc_html($lang['name']); ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            <?php
                                foreach($listqlanguages as $lang){
                                    $select = '';
                                    if(!empty($languagesdisplaymode) && isset($languagesdisplaymode[$lang['language']])){                                        
                                        $select = $languagesdisplaymode[$lang['language']];
                                    }                                    
                                    if(empty($select) || $select == ''){
                                        $select = 'ltr';
                                    }
                            ?>
                            <div id="mobiconnector-languages-displaymode-<?php echo esc_html($lang['language']); ?>" class="mobiconnector-languages-displaymode">
                                <input type="radio" <?php if($select == 'ltr'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio first-child" id="mobiconnector_settings_display_mode_ltr_<?php echo esc_html($lang['language']); ?>" name="mobiconnector_settings-languages-display-mode[<?php echo esc_html($lang['language']); ?>]" value="ltr"   />
                                <label for="mobiconnector_settings_display_mode_ltr_<?php echo esc_html($lang['language']); ?>"><?php esc_html_e("Left to Right","mobiconnector"); ?></label>
                                <input type="radio" <?php if($select == 'rtl'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio" id="mobiconnector_settings_display_mode_rtl_<?php echo esc_html($lang['language']); ?>" name="mobiconnector_settings-languages-display-mode[<?php echo esc_html($lang['language']); ?>]" value="rtl"   /> 
                                <label for="mobiconnector_settings_display_mode_rtl_<?php echo esc_html($lang['language']); ?>"><?php esc_html_e("Right to Left","mobiconnector"); ?></label>
                            </div>
                            <?php
                                }
                            ?>
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo esc_html(__('Languages mode (LTR or RTL) when they open mobile application.','mobiconnector')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }elseif(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php') && !empty($listwpmllanguages)){
                ?>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Languages Display Mode','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <select id="mobiconnector-select-languages-displaymode" class="mobiconnector-select" name="mobiconnector-languages-display-mode">
                                <?php
                                    foreach($listwpmllanguages as $lang){
                                ?>
                                <option value="<?php echo esc_html($lang['code']); ?>"><?php echo esc_html($lang['english_name']); ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                            <?php
                                foreach($listwpmllanguages as $lang){
                                    $select = '';
                                    if(!empty($languageswpmldisplaymode)){
                                        $select = $languageswpmldisplaymode[$lang['code']];
                                    }                                    
                                    if(empty($select) || $select == ''){
                                        $select = 'ltr';
                                    }
                            ?>
                            <div id="mobiconnector-languages-displaymode-<?php echo esc_html($lang['code']); ?>" class="mobiconnector-languages-displaymode">
                               
                                <input type="radio" <?php if($select == 'ltr'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio first-child" id="mobiconnector_settings_display_mode_ltr_<?php echo esc_html($lang['code']); ?>" name="mobiconnector_settings-languages-wpml-display-mode[<?php echo esc_html($lang['code']); ?>]" value="ltr"   />
                                <label for="mobiconnector_settings_display_mode_ltr_<?php echo esc_html($lang['code']); ?>"><?php esc_html_e("Left to Right","mobiconnector"); ?></label>
                                <input type="radio" <?php if($select == 'rtl'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio" id="mobiconnector_settings_display_mode_rtl_<?php echo esc_html($lang['code']); ?>" name="mobiconnector_settings-languages-wpml-display-mode[<?php echo esc_html($lang['code']); ?>]" value="rtl"   /> 
                                <label for="mobiconnector_settings_display_mode_rtl_<?php echo esc_html($lang['code']); ?>"><?php esc_html_e("Right to Left","mobiconnector"); ?></label>
                            </div>
                            <?php
                                }
                            ?>
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo esc_html(__('Languages mode (LTR or RTL) when they open mobile application.','mobiconnector')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }
                ?>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Google Analytics Tracking Code','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="UA-xxxxxxx-x" id="mobiconnector_settings_google_analytics" name="mobiconnector_settings-google-analytics" value="<?php echo get_option('mobiconnector_settings-google-analytics'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Tracking ID like UA-000000-2 from your google analytics account,  %s','mobiconnector'),'<a target="_blank" href="https://support.google.com/analytics/answer/1008080">Read more</a>'); ?>                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Date Format','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="hh:mm a, dd/MM/yyyy" id="mobiconnector_settings_date_format" name="mobiconnector_settings-date-format" value="<?php echo get_option('mobiconnector_settings-date-format'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Date formatted to only display on your mobile application. %s','mobiconnector'),'<a target="_blank" href="https://docs.angularjs.org/api/ng/filter/date">Read more formatted string</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Default Language','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <?php
                                $selected = '';
                                $picklanguege = get_option('mobiconnector_settings-application-languages');
                                if(!empty($picklanguege)){
                                    $selected = $picklanguege;
                                }else{
                                    $selected = get_locale();
                                }
                                $argslang = array(
                                   'en'     => array('lang' => 'en','name'=> 'English (United States)'),
                                   'af'     => array('lang' => 'af','name'=> 'Afrikaans'),
                                   'ar'     => array('lang' => 'ar','name'=> 'العربية'),
                                   'ary'    => array('lang' => 'ar','name'=> 'العربية المغربية'),
                                   'as'     => array('lang' => 'as','name'=> 'অসমীয়া'),
                                   'az'     => array('lang' => 'az','name'=> 'Azərbaycan dili'),
                                   'azb'    => array('lang' => 'az','name'=> 'گؤنئی آذربایجان'),
                                   'bel'    => array('lang' => 'be','name'=> 'Беларуская мова'),
                                   'bg_BG'  => array('lang' => 'bg','name'=> 'Български'),
                                   'bn_BD'  => array('lang' => 'bn','name'=> 'bn_BD'),
                                   'bo'     => array('lang' => 'bo','name'=> 'བོད་ཡིག'),
                                   'bs_BA'  => array('lang' => 'bs','name'=> 'Bosanski'),
                                   'ca'     => array('lang' => 'ca','name'=> 'Català'),
                                   'ceb'    => array('lang' => 'ceb','name'=> 'Cebuano'),
                                   'cs_CZ'  => array('lang' => 'cs','name'=> 'Čeština'),
                                   'cy'     => array('lang' => 'cy','name'=> 'Cymraeg'),
                                   'da_DK'  => array('lang' => 'da','name'=> 'Dansk'),
                                   'de_CH_informal' => array('lang' => 'de','name'=> 'Deutsch (Schweiz, Du)'),
                                   'de_DE'  => array('lang' => 'de','name'=> 'Deutsch'),
                                   'de_CH'  => array('lang' => 'de','name'=> 'Deutsch (Schweiz)'),
                                   'de_DE_formal'   => array('lang' => 'de','name'=> 'Deutsch (Sie)'),
                                   'dzo'    => array('lang' => 'dz','name'=> 'རྫོང་ཁ'),
                                   'el'     => array('lang' => 'el','name'=> 'Ελληνικά'),
                                   'en_ZA'  => array('lang' => 'en','name'=> 'English (South Africa)'),
                                   'en_CA'  => array('lang' => 'en','name'=> 'English (Canada)'),
                                   'en_NZ'  => array('lang' => 'en','name'=> 'English (New Zealand)'),
                                   'en_AU'  => array('lang' => 'en','name'=> 'English (Australia)'),
                                   'en_GB'  => array('lang' => 'en','name'=> 'English (UK)'),
                                   'eo'     => array('lang' => 'eo','name'=> 'Esperanto'),
                                   'es_VE'  => array('lang' => 'es','name'=> 'Español de Venezuela'),
                                   'es_ES'  => array('lang' => 'es','name'=> 'Español'),
                                   'es_GT'  => array('lang' => 'es','name'=> 'Español de Guatemala'),
                                   'es_CO'  => array('lang' => 'es','name'=> 'Español de Colombia'),
                                   'es_MX'  => array('lang' => 'es','name'=> 'Español de México'),
                                   'es_CR'  => array('lang' => 'es','name'=> 'Español de Costa Rica'),
                                   'es_PE'  => array('lang' => 'es','name'=> 'Español de Perú'),
                                   'es_CL'  => array('lang' => 'es','name'=> 'Español de Chile'),
                                   'es_AR'  => array('lang' => 'es','name'=> 'Español de Argentina'),
                                   'et'     => array('lang' => 'et','name'=> 'Eesti'),
                                   'eu'     => array('lang' => 'eu','name'=> 'Euskara'),
                                   'fa_IR'  => array('lang' => 'fa','name'=> 'فارسی'),
                                   'fi'     => array('lang' => 'fi','name'=> 'Suomi'),
                                   'fr_FR'  => array('lang' => 'fr','name'=> 'Français'),
                                   'fr_CA'  => array('lang' => 'fr','name'=> 'Français du Canada'),
                                   'fr_BE'  => array('lang' => 'fr','name'=> 'Français de Belgique'),
                                   'fur'    => array('lang' => 'fur','name'=> 'Friulian'),
                                   'gd'     => array('lang' => 'gd','name'=> 'Gàidhlig'),
                                   'gl_ES'  => array('lang' => 'gl','name'=> 'Galego'),
                                   'gu'     => array('lang' => 'gu','name'=> 'ગુજરાતી'),
                                   'haz'    => array('lang' => 'haz','name'=> 'هزاره گی'),
                                   'he_IL'  => array('lang' => 'he','name'=> 'עִבְרִית'),
                                   'hi_IN'  => array('lang' => 'hi','name'=> 'हिन्दी'),
                                   'hr'     => array('lang' => 'hr','name'=> 'Hrvatski'),
                                   'hu_HU'  => array('lang' => 'hu','name'=> 'Magyar'),
                                   'hy'     => array('lang' => 'hy','name'=> 'Հայերեն'),
                                   'id_ID'  => array('lang' => 'id','name'=> 'Bahasa Indonesia'),
                                   'is_IS'  => array('lang' => 'is','name'=> 'Íslenska'),
                                   'ja'     => array('lang' => 'ja','name'=> '日本語'),
                                   'jv_ID'  => array('lang' => 'jv','name'=> 'Basa Jawa'),
                                   'ka_GE'  => array('lang' => 'ka','name'=> 'ქართული'),
                                   'kab'    => array('lang' => 'kab','name'=> 'Taqbaylit'),
                                   'kk'     => array('lang' => 'kk','name'=> 'Қазақ тілі'),
                                   'km'     => array('lang' => 'km','name'=> 'ភាសាខ្មែរ'),
                                   'ko_KR'  => array('lang' => 'ko','name'=> '한국어'),
                                   'ckb'    => array('lang' => 'ku','name'=> 'كوردی'),
                                   'lo'     => array('lang' => 'lo','name'=> 'ພາສາລາວ'),
                                   'lt_LT'  => array('lang' => 'lt','name'=> 'Lietuvių kalba'),
                                   'lv'     => array('lang' => 'lv','name'=> 'Latviešu valoda'),
                                   'mk_MK'  => array('lang' => 'mk','name'=> 'Македонски јазик'),
                                   'ml_IN'  => array('lang' => 'ml','name'=> 'മലയാളം'),
                                   'mn'     => array('lang' => 'mn','name'=> 'Монгол'),
                                   'mr'     => array('lang' => 'mr','name'=> 'मराठी'),
                                   'ms_MY'  => array('lang' => 'ms','name'=> 'Bahasa Melayu'),
                                   'my_MM'  => array('lang' => 'my','name'=> 'ဗမာစာ'),
                                   'nb_NO'  => array('lang' => 'nb','name'=> 'Norsk bokmål'),
                                   'ne_NP'  => array('lang' => 'ne','name'=> 'नेपाली'),
                                   'nl_BE'  => array('lang' => 'nl','name'=> 'Nederlands (België)'),
                                   'nl_NL'  => array('lang' => 'nl','name'=> 'Nederlands'),
                                   'nl_NL_formal'  => array('lang' => 'nl','name'=> 'Nederlands (Formeel)'),
                                   'nn_NO'  => array('lang' => 'nn','name'=> 'Norsk nynorsk'),
                                   'oci'    => array('lang' => 'oc','name'=> 'Occitan'),
                                   'pa_IN'  => array('lang' => 'pa','name'=> 'ਪੰਜਾਬੀ'),
                                   'pl_PL'  => array('lang' => 'pl','name'=> 'Polski'),
                                   'ps'     => array('lang' => 'ps','name'=> 'پښتو'),
                                   'pt_PT'  => array('lang' => 'pt','name'=> 'Português'),
                                   'pt_BR'  => array('lang' => 'pt','name'=> 'Português do Brasil'),
                                   'pt_PT_ao90'  => array('lang' => 'pt','name'=> 'Português (AO90)'),
                                   'rhg'    => array('lang' => 'rhg','name'=> 'Ruáinga'),
                                   'ro_RO'  => array('lang' => 'ro','name'=> 'Română'),
                                   'ru_RU'  => array('lang' => 'ru','name'=> 'Русский'),
                                   'sah'    => array('lang' => 'sah','name'=> 'Сахалыы'),
                                   'si_LK'  => array('lang' => 'si','name'=> 'සිංහල'),
                                   'sk_SK'  => array('lang' => 'sk','name'=> 'Slovenčina'),
                                   'sl_SI'  => array('lang' => 'sl','name'=> 'Slovenščina'),
                                   'sr_RS'  => array('lang' => 'sr','name'=> 'Српски језик'),
                                   'sv_SE'  => array('lang' => 'sv','name'=> 'Svenska'),
                                   'szl'    => array('lang' => 'szl','name'=> 'Ślōnskŏ gŏdka'),
                                   'ta_IN'  => array('lang' => 'ta','name'=> 'தமிழ்'),
                                   'te'     => array('lang' => 'te','name'=> 'తెలుగు'),
                                   'th'     => array('lang' => 'th','name'=> 'ไทย'),
                                   'tl'     => array('lang' => 'tl','name'=> 'Tagalog'),
                                   'tr_TR'  => array('lang' => 'tr','name'=> 'Türkçe'),
                                   'tt_RU'  => array('lang' => 'tt','name'=> 'Татар теле'),
                                   'tah'    => array('lang' => 'ty','name'=> 'Reo Tahiti'),
                                   'ug_CN'  => array('lang' => 'ug','name'=> 'ئۇيغۇرچە'),
                                   'uk'     => array('lang' => 'uk','name'=> 'Українська'),
                                   'ur'     => array('lang' => 'ur','name'=> 'اردو'),
                                   'uz_UZ'  => array('lang' => 'uz','name'=> 'O‘zbekcha'),
                                   'vi'     => array('lang' => 'vi','name'=> 'Tiếng Việt'),
                                   'zh_HK'  => array('lang' => 'zh','name'=> '香港中文版'),
                                   'zh_TW'  => array('lang' => 'zh','name'=> '繁體中文'),
                                   'zh_CN'  => array('lang' => 'zh','name'=> '简体中文')
                                );
                                $showlanguages = get_option('mobiconnector_settings-show-languages');                               
                                if(empty($showlanguages)){
                                    $showlanguages = 'en';
                                }                     
                            ?>  
                            <input type="hidden" name="mobiconnector_settings-application-languages" id="mobiconnector_settings-application-languages"/>     
                            <input type="hidden" name="mobiconnector-show-application-languages" id="mobiconnector-show-application-languages"/>                         
                            <select id="mobiconnector_application_languages">                                
                                <?php 
                                    foreach($argslang as $key => $value){
                                ?>
                                <option <?php if($showlanguages == $key){echo 'selected="selected"';} ?> value="<?php echo $key; ?>" lang="<?php echo $value['lang']; ?>"><?php echo $value['name'] ?></option>
                                <?php       
                                    }
                                ?>
                            </select>
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span style="margin-left:1px;">?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo esc_html(__('Default language for API which user can see first it when they open mobile application.','mobiconnector')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?php 
                        $select = get_option('mobiconnector_settings-display-mode');
                        if(empty($select)){
                            $select = 'ltr';
                        }
                    ?>
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Default Language Mode','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <div style="float:left;">
                                <input type="radio" <?php if($select == 'ltr'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio first-child" id="mobiconnector_settings_display_mode_ltr" name="mobiconnector_settings-display-mode" value="ltr"   />
                                <label for="mobiconnector_settings_display_mode_ltr"><?php esc_html_e("Left to Right","mobiconnector"); ?></label>
                                <input type="radio" <?php if($select == 'rtl'){ echo 'checked="checked"';}else{ echo ""; } ?> class="mobi-radio" id="mobiconnector_settings_display_mode_rtl" name="mobiconnector_settings-display-mode" value="rtl"   /> 
                                <label for="mobiconnector_settings_display_mode_rtl"><?php esc_html_e("Right to Left","mobiconnector"); ?></label>
                            </div>
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:0px;">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:32px;">
                                    <?php echo esc_html(__('Default language mode (LTR or RTL) when they open mobile application.','mobiconnector')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(is_plugin_active('modernshop/modernshop.php') || is_plugin_active('cellstore/cellstore.php') || is_plugin_active('olike/olike.php') || is_plugin_active('azstore/azstore.php') || bamobile_is_extension_active('modernshop/modernshop.php') || bamobile_is_extension_active('cellstore/cellstore.php') || bamobile_is_extension_active('olike/olike.php') || bamobile_is_extension_active('azstore/azstore.php')){ ?>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Banner Android Admob','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="ca-app-pub-xxxxxxxxxxxxxxxxxxxxxxxxxx" id="mobiconnector_settings_banner_aa" name="mobiconnector_settings-banner-aa" value="<?php echo get_option('mobiconnector_settings-banner-aa'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Only use this setting if your mobile application included Admob plugin, %s','mobiconnector'),'<a target="_blank" href="https://taydoapp.com/knowledge-base/how-to-setting-admod-for-wordpress-news-app/">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Interstitial Android Admob','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="ca-app-pub-xxxxxxxxxxxxxxxxxxxxxxxxxx"  id="mobiconnector_settings_interstitial_aa" name="mobiconnector_settings-interstitial-aa" value="<?php echo get_option('mobiconnector_settings-interstitial-aa'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Only use this setting if your mobile application included Admob plugin, %s','mobiconnector'),'<a target="_blank" href="https://taydoapp.com/knowledge-base/how-to-setting-admod-for-wordpress-news-app/">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Banner Ios Admob','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="ca-app-pub-xxxxxxxxxxxxxxxxxxxxxxxxxx"  id="mobiconnector_settings_banner_ia" name="mobiconnector_settings-banner-ia" value="<?php echo get_option('mobiconnector_settings-banner-ia'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Only use this setting if your mobile application included Admob plugin, %s','mobiconnector'),'<a target="_blank" href="https://taydoapp.com/knowledge-base/how-to-setting-admod-for-wordpress-news-app/">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Interstitial Ios Admob','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="ca-app-pub-xxxxxxxxxxxxxxxxxxxxxxxxxx"  id="mobiconnector_settings_interstitial_ia" name="mobiconnector_settings-interstitial-ia" value="<?php echo get_option('mobiconnector_settings-interstitial-ia'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('Only use this setting if your mobile application included Admob plugin, %s','mobiconnector'),'<a target="_blank" href="https://taydoapp.com/knowledge-base/how-to-setting-admod-for-wordpress-news-app/">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label for="mobiconnector_settings_maintainmode"><?php echo esc_html(__('Enable Maintenance Mode','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="checkbox" style="float:left;margin-top:1px;" <?php if($maintain == 1){echo 'checked="checked"';}?> class="mobi-checkbox" id="mobiconnector_settings_maintainmode" name="mobiconnector_settings-maintainmode" value="1"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol" style="margin-top:-1px;">
                                    <span style="margin-left:1px;">?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content" style="top:31px;">
                                    <?php echo esc_html(__("When this mode is enabled, an User open the application on their mobile, they only see Maintenance Page and can not use any functions from your application. But an administrator's account can login to use mobile application as normally.",'mobiconnector')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Google API Key','mobiconnector')); ?></label></div>
                        <div class="mobi-content">
                            <input type="text" class="mobi-input" placeholder="xxxxxxxxxxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxx" id="mobiconnector_settings-google-api-key" name="mobiconnector_settings-google-api-key" value="<?php echo get_option('mobiconnector_settings-google-api-key'); ?>"   /> 
                            <div class="mobiconnector-support-input">
                                <div class="mobiconnector-tooltip-symbol">
                                    <span>?</span>
                                </div>
                                <div class="mobiconnector-tooltip-content">
                                    <?php echo sprintf(__('To use the Google Maps API, you must register your app project on the Google Cloud Platform Console and get a Google API key which you can add to your app. %s','mobiconnector'),'<a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key#step-1-get-an-api-key-from-the-google-cloud-platform-console">Read more</a>'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-element">
                        <div class="mobi-label"><label><?php echo esc_html(__('Social Login','mobiconnector')); ?></label></div>
                        <div class="mobi-content mobi-content-fields">
                            <div class="mobi-fields-group">
                                <div class="mobi-fields-element">
                                    <input type="checkbox" style="float:left;margin-top:1px;" <?php if($socialfacebook === true){echo 'checked="checked"';}?> class="mobi-checkbox" id="mobiconnector_settings_social_facebook" name="mobiconnector_settings-socials[]" value="facebook"   /> 
                                    <label for="mobiconnector_settings_social_facebook"><?php esc_html_e(__('Enable Facebook','mobiconnector')); ?></label>
                                </div>
                                <div class="mobiconnector-support-input mobiconnector-support-fields">
                                    <div class="mobiconnector-tooltip-symbol" style="margin-top:-1px;">
                                        <span>?</span>
                                    </div>
                                    <div class="mobiconnector-tooltip-content" style="top:31px;">
                                        <?php echo esc_html(__("Login by Facebook will be enabled/disabled in Login page from Mobile Application",'mobiconnector')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mobi-fields-group">
                                <div class="mobi-fields-element">
                                    <input type="checkbox" style="float:left;margin-top:1px;" <?php if($socialgoogle == true){echo 'checked="checked"';}?> class="mobi-checkbox" id="mobiconnector_settings_social_google" name="mobiconnector_settings-socials[]" value="google"   /> 
                                    <label for="mobiconnector_settings_social_google"><?php esc_html_e(__('Enable Google +','mobiconnector')); ?></label>
                                </div>
                                <div class="mobiconnector-support-input mobiconnector-support-fields">
                                    <div class="mobiconnector-tooltip-symbol" style="margin-top:-1px;">
                                        <span>?</span>
                                    </div>
                                    <div class="mobiconnector-tooltip-content" style="top:31px;">
                                        <?php echo esc_html(__("Login by Google Plus will be enabled/disabled in Login page from Mobile Application",'mobiconnector')); ?>
                                    </div>
                                </div>
                            </div>
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