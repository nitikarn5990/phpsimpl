<?php
/**
 * Created on Nov 1, 2006
 * Filename define.php
 */
// Basic Information
define('ADDRESS', 'http://' . $_SERVER['SERVER_NAME'] . '/');
define('TITLE', 'My Blog');

// Config
define('DEBUG', false);
define('DEBUG_QUERY', false);
define('USE_CACHE', false);
define('CLEAR_CACHE', true);

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS', '/users/nick/Sites/example/');
define('FS_SIMPL', DIR_ABS . 'simpl/');
define('FS_CACHE', DIR_ABS . 'examples/cache/');
define('DIR_INC', 'inc/');
define('DIR_CSS', 'css/');

// Tables
define('TB_AUTHOR', 'author');
define('TB_POST', 'post');

// Basic Elements
$yesno = array('1' => 'Yes','0' => 'No');

// Database Connection Options
define('DB_USER', '');
define('DB_HOST', '');
define('DB_PASS', '');
define('DB_DEFAULT', 'simpl_example');
?>