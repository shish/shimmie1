<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		$pageNav
		<p><form action="index.php" method="GET">
			<input id="searchBox" name="tags" type="text" value="Search">
			<input type="submit" value="Find" style="display: none;">
		</form>
	</div>

	<h3 onclick="toggle('tags')">Edit Tags</h3>
	<div id="tags">
		<form action="./update.php" method="POST">
			<input name="image_id" type="hidden" value="$img_id">
			<input name="tags" type="text" value="$img_tags">
			<input type="submit" value="Set">
		</form>
		<p>$tagLinks
	</div>

	$commentBlock
	$userBlock
	$adminBlock
</div>

<div id="body">
	<h3>Image</h3>
	<center>
		<!-- <img onclick="scale(this)" src="$dir_images/$img_hash.$img_ext" alt="$img_fname" $scale> -->
		<img onclick="scale(this)" src="$img_link" alt="$img_tags" $scale>
		<br/>Short link: <input type="text" size="50" value="$baseurl$img_slink">
		<br/>Uploaded by $img_user
	</center>
</div>
EOD;
require_once "templates/footer.php";
?>
