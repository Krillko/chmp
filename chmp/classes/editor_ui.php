<?php
/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-20
 * Time: 16:32
 */

class Editor_ui {

	function __construct() {


	}


	/**
	 * Shows navigation when logged into the editor
	 * @param string $mode
	 * @param bool $nofloat - force float false
	 * @param bool $edit
	 * @param int|null $page_id
	 * @param object $session \Session
	 * @param object $structure \Read_structure
	 * @return string
	 */
	public function editor_nav($mode = '', $nofloat = FALSE, $edit = FALSE, $page_id = NULL, $session = NULL, $structure = NULL) {

		$test = 1;

		if ( Config::get('float_navigation') == FALSE or $nofloat) {
			$float = false;
		} else {
			$float = true;
		}


		if (!$float) {
			$out = '<div class="chmp chmp-navigation-holder">';
		} else {
			$out .= '<div class="chmp chmp-nav-showhide-btn" id="chmp-nav-showhide"><p class="chmp chmp-ico-downarrow"></p></div>';

		}


		$out .= '
	<div class="chmp chmp-navigation" id="chmp-nav">

		<div class="chmp chmp-nav-edit'.( $float ? ' chmp-nav-float':'').'">';


	if (Config::get('language_on')) {

		$arr_lang = Config::get('languages');
		$flag = $arr_lang[$session->get('lang')]['flag'];

		$out .= '<div class="chmp chmp-nav-part chmp-nav-part-text chmp-nav-part-flag" >
					<img src="chmp/flags/'.$flag .'.png">
				</div>

		<div class="chmp chmp-nav-part chmp-nav-part-btn chmp-nav-part-separator"></div>';

	}





	if ($mode == '' or $mode == 'surf') {

		$out .= '
		<div class="chmp chmp-nav-part chmp-nav-part-text">
				<p>EDIT:</p>
			</div>

			<div class="chmp chmp-nav-part">
				<div class="chmp chmp-nav-onoff-holder">
					<div class="chmp chmp-nav-onoff chmp-nav-onoff-' . ( $edit ? 'on' : 'off' ) . '">
						<div class="chmp chmp-nav-onoff-inner">
							<a href="chmp/'.$page_id.'/?chmp-edit=1"><div class="chmp chmp-nav-onoff-btn chmp-nav-btn-on">ON</div></a>
							<a href="chmp/'.$page_id.'/?chmp-edit=0"><div class="chmp chmp-nav-onoff-btn chmp-nav-btn-off">OFF</div></a>
						</div>
					</div>
				</div>
			</div>


			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button"><p>Change image</p></div>
			</div>


			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-button-expandable">
					<div class="chmp chmp-input-expand-btn chmp-input-small chmp-submit chmp-do-expand"><p>Publish</p>
					</div>
					<div class="chmp chmp-input-expand"><p><a href="javascript:;"
					                                          class="chmp chmp-input-cancel">CANCEL</a> &nbsp; <a
							href="javascript:;" class="chmp chmp-input-confirm" id="chmp-do-publish">OK!</a></p></div>
				</div>
			</div>


			<div class="chmp chmp-nav-part chmp-start-hidden" id="chmp-save-animation">
				<img src="chmp/editordesign/save-animation.gif" alt="save-animation" width="26" height="17" class="chmp chmp-save-icon">
			</div>

			';
	} else if ($mode == 'structure' or $mode == 'files' or $mode == 'users' or $mode == 'language') {

		$out .= '

			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<a href="chmp/'.$structure->get_first_page_in_lang($session->get('lang')) .'">
				<div class="chmp chmp-input-dynamic chmp-button"><p class="chmp-ico-leftarrow chmp-ico-w-text"><span>Back to site</span></p></div>
				</a>
			</div>

			<div class="chmp chmp-nav-part chmp-nav-part-btn chmp-nav-part-separator"></div>


			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button chmp-button-active"><p class="chmp-ico-structure chmp-ico-w-text"><span>Structure</span></p></div>
			</div>

			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button"><p class="chmp-ico-files chmp-ico-w-text"><span>Files</span></p></div>
			</div>

			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button"><p class="chmp-ico-users chmp-ico-w-text"><span>Users</span></p></div>
			</div>';



	}


	// TODO: if your looking at a page and not structure, logout should take you to the published page (if published) otherwise to /chmp/index.php
	$out .= '
			<div class="chmp chmp-nav-right">
				<a href="chmp/index.php?do=logout"><div class="chmp chmp-logout" id="chmp-logout"></div></a>

			</div>
	';



	if ($mode == '' or $mode == 'surf') {
	$out .= '		<div class="chmp chmp-nav-right">
						<a href="chmp/structure.php">
						<div class="chmp chmp-nav-part chmp-nav-part-btn">
							<div class="chmp chmp-input-dynamic chmp-button"><p class="chmp-ico-settings chmp-ico-w-text"><span>SITE SETTINGS</span></p></div>
						</div>
						</a>
					</div>';

	}

	$out .= '</div>
		<!-- end .chmp-nav-edit -->

	</div>
	<!-- end #chmp-nav -->
';

		if (!$float ) {
			$out .= '</div>
<!-- end chmp-navigation-holder -->';
		}

		return ( $out );


	}


} 