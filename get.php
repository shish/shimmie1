<?php
/*
 * image.php (c) Shish 2006
 *
 * Spew an image, allows things like nice filenames
 */

require_once "header.php";


/*
 * Get the ID of the image to view
 */
$args = explode("/", $_SERVER['PATH_INFO']);
$hash = sql_escape($args[1]);

$row = sql_fetch_row(sql_query("SELECT * FROM shm_images WHERE hash='$hash'"));

$image_dir = $config['dir_images'];
$hash = $row['hash'];
$ext  = $row['ext'];
$filename = "$image_dir/$hash.$ext";

$gmdate_mod = @gmdate('D, d M Y H:i:s', @filemtime($filename)) . ' GMT';

	
if(!is_null($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
	$if_modified_since = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);

	if($if_modified_since == $gmdate_mod) {
		header("HTTP/1.0 304 Not Modified");
		header("Content-type: image/jpeg");
	}
}
else {
	header("Content-type: image/jpeg");
	header("Last-Modified: $gmdate_mod");
	print file_get_contents($filename);
}
?>
