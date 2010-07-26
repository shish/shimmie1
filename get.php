<?php
/*
 * image.php (c) Shish 2006
 *
 * Spew an image, allows things like nice filenames
 */

require_once "header.php";
session_cache_limiter('public'); // caching disabled for most pages -- turn it back on for images


/*
 * Get the ID of the image to view
 */
preg_match("#/[^\d]*(\d+).*\.(jpg|gif|png)$#", $_SERVER['PATH_INFO'], $args);
$image_dir = get_config('dir_images');
$name = $args[1];
$ext = $args[2];
$filename = "$image_dir/$name.$ext";


/*
 * Check if the user's version of the file matches the server's,
 * if it does, simply reply "your version is OK"
 */
$if_modified_since = preg_replace('/;.*$/', '', $_SERVER["HTTP_IF_MODIFIED_SINCE"]);
$gmdate_mod = gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT';

if($if_modified_since == $gmdate_mod) {
    header("HTTP/1.0 304 Not Modified");
    header("Content-type: image/$ext");
}
else {
    header("Content-type: image/$ext");
    header("Last-Modified: $gmdate_mod");
    print read_file($filename);
}
?>
