<?php
/*
 * comments.php (c) Shish 2005
 *
 * Make a block of recent comments
 *
 * FIXME: make these relevant to the image being viewed
 */

$com_count = $config['recent_count'];
$com_query = <<<EOD
	SELECT 
		*,
		if(
			length(comment) > 128,
			concat(substring(comment, 1, 128), '>>>'),
			comment
		) as scomment FROM shm_comments
	LEFT JOIN users ON shm_comments.owner_id=users.id 
	ORDER BY shm_comments.id DESC
	LIMIT $com_count
EOD;
$com_result = sql_query($com_query);
$commentBlock = "<h3 onclick=\"toggle('comments')\">Comments</h3>\n<div id=\"comments\">";
while($row = sql_fetch_row($com_result)) {
	$iid = $row['image_id'];
	$uname = htmlentities($row['name']);
	$comment = htmlentities($row['scomment']);
	$commentBlock .= "<p><a href='view.php?image_id=$iid'>$uname</a>: $comment</p>\n";
}
$commentBlock .= "</div>\n";
?>
