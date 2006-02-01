<?php
/*
 * popular.php (c) Shish 2005, 2006
 *
 * List popular tags
 *
 * FIXME: only count tags that match the current search (ie, "related tags")
 */


//if($_GET['tags']) {
//	$tags = htmlentities($_GET['tags']);
//	$popularBlock = "<h3 onclick=\"toggle('popular')\">Related Tags</h3>\n<div id=\"popular\">";
//}
//else {
	$popularBlock = "<h3 onclick=\"toggle('popular')\">Popular Tags</h3>\n<div id=\"popular\">";
//}

$pop_count = $config['popular_count'];
$pop_query = <<<EOD
	SELECT 
		tag,
		COUNT(image_id) AS count
	FROM shm_tags
	GROUP BY tag
	ORDER BY count DESC
	LIMIT $pop_count
EOD;
$pop_result = sql_query($pop_query);
$n = 0;
while($row = sql_fetch_row($pop_result)) {
	$tag = htmlentities($row['tag']);
	$count = $row['count'];
	if($n++) $popularBlock .= "<br/>";
	$popularBlock .= "<a href='index.php?tags=$tag'>$tag ($count)</a> ";
//	$popularBlock .= "<a href='index.php?tags=$tags $tag'>(+)</a>\n";
}
$popularBlock .= "<p><a href='tags.php'>Full List &gt;&gt;&gt;</a>\n";
$popularBlock .= "</div>";
?>
