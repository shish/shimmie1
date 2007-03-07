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

foreach($blocks as $heading => $content) {
	if(!empty($content)) {
		$blocks_html .= "<h3 id='$heading-toggle' onclick=\"toggle('$heading')\">$heading</h3>\n";
		$blocks_html .= "<div id='$heading'>$content</div>\n";
	}
}

foreach($body as $heading => $content) {
	if(is_string($heading)) $body_html .= "<h3>$heading</h3>";
	$body_html .= $content;
}




if(get_config('debug_enabled')) {
	if(function_exists('memory_get_usage')) {
		$i_mem = sprintf("%5.2f", ((memory_get_usage()+512)/1024)/1024);
	}
	else {
		$i_mem = "???";
	}
	$ru = getrusage();
	$i_utime = sprintf("%5.2f", ($ru["ru_utime.tv_sec"]*1e6+$ru["ru_utime.tv_usec"])/1000000);
	$i_stime = sprintf("%5.2f", ($ru["ru_stime.tv_sec"]*1e6+$ru["ru_stime.tv_usec"])/1000000);
	$i_files = count(get_included_files());
	global $_execs;
	$debug = "<br>Took $i_utime + $i_stime seconds and {$i_mem}MB of RAM";
	$debug .= "; Used $i_files files and $_execs queries";
}
else {
	$debug = "";
}

if(empty($heading)) $heading = $title;

global $version;

//		<base href='$base_href'>
echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>$title</title>
		<link rel="stylesheet" href="$base_href/themes/default/style.css" type="text/css">
		<script src='$base_href/themes/default/sidebar.js' type='text/javascript'></script>
		$script_html
		$extra_headers
	</head>

	<body>
		<h1>$title</h1>
		$subtitle_html
		
		<div id="nav">
			$blocks_html
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
			$debug
		</div>
	</body>
</html>
EOD;
?>
