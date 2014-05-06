<?php

require_once( 'classes/Config.php' );
Config::init('../');

require_once( 'classes/Tools.php' ); // a collection of "good to have" methods
// this file doubles as a settings file
// for stuff that needs programming to change

require_once( 'classes/Read_content.php' );

require_once( 'classes/Image_library.php' );
require_once( 'classes/Image_manifest.php' );
require_once( 'classes/Image_processor.php' );
require_once( 'classes/Image_geometry.php' );

$images_originals_folder = '../' . Config::get('assets_folder') . 'images_originals/';
$images_output_folder    = '../' . Config::get('assets_folder') . 'images/';

// check for uploaded images
Image_library::add_uploaded();

// get originals.json
$manifest = new Image_manifest( $images_originals_folder . 'manifest.json' );

if ( isset( $_POST[ 'from_page' ] ) ) {
	$chmp_attr       = json_decode(base64_decode($_POST[ 'chmp_attr' ]), TRUE);
	$chmp_attr_json  = base64_decode($_POST[ 'chmp_attr' ]);
	$image_tuid      = $_POST[ 'image_tuid' ];
	$original_img_id = $_POST[ 'original_img_id' ];
} else { // imageeditor just opened

	$chmp_attr = array();
	foreach ( $_GET as $getrowK => $getrowV ) {
		if ( substr($getrowK, 0, 10) == 'data-chmp-' ) {
			$chmp_attr[ $getrowK ] = $getrowV;
		}
	}
	$image_tuid      = $_GET[ 'data-chmp-tuid' ];
	$original_img_id = $_GET[ 'data-chmp-orgimgid' ];

}

if ( $_POST[ 'from_page' ] == 'select' or $_POST[ 'from_page' ] == 'scale' ) {
	$view     = 'crop';
	$geometry = new Image_geometry( $original_img_id, $manifest, 0, 0, $chmp_attr );
} elseif ( $_POST[ 'from_page' ] == 'crop' ) {
	$view = 'scale';

	$existing_files = array();
	// making a new img file
	foreach ( Tools::read_folder($images_output_folder) as $old_img ) {
		$existing_files[ ] = intval(pathinfo($old_img, PATHINFO_FILENAME));
	}
	$new_img_id = @max($existing_files) + 1;

	// makes an empty file to make sure filename doesn't get taken
	file_put_contents($images_output_folder . $new_img_id . '.jpg', '');

	$image = new Image_processor( $images_originals_folder . $manifest->get_image($original_img_id) );

	$image->crop_image(round($_POST[ 'crop-w' ]), round($_POST[ 'crop-h' ]), round($_POST[ 'crop-x1' ]), round($_POST[ 'crop-y1' ]));

	$test = 1;

	if ( $chmp_attr[ 'data-chmp-keepheight' ] and !$chmp_attr[ 'data-chmp-keepwidth' ] ) {
		$to_w = 0;
	} else {
		$to_w = $chmp_attr[ 'data-chmp-width' ];
	}

	if ( !$chmp_attr[ 'data-chmp-keepheight' ] and $chmp_attr[ 'data-chmp-keepwidth' ] ) {
		$to_h = 0;
	} else {
		$to_h = $chmp_attr[ 'data-chmp-height' ];
	}

	$image->resize_image($to_w, $to_h);

	$image->set_jpg();

	$output_size = $image->get_size();

	$image->save_image($images_output_folder . $new_img_id . '.jpg');

	$new_img_id_out = $new_img_id . '.jpg';


} else {
	$view = 'select';
}

$out = '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="editordesign/chmp.css">
	<link rel="stylesheet" type="text/css" href="editordesign/chmp_in_iframe.css">
	<link rel="stylesheet" type="text/css" href="editordesign/jquery.Jcrop.min.css">
	' . Config::jquery() . '
	<script type="text/javascript">
		var chmp = chmp || [];
			chmp.view = "' . $view . '";

	';

if ( $view == 'crop' ) {

	$out .= '
			 chmp.truesize = [' . $geometry->get_original('comma') . '];
			 chmp.startsize = [';

	if ( is_numeric($_POST[ 'crop-x1' ]) and is_numeric($_POST[ 'crop-y1' ]) and is_numeric($_POST[ 'crop-x2' ]) and is_numeric($_POST[ 'crop-y2' ]) ) {
		$out .= $_POST[ 'crop-x1' ] . ',' . $_POST[ 'crop-y1' ] . ',' . $_POST[ 'crop-x2' ] . ',' . $_POST[ 'crop-y2' ];

	} else {
		$out .= $geometry->get_crop_start();
	}

	$out .= '];';

	if ( !is_null($geometry->get_ratio()) ) {
		$out .= $geometry->get_ratio();
	} else {
		$out .= $geometry->get_min_max();
	}

}

$out .= '
	</script>

    <script src="js/jquery.Jcrop.js" type="text/javascript"></script>
	<script src="js/jquery.color.js" type="text/javascript"></script>
	<script src="js/imageeditor.js" type="text/javascript"></script>




	<title>Image editor</title>
</head>

<body>
<form method="post" id="chmp_use_img">

<!-- navigation -->
<div class="chmp chmp-arrownav">
	<div class="chmp chmp-arrownav-item' . ( $view == 'select' ? ' chmp-arrownav-selected' : ' chmp-arrownav-click' ) . '" ' . ( $view != 'select' ? ' id="chmp-arrownav-select"' : '' ) . '>
		<p>Select Image</p>
	</div>';

switch ($view) {
	case 'crop':
		$out .= '<div class="chmp chmp-arrownav-arrow chmp-arrownav-arrow-toselected"><div class="chmp chmp-arrow-toselected"></div></div>';
		break;

	case 'scale':
		$out .= '<div class="chmp chmp-arrownav-arrow chmp-arrownav-noselect"></div>';
		break;

	default:
		$out .= '<div class="chmp chmp-arrownav-arrow"><div class="chmp chmp-arrow-fromselected"></div></div>';
		break;
}

$out .= '
	<div class="chmp chmp-arrownav-item' . ( $view == 'crop' ? ' chmp-arrownav-selected' : ' chmp-arrownav-click' ) . '" ' . ( $view == 'scale' ? ' id="chmp-arrownav-crop"' : '' ) . '>
		<p>Crop Image</p>
	</div>';

switch ($view) {
	case 'crop':
		$out .= '<div class="chmp chmp-arrownav-arrow"><div class="chmp chmp-arrow-fromselected"></div></div>';
		break;

	case 'scale':
		$out .= '<div class="chmp chmp-arrownav-arrow chmp-arrownav-arrow-toselected"><div class="chmp chmp-arrow-toselected"></div></div>';
		break;

	default:
		$out .= '<div class="chmp chmp-arrownav-arrow chmp-arrownav-noselect"></div>';
		break;
}

$out .= '
	<div class="chmp chmp-arrownav-item' . ( $view == 'scale' ? ' chmp-arrownav-selected' : '' ) . '">
		<p>Preview</p>
	</div>


	<div class="chmp chmp-arrownav-close"><div class="chmp chmp-icon-center chmp-icon-close"></div></div>
</div>
<!-- /navigation -->

<div class="chmp chmp-iframe">
		<div class="chmp chmp-iframe-content' . ( $view == 'select' ? ' chmp-padding' : ' chmp-padding-small' ) . '">';

if ( $view == 'select' ) {
	$out .= '
		<!-- select image -->
';

	// current imag
	if ( $chmp_attr[ 'data-chmp-orgimgid' ] > 0 and $manifest->image_exists($chmp_attr[ 'data-chmp-orgimgid' ]) ) {
		$out .= '<div class="chmp chmp-imgedit chmp-imgedit-current">
				<h2>Current image</h2>
				<div class="chmp"><img src="assets/images_originals/' . $manifest->get_image($chmp_attr[ 'data-chmp-orgimgid' ], 'thumb') . '" class="chmp chmp-imgedit-preview"></div>
			</div>';
	}

	// upload image
	$out .= '<div class="chmp chmp-imgedit chmp-imgedit-upload">
				<h2>Upload image</h2>
				<div class="chmp chmp-imgedit-upload">
						<div class="chmp chmp-imgedit-uploadinfo">Upload your image in any size/format<br><br>
						<b>or</b> exact size,<br>
						jpg, png or gif<br>
						Width: 200 px<br>
						Height: any</div>
					<div class="chmp chmp-imgedit-uploader" id="chmp-dropbox">
						<div class="chmp chmp-imgedit-droptext"><p>Drag & drop your image here</p></div>
						<div class="chmp chmp-imgedit-uploader2">
							<form method="post" enctype="multipart/form-data" name="form1" id="form1">
							  <label for="fileField">or:</label>
							  <input type="file" name="fileField" id="fileField">
							</form>
						</div>

						<div id="chmp-dropbox-progress"></div>

					</div>
				</div>
			</div>';

	// archive

	if ( count($manifest->get_image_array()) > 0 ) {

		$out .= '<div class="chmp chmp-imgedit chmp-imgedit-archive">
				<h2>All images</h2>';

		foreach ( $manifest->get_image_array() as $mKey => $mValue ) {

			$out .= '<div class="chmp chmp-imgedit-oldimg" data-chmp-useOldImage=' . $mKey . '>
					<img src="assets/images_originals/' . $manifest->get_image($mKey, 'thumb') . '"  class="chmp chmp-imgedit-preview">
					<p class="chmp chmp-imgedit-filename">' . $mValue[ 'name' ] . '</p>
				</div>';

		}

		$out .= '</div>




		';

	}

	$out .= '
		<!-- / select image -->
';
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - crop

if ( $view == 'crop' ) {

	$out .= '<div class="chmp-cropimg-holder">
	<img src="assets/images_originals/' . $manifest->get_image($original_img_id) . '" ' . $geometry->get_editor() . ' id="chmp-cropimg">
	</div>

	<div class="chmp chmp-imgedit-btns">
				<div class="chmp chmp-cell">
					<input id="disable_lowres" class="chmp-checkbox" type="checkbox" value="1">
					<label for="disable_lowres" name="demo_lbl_1" class="chmp-label">No low quality</label>
				</div>
				<div class="chmp chmp-cell">
					<div class="chmp chmp-tooltip-element chmp-input chmp-input-small chmp-submit chmp-right" id="chmp-do-crop"><p><u>C</u>rop</p></div>
				</div>

			</div>


	';


}

if ( $view == 'scale' ) {

	$out .= '
	<div class="chmp chmp-imgedit-scale">

		<img src="assets/images/' . $new_img_id . '.jpg">

	</div>

	<div class="chmp chmp-imgedit-btns">
				<div class="chmp chmp-cell">
					<!--<input id="disable_lowres" class="chmp-checkbox" type="checkbox" value="1">
					<label for="disable_lowres" name="demo_lbl_1" class="chmp-label">No low quality</label>-->
				</div>
				<div class="chmp chmp-cell">
					<div class="chmp chmp-tooltip-element chmp-input chmp-input-small chmp-submit chmp-right" id="chmp-do-scale"><p><u>S</u>ave</p></div>
				</div>

			</div>
			';


}

$out .= '
			<input type="hidden" name="from_page" id="from_page" value="' . $view . '">
			<input type="hidden" name="chmp_attr" value="' . base64_encode(json_encode($chmp_attr)) . '">
			<input type="hidden" name="original_img_id" id="original_img_id" value="' . ( $original_img_id > 0 ? $original_img_id : 0 ) . '">
			<input type="hidden" name="image_tuid" id="image_tuid" value="' . $image_tuid . '">';

if ( $view == 'scale' ) {
	$out .= '<input type="hidden" id="new_img_id" value="' . $new_img_id_out . '">
			<input type="hidden" name="output_w" id="output_w" value="' . $output_size[ 'width' ] . '">
			<input type="hidden" name="output_h" id="output_h" value="' . $output_size[ 'height' ] . '">
			';


}

if ( $view == 'crop' or $view == 'scale' ) {
	$out .= '
    <input type="hidden"  id="x1" name="crop-x1" value="' . $_POST[ 'crop-x1' ] . '"></label>
    <input type="hidden"  id="y1" name="crop-y1" value="' . $_POST[ 'crop-y1' ] . '"></label>
    <input type="hidden"  id="x2" name="crop-x2" value="' . $_POST[ 'crop-x2' ] . '"></label>
    <input type="hidden"  id="y2" name="crop-y2" value="' . $_POST[ 'crop-y2' ] . '"></label>
    <input type="hidden"  id="w" name="crop-w" value="' . $_POST[ 'crop-w' ] . '"></label>
    <input type="hidden"  id="h" name="crop-h" value="' . $_POST[ 'crop-h' ] . '"></label>
	';
}

$out .= '

	</div>
</div>

</form>
</body>
</html>';

echo $out;



