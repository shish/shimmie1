<?php
/*
 * upload.php (c) Shish 2005, 2006
 *
 * Show a block which lets users upload
 */

class upload extends block {
	function get_title() {
		return "Upload";
	}

	function get_html($pageType) {
		global $user, $config;

		// Don't show the block if anon uploads are disabled
		if(($pageType == "index") && ($config["upload_anon"] || $user->isUser())) {
			$maxSize = $config["upload_size"];

			$uploadList = "";
			for($i=0; $i<$config['upload_count']; $i++) {
				if($i == 0) $style = "style='display:visible'";
				else $style = "style='display:none'";
				$uploadList .= "<input accept='image/jpeg,image/png,image/gif' size='10' ".
					"id='data$i' name='data$i' $style onchange=\"showUp('data".($i+1)."')\" type='file'>\n";
			}
			$maxkb = $maxSize / 1024;
			return <<<EOD
					<form enctype='multipart/form-data' action='metablock.php?block=upload' method='post'>
						<input type='hidden' name='max_file_size' value='$maxSize'>
						$uploadList
						<input id="tagBox" name='tags' type='text' value="tagme" autocomplete="off">
						<input type='submit' value='Post'>
					</form>
					<div id="upload_completions" style="clear:both;"><small>(Max file size is {$maxkb}KB)</small></div>
EOD;
		}
	}

	function get_priority() {
		return 30;
	}

	function check_filecount() {
		if(count($_FILES) == 0) {
			header("X-Shimmie-Status: Error - No Images Specified");
			$title = "No Images Specified";
			$body = "You need to select a file to be uploaded";
			include_once get_theme_template();
			exit;
		}
		else {
			header("X-Shimmie-Status: OK - Images Uploaded");
		}
	}

	function run($action) {
		global $config, $user;

		if($config["upload_anon"] || user_or_die()) {
			$owner_ip = $_SERVER['REMOTE_ADDR'];
			$dir_images = $config['dir_images'];
			$dir_thumbs = $config['dir_thumbs'];

			$err = null;

			$this->check_filecount();

			/*
			 * Check as many upload stots as there should be
			 *
			 * FIXME: If a slot isn't used, ignore it. Currently this is done
			 * in several nasty ways -- is there a way to do it properly?
			 */
			// for($dnum=0; $dnum<min($config['upload_count'], count($_FILES)); $dnum++) {
			//	$info = $_FILES["data$dnum"];
			foreach($_FILES as $info) {
				if(strlen($info['name']) > 0) {
					$ok = add_image($info['tmp_name'], $info['name'], $_POST['tags']);
					if(!$ok) {
						$err .= "<br>Failed to upload: '".html_escape($info['name'])."'";
					}
				}
			}

			/*
			 * If the error flag is set, keep on the current page so the user
			 * can see the error message. If all is OK, redirect back automatically
			 */
			if(!is_null($err)) {
				$title = "Upload error";
				$message = $err;
				require_once get_theme_template();
			}
			else {
				header("Location: ./index.php");
				echo "<p><a href='index.php'>Back</a>";
			}
		}
	}
}
?>
