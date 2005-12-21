<?php
require_once "templates/header.php";

/*
 * Only show the data box if there is any data to go in it
 */
if(!is_null($data)) $databox = "<p><textarea cols=\"50\" rows=\"10\">$data</textarea>";
else $databox = "";

echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('help')">Help</h3>
	<div id="help">
		This is a generic error page
	</div>
</div>

<div id="body">
	<h3>Error</h3>
	$message
	$databox
</div>
EOD;
require_once "templates/footer.php";
?>
