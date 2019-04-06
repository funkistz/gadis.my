<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(MODERN_ABSPATH."xml/modernshop-static.php");
$xmls = modernshop_get_static();
$core = get_option('modern_settings-core');	
$core = unserialize($core);
?>
<div class="wrap wooconnector-settings">
	<h1><?php echo __('Settings','woocommerce')?></h1>
	<?php bamobile_mobiconnector_print_notices(); ?>
	<form method="POST" class="wooconnector-setting-form" action="?page=modernshop-settings" id="settings-form">
		<input type="hidden" name="moderntask" value="savesetting"/>		
		<div id="modern-settings-body">
			<div id="modern-body" >
				<div id="modern-body-content">	
					<table id="table-modern" class="i18n-multilingual-display">
						<?php												
							foreach($xmls as $xm){	
								$xml = (object)$xm;									
								$oldname = $xml->name;
								$name = str_replace("-", "_", $oldname);								
								if(!empty($core) && $xml->type == 'checkbox'){
									if(isset($core["$name"])){	
										$valuecheckbox = $core["$name"];	
										$valuemodern = $xml->defaultValue;
										if($valuecheckbox == $valuemodern){
											$checkedcheckbox = 'checked="checked"';
										}else{
											$checkedcheckbox = '';
										}
									}else{
										$checkedcheckbox = '';
									}
								}
								elseif(!empty($core) && $xml->type == 'radio'){	
									if(isset($core["$name"])){	
										$valueradio = $core["$name"];
										$valuemodern = $xml->defaultValue;
										if($valueradio == $valuemodern){
											$checkedradio = 'checked="checked"';
										}else{
											$checkedradio = '';
										}
									}else{
										$checkedradio = '';
									}
								}
								elseif(!empty($core)){		
									if(isset($core["$name"])){		
										$valuemodern = $core["$name"];
									}else{
										$valuemodern = '';
									}
								}else{
									$valuemodern = $xml->defaultValue;
								}
																								
								if($xml->type == 'textarea'){							
							?>	
								<tr>							
									<td class="woo-label">
										<label  for="<?php echo $xml->id;?>"><?php echo $xml->label;?></label>
									</td>
									<td class="woo-content">
										<textarea  class="<?php if(isset($xml->notice) && !empty($xml->notice)){ echo "modern-isset-notice";} ?> <?php echo $xml->className?>" id="<?php echo $xml->id;?>" name="<?php echo $xml->name;?>" placeholder="<?php echo $xml->placeholder;?>"><?php echo $valuemodern; ?></textarea>
										<?php
											if(isset($xml->notice) && !empty($xml->notice)){											
										?>
										<div class="modern-support-input modern-support-textarea">
											<div class="modern-tooltip-symbol">
												<span>?</span>
											</div>
											<div class="modern-tooltip-content">
												<?php echo __($xml->notice,'modern'); ?>
											</div>
										</div>	
										<?php
											}
										?>	
									</td> 
								</tr>	
							<?php
								}
								elseif($xml->type == 'editor'){
									$id = str_replace("-", "_", $xml->id);
							?>
								<tr>							
									<td class="woo-label"><label  for="<?php echo $xml->id;?>"><?php echo $xml->label;?></label></td>
									<td class="woo-content">
										<div class="modern-border-editor <?php if(isset($xml->notice) && !empty($xml->notice)) { echo "modern-isset-notice";} ?>">
										<?php wp_editor(stripslashes(html_entity_decode($valuemodern)),$id,array('textarea_name'=>$xml->name))?>
										</div>
										<?php
											if(isset($xml->notice) && !empty($xml->notice)){											
										?>
										<div class="modern-support-input modern-support-editor">
											<div class="modern-tooltip-symbol">
												<span>?</span>
											</div>
											<div class="modern-tooltip-content">
												<?php echo __($xml->notice,'modern'); ?>
											</div>
										</div>	
										<?php
											}
										?>
									</td> 
								</tr>
							<?php							
								}
								else{	
							?>										
								<tr>							
									<td class="woo-label"><label  for="<?php echo $xml->id;?>"><?php echo $xml->label;?></label></td>
									<td class="woo-content">
										<input type="<?php echo $xml->type;?>" class="<?php if(isset($xml->notice) && !empty($xml->notice)){ echo "modern-isset-notice";} ?> <?php echo $xml->className?>" id="<?php echo $xml->id;?>" name="<?php echo $xml->name;?>" placeholder="<?php echo $xml->placeholder;?>" value="<?php echo $valuemodern; ?>" <?php if($xml->type == 'checkbox'){ echo $checkedcheckbox; } if($xml->type == 'radio'){echo $checkedradio;}?> />
										<?php
										if(isset($xml->notice) && !empty($xml->notice)){											
										?>
										<div class="modern-support-input modern-support-inputs">
											<div class="modern-tooltip-symbol">
												<span>?</span>
											</div>
											<div class="modern-tooltip-content">
												<?php echo __($xml->notice,'modern'); ?>
											</div>
										</div>	
										<?php
											}
										?>
									</td> 								
								</tr>
							<?php
								}
												
							}
						?>
					</table>
				</div>
				<div id="woo-button">
					<input  type="submit" name="publish2" class="button button-primary button-large" value="<?php echo __('Save');?>">
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</form>
</div>