<?php

/**
 * Builds an entire page and returns as html
 */
class Show_page {

	/**
	 * @var \Read_structure
	 */
	private $structure;

	/**
	 * @var \Session
	 */
	private $session;

	private $html_output, $edit, $login, $baseurl, $page_id;

	function __construct($edit, $login, $baseurl, $structure, $session) {
		$this->edit    = $edit;
		$this->login   = $login;
		$this->baseurl = $baseurl;
		$this->structure = $structure;
		$this->session = $session;
	}

	/**
	 * Shows currently loaded page
	 * @return string - html of entire page
	 */
	public function show_page() {
		return $this->html_output;
	}

	/**
	 * Loads a specific page into $this->html_output;
	 * @param int $page_id
	 */
	public function load_page($page_id) {
		$this->page_id = $page_id;

		/* Get content
			Reads content from chmp/content/[pagenumber].json and makes an array
		*/
		$content = new Read_content( $page_id, $this->edit, NULL , $this->structure);

		$pageinfo = $content->get_info();

		/* Reads template
			Convert chmp/templates/[template].[html|php] to an array
		*/
		$template_raw = new Read_template_file( $content->get('templatefile') );

		$html = new simple_html_dom();
		$html->load($template_raw->template);

		$template = new Read_tempate( $html );



		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - gets navigation
		$nav = new Show_navigation( $content, $this->structure, $this->session );
		$nav->set_currentpage($page_id);

		$show_content = new Show_content( $template, $content );
		$show_content->set_edit($this->edit);

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


		/**
		 * @var $contentarea \simple_html_dom
		 */
		foreach ( $html->find('content') as $contentarea ) {

			// converts chmp contentarea to a div
			if ( $this->edit ) {
				$attr = '';
				// keeps attr
				foreach ( $contentarea->getAllAttributes() as $attrK => $attrV ) {
					$attr .= ' ' . $attrK . '="' . $attrV . '"';
				}

				// add module

				$add_module = $this->editor_modulelist($contentarea->attr[ 'data-chmp-uid' ], $template);

				$contentarea->outertext = '<div class="chmp-edit-contentarea" id="chmp-edit-contentarea-' . $contentarea->attr[ 'data-chmp-uid' ] . '" ' . $attr . '>'
					. '<ul class="chmp-move-modules">'
					. $show_content->show_contentarea($contentarea)
					. '</ul></div>'
					. $add_module
					. '<!-- end chmp contentarea -->';
				//$contentarea->outertext = '<div class="chmp-edit-contentarea" ' . $attr . '>test</div><!-- end chmp contentarea -->';

			} else {
				$contentarea->outertext = $show_content->show_contentarea($contentarea);

			}


		}

		// - - - - - - - - - - - - - - - - - - - - Set page title
		$html->find('title', 0)->innertext = $show_content->get_title();

		// - - - - - - - - - - - - - - - - - - - -  Shows navigation
		foreach ( $html->find('navigation') as $navigation_row ) {
			$thisNavigationOutput      = $nav->get_nav('ul', $navigation_row->getAllAttributes(), 0, NULL);
			$navigation_row->outertext = $thisNavigationOutput;
		}

		/* - - - - - - - - - - - - - - - - - - - - Adding scripts
			the method makeup() is not documented in simple_html_dom
		*/

		if ( $this->login ) {
			$add_scripts .= '<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp.css"/>';
		} else {
			$add_scripts .= '<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp_notloggedin.css"/>';
		}

		$add_scripts_settings = '<script type="text/javascript">
			if ( typeof console === "undefined" || typeof console.log === "undefined" ) {console = {};console.log = function(){};console.warn=function(){};console.error = function () {}}
			var chmp = chmp || [];
				chmp.chmp_cnf_texts = ' . json_encode(Config::$chmp_cnf_texts) . ';
				chmp.edit_chr = ' . Config::get('empty_area_chr') . ';

			';

		// check if we have a login tag
		$logintag = $html->find('login');
		if ( count($logintag) > 0 ) {
			if ( $this->login ) {

				$html->find('login', 0)->outertext = '';


			} else { // adds loginbox and javascript

				$add_scripts .= '<script type="text/javascript" src="chmp/js/jquery.powertip.min.js"></script>';
			$add_scripts .= '<link rel="stylesheet" type="text/css" href="chmp/editordesign/jquery.powertip.css"/>';
			$add_scripts_settings .= "chmp.logintexts = " . json_encode(Config::get_logintext('sv', ''), JSON_FORCE_OBJECT) . ";";

			$html->find('login', 0)->outertext = '<a href="javascript:;" id="chmp-login-btn">' . Config::get_logintext('sv', 'login') . '</a>';

				$add_scripts .= '<script type="text/javascript" src="chmp/js/production.js"></script>';

			}

		}

		// add scripts if we are in editor mode

		if ( $this->login or $this->edit ) {

			$editor_ui = new Editor_ui();

			$add_scripts .= '<script type="text/javascript" src="chmp/js/loggedin.js"></script>
			<script type="text/javascript" src="chmp/js/editor.js"></script>';

			//if ( $this->edit ) {

				// sets missing values to default values
				if ($pageinfo['templatefile'] == '') {
					$pageinfo['templatefile'] = $content->get('templatefile');
				}


				$add_scripts_settings .= 'chmp.pageinfo = ' . json_encode($pageinfo) . ';';

				$uri_parts = explode('?', $_SERVER[ 'REQUEST_URI' ], 2);

				$add_scripts_settings .= 'chmp.path = "'.$this->baseurl.'";';

				$add_scripts_settings .= 'chmp.edit = '.($this->edit ? 'true':'false').';';

				// TODO: Move so that jquery ui, and jquery only loads if needed
				$add_scripts .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
				';
			//}

			$add_scripts .= '<script type="text/javascript" src="chmp/js/featherlight.min.js"></script>';

			$body = $html->find("body", 0);

			$body->outertext = $body->makeup() . '<!-- chmp is logged in -->'

				.( $this->edit ? $this->editor_textoptions() : '' )
				. $editor_ui->editor_nav('surf', FALSE, $this->edit, $this->page_id, $this->session, NULL)
				. $body->innertext . '</body>';


		}

		if ( $this->edit ) {

		}


		$add_scripts_settings .= '</script>';

		$head = $html->find("head", 0);
		// the method makeup() is not documented in simple_html_dom
		$head->outertext = $head->makeup() . $head->innertext . $add_scripts_settings . $add_scripts . '</head>';

		$this->html_output = $html->save();

		/* clears the memory:  according to the instructions from the creators of simple_html_dom memory may become a problem
		with this plugin.
		*/
		$html->clear();
		unset( $html );


	}


	/**
	 * Shows a list of avaliable modules, either to add a new one
	 * or to change an exisiting
	 * @param int $content_uid
	 * @param object $template
	 * @param null|int $module_uid optional - null (default): list to add new module, int: change existing
	 * @return string
	 */
	private function editor_modulelist($content_uid, $template, $module_uid = NULL) {

		if ( $module_uid === NULL ) {

			$all_modules = $template->get_module_elements($content_uid, $module_uid = '', $type = 'all');

			if ( count($all_modules) > 0 ) {

				$out .= '<form><div class="chmp chmp-add-module">

							<select id="chmp-add-new-module-' . $content_uid . '" class="chmp chmp-add-module-select">
								<option value="">Add module:</option>';
				foreach ( $all_modules as $amKey => $amValue ) {
					$out .= '<option value="' . $amKey . '">' . $amValue . '</option>';
				}

				$out .= '</select>
							<input type="button" value="+" class="chmp chmp-add-module-ok chmp-add-module-to" data-chmp-add-module-to="' . $content_uid . '">
						</div></form>';


			}


		} else {
			// code fore changeing an existing module
		}

		return $out;

	}

	/**
	 * Adds the code for textoptions, ie, bold/italic/etc
	 * from zenpen
	 * @return string
	 */
	private function editor_textoptions() {

		$out = '<div class="chmp_zen_text-options" id="chmp_text_options">
			<div class="chmp_zen_options">
				<span class="chmp_zen_no-overflow">
					<span class="chmp_zen_lengthen chmp_zen_ui-inputs">
						<button class="chmp_zen_url chmp_zen_useicons"></button>
						<input class="chmp_zen_url-input" type="text" placeholder="Type or Paste URL here"/>
						<button class="chmp_zen_bold">b</button>
						<button class="chmp_zen_italic">i</button>
						<button class="chmp_zen_quote">&rdquo;</button>
					</span>
				</span>
			</div>
		</div>';

		return $out;
	}




}