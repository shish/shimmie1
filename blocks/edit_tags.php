<?php
/*
 * edittags.php (c) Shish 2006
 *
 * A block to edit image tags
 */

if($pageType == "view") {
	global $image, $tagLinks;

	$tags = implode(" ", $image->tags);

	$blocks[20] = <<<EOD
	<h3 onclick="toggle('tags')">Edit Tags</h3>
	<div id="tags">
		<form action="metablock.php?block=edit_tags" method="POST">
			<input name="image_id" type="hidden" value="$image->id">
			<input name="tags" type="text" value="$tags">
			<input type="submit" value="Set">
		</form>
		<p>$tagLinks
	</div>
EOD;
}

if($pageType == "block") {
	$config['upload_anon'] || user_or_die();

	// get input
	$image_id = (int)$_POST['image_id'];
	updateTags($image_id, sql_escape($_POST['tags']));

	// go back
	header("Location: view.php?image_id=$image_id");
	echo "<p><a href='view.php?image_id=$image_id'>Back</a>";
}
?>
