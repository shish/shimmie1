<?php
/*
 * setup.php (c) Shish 2005, 2006
 *
 * Edit the "config" table
 */

require_once "header.php";


admin_or_die();


/*
 * If we're setting the options, that's the first thing we want to
 * do, so they'll be ready by the time the rest of the page is shown
 */
if($_POST["action"] == "set") {
	sql_query("DELETE FROM shm_config");
	
	$config_keys = array_keys($config_defaults);
	foreach($config_keys as $cname) {
		$cval = $_POST[$cname];
		$s_cname = sql_escape($cname);
		$s_cval = sql_escape($cval);
		sql_query("INSERT INTO shm_config(name, value) VALUES('$s_cname', '$s_cval')");
		$config[$cname] = $cval; // update here so the display below is correct
	}
}


/*
 * Generate the HTML form
 */
$configOptions1 .= makeRow("Global");
$configOptions1 .= makeOptText("Title", "title", strlen($config["title"]) > 0);

$configOptions1 .= makeRow();
$configOptions1 .= makeRow("Directories", "Make sure the web server can write to these!");
$configOptions1 .= makeOptText("Images", "dir_images", is_writable($config["dir_images"]));
$configOptions1 .= makeOptText("Thumbnails", "dir_thumbs", is_writable($config["dir_thumbs"]));

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("Index Page");
$configOptions2 .= makeOptText("Width", "index_width", $config["index_width"] > 0);
$configOptions2 .= makeOptText("Height", "index_height", $config["index_height"] > 0);
$configOptions2 .= makeOptCheck("Inverted Index", "index_invert",
                  "On: Page 1 is always the newest, higher numbered pages have older images.".
				  "<p>Off: When page 1 is full, new images go on page 2, then on page 3, etc.");

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("Thumbnails");
$configOptions2 .= makeOptText("Width",     "thumb_w", $config["thumb_w"] > 0);
$configOptions2 .= makeOptText("Height",    "thumb_h", $config["thumb_h"] > 0);
$configOptions2 .= makeOptText("Quality %", "thumb_q", $config["thumb_q"] > 0 && $config["thumb_q"] <= 100);

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("View Page", "Links can be made nicer by changing these, then adding some mod_rewrite rules in a .htaccess");
$configOptions2 .= makeOptCheck("Scale by default", "view_scale", "whether or not images should fit the page");
$configOptions2 .= makeOptText("Full link", "image_link", preg_match('/\$id/', $config['image_link']));
$configOptions2 .= makeOptText("Short link", "image_slink", preg_match('/\$id/', $config['image_slink']));

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("Tags Page");
//$configOptions2 .= makeOptText("Default layout", "tags_default", null);
$configOptions2 .= makeOptCombo("Default layout", "tags_default",
                   array("Alphabetical" => "alphabet", "Map" => "map", "Popularity" => "popular"));
$configOptions2 .= makeOptText("Min usage threshold", "tags_min", $config['tags_min'] >= 0);

$configOptions1 .= makeRow();
$configOptions1 .= makeRow("Misc");
$configOptions1 .= makeOptText("Max Uploads", "upload_count", $config["upload_count"] > 0);
$configOptions1 .= makeOptText("Upload Size", "upload_size", $config["upload_size"] > 0);
$configOptions1 .= makeOptCheck("Anon Upload", "upload_anon");
$configOptions1 .= makeOptCheck("Anon Comment", "comment_anon");
$configOptions1 .= makeOptCheck("Allow logins", "login_enabled");
$configOptions1 .= makeOptText("Recent Comments", "recent_count", $config['recent_count'] > 0);
$configOptions1 .= makeOptText("Popular Tags", "popular_count", $config['popular_count'] > 0);

$configOptions1 .= makeRow();
$configOptions1 .= makeRow("Flood Protection", "No more than [count] comments per [window] minutes will be allowed");
$configOptions1 .= makeOptText("Comment window", "comment_window", $config["comment_window"] > 0);
$configOptions1 .= makeOptText("Comment count", "comment_limit", $config["comment_limit"] > 0);

$title = "Shimmie Setup";
$blocks = get_blocks_html("setup");
require_once "templates/setup.php";


/* =================================================================== */

/*
 * Quick functions
 */
function makeRow($content = "&nbsp;", $help=false) {
	if($help) {
		$helptag = " onMouseOver='setHelp(\"$help\")' class='helpable'";
	}
	return "<tr><td colspan='3'><span$helptag>$content</span></td></tr>\n";
}
function makeOptText($friendly, $varname, $ok=null, $help=false) {
	global $config;
	$okv = (is_null($ok) ? "" : ($ok ? "ok" : "bad"));
	$default = $config[$varname];
	if($help) {
		$helptag = " onMouseOver='setHelp(\"$help\")' class='helpable'";
	}
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='text' name='$varname' value='$default' class='$okv'></td>
		</tr>
	";	
}
function makeOptCheck($friendly, $varname, $help=false) {
	global $config;
	$default = $config[$varname] ? " checked" : "";
	if($help) {
		$helptag = " onMouseOver='setHelp(\"$help\")' class='helpable'";
	}
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='checkbox' name='$varname'$default></td>
		</tr>
	";
}
function makeOptCombo($friendly, $varname, $options, $help=false) {
	global $config;
	if($help) {
		$helptag = " onMouseOver='setHelp(\"$help\")' class='helpable'";
	}
	$html = "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><select name='$varname' class='ok'>
	";
	foreach($options as $optname => $optval) {
		if($optval == $config[$varname]) $selected=" selected";
		else $selected="";
		$html .= "<option value='$optval'$selected>$optname</option>\n";
	}
	$html .= "
			</select></td>
		</tr>
	";
	return $html;
}
?>
