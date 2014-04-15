<?php

/**
 * Builds an entire page and returns as html
 */
class Show_page {

	public $html_output, $edit;


	public function show_page() {

		return $this->html_output;

	}


	public function load_page($page_id, $edit = FALSE) {

		/* Get content
			Reads content from chmp/content/[pagenumber].json and makes an array
		*/
		$content = new Read_content( $page_id );

		/* Reads template
			Convert chmp/templates/[template].[html|php] to an array
		*/
		$template_raw = new Read_template_file( $content->get('templatefile') );

		$html = new simple_html_dom();
		$html->load($template_raw->template);

		$template = new Read_tempate( $html );

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - gets navigation
		$nav = new Show_navigation( $content );
		$nav->set_currentpage($page_id);

		$show_content = new Show_content( $template, $content );
		$show_content->set_edit($edit);

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - starts actual output

		/* empty out contentareas , then reloads the html
			The reason we do this is because ->find() is looking in the original dom, and not in the manipulated version
		*/
		foreach ( $html->find('content') as $contentarea ) {
			$contentarea->innertext = '';
		}

		$html->load($html->save()); // updates simple_html_doms internal dom-tree

		// - - - - - - - - - - - - - - - - - - - - Changes every text/img outside contentareas
		foreach ( $html->find('*[data-chmp-name]') as $outsideContent ) {

			if ( Config::tag_kind($outsideContent->tag) == 'text' ) {
				$outsideContent->innertext = $show_content->show_outside_content($outsideContent, 'text');
			} else if ( $outsideContent->tag == 'img' ) {
				$outsideContent->outertext = $show_content->show_outside_content($outsideContent, 'img');
			}

		}

		// - - - - - - - - - - - - - - - - - - - - Builds the contentarea

		foreach ( $html->find('content') as $contentarea ) {

			// converts chmp contentarea to a div
			if ( $edit ) {
				$attr = '';
				// keeps attr
				foreach ( $contentarea->getAllAttributes() as $attrK => $attrV ) {
					$attr .= ' ' . $attrK . '="' . $attrV . '"';
				}

				$contentarea->outertext = '<div class="chmp-edit-contentarea" ' . $attr . '>' . $show_content->show_contentarea($contentarea) . '</div>';

			} else {
				$contentarea->outertext = $show_content->show_contentarea($contentarea);

			}


		}

		// - - - - - - - - - - - - - - - - - - - - Set page title
		$html->find('title', 0)->innertext = $show_content->get_title();

		/* - - - - - - - - - - - - - - - - - - - - Adding scripts
			the method makeup() is not documented in simple_html_dom
		*/

		$add_scripts .= '<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp.css"/>';

		$add_scripts_settings = '<script type="text/javascript">
			if ( typeof console === "undefined" || typeof console.log === "undefined" ) {console = {};console.log = function(){};console.warn=function(){};console.error = function () {}}
			var chmp = chmp || [];
				chmp.chmp_cnf_texts = ' . json_encode(Config::$chmp_cnf_texts) . ';


			';

		// check if we have a login tag
		$login = $html->find('login');
		if ( count($login) > 0 ) {
			$add_scripts .= '<script type="text/javascript" src="chmp/js/jquery.powertip.min.js"></script>';
			$add_scripts .= '<link rel="stylesheet" type="text/css" href="chmp/editordesign/jquery.powertip.css"/>';
			$add_scripts_settings .= "chmp.logintexts = " . json_encode(Config::get_logintext('sv', ''), JSON_FORCE_OBJECT) . ";";

			$html->find('login', 0)->outertext = '<a href="javascript:;" id="chmp-login-btn">' . Config::get_logintext('sv', 'login') . '</a>';

		}

		// add scripts if we are in editor mode

		if ( $edit ) {

			$add_scripts .= '<script type="text/javascript" src="chmp/js/editor.js"></script>';
			$add_scripts .= '<script type="text/javascript" src="chmp/js/featherlight.min.js"></script>';


		}

		$add_scripts_settings .= '</script>';

		$add_scripts .= '<script type="text/javascript" src="chmp/js/production.js"></script>';

		$head = $html->find("head", 0);
		// the method makeup() is not documented in simple_html_dom
		$head->outertext = $head->makeup() . $head->innertext . $add_scripts_settings . $add_scripts . '</head>';

		// - - - - - - - - - - - - - - - - - - - -  Shows navigation
		foreach ( $html->find('navigation') as $navigation_row ) {
			$thisNavigationOutput      = $nav->get_nav('ul', $navigation_row->getAllAttributes(), 0, null);
			$navigation_row->outertext = $thisNavigationOutput;
		}

		$this->html_output = $html->save();

		/* clears the memory:  according to the instructions from the creators of simple_html_dom memory may become a problem
		with this plugin.
		*/
		$html->clear();
		unset( $html );


	}

	private function editorbuttons() {


	}


}