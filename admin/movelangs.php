<? include("dbconnect.php") ?>
<html>
<head>
	<title>Book</title>
</head>
<body>
<?
/******************************
Whenever you get a PHP error with DBs, use this code
	if (!$intro_mysql) {
	   throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
	}
*******************************/

	$query_str = "SELECT pages.BookPageNum, pageArticles.BookPageArtLandscapeX, pageArticles.BookPageArtLandscapeY,
					pageArticles.BookPageArtPortraitX, pageArticles.BookPageArtPortraitY, articlesTranslated.*
			FROM BookPages pages
			INNER JOIN BookPageArticles pageArticles
				ON  pages.BookPageID = pageArticles.BookPageID
			INNER JOIN BookArticles articles
				ON pageArticles.BookArticleID = articles.BookArticleID
			INNER JOIN BookArticleLangs articlesTranslated
				ON articles.BookArticleID = articlesTranslated.BookArticleID
				AND articles.BookArticleDefaultLang = articlesTranslated.BookLangID
			WHERE pages.BookPageNum = 9
				AND BookID = 1;";
	$photo_mysql = $mysqli->query($query_str);
	$new_stuff = "";
	while ($row = $photo_mysql->fetch_assoc())
	{
		echo $row['BookArticleLangText'];
	}
	$photo_mysql->free();
	if (!$photo_mysql) {
	   throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
	}
?>
</body>
</html>
<? include("dbclose.php") ?>