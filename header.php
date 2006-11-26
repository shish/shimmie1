<?php
/*
 * header.php (c) Shish 2005, 2006
 *
 * Connect to database, create common sidebar blocks,
 * HTML output functions
 */


$version = "Shimmie 0.7.0";


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
	'title' => $version,
	'dir_images' => 'images',
	'dir_thumbs' => 'thumbs',
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
	'comment_window' => 5,
	'comment_limit' => 3,
	'recent_count' => 5,
	'popular_count' => 15,
	'login_enabled' => true,
	'image_link' => 'get.php/$id - $tags.$ext',
	'image_slink' => 'images/$id.$ext',
);


/*
 * Now we have a database to connect to, and functions to use it, the
 * first thing we want to do is load the settings. They're stored as
 * simple name:value pairs, as given by the admin control panel (which
 * is why ""=false and "on"=true -- that's how checkbox data is sent)
 */
function get_config() {
	global $config_defaults;

	$config_result = sql_query("SELECT * FROM shm_config");
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

	return $config;
}

$config = get_config();


/*
 * check for bans
 */
function get_ban_info($type, $value) {
	return sql_fetch_row(sql_query("SELECT * FROM shm_bans WHERE type='$type' AND value='$value'"));
}

function print_ip_ban($ip, $date, $reason) {
	header("X-Shimmie-Status: Error - IP Banned");
	$title = "IP Banned";
	$message = "IP $ip was banned at $date for $reason";
	require_once "templates/generic.php";
}

if($row = get_ban_info('ip', $_SERVER['REMOTE_ADDR'])) {
	print_ip_ban($row['value'], $row['date'], $row['reason']);
	exit;
}


/*
 * Make sure a web site viewer has permission to view a page.
 * If they don't, exit with an error message.
 */
function admin_or_die() {
	global $user;
	if($user->isAdmin() != true) {
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
	if($user->isUser() != true) {
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
function delete_tags($image_id) {
	sql_query("DELETE FROM shm_tags WHERE image_id=$image_id");
}
function delete_comments($image_id) {
	sql_query("DELETE FROM shm_comments WHERE image_id=$image_id");
}

function add_tags($image_id, $tag_list) {
	$tags = Array();
	
	if(is_array($tag_list)) {
		$tags = $tag_list;
	}
	else if(is_string($tag_list)) {
		$tags = explode(" ", strtolower($tag_list));
	}
	
	if(count($tags) == 0) {
		$tags = Array("tagme");
	}

	// insert each new tag
	foreach($tags as $tag) {
		sql_query("INSERT INTO shm_tags(image_id, tag) VALUES($image_id, '$tag')");
	}
}

function updateTags($image_id, $tag_list) {
	delete_tags($image_id);
	add_tags($image_id, $tag_list);
}

function delete_image($image_id) {
	delete_tags($image_id);
	delete_comments($image_id);

	$row = sql_fetch_row(sql_query("SELECT ext FROM shm_images WHERE id=$image_id"));
	$iname = $config['dir_images']."/$image_id.".$row['ext'];
	$tname = $config['dir_thumbs']."/$image_id.jpg";
	if(file_exists($iname)) unlink($iname);
	if(file_exists($tname)) unlink($tname);

	sql_query("DELETE FROM shm_images WHERE id=$image_id");
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
		session_start();
		setcookie("shm_login", "true", time()+60*60*24*30);
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
 * get blocks for a page
 */
class block {
	function get_html($pageType) {}
	function get_priority() {return 999;}
	function run($action) {}
}

function block_filename_to_name($fname) {
	$fname = str_replace("blocks/", "", $fname);
	$fname = str_replace(".php", "", $fname);
	return $fname;
}

function get_blocks() {
	$blockFiles = glob("blocks/*.php");
	$blocks = Array();

	foreach($blockFiles as $fname) {
		$blockname = block_filename_to_name($fname);
		require_once $fname;

		$block = new $blockname();
		
		$n = $block->get_priority();
		while(isset($blocks[$n])) {$n++;}
		$blocks[$n] = $block;
	}

	return $blocks;
}

function block_array_to_html($blocks, $pageType) {
	$allBlocks = "";
	ksort($blocks);
	foreach($blocks as $block) {
		if($block instanceof block)
			$allBlocks .= $block->get_html($pageType);
	}
	return $allBlocks;
}

function get_blocks_html($pageType) {
	return block_array_to_html(get_blocks(), $pageType);
}


/*
 * Count how many times a tag is used
 * FIXME: count where tag = tag1 or tag2 or tag3
 * store results in count['tag']
 */
function countImagesForTag($tag) {
	$tag_query = "SELECT count(*) as count FROM shm_tags WHERE tag='$tag'";
	$row = sql_fetch_row(sql_query($tag_query));
	return $row['count'];
}


/*
 * A PHP-friendly view of a row in the users table
 */
class User {
	var $id = null;
	var $name = 'Anonymous';
	var $uconfig = Array();

	function User($cname) {
		global $config;
		$this->id = $config['anon_id'];

		if(is_null($cname)) return;

		$result = sql_query("SELECT * FROM shm_users WHERE name LIKE '$cname'");
		if(sql_num_rows($result) == 1) {
			$row = sql_fetch_row($result);
			$this->id = $row['id'];
			$this->name = $row['name'];

			$result = sql_query("SELECT * FROM shm_user_configs WHERE owner_id={$this->id}");
			while($row = sql_fetch_row($result)) {
				$this->uconfig[$row['name']] = $row['value'];
			}
		}
	}

	function isAdmin() {
		return ($this->uconfig['isadmin'] == 'true');
	}
	function isUser() {
		global $config;
		return ($this->id != $config['anon_id']);
	}
	function isAnonymous() {
		global $config;
		return ($this->id == $config['anon_id']);
	}
}


/*
 * A PHP-friendly view of a row in the images table
 */
class Image {
	var $id = null;
	var $filename = null;
	var $hash = null;
	var $ext = null;

	var $owner = null;
	var $owner_id = null;

	var $link = null;
	var $slink = null;

	var $tag_array = null;
	var $tags = null;

	function Image($id) {
		global $config;
		if(is_null((int)$id)) return;

		$img_query = <<<EOD
			SELECT shm_images.*, shm_users.name
			FROM shm_images
			LEFT JOIN shm_users ON shm_images.owner_id=shm_users.id
			WHERE shm_images.id=$id
EOD;
		$img_result = sql_query($img_query);
		if(sql_num_rows($img_result) == 1) {
			$img_info = sql_fetch_row($img_result);
			$this->id = $img_info["id"];
			$this->owner = htmlentities($img_info['name']);
			$this->filename = $img_info['filename'];
			$this->hash = $img_info['hash'];
			$this->ext = $img_info['ext'];

			$tag_query = "SELECT * FROM shm_tags WHERE image_id={$this->id}";
			$tag_result = sql_query($tag_query);
			$this->tag_array = Array();
			while($row = sql_fetch_row($tag_result)) {
				$this->tag_array[] = htmlentities($row['tag']);
			}
			$this->tags = implode(" ", $this->tag_array);
			
			$this->link = $this->parse_link_template($config['image_link'], $this);
			$this->slink = $this->parse_link_template($config['image_slink'], $this);
		}
		else {
			header("X-Shimmie-Status: Error - No Such Image");
			$title = "No Image $id";
			$body = "The image has either been deleted, or there aren't that many images in the database";
			require_once "templates/generic.php";
			exit;
		}
	}
	
	function parse_link_template($tmpl, $img) {
		$tmpl = str_replace('$id',   $img->id,   $tmpl);
		$tmpl = str_replace('$hash', $img->hash, $tmpl);
		$tmpl = str_replace('$tags', $img->tags, $tmpl);
		$tmpl = str_replace('$ext',  $img->ext,  $tmpl);
		return $tmpl;
	}
}


/*
 * With all the settings and stuff ready, see if we have a user logged in
 */
if($_COOKIE['shm_login']) {
	session_start();
	if(up_passCheck(sql_escape($_SESSION['shm_user']), $_SESSION['shm_pass'])) {
		$cuser = $_SESSION['shm_user'];
		$cpass = $_SESSION['shm_pass'];
	}
	else {
		$cuser = null;
		$cpass = null;
	}
}
$user = new User($cuser);
?>
