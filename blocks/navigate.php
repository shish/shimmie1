<?php
/*
 * navigate.php (c) Shish 2006
 *
 * Go back & forth, to the index, and search
 */

class navigate extends block {
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
			$pageNav = <<<EOD
				<a href='index.php'>Index</a> | 
				<a href='tags.php?mode=alphabet'>Alphabetical</a> | 
				<a href='tags.php?mode=popular'>Popularity</a> | 
				<a href='tags.php?mode=map'>Map</a>
EOD;
		}
		else if($pageType == "index") {
			global $cpage, $vprev, $vnext, $h_tag_list, $morePages;

			$pageNav =
				($cpage>0   ? "<a href='index.php?page=$vprev&tags=$h_tag_list'>Prev</a> | " : "Prev | ").
				"<a href='index.php'>Index</a> | ".
				($morePages ? "<a href='index.php?page=$vnext&tags=$h_tag_list'>Next</a>" : "Next");
		}
		else if($pageType == "view") {
			global $image;

			$row = sql_fetch_row(sql_query("SELECT id FROM shm_images WHERE id < {$image->id} ORDER BY id DESC LIMIT 1"));
			$previd = $row ? $row['id'] : null;
			$row = sql_fetch_row(sql_query("SELECT id FROM shm_images WHERE id > {$image->id} ORDER BY id ASC  LIMIT 1"));
			$nextid = $row ? $row['id'] : null;

			$pageNav =
				($previd ? "<a href='view.php?image_id=$previd'>Prev</a> | " : "Prev | ").
				"<a href='index.php'>Index</a> | ".
				($nextid ? "<a href='view.php?image_id=$nextid'>Next</a>" : "Next");
		}

		return <<<EOD
	<h3 id="navigate-toggle" onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		$pageNav
		<p><form action="index.php" method="GET">
			<input id="searchBox" name="tags" type="text" value="$search_string" autocomplete="off">
			<input type="submit" value="Find" style="display: none;">
		</form>
		<div id="search_completions"></div>
	</div>
EOD;
	}

	function get_priority() {
		return 10;
	}
}
?>
