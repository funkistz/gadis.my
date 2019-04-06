<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php');
$mobihtaccess = get_option('mobiconnector_settings-htaccess-data');
?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('Htaccess','mobiconnector')); ?></h1>
	<?php bamobile_mobiconnector_print_notices(); ?>
    <h4><?php echo esc_html_e('Please manual copy source and write it to .htaccess file if The plugins can not write setting to .htaccess file'); ?></h4>
	<textarea style="line-height: 1.4; font-size: 14px; transition: 50ms border-color ease-in-out; color: #32373c; border-radius:2px; box-sizing: border-box; width:99%; border:solid 1px #ddd; background:#eee; box-shadow: inset 0 1px 2px rgba(0,0,0,.07); height:150px; padding:10px; overflow-y:auto"><?php echo $mobihtaccess; ?></textarea>
</div>