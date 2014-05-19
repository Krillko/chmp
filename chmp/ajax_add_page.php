<?php
/*
 * This adds a page to structure and marks it as preliminary
 * The reason we add the page to the database is to give it a real id
 * and makes it easier if the user nest new pages
 *
 * If the user closes the window we try to send an ajax call to delete this page
 * TODO: We should also add some kind of timer to delete unused pages
 *
 * */

mb_internal_encoding("UTF-8");
header('Content-Type:text/html; charset=UTF-8');

// connects to our sqlite3 database
$db = new SQLite3( 'content/structure.sqlite3' );

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - a collection of "good to have"
//require_once( 'classes/Tools.php' ); // a collection of "good to have" methods

//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  Require classes
require_once( 'classes/Config.php' );
Config::init('../');

// sets timezone
date_default_timezone_set(Config::get('timezone'));

require_once( 'classes/Session.php' );
$session = new Session('../');

if ($session->is_loggedin()) {

	$test = 1;

	$sql = "SELECT max(page_id) FROM structure";

	$result = $db->querySingle($sql);

	$new_name = 'New Page '.$result+1;

	$sql = "INSERT INTO structure (lang, name, father, depth, preliminary, created_on, url)
			VALUES (".intval($_POST['lang']).", 'New Page', 0, 1, 1, '".date("Y-m-d H:i:s")."', '".$new_name."')";

	$db->query($sql);

	$new_id = $db->lastInsertRowID();

	header("HTTP/1.1 200 OK");
	echo $new_id;

} else {

	header('HTTP/1.0 403 Forbidden');
	echo('HTTP/1.0 403 Forbidden');

}