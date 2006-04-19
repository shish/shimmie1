<?php
/*
 * navigate.php (c) Shish 2006
 *
 * Go back & forth, to the index, and search
 */


if(defined(getNav)) {$pageNav = getNav();}
else {$pageNav = "";}

if($searchString == null) {
	$searchString = "Search";
}

$navBlock = <<<EOD
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		$pageNav
		<p><form action="index.php" method="GET">
			<input id="searchBox" name="tags" type="text" value="$searchString" autocomplete="off">
			<input type="submit" value="Find" style="display: none;">
		</form>
		<div id="search_completions"></div>
	</div>
EOD
?>
