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
				Hover over an option for help
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
