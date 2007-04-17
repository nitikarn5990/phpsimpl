<?php
// Basic Information
define('ADDRESS', 'http://' . $_SERVER['SERVER_NAME'] . '/examples/');
define('TITLE', 'My Blog');

// Simpl Config
define('DEBUG',false);
define('DEBUG_QUERY',false);
define('USE_CACHE',true);
define('QUERY_CACHE',true);
define('DB_SESSIONS',true);

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS', '/usr/local/www/examples/');
define('FS_SIMPL', DIR_ABS . 'simpl/');
define('FS_CACHE', DIR_ABS . 'cache/');
define('DIR_INC', 'inc/');
define('DIR_CSS', 'css/');

// Basic Elements
$yesno = array('1' => 'Yes','0' => 'No');

// Database Connection Options
define('DB_USER', '');
define('DB_PASS', '');
define('DB_HOST', 'localhost');
define('DB_DEFAULT', 'simpl_example');
?>