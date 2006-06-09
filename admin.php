<?php
/*
 * admin.php (c) Shish 2005, 2006
 *
 * Things relating to overall board management
 */

require_once "header.php";

admin_or_die();

$action = $_GET['action'];


/*
 * Default action - show a list of functions
 */
if(is_null($action)) {
	header("X-Shimmie-Status: OK - Admin Shown");
	$title = "Board Admin";
	$blocks = getBlocks("admin");
	require_once "templates/admin.php";
}


/*
 * do a mass search & replace
 *
 * XXX: Should we warn the user that if there are already
 * lots of images with a tag, they'll be impossible to
 * separate once merged?
 */
else if($action == "replacetag") {
	$search = sql_escape(defined_or_die($_POST["search"]));
	$replace = sql_escape(defined_or_die($_POST["replace"]));

	sql_query("UPDATE shm_tags SET tag='$replace' WHERE tag='$search'");

	// go back to the viewed page
	header("X-Shimmie-Status: OK - Tags Replaced");
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}


/*
 * Remove an image from the database, along with comments and tags
 */
else if($action == "rmimage") {
	$image_id = (int)defined_or_die($_GET["image_id"]);

	$row = sql_fetch_row(sql_query("SELECT hash, ext FROM shm_images WHERE id=$image_id"));
	$di = $config['dir_images'];
	$dt = $config['dir_thumbs'];
	$id = $row['id'];
	$ext = $row['ext'];
	$iname = "$di/$id.$ext";
	$tname = "$dt/$id.$ext";
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


/*
 * Remove a comment from the database
 */
else if($action == "rmcomment") {
	$comment_id = (int)defined_or_die($_GET["comment_id"]);

	sql_query("DELETE FROM shm_comments WHERE id=$comment_id");

	// view page no longer exists, go to the index
	header("X-Shimmie-Status: OK - Comment Deleted");
	header("Location: index.php");
	echo "<a href='index.php'>Back</a>";
}
?>
