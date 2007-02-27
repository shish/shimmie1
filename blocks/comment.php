<?php
/*
 * comment.php (c) Shish 2005, 2006
 *
 * Make a block of recent comments
 */

define(ERR_COMMENT_EMPTY, "Empty comment");
define(ERR_COMMENT_LIMIT_HIT, "Comment limit hit");
define(ERR_COMMENT_NO_ANON, "Anonymous commenting disabled");
define(ERR_COMMENT_NOT_ADMIN, "You need to be an admin to do that");

class comment extends block {
	function get_title() {
		return "Comments";
	}

	function query_to_array($query) {
		global $db;
		$result = $db->Execute($query);
		$array = array();
		while(!$result->EOF) {
			 $array[] = $result->fields;
			 $result->MoveNext();
		}
		return $array;
	}
	
	/*
	 * get comments for the image with id $image_id
	 */
	function get_comments($image_id) {
		$i_image_id = int_escape($image_id);
		
		$query = "
		SELECT 
			users.id as user_id, comments.id as id, image_id, 
			name, owner_ip, comment as scomment
		FROM comments
		LEFT JOIN users ON comments.owner_id=users.id 
		WHERE image_id=$i_image_id
		ORDER BY comments.id DESC
		";

		return $this->query_to_array($query);
	}

	/*
	 * get an array of the $count most recent comments,
	 * use the site default if $count is -1
	 */
	function get_recent_comments($count) {
		$i_count = int_escape($count >= 0 ? $count : get_config('recent_count'));
		
		$query = "
		SELECT 
			users.id as user_id, comments.id as id, image_id, name, owner_ip, 
			if(
				length(comment) > 100,
				concat(substring(comment, 1, 100), ' (...)'),
				comment
			) as scomment FROM comments
		LEFT JOIN users ON comments.owner_id=users.id 
		ORDER BY comments.id DESC
		LIMIT $i_count
		";

		return $this->query_to_array($query);
	}

	function comment_to_html($row) {
		global $user;

		$cid = $row['id'];
		$uid = $row['user_id'];
		$iid = $row['image_id'];
		$oip = $row['owner_ip'];
		$uname = htmlentities($row['name']);
		$comment = htmlentities($row['scomment']);
		$dellink = $user->isAdmin() ? 
			"<br>(<a href='metablock.php?block=comment&amp;".
			"action=delete&amp;comment_id=$cid'>Del</a>) ($oip)" : "";
		return "<p><a href='user.php?user_id=$uid'>$uname</a>: $comment ".
		       "<a href='view.php?image_id=$iid'>&gt;&gt;&gt;</a> $dellink</p>\n";
	}

	function get_postbox_html() {
		$image_id = int_escape($_GET['image_id']);
		return "
			<form action='metablock.php?block=comment&amp;action=add' method='POST'>
				<input type='hidden' name='image_id' value='$image_id'>
				<input id='commentBox' type='text' name='comment' value='Comment'>
				<input type='submit' value='Say' style='display: none;'>
			</form>
		";
	}

	function query_to_html($query) {
		global $db;
		$result = $db->Execute($query);
		$comments = "";
		while(!$result->EOF) {
			 $comments .= $this->comment_to_html($result->fields);
			 $result->MoveNext();
		}
		return $comments;
	}

	function get_html($pageType) {
		if($pageType == "index" || $pageType == "view") {
			if($pageType == "index") {
				$comments = $this->get_recent_comments(-1);
				foreach($comments as $comment) {
					$commentBlock .= $this->comment_to_html($comment);
				}
			}
			if($pageType == "view") {
				$comments = $this->get_comments($_GET['image_id']);
				foreach($comments as $comment) {
					$commentBlock .= $this->comment_to_html($comment);
				}
				$commentBlock .= $this->get_postbox_html();
			}

			return $commentBlock;
		}
	}

	function get_priority() {
		return 40;
	}

	function is_comment_limit_hit() {
		global $user, $db;

		$window = int_escape(get_config('comment_window'));
		$max = int_escape(get_config('comment_limit'));
		
		$result = $db->Execute("SELECT * FROM comments WHERE owner_ip = ? ".
							   "AND posted > date_sub(now(), interval ? minute)",
					 		   Array($user->ip, $window));
		$recent_comments = $result->RecordCount();

		return ($recent_comments >= $max);
	}

	function add_comment($image_id, $comment) {
		global $user, $db;
		
		if(!get_config('comment_anon') && $user->isAnonymous()) {
			return ERR_COMMENT_NO_ANON;
		}
		else if(trim($comment) == "") {
			return ERR_COMMENT_EMPTY;
		}
		else if($this->is_comment_limit_hit()) {
			return ERR_COMMENT_LIMIT_HIT;
		}
		else {
			$db->Execute("INSERT INTO comments(image_id, owner_id, owner_ip, posted, comment) ".
			             "VALUES(?, ?, ?, now(), ?)", Array($image_id, $user->id, $user->ip, $comment));
			return $db->Insert_ID();
		}
	}

	function delete_comment($comment_id) {
		global $user, $db;
		
		if($user->isAdmin()) {
			$db->Execute("DELETE FROM comments WHERE id=?", Array($comment_id));
			return true;
		}
		else {
			return ERR_COMMENT_NOT_ADMIN;
		}
	}

	function run($action) {
		if($action == "delete") {
			admin_or_die();
			delete_comment(defined_or_die($_GET["comment_id"]));
			header("X-Shimmie-Status: OK - Comment Deleted");
			header("Location: index.php");
			echo "<a href='index.php'>Back</a>";
		}

		if($action == "add") {
			$image_id = defined_or_die($_POST['image_id']);
			$comment = defined_or_die($_POST['comment']);
			$i_image_id = int_escape($image_id);
			
			$comment_id = $this->add_comment($image_id, $comment);
						
			switch($comment_id) {
				case ERR_COMMENT_NO_ANON:
					$i_image_id = int_escape($image_id);
					header("X-Shimmie-Status: Error - Anonymous commenting disabled");
					$title = "Anonymous commenting disabled";
					$message = "<a href='view.php?image_id=$i_image_id'>Back</a>";
					require_once get_theme_template();
					break;
				case ERR_COMMENT_EMPTY:
					$i_image_id = int_escape($image_id);
					header("X-Shimmie-Status: Error - Blank Comment");
					$title = "No Message";
					$message = "Comment was empty; <a href='view.php?image_id=$i_image_id'>Back</a>";
					require_once get_theme_template();
					break;
				case ERR_COMMENT_LIMIT_HIT:
					$window = get_config('comment_window');
					$max = get_config('comment_limit');
					$i_image_id = int_escape($image_id);
					header("X-Shimmie-Status: Error - Comment Limit Hit");
					$title = "Comment Limit Hit";
					$message = "To prevent spam, users are only allowed $max comments per $window minutes";
					require_once get_theme_template();
					break;
				default:
					// go back to the viewed page
					header("Location: view.php?image_id=$i_image_id");
					header("X-Shimmie-Status: OK - Comment Added");
					header("X-Shimmie-Comment-ID: $comment_id");
					echo "<a href='view.php?image_id=$i_image_id'>Back</a>";
					break;
			}
		}
	}

	function get_xmlrpc_funclist() {
		return array("get_comments", "get_recent_comments", "add_comment", "delete_comment");
	}
}
?>
