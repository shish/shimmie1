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
?>
