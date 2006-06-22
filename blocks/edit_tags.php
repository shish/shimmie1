<?php
/*
 * edittags.php (c) Shish 2006
 *
 * A block to edit image tags
 */

if($pageType == "view") {
	global $image;

	$blocks[20] .= <<<EOD
	<script language="javascript">
		function tagEditConfirm() {
			if(getCookie("I_know_that_edit_is_not_search")) {
				confirmed = true;
			}
			else {
				confirmed = confirm(""+
					"This is a tag edit box.\\n"+
					"This is not a search box.\\n"+
					"Do you *really* want to edit the tags?"+
					"");
				if(confirm("Can you remember that the thing labelled\\n'edit tags' is not a search box?")) {
					setCookie("I_know_that_edit_is_not_search", true);
				}
			}
			return confirmed;
		}
	</script>
	<h3 onclick="toggle('tags')">Edit Tags</h3>
	<div id="tags">
		<form onSubmit="return tagEditConfirm();" action="metablock.php?block=edit_tags" method="POST">
			<input name="image_id" type="hidden" value="$image->id">
			<input name="tags" type="text" value="$image->tags">
			<!-- <input type="submit" value="Set"> -->
		</form>
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
