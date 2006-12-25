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
		global $user, $config;
		
		// Don't show the block if logins are disabled
		if(!$config['login_enabled']) return;


		if($user->isAnonymous()) {
			return <<<EOD
				<form action="user.php?action=login" method="POST">
					<table border="1" summary="Login Form">
					
						<tr><td>Name</td><td width="50%"><input type="text" name="user"></td></tr>
						<tr><td>Password</td><td><input type="password" name="pass"></td></tr>
						<tr><td>New Account?</td><td><input type="checkbox" name="create" onchange="toggleLogin(this,gobu)"></td></tr>
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
