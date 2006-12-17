<?php

$base = get_base_href();
$scripts = glob("scripts/*.js");
$scripthtml = "";
foreach($scripts as $script) {
	$scripthtml .= "\t\t<script src='$script' type='text/javascript'></script>\n";
}


echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<base href="$base">
		<title>$title</title>
		<link rel="stylesheet" href="style.css" type="text/css">
$scripthtml
		$moreHtmlHeaders
	</head>

	<body>
		<h1>$title</h1>
EOD;
if($subtitle) {
	echo "<div id='subtitle'>$subtitle</div>";
}

unset($base);
unset($scripts);
unset($scripthtml);
?>
