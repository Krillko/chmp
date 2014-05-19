<?php


class Config {

	/**
	 * Here we define some config that are not changeable by someone that just wants to change the template
	 * The config here requires programming changes also
	 */

	public static $config = array();

	public static $chmp_cnf_texts = array( 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' );

	public static $chmp_login_text = array(
		'sv'      => array(
			'login'     => 'Logga in',
			'logout'    => 'Logga ut',
			'loginfail' => 'Användarnamn eller lösenord är fel',
			'username'  => 'Användarnamn',
			'password'  => 'Lösenord'
		),
		'default' => array(
			'login'     => 'Log in',
			'logout'    => 'Log out',
			'loginfail' => 'Username or password is wrong',
			'username'  => 'Username',
			'password'  => 'Password'
		)

	);

	public static function init($path = '') {
		// reads config file
		if ( count(Config::$config) == 0 ) {
			require_once( $path . 'config.php' );
			Config::$config = $chmp_config;
		}

		// Making some changes
		if ( !isset( Config::$config[ 'timezone' ] ) or Config::$config[ 'timezone' ] == '' ) {
			Config::$config[ 'timezone' ] = date_default_timezone_get();
		}


		// TODO: Read language from database

		// TODO: Reads settings from database and overwrite from file

	}

	/**
	 * Shows a config variable
	 * @param string $var
	 * @return string|false
	 */
	public static function get($var) {
		if ( isset( Config::$config[ $var ] ) ) {
			return Config::$config[ $var ];
		} else {
			return FALSE;
		}
	}

	public static function set($var, $value) {
		Config::$config[ $var ] = $value;
	}


	public static function jquery() {
		if ( Config::$config[ 'jquery_external' ] ) {
			return ( '<script src="http://ajax.googleapis.com/ajax/libs/jquery/' . Config::$config[ 'jquery_version' ] . '/jquery.min.js" type="text/javascript"></script>' );
		} else {
			return ( '<script src="js/jquery-' . Config::$config[ 'jquery_version' ] . '.min.js" type="text/javascript"></script>' );

		}
	}

	/**
	 * Groups together a couple of tags and calls them "text", other tags are just returned as is
	 *
	 * @param string $in
	 * @return string
	 */
	static public function tag_kind($in) {
		if ( in_array($in, Config::$chmp_cnf_texts) ) {
			return 'text';
		} else {
			return ( $in );
		}

	}

	/**
	 * Get text for login box,
	 * if $var is empty, it returns an array with all texts
	 * TODO: Maybe stuff like this should have it's own class
	 * @param $lang
	 * @param string $var
	 * @return mixed
	 */
	static public function get_logintext($lang, $var = '') {
		$out    = '';
		$config = Config::get('login');

		if ( !is_array($config[ $lang ]) ) {
			$config = Config::$chmp_login_text;
		}

		if ( !is_array($config[ $lang ]) ) {
			$lang = 'default';
		}

		if ( $var == '' ) {
			return $config[ $lang ];
		} else {
			return $config[ $lang ][ $var ];
		}
	}


} 