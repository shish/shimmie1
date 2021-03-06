<?php
function update_version($ver) {
	global $db;
	$db->Execute("DELETE FROM config WHERE name='db_version'");
	$db->Execute("INSERT INTO config (name, value) VALUES (?, ?)", Array('db_version', $ver));
}

$db_current = get_config('db_version');

if($_GET['do_upgrade'] != 'yes') {
	$title = "Database Update Needed";
	$body["Explanation"] = "
	    DB is at version $db_current, should be $db_version
		<p>Please make sure you have done a update_logbase backup, then
		click <a href='index.php?do_upgrade=yes'>here</a> to 
		update the update_logbase schema.";
	require_once get_theme_template();
	exit;
}
else {
	$title = "Updating...";
	$body["The Plan"] = "Currently at DB version $db_current, updating to $db_version...";
	$update_log = "Update log:\n";

	/*
	 * Yay for falling through; this switch jumps to the current
	 * DB version, and goes through applying updates until it hits
	 * the latest code version
	 */
	switch(get_config('db_version')) {
		default:
			$update_log .= "At version ummmm... wtf? Something broke :|\n";
			$update_log .= "Attempting DB upgrades from the start...\n";

		case 'pre-0.7.5': // the default version
			$db->StartTrans();
			$update_log .= "At version pre-0.7.5\n";
			$users_cols = $db->MetaColumnNames("users");
			if(isset($users_cols['JOINDATE'])) {
				$update_log .= "Users already has a joindate column; skipping update\n";
			}
			else {
				$update_log .= "Adding joindate to users\n";
				$db->Execute("ALTER TABLE users ADD COLUMN joindate DATETIME NOT NULL");
				$db->Execute("UPDATE users SET joindate=now()");
			}
			update_version('0.7.5');
			$db->CommitTrans();
		
		case '0.7.5':
			$db->StartTrans();
			$update_log .= "At version 0.7.5\n";
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
				$update_log .= "You seem to already be using the new tags schema...";
			}
			else {
				$update_log .= "Moving tags to tags_old\n";
				$db->Execute("RENAME TABLE tags TO tags_old");
		
				$update_log .= "Creating image_tags table";
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
		
				$update_log .= "Creating tags table\n";
				$db->Execute("
					CREATE TABLE tags (
						id int primary key auto_increment,
						tag varchar(255) not null,
						UNIQUE(tag)
					)
				");
		
				$update_log .= "Converting tags_old to image_tags + tags\n";
				$result = $db->Execute("SELECT * FROM tags_old");
				$tag_ids = Array();
				$anon_id = int_escape(get_config('anon_id'));
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
					$db->Execute("INSERT INTO image_tags(image_id, tag_id, owner_id) VALUES (?, ?, ?)",
						Array($image_id, $tag_id, $anon_id));
					$result->MoveNext();
				}
				$db->CommitTrans();
			}
			break;
	}
	
	$body["Update Log"] = gen_textarea($update_log);
	require_once get_theme_template();
	exit;
}
?>
