<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-20
 * Time: 16:21
 */
class Image_manifest {

	public $json = array(), $new_id, $manifestpath;

	function __construct($manifestpath) {
		$this->manifestpath = $manifestpath;

		if ( !is_file($manifestpath) ) {
			file_put_contents($manifestpath, '{}');

		}

		$this->json   = json_decode(file_get_contents($manifestpath), TRUE);
		$this->new_id = $this->json[ 'info' ][ 'auto_id' ] + 1;
	}

	public function get_new_id() {
		return $this->new_id;
	}

	public function add_image($file_id, $ext = 'jpg', $org_name = '', $w = 0, $h = 0) {

		$this->json[ 'files' ][ $file_id ] = array(
			'filename'    => $file_id . '.' . $ext,
			'orgfilename' => $org_name,
			'name'        => Tools::filename_to_text($org_name),
			'added'       => date("Y-m-d H:i:s"),
			'ext'         => $ext,
			'w'           => $w,
			'h'           => $h
		);

		$this->new_id                      = $this->new_id + 1;
		$this->json[ 'info' ][ 'auto_id' ] = $this->new_id;
	}

	public function save() {
		file_put_contents($this->manifestpath, json_encode($this->json, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT));
	}

	public function get_image_array($sort = 'added desc') {
		$out = $this->json[ 'files' ];

		if ( !is_array($out) ) {
			$out = array();
		}

		if ( $sort == 'added desc' ) {
			krsort($out);
		}
		if ( $sort == 'added asc' ) {
			ksort($out);
		}

		return $out;
	}

	public function get_image($id, $type = 'filename') {

		if ( !$this->image_exists($id) ) {
			// TODO: error handling for this?
			return NULL;

		}

		if ( $type == 'array' ) {
			return $out = $this->json[ 'files' ][ $id ];
		} elseif ( $type == 'thumb' ) {
			return $id . '_thumb.jpg'; // thumb is always jpg
		} else {
			return $this->json[ 'files' ][ $id ][ $type ];
		}


	}

	// check if an image exist, first in manifest, then real file
	public function image_exists($id) {
		if ( array_key_exists($id, $this->json[ 'files' ]) ) {
			if ( is_file('../' . Config::get('assets_folder') . 'images_originals/' . $this->json[ 'files' ][ $id ][ 'filename' ]) ) {
				return TRUE;
			}
		}

		return FALSE;
	}


}