<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add a notice.
 * 
 * @since 1.1.5
 * 
 * @param string $message The text to display in the notice.
 * @param string $notice_type Optional. The name of the notice type - either error, success or notice.
 */
function bamobile_mobiconnector_add_notice( $message, $notice_type = 'success' ) {
    $notices = mc()->session->get( 'mobiconnector_notices', array() );
    $notices[ $notice_type ][] =  $message;
    mc()->session->set( 'mobiconnector_notices', $notices );
}

/**
 * Returns all queued notices, optionally filtered by a notice type.
 * 
 * @since 1.1.5
 *
 * @param  string $notice_type Optional. The singular name of the notice type - either error, success or notice.
 * @return array|mixed
 */
function bamobile_mobiconnector_get_notices( $notice_type = '' ) {	
    $all_notices = mc()->session->get( 'mobiconnector_notices', array() );

    if ( empty( $notice_type ) ) {
        $notices = $all_notices;
    } elseif ( isset( $all_notices[ $notice_type ] ) ) {
        $notices = $all_notices[ $notice_type ];
    } else {
        $notices = array();
    }

    return $notices;
}

/**
 * Set all notices at once.
 * 
 * @since 1.1.5
 *
 * @param mixed $notices Array of notices.
 */
function bamobile_mobiconnector_set_notices( $notices ) {
    mc()->session->set( 'mobiconnector_notices', $notices );
}

/**
 * Prints messages and errors which are stored in the session, then clears them.
 * 
 * @since 1.1.5
 */
function bamobile_mobiconnector_print_notices() {

    $all_notices  = mc()->session->get( 'mobiconnector_notices', array() );   
   
    $notice_types = array('success', 'error', 'notice' , 'successonesignal', 'erroronesignal' );

    foreach ( $notice_types as $notice_type ) {
        if ( bamobile_mobiconnector_notice_count( $notice_type ) > 0 ) {
            bamobile_mobiconnector_get_template( "notices/{$notice_type}.php", array(
                'messages' => array_filter( $all_notices[ $notice_type ] ),
            ) );
        }
    }

    bamobile_mobiconnector_clear_notices();
}

/**
 * Get the count of notices added, either for all notices (default) or for one.
 * particular notice type specified by $notice_type.
 * 
 * @since 1.1.5
 *
 * @param  string $notice_type Optional. The name of the notice type - either error, success or notice.
 * @return int
 */
function bamobile_mobiconnector_notice_count( $notice_type = '' ) {
    $notice_count = 0;
    $all_notices  = mc()->session->get( 'mobiconnector_notices', array() );

    if ( isset( $all_notices[ $notice_type ] ) ) {

        $notice_count = count( $all_notices[ $notice_type ] );

    } elseif ( empty( $notice_type ) ) {

        foreach ( $all_notices as $notices ) {
            $notice_count += count( $notices );
        }
    }

    return $notice_count;
}

/**
 * Unset all notices.
 *
 * @since 1.1.5
 */
function bamobile_mobiconnector_clear_notices() {    
    mc()->session->set('mobiconnector_notices', array() );    
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Add a notice.
 * 
 * @param string $message The text to display in the notice.
 * @param string $notice_type Optional. The name of the notice type - either error, success or notice.
 */
function mobiconnector_add_notice( $message, $notice_type = 'success' ) {
    return bamobile_mobiconnector_add_notice($message, $notice_type);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Returns all queued notices, optionally filtered by a notice type.
 *
 * @param  string $notice_type Optional. The singular name of the notice type - either error, success or notice.
 * @return array|mixed
 */
function mobiconnector_get_notices( $notice_type = '' ) {	
    return bamobile_mobiconnector_get_notices($notice_type);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Set all notices at once.
 *
 * @param mixed $notices Array of notices.
 */
function mobiconnector_set_notices( $notices ) {
    return bamobile_mobiconnector_set_notices($notices);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Prints messages and errors which are stored in the session, then clears them.
 * 
 */
function mobiconnector_print_notices() {
    return bamobile_mobiconnector_print_notices();
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Get the count of notices added, either for all notices (default) or for one.
 * particular notice type specified by $notice_type.
 *
 * @param  string $notice_type Optional. The name of the notice type - either error, success or notice.
 * @return int
 */
function mobiconnector_notice_count( $notice_type = '' ) {
    return bamobile_mobiconnector_notice_count($notice_type);
}

/**
 * This function will be removed after Jan 01, 2019
 * 
 * Unset all notices.
 */
function mobiconnector_clear_notices() {   
    bamobile_mobiconnector_clear_notices();
}
?>