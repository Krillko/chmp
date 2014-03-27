<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-17
 * Time: 16:10
 */
class Show_content {


	function __construct($template, $content) {
		$this->template = $template;
		$this->content  = $content;

	}


	public function show_outside_content($element, $type = 'text') {
		$out       = '';
		$this_name = $element->getAttribute('data-chmp-name');

		if ( $type == 'text' ) {
			$out = $this->content->content[ 'content' ][ 'ext' ][ 'text' ][ $this_name ];
		} else if ( $type == 'text' ) {
			$out = '<img src="1.jpg">';
		}

		return $out;
	}

	/** Returns html for a content area
	 *
	 * @param $element
	 * @return string
	 */
	public function show_contentarea($element) {
		$out = '';

		$contentarea_uid = $element->getAttribute('data-chmp-uid');

		if ( is_array($this->content->content[ 'content' ][ $contentarea_uid ][ 'modules' ]) ) {
			foreach ( $this->content->content[ 'content' ][ $contentarea_uid ][ 'modules' ] as $cont_row ) {
				$out .= $this->show_module($contentarea_uid, $cont_row);
			}
		}

		return $out;
	}

	/** Returns html for a module
	 *
	 * @param $contentarea_uid
	 * @param $element
	 */
	public function show_module($contentarea_uid, $element) {
		$output = '';

		$module_design        = $this->template->get_module_design($contentarea_uid, $element[ 'uid' ]);
		$module_elements_text = $this->template->get_module_elements($contentarea_uid, $element[ 'uid' ], 'text');
		$module_elements_img  = $this->template->get_module_elements($contentarea_uid, $element[ 'uid' ], 'img');

		$module = new simple_html_dom();
		$module->load($module_design);

		foreach ( $module_elements_text as $module_elements_row ) {
			foreach ( $module->find('*[data-chmp-name*=' . $module_elements_row[ 'data-chmp-name' ] . ']') as $thisTag ) {
				// checking so that the template doesnt have texts and images named the same
				if ( Tools::tag_kind($thisTag->tag) == 'text' ) {
					$thisTag->outertext = $element[ 'text' ][ $module_elements_row[ 'data-chmp-name' ] ];
				}
			}
		}

		foreach ( $module_elements_img as $module_elements_row ) {
			foreach ( $module->find('*[data-chmp-name*=' . $module_elements_row[ 'data-chmp-name' ] . ']') as $thisTag ) {
				// checking so that the template doesnt have texts and images named the same
				if ( Tools::tag_kind($thisTag->tag) == 'img' ) {
					$thisTag->outertext = $this->show_image($element[ 'img' ][ $module_elements_row[ 'data-chmp-name' ] ], $module_elements_row);
				}
			}
		}

		$output = $module->save();

		$module->clear();
		unset( $module );

		return ( $output );

	}

	/** TODO: Add format support
	 * TODO: add get image size if not supplied
	 *
	 *
	 * @param array $element
	 * @param array $design
	 * @return string
	 */
	public function show_image($element, $design = array()) {

		if ( is_array($element) ) {

			$out = '<img src="chmp/assets/images/' . $element[ 'uid' ] . '.' . ( $element[ 'format' ] != '' ? $element[ 'format' ] : 'jpg' ) . '"';

			if ( $element[ 'width' ] > 0 ) {
				$out .= ' width="' . $element[ 'width' ] . '"';
			}

			if ( $element[ 'height' ] > 0 ) {
				$out .= ' height="' . $element[ 'height' ] . '"';
			}

			if ( $element[ 'alt' ] != '' ) {
				$out .= ' alt="' . $element[ 'alt' ] . '" title="' . $element[ 'alt' ] . '"';
			}

			$out .= '>';

		} else {
			/*
			  No image exist, we read size from template and add the empty image
			 */
			$out .= '<img src="chmp/editordesign/img_placeholder.png"';

			if ( $design[ 'data-chmp-width' ] > 0 or $design[ 'width' ] > 0 ) {
				$out .= ' width="' . ( $design[ 'data-chmp-width' ] > 0 ? $design[ 'data-chmp-width' ] : $design[ 'width' ] ) . '"';
			}
			if ( $design[ 'data-chmp-height' ] > 0 or $design[ 'height' ] > 0 ) {
				$out .= ' height="' . ( $design[ 'data-chmp-height' ] > 0 ? $design[ 'data-chmp-height' ] : $design[ 'height' ] ) . '"';
			}

			$out .= '>';

		}

		return $out;
	}

	public function get_title() {
		// TODO: make a seo friendly path here
		return ( $this->content->get('title') );
	}


}