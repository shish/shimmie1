<?php
/*
 * header.php (c) Shish 2005, 2006
 *
 * Connect to database, create common sidebar blocks,
 * HTML output functions
 */


$version = "Shimmie 0.8.4";
$db_version = "0.7.5";

// only images are good for caching, and
// they have cache turned on explicitly
session_cache_limiter('nocache');


/*
 * If we're ready to run, run. If not, show installer.
 */
if(is_readable("config.php")) {require_once "config.php";}
else {
	header("Location: install.php");
	exit;
}

require_once "lib/libsio.php";
require_once "lib/adodb/adodb.inc.php";
if(is_null($config['database_dsn'])) {
//	echo "WARNING: Shimmie has changed from using ";
	$config['database_dsn'] = 
		$config['database_api']."://".
		$config['mysql_user'].":".
		$config['mysql_pass']."@".
		$config['mysql_host']."/".
		$config['mysql_db'];

}

function CountExecs($db, $sql, $inputarray) {
	global $_execs;
	if (!is_array($inputarray)) $_execs++;
	else if (is_array(reset($inputarray))) $_execs += sizeof($inputarray);
	else $_execs++;
	$null = null; return $null;
}

$db = NewADOConnection($config['database_dsn']);
$db->SetFetchMode(ADODB_FETCH_ASSOC);
$db->fnExecute = 'CountExecs';


/*
 * Hopefully sensible defaults
 *
 * XXX: never set login_enabled to false by default -- it
 * stops the admin logging in and changing things after
 * the installation is done!
 */
$config_defaults = Array(
	'title' => $version,
	'admin_contact' => '',
	'db_version' => 'pre-0.7.5', // this should be managed by upgrade.php
	'base_href' => '',
	'theme' => 'default',
	'debug_enabled' => true,
	'anon_id' => 0,
	'dir_images' => 'images',
	'dir_thumbs' => 'thumbs',
	'index_width' => 3,
	'index_height' => 4,
	'index_invert' => true,
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
	'login_memory' => 7,
	'image_link' => 'get.php/$id - $tags.$ext',
	'image_slink' => '$base/images/$id.$ext',
	'image_vlink' => 'view.php?image_id=$id',
);


/*
 * Now we have a database to connect to, and functions to use it, the
 * first thing we want to do is load the settings. They're stored as
 * simple name:value pairs, as given by the admin control panel (which
 * is why ""=false and "on"=true -- that's how checkbox data is sent)
 */

$config = $db->GetAssoc("SELECT name, value FROM config");

function get_config($name) {
	global $config, $config_defaults;

	$value = isset($config[$name]) ? $config[$name] : $config_defaults[$name];

	if(is_numeric($value)) {
		return (int)$value;
	}
	else if(is_null($value)) {
		return false;
	}
	else {
		return $value;
	}
}

function get_theme_template() {
	$theme = get_config('theme');
	if(is_readable("themes/$theme/template.php")) {
		return "themes/$theme/template.php";
	}
	else {
		return "themes/default/template.php";
	}
}

if(get_config('db_version') != $db_version) {
	require_once "upgrade.php";
}


/*
 * check for bans
 */
function get_ban_info($type, $value) {
	global $db;
	$row = $db->Execute("SELECT * FROM bans WHERE type=? AND value=?", Array($type, $value));
	return $row->fields;
}

function print_ip_ban($ip, $date, $reason) {
	$s_ip = html_escape($ip);
	$s_date = html_escape($date);
	$s_reason = html_escape($reason);

	$title = "IP Banned";
	$body["IP Banned"] = "IP $s_ip was banned at $s_date for $s_reason";
	require_once get_theme_template();
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
		$title = "Not Admin";
		$body["Not Admin"] = "You need to have administrator rights to view this page";
		$blocks = get_blocks_html("login_error");
		require_once get_theme_template();
		exit;
	}
	return true;
}
function user_or_die() {
	global $user;
	if($user->isUser() != true) {
		$title = "Not Logged In";
		$body["Not Logged In"] = "You need to be logged in";
		$blocks = get_blocks_html("login_error");
		require_once get_theme_template();
		exit;
	}
	return true;
}
function defined_or_die($var, $name=null) {
	if(is_null($var)) {
		$title = "Variable Not Set";
		if(is_null($name)) {
			$body["Error"] = "variable not specified";
		}
		else {
			$s_name = html_escape($name);
			$body["Error"] = "not set: '$s_name'";
		}
		require_once get_theme_template();
		exit;
	}
	return $var;
}


/*
 * some sanitisers
 */
function int_escape($var) {
	return (int)$var;
}
function html_escape($var) {
	return htmlentities($var);
}
function glob_to_sql($tag) {
	global $db;
	$tag = str_replace("*", "%", $tag);
	$tag = str_replace("?", "_", $tag);
	$tag = $db->Quote($tag);
	return $tag;
}


/*
 * A couple of pages want to be able to update tags for an image,
 * so it got put into it's own function.
 */
function delete_tags($image_id) {
	global $db;
	$db->Execute("DELETE FROM tags WHERE image_id=?", Array($image_id));
}
function delete_comments($image_id) {
	global $db;
	$db->Execute("DELETE FROM comments WHERE image_id=?", Array($image_id));
}
function delete_comment($comment_id) {
	global $db;
	$db->Execute("DELETE FROM comments WHERE id=?", Array($comment_id));
}

function add_tags($image_id, $tag_list) {
	global $db;
	
	$tags = Array();
	
	if(is_array($tag_list)) {
		$tags = $tag_list;
	}
	else if(is_string($tag_list)) {
		$tags = explode(" ", strtolower($tag_list));
	}
	else {
		$tags = array();
	}
	
	if(count($tags) == 0) {
		$tags = Array("tagme");
	}

	$tags = array_unique($tags); // remove any duplicate tags

	// insert each new tag
	foreach($tags as $tag) {
		$db->Execute("INSERT INTO tags(image_id, tag) VALUES(?, ?)", Array($image_id, $tag));
	}
}

function update_tags($image_id, $tag_list) {
	delete_tags($image_id);
	add_tags($image_id, $tag_list);
}

function delete_image($image_id) {
	global $db;

	delete_tags($image_id);
	delete_comments($image_id);
	
	$ext = $db->GetOne("SELECT ext FROM images WHERE id=?", Array($image_id));
	if($ext) {
		$iname = get_config('dir_images')."/$image_id.$ext";
		$tname = get_config('dir_thumbs')."/$image_id.jpg";
		if(file_exists($iname)) unlink($iname);
		if(file_exists($tname)) unlink($tname);

		$db->Execute("DELETE FROM images WHERE id=?", Array($image_id));
	}
}

function mime_to_ext($mime) {
	$ext = null;
	switch($mime) {
		case "image/jpeg": $ext = "jpg"; break;
		case "image/png":  $ext = "png"; break;
		case "image/gif":  $ext = "gif"; break;
	}
	return $ext;
}

/*
 * check if an image with the given hash already exists
 */
function is_dupe($hash) {
	global $db;
	$result = $db->Execute("SELECT * FROM images WHERE hash=?", Array($hash));
	return $result->fields;
}

/*
 * get a thumbnail from a file
 */
function get_thumb($tmpname) {
	$image = imagecreatefromstring(read_file($tmpname));
		
	$width = imagesx($image);
	$height = imagesy($image);
	$max_width  = get_config('thumb_w');
	$max_height = get_config('thumb_h');
	$xscale = ($max_height / $height);
	$yscale = ($max_width / $width);
	$scale = ($xscale < $yscale) ? $xscale : $yscale;
	
	if($scale >= 1) {
		$thumb = $image;
	}
	else {
		$thumb = imagecreatetruecolor($width*$scale, $height*$scale);
		imagecopyresampled(
			$thumb, $image, 0, 0, 0, 0,
			$width*$scale, $height*$scale, $width, $height
		);
	}

	return $thumb;
}

/*
 * add the file "tmpname" to the database, with the original
 * filename and tags noted
 */
function add_image($tmpname, $filename, $tags) {
	global $user, $db;
	
	$dir_images = get_config('dir_images');
	$dir_thumbs = get_config('dir_thumbs');

	$imgsize = getimagesize($tmpname);
	$h_filename = html_escape($filename);
	
	if($imgsize) {
		$ext = mime_to_ext($imgsize['mime']);
		if(is_null($ext)) {
			print "<p>Unrecognised file type for '$h_filename' (not jpg/gif/png)";
			return false;
		}

		$hash = md5_file($tmpname);

		/*
		 * Check for an existing image
		 */
		if($row = is_dupe($hash)) {
			$iid = $row['id'];
			$err .= "<p>Upload of '$h_filename' failed:";
			$err .= "<br>There's already an image with hash '$hash' (<a href='view.php?image_id=$iid'>view</a>)";
			return false;
		}

		$thumb = get_thumb($tmpname);

		// actually insert the info
		$db->Execute("INSERT INTO images(owner_id, owner_ip, filename, hash, ext) ".
		             "VALUES (?, ?, ?, ?, ?)",
					 Array($user->id, $user->ip, $filename, $hash, $ext));
		$id = $db->Insert_ID();

		/*
		 * If no errors: move the file from the temporary upload
		 * area to the main file store, create a thumbnail, and
		 * insert the image info into the database
		 */
		if(!move($tmpname, "$dir_images/$id.$ext")) {
			print "<p>The image couldn't be moved from the temporary area to the
			         main data store -- is the web server allowed to write to '$dir_images'?";
			$db->Execute("DELETE FROM images WHERE id=?", Array($id));
			return false;
		}
		chmod("$dir_images/$id.$ext", 0644);
		if(!imagejpeg($thumb, "$dir_thumbs/$id.jpg", get_config('thumb_q'))) {
			print "<p>The image thumbnail couldn't be generated -- is the web
			         server allowed to write to '$dir_thumbs'?";
			$db->Execute("DELETE FROM images WHERE id=?", Array($id));
			return false;
		}
		
		add_tags($id, $tags);
		return true;
	}
	else {
		print "<p>$h_filename upload failed";
		return false;
	}
}

/*
 * Add all the images in a folder, recursively. Any folders
 * below the base one will be used as tags.
 */
function add_dir($base, $subdir="") {
	$list = "";

	if(!is_dir($base)) return "$base is not a directory";

	$dir = opendir("$base/$subdir");
	while($filename = readdir($dir)) {
		$fullpath = "$base/$subdir/$filename";
		
		if(is_dir($fullpath)) {
			if($filename[0] != ".") {
				$list .= add_dir($base, "$subdir/$filename");
			}
		}
		else {
			$tmpfile = write_temp_file(read_file($fullpath));
			$list .= html_escape("$subdir/$filename (".str_replace("/", ",", $subdir).")...");
			if(add_image($tmpfile, $filename, str_replace("/", " ", $subdir))) {
				$list .= "ok\n";
			}
			else {
				$list .= "failed\n";
			}
			// add_image moves $tmpfile to $images/$id.$ext
			// unlink($tmpfile);
		}
	}
	closedir($dir);

	return $list;
}

function gen_textarea($text) {
	$h_text = html_escape($text);
	return "<p><textarea cols='80' rows='10'>$h_text</textarea>";
}

/*
 * get blocks for a page
 */
class block {
	function get_html($pageType) {}
	function get_priority() {return 999;}
	function get_xmlrpc_funclist() {return array();}
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

	ksort($blocks);

	return $blocks;
}

function block_array_to_html($blocks, $pageType) {
	$allBlocks = Array();
	foreach($blocks as $block) {
		$allBlocks[$block->get_title()] = $block->get_html($pageType);
	}
	return $allBlocks;
}

function get_blocks_html($pageType) {
	return block_array_to_html(get_blocks(), $pageType);
}


/*
 * A PHP-friendly view of a row in the users table
 */
class User {
	var $id = null;
	var $name = 'Anonymous';
	var $uconfig = Array();
	var $ip = null;

	function User() {
		$this->id = get_config('anon_id');
		$this->ip = $_SERVER['REMOTE_ADDR'];
	}

	function load_from_row($row) {
		global $db;
		
		$this->id = $row['id'];
		$this->name = $row['name'];

		$row = $db->Execute("SELECT * FROM user_configs WHERE owner_id=?", Array($this->id));
		while(!$row->EOF) {
			$this->uconfig[$row->fields['name']] = $row->fields['value'];
			$row->MoveNext();
		}

		return true;
	}
	function load_from_query($query) {
		global $db;
		
		$result = $db->Execute($query);
		if(!$result->EOF) {
			return $this->load_from_row($result->fields);
		}
		else {
			return false;
		}
	}
	function load_from_id($id) {
		$i_id = int_escape($id);
		return $this->load_from_query("SELECT * FROM users WHERE id=$i_id");
	}
	function load_from_name($name) {
		global $db;
		$s_name = $db->qstr($name);
		return $this->load_from_query("SELECT * FROM users WHERE name LIKE $s_name");
	}
	function load_from_name_hash($name, $hash) {
		global $db;
		$s_name = $db->qstr($name);
		$s_hash = $db->qstr($hash);
		return $this->load_from_query("SELECT * FROM users WHERE name LIKE $s_name AND pass = $s_hash");
	}
	function load_from_name_session($name, $session) {
		global $db;
		$s_name = $db->qstr($name);
		$s_session = $db->qstr($session);
		$s_addr = $db->qstr($_SERVER['REMOTE_ADDR']);
		return $this->load_from_query("
			SELECT *
			FROM users
			WHERE name LIKE $s_name
			AND md5(concat(pass, $s_addr)) = $s_session");
	}
	function load_from_name_pass($name, $pass) {
		return $this->load_from_name_hash($name, md5(strtolower($name).$pass));
	}

	function isAdmin() {
		return ($this->uconfig['isadmin'] == 'true');
	}
	function isUser() {
		return ($this->id != get_config('anon_id'));
	}
	function isAnonymous() {
		global $config;
		return ($this->id == get_config('anon_id'));
	}

	function stat_count_images() {
		global $db;
		$row = $db->Execute("SELECT count(*) AS count FROM images WHERE owner_id=?", Array($this->id));
		return $row->fields['count'];
	}
	function stat_count_comments() {
		global $db;
		$row = $db->Execute("SELECT count(*) AS count FROM comments WHERE owner_id=?", Array($this->id));
		return $row->fields['count'];
	}
	function stat_days_old() {
		global $db;
		$row = $db->Execute("SELECT (now()-joindate)/(1000*60*60*24) AS days_old FROM users WHERE id=?", Array($this->id));
		return $row->fields['days_old'];
	}
	function stat_join_date() {
		global $db;
		$row = $db->Execute("SELECT joindate FROM users WHERE id=?", Array($this->id));
		return $row->fields['joindate'];
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
		global $db;
		
		if(is_null($id)) return;

		$s_id = int_escape($id);

		$row = $db->Execute("
			SELECT images.*, users.name
			FROM images
			LEFT JOIN users ON images.owner_id=users.id
			WHERE images.id=?
			", Array($id));
		if($row->RecordCount() == 1) {
			$img_info = $row->fields;
			$this->id = $img_info["id"];
			$this->owner = htmlentities($img_info['name']);
			$this->filename = $img_info['filename'];
			$this->hash = $img_info['hash'];
			$this->ext = $img_info['ext'];

			$this->tag_array = Array();
			$row = $db->Execute("SELECT * FROM tags WHERE image_id=?", Array($id));
			while(!$row->EOF) {
				$this->tag_array[] = $row->fields['tag'];
				$row->MoveNext();
			}
			$this->tags = implode(" ", $this->tag_array);
			
			$this->link = $this->parse_link_template(get_config('image_link'), $this);
			$this->slink = $this->parse_link_template(get_config('image_slink'), $this);
			$this->vlink = $this->parse_link_template(get_config('image_vlink'), $this);
			$this->tlink = get_config('dir_thumbs').'/'.($this->id).'.jpg';
		}
		else {
			$title = "No Image $id";
			$body = "The image has either been deleted, or there aren't that many images in the database";
			require_once get_theme_template();
			exit;
		}
	}

	function parse_link_template($tmpl, $img) {
		$safe_tags = preg_replace("/[^a-zA-Z0-9_\- ]/", "", $img->tags);
		$base_href = get_config('base_href');
		$base_fname = strstr($img->filename, '.') ?
		        substr($img->filename, 0, strrpos($img->filename,'.')) : 
		        $img->filename;
		$tmpl = str_replace('$id',   $img->id,   $tmpl);
		$tmpl = str_replace('$hash', $img->hash, $tmpl);
		$tmpl = str_replace('$tags', $safe_tags, $tmpl);
		$tmpl = str_replace('$base', $base_href, $tmpl);
		$tmpl = str_replace('$ext',  $img->ext,  $tmpl);
		$tmpl = str_replace('$filename', $base_fname, $tmpl);
		return $tmpl;
	}
}


/*
 * With all the settings and stuff ready, see if we have a user logged in
 */
$user = new User();

if($_COOKIE['shm_user'] && $_COOKIE['shm_session']) {
	$user->load_from_name_session($_COOKIE['shm_user'], $_COOKIE['shm_session']);
}
?>
