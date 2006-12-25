<?php
if(empty($base_href)) {
	$base_href = preg_replace(
		'#[^/]+$#', '',
		"http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']
	);
}

$scripts = glob("scripts/*.js");
$script_html = "";
foreach($scripts as $script) {
	$script_html .= "\t\t<script src='$script' type='text/javascript'></script>\n";
}

if(!empty($subtitle)) {
	$subtitle_html = "<div id='subtitle'>$subtitle</div>";
}

if(!empty($navigation)) {
	$navblock .= "<h3 onclick=\"toggle('navigate')\">Navigate</h3>\n";
	$navblock .= "<div id='navigate'>$navigation</div>\n";
}

if(!empty($help)) {
	$helpblock .= "<h3 onclick=\"toggle('help')\">Help</h3>\n";
	$helpblock .= "<div id='help'>$help</div>\n";
}

if(!empty($message) || !empty($data)) {
	if(!empty($data)) {
		$databox = "<p><textarea cols='80' rows='10'>$data</textarea>";
	}
	$body[$heading] = "
			$message
			$databox
	";
}

foreach($body as $heading => $content) {
	if(is_string($heading)) $body_html .= "<h3>$heading</h3>";
	$body_html .= $content;
}




if(empty($heading)) $heading = $title;

global $version;

echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<base href='$base_href'>
		<title>$title</title>
		<link rel="stylesheet" href="$base_href/style.css" type="text/css">
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
			$body_html
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
