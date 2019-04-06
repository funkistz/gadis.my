<?php
namespace DesignForm\Core;
	class BAFormAddFieldCustom{
		public function baform_run_add_field_custom(){
			add_filter( 'woocommerce_form_field_radio', array($this,'baform_checkout_fields_radio_field'), 10, 4 );
			add_filter( 'woocommerce_form_field_multiselect',array($this,'baform_checkout_fields_multiselect_field'), 10, 4 );
			add_filter( 'woocommerce_form_field_multicheckbox',array($this,'baform_checkout_fields_multi_checkbox'), 10, 4 );
			add_filter( 'woocommerce_form_field_ip_address',array($this,'baform_checkout_fields_ip_address'), 10, 4 );
			add_filter( 'woocommerce_form_field_bacountry',array($this,'baform_checkout_fields_bacountry'), 12, 4 );
			add_filter( 'woocommerce_form_field_bastate',array($this,'baform_checkout_fields_bastate'), 12, 4 );
		}
		public function baform_checkout_fields_radio_field( $field = '', $key, $args, $value ) {
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			} else {
				$required = '';
			}
			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
			$field .= '<label>' . $args['label'] . $required . '</label>';
			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					if (!empty($option_text)){
						$field .= '<span><input type="radio" ' . checked( $value, esc_attr( $option_text ), false ) . ' name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_text ) . '" /> ' . esc_html( $option_text ) . '</span><br>';
					}
					
				}
			}
			$field .= '</p>' . $after;
			return $field;
		}
		public function baform_checkout_fields_multiselect_field( $field = '', $key, $args, $value ) {
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			} else {
				$required = '';
			}
			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			$options = '';
			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$options .= '<option '. selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) .'</option>';
				}
				$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
				if ( $args['label'] ) {
					$field .= '<span for="' . esc_attr( $key ) . '" class="' . implode( ' ', $args['label_class'] ) .'">' . $args['label']. $required . '</span><br>';
				}
				$class = '';
				$field .= '<select data-placeholder="' . __( 'Select some options', 'ba-mobile-form' ) . '" multiple="multiple" name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $key ) . '" class="checkout_chosen_select select wc-enhanced-select ' . $class . '">
						' . $options . '
					</select>
				</p>' . $after;
			}
			return $field;
		}
		/*Multi checkbox*/
		public function baform_checkout_fields_multi_checkbox( $field = '', $key, $args, $value ) {
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			} else {
				$required = '';
			}
			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
			$field .= '<label>' . $args['label'] . $required . '</label>';
			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$field .= '<span><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $option_text ) . '" /> ' . esc_html( $option_text ) . '</span><br>';
				}
			}
			$field .= '</p><br>' . $after;
			return $field;
		}
		public function baform_checkout_fields_ip_address($field = '', $key, $args, $value){
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			} else {
				$required = '';
			}
			$args['maxlength'] = ( $args['maxlength'] ) ? 'maxlength="' . absint( $args['maxlength'] ) . '"' : '';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
			$field .= '<label>' . $args['label'] . $required . '</label>';
			$field .= '<input class="'.$args['type'].'" type="text" name="' . esc_attr( $key ) . '" value="" /><span id="validate_ip"></span>';
			$field .= '</p>' . $after;
			return $field;
		}
		public function baform_checkout_fields_bacountry($field = '', $key, $args, $value){
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
			$field .= '<label>' . $args['label'] . $required . '</label>';
			$field .='<select id="'.$key.'" name="'.$key.'" >';
			$state = $args['label_class'][0];
			$field .='</select>';
			$field .='<script language="javascript">
			jQuery(function($){
				 $("#'.$key.'").select2();
				});
	            populateCountries("'.$key.'","'.$state.'");
	        </script>';
	        $field .= '</p>' . $after;
	        return $field;
		}
		public function baform_checkout_fields_bastate($field = '', $key, $args, $value){
			if ( ( ! empty( $args['clear'] ) ) ) $after = '<div class="clear"></div>'; else $after = '';
			$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'ba-mobile-form' ) . '">*</abbr>';
			$field = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $key ) . '_field" data-priority="'. $args['priority'].'">';
			$field .= '<label>' . $args['label'] . $required . '</label>';
			$field .='<select id="'.$key.'" name="'.$key.'">';
			$field .='</select>';
			$field .='<script language="javascript">
			jQuery(function($){
				 $("#'.$key.'").select2();
				});
	        </script>';
	        $field .= '</p>' . $after;
	        return $field;
		}
	}
?>