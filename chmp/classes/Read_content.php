<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-17
 * Time: 15:35
 */
class Read_content {
	public $content;
	private $version;

	function __construct($page_id, $edit, $version) {

		// edit creates a new file if not existing
		// and gets the content of the editfile
		if ( $edit ) {
			if ( !is_file('chmp/content/' . $page_id . '_edit.json') ) {
				copy('chmp/content/' . $page_id . '.json', 'chmp/content/' . $page_id . '_edit.json');
			}
			$content_raw = file_get_contents('chmp/content/' . $page_id . '_edit.json');

		} else {
			$content_raw = file_get_contents('chmp/content/' . $page_id . '.json');
		}

		$this->content = json_decode($content_raw, TRUE);

		$this->version = $version;
	}

	// gets something specific from info
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

		return NULL;

	}

	// returns info as array
	public function get_info() {
		if ( isset( $this->content[ 'info' ] ) ) {
			return ( $this->content[ 'info' ] );
		} else {
			return NULL;
		}

	}


}