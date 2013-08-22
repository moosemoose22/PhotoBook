<?
if (isset($_GET['message']))
{
	echo "<div style=\"color: red; font-weight: bold; text-align: center\">";
	$message = $_GET['message'];
	if ($message == "logout")
	{
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		echo "You have successfully logged out<br />";
	}
	elseif ($message == "initial_login")
		echo "You need to log in first<br />";
	elseif ($message == "login_failed")
		echo "Your attempt to log in failed<br />";
	echo "</div>";
}
?>
<html>
<head>
<title>Book Login Page</title>
</head>
<body onload="document.LoginForm.BookLoginUsername.focus()">
<form name="LoginForm" method="post" action="book_admin.php">
<table align="center">
<tr><td>Username</td><td><input type="text" name="BookLoginUsername" /></td></tr>
<tr><td>Password</td><td><input type="password" name="BookLoginPassword" /></td></tr>
<tr><td align="center" colspan="2"><input type="submit" value="Log in" 
/></td></tr>
</table>
</form>
</body>
</html>