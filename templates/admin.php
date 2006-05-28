<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Things</h3>
	<form action="admin.php?action=replacetag" method="POST">
		<table style="width: 300px">
			<tr><td>Replace tag<td><input name='search' type='text'></tr>
			<tr><td>With<td><input name='replace' type='text'></tr>
			<tr><td colspan="2"><input type='submit' value='Replace'></td></tr>
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
