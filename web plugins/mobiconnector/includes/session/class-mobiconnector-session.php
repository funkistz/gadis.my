<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * BAMobile Session
 */
class BAMobile_Session{

    /**
	 * Customer ID.
	 *
	 * @var int $_customer_id Customer ID.
	 */
    protected $__customer_id;

    /**
	 * Session Data.
	 *
	 * @var array $_data Data array.
	 */
    protected $_data = array();
    
    /**
	 * Dirty when the session needs saving.
	 *
	 * @var bool $_dirty When something changes
	 */
	protected $_dirty = false;

    /**
	 * Cookie name used for the session.
	 *
	 * @var string cookie name
	 */
    protected $_cookie;
    
    /**
	 * Stores session expiry.
	 *
	 * @var string session due to expire timestamp
	 */
	protected $_session_expiring;
    
    /**
     * Stores session due to expire timestamp.
     *
     * @var string session expiration timestamp
     */
    protected $_session_expiration;

    /**
     * True when the cookie exists.
     *
     * @var bool Based on whether a cookie exists.
     */
    protected $_has_cookie = false;

    /**
     * Table name for session data.
     *
     * @var string Custom session table name
     */
    protected $_table;

    /**
	 * Get a session variable.
	 *
	 * @param string $key Key to get.
	 * @param mixed  $default used if the session variable isn't set.
	 * @return array|string value of session variable
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @param string $key Key to set.
	 * @param mixed  $value Value to set.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty                        = true;
		}
	}

    /**
	 * Constructor for the session class.
	 */
	public function __construct() {		
		$this->_cookie = apply_filters( 'mobiconnector_cookie', 'wp_mobiconnector_session_' . COOKIEHASH );
		$this->_table  = $GLOBALS['wpdb']->prefix . 'mobiconnector_sessions';		
    }
    
    /**
	 * Init hooks and session data.
	 */
	public function bamobile_init() {
		$cookie = $this->bamobile_get_session_cookie();

		if ( $cookie ) {
			$this->_customer_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;

			// Update session if its close to expiring.
			if ( time() > $this->_session_expiring ) {
				$this->bamobile_set_session_expiration();
				$this->bamobile_update_session_timestamp( $this->_customer_id, $this->_session_expiration );
			}
		} else {
			$this->bamobile_set_session_expiration();
			$this->_customer_id = $this->bamobile_generate_customer_id();
		}

        $this->_data = $this->bamobile_get_session_data();
        
		add_action( 'shutdown', array( $this, 'bamobile_save_data' ), 20 );
		add_action( 'wp_logout', array( $this, 'bamobile_destroy_session' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'bamobile_nonce_user_logged_out' ) );
		}
    }
    
    /**
	 * Get the session cookie, if set. Otherwise return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function bamobile_get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->_cookie ] ) ? wp_unslash( $_COOKIE[ $this->_cookie ] ) : false; // @codingStandardsIgnoreLine.

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $cookie_value );

		if ( empty( $customer_id ) ) {
			return false;
		}

		// Validate hash.
		$to_hash = $customer_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
    }
    
    /**
	 * Set session expiration.
	 */
	public function bamobile_set_session_expiration() {
		$this->_session_expiring   = time() + intval( apply_filters( 'mobiconnector_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$this->_session_expiration = time() + intval( apply_filters( 'mobiconnector_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
    }

    /**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return string
	 */
	public function bamobile_generate_customer_id() {
		$customer_id = '';

		if ( is_user_logged_in() ) {
			$customer_id = get_current_user_id();
		}

		if ( empty( $customer_id ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher      = new PasswordHash( 8, false );
			$customer_id = md5( $hasher->get_random_bytes( 32 ) );
		}

		return $customer_id;
	}
    
    /**
	 * Update the session expiry timestamp.
	 *
	 * @param string $customer_id Customer ID.
	 * @param int    $timestamp Timestamp to expire the cookie.
	 */
	public function bamobile_update_session_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		// @codingStandardsIgnoreStart.
		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp,
			),
			array(
				'session_key' => $customer_id,
			),
			array(
				'%d'
			)
		);
		// @codingStandardsIgnoreEnd.
    }

    /**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function bamobile_has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in(); // @codingStandardsIgnoreLine.
	}
    
    /**
	 * Get session data.
	 *
	 * @return array
	 */
	public function bamobile_get_session_data() {
		return $this->bamobile_has_session() ? (array) $this->bamobile_get_session( $this->_customer_id, array() ) : array();
    }
    
    /**
	 * Returns the session.
	 *
	 * @param string $customer_id Custo ID.
	 * @param mixed  $default Default session value.
	 * @return string|array
	 */
	public function bamobile_get_session( $customer_id, $default = false ) {
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
        }
        
        $value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM $this->_table WHERE session_key = %s", $customer_id ) ); // @codingStandardsIgnoreLine.

        if ( is_null( $value ) ) {
            $value = $default;
        }

		return maybe_unserialize( $value );
    }

    /**
	 * Save data.
	 */
	public function bamobile_save_data() {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->_dirty && $this->bamobile_has_session() ) {

			global $wpdb;
			// If exist session in database
			if($this->bamobile_has_session_in_database()){
				$wpdb->update( // @codingStandardsIgnoreLine.
					$this->_table,
					array(						
						'session_value'  => maybe_serialize( $this->_data ),
						'session_expiry' => $this->_session_expiration,
					),
					array('session_key'    => $this->_customer_id),
					array(						
						'%s',
						'%d',
					),
					array('%s')
				);
				$this->_dirty = false;
			}else{
				$wpdb->insert( // @codingStandardsIgnoreLine.
					$this->_table,
					array(
						'session_key'    => $this->_customer_id,
						'session_value'  => maybe_serialize( $this->_data ),
						'session_expiry' => $this->_session_expiration,
					),
					array(
						'%s',
						'%s',
						'%d',
					)
				);
				$this->_dirty = false;
			}
		}
	}

	/**
	 * Check session exist in database
	 */
	private function bamobile_has_session_in_database(){
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM $this->_table WHERE session_key = %s", $this->_customer_id ) );
		if ( is_null( $value ) ) {
            return false;
		}
		return true;
	}

	/**
	 * Cleanup session data from the database and clear caches.
	 */
	public function bamobile_cleanup_sessions() {
		global $wpdb;
		date_default_timezone_set('Etc/GMT0');
        $nowgmt = date('Y-m-d H:i:s',time());
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", strtotime($nowgmt) ) ); 
	}
    
    /**
	 * Destroy all session data.
	 */
	public function bamobile_destroy_session() {
		bamobile_mobiconnector_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, apply_filters( 'mobiconnector_session_use_secure_cookie', false ) );

        $this->bamobile_delete_session( $this->_customer_id );
        
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->bamobile_generate_customer_id();
	}
	
	/**
	 * Delete the session from the cache and database.
	 *
	 * @param int $customer_id Customer ID.
	 */
	public function bamobile_delete_session( $customer_id ) {
		global $wpdb;

		$wpdb->delete( // @codingStandardsIgnoreLine.
			$this->_table,
			array(
				'session_key' => $customer_id,
			)
		);
	}
    
    /**
	 * When a user is logged out, ensure they have a unique nonce by using the customer/session ID.
	 *
	 * @param int $uid User ID.
	 * @return string
	 */
	public function bamobile_nonce_user_logged_out( $uid ) {
		return $this->bamobile_has_session() && $this->_customer_id ? $this->_customer_id : $uid;
	}
}
?>