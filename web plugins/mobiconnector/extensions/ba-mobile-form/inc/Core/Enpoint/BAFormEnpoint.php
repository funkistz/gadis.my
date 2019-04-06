<?php
namespace DesignForm\Core\Enpoint;
	class BAFormEnpoint{
		private $rest_url = 'ba-mobile-form';
		public function baform_enpoint_run(){
			$this->baform_register_routes();
		}
		public function baform_register_routes() {
	        add_action( 'rest_api_init', array( $this, 'baform_list_data_form'));
	    }
	    public function baform_list_data_form(){
	    	register_rest_route(  $this->rest_url, '/data-form',
	            array(
	                'callback'    => array($this, 'baform_data_api'),
	                'methods'         => 'GET',
	                'args'            => array(
	                ),
	            )
	        );
	    }
	    public function baform_data_api(){
	    	$required_icon = "&nbsp<span class='required' title='required'>*</span>";
			$fields = get_option('ba_design_form');
        	$option_fields = unserialize($fields);
        	foreach ($option_fields as $key => $value) {
                    if(isset($value['country_belong_to']) && !empty($value['country_belong_to'])){
                        $country_belong_to = $value['country_belong_to'];
                    } else {
                        $country_belong_to = "";
                    }
                    if(isset($value['country_has_state']) && !empty($value['country_has_state'])){
                        $country_has_state = $value['country_has_state'];
                    } else {
                        $country_has_state = "";
                    }
                    if ($value['required_billing'] == 1) {
                        $data_billing['custom']= array();
                        $shipping = $value['name_id'];
                        if($value['required_check'] == 1){
                            $check = $required_icon;
                        } else {
                            $check = "";
                        }
                        $data['billing'][] = array(
                            'name_id' =>  $value['name_id'],
                            'type' => $value['type'],
                            'label' => $value['label']."".$check,
                            'required_check' => $value['required_check'],
                            'option_field' => unserialize($value['option_field']),
                            'cssClass' => $value['position_field'],
                            'country_belong_to' => $country_belong_to,
                            'country_has_state' => $country_has_state,
                        );
                    }
                    if ($value['required_shipping'] == 1) {
                         $data_shipping['custom'] = array();
                        $shipping = $value['name_id'];
                        $field = str_replace("billing_","shipping_",$shipping);
                        if($value['required_check'] == 1){
                            $check = $required_icon;
                        } else {
                            $check = "";
                        }
                        $data['shipping'][] = array(
                            'name_id' => $field,
                            'type' => $value['type'],
                            'label' => $value['label']."".$check,
                            'required_check' => $value['required_check'],
                            'option_field' => unserialize($value['option_field']),
                            'cssClass' => $value['position_field'],
                            'country_belong_to' => str_replace("billing_","shipping_",$country_belong_to),
                            'country_has_state' => str_replace("billing_","shipping_",$country_has_state),
                        );
                    }
                    
                    if ($value['required_register'] == 1) {
                        $shipping = $value['name_id'];
                        if($value['required_check'] == 1){
                            $check = $required_icon;
                        } else {
                            $check = "";
                        }
                        
                        $password[] = array(
                            'name_id' => $shipping,
                            'type' => $value['type'],
                            'label' => $value['label']."".$check,
                            'required_check' => $value['required_check'],
                            'option_field' => unserialize($value['option_field']),
                            'cssClass' => $value['position_field'],
                            'country_belong_to' => $country_belong_to,
                            'country_has_state' => $country_has_state,
                        );
                        $data['register'] = $password;
                    }
                    if ($value['required_profile'] == 1) {
                        if($value['name_id'] !== 'billing_user_login'){
                            $shipping = $value['name_id'];
                            $field_profile = str_replace("billing_","",$shipping);
                            
                            if($value['required_check'] == 1){
                                $check = $required_icon;
                            } else {
                                $check = "";
                            }
                            if ($field_profile === 'display_name'){
                                $type_profile = 'text';
                            } else {
                                $type_profile = $value['type'];
                            }

                            if ($field_profile ==='first_name' || $field_profile==='last_name'){
                                $value['required_check'] = 0;
                            } else {
                                $value['required_check'] = $value['required_check'];
                            }
                                        
                            $data_profile[] = array(
                                'name_id' => $shipping,
                                'type' => $type_profile,
                                'label' => $value['label']."".$check,
                                'required_check' => $value['required_check'],
                                'option_field' => unserialize($value['option_field']),
                                'cssClass' => $value['position_field'],
                                'country_belong_to' => $country_belong_to,
                                'country_has_state' => $country_has_state,
                            );
                            $data['profile'] = $data_profile;
                        }
                    }
                }
        	return $data;
	    }
	}
?>