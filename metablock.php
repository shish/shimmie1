<?php
/*
 * metablock.php (c) Shish 2006
 *
 * run a block as a standalone page, used for eg upload, so the block
 * can be included on the sidebar normally, and it can call itself to
 * do the uploading
 */

require_once "header.php";

$blockname = $_GET['block'];
if($blockname) {
	require_once "./blocks/$blockname.php";
	$block = new $blockname();
	$block->run($_GET["action"]);
}
else {
	header("X-Shimmie-Status: Error - No block specified");
	header("Location: ./");
}
?>
