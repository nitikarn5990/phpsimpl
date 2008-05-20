<?php
// Debug everything, set only if you need to know exactly what is going on
define('DEBUG', false);
// Debug only raw queies
define('DEBUG_QUERY', false);
// Store all the debug info in a debug log file
define('DEBUG_LOG', false);
// Use a file cache to store the data structures (recommended)
define('USE_CACHE', true);
// Pull the ENUM data from database as options (only use if your tables have ENUM data)
define('USE_ENUM', false);
// Cache query results to a file for faster re-queries
define('QUERY_CACHE', false);
// Store the session data in a table (import the table from the examples)
define('DB_SESSIONS', false);

// Table Stripes
define('SIMPL_TABLE_STRIPES', true);

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
define('DB_DEFAULT', NULL);
?>