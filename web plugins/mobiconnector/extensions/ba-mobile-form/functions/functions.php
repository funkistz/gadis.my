<?php
	class BaForm_Functions{
		public function __construct(){
			add_action('woocommerce_init', array($this,'baform_resetform'),20);
			/*action hook ajax required check*/
			//add_action( 'wp_ajax_baform_checkrequired', array($this,'baform_checkrequired'));
	    	add_action( 'plugins_loaded', array($this,'baform_checkrequired'));

			/*action hook ajax shipping check*/
			//add_action( 'wp_ajax_baform_shippingcheck', array($this,'baform_shippingcheck'));
	    	add_action( 'plugins_loaded', array($this,'baform_shippingcheck'));
	    	/*action hook ajax billing check*/
	    	add_action( 'plugins_loaded', array($this,'baform_billingcheck' ));
	    	//add_action( 'wp_ajax_nopriv_baform_billingcheck', array($this,'baform_billingcheck' ));

	    	add_action('plugins_loaded', array($this, 'baform_registercheck'));
		    add_action( 'plugins_loaded', array($this,'baform_profilecheck' ));
		}
		/*Notice reset successfull*/
    public function baform_notices_reset_oke(){
        $baform_notices_check =  '<div id="message" class="updated notice notice-success"><p>'.__('Reset successfully.').'</p></div>';
        echo  $baform_notices_check;
    }
    
	    public function baform_resetform(){
	    	$ba_design = array();
	        $locale_fields = array(
	            'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name',
	            );
	        $profile_filed = array(
	        	'billing_user_login' => array(
	        		'label'=> __('Username','ba-mobile-booking'),
	        		'required' => 0,
	        		'class' => array(),
	        		'validate'=> array(),
	        		'type' => 'text'
	        	),
	        	'billing_nicename' => array(
	        		'label'=> __('Nickname (required)','ba-mobile-booking'),
	        		'required' => 1,
	        		'class' => array(),
	        		'validate'=> array(),
	        		'type' => 'text'
	        	),
	        	'billing_display_name' => array(
	        		'label'=> __('Display name publicly as','ba-mobile-booking'),
	        		'required' => 1,
	        		'class' => array(),
	        		'validate'=> array(),
	        		'type' => 'select'
	        	),
	        	'billing_url' => array(
	        		'label'=> __('Website','ba-mobile-booking'),
	        		'required' => 0,
	        		'class' => array(),
	        		'validate'=> array(),
	        		'type' => 'url'
	        	),
	        	'billing_description' => array(
	        		'label'=> __('Biographical Info','ba-mobile-booking'),
	        		'required' => 0,
	        		'class' => array(),
	        		'validate'=> array(),
	        		'type' => 'textarea'
	        	),
	        	'billing_password' => array(
                'label'=> __('Password','ba-mobile-booking'),
                'required' => 0,
                'class' => array(),
                'validate'=> array(),
                'type' => 'password'
            ),
	        );
	        $required_fields = array('billing_last_name','billing_country', 'billing_address_1', 'billing_city','billing_email','billing_phone','billing_first_name');	        
        	$res_page = isset($_GET['page']) ? $_GET['page']  : '';
        	if ( isset( $_POST['name_id'] ) && ( $_POST['_wpnonce'] ) && ($res_page === 'form_design') && isset($_GET['register']) && ($_GET['register'] === 'oke')){
        		$countries = WC()->countries;
        		update_option( 'ba_design_form', '' );
                add_action( 'ba_design_notices', array($this,'baform_notices_reset_oke'), 10, 1 );
                 $fields =  $countries->get_address_fields($countries->get_base_country(),'billing_');
                $fieldsx = array_merge($fields,$profile_filed);
                    $i=0;
                    foreach( $fieldsx as $name => $options ) :
                        //if (in_array($name, $locale_fields)) {
                            if (isset($name)){
                                $ba_design[$i]['name_id'] = $name;
                            } else {
                                $ba_design[$i]['name_id'] = "";
                            }
                            if (isset($options['type'])){
                                if ($ba_design[$i]['name_id'] === 'billing_last_name' || $ba_design[$i]['name_id'] === 'billing_first_name' || $ba_design[$i]['name_id'] === 'billing_company' || $ba_design[$i]['name_id'] === 'billing_address_1' || $ba_design[$i]['name_id'] === 'billing_address_2' || $ba_design[$i]['name_id'] === 'billing_city' || $ba_design[$i]['name_id'] === 'billing_postcode' ){
                                    $ba_design[$i]['type'] = 'text';
                                } else {
                                    $ba_design[$i]['type'] = $options['type'];
                                }
                            } else {
                                $ba_design[$i]['type'] = 'text';
                            }
                            if (isset($options['label'])){
                                $ba_design[$i]['label'] = $options['label'];
                            } else {
                                $ba_design[$i]['label'] = "";
                            }
                            if (isset($options['required'])){
                            	if(($options['required'] == true) && ($ba_design[$i]['type'] !== 'state')){
			                        $ba_design[$i]['required_check'] = 1;
			                    } elseif ($ba_design[$i]['type'] === 'state') {
			                        $ba_design[$i]['required_check'] = 0;
			                    } else {
			                    	$ba_design[$i]['required_check'] = 0;
			                    }
                            } else {
                            	if(in_array($name, $required_fields)){
                            		$ba_design[$i]['required_check'] = 1;
                            	} else {
                            		$ba_design[$i]['required_check'] = 0;
                            	}
                                
                            }

                            if (isset($options['required_billing'])){
                            $ba_design[$i]['required_billing'] = 1;
                            } else {
                            	if($name !== 'billing_user_login' &&  $name !== 'billing_nicename' && $name !== 'billing_display_name'  && $name !== 'billing_url' && $name !== 'billing_description'  && $name !=='billing_password'){
                            		$ba_design[$i]['required_billing'] = 1;
                            	} else {
                            		$ba_design[$i]['required_billing'] = 0;
                            	}
                                
                            }

                            if (isset($options['required_shipping'])){
                            	$ba_design[$i]['required_shipping'] = 1;
                            } else {
                            	if($name !== 'billing_user_login' &&  $name !== 'billing_nicename' && $name !== 'billing_display_name'  && $name !== 'billing_url' && $name !== 'billing_description'  && $name !=='billing_password'){
                            		$ba_design[$i]['required_shipping'] = 1;
                            	} else {
                            		$ba_design[$i]['required_shipping'] = 0;
                            	}
                                
                            }
                            
                            if (isset($options['required_register'])){
                           		 $ba_design[$i]['required_register'] = 1;
                            } else {
                            	if( $name !== 'billing_nicename' && $name !== 'billing_display_name'  && $name !== 'billing_url' && $name !== 'billing_description' && $name !=='billing_company' && $name !=='billing_country' && $name !=='billing_address_1' && $name !=='billing_address_2' && $name !=='billing_city' && $name !=='billing_state' && $name !=='billing_postcode' && $name !=='billing_phone'){
                            		$ba_design[$i]['required_register'] = 1;
                            	} else {
                            		$ba_design[$i]['required_register'] = 0;
                            	}
                            }

                            if (isset($options['required_profile'])){
                                $ba_design[$i]['required_profile'] = 0;
                            } else {
                            	if($name !== 'billing_last_name' && $name !== 'billing_first_name' && $name !== 'billing_email' && $name !== 'billing_user_login' &&  $name !== 'billing_nicename' && $name !== 'billing_display_name'  && $name !== 'billing_url' && $name !== 'billing_description'  && $name !=='billing_password'){
                            		$ba_design[$i]['required_profile'] = 0;
                            	} else {
                            		$ba_design[$i]['required_profile'] = 1;
                            	}
                                
                            }

                            if (isset($options['position_field'])){
				                if ($name === 'billing_first_name'){
				                    $ba_design[$i]['position_field'] = 'form-row-first';
				                } elseif ($name === 'billing_last_name') {
				                    $ba_design[$i]['position_field'] = 'form-row-last';
				                } else{
				                    $ba_design[$i]['position_field'] = 'form-row-wide';
				                }
				            } else {
				                if ($name === 'billing_first_name'){
				                    $ba_design[$i]['position_field'] = 'form-row-first';
				                } elseif($name === 'billing_last_name') {
				                    $ba_design[$i]['position_field'] = 'form-row-last';
				                } else{
				                    $ba_design[$i]['position_field'] = 'form-row-wide';
				                }
				            }
                            $ba_design[$i]['option_field'] = serialize("");
                            if ($name === 'billing_state'){
			                    $ba_design[$i]['country_belong_to'] = 'billing_country';
			                } else {
			                    $ba_design[$i]['country_belong_to'] = '';
			                }
			                if ($name === 'billing_country'){
			                	$ba_design[$i]['country_has_state'] = 'billing_state';
			                } else {
			                	$ba_design[$i]['country_has_state'] = '';
			                }
                            $i++;
                        //}
                    endforeach;
                    $strSerialize = serialize($ba_design);
                    
                    update_option( 'ba_design_form', $strSerialize );
            }
    	}
    	/*Ajax check required*/
    	public function baform_checkrequired(){
	        if ( isset( $_GET['page'] ) && isset( $_GET['active'] ) && $_GET['active'] === 'required_check') {
		        $id_name = isset($_GET['id_name']) ? $_GET['id_name'] : '';
		        $i=0;
		        $fields = get_option('ba_design_form', array());
		        $fields_ba = unserialize($fields);
		        foreach( $fields_ba as $name => $options ) :
		                $ba_design[$i]['name_id'] = $options['name_id'];
		                $ba_design[$i]['type'] = $options['type'];
		                $ba_design[$i]['label'] = $options['label'];
		                
		                if ($options['name_id'] == $id_name){
		                    if ($options['required_check'] == 1) {
		                        $ba_design[$i]['required_check'] = 0;
		                    } else {
		                        $ba_design[$i]['required_check'] = 1;
		                    }
		                } else {
		                    $ba_design[$i]['required_check'] = $options['required_check'];
		                }
		                $ba_design[$i]['required_shipping'] = $options['required_shipping'];
		                $ba_design[$i]['required_billing'] = $options['required_billing'];

		               
		                $ba_design[$i]['required_register'] = $options['required_register'];
		                $ba_design[$i]['required_profile'] = $options['required_profile'];
		                $ba_design[$i]['option_field'] = $options['option_field'];
		                $ba_design[$i]['position_field'] = $options['position_field'];
		                $ba_design[$i]['country_has_state'] = $options['country_has_state'];
		                $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
		            $i++;
		        endforeach;
		        $strSerialize = serialize($ba_design);
		       	update_option( 'ba_design_form', $strSerialize );
	        	wp_redirect('admin.php?page=form_design');
	        }
	    }
    	/*Ajax check shipping*/
	    public function baform_shippingcheck(){
	        if ( isset( $_GET['page'] ) && isset( $_GET['active'] ) && $_GET['active'] === 'shipping_check') {
		        $id_name = isset($_GET['id_name']) ? $_GET['id_name'] : '';
		        $i=0;
		        $fields = get_option('ba_design_form', array());
		        $fields_ba = unserialize($fields);
		        foreach( $fields_ba as $name => $options ) :
		                $ba_design[$i]['name_id'] = $options['name_id'];
		                $ba_design[$i]['type'] = $options['type'];
		                $ba_design[$i]['label'] = $options['label'];
		                $ba_design[$i]['required_check'] = $options['required_check'];
		                $ba_design[$i]['required_billing'] = $options['required_billing'];

		                if ($options['name_id'] == $id_name){
		                    if ($options['required_shipping'] == 1) {
		                        $ba_design[$i]['required_shipping'] = 0;
		                    } else {
		                        $ba_design[$i]['required_shipping'] = 1;
		                    }
		                } else {
		                    $ba_design[$i]['required_shipping'] = $options['required_shipping'];
		                }
		                $ba_design[$i]['required_register'] = $options['required_register'];
		                $ba_design[$i]['required_profile'] = $options['required_profile'];
		                $ba_design[$i]['option_field'] = $options['option_field'];
		                $ba_design[$i]['position_field'] = $options['position_field'];
		                $ba_design[$i]['country_has_state'] = $options['country_has_state'];
		                $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
		            $i++;
		        endforeach;
		        $strSerialize = serialize($ba_design);
		       	update_option( 'ba_design_form', $strSerialize );
	        	wp_redirect('admin.php?page=form_design');
	        }
	    }
	    /*Ajax check billing*/
	    public function baform_billingcheck(){
	        if ( isset( $_GET['page'] ) && isset( $_GET['active'] ) && $_GET['active'] === 'billing_check') {
		        $id_name = isset($_GET['id_name']) ? $_GET['id_name'] : '';
		        $i=0;
		        $fields = get_option('ba_design_form', array());
		        $fields_ba = unserialize($fields);
		        foreach( $fields_ba as $name => $options ) :
		                $ba_design[$i]['name_id'] = $options['name_id'];
		                $ba_design[$i]['type'] = $options['type'];
		                $ba_design[$i]['label'] = $options['label'];
		                $ba_design[$i]['required_check'] = $options['required_check'];
		                $ba_design[$i]['required_shipping'] = $options['required_shipping'];

		                if ($options['name_id'] == $id_name){
		                    if ($options['required_billing'] == 1) {
		                        $ba_design[$i]['required_billing'] = 0;
		                    } else {
		                        $ba_design[$i]['required_billing'] = 1;
		                    }
		                } else {
		                    $ba_design[$i]['required_billing'] = $options['required_billing'];
		                }
		                $ba_design[$i]['required_register'] = $options['required_register'];
		                $ba_design[$i]['required_profile'] = $options['required_profile'];
		                $ba_design[$i]['option_field'] = $options['option_field'];
		                $ba_design[$i]['position_field'] = $options['position_field'];
		                $ba_design[$i]['country_has_state'] = $options['country_has_state'];
		                $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
		            $i++;
		        endforeach;
		        $strSerialize = serialize($ba_design);
		        update_option( 'ba_design_form', $strSerialize );
		        wp_redirect('admin.php?page=form_design');
	        }
	    }
	    /*Ajax check register*/
	    public function baform_registercheck(){
	    	if ( isset( $_GET['page'] ) && isset( $_GET['active'] ) && $_GET['active'] === 'register_check') {
		        $id_name = isset($_GET['id_name']) ? $_GET['id_name'] : '';
		        $i=0;
		        $fields = get_option('ba_design_form', array());
		        $fields_ba = unserialize($fields);
		        foreach( $fields_ba as $name => $options ) :
	                $ba_design[$i]['name_id'] = $options['name_id'];
	                $ba_design[$i]['type'] = $options['type'];
	                $ba_design[$i]['label'] = $options['label'];
	                $ba_design[$i]['required_check'] = $options['required_check'];
	                $ba_design[$i]['required_shipping'] = $options['required_shipping'];
	                $ba_design[$i]['required_billing'] = $options['required_billing'];
	                if ($options['name_id'] == $id_name){
	                    if ($options['required_register'] == 1) {
	                        $ba_design[$i]['required_register'] = 0;
	                    } else {
	                        $ba_design[$i]['required_register'] = 1;
	                    }
	                } else {
	                    $ba_design[$i]['required_register'] = $options['required_register'];
	                }
	                $ba_design[$i]['required_profile'] = $options['required_profile'];
	                $ba_design[$i]['option_field'] = $options['option_field'];
	                $ba_design[$i]['position_field'] = $options['position_field'];
	                $ba_design[$i]['country_has_state'] = $options['country_has_state'];
		            $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
		            $i++;
		        endforeach;
		        $strSerialize = serialize($ba_design);
		        update_option( 'ba_design_form', $strSerialize );
		        wp_redirect('admin.php?page=form_design');
	        }
	    }
	    /*Ajax check profile*/
	    public function baform_profilecheck(){
	        if ( isset( $_GET['page'] ) && isset( $_GET['active'] ) && $_GET['active'] === 'profile_check') {
			    $id_name = isset($_GET['id_name']) ? $_GET['id_name'] : '';
		        $i=0;
		        $fields = get_option('ba_design_form', array());
		        $fields_ba = unserialize($fields);
		        foreach( $fields_ba as $name => $options ) :
		                $ba_design[$i]['name_id'] = $options['name_id'];
		                $ba_design[$i]['type'] = $options['type'];
		                $ba_design[$i]['label'] = $options['label'];
		                $ba_design[$i]['required_check'] = $options['required_check'];
		                $ba_design[$i]['required_shipping'] = $options['required_shipping'];
		                $ba_design[$i]['required_billing'] = $options['required_billing'];
		                $ba_design[$i]['required_register'] = $options['required_register'];

		                if ($options['name_id'] == $id_name){
		                    if ($options['required_profile'] == 1) {
		                        $ba_design[$i]['required_profile'] = 0;
		                    } else {
		                        $ba_design[$i]['required_profile'] = 1;
		                    }
		                } else {
		                    $ba_design[$i]['required_profile'] = $options['required_profile'];
		                }
		                $ba_design[$i]['option_field'] = $options['option_field'];
		                $ba_design[$i]['position_field'] = $options['position_field'];
		                $ba_design[$i]['country_has_state'] = $options['country_has_state'];
		                $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
		            $i++;
		        endforeach;
		        $strSerialize = serialize($ba_design);
		       update_option( 'ba_design_form', $strSerialize );
		       wp_redirect('admin.php?page=form_design');
		    }
	    }
	}
	new BaForm_Functions;
?>