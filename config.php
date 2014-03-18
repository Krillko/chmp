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

