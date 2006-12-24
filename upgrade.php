<?php
function update_version($ver) {
	global $db;
	$db->Execute("DELETE FROM config WHERE name='db_version'");
	$db->Execute("INSERT INTO config (name, value) VALUES (?, ?)", Array('db_version', $ver));
}

function update_status($text) {
	print("<br>$text");
}

$db_current = $config['db_version'];

if($_GET['do_upgrade'] != 'yes') {
	$title = "Database Update Needed";
	$message = "DB is at version $db_current, should be $db_version
		<p>Please make sure you have done a database backup, then
		click <a href='index.php?do_upgrade=yes'>here</a> to 
		update the database schema.";
	require_once "templates/generic.php";
	exit;
}
else {
	$title = "Updating...";
	$message = "Currently at DB version $db_current, updating to $db_version...";
	$data = "Update log:\n";

	/*
	 * Yay for falling through; this switch jumps to the current
	 * DB version, and goes through applying updates until it hits
	 * the latest code version
	 */
	switch($config['db_version']) {
		default:
			$data .= "At version ummmm... wtf? Something broke :|\n";
			$data .= "Attempting DB upgrades from the start...\n";

		case 'pre-0.7.5': // the default version
			$db->StartTrans();
			$data .= "At version pre-0.7.5\n";
			$users_cols = $db->MetaColumnNames("users");
			if(isset($users_cols['JOINDATE'])) {
				$data .= "Users already has a joindate column; skipping update\n";
			}
			else {
				$data .= "Adding joindate to users\n";
				$db->Execute("ALTER TABLE users ADD COLUMN joindate DATETIME NOT NULL");
				$db->Execute("UPDATE users SET joindate=now()");
			}
			update_version('0.7.5');
			$db->CommitTrans();
		
		case '0.7.5':
			$db->StartTrans();
			$data .= "At version 0.7.5\n";
			// do 0.7.5 -> 0.7.6 stuff
			// update_version('0.7.6');
			$db->CommitTrans();

		// latest
			update_version($db_version);
			break;
	
		case 'future':
			$db->StartTrans();

			$tags_cols = $db->MetaColumnNames("tags");
			if(isset($tags_cols['ID'])) {
				$data .= "You seem to already be using the new tags schema...";
			}
			else {
				update_status("Moving tags to tags_old");
				$db->Execute("RENAME TABLE tags TO tags_old");
		
				update_status("Creating image_tags table");
				$db->Execute("
					CREATE TABLE image_tags (
						image_id int not null,
						tag_id int not null,
						owner_id int not null,
						UNIQUE(image_id, tag_id),
						INDEX(image_id),
						INDEX(tag_id)
					)
				");
		
				update_status("Creating tags table");
				$db->Execute("
					CREATE TABLE tags (
						id int primary key auto_increment,
						tag varchar(255) not null,
						UNIQUE(tag)
					)
				");
		
				update_status("Converting tags_old to image_tags + tags");
				$result = $db->Execute("SELECT * FROM tags_old");
				$tag_ids = Array();
				$anon_id = int_escape($config['anon_id']);
				$triplets = Array();
				while(!$result->EOF) {
					$row = $result->fields;
					$tag = $row['tag'];
					$image_id = int_escape($row['image_id']);
					if(isset($tag_ids[$tag])) {
						$tag_id = $tag_ids[$tag];
					}
					else {
						$db->Execute("INSERT INTO tags(tag) VALUES(?)", Array($tag));
						$tag_id = $db->Insert_ID();
						$tag_ids[$tag] = $tag_id;
					}
					$triplets[] = Array($image_id, $tag_id, $anon_id);
					$result->MoveNext();
				}
				$db->Execute("INSERT INTO image_tags(image_id, tag_id, owner_id) VALUES (?, ?, ?)", $triplets);
				$db->CommitTrans();
			}
			break;
	}
	
	require_once "templates/generic.php";
	exit;
}
?>
