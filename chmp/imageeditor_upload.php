<?php
/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-24
 * Time: 14:29
 */
require_once( 'classes/Config.php' );
Config::init('../');
require_once( 'classes/Tools.php' ); // a collection of "good to have" methods

require_once( 'classes/Image_library.php' );
require_once( 'classes/Image_manifest.php' );
require_once( 'classes/Image_processor.php' );

$output_dir = "assets/images_upload/";

if ( isset( $_FILES[ "file" ] ) ) {
//Filter the file types , if you want.
	if ( $_FILES[ "file" ][ "error" ] > 0 ) {
		echo "Error: " . $_FILES[ "file" ][ "error" ];


	} else {

		list( $width, $height, $type, $attr ) = getimagesize($_FILES[ "file" ][ "tmp_name" ]);

		if ( $width > 0 and $height > 0 ) {

			move_uploaded_file($_FILES[ "file" ][ "tmp_name" ], $output_dir . $_FILES[ "file" ][ "name" ]);

			$added_file = array( $_FILES[ "file" ][ "name" ] );

			$added_file_id = Image_library::add_uploaded($added_file);

			echo $added_file_id;

		} else {
			header('HTTP/1.0 400 Bad Request', TRUE, 400);
			echo 'File could not be read as image';

		}


	}

}