<?php

// naming conventions from code igniter

// testvalues, replace with real
$page_id       = 1;
$test_language = 'sv';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  Require classes
// - - - - - - - - - - - - - - - - - - - - for production
require_once( 'chmp/classes/Config.php' );
Config::init();

require_once( 'chmp/classes/Tools.php' ); // a collection of "good to have" methods
// this file doubles as a settings file
// for stuff that needs programming to change

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

// Builds a page and shows it
$page = new Show_page();
$page->load_page($page_id);

echo $page->show_page();

// shows error as html comment
echo $error_log->list_errors('comment', TRUE);