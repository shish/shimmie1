<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('help')">Help</h3>
	<div id="help">
		Shimmie is developed with MySQL, and support
		for it is included. Other databases may work,
		but you'll need to add the appropriate ADOdb
		drivers yourself.
	</div>
</div>

<div id="body">
	<h3>Fill in this form</h3>
	<form id="instform" name="instform" action="$target" method="POST">
		<table style="width: 400px;">
			$configOptions
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
