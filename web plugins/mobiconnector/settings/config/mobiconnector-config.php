<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
require_once(MOBICONNECTOR_ABSPATH . 'settings/views/mobiconnector-tab-settings.php');
$home_path = ABSPATH;
$home_path = trailingslashit($home_path);
$home_url = get_bloginfo('url');
$keyPlugin = md5("MobileConnector");
$key = $keyPlugin . '-' . md5($home_path) . '-' . md5($home_url);
$data = "//* BEGIN MobileConnector *//\n";
$data .= 'define("JWT_AUTH_SECRET_KEY","' . $key . '");' . "\n";
$data .= 'define("JWT_AUTH_CORS_ENABLE",true);' . "\n";
$data .= "//* END MobileConnector *//\n";
?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('Config', 'mobiconnector')); ?></h1>
	<?php bamobile_mobiconnector_print_notices(); ?>
    <h4><?php echo esc_html_e('Please manual copy source and write it to wp-config.php file if The plugins can not write setting to wp-config.php file'); ?></h4>
	<textarea style="line-height: 1.4; font-size: 14px; transition: 50ms border-color ease-in-out; color: #32373c; border-radius:2px; box-sizing: border-box; width:99%; border:solid 1px #ddd; background:#eee; box-shadow: inset 0 1px 2px rgba(0,0,0,.07); height:150px; padding:10px; overflow-y:auto"><?php echo $data; ?></textarea>
</div>