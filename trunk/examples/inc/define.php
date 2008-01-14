<?php
$mode = 'dev';

switch ($mode){
	case 'production':
		// Simpl Config
		define('LOGGING', true);
		define('DEBUG', false);
		define('DEBUG_QUERY', false);
		define('USE_CACHE', true);
		define('USE_ENUM', true);
		define('QUERY_CACHE', true);
		define('DB_SESSIONS', true);
		
		break;
	case 'dev':
	case 'testing':
	default:
		// Simpl Config
		define('LOGGING', true);
		define('DEBUG', false);
		define('DEBUG_QUERY', false);
		define('USE_CACHE', true);
		define('USE_ENUM', true);
		define('QUERY_CACHE', false);
		define('DB_SESSIONS', false);
		
		break;
}

// Basic Information
define('ADDRESS', 'http://' . $_SERVER['SERVER_NAME'] . '/examples/');
define('TITLE', 'PHPSimpl Blog');

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS', $_SERVER["DOCUMENT_ROOT"] . '/examples/');
define('DIR_INC', 'inc/');
define('DIR_CSS', 'css/');
define('DIR_MANAGER', 'manager/');

// Simpl Directories
define('FS_SIMPL', DIR_ABS . 'simpl/');
define('FS_CACHE', DIR_ABS . 'cache/');


// Database Connection Options
define('DB_USER', 'nick');
define('DB_PASS', 'trysk8ting');
define('DB_HOST', 'localhost');
define('DB_DEFAULT', 'simpl_example');
?>