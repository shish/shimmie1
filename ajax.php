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

$start = $db->Quote($_GET['start'].'%');
$tags_min = int_escape(get_config('tags_min'));

$query = <<<EOD
	SELECT tag,COUNT(image_id) AS count
	FROM tags
	WHERE tag LIKE $start
	GROUP BY tag
	ORDER BY count DESC, tag ASC
	LIMIT 10
EOD;
	#HAVING count > $tags_min

$n = 0;
$row = $db->Execute($query);
while(!$row->EOF) {
	$tag = htmlentities($row->fields['tag']);
	$count = $row->fields['count'];
	#print "<br/><a href='index.php?tags=$tag'>$tag ($count)</a>\n";
	print "$tag\n";
	$row->MoveNext();
}
?>

