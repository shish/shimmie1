<?php
/*
 * user.php (c) Shish 2005, 2006
 *
 * Things relating to user self-management
 */

require_once "header.php";

if($_GET['action'] == "login") {
	up_login();
	exit;
}
else {
	user_or_die();
}

if(is_null($_GET['action'])) {
	header("X-Shimmie-Status: OK - Settings Shown");
	$title = html_escape($user->name)."'s settings";
	$blocks = get_blocks_html("user");
	require_once "templates/user.php";
}
else if($_GET['action'] == "pass") {
	$old1 = md5(strtolower($user->name) . $_POST['old1']);
	$new1 = md5(strtolower($user->name) . $_POST['new1']);
	$new2 = md5(strtolower($user->name) . $_POST['new2']);
	if($old1 == $user->pass) {
		if($new1 == $new2) {
			$query = "UPDATE shm_users SET pass='$new1' WHERE pass='$old1' AND id='$user->id'";
			sql_query($query);
			
			$title = "Password Changed";
			$message = "<a href='user.php'>Back</a>";
			require_once "templates/generic.php";
		}
		else {
			$title = "Password Error";
			$message = "New passwords don't match";
			require_once "templates/generic.php";
		}
	}
	else {
		$title = "Password Error";
		$message = "Wrong original password";
		require_once "templates/generic.php";
	}
}
else if($_GET['action'] == "logout") {
	setcookie("shm_hash", "");
	header("X-Shimmie-Status: OK - Logged Out");
	header("Location: index.php");
	echo "<a href='index.php'>To index</a>";
}

?>
