<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-03-17
 * Time: 16:10
 */
class Show_content {

	public $edit;

	/**
	 * @var \Read_tempate
	 */
	private $template;

	function __construct($template, $content) {
		$this->template = $template;
		$this->content  = $content;
		$this->edit     = FALSE;
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

	public function set_edit($edit = TRUE) {
		$this->edit = $edit;
		$test       = 1;
	}


	/** Returns html for a content area
	 *
	 * @param $element
	 * @internal param bool $edit
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
	 * @param $element ,  $empty_module = false =  array,  $empty_module = true = string
	 * @param $empty_module
	 * @return string
	 */
	public function show_module($contentarea_uid, $element, $empty_module = FALSE) {
		$output = '';

		if ( $empty_module ) {
			$uid = $element;
		} else {
			$uid = $element[ 'uid' ];


		}

		$module_design        = $this->template->get_module_design($contentarea_uid, $uid);
		$module_attr          = $this->template->get_module_elements($contentarea_uid, $uid, 'attr');
		$module_elements_text = $this->template->get_module_elements($contentarea_uid, $uid, 'text');
		$module_elements_img  = $this->template->get_module_elements($contentarea_uid, $uid, 'img');
		$module_elements_plugin = $this->template->get_module_elements($contentarea_uid, $uid, 'plugin');

		$module = new simple_html_dom();
		$module->load($module_design);

		$test = 1;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - texts
		foreach ( $module_elements_text as $module_elements_row ) {
			foreach ( $module->find('*[data-chmp-name*=' . $module_elements_row[ 'data-chmp-name' ] . ']') as $thisTag ) {
				// checking so that the template doesnt have texts and images named the same
				if ( Config::tag_kind($thisTag->tag) == 'text' ) {

					// adds contenteditable
					if ( $this->edit ) {
						$thisTag->setAttribute('contenteditable', 'true');
					}

					if ( !$empty_module and @trim($element[ 'text' ][ $module_elements_row[ 'data-chmp-name' ] ]) != '' ) {
						$thisTag->innertext = $element[ 'text' ][ $module_elements_row[ 'data-chmp-name' ] ];
					} else {
						$test = $thisTag->attr[ 'data-chmp-notempty' ];
						if ( $this->edit ) {
							$thisTag->innertext = '&#' . Config::get('empty_area_chr') . ';';
						} else if ( $thisTag->attr[ 'data-chmp-notempty' ] !== NULL ) {
							$thisTag->innertext = '&nbsp;';

						} else {
							$thisTag->innertext = '';

						}

					}

				}
			}
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - images
		foreach ( $module_elements_img as $module_elements_row ) {
			foreach ( $module->find('*[data-chmp-name*=' . $module_elements_row[ 'data-chmp-name' ] . ']') as $thisTag ) {
				// checking so that the template doesnt have texts and images named the same
				if ( Config::tag_kind($thisTag->tag) == 'img' ) {

					$thisTag->outertext = $this->show_image(( $empty_module ? '' : $element[ 'img' ][ $module_elements_row[ 'data-chmp-name' ] ] ), $module_elements_row);

				}
			}
		}

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - plugins


		foreach ( $module_elements_plugin as $module_elements_row) {

			foreach ( $module->find('*[data-chmp-plugin=' . $module_elements_row[ 'data-chmp-plugin' ] . ']') as $thisTag ) {


				if (is_file('chmp/plugins/chmp_plugin_'.$module_elements_row[ 'data-chmp-plugin' ].'.php')) {
					$chmp_plugin_vars = $module_elements_row;
					$chmp_plugin_content = $thisTag->innertext;



					if ($this->edit and $module_elements_row[ 'data-chmp-plugin-settings' ] != '') {

						$plugin_uid = uniqid('plugin');

						$plugin_settings = json_decode($module_elements_row[ 'data-chmp-plugin-settings' ], true);

						$out = '<div class="'.($module_elements_row['data-chmp-plugin-settings-class'] != '' ? $module_elements_row['data-chmp-plugin-settings-class']:'chmp chmp-plugin-settings').'">
						<form class="chmp-plugin-settings-form"
								id="'.$plugin_uid.'"
								data-chmp-plugin = "'.$module_elements_row[ 'data-chmp-plugin' ].'"
								>';

						foreach ($plugin_settings as $ps_key => $ps_value) {

							$plugin_standard = ' id="'.$ps_key.'" name="'.$ps_key.'" class="chmp-plugin-setting" data-chmp-plugin-uid="'.$plugin_uid.'"'
								.($ps_value['required'] ? ' required':'')
								.($ps_value['placeholder'] != '' ? ' placeholder="'.$ps_value['placeholder'].'"':'')
								.($ps_value['style'] != '' ? 'style="'.$ps_value['style'].'"':'');

							$out .= '<div class="chmp-plugin-settings-row"><p>'.$ps_value['title'].'</p>';



								switch($ps_value['type']) {

									case 'text':
									$out .= '<input type="text" '.$plugin_standard
											.($ps_value['size'] != '' ? ' size="'.$ps_value['size'].'"':'')
											.'>';

									break;

									case 'number':
									$out .= '<input type="number" '.$plugin_standard
										.(is_numeric($ps_value['step']) ? ' step="'.$ps_value['step'].'"':'')
										.'>';

									break;

									case 'checkbox':
									$out .= '<input type="checkbox" '.$plugin_standard
											.'value="'.($ps_value['value'] != 1 ? $ps_value['value']:'1').'"'
											.'>';
									break;


									case 'textarea':
									$out .= '<textarea id="'.$ps_key.'" '.$plugin_standard
											.'rows="'.($ps_value['rows'] != 4 ? $ps_value['rows']:'4').'"'
											.'>';

									$out .= '</textarea>';

									break;

									case 'select':
										$out .= '<select id="'.$ps_key.'" '.$plugin_standard.'>';

										if (is_array($ps_value['options'])) {
											foreach ($ps_value['options'] as $psv_value) {
												$out .= '<option value="'.$psv_value[0].'">'.$psv_value[1].'</option>';

											}

										}

										$out .= '</select>';

										break;

								}






							$out .= '</div>';

						}


						$out .= '</form></form></div>';


						$thisTag->outertext = $out;

					} else {

						$test = 1;

						if ($module_elements_row[ 'data-chmp-plugin-apikeys' ] == 'true') {
							require_once ('chmp/apikeys.php');
						}


						include('chmp/plugins/chmp_plugin_'.$module_elements_row[ 'data-chmp-plugin' ].'.php');


						if ($module_elements_row[ 'data-chmp-plugin-type' ] == 'simple') {
							$thisTag->outertext = $chmp_output_plugin;
						} else {
							$plugin_uid = uniqid('plugin');
							$showplugin[$plugin_uid] = new $module_elements_row[ 'data-chmp-plugin' ]($chmp_plugin_vars , $chmp_plugin_content);

							$thisTag->outertext = $showplugin[$plugin_uid]->show_plugin();

						}

					}


				}

			}



		}


		if ( $this->edit ) {
			$module_attr = $this->template->get_module_elements($contentarea_uid, $uid, 'attr');
			$attr        = '';
			$this_uid    = uniqid('mod');
			// keeps attr
			foreach ( $module_attr as $attrK => $attrV ) {
				$attr .= ' ' . $attrK . '="' . $attrV . '"';
			}

			// adds temporary unique id
			$attr .= ' data-chmp-tuid="' . $this_uid . '"';

			//$output = '<li><div class="chmp-edit-module" ' . $attr . ' contenteditable="true">
			$output = '<li><div class="chmp-edit-module" ' . $attr . ' >
								<div class="chmp-edit-module-btns">
									<div class="chmp-dragicon"></div>
									<!--Buttons here for change module -->
									<div class="chmp-deletemodule">
										<div class="chmp-deletemodule-confirm">
											<div>Really delete? <a href="javascript:;" class="chmp_delete_module" data-chmp-delete-mod="' . $this_uid . '">OK</a></div>
										</div>
								</div>
							</div>' . $module->save() . '</div></li><!-- end chmp module -->';


		} else {
			$output = $module->save();
		}

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

		$out = ( $this->edit ? '<div class="chmp-edit-holder">' : '' );

		if ( is_array($element) ) { // images exists

			$out .= '<img src="chmp/assets/images/' . $element[ 'src' ] . '"';

			if ( $element[ 'width' ] > 0 ) {
				$out .= ' width="' . $element[ 'width' ] . '"';
			}

			if ( $element[ 'height' ] > 0 ) {
				$out .= ' height="' . $element[ 'height' ] . '"';
			}

			if ( $element[ 'alt' ] != '' ) {
				$out .= ' alt="' . $element[ 'alt' ] . '" title="' . $element[ 'alt' ] . '"';
			}

			// original image - id same as manifest
			$out .= ' data-chmp-orgimgid="' . $element[ 'orgImgId' ] . '"';


		} else {
			$out .= '<img src="chmp/editordesign/img_placeholder.png"';

			if ( $design[ 'data-chmp-width' ] > 0 or $design[ 'width' ] > 0 ) {
				$out .= ' width="' . ( $design[ 'data-chmp-width' ] > 0 ? $design[ 'data-chmp-width' ] : $design[ 'width' ] ) . '"';
			}
			if ( $design[ 'data-chmp-height' ] > 0 or $design[ 'height' ] > 0 ) {
				$out .= ' height="' . ( $design[ 'data-chmp-height' ] > 0 ? $design[ 'data-chmp-height' ] : $design[ 'height' ] ) . '"';
			}

		}

		// adds a temporary unique id, to communicate to and from iframe in javascript
		$out .= ' data-chmp-tuid="' . uniqid('img') . '"';

		$generated_attr = array( 'src', 'width', 'height', 'alt', 'title' );

		// adding template attibutes, except genrated, removes data-chmp- if not in edit mode
		foreach ( $design as $designKey => $designValue ) {
			if ( substr($designKey, 0, 10) == 'data-chmp-' ) {
				if ( $this->edit ) {
					$out .= ' ' . $designKey . '="' . $designValue . '"';
				}
			} elseif ( $designKey == 'class' ) {
				$out .= ' ' . $designKey . '="' . $designValue . ' chmp-editable' . ( $this->edit ? ' chmp-editable-img' : '' ) . '"';
			} elseif ( !in_array($designKey, $generated_attr) ) {
				$out .= ' ' . $designKey . '="' . $designValue . '"';
			}
		}

		// adds edit classes if template has no class
		if ( !array_key_exists('class', $design) ) {
			$out .= ' class="chmp-editable' . ( $this->edit ? ' chmp-editable-img' : '' ) . '"';

		}

		$out .= '>';

		$out .= ( $this->edit ? '</div><!-- end  chmp-edit-holder -->' : '' );

		return $out;
	}

	public function get_title() {
		// TODO: make a seo friendly path here
		return ( $this->content->get('title') );
	}


}