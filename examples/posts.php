<?php
	// Prerequisites
	include_once('application_top.php');
	
	// Create the Post Class
	$myPost = new Post;
	
	// Setup the Display
	$display[] = array('title', 'date_entered', 'author_id', 'is_published');
	$locations = array('title' => '<a href="post.php?id={$item_id}">{$data}</a>');
	$options = array('is_published' => $yesno);
	
	// Create the Author Class
	$myAuthor = new Author;
	$display[] = array('first_name','last_name');
	$myPost->Join($myAuthor,'author_id','LEFT');
	
	// Add some Filtering
	if (trim($_GET['q']) != '')
		$myPost->search = trim($_GET['q']);
	
	// Figure out the display type and order, Taken from DisplayList(), Need to find a better way to do this, it does not work with joined orders
	$_SESSION[$myPost->table . '_sort'] = ($_GET['sort'] != '')?$_GET['sort']:$_SESSION[$myPost->table . '_sort'];
	$_SESSION[$myPost->table . '_order'] = ($_GET['order'] != '')?$_GET['order']:$_SESSION[$myPost->table . '_order'];
	
	// Get the List
	$myPost->GetList($display, $_SESSION[$myPost->table . '_sort'], $_SESSION[$myPost->table . '_order']);
	
	// Header
	define('PAGE_TITLE','Edit Posts');
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1>Blog Posts</h1>
	<form action="<?php echo htmlspecialchars(sprintf("%s%s%s","http://",$_SERVER["HTTP_HOST"],$_SERVER["REQUEST_URI"])); ?>" method="get" name="search" id="search">
		<fieldset>
			<legend>Search</legend>
			<div><label>Search:</label><input name="q" type="text" class="search" value="<?php echo stripslashes($_GET['q']); ?>" /> <input name="submit" type="submit" value="Search" class="submit" /></div>
		</fieldset>
	</form>
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
		<li class="add"><a href="post.php" title="A New Blog Post">Add New Post</a></li>
	</ul>
	<?php
		// Display the List
		$display = array('title', 'date_entered', 'first_name', 'last_name', 'is_published');
		$myPost->DisplayList($display, $locations, $options, false);
	?>
</div>
<?php
	// Footer
	include_once('inc/footer.php');
?>
