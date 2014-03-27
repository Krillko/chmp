<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-20
 * Time: 11:42
 */
class Image_library {

	function __construct() {


	}


	/**
	 * Scan the uploaded folder and moves to originals
	 */
	static function add_uploaded($specific = array()) {

		$images_upload_folder    = '../' . Config::get('assets_folder') . 'images_upload/';
		$images_originals_folder = '../' . Config::get('assets_folder') . 'images_originals/';

		// check images_temp for new image
		if ( count($specific) > 0 ) {
			$images_temp = $specific;
		} else {
			$images_temp = Tools::read_folder($images_upload_folder, FALSE, TRUE, TRUE);
		}

		// get originals.json
		$manifest = new Image_manifest( $images_originals_folder . 'manifest.json' );

		$newimgglobal = FALSE;
		foreach ( $images_temp as $images_temp_row ) {
			$next_auto_id = $manifest->get_new_id();
			$newimg       = FALSE;
			$ext          = pathinfo($images_upload_folder . $images_temp_row, PATHINFO_EXTENSION);
			list( $width, $height, $type, $attr ) = getimagesize($images_upload_folder . $images_temp_row);

			if ( $type == 1 or $type == 2 or $type == 3 ) {
				rename($images_upload_folder . $images_temp_row, $images_originals_folder . $next_auto_id . '.' . $ext);
				$newimg = TRUE;
				$image  = new Image_processor();
				$image->load_image($images_originals_folder . $next_auto_id . '.' . $ext);

			} else if ( $type > 3 ) {
				$ext   = 'jpg';
				$image = new Image_processor();
				$image->load_image($images_temp_row);
				$image->set_jpg();
				$image->save_image($images_originals_folder . $next_auto_id . '.' . $ext, TRUE);

				$newimg = TRUE;
			} else {
				// not an image file
				$test = 1;
			}

			if ( $newimg ) {
				$manifest->add_image($next_auto_id, $ext, basename($images_temp_row), $width, $height);
				$newimgglobal = TRUE;
				$image->create_thumbnail($images_originals_folder . $next_auto_id . '_thumb.jpg');

			}


		}

		if ( $newimgglobal ) {
			$manifest->save();

			return $next_auto_id;
		}


	}


}