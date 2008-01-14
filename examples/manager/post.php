<?php
	// Prerequisites
	include_once($_SERVER["DOCUMENT_ROOT"] . '/examples/manager/inc/application_top.php');
		
	// Create the Post Class
	$myPost = new Post;
	
	// Get a list of all the authors
	$myAuthor = new Author;
	$authors = $myAuthor->GetList(array('first_name', 'last_name'),'last_name','DESC');
	$author_list = array();
	
	// Format the author list how we would like
	foreach($authors as $author_id=>$author)
		$author_list[$author_id] = $author['first_name'] . ' ' . $author['last_name'];
	
	// Add State Options
	$myPost->SetOption('author_id', $author_list, 'Please Select');
	
	// If they are saving the Information
	if ($_POST['submit_button'] == 'Save Draft and Continue Editing' || $_POST['submit_button'] == 'Save' || $_POST['submit_button'] == 'Save and Publish'){
		$redirect = true;
		
		// Get all the Form Data
		$myPost->SetValues($_POST);
		
		switch($_POST['submit_button']){
			case 'Save Draft and Continue Editing':
				$myPost->SetValue('status', 'Draft');
				$redirect = false;
				break;
			case 'Save and Publish':
				$myPost->SetValue('status', 'Published');
			default:
				break;
		}
		
		// Save the info to the DB if there is no errors
		if ($myPost->Save()){
			SetAlert('Post Information Saved.','success');
			
			// Redirect if needed
			if ($redirect){
				header('location:posts.php');
				die();
			}	
		}
	}
	
	// If Deleting the Page
	if ($_POST['submit_button'] == 'Delete'){
		// Get all the form data
		$myPost->SetValues($_POST);
		
		// Remove the info from the DB
		if ($myPost->Delete()){
			// Set status and redirect
			SetAlert('Post Deleted Successfully','success');
			header('location:posts.php');
			die();
		}else{
			SetAlert('Error Deleting Post, Please Try Again');
		}
	}
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != '' && $myPost->GetPrimary() == ''){
		// Set the primary key
		$myPost->SetPrimary((int)$_GET['id']);
		
		// Try to get its information
		if (!$myPost->GetInfo()){
			SetAlert('Invalid Post, please try again');
			$myPost->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myPost->GetPrimary() != '')?'Edit':'Add') . ' Post');
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1><?php echo PAGE_TITLE; ?></h1>
</div>
<div id="data">
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
		<?php $myPost->Form(); ?>
		<fieldset class="submit_button">
			<label for="submit_button">&nbsp;</label><input name="submit_button" type="submit" value="Save Draft and Continue Editing" class="submit" />
			<input name="submit_button" type="submit" value="Save" class="submit" />
			<input name="submit_button" type="submit" value="Save and Publish" class="submit" />
			<?php echo ($myPost->GetPrimary() != '')?' <input name="submit_button" type="submit" value="Delete" class="submit" />':''; ?>
		</fieldset>
	</form>
</div>
<?php 
	// Footer
	include_once('inc/footer.php');
?>