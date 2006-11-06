<?php
/**
 * Created on Nov 5, 2006
 * Filename view.php
 */
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	
	// Setup the Display
	$display = array('title','author','category','is_published','body');
	// Do not show these fields
	$hidden = array();
	// Create State Options
	$options = array('is_published' => $yesno);
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != ''){
		$myPost->SetPrimary((int)$_GET['id']);
		if (!$myPost->GetInfo()){
			SetAlert('Invalid Post, please try again');
			$myPost->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myPost->GetPrimary() != '')?htmlspecialchars($myPost->GetValue('title')):'Error'));
	include_once('inc/header.php');
	
	echo '<h1>' . (($myPost->GetPrimary() != '')?'View Post':'Error') . '</h1>' . "\n";
?>	
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
		echo '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($myPost->GetValue('date_entered'))) . (($myPost->GetValue('category') != '')?' in ' . htmlspecialchars($myPost->GetValue('category')):'') . (($myPost->GetValue('author') != '')?' by ' . htmlspecialchars($myPost->GetValue('author')):'') . '</div>';
		echo '<div id="post">' . htmlspecialchars($myPost->GetValue('body')) . '</div>';
		echo '</div>' . "\n";
	}
	
	// Footer
	include_once('inc/footer.php');
?>