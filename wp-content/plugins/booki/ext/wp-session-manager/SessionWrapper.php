<?php
/**
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of cart items etc
 * Adapted from EDD session wrapper, originally written by Pippin Williamson	
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * SessionWrapper Class
 *
 */
class Booki_SessionWrapper {

	private $session;
	private $use_php_sessions = false;
	private $prefix = '';
	private $lifeTime = 1800;
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();

		if( $this->use_php_sessions ) {

			if( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();
	
			}

			// Use PHP SESSION (must be enabled via the BOOKI_USE_PHP_SESSIONS constant)
			add_action( 'init', array( $this, 'maybe_start_session' ), -2 );

		} else {

			// Use WP_Session (default)

			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'booki_wp_session' );
			}
			
			if ( ! function_exists( 'wp_session_start' ) ) {
				require_once BOOKI_WP_SESSION_MANAGER . 'wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		}

		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}

	}

	/**
	 * Setup the WP_Session instance
	 */
	public function init() {

		if( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION['booki' . $this->prefix ] ) && is_array( $_SESSION['booki' . $this->prefix ] ) ? $_SESSION['booki' . $this->prefix ] : array();
			$this->validate($this->lifeTime);
		} else {
			$this->session = WP_Session::get_instance();
		}
		return $this->session;
	}

	protected function validate($seconds){
		$lastActivity = $this->get('Booki_Last_Activity');
		if ($lastActivity && (time() - $lastActivity > $this->lifeTime)) {
			$this->delete('Booki_Bookings');
			$this->delete('Booki_MailChimpList');
		}
		$this->set('Booki_Last_Activity', time());
	}

	/**
	 * Retrieve session ID
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 * @param string $key Session key
	 * @return string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 * @param string $key Session key
	 * @param integer $value Session variable
	 * @return string Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value )) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		if( $this->use_php_sessions ) {

			$_SESSION['booki' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}

	public function delete($key){
		if($this->get($key)){
			$this->set($key, null);
		}
	}
	/**
	 * Force the cookie expiration variant time to 30minutes
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( $this->lifeTime );
	}

	/**
	 * Force the cookie expiration time to 30minutes
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_time( $exp ) {
		return ( $this->lifeTime);
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @return boolean
	 * Checks to see if the server supports PHP sessions
	 * or if the BOOKI_USE_PHP_SESSIONS constant is defined
	 * @author Daniel J Griffiths
	 * @return boolean $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {
		$ret = false;
		// If the database variable is already set, no need to run autodetection
		$booki_use_php_sessions = (bool) get_option( 'booki_use_php_sessions' );

		if ( ! $booki_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions
			if( function_exists( 'session_start' ) && ! ini_get( 'safe_mode' ) ) {

				$this->set( 'booki_use_php_sessions', 1 );

				if( $this->get( 'booki_use_php_sessions' ) ) {

					$ret = true;

					// Set the database option
					update_option( 'booki_use_php_sessions', true );

				}

			}

		} else {
			$ret = $booki_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the BOOKI_USE_PHP_SESSIONS constant
		if ( defined( 'BOOKI_USE_PHP_SESSIONS' ) && BOOKI_USE_PHP_SESSIONS ) {
			$ret = true;
		} else if ( defined( 'BOOKI_USE_PHP_SESSIONS' ) && ! BOOKI_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'booki_use_php_sessions', $ret );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {
		if( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

}

