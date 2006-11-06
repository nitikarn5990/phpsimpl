<?php
/**
 * Created on Nov 5, 2006
 * Filename blog.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	
	// Setup the Display
	$display = array('title', 'date_entered', 'author', 'category', 'body');
	
	// Add some Filtering
	if (trim($_GET['q']) != '')
		$myPost->search = trim($_GET['q']);
		
	// Set some filters to only get the published Posts
	$myPost->SetValue('is_published','1');
		
	// Get the List
	$myPost->GetList($display);
	
	// Header
	define('PAGE_TITLE','View Blog');
	include_once('inc/header.php');
?>
<h1>My Blog</h1>
<?php
	if (is_array($myPost->results) && count($myPost->results) > 0){
		echo '<dl id="posts">' . "\n";
		
		foreach($myPost->results as $post){
			echo '<dt><a href="view.php?id=' . $post['post_id'] . '" title="' . htmlspecialchars($post['title']) . '">' . htmlspecialchars($post['title']) . '</a></dt>' . "\n";
			echo '<dd>' . htmlspecialchars(substr($post['body'],0,350)) . "\n" . '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($post['date_entered'])) . (($post['category'] != '')?' in ' . htmlspecialchars($post['category']):'') . (($post['author'] != '')?' by ' . htmlspecialchars($post['author']):'') . '</div></dd>' . "\n";
		}
		
		echo '</dl>' . "\n";
	}else{
		echo '<p>Currently there are no posts, please add some</p>' . "\n";
	}
	
	// Footer
	include_once('inc/footer.php');
?>
