<?php
/**
 * Created on Nov 2, 2006
 * Filename posts.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	
	// Setup the Display
	$display = array('title', 'date_entered', 'author', 'is_published');
	$locations = array('title' => '<a href="post.php?id={$item_id}">{$data}</a>');
	$options = array('is_published' => $yesno);
	
	// Add some Filtering
	if (trim($_GET['q']) != '')
		$myPost->search = trim($_GET['q']);
		
	// Get the List
	$myPost->GetList($display);
	
	// Header
	define('PAGE_TITLE','Edit Posts');
	include_once('inc/header.php');
?>
<h1>Blog Posts</h1>
<ul id="options">
	<li class="add"><a href="post.php" title="A New Blog Post">Add New Post</a></li>
</ul>
<?php
	// Display the List
	$myPost->DisplayList($display, $locations, $options);
	
	// Footer
	include_once('inc/footer.php');
?>
