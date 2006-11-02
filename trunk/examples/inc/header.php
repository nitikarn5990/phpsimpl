<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo TITLE; echo (defined('PAGE_TITLE'))?' - ' . PAGE_TITLE:''; ?></title>
<style type="text/css" media="all">
<!--/*--><![CDATA[/*><!--*/
	@import url("css/basic.css");
	@import url("css/advanced.css");
	@import url("<?php echo ADDRESS . WS_SIMPL . WS_SIMPL_CSS; ?>calendar.css");
/*]]>*/-->
</style>
<!--[if lt IE 7.]>
<script defer type="text/javascript" src="js/pngfix.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
<script src="<?php echo ADDRESS . WS_SIMPL . WS_SIMPL_JS; ?>calendar.js" type="text/javascript"></script>
<script src="js/general.js" type="text/javascript"></script>
</head>

<body>

<div id="container">
	<div id="header">
		<h1>Blog Manager</h1>
	</div>
	
	<div id="left-column">
		<ul>
			<li><a href="index.php" title="Return Home">Home</a></li>
			<li><a href="posts.php" title="Edit Posts">Edit Posts</a></li>
			<li><a href="view.php" title="View Posts" class="last">View Posts</a></li>
		</ul>
	</div>
	
	<div id="content">
	
