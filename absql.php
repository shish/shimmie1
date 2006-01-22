<?php
if($config["database_api"] == "sqlite") {
	$sqliteHandle = sqlite_open($config['sqlite_file']);
	$sqliteError = null;
	
	function sql_query($query) {
		global $sqliteHandle, $sqliteError;
		$query = str_replace("shm_", "", $query);
		$result = sqlite_query($sqliteHandle, $query);
		if($result) {
			return $result;
		}
		else {
			$title = "Error";
			$message = "Error processing SQLite query";
			$data = "SQL: $query\n\nError: ".sql_error();
			require_once "templates/generic.php";
			exit;
		}
	}
	function sql_error() {global $sqliteHandle; return sqlite_error_string(sqlite_last_error($sqliteHandle));}
	function sql_fetch_row($resultSet) {return sqlite_fetch_array($resultSet, SQLITE_ASSOC);}
	function sql_num_rows($resultSet) {return sqlite_num_rows($resultSet);}
	function sql_insert_id() {return sqlite_last_insert_rowid();}

}
else if($config["database_api"] == "pgsql") {
	function sql_query($query) {return null;}
	function sql_error() {return "Postgres not supported yet";}
	function sql_fetch_row($resultSet) {return null;}
	function sql_num_rows($resultSet) {return null;}
	function sql_insert_id() {return null;}
}
else {
	mysql_pconnect($config['mysql_host'], $config['mysql_user'], $config['mysql_pass']) or die(mysql_error());
	mysql_select_db($config['mysql_db']) or die(mysql_error());

	function sql_query($query) {
		$query = str_replace("shm_", $config['mysql_prefix'], $query);
		$result = mysql_query($query);
		if($result) {
			return $result;
		}
		else {
			$title = "Error";
			$message = "Error processing MySQL query";
			$data = "SQL: $query\n\nError: ".sql_error();
			require_once "templates/generic.php";
			exit;
		}
	}
	function sql_error() {return mysql_error();}
	function sql_fetch_row($resultSet) {return mysql_fetch_assoc($resultSet);}
	function sql_num_rows($resultSet) {return mysql_num_rows($resultSet);}
	function sql_insert_id() {return mysql_insert_id();}
}
?>
