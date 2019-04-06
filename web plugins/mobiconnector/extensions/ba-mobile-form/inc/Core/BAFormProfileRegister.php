<?php
namespace DesignForm\Core;  
	class BAFormProfileRegister{
		public function baform_run_function_profile(){
			/*Profile*/
			add_action( 'user_register', array($this,'baform_user_register') );
			add_action( 'show_user_profile', array($this,'baform_extra_user_profile_fields') );
			add_action( 'edit_user_profile', array($this,'baform_extra_user_profile_fields') );
			add_action( 'personal_options_update',array($this,'baform_update_profile_fields') );
			add_action( 'edit_user_profile_update',array($this, 'baform_update_profile_fields') );
			add_action( 'user_new_form',array($this, 'baform_new_user_profile_fields') );
			/*Register*/
			//add_action( 'woocommerce_register_form_start',array($this,'baform_register_fields'));
			add_action( 'woocommerce_created_customer', array($this,'baform_save_extra_register_fields') );
		}
		/*Profile*/
		/*Add field to form when add user*/
		public  function baform_new_user_profile_fields( $operation ) {

			//disable this plugin for web
			return;

			if ( 'add-new-user' !== $operation ) {
				return;
			}
		 ?>
		    
		    <?php $exxtras = get_the_author_meta('field_extra_user');
		    if (isset($exxtras) && !empty($exxtras)){ ?>
		    	<h3><?php _e("Extra profile information", "ba-mobile-form"); ?></h3>
		    <?php }
		    ?>
		    <table class="form-table">
		    <?php 
		    	$locale_fields = array(
					'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_email','billing_url','billing_description','billing_password'
					);
				$fieldss = get_option('ba_design_form');
				$option_fields = unserialize($fieldss);
				foreach ($option_fields as $key => $option_fl) {
					$array_list[]  = $option_fl['name_id'];
				}
				$result = array_diff($array_list, $locale_fields);
				foreach ($option_fields as $key => $check_fl) {
					if(in_array( $check_fl['name_id'], $result)){
			   		 ?>
				    <tr class="form-field-custom">
				        <th><label for="<?php echo esc_attr($check_fl['name_id']); ?>"><?php echo esc_attr($check_fl['label']); ?></label></th>
				        <td>
				        	<?php if($check_fl['type'] === "text"){ ?>
				            <input type="text" name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="" class="regular-text" /><br />
				        	<?php } elseif ($check_fl['type'] === "select"){ ?>
				        		<select name="<?php echo esc_attr($check_fl['name_id']); ?>">
				        			<?php 
				        				$array_option = unserialize($check_fl['option_field']);
							            if (is_array($array_option) || is_object($array_option)){
							                foreach($array_option as $option_f){ 
							                    echo "<option value='".$option_f."'>".$option_f."</option>";
							                }
							            } ?>
				        		</select><br />
				        	<?php } elseif ($check_fl['type'] === "radio") { 
				        			$array_option = unserialize($check_fl['option_field']);
						            if (is_array($array_option) || is_object($array_option)){
						                foreach($array_option as $option_f){ ?>
				        				<span><input type="radio" checked name="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php echo esc_attr($option_f);?>" /><?php echo esc_attr($option_f);?><span>
				        			<?php 
				        				}
				        			}
				        			?>
				        	<?php } elseif($check_fl['type'] === "textarea"){?>
				        		<textarea name="<?php echo esc_attr($check_fl['name_id']); ?>" class="input-text " id="<?php echo esc_attr($check_fl['name_id']); ?>"  rows="5" cols="8"></textarea>
				        	<?php  } elseif($check_fl['type'] === "multiselect"){?>
				        		<?php $array_option = unserialize($check_fl['option_field']);
						            if (is_array($array_option) || is_object($array_option)){?>
						            	<select multiple="multiple" data-placeholder="<?php echo __( 'Select some options', 'ba-mobile-form' );?>" class="checkout_chosen_select select wc-enhanced-select" name="<?php echo esc_attr($check_fl['name_id']); ?>[]" >
						                <?php foreach($array_option as $option_f){
						                	if (isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && !empty(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])){ ?>
							                	<option <?php if(!empty($option_f) && isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && in_array($option_f,get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])){?> selected <?php }?> value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?>
							                	</option>
					        			<?php } else { ?>
					        					<option  value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?>
							                	</option>
					        				<?php }
					        			} ?>
					        			</select>
				        			<?php }
				        			?>
				        	<?php  } elseif($check_fl['type'] === "multicheckbox"){ ?>
			        			<?php $array_option = unserialize($check_fl['option_field']);
					            if (is_array($array_option) || is_object($array_option)){
					                foreach($array_option as $option_f){ ?>
					                	<label><input type="checkbox" name="<?php echo esc_attr($check_fl['name_id']); ?>[]" value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?> </label>&nbsp&nbsp
			        			<?php 
			        				}
			        			}
			        			?>
			        		<?php  } elseif($check_fl['type'] === "email"){?>
				        			<input type="email" class="input-text" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if ( ! empty( $_POST[$check_fl['name_id']] ) ) esc_attr( $_POST[$check_fl['name_id']] ); ?>" />
				        	<?php  } elseif($check_fl['type'] === "tel"){?>
				        			<input type="tel" class="input-text" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if ( ! empty( $_POST[$check_fl['name_id']] ) ) esc_attr( $_POST[$check_fl['name_id']] ); ?>" />
				        	<?php  } elseif($check_fl['type'] === "ba-date"){?>
			        				 <input type="text" class="input-text ba-date" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if ( ! empty( $_POST[$check_fl['name_id']] ) ) esc_attr( $_POST[$check_fl['name_id']] ); ?>" />
				        	<?php  } elseif($check_fl['type'] === "country"){
				        		$field = "";
				        		$field .='<select style="width: 25em;" id="'.$check_fl['name_id'].'" name="'.$check_fl['name_id'].'" >';
				        		if (!empty($check_fl['country_has_state'])){
				        			$state = $check_fl['country_has_state'];
				        		} else {
				        			$state = "";
				        		}
								$field .='</select>';
								$field .='<script language="javascript">
									jQuery(function($){
									   $("#'.$check_fl['name_id'].'").select2();
									});
						            populateCountries("'.$check_fl['name_id'].'","'.$state.'");';
						        $field .='</script>';
						        echo $field;?>
				        	<?php } elseif($check_fl['type'] === "state") { 
				        		
				        		$field = "";
				        		
				        		$field .='<select style="width: 25em;" id="'.$check_fl['name_id'].'" name="'.$check_fl['name_id'].'">';
								$field .='</select>';
								$field .='<script language="javascript">
								jQuery(function($){
									 $("#'.$check_fl['name_id'].'").select2();';
								$field .='	});
						        </script>';
						        echo $field;
						         ?>

						     <?php } elseif($check_fl['type'] === "number"){?>
				        		<input type="number" name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" class="regular-text" />
				        	<?php } elseif($check_fl['type'] === "ip_address"){?>
				        		<input type="text" name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="" class="regular-text <?php echo esc_attr($check_fl['type']); ?>" />
				        		<label id="validate_ip" class="send_user_notification"></label>
				        	<?php } else {}?>
				        </td>
				    </tr>
			    <?php
			    	} 
				}?>
		    </table>
		<?php }

		/*Update meta user when add new user*/
		public function baform_user_register( $user_id ) {
			$locale_fields = array( 'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description', 'billing_password',
					);
			$field_profile = array('billing_last_name','billing_email','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description','billing_password');
			$fieldss = get_option('ba_design_form');
			$option_fields = unserialize($fieldss);
			foreach ($option_fields as $key => $option_fl) {
				$array_list[]  = $option_fl['name_id'];
			}
			$result = array_diff($array_list, $locale_fields);

			foreach ($option_fields as $key => $check_fl) {
				if(in_array( $check_fl['name_id'], $result)){
				 	$value = !empty($_POST[$check_fl['name_id']]) ?  ( $_POST[$check_fl['name_id']] ) : '';
				 	if ($check_fl['type'] === 'multiselect'){
				 		$fields[$check_fl['name_id']] = $value;
				 	} else {
				 		$fields[$check_fl['name_id']] = $value;
				 	}
				} elseif(in_array( $check_fl['name_id'], $field_profile)){
					$field_meta_id = str_replace("billing_","",$check_fl['name_id']);
					$value = !empty($_POST[$check_fl['name_id']]) ?  ( $_POST[$check_fl['name_id']] ) : '';
					if ($field_meta_id === 'url'){
						wp_update_user(array( 'ID' => $user_id, 'user_url' => $value ));
					} elseif($field_meta_id === 'display_name'){
						wp_update_user(array( 'ID' => $user_id, 'display_name' => $value ));
					} elseif($field_meta_id === 'password'){
						wp_update_user(array( 'ID' => $user_id, 'user_pass' =>($value) ));
					} elseif($field_meta_id === 'user_login'){
						wp_update_user(array( 'ID' => $user_id, 'user_login' => $value ));
					} elseif($field_meta_id === 'nickname'){
						wp_update_user(array( 'ID' => $user_id, 'user_nicename' => $value ));
					} else {
						update_user_meta($user_id, $field_meta_id, $value );
					}
					
				} else {

				}
			}
			if ( ! empty( $fields ) ) {
				update_user_meta( $user_id, 'field_extra_user', $fields );
			}
		}
		/*Form field in profile edit*/
		public function baform_extra_user_profile_fields( $user ) {
		 ?>
		    <?php $exxtras = get_the_author_meta('field_extra_user', $user->ID);
		    if(is_array($exxtras)){
		    	if (count($exxtras) > 0){ ?>
			    	<h3><?php _e("Extra profile information", "ba-mobile-form"); ?></h3>
			    <?php }
		    }     
		    ?>
		    <table class="form-table">
		    <?php 
		    	$locale_fields = array(
					'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description','billing_password',
					);
		    	$array_list = array();
				$fieldss = get_option('ba_design_form');
				if(isset($fieldss)){
					$option_fields = unserialize($fieldss);
				} else{
					$option_fields = array();
				}
				
				foreach ($option_fields as $key => $option_fl) {
					$array_list[]  = $option_fl['name_id'];
				}
				$result = array_diff($array_list, $locale_fields);
				foreach ($option_fields as $key => $check_fl) {
					if(in_array( $check_fl['name_id'], $result)){
			   		 ?>
				    <tr>
				        <th><label for="<?php echo esc_attr($check_fl['name_id']); ?>"><?php echo esc_attr($check_fl['label']); ?></label></th>
				        <td>
				        	<?php if($check_fl['type'] === "text"){ ?>
				            <input type="text"  <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" class="regular-text" /><br />
				        	<?php } elseif ($check_fl['type'] === "select"){ ?>
				        		<select <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>" class="regular-text">
				        			<?php 
				        				$array_option = unserialize($check_fl['option_field']);
							            if (is_array($array_option) || is_object($array_option)){
							                foreach($array_option as $option_f){ ?>
							                    <option <?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && (get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] == $option_f)){?> selected <?php }?> value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?></option>
							                <?php }
							            } ?>
				        		</select>
				        	<?php } elseif ($check_fl['type'] === "radio") { 
				        			$array_option = unserialize($check_fl['option_field']);
						            if (is_array($array_option) || is_object($array_option)){
						                foreach($array_option as $option_f){ ?>
				        				<span><input type="radio" <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> <?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && (get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] == $option_f)){?> checked <?php }?>   name="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php echo esc_attr($option_f);?>" /><?php echo esc_attr($option_f);?><span>
				        			<?php 
				        				}
				        			} else {
				        				$array_option = unserialize($check_fl['option_field']);
				        				foreach($array_option as $option_f){ ?>
				        				<span><input <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> type="radio"  name="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php echo esc_attr($option_f);?>" /><?php echo esc_attr($option_f);?><span>
				        				<?php 
				        				}
				        			}
				        			?>
				        	<?php } elseif($check_fl['type'] === "textarea"){?>
				        		<textarea <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>" class="input-text " id="<?php echo esc_attr($check_fl['name_id']); ?>"  rows="5" cols="8"><?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?></textarea>
				        	<?php  } elseif($check_fl['type'] === "multiselect"){?>
				        		
				        			<?php $array_option = unserialize($check_fl['option_field']);
						            if (is_array($array_option) || is_object($array_option)){?>
						            	<select <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> multiple="multiple" data-placeholder="<?php echo __( 'Select some options', 'ba-mobile-form' );?>" class="checkout_chosen_select select wc-enhanced-select" name="<?php echo esc_attr($check_fl['name_id']); ?>[]" >
						                <?php foreach($array_option as $option_f){
						                	if (isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && !empty(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])){ ?>
							                	<option <?php if(!empty($option_f) && isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && in_array($option_f,get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])){?> selected <?php }?> value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?>
							                	</option>
					        			<?php } else { ?>
					        					<option  value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?>
							                	</option>
					        				<?php }
					        			} ?>
					        			</select>
				        			<?php }
				        			?>
				        	<?php  }  elseif($check_fl['type'] === "multicheckbox"){?>
				        		
				        			<?php $array_option = unserialize($check_fl['option_field']);
						            if (is_array($array_option) || is_object($array_option)){?>
						                <?php foreach($array_option as $option_f){
						                	if (isset($option_f) && !empty($option_f)){ ?>
						                		<input <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> type="checkbox" <?php if (isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && in_array($option_f,get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])){?> checked <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>[]" value="<?php echo esc_attr($option_f);?>">
						                	<?php } echo esc_attr($option_f);?>
					        			<?php } ?>
				        			<?php }
				        			?>
				        	<?php  } elseif($check_fl['type'] === "email"){?>
				        			<input <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> type="email" class="input-text" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" />
				        	<?php  } elseif($check_fl['type'] === "tel"){?>
				        			<input <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> type="tel" class="input-text" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" />
				        	<?php  } elseif($check_fl['type'] === "ba-date"){?>
			        				<input <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> type="text" class="input-text ba-date" <?php if($check_fl['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($check_fl['name_id']);?>" id="reg_<?php echo esc_attr($check_fl['name_id']);?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" />
				        	<?php  } elseif($check_fl['type'] === "country"){
				        		if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { 
				        			$value_country =  esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] );
				        		} else {$value_country ="";
				        		} 
				        		$field = "";
				        		$field .='<select style="width: 25em;" id="'.$check_fl['name_id'].'" name="'.$check_fl['name_id'].'" >';
				        		if (!empty($check_fl['country_has_state'])){
				        			$state = $check_fl['country_has_state'];
				        		} else {
				        			$state = "";
				        		}
								$field .='</select>';
								$field .='<script language="javascript">
									jQuery(function($){
									   $("#'.$check_fl['name_id'].'").select2();
									});
						            populateCountries("'.$check_fl['name_id'].'","'.$state.'");';
						        if($value_country !== "" && !empty($value_country)){
						        	$field .='jQuery(function($){
						            	$("td #'.$check_fl['name_id'].' option").each(function(){
										  if ($(this).text() == "'.$value_country.'")
										    $(this).attr("selected","selected");
										});
						             $("td #s2id_'.$check_fl['name_id'].' .select2-chosen").text("'.$value_country.'");
						             });';
						        }
						        
						         $field .='</script>';
						        echo $field;?>
				        	<?php } elseif($check_fl['type'] === "state") { 
				        		if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]) && (!empty(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']]))) { 
				        			$value_state =  esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] );
				        		} else {$value_state = "";
				        		} 
				        		$field = "";
				        		
				        		$field .='<select style="width: 25em;" id="'.$check_fl['name_id'].'" name="'.$check_fl['name_id'].'">';
								$field .='</select>';
								$field .='<script language="javascript">
								jQuery(function($){
									 $("#'.$check_fl['name_id'].'").select2();';
									 if($value_state !== "" && !empty($value_state)){
								       	$field .='
								       	$("td #'.$check_fl['name_id'].' option").each(function(){
										  if ($(this).text() == "'.$value_state.'")
										    $(this).attr("selected","selected");
										});

								       	$("td #s2id_'.$check_fl['name_id'].' .select2-chosen").text("'.$value_state.'");';
								        }
									  
								$field .='	});
						        </script>';
						        echo $field;
						         ?>
						     <?php } elseif($check_fl['type'] === "number"){?>
				        		<input type="number" <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" class="regular-text" />
				        	<?php } elseif($check_fl['type'] === "ip_address"){?>
				        		<input type="text" <?php if($check_fl['required_profile'] == 0){?> disabled="disabled" <?php }?> name="<?php echo esc_attr($check_fl['name_id']); ?>" id="<?php echo esc_attr($check_fl['name_id']); ?>" value="<?php if(isset(get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']])) { echo esc_attr( get_the_author_meta('field_extra_user', $user->ID)[$check_fl['name_id']] ); } else {} ?>" class="regular-text <?php echo esc_attr($check_fl['type']); ?>" />
				        		<p id="validate_ip" class="description"></p>
				        	<?php } else {}?>
				        </td>
				    </tr>
			    <?php
			    	} 
				}?>
		    </table>
		<?php }
		/*Update feild custom profile when edit*/
		/*Update option*/
		public function baform_update_profile_fields( $user_id ) {
			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return false;
			}
			$locale_fields = array( 'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description','billing_password'
					);
			$fieldss = get_option('ba_design_form');
			$option_fields = unserialize($fieldss);
			foreach ($option_fields as $key => $option_fl){
				$array_list[]  = $option_fl['name_id'];
			}
			$result = array_diff($array_list, $locale_fields);

			foreach ($option_fields as $key => $check_fl) {
				if(in_array( $check_fl['name_id'], $result)){
				 	$value = !empty($_POST[$check_fl['name_id']]) ?  ( $_POST[$check_fl['name_id']] ) : '';
				 	$fields[$check_fl['name_id']] = $value;
				}
			}
			if ( ! empty( $fields ) ) {
				update_user_meta( $user_id, 'field_extra_user', $fields );
			}
		}
		/*Add feild to form register*/
		public function baform_register_fields($fields) {
			$fieldss = get_option('ba_design_form');
			$option_fields = unserialize($fieldss);
			foreach ($option_fields as $key => $value) {
				if ($value['required_register'] == 1 && $value['name_id'] !== 'billing_email'){
			?>
				<div class="form-row zz <?php if($value['name_id'] === 'billing_first_name') {?>form-row-first <?php } elseif ($value['name_id'] === 'billing_last_name'){?>form-row-last <?php } else {?>form-row-wide <?php }?>">
					<?php if($value['type'] !== "country"){?>
			       <label for="reg_<?php echo esc_attr($value['name_id']);?>"><?php echo esc_attr($value['label']);?>
			       		<?php if($value['required_check'] == 1) {?>
			       			<span class="required">*</span>
			       		<?php }?>
			       </label>
			   		<?php } else {}?>
			       <?php if($value['type'] === "text" ){ ?>
		            <input type="text" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php } elseif ($value['type'] === "select"){ ?>
		        		<select name="<?php echo esc_attr($value['name_id']); ?>" class="design-select">
		        			<?php 
		        				$array_option = unserialize($value['option_field']);
					            if (is_array($array_option) || is_object($array_option)){
					                foreach($array_option as $option_f){ 
					                    echo "<option value='".$option_f."'>".$option_f."</option>";
					                }
					            } ?>
		        		</select>
		        	<?php } elseif ($value['type'] === "radio") { 
		        			$array_option = unserialize($value['option_field']);
				            if (is_array($array_option) || is_object($array_option)){
				                foreach($array_option as $option_f){ ?>
		        				<span><input type="radio"  name="<?php echo esc_attr($value['name_id']); ?>" value="<?php echo esc_attr($option_f);?>" /><?php echo esc_attr($option_f);?><span>
		        			<?php 
		        				}
		        			}
		        			?>
		        	<?php } elseif($value['type'] === "textarea"){?>
				        		<textarea name="<?php echo esc_attr($value['name_id']); ?>" class="input-text " id="<?php echo esc_attr($value['name_id']); ?>"  rows="5" cols="8"></textarea>
				    <?php  } elseif($value['type'] === "multiselect"){?>
		        		<select multiple="multiple" data-placeholder="<?php echo __( 'Select some options', 'ba-mobile-form' );?>" class="checkout_chosen_select select wc-enhanced-select" name="<?php echo esc_attr($value['name_id']); ?>[]" >
		        			<?php $array_option = unserialize($value['option_field']);
				            if (is_array($array_option) || is_object($array_option)){
				                foreach($array_option as $option_f){ ?>
				                	<option value="<?php echo esc_attr($option_f);?>" >
				                		<?php echo esc_attr($option_f);?>
				                	</option>
		        			<?php 
		        				}
		        			}
		        			?>
		        		</select>
		        	<?php  } elseif($value['type'] === "multicheckbox"){?>
		        			<?php $array_option = unserialize($value['option_field']);
				            if (is_array($array_option) || is_object($array_option)){
				                foreach($array_option as $option_f){ ?>
				                	<label><input type="checkbox" name="<?php echo esc_attr($value['name_id']); ?>[]" value="<?php echo esc_attr($option_f);?>"><?php echo esc_attr($option_f);?> </label>
		        			<?php 
		        				}
		        			}
		        			} elseif($value['type'] === "email"){?>
		        			<input type="email" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php  } elseif($value['type'] === "url"){?>
		        			<input type="url" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php  } elseif($value['type'] === "tel"){?>
		        			<input type="tel" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php  } elseif($value['type'] === "ba-date"){?>
	        				 <input type="text" class="input-text ba-date" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php  } elseif($value['type'] === "country"){ ?>
			        		<label for="reg_<?php echo esc_attr($value['name_id']);?>"><?php echo esc_attr($value['label']);?>
					       		<?php if($value['required_check'] == 1) {?>
					       			<span class="required">*</span>
					       		<?php }?>
					       </label>
		        		<?php $field = "";
		        		$field .='<select id="'.$value['name_id'].'" name="'.$value['name_id'].'" >';
		        		if (!empty($value['country_has_state'])){
		        			$state = $value['country_has_state'];
		        		} else {
		        			$state = "";
		        		}
						$field .='</select>';
						$field .='<script language="javascript">
						jQuery(function($){
							 $("#'.$value['name_id'].'").select2();
							});
				            populateCountries("'.$value['name_id'].'","'.$state.'");
				        </script>';
				        echo $field;
		        			 ?>
		        	<?php } elseif($value['type'] === "state") { 
		        		$field = "";
		        		$field .='<select id="'.$value['name_id'].'" name="'.$value['name_id'].'">';
						$field .='</select>';
						$field .='<script language="javascript">
						jQuery(function($){
							 $("#'.$value['name_id'].'").select2();
							});
				        </script>';
				        echo $field;
				         ?>
		        	<?php }  elseif($value['type'] === "number"){?>
		        		<input type="number" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
		        	<?php } elseif($value['type'] === "ip_address"){?>
				        		<input type="text" name="<?php echo esc_attr($value['name_id']); ?>" id="<?php echo esc_attr($value['name_id']); ?>" value="" class="regular-text <?php echo esc_attr($value['type']); ?>" />
				        		<label id="validate_ip" class="send_user_notification"></label>
				        	<?php } elseif($value['type'] === "password") {?>
				        		<input type="password" class="input-text" <?php if($value['required_check'] == 1) {?> required <?php }?> name="<?php echo esc_attr($value['name_id']);?>" id="reg_<?php echo esc_attr($value['name_id']);?>" value="<?php if ( ! empty( $_POST[$value['name_id']] ) ) esc_attr( $_POST[$value['name_id']] ); ?>" />
				        	<?php } else {}?>
			    </div>
			<?php 
				}
			 }?>
		    <div class="clear"></div>
		<?php  }
		/*Regiter save data meta user*/
		public function baform_save_extra_register_fields( $customer_id ) {
		   if ( isset( $_POST['billing_first_name'] ) ) {
		          // WordPress default first name field.
		   	if(isset($_POST['first_name'])){
		   		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
		   	} else {
		   		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		   	}
		          
		          // WooCommerce billing first name.
		          update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		          update_user_meta( $customer_id, 'shipping_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		   }
		   if ( isset( $_POST['billing_last_name'] ) ) {
	          // WordPress default last name field.
	          if(isset($_POST['last_name'])){
			   		update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
			   	} else {
			   		update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
			   	}
	          // WooCommerce billing last name.
	          update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
	          update_user_meta( $customer_id, 'shipping_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
		   }
		   if ( isset( $_POST['billing_phone'] ) ) {
	          // WooCommerce billing phone
	          update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
		   }
		   if ( isset( $_POST['billing_company'] ) ) {
	          // WooCommerce billing company
	          update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
	          update_user_meta( $customer_id, 'shipping_company', sanitize_text_field( $_POST['billing_company'] ) );
		   }
		   if ( isset( $_POST['billing_country'] ) ) {
		          // WooCommerce billing country
		          update_user_meta( $customer_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
		          update_user_meta( $customer_id, 'shipping_country', sanitize_text_field( $_POST['billing_country'] ) );
		   }
		   if ( isset( $_POST['billing_address_1'] ) ) {
		          // WooCommerce billing address 1
		          update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
		          update_user_meta( $customer_id, 'shipping_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
		   }
		   if ( isset( $_POST['billing_address_2'] ) ) {
		          // WooCommerce billing address 2
		          update_user_meta( $customer_id, 'billing_address_2', sanitize_text_field( $_POST['billing_address_2'] ) );
		          update_user_meta( $customer_id, 'shipping_address_2', sanitize_text_field( $_POST['billing_address_2'] ) );
		   }
		   if ( isset( $_POST['billing_city'] ) ) {
		          // WooCommerce billing city
		          update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
		          update_user_meta( $customer_id, 'shipping_city', sanitize_text_field( $_POST['billing_city'] ) );
		   }
		   if ( isset( $_POST['billing_state'] ) ) {
		          // WooCommerce billing state
		          update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );
		          update_user_meta( $customer_id, 'shipping_state', sanitize_text_field( $_POST['billing_state'] ) );
		   }
		   if ( isset( $_POST['billing_postcode'] ) ) {
		          // WooCommerce billing postcode
		          update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
		   }
		   if ( isset( $_POST['billing_email'] ) ) {
		          // WooCommerce billing email
		          update_user_meta( $customer_id, 'billing_email', sanitize_text_field( $_POST['billing_email'] ) );
		   }
		   $locale_fields = array(
				'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description','billing_password'
				);
			$fieldss = get_option('ba_design_form');
			$option_fields = unserialize($fieldss);
			foreach ($option_fields as $key => $option_fl) {
				$array_list[]  = $option_fl['name_id'];
			}
			$result = array_diff($array_list, $locale_fields);
			$field_profile = array('billing_last_name','billing_email','billing_first_name','billing_user_login','billing_nicename','billing_display_name','billing_url','billing_description','billing_password');
			foreach ($option_fields as $key => $check_fl) {
				if(in_array( $check_fl['name_id'], $result)){
				 	$value = !empty($_POST[$check_fl['name_id']]) ?  ( $_POST[$check_fl['name_id']] ) : '';
				 	if ($check_fl['type'] === 'multiselect'){
				 		$fields[$check_fl['name_id']] = $value;
				 	} else {
				 		$fields[$check_fl['name_id']] = $value;
				 	}
				}
			}
			if ( ! empty( $fields ) ) {
				update_user_meta( $customer_id, 'field_extra_user', $fields );
			}
		}
	}

?>