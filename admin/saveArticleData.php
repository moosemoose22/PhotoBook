<?php
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	
	$articleID = $_REQUEST['ID'];
	$articleInstanceID = $_REQUEST['instanceID'];
	if (is_int($articleID))
	{
		$db_str = "SELECT BookArticleIsShared
					FROM BookArticles
					WHERE BookArticleID = " . $articleID . ";";
		$is_book_shared_sql = $mysqli->query($db_str);
		$row = $is_book_shared_sql->fetch_assoc();
		$is_shared_instance = (intval($row['BookArticleIsShared']) == 1);
		$is_book_shared_sql->free();
	}
	if ($_REQUEST['mode'] == "delete")
	{
		if ($is_shared_instance)
		{
			$db_str = "DELETE FROM BookPageArticles
						WHERE BookArticleID = " . $articleID . "
						AND BookPageArticleInstanceNum = " . $articleInstanceID . ";";
			$mysqli->query($db_str);
		}
		else
		{
			$db_str = "DELETE FROM BookPageArticles
						WHERE BookArticleID = " . $articleID . ";";
			$mysqli->query($db_str);
			
			$db_str = "DELETE FROM BookArticleLangs
						WHERE BookArticleID = " . $articleID . ";";
			$mysqli->query($db_str);
			
			$db_str = "DELETE FROM BookArticles
						WHERE BookArticleID = " . $articleID . ";";
			$mysqli->query($db_str);
		}
		echo "Deleted:Article," . $articleID . "," . $articleInstanceID . "," . $_REQUEST['pageID'];
		exit(0);
	}
	else if ($_REQUEST['mode'] == "update")
	{
		// Update article in DB ********************************
		if (!$is_shared_instance)
		{
			$db_str = "UPDATE BookArticleLangs
		 				SET BookArticleLangText = '" . $_REQUEST['articleText'] . "'
		 				WHERE BookLangID = '" . $_REQUEST['LangID'] . "'
		 				AND BookArticleID = " . $articleID . ";";
			$mysqli->query($db_str);
		}
		$db_str = "UPDATE BookPageArticles
					SET BookPageArticleInstanceNum = " . $articleInstanceID;
					if (isset($_REQUEST['orientation']))
						$db_str .= ", BookPageArticleXCoord = '" . $_REQUEST['orientation'] . "'";
					if (isset($_REQUEST['Xcoord']))
						$db_str .= ", BookPageArticleXCoord = " . $_REQUEST['Xcoord'];
					if (isset($_REQUEST['Ycoord']))
						$db_str .= ", BookPageArticleYCoord = " . $_REQUEST['Ycoord'];
					if (isset($_REQUEST['width']))
						$db_str .= ", BookPageArticleWidth = " . $_REQUEST['width'];
					if (isset($_REQUEST['height']))
						$db_str .= ", BookPageArticleHeight = " . $_REQUEST['height'];
		$db_str .= " WHERE BookArticleID = " . $articleID . " 
					AND BookPageArticleInstanceNum = " . $articleInstanceID . ";";
		$mysqli->query($db_str);
	}
	else if ($_REQUEST['mode'] == "add")
	{
		// Add article to DB if user typed new text in ********************************
		$db_str = "INSERT INTO BookArticles (BookID) VALUES (" . $_REQUEST['BookID'] . ");";
		$mysqli->query($db_str);

		$articleID = $mysqli->insert_id;
		$db_str = "INSERT INTO BookArticleLangs (BookArticleID, BookArticleLangText, BookLangID)
						VALUES (" . $articleID . ", '" . $_REQUEST['articleText'] . "', '" . $_REQUEST['LangID'] . "');";
		$mysqli->query($db_str);

		// Article instance ID is 1 because a new text box can only have 1 instance
		$db_str = "INSERT INTO BookPageArticles (BookPageID, BookArticleID, BookPageArticleInstanceNum,
					BookPageArticleIpadOrientation, BookPageArticleXCoord, BookPageArticleYCoord, BookPageArticleWidth,
					BookPageArticleHeight, BookPageArticleStackOrder)
					VALUES (" . $_REQUEST['pageID'] . ", " . $articleID . ", 1,
					'" . $_REQUEST['orientation'] . "', " . $_REQUEST['Xcoord'] . ", " . $_REQUEST['Ycoord'] . ",
					" . $_REQUEST['width'] . ", " . $_REQUEST['height'] . ", 1);";
		$mysqli->query($db_str);
		$articleInstanceID = $mysqli->insert_id;
	}	
	else if ($_REQUEST['mode'] == "add_instance")
	{
		$db_str = "INSERT INTO BookPageArticles (BookPageID, BookArticleID, BookPageArticleInstanceNum,
					BookPageArticleIpadOrientation, BookPageArticleXCoord, BookPageArticleYCoord, BookPageArticleWidth,
					BookPageArticleHeight, BookPageArticleStackOrder)
					VALUES (" . $_REQUEST['pageID'] . ", " . $articleID . ", " . $articleInstanceID . ",
					'" . $_REQUEST['orientation'] . "', " . $_REQUEST['Xcoord'] . ", " . $_REQUEST['Ycoord'] . ",
					" . $_REQUEST['width'] . ", " . $_REQUEST['height'] . ", 1);";
		$mysqli->query($db_str);
	}


	$ArticleData = "";
	if ($is_shared_instance)
	{
		$ArticleData = "Article:mode=" . $_REQUEST['mode'] . "&loggingIn=false" . $item_delimiter
					. "ID=" . $articleID . "&text=" . $_REQUEST['articleText']
					. "&isShared=false";
	}

	$ArticleInstanceData = "ArticleInstance:mode=" . $_REQUEST['mode'] . "&loggingIn=false" . $item_delimiter
						. "ID=" . $articleID . "&instanceID=" . $articleInstanceID
						. "&pageID=" . $_REQUEST['pageID'] . "&orientation=" . $_REQUEST['orientation']
						. "&Xcoord=" . $_REQUEST['Xcoord'] . "&Ycoord=" . $_REQUEST['Ycoord']
						. "&width=" . $_REQUEST['width'] . "&height=" . $_REQUEST['height'];
	if (!empty($ArticleData))
		echo $ArticleData . $data_delimiter;
	echo $ArticleInstanceData;
/*	
	echo "Article:mode=" . $_REQUEST['mode'] . "&loggingIn=false" . $item_delimiter . "&pageID=" . $_REQUEST['pageID'] ."&ID=" . $articleID
		. "&instanceID=" . $articleInstanceID
		. "&orientation=" . $_REQUEST['orientation'] . "&Xcoord=" . $_REQUEST['Xcoord'] . "&Ycoord=" . $_REQUEST['Ycoord']
		. "&width=" . $_REQUEST['width'] . "&height=" . $_REQUEST['height'] . "&text=". $_REQUEST['articleText'];
*/
	//echo $db_str;
?>