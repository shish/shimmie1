<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Fill in this form</h3>
	<form action="setup.php" method="POST">
		<table style="width: 400px;">
			$configOptions

			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2"><input type="hidden" name="action" value="set"><input type="submit" value="Set Settings"></td></tr>
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
