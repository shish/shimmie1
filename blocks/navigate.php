<?php
/*
 * navigate.php (c) Shish 2006
 *
 * Go back & forth, to the index, and search
 */

class navigate extends block {
	function get_title() {
		return "Navigate";
	}

	function get_html($pageType) {
		global $h_tag_list;

		if($h_tag_list == null) {
			$search_string = "Search";
		}
		else {
			$search_string = $h_tag_list;
		}

		if(($pageType == "user") || ($pageType == "admin") || ($pageType == "setup")) {
			$pageNav = "<a href='index.php'>Index</a>";
		}
		else if($pageType == "tags") {
			$pageNav = "
				<a href='index.php'>Index</a> | 
				<a href='tags.php?mode=alphabet'>Alphabetical</a> | 
				<a href='tags.php?mode=popular'>Popularity</a> | 
				<a href='tags.php?mode=map'>Map</a>
			";
		}
		else if($pageType == "index") {
			global $page, $total_pages, $h_tag_list, $config;
			
			if(strlen($h_tag_list) > 0) {
				$tags = "&tags=$h_tag_list";
			}

			if($page == 0 && !$config['index_invert']) {$current_page = $total_pages;}
			else if($page == 0) {$current_page = 1;}
			else {$current_page = $page;}
			
			$next = $current_page + 1;
			$prev = $current_page - 1;
		
			if($current_page >= $total_pages) {$next_html .= "Next";}
			else {$next_html .= "<a href='index.php?page=$next$tags'>Next</a>";}
	
			$index_html .= "<a href='index.php'>Index</a>";

			if($current_page <= 1 || $total_pages <= 1) {$prev_html .= "Prev";}
			else {$prev_html .= "<a href='index.php?page=$prev$tags'>Prev</a>";}

			if($config['index_invert']) {
				$pageNav = "$prev_html | $index_html | $next_html";
			}
			else {
				$pageNav = "$next_html | $index_html | $prev_html";
			}
		}
		else if($pageType == "view") {
			global $image, $config, $db;

			$lowerid  = $db->GetOne("SELECT id FROM images WHERE id < ? ORDER BY id DESC", Array($image->id));
			$higherid = $db->GetOne("SELECT id FROM images WHERE id > ? ORDER BY id ASC ", Array($image->id));

			if($config['index_invert']) {
				$nextid = $lowerid;
				$previd = $higherid;
			}
			else {
				$nextid = $higherid;
				$previd = $lowerid;
			}

			$prev_html = ($previd ? "<a href='view.php?image_id=$previd'>Prev</a>" : "Prev");
			$index_html = "<a href='index.php'>Index</a>";
			$next_html = ($nextid ? "<a href='view.php?image_id=$nextid'>Next</a>" : "Next");
				
			if($config['index_invert']) {
				$pageNav = "$prev_html | $index_html | $next_html";
			}
			else {
				$pageNav = "$next_html | $index_html | $prev_html";
			}
		}
		if($pageType == "index" || $pageType == "view") {
			$searchHtml = "
				<p><form action='index.php' method='GET'>
					<input id='searchBox' name='tags' type='text'
							value='$search_string' autocomplete='off'>
					<input type='submit' value='Find' style='display: none;'>
				</form>
				<div id='search_completions'></div>
			";
		}

		return $pageNav . $searchHtml;
	}

	function get_priority() {
		return 10;
	}
}
?>
