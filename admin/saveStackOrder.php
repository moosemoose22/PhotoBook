<?php
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");

	$client_form_data = json_decode(file_get_contents('php://input'), true);

		$stack_order_array = $client_form_data['stackorder'];
		$globals_array = $client_form_data['globals'];
		$isNewStackorderArray = explode(',', $client_form_data['stackOrder']);
		$ErrString = "";
		$hasCorrectObjectType = true;
		$tablePrefix = "";
		$parentTablePrefix = "";
		$stackOrderObj;
		for ($x = 0; $x < count($stack_order_array); $x++)
		{
			$stackOrderObj = $stack_order_array[$x];
			if (strcmp($stackOrderObj['type'], $g_article_object_name) == 0)
			{
				$parentTablePrefix = "BookArticle";
				$tablePrefix = "BookPageArticle";
			}
			else if (strcmp($stackOrderObj['type'], $g_photo_object_name) == 0)
			{
				$parentTablePrefix = "BookPhoto";
				$tablePrefix = "BookPagePhoto";
			}
			else
			{
				if (!empty($ErrString))
					$ErrString .= $data_delimiter;
				$ErrString .= "Error: no object for parentID " . $stackOrderObj['parentID'] . " and instance ID " . $stackOrderObj['instanceID']
							. " of object " . $stackOrderObj['type'];
				continue;
			}
				
			$checkIfExists = "SELECT BookPageStackOrderVal
								FROM BookPageStackOrder
								WHERE BookPageStackOrderTableName = '" . $stackOrderObj['type'] . "'
								AND BookPageStackOrderTableID in
									(SELECT ". $tablePrefix . "ID
									FROM " . $tablePrefix . "s
									WHERE " . $parentTablePrefix . "ID = " . $stackOrderObj['parentID'] . "
									AND " . $tablePrefix . "InstanceNum = " . $stackOrderObj['instanceID'] . ");";
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
								SET BookPageStackOrderVal = " . $stackOrderObj['stackOrder'] . "
								WHERE BookPageStackOrderTableName = '" . $stackOrderObj['type'] . "'
								AND BookPageStackOrderTableID in
									(SELECT ". $tablePrefix . "ID
									FROM " . $tablePrefix . "s
									WHERE " . $parentTablePrefix . "ID = " . $stackOrderObj['parentID'] . "
									AND " . $tablePrefix . "InstanceNum = " . $stackOrderObj['instanceID'] . ");";
			}
			else
			{
				$tableIDstr = "SELECT " . $tablePrefix . "ID
								FROM " . $tablePrefix . "s
								WHERE " . $parentTablePrefix . "ID = " . $stackOrderObj['parentID'] . "
								AND " . $tablePrefix . "InstanceNum = " . $stackOrderObj['instanceID'] . ";";
				$tableID_sql = $mysqli->query($tableIDstr);
				$row = $tableID_sql->fetch_assoc();
				$tableID = $row[$tablePrefix . "ID"];
				$tableID_sql->free();
				$stackOrderSQL = "INSERT INTO BookPageStackOrder (BookPageStackOrderTableName, BookPageStackOrderTableID,
									BookPageStackOrderVal) VALUES ('" . $stackOrderObj['type'] . "', " . $tableID . ", "
									. $stackOrderObj['stackOrder'] . ");";
				//echo $stackOrderSQL;
				//exit(0);
			}
			$mysqli->query($stackOrderSQL);
			$StackOrderData .= $item_delimiter . "objectType=" . $stackOrderObj['type'] . "&pageID=" . $globals_array['pageID']
							. "&ID=" . $stackOrderObj['parentID'] . "&instanceID=" . $stackOrderObj['instanceID']
							. "&stackOrder=" . $stackOrderObj['stackOrder'] . "&orientation=" . $stackOrderObj['ipadOrientation'] . "&new=" . $stackOrderExists;
			array_push($stack_order_array,
				array("ID" => $stackOrderObj['parentID'],
					"instanceID" => $stackOrderObj['instanceID'],
					"pageID" => $globals_array['pageID'],
					"objectType" => $stackOrderObj['type'],
					"orientation" => $stackOrderObj['ipadOrientation'],
					"stackOrder" => $stackOrderObj['stackOrder'],
					"new" => $stackOrderExists
				)
			);
		}
		$allDataArray = array();
		$allDataArray["globals"] = array("loggingIn" => "false", "mode" => "update");
		$allDataArray["stackorder"] = $stack_order_array;
		echo json_encode(array("allData" => $allDataArray));
		//$errArray = array("error" => $stackOrderSQL, "globals" => $allDataArray["globals"]);
		//echo json_encode(array("allData" => $errArray));
?>