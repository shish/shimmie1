<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('help')">Help</h3>
	<div id="navigate">
		Extra notes'll go here

		<p>Make sure the web server can write to the
		directories specified in "images" and "thumbnails"
	</div>

	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		<a href="index.php">index</a>
		<div id="search">
			<p><form action='index.php' method='GET'>
				<input name='tags' type='text'>
				<input type='submit' value='Find'>
			</form>
		</div>
	</div>

	$commentBlock
	$popularBlock
	$userBlock
	$adminBlock
</div>

<div id="body">
	<h3>Fill in this form</h3>
	<form action="setup.php" method="POST">
		<table style="width: 300px;">
			$configOptions

			<tr><td colspan="2">&nbsp;</td></tr>
			<tr><td colspan="2"><input type="hidden" name="action" value="set"><input type="submit" value="Set Settings"></td></tr>
		</table>
	</form>
</div>
EOD;
require_once "templates/footer.php";
?>
