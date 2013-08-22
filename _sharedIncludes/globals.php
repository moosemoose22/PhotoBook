<?
	setlocale(LC_ALL, "US");
	$g_suffix_medium_image = "_med";
	$g_suffix_small_image = "_small";
	$g_image_web_location =  "/images/book/";
	$g_admin_location =  "/book/admin/";
	$g_system_image_web_location =  $g_image_web_location . "system/";
	$g_image_web_location_user =  $g_image_web_location . $_SESSION["BookLoginUsername"] . "/";
	$g_image_directory_path = "/home/orent/mainwebsite_html" . $g_image_web_location;
	$g_image_directory_path_user = $g_image_directory_path . $_SESSION["BookLoginUsername"] . "/";
	$g_med_img_long_side = 1024;
	$g_small_img_long_side = 100;
	
	$g_photo_object_name = "Photo";
	$g_article_object_name = "Article";
	
	// The following characters can't be typed. Perfect as delimiters
	$item_delimiter = chr(7); // Beep, or bell, character
	$data_delimiter = chr(8); // Backspace
	$text_delimiter_replace_amperstand = chr(27); // Escape
	$text_delimiter_replace_less_than = chr(6); // Acknowledge
	$text_delimiter_replace_greater_than = chr(16); // Data link escape
	
	function bookImageName($pageID, $instanceNum, $URL)
	{
		return str_replace(".", "_" . $pageID . "_" . $instanceNum . ".", $URL);
	}
?>