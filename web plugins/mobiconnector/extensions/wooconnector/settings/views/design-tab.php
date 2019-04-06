<?php
/**
 * Tab Theme Application
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$action = isset( $_REQUEST[ 'action' ] ) ? $_REQUEST[ 'action' ] : bamobile_get_first_menu_in_design();
$plugins = bamobile_get_menu_design();
$last = end($plugins);
?>
<?php if($action !== false){ ?>
<ul class="subsubsub list_sub_tab_wooconnector">
    <?php 
        foreach($plugins as $plugin){
		    if(is_plugin_active($plugin.'/'.$plugin.'.php') || bamobile_is_extension_active($plugin.'/'.$plugin.'.php')){ ?>
    <li><a href="?page=wooconnector&wootab=design&action=<?php esc_html_e($plugin); ?>" class="<?php if($action == $plugin) {esc_html_e('current'); }{  echo ''; } ?>" ><?php esc_html_e(ucfirst($plugin)); ?></a> <?php if($plugin !== $last){esc_html_e("|");} ?> </li>
    <?php 
            }
        } 
    ?>
</ul>
<?php } ?>