<?php
/*
 * refine.php (c) Shish 2006
 *
 * Refine a search
 */

global $image;

if($pageType == "view") {
	$pop_count = $config['popular_count'];
	$related_query = <<<EOD
		SELECT COUNT(t3.image_id) as count, t3.tag 
		FROM
			tags AS t1,
			tags AS t2,
			tags AS t3 
		WHERE
			t1.image_id={$image->id}
			AND t1.tag=t2.tag
			AND t2.image_id=t3.image_id
		GROUP by t3.tag
		ORDER by count DESC
		LIMIT $pop_count;
EOD;
	$result = sql_query($related_query);
	$n = 0;

	$relatedBlock = "<h3 id=\"related-toggle\" onclick=\"toggle('related')\">Related Tags</h3>\n<div id=\"related\">";
	while($row = sql_fetch_row($result)) {
		$tag = htmlentities($row['tag']);
		$count = $row['count'];
		if($n++) $relatedBlock .= "<br/>";
		$relatedBlock .= "<a href='index.php?tags=$tag'>$tag</a>\n";
	}
	$relatedBlock .= "</div>";

	$blocks[60] .= $relatedBlock;
}
?>
