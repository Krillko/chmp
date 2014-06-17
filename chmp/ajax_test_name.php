<?php
/*
 * Check if a url is allready used, echos a usable url

 * */

mb_internal_encoding("UTF-8");
header('Content-Type:text/html; charset=UTF-8');

// connects to our sqlite3 database
$db = new SQLite3( 'content/structure.sqlite3' );

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - a collection of "good to have"
require_once( 'classes/Tools.php' ); // a collection of "good to have" methods

//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  Require classes
require_once( 'classes/Config.php' );
Config::init('../');

// sets timezone
date_default_timezone_set(Config::get('timezone'));

require_once( 'classes/Session.php' );
$session = new Session('../');

if ($session->is_loggedin()) {

	require_once( 'classes/Read_structure.php' ); // model, keeps the structure of the site

	$structure = new Read_structure($db);



	echo $structure->check_url($_POST[ 'url' ], $_POST[ 'lang' ], (is_array($_POST['used']) ? $_POST['used']:array() ) );




} else {

	header('HTTP/1.0 403 Forbidden');
	echo('HTTP/1.0 403 Forbidden');

}