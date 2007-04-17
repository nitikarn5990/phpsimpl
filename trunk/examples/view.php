<?php
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	$myAuthor = new Author;
	
	// Setup the Display
	$display = array('title','author_id','category','is_published','body');
	// Do not show these fields
	$hidden = array();
	// Create State Options
	$options = array('is_published' => $yesno);
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != ''){
		// Set the primary key
		$myPost->SetPrimary((int)$_GET['id']);
		
		// Try to get the posts information
		if (!$myPost->GetInfo()){
			SetAlert('Invalid Post, please try again');
			$myPost->ResetValues();
		}else{
			// Get the Authors Information (this can eventually be cleaned up to one Query with e GetList)
			$myAuthor->SetPrimary($myPost->GetValue('author_id'));
			if (!$myAuthor->GetInfo())
				$myAuthor->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myPost->GetPrimary() != '')?htmlspecialchars($myPost->GetValue('title')):'Error'));
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
		<li class="back"><a href="blog.php">Return to Blog</a></li>
	</ul>
	
	<?php
		// Make sure there is a post to view
		if ($myPost->GetPrimary() != ''){
			echo '<div id="view-post">' . "\n";
			echo '<h1>' . htmlspecialchars($myPost->GetValue('title')) . '</h1>';
			echo '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($myPost->GetValue('date_entered'))) . (($myPost->GetValue('category') != '')?' in ' . htmlspecialchars($myPost->GetValue('category')):'') . (($myAuthor->GetValue('author_id') != '')?' by <a href="mailto:' . htmlspecialchars($myAuthor->GetValue('email')) . '" title="Send Email to Author">' . htmlspecialchars($myAuthor->GetValue('first_name')) . ' ' . htmlspecialchars($myAuthor->GetValue('last_name')) . '</a>':' by Anonymous') . '</div>';
			echo '<div id="post">' . htmlspecialchars($myPost->GetValue('body')) . '</div>';
			echo '</div>' . "\n";
		}
	?>
</div>
<?php
	// Footer
	include_once('inc/footer.php');
?>