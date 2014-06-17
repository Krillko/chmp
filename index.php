<?php
mb_internal_encoding("UTF-8");
header('Content-Type:text/html; charset=UTF-8');

// testvalues, replace with real
$test_language = 'sv';
$test_lang_id  = 1;

// connects to our sqlite3 database
$db = new SQLite3( 'chmp/content/structure.sqlite3' );

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - a collection of "good to have"
require_once( 'chmp/classes/Tools.php' ); // a collection of "good to have" methods

//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  Require classes
require_once( 'chmp/classes/Config.php' );
Config::init();

// sets timezone
date_default_timezone_set(Config::get('timezone'));

require_once( 'chmp/classes/Session.php' );
$session = new Session();

if ( isset( $_POST[ 'chmp-login' ] ) ) {
	$login_result = $session->login($_POST[ 'chmp-login-user' ], $_POST[ 'chmp-login-password' ]);
}

// turns editing on off by button
if ( isset( $_GET[ 'chmp-edit' ] ) ) {
	$session->set_edit($_GET[ 'chmp-edit' ]);
}
// turns of editing when publish is done
if ( $_GET[ 'published' ] == 'done' ) {
	$session->set_edit(0);
}

if ( isset( $_GET[ 'logout' ] ) ) {
	$session->clear_session();
}

require_once( 'chmp/classes/simple_html_dom.php' ); // parses and manipulates html documents
require_once( 'chmp/classes/Read_template.php' ); // model, builds an array of template
require_once( 'chmp/classes/Read_content.php' ); // model, gets necessary info of content
require_once( 'chmp/classes/Show_content.php' );
require_once( 'chmp/classes/Show_navigation.php' );
require_once( 'chmp/classes/Show_page.php' );
require_once( 'chmp/classes/Error_log.php' );
require_once( 'chmp/classes/Read_structure.php' ); // model, keeps the structure of the site
require_once( 'chmp/classes/Editor_ui.php' );

// starts error handling
$error_log = new Error_log();

$structure = new Read_structure( $db, $page_id, 'chmp/' );


// figure out what page we are on
// if $_GET[ 'chmp' ] we are inside editor, otherwise on published pages
if ( $_GET[ 'chmp' ] ) {

	// we can only be here if logged in
	// TODO: redirect this to the published page if exists
	if (!$session->is_loggedin()) {
		Tools::redirect('../index.php', false, true);
	}

	$page_id = $_GET[ 'page_id' ];

	// check that the page exists in structure
	// otherwise, redirect to structure
	if (!$structure->page_exists($page_id)) {
		Tools::redirect('../structure.php', false, true);
	}

	$lang = $structure->set_lang_from_page_id($page_id);
	$session->set_lang($lang);

} else {

	$path = trim($_SERVER[ 'PATH_INFO' ]);

	// remove starting and ending slash
	if ( substr($path, 0, 1) == '/' ) {
		$path = substr($path, 1);
	}
	if (substr($path, -1) == '/') {
		$path = substr($path,0,-1);
	}


	$languages = Config::get('languages');

	if ( $path == '' or $path == 'index.php') { // completly empty url or index.php
		$lang = Config::get('language_main');
		$structure->set_lang($lang);
		$page_id = $structure->get_first_page_in_lang();

	} elseif ( Config::get('language_on') ) {


		if ( strpos($path, '/') !== FALSE ) {
			$url_parts = explode('/', $path);
			$lang      = $structure->get_language_id($url_parts[ 0 ]);
			if ($lang !== FALSE) {
				$path = substr($path,strlen($url_parts[ 0 ])+1);
			}
		} else {
			$lang = $structure->get_language_id($path);
			if ($lang !== FALSE) {
				$bare_lang = true;
			}
		}

	}


	if (is_null($page_id)) {

		$test1 = 1;
		if (is_null($lang) OR $lang  === false) {
		/*
			We didn't get a languge on the first try, possible reasons:
			a) language_main_no_url is true so it's a page
			b) it's an alias
			*/

			// testing a
			if ( Config::get('language_main_no_url') or !Config::get('language_on') )
				 {

				$test_url = $structure->get_page_id_from_url($path, Config::get('language_main'));
				if ( $test_url !== FALSE ) {
					$page_id = $test_url;
					$lang    = Config::get('language_main');
				}

			}

			// testing b
			$test_alias = $structure->get_page_id_from_url($path, true);
			if ($test_alias !== FALSE) {
				list($page_id, $lang) = $test_alias;
			}

		}


		// If we still don't have a page_id it's a subpage
		if (!is_null($lang) and $lang !== FALSE and !$bare_lang) {

			$page_id = $structure->get_page_id_from_url($path,$lang,true);

		}

	}

	$test1 = 1;

	if ( $page_id === FALSE or $lang === FALSE ) {
		// TODO: error 404
		die( '404' );
	}



}


$structure->set_lang($lang);


// publish a page and redirects to finished
if ( $session->is_loggedin() and $_GET[ 'do' ] == 'publish' ) {
	if ( $structure->publish($page_id) ) {
		Tools::redirect('?published=done', FALSE, FALSE);
	} else {
		die('Couldn\'t publish');

	}
}



// Builds a page and shows it
$selflocation = 'http://' . $_SERVER[ 'HTTP_HOST' ] . str_ireplace('index.php', '', $_SERVER[ 'PHP_SELF' ]);



$page = new Show_page( $session->is_edit(), $session->is_loggedin(), $selflocation, $structure, $session );
$page->load_page($page_id);

echo $page->show_page();

// shows error as html comment
echo $error_log->list_errors('comment', TRUE);