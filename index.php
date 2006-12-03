<?php
/*
 * index.php (c) Shish 2005, 2006
 *
 * View the recently posted images, or search them
 */

require_once "header.php";

header("X-Shimmie-Status: OK - Index Successful");

$images_per_page = $config['index_images'];


/*
 * Get the page to look at -- assume the front page if one
 * isn't specified
 */
if(!is_null($_GET['page'])) {
	$page = int_escape($_GET['page']);
}
else {
	$page = 0;
}

/*
 * Do the SQL to get the images to display; generate a search query
 * if a search is being done, else do a simple select. Also, count
 * the total number of results so we can put a "jump to page" bar
 * at the bottom.
 *
 * FIXME: Find a better way of counting results than running the
 * query a second time. SELECT COUNT(*) doesn't work for more
 * complex queries. Rather than running a limited query for display
 * and an unlimited one to count, we could just do the unlimited
 * one and put the limit in PHP, but that's still not as neat as
 * doing a limited query and a separate count()...

  searched for, score = 1; if the tag is one the user has blocked,
  score = -1. so the user searches for "tag1 not tag2", which gives us:
  objectName,tagValue,score
  object1,tag1,1
  object1,tag2,-1
  object2,tag1,1

  then we group the rows by objectName, and sum the score column:
  objectName,tagValue,score
  object1,tag1,0
  object2,tag1,1

  then do "having score = $number of tags searched for" ("having"
  works like "where", but it works with group columns, eg "sum" or "average"):
  objectName,tagValue,score
  object2,tag1,1

  which then gives you what you searched for. You can also set the
  threshold lower, eg show things that match at least one tag (having
  score > 0), or you can combine that with the tag block with (if tag
  = one blocked, score = -99999)
  
 */
 
$h_title = ""; // searches
$h_subtitle = ""; // negators
$h_tag_list = ""; // both

if($_GET['tags']) {
	$tags = explode(" ", str_replace("  ", " ", $_GET["tags"]));

	if(count($tags) > 1) $moreHtmlTags = "<meta name='robots' content='noindex,follow'>";
	
	$search_sql = "";

	$tnum = 0;
	foreach($tags as $tag) {
		if($tag[0] == '-') continue;
		$s_tag = sql_escape($tag);
		$h_tag = html_escape($tag);
		$search_sql .= ($tnum == 0 ? "(" : "OR");
		$search_sql .= " tag LIKE '$s_tag' ";
		$h_title    .= " $h_tag";
		$h_tag_list .= " $h_tag";
		$tnum++;
	}
	$min_score = $tnum;
	if($tnum > 0) $search_sql .= ") ";

	$tnum = 0;
	foreach($tags as $tag) {
		if($tag[0] != '-') continue;
		$tag = preg_replace("/^-/", "", $tag);
		$s_tag = sql_escape($tag);
		$h_tag = html_escape($tag);
		$search_sql .= ($tnum == 0 ? "-(" : "OR");
		$search_sql .= " tag LIKE '$s_tag' ";
		$h_subtitle .= ($tnum == 0 ? "Ignoring:" : ",");
		$h_subtitle .= " $h_tag";
		$h_tag_list .= " -$h_tag";
		$tnum++;
	}
	if($tnum > 0) $search_sql .= ") ";
	$h_tag_list = trim($h_tag_list);

	$full_query = <<<EOD
		SELECT 
			shm_images.id AS id, shm_images.hash AS hash, shm_images.ext AS ext, 
			SUM($search_sql) AS score
		FROM shm_tags
		LEFT JOIN shm_images ON image_id=shm_images.id
		GROUP BY shm_images.id 
		HAVING score >= $min_score
EOD;
}
else {
	$full_query = "SELECT * FROM shm_images";
}

$total_images = sql_num_rows(sql_query($full_query));

if($page == 0) {
	$list_query = $full_query . " ORDER BY shm_images.id DESC LIMIT $images_per_page";
}
else {
	if($config['index_invert']) {
		$start = (($page-1) * $images_per_page);
	}
	else {
		$start = $total_images - ($page * $images_per_page);
	}
	if($start < 0) {
		$start_pad = -$start;
		$images_per_page -= $start_pad;
		$start = 0;
	}
	$list_query = $full_query . " ORDER BY shm_images.id DESC LIMIT $start,$images_per_page";
}


if($total_images == 0) {
	header("X-Shimmie-Status: Error - No Results");
}
else {
	header("X-Shimmie-Status: OK - Search Successful");
}


function get_table_div($num, $width, $content) {
	$html = "";
	
	if($num % $width == 0) {
		$html .= "\n<tr>\n";
	}
	$html .= "\t<td>$content</td>\n";
	if($i % $width == $width-1) {
		$html .= "\n</tr>\n";
	}
	return $html;
}

/*
 * Generate the imageTable to go in the main part of the page
 *
 * FIXME: alt attribute on the image, preferably without running a 
 * sepatate query for every image. MySQL's group_concat is what we
 * want, but that's a 4.1 feature, and debian stable is still 4.0.
 */
function query_to_image_table($query, $start_pad) {
	global $config;

	$imageTable = "<table>\n";
	$i = 0;
	$width = 3;
	$dir_thumbs = $config['dir_thumbs'];
	$list_result = sql_query($query);
	
	for($j=0; $j<$start_pad; $j++) {
		$imageTable .= get_table_div($i++, $width, "&nbsp;");
	}
	
	while($row = sql_fetch_row($list_result)) {
		$image_id = $row['id'];
		$hash = $row['hash'];
		$h_filename = html_escape($row['filename']);

		# FIXME: Do this better
		$h_tags = "";
		$tags_result = sql_query("SELECT * FROM shm_tags WHERE image_id=$image_id");
		while($row = sql_fetch_row($tags_result)) {$h_tags .= html_escape($row['tag'])." ";}

		$imageTable .= get_table_div($i++, $width, "<a href='view.php?image_id=$image_id'>".
				"<img src='$dir_thumbs/$image_id.jpg' alt='$h_filename' title='$h_tags'></a>");
	}

	$imageTable .= "</table>";

	return $imageTable;
}



/*
 * Calculate navigation bars ("prev | next" for the nav bar,
 * "prev | page numbers | next" in the footer)
 */
function gen_page_link($target, $current_page, $tags) {
	$paginator = "";
	if($target == $current_page) $paginator .= "<b>";
	$paginator .= "<a href='index.php?page=$target$tags' style='width: 20px;'>$target</a>";
	if($target == $current_page) $paginator .= "</b>";
	$paginator .= " | ";
	return $paginator;
}
function gen_paginator($current_page, $total_pages, $h_tag_list) {
	global $config;
	
	if(strlen($h_tag_list) > 0) {
		$tags = "&tags=$h_tag_list";
	}

	$given_page = $current_page;
	
	if($current_page == 0 && !$config['index_invert']) {
		$current_page = $total_pages;
	}
	$next = $current_page + 1;
	$prev = $current_page - 1;

	$paginator = "";
	
	if($current_page == $total_pages) {
		$next_html = "Next";
	}
	else {
		$next_html = "<a href='index.php?page=$next$tags'>Next</a>";
	}
	
	$start = $current_page-5 > 1 ? $current_page-5 : 1;
	$end = $start+10 < $total_pages ? $start+10 : $total_pages;
	
	if(!$config['index_invert']) {
		$tmp = $start;
		$start = $end;
		$end = $tmp;
	} 
	
	foreach(range($start, $end) as $i) {
		$paginator .= gen_page_link($i, $given_page, $tags);
	} 

	if($current_page <= 1 || $total_pages <= 1) {
		$prev_html = "Prev";
	}
	else {
		$prev_html = "<a href='index.php?page=$prev$tags'>Prev</a>";
	}

	if($config['index_invert']) {
		return "$prev_html | $paginator $next_html";
	}
	else {
		return "$next_html | $paginator $prev_html";
	}
}

$total_pages = ceil($total_images / $config['index_images']);
$paginator = gen_paginator($page, $total_pages, $h_tag_list);


/*
 * If we're running a search, show the search terms.
 * If not, show the title string.
 */
function get_title_html($title, $page) {
	global $config;
	if(strlen($title) == 0) {
		$title = html_escape($config['title']);
	}
	if($page != 0) {
		$title .= " / $page";
	}
	return $title;
}

$title = get_title_html($h_title, $page);
$subtitle = $h_subtitle;
$image_table = query_to_image_table($list_query, $start_pad);
$blocks = get_blocks_html("index");
require_once "templates/index.php";
?>
