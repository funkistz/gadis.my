<?php
namespace DesignForm\Base;
    class EnQueue{
        public function baform_frontend_enqueue(){
            wp_enqueue_script( 'jquery-ui-sortable');
            wp_enqueue_script( 'jquery-ui-datepicker');
            wp_enqueue_style( 'style-ngothoai-frontend', plugins_url( '/assets/css/ngothoai-style.css', __FILE__ ), array(),true,false );
            wp_enqueue_style('css_picker',plugins_url('/assets/css/jquery-ui.css',__FILE__),array(),true,false);
        }	
        public function baform_admin_enqueue(){
            wp_enqueue_style('css_picker',plugins_url('/assets/css/jquery-ui.css',__FILE__),array(),true,false);
        	wp_enqueue_style( 'style-design', plugins_url( '/assets/css/style.css', __FILE__ ), array(),true,false );
            wp_enqueue_script( 'ba-editor-admin', plugins_url( '/assets/js/main.js', __FILE__ ),array(),false,true);
        	
        	wp_enqueue_script( 'jquery-ui-sortable');
            wp_enqueue_script( 'jquery-ui-datepicker');
            if ( !is_product() ){
                wp_register_style( 'select2css',plugins_url('/assets/css/select2.css',__FILE__), false, '1.0', 'all' );
                wp_register_script( 'select2', plugins_url('/assets/css/select2.js',__FILE__), array( 'jquery' ), '1.0', true );
                wp_enqueue_style( 'select2css' );
                wp_enqueue_script( 'select2' );
            }
            $pattern = array(
                    //day
                    'd',        //day of the month
                    'j',        //3 letter name of the day
                    'l',        //full name of the day
                    'z',        //day of the year
                    'S',
                    //month
                    'F',        //Month name full
                    'M',        //Month name short
                    'n',        //numeric month no leading zeros
                    'm',        //numeric month leading zeros
                    //year
                    'Y',        //full numeric year
                    'y'     //numeric year: 2 digit
                );
                $replace = array(
                    'dd','d','DD','o','',
                    'MM','M','m','mm',
                    'yy','y'
                );
                foreach( $pattern as &$p ) {
                    $p = '/' . $p . '/';
                }
                wp_localize_script( 'ba-editor-admin', 'ba_date_fields_params', array(
                    'date_format' => preg_replace( $pattern, $replace, wc_date_format() )
                ) );
                wp_enqueue_script( 'ba-editor-admin' );
                wp_enqueue_script( 'admin-country-state', plugins_url( '/assets/js/country-state.js', __FILE__ ),array(),true,false);
        }
        public function baform_checkout_fields_scripts() {
            global $wp_scripts;
            if ( is_checkout() || is_account_page() ) {
               

                $pattern = array(
                    //day
                    'd',        //day of the month
                    'j',        //3 letter name of the day
                    'l',        //full name of the day
                    'z',        //day of the year
                    'S',
                    //month
                    'F',        //Month name full
                    'M',        //Month name short
                    'n',        //numeric month no leading zeros
                    'm',        //numeric month leading zeros
                    //year
                    'Y',        //full numeric year
                    'y'     //numeric year: 2 digit
                );
                $replace = array(
                    'dd','d','DD','o','',
                    'MM','M','m','mm',
                    'yy','y'
                );
                foreach( $pattern as &$p ) {
                    $p = '/' . $p . '/';
                }
                wp_enqueue_script( 'ba-editor-frontend', plugins_url( '/assets/js/ngothoai-frontend.js', __FILE__ ), array(),false,true );
                wp_enqueue_script( 'country-state', plugins_url( '/assets/js/country-state.js', __FILE__ ),array(),true,false);
                wp_localize_script( 'ba-editor-frontend', 'ba_checkout_fields_params', array(
                    'date_format' => preg_replace( $pattern, $replace, wc_date_format() )
                ) );
            }
        }
        public function checkrequired_ajax(){
            echo '<script type="text/javascript">var checkrequired_ajax = \'' . admin_url('admin-ajax.php') . '\';</script>';
        }

         public function baform_register_enqueue(){
            add_action('admin_enqueue_scripts', array($this,'baform_admin_enqueue'));
            add_action('admin_enqueue_scripts', array($this,'checkrequired_ajax'));
            add_action('wp_enqueue_scripts', array($this,'baform_frontend_enqueue'));
            add_action('wp_enqueue_scripts', array($this,'baform_checkout_fields_scripts'));
        }

    }
?>