<?php
/**
 * Created on Nov 1, 2006
 * Filename application_top.php
 */
 
// Start the user sessions
session_start();

// Global Defines
include_once('inc/define.php');

// Simpl Framework
include_once(FS_SIMPL . 'simpl.php');

// Load the Simpl classes
$mySimpl->Load('Form');
$mySimpl->Load('Db');
$mySimpl->Load('DbTemplate');

// Custom Functions and Classes
include_once(DIR_INC . 'functions.php');
include_once(DIR_INC . 'classes.php');
?>
