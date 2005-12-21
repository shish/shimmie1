<?php
/*
 * index.php (c) Shish 2005
 *
 * view the recently posted images, or search them
 */

require_once "header.php";

$imagesPerPage = $config['index_images'];


/*
 * Get the page to look at -- assume the front page if one
 * isn't specified
 */
if($_SERVER['PATH_INFO'] && is_numeric(substr($_SERVER['PATH_INFO'], 1))) {
	$vpage = (int)substr($_SERVER['PATH_INFO'], 1);
}
else if(!is_null($_GET['page'])) {
	$vpage = (int)$_GET['page'];
}
else {
	$vpage = 1;
}


// visible page numbers start at 1, code page numbers start at 0
$vnext = $vpage + 1;
$vprev = $vpage - 1;
$cpage = ($vpage-1);
$start = $cpage*$imagesPerPage;


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
 */
$searchString = "";

$htmlSafeTags = htmlentities($_GET['tags']);
$sqlSafeTags = addslashes($_GET['tags']);

if($_GET['tags']) {
	$tags = explode(" ", $sqlSafeTags);
	
	$search_sql = "";
	$tnum = 0;
	foreach($tags as $tag) {
		$searchString .= " $tag";
		if($tnum == 0) $search_sql .= "LIKE '$tag' ";
		else $search_sql .= "OR tag LIKE '$tag' ";
		$tnum++;
	}
	$searchString = trim($searchString);

	$tagCount = count($tags);

	$list_query = <<<EOD
		SELECT 
			*,
			COUNT(tag) AS count
		FROM shm_tags
		LEFT JOIN shm_images ON image_id=shm_images.id
		WHERE tag $search_sql 
		GROUP BY image_id 
		HAVING count = $tagCount
	    ORDER BY image_id DESC 
	    LIMIT $start,$imagesPerPage
EOD;
	$total_query = <<<EOD
		SELECT 
			*,
			COUNT(tag) AS count
		FROM shm_tags
		LEFT JOIN shm_images ON image_id=shm_images.id
		WHERE tag $search_sql 
		GROUP BY image_id 
		HAVING count = $tagCount
EOD;
}
else {
	$list_query = "SELECT * FROM shm_images ORDER BY id DESC LIMIT $start,$imagesPerPage";
	$total_query = "SELECT * FROM shm_images";
}
$list_result = sql_query($list_query);

$total_result = sql_query($total_query);
$totalImages = sql_num_rows($total_result);


/*
 * Generate the content to go in the main part of the page
 *
 * FIXME: alt attribute on the image, preferably without running a 
 * sepatate query for every image. MySQL's group_concat is what we
 * want, but that's a 4.1 feature, and debian stable is still 4.0.
 */
$content = "";
$i = 0;
$width = 3;
$dir_thumbs = $config['dir_thumbs'];
while($row = sql_fetch_row($list_result)) {
	$image_id = $row['id'];
	$hash = $row['hash'];
	$filename = htmlentities($row['filename']);

	if($i%$width==0) $content .= "\n<tr>\n";
	$content .= "\t<td>".
		"<a href='view.php?image_id=$image_id'><img src='$dir_thumbs/$hash.jpg' alt='$filename'></a>".
		"</td>\n";
	if($i%$width==$width-1) $content .= "\n</tr>\n";
	$i++;
}


/*
 * Calculate navigation bars ("prev | next" for the nav bar,
 * "prev | page numbers | next" in the footer)
 */
$morePages = (($cpage+1)*$config['index_images'] < $totalImages);

$pageNav = ($cpage>0   ? "<a href='index.php?page=$vprev&tags=$htmlSafeTags'>Prev</a> | " : "Prev | ").
           "<a href=\"index.php\">Index</a> | ".
		   ($morePages ? "<a href='index.php?page=$vnext&tags=$htmlSafeTags'>Next</a>" : "Next");

$pageNav2 = ($cpage>0 ? "<a href='index.php?page=$vprev&tags=$htmlSafeTags'>Prev</a> | " : "Prev | ");
for($i=0, $j=$cpage-5; $i<11; $j++) {
	if($j > 0) {
		if(($j-1)*$config['index_images'] < $totalImages) {
			$pageNav2 .= "<a href='index.php?page=$j&tags=$htmlSafeTags' style='width: 20px;'>$j</a> | ";
		}
		$i++;
	}
}
$pageNav2 .= ($morePages ? "<a href='index.php?page=$vnext&tags=$htmlSafeTags'>Next</a>" : "Next");


/*
 * If we're running a search, show the search terms.
 * If not, show the version string.
 */
if($_GET['tags']) $title = "$htmlSafeTags / $vpage";
else $title = "$version / $vpage";


/*
 * Finally display the page \o/
 */
require_once "templates/index.php";
?>
