<?php
/*
 * refine.php (c) Shish 2006
 *
 * Refine a search
 */

class refine extends block {
	function get_html($pageType) {
		global $h_tag_list, $config;
	
		if(($pageType == "index") && (strlen($_GET["tags"]) > 0)) {
			$s_tag_list = "";

			$n = 0;
			foreach(explode(" ", $_GET["tags"]) as $tag) {
				if($tag[0] != '-') {
					if($n++) $s_tag_list .= ", ";
					$s_tag = sql_escape($tag);
					$s_tag_list .= "'$s_tag'";
				}
			}

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
			$related_query = <<<EOD
				SELECT 
					tag,
					COUNT(image_id) AS count
				FROM shm_tags
				WHERE image_id IN (SELECT image_id FROM tags WHERE tag IN($s_tag_list) GROUP BY image_id)
				GROUP BY tag
				ORDER BY count DESC
				LIMIT $pop_count
EOD;
			$related_query_tables = <<<EOD
				SELECT COUNT(t2.image_id) AS count,t2.tag
				FROM
					tags AS t1,
					tags AS t2
				WHERE 
					t1.tag IN($s_tag_list)
					AND t1.image_id=t2.image_id
				GROUP BY t2.tag 
				ORDER BY count
				DESC LIMIT $pop_count;
EOD;
			$result = sql_query($related_query_tables);
			$n = 0;

			$popularBlock = "<h3 id=\"refine-toggle\" onclick=\"toggle('refine')\">Refine Search</h3>\n";
			$popularBlock .= "<div id='refine'>\n";
			while($row = sql_fetch_row($result)) {
				$tag = html_escape($row['tag']);
				if($n++) $popularBlock .= "<br/>";
				$untagged = trim(preg_replace("/-?$tag/", "", $h_tag_list));
				$popularBlock .= "<a href='index.php?tags=$tag'>$tag</a> (";
				$popularBlock .= "<a href='index.php?tags=$untagged+$tag' ".
				                 "title='add tag to the current search'>a</a>/";
				if($untagged != $h_tag_list) {
					$popularBlock .= "<a href='index.php?tags=$untagged' ".
					                 "title='remove tag from the current search'>r</a>/";
				}
				$popularBlock .= "<a href='index.php?tags=$untagged+-$tag' ".
				                 "title='subtract matching images from the results'>s</a>)\n";
			}
			$popularBlock .= "</div>";

			return $popularBlock;
		}
	}

	function get_priority() {
		return 30;
	}
}
?>
