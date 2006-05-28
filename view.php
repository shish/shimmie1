<?php
/*
 * view.php (c) Shish 2005, 2006
 *
 * View an image and it's comments
 */

require_once "header.php";


$img_id = (int)$_GET['image_id'];


/*
 * Calculate the "related tags" sidebar
 */
$img_query = <<<EOD
	SELECT name,hash,ext,filename
	FROM shm_images
	LEFT JOIN shm_users ON shm_images.owner_id=shm_users.id
	WHERE shm_images.id=$img_id
EOD;
$img_result = sql_query($img_query);
if(sql_num_rows($img_result) == 0) {
	header("X-Shimmie-Status: Error - No Such Image");
	$title = "No Image $img_id";
	$body = "The image has either been deleted, or there aren't that many images in the database";
	require_once "templates/generic.php";
	exit;
}
else {
	header("X-Shimmie-Status: OK - Showing Image");
}

$img_info = sql_fetch_row($img_result);
$img_user = htmlentities($img_info['name']);
$img_hash = $img_info['hash'];
$img_ext = $img_info['ext'];
$img_fname = htmlentities($img_info['filename']);
$dir_images = $config['dir_images'];
$baseurl = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
$baseurl = preg_replace('#[^/]+$#', '', $baseurl);

// FIXME: count where tag = tag1 or tag2 or tag3
// store results in count['tag']
function countImagesForTag($tag) {
	$tag_query = "SELECT count(*) as count FROM shm_tags WHERE tag='$tag'";
	$row = sql_fetch_row(sql_query($tag_query));
	return $row['count'];
}

$tag_query = "SELECT * FROM shm_tags WHERE image_id=$img_id";
$tag_result = sql_query($tag_query);
$img_tags = "";
$tagLinks = "";
$n = 0;
while($row = sql_fetch_row($tag_result)) {
	$tag = htmlentities($row['tag']);
	$img_tags .= "$tag ";
	if($n++) $tagLinks .= "<br/>";
	$count = countImagesForTag(sql_escape($tag));
	$tagLinks .= "<a href='index.php?tags=$tag'>$tag ($count)</a>";
}
$img_tags = trim($img_tags);


/*
 * parse the image link templates
 */
function parseLinkTemplate($tmpl) {
	global $img_id, $img_hash, $img_tags, $img_ext;
	$tmpl = str_replace('$id',   $img_id,   $tmpl);
	$tmpl = str_replace('$hash', $img_hash, $tmpl);
	$tmpl = str_replace('$tags', $img_tags, $tmpl);
	$tmpl = str_replace('$ext',  $img_ext,  $tmpl);
	return $tmpl;
}
$img_link = parseLinkTemplate($config['image_link']);
$img_slink = parseLinkTemplate($config['image_slink']);


/*
 * Should we start with the image full sized, or
 * squashed into the available space?
 */
if($config['view_scale']) $scale = "style='width: 90%'";
else $scale = "";


$title = htmlentities($img_tags);
$blocks = getBlocks("view");
require_once "templates/view.php";
?>
