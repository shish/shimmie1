<?php
/*
 * view.php (c) Shish 2005, 2006
 *
 * View an image and it's comments
 */

require_once "header.php";

$image = new Image($_GET['image_id']);

$dir_images = $config['dir_images'];
$baseurl = $config['base_href'];
$scale = $config['view_scale'] ? "style='width: 90%'" : "";

if(!empty($image->slink)) {
	$slink_html = "<br/>Short link: <input type='text' size='50' value='{$image->slink}'>";
}

$title = html_escape($image->tags);
$blocks = get_blocks_html("view");
$heading = "Image";
$message = "
	<img onclick='scale(this)' src='{$image->link}' alt='{$image->tags}' $scale>
	$slink_html
	<br/>Uploaded by {$image->owner}
";
require_once "templates/generic.php";
?>
