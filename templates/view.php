<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">$pageNav</div>

	<h3 onclick="toggle('tags')">Tags</h3>
	<div id="tags">
		<form action="./update.php" method="POST">
			<input name="image_id" type="hidden" value="$image_id">
			<input name="tags" type="text" value="$tags">
			<input type="submit" value="Set">
		</form>
		<p>$tagLinks
	</div>

	<h3 onclick="toggle('comments')">Comments</h3>
	<div id="comments">
		$comments
		<form action="comment.php" method="POST">
			<input type="hidden" name="image_id" value="$image_id">
			<input id="commentBox" type="text" name="comment"
				value="Comment"
				onFocus="cleargray(this, 'Comment')"
				onBlur="setgray(this, 'Comment')"
				>
			<input type="submit" value="Say" style="display: none;">
		</form>
	</div>

	$userBlock
	$adminBlock
</div>

<div id="body">
	<h3>Image</h3>
	<center>
		<img onclick="scale(this)" src="$dir_images/$img_hash.$img_ext" alt="$img_fname" $scale>
		<br/>Uploaded by $img_user
	</center>
</div>
EOD;
require_once "templates/footer.php";
?>
