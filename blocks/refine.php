<?php
/*
 * refine.php (c) Shish 2006
 *
 * Refine a search
 */

global $htmlSafeTags;
	
if(($pageType == "index") && (strlen($htmlSafeTags) > 0)) {
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

	$popularBlock = "<h3 onclick=\"toggle('refine')\">Refine Search</h3>\n<div id=\"refine\">";
	while($row = sql_fetch_row($pop_result)) {
		$tag = htmlentities($row['tag']);
		if($n++) $popularBlock .= "<br/>";
		$untagged = trim(preg_replace("/-?$tag/", "", $htmlSafeTags));
		$popularBlock .= "<a href='index.php?tags=$tag'>$tag</a> (";
		$popularBlock .= "<a href='index.php?tags=$untagged $tag' title='add tag to the current search'>a</a>, ";
		$popularBlock .= "<a href='index.php?tags=$untagged' title='remove tag from the current search'>r</a>, ";
		$popularBlock .= "<a href='index.php?tags=$untagged -$tag' title='subtract matching images from the results'>s</a>)\n";
	}
	$popularBlock .= "</div>";

	$blocks[60] = $popularBlock;
}
?>
