<?php
require_once "templates/header.php";

echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Board Settings</h3>
	Things will go here as soon as there's something to set...
	<h3>User Stats</h3>
	Images uploaded: $image_count
	<br>Comments made: $comment_count
</div>
EOD;

require_once "templates/footer.php";
?>
