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
	$db->StartTrans();
	$db->Execute("DELETE FROM config");
	
	$config_keys = array_keys($config_defaults);
	foreach($config_keys as $cname) {
		$cval = $_POST[$cname];
		$db->Execute("INSERT INTO config(name, value) VALUES(?, ?)", Array($cname, $cval));
		$config[$cname] = $cval; // update here so the display below is correct
	}
	$db->CommitTrans();
}


/*
 * Things which need to be saved, but not changed
 */
$configOptions1 .= makeOptHidden("db_version");
$configOptions1 .= makeOptHidden("anon_id");

/*
 * Generate the HTML form
 */
$configOptions1 .= makeRow("Global");
$configOptions1 .= makeOptText("Title", "title", strlen($config["title"]) > 0);
$configOptions1 .= makeOptText("Base URL", "base_href", null);

$configOptions1 .= makeRow();
$configOptions1 .= makeRow("Directories");
$configOptions1 .= makeOptText("Images", "dir_images", is_writable($config["dir_images"]));
$configOptions1 .= makeOptText("Thumbnails", "dir_thumbs", is_writable($config["dir_thumbs"]));

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("Index Page");
$configOptions2 .= makeOptText("Width", "index_width", $config["index_width"] > 0);
$configOptions2 .= makeOptText("Height", "index_height", $config["index_height"] > 0);
$configOptions2 .= makeOptCheck("Inverted Index", "index_invert");

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("Thumbnails");
$configOptions2 .= makeOptText("Width",     "thumb_w", $config["thumb_w"] > 0);
$configOptions2 .= makeOptText("Height",    "thumb_h", $config["thumb_h"] > 0);
$configOptions2 .= makeOptText("Quality %", "thumb_q", $config["thumb_q"] > 0 && $config["thumb_q"] <= 100);

$configOptions2 .= makeRow();
$configOptions2 .= makeRow("View Page");
$configOptions2 .= makeOptCheck("Scale by default", "view_scale");
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
$configOptions1 .= makeRow("Flood Protection");
$configOptions1 .= makeOptText("Comment window", "comment_window", $config["comment_window"] > 0);
$configOptions1 .= makeOptText("Comment count", "comment_limit", $config["comment_limit"] > 0);

$title = "Shimmie Setup";
$blocks = get_blocks_html("setup");
$body["Fill in this form"] = "
	<form action='setup.php' method='POST'>
		<table style='width: 800px;' border='1'>
			<tr>
				<td><table style='width: 400px;'>$configOptions1</table></td>
				<td><table style='width: 400px;'>$configOptions2</table></td>
			</tr>
			<tr><td colspan='2'><input type='hidden' name='action' value='set'><input type='submit' value='Set Settings'></td></tr>
		</table>
	</form>
";
require_once "templates/generic.php";


/* =================================================================== */

/*
 * Quick functions
 */
function makeRow($content = "&nbsp;") {
	return "<tr><td colspan='3'><span$helptag><b>$content</b></span></td></tr>\n";
}
function makeOptText($friendly, $varname, $ok=null) {
	global $config;
	$okv = (is_null($ok) ? "" : ($ok ? "ok" : "bad"));
	$default = $config[$varname];
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='text' name='$varname' value='$default' class='$okv'></td>
		</tr>
	";	
}
function makeOptHidden($varname) {
	global $config;
	$default = $config[$varname];
	return "
		<input type='hidden' name='$varname' value='$default'>
	";	
}
function makeOptCheck($friendly, $varname) {
	global $config;
	$default = $config[$varname] ? " checked" : "";
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='checkbox' name='$varname'$default></td>
		</tr>
	";
}
function makeOptCombo($friendly, $varname, $options) {
	global $config;
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
