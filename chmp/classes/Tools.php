<?php

/*This class contains "good to have" methods
 *
 * */

class Tools {

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

	static public function cleanInt($in) {
		if ( is_int($in) ) {
			return ( $in );
		} else if ( is_float($in) ) {
			return ( round($in) );
		} else {
			$out = intval($in);
			if ( $out > 0 ) {
				return $out;
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Groups together a couple of tags and calls them "text", other tags are just returned
	 *
	 * @param $in
	 * @return string
	 */
	static public function tag_kind($in) {
		if ( in_array($in, Tools::$chmp_cnf_texts) ) {
			return 'text';
		} else {
			return ( $in );
		}

	}

	static public function json_error($error) {

		switch ($error) {
			case JSON_ERROR_NONE:
				return ' - No errors';
				break;
			case JSON_ERROR_DEPTH:
				return ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				return ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				return ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				return ' - Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				return ' - Unknown error';
				break;
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
			$config = Tools::$chmp_login_text;
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


	/** Returns an array with all files in a folder
	 * @param string $dir - folder to read
	 * @param bool $recursive - scan with subfolders
	 * @param bool $simple - true: returns only filename, false: returns array with size etc
	 * @param bool $include_folders - true: also returns subfolder
	 * @param bool $ignore_dsstore - ignores mac .DS_Store files
	 * @return array
	 */
	static public function read_folder($dir, $recursive = FALSE, $simple = TRUE, $include_folders = FALSE, $ignore_dsstore = TRUE) {

		$output = array();

		if ( $recursive ) {

			$di = new RecursiveDirectoryIterator( $dir );
			foreach ( new RecursiveIteratorIterator( $di ) as $filename => $file ) {
				//echo $filename . ' - ' . $file->getSize() . ' bytes <br/>';
				if ( !$file->isDot() ) {
					$output[ ] = $filename;
				}
			}


		} else {
			foreach ( new DirectoryIterator( $dir ) as $file ) {
				if ( ( $file->isFile() or $include_folders )
					and ( $file->getFilename() != '.DS_Store' or $ignore_dsstore == FALSE )
					and $file->isDot() == FALSE
				) {
					if ( $simple ) {
						$output[ ] = $file->getFilename();
					} else {

						$output[ ] = array( 'filename' => $file->getFilename(),
						                    'size'     => $file->getSize(),
						                    'type'     => $file->getType(),
						                    'path'     => $file->getPath(),
						                    'is_dir'   => ( $file->isDir() ? TRUE : FALSE )

						);

					}
				}
			}

		}

		return $output;

	}

	static public function filename_to_text($in = '') {

		$out = $in;

		$dotpos = strrpos($out, '.');

		if ( $dotpos !== FALSE ) {

			$out = substr($out, 0, $dotpos);

		}

		$out = ucfirst(str_ireplace('_', ' ', $out));

		return $out;
	}


}