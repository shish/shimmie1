<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('help')">Help</h3>
	<div id="help">
		Shimmie should use MySQL, PostgreSQL, and SQLite as databases, but
		development focuses on MySQL (and is the only one which works at all
		right now...)
	</div>
</div>

<div id="body">
	<h3>Fill in this form</h3>
	<form id="instform" name="instform" action="$target" method="POST">
		<table style="width: 300px;">
			$configOptions
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
