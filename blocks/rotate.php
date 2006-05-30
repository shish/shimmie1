<?php
/*
 * edittags.php (c) Shish 2006
 *
 * Rotate an image, losslessly if possible
 */

if($pageType == "view") {
	global $image;

	$blocks[50] .= <<<EOD
	<h3 onclick="toggle('rotate')">Rotate Image</h3>
	<div id="rotate">
		<form action="metablock.php" method="POST">
			<input name="block" type="hidden" value="rotate">
			<input name="image_id" type="hidden" value="$image->id">
			<input type="radio" name="angle" value="270"> Clockwise 90
			<br><input type="radio" name="angle" value="90"> Anti-clockwise 90
			<br><input type="radio" name="angle" value="180"> 180
			<br><input type="submit" value="Rotate">
		</form>
	</div>
EOD;
}

if($pageType == "block") {
	switch($_POST['angle']) {
		case "90": $aarg = "-9"; break;
		case "270": $aarg = "-2"; break;
		case "180": $aarg = "-1"; break;
		default:
			header("X-Shimmie-Status: Error - Bad Angle");
			$title = "Bad Angle";
			$body = "Images can only be rotated in units of 90 degrees";
			require_once "templates/generic.php";
			exit;
	}

	$image = new Image($_POST['image_id']);
	$dir_images = $config['dir_images'];
	$dir_thumbs = $config['dir_thumbs'];

	if($image->ext == "jpg") {
		system("exiftran $aarg $dir_thumbs/{$image->id}.jpg");
	}

	header("Location: view.php?image_id={$image->id}");
	header("X-Shimmie-Status: OK - Image Rotated");
	echo "<a href='view.php?image_id={$image->id}'>Back</a>";
}
?>
