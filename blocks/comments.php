<?php
/*
 * comments.php (c) Shish 2005, 2006
 *
 * Make a block of recent comments
 */

require_once "header.php";

if($blockmode == "block") {
	if($_GET['image_id']) {
		$image_id = (int)$_GET['image_id'];
		$where = "WHERE image_id=$image_id";
	}
	else {
		$image_id = false;
		$where = "";
	}

	$com_count = $config['recent_count'];
	$com_query = <<<EOD
		SELECT 
			image_id, name,
			if(
				length(comment) > 128,
				concat(substring(comment, 1, 128), '>>>'),
				comment
			) as scomment FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		$where
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
	if($image_id) {
		$image_id = (int)$_GET['image_id'];
		$commentBlock .= <<<EOD
		<form action="metablock.php?block=comment" method="POST">
			<input type="hidden" name="image_id" value="$image_id">
			<input id="commentBox" type="text" name="comment" value="Comment">
			<input type="submit" value="Say" style="display: none;">
		</form>
EOD;
	}
	$commentBlock .= "</div>\n";
}

if($blockmode == "standalone" && ($config["comment_anon"] || user_or_die())) {
	// get input
	$image_id = (int)$_POST['image_id'];
	$owner_id = $user->id;
	$owner_ip = $_SERVER['REMOTE_ADDR'];
	$comment = sql_escape($_POST['comment']);

	// check validity
	if(trim($comment) == "") {
		$title = "No message";
		$message = "Comment was empty; <a href='view.php?image_id=$image_id'>Back</a>";
		require_once "templates/generic.php";
	}
	else {
		// update database
		$new_query = "INSERT INTO shm_comments(image_id, owner_id, owner_ip, comment) ".
		             "VALUES($image_id, $owner_id, '$owner_ip', '$comment')";
		sql_query($new_query);
	
		// go back to the viewed page
		header("Location: view.php?image_id=$image_id");
		echo "<a href='view.php?image_id=$image_id'>Back</a>";
	}
}
?>
