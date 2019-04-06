<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(MOBICONNECTOR_ABSPATH."xml/mobiconnector-static.php");
$xmls = bamobile_mobiconnector_get_static();
$core = get_option('mobiconnector_settings-text-core');	
$core = unserialize($core);
?>
<?php require_once(MOBICONNECTOR_ABSPATH.'settings/views/mobiconnector-tab-settings.php'); ?>
<div class="wrap mobiconnector-settings">
	<h1><?php echo esc_html(__('Settings Text','mobiconnector')); ?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<form method="POST" class="mobiconnector-setting-form" action="?page=mobiconnector-settings&mtab=textapp" id="settings-form">
        <input type="hidden" name="mtask" value="savesettingtext"/>	
		<div id="mobiconnector-settings-body">
			<div id="mobiconnector-body" >
				<div id="mobiconnector-body-content">
					<table id="table-mobiconnector">
					<?php												
							foreach($xmls as $xm){	
								$xml = (object)$xm;					
								$oldname = $xml->name;
								$name = str_replace("-", "_", $oldname);								
								if(!empty($core) && $xml->type == 'checkbox'){	
									$valuecheckbox = $core["$name"];	
									$valuemodern = $xml->defaultValue;
									if($valuecheckbox == $valuemodern){
										$checkedcheckbox = 'checked="checked"';
									}else{
										$checkedcheckbox = '';
									}
								}
								elseif(!empty($core) && $xml->type == 'radio'){	
									$valueradio = $core["$name"];
									$valuemodern = $xml->defaultValue;
									if($valueradio == $valuemodern){
										$checkedradio = 'checked="checked"';
									}else{
										$checkedradio = '';
									}
								}
								elseif(!empty($core)){			
									$valuemodern = $core["$name"];
								}else{
									$valuemodern = $xml->defaultValue;
								}
																								
								if($xml->type == 'textarea'){							
							?>	
								<tr>							
									<td class="mobi-label"><label  for="<?php echo esc_html($xml->id); ?>"><?php echo esc_html($xml->label); ?></label></td>
									<td class="mobi-content"><textarea  class="<?php echo esc_html($xml->className); ?>" id="<?php echo esc_html($xml->id);?>" name="<?php echo esc_html($xml->name); ?>" placeholder="<?php echo esc_html($xml->placeholder); ?>"><?php echo esc_html($valuemodern); ?></textarea></td> 
								</tr>	
							<?php
								}
								elseif($xml->type == 'editor'){
									$id = str_replace("-", "_", esc_html($xml->id));
							?>
								<tr>							
									<td class="mobi-label"><label  for="<?php echo esc_html($xml->id); ?>"><?php echo esc_html($xml->label); ?></label></td>
									<td class="mobi-content">
										<?php wp_editor(stripslashes(html_entity_decode($valuemodern)),$id,array('textarea_name'=>$xml->name))?>
									</td> 
								</tr>
							<?php							
								}
								else{	
							?>										
								<tr>							
									<td class="mobi-label"><label  for="<?php echo esc_html($xml->id);?>"><?php echo esc_html($xml->label);?></label></td>
									<td class="mobi-content"><input type="<?php echo esc_html($xml->type);?>" class="<?php echo esc_html($xml->className); ?>" id="<?php echo esc_html($xml->id); ?>" name="<?php echo esc_html($xml->name); ?>" placeholder="<?php echo esc_html($xml->placeholder); ?>" value="<?php echo esc_html($valuemodern); ?>" <?php if($xml->type == 'checkbox'){ echo esc_html($checkedcheckbox); } if($xml->type == 'radio'){echo esc_html($checkedradio);}?> /></td> 
								</tr>
							<?php
								}
												
							}
						?>
					</table>
				</div>
				<div id="mobi-button">
					<input  type="submit" name="publish2" class="button button-primary button-large" value="<?php echo esc_html(__('Save'));?>">
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</form>
</div>