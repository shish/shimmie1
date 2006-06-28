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
			<a href="metablock.php?block=admin&action=rmimage&image_id={$image->id}">Delete Image</a>
		</div>
EOD;
	}
}

if(($pageType == "block") && ($_GET["action"] = "rmimage")) {
	admin_or_die();

	/*
	 * Remove an image from the database, along with comments and tags
	 */
	$image_id = (int)defined_or_die($_GET["image_id"]);

	$row = sql_fetch_row(sql_query("SELECT ext FROM shm_images WHERE id=$image_id"));
	$iname = $config['dir_images']."/$image_id.".$row['ext'];
	$tname = $config['dir_thumbs']."/$image_id.jpg";
	if(file_exists($iname)) unlink($iname);
	if(file_exists($tname)) unlink($tname);

	sql_query("DELETE FROM shm_images WHERE id=$image_id");
	sql_query("DELETE FROM shm_tags WHERE image_id=$image_id");
	sql_query("DELETE FROM shm_comments WHERE image_id=$image_id");

	// view page no longer exists, go to the index
	header("X-Shimmie-Status: OK - Image Deleted");
	header("Location: index.php");
	echo "<a href='index.php'>Back</a>";
}
?>
