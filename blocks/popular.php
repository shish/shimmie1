<?php
/*
 * popular.php (c) Shish 2005, 2006
 *
 * List popular tags
 */

class popular extends block {
	function get_html($pageType) {
		global $htmlSafeTags, $config, $db;
	
		if(($pageType == "index") && (strlen($htmlSafeTags) == 0)) {
			$query = "
				SELECT tag, COUNT(image_id) AS count
				FROM tags
				GROUP BY tag
				ORDER BY count DESC
				LIMIT ?
			";

			$html .= "<h3 id='popular-toggle' onclick=\"toggle('popular')\">Popular Tags</h3>\n";
			$html .= "<div id='popular'>\n";

			$n = 0;
			$result = $db->Execute($query, Array($config['popular_count']));
			while(!$result->EOF) {
				$row = $result->fields;
				$tag = html_escape($row['tag']);
				$count = $row['count'];
				if($n++) $html .= "<br/>";
				$html .= "<a href='index.php?tags=$tag'>$tag ($count)</a>\n";
				if($htmlSafeTags) $html .= "<a href='index.php?tags=$htmlSafeTags+$tag'>(+)</a>\n";
				$result->MoveNext();
			}
			$result->Close();

			$html .= "<p><a href='tags.php'>Full List &gt;&gt;&gt;</a>\n";
			$html .= "</div>";

			return $html;
		}
	}

	function get_priority() {
		return 60;
	}
}
?>
