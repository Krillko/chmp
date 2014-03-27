<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-20
 * Time: 15:28
 * TODO: Fallback to command line imagemagick if extension not available
 */
class Image_processor {
	public $imagemagick, $w, $h, $org_w, $org_h;

	private $image;

	function __construct($imagepath = '') {
		if ( !extension_loaded('imagick') ) {
			$this->imagemagick = FALSE;
		} else {
			$this->imagemagick = TRUE;
		}

		if ( $imagepath != '' ) {
			$this->load_image($imagepath);
		}

	}

	public function load_image($imagepath) {
		$this->image = new Imagick( $imagepath );

		$this->org_w = $this->image->getImageWidth();
		$this->org_h = $this->image->getImageHeight();

		$this->w = $this->image->getImageWidth();
		$this->h = $this->image->getImageHeight();

	}

	// makes a 90% quality jpg
	public function set_jpg($compression = 90) {
		$this->image->setImageFormat('jpeg');
		$this->image->setImageCompressionQuality($compression);
	}

	public function save_image($imagepath, $keep = FALSE) {
		$this->image->writeImage($imagepath);

		if ( !$keep ) {
			$this->image->destroy();
		}

	}

	public function show_image($echo = FALSE, $continue_after_eco = FALSE) {

		if ( $echo ) {
			$format = strtolower($this->image->getImageFormat());
			header('Content-type: image/' . $format);
			echo $this->image;

			if ( !$continue_after_eco ) {
				die();
			}

		} else {
			return $this->image;
		}
	}


	public function create_thumbnail($filepath) {

		$this->resize_image(180, 180, '<');
		$this->image->setImageFormat('jpeg');
		$this->image->setImageCompressionQuality(90);

		$this->image->writeImage($filepath);

	}

	/* This function resize the image,
		if $w or $h is 0 it can be any value
		$operator mimic imagemagick cli syntax:
		! = ignore aspect ratio
		> = only shrink larger images
		^ = fill area, resize the image based on the smallest fitting dimension

	  */
	public function resize_image($w = 0, $h = 0, $operator = '') {

		if ( $operator == '>' ) {
			if ( $w > 0 and $this->w < $w ) {
				$w = $this->w;
			}
			if ( $h > 0 and $this->h < $h ) {
				$h = $this->h;
			}
		}

		if ( $w != 0 and $h != 0 and $operator != '!' ) {
			$bestfit = TRUE;
		} else {
			$bestfit = FALSE;
		}

		$this->image->scaleImage($w, $h, $bestfit);

		// gets new dimensions
		$this->w = $this->image->getImageWidth();
		$this->h = $this->image->getImageHeight();


	}


	public function crop_image($width, $height, $x, $y) {

		$this->image->cropImage($width, $height, $x, $y);

		// gets new dimensions
		$this->w = $this->image->getImageWidth();
		$this->h = $this->image->getImageHeight();


	}


} 