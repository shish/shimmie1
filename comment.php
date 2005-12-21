<?php
/*
 * comment.php (c) Shish 2005
 *
 * Adds a comment to an image
 */

require_once "header.php";

$config["comment_anon"] || user_or_die();


// get input
$image_id = (int)$_POST['image_id'];
$owner_id = $user->id;
$owner_ip = $_SERVER['REMOTE_ADDR'];
$comment = addslashes($_POST['comment']);

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
?>

