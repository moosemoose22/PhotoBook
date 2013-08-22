<?
	include("dbconnect.php");
	include("checkLogin.php");
	include("globals.php");
	// Book data population ***********************************
	$book_str = "SELECT BookID, BookTitle
				FROM Books
				WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "';";
	$book_sql = $mysqli->query($book_str);
	$book_array = array();
	$counter = 0;
	while ($row = $book_sql->fetch_assoc())
	{
		$book_array[$counter][0] = $row['BookID'];
		$book_array[$counter][1] = $row['BookTitle'];
		$counter++;
	}
	$book_sql->free();

	if (sizeof($book_array) == 1)
	{
		$_SESSION["BookID"] = $book_array[0][0];
header("Location: /book/book_admin.php"); 
	}
?>
<!DOCTYPE html>
<html>
<head>
<title>Choose a Book to edit</title>
</head>
<body>
</body>
</html>
<? include("dbclose.php") ?>