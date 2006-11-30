<?php
/*
 * related.php (c) Shish 2006
 *
 * Get related tags for an image
 */

class related extends block {
	function get_html($pageType) {
		if($pageType == "view") {
			global $image, $config;

			$pop_count = int_escape($config['popular_count']);
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
				$tag = html_escape($row['tag']);
				$count = $row['count'];
				if($n++) $relatedBlock .= "<br/>";
				$relatedBlock .= "<a href='index.php?tags=$tag'>$tag</a>\n";
			}
			$relatedBlock .= "</div>";

			return $relatedBlock;
		}
	}

	function get_priority() {
		return 60;
	}
}
?>
