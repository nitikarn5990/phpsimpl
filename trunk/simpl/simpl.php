<?php
// Start a session if not already started
if (session_id() == '')
	session_start();

// Include the Config
if (defined('FS_SIMPL'))
	include_once(FS_SIMPL . 'config.php');
else
	include_once(DIR_ABS . 'simpl/config.php');

// Include the functions
include_once(FS_SIMPL . 'functions.php');

// Include the Simpl Loader class
include_once(FS_SIMPL . 'main.php');

// Load the Base Classes
$mySimpl = new Simpl;
$myValidator = new Validate;
?>
