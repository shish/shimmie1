<?php

$base = "http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
//		<base href='$base'>

$scripts = glob("scripts/*.js");
$scripthtml = "";
foreach($scripts as $script) {
	$scripthtml .= "<script src='$script' type='text/javascript'></script>\n";
}

echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>$title</title>
		<link rel="stylesheet" href="style.css" type="text/css">
		$scripthtml
	</head>

	<body>
		<h1>$title</h1>
EOD;

unset($base);
unset($scripts);
unset($scripthtml);
?>
