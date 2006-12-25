<?php
/*
 * help.php (c) Shish 2006
 *
 * Informational texts
 */

class help extends block {
	function get_title() {
		return "Help";
	}

	function get_html($pageType) {
		if($pageType == "setup") {
			return "
				Explanations for each option are given on
				<a href='http://trac.shishnet.org/shimmie/wiki/Settings'>the shimmie wiki</a>
			";
		}
	}

	function get_priority() {
		return 20;
	}
}
?>
