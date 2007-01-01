<?php
/*
 * userinfo.php (c) Shish 2005, 2006
 *
 * Show either login prompt, or links to user info
 */

class userinfo extends block {
	function get_title() {
		return "User Info";
	}

	function get_html($pageType) {
		global $user;
		
		// Don't show the block if logins are disabled
		if(!get_config('login_enabled')) return;


		if($user->isAnonymous()) {
			return <<<EOD
				<!--
				<form action="user.php?action=login" method="POST">
					<div>
						<small>Name</small>
						<input type="text" name="user">
					</div>
					<div>
						<small>Password</small>
						<input type="password" name="pass">
					</div>
					<div>
						<small>New Account?</small>
						<input type="checkbox" onchange="toggleLogin(this,gobu)">
					</div>
					<div id="pass_confirm">
						<small>Confirm Password</small>
						<input id="pass_confirm_2" type="password" name="pass2">
					</div>
					<div>
						<input type="submit" name="gobu" value="Log In">
					</div>
				</form>
				-->
				<form action="user.php?action=login" method="POST">
					<table border="1" summary="Login Form">
						<tr><td width="70">Name</td><td width="70"><input type="text" name="user"></td></tr>
						<tr><td>Password</td><td><input type="password" name="pass"></td></tr>
						<tr><td>New Account?</td><td><input type="checkbox" name="create" onchange="toggleLogin(this,gobu)"></td></tr>
						<!-- <tr id="pass_confirm"><td>Confirm Password</td><td><input type="password" name="pass2"></td></tr> -->
						<tr><td colspan="2"><input type="submit" name="gobu" value="Log In"></td></tr>
					</table>
				</form>
EOD;
		}
		else {
			if($user->isAdmin()) {
				$extra = "<br/><a href='setup.php'>Board Config</a>";
				$extra .= "<br/><a href='admin.php'>Admin</a>";
			}
			else {
				$extra = "";
			}
			return <<<EOD
				Logged in as $user->name
				<br/><a href='user.php'>User Config</a>
				$extra
				<br/><a href='user.php?action=logout'>Log Out</a>
EOD;
		}
	}

	function get_priority() {
		return 80;
	}
}
?>
