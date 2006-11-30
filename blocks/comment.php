<?php
/*
 * comment.php (c) Shish 2005, 2006
 *
 * Make a block of recent comments
 */

class comment extends block {
	function get_index_query() {
		global $config;
		
		$com_count = int_escape($config["recent_count"]);
		
		return "
		SELECT 
			shm_comments.id as id, image_id, name, owner_ip, 
			if(
				length(comment) > 100,
				concat(substring(comment, 1, 100), ' (...)'),
				comment
			) as scomment FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		ORDER BY shm_comments.id DESC
		LIMIT $com_count
		";
	}

	function get_view_query() {
		$s_image_id = int_escape($_GET['image_id']);
		
		return "
		SELECT 
			shm_comments.id as id, image_id, 
			name, owner_ip, comment as scomment
		FROM shm_comments
		LEFT JOIN users ON shm_comments.owner_id=users.id 
		WHERE image_id=$s_image_id
		ORDER BY shm_comments.id DESC
		";
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
				$commentBlock .= $this->query_to_html($this->get_index_query());
			}
			if($pageType == "view") {
				$commentBlock .= $this->query_to_html($this->get_view_query());
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

	function run($action) {
		if($action == "delete") {
			admin_or_die();

			$comment_id = int_escape(defined_or_die($_GET["comment_id"]));
			sql_query("DELETE FROM shm_comments WHERE id=$comment_id");
			header("X-Shimmie-Status: OK - Comment Deleted");
			header("Location: index.php");
			echo "<a href='index.php'>Back</a>";
		}


		if($action == "add") {
			global $user, $config;

			$config["comment_anon"] || user_or_die();

			// get input
			$image_id = int_escape(defined_or_die($_POST['image_id']));
			$comment = sql_escape(defined_or_die($_POST['comment']));

			// check validity
			if(trim($comment) == "") {
				header("X-Shimmie-Status: Error - Blank Comment");
				$title = "No Message";
				$message = "Comment was empty; <a href='view.php?image_id=$image_id'>Back</a>";
				require_once "templates/generic.php";
			}
			else if($this->is_comment_limit_hit()) {
				$window = $config['comment_window'];
				$max = $config['comment_limit'];

				header("X-Shimmie-Status: Error - Comment Limit Hit");
				$title = "Comment Limit Hit";
				$message = "To prevent spam, users are only allowed $max comments per $window minutes";
				require_once "templates/generic.php";
			}
			else {
				$new_query = "INSERT INTO shm_comments(image_id, owner_id, owner_ip, posted, comment) ".
				             "VALUES($image_id, {$user->id}, '{$user->ip}', now(), '$comment')";
				sql_query($new_query);
				$cid = sql_insert_id();
		
				// go back to the viewed page
				header("Location: view.php?image_id=$image_id");
				header("X-Shimmie-Status: OK - Comment Added");
				header("X-Shimmie-Comment-ID: $cid");
				echo "<a href='view.php?image_id=$image_id'>Back</a>";
			}
		}
	}
}
?>
