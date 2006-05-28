<?php
/*
 * user.php (c) Shish 2005, 2006
 *
 * Show either login prompt, or links to user info
 */


// Don't show the block if logins are disabled
if(!$config['login_enabled']) return;


/*
 * if not logged in, show "log in" box
 */
if(is_null($user->name) || ($user->name == "Anonymous")) {
	$blocks[80] = <<<EOD
	<h3 onclick="toggle('user')">User Login</h3>
	<div id="user">
		<form action="user.php?action=login" method="POST">
			<table border="1" width="150" summary="Login Form">
				<tr><td>Name</td><td><input type="text" name="user"></td></tr>
				<tr><td>Password</td><td><input type="password" name="pass"></td></tr>
				<tr><td>New Account?</td><td><input type="checkbox" name="create" onchange="toggleLogin(this,gobu)"></td></tr>
				<tr><td colspan="2"><input type="submit" name="gobu" value="Log In"></td></tr>
			</table>
		</form>
	</div>
EOD;
}


/*
 * If logged in, show links to control panels
 */
else {
	if($user->isAdmin) {
		$extra = "<br/><a href='setup.php'>Board Config</a>";
		$extra .= "<br/><a href='admin.php'>Admin</a>";
	}
	else {
		$extra = "";
	}
	$blocks[] = <<<EOD
	<h3 onclick="toggle('user')">User Info</h3>
	<div id="user">
		Logged in as $user->name
		<br/><a href='user.php'>User Config</a>
		$extra
		<br/><a href='user.php?action=logout'>Log Out</a>
	</div>
EOD;
}
?>
