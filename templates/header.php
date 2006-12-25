<?php
$scripts = glob("scripts/*.js");
$scripthtml = "";
foreach($scripts as $script) {
	$scripthtml .= "\t\t<script src='$script' type='text/javascript'></script>\n";
}

if($baseurl) {
	$base_html = "<base href='$baseurl'>";
}


echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		$base_html
		<title>$title</title>
		<link rel="stylesheet" href="style.css" type="text/css">
$scripthtml
		$extra_headers
	</head>

	<body>
		<h1>$title</h1>
EOD;
if($subtitle) {
	echo "<div id='subtitle'>$subtitle</div>";
}

unset($base_html);
unset($scripts);
unset($scripthtml);
?>
