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
	$blocks = get_blocks_html("admin");
	
	$banned_ip_list = "";
	$res = sql_query("SELECT * FROM bans WHERE type='ip'");
	while($row = sql_fetch_row($res)) {
		$ip = html_escape($row["value"]);
		$date = html_escape($row["date"]);
		$reason = html_escape($row["reason"]);
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
 * bulk add from a folder
 */
else if($action == "bulkadd") {
	$list = add_dir(defined_or_die($_POST["dir"]));
	
	header("X-Shimmie-Status: OK - Images Uploaded");
	$title = "Bulk Upload";
	$message = "<br><a href='admin.php'>Back</a><br>";
	$data = $list;
	include_once "templates/generic.php";
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
