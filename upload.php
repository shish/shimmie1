<?php
/*
 * upload.php (c) Shish 2005
 *
 * Accept an image upload, insert details into the database
 */

require_once "header.php";


$config["upload_anon"] || user_or_die();


$owner_ip = $_SERVER['REMOTE_ADDR'];
$dir_images = $config['dir_images'];
$dir_thumbs = $config['dir_thumbs'];

$err = null;

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
	$fname = addslashes($_FILES[$dname]['name']);
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
		if(sql_num_rows($existing_result) > 0) {
			$err .= "<p>Upload of '$fname' failed -- there's already an image with hash '$hash'";
			continue;
		}
		
		/*
		 * If no errors: move the file from the temporary upload
		 * area to the main file store, create a thumbnail, and
		 * insert the image info into the database
		 */
		if(!move_uploaded_file($_FILES[$dname]['tmp_name'], "$dir_images/$hash.$ext")) {
			$err .= "<p>The image couldn't be moved from the temporary area to the
			         main data store -- is the web server allowed to write to '$dir_images'?";
			continue;
		}
			
		$image = imagecreatefromstring(file_get_contents("$dir_images/$hash.$ext"));
		
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
		if(!imagejpeg($thumb, "$dir_thumbs/$hash.jpg", $config['thumb_q'])) {
			$err .= "<p>The image thumbnail couldn't be generated -- is the web
			         server allowed to write to '$dir_thumbs'?";
			continue;
		}

		// actually insert the info
		$new_query = "INSERT INTO shm_images(owner_id, owner_ip, filename, hash, ext) ".
		             "VALUES($user->id, '$owner_ip', '$fname', '$hash', '$ext')";
		sql_query($new_query);
		updateTags(sql_insert_id(), addslashes($_POST['tags']));
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
	echo "<p><a href='./index.php'>Back</a>";
}
?>
