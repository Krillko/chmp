<?php
mb_internal_encoding("UTF-8");
header('Content-Type:text/html; charset=UTF-8');

// testvalues, replace with real
$test_language = 'sv';
$test_lang_id = 1;

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

if ( isset( $_GET[ 'chmp-edit' ] ) ) {
	$session->set_edit($_GET[ 'chmp-edit' ]);
}

if ( isset( $_GET[ 'logout' ] ) ) {
	$session->clear_session();
}

$page_id = $_GET[ 'page_id' ];

require_once( 'chmp/classes/simple_html_dom.php' ); // parses and manipulates html documents

require_once( 'chmp/classes/Read_template.php' ); // model, builds an array of template
require_once( 'chmp/classes/Read_content.php' ); // model, gets necessary info of content

require_once( 'chmp/classes/Show_content.php' );
require_once( 'chmp/classes/Show_navigation.php' );

require_once( 'chmp/classes/Show_page.php' );

// - - - - - - - - - - - - - - - - - - - - for editor
require_once( 'chmp/classes/Error_log.php' );

// starts error handling
$error_log = new Error_log();

require_once( 'chmp/classes/Read_structure.php' ); // model, keeps the structure of the site

$structure = new Read_structure( $page_id, $test_lang_id );

if ( $session->is_loggedin() and $_GET[ 'do' ] == 'publish' ) {


}

// Builds a page and shows it
$selflocation = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ];

$page = new Show_page( $session->is_edit(), $session->is_loggedin(), $selflocation );
$page->load_page($page_id);

echo $page->show_page();

// shows error as html comment
echo $error_log->list_errors('comment', TRUE);