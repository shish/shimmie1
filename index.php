<?php
/*
 * index.php (c) Shish 2005, 2006
 *
 * View the recently posted images, or search them
 */

require_once "header.php";

header("X-Shimmie-Status: OK - Index Successful");

$imagesPerPage = $config['index_images'];


/*
 * Get the page to look at -- assume the front page if one
 * isn't specified
 */
if(!is_null($_GET['page'])) {
	$vpage = int_escape($_GET['page']);
}
else {
	$vpage = 1;
}


// visible page numbers start at 1, code page numbers start at 0
$vnext = $vpage + 1;
$vprev = $vpage - 1;
$cpage = $vpage - 1;
$start = $cpage * $imagesPerPage;


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

	$list_query = <<<EOD
		SELECT 
			images.id AS id, images.hash AS hash, images.ext AS ext, 
			SUM($search_sql) AS score
		FROM shm_tags
		LEFT JOIN shm_images ON image_id=shm_images.id
		GROUP BY image_id 
		HAVING score >= $min_score
	    ORDER BY image_id DESC 
	    LIMIT $start,$imagesPerPage
EOD;

	$total_query = <<<EOD
		SELECT 
			*,
			SUM($search_sql) AS score
		FROM shm_tags
		LEFT JOIN shm_images ON image_id=shm_images.id
		GROUP BY image_id 
		HAVING score >= $min_score
EOD;
}
else {
	$list_query = "SELECT * FROM shm_images ORDER BY id DESC LIMIT $start,$imagesPerPage";
	$total_query = "SELECT * FROM shm_images";
}
$list_result = sql_query($list_query);

$total_result = sql_query($total_query);
$totalImages = sql_num_rows($total_result);

if($totalImages == 0) {
	header("X-Shimmie-Status: Error - No Results");
}
else {
	header("X-Shimmie-Status: OK - Search Successful");
}

/*
 * Generate the imageTable to go in the main part of the page
 *
 * FIXME: alt attribute on the image, preferably without running a 
 * sepatate query for every image. MySQL's group_concat is what we
 * want, but that's a 4.1 feature, and debian stable is still 4.0.
 */
$imageTable = "";
$i = 0;
$width = 3;
$dir_thumbs = $config['dir_thumbs'];
while($row = sql_fetch_row($list_result)) {
	$image_id = $row['id'];
	$hash = $row['hash'];
	$h_filename = html_escape($row['filename']);

	# FIXME: Do this better
	$h_tags = "";
	$tags_result = sql_query("SELECT * FROM shm_tags WHERE image_id=$image_id");
	while($row = sql_fetch_row($tags_result)) {$h_tags .= html_escape($row['tag'])." ";}
	

	if($i%$width==0) $imageTable .= "\n<tr>\n";
	$imageTable .= "\t<td>".
		"<a href='view.php?image_id=$image_id'><img src='$dir_thumbs/$image_id.jpg' alt='$h_filename' title='$h_tags'></a>".
		"</td>\n";
	if($i%$width==$width-1) $imageTable .= "\n</tr>\n";
	$i++;
}


/*
 * Calculate navigation bars ("prev | next" for the nav bar,
 * "prev | page numbers | next" in the footer)
 */
$morePages = (($cpage+1)*$config['index_images'] < $totalImages);

$paginator = ($cpage>0 ? "<a href='index.php?page=$vprev&tags=$h_tag_list'>Prev</a> | " : "Prev | ");
for($i=0, $j=$cpage-5; $i<11; $j++) {
	if($j > 0) {
		if(($j-1)*$config['index_images'] < $totalImages) {
			$paginator .= "<a href='index.php?page=$j&tags=$h_tag_list' style='width: 20px;'>$j</a> | ";
		}
		$i++;
	}
}
$paginator .= ($morePages ? "<a href='index.php?page=$vnext&tags=$h_tag_list'>Next</a>" : "Next");


/*
 * If we're running a search, show the search terms.
 * If not, show the version string.
 */
if($_GET['tags']) $title = "$h_title / $vpage";
else $title = html_escape($config['title'])." / $vpage";

$subtitle = $h_subtitle;

/*
 * Finally display the page \o/
 */
$blocks = get_blocks_html("index");
require_once "templates/index.php";
?>
