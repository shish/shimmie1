<?php
/*
 * admin.php (c) Shish 2005
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
	$search = addslashes($_POST["search"]);
	$replace = addslashes($_POST["replace"]);

	sql_query("UPDATE shm_tags SET tag='$replace' WHERE tag='$search'");

	// go back to the viewed page
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}


/*
 * Remove an image from the database, along with comments and tags
 *
 * FIXME: Get rid of the file on-disk too
 */
else if($action == "delete") {
	$image_id = (int)$_GET["image_id"];

	sql_query("DELETE FROM shm_images WHERE id=$image_id");
	sql_query("DELETE FROM shm_tags WHERE image_id=$image_id");
	sql_query("DELETE FROM shm_comments WHERE image_id=$image_id");

	// view page no longer exists, go to the index
	header("Location: index.php");
	echo "<a href='index.php'>Back</a>";
}
?>
