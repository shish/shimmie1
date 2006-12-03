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
$configOptions .= makeRow("Global");
$configOptions .= makeOpt("Title", "title", strlen($config["dir_thumbs"]) > 0);

$configOptions .= makeRow();
$configOptions .= makeRow("Directories");
$configOptions .= makeOpt("Images", "dir_images", is_writable($config["dir_images"]));
$configOptions .= makeOpt("Thumbnails", "dir_thumbs", is_writable($config["dir_thumbs"]));

$configOptions .= makeRow();
$configOptions .= makeRow("Index Page");
$configOptions .= makeOpt("Width", "index_width", $config["index_width"] > 0);
$configOptions .= makeOpt("Height", "index_height", $config["index_height"] > 0);
$configOptions .= makeOptCheck("Invert List", "index_invert", null);

$configOptions .= makeRow();
$configOptions .= makeRow("Thumbnails");
$configOptions .= makeOpt("Width",     "thumb_w", $config["thumb_w"] > 0);
$configOptions .= makeOpt("Height",    "thumb_h", $config["thumb_h"] > 0);
$configOptions .= makeOpt("Quality %", "thumb_q", $config["thumb_q"] > 0 && $config["thumb_q"] <= 100);

$configOptions .= makeRow();
$configOptions .= makeRow("View Page");
$configOptions .= makeOptCheck("Scale by default", "view_scale", null);
$configOptions .= makeOpt("Full link", "image_link", null);
$configOptions .= makeOpt("Short link", "image_slink", null);

$configOptions .= makeRow();
$configOptions .= makeRow("Tags Page");
$configOptions .= makeOpt("Default layout", "tags_default", null);
$configOptions .= makeOpt("Min usage threshold", "tags_min", null);

$configOptions .= makeRow();
$configOptions .= makeRow("Misc");
$configOptions .= makeOpt("Max Uploads", "upload_count", $config["upload_count"] > 0);
$configOptions .= makeOpt("Upload Size", "upload_size", $config["upload_size"] > 0);
$configOptions .= makeOptCheck("Anon Upload", "upload_anon", null);
$configOptions .= makeOptCheck("Anon Comment", "comment_anon", null);
$configOptions .= makeOptCheck("Allow logins", "login_enabled", null);
$configOptions .= makeOpt("Recent Comments", "recent_count", null);
$configOptions .= makeOpt("Popular Tags", "popular_count", null);

$configOptions .= makeRow();
$configOptions .= makeRow("Flood Protection");
$configOptions .= makeRow("(ie, no more than [count] comments per [window] minutes)");
$configOptions .= makeOpt("Comment window", "comment_window", $config["comment_window"] > 0);
$configOptions .= makeOpt("Comment count", "comment_limit", $config["comment_limit"] > 0);

$title = "Shimmie Setup";
$blocks = get_blocks_html("setup");
require_once "templates/setup.php";


/* =================================================================== */

/*
 * Quick functions
 */
function makeRow($content = "&nbsp;") {
	return "<tr><td colspan='3'>$content</td></tr>\n";
}
function makeOpt($friendly, $varname, $ok) {
	global $config;
	$okv = (is_null($ok) ? "-" : ($ok ? "OK" : "Fail"));
	$default = $config[$varname];
	return "<tr><td>$friendly</td><td><input type='text' name='$varname' value='$default'></td><td>$okv</td></tr>\n";
}
function makeOptCheck($friendly, $varname, $ok) {
	global $config;
	$okv = (is_null($ok) ? "-" : ($ok ? "OK" : "Fail"));
	$default = $config[$varname] ? " checked" : "";
	return "<tr><td>$friendly</td><td><input type='checkbox' name='$varname'$default></td><td>$okv</td></tr>\n";
}
?>
