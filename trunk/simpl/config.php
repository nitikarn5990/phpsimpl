<?php
// Main Config
define('DEBUG',false);
define('DEBUG_QUERY',false);
define('USE_CACHE',true);
define('QUERY_CACHE',false);

// Where things are sitting
// Always Include trailing slash "/" in Direcories
define('DIR_ABS','./');
define('WS_SIMPL','simpl/');
define('WS_SIMPL_IMAGE','img/');
define('WS_SIMPL_INC','inc/');
define('WS_SIMPL_CSS','css/');
define('WS_SIMPL_JS','js/');
define('WS_CACHE','cache/');
define('FS_SIMPL',DIR_ABS . WS_SIMPL);
define('FS_CACHE',FS_SIMPL . WS_CACHE);

// Database Connection Option
define('DB_USER',DBUSER);
define('DB_HOST',DBHOST);
define('DB_PASS',DBPASS);
define('DB_DEFAULT','db1');
?>
