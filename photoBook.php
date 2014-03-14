<?
	include("_sharedIncludes/dbconnect.php");
	include("_sharedIncludes/globals.php");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Book</title>
	<script src="http://code.jquery.com/jquery-2.1.0.js"></script>
	<script src="admin/BasicDataStructures.js"></script>
	<script>
<?

/************************************************
*	NOTE: All this server code will be moved    *
*	to an AJAX request eventually.              *
************************************************/

	$BookID = $_GET['bookID'];
	if ($BookID == "" || !is_numeric($BookID))
	{
		echo "var loaded = false;
				function onload(){document.write('Please specify a bookID in the URL<br />Example:book2.php?bookID=1');}";
	}
	else
	{
		echo "var loaded = true;";
		$bookTitle = "";
		$bookAuthor = "";
		$query_str = "SELECT BookLoginUsername as BookURLprefix, BookTitleTitle, BookTitleAuthor, BookDefaultLangID, BookDefaultPageID 
					FROM BookTitleLangs 
					INNER JOIN Books 
					ON BookTitleLangs.BookID = Books.BookID 
					AND Books.BookDefaultLangID = BookTitleLangs.BookLangID 
					WHERE BookTitleLangs.BookID = {$BookID}
						AND BookTitleIsDefault = 1;";
		$book_title_sql = $mysqli->query($query_str);
		$row = $book_title_sql->fetch_assoc();
		if ($row)
		{
			$BookURLprefix = $row['BookURLprefix'];
			$BookTitle = $row['BookTitleTitle'];
			$BookAuthor = $row['BookTitleAuthor'];
			$BookDefaultLangID = $row['BookDefaultLangID'];
			$BookDefaultPageID = $row['BookDefaultPageID'];
		}
	}
?>
		var g_bookID = "<?=$BookID?>";
		var g_bookLang = "<?=$BookDefaultLangID?>";
		var g_imageRoot = "<?=$g_image_web_location . '/' . $BookURLprefix . '/'?>";
		var g_photoObjectType = "<?=$g_photo_object_name?>";
		var g_articleObjectType = "<?=$g_article_object_name?>";

	</script>
	<script src="ClientDataStructures.js"></script>
	<script>
		$(document).ready(function()
		{
			if (loaded)
			{
				ClientCommunicator.getData({loadPageData: 'true', pageID:'<?=$BookDefaultPageID?>', BookID: g_bookID, BookLang: g_bookLang});
				ClientCommunicator.getData({loadPages: 'true', BookID: g_bookID});
				ClientCommunicator.getData({loadPhotos: 'true', BookID: g_bookID, BookLang: g_bookLang});
				ClientCommunicator.getData({loadPhotoInstances: 'true', BookID: g_bookID, BookLang: g_bookLang});
				ClientCommunicator.getData({loadArticles: 'true', BookID: g_bookID, BookLang: g_bookLang});
				ClientCommunicator.getData({loadArticleInstances: 'true', BookID: g_bookID, BookLang: g_bookLang});
				PageManager.init();
			}
			else
				onload();
		});
	</script>
</head>
<body style="overflow-y: auto; overflow-x: hidden">
<table style="position: absolute; left: 0px; right: 0px;">
<tr><td>
	<h1><?=$BookTitle?></h1>
	<h4><i><?=$BookAuthor?></i></h4>
</td><td id="selectPageContainer">
	<select id="selectPage" onchange="PageManager.setCurrentPage(this.value, false)"></select>
</td></tr>
<tr><td>
	<div id="photoBookBody"></div>
</td></tr>
</table>
</body>
</html>
<? include("dbclose.php") ?>