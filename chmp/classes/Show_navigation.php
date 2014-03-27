<?php


class Show_navigation {

	public $nav_in, $currentpage, $templatefile, $lang, $structure;

	function __construct($content) {

		$this->templatefile = $content->get('templatefile');
		$this->lang         = $content->get('lang');
		$this->structure    = $content->get('structure');

		$stucturefile = 'chmp/content/structure_' . $this->structure . '.json';

		$structure_raw = file_get_contents($stucturefile);
		$this->nav_in  = @json_decode($structure_raw, TRUE);

		if ( $data === null
			&& json_last_error() !== JSON_ERROR_NONE
		) {
			die( "<h2>Error</h2><p>Unable to read chmp/templates/" . $stucturefile . "</p>
			<code>" . Tools::json_error(json_last_error()) . "</code>" );
		}


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
	 * @param string $type 'ul' returns <ul>, 'sitemapxml'
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

		if ( is_array($this->nav_in[ 'nav' ]) ) {
			$out = $this->build_recursive($type, $this->nav_in[ 'nav' ], $attr);

		}

		return $out;
	}


	// recursivly build the navigation
	private function build_recursive($type = 'ul', $in = array(), $attr = array(), $depth = 1, $parentActive = FALSE) {
		$out = '';

		if ( count($in) > 0 ) {
			if ( $type == 'ul' ) {
				$out = PHP_EOL . '<ul'
					. ( $attr[ 'data-chmp-id' ] != '' ? ' id="' . $attr[ 'data-chmp-id' ] . '"' : '' )
					. ( $attr[ 'data-chmp-class' ] != '' ? ' class="' . $attr[ 'data-chmp-class' ] . '"' : '' )
					. '>';
			}

			foreach ( $in as $in_key => $in_value ) {
				$class       = '';
				$notSelected = TRUE;

				if ( $type == 'sitemapxml' ) {
					// TODO: change this to real url later
					$out .= ' <url><loc>http://www.example.com/?page=' . $in_key . '</loc></url>';

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
				if ( is_array($in_value[ 'children' ]) and ( !isset( $attr[ 'data-chmp-end' ] ) or $depth < $attr[ 'data-chmp-end' ] ) ) {
					$out .= $this->build_recursive($type, $in_value[ 'children' ], $attr, $depth + 1, $parentActive);

				}

				// close tag
				if ( $type == 'ul' ) {
					$out .= '</li>' . PHP_EOL;
				}
			}
			// close tag
			if ( $type == 'ul' ) {
				$out .= '</ul>';
			}
		}

		return ( $out );

	}

	// find all child of a page and see if any of them are the active page
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