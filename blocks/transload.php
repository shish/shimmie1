<?php
/*
 * transload.php (c) Shish 2005, 2006
 *
 * Show a block which lets users upload
 */
 
class transload extends block {
	function get_html($pageType) {
		return "";

		global $user, $config;
		
		// Don't show the block if anon uploads are disabled
		if(($pageType == "index") && ($config["upload_anon"] || $user->isUser())) {
			$maxSize = $config["uploads_size"];

			$uploadList = "";
			for($i=0; $i<$config['upload_count']; $i++) {
				if($i == 0) $style = "style='display:visible'";
				else $style = "style='display:none'";
				$uploadList .= "<input id='trans$i' name='data$i' $style ".
				               " onchange=\"showUp('trans".($i+1)."')\" type='text'>\n";
			}
			return <<<EOD
				<h3 id="transload-toggle" onclick="toggle('transload')">Transload</h3>
				<div id="transload">
					<form enctype='multipart/form-data' action='metablock.php?block=transload' method='post'>
						<!-- $uploadList -->
						<input name='url' type='text'>
						<input name='tags' type='text' value='tagme'>
						<input type='submit' value='Post'>
					</form>
				</div>
EOD;
		}
	}

	function get_priority() {
		return 40;
	}

	function check_url($url) {
		if(is_null($url)) {
			$title = "No URL";
			$message = "No URL specified";
			require_once "templates/generic.php";
			return false;
		}

		if(!ereg("http://", $url)) {
			$title = "Invalid URL";
			$message = "URLs must begin with http://";
			require_once "templates/generic.php";
			return false;
		}

		return true;
	}

	function run($action) {
		global $config;
		
		if($config["upload_anon"] || user_or_die()) {
			$url = $_POST["url"];

			if(!$this->check_url($url)) {
				return;
			}

			$tmpname = write_temp_file(read_url($url));
			add_image($tmpname, $url, $_POST['tags']);
			unlink($tmpname);
		}
	}
}
?>
