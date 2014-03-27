<?php


class Config {

	public static $config = array();


	public static function init($path = '') {
		// reads config file
		if ( count(Config::$config) == 0 ) {
			require_once( $path . 'config.php' );
			Config::$config = $chmp_config;
		}

		// future, get other settings here

	}

	public static function get($var) {
		return Config::$config[ $var ];
	}

	public static function jquery() {
		if ( Config::$config[ 'jquery_external' ] ) {
			return ( '<script src="http://ajax.googleapis.com/ajax/libs/jquery/' . Config::$config[ 'jquery_version' ] . '/jquery.min.js" type="text/javascript"></script>' );
		} else {
			return ( '<script src="js/jquery-' . Config::$config[ 'jquery_version' ] . '.min.js" type="text/javascript"></script>' );

		}
	}


} 