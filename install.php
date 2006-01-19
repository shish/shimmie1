<?php
/*
 * install.php (c) Shish 2005
 *
 * Initialise the database, check that folder
 * permissions are set properly, set an admin
 * account.
 *
 * This file should be independant of the database
 * and other such things that aren't ready yet --
 * currently the only external resources are the
 * template, header, stylesheet, and footer
 *
 * FIXME: table prefixing works in the rest of the
 * site, we just need the installer to set it up...
 *
 * FIXME: given a mysql admin password, create a
 * more restricted user and switch to it
 */


/*
 * This file lets anyone destroy the database -- disable it
 * as soon as the admin is done installing for the first time
 */
if(is_readable("config.php")) {
	echo "'config.php' exists -- install mode is disabled";
	exit;
}


/*
 * If nothing else is being done, show the install options
 */
if(is_null($_GET['action'])) {
	$selectDb = <<<EOD
	<tr><td>Backend</td><td>
	<script language="javascript">
	function setdbapi(db) {
		document.getElementById("mysqlconf").style.display = "none";
		document.getElementById("pgsqlconf").style.display = "none";
		document.getElementById("sqliteconf").style.display = "none";
		
		if(db == "mysql") {
			document.getElementById("mysqlconf").style.display = null;
		}
		else if(db == "pgsql") {
			document.getElementById("pgsqlconf").style.display = null;
		}
		else if(db == "sqlite") {
			document.getElementById("sqliteconf").style.display = null;
		}
	}
	</script>
	<select name="database_api" onchange="setdbapi(this.value)">
		<option value="-">Select One</option>
		<option value="mysql">MySQL</option>
		<!-- <option value="pgsql">Postgres</option> -->
		<!-- <option value="sqlite">SQLite</option> -->
	</select>
	</td></tr>
EOD;
	$configOptions .= makeRow("Database Config");
	$configOptions .= $selectDb;

	$configOptions .= "<tbody id='mysqlconf' style='display: none;'>\n";
	$configOptions .= makeOpt("Host", "mysql_host");
//	$configOptions .= makeOptCheck("Create account", "mysql_createaccount");
//	$configOptions .= makeOpt("MySQL Admin Name", "mysql_auser");
//	$configOptions .= makeOpt("MySQL Admin Password", "mysql_apass");
	$configOptions .= makeOpt("User Name", "mysql_user");
	$configOptions .= makeOpt("Password", "mysql_pass");
	$configOptions .= makeOpt("Database", "mysql_db");
	$configOptions .= makeOpt("Table Prefix", "mysql_prefix");
	$configOptions .= "</tbody>\n";

	$configOptions .= "<tbody id='pgsqlconf' style='display: none;'>\n";
	$configOptions .= makeOpt("Host", "pgsql_host");
	$configOptions .= makeOpt("User Name", "pgsql_user");
	$configOptions .= makeOpt("Password", "pgsql_pass");
	$configOptions .= makeOpt("Database", "pgsql_db");
	$configOptions .= makeOpt("Table Prefix", "pgsql_prefix");
	$configOptions .= "</tbody>\n";

	$configOptions .= "<tbody id='sqliteconf' style='display: none;'>\n";
	$configOptions .= makeOpt("File", "sqlite_file");
	$configOptions .= "</tbody>\n";

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
	$dba = $_POST['database_api'];
	$admin_name = $_POST["admin_name"];
	$admin_pass = $_POST["admin_pass"];
	
	/*
	 * Setup for MySQL databases
	 */
	if($dba == "mysql") {
		$mysql_createaccount = ($_POST['mysql_createaccount'] == "on");
		
		$mysql_auser = $_POST['mysql_auser'];
		$mysql_apass = $_POST['mysql_apass'];
		
		$mysql_host = $_POST['mysql_host'];
		$mysql_user = $_POST['mysql_user'];
		$mysql_pass = $_POST['mysql_pass'];
		$mysql_db = $_POST['mysql_db'];
//		$tp = $_POST["mysql_prefix"];
		$tp = '';

		if($mysql_createaccount) {
			if(!@mysql_connect($mysql_host, $mysql_user, $mysql_pass)) {
				$title = "Error";
				$message = "Unable to connect to database server '$mysql_host' ".
				           "using login '$mysql_user:$mysql_pass'";
				$data = mysql_error();
				require_once "templates/error.php";
				exit;
			}
			mysql_query2("GRANT ALL on $mysql_db.* TO '$mysql_user'@'%' IDENTIFIED BY '$mysql_pass'");
		}
		
		if(!@mysql_connect($mysql_host, $mysql_user, $mysql_pass)) {
			$title = "Error";
			$message = "Unable to connect to database server '$mysql_host' ".
			           "using login '$mysql_user:$mysql_pass'";
			$data = mysql_error();
			require_once "templates/error.php";
			exit;
		}

		if(!@mysql_select_db($mysql_db)) {
			$title = "Error";
			$message = "Connected to database server, but unable to open ".
			           "database '$mysql_db' using login '$mysql_user:$mysql_pass'";
			$data = mysql_error();
			require_once "templates/error.php";
			exit;
		}
		
		/*
		 * Create a config file
		 */
		$data .= "<?php\n";
		$data .= "\$config['database_api'] = 'mysql';\n";
		$data .= "\$config['mysql_host']   = '$mysql_host';\n";
		$data .= "\$config['mysql_user']   = '$mysql_user';\n";
		$data .= "\$config['mysql_pass']   = '$mysql_pass';\n";
		$data .= "\$config['mysql_db']     = '$mysql_db';\n";
		$data .= "\$config['mysql_prefix'] = '$tp';\n";
		$data .= "?>";

		/*
		 * Create the database
		 */
		mysql_query2("CREATE TABLE ${tp}comments (
			id int primary key auto_increment,
			image_id int not null,
			owner_id int not null,
			owner_ip char(16),
			comment text
		)");
		mysql_query2("CREATE TABLE ${tp}images (
			id int primary key auto_increment,
			owner_id int not null,
			owner_ip char(16),
			filename char(32),
			hash char(32),
			ext char(4),
			UNIQUE(hash)
		)");
		mysql_query2("CREATE TABLE ${tp}tags (
			image_id int not null,
			tag char(32),
			UNIQUE(image_id, tag)
		)");
		mysql_query2("CREATE TABLE ${tp}users (
			id int primary key auto_increment,
			name char(16) not null,
			pass char(32),
			permissions int default 0,
			UNIQUE(name)
		)");
		mysql_query2("CREATE TABLE ${tp}config (
			name varchar(255),
			value varchar(255),
			UNIQUE(name)
		)");

		/*
		 * Insert a couple of default users
		 */
		mysql_query2("INSERT INTO ${tp}users VALUES (
			0, 'Anonymous', NULL, 0
		)");
		mysql_query2("INSERT INTO ${tp}users VALUES (
			1, '$admin_name', md5(concat(lower('$admin_name'), '$admin_pass')), 1023
		)");
	}


	/*
	 * Setup for PostgreSQL databases
	 *
	 * FIXME: Make this work
	 */
	else if($dba == "pgsql") {
		$title = "Postgres Unfinished";
		$message = "I don't have a postgres installation to test with &lt;_&lt;";
		require_once "templates/error.php";
		exit;
	}


	/*
	 * Setup for SQLite databases
	 *
	 * FIXME: Make this work
	 */
	else if($dba == "sqlite") {
		$sqlite_file = $_POST["sqlite_file"];
		$tp = "";
		
		if(!@sqlite_open($sqlite_file)) {
			$title = "Error";
			$message = "Unable to open database file '$sqlite_file'; try creating a blank ".
			           "file and making sure the web server can write to it";
			$data = mysql_error();
			require_once "templates/error.php";
			exit;
		}
		
		$data .= "<?php\n";
		$data .= "\$config['database_api'] = 'sqlite';\n";
		$data .= "\$config['sqlite_file']  = '$sqlite_file';\n";
		$data .= "?>";
		
		/*
		 * Create the database
		 */
		sqlite_query2("CREATE TABLE ${tp}comments (
			id int primary key auto_increment,
			image_id int not null,
			owner_id int not null,
			owner_ip char(16),
			comment text
		)");
		sqlite_query2("CREATE TABLE ${tp}images (
			id int primary key auto_increment,
			owner_id int not null,
			owner_ip char(16),
			filename char(32),
			hash char(32),
			ext char(4),
			UNIQUE(hash)
		)");
		sqlite_query2("CREATE TABLE ${tp}tags (
			image_id int not null,
			tag char(32),
			UNIQUE(image_id, tag)
		)");
		sqlite_query2("CREATE TABLE ${tp}users (
			id int primary key auto_increment,
			name char(16) not null,
			pass char(32),
			permissions int default 0,
			UNIQUE(name)
		)");
		sqlite_query2("CREATE TABLE ${tp}config (
			name varchar(255),
			value varchar(255),
			UNIQUE(name)
		)");

		/*
		 * Insert a couple of default users
		 */
		sqlite_query2("INSERT INTO ${tp}users VALUES (
			0, 'Anonymous', NULL, 0
		)");
		sqlite_query2("INSERT INTO ${tp}users VALUES (
			1, '$admin_name', '".md5(strtolower($admin_name).$admin_pass)."', 1023
		)");
	}

	/*
	 * This shouldn't happen
	 */
	else {
		$title = "No DB Selected";
		$message = "You need to select a back end database";
		require_once "templates/error.php";
		exit;
	}


	/*
	 * With everything else done, try to seal the installation by
	 * writing config.php. Once it exists, the installer is disabled.
	 */
	if(is_writable("./")) {
		$fp = fopen("config.php", "w");
		fwrite($fp, $data);
		fclose($fp);
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
 * If any installation query fails, we want do die
 */
function mysql_query2($query) {
	if(!mysql_query($query)) {
		$title = "Error";
		$message = "Something failed mid-way through setting up the database... ";
		$data = "Query: $query\n\nError: ".mysql_error();
		require_once "templates/error.php";
		exit;
	}
}
function pgsql_query2($query) {
	if(!pg_query($query)) {
		$title = "Error";
		$message = "Something failed mid-way through setting up the database... ";
		$data = "Query: $query\n\nError: "."unknown :-/";
		require_once "templates/error.php";
		exit;
	}
}
function sqlite_query2($query) {
	if(!sqlite_query($query)) {
		$title = "Error";
		$message = "Something failed mid-way through setting up the database... ";
		$data = "Query: $query\n\nError: ".mysql_error();
		require_once "templates/error.php";
		exit;
	}
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
