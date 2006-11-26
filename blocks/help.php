<?php
/*
 * help.php (c) Shish 2006
 *
 * Informational texts
 */

class help extends block {
	function get_html($pageType) {
		if($pageType == "setup") {
			return <<<EOD
			<h3 id="help-toggle" onclick="toggle('help')">Help</h3>
			<div id="navigate">
				Extra notes'll go here
		
				<p>Make sure the web server can write to the
				directories specified in "images" and "thumbnails"
			</div>
EOD;
		}
	}

	function get_priority() {
		return 20;
	}
}
?>
