<?php
/*
 * user.php (c) Shish 2005, 2006
 *
 * Things relating to user self-management
 */

require_once "header.php";

if($_GET['action'] == "login") {
	up_login();
}
else if(is_null($cuser) || is_null($cpass) || !up_passCheck($cuser, $cpass)) {
	$title = "Not logged in";
	$message = "Log in using the box in the nav bar~";
	require_once "templates/generic.php";
}
else {
	if(is_null($_GET['action'])) {
		$title = "$user->name's settings ";
		require_once "templates/user.php";
	}
	else if($_GET['action'] == "pass") {
		// md5(concat(lower('${_POST[admin_name]}'), '${_POST[admin_pass]}'))
		$old1 = md5($_POST['old1']);
		$new1 = md5($_POST['new1']);
		$new2 = md5($_POST['new2']);
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
		session_destroy();
		header("Location: index.php");
		echo "<a href='index.php'>To index</a>";
	}
}
?>
