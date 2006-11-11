<?php
/**
 * Created on Nov 5, 2006
 * Filename post.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	
	// Get a list of all the authors
	$myAuthor = new Author;
	$authors = $myAuthor->GetList(array('first_name', 'last_name'),'last_name','DESC');
	$author_list[] = 'Please Select';
	
	if (is_array($authors))
		foreach($authors as $author_id=>$author)
			$author_list[$author_id] = $author['first_name'] . ' ' . $author['last_name'];
	
	// Setup the Display
	$display = array('title','author_id','category','is_published','body');
	// Do not show these fields
	$hidden = array();
	// Create State Options
	$options = array('is_published' => $yesno, 'author_id' => $author_list);
	
	// If they are saving the Information
	if ($_POST['submit_button'] == 'Save Post'){
		// Get all the Form Data
		$myPost->SetValues($_POST);
		// Check for Required Items
		$myPost->CheckRequired();
		// Save the info to the DB if there is no errors
		if ($myPost->Save())
			SetAlert('Post Information Saved.','success');
	}

	// If Deleting the Page
	if ($_POST['submit_button'] == 'Delete'){
		// Get all the form data
		$myPost->SetValues($_POST);
		// Remove the info from the DB
		if ( $myPost->Delete()){
			SetAlert('Post Deleted Successfully','success');
			header('location:posts.php');
			die();
		}else{
			SetAlert('Error Deleting Post, Please Try Again');
		}
	}
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != ''){
		$myPost->SetPrimary((int)$_GET['id']);
		if (!$myPost->GetInfo()){
			SetAlert('Invalid Post, please try again');
			$myPost->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myPost->GetPrimary() != '')?'Edit':'Add') . ' Post');
	include_once('inc/header.php');
	
	echo '<h1>' . (($myPost->GetPrimary() != '')?'Edit':'Add') . ' Post</h1>' . "\n";
?>	
<div id="notifications">
<?php
	// Report errors to the user
	Alert(GetAlert('error'));
	Alert(GetAlert('success'),'success');
?>
</div>

<ul id="options">
	<li class="back"><a href="posts.php">Return to Post List</a></li>
	<?php echo ($myPost->GetPrimary() != '')?'<li class="add"><a href="post.php" title="Add a New Blog Post">Add New Post</a></li>':'';?>
</ul>

<form action="post.php<?php echo ($myPost->GetPrimary() != '')?'?id=' . $myPost->GetPrimary():''; ?>" method="post" name="edit_post">
	<?php $myPost->Form($display, $hidden, $options); ?>
	<fieldset class="submit_button">
		<label for="submit_button">&nbsp;</label><input name="submit_button" id="submit_button" type="submit" value="Save Post" class="submit" /><?php echo ($myPost->GetPrimary() != '')?' <input name="submit_button" type="submit" value="Delete" class="submit" />':''; ?>
	</fieldset>
</form>
<?php 
	// Footer
	include_once('inc/footer.php');
?>