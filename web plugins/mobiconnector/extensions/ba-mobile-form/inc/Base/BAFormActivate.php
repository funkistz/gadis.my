<?php
	namespace DesignForm\Base;
	class BAFormActivate{
		public static function baform_activate(){
			// add_option( 'ba_design_form', '', '', 'yes' );
			// $fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(),'billing_');

			// $i=0;
			// foreach( $fields as $name => $options ) :
   //      if (isset($name)){
   //          $ba_design[$i]['name_id'] = $name;
   //      } else {
   //          $ba_design[$i]['name_id'] = "";
   //      }
   //      if (isset($options['type'])){
   //          if ($ba_design[$i]['name_id'] === 'billing_last_name' || $ba_design[$i]['name_id'] === 'billing_first_name' || $ba_design[$i]['name_id'] === 'billing_company' || $ba_design[$i]['name_id'] === 'billing_address_1' || $ba_design[$i]['name_id'] === 'billing_address_2' || $ba_design[$i]['name_id'] === 'billing_city' || $ba_design[$i]['name_id'] === 'billing_postcode'){
   //              $ba_design[$i]['type'] = 'text';
   //          } else {
   //              $ba_design[$i]['type'] = $options['type'];
   //          }
   //      } else {
   //          $ba_design[$i]['type'] = "";
   //      }
   //      if (isset($options['label'])){
   //          $ba_design[$i]['label'] = $options['label'];
   //      } else {
   //          $ba_design[$i]['label'] = "";
   //      }
   //      if (isset($options['required_check'])){
   //      $ba_design[$i]['required_check'] = $options['required'];
   //      } else {
   //          $ba_design[$i]['required_check'] = 1;
   //      }

   //      if (isset($options['required_billing'])){
   //      $ba_design[$i]['required_billing'] = 1;
   //      } else {
   //          $ba_design[$i]['required_billing'] = 1;
   //      }

   //      if (isset($options['required_shipping'])){
   //      $ba_design[$i]['required_shipping'] = 1;
   //      } else {
   //          $ba_design[$i]['required_shipping'] = 1;
   //      }

   //      if (isset($options['required_register'])){
   //      $ba_design[$i]['required_register'] = 0;
   //      } else {
   //          $ba_design[$i]['required_register'] = 0;
   //      }
        
   //      $ba_design[$i]['option_field'] = serialize("");
   //      $i++;
   //      endforeach;
   //      $strSerialize = serialize($ba_design);
   //      update_option( 'ba_design_form', $strSerialize );
		}	
	}
?>