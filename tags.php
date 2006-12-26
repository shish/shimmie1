<?php
/*
 * tags.php (c) Shish 2005, 2006
 *
 * List all tags, in a variety of ways
 */

require_once "header.php";

if(is_null($_GET['mode'])) $mode = html_escape($config['tags_default']);
else $mode = html_escape($_GET['mode']);

if(is_null($_GET['tags_min'])) $tags_min = int_escape($config['tags_min']);
else $tags_min = int_escape($_GET['tags_min']);

if($tags_min > 0) {
	$listMore = "Only tags with more than $tags_min uses are shown; ".
	            "click <a href='tags.php?mode=$mode&tags_min=0'>here</a> to see all of them.";
}
else {
	$listMore = "";
}

$base_query = "SELECT tag,COUNT(image_id) AS count FROM tags GROUP BY tag HAVING count > $tags_min";


/*
 * list tags by popularity, grouped by /# $ floor(log_{e}(tags)) $ #/.
 * This means that a site with lots of single use
 * tags, some medium use, and few common use, will be given 
 * a more even layout.
 */
if($mode == "popular") {
	$tlist_query = "$base_query ORDER BY count DESC, tag ASC";
	$tlist_result = $db->Execute($tlist_query);
	$n = 0;
	$tlist = "Results grouped by log<sub>e</sub>(n)";
	$lastLog = 0;
	while(!$tlist_result->EOF) {
		$row = $tlist_result->fields;
		$h_tag = html_escape($row['tag']);
		$count = $row['count'];
		if($lastLog != floor(log($count))) {
			$lastLog = floor(log($count));
			$tlist .= "<p>$lastLog<br>";
		}
		$tlist .= "<a href='index.php?tags=$h_tag'>$h_tag&nbsp;($count)</a>\n";
		$tlist_result->MoveNext();
	}
}


/*
 * List all in alphabetical order, grouped by letter
 */
else if($mode == "alphabet") {
	$tlist_query = "$base_query ORDER BY tag";
	$tlist_result = $db->Execute($tlist_query);
	$n = 0;
	$lastLetter = 0;
	while(!$tlist_result->EOF) {
		$row = $tlist_result->fields;
		$h_tag = html_escape($row['tag']);
		$count = $row['count'];
		if($lastLetter != substr($h_tag, 0, 1)) {
			$lastLetter = substr($h_tag, 0, 1);
			$tlist .= "<p>$lastLetter<br>";
		}
		$tlist .= "<a href='index.php?tags=$h_tag'>$h_tag&nbsp;($count)</a>\n";
		$tlist_result->MoveNext();
	}
}


/*
 * List the most popular tags in alphabetical order, with the
 * name of the tag scaled to it's popularity -- it makes spotting
 * popular tags easy.
 */
else {
	$tlist_query = "$base_query ORDER BY tag";
	$tlist_result = $db->Execute($tlist_query);
	$n = 0;
	while(!$tlist_result->EOF) {
		$row = $tlist_result->fields;
		$h_tag = html_escape($row['tag']);
		$count = $row['count'];
		if($count > 1) {
			$size = floor(log(log($row['count'] - $tags_min + 1)+1)*1.5*100)/100;
			$tlist .= "&nbsp;<a style='font-size: ${size}em' href='index.php?tags=$h_tag'>$h_tag</a>&nbsp;\n";
		}
		$tlist_result->MoveNext();
	}
}


$title = "Tags";
$blocks = get_blocks_html("tags");
$body["Tag List"] = $listMore;
$body[] = "<div id='taglist'>$tlist</div>";
require_once get_theme_template();
?>

