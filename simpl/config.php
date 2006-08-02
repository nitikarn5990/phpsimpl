<?php
// Always Include trailing slash "/" in Direcories
define('DEBUG',false);
define('USE_CACHE',true);
define('CLEAR_CACHE',false);

// Where things are sitting
define('WS_SIMPL','simpl/');
define('FS_SIMPL',DIR_ABS . WS_SIMPL);
define('WS_SIMPL_IMAGE','img/');
define('WS_SIMPL_INC','inc/');
define('WS_SIMPL_CSS','css/');
define('WS_SIMPL_JS','js/');
define('WS_CACHE','cache/');

// Database Connection Option
define('DB_USER',DBUSER);
define('DB_HOST',DBHOST);
define('DB_PASS',DBPASS);
define('DB_DEFAULT','db1');
?>