<?php
	// Prerequisites
	include_once('application_top.php');
	
		// Create the Post Class
	$myPost = new Post;
	$myPost->SetValue('status', 'Published');
	
	// Setup the Display
	$display[] = array('title', 'date_entered', 'author_id', 'category', 'body');
	
	// Create the Author Class
	$myAuthor = new Author;
	$display[] = array('first_name','last_name','email');
	$myPost->Join($myAuthor,'author_id','LEFT');
	
	// Add some Filtering
	if (trim($_GET['q']) != '')
		$myPost->search = trim($_GET['q']);
		
	// Set some filters to only get the published Posts
	$myPost->SetValue('is_published','1');
		
	// Get the List
	$myPost->GetList($display);
		
	// Header
	define('PAGE_TITLE','Welcome');
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1>Answers are always in the simplest items</h1>
</div>
<div id="data">
	<?php
		if (is_array($myPost->results) && count($myPost->results) > 0){
			echo '<dl id="posts">' . "\n";
			
			foreach($myPost->results as $post){
				echo '<dt><a href="view.php?id=' . $post['post_id'] . '" title="' . htmlspecialchars($post['title']) . '">' . htmlspecialchars($post['title']) . '</a></dt>' . "\n";
				echo '<dd>' . htmlspecialchars(substr($post['body'],0,350)) . 
					"\n" . '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($post['date_entered'])) . 
					(($post['category'] != '')?' in ' . htmlspecialchars($post['category']):'') . 
					(($post['first_name'] != '')?' by <a href="mailto:' . htmlspecialchars($post['email']) . '" title="Email this Author">' . htmlspecialchars($post['first_name']) . ' ' . htmlspecialchars($post['last_name']) . '</a>':' by Anonymous') . '</div></dd>' . "\n";
			}
			
			echo '</dl>' . "\n";
		}else{
			echo '<p>Currently there are no posts, please <a href="manager/post.php">add some</a></p>' . "\n";
		}
	?>
</div>
<?php include_once('inc/footer.php'); ?>