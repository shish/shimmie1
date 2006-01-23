<?php
/*
 * view.php (c) Shish 2005
 *
 * View an image and it's comments
 */

require_once "header.php";


/*
 * Get the ID of the image to view
 */
if($_SERVER['PATH_INFO'] && is_numeric(substr($_SERVER['PATH_INFO'], 1))) {
	$image_id = (int)substr($_SERVER['PATH_INFO'], 1);
}
else {
	$image_id = (int)$_GET['image_id'];
}


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
$img_info = sql_fetch_row($img_result);
$img_user = htmlentities($img_info['name']);
$img_hash = $img_info['hash'];
$img_ext = $img_info['ext'];
$img_fname = htmlentities($img_info['filename']);
$dir_images = $config['dir_images'];

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
	$count = countImagesForTag(addslashes($tag));
	$tagLinks .= "<a href='index.php?tags=$tag'>$tag ($count)</a>";
}
$tags = trim($tags);


/*
 * Find comments for this image
 */
$com_query = "SELECT name,comment FROM shm_comments LEFT JOIN shm_users ON shm_comments.owner_id=shm_users.id WHERE image_id=$image_id";
$com_result = sql_query($com_query);
$comments = "";
while($row = sql_fetch_row($com_result)) {
	$uname = htmlentities($row['name']);
	$comment = htmlentities($row['comment']);
	$comments .= "<p class='comment'>$uname: $comment</p>\n";
}


/*
 * Admins are allowed to delete things
 */
if($user->isAdmin) {
	$adminBlock = <<<EOD
	<h3 onclick="toggle('admin')">Admin</h3>
	<div id="admin">
		<a href="admin.php?action=delete&image_id=$image_id">Delete Image</a>
	</div>
EOD;
}


/*
 * Fill the navigation block
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
