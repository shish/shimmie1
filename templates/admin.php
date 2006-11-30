<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Mass Tag Edit</h3>
	<form action="admin.php?action=replacetag" method="POST">
		<table style="width: 300px">
			<tr><td>Replace tag<td><input name='search' type='text'></tr>
			<tr><td>With<td><input name='replace' type='text'></tr>
			<tr><td colspan="2"><input type='submit' value='Replace'></td></tr>
		</table>
	</form>
	
	<h3>IP Ban / Restore</h3>
	<form action="admin.php?action=addipban" method="POST">
		<table style="width: 300px">
			<tr><td>IP<td><input name='ip' type='text'></tr>
			<tr><td>Reason<td><input name='reason' type='text'></tr>
			<tr><td colspan="2"><input type='submit' value='Ban'></td></tr>
		</table>
	</form>
	$banned_ip_list

	<h3>Add Folder</h3>
	<form action="admin.php?action=bulkadd" method="POST">
		<table style="width: 300px">
			<tr><td colspan="2">subfolder names will be used as tags</td></tr>
			<tr><td>Folder<td><input name='dir' type='text'></tr>
			<tr><td colspan="2"><input type='submit' value='Add'></td></tr>
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
