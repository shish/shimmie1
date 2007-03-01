<?php
/*
 * rotate.php (c) Shish 2006
 *
 * Rotate an image, losslessly if possible
 */

class rotate extends block {
	function get_title() {
		return "Rotate Image";
	}

	function get_html($pageType) {
		return "";
		
		if($pageType == "view") {
			global $image;

			return <<<EOD
		<form action="metablock.php" method="POST">
			<input name="block" type="hidden" value="rotate">
			<input name="image_id" type="hidden" value="$image->id">
			<input type="radio" name="angle" value="270"> Clockwise 90
			<br><input type="radio" name="angle" value="90"> Anti-clockwise 90
			<br><input type="radio" name="angle" value="180"> 180
			<br><input type="submit" value="Rotate">
		</form>
EOD;
		}
	}

	function get_priority() {
		return 50;
	}

	function run($action) {
		switch($_POST['angle']) {
			case "90": $aarg = "-9"; break;
			case "270": $aarg = "-2"; break;
			case "180": $aarg = "-1"; break;
			default:
				$title = "Bad Angle";
				$body = "Images can only be rotated in units of 90 degrees";
				require_once get_theme_template();
				exit;
		}

		$image = new Image($_POST['image_id']);
		$dir_images = get_config('dir_images');
		$dir_thumbs = get_config('dir_thumbs');
	
		if($image->ext == "jpg") {
			system("exiftran $aarg $dir_thumbs/{$image->id}.jpg");
		}

		header("Location: view.php?image_id={$image->id}");
		echo "<a href='view.php?image_id={$image->id}'>Back</a>";
	}
}
?>
