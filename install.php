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

session_start(); // hold temp stuff in session

/*
 * If nothing else is being done, show the install options
 */
switch($_GET['stage']) {
	default:
		$title = "Shimmie Installer";
		$blocks["Help"] = "
			Shimmie is developed with MySQL, and support
			for it is included. Other databases may work,
			but you'll need to add the appropriate ADOdb
			drivers yourself.";
		$body["Fill in this form"] = 
			makeHtml("<form action='install.php?stage=createdb' method='POST'>").
			makeHtml("<table style='width: 400px;'>").
			makeRow("Database Config").
			makeOpt("DSN", "database_dsn").
			makeRow("ie: protocol://username:password@host/database?options").
			makeRow("eg: mysql://shimmie:pw123@localhost/shimmie?persist").
			makeRow().
			makeRow("Initial Admin User").
			makeOpt("Name", "admin_name").
			makeOpt("Password", "admin_pass").
			makeRow().
			makeRow("<input type='submit' value='Install'>").
			makeHtml("</table>").
			makeHtml("</form>");
		require_once get_theme_template();
		break;
	
	case 'createdb':
		$dsn = $_SESSION['database_dsn'] = $_POST['database_dsn'];
		$admin_name = $_SESSION['admin_name'] = $_POST["admin_name"];
		$admin_pass = $_SESSION['admin_pass'] = $_POST["admin_pass"];
		
		$db = NewADOConnection($dsn);
		if(!$db) {
			$title = "Error";
			$body["Error"] = "Couldn't connect to \"$dsn\".<p><a href='install.php'>Back</a>";
			require_once get_theme_template();
			exit;
		}
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		
		if(initDb($db, $admin_name, $admin_pass)) {
			header("Location: install.php?stage=writeconfig");
			echo "Database installed OK<p><a href='install.php?stage=writeconfig'>Continue</a>";
		}
		break;
	
	case 'writeconfig':
		$dsn = $_SESSION['database_dsn'];
		
		$file_content .= "<?php\n";
		$file_content .= "\$config['database_dsn'] = '$dsn';\n";
		$file_content .= "?>";
		
		if(is_writable("./") && write_file("config.php", $file_content)) {
			@mkdir("images"); // try and make default dirs, if possible
			@mkdir("thumbs");
			header("Location: setup.php");
			echo "Config written to 'config.php'<p><a href='setup.php'>Continue</a>";
		}
		else {
			$title = "Error";
			$body["Error"] = "
				The web server isn't allowed to write to the config file; please copy
			    the text below, save it as 'config.php', and upload it into the shimmie
			    folder manually.
						
				<p>One done, <a href='setup.php'>Continue</a>";
			$body[] = gen_textarea($file_content);
			require_once get_theme_template();
		}
		break;
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
		tag varchar(255) not null,
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

	return $db->CommitTrans();
}


/*
 * Functions to easily generate an HTML form
 */
function makeHtml($html) {
	return $html;
}
function makeRow($content = "&nbsp;") {
	return "<tr><td colspan='2'>$content</td></tr>\n";
}
function makeOpt($friendly, $varname) {
	$default = $_SESSION[$varname];
	return "<tr><td>$friendly</td><td><input type='text' name='$varname' value='$default'></td></tr>\n";
}
?>
