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

	require_once( 'classes/Read_structure.php' ); // model, keeps the structure of the site
	$structure = new Read_structure( $db, null, '../' );
	$structure->set_lang($_POST['lang']);

	$reload = false;

	/*
	 * Posted arrays:
	 * $_POST['active'] - contains the order from nestable.js
	 * $_POST['structure'] - flat array with additional info, name|skip|hide etc
	 * $_POST['trash'] - pages to be trashed, also from nestable.js
	 * $_POST['current_structure'] - currently not used in php
	 * */



	if (is_array($_POST['structure'])) {
		$update_result = $structure->save_structure($_POST['active'] , $_POST[ 'structure' ], $_POST[ 'trash' ]);
	}


	if ($update_result['delete'] > 0 or $update_result['new'] > 0) {
		$reload = true;
	}

	//header("HTTP/1.1 200 OK");
	if ($reload) {
		echo 'reload';
		//echo 'ok';
	} else {
		echo 'ok';
	}

} else {

	header('HTTP/1.0 403 Forbidden');
	echo('HTTP/1.0 403 Forbidden');

}