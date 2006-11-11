<?php
/**
 * Created on Nov 11, 2006
 * Filename authors.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Author Class
	$myAuthor = new Author;
	
	// Setup the Display
	$display = array('first_name', 'last_name', 'email', 'date_entered');
	$locations = array('title' => '<a href="author.php?id={$item_id}">{$data}</a>');
	$options = array();
	
	// Add some Filtering
	if (trim($_GET['q']) != '')
		$myAuthor->search = trim($_GET['q']);
		
	// Get the List
	$myAuthor->GetList($display,'last_name','DESC');
	
	// Header
	define('PAGE_TITLE','Edit Authors');
	include_once('inc/header.php');
?>
<h1>Blog Authors</h1>
<form action="<?php echo htmlspecialchars(sprintf("%s%s%s","http://",$_SERVER["HTTP_HOST"],$_SERVER["REQUEST_URI"])); ?>" method="get" name="search" id="search">
	<fieldset>
		<legend>Search</legend>
		<div><label>Search:</label><input name="q" type="text" class="search" value="<?php echo stripslashes($_GET['q']); ?>" /> <input name="submit" type="submit" value="Search" class="submit" /></div>
	</fieldset>
</form>
<ul id="options">
	<li class="add"><a href="author.php" title="A New Blog Author">Add New Author</a></li>
</ul>
<?php
	// Display the List
	$myAuthor->DisplayList($display, $locations, $options, false);
	
	// Footer
	include_once('inc/footer.php');
?>