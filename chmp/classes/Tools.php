<?php

/*This class contains "good to have" methods
 *
 * */

class Tools {


	/**
	 * Try to make an int from int, string or float (rounded)
	 * @param $in
	 * @return int|false
	 */
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
	 * Returns plain text of json error
	 * @param $error
	 * @return string
	 */
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

	/**
	 * Removes file extension and converts _ to spaces
	 * @param string $in
	 * @return string
	 */
	static public function filename_to_text($in = '') {

		$out = $in;

		$dotpos = strrpos($out, '.');

		if ( $dotpos !== FALSE ) {

			$out = substr($out, 0, $dotpos);

		}

		$out = ucfirst(str_ireplace('_', ' ', $out));

		return $out;
	}


	/**
	 * Redirecting to another page
	 * @param $url
	 * @param bool $permanent - default false: temporary redirect, true: 301 Move Permanently
	 * @param bool $rand - default false, true: add a random value to force no cache
	 */
	static public function redirect($url, $permanent = FALSE, $rand = FALSE) {

		if ( $rand ) {
			if ( strpos($url, '?') === false ) {
				$url .= '?rnd=' . uniqid();
			} else {
				$url .= '&rnd=' . uniqid();
			}
		}

		if ( !$permanent ) {
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", FALSE);
			header("Pragma: no-cache");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		}

		if ( $permanent ) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: " . $url, TRUE, 301);
		} else {
			header("Location: " . $url);
		}

		echo '<html>
		<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<script language="JavaScript" type="text/JavaScript">
			this.document.location = "' . $url . '";
		</script>
		</head>
		<body>
		Redirecting to <a href="' . $url . '">' . $url . '</a>
		</body>
		</html>';
		die();


	}


	/**
	 * Makes a url friendly string
	 * This is the same function as js: chmp.urlformat in chmp/js/structure.js
	 * @param string $input
	 * @param bool $utf8 -
	 * @return string
	 */
	static public function urlformat($input, $utf8 = FALSE) {

		$output = strtolower($input);

		$output = preg_replace('/\s/', '_', $output);

		if ( $utf8 ) {

			$output = preg_replace('/[^A-za-z0-9\s._,\-À-ʸ]/u', '', $output);

		} else {

			$output = preg_replace('/[åäæàáâãāăảȧǎȁąạḁẚầấẫẩằắẵẳǡǟǻậặǽǣ]/u', 'a', $output);
			$output = preg_replace('/[ḃɓḅḇƀƃƅ]/u', 'b', $output);
			$output = preg_replace('/[ćĉċčƈçḉ]/u', 'c', $output);
			$output = preg_replace('/[ḋɗḍḏḑḓďđƌȡ]/u', 'd', $output);
			$output = preg_replace('/[èéêẽēĕėëẻěȅȇẹȩęḙḛềếễểḕḗệḝǝɛ]/u', 'e', $output);
			$output = preg_replace('/[ḟƒ]/u', 'f', $output);
			$output = preg_replace('/[ǵĝḡğġǧɠģǥ]/u', 'g', $output);
			$output = preg_replace('/[ĥḣḧȟƕḥḩḫẖħ]/u', 'h', $output);
			$output = preg_replace('/[ìíîĩīĭıïỉǐịȉȋḭɨḯ]/u', 'i', $output);
			$output = preg_replace('/[ĵǰ]/u', 'j', $output);
			$output = preg_replace('/[ḱǩḵƙḳķ]/u', 'k', $output);
			$output = preg_replace('/[ĺḻḷļḽľŀłƚḹȴ]/u', 'l', $output);
			$output = preg_replace('/[ḿṁṃɯ]/u', 'm', $output);
			$output = preg_replace('/[ǹńñṅňŋɲṇņṋṉŉƞȵ]/u', 'n', $output);
			$output = preg_replace('/[òóôõōŏȯöỏőǒȍȏơǫọɵøồốỗổȱȫȭṍṏṑṓờớỡởợǭộǿɔ]/u', 'o', $output);
			$output = preg_replace('/[ṕṗƥ]/u', 'p', $output);
			$output = preg_replace('/[ŕṙřȑȓṛŗṟṝ]/u', 'r', $output);
			$output = preg_replace('/[śŝṡšṣșşṥṧṩſẛ]/u', 's', $output);
			$output = preg_replace('/[ß]/u', 'ss', $output);
			$output = preg_replace('/[ṫẗťƭʈƫṭțţṱṯŧȶ]/u', 't', $output);
			$output = preg_replace('/[ùúûũūŭüủůűǔȕȗưụṳųṷṵṹṻǜǘǖǚừứữửự]/u', 'u', $output);
			$output = preg_replace('/[ṽṿ]/u', 'v', $output);
			$output = preg_replace('/[ẁẃŵẇẅẘẉ]/u', 'w', $output);
			$output = preg_replace('/[ẋẍ]/u', 'x', $output);
			$output = preg_replace('/[ỳýŷȳẏÿỷẙƴỵ]/u', 'y', $output);
			$output = preg_replace('/[źẑżžȥẓẕƶ]/u', 'z', $output);
			//ligatures
			$output = preg_replace('/[ĳ]/u', 'ij', $output);
			$output = preg_replace('/[ﬀ]/u', 'ff', $output);
			$output = preg_replace('/[ﬁ]/u', 'fi', $output);
			$output = preg_replace('/[ﬂ]/u', 'ff', $output);
			$output = preg_replace('/[ﬃ]/u', 'ffi', $output);
			$output = preg_replace('/[ﬄ]/u', 'ffl', $output);
			$output = preg_replace('/[œ]/u', 'oe', $output);
			$output = preg_replace('/[ĳ]/u', 'ij', $output);

			// remove everything else
			$output = preg_replace('/[^A-Za-z0-9\s._,\-]/', '', $output);


		}

		while ( strpos($output, '__') !== FALSE ) {
			$output = str_replace('__', '_', $output);
		}

		return $output;

	}

}