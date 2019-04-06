<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$cous = get_option('wooconnector_settings-countries');
if(!empty($cous)){
	if(is_string($cous)){
		$cous = unserialize($cous);
	}
	foreach($cous as $cou){
		$valuescou[] = $cou['value'];
		$namescou[] = $cou['name'];
	}
	$valueout = implode(',',$valuescou);
	if(empty($valueout)){
		$valueout = "";
	}else{
		$valueout = $valueout.',';
	}	
	$nameout = implode(',',$namescou);
	if(empty($nameout)){
		$nameout = "";
	}else{
		$nameout = $nameout.',';
	}
}
$namestateout = "";
$allstas = get_option('wooconnector_settings-states');
if(!empty($allstas)){
	if(is_string($allstas)){
		$allstas = unserialize($allstas);
	}
	foreach($allstas as $allsta => $values){
		foreach($values as $value => $val){
			$namesta[] = $val['name'];
		}			
	}
	$namestateout = implode(',',$namesta);
	if(!empty($allstas)){
		foreach($allstas as $allsta => $values){
			foreach($values as $value => $val){
				$namesta[] = $val['name'];
			}			
		}
		$namestateout = implode(',',$namesta);
		if(!empty($namestateout)){
			$namestateout = $namestateout.',';
		}
	}
	$valuestas = get_option('wooconnector_settings-first-states');
	$oldstates = trim($valuestas,',');		
	$valuestates = explode(',',$oldstates);
}

$searchactives = get_option('wooconnector_settings-search');
$searchactives = unserialize($searchactives);
$checkcate = '';
$checktag = '';
$checkdes = '';
if(!empty($searchactives)){
	foreach($searchactives as $search => $value){
		if($search == 'category' && $value == 1){
			$checkcate = 'checked="checked"';
		}elseif($search == 'category' && $value != 1) {
			$checkcate = '';
		}
		if($search == 'tag' && $value == 1){
			$checktag = 'checked="checked"';
		}elseif($search == 'tag' && $value != 1){
			$checktag = '';
		}
		if($search == 'description' && $value == 1){
			$checkdes = 'checked="checked"';
		}elseif($search == 'description' && $value != 1){
			$checkdes = '';
		}
	}
}
$checkprice = get_option('wooconnector_settings-change-price');
?>
<?php require_once(WOOCONNECTOR_ABSPATH.'settings/onesignal/tab.php'); ?>
<div class="wrap wooconnector-settings">
	<h1><?php echo __('Settings','wooconnector')?></h1>
	<?php
		bamobile_mobiconnector_print_notices();
	?>
	<form method="POST" class="wooconnector-setting-form" action="?page=wooconnector" id="settings-form">
		<input type="hidden" name="wootask" value="savesetting"/>		
		<div id="woo-settings-body">
			<div id="woo-body" >
				<div id="woo-body-content">					
					<div class="form-group">
						<div class="form-element">							
							<div class="woo-label"><label  for="wooconnector_settings_mail"><?php echo __('Email received in Contact page','wooconnector');?></label></div>
							<div class="woo-content">
								<input type="text" class="woo-input" placeholder="someone@domain.com" id="wooconnector_settings_mail" name="wooconnector_settings-mail" value="<?php echo get_option('wooconnector_settings-mail'); ?>" />
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo __('Email receive a message when someone submit a message in Contact page on mobile application. This option only work if your mobile application have a Form in Contact page','mobiconnector'); ?>
									</div>
								</div>
							</div> 
						</div>
					</div>
					<div class="form-group" style="display:none">
						<div class="form-element">							
							<div class="woo-label"><label  for="wooconnector_settings_custom_attribute"><?php echo __('Include Custom Attributes to Filter','wooconnector');?></label></div>
							<div class="woo-content"><input <?php if(get_option('wooconnector_settings-custom-attribute') == 1){echo 'checked="checked"';}else{echo '';}?> style="margin-top:8px;" type="checkbox" id="wooconnector_settings_custom_attribute" name="wooconnector_settings-custom-attribute" value="1"   /></div> 
						</div>
					</div>
					<div class="form-group">
						<div class="form-element">		
							<div class="woo-label"><label  for="wooconnector_settings_countries"><?php echo __('Billing/Shipping Countries','wooconnector');?></label></div>
							<div class="woo-content wooconnector-dropdown">
								<input type="text" readonly id="wooconnector_settings_countries" class="list-segment multi-dropdown-setting" value="<?php if(!empty($nameout)) { echo $nameout; }else{ echo '';} ?>" autocomplete="off" />
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo __('List Countries display in Billing/Shipping Form when checkout on mobile application','mobiconnector'); ?>
									</div>
								</div>
								<input type="hidden" id="wooconnector_settings_countries_symbol" class="symbol-multi-dropdown-setting" name="wooconnector_settings_countries" value="<?php if(!empty($valueout)){ echo $valueout; }else{ echo '';} ?>" autocomplete="off" />
								<div class="mutliSelect wooconnector-dropdown-settings">
									<ul class="wooconnector-ul-dropdown">
										<li class="search-li">
											<input type="text" id="search" name="searchvalue" class="wooconnector-search-value" placeholder="Input country name" /></li>
										</li>
										<hr>
										<li class="wooconnector-all-li">
											<input type="checkbox" value="all" id="check-all-countries" /><label for="check-all-countries">All</label>
										</li>
										<?php
											$ct = new WC_Countries();
											$coutries = $ct->get_countries();
											$listcountry = array();
											$woocommerce_allowed_countries = get_option('woocommerce_allowed_countries');
											if($woocommerce_allowed_countries == 'specific'){
												$woocommerce_specific_allowed_countries = get_option('woocommerce_specific_allowed_countries');
												if(!empty($woocommerce_specific_allowed_countries)){
													foreach($coutries as $country => $value){
														foreach($woocommerce_specific_allowed_countries as $specific){
															if($country == $specific){
																$listcountry[$country] = $value;
															}
														}
													}
												}
											}elseif($woocommerce_allowed_countries == 'all_except'){
												$woocommerce_all_except_countries = get_option('woocommerce_all_except_countries');
												if(!empty($woocommerce_all_except_countries)){
													foreach($coutries as $country => $value){
														foreach($woocommerce_all_except_countries as $allex){
															if($country != $allex){
																$listcountry[$country] = $value;
															}
														}
													}
												}
											}else{
												$listcountry = $coutries;
											}
											if(!empty($valuescou)){		
												foreach($listcountry as $country => $value){													
										?>
										<li class="wooconnector-li-dropdown">
											<input type="checkbox" class="wooconnector-value-dropdown" <?php foreach($valuescou as $val){ if($country == $val){ echo 'checked="checked"'; }else{ echo ''; } }?> value="<?php echo $country; ?>" id="<?php echo $country; ?>" /><label class="wooconnector-label-dropdown" for="<?php echo $country; ?>"><?php echo $value; ?></label>
										</li>
										<?php													
												}
											}
											else{
												foreach($listcountry as $country => $value){
										?>
										<li class="wooconnector-li-dropdown">
											<input type="checkbox" class="wooconnector-value-dropdown" value="<?php echo $country; ?>" id="<?php echo $country; ?>" /><label for="<?php echo $country; ?>"><?php echo $value; ?></label>
										</li>			
										<?php
												}
											}
										?>		
									</ul>
								</div>													
							</div>
						</div>						
					</div>
					<div class="form-group">
						<div class="form-element">	
							<div class="woo-label"><label  for="wooconnector_settings_states"><?php echo __('Billing/Shipping States','wooconnector');?></label></div>
							<div class="woo-content wooconnector-dropdown">
								<input type="text" readonly id="wooconnector_settings_states" class="list-segment multi-dropdown-setting" value="<?php if(!empty($namestateout)){ echo $namestateout; } else { echo ''; } ?>" autocomplete="off" />
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo __('List States display in Billing/Shipping Form when checkout on mobile application','mobiconnector'); ?>
									</div>
								</div>
								<input type="hidden" id="wooconnector_settings_states_symbol" class="symbol-multi-dropdown-setting" name="wooconnector_settings_states" value="<?php if(!empty($valuestas)){ echo $valuestas; } else { echo ''; } ?>" autocomplete="off" />
								<div class="wooconnector-states-multiselect wooconnector-dropdown-settings">
									<ul class="wooconnector-ul-dropdown">
										<li class="search-li">
											<input type="text" id="search" name="searchvalue" class="wooconnector-search-value-states" placeholder="Input country name" /></li>
										</li>
										<hr>
										<li class="wooconnector-all-li">
											<input type="checkbox" value="all" id="check-all-states" /><label for="check-all-states">All</label>
										</li>
										<?php
											$ct = new WC_Countries();
											$states = $ct->get_states();
											$coutries = $ct->get_countries();		
											foreach($states as $state => $values){
												if(!empty($values)){
													$list = array();
													foreach($coutries as $country => $val){
														if($state == $country){
															$list = array(
																'symbol' => $country,
																'country' => $val,
																'states' => $values
															);
														}
													}
													$statesout[] = $list;
												}else{
													continue;
												}
											}	
											if(!empty($valuestates)){
												if(!empty($statesout)){		
													foreach($statesout as $state => $value){
											?>
											<li class="wooconnector-li-dropdown">
												<label class="wooconnector-states-label"><?php echo $value['country']; ?></label>
												<div class="wooconnector-states-content">												
													<ul>
													<?php
														foreach($value['states'] as $valuesym => $valuename){
													?>
														<li>
															<input type="checkbox" <?php foreach($valuestates as $valuestate){if($valuestate == ($value['symbol'].'-'.$valuesym)){ echo 'checked="checked"'; }else{ echo ''; }  } ?> class="wooconnector-value-dropdown" value="<?php echo $value['symbol'].'-'.$valuesym; ?>" id="<?php echo $value['symbol'].'-'.$valuesym; ?>" data-statename="<?php echo $valuename; ?>" /><label class="wooconnector-label-dropdown" for="<?php echo $value['symbol'].'-'.$valuesym; ?>"><?php echo $valuename; ?></label>
														</li>
													<?php } ?>
													</ul>
												</div>
											</li>			
											<?php
													}
												}else{
													?>
													<li class="wooconnector-li-dropdown">
														<label class="wooconnector-states-label"></label>
														<div class="wooconnector-states-content">												
															<ul>
															</ul>
														</div>
													</li>
													<?php
												}
											}else{
												if(!empty($statesout)){
													foreach($statesout as $state => $value){
														?>
														<li class="wooconnector-li-dropdown">
															<label class="wooconnector-states-label"><?php echo $value['country']; ?></label>
															<div class="wooconnector-states-content">												
																<ul>
																<?php
																	foreach($value['states'] as $valuesym => $valuename){
																?>
																	<li>
																		<input type="checkbox" class="wooconnector-value-dropdown" value="<?php echo $value['symbol'].'-'.$valuesym; ?>" id="<?php echo $value['symbol'].'-'.$valuesym; ?>" data-statename="<?php echo $valuename; ?>" /><label class="wooconnector-label-dropdown" for="<?php echo $value['symbol'].'-'.$valuesym; ?>"><?php echo $valuename; ?></label>
																	</li>
																<?php } ?>
																</ul>
															</div>
														</li>
														<?php
													}
												}else{
													?>
													<li class="wooconnector-li-dropdown">
														<label class="wooconnector-states-label"></label>
														<div class="wooconnector-states-content">												
															<ul>
															</ul>
														</div>
													</li>
													<?php
												}
											}
										?>		
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="form-element">		
							<div class="woo-label"><label><?php echo __('Search Keyword In','wooconnector');?></label></div>
							<div class="woo-content">
								<div class="wooconnector-checkbox">
									<input type="checkbox" <?php echo $checkcate; ?> class="wooconnector-checkbox-box" name="wooconnector_settings-search[category]" id="wooconnector_settings_search_category" value="1"/>
									<label for="wooconnector_settings_search_category"><?php echo __('Categories','wooconnector');?></label>
								</div>
								<div class="wooconnector-checkbox">
									<input type="checkbox" <?php echo $checktag; ?> class="wooconnector-checkbox-box" name="wooconnector_settings-search[tag]" id="wooconnector_settings_search_tag" value="1"/>
									<label for="wooconnector_settings_search_tag"><?php echo __('Product Tags','wooconnector');?></label>
								</div>
								<div class="wooconnector-checkbox">
									<input type="checkbox" <?php echo $checkdes; ?> class="wooconnector-checkbox-box" name="wooconnector_settings-search[description]" id="wooconnector_settings_search_des" value="1"/>
									<label for="wooconnector_settings_search_des"><?php echo __('Product Description','wooconnector');?></label>
								</div>
								<div class="support-input">
									<div class="symb-support-input">
										<span>?</span>
									</div>
									<div class="tooltip-support-input">
										<?php echo __('When an User enter a keyword in Search page of mobile application, the app can search keyword in Product Name. Also you can extend result with search keyword in Category Name, Product Tags, Product Description from this option','mobiconnector'); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group" style="display:none">
						<div class="form-element">		
							<div class="woo-label"><label><?php echo __('Change Position Price','wooconnector');?></label></div>
							<div class="woo-content">
								<div class="wooconnector-checkbox">
									<input type="checkbox" <?php if($checkprice == 1){echo 'checked="checked"';}else{echo '';} ?> class="wooconnector-checkbox-box" name="wooconnector_settings-change-price" id="wooconnector_settings_price" value="1"/>
									<label for="wooconnector_settings_price"><?php echo __('Change','wooconnector');?></label>
								</div>
							</div>
						</div>
					</div>		
				</div>		
				
				<div id="woo-button">
					<input  type="submit" name="savesetting" class="button button-primary button-large" value="<?php echo __('Save','wooconnector');?>">
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</form>
</div>