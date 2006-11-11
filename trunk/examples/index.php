<?php
/**
 * Created on Nov 1, 2006
 * Filename index.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Grab some Information about the Blog
	$myAuthor = new Author;
	$authors = $myAuthor->GetList('count');
	
	$myPost = new Post;
	$posts = $myPost->GetList('count');
		
	// Header
	define('PAGE_TITLE','Welcome');
	include_once('inc/header.php');
?>
<h1>Blog Manager</h1>
<p>Welcome to the Blog Manager, Please select an option on the left.</p>
<ul>
	<li>Authors: <?php echo $authors['count']; ?></li>
	<li>Posts: <?php echo $posts['count']; ?></li>
</ul>
<?php
	// Footer
	include_once('inc/footer.php');
?>
