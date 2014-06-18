<?php
/**
 * Api-keys should not go on github so the real api-keys is excluded from git
 */

if (!defined('CHMP_EMAIL_HOST')) {
	define('CHMP_EMAIL_HOST', '');
	define('CHMP_EMAIL_USERNAME', '');
	define('CHMP_EMAIL_PASSWORD', '');
	define('CHMP_EMAIL_ENCRYPTION', 'ssl'); // '', 'ssl', 'tls'
	define('CHMP_EMAIL_PORT', 465);
}