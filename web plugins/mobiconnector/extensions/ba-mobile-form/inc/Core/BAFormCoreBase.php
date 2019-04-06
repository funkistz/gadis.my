<?php
namespace DesignForm\Core;
use WC_Countries;
use \DesignForm\Core\BAFormCoreSetting;
use \DesignForm\Core\BAFormFunctionCallBack;
class BAFormCoreBase{
    public $setting;
    public $page = array();
    public $subpages = array();
    public $callbacks;
    public function baform_setPage(){
        $this->pages = array(
                array(
                'page_title' => 'BA Form',
                'menu_title' => 'BA Form',
                'capability' => 'manage_options',
                'menu_slug' => 'form_design',
                'callback' => array($this->callbacks, 'baform_form_design'),
                'icon_url' => 'dashicons-editor-expand',
                'position' => 35,
            )
        );
    }
    public function baform_setAddnewField(){
        $this->subpages = array(
            array(
                'parent_slug' => 'form_design',
                'page_title' => 'Add Fields',
                'menu_title' => 'Add Fields',
                'capability' => 'manage_options',
                'menu_slug' => 'add_field',
                'callback' => array($this->callbacks, 'baform_add_field_func'),
            ),
             array(
                'parent_slug' => 'form_design',
                'page_title' => 'Settings',
                'menu_title' => 'Settings',
                'capability' => 'manage_options',
                'menu_slug' => 'setting_field',
                'callback' => array($this->callbacks, 'baform_setting_field_func'),
            ),
        );
    }
    public function baform_register_menu(){
        $this->setting = new BAFormCoreSetting();
        $this->callbacks = new BAFormFunctionCallBack();
        $this->baform_setPage();
        $this->baform_setAddnewField();
        $this->baform_setSetting();
        $this->baform_setSection();
        $this->baform_setField();
        $this->setting->AddPage($this->pages)->addSubadmin($this->subpages)->baform_register();
        $option_enable = get_option('baform_option_name');
        if (isset($option_enable) && !empty($option_enable['baform_enable_field'])){
        
        } else {
            add_filter( 'woocommerce_billing_fields', array($this,'baform_checkout_fields'), 99, 1);
            add_filter( 'woocommerce_checkout_fields', array($this,'baform_checkout_fields'), 99, 1);
            add_filter( 'woocommerce_shipping_fields', array($this,'baform_shipping_checkout_fields'), 13, 1);
        }

        add_filter('woocommerce_get_country_locale', array($this,'baform_get_country_locale'),10,1);
        add_filter('woocommerce_get_country_locale_default', array($this,'baform_prepare_country_locale'),10,1);
        add_filter('woocommerce_get_country_locale_base', array($this,'baform_prepare_country_locale'),10,1);
        
 /*Billing order*/
        add_filter( 'woocommerce_order_formatted_billing_address',array($this, 'baform_formatted_billing_address'),11,2 );
        add_filter( 'woocommerce_order_formatted_shipping_address',array($this, 'baform_formatted_shipping_address'),11,2 );

        add_filter( 'woocommerce_formatted_address_replacements',array($this,'baform_add_new_replacement_fields'),12,2 );
        add_filter( 'woocommerce_localisation_address_formats' , array($this,'baform_includes_address_formats'), 20, 1);


        add_action('plugins_loaded', array($this, 'baform_add_field_design'), 10, 1);
        add_action('plugins_loaded', array($this, 'baform_edit_field_design'));
        add_action('plugins_loaded', array($this, 'baform_delete_field_design'));
       add_action('plugins_loaded', array($this, 'baform_save_change_form_dessign'));
        add_filter('baform_fields_ba', array($this,'baform_run_filter'), 10, 1 );
    }


 private function baform_unset_field(){
    $locale_fields = array(
        'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2','billing_city','billing_state','billing_postcode','billing_email','billing_phone','billing_first_name',
        );
    $fieldss = get_option('ba_design_form');
    $option_fields = unserialize($fieldss);
    foreach ($option_fields as $key => $option_fl) {
        $array_list[]  = $option_fl['name_id'];
    }
    $result = array_diff($array_list, $locale_fields);
    return $result;
}
private function baform_unset_field_shipping(){
    $locale_fields = array(
        'shipping_last_name', 'shipping_company', 'shipping_country', 'shipping_address_1', 'shipping_address_2','shipping_city','shipping_state','shipping_postcode','shipping_email','shipping_phone','shipping_first_name',
        );
    $fieldss = get_option('ba_design_form');
    $option_fields = unserialize($fieldss);
    foreach ($option_fields as $key => $option_fl) {
        $array_list[]  = str_replace("billing_", "shipping_", $option_fl['name_id']);
    }
    $result = array_diff($array_list, $locale_fields);
    return $result;
}
/*Add field to Shipping*/
    public  function baform_shipping_checkout_fields($fields){
        $fieldss = get_option('ba_design_form');
        $option_fields = unserialize($fieldss);
        $keyxx = 70;
            if(is_array($option_fields)){
                foreach ($option_fields as $key => $value) {
               // $fields[$option_field]['priority'] = $keyxx;
                $shipping = $value['name_id'];
                $option_field = str_replace("billing_","shipping_",$shipping);
                $states_check = str_replace("billing_","shipping_",$value['country_has_state']);
                $option_fields = $value['option_field'];
                $array_option = unserialize($option_fields);
                if (is_array($array_option) || is_object($array_option)){
                    foreach($array_option as $option_f){ 
                        $option_fl[$option_f] = $option_f; 
                    }
                } else {
                    $option_fl = array();
                }
                if ($value['required_shipping'] == 1){
                    $fields[$option_field]['priority'] = $keyxx;
                    $fields[$option_field]['label'] = $value['label'];
                    $fields[$option_field]['label_class'] = $value['country_belong_to'];
                    $fields[$option_field]['required'] = $value['required_check'];
                    if ($value['type'] === 'country' ) {
                        $fields[$option_field]['class'] = array($value['name_id']);
                        $fields[$option_field]['custom_attributes'] = array( 'data-plugin' => 'select2', 'data-allow-clear' => 'true', 'aria-hidden' => 'true',  ); 
                        
                    } elseif($value['type'] === 'state'){
                        $fields[$option_field]['label_class'] = array($value['country_belong_to']);
                        $fields[$option_field]['custom_attributes'] = array( 'data-plugin' => 'select2', 'data-allow-clear' => 'true', 'aria-hidden' => 'true',  );
                    }
                     else {
                        $fields[$option_field]['input_class'] = array($value['type']);
                    }
                    
                    $fields[$option_field]['class'] = array($value['position_field']);
                    $mydateoptions = array('' => __('Select PickupDate', 'ba-mobile-form' ));
                    if($value['type'] === 'ba-date') {
                         $fields[$option_field]['type'] = 'text'; 
                    } elseif($value['type'] === 'country' && ($value['name_id'] !== 'billing_country')){
                        $fields[$option_field]['type'] = 'bacountry'; 
                        $fields[$option_field]['label_class'] = array($states_check);
                    }elseif($value['type'] === 'state' && ($value['name_id'] !== 'billing_state')){
                        $fields[$option_field]['type'] = 'bastate'; 
                    } else {
                        $fields[$option_field]['type'] = $value['type']; 
                    }
                    
                    if ($value['type'] === "select" || ($value['type'] === "radio") || ($value['type'] === "multiselect") || ($value['type'] === "multicheckbox")){
                        $fields[$option_field]['options'] = $option_fl;
                    } elseif($value['type'] === "ba-date"){
                        $fields[$option_field]['options'] = $mydateoptions;
                    }else {
                        $fields[$option_field]['options'] = array();
                    }


                    if ($value['type'] === "email" || $value['type'] === "date" || $value['type'] === "number" || $value['type'] === "tel"){
                        $fields[$option_field]['validate'] = array($value['type']);
                    } else {
                        $fields[$option_field]['validate'] = array();
                    }
                    
                     // $fields[$option_field] =array(
                    //     'type'              => $value['type'],
                    //     'label'             => $value['label'],
                    //     'description'       => '',
                    //     'placeholder'       => '',
                    //     'required'          => $value['required_check'],
                    //     'label_class'       => array(),
                    //     'input_class'       => array(),
                    //     'options'           => array("test" => "Test", "oke" => "OKE"),
                    //     'custom_attributes' => array(),
                    //     'validate'          => array(),
                    // );
                    // switch ($value['name_id']) {
                    //     case 'billing_first_name':
                    //         $fields[$option_field]['class'] = array('form-row form-row-first');
                    //         break;
                    //     case 'billing_last_name':
                    //         $fields[$option_field]['class'] = array('form-row form-row-last');
                    //         break;
                    //     default:
                    //         $fields[$option_field]['class'] = array('form-row form-row-wide');
                    // }
                } else {
                    unset($fields[$option_field]);
                }
                $keyxx++;
            }
            }
        return $fields;
    }

    /*Reset form field billing*/
     public function baform_checkout_fields( $fields ) {
        $fieldss = get_option('ba_design_form');
        $option_fields = unserialize($fieldss);
        
        $keyxx = 10;
            if(is_array($option_fields)){
                foreach ($option_fields as $key => $value) {
               // $fields[$option_field]['priority'] = $keyxx;
                $option_field = $value['name_id'];
                $option_fields = $value['option_field'];
                if (isset($value['country_has_state']) && (!empty($value['country_has_state']))){
                     $states_check = $value['country_has_state'];
                 } else {
                     $states_check = "";
                 }
               
                $array_option = unserialize($option_fields);
                if (is_array($array_option) || is_object($array_option)){
                    foreach($array_option as $option_f){ 
                        $option_fl[$option_f] = $option_f; 
                    }
                } else {
                    $option_fl = array();
                }
                if ($value['required_billing'] == 1){
                    $fields[$option_field]['priority'] = $keyxx;
                    $fields[$option_field]['label'] = $value['label'];
                    $fields[$option_field]['required'] = $value['required_check'];
                    $fields[$option_field]['class'] = array($value['position_field']);
                    $fields[$option_field]['input_class'] = array($value['type']);
                    $mydateoptions = array('' => __('Select PickupDate', 'ba-mobile-form' ));
                    if($value['type'] === 'ba-date' ) {
                         $fields[$option_field]['type'] = 'text'; 
                     }  elseif($value['type'] === 'country' && ($value['name_id'] !== 'billing_country')){
                        $fields[$option_field]['type'] = 'bacountry';
                        $fields[$option_field]['label_class'] = array($states_check); 
                    }elseif($value['type'] === 'state' && ($value['name_id'] !== 'billing_state')){
                        $fields[$option_field]['type'] = 'bastate'; 
                    } else {
                        $fields[$option_field]['type'] = $value['type']; 
                    }
                    
                    if ($value['type'] === "select" || ($value['type'] === "radio") || ($value['type'] === "multiselect") || ($value['type'] === "multicheckbox")){
                        $fields[$option_field]['options'] = $option_fl;
                    } elseif($value['type'] === "ba-date"){
                        $fields[$option_field]['options'] = $mydateoptions;
                    }else {
                        $fields[$option_field]['options'] = array();
                    }


                    if ($value['type'] === "email" || $value['type'] === "date" || $value['type'] === "number" || $value['type'] === "tel"){
                        $fields[$option_field]['validate'] = array($value['type']);
                    } else {
                        $fields[$option_field]['validate'] = array();
                    }
                    
                     // $fields[$option_field] =array(
                    //     'type'              => $value['type'],
                    //     'label'             => $value['label'],
                    //     'description'       => '',
                    //     'placeholder'       => '',
                    //     'required'          => $value['required_check'],
                    //     'label_class'       => array(),
                    //     'input_class'       => array(),
                    //     'options'           => array("test" => "Test", "oke" => "OKE"),
                    //     'custom_attributes' => array(),
                    //     'validate'          => array(),
                    // );
                    // switch ($value['name_id']) {
                    //     case 'billing_first_name':
                    //         $fields[$option_field]['class'] = array('form-row form-row-first');
                    //         break;
                    //     case 'billing_last_name':
                    //         $fields[$option_field]['class'] = array('form-row form-row-last');
                    //         break;
                    //     default:
                    //         $fields[$option_field]['class'] = array('form-row form-row-wide');
                    // }
                } else {
                    unset($fields[$option_field]);
                }
                $keyxx++;
            }
            }

        return $fields;
    }

 /*Billing order*/
    public function baform_add_new_replacement_fields( $replacements, $address ) {
        
        foreach ($this->baform_unset_field() as $key => $val) {
            $fi = str_replace('billing_', '', $val);
            $replacements['{'.$fi.'}'] = isset($address[$fi]) ? $address[$fi] : '';
        }
        return $replacements;
    }
    /*Check */
    
    public function baform_formatted_billing_address( $fields, $order){
            $text_String ="";
            $fields_x = array();
            foreach ($this->baform_unset_field() as $key => $val) {
                $fi = str_replace('billing_', '', $val);
                $meta_field =  get_post_meta( $order->get_id(), '_'.$val, true );
                if (isset( $meta_field)){
                     if (is_array($meta_field) && isset($meta_field)){
                        foreach ($meta_field as $key => $fieldsx) {
                           $text_String .= $fieldsx.", ";
                       }
                       $meta_fields = trim($text_String,',');
                        $fields_x[$fi] = $meta_fields;
                    } else {
                        if (isset($meta_field)) {
                             $fields_x[$fi] = $meta_field;
                        }
                      
                    }
                }   
            }

            $fieldssx = array(
            'first_name'    => $order->get_billing_first_name(),
            'last_name'     => $order->get_billing_last_name(),
            'company'       => $order->get_billing_company(),
            'address_1'     => $order->get_billing_address_1(),
            'address_2'     => $order->get_billing_address_2(),
            'city'          => $order->get_billing_city(),
            'state'         => $order->get_billing_state(),
            'postcode'      => $order->get_billing_postcode(),
            'country'       => $order->get_billing_country(),
            );
            $fields = array_merge($fields_x,$fieldssx);
            return $fields;
    }
    public function baform_formatted_shipping_address( $fields, $order){
            $text_String ="";
            $fields_x = array();
            foreach ($this->baform_unset_field_shipping() as $key => $val) {
                $fi = str_replace('shipping_', '', $val);
                $meta_field =  get_post_meta( $order->get_id(), '_'.$val, true );
                if (isset( $meta_field)){
                    if (is_array($meta_field)){
                        foreach ($meta_field as $key => $fieldsx) {
                           $text_String .= $fieldsx.", ";
                       }
                       $meta_fields = trim($text_String,',');
                        $fields_x[$fi] = $meta_fields;
                        
                    } else {
                       $fields_x[$fi] = $meta_field;
                    }
                }
            }

            $fieldssx = array(
            'first_name'    => $order->get_shipping_first_name(),
            'last_name'     => $order->get_shipping_last_name(),
            'company'       => $order->get_shipping_company(),
            'address_1'     => $order->get_shipping_address_1(),
            'address_2'     => $order->get_shipping_address_2(),
            'city'          => $order->get_shipping_city(),
            'state'         => $order->get_shipping_state(),
            'postcode'      => $order->get_shipping_postcode(),
            'country'       => $order->get_shipping_country(),
            );
            $fields = array_merge($fields_x,$fieldssx);
        return $fields;
    }  
    public function baform_includes_address_formats($address_formats) {
        $string_show='';
        foreach ($this->baform_unset_field() as $key => $val) {
            $fi = str_replace('billing_', '', $val);
            $string_show .= "\n{".$fi."}";
        }
        $address_formats = array(
            'default' => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}",
            'AU'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city} {state} {postcode}\n{country}",
            'AT'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'BE'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'CA'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city} {state} {postcode}\n{country}",
            'CH'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'CL'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{state}\n{postcode} {city}\n{country}",
            'CN'      => "{country} {postcode}\n{state}, {city}, {address_2}, {address_1}\n{name}".$string_show."\n{company}\n{name}",
            'CZ'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'DE'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'EE'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'FI'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'DK'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'FR'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city_upper}\n{country}",
            'HK'      => "{name}".$string_show."\n{company}\n{first_name} {last_name_upper}\n{address_1}\n{address_2}\n{city_upper}\n{state_upper}\n{country}",
            'HU'      => "{name}".$string_show."\n{company}\n{city}\n{address_1}\n{address_2}\n{postcode}\n{country}",
            'IN'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city} - {postcode}\n{state}, {country}",
            'IS'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'IT'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode}\n{city}\n{state_upper}\n{country}",
            'JP'      => "{postcode}\n{state} {city} {address_1}\n{address_2}\n{name}".$string_show."\n{company}\n{last_name} {first_name}\n{country}",
            'TW'      => "{name}".$string_show."\n{company}\n{last_name} {first_name}\n{address_1}\n{address_2}\n{state}, {city} {postcode}\n{country}",
            'LI'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'NL'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'NZ'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city} {postcode}\n{country}",
            'NO'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'PL'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'PT'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'SK'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'SI'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'ES'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}",
            'SE'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}",
            'TR'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{postcode} {city} {state}\n{country}",
            'US'      => "{name}".$string_show."\n{company}\n{address_1}\n{address_2}\n{city}, {state_code} {postcode}\n{country}",
            'VN'      => "{name}".$string_show."\n{company}\n{address_1}\n{city}\n{country}",
            );

        return $address_formats;
    }
    public function baform_get_country_locale($locale) {
        if(is_array($locale)){
            foreach($locale as $country => $fields){
                $locale[$country] = $this->baform_prepare_country_locale($fields);
            }
        }
        return $locale;
    }
    public function baform_prepare_country_locale($fields) {
        if(is_array($fields)){
            foreach($fields as $key => $props){
                $override_ph = apply_filters('ngothoai_field_override_placeholder', true);
                $override_label = apply_filters('ngothoai_address_field_override_label', true);
                $override_required = apply_filters('ngothoai_address_field_override_required', false);
                $override_priority = apply_filters('ngothoai_address_field_override_priority', true);
                
                if($override_ph && isset($props['placeholder'])){
                    unset($fields[$key]['placeholder']);
                }
                if($override_label && isset($props['label'])){
                    unset($fields[$key]['label']);
                }
                if($override_required && isset($props['required'])){
                    unset($fields[$key]['required']);
                }
                
                if($override_priority && isset($props['priority'])){
                    unset($fields[$key]['priority']);
                }
            }
        }
        return $fields;
    }

   
    /*Chay filter*/
    public function baform_run_filter($fields_ba){
        return $fields_ba;
    }
    public function baform_notices_check($baform_notices_check){
        $baform_notices_check =  '<div id="message" class="error below-h2"><p>'.__('This id field is exist.').'</p></div>';
        echo  $baform_notices_check;
    }
    public function baform_notices_check_oke($baform_notices_check){
        $baform_notices_check =  '<div id="message" class="updated notice notice-success"><p>'.__('This field is added.').'</p></div>';
        echo  $baform_notices_check;
    }
     public function baform_notices_check_edit_oke(){
        $baform_notices_check =  '<div id="message" class="updated notice notice-success"><p>'.__('This field is updated.').'</p></div>';
        echo  $baform_notices_check;
    }
    /*Notice save full successfull*/
    public function baform_notices_save_oke($baform_notices_check){
        $baform_notices_check =  '<div id="message" class="updated notice notice-success"><p>'.__('Successfully saved.').'</p></div>';
        echo  $baform_notices_check;
    }
    
    public function baform_add_field_design($fields_ba){
        $fields = get_option('ba_design_form', array());
        $action = isset($_POST['action']) ? $_POST['action']  : '';
        $res_page = isset($_REQUEST['page']) ? $_REQUEST['page']  : '';
        if ( ! empty( $_POST )  &&  ! empty( $_POST['_wpnonce'] ) && ($action === 'add') && ($res_page ==='add_field') && !isset($_GET['register'])) {
            $name_id = !empty($_POST['name_id']) ?  ( $_POST['name_id'] ) : '';
            $type_design = !empty($_POST['type_design']) ?  ( $_POST['type_design'] ) : 'text';
            $label_design = !empty($_POST['label_ngothoai']) ?  ( $_POST['label_ngothoai'] ) : '';
            $required_check = !empty($_POST['required_check']) ?  absint( $_POST['required_check'] ) : 0;
            $required_billing = !empty($_POST['required_billing']) ?  absint( $_POST['required_billing'] ) : 0;
            $required_shipping = !empty($_POST['required_shipping']) ?  absint( $_POST['required_shipping'] ) : 0;
            $required_register = !empty($_POST['required_register']) ?  absint( $_POST['required_register'] ) : 0;
            $required_profile = !empty($_POST['required_profile']) ?  absint( $_POST['required_profile'] ) : 0;
            $position_field = !empty($_POST['position_field']) ?  ( $_POST['position_field'] ) : "form-row-wide";
            $country_belong_to = !empty($_POST['country_belong_to']) ?  ( $_POST['country_belong_to'] ) : "";
            $country_has_state = isset($_POST['country_has_state']) ?  ( $_POST['country_has_state'] ) : '';
            $option_field = isset($_POST['inputdata']) ?  ( $_POST['inputdata'] ) : "";
            $explore_option_field = explode(",",$option_field);
            $ser_option_field = serialize($explore_option_field);
            if($type_design === 'country'){
                $id_add = 'billing_country_'.$name_id;
            } else {
                $id_add = 'billing_'.$name_id;
            }
            if($type_design==='state'){
                $required_check = 0;
            } else {
                $required_check = $required_check;
            }
            $add_new = array(
                'name_id' => $id_add,
                'type'=> $type_design,
                'label' => $label_design,
                'required_check' => $required_check,
                'required_billing' => $required_billing,
                'required_shipping' => $required_shipping,
                'required_register' => $required_register,
                'required_profile' => $required_profile,
                'position_field' => $position_field,
                'option_field' => $ser_option_field,
                'country_belong_to' => $country_belong_to,
                'country_has_state' => $country_has_state,
            );
            $fields_ba = unserialize($fields);
            $key = $this->baform_searchForId($id_add, $fields_ba);
            session_start(); 
            if(isset($key)){
                add_action( 'ba_design_tices', array($this,'baform_notices_check'), 10, 1 );
                $this->_cheng_redirect('admin.php?page=add_field','This id name field is exist.','error');
            } else {
                array_push($fields_ba,$add_new);
                /*Tao filter*/
                $fields_ba = apply_filters('baform_fields_ba', $fields_ba);
                update_option('ba_design_form',serialize($fields_ba));
               $this->_cheng_redirect('admin.php?page=add_field&edit='.$id_add,'This field is added.','success');
            }
        } else {

        }
    }
    public function baform_edit_field_design(){
        if ( ! empty( $_POST )  &&  ! empty( $_POST['_wpnonce'] ) && ! empty( $_REQUEST['edit'] )) {
            $name_base = !empty($_POST['name_base']) ?  ( $_POST['name_base'] ) : '';
            $name_id = !empty($_POST['name_id']) ?  ( $_POST['name_id'] ) : $name_base;
            $check_country = $_REQUEST['edit'];
            $type_design = !empty($_POST['type_design']) ?  ( $_POST['type_design'] ) : 'text';
            $label_design = !empty($_POST['label_ngothoai']) ?  ( $_POST['label_ngothoai'] ) : '';
            $required_check = isset($_POST['required_check']) ?  absint( $_POST['required_check'] ) : 0;
            $required_billing = isset($_POST['required_billing']) ?  absint( $_POST['required_billing'] ) : 0;
            $required_shipping = isset($_POST['required_shipping']) ?  absint( $_POST['required_shipping'] ) : 0;
            $required_register = !empty($_POST['required_register']) ?  absint( $_POST['required_register'] ) : 0;
            $required_profile = !empty($_POST['required_profile']) ?  absint( $_POST['required_profile'] ) : 0;
            $position_field = isset($_POST['position_field']) ?  ( $_POST['position_field'] ) : 'form-row-wide';
            $country_belong_to = isset($_POST['country_belong_to']) ?  ( $_POST['country_belong_to'] ) : '';
            $country_has_state = isset($_POST['country_has_state']) ?  ( $_POST['country_has_state'] ) : '';
            $option_field = isset($_POST['inputdata']) ?  ( $_POST['inputdata'] ) : "";
            $explore_option_field = explode(",",$option_field);
            $ser_option_field = serialize($explore_option_field);
            $fields = get_option('ba_design_form', array());
            $fields_ba = unserialize($fields);
            if(($type_design === 'country') && ($name_id !=='country')){
                if ((strpos($check_country, 'billing_country_') !== false) ) {
                    if(strpos($name_id, 'country_')!== false){
                        $id_add = 'billing_'.$name_id;
                    } else {
                        $id_add = 'billing_country_'.$name_id;
                    }
                    
                } else {
                    $id_add = 'billing_country_'.$name_id;
                }
                
            } else {
                $id_add = 'billing_'.$name_id;
            }

            if($type_design==='state'){
                $required_check = 0;
            } else {
                $required_check = $required_check;
            }
           $i=0;
            foreach( $fields_ba as $name => $options ) :
                if ($options['name_id'] == $_REQUEST['edit']){
                    $ba_design[$i]['name_id'] = $id_add;
                    $ba_design[$i]['type'] = $type_design;
                    $ba_design[$i]['label'] = $label_design;
                    $ba_design[$i]['position_field'] = $position_field;
                    $ba_design[$i]['required_check'] = $required_check;
                    $ba_design[$i]['required_billing'] = $required_billing;
                    $ba_design[$i]['required_shipping'] = $required_shipping;
                    $ba_design[$i]['required_register'] = $required_register;
                    $ba_design[$i]['required_profile'] = $required_profile;
                    $ba_design[$i]['option_field'] = $ser_option_field;
                    $ba_design[$i]['country_belong_to'] = $country_belong_to;
                    $ba_design[$i]['country_has_state'] = $country_has_state;
                } else {
                    $ba_design[$i]['name_id'] = $options['name_id'];
                    $ba_design[$i]['type'] = $options['type'];
                    $ba_design[$i]['label'] = $options['label'];
                    $ba_design[$i]['position_field'] = $options['position_field'];
                    $ba_design[$i]['required_check'] = $options['required_check'];
                    $ba_design[$i]['required_billing'] = $options['required_billing'];
                    $ba_design[$i]['required_shipping'] = $options['required_shipping'];
                    $ba_design[$i]['required_register'] = $options['required_register'];
                    $ba_design[$i]['required_profile'] = $options['required_profile'];
                    $ba_design[$i]['option_field'] = $options['option_field'];
                    $ba_design[$i]['country_belong_to'] = $options['country_belong_to'];
                    $ba_design[$i]['country_has_state'] = $options['country_has_state'];
                }
                $i++;
            endforeach;
            
            // $strSerialize = serialize($ba_design);
            // $result = update_option( 'ba_design_form', $strSerialize );
            session_start();
            // $_SESSION["edit-form"] = '<div id="message" class="updated notice notice-success"><p>'.__('This field is updated.','ba-mobile-form').'</p></div>';
            
            foreach ($fields_ba as $name => $option_check) {
                $list_field[] = $option_check['name_id'];
            }
            if (in_array($id_add,$list_field)){
                $check_edit = 1;
            } else {
                $check_edit = 0;
            }
            $key = $this->baform_searchForId($id_add, $fields_ba);
            if(isset($key) && ($_REQUEST['edit'] !== $id_add)){
                add_action( 'ba_design_notices', array($this,'baform_notices_check'), 10, 1 );
                $this->_cheng_redirect('admin.php?page=add_field&edit='.$_REQUEST['edit'],'This id name field is exist.','error');
            } else {
                $strSerialize = serialize($ba_design);
                $result = update_option( 'ba_design_form', $strSerialize );
                add_action( 'ba_design_notices', array($this,'baform_notices_check_edit_oke'), 10, 1 );
                $this->_cheng_redirect('admin.php?page=add_field&edit='.$id_add,'This field is updated.','success');
                
            }  

        }
    }
    public function _cheng_redirect($url=NULL, $message=NULL, $message_type=NULL){
        $_SESSION['cheng_flash_message']= array();
        if($message){
              switch($message_type){ 
                case 'success': $_SESSION['cheng_flash_message'][] = '<div id="message" class="updated notice notice-success"><p>'.$message.'</p></div>';break;
                case 'error': $_SESSION['cheng_flash_message'][] = '<div id="message" class="updated notice error"><p>'.$message.'</p></div>';break;
                case 'notice': $_SESSION['cheng_flash_message'][] = '<div id="message" class="updated notice notice-success"><p>'.$message.'</p></div>';break;
                case 'warning': $_SESSION['cheng_flash_message'][] = '<div id="message" class="updated notice notice-warning"><p>'.$message.'</p></div>';break;
                default: $_SESSION['cheng_flash_message'][] = $message;
              }
          }
        if($url) {
            header("Location: ".$url);
        } else {
            header("Location: ".$_SERVER['HTTP_REFERER']);
        }
         exit();
         ob_flush();    
    }
    public function baform_searchForId($id, $array) {
       foreach ($array as $key => $val) {
           if ($val['name_id'] === $id) {
               return $key;
           }
       }
       return null;
    }

    public function baform_delete_field_design(){
        $fields = get_option('ba_design_form', array());
        if ( isset( $_GET['name_id'] ) && ( $_GET['action'] === 'delete' )){
            $fields_ba = unserialize($fields);
            $keyxx = $this->baform_searchForId($_GET['name_id'], $fields_ba);
            unset($fields_ba[$keyxx]);
            wp_redirect('admin.php?page=form_design');
            $strSerialize = serialize($fields_ba);
            update_option( 'ba_design_form', $strSerialize );
        } else {
          
        }
    }
    public function baform_save_change_form_dessign(){
        $res_page = isset($_GET['page']) ? $_GET['page']  : '';
        if ( isset( $_POST['name_id'] ) && ( $_POST['_wpnonce'] ) && ($res_page === 'form_design') && (!isset($_GET['register']))){
            $name_id = isset( $_POST['name_id'] ) ? $_POST['name_id'] : '';
            $type = isset( $_POST['type'] ) ? $_POST['type'] : 'text';
            $label = isset( $_POST['label'] ) ? $_POST['label'] : '';
            $required_check = isset( $_POST['required_check'] ) ? $_POST['required_check'] : 0;
            $required_shipping = isset( $_POST['required_shipping'] ) ? $_POST['required_shipping'] : 0;
            $required_billing = isset( $_POST['required_billing'] ) ? $_POST['required_billing'] : 0;
            $required_register = isset( $_POST['required_register'] ) ? $_POST['required_register'] : 0;
            $required_profile = isset( $_POST['required_profile'] ) ? $_POST['required_profile'] : 0;
            $position_field = !empty($_POST['position_field']) ?  ( $_POST['position_field'] ) : "";
            $option_field = !empty($_POST['option_field']) ?  ( $_POST['option_field'] ) : "";
            $country_belong_to = !empty($_POST['country_belong_to']) ?  ( $_POST['country_belong_to'] ) : "";
            $country_has_state = isset($_POST['country_has_state']) ?  ( $_POST['country_has_state'] ) : '';
            $count_item = count( $name_id );
            for ( $i = 0; $i < $count_item; $i++ ) {
                $ba_design[$i]['name_id'] = $name_id[$i];
                $type[$i] = !empty( $_POST['type'][$i] ) ? $_POST['type'][$i] : 'text';
                $ba_design[$i]['type'] = $type[$i];
                $ba_design[$i]['label'] = $label[$i];
                $ba_design[$i]['required_check'] = $required_check[$i];
                $ba_design[$i]['required_billing'] =  $required_shipping[$i];
                $ba_design[$i]['required_shipping'] = $required_billing[$i];
                $ba_design[$i]['required_register'] = $required_register[$i];
                $ba_design[$i]['required_profile'] = $required_profile[$i];
                $ba_design[$i]['position_field'] = $position_field[$i];
                $ba_design[$i]['option_field'] = $option_field[$i];
                $ba_design[$i]['country_belong_to'] = $country_belong_to[$i];
                $ba_design[$i]['country_has_state'] = $country_has_state[$i];
            }
            $strSerialize = serialize($ba_design);
            $fields = get_option('ba_design_form', array());
            if ( !empty( $strSerialize ) && $strSerialize != $fields ){
                update_option( 'ba_design_form', $strSerialize );
            } 
            add_action( 'ba_design_notices', array($this,'baform_notices_save_oke'), 10, 1 );
        } 
    }

        /*Section group*/
     public  function baform_setSetting(){
        $args = array(
            array(
                'option_group' => 'baform_option_group',
                'option_name' => 'baform_option_name', /*id input*/
                'callback' => array($this->callbacks,'baform_OptionGroup')
            ),
        );
        $this->setting->baform_setSetting($args);
     }

    /*Field setup option*/
    public  function baform_setSection(){
        $args = array(
            array(
                'id' => 'baform_enable',
                'title' => '',
                'callback' => array($this->callbacks,'option_section_func'),
                'page' => 'setting_field',
            )
        );
        $this->setting->baform_setSection($args);
     }
     /*Field option*/
    public  function baform_setField(){
        $args = array(
            array(
                'id' => 'baform_enable_field',
                'title' => __('Only work on a mobile','ba-mobile-form'),
                'callback' => array( $this->callbacks, 'baform_setup_field'),
                'page' => 'setting_field',
                'section' => 'baform_enable',
                 'args' => array(
                    'label_for' => 'baform_setup_field',
                    'class' => 'baform_setup_field'
                )
            ),
        );
        $this->setting->baform_setField($args);
     }
}
?>