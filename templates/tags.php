<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>List</h3>
	$listMore
	<div id="taglist">$tlist</div>
</div>
EOD;
require_once "templates/footer.php";
?>
