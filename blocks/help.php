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
			<div id="help">
				Explanations for each option are given on
				<a href='http://trac.shishnet.org/shimmie/wiki/Settings'>the shimmie wiki</a>
			</div>
			<script>
			function setHelp(text) {
				if(text != "") document.getElementById('help').innerHTML = text;
			}
			</script>
EOD;
		}
	}

	function get_priority() {
		return 20;
	}
}
?>
