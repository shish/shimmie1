<?php
function update_version($ver) {
	sql_query("DELETE FROM shm_config WHERE name='db_version'");
	sql_query("INSERT INTO shm_config (name, value) VALUES ('db_version', '$ver')");
	update_status("DB updated to version $ver");
}

function update_status($text) {
	print("<br>$text");
}

if($_GET['do_upgrade'] != 'yes') {
?>
<html>
	<head>
		<title>DB Update Needed</title>
	</head>
	<body>
		Please make sure you have done a database backup, then 
		click <a href='index.php?do_upgrade=yes'>here</a> to 
		update the database schema.
	</body>
</html>
<?php
	exit;
}

/*
 * Yay for falling through; this switch jumps to the current
 * DB version, and goes through applying updates until it hits
 * the latest code version
 */
switch($config['db_version']) {
	case 'pre-0.7.5': // the default version
		update_status("Adding joindate to users");
		sql_query("ALTER TABLE shm_users ADD COLUMN joindate DATETIME NOT NULL");
		sql_query("UPDATE shm_users SET joindate=now()");
		update_version('0.7.5');

	case '0.7.5':
		break; // latest

	case 'future':
		update_status("Moving tags to tags_old");
		sql_query("
			RENAME TABLE shm_tags TO shm_tags_old
		");
		update_status("Creating image_tags table");
		sql_query("
			CREATE TABLE shm_image_tags (
				image_id int not null,
				tag_id int not null,
				owner_id int not null,
				UNIQUE(image_id, tag_id),
				INDEX(image_id),
				INDEX(tag_id)
			)
		");
		update_status("Creating tags table");
		sql_query("
			CREATE TABLE shm_tags (
				id int primary key auto_increment,
				tag varchar(255) not null
			)
		");
		update_status("Converting tags_old to image_tags + tags");
		$result = sql_query("SELECT * FROM shm_tags_old");
		$tag_ids = Array();
		$anon_id = (int)$config['anon_id'];
		while($row = sql_fetch_row($result)) {
			$tag_id = $tags_ids[$row['tag']];
			$image_id = int_escape($row['image_id']);
			$s_tag = sql_escape($row['tag']);
			if(is_null($tag_id)) {
				sql_query("INSERT INTO shm_tags(tag) VALUES('$s_tag')");
				$tag_id = sql_insert_id();
				$tag_ids[$row['tag']] = $tag_id;
			}

			sql_query("
				INSERT INTO shm_image_tags(image_id, tag_id, owner_id)
				VALUES ($image_id, $tag_id, $anon_id)
			");
		}
		break;
}
?>
