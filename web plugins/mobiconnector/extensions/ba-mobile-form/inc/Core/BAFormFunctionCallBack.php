<?php
namespace DesignForm\Core;
use \DesignForm\Core\BAFormCoreBase;
use \DesignForm\Core\BAFormCoreSetting;
	class BAFormFunctionCallBack{
		public $countries;
		public $locale_fields = array(
			'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name'
			        );
		
		public function __construct(){
			$this->core_base = new BAFormCoreBase();
		}
		public function baform_action_construct(){
	        add_action('admin_post_save_ba-mobile-form', array(&$this, 'baform_on_save_changes'));
	        add_action('admin_post_nopriv_save_ba-mobile-form', array(&$this, 'baform_on_save_changes'));
	    }
		public function baform_form_design(){
			$this->baform_checkout_form_field_editor();
		}
		public function baform_add_field_func(){ ?>
			<div class="wrap woocommerce" >
				<h2>
					<?php
						if (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])){
							$fields = get_option('ba_design_form', array());
							$fields_ba = unserialize($fields);
							foreach ($fields_ba as $key => $fl) {
								if ($fl['name_id'] == $_REQUEST['edit']){
									$name_id = $fl['name_id'];
									$type_design = $fl['type'];
									$position_field = $fl['position_field'];
									$label = $fl['label'];
									$option_field = $fl['option_field'];
									$required_check = $fl['required_check'];
									$required_billing = $fl['required_billing'];
									$required_shipping = $fl['required_shipping'];
									$required_register = $fl['required_register'];
									$required_profile = $fl['required_profile'];
									if(isset( $fl['country_belong_to'])){
										$country_belong_to = $fl['country_belong_to'];
									} else {
										$country_belong_to = '';
									}
									if(isset( $fl['country_has_state'])){
										$country_has_state = $fl['country_has_state'];
									} else {
										$country_has_state = '';
									}
									
									$count_field = str_replace("billing_","",$name_id);
								}
							}
							
							
						 	echo __('Edit Field','ba-mobile-form');
						 	
						} else {
							$array_key = array();
						 	echo __('Add Field','ba-mobile-form');
						 	$fields_option_sql = get_option('ba_design_form', array());
							$fields_ba_option_sql = unserialize($fields_option_sql);
							foreach ($fields_ba_option_sql as $key => $fiela_name) {
								$count_field_isset = strpos($fiela_name['name_id'],'billing_field_');
								if ($count_field_isset !== false){
									if (is_numeric(substr($fiela_name['name_id'], 14 ))) {
										$array_key[] = substr($fiela_name['name_id'], 14 );
									}
								} else {
									
								}
							}
							if (!empty($array_key)){
								$count_field = "field_".(absint(max($array_key) + 1));
							} else {
								$count_field = "field_1";
							}
							
						}
						
					?>
				</h2>

				 <?php
				if(!isset($_SESSION)) 
			    { 
			        session_start(); 
				} 
				 if(isset($_SESSION["cheng_flash_message"])){
				 	echo ($_SESSION['cheng_flash_message'][0]) ;
				    unset($_SESSION["cheng_flash_message"]);
				    session_destroy();
				}

				 
				 do_action( 'ba_design_notices');
				 $arr_select['type_design'] = array(
			        'text'  => __( 'Text', 'ba-mobile-form' ),
			        'textarea'  => __( 'Text Area', 'ba-mobile-form' ),
			        'radio'  => __( 'Radio', 'ba-mobile-form' ),
			        'select'  => __( 'Select', 'ba-mobile-form' ),
			        'multiselect'  => __( 'Multi Select', 'ba-mobile-form' ),
			        'checkbox'  => __( 'Checkbox', 'ba-mobile-form' ),
			        //'multicheckbox'  => __( 'Multi Checkbox', 'ba-mobile-form' ),
			        'country'  => __( 'Country', 'ba-mobile-form' ),
			        'state'  => __( 'State', 'ba-mobile-form' ),
			        'ba-date'  => __( 'Date', 'ba-mobile-form' ),
			        'tel'  => __( 'Phone', 'ba-mobile-form' ),
			        'email'  => __( 'Email', 'ba-mobile-form' ),
			        'number'  => __( 'Number', 'ba-mobile-form' ),
			        'ip_address'  => __( 'IP Address', 'ba-mobile-form' ),
			        //'password'  => __( 'Password', 'ba-mobile-form' ),
			    );
				 $arr_select['position_field'] = array(
			        'form-row-first'  => __( 'Left', 'ba-mobile-form' ),
			        'form-row-last'  => __( 'Right', 'ba-mobile-form' ),
			        'form-row-wide'  => __( 'Full', 'ba-mobile-form' ),
			    );
				 $locale_fields = array(
			        'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name'
			        );
					$profile_filed = array(
			        	'billing_user_login',
			        	'billing_nicename',
			        	'billing_display_name',
			        	'billing_url',
			        	'billing_description',
			        	'billing_password',
			        );
			        $register_feild = array(
			        	'billing_email',
			        	'billing_nicename',
			        	'billing_last_name',
			        	'billing_first_name',
			        	'billing_password',
			        );
		 			$field_defautl = array_merge($locale_fields,$profile_filed);
					$required_fields = array('billing_last_name','billing_country', 'billing_address_1', 'billing_city','billing_email','billing_phone','billing_first_name','billing_nicename','billing_display_name');
				  ?>

				<form method="POST" action="" class="form-design">
					<?php 
					if (isset($_REQUEST['page']) && ($_REQUEST['page']==='add_field') && !empty($_REQUEST['edit'])){ ?>
						<input type="hidden" name="action" value="edit" />
					<?php } elseif (isset($_REQUEST['page']) && !empty($_REQUEST['page']) && !isset($_REQUEST['edit'])){ ?>
						<input type="hidden" name="action" value="add" />
					<?php } else {}?>
					<div class="ba-background-white">
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><?php echo __('Name (it is unique)','ba-mobile-form');?></th>
									<td>
										<input type="text"  name="name_id" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> disabled <?php } else {} ?>  value="<?php if(isset($name_id)){echo esc_attr( str_replace("billing_","",$name_id));} else { echo $count_field; } ?>" placeholder="<?php echo __('Name (it is unique)','ba-mobile-form');?>">
										<input type="hidden"  name="name_base"  value="<?php  echo $count_field;?>" placeholder="<?php echo __('Name (it is unique)','ba-mobile-form');?>">
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Type','ba-mobile-form');?></th>
									<td>
										<select name="type_design" id="type_design_add" class="type_design" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
										<?php  if (isset($type_design)){
											foreach ( $arr_select['type_design'] as $key => $type_name ) : ?>
								            <option value="<?php echo $key; ?>" <?php selected( $type_design, $key ) ?>><?php echo $type_name; ?></option>
								            <?php endforeach;		
								    	} else {
								   			foreach ( $arr_select['type_design'] as $key => $type_name ) : ?>
								            	<option value="<?php echo $key; ?>"><?php echo $type_name; ?></option>
								            <?php endforeach;
								        }?>
									</select>
									</td>
								</tr>
								<tr id="country_belong_to_tr">
									<th scope="row"><?php echo __('Country belong to','ba-mobile-form');?></th>
									<td>
										<select name="country_belong_to" id="country_belong_to" class="type_design">
											<option  value=""><?php echo __('Select id name', 'ba-mobile-form');?></option>
											<?php  
											if (isset($name_id) && !empty($name_id)){
												foreach ( $fields_ba as $key => $idx ) : 
													if (isset($country_belong_to) && ( $country_belong_to === $idx['name_id']) && ($idx['type'] === 'country')) { ?>
														<option selected value="<?php echo $idx["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$idx['name_id'])); ?></option>
													<?php  } else {
														if ($idx['type'] === 'country'){ ?>
															<option  value="<?php echo $idx["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$idx['name_id'])); ?></option>
													<?php 	}
													 ?>
													<?php }
												endforeach;		
											} else {
												foreach ( $fields_ba_option_sql as $key => $idx ) : 
													if ($idx['type'] === 'country'){?>
															<option  value="<?php echo $idx["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$idx['name_id'])); ?></option>
													<?php 	}
												endforeach;	
											}

									    	 ?>
										</select>
										</td>
								</tr>
								<tr id="country_has_state_tr">
									<th scope="row"><?php echo __('Country has state','ba-mobile-form');?></th>
									<td>
										<select name="country_has_state" id="country_has_state" class="type_design">
											<option  value=""><?php echo __('Select state', 'ba-mobile-form');?></option>
											<?php  
											if (isset($name_id) && !empty($name_id)){
												foreach ( $fields_ba as $key => $stex ) : 
													if (isset($country_has_state) && ( $country_has_state === $stex['name_id']) && ($stex['type'] === 'state')) { ?>
														<option selected value="<?php echo $stex["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$stex['name_id'])); ?></option>
													<?php  } else {
														if ($stex['type'] === 'state'){ ?>
															<option  value="<?php echo $stex["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$stex['name_id'])); ?></option>
													<?php 	}
													 ?>
													<?php }
												endforeach;		
											} else {
												foreach ( $fields_ba_option_sql as $key => $stex ) : 
													if ($stex['type'] === 'state'){?>
															<option  value="<?php echo $stex["name_id"]; ?>" ><?php echo esc_attr( str_replace("billing_","",$stex['name_id'])); ?></option>
													<?php 	}
												endforeach;	
											}
									    	 ?>
										</select>
										</td>
								</tr>
								<tr class="option-fields">
									<th scope="row"><?php echo __('Options','ba-mobile-form');?></th>
									<td>
										<?php if(isset($option_field) && !empty($option_field) && ($option_field !== "")){  
											$separator = ',';
											$tag_list  = '';
											$array_option = unserialize($option_field);
											if (is_array($array_option)){
												foreach($array_option as $option_f){ 
													$tag_list .= $option_f."".$separator;
												}
												$op = trim($tag_list, $separator);
											} else {
												$op="";
											}
										} else {
											$op="";
										}
									?>
										<input type="hidden" class="inputdata" name="inputdata" value="<?php echo esc_attr($op);?>">
										<p>
											<input style="width: 35.2%;" type="text" id="new-tag-post_tag" name="newtag[post_tag]" class="newtag ba-add-option form-input-tip ui-autocomplete-input" size="16"  value="" role="combobox">
										<input type="button" class="button optionadd" value="Add">
									</p>
									<ul class="tagchecklist" role="list">
										
										<?php if(isset($option_field) && !empty($option_field) && ($option_field !== "")){
											$array_option = unserialize($option_field);
												if (is_array($array_option)){
													foreach($array_option as $key=>$option_f){ 
													 	if (isset($option_f) && !empty($option_f)){ ?>
														 	<li>
																<button type="button" id="form-check-num-<?php echo esc_attr($key);?>" class="ntdelbutton">
																	<span class="remove-tag-icon" aria-hidden="true"></span>
																</button>&nbsp;<?php echo esc_attr($option_f);?>
															</li>
														<?php }	
													}
												} else {
													
												}
												 
											} else {
												
											}
										 ?>
										
									</ul>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Select position','ba-mobile-form');?></th>
									<td>
										<select name="position_field" id="position_field_add" class="type_design">
										<?php  if (isset($position_field) && !empty($position_field)){
											foreach ( $arr_select['position_field'] as $key => $posit ) : ?>
								            <option value="<?php echo $key; ?>" <?php selected( $position_field, $key ) ?>><?php echo $posit; ?></option>
								            <?php endforeach;		
								    	} else {
								   			foreach ( $arr_select['position_field'] as $key => $posit ) : ?>
								            	<option value="<?php echo $key; ?>"><?php echo $posit; ?></option>
								            <?php endforeach;
								        }?>
									</select>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Name','ba-mobile-form');?></th>
									<td>
										<input type="text" required  name="label_ngothoai" value="<?php if(isset($label)){echo esc_attr($label);} ?>" placeholder="Label">
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Required','ba-mobile-form');?></th>
									<td>
										<?php if (isset($required_check) && !empty($required_check)){
											?>
											<input <?php if($required_check == 1) {?> checked <?php }?> value="1" type="checkbox" name="required_check" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Check required','ba-mobile-form')?></span> -->
										<?php } else {?>
											<input type="checkbox" name="required_check" value="1" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Check required','ba-mobile-form')?></span> -->
										<?php }?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Show in Billing Form','ba-mobile-form');?></th>
									<td>
										<?php if (isset($required_billing) && !empty($required_billing)){
											?>
											<input <?php if($required_billing == 1) {?> checked <?php }?> value="1" type="checkbox" name="required_billing" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Enable show in billing form','ba-mobile-form')?></span> -->
										<?php } else {?>
											<input type="checkbox" name="required_billing" value="1" <?php if(isset($name_id) && in_array($name_id, $profile_filed)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
										<?php }?>
									</td>
								</tr>
								<tr> 
									<th scope="row"><?php echo __('Show in Shiping Form','ba-mobile-form');?></th>
									<td>
										<?php if (isset($required_shipping) && !empty($required_shipping)){
											?>
											<input <?php if($required_shipping == 1) {?> checked <?php }?> value="1" type="checkbox" name="required_shipping" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Enable show in shipping form','ba-mobile-form')?></span> -->
										<?php } else {
											?>
											<input type="checkbox" name="required_shipping" value="1" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Enable show in shipping form','ba-mobile-form')?></span> -->
										<?php }?>
									</td>
								</tr>
								<tr> 
									<th scope="row"><?php echo __('Show in Register Form','ba-mobile-form');?></th>
									<td>
										<?php if (isset($required_register) && !empty($required_register)){
											?>
											<input <?php if($required_register == 1) {?> checked <?php }?> value="1" type="checkbox" name="required_register" <?php if(isset($name_id) && in_array($name_id, $register_feild)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Enable show in register form','ba-mobile-form')?></span> -->
										<?php } else {?>
											<input type="checkbox" name="required_register" value="1" <?php if(isset($name_id) && in_array($name_id, $register_feild)) {?> style="pointer-events:none;background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
											<!-- <span class="description"><?php echo __('Enable show in register form','ba-mobile-form')?></span> -->
										<?php }?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php echo __('Show in Profile Form','ba-mobile-form');?></th>
									<td>
										<?php if (isset($required_profile) && !empty($profile_filed)){
											?>
											<input <?php if($required_profile == 1) {?> checked <?php }?> value="1" type="checkbox" name="required_profile" <?php if(isset($name_id) && in_array($name_id, $field_defautl)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
										<?php } else {?>
											<input type="checkbox" name="required_profile" value="1" <?php if(isset($name_id) && in_array($name_id, $profile_filed)) {?> style="pointer-events:none;    background: rgba(255,255,255,.5);border-color: rgba(222,222,222,.75);box-shadow: inset 0 1px 2px rgba(0,0,0,.04);color: rgba(51,51,51,.5);" <?php } else {} ?>>
										<?php }?>
									</td>
								</tr>
							</tbody>
					  	</table>
					  	
					</div>
					<div class="baform_button_from">
					  		<!-- <?php if(isset($name_id)){ ?>
						  		<input type="hidden" name="edit_check" value="1">
						  	<?php }?> -->
					  		<p class="submit">
								<a class="button" href="<?php echo admin_url('admin.php?page=form_design')?>"><?php echo __('Back','ba-mobile-form');?></a>	
							</p>
					  		<?php wp_nonce_field( 'create_field_design' ); ?>
							<?php
							if (isset($_REQUEST['edit']) && !empty($_REQUEST['edit'])){
							 submit_button(__('Save','ba-mobile-form'), $type = 'button button-primary', $name = 'submit');
							} else {
								submit_button(__('Save','ba-mobile-form'), $type = 'button button-primary', $name = 'submit');
							}
							 ?>
					  	</div>
				</form>
			</div>
		<?php }
	   
		public function baform_render_actions_row($section){ ?>
	        <th colspan="7">
	            <a type="button" class="button button-primary" href="<?php echo admin_url('admin.php?page=add_field&action=add')?>"><?php _e( '+ Add field', 'ba-mobile-form' ); ?></a>
	        </th>
	        <th colspan="4">
	        	<?php wp_nonce_field( 'save_change_dessign' ); ?>
	        	<input type="submit" name="save_fields" class="button-primary" value="<?php _e( 'Save changes', 'ba-mobile-form' ) ?>" style="float:right" />
	        	<input type="submit" formaction="<?php echo admin_url('admin.php?page=form_design&register=oke')?>" name="save_fields" style="float:right; margin-right: 5px;"  class="button" value="<?php _e( 'Reset to default fields', 'ba-mobile-form' ) ?>" style="float:right" />
	        </th>  
	    	<?php 
		}
		public function get_current_tab(){
			return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
		}
		public function get_current_section(){
			$tab = $this->get_current_tab();
			$section = '';
			if($tab === 'fields'){
				$section = isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : 'billing';
			}
			return $section;
		}
		public function baform_checkout_form_field_editor() {
			$locale_fields = array(
			'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name',
			);
			$profile_filed = array(
	        	'billing_user_login',
	        	'billing_nicename',
	        	'billing_display_name',
	        	'billing_url',
	        	'billing_description',
	        	'billing_password'
	        );
 			$field_defautl = array_merge($locale_fields,$profile_filed);
 			$field_defautl_profile = array(
 				'billing_user_login',
	        	'billing_nicename',
	        	'billing_display_name',
	        	'billing_url',
	        	'billing_description',
	        	'billing_password'
			);
 			 $required_fields = array('billing_last_name','billing_country', 'billing_address_1', 'billing_city','billing_email','billing_phone','billing_first_name','billing_nicename','billing_display_name','billing_password');
 			 $register_feild = array(
	        	'billing_email',
	        	'billing_user_login',
	        	'billing_last_name',
	        	'billing_first_name',
	        	'billing_password',
	        );
			$fieldss = get_option('ba_design_form');
			$option_fields = unserialize($fieldss);
			foreach ($option_fields as $key => $option_fl) {
				$array_list[]  = $option_fl['name_id'];
			}
			$result = array_diff($array_list, $locale_fields);
			$section = $this->get_current_section();			
			echo '<div class="wrap woocommerce">';
			echo '<h2>'.__('Fields List','ba-mobile-form').'</h2>'; ?>
			<div id="baform_update_message"></div>
			<?php if ( isset( $_POST['reset_fields'] ) )
					echo $this->reset_checkout_fields();		
				do_action('ba_design_notices');
				//var_dump(get_option('ba_design_form'));
				?>
				<form method="post" id="ba_checkout_fields_form" action="<?php echo admin_url('admin.php?page=form_design')?>">
	            	<table id="ba_checkout_fields" class="wc_gateways widefat" cellspacing="0">
						<thead>
	                    	<tr><?php $this->baform_render_actions_row($section); ?></tr>
	                    	<tr><?php $this->baform_render_checkout_fields_heading_row(); ?></tr>						
						</thead>
	                    <tfoot>
	                    	<tr><?php $this->baform_render_checkout_fields_heading_row(); ?></tr>
							<tr><?php $this->baform_render_actions_row($section); ?></tr>
						</tfoot>
						<tbody class="ui-sortable">
	                    <?php 
						$i=0;
						foreach( $this->baform_get_fields() as $name => $options ) :	
						?>
							<tr class="row_<?php echo $i; ?>">
								<td class="sort ui-sortable-handle">|||</td>
	                            <td class="td_name">
	                            	<?php echo esc_attr( str_replace("billing_","",$options['name_id'])); ?><input type="hidden" name="name_id[]" value="<?php echo esc_attr($options['name_id']);?>">
	                            </td>
	                            <td class="td_type">
	                            	<?php echo $options['type']; ?>
	                            	<input type="hidden" name="type[]" value="<?php echo esc_attr($options['type']);?>">
	                            </td>
	                            <td class="td_label">
	                            	<?php echo $options['label']; ?>
	                            	<input type="hidden" name="label[]" value="<?php echo esc_attr($options['label']);?>">
	                            </td>
	                            <td class="td_check status required_check <?php if(isset($options['name_id']) && in_array($options['name_id'], $required_fields)) {?> default_not_click <?php } else {} ?>">
	                            	<?php echo($options['required_check'] == 1 ? '<a href="admin.php?page=form_design&active=required_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-completed"></span></a>' : '<a href="admin.php?page=form_design&active=required_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-cancelled"></span></a>' ) ?>
	                            	<input type="hidden" name="required_check[]" value="<?php echo esc_attr($options['required_check']);?>">
	                            </td>
	                            <td class="td_shipping status shipping_check <?php if(isset($options['name_id']) && in_array($options['name_id'], $field_defautl)) {?> shipping_not_click <?php } else {} ?> ">
	                            	<?php echo($options['required_shipping'] == 1 ? '<a href="admin.php?page=form_design&active=shipping_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-completed"></span></a>' : '<a href="admin.php?page=form_design&active=shipping_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-cancelled"></span></a>' ) ?>
	                            	<input type="hidden" name="required_shipping[]" value="<?php echo esc_attr($options['required_shipping']);?>">
	                            </td>
	                            <td class="td_billing status billing_check <?php if(isset($options['name_id']) && in_array($options['name_id'], $field_defautl)) {?> billing_not_click <?php } else {} ?>">
	                            	<?php echo($options['required_billing'] == 1 ? '<a href="admin.php?page=form_design&active=billing_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-completed"></span></a>' : '<a href="admin.php?page=form_design&active=billing_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-cancelled"></span></a>' ) ?>
	                            	<input type="hidden" name="required_billing[]" value="<?php echo esc_attr($options['required_billing']);?>">
	                            </td>
	                            <td class="td_register status register_check <?php if(isset($options['name_id']) && in_array($options['name_id'], $register_feild)) {?> register_not_click <?php } else {} ?> ">
	                            	<?php echo($options['required_register'] == 1 ? '<a href="admin.php?page=form_design&active=register_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-completed"></span></a>' : '<a href="admin.php?page=form_design&active=register_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-cancelled"></span></a>' ) ?>
	                            	<input type="hidden" name="required_register[]" value="<?php echo esc_attr($options['required_register']);?>">
	                            	<input type="hidden" name="option_field[]" value="<?php echo esc_attr($options['option_field']);?>">
	                            	<input type="hidden" name="position_field[]" value="<?php echo esc_attr($options['position_field']);?>">
	                            	<input type="hidden" name="country_belong_to[]" value="<?php if(isset($options['country_belong_to'])){echo esc_attr($options['country_belong_to']);}?>">
	                            	<input type="hidden" name="country_has_state[]" value="<?php if(isset($options['country_has_state'])){echo esc_attr($options['country_has_state']);}?>">
	                            </td>
	                            <td class="td_profile status profile_check <?php if(isset($options['name_id']) && in_array($options['name_id'], $field_defautl)) {?> profile_not_click <?php } else {} ?>">
	                            	<?php echo($options['required_profile'] == 1 ? '<a href="admin.php?page=form_design&active=profile_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-completed"></span></a>' : '<a href="admin.php?page=form_design&active=profile_check&id_name='. esc_attr($options['name_id']).'" class="'. esc_attr($options['name_id']).'" ><span class="baform_icon-status-cancelled"></span></a>' ) ?>
	                            	<input type="hidden" name="required_profile[]" value="<?php echo esc_attr($options['required_profile']);?>">
	                            </td>
	                            <td class="td_edit">
	                            	<a type="button" class="btn button-primary ngothoai_edit_btn" 
	                                href="<?php echo admin_url("admin.php?page=add_field")?>&edit=<?php echo esc_attr( $options['name_id'] );?>"><?php _e( 'Edit', 'ba-mobile-form' ); ?></a>
	                            </td>
	                            <td class="td_edit">
	                            	<a <?php if (in_array($options['name_id'], $field_defautl)) {?> disabled <?php }?> href="<?php echo admin_url("admin.php?page=form_design")?>&action=delete&name_id=<?php echo esc_attr( $options['name_id'] );?>"   class="btn button ngothoai-delete" id="delete-<?php echo esc_attr( $options['name_id'] );?>"  
	                                ><?php _e( 'Delete', 'ba-mobile-form' ); ?></a>
	                            </td>
	                    	</tr>
	                    <?php $i++; endforeach; ?>
	                	</tbody>
					</table> 
	            </form>
	    	</div>
	    <?php 		
		}
		public function baform_render_checkout_fields_heading_row(){
		?>
			<th class="status"><?php echo __('|||','ba-mobile-form');?></th>
			<th class="name"><?php echo __('Name','ba-mobile-form');?></th>
			<th class="id"><?php echo __('Type','ba-mobile-form');?></th>
			<th><?php echo __('Label','ba-mobile-form');?></th>
			<th class="baform-tick-col"><?php echo __('Required','ba-mobile-form');?></th> 
			<th class="baform-tick-col"><?php echo __('Shipping','ba-mobile-form');?></th> 
			<th class="baform-tick-col"><?php echo __('Billing','ba-mobile-form');?></th>
	        <th class="baform-tick-col"><?php echo __('Register','ba-mobile-form');?></th>	
	        <th class="baform-tick-col"><?php echo __('Profile','ba-mobile-form');?></th>	
	        <th class="status"><?php echo __('Edit','ba-mobile-form');?></th>	
	        <th class="status"><?php echo __('Delete','ba-mobile-form');?></th>	
	        <?php
		}
		public static function baform_get_fields(){
			$fields = get_option('ba_design_form', array());
			$fields_ba = unserialize($fields);
			if(empty($fields_ba) || sizeof($fields_ba) == 0){
					$fields_ba = WC()->countries->get_address_fields(WC()->countries->get_base_country(),'_');	
			}
			return $fields_ba;
		}
				/*Field checkbox*/
		public function baform_setup_field(){?>
			<label for="baform_enable_field">
				<?php 
				$option_enable = get_option('baform_option_name');
					if (isset($option_enable['baform_enable_field']) && !empty($option_enable['baform_enable_field'])){
						$textval = $option_enable['baform_enable_field'];
					} else {
						$textval = "";
					}
				?>
				<span class="woocommerce-help-tip"><span class="tooltiptext tooltip-top"><?php echo __('If options is checked, this BA Form only work on a mobile application','ba-mobile-form');?></span></span><input class="regular-text" <?php if($textval==1){?> checked <?php } else {} ?> type="checkbox" name="baform_option_name[baform_enable_field]"  value="1"> 
			</label>
		<?php }
		/*function  setting field baform_setting_field_func*/
		/*Function add section*/
		public function option_section_func(){
			 //echo "Setup fields";
		}	
		/*Section group*/
		public function baform_OptionGroup($input){
	        $new_input = array();
	        if( isset( $input['baform_enable_field'] ) ){
	            $new_input['baform_enable_field'] = ( $input['baform_enable_field'] );
	        }
	        return $new_input;
        }
	    public function baform_setting_field_func(){ ?>
	        <div class="wrap">
	            <h2>
	                <?php echo __('Settings','ba-mobile-form');?>
	            </h2>
	            <?php settings_errors();?>
	            <form method="post" action="options.php" >
	                <?php settings_fields('baform_option_group');
	                    do_settings_sections('setting_field');
	                    submit_button();
	                ?>
	            </form>
	        </div>  
	    <?php }
	}
 ?>