<?php


class Show_navigation {

	/**
	 * @var \Read_structure
	 */
	private $structure;

	public $nav_in, $currentpage, $templatefile, $lang;

	/**
	 * @param $content \Read_content
	 * @param $structure \Read_structure
	 */
	function __construct($content, $structure) {

		if ($content !== null) {
			$this->templatefile = $content->get('templatefile');
			$this->lang         = $content->get('lang');

		}

		$this->structure    = $structure;

		// TODO: replace here to database driven

		$this->nav_in = $structure->get_structure();

		$test = 1;

	}

	public function set_currentpage($page_id) {
		if ( is_numeric($page_id) ) {
			$this->currentpage = $page_id;
		} else {
			$this->currentpage = null;
		}
	}

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - make a pretty url link

	public function view_prettyUrl($pageId) {
		// TODO: this is not even started
		return ( $pageId );

	}



	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - builds the output

	// returns <ul> with navigation

	/** Returns formatted navigation
	 * TODO: include start level
	 * TODO: class on a
	 * TODO: id on a
	 * TODO: option to wrap text in a with span etc
	 *
	 * @param string $type 'ul' returns <ul>, 'sitemapxml', 'structure'
	 * @param array $attr attibutes from <navigation> tag
	 * @return string formatted html or xml
	 */
	public function get_nav($type = 'ul', $attr = array()) {
		$out = '';
		// preprocessing
		if ( $attr[ 'data-chmp-end' ] != '' ) {
			if ( Tools::cleanInt($attr[ 'data-chmp-end' ]) !== FALSE ) {
				$attr[ 'data-chmp-end' ] = Tools::cleanInt($attr[ 'data-chmp-end' ]);
			} else {
				unset( $attr[ 'data-chmp-end' ] );
			}
		}

		if ( is_array($this->nav_in) ) {
			$out = $this->build_recursive($type, $this->nav_in, $attr);

		}

		return $out;
	}


	/**
	 * recursivly build the navigation
	 * @param string $type
	 * @param array $in
	 * @param array $attr
	 * @param int $depth
	 * @param bool $parentActive
	 * @return string
	 */
	private function build_recursive($type = 'ul', $in = array(), $attr = array(), $depth = 1, $parentActive = FALSE) {
		$out = '';

		if ( count($in) > 0 ) {
			if ( $type == 'ul' ) {
				$out = PHP_EOL . '<ul'
					. ( $attr[ 'data-chmp-id' ] != '' ? ' id="' . $attr[ 'data-chmp-id' ] . '"' : '' )
					. ( $attr[ 'data-chmp-class' ] != '' ? ' class="' . $attr[ 'data-chmp-class' ] . '"' : '' )
					. '>';
			} else if ($type == 'structure') {

				$out = PHP_EOL . '<ol class="dd-list" id="chmp_structure_active">';

			}

			foreach ( $in as $in_key => $in_value ) {
				$class       = '';
				$notSelected = TRUE;

				if ( $type == 'sitemapxml' ) {
					// TODO: change this to real url later
					$out .= ' <url><loc>http://www.example.com/?page=' . $in_key . '</loc></url>';

				} elseif ( $type == 'structure' ) {

					$out .= '<li class="dd-item dd3-item" data-id="'.$in_key.'">
								<div class="dd-handle dd3-handle"></div>
								<div class="dd3-content">
									<!-- title -->
									<div class="chmp-struct-title">
										<div class="chmp-struct-icon"></div>
										<div class="chmp-struct-text"></div>
										<div class="chmp-struct-skip-icon"></div>
									</div>
									<div class="chmp-struct-goto"><a href="javascript:;">Show</a></div>
									<!-- end title -->

								</div>';


				} else { // default: ul
					// check if this is the active page
					if ( $in_key == $this->currentpage and $attr[ 'data-chmp-active' ] != '' ) {
						$class .= $attr[ 'data-chmp-active' ] . ',';
						$notSelected  = FALSE;
						$parentActive = TRUE;
					}

					// check if this or any of the children to this page is the active page
					if ( $this->find_page_recursive($in_value[ 'children' ]) or $in_key == $this->currentpage ) {
						if ( $attr[ 'data-chmp-selected' ] != '' ) {
							$class .= $attr[ 'data-chmp-selected' ] . ',';
						}
						$notSelected = FALSE;
					}

					// check if any of the children is active
					if ( $this->find_page_recursive($in_value[ 'children' ]) ) {
						if ( $attr[ 'data-chmp-parents' ] != '' ) {
							$class .= $attr[ 'data-chmp-parents' ] . ',';
						}
						$notSelected = FALSE;
					}

					// check if this is the direct parent of active
					if ( $this->find_page_recursive($in_value[ 'children' ], 'active', FALSE) ) {
						if ( $attr[ 'data-chmp-parent' ] != '' ) {
							$class .= $attr[ 'data-chmp-parent' ] . ',';
						}
					}
					// adds notselected
					if ( $attr[ 'data-chmp-notselected' ] != '' and $notSelected ) {
						$class .= $attr[ 'data-chmp-notselected' ] . ',';
					}

					// adds a class depending on depth
					if ( $attr[ 'data-chmp-depth' ] != '' ) {
						$class .= $attr[ 'data-chmp-depth' ] . $depth . ',';
					}

					$out .= PHP_EOL . '<li ' . ( $class != '' ? 'class="' . substr($class, 0, -1) . '"' : '' ) . '><a href="' . $this->view_prettyUrl($in_key) . '">' . $in_value[ 'name' ] . '</a>';
				}

				// finds the children of this page
				if ( is_array($in_value[ 'children' ])
					and ( !isset( $attr[ 'data-chmp-end' ] ) or $depth < $attr[ 'data-chmp-end' ] or $type == 'structure') ) {
					$out .= $this->build_recursive($type, $in_value[ 'children' ], $attr, $depth + 1, $parentActive);

				}

				// close tag
				if ( $type == 'ul' or $type == 'structure' ) {
					$out .= '</li>' . PHP_EOL;
				}
			}
			// close tag
			if ( $type == 'ul' ) {
				$out .= '</ul>';
			} else if ($type == 'structure') {
				$out .= '</ol>';
			}
		}

		return ( $out );

	}

	/**
	 * find all child of a page and see if any of them are the active page
	 * @param array $in
	 * @param string $find
	 * @param bool $recursive
	 * @return bool
	 */
	private function find_page_recursive($in = array(), $find = 'active', $recursive = TRUE) {

		if ( $this->currentpage === NULL or !isset( $this->currentpage ) ) {
			return FALSE;
		}

		if ( is_array($in) ) {
			foreach ( $in as $in_key => $in_value ) {
				if ( $find == 'active' and $in_key == $this->currentpage ) {
					return TRUE;
				}

				if ( is_array($in_value[ 'children' ]) AND $recursive ) {
					if ( $this->find_page_recursive($in_value[ 'children' ], $find, $recursive) ) {
						return TRUE;
					}
				}

			}


		}

		return FALSE;

	}


}