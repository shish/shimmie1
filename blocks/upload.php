<?php
/*
 * upload.php (c) Shish 2005
 *
 * Show a block which lets users upload
 */


// Don't show the block if anon uploads are disabled
if(($config["upload_anon"] == 0) && ($user->id == 0)) return;

$maxSize = $config["uploads_size"];

$uploadList = "";
for($i=0; $i<$config['upload_count']; $i++) {
	if($i == 0) $style = "style='display:visible'";
	else $style = "style='display:none'";
	$uploadList .= "<input accept='image/jpeg,image/png,image/gif' size='10' ".
		"id='data$i' name='data$i' $style onchange=\"showUp('data".($i+1)."')\" type='file'>\n";
}
$uploadBlock = <<<EOD
		<h3 onclick="toggle('upload')">Upload</h3>
		<div id="upload">
			<form enctype='multipart/form-data' action='./upload.php' method='post'>
				<input type='hidden' name='max_file_size' value='$maxSize'>
				$uploadList
				<input name='tags' type='text' value='tagme'>
				<input type='submit' value='Post'>
			</form>
		</div>
EOD;
?>
