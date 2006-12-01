<?php
require_once "templates/header.php";

echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>List</h3>
	$image_table
	<div id="pagelist">$paginator</div>
</div>
EOD;

require_once "templates/footer.php";
?>
