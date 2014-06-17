<?php

/**
 * Created by PhpStorm.
 * User: Kristoffer Ekendahl
 * Date: 2014-03-17
 * Time: 12:57
 */
class Read_template_file {
	function __construct($filepath = 'example.html', $filepath_prefix = '') {
		$this->filepath = $filepath_prefix . 'chmp/templates/' . $filepath;
		$this->template = file_get_contents($this->filepath);
	}


}

class Read_tempate {
	public $template;


	function __construct($html) {
		$this->html     = $html;
		$this->template = array();
		$content        = $this->html->find('content');

		foreach ( $content as $content_row ) {

			// gets the id from the tag content area
			// data-chmp-uid is required and has to be numeric

			$content_uid = $content_row->getAttribute('data-chmp-uid');

			if ( is_numeric($content_uid) ) {

				// get all the attributes from tag content area
				$this->template[ $content_uid ][ 'attr' ] = $content_row->getAllAttributes();

				// array for all the modules
				$this->template[ $content_uid ][ 'modules' ] = array();

				// find the modules
				foreach ( $content_row->find('module') as $module_row ) {

					$moduleUid = $module_row->getAttribute('data-chmp-uid');

					if ( is_numeric($moduleUid) ) {
						$this->template[ $content_uid ][ 'modules' ][ $moduleUid ] = array(
							'html' => $module_row->innertext,
							'attr' => $module_row->getAllAttributes()
						);

						// get all editable text, images
						foreach ( $module_row->find('*[data-chmp-name]') as $editable_row ) {

							$thisEditableName = $editable_row->getAttribute('data-chmp-name');
							$thisEditableTag = Config::tag_kind($editable_row->tag);

							if ( is_array($this->template[ $content_uid ][ 'modules' ][ $moduleUid ][ $thisEditableTag ][ $thisEditableName ]) ) {
								//$error_log->add_warning('Editable element "'.$thisEditableTag.'" with duplicate name "'.$thisEditableName.'" in content uid '.$content_uid.' module '.$moduleUid.'. Attributes only taken from first occurance');
							} else {
								$this->template[ $content_uid ][ 'modules' ][ $moduleUid ][ $thisEditableTag ][ $thisEditableName ] = $editable_row->getAllAttributes();
							}
						}

						// get plugins

						foreach ( $module_row->find('plugin') as $plugin_row) {

							$thisPluginName = $plugin_row->getAttribute('data-chmp-plugin');

							$this->template[ $content_uid ][ 'modules' ][ $moduleUid ]['plugin'][$thisPluginName] =  $plugin_row->getAllAttributes();



						}



					}
				}

			} else {
				//$error_log->add_error('data-chmp-uid is missing or is not numeric in '.PHP_EOL.$content_row->outertext);

			}
		}


	}

	/** Returns a string with module (raw) html
	 * @param $content_uid
	 * @param $module_uid
	 * @return string
	 */
	public function get_module_design($content_uid, $module_uid) {
		return ( $this->template[ $content_uid ][ 'modules' ][ $module_uid ][ 'html' ] );
	}

	/** Returns an array with module elements
	 *
	 * @param $content_uid
	 * @param $module_uid
	 * @param string $type 'all', 'text', 'img', 'attr'
	 * @return array
	 */
	public function get_module_elements($content_uid, $module_uid = '', $type = 'all') {
		if ( $type == 'all' ) {

			if ( $module_uid == '' and is_array($this->template[ $content_uid ][ 'modules' ]) ) {
				$output = array();

				foreach ( $this->template[ $content_uid ][ 'modules' ] as $module_row ) {
					$output[ $module_row[ 'attr' ][ 'data-chmp-uid' ] ] = $module_row[ 'attr' ][ 'data-chmp-name' ];
				}

				return $output;

			} else if ( is_array($this->template[ $content_uid ][ 'modules' ][ $module_uid ]) ) {
				return $this->template[ $content_uid ][ 'modules' ][ $module_uid ];
			}
		} else if ( $type == 'html' ) {
			return $this->template[ $content_uid ][ 'modules' ][ $module_uid ][ $type ];
		} else {
			if ( is_array($this->template[ $content_uid ][ 'modules' ][ $module_uid ][ $type ]) ) {
				return $this->template[ $content_uid ][ 'modules' ][ $module_uid ][ $type ];
			}
		}

		$array = array();

		return ( $array );
	}


}