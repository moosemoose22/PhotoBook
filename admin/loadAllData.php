<?php
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	$showAllData = ($_REQUEST['loadAllData'] == "true");
	$showAllDataInitString = "mode=add&loggingIn=true";
	
	function loadPages($BookID)
	{
		$mysqli = $GLOBALS["mysqli"];
		// Pages
		$pages_str = "SELECT BookPageID, BookPageNum
						FROM BookPages
						WHERE BookID = " . $BookID . ";";
		$pages_sql = $mysqli->query($pages_str);
		$PageData = "Page:" . $GLOBALS["showAllDataInitString"];
		while ($row = $pages_sql->fetch_assoc())
		{
			$PageData .= $GLOBALS["item_delimiter"];
			$PageData .= "bookID=" . $BookID . "&pageID=" . $row['BookPageID'] . "&pageNum=" . $row['BookPageNum'];
		}
		$pages_sql->free();
		return $PageData;
	}
	
	function loadPhotos($UserLogin)
	{
		$mysqli = $GLOBALS["mysqli"];
		$pix_str = "SELECT BookPhotoID, BookPhotoURL, BookPhotoWidth, BookPhotoHeight, BookPhotoWidthSmall, BookPhotoHeightSmall
					FROM BookPhotos
					WHERE BookLoginUsername = '" . $UserLogin . "'
					ORDER BY BookPhotoID;";
	
		$images_sql = $mysqli->query($pix_str);
		$PhotoData = "Photo:" . $GLOBALS["showAllDataInitString"];
		while ($row = $images_sql->fetch_assoc())
		{
			$PhotoData .= $GLOBALS["item_delimiter"];
			$PhotoData .= "ID=" . $row['BookPhotoID'] . "&photoURL=" . $row['BookPhotoURL'] . "&width=" . $row['BookPhotoWidth']
						. "&height=" . $row['BookPhotoHeight'] . "&widthSmall=" . $row['BookPhotoWidthSmall']
						. "&heightSmall=" . $row['BookPhotoHeightSmall'];
		}
		$images_sql->free();
		return $PhotoData;
	}

	function loadPhotoInstances($UserLogin, $BookID)
	{
		if (empty($UserLogin) || empty($BookID))
			return "Error:PhotoInstance has no book ID or user login";
		$mysqli = $GLOBALS["mysqli"];
		// Page photo population **********************************
		$pix_str = "SELECT BookPhotoID, BookPagePhotoInstanceNum, BookPageID, BookPagePhotoIpadOrientation, 
					BookPagePhotoXCoord, BookPagePhotoYCoord, BookPagePhotoWidth, BookPagePhotoHeight,
					IF(BookPagePhotoStretchToFill,'true','false') as BookPagePhotoStretchToFill
					FROM BookPagePhotos
					WHERE BookPhotoID IN
						(SELECT BookPhotoID
						FROM BookPhotos
						WHERE BookLoginUsername = '" . $UserLogin . "'
							AND BookID = " . $BookID . ")
					ORDER BY BookPhotoID, BookPagePhotoInstanceNum;";
		//return "Error:$pix_str";
	
		$images_sql = $mysqli->query($pix_str);
		$PhotoInstanceData = "PhotoInstance:" . $GLOBALS["showAllDataInitString"];
		while ($row = $images_sql->fetch_assoc())
		{
			$PhotoInstanceData .= $GLOBALS["item_delimiter"];
			$PhotoInstanceData .= ("ID=" . $row['BookPhotoID'] . "&instanceID=" . $row['BookPagePhotoInstanceNum']
				. "&pageID=" . $row['BookPageID'] . "&Xcoord=" . $row['BookPagePhotoXCoord'] . "&Ycoord=" . $row['BookPagePhotoYCoord']
				. "&width=" . $row['BookPagePhotoWidth'] . "&height=" . $row['BookPagePhotoHeight']
				. "&stretchToFill=" . $row['BookPagePhotoStretchToFill'] . "&orientation=" . $row['BookPagePhotoIpadOrientation']);
		}
		$images_sql->free();
		return $PhotoInstanceData;
	}
	
	function loadArticles($BookID, $BookLang)
	{
		$mysqli = $GLOBALS["mysqli"];
		$articles_str = "SELECT articleLangs.BookArticleID, articleLangs.BookArticleLangTitle, articleLangs.BookArticleLangAuthor,
						articleLangs.BookArticleLangText, articles.BookArticleIsShared
						FROM BookArticleLangs articleLangs
						INNER JOIN BookArticles articles
							ON articleLangs.BookArticleID = articles.BookArticleID
						WHERE articleLangs.BookLangID = '" . $BookLang . "'
						AND articles.BookID = " . $BookID . "
						ORDER BY articleLangs.BookArticleID ASC;";
		$articles_sql = $mysqli->query($articles_str);
		$ArticleData = "Article:" . $GLOBALS["showAllDataInitString"];
		while ($row = $articles_sql->fetch_assoc())
		{
			$ArticleData .= $GLOBALS["item_delimiter"];
			$ArticleData .= "ID=" . $row['BookArticleID'] . "&title=" . $row['BookArticleLangTitle']
				. "&author=" . $row['BookArticleLangAuthor']
				. "&text=" . str_replace("&", $GLOBALS["text_delimiter_replace_amperstand"], $row['BookArticleLangText'])
				. "&isShared=" . $row['BookArticleIsShared'];
		}
		$articles_sql->free();
		return $ArticleData;
	}

	function loadArticleInstances($BookID)
	{
		if (empty($BookID))
			return "Error:ArticleInstance has no book ID";
		$mysqli = $GLOBALS["mysqli"];
		// Article data population ********************************
		$articles_instance_str = "SELECT pageArticles.BookArticleID, pageArticles.BookPageArticleInstanceNum, pageArticles.BookPageID, pageArticles.BookPageArticleIpadOrientation,
						pageArticles.BookPageArticleXCoord, pageArticles.BookPageArticleYCoord,
						pageArticles.BookPageArticleWidth, pageArticles.BookPageArticleHeight
						FROM BookPageArticles pageArticles
						WHERE pageArticles.BookArticleID IN
							(SELECT BookArticleID
							FROM BookArticles
							WHERE BookID = " . $BookID . ")
						ORDER BY pageArticles.BookArticleID ASC, pageArticles.BookPageArticleInstanceNum ASC;";

		$articles_instance_sql = $mysqli->query($articles_instance_str);
		$ArticleInstanceData = "ArticleInstance:" . $GLOBALS["showAllDataInitString"];
		while ($row = $articles_instance_sql->fetch_assoc())
		{
			$ArticleInstanceData .= $GLOBALS["item_delimiter"];
			$ArticleInstanceData .= "ID=" . $row['BookArticleID'] . "&instanceID=" . $row['BookPageArticleInstanceNum']
				. "&pageID=" . $row['BookPageID'] . "&orientation=" . $row['BookPageArticleIpadOrientation']
				. "&Xcoord=" . $row['BookPageArticleXCoord'] . "&Ycoord=" . $row['BookPageArticleYCoord']
				. "&width=" . $row['BookPageArticleWidth'] . "&height=" . $row['BookPageArticleHeight'];
		}
		$articles_instance_sql->free();
		return $ArticleInstanceData;
	}
	
	function loadStackOrder($BookID)
	{
		if (empty($BookID))
			return "Error:Stack Order has no book ID";
		$mysqli = $GLOBALS["mysqli"];
		// Article data population ********************************
		$stack_order_str = "SELECT BookPageStackOrderTableName, BookPageID, BookArticleID as ID, BookPageArticleInstanceNum as instanceID,
								BookPageArticleIpadOrientation as orientation, BookPageStackOrderVal
							FROM BookPageStackOrder stackOrder
							INNER JOIN BookPageArticles pageArticles
								ON stackOrder.BookPageStackOrderTableID = pageArticles.BookPageArticleID
							WHERE BookPageStackOrderTableName = 'Article'
							UNION
							SELECT BookPageStackOrderTableName, BookPageID, BookPhotoID as ID, BookPagePhotoInstanceNum as instanceID,
								BookPagePhotoIpadOrientation as orientation, BookPageStackOrderVal
							FROM BookPageStackOrder stackOrder
							INNER JOIN BookPagePhotos pagePhotos
								ON stackOrder.BookPageStackOrderTableID = pagePhotos.BookPagePhotoID
							WHERE BookPageStackOrderTableName = 'Photo'
							ORDER BY BookPageID, BookPageStackOrderVal;";
		$stack_order_sql = $mysqli->query($stack_order_str);
		$StackOrderData = "StackOrder:" . $GLOBALS["showAllDataInitString"];
		while ($row = $stack_order_sql->fetch_assoc())
		{
			$StackOrderData .= $GLOBALS["item_delimiter"];
			$StackOrderData .= "objectType=" . $row["BookPageStackOrderTableName"] . "&pageID=" . $row["BookPageID"]
							. "&ID=" . $row["ID"] . "&instanceID=" . $row["instanceID"] . "&orientation=" . $row["orientation"]
							. "&stackOrder=" . $row["BookPageStackOrderVal"];
		}
		$stack_order_sql->free();
		return $StackOrderData;
	}
	$printString = "";
	if ($_REQUEST['loadPages'] == "true" || $showAllData)
		$printString .= loadPages($_REQUEST['BookID']);
	if ($showAllData)
		$printString .= $data_delimiter;
	if ($_REQUEST['loadPhotos'] == "true" || $showAllData)
		$printString .= loadPhotos($_REQUEST['UserLogin']);
	if ($showAllData)
		$printString .= $data_delimiter;
	if ($_REQUEST['loadPhotoInstances'] == "true" || $showAllData)
		$printString .= loadPhotoInstances($_REQUEST['UserLogin'], $_REQUEST['BookID']);
	if ($showAllData)
		$printString .= $data_delimiter;
	if ($_REQUEST['loadArticles'] == "true" || $showAllData)
		$printString .= loadArticles($_REQUEST['BookID'], $_REQUEST['BookLang']);
	if ($showAllData)
		$printString .= $data_delimiter;
	if ($_REQUEST['loadArticleInstances'] == "true" || $showAllData)
		$printString .= loadArticleInstances($_REQUEST['BookID']);
	if ($showAllData)
		$printString .= $data_delimiter;
	if ($_REQUEST['loadStackOrder'] == "true" || $showAllData)
		$printString .= loadStackOrder($_REQUEST['BookID']);
	echo $printString;
?>