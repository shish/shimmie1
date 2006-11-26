<?php
/*
 * popular.php (c) Shish 2005, 2006
 *
 * List popular tags
 */

class popular extends block {
	function get_html($pageType) {
		global $htmlSafeTags, $config;
	
		if(($pageType == "index") && (strlen($htmlSafeTags) == 0)) {
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

			$popularBlock = "<h3 id=\"popular-toggle\" onclick=\"toggle('popular')\">Popular Tags</h3>\n<div id=\"popular\">";
			while($row = sql_fetch_row($pop_result)) {
				$tag = htmlentities($row['tag']);
				$count = $row['count'];
				if($n++) $popularBlock .= "<br/>";
				$popularBlock .= "<a href='index.php?tags=$tag'>$tag ($count)</a>\n";
				if($htmlSafeTags) $popularBlock .= "<a href='index.php?tags=$htmlSafeTags $tag'>(+)</a>\n";
			}
			$popularBlock .= "<p><a href='tags.php'>Full List &gt;&gt;&gt;</a>\n";
			$popularBlock .= "</div>";

			return $popularBlock;
		}
	}

	function get_priority() {
		return 60;
	}
}
?>
