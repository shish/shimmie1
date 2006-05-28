<?php
/*
 * view.php (c) Shish 2005, 2006
 *
 * View an image and it's comments
 */

require_once "header.php";

$image = new Image($_GET['image_id']);

$dir_images = $config['dir_images'];
$baseurl = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
$baseurl = preg_replace('#[^/]+$#', '', $baseurl);
$scale = $config['view_scale'] ? "style='width: 90%'" : "";

$title = implode(" ", $image->tags);
$blocks = getBlocks("view");
require_once "templates/view.php";
?>
