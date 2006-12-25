<?php
/*
 * edittags.php (c) Shish 2006
 *
 * A block to edit image tags
 */

class edit_tags extends block {
	function get_title() {
		return "Edit Tags";
	}

	function get_html($pageType) {
		if($pageType == "view") {
			global $image, $user;

			$h_tags = html_escape($image->tags);
			
			if($user->isAnonymous()) {
				return <<<EOD
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
		<form onSubmit="return tagEditConfirm();" action="metablock.php?block=edit_tags&amp;action=update" method="POST">
			<input name="image_id" type="hidden" value="$image->id">
			<input name="tags" type="text" value="$h_tags">
			<!-- <input type="submit" value="Set"> -->
		</form>
EOD;
			}
			else {
				return <<<EOD
		<form action="metablock.php?block=edit_tags&amp;action=update" method="POST">
			<input name="image_id" type="hidden" value="$image->id">
			<input name="tags" type="text" value="$h_tags">
			<!-- <input type="submit" value="Set"> -->
		</form>
EOD;
			}
		}
	}

	function get_priority() {
		return 20;
	}

	function run($action) {
		global $config;

		if($action == "update") {
			$config['upload_anon'] || user_or_die();

			// get input
			$image_id = int_escape(defined_or_die($_POST['image_id']));
			update_tags($image_id, defined_or_die($_POST['tags']));

			// go back
			header("Location: view.php?image_id=$image_id");
			echo "<p><a href='view.php?image_id=$image_id'>Back</a>";
		}
	}
}
?>
