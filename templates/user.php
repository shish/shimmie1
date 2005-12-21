<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">
	<h3 onclick="toggle('navigate')">Navigate</h3>
	<div id="navigate">
		<a href="index.php">Index</a>
		<div id="search">
			<p><form action='index.php' method='GET'>
				<input name='tags' type='text' value="$searchString">
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
	<h3>Boad Settings</h3>
	Things will go here as soon as there's something to set...
</div>
EOD;
require_once "templates/footer.php";
?>
