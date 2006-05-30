<?php
/*
 * upload.php (c) Shish 2005, 2006
 *
 * Show a block which lets users upload
 */

require_once "header.php";

// Don't show the block if anon uploads are disabled
if(($pageType == "index") && ($config["upload_anon"] || $user->id != 0)) {
	$maxSize = $config["upload_size"];

	$uploadList = "";
	for($i=0; $i<$config['upload_count']; $i++) {
		if($i == 0) $style = "style='display:visible'";
		else $style = "style='display:none'";
		$uploadList .= "<input accept='image/jpeg,image/png,image/gif' size='10' ".
			"id='data$i' name='data$i' $style onchange=\"showUp('data".($i+1)."')\" type='file'>\n";
	}
	$blocks[30] .= <<<EOD
		<h3 onclick="toggle('upload')">Upload</h3>
		<div id="upload">
			<form enctype='multipart/form-data' action='metablock.php?block=upload' method='post'>
				<input type='hidden' name='max_file_size' value='$maxSize'>
				$uploadList
				<input id="tagBox" name='tags' type='text' value="tagme" autocomplete="off">
				<input type='submit' value='Post'>
			</form>
			<div id="upload_completions" style="clear:both;"></div>
		</div>
EOD;
}

if(($pageType == "block") && ($config["upload_anon"] || user_or_die())) {
	$owner_ip = $_SERVER['REMOTE_ADDR'];
	$dir_images = $config['dir_images'];
	$dir_thumbs = $config['dir_thumbs'];

	$err = null;

	if(count($_FILES) == 0) {
		header("X-Shimmie-Status: Error - No Images Specified");
		$title = "No Images Specified";
		$body = "You need to select a file to be uploaded";
		include_once "templates/generic.php";
		exit;
	}
	else {
		header("X-Shimmie-Status: OK - Images Uploaded");
	}

	/*
	 * Check as many upload stots as there should be
	 *
	 * FIXME: If a slot isn't used, ignore it. Currently this is done
	 * in several nasty ways -- is there a way to do it properly?
	 */
	for($dnum=0; $dnum<min($config['upload_count'], count($_FILES)); $dnum++) {
		$dname = "data$dnum";

		if(strlen($_FILES[$dname]['tmp_name']) < 4) continue;
	
		$tname = $_FILES[$dname]['tmp_name'];
		$fname = sql_escape($_FILES[$dname]['name']);
		$imgsize = getimagesize($tname);
	
		if($imgsize != false) {
			$mime_type = $imgsize['mime'];
			$ext = null;
			switch($mime_type) {
				case "image/jpeg": $ext = "jpg"; break;
				case "image/png":  $ext = "png"; break;
				case "image/gif":  $ext = "gif"; break;
			}
			if(is_null($ext)) {
				$err .= "<p>Unrecognised file type for '$fname' (not jpg/gif/png)";
				continue;
			}

			$hash = md5_file($tname);
	
			/*
			 * Check for an existing image
			 */
			$existing_result = sql_query("SELECT * FROM shm_images WHERE hash='$hash'");
			if($existing_row = sql_fetch_row($existing_result)) {
				header("X-Shimmie-Status: Error - Hash Clash");
				$iid = $existing_row['id'];
				$err .= "<p>Upload of '$fname' failed:";
				$err .= "<br>There's already an image with hash '$hash' (<a href='view.php?image_id=$iid'>view</a>)";
				continue;
			}
			
			$image = imagecreatefromstring(file_get_contents($_FILES[$dname]['tmp_name']));
		
			$width = $imgsize[0];
			$height = $imgsize[1];
			$max_width  = $config['thumb_w'];
			$max_height = $config['thumb_h'];
			$xscale = ($max_height / $height);
			$yscale = ($max_width / $width);
			$scale = ($xscale < $yscale) ? $xscale : $yscale;
			
			if($scale >= 1) {
				$thumb = $image;
			}
			else {
				$thumb = imagecreatetruecolor($width*$scale, $height*$scale);
				imagecopyresampled(
					$thumb, $image, 0, 0, 0, 0,
					$width*$scale, $height*$scale, $width, $height
				);
			}

			// actually insert the info
			$new_query = "INSERT INTO shm_images(owner_id, owner_ip, filename, hash, ext) ".
			             "VALUES($user->id, '$owner_ip', '$fname', '$hash', '$ext')";
			sql_query($new_query);
			$id = sql_insert_id();
			$del_query = "DELETE FROM shm_images WHERE id=$id";
		
			/*
			 * If no errors: move the file from the temporary upload
			 * area to the main file store, create a thumbnail, and
			 * insert the image info into the database
			 */
			if(!move_uploaded_file($_FILES[$dname]['tmp_name'], "$dir_images/$id.$ext")) {
				$err .= "<p>The image couldn't be moved from the temporary area to the
				         main data store -- is the web server allowed to write to '$dir_images'?";
				sql_query($del_query);
				continue;
			}
			if(!imagejpeg($thumb, "$dir_thumbs/$id.jpg", $config['thumb_q'])) {
				$err .= "<p>The image thumbnail couldn't be generated -- is the web
				         server allowed to write to '$dir_thumbs'?";
				sql_query($del_query);
				continue;
			}
			
			header("X-Shimmie-Image-ID: $id");
			updateTags($id, sql_escape($_POST['tags']));
		}
		else {
			$err .= "<p>$fname upload failed";
		}
	}


	/*
	 * If the error flag is set, keep on the current page so the user
	 * can see the error message. If all is OK, redirect back automatically
	 */
	if(!is_null($err)) {
		$title = "Upload error";
		$message = $err;
		require_once "templates/generic.php";
	}
	else {
		header("Location: ./index.php");
		echo "<p><a href='index.php'>Back</a>";
	}
}
?>
