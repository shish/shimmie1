<?php
/*
 * transload.php (c) Shish 2005, 2006
 *
 * Show a block which lets users upload
 */

require_once "header.php";

// Don't show the block if anon uploads are disabled
if(($pageType == "index") && ($config["upload_anon"] || $user->isUser())) {
	$maxSize = $config["uploads_size"];

	$uploadList = "";
	for($i=0; $i<$config['upload_count']; $i++) {
		if($i == 0) $style = "style='display:visible'";
		else $style = "style='display:none'";
		$uploadList .= "<input id='trans$i' name='data$i' $style onchange=\"showUp('trans".($i+1)."')\" type='text'>\n";
	}
	$uploadBlock = <<<EOD
		<h3 id="transload-toggle" onclick="toggle('transload')">Transload</h3>
		<div id="transload">
			<form enctype='multipart/form-data' action='metablock.php?block=transload' method='post'>
				$uploadList
				<input name='tags' type='text' value='tagme'>
				<input type='submit' value='Post'>
			</form>
		</div>
EOD;
}


if(($pageType == "block") && ($config["upload_anon"] || user_or_die())) {
	$owner_ip = $_SERVER['REMOTE_ADDR'];
	$dir_images = $config['dir_images'];
	$dir_thumbs = $config['dir_thumbs'];
	$url = $_POST["url"];
	

	if(is_null($url)) {
		$title = "No URL";
		$message = "No URL specified";
		require_once "templates/generic.php";
		exit;
	}

	if(!ereg("http://", $url)) {
		$title = "Invalid URL";
		$message = "URLs must begin with http://";
		require_once "templates/generic.php";
		exit;
	}
	
	$fp = fopen($url, "r");
	$size = 0;
	$maxsize = $config["uploads_size"];
	$content = '';
	while(!feof($fp)) {
		$tmp = fread($fp, 4096);
		$content .= $tmp;
		$size += strlen($tmp);
		if($size > $maxsize) {
			fclose($fp);
			$title = "File too big";
			$mkb = $maxsize/1024;
			$message = "Max upload size is $maxsize bytes ($mkb KB)";
			require_once "templates/generic.php";
			exit;
		}
	}
	fclose($fp);
	
	
	$tname = tempnam("/tmp", "shm_transload_");
	$tmp = fopen($tname, "w");
	fwrite($tmp, $content);
	fclose($tmp);
	
	$fname = $url;
	$imgsize = getimagesize($tname);
		
	if($imgsize != false) {
		$mime_type = $imgsize['mime'];
		switch($mime_type) {
			case "image/jpeg": $ext = "jpg"; break;
			case "image/png":  $ext = "png"; break;
			case "image/gif":  $ext = "gif"; break;
			default:           $err .= "<p>Unrecognised file type for '$fname' (not jpg/gif/png)"; break;
		}
		$hash = md5_file($tname);
		
		/*
		 * Check for an existing image
		 */
		$existing_result = sql_query("SELECT * FROM shm_images WHERE hash='$hash'");
		if(sql_num_rows($existing_result) > 0) {
			$err .= "<p>Upload of '$fname' failed -- there's already an image with hash '$hash'";
		}
			
		# if(!(copy($tname, "$dir_images/$hash.$ext") && unlink($tname))) {
		if(!rename($tname, "$dir_images/$hash.$ext")) {
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
		updateTags(sql_insert_id(), sql_escape($_POST['tags']));
	}
	else {
		$err .= "<p>$fname upload failed";
	}
}
?>
