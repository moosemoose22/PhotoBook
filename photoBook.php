<?
	include("_sharedIncludes/dbconnect.php");
	include("_sharedIncludes/globals.php");
?>
<html>
<head>
	<title>Book</title>
</head>
<body>
<?	$BookID = $_GET['bookID'];
	if ($BookID == "" || !is_numeric($BookID))
	{
		echo "Please specify a bookID in the URL<br />Example:book2.php?bookID=1";
		exit(0);
	}
	else
	{
		$bookTitle = "";
		$bookAuthor = "";
		$query_str = "SELECT bookTitleTitle, bookTitleAuthor
					FROM bookTitleLangs
					WHERE bookTitleBookID = {$BookID}
						AND bookTitleIsDefault = 1";
		$book_title_sql = $mysqli->query($query_str);
		$row = $book_title_sql->fetch_assoc();
		if ($row)
		{
			$bookTitle = $row['bookTitleTitle'];
			$bookAuthor = $row['bookTitleAuthor'];
		}
	}
?>
<h1><?=$bookTitle?></h1>
<h4><i><?=$bookAuthor?></i></h4>
</body>
</html>
<? include("dbclose.php") ?>