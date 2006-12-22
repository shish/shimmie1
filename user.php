<?php
/*
 * user.php (c) Shish 2005, 2006
 *
 * Things relating to user self-management
 */

require_once "header.php";

/*
 * Take care of the whole login process
 * (cut & paste from hader.php -- this is the only
 * place it was used anyway...)
 */
function up_login() {
	global $base_url, $user;

	$name = $_POST['user'];
	$hash = md5( strtolower($_POST['user']) . $_POST['pass'] );

	if($user->load_from_name_hash($name, $hash)) {
		setcookie("shm_user", $name);
		setcookie("shm_hash", $hash);

		header("X-Shimmie-Status: OK - Logged In");
		header("Location: user.php");
		$title = "Login OK";
		$message = "<a href='user.php'>Continue</a>";
		require_once "templates/generic.php";
	}
	else if($_POST['create']) {
		$s_name = sql_escape($name);
		if(sql_num_rows(sql_query("SELECT * FROM shm_users WHERE name='$s_name'")) == 0) {
			sql_query("INSERT INTO shm_users(name, pass, joindate) VALUES('$s_name', '$hash', now())");
			
			header("X-Shimmie-Status: OK");
			$title = "Account Created";
			$message = "Now you can log in with that name and password";
			require_once "templates/generic.php";
		}
		else {
			header("X-Shimmie-Status: Error - Name Taken");
			$title = "Name Taken";
			$message = "Somebody is already using that username";
			require_once "templates/generic.php";
		}
	}
	else {
		header("X-Shimmie-Status: Error - Bad Password");
		$title = "Login Failed";
		$message = "<a href='index.php'>Back to index</a>";
		require_once "templates/generic.php";
	}
}


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
	$days_old = $user->stat_days_old();
	$image_count = $user->stat_count_images();
	$comment_count = $user->stat_count_comments();
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
