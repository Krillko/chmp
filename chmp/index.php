<?php

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

if ($_GET['do'] == 'logout') {
	$session->clear_session();
	Tools::redirect('index.php',false,false); // redirects to get rid of get
}


if ( $session->is_loggedin() ) {

	Tools::redirect('structure.php',false, true);

} else {

	if (isset($_POST['chmp-login-user']) or isset($_POST['chmp-login-password'])) {
		$error = true;
		$login_result = $session->login($_POST[ 'chmp-login-user' ], $_POST[ 'chmp-login-password' ]);

	}

	if ($login_result) {
		Tools::redirect('structure.php',false, true);
	}




$out .= '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<base href="../">

	<link rel="stylesheet" type="text/css" href="chmp/editordesign/reset5.css">
	<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp.css">
	<link rel="stylesheet" type="text/css" href="chmp/editordesign/chmp_inside.css">


	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>

</head>

<body>


<div id="admin" class="admin_bg">
	<div class="admin_content">
		<div class="admin_headline">
			<h1>Log in</h1>
		</div>

		'.($error ? '<div class="loginerror"><p>Username or Password was wrong</p></div>':'').'

		<div class="admin_headline">
			<form method="post" target="index.php">
				<table class="login_holder">

					<tr>
						<td><label for="chmp-login-user">Username:</label></td>
						<td><input type="text"  id="chmp-login-user" name="chmp-login-user" class="chmp_form_input"></td>
						<td><label for="chmp-login-password">Password:</label></td>
						<td><input type="password" id="chmp-login-password" name="chmp-login-password" class="chmp_form_input"></td>
						<td>

							<button type="submit" class="chmp chmp-input-dynamic chmp-button">Login</button>

						</td>


					</tr>


				</table>
			</form>
		</div>


	</div>
</div>

</body>';

	echo $out;


}