<?php
require_once "templates/header.php";

$image_rate = (int)($image_count / $days_old);
$comment_rate = (int)($comment_count / $days_old);

echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Board Settings</h3>
	Things will go here as soon as there's something to set...
	<h3>User Stats</h3>
	<br>Images uploaded: $image_count ($image_rate / day)
	<br>Comments made: $comment_count ($comment_rate / day)
</div>
EOD;

require_once "templates/footer.php";
?>
