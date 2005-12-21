<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		<a href='index.php'>Index</a> | 
		<a href='tags.php?mode=alphabet'>Alphabetical</a> | 
		<a href='tags.php?mode=popular'>Popularity</a> | 
		<a href='tags.php?mode=map'>Map</a>
	</div>

	$uploadBlock
	$commentBlock
	$popularBlock
	$userBlock
	$adminBlock
</div>

<div id="body">
	<h3>List</h3>
	<div id="taglist">$tlist</div>
</div>
EOD;
require_once "templates/footer.php";
?>
