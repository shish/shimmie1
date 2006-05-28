<?php
require_once "templates/header.php";
echo <<<EOD
<div id="nav">$blocks</div>

<div id="body">
	<h3>Image</h3>
	<center>
		<img onclick="scale(this)" src="$img_link" alt="$img_tags" $scale>
		<br/>Short link: <input type="text" size="50" value="$baseurl$img_slink">
		<br/>Uploaded by $img_user
	</center>
</div>
EOD;
require_once "templates/footer.php";
?>
