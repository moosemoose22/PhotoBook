<?php
	include("dbconnect.php");
	include("globals.php");

	if (isset($_REQUEST['stackOrder']))
	{
		$parentIDArray = explode(',', $_REQUEST['parentID']);
		$instanceIDArray = explode(',', $_REQUEST['instanceID']);
		$objectTypeArray = explode(',', $_REQUEST['objectType']);
		$stackArray = explode(',', $_REQUEST['stackOrder']);
		$orientationArray = explode(',', $_REQUEST['stackOrder']);
		$ErrString = "";
		if (count($parentIDArray) == count($instanceIDArray) && count($parentIDArray) == count($objectTypeArray) && count($parentIDArray) == count($stackArray) && isset($_REQUEST['pageID']))
		{
			$hasCorrectObjectType = true;
			$StackOrderData = "StackOrder:mode=update&loggingIn=false";
			$tablePrefix = "";
			$parentTablePrefix = "";
			for ($x = 0; $x < count($parentIDArray); $x++)
			{
				if (strcmp($objectTypeArray[$x], $g_article_object_name) == 0)
				{
					$parentTablePrefix = "BookArticle";
					$tablePrefix = "BookPageArticle";
				}
				else if (strcmp($objectTypeArray[$x], $g_photo_object_name) == 0)
				{
					$parentTablePrefix = "BookPhoto";
					$tablePrefix = "BookPagePhoto";
				}
				else
				{
					if (!empty($ErrString))
						$ErrString .= $data_delimiter;
					$ErrString .= "Error: no object for parentID " . $parentIDArray[$x] . " and instance ID " . $instanceIDArray[$x]
								. " of object " . $objectTypeArray[$x];
					continue;
				}
					
				$checkIfExists = "SELECT BookPageStackOrderVal
									FROM BookPageStackOrder
									WHERE BookPageStackOrderTableName = '" . $objectTypeArray[$x] . "'
									AND BookPageStackOrderTableID in
										(SELECT ". $tablePrefix . "ID
										FROM " . $tablePrefix . "s
										WHERE " . $parentTablePrefix . "ID = " . $parentIDArray[$x] . "
										AND " . $tablePrefix . "InstanceNum = " . $instanceIDArray[$x] . ");";
				$stackOrderExists = false;
				if ($stmt = $mysqli->prepare($checkIfExists))
				{
					$stmt->execute();
					$stmt->store_result();
					$stackOrderExists = ($stmt->num_rows > 0);
					$stmt->close();
				}
				if ($stackOrderExists)
				{
					$stackOrderSQL = "UPDATE BookPageStackOrder
									SET BookPageStackOrderVal = " . $stackArray[$x] . "
									WHERE BookPageStackOrderTableName = '" . $g_article_object_name . "'
									AND BookPageStackOrderTableID in
										(SELECT ". $tablePrefix . "ID
										FROM " . $tablePrefix . "s
										WHERE " . $parentTablePrefix . "ID = " . $parentIDArray[$x] . "
										AND " . $tablePrefix . "InstanceNum = " . $instanceIDArray[$x] . ");";
				}
				else
				{
					$tableIDstr = "SELECT " . $tablePrefix . "ID
									FROM " . $tablePrefix . "s
									WHERE " . $parentTablePrefix . "ID = " . $parentIDArray[$x] . "
									AND " . $tablePrefix . "InstanceNum = " . $instanceIDArray[$x] . ";";
					$tableID_sql = $mysqli->query($tableIDstr);
					$row = $tableID_sql->fetch_assoc();
					$tableID = $row[$tablePrefix . "ID"];
					$tableID_sql->free();
					$stackOrderSQL = "INSERT INTO BookPageStackOrder (BookPageStackOrderTableName, BookPageStackOrderTableID,
										BookPageStackOrderVal) VALUES ('" . $objectTypeArray[$x] . "', " . $tableID . ", "
										. $stackArray[$x] . ");";
					//echo $stackOrderSQL;
					//exit(0);
				}
				$mysqli->query($stackOrderSQL);
				$StackOrderData .= $item_delimiter . "objectType=" . $objectTypeArray[$x] . "&pageID=" . $_REQUEST['pageID']
								. "&ID=" . $parentIDArray[$x] . "&instanceID=" . $instanceIDArray[$x]
								. "&stackOrder=" . $stackArray[$x] . "&orientation=" . $orientationArray[$x] . "&new=" . $stackOrderExists;
			}
			if (!empty($ErrString))
				$StackOrderData .= $data_delimiter . $ErrString;
			echo $StackOrderData;
		}
	}
?>