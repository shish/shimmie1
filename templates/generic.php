<?php
if($baseurl) {
	$base_html = "<base href='$baseurl'>";
}

$scripts = glob("scripts/*.js");
$script_html = "";
foreach($scripts as $script) {
	$script_html .= "\t\t<script src='$script' type='text/javascript'></script>\n";
}

if(!is_null($subtitle)) {
	$subtitle_html = "<div id='subtitle'>$subtitle</div>";
}

if(!is_null($navigation)) {
	$navblock .= "<h3 onclick=\"toggle('navigate')\">Navigate</h3>\n";
	$navblock .= "<div id='navigate'>$navigation</div>\n";
}

if(!is_null($help)) {
	$helpblock .= "<h3 onclick=\"toggle('help')\">Help</h3>\n";
	$helpblock .= "<div id='help'>$help</div>\n";
}

if(!is_null($data)) {
	$databox = "<p><textarea cols='80' rows='10'>$data</textarea>";
}

if(is_null($heading)) $heading = $title;

global $version;

echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		$base_html
		<title>$title</title>
		<link rel="stylesheet" href="style.css" type="text/css">
		$script_html
		$extra_headers
	</head>

	<body>
		<h1>$title</h1>
		$subtitle_html
		
		<div id="nav">
			$blocks
			$navblock
			$helpblock
		</div>

		<div id="body">
			<h3>$heading</h3>
			$message
			$databox
		</div>

		<div id="footer">
			<hr>
			Images &copy; their respective owners,
			<a href="http://trac.shishnet.org/shimmie/">$version</a> &copy; 
			<a href="http://www.shishnet.org/">Shish</a> 2005 - 2006,
			based on the <a href="http://danbooru.donmai.us/">Danbooru</a> concept.
		</div>
	</body>
</html>
EOD;
?>
