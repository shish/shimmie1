<?php
$base = "http://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"];
//		<base href='$base'>

echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>$title</title>
		<link rel="stylesheet" href="style.css" type="text/css">
		<script src="scripts/libshish.js" type="text/javascript"></script>
		<script src="scripts/sidebar.js" type="text/javascript"></script>
		<script src="scripts/shimmie.js" type="text/javascript"></script>
	</head>

	<body>
		<h1>$title</h1>
EOD;
?>
