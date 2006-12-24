<?php
/*
 * related.php (c) Shish 2006
 *
 * Get related tags for an image
 */

class related extends block {
	function get_html($pageType) {
		if($pageType == "view") {
			global $image, $config, $db;

			$query = "
				SELECT COUNT(t3.image_id) as count, t3.tag 
				FROM
					tags AS t1,
					tags AS t2,
					tags AS t3 
				WHERE
					t1.image_id=?
					AND t1.tag=t2.tag
					AND t2.image_id=t3.image_id
				GROUP by t3.tag
				ORDER by count DESC
				LIMIT ?
			";

			$html = "<h3 id=\"related-toggle\" onclick=\"toggle('related')\">Related Tags</h3>\n";
			$html .= "<div id=\"related\">\n";
			
			$n = 0;
			$result = $db->Execute($query, Array($image->id, $config['popular_count']));
			while(!$result->EOF) {
				$row = $result->fields;
				$tag = html_escape($row['tag']);
				$count = $row['count'];
				if($n++) $html .= "<br/>";
				$html .= "<a href='index.php?tags=$tag'>$tag</a>\n";
				$result->MoveNext();
			}
			$result->Close();
			
			$html .= "</div>";

			return $html;
		}
	}

	function get_priority() {
		return 60;
	}
}
?>
