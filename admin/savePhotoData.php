<?php
	include("SimpleImage_class.php");
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	
	//echo $_REQUEST;
	//print_r($_REQUEST['Ycoord']);
	//exit(0);
	$PhotoData = "";
	$photoInstanceID = "";
	if ($_REQUEST['mode'] == "publishBook")
	{
		// Page photo population **********************************
		$pix_str = "SELECT BookPagePhotoID, BookPhotoURL, BookPageID, BookPagePhotoInstanceNum,
					BookPhotoWidth, BookPhotoHeight, BookPagePhotoWidth, BookPagePhotoHeight,
					BookPagePhotoStretchToFill
					FROM BookPhotos BP
					INNER JOIN BookPagePhotos BPP
						ON BP.BookPhotoID = BPP.BookPhotoID
					WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "'
						AND BookID = " . $_REQUEST['bookID'] . "
						AND BookPagePhotoIpadOrientation != 'outside';";
		$images_sql = $mysqli->query($pix_str);
		while ($row = $images_sql->fetch_assoc())
		{
			$image = new SimpleImage();
			$image->load($g_image_directory_path_user . $row['BookPhotoURL']);
			if ($row['BookPagePhotoStretchToFill'] == 1)
				$image->resize($row['BookPagePhotoWidth'], $row['BookPagePhotoHeight']);
			else
			{
				$image_orig_ratio = $row['BookPhotoWidth'] / $row['BookPhotoHeight'];
				$image_page_ratio = $row['BookPagePhotoWidth'] / $row['BookPagePhotoHeight'];
				if ($image_page_ratio > $image_orig_ratio)
					$image->smart_crop($row['BookPagePhotoWidth'], $row['BookPagePhotoHeight']);
				else
				{
					$image->fit_to_width($row['BookPagePhotoWidth']);
					$newHeight = ($row['BookPagePhotoWidth'] * $row['BookPhotoHeight']) / $row['BookPhotoWidth'];
					$Ycoord = $row['BookPagePhotoYCoord'] + (($row['BookPagePhotoHeight'] - $newHeight) / 2);
					$mysqli->query("UPDATE BookPagePhotos
									SET BookPagePhotoYCoord = $Ycoord,
									BookPagePhotoHeight = $newHeight
									WHERE BookPagePhotoID = " . $row['BookPagePhotoID'] . ";");
				}
			}
			$new_filename = bookImageName($row['BookPageID'], $row['BookPagePhotoInstanceNum'], $row['BookPhotoURL']);
			$image->save($g_image_directory_path_user . $new_filename);
		}
		$images_sql->free();
		echo "Published!";
		exit(0);
	}
	else if ($_REQUEST['mode'] == "delete")
	{
		if (!empty($_REQUEST['instanceID']))
		{
			$db_str = "DELETE FROM BookPagePhotos
						WHERE BookPhotoID = " . $_REQUEST['ID'] . "
						AND BookPagePhotoInstanceNum = " . $_REQUEST['instanceID'] . ";";
			$mysqli->query($db_str);
			echo "Deleted:PhotoInstance," . $_REQUEST['ID'] . "," . $_REQUEST['instanceID'] . "," . $_REQUEST['pageID'];
			exit(0);
		}
		else
		{
			$are_there_photo_instances_str = "SELECT BookPagePhotoID
											FROM BookPagePhotos
											WHERE BookPhotoID = " . $_REQUEST['ID'] . ";";
			$stmt = $mysqli->prepare($are_there_photo_instances_str);
			$stmt->execute();
			$stmt->store_result();
			$there_are_photo_instances = ($stmt->num_rows > 0);
			$stmt->close();
			if ($there_are_photo_instances)
			{
				echo "Error:You cannot delete a photo when there are other copies that exist";
				exit(0);
			}
			else
			{
				$db_str = "SELECT BookPhotoURL
							FROM BookPhotos
							WHERE BookPhotoID = " . $_REQUEST['ID'] . ";";
						
				$file_sql = $mysqli->query($db_str);
				$row = $file_sql->fetch_assoc();
				$filename = $row['BookPhotoURL'];
				$file_sql->free();
				$fullImagePath = $GLOBALS["g_image_directory_path"] . $_SESSION["BookLoginUsername"];
				$fullsize_filename = $fullImagePath . $filename;
				$med_filename = str_replace(".", $g_suffix_medium_image . ".", $fullsize_filename);
				$small_filename = str_replace(".", $g_suffix_small_image . ".", $fullsize_filename);
				if (is_file($fullsize_filename))
					unlink($fullsize_filename);
				if (is_file($med_filename))
					unlink($med_filename);
				if (is_file($small_filename))
					unlink($small_filename);
				$db_str = "DELETE
							FROM BookPhotos
							WHERE BookPhotoID = " . $_REQUEST['ID'] . ";";
				$mysqli->query($db_str);
				echo "Deleted:Photo," . $_REQUEST['ID'];
				exit(0);
			}
		}
	}
	else if ($_REQUEST['mode'] == "add")
	{
		/****************************************
			We now pass stack order from client
		*****************************************

		// Get stack order number (aka z-index) for photo ********************************
		$stackOrder = 0;	
		$stack_order_str = "SELECT IFNULL(MAX(BookPagePhotoStackOrder), 0) as MaxStackOrder
					FROM BookPagePhotos
					WHERE BookPageID = " . $_REQUEST['pageID'] . "
						AND BookPagePhotoIpadOrientation = '" . $_REQUEST['orientation'] . "';";
		$stack_order_sql = $mysqli->query($stack_order_str);
		if (!$stack_order_sql)
			echo "no results";
		else
		{
			$row = $stack_order_sql->fetch_assoc();
			$stackOrder = $row['MaxStackOrder'] + 1;
		}
		$stack_order_sql->free();
		*/
		$photoInstanceID = 0;
	
		// Get instance number for photo ********************************
		$next_instance_str = "SELECT IFNULL(MAX(BookPagePhotoInstanceNum), 0) as MaxInstanceNum
					FROM BookPagePhotos
					WHERE BookPhotoID = " . $_REQUEST['ID'] . ";";
		$next_instance_sql = $mysqli->query($next_instance_str);
		if (!$next_instance_sql)
			echo "no results";
		else
		{
			$row = $next_instance_sql->fetch_assoc();
			$photoInstanceID = $row['MaxInstanceNum'] + 1;
		}
		$next_instance_sql->free();
	
		// Insert photo instance into DB ********************************
		$db_str = "INSERT INTO BookPagePhotos (BookPageID, BookPhotoID, BookPagePhotoInstanceNum,
			BookPagePhotoIpadOrientation, BookPagePhotoXCoord, BookPagePhotoYCoord,
			BookPagePhotoWidth, BookPagePhotoHeight, BookPagePhotoStretchToFill, BookPagePhotoStackOrder)
			VALUES (" . $_REQUEST['pageID'] . ", " . $_REQUEST['ID'] . ", " . $photoInstanceID . ", 
			'" . $_REQUEST['orientation'] . "', " . $_REQUEST['Xcoord'] . ", " . $_REQUEST['Ycoord'] . ", "
				. $_REQUEST['width'] . ", " . $_REQUEST['height'] . ", "
				. $_REQUEST['stretchToFill'] . ", " . $_REQUEST['stackOrder'] . ");";
	}
	else if ($_REQUEST['mode'] == "update")
	{
		/*
		$query  = explode('&', $_SERVER['QUERY_STRING']);
		$params = array();

		foreach( $query as $param )
		{
			list($name, $value) = explode('=', $param);
			$params[urldecode($name)][] = urldecode($value);
		}
		*/
		$photoInstanceID = $_REQUEST['instanceID'];
		// Update photos in DB ********************************
		$db_str = "UPDATE BookPagePhotos
					SET BookPagePhotoInstanceNum = " . $photoInstanceID;
					if (isset($_REQUEST['orientation']))
						$db_str .= ", BookPagePhotoIpadOrientation = '" . $_REQUEST['orientation'] . "'";
					if (isset($_REQUEST['Xcoord']))
						$db_str .= ", BookPagePhotoXCoord = " . $_REQUEST['Xcoord'];
					if (isset($_REQUEST['Ycoord']))
						$db_str .= ", BookPagePhotoYCoord = " . $_REQUEST['Ycoord'];
					if (isset($_REQUEST['width']))
						$db_str .= ", BookPagePhotoWidth = " . $_REQUEST['width'];
					if (isset($_REQUEST['height']))
						$db_str .= ", BookPagePhotoHeight = " . $_REQUEST['height'];
					if (isset($_REQUEST['stretchToFill']))
						$db_str .= ", BookPagePhotoStretchToFill = " . $_REQUEST['stretchToFill'];
					$db_str .= " WHERE BookPhotoID = " . $_REQUEST['ID'] . "
					AND BookPagePhotoInstanceNum = " . $photoInstanceID . ";";
	}
	if (!empty($db_str))
		$mysqli->query($db_str);
	$PhotoData = "PhotoInstance:mode=" . $_REQUEST['mode'] . "&loggingIn=false" . $item_delimiter . "pageID=" . $_REQUEST['pageID']
		. "&ID=" . $_REQUEST['ID'] . "&instanceID=" . $photoInstanceID
		. "&orientation=" . $_REQUEST['orientation'] . "&Xcoord=" . $_REQUEST['Xcoord'] . "&Ycoord=" . $_REQUEST['Ycoord']
		. "&width=" . $_REQUEST['width'] . "&height=" . $_REQUEST['height'] . "&stretchToFill=" . $_REQUEST['stretchToFill'];
	echo $PhotoData;
?>