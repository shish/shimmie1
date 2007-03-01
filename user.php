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
	$pass = $_POST['pass'];
	$addr = $_SERVER['REMOTE_ADDR'];
	$hash = md5( strtolower($name) . $pass );

	if($user->load_from_name_hash($name, $hash)) {
		setcookie("shm_user", $name, time()+60*60*24*365);
		setcookie("shm_session", md5($hash.$addr), time()+60*60*24*get_config('login_memory'));

		header("Location: user.php");
		$title = "Login OK";
		$body["Login OK"] = "<a href='user.php'>Continue</a>";
		require_once get_theme_template();
	}
	else if($_POST['create']) {
		$result = $db->Execute("SELECT * FROM users WHERE name=?", Array($name));
		if($result->RecordCount() == 0) {
			$db->Execute("INSERT INTO users(name, pass, joindate) VALUES(?, ?, now())", Array($name, $hash));

			$title = "Account Created";
			$body["Account Created"] = "Now you can log in with that name and password";
			$blocks = get_blocks_html("login_error");
			require_once get_theme_template();
		}
		else {
			$title = "Name Taken";
			$body["Name Taken"] = "Somebody is already using that username";
			$blocks = get_blocks_html("login_error");
			require_once get_theme_template();
		}
	}
	else {
		$title = "Login Failed";
		$body["Login Failed"] = "<a href='index.php'>Back to index</a>";
		$blocks = get_blocks_html("login_error");
		require_once get_theme_template();
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
	if(is_null($_GET['user_id'])) {
		$duser = $user;
		$title = html_escape($duser->name)."'s settings";
	}
	else {
		$duser = new User();
		$duser->load_from_id($_GET['user_id']);
		$title = html_escape($duser->name)."'s stats";
	}
	$blocks = get_blocks_html("user");
	$days_old = $duser->stat_days_old();
	$image_count = $duser->stat_count_images();
	$comment_count = $duser->stat_count_comments();
	$join_date = $duser->stat_join_date();
	$image_rate = (int)($image_count / $days_old);
	$comment_rate = (int)($comment_count / $days_old);
	if(is_null($_GET['user_id'])) {
		$body["User Settings"] = "Things will go here as soon as there's something to set...";
	}
	$body["User Stats"] = "
		<br>Join date: $join_date
		<br>Images uploaded: $image_count ($image_rate / day)
		<br>Comments made: $comment_count ($comment_rate / day)
	";
	require_once get_theme_template();
}
else if($_GET['action'] == "pass") {
	$old1 = md5(strtolower($user->name) . $_POST['old1']);
	$new1 = md5(strtolower($user->name) . $_POST['new1']);
	$new2 = md5(strtolower($user->name) . $_POST['new2']);
	if($old1 == $user->pass) {
		if($new1 == $new2) {
			$db->Execute("UPDATE users SET pass=? WHERE pass=? AND id=?", Array($new1, $old1, $user->id));
			
			$title = "Password Changed";
			$body["Password Changed"] = "<a href='user.php'>Back</a>";
			require_once get_theme_template();
		}
		else {
			$title = "Password Error";
			$body["Error"] = "New passwords don't match";
			require_once get_theme_template();
		}
	}
	else {
		$title = "Password Error";
		$body["Error"] = "Wrong original password";
		require_once get_theme_template();
	}
}
else if($_GET['action'] == "logout") {
	setcookie("shm_session", "");
	header("Location: index.php");
	echo "<a href='index.php'>To index</a>";
}

?>
