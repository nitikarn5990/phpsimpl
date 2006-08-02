<?php
	// Include the Config
	include_once('config.php');
	
	// Include the functions
	include_once('functions.php');
	
	// Include the Form Class
	include_once('db_form.php');
	
	// Include the Database Functions
	include_once('db_functions.php');
	
	// Include the Database Frameowrk
	include_once('db_template.php');
	
	// Include the Database Export
	include_once('db_export.php');
	
	// Clear Cache if need be
	if (CLEAR_CACHE === true)
		foreach (glob(FS_SIMPL . WS_CACHE . '*.cache') as $filename)
			unlink($filename);
?>