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
	global $base_url, $user, $db;

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
		$result = $db->Execute("SELECT * FROM users WHERE name=?", Array($name));
		if($result->RecordCount() == 0) {
			$db->Execute("INSERT INTO users(name, pass, joindate) VALUES(?, ?, now())", Array($name, $hash));

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
	$heading = "User Settings";
	$days_old = $user->stat_days_old();
	$image_count = $user->stat_count_images();
	$comment_count = $user->stat_count_comments();
	$image_rate = (int)($image_count / $days_old);
	$comment_rate = (int)($comment_count / $days_old);
	$message = "
		Things will go here as soon as there's something to set...
		<h3>User Stats</h3>
		<br>Images uploaded: $image_count ($image_rate / day)
		<br>Comments made: $comment_count ($comment_rate / day)
	";
	require_once "templates/generic.php";
}
else if($_GET['action'] == "pass") {
	$old1 = md5(strtolower($user->name) . $_POST['old1']);
	$new1 = md5(strtolower($user->name) . $_POST['new1']);
	$new2 = md5(strtolower($user->name) . $_POST['new2']);
	if($old1 == $user->pass) {
		if($new1 == $new2) {
			$db->Execute("UPDATE users SET pass=? WHERE pass=? AND id=?", Array($new1, $old1, $user->id));
			
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
