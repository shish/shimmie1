<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Fill in this form</h3>
	<form action="setup.php" method="POST">
		<table style="width: 800px;" border="1">
			<tr>
				<td><table style="width: 400px;">$configOptions1</table></td>
				<td><table style="width: 400px;">$configOptions2</table></td>
			</tr>
			<tr><td colspan="2"><input type="hidden" name="action" value="set"><input type="submit" value="Set Settings"></td></tr>
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
