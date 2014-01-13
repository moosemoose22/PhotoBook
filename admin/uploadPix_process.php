<?php
	include("SimpleImage.php");
	include("../_sharedIncludes/dbconnect.php");
	include("../_sharedIncludes/globals.php");
	
	/****************************
		functions begin here
	****************************/
	
	function uploadImage()
	{
		$uploadedViaAjax = (empty($_SERVER['HTTP_X_FILE_NAME'])) ? false : true;
		
		$imageDirectoryPath = $GLOBALS["g_image_directory_path"];
		$imageDirectory = $_SESSION["BookLoginUsername"];
		$g_fullImagePath = $imageDirectoryPath . $imageDirectory;
		
		$fileName = $uploadedViaAjax ? $_SERVER['HTTP_X_FILE_NAME'] : $_FILES["file"]["name"];
		$fileType = $uploadedViaAjax ? $_SERVER['HTTP_X_FILE_TYPE'] : $_FILES["file"]["type"];
		$fileSize = $uploadedViaAjax ? $_SERVER['HTTP_X_FILE_SIZE'] : $_FILES["file"]["size"];
		
		$allowedExts = array("jpg", "jpeg", "gif", "png");
		$extension = end(explode(".", $fileName));
		//&& ($_FILES["file"]["size"] < 20000)
		if ((($fileType == "image/gif") || ($fileType == "image/jpeg")
			|| ($fileType == "image/png") || ($fileType == "image/pjpeg"))
			&& in_array($extension, $allowedExts))
		{
			if (!$uploadedViaAjax && $_FILES["file"]["error"] > 0)
				return "Error: " . $_FILES["file"]["error"];
			else
			{
				if (!is_dir($g_fullImagePath))
					mkdir($g_fullImagePath, 0755);
				if (file_exists($g_fullImagePath . "/" . $fileName))
					return "Error: " . $fileName . " already exists. ";
				else
				{
					if ($uploadedViaAjax)
					{
						file_put_contents(
							$g_fullImagePath . "/" . $fileName,
							file_get_contents('php://input')
						);
						return $fileName;
					}
					else
					{
						move_uploaded_file($_FILES["file"]["tmp_name"],
						$g_fullImagePath . "/" . $fileName);
						return $fileName;
					}
				}
			}
		}
		else
			return "Error: Invalid file. You can only upload image files.";
	}

	function resizeAndSaveToDB($filename)
	{
		$mysqli = $GLOBALS["mysqli"];
		//$imageDirectoryPath = $GLOBALS["g_image_directory_path"];
		//$imageDirectory = $_SESSION["BookLoginUsername"];
		$fullImagePath = $GLOBALS["g_image_directory_path"] . $_SESSION["BookLoginUsername"];
	
		$image = new SimpleImage();
		$image->load($fullImagePath . "/" . $filename);
		$uploadedWidth = $image->getWidth();
		$uploadedHeight = $image->getHeight();
		if ($uploadedWidth > $uploadedHeight)
			$image->resizeToWidth(($uploadedWidth > 1024) ? 1024 : $uploadedWidth);
		else
			$image->resizeToHeight(($uploadedHeight > 1024) ? 1024 : $uploadedHeight);
		
		$new_filename = str_replace(".", $GLOBALS["g_suffix_medium_image"] . ".", $filename);
		$image->save($fullImagePath . "/" . $new_filename);
		
		if ($uploadedWidth > $uploadedHeight)
			$image->resizeToWidth(100);
		else
			$image->resizeToHeight(100);
		
		$new_filename = str_replace(".", $GLOBALS["g_suffix_small_image"] . ".", $filename);
		$image->save($fullImagePath . "/" . $new_filename);

		$db_insert_str = "INSERT INTO BookPhotos (BookLoginUsername, BookPhotoURL, BookPhotoWidth, BookPhotoHeight, BookPhotoWidthSmall, BookPhotoHeightSmall)
						VALUES ('" . $_SESSION["BookLoginUsername"] . "', '$filename', $uploadedWidth, $uploadedHeight, " . $image->getWidth() . ", " . $image->getHeight() . ");";
		
		$mysqli->query($db_insert_str);
		$photoDataForClient = $mysqli->insert_id . $GLOBALS["data_delimiter"] . $filename  . $GLOBALS["data_delimiter"] . $uploadedWidth
			. $GLOBALS["data_delimiter"] . $uploadedHeight . $GLOBALS["data_delimiter"] . $image->getWidth() . $GLOBALS["data_delimiter"] . $image->getHeight();
		return $photoDataForClient;
	}

	/*******************************
			Code begins here
	********************************/
	
	$result = uploadImage();
	if (substr($result, 0, 6) != "Error:")
	{
		$photoDataForClient = resizeAndSaveToDB($result);
		echo "Success:{$photoDataForClient}";
	}
	else
		echo $result;
?>