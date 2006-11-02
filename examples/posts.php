<?php
/**
 * Created on Nov 2, 2006
 * Filename posts.php
 */
// Prerequisites
include_once('application_top.php');


// Create the Template Class
$myTemplate = new Template;

// Setup the Display
$display = array('title','mode','is_public');

// Set the Default Values
$myTemplate->SetValue('site_id',$mySite->GetPrimary());

// Add some Filtering
if (trim($_GET['q']) != '')
	$myTemplate->search = trim($_GET['q']);
	
// Get the List
$myTemplate->GetList($display);

// Header
include_once('inc/header.php');

// Display the List
$myTemplate->DisplayList($display);

// Footer
include_once('inc/footer.php');
?>
