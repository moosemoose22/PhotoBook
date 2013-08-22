<?
	if (isset($_POST["BookLoginUsername"]))
	{
		$BookLoginUsername = $_POST["BookLoginUsername"];
		$BookLoginPassword = $_POST["BookLoginPassword"];
	}
	else
	{
		$BookLoginUsername = $_SESSION["BookLoginUsername"];
		$BookLoginPassword = $_SESSION["BookLoginPassword"];
	}

	if (isset($BookLoginUsername))
	{
		$password_str = "SELECT COUNT(*) AS LoginCount
						FROM BookUsers
						WHERE BookLoginUsername = '" . $BookLoginUsername . "'
						AND BookLoginPassword = '" . $BookLoginPassword . "';";
		$result_mysql = $mysqli->query($password_str);

		$row = $result_mysql->fetch_assoc();

		$LoginCount = $row['LoginCount'];

		if ($LoginCount == 0)
header("Location: {$g_admin_location}login.php?message=login_failed"); 
		else
		{
			$_SESSION["BookLoginUsername"] = $BookLoginUsername;
			$_SESSION["BookLoginPassword"] = $BookLoginPassword;
			if (!isset($_SESSION["BookID"]))
			{
				if (basename($_SERVER['PHP_SELF']) == "book_admin.php")
header("Location: {$g_admin_location}chooseBook.php"); 
			}
			else
			{
				$book_title_str = "SELECT BookTitle, BookLangID
									FROM Books
									WHERE BookID = " . $_SESSION['BookID'] . ";";
				$book_title_sql = $mysqli->query($book_title_str);
				$row = $book_title_sql->fetch_assoc();
				$g_book_title = $row['BookTitle'];
				$g_book_default_lang = $row['BookLangID'];
				$book_title_sql->free();							
			}
		}
	}
	else
header("Location: {$g_admin_location}login.php?message=initial_login"); 
?>