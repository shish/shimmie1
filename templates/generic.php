<?php
require_once "templates/header.php";

if(!is_null($data)) {
	$databox = "<p><textarea cols='80' rows='10'>$data</textarea>";
}

if(!is_null($help)) {
	$helpblock .= "<h3 onclick=\"toggle('help')\">Help</h3>\n";
	$helpblock .= "<div id='help'>$help</div>\n";
}

if(!is_null($navigation)) {
	$navblock .= "<h3 onclick=\"toggle('navigate')\">Navigate</h3>\n";
	$navblock .= "<div id='navigate'>$navigation</div>\n";
}

if(is_null($heading)) $heading = $title;

echo <<<EOD
<div id="nav">
	$blocks
	$navblock
	$helpblock
</div>

<div id="body">
	<h3>$heading</h3>
	$message
	$databox
</div>
EOD;

require_once "templates/footer.php";
?>
