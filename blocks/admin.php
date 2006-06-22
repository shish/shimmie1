<?php
/*
 * admin.php (c) Shish 2005, 2006
 *
 * Filled in per-page (eg the view page fills it with "delete this image")
 */

if($pageType == "view") {
	global $image;

	if($user->isAdmin()) {
		$blocks[90] .= <<<EOD
		<h3 id="admin-toggle" onclick="toggle('admin')">Admin</h3>
		<div id="admin">
			<a href="admin.php?action=rmimage&image_id={$image->id}">Delete Image</a>
		</div>
EOD;
	}
}
?>
