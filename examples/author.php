<?php
/**
 * Created on Nov 11, 2006
 * Filename author.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Author Class
	$myAuthor = new Author;
	
	// Setup the Display
	$display = array('first_name','last_name','email');
	// Do not show these fields
	$hidden = array();
	// Create State Options
	$options = array();
	
	// If they are saving the Information
	if ($_POST['submit_button'] == 'Save Author'){
		// Get all the Form Data
		$myAuthor->SetValues($_POST);
		// Check for Required Items
		$myAuthor->CheckRequired();
		// Save the info to the DB if there is no errors
		if ($myAuthor->Save())
			SetAlert('Author Information Saved.','success');
	}

	// If Deleting the Page
	if ($_POST['submit_button'] == 'Delete'){
		// Get all the form data
		$myAuthor->SetValues($_POST);
		// Remove the info from the DB
		if ( $myAuthor->Delete()){
			SetAlert('Author Deleted Successfully','success');
			header('location:authors.php');
			die();
		}else{
			SetAlert('Error Deleting Author, Please Try Again');
		}
	}
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != ''){
		$myAuthor->SetPrimary((int)$_GET['id']);
		if (!$myAuthor->GetInfo()){
			SetAlert('Invalid Author, please try again');
			$myAuthor->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myAuthor->GetPrimary() != '')?'Edit':'Add') . ' Author');
	include_once('inc/header.php');
	
	echo '<h1>' . (($myAuthor->GetPrimary() != '')?'Edit':'Add') . ' Author</h1>' . "\n";
?>	
<div id="notifications">
<?php
	// Report errors to the user
	Alert(GetAlert('error'));
	Alert(GetAlert('success'),'success');
?>
</div>

<ul id="options">
	<li class="back"><a href="authors.php">Return to Author List</a></li>
	<?php echo ($myAuthor->GetPrimary() != '')?'<li class="add"><a href="author.php" title="Add a New Blog Author">Add New Author</a></li>':'';?>
</ul>

<form action="author.php<?php echo ($myAuthor->GetPrimary() != '')?'?id=' . $myAuthor->GetPrimary():''; ?>" method="post" name="edit_author">
	<?php $myAuthor->Form($display, $hidden, $options); ?>
	<fieldset class="submit_button">
		<label for="submit_button">&nbsp;</label><input name="submit_button" id="submit_button" type="submit" value="Save Author" class="submit" /><?php echo ($myAuthor->GetPrimary() != '')?' <input name="submit_button" type="submit" value="Delete" class="submit" />':''; ?>
	</fieldset>
</form>
<?php 
	// Footer
	include_once('inc/footer.php');
?>
