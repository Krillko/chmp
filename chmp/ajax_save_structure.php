<?php
/**
 * Created by PhpStorm.
 * User: kristoffer
 * Date: 2014-05-16
 * Time: 10:47
 */

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

	$structure = new Read_structure($db);
	$structure->set_lang($_POST['lang']);

	$test = 1;

	if (is_array($_POST['structure'])) {



	}


} else {

	header('HTTP/1.0 403 Forbidden');
	echo('HTTP/1.0 403 Forbidden');

}