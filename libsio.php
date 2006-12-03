<?php
/*
 * Shimmie IO: because dealing with multiple PHP versions is a
 * pain in the ass :(
 */


/*
 * put the data somewhere, return a filename for it
 */
function write_temp_file($data) {
	$tname = tempnam("/tmp", "shm_tmp_");
	write_file($tname, $data);
	return $tname;
}

/*
 * file_put_contents requires PHP 5.0
 */
function write_file($fname, $data) {
	$fp = fopen($fname, "w");
	if(!$fp) return false;
	
	fwrite($fp, $data);
	fclose($fp);
	
	return true;
}

/*
 * file_get_contents requires PHP 4.3
 */
function read_file($fname) {
	$fp = fopen($fname, "r");
	if(!$fp) return false;
	
	$data = fread($fp, filesize($fname));
	fclose($fp);
	
	return $data;
}

/*
 * "rename" that works across partitions needs PHP 4.3.3
 */
function move($from, $to) {
	return (copy($from, $to) && unlink($from));
}

/*
 * PHP4 lacks a consistent URL reader function
 */
function read_url($url, $maxsize=1048576) {
	$fp = fopen($url, "r");
	$size = 0;
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

	return $content;
}
?>
