<?php
require_once "templates/header.php";

if($searchString == null) {
	$searchString = "Search";
}

echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		$pageNav
		<p><form action="index.php" method="GET">
			<input id="searchBox" name="tags" type="text" value="$searchString"
				onFocus="cleargray(this, 'Search')"
				onBlur="setgray(this, 'Search')"
				>
			<input type="submit" value="Find" style="display: none;">
		</form>
	</div>

	$uploadBlock
	$commentBlock
	$popularBlock
	$userBlock
	$adminBlock
</div>

<div id="body">
	<h3>List</h3>
	<table>$content</table>
	<div id="pagelist">$pageNav2</div>
</div>
EOD;
require_once "templates/footer.php";
?>
