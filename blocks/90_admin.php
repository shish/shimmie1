<?php
/*
 * admin.php (c) Shish 2005, 2006
 *
 * Filled in per-page (eg the view page fills it with "delete this image")
 */

if($pageType == "view") {
	if($user->isAdmin) {
		$blocks[] = <<<EOD
		<h3 onclick="toggle('admin')">Admin</h3>
		<div id="admin">
			<a href="admin.php?action=rmimage&image_id={$image->id}">Delete Image</a>
		</div>
EOD;
	}
}
?>
