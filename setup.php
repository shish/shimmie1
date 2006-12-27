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


$theme_dirs = glob("themes/*/template.php");
$themes = Array();
foreach($theme_dirs as $theme_dir) {
	$theme = str_replace("themes/", "", $theme_dir);
	$theme = str_replace("/template.php", "", $theme);
	$themes[$theme] = $theme;
}
		

/*
 * Things which need to be saved, but not changed
 */
$c1 .= makeOptHidden("db_version");
$c1 .= makeOptHidden("anon_id");

/*
 * Generate the HTML form
 */
$c1 .= makeRow("Global");
$c1 .= makeOptText("Title", "title", strlen(get_config('title')) > 0);
$c1 .= makeOptText("Base URL", "base_href", null);
$c1 .= makeOptCombo("Theme", "theme", $themes);

$c1 .= makeRow();
$c1 .= makeRow("Directories");
$c1 .= makeOptText("Images", "dir_images", is_writable(get_config('dir_images')));
$c1 .= makeOptText("Thumbnails", "dir_thumbs", is_writable(get_config('dir_thumbs')));

$c2 .= makeRow();
$c2 .= makeRow("Index Page");
$c2 .= makeOptText("Width", "index_width", get_config('index_width') > 0);
$c2 .= makeOptText("Height", "index_height", get_config('index_height') > 0);
$c2 .= makeOptCheck("Inverted Index", "index_invert");

$c2 .= makeRow();
$c2 .= makeRow("Thumbnails");
$c2 .= makeOptText("Width",     "thumb_w", get_config('thumb_w') > 0);
$c2 .= makeOptText("Height",    "thumb_h", get_config('thumb_h') > 0);
$c2 .= makeOptText("Quality %", "thumb_q", get_config('thumb_q') > 0 && get_config('thumb_q') <= 100);

$c2 .= makeRow();
$c2 .= makeRow("View Page");
$c2 .= makeOptCheck("Scale by default", "view_scale");
$c2 .= makeOptText("Full link", "image_link", preg_match('/\$id/', get_config('image_link')));
$c2 .= makeOptText("Short link", "image_slink", preg_match('/\$id/', get_config('image_slink')));

$c2 .= makeRow();
$c2 .= makeRow("Tags Page");
//$c2 .= makeOptText("Default layout", "tags_default", null);
$c2 .= makeOptCombo("Default layout", "tags_default",
                   array("Alphabetical" => "alphabet", "Map" => "map", "Popularity" => "popular"));
$c2 .= makeOptText("Min usage threshold", "tags_min", get_config('tags_min') >= 0);

$c1 .= makeRow();
$c1 .= makeRow("Misc");
$c1 .= makeOptText("Max Uploads", "upload_count", get_config('upload_count') > 0);
$c1 .= makeOptText("Upload Size", "upload_size", get_config('upload_size') > 0);
$c1 .= makeOptCheck("Anon Upload", "upload_anon");
$c1 .= makeOptCheck("Anon Comment", "comment_anon");
$c1 .= makeOptCheck("Allow logins", "login_enabled");
$c1 .= makeOptText("Recent Comments", "recent_count", get_config('recent_count') > 0);
$c1 .= makeOptText("Popular Tags", "popular_count", get_config('popular_count') > 0);

$c1 .= makeRow();
$c1 .= makeRow("Flood Protection");
$c1 .= makeOptText("Comment window", "comment_window", get_config('comment_window') > 0);
$c1 .= makeOptText("Comment count", "comment_limit", get_config('comment_limit') > 0);

$title = "Shimmie Setup";
$blocks = get_blocks_html("setup");
$body["Fill in this form"] = "
	<form action='setup.php' method='POST'>
		<table style='width: 800px;' border='1'>
			<tr>
				<td><table style='width: 400px;'>$c1</table></td>
				<td><table style='width: 400px;'>$c2</table></td>
			</tr>
			<tr><td colspan='2'><input type='hidden' name='action' value='set'><input type='submit' value='Set Settings'></td></tr>
		</table>
	</form>
";
require_once get_theme_template();


/* =================================================================== */

/*
 * Quick functions
 */
function makeRow($content = "&nbsp;") {
	return "<tr><td colspan='3'><span$helptag><b>$content</b></span></td></tr>\n";
}
function makeOptText($friendly, $varname, $ok=null) {
	$okv = (is_null($ok) ? "" : ($ok ? "ok" : "bad"));
	$default = get_config($varname);
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='text' name='$varname' value='$default' class='$okv'></td>
		</tr>
	";	
}
function makeOptHidden($varname) {
	$default = get_config($varname);
	return "
		<input type='hidden' name='$varname' value='$default'>
	";	
}
function makeOptCheck($friendly, $varname) {
	$default = get_config($varname) ? " checked" : "";
	return "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><input type='checkbox' name='$varname'$default></td>
		</tr>
	";
}
function makeOptCombo($friendly, $varname, $options) {
	$html = "
		<tr>
			<td><span$helptag>$friendly</span></td>
			<td><select name='$varname' class='ok'>
	";
	foreach($options as $optname => $optval) {
		if($optval == get_config($varname)) $selected=" selected";
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
