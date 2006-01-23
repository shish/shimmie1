<?php
/*
 * tags.php (c) Shish 2005
 *
 * List all tags, in a variety of ways
 */

require_once "header.php";

if(is_null($_GET['mode'])) $mode = $config['tags_default'];
else $mode = $_GET['mode'];


$tags_min = $config["tags_min"];
$base_query = "SELECT tag,COUNT(image_id) AS count FROM shm_tags GROUP BY tag HAVING count > $tags_min";


/*
 * list tags by popularity, grouped by first digit of natural
 * logarythm. This means that a site with lots of single use
 * tags, some medium use, and few common use, will be given 
 * a more even layout.
 */
if($mode == "popular") {
	$tlist_query = "$base_query ORDER BY count DESC, tag ASC";
	$tlist_result = sql_query($tlist_query);
	$n = 0;
	$tlist = "Results grouped by log<sub>e</sub>(n)";
	$lastLog = 0;
	while($row = sql_fetch_row($tlist_result)) {
		$tag = $row['tag'];
		$count = $row['count'];
		if($lastLog != floor(log($count))) {
			$lastLog = floor(log($count));
			$tlist .= "<p>$lastLog<br>";
		}
		$tlist .= "<a href='index.php?tags=$tag'>$tag&nbsp;($count)</a>\n";
	}
}


/*
 * List all in alphabetical order
 */
else if($mode == "alphabet") {
	$tlist_query = "$base_query ORDER BY tag";
	$tlist_result = sql_query($tlist_query);
	$n = 0;
	$lastLetter = 0;
	while($row = sql_fetch_row($tlist_result)) {
		$tag = $row['tag'];
		$count = $row['count'];
		if($lastLetter != substr($tag, 0, 1)) {
			$lastLetter = substr($tag, 0, 1);
			$tlist .= "<p>$lastLetter<br>";
		}
		$tlist .= "<a href='index.php?tags=$tag'>$tag&nbsp;($count)</a>\n";
	}
}


/*
 * List the most popular tags in alphabetical order, with the
 * name of the tag scaled to it's popularity -- it makes spotting
 * popular tags easy.
 *
 * This cuts out tags that are only used once, as they take up a
 * lot of space with little use otherwise.
 *
 * FIXME: put a threshold into the config table, as a large site
 * may want to hide tags that are used less than eg 10 times.
 */
#else if($mode == "map") {
else {
	$tlist_query = "$base_query ORDER BY tag";
	$tlist_result = sql_query($tlist_query);
	$n = 0;
	while($row = sql_fetch_row($tlist_result)) {
		$tag = $row['tag'];
		$count = $row['count'];
		if($count > 1) {
			$size = floor(log(log($row['count'])+1)*1.5*100)/100;
			$tlist .= "<a style='font-size: ${size}em' href='index.php?tags=$tag'>$tag</a>\n";
		}
	}
}


$title = "Tags";
require_once "templates/tags.php";
?>

