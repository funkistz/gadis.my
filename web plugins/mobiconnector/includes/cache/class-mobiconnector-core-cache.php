<?php
/**
 * MobiConnector Core Cache
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.	
}
class BAMobileCoreCacheAPI{
    /**
     * Check expired data in database
     * 
     * @param string $key_data   Key data in database
     * 
     * @return boolean
     */
    public function bamobile_check_expired_data_in_database($key_data){
        global $wpdb;
        $table_name = $wpdb->prefix . "mobiconnector_sessions";
        $create_time = $wpdb->get_results('SELECT session_expiry FROM '.$table_name.' WHERE session_key = "'.$key_data.'"',ARRAY_N);
        if(!empty($create_time[0][0])){
            $timedb = $create_time[0][0];
            date_default_timezone_set('Etc/GMT0');
            $nowgmt = date('Y-m-d H:i:s',time());
            $timeexpiry = get_option('mobiconnector_settings-session-expiry');
            if((strtotime($nowgmt) - $timedb) > $timeexpiry){
                return false;
            }          
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check data in database
     * 
     * @param string $key_data   Key data in database
     * 
     * @return boolean
     */
    public function bamobile_check_data_in_database($key_data){
        global $wpdb;
        $table_name = $wpdb->prefix . "mobiconnector_sessions";
        $create_time = $wpdb->get_results('SELECT session_expiry FROM '.$table_name.' WHERE session_key = "'.$key_data.'"',ARRAY_N);
        if(!empty($create_time[0][0])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Add to Database data in rest
     * 
     * @param string $key_data   Key data in database
     * @param string $data       Data insert to database
     * 
     */
    public function bamobile_add_item_to_database($key_data,$data){
        global $wpdb;
        date_default_timezone_set('Etc/GMT0');
        $nowgmt = date('Y-m-d H:i:s',time());
        $nowtime = strtotime($nowgmt);
        $table_name = $wpdb->prefix . "mobiconnector_sessions";
        $wpdb->insert( 
            $table_name, 
            array( 
                'session_key'      => $key_data, 
                'session_value'    => $data,
                'session_expiry'   => $nowtime
            ), 
            array( 
                '%s', 
                '%s',
                '%s'
            ) 
        );
    }

    /**
     * Update to Database data in rest
     * 
     * @param string $key_data   Key data in database
     * @param string $data       Data insert to database
     * 
     */
    public function bamobile_update_item_to_database($key_data,$data){
        global $wpdb;
        date_default_timezone_set('Etc/GMT0');
        $nowgmt = date('Y-m-d H:i:s',time());
        $nowtime = strtotime($nowgmt);
        $table_name = $wpdb->prefix . "mobiconnector_sessions";
        $wpdb->update( 
            $table_name, 
            array( 
                'session_value'  => $data,	
                'session_expiry' => $nowtime	
            ), 
            array( 'session_key' => $key_data ), 
            array( 
                '%s',	
                '%s'	
            ), 
            array( '%s' ) 
        );
    }

    /**
     * Get data in database
     * 
     * @param string $key_data   Key data in database
     * 
     * @return string
     */
    public function bamobile_get_item_in_database($key_data){
        global $wpdb;       
        $table_name = $wpdb->prefix . "mobiconnector_sessions";
        $data = $wpdb->get_results('SELECT session_value FROM '.$table_name.' WHERE session_key = "'.$key_data.'"');
        return $data[0];
    }
}
$BAMobileCoreCacheAPI = new BAMobileCoreCacheAPI();
?>