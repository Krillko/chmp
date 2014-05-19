<?php
/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-09
 * Time: 11:27
 */

/*
 TODO: Create all databases
 */

$sql = "CREATE TABLE IF NOT EXISTS structure (
			page_id INTEGER PRIMARY KEY AUTOINCREMENT,
			lang int NOT NULL,
			father int DEFAULT 0,
			depth int DEFAULT 1,
			sort int DEFAULT 1,
			name text NOT NULL,
			title text,
			description text,
			published int DEFAULT 0,
			publish_time datetime,
			url text NOT NULL,
			preliminary int DEFAULT 0,
			created_on datetime,
			skip int DEFAULT 0,
			hidden int DEFAULT 0
		)";

$sql = "CREATE UNIQUE INDEX IF NOT EXISTS url_unique ON structure (url)";

$sql = "CREATE TABLE IF NOT EXISTS alias (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			alias text NOT NULL,
			page_id INTEGER,
			redirect INTEGER DEFAULT 0,
			globalalias INTEGER DEFAULT 0,
			page_id INTEGER
			)";


$sql = "CREATE TABLE IF NOT EXISTS settings (
		setting_var text NOT NULL,
	 	setting_val text,
		PRIMARY KEY(setting_var)
)";


$sql = "CREATE TABLE languages (
	lang_id integer NOT NULL PRIMARY KEY AUTOINCREMENT,
	 name TEXT,
	 langcode text,
	 url TEXT,
	 primary integer DEFAULT 0
)";

$sql = "CREATE UNIQUE INDEX IF NOT EXISTS url_unique ON languages (url)";