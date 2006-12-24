<?php
/*
 * install.php (c) Shish 2005, 2006
 *
 * Initialise the database, check that folder
 * permissions are set properly, set an admin
 * account.
 *
 * This file should be independant of the database
 * and other such things that aren't ready yet --
 * currently the only external resources are the
 * template, header, stylesheet, and footer
 */


/*
 * This file lets anyone destroy the database -- disable it
 * as soon as the admin is done installing for the first time
 */
if(is_readable("config.php")) {
	echo "'config.php' exists -- install mode is disabled";
	exit;
}
require_once "lib/adodb/adodb.inc.php";
require_once "lib/libsio.php";


/*
 * If nothing else is being done, show the install options
 */
if(is_null($_GET['action'])) {
	$configOptions .= makeRow("Database Config");
	$configOptions .= makeOpt("DSN", "database_dsn");
	$configOptions .= makeRow("ie: protocol://username:password@host/database?options");
	$configOptions .= makeRow("eg: mysql://shimmie:pw123@localhost/shimmie?persist");

	$configOptions .= makeRow();
	$configOptions .= makeRow("Initial Admin User");
	$configOptions .= makeOpt("Name", "admin_name");
	$configOptions .= makeOpt("Password", "admin_pass");
	
	$configOptions .= makeRow();
	$configOptions .= makeRow("<input type=\"submit\" value=\"Install\">");

	$title = "Shimmie Installer";
	$target = "install.php?action=set";
	require_once "templates/install.php";
}


/*
 * Check that all the settings are OK. If not, complain. If
 * they are, attempt to write a config file.
 */
else if($_GET["action"] == "set") {
	$dsn = $_POST['database_dsn'];
	$admin_name = $_POST["admin_name"];
	$admin_pass = $_POST["admin_pass"];
	
	$db = NewADOConnection($dsn);
	if(!$db) {
		$title = "Error";
		$message = "Unable to connect to $dsn";
		require_once "templates/error.php";
		exit;
	}
	$db->SetFetchMode(ADODB_FETCH_ASSOC);

	/*
	 * Create a config file
	 */
	$data .= "<?php\n";
	$data .= "\$config['database_dsn'] = '$dsn';\n";
	$data .= "?>";

	/*
	 * Create the database
	 */
	initDb($db, $admin_name, $admin_pass);

	/*
	 * when they say "sql lite", they mean "insert, select, you do the rest"...
	function sqlite_cb_concat($a, $b) {return $a.$b;}
	sqlite_create_function($db, 'md5', 'md5', 1);
	sqlite_create_function($db, 'concat', 'sqlite_cb_concat', 2);
	sqlite_create_function($db, 'lower', 'strtolower', 2);
	 */

	/*
	 * Create the database
	sqlite_query2($db, "BEGIN TRANSACTION;");
	initDb($db, "sqlite_query2", $tp, $admin_name, $admin_pass,
		"integer primary key", "sqlite_last_insert_rowid");
	sqlite_query2($db, "END TRANSACTION;");
	 */

	/*
	 * With everything else done, try to seal the installation by
	 * writing config.php. Once it exists, the installer is disabled.
	 */
	if(is_writable("./") && write_file("config.php", $data)) {
		echo "Config written to 'config.php'<p><a href='setup.php'>Continue</a>";
	}
	else {
		$title = "Error";
		$message = "The web server isn't allowed to write to the config file; please copy
		            the text below, save it as 'config.php', and upload it into the shimmie
		            folder manually.
					
					<p>One done, <a href='setup.php'>Continue</a>";
		require_once "templates/error.php";
	}
}


/*
 * This is big, try and keep it generic rather than copy & pasting per backend
 */
function initDb($db, $admin_name, $admin_pass) {
	$db->StartTrans();
	$db->Execute("CREATE TABLE comments (
		id int primary key auto_increment,
		image_id int not null,
		owner_id int not null,
		owner_ip char(16),
		posted datetime,
		comment text,
		INDEX(image_id)
	)");
	$db->Execute("CREATE TABLE images (
		id int primary key auto_increment,
		owner_id int not null,
		owner_ip char(16),
		filename char(32),
		hash char(32),
		ext char(4),
		UNIQUE(hash),
		INDEX(id)
	)");
	$db->Execute("CREATE TABLE tags (
		image_id int not null,
		tag int not null,
		UNIQUE(image_id, tag),
		INDEX(image_id),
		INDEX(tag)
	)");
	/*
	$db->Execute("CREATE TABLE image_tags (
		image_id int not null,
		tag_id int not null,
		owner_id int not null,
		UNIQUE(image_id, tag_id),
		INDEX(image_id),
		INDEX(tag_id)
	)");
	$db->Execute("CREATE TABLE tags (
		id int primary key auto_increment,
		tag varchar(255),
		UNIQUE(tag)
	)");
	*/
	$db->Execute("CREATE TABLE users (
		id int primary key auto_increment,
		name char(16) not null,
		pass char(32),
		joindate datetime not null,
		UNIQUE(name),
		INDEX(id)
	)");
	$db->Execute("CREATE TABLE user_configs (
		owner_id int primary key auto_increment,
		name varchar(255),
		value varchar(255),
		UNIQUE(owner_id, name),
		INDEX(owner_id)
	)");
	$db->Execute("CREATE TABLE config (
		name varchar(255),
		value varchar(255),
		UNIQUE(name)
	)");
	$db->Execute("CREATE TABLE bans (
		id int primary key auto_increment,
		type char(16),
		value char(64),
		date datetime,
		reason varchar(255),
		UNIQUE(type, value)
	)");

	/*
	 * Insert some defaults
	 */
	$config_insert = $db->Prepare("INSERT INTO config(name, value) VALUES(?, ?)");
	$user_insert = $db->Prepare("INSERT INTO users(name, pass, joindate) VALUES(?, ?, now())");
	$user_config_insert = $db->Prepare("INSERT INTO user_configs(owner_id, name, value) VALUES(?, ?, ?)");

	
	$db->Execute($config_insert, Array('db_version', '0.7.5'));
	
	$db->Execute($user_insert, Array('Anonymous', null));
	$db->Execute($config_insert, Array('anon_id', $db->Insert_ID()));

	$admin_pass = md5(strtolower($admin_name).$admin_pass);
	$db->Execute($user_insert, Array($admin_name, $admin_pass));
	$db->Execute($user_config_insert, Array($db->Insert_ID(), 'isadmin', 'true'));

	$db->CommitTrans();
}


/*
 * Functions to easily generate an HTML form
 */
function makeRow($content = "&nbsp;") {
	return "<tr><td colspan='2'>$content</td></tr>\n";
}
function makeOpt($friendly, $varname) {
	global $config;
	$default = $config[$varname];
	return "<tr><td>$friendly</td><td><input type='text' name='$varname' value='$default'></td></tr>\n";
}
function makeOptCheck($friendly, $varname) {
	global $config;
	$default = $config[$varname] ? " checked" : "";
	return "<tr><td>$friendly</td><td><input type='checkbox' name='$varname'$default></td></tr>\n";
}
?>
