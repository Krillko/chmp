<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-24
 * Time: 18:41
 */
class Image_geometry {
	public $manifest, $original_img_id, $org_w, $org_h, $frame_w, $frame_h, $frame_percent,
		$show_editor_w, $show_editor_h, $chmp_attr, $start_w, $start_h, $start_scale, $start_orientation;

	function __construct($original_img_id, $manifest, $frame_w = 0, $frame_h = 0, $chmp_attr = null) {

		// TODO: Get this value either for js or from settings
		( $frame_w == 0 ? $this->frame_w = 1004 : $this->frame_w = $frame_w );
		( $frame_h == 0 ? $this->frame_h = 542 : $this->frame_h = $frame_h );

		$this->original_img_id = $original_img_id;
		$this->manifest        = $manifest;

		$this->imageinfo = $this->manifest->get_image($this->original_img_id, 'array');

		$this->org_w = $this->imageinfo[ 'w' ];
		$this->org_h = $this->imageinfo[ 'h' ];

		if ( $this->org_w <= $this->frame_w and $this->org_h <= $this->frame_h ) {
			$this->show_editor_w = $this->org_w;
			$this->show_editor_h = $this->org_h;
			$this->frame_percent = 1;
		} else {
			$percent_w           = $this->frame_w / $this->org_w;
			$percent_h           = $this->frame_h / $this->org_h;
			$this->frame_percent = ( $percent_h < $percent_w ? $percent_h : $percent_w );
			$this->show_editor_w = floor($this->org_w * $this->frame_percent);
			$this->show_editor_h = floor($this->org_h * $this->frame_percent);
		}

		if ( is_array($chmp_attr) ) {
			$this->chmp_attr = $chmp_attr;
		}


	}


	public function get_editor($type = 'html') {

		switch ($type) {

			case 'comma':
				return $this->show_editor_w . ',' . $this->show_editor_h;
				break;

			default:
				return ' width="' . $this->show_editor_w . '" height="' . $this->show_editor_h . '"';
				break;

		}


	}

	public function get_original($type = 'html') {
		switch ($type) {

			case 'comma':
				return $this->org_w . ',' . $this->org_h;
				break;

			default:
				return ' width="' . $this->org_w . '" height="' . $this->org_h . '"';
				break;

		}


	}

	public function get_ratio() {

		if ( $this->chmp_attr[ 'data-chmp-keepwidth' ] == TRUE and $this->chmp_attr[ 'data-chmp-keepheight' ] == TRUE ) {
			return 'chmp.ratio = ' . $this->chmp_attr[ 'data-chmp-width' ] / $this->chmp_attr[ 'data-chmp-height' ] . ';';
		} else {
			return null;
		}


	}


	public function get_crop_start() {

		$w = $this->chmp_attr[ 'data-chmp-width' ];
		$h = $this->chmp_attr[ 'data-chmp-height' ];

		// test override
		$w = 500;
		$h = 300;

		if ( $w >= $h ) {
			$orientation = 'l';
		} else {
			$orientation = 'p';
		}

		// org_w 1805x2723
		// show_editor_w 1004x542

		$scale_w = $this->org_w / $w;
		$scale_h = $this->org_h / $h;

		if ( $orientation == 'l' and
			( floor($h * $scale_w) <= $this->org_h )
		) {
			$output_w                = $this->org_w;
			$output_h                = floor($h * $scale_w);
			$this->start_scale       = $scale_w;
			$this->start_orientation = 'l';

		} elseif ( floor($w * $scale_h) <= $this->org_w ) {
			$output_h                = $this->org_h;
			$output_w                = floor($w * $scale_h);
			$this->start_scale       = $scale_h;
			$this->start_orientation = 'p';
		} else {
			die( 'an unexpected error occured, Image_geometry->get_crop_start, <br><br>please report these values:
				$w: ' . $w . ',<br>
				$h: ' . $h . ',<br>
				$orientation: ' . $orientation . ',<br>
				$scale_w: ' . $scale_w . ',<br>
				$scale_h: ' . $scale_h . ',<br>
				$this->org_w: ' . $this->org_w . ',<br>
				$this->org_h: ' . $this->org_h
			);
		}

		$this->start_w = $output_w;
		$this->start_h = $output_h;

		return '0,0,' . $output_w . ',' . $output_h;

	}

	public function get_min_max() {

		// TODO: Figure out a way to do this
		/*
		if (!is_numeric($this->start_w)) {
			$this->get_crop_start();
		}

		$test = 1;

		if (
			( // width restricted
				isset( $this->chmp_attr['data-chmp-maxwidth'] ) and isset( $this->chmp_attr['data-chmp-minwidth'] ) and $this->chmp_attr['data-chmp-keepwidth'] != true
			)
			and
			(   // height restricted
				(isset( $this->chmp_attr['data-chmp-maxheigth'] ) and isset( $this->chmp_attr['data-chmp-minheight'] )) and $this->chmp_attr['data-chmp-keepheight'] != true
			)
		) {

			if ($this->start_orientation == 'l') {


					$maxW = $this->start_w;
					$min_to_max = $this->chmp_attr['data-chmp-minwidth'] / $this->chmp_attr['data-chmp-maxwidth'];
					$minW = floor($this->start_w * $min_to_max);





				$test = 1;


			}

			return 'chmp.minSize = ['.$minW.','.$minH.'];
					chmp.maxSize = ['.$maxW.','.$maxH.'];
					';


		} else {
			$test = 1;
		}


		$test = 1;

		*/

		return '';

	}


} 