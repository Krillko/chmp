<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-04-15
 * Time: 16:05
 */
class Session {

	private $cookiename, $login, $users, $edit;

	function __construct($path = '') {

		if ( session_id() == '' ) {
			session_start();
		}

		// make a unique cookiename, incase there are several chmp installations on the same server
		$this->cookiename = 'chmp-' . Config::get('sitename');

		if ( !is_file($path . 'chmp/content/users.php') ) {
			die( 'No users defined. Please run setup' );
		} else {
			require_once( $path . 'chmp/content/users.php' );
		}

		$this->users = $chmp_user;
		$this->edit  = FALSE;

	}

	public function login($user, $password) {

		if ( array_key_exists($user, $this->users) ) {

			if ( crypt($password, Config::get('salt')) == $this->users[ $user ][ 'password' ] ) {

				$_SESSION[ $this->cookiename . '-user' ]        = $user;
				$_SESSION[ $this->cookiename . '-login' ]       = TRUE;
				$_SESSION[ $this->cookiename . '-rightslevel' ] = $this->users[ $user ][ 'rightslevel' ];

			} else {
				$this->clear_session();

				return FALSE;
			}

		} else {
			$this->clear_session();

			return FALSE;
		}

		return TRUE;

	}

	public function set_edit($set) {

		if ( $this->is_loggedin() and $set ) {
			$this->edit = $set;

			$_SESSION[ $this->cookiename . '-edit' ] = $set;

		} else {
			$this->edit = FALSE;

			$_SESSION[ $this->cookiename . '-edit' ] = FALSE;

		}


	}


	public function is_loggedin() {
		if ( $_SESSION[ $this->cookiename . '-login' ] ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function is_edit() {
		if ( $this->is_loggedin() and $_SESSION[ $this->cookiename . '-edit' ] ) {
			return TRUE;
		}

		return FALSE;
	}

	public function loginlevel() {
		if ( $_SESSION[ $this->cookiename . '-login' ] ) {
			return $_SESSION[ $this->cookiename . '-rightslevel' ];
		} else {
			return NULL;
		}
	}


	/**
	 * Removes login related session variables
	 */
	public function clear_session() {

		$_SESSION[ $this->cookiename . '-login' ] = FALSE;
		unset( $_SESSION[ $this->cookiename . '-login' ] );
		$_SESSION[ $this->cookiename . '-user' ] = '';
		unset( $_SESSION[ $this->cookiename . '-user' ] );
		$_SESSION[ $this->cookiename . '-rightslevel' ] = 0;
		unset( $_SESSION[ $this->cookiename . '-rightslevel' ] );

	}

	/**
	 * Sets a session variable
	 * @param string $var
	 * @param string $val
	 */
	public function set($var, $val) {
		$_SESSION[ $this->cookiename . '-' . $var ] = $val;
	}

	/**
	 * Gets a session variable
	 * @param string $var
	 * @return mixed
	 */
	public function get($var) {
		return $_SESSION[ $this->cookiename . '-' . $var ];
	}


	/**
	 * Sets the lang session for admin
	 * @param int|null $lang
	 */
	public function set_lang($lang = null) {
		if (!is_int($lang)) {
			$_SESSION[ $this->cookiename .'-lang'] = Config::get('language_main');
		} else {
			$_SESSION[ $this->cookiename .'-lang'] = $lang;
		}

	}


} 