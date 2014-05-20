<?php

/*
Site-name used by login cookie. If you are running more then one copy of chmp on the same server,
this should be a unique name. Can be changed later
*/
$chmp_config[ 'sitename' ] = 'testsite';

/*
	- - - - - - - - - - - - - - salt for blowfish encryption
	you should change this to something else _ONCE_,
	if you change it again later, all login accounts will stop working

	you will never be asked to type this,
	so you don't have to remember it,
	so it's best if you use a completly random string
*/
$chmp_config[ 'salt' ] = '$2y$07$' // leave this
	. 'chmpDefaultSaltPlseChange' // change this, 25 letters ./0-9A-Za-z
	. '$'; // leave this


// - - - - - - - - - - - - - - multi language support
// turn langage support on
$chmp_config[ 'language_on' ]  = true;

/* list the langages
	name
	langcode, this is not the same as tld, see this list: http://www.w3schools.com/tags/ref_language_codes.asp
	url, part of the url to find the language, http://yourdomain/{url}/name_of_page
		Important: NOT NULLABLE!, all chars usually allowed in url except '/',
		so don't write ex: 'se/' yourself, just 'se'

	Minimum 1 language has to be defined even if we don't use multi-language
*/
$chmp_config[ 'languages' ] = array(
	0 => array( 'name' => 'English',
				'langcode' => 'en',
				'url' => 'en',
				'flag' => 'gb'
	),
	1 => array( 'name' => 'Svenska',
				'langcode' => 'sv',
				'url' => 'se',
				'flag' => 'se'
	)
);

// main language, if we don't know what language the visitor wants
// we use this one
$chmp_config[ 'language_main' ] = 0;

/*
Selects if the main language should have the url part or be in root
Example, english is main langauge:
True:
http://yourdomain/name_of_page
False:
http://yourdomain/english/name_of_page
 */
$chmp_config[ 'language_main_no_url'] = true;

/*
Detects the visitors browser language and goes to that page

Can not be together with language_main_no_url
If true, it sets language_main_no_url to false
 */
$chmp_config[ 'language_autodetect'] = false;








// - - - - - - - - - - - - - - relative path from root to assets
$chmp_config[ 'assets_folder' ]  = 'chmp/assets/';
$chmp_config[ 'content_folder' ] = 'chmp/content/';

/* - - - - - - - - - - - - - -login dialog.
Use this to customize login, or if your language is missing
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

// - - - - - - - - - - - - - -jquery
// true = load from googleapis , false = load internaly
$chmp_config[ 'jquery_external' ] = TRUE;
$chmp_config[ 'jquery_version' ]  = '2.1.0';

// - - - - - - - - - - - - - - editor navigation
// false (default) adds navigation as a block on top of the page
// true: navigation floats over content, this covers a part of the content, but may work better if you have a lot of design with absolute positioning
$chmp_config[ 'float_navigation' ] = FALSE;

// - - - - - - - - - - - - - - empty textarea marker
// This html-chr is shown in editor to indicate an empty text area
// This chr is never shown on published pages, and should be something you wouldn't normally use in a text
// example if you use default 10002, you can't use ✒ in a text
$chmp_config[ 'empty_area_chr' ] = 10002;

// - - - - - - - - - - - - - - timezone
// A list of timezones can be found here:
// http://www.php.net/manual/en/timezones.php
// It's used to show when a perticular page was published
// Leave empty for server default
$chmp_config[ 'timezone' ] = 'Asia/Bangkok';

