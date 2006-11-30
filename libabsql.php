<?php
/*
 * libabsql.php (c) Shish 2005, 2006
 *
 * A library to abstract the databases that PHP can use -- calling sql_foo
 * will redirect to specific_database_foo, and change any relevant paramaters
 */

if($config["database_api"] == "sqlite") {
	$sqliteHandle = sqlite_open($config['sqlite_file']) or die("Couldn't open SQLite DB ".$config['sqlite_file']);
	$sqliteError = null;

	function sqlite_cb_concat($a, $b) {return $a.$b;}
	function sqlite_cb_if($a, $b, $c) {return $a ? $b : $c;}

	sqlite_create_function($sqliteHandle, 'md5', 'md5', 1);
	sqlite_create_function($sqliteHandle, 'concat', 'sqlite_cb_concat', 2);
	sqlite_create_function($sqliteHandle, 'if', 'sqlite_cb_if', 3);
	sqlite_create_function($sqliteHandle, 'substring', 'substr', 3);
	sqlite_create_function($sqliteHandle, 'lower', 'strtolower', 2);
	
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
	function sql_insert_id() {global $sqliteHandle; return sqlite_last_insert_rowid($sqliteHandle);}
	function sql_escape($string) {return sqlite_escape_string($string);}
}
else if($config["database_api"] == "pgsql") {
	function sql_query($query) {return null;}
	function sql_error() {return "Postgres not supported yet";}
	function sql_fetch_row($resultSet) {return null;}
	function sql_num_rows($resultSet) {return null;}
	function sql_insert_id() {return null;}
	function sql_escape($string) {return pg_escape_string($string);}
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
	function sql_escape($string) {return mysql_real_escape_string($string);}
}
?>
