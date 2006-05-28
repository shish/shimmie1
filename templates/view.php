<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Image</h3>
	<center>
		<img onclick="scale(this)" src="$image->link" alt="$image->tags" $scale>
		<br/>Short link: <input type="text" size="50" value="$baseurl$image->slink">
		<br/>Uploaded by $image->owner
	</center>
</div>
EOD;
require_once "templates/footer.php";
?>
