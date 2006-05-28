<?php
/*
 * edittags.php (c) Shish 2006
 *
 * A block to edit image tags
 */

if($pageType == "view") {
	global $img_id, $img_tags, $tagLinks;

	$blocks[] = <<<EOD
	<h3 onclick="toggle('tags')">Edit Tags</h3>
	<div id="tags">
		<form action="./update.php" method="POST">
			<input name="image_id" type="hidden" value="$img_id">
			<input name="tags" type="text" value="$img_tags">
			<input type="submit" value="Set">
		</form>
		<p>$tagLinks
	</div>
EOD;
}
?>
