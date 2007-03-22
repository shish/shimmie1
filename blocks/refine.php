<?php
/*
 * refine.php (c) Shish 2006
 *
 * Refine a search
 */

class refine extends block {
	function get_title() {
		return "Refine Search";
	}

	function get_html($pageType) {
		global $h_tag_list, $db;
	
		if(($pageType == "index") && (strlen($_GET["tags"]) > 0)) {
			$s_tag_list = "";

			$n = 0;
			foreach(explode(" ", $_GET["tags"]) as $tag) {
				if($tag[0] != '-') {
					if($n++) $s_tag_list .= ", ";
					$s_tag_list .= glob_to_sql($tag);
				}
			}

			if($s_tag_list == "") return null;

			$query = "
				SELECT COUNT(t2.image_id) AS count,t2.tag
				FROM
					tags AS t1,
					tags AS t2
				WHERE 
					t1.tag IN($s_tag_list)
					AND t1.image_id=t2.image_id
				GROUP BY t2.tag 
				ORDER BY count
				DESC LIMIT ?
			";
			
			$hp_tag_list = str_replace("%20", "+", $h_tag_list);
			$hp_tag_list = str_replace(" ", "+", $hp_tag_list);

			$n = 0;
			$result = $db->Execute($query, Array(get_config('popular_count')));
			while(!$result->EOF) {
				$row = $result->fields;
				$tag = html_escape($row['tag']);
				if($n++) $html .= "<br/>";
				$untagged = str_replace("++", "+", trim(preg_replace("/\\b-?{$tag}\\b/", '', $hp_tag_list), " \t\n\r\0\x0B+"));
				$html .= "<a href='index.php?tags=$tag'>$tag</a> (";
				$html .= "<a href='index.php?tags=$untagged+$tag' ".
				                 "title='add tag to the current search'>a</a>/";
				if($untagged != $hp_tag_list) {
					$html .= "<a href='index.php?tags=$untagged' ".
					                 "title='remove tag from the current search'>r</a>/";
				}
				$html .= "<a href='index.php?tags=$untagged+-$tag' ".
				                 "title='subtract matching images from the results'>s</a>)\n";
				$result->MoveNext();
			}
			$result->Close();
			
			return $html;
		}
	}

	function get_priority() {
		return 30;
	}
}
?>
