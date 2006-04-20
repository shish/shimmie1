<?php
/*
 * header.php (c) Shish 2005, 2006
 *
 * Connect to database, create common sidebar blocks,
 * HTML output functions
 */


$version = "Shimmie 0.5.0";


/*
 * If we're ready to run, run. If not, show installer.
 */
if(is_readable("config.php")) {require_once "config.php";}
else {require_once "install.php"; exit;}


require_once "libabsql.php";


/*
 * Hopefully sensible defaults
 *
 * XXX: never set login_enabled to false by default -- it
 * stops the admin logging in and changing things after
 * the installation is done!
 */
$config_defaults = Array(
	'dir_images' => './images',
	'dir_thumbs' => './thumbs',
	'index_images' => 12,
	'thumb_w' => 192,
	'thumb_h' => 192,
	'thumb_q' => 75,
	'view_scale' => false,
	'tags_default' => 'map',
	'tags_min' => '2',
	'upload_count' => 3,
	'upload_size' => 256*1024,
	'upload_anon' => true,
	'comment_anon' => true,
	'recent_count' => 5,
	'popular_count' => 15,
	'login_enabled' => true
);


/*
 * Now we have a database to connect to, and functions to use it, the
 * first thing we want to do is load the settings. They're stored as
 * simple name:value pairs, as given by the admin control panel (which
 * is why ""=false and "on"=true -- that's how checkbox data is sent)
 */
$config_result = sql_query("SELECT * FROM config");
while($config_row = sql_fetch_row($config_result)) {
	$config[$config_row['name']] = $config_row['value'];
}

$config_default_keys = array_keys($config_defaults);
foreach($config_default_keys as $cname) {
	if(is_null($config[$cname])) {
		$config[$cname] = $config_defaults[$cname];
	}
	else if($config[$cname] == "") {
		$config[$cname] = false;
	}
	else if($config[$cname] == "on") {
		$config[$cname] = true;
	}
}


/*
 * Make sure a web site viewer has permission to view a page.
 * If they don't, exit with an error message.
 */
function admin_or_die() {
	global $user;
	if($user->isAdmin != true) {
		header("X-Shimmie-Status: Error - Not Admin");
		$title = "Not Admin";
		$message = "You need to have administrator rights to view this page";
		require_once "templates/generic.php";
		exit;
	}
	return true;
}
function user_or_die() {
	global $user;
	if($user->id == 0) {
		header("X-Shimmie-Status: Error - Not Logged In");
		$title = "Not Logged In";
		$message = "You need to be logged in";
		require_once "templates/generic.php";
		exit;
	}
	return true;
}
function defined_or_die($var, $name=null) {
	if(is_null($var)) {
		header("X-Shimmie-Status: Error - Variable Not Set");
		$title = "Variable Not Set";
		if(is_null($name)) {
			$message = "variable not specified";
		}
		else {
			$message = "not set: '$name'";
		}
		require_once "templates/generic.php";
		exit;
	}
	return $var;
}


/*
 * A couple of pages want to be able to update tags for an image,
 * so it got put into it's own function.
 */
function updateTags($image_id, $tagList) {
	$tags = explode(" ", $tagList);

	// clear old tags
	sql_query("DELETE FROM shm_tags WHERE image_id=$image_id");

	// insert each new tag
	foreach($tags as $tag) {
		$ltag = strtolower($tag);
		sql_query("INSERT INTO shm_tags(image_id, tag) VALUES($image_id, '$ltag')");
	}
}


/*
 * Check that a user has the right password
 */
function up_passCheck($name, $hash) {
	$pc_query = "SELECT * FROM shm_users WHERE name LIKE '$name' AND pass = '$hash'";
	return (sql_num_rows(sql_query($pc_query)) == 1);
}

/*
 * Take care of the whole login process
 */
function up_login() {
	global $base_url;
	$name = sql_escape($_POST['user']);
	$hash = md5( strtolower($_POST['user']) . $_POST['pass'] );
	if(up_passCheck($name, $hash)) {
		$_SESSION["shm_user"] = $name;
		$_SESSION["shm_pass"] = $hash;

		header("X-Shimmie-Status: OK - Logged In");
		header("Location: user.php");
		$title = "Login OK";
		$message = "<a href='user.php'>Continue</a>";
		require_once "templates/generic.php";
	}
	else if($_POST['create']) {
		if(sql_num_rows(sql_query("SELECT * FROM shm_users WHERE name='$name'")) == 0) {
			sql_query("INSERT INTO shm_users(name, pass) VALUES('$name', '$hash')");
			
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


/*
 * A PHP-friendly view of a row in the users table
 */
class User {
	var $id = 0;
	var $name = "Anonymous";
	var $isAdmin = false;

	function User($cname) {
		if(is_null($cname)) return;

		$result = sql_query("SELECT * FROM shm_users WHERE name LIKE '$cname'");
		if(sql_num_rows($result) == 1) {
			$row = sql_fetch_row($result);
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->isAdmin = ($row['permissions'] > 0);
		}
	}
}


/*
 * With all the settings and stuff ready, see if we have a user logged in
 */
session_start();
if(up_passCheck(sql_escape($_SESSION['shm_user']), $_SESSION['shm_pass'])) {
	$cuser = $_SESSION['shm_user'];
	$cpass = $_SESSION['shm_pass'];
}
else {
	$cuser = null;
	$cpass = null;
}
$user = new User($cuser);


/*
 * Load each block for the sidebar
 */
if(is_null($blockmode)) {
	$blockmode = "block";
	$blocks = glob("blocks/*.php");
	foreach($blocks as $block) {
		require_once $block;
	}
}
?>
