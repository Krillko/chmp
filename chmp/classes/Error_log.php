<?php

/**
 * Created by PhpStorm.
 * User: Kristoffer Ekendahl
 * Date: 2014-03-17
 * Time: 12:58
 */
class Error_log {

	function __construct() {
		$this->errors   = array();
		$this->warnings = array();

	}

	public function add_error($plaintext = '') {
		$this->errors[ ] = array( 'plaintext' => $plaintext );
	}

	public function add_warning($plaintext = '') {
		$this->warnings[ ] = array( 'plaintext' => $plaintext );
	}

	function style_error($type, $in, $console = 'error') {
		$in = trim(preg_replace('/\t+/', '', $in));
		if ( $type == 'console' ) {
			return ( 'console.' . $console . '("' . preg_replace('/^\s+|\n|\r|\s+$/m', '', str_replace('"', '\\"', $in)) . '");' );
		} else {
			return ( ' . . . . . . . . . . . . . . . . . . . . . . . .   ' . $console . ':' . PHP_EOL . $in . PHP_EOL );
		}
	}


	public function list_errors($type = 'comment', $warnings = TRUE) {
		$return = '';

		if ( count($this->errors) > 0 or count($this->warnings) > 0 ) {

			if ( $type == 'console' ) {
				$return .= '<script type="text/javascript">';
			} else {
				$return .= PHP_EOL . '<!--';
			}

			foreach ( $this->errors as $error_row ) {
				$return .= $this->style_error($type, $error_row[ 'plaintext' ]);
			}

			if ( $warnings ) {
				foreach ( $this->warnings as $warnings_row ) {
					$return .= $this->style_error($type, $warnings_row[ 'plaintext' ], 'warn');
				}
			}

			if ( $type == 'console' ) {
				$return .= '</script>';
			} else {
				$return .= '-->';
			}

		}

		return ( $return );

	}


}