<?
	include("_sharedIncludes/dbconnect.php");
	include("_sharedIncludes/globals.php");
?>
<html>
<head>
	<title>Book</title>
	<script>
		var g_orientation = "landscape";
		var g_showArticles = false;
		var g_showImageData = false;
		function objectContainer()
		{
			this.landscape;
			this.portrait;
		}
		
		function imageObj()
		{
			this.URL;
			this.caption;
			this.imgDate;
			this.Xcoord;
			this.Ycoord;
			this.orientation
			this.stackOrder = 1;
		}
		function articlesObj()
		{
			this.articleTitle;
			this.author;
			this.articleText;
			this.Xcoord;
			this.Ycoord;
			this.orientation
			this.stackOrder = 1;
		}
		var newImgObj;
		var newArticleObj;
		var imageArr = new Array();
		var articleArr = new Array();
<?	$BookID = $_GET['bookID'];
/******************************
Whenever you get a PHP error with DBs, use this code
	if (!$intro_mysql) {
	   throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
	}
*******************************/

	if ($BookID == "" || !is_numeric($BookID))
	{
		echo "</script></head><body>" .
				"Please specify a bookID in the URL<br />Example:book2.php?bookID=1";
	}
	else
	{
		// **********************************************
		// Write title and, if no page declared, default to first one
		// **********************************************
		$PageNum = $_GET['pageNum'];
		if ($PageNum == "")
			$PageNum = "1";
		$query_str = "SELECT BookPageNum, BookTitle, BookLoginUsername
					FROM Books
					INNER JOIN BookPages
						ON Books.BookID = BookPages.BookID
						AND Books.BookID = $BookID
						AND BookPages.BookPageNum = $PageNum
					ORDER BY BookPageID ASC LIMIT 1;";
		$intro_mysql = $mysqli->query($query_str);
		$row = $intro_mysql->fetch_assoc();
		// If the book ID passed in the URL isn't in the database, stop
		if (!$row)
		{
			echo "document.title = \"Invalid Parameter\";";
			echo "Invalid parameter";
			exit(0);
		}
		$PageNum = $row['BookPageNum'];
		$BookTitle = $row['BookTitle'];
		$BookImageLocationPrefix = "/images/book/" . $row['BookLoginUsername'];
		//$BookImageLocationPrefix = $row['BookImgPrefix'];
		mysqli_free_result($intro_mysql);
		echo "document.title = \"$BookTitle Page $PageNum\";";
		// **********************************************
		// Grabbing all photos (if any)
		// **********************************************
		$query_str = "SELECT pages.BookPageID, pages.BookPageNum, pagePhotos.BookPagePhotoInstanceNum,
					pagePhotos.BookPagePhotoIpadOrientation,
					pagePhotos.BookPagePhotoXCoord, pagePhotos.BookPagePhotoYCoord, photos.BookPhotoURL,
					photos.BookPhotoCaption, photos.BookPhotoDate
			FROM BookPages pages
			INNER JOIN BookPagePhotos pagePhotos
				ON pages.BookPageID = pagePhotos.BookPageID
			INNER JOIN BookPhotos photos
				ON pagePhotos.BookPhotoID = photos.BookPhotoID
			WHERE pages.BookPageNum = $PageNum
				AND pages.BookID = $BookID
			ORDER BY pages.BookPageID, pagePhotos.BookPhotoID, pagePhotos.BookPagePhotoInstanceNum, BookPagePhotoIpadOrientation;";
		$photo_mysql = $mysqli->query($query_str);
		//echo "\n/*$query_str*/\n";
		$lastPage = 0;
		$lastPhoto = 0;
		$lastInstance = 0;
		while ($row = $photo_mysql->fetch_assoc())
		{
?>				newImgObj = new imageObj();
				newImgObj.URL = "<?=bookImageName($row['BookPageID'], $row['BookPagePhotoInstanceNum'], $row['BookPhotoURL'])?>";
				newImgObj.caption = "<?=$row['BookPhotoCaption']?>";
				newImgObj.imgDate = "<?=$row['BookPhotoDate']?>";
				newImgObj.Xcoord = "<?=$row['BookPagePhotoXCoord']?>";
				newImgObj.Ycoord = "<?=$row['BookPagePhotoYCoord']?>";
				newImgObj.orientation = "<?=$row['BookPagePhotoIpadOrientation']?>";
				imageArr.push(newImgObj);
<?
		}
		$photo_mysql->free();

		// **********************************************
		// Grabbing all articles (if any)
		// **********************************************
		$query_str = "SELECT pages.BookPageNum, pageArticles.BookPageArticleIpadOrientation,
				pageArticles.BookPageArticleWidth, pageArticles.BookPageArticleHeight,
				pageArticles.BookPageArticleXCoord, pageArticles.BookPageArticleYCoord,
				articlesTranslated.BookArticleLangTitle, articlesTranslated.BookArticleLangAuthor,
				articlesTranslated.BookArticleLangText
			FROM BookPages pages
			INNER JOIN BookPageArticles pageArticles
				ON  pages.BookPageID = pageArticles.BookPageID
			INNER JOIN BookArticles articles
				ON pageArticles.BookArticleID = articles.BookArticleID
			INNER JOIN BookArticleLangs articlesTranslated
				ON articles.BookArticleID = articlesTranslated.BookArticleID
			WHERE pages.BookPageNum = $PageNum
				AND pages.BookID = $BookID;";
		$articles_mysql = $mysqli->query($query_str);
		while ($row = $articles_mysql->fetch_assoc())
		{
?>			newArticleObj = new articlesObj();
			newArticleObj.articleTitle = <?=json_encode($row['BookArticleLangTitle'])?>;
			newArticleObj.author = "<?=$row['BookArticleLangAuthor']?>";
			newArticleObj.articleText = <?=json_encode($row['BookArticleLangText'])?>;
			newArticleObj.Xcoord = "<?=$row['BookPageArticleXCoord']?>";
			newArticleObj.Ycoord = "<?=$row['BookPageArticleYCoord']?>";
			newArticleObj.width = <?=$row['BookPageArticleWidth']?>;
			newArticleObj.height = <?=$row['BookPageArticleHeight']?>;
			newArticleObj.orientation = "<?=$row['BookPageArticleIpadOrientation']?>";
			articleArr.push(newArticleObj);
<?		}
		$articles_mysql->free();
?>
/***********************
JavaScript functions to show/hide stuff
and to switch pages and deal with rotating iPad
************************/
		function GotoNewPage(maxPage)
		{
			// If page number is good, go to new page. Otherwise, tell user he's Gerbilled
			var newPage = document.getElementById('GotoPage').value;
			if ((parseFloat(newPage) == parseInt(newPage)) && !isNaN(newPage))
			{
				if (parseInt(newPage) > parseInt(maxPage))
					alert("Last page of book is " + maxPage);
				else
					document.location.href='book2.php?bookID=<?=$BookID?>&pageNum=' + newPage;
			}
			else
				alert("Page number isn't an integer!");
			document.getElementById('GotoPage').focus();
		}
		
		function initPage()
		{
			var root=document.getElementsByTagName('body')[0];
			var oImg;
			for (var x = 0; x < imageArr.length; x++)
			{
				oImg = document.createElement('img');
				oImg.id ='img' + x;
				oImg.setAttribute('src', "<?=$BookImageLocationPrefix?>/" + imageArr[x].URL);
				oImg.style.position='absolute';
				oImg.style.left = imageArr[x].Xcoord;
				oImg.style.top = imageArr[x].Ycoord;
				oImg.style.zIndex = imageArr[x].stackOrder;
				oImg.style.display = (imageArr[x].orientation == "horizontal") ? "" : "none";
				root.appendChild(oImg);
			}
			if (articleArr.length > 0)
				document.getElementById("articlesButton").style.display = "";
			for (var x = 0; x < articleArr.length; x++)
			{
				oArticle = document.createElement('div');
				oArticle.id ='art' + x;
				oArticle.style.position='absolute';
				oArticle.innerHTML = "<h4>" + articleArr[x].articleTitle + "</h4>" + articleArr[x].articleText;
				oArticle.style.left = articleArr[x].Xcoord;
				oArticle.style.top = articleArr[x].Ycoord;
				oArticle.style.width = articleArr[x].width;
				oArticle.style.height = articleArr[x].height;
				oArticle.style.zIndex = articleArr[x].stackOrder;
				oArticle.style.overflowY = "auto";
				root.appendChild(oArticle);
			}
		}
		
		function toggleArticles()
		{
			for (var x = 0; x < articleArr.length; x++)
				document.getElementById('art' + x).style.display = g_showArticles ? "none" : "";
			document.getElementById('articlesButton').innerHTML = g_showArticles ? "Show articles" : "Hide articles";
			g_showArticles = !g_showArticles;
		}
		
		function toggleImageData()
		{
			if (!g_showImageData)
				;
			else
				;
			g_showImageData = !g_showImageData;
		}
		
		function rotatePage()
		{
			var isLandscape = (g_orientation == "landscape");
			document.getElementById("rotateButton").innerHTML = isLandscape ? "Make iPad horizontal" : "Make iPad verticle";
			for (var x = 0; x < imageArr.length; x++)
			{
				document.getElementById("img" + x).style.left = imageArr[x].Xcoord;
				document.getElementById("img" + x).style.top = imageArr[x].Ycoord;
			}
			for (x = 0; x < articleArr.length; x++)
			{
				document.getElementById("art" + x).style.left = articleArr[x].Xcoord;
				document.getElementById("art" + x).style.top = articleArr[x].Ycoord;
			}
			g_orientation = isLandscape ? "portrait" : "landscape";
		}
	</script>
</head>
<body onload="initPage()">
<?
		// **********************************************
		// Links for previous page and next page
		// **********************************************
		// Note: this would be a stored procedure, were PHP not giving me error 2014
		echo "<div style=\"position: absolute; opacity:0.5; z-index:2;\"
		onmouseOver=\"this.style.opacity=1\" onmouseOut=\"this.style.opacity=.5\">";
		$query_str = "SELECT BookPageNum
					FROM BookPages
					WHERE BookPageNum < $PageNum
					AND BookID = $BookID
					ORDER BY BookPageNum DESC LIMIT 1;";
		$prevbutton_mysql = $mysqli->query($query_str);
		$row = $prevbutton_mysql->fetch_assoc();
		$prevbutton_mysql->free();
		if (!is_null($row['BookPageNum']))
			echo "<button type=\"button\"
				onclick=\"document.location.href='book2.php?bookID=$BookID&pageNum=" . $row['BookPageNum'] . "'\">Prev</button>&nbsp;&nbsp;";
		$query_str = "SELECT BookPageNum
					FROM BookPages
					WHERE BookPageNum > $PageNum
					AND BookID = $BookID
					ORDER BY BookPageNum ASC LIMIT 1;";
		$nextbutton_mysql = $mysqli->query($query_str);
		$row = $nextbutton_mysql->fetch_assoc();
		$nextbutton_mysql->free();
		if (!is_null($row['BookPageNum']))
			echo "<button type=\"button\"
				onclick=\"document.location.href='book2.php?bookID=$BookID&pageNum=" . $row['BookPageNum'] . "'\">Next</button>&nbsp;&nbsp;";

		// **********************************************
		// Here's the HTML for the buttons that are 1/2 opaque
		// **********************************************
		$query_str = "SELECT MAX(BookPageNum) as maxPageNum
					FROM BookPages
					WHERE BookID = $BookID;";
		$lastpage_mysql = $mysqli->query($query_str);
		$row = $lastpage_mysql->fetch_assoc();
		echo "Goto page: <input type=\"text\" id=\"GotoPage\" value=\"$PageNum\" maxlength=\"3\" size=\"5\"
			onkeydown=\"if (event.keyCode == 13) GotoNewPage('" . $row['maxPageNum'] . "')\" />
					<button type=\"button\" onclick=\"GotoNewPage('" . $row['maxPageNum'] . "')\">Go!</button>";
		$lastpage_mysql->free();
		// NOTE: the show image data button below is currently hidden thanks to display: none
		echo "<button type=\"button\" id=\"imageDataButton\" style=\"display: none\"
				onclick=\"toggleImageData()\">Show Image Data</button>
			<button type=\"button\" id=\"articlesButton\" style=\"display: none\"
				onclick=\"toggleArticles()\">Show Articles</button>
			<button type=\"button\" id=\"rotateButton\"
				onclick=\"rotatePage()\">Make iPad verticle</button>
			</div>";
	}
?>
</body>
</html>
<? include("dbclose.php") ?>