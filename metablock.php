<?php
/*
 * metablock.php (c) Shish 2006
 *
 * run a block as a standalone page
 */

$block = $_GET['block'];
if($block) {
	$standalone = true;
	require_once "./blocks/$block.php";
}
else {
	header("Location: ./");
}
?>
