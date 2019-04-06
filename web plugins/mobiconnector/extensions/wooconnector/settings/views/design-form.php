<?php

/**
 * Settings Theme Application
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
$design_data = get_option('wooconnector_settings-design');
if (!empty($design_data) && is_string($design_data)) {
    $design_data = unserialize($design_data);
} elseif (!empty($design_data) && is_array($design_data)) {
    $design_data = $design_data;
} else {
    $design_data = array();
}
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : bamobile_get_first_menu_in_design();
$current_tab = isset($_REQUEST['current_tab']) ? $_REQUEST['current_tab'] : 'homepage';
$data = bamobile_get_base_data_design();
$dataaction = !empty($data[$action]) ? $data[$action] : false;
$dataaction = (array)$dataaction;
$datas = !empty($dataaction[$current_tab]->blocks) ? $dataaction[$current_tab]->blocks : false;
$listtabs = array();
if (!empty($dataaction)) {
    foreach ($dataaction as $datatab => $valtab) {
        $listtabs[] = array(
            "keytab" => $datatab,
            "valtab" => $valtab->name
        );
    }
}
$check = array();
?>
<?php require_once(WOOCONNECTOR_ABSPATH . 'settings/onesignal/tab.php'); ?>
<div class="wrap wooconnector-settings">
	<h1><?php echo esc_html(__('Mobile Themes', 'wooconnector')); ?></h1>
    <?php bamobile_mobiconnector_print_notices(); ?>
    <?php require_once(WOOCONNECTOR_ABSPATH . 'settings/views/design-tab.php'); ?>
	<form method="POST" class="wooconnector-setting-form" action="?page=wooconnector" id="settings-form">
        <input type="hidden" name="wootask" value="saveapplicationdesign"/>		
        <input type="hidden" name="action" value="<?php esc_html_e($action); ?>" />
        <ul class="wooconnector-tab-page-design">
            <?php
            foreach ($listtabs as $tab) {
                $active = '';
                if ($tab["keytab"] == $current_tab) {
                    $active = 'wooconnector-tab-active';
                } else {
                    $active = '';
                }
                ?>
            <li class="<?php echo $active; ?>"><a href="<?php echo admin_url() . 'admin.php?page=wooconnector&wootab=design&action=' . $action . '&current_tab=' . $tab["keytab"]; ?>"><?php echo $tab["valtab"]; ?></a></li>
            <?php

        }
        ?>
        </ul>
        <?php 
        if (!empty($datas)) {
            foreach ($datas as $k => $v) {
                $fields = $v->fields;
                if (!in_array($k, $check)) {
                    ?>          
        <div class="wooconnector-fields">
            <h1><?php esc_html_e($v->name); ?></h1>
            <button class="wooconnector-hidden-tab-button button-up-arrow" data-type="up-arrow" style="background:url(<?php echo plugins_url('assets/images/up-arrow.svg', WOOCONNECTOR_PLUGIN_FILE); ?>);"></button>
            <table class="wooconnector-design">
                <?php 
                foreach ($fields as $key => $value) {
                    if ($value->type == 'images') {
                        ?>    
                <tr class="wooconnector-images-td">							
                    <td class="woo-label"></td> 
                    <td class="woo-content"> 
                        <img class="wooconnector-images-after-choose" id="<?php esc_html_e($key); ?>" src=""/>
                        <span class="wooconnector-warning"> <?php if (!empty($value->warring)) {
                                                                echo "* ";
                                                            } else {
                                                                echo "";
                                                            }
                                                            esc_html_e($value->warring); ?></span>
                    </td> 
                </tr>	
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content">
                        <?php bamobile_show_update_file_for_design_form($key, $k, $design_data, $value->notice) ?>
                    </td> 							
                </tr> 
                <?php 
            } elseif ($value->type == 'background') { ?> 
                <tr class="wooconnector-selected-form">							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_background_for_design_form($key, $k, $design_data, $value->type, $value->notice); ?>                    
                    </td> 							
                </tr>
                <?php 
            } elseif ($value->type == 'text') { ?> 
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_input_for_design_form($key, $k, $design_data, $value->type, $value->notice); ?>                    
                    </td> 							
                </tr> 
                <?php 
            } elseif ($value->type == 'number') { ?> 
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_input_for_design_form($key, $k, $design_data, $value->type, $value->notice); ?>                    
                    </td> 							
                </tr> 
                <?php 
            } elseif ($value->type == 'number-px') { ?> 
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_input_for_design_form($key, $k, $design_data, $value->type, $value->notice); ?>                    
                    </td> 							
                </tr>
                <?php 
            } elseif ($value->type == 'select-font-size') { ?>        
                <tr class="wooconnector-selected-form">							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_select_for_design_form($key, $k, $design_data, 'font-size', $value->notice); ?>
                    </td> 							
                </tr>
                <?php 
            } elseif ($value->type == 'select-font-weight') { ?>        
                <tr class="wooconnector-selected-form">							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_select_for_design_form($key, $k, $design_data, 'font-weight', $value->notice); ?>
                    </td> 							
                </tr>
                <?php 
            } elseif ($value->type == 'color') { ?>
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_color_for_design_form($key, $k, $design_data, $value->notice); ?>
                    </td> 							
                </tr>
                <?php 
            } elseif ($value->type == 'select-percent') { ?>
                <tr>							
                    <td class="woo-label"><label  for="<?php esc_html_e($key); ?>"><?php esc_html_e($value->name); ?></label></td>
                    <td class="woo-content"> 
                        <?php bamobile_show_percent_for_design_form($key, $k, $design_data, $value->notice); ?>
                    </td> 							
                </tr>
                <?php

            } else {
                continue;
            }
        }
        ?>  
            </table>
        </div>
            <?php 
            array_push($check, $k);
        } else {
            continue;
        }
    }
}
?>
        <input type="submit" class="button button-primary" id="wooconnector-save-design" value="<?php esc_html_e(__('Save', 'wooconnector')); ?>">
    </form>
</div>