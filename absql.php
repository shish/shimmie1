<?php
/*
if($config["database_api"] == "sqlite") {
	$sqliteError = null;
	$sqliteHandle = null;
	
	function sql_query($query) {
		$result = sqlite_query($query, $sqliteHandle, SQLITE_ASSOC, $sqliteError);
		if($result) {return $result;}
		else {
			$title = "Error";
			$message = "Error processing SQLite query";
			$data = mysql_error();
			require_once "templates/generic.php";
			exit;
		}
	}
	function sql_error() {return $sqliteError;}
	function sql_fetch_row($resultSet) {return sqlite_fetch_array($resultSet, SQLITE_ASSOC);}
	function sql_num_rows($resultSet) {return sqlite_num_rows($resultSet);}
	function sql_insert_id() {return sqlite_last_insert_rowid();}

	$sqliteHandle = sqlite_popen($config['sqlite_db']);
}
else {
*/
	function sql_query($query) {
		$query = str_replace("shm_", $config['mysql_prefix'], $query);
		$result = mysql_query($query);
		if($result) {return $result;}
		else {
			$title = "Error";
			$message = "Error processing MySQL query";
			$data = mysql_error();
			require_once "templates/generic.php";
			exit;
		}
	}
	function sql_error() {return mysql_error();}
	function sql_fetch_row($resultSet) {return mysql_fetch_assoc($resultSet);}
	function sql_num_rows($resultSet) {return mysql_num_rows($resultSet);}
	function sql_insert_id() {return mysql_insert_id();}
	
	mysql_pconnect($config['mysql_host'], $config['mysql_user'], $config['mysql_pass']) or die(mysql_error());
	mysql_select_db($config['mysql_db']) or die(mysql_error());
/*
}
*/
?>
