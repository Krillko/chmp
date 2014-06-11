<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-07
 * Time: 11:01
 */
class Read_structure {

	/**
	 * @var \SQLite3
	 */
	private $db;

	private $page_id = NULL, $lang, $structure_array, $structure_array_flat, $file_list_json, $file_list_template, $path;

	/**
	 * @param $db \SQLite3
	 * @param null $page_id
	 * @param string $path
	 */
	function __construct($db, $page_id = NULL, $path = '') {
		if ( !is_null($page_id) ) {
			$this->page_id = $page_id;
		}

		$this->db   = $db;
		$this->path = $path;
	}

	/**
	 * Sets language
	 * @param {int} $lang
	 */
	public function set_lang($lang) {
		$this->lang = $lang;
	}


	/**
	 * Set language from page_id
	 * @param int $page_id
	 * @return int|false
	 */
	public function set_lang_from_page_id($page_id) {
		$sql = "SELECT lang FROM structure WHERE page_id = " . intval($page_id);

		$result = $this->db->querySingle($sql);

		if ( $result !== FALSE ) {
			$this->lang = $result;

			return $result;
		} else {
			return FALSE;
		}

	}


	/**
	 * Returns language id
	 * @return int
	 */
	public function get_lang() {
		return $this->lang;
	}


	/**
	 * Publish a page.
	 * If it's previously published the old version is renamed with datetime
	 * @param {int} $page_id
	 * @return bool
	 */
	public function publish($page_id) {

		if ( !isset( $page_id ) ) {
			$page_id = $this->page_id;
		}

		$test = 1;
		if ( is_file('chmp/content/' . $page_id . '_edit.json') ) {

			// Renames current content
			if ( is_file('chmp/content/' . $page_id . '.json') ) {
				// remember kids, all other date formats than ISO 8601 are stupid
				rename('chmp/content/' . $page_id . '.json', 'chmp/content/' . $page_id . '_' . date('Ymd-His') . '.json');
			}

			rename('chmp/content/' . $page_id . '_edit.json', 'chmp/content/' . $page_id . '.json');

			// Sets time
			$sql = "UPDATE structure SET publish_time = '" . date("Y-m-d H:i:s") . "', published = 1 WHERE page_id  =" . intval($page_id);

			$sql_do = $this->db->exec($sql);
			if ( !$sql_do ) {
				die( 'Error :' . $this->db->lastErrorMsg() );
			}

			// (Re)calculate depth
			$this->update_page_depth($page_id);

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * Gets the page_id of the first page in given language
	 * Returns false if no page exists
	 * @param {null|int} $lang - null sets to $this->lang
	 * @return false|int
	 */
	public function get_first_page_in_lang($lang = NULL) {
		if ( is_null($lang) ) {
			$lang = $this->lang;
		}

		$sql = "SELECT page_id
				FROM structure
				WHERE lang = " . intval($lang) . "
				AND published = 1
				AND father = 0
				ORDER BY sort ASC
				LIMIT 0,1";

		$row = $this->db->querySingle($sql);

		if ( $row === FALSE ) {
			return FALSE;
		} else {
			return $row;
		}

	}


	/**
	 * Returns the language id from a part of the url
	 * Returns false if no match
	 * @param string $lang_name
	 * @return false|int
	 */
	public function get_language_id($lang_name) {

		if ( substr($lang_name, -1) == '/' ) {
			$lang_name = substr($lang_name, 0, -1);
		}

		foreach ( Config::get('languages') as $key => $value ) {
			if ( $value[ 'url' ] == $lang_name ) {
				return $key;
			}
		}

		return FALSE;
	}

	/**
	 * Calculate the depth of page in navigation
	 * @param $page_id
	 */
	public function update_page_depth($page_id) {
		$result = $this->update_page_depth_recursive($page_id);
		$sql    = "UPDATE structure SET depth = " . $result . " WHERE page_id = " . intval($page_id);
		$sql_do = $this->db->exec($sql);
		if ( !$sql_do ) {
			die( 'Error :' . $this->db->lastErrorMsg() );
		}
	}

	/**
	 * Part of update_page_depth
	 * @param int $page_id
	 * @param int $depth
	 * @return int
	 */
	private function update_page_depth_recursive($page_id, $depth = 0) {
		$depth++;
		$sql = "SELECT page_id, depth, father FROM structure WHERE page_id = " . intval($page_id);
		$row = $this->db->querySingle($sql, TRUE);

		if ( $row[ 'father' ] > 0 ) {
			return $this->update_page_depth_recursive($row[ 'father' ], $depth);
		} else {
			return $depth;
		}
	}


	/**
	 * Check for url and returns page_id, or false
	 * @param string $url
	 * @param int $lang
	 * @param bool $strict - returns only published pages
	 * @return false|int
	 */
	public function get_page_id_from_url($url, $lang, $strict = TRUE) {
		$sql = "SELECT page_id, lang FROM structure
				WHERE url = '" . SQLite3::escapeString($url) . "'
				AND lang = '" . SQLite3::escapeString($lang) . "'";

		if ( $strict ) {
			$sql .= " AND published = 1";
		}

		$row = $this->db->querySingle($sql);

		if ( $row === FALSE ) {
			return FALSE;
		} else {
			return $row;
		}
	}


	/**
	 * Get page_id and lang from an alias, or return false
	 * @param string $alias
	 * @param null|int $lang
	 * @param bool $strict - returns only published pages
	 * @return array|false
	 */
	public function get_page_id_from_alias($alias, $lang = NULL, $strict = TRUE) {

		$sql = "SELECT structure.page_id, structure.lang, alias.redirect FROM alias
				JOIN structure ON alias.page_id = structure.page_id
				WHERE alias.alias = '" . SQLite3::escapeString($alias) . "'";

		if ( $strict ) {
			$sql .= " AND structure.published = 1";
		}

		if ( !is_null($lang) ) {
			$sql .= " AND alias.lang = " . intval($lang);
		} else {
			$sql .= " AND alias.globalalias = 1";
		}

		$row = $this->db->querySingle($sql, TRUE);

		if ( $row === FALSE ) {
			return FALSE;
		} else {
			// TODO: add redirect here

			return array( $row[ 'page_id' ], $row[ 'lang' ] );
		}
	}

	/**
	 * @param int $page_id
	 * @param int $father
	 * @param int $lang
	 * @param string $templatefile
	 * @param string $name
	 * @param string $title
	 * @param int|boolean $copy_of
	 */
	public function create_new_page($page_id, $father, $lang = NULL, $templatefile = '', $name = '', $title = '', $copy_of = false) {

		$arr_lang  = Config::get('languages');
		$lang_name = $arr_lang[ $lang ];



		// copies a page
		if ($copy_of !== false and is_file($this->path . 'content/'.$copy_of.'.json')) {

			$json = json_decode( file_get_contents( $this->path . 'content/'.$copy_of.'.json'), true);

			$json['info']['name'] = $name;
			$json['info']['title'] = ($title != $name ? $title:'');
			$json['info']['template'] = $templatefile;

		} else {

			$json = array(
				'info'    => array(
					'page_id' => $page_id,
					'title' => ($title != $name ? $title:''),
					'name' => $name,
					'templatefile' => $templatefile,
					'structure' => $lang,
					'lang' => $lang_name['lang_code']

				),
				'content' => array()
			);

		}

		file_put_contents( $this->path . 'content/'.$page_id.'.json', json_encode($json, JSON_FORCE_OBJECT));


	}


	/**
	 * Save the structure - Recursive
	 * @param array $active
	 * @param array $structure
	 * @param array $trash
	 * @param int $father
	 * @param int $depth
	 * @param int|null $lang
	 * @return array - number of edited/deleted pages
	 */
	public function save_structure($active = array(), $structure = array(), $trash = array(), $father = 0, $depth = 0, $lang = NULL) {

		if ( is_null($lang) ) {
			$lang = $this->lang;
		}

		$output[ 'update' ] = 0;
		$output[ 'delete' ] = 0;
		$output[ 'new' ] = 0;

		// saves existing pages
		$sort = 1;
		if ( is_array($active) ) {
			foreach ( $active as $key => $value ) {

				$id = $value[ 'id' ];

				$sql = "UPDATE structure SET
							preliminary = 0,
							lang = " . intval($lang) . ",
							father = " . intval($father) . ",
							sort = " . intval($sort);

				// Name
				$name = $structure[ $id ][ 'name' ];
				$name = ( trim($name) != '' ? SQLite3::escapeString(trim($name)) : 'untitled' );
				$sql .= ", name = '" . $name . "'";

				// Skip
				$sql .= ", skip = " . ( $structure[ $id ] == 'true' ? 1 : 0 );

				// Hidden
				$sql .= ", hidden = " . ( $structure[ $id ] == 'true' ? 1 : 0 );

				// URL

				// Where

				$sql .= " WHERE page_id =" . intval($id);

				$test = 1;

				$this->db->query($sql);
				$output[ 'update' ]++;

				// check if this is a new page
				if ( $structure[ $id ][ 'new_page' ] ) {

					$this->create_new_page($id, $father, $lang, $structure[ $id ][ 'template' ], $name, $name, $structure[ $id ][ 'copy_of' ]);
					$output[ 'new' ]++;

				}

				// there is no need to send trash into recursion
				if ( is_array($value[ 'children' ]) ) {
					$result = $this->save_structure($value[ 'children' ], $structure, array(), $id, $depth + 1, $lang);
					$output[ 'update' ] += $result[ 'update' ];
					$output[ 'new' ] += $result[ 'new' ];
				}

				$sort++;


			}
		}

		// removes trash
		$output[ 'delete' ] = $this->remove_from_structure($trash, $lang);

		return $output;

	}

	/**
	 * Removes pages from structure recursive
	 * called from $this->save_structure
	 * @param array $trash
	 * @param null $lang
	 * @return int
	 */
	private function remove_from_structure($trash, $lang = NULL) {

		$delete = 0;

		if ( is_null($lang) ) {
			$lang = $this->lang;
		}

		if ( is_array($trash) ) {
			foreach ( $trash as $trash_row ) {
				$sql = "DELETE FROM structure WHERE page_id = " . intval($trash_row['id']);
				$this->db->query($sql);

				$result = $this->db->lastErrorMsg();

				if ($result != 'not an error') {
					die('error on line '. __LINE__ .' in :'.$_SERVER['PHP_SELF'].'<br><br>, could not execute<br>'.$sql.'<br>Error: '.$result.'<br>');
				}

				$delete++;

				if ( is_array($trash_row[ 'children' ]) ) {

					$rec_deleted = $this->remove_from_structure($trash_row['children'], $lang);

					$delete += $rec_deleted;

				}

			}
		}

		return $delete;

	}

	/**
	 * Returns the number of pages in a given language
	 * @param bool $published - default false: all pages, true: only published
	 * @param int|null $lang - default null, uses $this->lang
	 * @return int
	 */
	public function count_pages($published = false, $lang = null) {

		if (is_null($lang)) {
			$lang = $this->lang;
		}

		$sql = "SELECT count(page_id) as num_pages FROM structure WHERE lang = ".intval($lang);

		if ($published) {
			$sql .= " AND published = 1";
		}

		$result = $this->db->querySingle($sql);

		return $result;

	}


	/**
	 * Check that a url is unique, otherwise add a number
	 * @param string $input
	 * @param int $add_number
	 * @param $exclude
	 * @return string
	 */
	public function check_url($input, $add_number = 0, $exclude = 0) {

		if ( $add_number > 0 ) {
			$input_test = $input . '_' . $add_number;
		}

		$sql = "SELECT count(page_id) FROM structure WHERE url = '" . SQLite3::escapeString($input_test) . "'";

		if ( $exclude > 0 ) {
			$sql .= " AND page_id != " . intval($exclude);
		}

		$result = $this->db->querySingle($sql);

		if ( $result == 0 ) {
			return ( $output );
		} else {
			return $this->check_url($input, $add_number + 1, $exclude);
		}

	}

	/**
	 * Get suggested url for page (recursive)
	 * same as chmp.get_autourl in chmp/js/structure.js
	 * @param int $id
	 * @return string
	 */
	public function get_autourl($id) {

		$sql = "SELECT name, father FROM structure WHERE page_id = " . intval($id);

		$result = $this->db->querySingle($sql, TRUE);

		$name = Tools::urlformat($result[ 'name' ], Config::get('rich_urls'));

		if ( $result[ 'father' ] > 0 ) {
			return $this->get_autourl($result[ 'father' ]) . '/' . $name;

		} else {
			return $name;

		}

	}

	/**
	 * Makes an array with the structure
	 * Saves an array of jsonfiles in /content to $this->file_list_json
	 *
	 * Returns an array of the structure
	 *
	 * @param bool $flat - false (default): returns a nested array of the structure, true: not nested, just a list of all pages
	 * @return array
	 */
	public function get_structure($flat = FALSE) {

		// reads json files to see what pages are changed
		if ( !is_array($this->file_list_json) ) {
			$this->make_file_list_json();
		}

		// makes the structure if it's not built already
		if ( !is_array($this->structure_array) ) {
			$this->make_structure();
		}

		// return nested or flat structure
		if ( !$flat ) {
			return $this->structure_array;
		} else {
			return $this->structure_array_flat;
		}

	}


	/**
	 * Makes an array of the .json files in /content
	 */
	private function make_file_list_json() {
		$output = array();
		foreach ( new DirectoryIterator( $this->path . 'content' ) as $fileInfo ) {
			if ( $fileInfo->getExtension() == 'json' ) {
				$output[ ] = $fileInfo->getFilename();
			}
		}
		$this->file_list_json = $output;
	}


	/**
	 * Returns an array of all template files
	 * @return array
	 */
	public function get_file_list_template() {
		if ( !is_array($this->file_list_template) ) {
			$output = array();
			foreach ( new DirectoryIterator( $this->path . 'templates' ) as $fileInfo ) {
				if ( $fileInfo->isFile() ) {
					$output[ ] = array(
						'name' => Tools::filename_to_text($fileInfo->getFilename()),
						'file' => $fileInfo->getFilename()

					);
				}
			}
			$this->file_list_template = $output;
		}

		return $this->file_list_template;
	}

	public function get_first_template($type = 'file') {
		if (!is_array($this->file_list_template)) {
			$this->get_file_list_template();
		}

		$test = 1;

			return $this->file_list_template[0][$type];



	}


	/**
	 * Part of $this->get_structure()
	 */
	private function make_structure() {

		function buildTree(array $elements, $parentId = 0) {
			$branch = array();

			foreach ( $elements as $element ) {
				if ( $element[ 'father' ] == $parentId ) {
					$children = buildTree($elements, $element[ 'page_id' ]);
					if ( $children ) {
						$element[ 'children' ] = $children;
					}
					$branch[ $element[ 'page_id' ] ] = $element;
				}
			}

			return $branch;
		}

		$sql = "SELECT * FROM structure WHERE lang = " . intval($this->lang) . " ORDER BY sort";

		$results = $this->db->query($sql);
		$rows    = array();

		$flat = array();

		while ( $result_row = $results->fetchArray() ) {
			$rows[ ] = array(
				'page_id'   => $result_row[ 'page_id' ],
				'father'    => $result_row[ 'father' ],
				'name'      => $result_row[ 'name' ],
				'published' => $result_row[ 'published' ]
			);

			if ( $result_row[ 'published' ] ) {
				if ( in_array($result_row[ 'page_id' ] . '_edit.json', $this->file_list_json) ) {
					$status = 'edited';
				} else {
					$status = 'published';
				}

			} else {
				$status = 'unpublished';
			}

			$flat[ $result_row[ 'page_id' ] ] = array(

				'name'   => $result_row[ 'name' ],
				'status' => $status,
				'hidden' => ( $result_row[ 'hidden' ] ? TRUE : FALSE ),
				'skip'   => ( $result_row[ 'skip' ] ? TRUE : FALSE ),
				'template' => $result_row['template']

			);

		}

		$this->structure_array      = buildTree($rows);
		$this->structure_array_flat = $flat;

	}


}