<?php
// Basic Information
define('ADDRESS', 'http://' . $_SERVER['SERVER_NAME'] . '/examples/');
define('TITLE', 'PHPSimpl Blog');

// Simpl Config
define('DEBUG',false);
define('DEBUG_QUERY',false);
define('USE_CACHE',true);
define('QUERY_CACHE',false);
define('DB_SESSIONS',false);
define('USE_ENUM',true);

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS', '/usr/local/www/examples/');
define('DIR_INC', 'inc/');
define('DIR_CSS', 'css/');
define('DIR_MANAGER', 'manager/');

// Simpl Define
define('FS_SIMPL', DIR_ABS . 'simpl/');
define('FS_CACHE', DIR_ABS . 'cache/');

// Database Connection Options
define('DB_USER', 'nick');
define('DB_PASS', 'trysk8ting');
define('DB_HOST', 'localhost');
define('DB_DEFAULT', 'simpl_example');
?>