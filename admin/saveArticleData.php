<?php
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	
	$client_form_data = json_decode(file_get_contents('php://input'), true);
	$articleID = $client_form_data['ID'];
	$articleInstanceID = $client_form_data['instanceID'];
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
	if ($client_form_data['mode'] == "delete")
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
		//echo "Deleted:Article," . $articleID . "," . $articleInstanceID . "," . $client_form_data['pageID'];
		$deletedHash = array("type" => "articleinstance",
							"ID" => $articleID,
							"instanceID" => $articleInstanceID,
							"pageID" => $client_form_data['pageID']);
		echo json_encode(array("allData" => array("deleted" => array($deletedHash))));
		exit(0);
	}
	else if ($client_form_data['mode'] == "update")
	{
		// Update article in DB ********************************
		if (!$is_shared_instance)
		{
			$db_str = "UPDATE BookArticleLangs
		 				SET BookArticleLangText = '" . $client_form_data['articleText'] . "'
		 				WHERE BookLangID = '" . $client_form_data['LangID'] . "'
		 				AND BookArticleID = " . $articleID . ";";
			$mysqli->query($db_str);
		}
		$db_str = "UPDATE BookPageArticles
					SET BookPageArticleInstanceNum = " . $articleInstanceID;
					if (isset($client_form_data['orientation']))
						$db_str .= ", BookPageArticleXCoord = '" . $client_form_data['orientation'] . "'";
					if (isset($client_form_data['Xcoord']))
						$db_str .= ", BookPageArticleXCoord = " . $client_form_data['Xcoord'];
					if (isset($client_form_data['Ycoord']))
						$db_str .= ", BookPageArticleYCoord = " . $client_form_data['Ycoord'];
					if (isset($client_form_data['width']))
						$db_str .= ", BookPageArticleWidth = " . $client_form_data['width'];
					if (isset($client_form_data['height']))
						$db_str .= ", BookPageArticleHeight = " . $client_form_data['height'];
		$db_str .= " WHERE BookArticleID = " . $articleID . " 
					AND BookPageArticleInstanceNum = " . $articleInstanceID . ";";
		$mysqli->query($db_str);
	}
	else if ($client_form_data['mode'] == "add")
	{
		// Add article to DB if user typed new text in ********************************
		$db_str = "INSERT INTO BookArticles (BookID) VALUES (" . $client_form_data['BookID'] . ");";
		$mysqli->query($db_str);
		$articleID = $mysqli->insert_id;

		$db_str = "INSERT INTO BookArticleLangs (BookArticleID, BookArticleLangText, BookLangID)
						VALUES (" . $articleID . ", '" . $client_form_data['articleText'] . "', '" . $client_form_data['LangID'] . "');";
		$mysqli->query($db_str);

		// Article instance ID is 1 because a new text box can only have 1 instance
		$db_str = "INSERT INTO BookPageArticles (BookPageID, BookArticleID, BookPageArticleInstanceNum,
					BookPageArticleIpadOrientation, BookPageArticleXCoord, BookPageArticleYCoord, BookPageArticleWidth,
					BookPageArticleHeight, BookPageArticleStackOrder)
					VALUES (" . $client_form_data['pageID'] . ", " . $articleID . ", 1,
					'" . $client_form_data['orientation'] . "', " . $client_form_data['Xcoord'] . ", " . $client_form_data['Ycoord'] . ",
					" . $client_form_data['width'] . ", " . $client_form_data['height'] . ", 1);";
		$mysqli->query($db_str);
		$articleInstanceID = $mysqli->insert_id;
	}	
	else if ($client_form_data['mode'] == "add_instance")
	{
		$db_str = "INSERT INTO BookPageArticles (BookPageID, BookArticleID, BookPageArticleInstanceNum,
					BookPageArticleIpadOrientation, BookPageArticleXCoord, BookPageArticleYCoord, BookPageArticleWidth,
					BookPageArticleHeight, BookPageArticleStackOrder)
					VALUES (" . $client_form_data['pageID'] . ", " . $articleID . ", " . $articleInstanceID . ",
					'" . $client_form_data['orientation'] . "', " . $client_form_data['Xcoord'] . ", " . $client_form_data['Ycoord'] . ",
					" . $client_form_data['width'] . ", " . $client_form_data['height'] . ", 1);";
		$mysqli->query($db_str);
	}


	$allDataArray = array();
	$allDataArray["globals"] = array("loggingIn" => "false", "mode" => $client_form_data['mode']);

	if ($is_shared_instance)
	{
		$allDataArray["articles"] =
			array("ID" => $articleID,
					"title" => $row['BookArticleLangTitle'],
					"author" => $row['BookArticleLangAuthor'],
					"text" => $client_form_data['articleText'],
					"isShared" => $is_shared_instance
				);
	}

	// Need an array of a hash because the client expects all photo instances to be in an array,
	// whether an array of 1 or an array of a ton
	$allDataArray["articleinstances"] =
		array(array("ID" => $articleID,
			"instanceID" => $articleInstanceID,
			"pageID" => $client_form_data['pageID'],
			"orientation" => $client_form_data['orientation'],
			"Xcoord" => $client_form_data['Xcoord'],
			"Ycoord" => $client_form_data['Ycoord'],
			"width" => $client_form_data['width'],
			"height" => $client_form_data['height'],
			"overwritePlaceholder" => ($client_form_data['mode'] == "add")
		));
	echo json_encode(array("allData" => $allDataArray));
?>