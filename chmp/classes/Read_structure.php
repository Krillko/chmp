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

	private $page_id = NULL, $lang;

	/**
	 * @param $db \SQLite3
	 * @param null $page_id
	 */
	function __construct($db, $page_id = NULL) {
		if ( !is_null($page_id) ) {
			$this->page_id = $page_id;
		}

		$this->db = $db;
	}

	/**
	 * Sets language
	 * @param {int} $lang
	 */
	public function set_lang($lang) {
		$this->lang = $lang;
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

		if ( is_file('chmp/content/' . $page_id . '_edit.json') ) {

			// Renames current content
			if ( is_file('chmp/content/' . $page_id . '.json') ) {
				// remember kids, all other date formats than ISO 8601 are stupid
				rename('chmp/content/' . $page_id . '.json', 'chmp/content/' . $page_id . '_' . date('Ymd-His') . '.json');
			}

			rename('chmp/content/' . $page_id . '_edit.json', 'chmp/content/' . $page_id . '.json');

			// Sets time
			$sql = "UPDATE structure SET publish_time = '".date("Y-m-d H:i:s")."', published = 1 WHERE page_id  =". intval($page_id);

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
	public function get_first_page_in_lang($lang = null) {
		if (is_null($lang)) { $lang = $this->lang; }

		$sql = "SELECT page_id
				FROM structure
				WHERE lang = ".intval($lang)."
				AND published = 1
				AND father = 0
				ORDER BY sort ASC
				LIMIT 0,1";

		$row = $this->db->querySingle($sql);

		if ($row === false) {
			return false;
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

		if (substr($lang_name, -1) == '/') {
			$lang_name = substr($lang_name,0,-1);
		}

		foreach (Config::get('languages') as $key => $value) {
			if ($value['url'] == $lang_name) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Calculate the depth of page in navigation
	 * @param $page_id
	 */
	public function update_page_depth($page_id) {
		$result = $this->update_page_depth_recursive($page_id);
		$sql = "UPDATE structure SET depth = ".$result." WHERE page_id = ".intval($page_id);
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
		$sql = "SELECT page_id, depth, father FROM structure WHERE page_id = ".intval($page_id);
		$row = $this->db->querySingle($sql,true);

		if ($row['father'] > 0) {
			return $this->update_page_depth_recursive($row['father'], $depth);
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
	public function get_page_id_from_url($url, $lang, $strict = true) {
		$sql = "SELECT page_id, lang FROM structure
				WHERE url = '".SQLite3::escapeString($url)."'
				AND lang = '".SQLite3::escapeString($lang)."'";

		if ($strict) {
			$sql .= " AND published = 1";
		}

		$row = $this->db->querySingle($sql);

		if ($row === false) {
			return false;
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
	public function get_page_id_from_alias($alias, $lang = null, $strict = TRUE) {

		$sql = "SELECT structure.page_id, structure.lang, alias.redirect FROM alias
				JOIN structure ON alias.page_id = structure.page_id
				WHERE alias.alias = '".SQLite3::escapeString($alias)."'";

		if ($strict) {
			$sql .= " AND structure.published = 1";
		}

		if (!is_null($lang)) {
			$sql .= " AND alias.lang = ".intval($lang);
		} else {
			$sql .= " AND alias.globalalias = 1";
		}


		$row = $this->db->querySingle($sql, true);

		if ($row === false) {
			return false;
		} else {
			// TODO: add redirect here

			return array($row['page_id'], $row['lang']);
		}
	}

	/**
	 * @param int $page_id
	 * @param int $father
	 * @param int $lang_id
	 * @param string $templatefile
	 * @param string $name
	 * @param string $title
	 */
	public function create_new_page($page_id, $father, $lang_id = 0, $templatefile = '', $name = '', $title = '') {

		$arr_lang = Config::get('languages');
		$lang_name = $arr_lang[$lang_id];

		$json = array(
			'info' => array(
				'page_id' => $page_id,

			),
			'content' => array()
		);


	}


	/**
	 * Save the structure - Recursive
	 * @param array $in_array
	 * @param int $father
	 * @param int $depth
	 */
	public function save_structure($in_array, $father = 0, $depth = 0) {

		foreach ($in_array as $key => $value) {

			$sql = "UPDATE structure SET
						preliminary = 0";

			// Name
			$name = $_POST['structure'][$value['id']]['name'];
			$name = (trim($name) != '' ?  SQLite3::escapeString(trim($name)):'untitled' );
			$sql .= ", name = '".$name."'";

			// Skip
			$sql .= ", skip = ".($_POST['structure'][$value['id']]['skip'] ? 1:0);

			// Hidden
			$sql .= ", hidden = ".($_POST['structure'][$value['id']]['hidden'] ? 1:0);

			// URL



			$this->db->query($sql);

			if (is_array($value['children'])) {
				$this->save_structure($value['children'], $value['id'], $depth+1);
			}


		}

	}


	/**
	 * Check that a url is unique, otherwise add a number
	 * @param string $input
	 * @param int $add_number
	 * @param $exclude
	 * @return string
	 */
	public function check_url($input, $add_number = 0, $exclude = 0) {

		if ($add_number > 0) {
			$input_test = $input.'_'.$add_number;
		}

		$sql = "SELECT count(page_id) FROM structure WHERE url = '".SQLite3::escapeString($input_test)."'";

		if ($exclude > 0 ) {
			$sql .= " AND page_id != ".intval($exclude);
		}

		$result = $this->db->querySingle($sql);

		if ( $result == 0 ) {
			return ($output);
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

		$sql = "SELECT name, father FROM structure WHERE page_id = ".intval($id);

		$result = $this->db->querySingle($sql, true);

		$name = Tools::urlformat( $result['name'], Config::get('rich_urls'));

		if ($result['father'] > 0) {
			return $this->get_autourl($result['father']) . '/'.$name;

		} else {
			return $name;

		}

	}


}