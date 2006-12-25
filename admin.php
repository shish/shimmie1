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
	$row = $db->Execute("SELECT * FROM bans WHERE type='ip'");
	while(!$row->EOF) {
		$ip = html_escape($row->fields["value"]);
		$date = html_escape($row->fields["date"]);
		$reason = html_escape($row->fields["reason"]);
		$banned_ip_list .= "<br>$ip at $date for $reason (<a href='admin.php?action=removeipban&ip=$ip'>X</a>)\n";
		$row->MoveNext();
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
	$search = defined_or_die($_POST["search"]);
	$replace = defined_or_die($_POST["replace"]);

	$db->Execute("UPDATE tags SET tag=? WHERE tag=?", Array($replace, $search));

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
	$navigation = "<a href='index.php'>Index</a> | <a href='admin.php'>Admin</a><br>";
	$data = $list;
	include_once "templates/generic.php";
}


/*
 * add an IP to the ban list
 */
else if($action == "addipban") {
	$ip = defined_or_die($_POST["ip"]);
	$reason = defined_or_die($_POST["reason"]);

	$db->Execute("INSERT INTO bans(type, value, reason, date) ".
		         "VALUES (?, ?, ?, now())", Array('ip', $ip, $reason));

	// go back to the viewed page
	header("X-Shimmie-Status: OK - IP Banned");
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}

/*
 * remove an IP from the ban list
 */
else if($action == "removeipban") {
	$ip = defined_or_die($_GET["ip"]);

	$db->Execute("DELETE FROM bans WHERE type=? AND value=?", Array('ip', $ip));

	// go back to the viewed page
	header("X-Shimmie-Status: OK - IP Allowed");
	header("Location: admin.php");
	echo "<a href='admin.php'>Back</a>";
}
?>
