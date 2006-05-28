<?php
/*
 * metablock.php (c) Shish 2006
 *
 * run a block as a standalone page, used for eg upload, so the block
 * can be included on the sidebar normally, and it can call itself to
 * do the uploading
 */

$block = $_GET['block'];
if($block) {
	$pageType = "block";
	require_once "./blocks/$block.php";
}
else {
	header("X-Shimmie-Status: Error - No block specified");
	header("Location: ./");
}
?>
