<?php

// naming conventions from code igniter

// testvalues, replace with real
$test_currentpage = 1;
$test_language    = 'sv';

//require_once( 'editor/simple_html_dom.php' );
//require_once( 'classes/Read_template.php' );

// get configuration

// Require classes
// for production

require_once( 'chmp/classes/Config.php' );
Config::init();

require_once( 'chmp/classes/Tools.php' ); // a collection of "good to have" methods
require_once( 'chmp/classes/simple_html_dom.php' ); // parses and manipulates html documents

require_once( 'chmp/classes/Read_template.php' ); // model, builds an array of template
require_once( 'chmp/classes/Read_content.php' ); // model, gets necessary info of content

require_once( 'chmp/classes/Show_content.php' );
require_once( 'chmp/classes/Show_navigation.php' );

// for editor
require_once( 'chmp/classes/Error_log.php' );

// starts error handling
$error_log = new Error_log();

// Get page content
$content = new Read_content( $test_currentpage );

// Reads template
$template_raw = new Read_template_file( $content->get_templatefile() );

$html = new simple_html_dom();
$html->load($template_raw->template);

$template = new Read_tempate( $html );

//die(print_r($template->template));

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - gets navigation
$nav = new Show_navigation();
$nav->set_currentpage($test_currentpage);

$show_content = new Show_content( $template, $content );

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - starts actual output

/* empty out contentareas , then reloads the html
	The reason we do this is because ->find() is looking in the original dom, and not in the manipulated version
*/
foreach ( $html->find('content') as $contentarea ) {
	$contentarea->innertext = '';
}

$html->load($html->save()); // updates simple_html_doms internal dom-tree

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Changes every text/img outside contentareas
foreach ( $html->find('*[data-chmp-name]') as $outsideContent ) {

	if ( Tools::tag_kind($outsideContent->tag) == 'text' ) {
		$outsideContent->innertext = $show_content->show_outside_content($outsideContent, 'text');
	} else if ( $outsideContent->tag == 'img' ) {
		$outsideContent->outertext = $show_content->show_outside_content($outsideContent, 'img');
	}


}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Builds the contentarea

foreach ( $html->find('content') as $contentarea ) {
	$contentarea->outertext = $show_content->show_contentarea($contentarea);
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Set page title
$html->find('title', 0)->innertext = $show_content->get_title();

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Adding scripts
	the method makeup() is not documented in simple_html_dom
*/
$add_scripts     = '<script type="text/javascript" src="chmp/js/production.js"></script>';
$head            = $html->find("head", 0);
$head->outertext = $head->makeup() . $head->innertext . $add_scripts . '</head>';

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - Shows navigation
foreach ( $html->find('navigation') as $navigation_row ) {
	$thisNavigationOutput      = $nav->get_nav('ul', $navigation_row->getAllAttributes(), 0, null);
	$navigation_row->outertext = $thisNavigationOutput;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - echos output

echo( $html );

/* clears the memory:  according to the instructions from the creators of simple_html_dom memory may become a problem
with this plugin.
*/
$html->clear();
unset( $html );

// shows error as html comment
echo $error_log->list_errors('comment', TRUE);