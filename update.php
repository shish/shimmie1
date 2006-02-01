<?php
/*
 * update.php (c) Shish 2005, 2006
 *
 * Updates an image's tag set
 */

require_once "header.php";

$config['upload_anon'] || user_or_die();


// get input
$image_id = (int)$_POST['image_id'];
updateTags($image_id, sql_escape($_POST['tags']));

// go back
header("Location: view.php?image_id=$image_id");
echo "<p><a href='view.php?image_id=$image_id'>Back</a>";
?>
