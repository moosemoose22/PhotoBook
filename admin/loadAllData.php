<?php
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	$client_form_data = json_decode(file_get_contents('php://input'), true);
	$showAllData = ($client_form_data['loadAllData'] == "true");
	
	function loadPages($BookID)
	{
		$mysqli = $GLOBALS["mysqli"];
		$pages_array = array();
		// Pages
		$pages_str = "SELECT BookPageID, BookPageNum
						FROM BookPages
						WHERE BookID = " . $BookID . ";";
		$pages_sql = $mysqli->query($pages_str);
		while ($row = $pages_sql->fetch_assoc())
		{
			array_push($pages_array,
				array("bookID" => $BookID,
						"pageID" => $row['BookPageID'],
						"pageNum" => $row['BookPageNum']
				)
			);
		}
		$pages_sql->free();
		return $pages_array;
	}
	
	function loadPhotos($UserLogin)
	{
		$mysqli = $GLOBALS["mysqli"];
		$photos_array = array();
		$pix_str = "SELECT BookPhotoID, BookPhotoURL, BookPhotoWidth, BookPhotoHeight, BookPhotoWidthSmall, BookPhotoHeightSmall
					FROM BookPhotos
					WHERE BookLoginUsername = '" . $UserLogin . "'
					ORDER BY BookPhotoID;";
	
		$images_sql = $mysqli->query($pix_str);
		while ($row = $images_sql->fetch_assoc())
		{
			array_push($photos_array,
				array("ID" => $row['BookPhotoID'],
						"photoURL" => $row['BookPhotoURL'],
						"width" => $row['BookPhotoWidth'],
						"height" => $row['BookPhotoHeight'],
						"widthSmall" => $row['BookPhotoWidthSmall'],
						"heightSmall" => $row['BookPhotoHeightSmall']
				)
			);
		}
		$images_sql->free();
		return $photos_array;
	}

	function loadPhotoInstances($BookID, $PageID)
	{
		$photo_instance_array = array();
		if (empty($BookID))
			return array_push($photo_instance_array, array("error" => "PhotoInstance has no book ID"));
		$mysqli = $GLOBALS["mysqli"];
		// Page photo population **********************************
		$pix_str = "SELECT BookPhotoID, BookPagePhotoInstanceNum, BookPageID, BookPagePhotoIpadOrientation, 
					BookPagePhotoXCoord, BookPagePhotoYCoord, BookPagePhotoWidth, BookPagePhotoHeight,
					IF(BookPagePhotoStretchToFill,'true','false') as BookPagePhotoStretchToFill
					FROM BookPagePhotos
					WHERE ";
		if (!is_null($PageID))
			$pix_str .= "BookPageID = {$PageID} AND ";
		$pix_str .= "BookPhotoID IN
						(SELECT BookPhotoID
						FROM BookPhotos
						WHERE BookID = " . $BookID . ")
					ORDER BY BookPhotoID, BookPagePhotoInstanceNum;";
	
		$images_sql = $mysqli->query($pix_str);
		while ($row = $images_sql->fetch_assoc())
		{
			array_push($photo_instance_array,
				array("ID" => $row['BookPhotoID'],
						"instanceID" => $row['BookPagePhotoInstanceNum'],
						"pageID" => $row['BookPageID'],
						"orientation" => $row['BookPagePhotoIpadOrientation'],
						"Xcoord" => $row['BookPagePhotoXCoord'],
						"Ycoord" => $row['BookPagePhotoYCoord'],
						"width" => $row['BookPagePhotoWidth'],
						"height" => $row['BookPagePhotoHeight'],
						"stretchToFill" => $row['BookPagePhotoStretchToFill']
				)
			);
		}
		$images_sql->free();
		return $photo_instance_array;
	}
	
	function loadArticles($BookID, $BookLang)
	{
		$mysqli = $GLOBALS["mysqli"];
		$articles_array = array();
		$articles_str = "SELECT articleLangs.BookArticleID, articleLangs.BookArticleLangTitle, articleLangs.BookArticleLangAuthor,
						articleLangs.BookArticleLangText, articles.BookArticleIsShared
						FROM BookArticleLangs articleLangs
						INNER JOIN BookArticles articles
							ON articleLangs.BookArticleID = articles.BookArticleID
						WHERE articleLangs.BookLangID = '" . $BookLang . "'
						AND articles.BookID = " . $BookID . "
						ORDER BY articleLangs.BookArticleID ASC;";
		$articles_sql = $mysqli->query($articles_str);
		while ($row = $articles_sql->fetch_assoc())
		{
			array_push($articles_array,
				array("ID" => $row['BookArticleID'],
						"title" => $row['BookArticleLangTitle'],
						"author" => $row['BookArticleLangAuthor'],
						"text" => str_replace("&", $GLOBALS["text_delimiter_replace_amperstand"], $row['BookArticleLangText']),
						"isShared" => $row['BookArticleIsShared']
				)
			);
		}
		$articles_sql->free();
		return $articles_array;
	}

	function loadArticleInstances($BookID, $PageID)
	{
		$article_instance_array = array();
		if (empty($BookID))
			return array_push($article_instance_array, array("error" => "ArticleInstance has no book ID"));
		$mysqli = $GLOBALS["mysqli"];
		// Article data population ********************************
		$articles_instance_str = "SELECT pageArticles.BookArticleID, pageArticles.BookPageArticleInstanceNum, pageArticles.BookPageID, pageArticles.BookPageArticleIpadOrientation,
						pageArticles.BookPageArticleXCoord, pageArticles.BookPageArticleYCoord,
						pageArticles.BookPageArticleWidth, pageArticles.BookPageArticleHeight
						FROM BookPageArticles pageArticles
						WHERE ";
		if (!is_null($PageID))
			$articles_instance_str .= "pageArticles.BookPageID = {$PageID} AND ";
		$articles_instance_str .= "pageArticles.BookArticleID IN
							(SELECT BookArticleID
							FROM BookArticles
							WHERE BookID = " . $BookID . ")
						ORDER BY pageArticles.BookArticleID ASC, pageArticles.BookPageArticleInstanceNum ASC;";

		$articles_instance_sql = $mysqli->query($articles_instance_str);
		while ($row = $articles_instance_sql->fetch_assoc())
		{
			array_push($article_instance_array,
				array("ID" => $row['BookArticleID'],
						"instanceID" => $row['BookPageArticleInstanceNum'],
						"pageID" => $row['BookPageID'],
						"orientation" => $row['BookPageArticleIpadOrientation'],
						"Xcoord" => $row['BookPageArticleXCoord'],
						"Ycoord" => $row['BookPageArticleYCoord'],
						"width" => $row['BookPageArticleWidth'],
						"height" => $row['BookPageArticleHeight']
				)
			);
		}
		$articles_instance_sql->free();
		return $article_instance_array;
	}
	
	function loadStackOrder($BookID, $PageID)
	{
		$stack_order_array = array();
		if (empty($BookID))
			return array_push($stack_order_array, array("error" => "Stack Order has no book ID"));
		$mysqli = $GLOBALS["mysqli"];
		// Article data population ********************************
		$stack_order_str = "SELECT BookPageStackOrderTableName, BookPageID, BookArticleID as ID, BookPageArticleInstanceNum as instanceID,
								BookPageArticleIpadOrientation as orientation, BookPageStackOrderVal
							FROM BookPageStackOrder stackOrder
							INNER JOIN BookPageArticles pageArticles
								ON stackOrder.BookPageStackOrderTableID = pageArticles.BookPageArticleID
							WHERE BookPageStackOrderTableName = 'Article' ";
		if (!is_null($PageID))
			$stack_order_str .= "AND BookPageID = {$PageID} ";
		$stack_order_str .= "UNION
							SELECT BookPageStackOrderTableName, BookPageID, BookPhotoID as ID, BookPagePhotoInstanceNum as instanceID,
								BookPagePhotoIpadOrientation as orientation, BookPageStackOrderVal
							FROM BookPageStackOrder stackOrder
							INNER JOIN BookPagePhotos pagePhotos
								ON stackOrder.BookPageStackOrderTableID = pagePhotos.BookPagePhotoID
							WHERE BookPageStackOrderTableName = 'Photo'";
		if (isset($PageID))
			$stack_order_str .= "AND BookPageID = {$PageID} ";
		$stack_order_str .= "ORDER BY BookPageID, BookPageStackOrderVal;";
		$stack_order_sql = $mysqli->query($stack_order_str);
		while ($row = $stack_order_sql->fetch_assoc())
		{
			array_push($stack_order_array,
				array("ID" => $row['ID'],
						"instanceID" => $row['instanceID'],
						"pageID" => $row['BookPageID'],
						"objectType" => $row['BookPageStackOrderTableName'],
						"orientation" => $row['orientation'],
						"stackOrder" => $row['BookPageStackOrderVal']
				)
			);
		}
		$stack_order_sql->free();
		return $stack_order_array;
	}
	
	function loadPageData($BookID, $PageID, $BookLang)
	{
		$pageDataArray = array();
		$pageDataArray["photoinstances"] = loadPhotoInstances($BookID, $PageID);
		$pageDataArray["articleinstances"] = loadArticleInstances($BookID, $PageID);
		$pageDataArray["stackorder"] = loadStackOrder($BookID, $PageID);
		return $pageDataArray;
	}

	$allDataArray = array();
	if ($showAllData)
		$allDataArray["globals"] = array("loggingIn" => "true", "mode" => "add");
	if ($client_form_data['loadPages'] == "true" || $showAllData)
		$allDataArray["pages"] = loadPages($client_form_data['BookID']);
	if ($client_form_data['loadPhotos'] == "true" || $showAllData)
		$allDataArray["photos"] = loadPhotos($client_form_data['UserLogin']);
	if ($client_form_data['loadPhotoInstances'] == "true" || $showAllData)
		$allDataArray["photoinstances"] = loadPhotoInstances($client_form_data['BookID'], null);
	if ($client_form_data['loadArticles'] == "true" || $showAllData)
		$allDataArray["articles"] = loadArticles($client_form_data['BookID'], $client_form_data['BookLang']);
	if ($client_form_data['loadArticleInstances'] == "true" || $showAllData)
		$allDataArray["articleinstances"] = loadArticleInstances($client_form_data['BookID'], null);
	if ($client_form_data['loadStackOrder'] == "true" || $showAllData)
		$allDataArray["stackorder"] = loadStackOrder($client_form_data['BookID'], null);

	if ($client_form_data['loadPageData'] == "true")
		$allDataArray = loadPageData($client_form_data['bookID'], $client_form_data['pageID'], $client_form_data['bookLang']);
	echo json_encode(array("allData" => $allDataArray));
?>