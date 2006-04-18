<?php
/*
 * view.php (c) Shish 2005, 2006
 *
 * -- view an image
 *
 * View an image and it's comments
 */

require_once "header.php";


$image_id = (int)$_GET['image_id'];


/*
 * Calculate the "related tags" sidebar
 */
$img_query = <<<EOD
	SELECT name,hash,ext,filename
	FROM shm_images
	LEFT JOIN shm_users ON shm_images.owner_id=shm_users.id
	WHERE shm_images.id=$image_id
EOD;
$img_result = sql_query($img_query);
if(sql_num_rows($img_result) == 0) {
	header("X-Shimmie-Status: Error - No Such Image");
	$title = "No Image $image_id";
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
$baseurl = $_SERVER['SCRIPT_URI'];
$baseurl = preg_replace("#[^/]+$#", "", $baseurl);

// FIXME: count where tag = tag1 or tag2 or tag3
// store results in count['tag']
function countImagesForTag($tag) {
	$tag_query = "SELECT count(*) as count FROM shm_tags WHERE tag='$tag'";
	$row = sql_fetch_row(sql_query($tag_query));
	return $row['count'];
}

$tag_query = "SELECT * FROM shm_tags WHERE image_id=$image_id";
$tag_result = sql_query($tag_query);
$tags = "";
$tagLinks = "";
$n = 0;
while($row = sql_fetch_row($tag_result)) {
	$tag = htmlentities($row['tag']);
	$tags .= "$tag ";
	if($n++) $tagLinks .= "<br/>";
	$count = countImagesForTag(sql_escape($tag));
	$tagLinks .= "<a href='index.php?tags=$tag'>$tag ($count)</a>";
}
$tags = trim($tags);


/*
 * Admins are allowed to delete things
 */
if($user->isAdmin) {
	$adminBlock = <<<EOD
	<h3 onclick="toggle('admin')">Admin</h3>
	<div id="admin">
		<a href="admin.php?action=rmimage&image_id=$image_id">Delete Image</a>
	</div>
EOD;
}


/*
 * Fill the navigation block, next = the lowest higher number, prev = the highest lower number
 */
$row = sql_fetch_row(sql_query("SELECT id FROM shm_images WHERE id < $image_id ORDER BY id DESC LIMIT 1"));
$previd = $row ? $row['id'] : null;
$row = sql_fetch_row(sql_query("SELECT id FROM shm_images WHERE id > $image_id ORDER BY id ASC  LIMIT 1"));
$nextid = $row ? $row['id'] : null;

$pageNav = ($previd ? "<a href='view.php?image_id=$previd'>Prev</a> | " : "Prev | ").
		   "<a href='index.php'>Index</a> | ".
           ($nextid ? "<a href='view.php?image_id=$nextid'>Next</a>" : "Next");


/*
 * Should we start with the image full sized, or
 * squashed into the available space?
 */
if($config['view_scale']) $scale = "style='width:90%'";
else $scale = "";


$title = htmlentities($tags);
require_once "templates/view.php";
?>
