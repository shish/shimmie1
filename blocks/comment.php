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
		ORDER BY comments.id ASC
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

	function comment_to_html($row, $link_to_image=True) {
		global $user;

		$cid = $row['id'];
		$uid = $row['user_id'];
		$iid = $row['image_id'];
		$oip = $row['owner_ip'];
		$uname = html_escape($row['name']);
		$comment = html_escape($row['scomment']);
		$userlink = "<a href='user.php?user_id=$uid'>$uname</a>";
		$dellink = $user->isAdmin() ? 
			"<br>(<a href='metablock.php?block=comment&amp;".
			"action=delete&amp;comment_id=$cid'>Del</a>) ($oip)" : "";
		$imagelink = $link_to_image ? "<a href='view.php?image_id=$iid'>&gt;&gt;&gt;</a>\n" : "";
		return "<p>$userlink: $comment $dellink $imagelink</p>";
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

			$commentBlock .= "<p><a href='metablock.php?block=comment&action=list'>Full List &gt;&gt;&gt;</a>";

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
		switch($action) {
			case "list":   $this->show_list_page(); break;
			case "delete": $this->delete_comment_wrapper(); break;
			case "add":    $this->add_comment_wrapper(); break;
		}
	}

	function delete_comment_wrapper() {
		admin_or_die();
		delete_comment(defined_or_die($_GET["comment_id"]));
		header("Location: index.php");
		echo "<a href='index.php'>Back</a>";
	}

	function add_comment_wrapper() {
		$image_id = defined_or_die($_POST['image_id']);
		$comment = defined_or_die($_POST['comment']);
		$i_image_id = int_escape($image_id);
		
		$comment_id = $this->add_comment($image_id, $comment);
					
		switch($comment_id) {
			case ERR_COMMENT_NO_ANON:
				$title = "Anonymous commenting disabled";
				$message = "<a href='view.php?image_id=$i_image_id'>Back</a>";
				require_once get_theme_template();
				break;
			case ERR_COMMENT_EMPTY:
				$title = "No Message";
				$message = "Comment was empty; <a href='view.php?image_id=$i_image_id'>Back</a>";
				require_once get_theme_template();
				break;
			case ERR_COMMENT_LIMIT_HIT:
				$max = get_config('comment_limit');
				$window = get_config('comment_window');
				$title = "Comment Limit Hit";
				$message = "To prevent spam, users are only allowed $max comments per $window minutes";
				require_once get_theme_template();
				break;
			default:
				// go back to the viewed page
				header("Location: view.php?image_id=$i_image_id");
				echo "<a href='view.php?image_id=$i_image_id'>Back</a>";
				break;
		}
	}

	function show_list_page() {
		global $db;

		$start = int_escape(is_null($_GET['start']) ? 0 : $_GET['start']);

		$get_threads = "
			SELECT image_id,MAX(posted) AS latest
			FROM comments
			GROUP BY image_id
			ORDER BY latest
			DESC LIMIT $start,10
		";
		$result = $db->Execute($get_threads);
		
		# FIXME: paginator
		$title = "Comments";
		$thisurl = "metablock.php?block=comment&action=list";
		$prev = ($start == 0) ? "Prev" : "<a href='$thisurl&start=".($start-10)."'>Prev</a>";
		$index = "<a href='index.php'>Index</a>";
		$next = ($result->RecordCount() < 10) ? "Next" : "<a href='$thisurl&start=".($start+10)."'>Next</a>";
		$blocks["Navigation"] = "$prev | $index | $next";
		$blocks = array_merge($blocks, get_blocks_html("comments"));

		while(!$result->EOF) {
			$image_id = $result->fields["image_id"];
			$image = new Image($image_id);
			$comments = $this->get_comments($image_id);

			$html = "<div style='text-align: left'>";
			$html .= "<a href='{$image->vlink}'>";
			$html .= "<img src='{$image->tlink}' align='left'></a>";
			foreach($comments as $comment) {
				$html .= $this->comment_to_html($comment, False);
			}
			$html .= "</div>";
			$html .= "<div style='clear:both;'>&nbsp;</div>";
			$body["{$image->id}: {$image->tags}"] = $html;
			$result->MoveNext();
		}
		require_once get_theme_template();
	}

	function get_xmlrpc_funclist() {
		return array("get_comments", "get_recent_comments", "add_comment", "delete_comment");
	}
}
?>
