<?php
/**
 * Created on Nov 1, 2006
 * Filename define.php
 */
 
// Basic Information
define('ADDRESS','http://' . $_SERVER['SERVER_NAME'] . '/');

// Config
define('LOGGING',true);
define('DEBUG',false);
define('DEBUG_QUERY',false);
define('USE_CACHE',true);
define('CLEAR_CACHE',true);

// Directories
// Always Include trailing slash "/" in Direcories
define('DIR_ABS','/usr/local/www/sites/wcs/www.80/dev/cms/');
define('FS_SIMPL',DIR_ABS . 'simpl/');
define('DIR_INC','inc/');
define('DIR_CSS','css/');

// Tables
define('TB_ABBR','abbreviation');
define('TB_BUG','bugs');

// Basic Elements
$yesno = array('1' => 'Yes','0' => 'No');

// Database Connection Options
define('DB_USER','');
define('DB_HOST','');
define('DB_PASS','');
define('DB_DEFAULT','wcs_tool');
?>
