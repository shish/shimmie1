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
		global $user;

		// Don't show the block if anon uploads are disabled
		if(($pageType == "index") && (get_config("upload_anon") || $user->isUser())) {
			$maxSize = get_config("upload_size");

			$uploadList = "";
			for($i=0; $i<get_config('upload_count'); $i++) {
				if($i == 0) $style = "style='display:visible'";
				else $style = "style='display:none'";
				$uploadList .= "<input accept='image/jpeg,image/png,image/gif' size='10' ".
					"id='data$i' name='data$i' $style onchange=\"showUp('data".($i+1)."')\" type='file'>\n";
			}
			$maxkb = (int)($maxSize / 1024);
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
			$title = "No Images Specified";
			$body = "You need to select a file to be uploaded";
			include_once get_theme_template();
			exit;
		}
	}

	function run($action) {
		global $user;

		if(get_config("upload_anon") || user_or_die()) {
			$owner_ip = $_SERVER['REMOTE_ADDR'];

			$err = null;

			$this->check_filecount();

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
