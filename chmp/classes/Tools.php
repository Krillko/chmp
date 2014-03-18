<?php

/*This class contains "good to have" methods
 *
 * */

class Tools {

	public static $chmp_cnf_texts = array( 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span' );

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


}