<?php

/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-07
 * Time: 11:01
 */
class Read_structure {

	private $page_id = NULL, $lang, $db;

	function __construct($page_id = NULL, $lang = 1) {
		if ( !is_null($page_id) ) {
			$this->page_id = $page_id;
		}

		$this->lang = $lang;

		$this->db = new SQLite3( 'chmp/content/structure.sqlite3' );

		/*
		if(!$this->db){
			die( $this->db->lastErrorMsg() );
		}
		*/

		$sql = "CREATE TABLE IF NOT EXISTS structure (
			page_id INTEGER PRIMARY KEY AUTOINCREMENT,
			lang int NOT NULL,
			father int DEFAULT 0,
			depth int DEFAULT 1,
			sort int DEFAULT 1,
			name varchar(255) NOT NULL,
			title varchar(255),
			description text,
			published int DEFAULT 0,
			publish_time datetime,
			url varchar(255) NOT NULL
		)";

		$ret = $this->db->exec($sql);
		if ( !$ret ) {
			die( 'Error :' . $this->db->lastErrorMsg() );
		}

		$sql = "INSERT INTO structure ( lang , name, url, publish_time) VALUES (1, 'Ny test', 'test', '2014-01-01 12:00:00')";

		$ret = $this->db->exec($sql);
		if ( !$ret ) {
			die( 'Error :' . $this->db->lastErrorMsg() );
		}


	}

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

			return TRUE;
		} else {
			return FALSE;
		}
	}


}