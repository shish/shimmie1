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

$title = html_escape($image->tags);
$blocks = get_blocks_html("view");
require_once "templates/view.php";
?>
