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
	
	$banned_ip_list = "";
	$res = sql_query("SELECT * FROM bans WHERE type='ip'");
	while($row = sql_fetch_row($res)) {
		$ip = $row["value"];
		$date = $row["date"];
		$reason = $row["reason"];
		$banned_ip_list .= "<br>$ip at $date for $reason (<a href='admin.php?action=removeipban&ip=$ip'>X</a>)\n";
	}

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
 * add an IP to the ban list
 */
else if($action == "addipban") {
	$ip = sql_escape(defined_or_die($_POST["ip"]));
	$reason = sql_escape(defined_or_die($_POST["reason"]));

	sql_query("INSERT INTO shm_bans(type, value, date, reason) VALUES ('ip', '$ip', now(), '$reason')");

	// go back to the viewed page
	header("X-Shimmie-Status: OK - IP Banned");
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}

/*
 * remove an IP from the ban list
 */
else if($action == "removeipban") {
	$ip = sql_escape(defined_or_die($_GET["ip"]));

	sql_query("DELETE FROM shm_bans WHERE type='ip' AND value='$ip'");

	// go back to the viewed page
	header("X-Shimmie-Status: OK - IP Allowed");
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}
?>
