<?php
/*
 * admin.php (c) Shish 2005, 2006
 *
 * Filled in per-page (eg the view page fills it with "delete this image")
 */

class admin extends block {
	function get_title() {
		return "Admin";
	}

	function get_html($pageType) {
		if($pageType == "view") {
			global $image, $user;

			if($user->isAdmin()) {
				return "<a href='metablock.php?block=admin&action=rmimage&image_id={$image->id}'>Delete Image</a>";
			}
		}
	}

	function get_priority() {
		return 90;
	}

	function run($action) {
		if($action == "rmimage") {
			admin_or_die();

			delete_image(defined_or_die($_GET["image_id"]));
			
			// view page no longer exists, go to the index
			header("Location: index.php");
			echo "<a href='index.php'>Back</a>";
		}
	}
}
?>
