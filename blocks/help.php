<?php
/*
 * help.php (c) Shish 2006
 *
 * Informational texts
 */

if($pageType == "setup") {
	$blocks[20] = <<<EOD
	<h3 onclick="toggle('help')">Help</h3>
	<div id="navigate">
		Extra notes'll go here

		<p>Make sure the web server can write to the
		directories specified in "images" and "thumbnails"
	</div>
EOD;
}
?>
