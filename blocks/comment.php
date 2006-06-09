<?php
/*
 * comments.php (c) Shish 2005, 2006
 *
 * Make a block of recent comments
 */

require_once "header.php";

if(($pageType == "index") || ($pageType == "view")) {
	$com_count = $config['recent_count'];

	if($pageType == "index") {
		$com_query = <<<EOD
		SELECT 
			shm_comments.id as id, image_id, name, owner_ip, 
			if(
				length(comment) > 100,
				concat(substring(comment, 1, 100), ' (...)'),
				comment
			) as scomment FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		$where
		ORDER BY shm_comments.id DESC
		LIMIT $com_count
EOD;
	}
	else if($pageType == "view") {
		$image_id = (int)$_GET['image_id'];
		$com_query = <<<EOD
		SELECT 
			shm_comments.id as id, image_id, 
			name, owner_ip, comment as scomment
		FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		WHERE image_id=$image_id
		ORDER BY shm_comments.id DESC
EOD;
	}
	$com_result = sql_query($com_query);
	$commentBlock = "<h3 onclick=\"toggle('comments')\">Comments</h3>\n<div id=\"comments\">";
	while($row = sql_fetch_row($com_result)) {
		$cid = $row['id'];
		$iid = $row['image_id'];
		$oip = $row['owner_ip'];
		$uname = htmlentities($row['name']);
		$comment = htmlentities($row['scomment']);
		$dellink = $user->isAdmin ? "<br>(<a href='admin.php?action=rmcomment&amp;comment_id=$cid'>X</a>) ($oip)" : "";
		$commentBlock .= "<p><a href='view.php?image_id=$iid'>$uname</a>: $comment$dellink</p>\n";
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

	$blocks[40] .= $commentBlock;
}

if(($pageType == "block") && ($config["comment_anon"] || user_or_die())) {
	// get input
	$image_id = (int)defined_or_die($_POST['image_id']);
	$owner_id = $user->id;
	$owner_ip = $_SERVER['REMOTE_ADDR'];
	$comment = sql_escape(defined_or_die($_POST['comment']));

	// check validity
	if(trim($comment) == "") {
		header("X-Shimmie-Status: Error - Blank Comment");
		$title = "No Message";
		$message = "Comment was empty; <a href='view.php?image_id=$image_id'>Back</a>";
		require_once "templates/generic.php";
	}
	else {
		// update database
		$new_query = "INSERT INTO shm_comments(image_id, owner_id, owner_ip, comment) ".
		             "VALUES($image_id, $owner_id, '$owner_ip', '$comment')";
		sql_query($new_query);
		$cid = sql_insert_id();
	
		// go back to the viewed page
		header("Location: view.php?image_id=$image_id");
		header("X-Shimmie-Status: OK - Comment Added");
		header("X-Shimmie-Comment-ID: $cid");
		echo "<a href='view.php?image_id=$image_id'>Back</a>";
	}
}
?>
