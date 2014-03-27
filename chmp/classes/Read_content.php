<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-17
 * Time: 15:35
 */
class Read_content {
	public $content;

	function __construct($pageId) {
		$content_raw = file_get_contents('chmp/content/' . $pageId . '.json');

		$this->content = json_decode($content_raw, TRUE);


	}


	public function get($var) {
		if ( $var == 'templatefile' ) {
			if ( isset( $this->content[ 'info' ][ 'templatefile' ] ) ) {
				return ( $this->content[ 'info' ][ 'templatefile' ] );
			} else {
				// TODO: Parse template folder and return first
			}

		} else {
			if ( isset( $this->content[ 'info' ][ $var ] ) ) {
				return ( $this->content[ 'info' ][ $var ] );
			}

		}

		return null;

	}


}