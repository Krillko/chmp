<?php
//TODO: IMPORTANT, replace test_lang with session based lang
$test_lang = 0;

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
$session = new Session( '../' );
$session->set_lang();

if ( $session->is_loggedin() ) {

	require_once( 'classes/Editor_ui.php' );
	$editor_ui = new Editor_ui();

	require_once( 'classes/Read_structure.php' ); // model, keeps the structure of the site

	$structure = new Read_structure( $db, '' );

	$structure->set_lang($test_lang);

	require_once( 'classes/Show_navigation.php' );

	$navigation = new Show_navigation( NULL, $structure, $session );

	$out = '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<base href="../">

<link rel="stylesheet" type="text/css" href="chmp/editordesign/reset5.css">
<!--
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10/themes/ui-lightness/jquery-ui.css">
-->


<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp.css">
<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp_inside.css">

<script type="text/javascript">
	// prevents console log from breaking stuff
	if ( typeof console === "undefined" || typeof console.log === "undefined" ) {
		console = {};
		console.log = function () {
		};
		console.warn = function () {
		};
		console.error = function () {
		}
	}


	// settings
	var chmp = chmp || [];
	chmp.structure = ' . json_encode($structure->get_structure(TRUE), JSON_FORCE_OBJECT) . ';
	chmp.templates = ' . json_encode($structure->get_file_list_template(), JSON_FORCE_OBJECT) . ';
	chmp.rich_urls = false; // allows utf8 in urls
	chmp.lang = ' . $test_lang . ';


</script>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>

<script type="text/javascript" src="chmp/js/jquery.nestable.js"></script>
<script type="text/javascript" src="chmp/js/loggedin.js"></script>
<script type="text/javascript" src="chmp/js/structure.js"></script>


<title>Structure</title>
</head>

<body>
' . $editor_ui->editor_nav('structure', TRUE, FALSE, NULL, $session, $structure)

		. '<div id="admin" class="admin_bg">
<div class="admin_content">
<div class="admin_headline">
	<h1>Structure</h1>
</div>

<div class="admin_save">

	<div class="chmp chmp-nav-part chmp-nav-part-btn">
		<div class="chmp chmp-button-expandable chmp-submit-inactive" id="stuct_save_holder">
			<div class="chmp chmp-input-expand-btn chmp-input-small chmp-submit chmp-do-expand"><p>Save changes</p>
			</div>
			<div class="chmp chmp-input-expand"><p><a href="javascript:;"
			                                          class="chmp chmp-input-cancel">CANCEL</a> &nbsp; <a
					href="javascript:;" class="chmp chmp-input-confirm" id="struct_save">OK!</a></p></div>
		</div>
	</div>

	<div class="chmp chmp-nav-part-btn">
		<div class="admin_unsaved chmp-start-hidden" id="struct_reminder">You have unsaved changes</div>
	</div>

</div>
<!-- / admin_save -->

<div class="clear vmargin"></div>

<div class="admin_left">
	<!-- nestable -->

	<div class="dd" id="chmp_structure">';

	if ( $structure->count_pages() > 0 ) {
		$out .= $navigation->get_nav('structure');
	} else {

		$out .= '<ol class="dd-list" id="chmp_structure_active"></ol>';


	}

	$out .= '
	</div>


	<!-- / nestable -->

	<div class="chmp chmp-nav-part chmp-nav-part-btn">
		<div class="chmp chmp-input-dynamic chmp-button" id="struct_add_page"><p>+ Add page</p></div>
	</div>

	<div class="clear"></div>

	<div id="struct_delete_headline">&nbsp;</div>

	<div class="dd" id="chmp_structure_trash">
		<div class="dd-empty"><div class="chmp_ico_trash"></div>Drag a page here to delete it</div>
	</div>

</div>
<!-- /admin_left -->


<div class="admin_right">
	<div class="admin_right_box">
		<div class="admin_right_headline">
			<h2>Page info</h2>

			<div class="admin_right_collaps"></div>
		</div>
		<div class="admin_right_content chmp-start-hidden" id="struct_pageinfo">

			<div class="chmp_form form_table">
				<div class="form_row">
					<div class="form_cell chmp_form_name">Name</div>
					<div class="form_cell form_rigth"><input type="text" id="struct_name"></div>
				</div>

				<div class="form_row">
					<div class="form_cell chmp_form_name">Template</div>
					<div class="form_cell form_rigth">
						<select id="struct_template">
							';
	foreach ( $structure->get_file_list_template() as $temlate_row ) {
		$out .= '<option value="' . $temlate_row[ 'file' ] . '">' . $temlate_row[ 'name' ] . '</option>';
	}
	$out .= '
						</select>
					</div>
				</div>

			</div>
			<!-- /chmp_form -->

		</div>
		<!-- /admin_right_content -->
	</div>
	<!-- /admin_right_box -->


	<div class="admin_right_box">
		<div class="admin_right_headline" id="toggleAdvanced">
			<h2>Advanced</h2>

			<div class="admin_right_collaps"></div>
		</div>
		<div class="admin_right_content chmp-start-hidden" id="struct_advanced">

			<div class="chmp_form">

				<div class="chmp_form_subheadline">URL</div>

				<div class="chmp_form_wide">
					<input type="text" id="struct_url">
				</div>
				<div class="chmp_form_wide" id="url_suggestion">
					Set automatic: <a href="javascript:;" id="url_accept_suggestion"></a>
				</div>


				<!-- TODO: implement alias function
				<div class="chmp_form form_table" id="alias_table">
					<div class="form_row form_row_head">
						<div class="form_cell chmp_form_name">Alias </div>
						<div class="form_cell chmp_form_name">Redirect</div>
						<div class="form_cell chmp_form_name">Global</div>
					</div>





						<div class="form_row">
							<div class="form_cell"><input type="text"></div>
							<div class="form_cell form_center"><input id="redirect" class="chmp-checkbox" type="checkbox" value="1">
								<label for="redirect" class="chmp-label chmp-label-small"></label></div>
							<div class="form_cell form_center"><input id="global" class="chmp-checkbox" type="checkbox" value="1">
								<label for="global" class="chmp-label chmp-label-small"></label></div>
						</div>





					<div class="form_row">
						<div class="form_cell"><input type="text"></div>
						<div class="form_cell form_center"><input id="redirect" class="chmp-checkbox" type="checkbox" value="1">
															<label for="redirect" class="chmp-label chmp-label-small"></label></div>
						<div class="form_cell form_center"><input id="global" class="chmp-checkbox" type="checkbox" value="1">
					<label for="global" class="chmp-label chmp-label-small"></label></div>
					</div>


				</div>
				-->

				<div class="chmp_form_subheadline">Skip page</div>

				<div class="chmp_form_wide">
					<input id="struct_skip" class="chmp-checkbox" type="checkbox" value="1">
					<label for="struct_skip" class="chmp-label chmp-label-small" id="struct_skip_label">Skip this page
						and jump to the first page below</label>
				</div>


			</div>

		</div>
		<!-- /admin_right_content -->
	</div>
	<!-- /admin_right_box -->


	<div class="admin_right_box">
		<div class="admin_right_headline">
			<h2>Actions</h2>

			<div class="admin_right_collaps"></div>
		</div>
		<div class="admin_right_content chmp-start-hidden" id="struct_actions">


			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button" id="struct_action_hide"><p>HIDE PAGE</p></div>
			</div>


			<div class="chmp chmp-nav-part chmp-nav-part-btn">
				<div class="chmp chmp-input-dynamic chmp-button" id="struct_action_duplicate"><p>DUPLICATE PAGE</p></div>
			</div>


			<div class="clear">


			</div>
			<!-- /admin_right_content -->
		</div>
		<!-- /admin_right_box -->


	</div>
	<!-- /admin_right -->


</div>
<!-- /admin_content -->

<div class="clear"></div>

</div>
<!-- /admin_bg -->



</body>
</html>';

	echo $out;


} else {

	//TODO: redirect to login
	header('HTTP/1.0 403 Forbidden');
	echo( 'HTTP/1.0 403 Forbidden' );

}