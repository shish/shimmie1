<?php
/*
 * tags.php (c) Shish 2005, 2006
 *
 * List all tags, in a variety of ways
 */

require_once "header.php";
header("Content-type: text/plain");

if(is_null($_GET['start']) || ($_GET['start'] == "")) {
	return;
}

$start = sql_escape($_GET['start']);
$tags_min = int_escape($config['tags_min']);

$query = <<<EOD
	SELECT tag,COUNT(image_id) AS count
	FROM shm_tags
	WHERE tag LIKE "$start%"
	GROUP BY tag
	ORDER BY count DESC, tag ASC
	LIMIT 10
EOD;
	#HAVING count > $tags_min

$result = sql_query($query);
$n = 0;
while($row = sql_fetch_row($result)) {
	$tag = htmlentities($row['tag']);
	$count = $row['count'];
	#print "<br/><a href='index.php?tags=$tag'>$tag ($count)</a>\n";
	print "$tag\n";
}
?>

