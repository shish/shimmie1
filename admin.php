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
	$title = "Board Admin";
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
	$search = sql_escape($_POST["search"]);
	$replace = sql_escape($_POST["replace"]);

	sql_query("UPDATE shm_tags SET tag='$replace' WHERE tag='$search'");

	// go back to the viewed page
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}


/*
 * Remove an image from the database, along with comments and tags
 */
else if($action == "delete") {
	$image_id = (int)$_GET["image_id"];

	$row = sql_fetch_row(sql_query("SELECT hash, ext FROM shm_images WHERE id=$image_id"));
	$di = $config['dir_images'];
	$dt = $config['dir_thumbs'];
	$hash = $row['hash'];
	$ext = $row['ext'];
	$iname = "$di/$hash.$ext";
	$tname = "$dt/$hash.$ext";
	if(file_exists($iname)) unlink($iname);
	if(file_exists($tname)) unlink($tname);

	sql_query("DELETE FROM shm_images WHERE id=$image_id");
	sql_query("DELETE FROM shm_tags WHERE image_id=$image_id");
	sql_query("DELETE FROM shm_comments WHERE image_id=$image_id");

	// view page no longer exists, go to the index
	header("Location: index.php");
	echo "<a href='index.php'>Back</a>";
}
?>
