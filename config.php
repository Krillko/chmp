<?php

/*
	salt for blowfish encryption
	you should change this to something eller
*/
$chmp_config[ 'salt' ] = '$2y$07$' // leave this
	. 'chmpDefaultSaltPlseChange' // change this, 25 letters ./0-9A-Za-z
	. '$'; // leave this

// relative path from root to assets
$chmp_config[ 'assets_folder' ]  = 'chmp/assets/';
$chmp_config[ 'content_folder' ] = 'chmp/content/';

/* login dialog. Use this to customize login, or if your language is missing
Add as many as you like

$chmp_config['login'][  langage code in, example 'en'  ] => array(
			'login' => 'Log in',
			'inmsg' => 'A message to login',
			'logout' => 'Log out',
			'outmsg' => 'Are you sure you want to log out?',
			'loginfail' => 'Username or password is wrong',
			'username' => 'Username',
			'password' => 'Password'
		);

*/

// jquery, true = load from googleapis , false = load internaly
$chmp_config[ 'jquery_external' ] = TRUE;
$chmp_config[ 'jquery_version' ]  = '2.1.0';
