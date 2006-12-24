<?php
require_once "templates/header.php";

if(!is_null($data)) $databox = "<p><textarea cols=\"80\" rows=\"10\">$data</textarea>";
else $databox = "";

echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		<a href="index.php">index</a>
	</div>
</div>

<div id="body">
	<h3>$title</h3>
	$message
	$databox
</div>
EOD;
require_once "templates/footer.php";
?>
