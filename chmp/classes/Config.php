<?php


class Config {

	public static $config = array();

	public static function init() {
		// reads config file
		if ( count(Config::$config) == 0 ) {
			require_once( 'config.php' );
			Config::$config = $chmp_config;
		}

		// future, get other settings here

	}

	public static function get($var) {
		return Config::$config[ $var ];
	}


} 