<?php
/*
 * navigate.php (c) Shish 2006
 *
 * Go back & forth, to the index, and search
 */

global $searchString;

if($searchString == null) {
	$searchString = "Search";
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
	global $cpage, $vprev, $vnext, $htmlSafeTags, $morePages;

	$pageNav =
		($cpage>0   ? "<a href='index.php?page=$vprev&tags=$htmlSafeTags'>Prev</a> | " : "Prev | ").
		"<a href='index.php'>Index</a> | ".
		($morePages ? "<a href='index.php?page=$vnext&tags=$htmlSafeTags'>Next</a>" : "Next");
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


$blocks[10] .= <<<EOD
	<h3 id="navigate-toggle" onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		$pageNav
		<p><form action="index.php" method="GET">
			<input id="searchBox" name="tags" type="text" value="$searchString" autocomplete="off">
			<input type="submit" value="Find" style="display: none;">
		</form>
		<div id="search_completions"></div>
	</div>
EOD;
?>
