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
	function query_to_array($query) {
		$result = sql_query($query);
		$array = array();
		while($row = sql_fetch_row($result)) {
			 $array[] = $row;
		}
		return $array;
	}
	
	/*
	 * get comments for the image with id $image_id
	 */
	function get_comments($image_id) {
		$s_image_id = int_escape($image_id);
		
		$query = "
		SELECT 
			shm_comments.id as id, image_id, 
			name, owner_ip, comment as scomment
		FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		WHERE image_id=$s_image_id
		ORDER BY shm_comments.id DESC
		";

		return $this->query_to_array($query);
	}

	/*
	 * get an array of the $count most recent comments,
	 * use the site default if $count is -1
	 */
	function get_recent_comments($count) {
		global $config;

		$s_count = int_escape($count >= 0 ? $count : $config['recent_count']);
		
		$query = "
		SELECT 
			shm_comments.id as id, image_id, name, owner_ip, 
			if(
				length(comment) > 100,
				concat(substring(comment, 1, 100), ' (...)'),
				comment
			) as scomment FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		ORDER BY shm_comments.id DESC
		LIMIT $s_count
		";

		return $this->query_to_array($query);
	}

	function comment_to_html($row) {
		global $user;

		$cid = $row['id'];
		$iid = $row['image_id'];
		$oip = $row['owner_ip'];
		$uname = htmlentities($row['name']);
		$comment = htmlentities($row['scomment']);
		$dellink = $user->isAdmin() ? 
			"<br>(<a href='metablock.php?block=comment&amp;".
			"action=delete&amp;comment_id=$cid'>Del</a>) ($oip)" : "";
		return "<p><a href='view.php?image_id=$iid'>$uname</a>: $comment$dellink</p>\n";
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
		$com_result = sql_query($query);
		$comments = "";
		while($comment = sql_fetch_row($com_result)) {
			 $comments .= $this->comment_to_html($comment);
		}
		return $comments;
	}

	function get_html($pageType) {
		if($pageType == "index" || $pageType == "view") {
			$commentBlock = "<h3 id=\"comments-toggle\" onclick=\"toggle('comments')\">Comments</h3>";
			$commentBlock .= "<div id=\"comments\">";

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
		
			$commentBlock .= "</div>\n";

			return $commentBlock;
		}
	}

	function get_priority() {
		return 40;
	}

	function is_comment_limit_hit() {
		global $config, $user;

		$window = int_escape($config['comment_window']);
		$max = int_escape($config['comment_limit']);
		
		$last_query = "SELECT count(*) AS recent_comments FROM shm_comments ".
		              "WHERE owner_ip = '{$user->ip}' ".
					  "AND posted > date_sub(now(), interval $window minute)";
		$row = sql_fetch_row(sql_query($last_query));
		$recent_comments = $row["recent_comments"];

		return ($recent_comments >= $max);
	}

	function add_comment($image_id, $comment) {
		global $user, $config;
		
		$s_image_id = int_escape($image_id);
		$s_comment = sql_escape($comment);
		
		if(!$config['comment_anon'] && $user->isAnonymous()) {
			return ERR_COMMENT_NO_ANON;
		}
		else if(trim($comment) == "") {
			return ERR_COMMENT_EMPTY;
		}
		else if($this->is_comment_limit_hit()) {
			return ERR_COMMENT_LIMIT_HIT;
		}
		else {
			$new_query = "INSERT INTO shm_comments(image_id, owner_id, owner_ip, posted, comment) ".
			             "VALUES($s_image_id, {$user->id}, '{$user->ip}', now(), '$s_comment')";
			sql_query($new_query);
			$cid = sql_insert_id();

			return $cid;
		}
	}

	function delete_comment($comment_id) {
		global $user;
		
		if($user->isAdmin()) {
			$i_comment_id = int_escape($comment_id);
			sql_query("DELETE FROM shm_comments WHERE id=$i_comment_id");
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
					require_once "templates/generic.php";
					break;
				case ERR_COMMENT_EMPTY:
					$i_image_id = int_escape($image_id);
					header("X-Shimmie-Status: Error - Blank Comment");
					$title = "No Message";
					$message = "Comment was empty; <a href='view.php?image_id=$i_image_id'>Back</a>";
					require_once "templates/generic.php";
					break;
				case ERR_COMMENT_LIMIT_HIT:
					global $config;
					$window = $config['comment_window'];
					$max = $config['comment_limit'];
					$i_image_id = int_escape($image_id);
					header("X-Shimmie-Status: Error - Comment Limit Hit");
					$title = "Comment Limit Hit";
					$message = "To prevent spam, users are only allowed $max comments per $window minutes";
					require_once "templates/generic.php";
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
