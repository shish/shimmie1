<?php
/*
 * transload.php (c) Shish 2005, 2006
 *
 * Show a block which lets users upload
 */
 
class transload extends block {
	function get_title() {
		return "Transload";
	}

	function get_html($pageType) {
		return ""; // this block is very beta

		global $user;
		
		// Don't show the block if anon uploads are disabled
		if(($pageType == "index") && (get_config("upload_anon") || $user->isUser())) {
			$maxSize = get_config("uploads_size");

			$uploadList = "";
			for($i=0; $i<get_config('upload_count'); $i++) {
				if($i == 0) $style = "style='display:visible'";
				else $style = "style='display:none'";
				$uploadList .= "<input id='trans$i' name='data$i' $style ".
				               " onchange=\"showUp('trans".($i+1)."')\" type='text'>\n";
			}
			return <<<EOD
					<form enctype='multipart/form-data' action='metablock.php?block=transload' method='post'>
						<!-- $uploadList -->
						<input name='url' type='text'>
						<input name='tags' type='text' value='tagme'>
						<input type='submit' value='Post'>
					</form>
EOD;
		}
	}

	function get_priority() {
		return 31;
	}

	function check_url($url) {
		if(is_null($url)) {
			$title = "No URL";
			$message = "No URL specified";
			require_once get_theme_template();
			return false;
		}

		if(!ereg("http://", $url)) {
			$title = "Invalid URL";
			$message = "URLs must begin with http://";
			require_once get_theme_template();
			return false;
		}

		return true;
	}

	function run($action) {
		if(get_config("upload_anon") || user_or_die()) {
			$url = $_POST["url"];

			if(!$this->check_url($url)) {
				return;
			}

			$tmpname = write_temp_file(read_url($url, get_config('uploads_size')));
			add_image($tmpname, $url, $_POST['tags']);
			unlink($tmpname);
		}
	}
}
?>
