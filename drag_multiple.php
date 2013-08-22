<?php
	include("dbconnect.php");
	include("checkLogin.php");

	$bookID = $_GET['bookID'];
	$bookLang;
	if (!$bookID)
	{
		$default_book_str = "SELECT BookLoginDefaultBookID, BookLoginDefaultLangID
							FROM BookUsers
							WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "'";
		$default_book_sql = $mysqli->query($default_book_str);
		$row = $default_book_sql->fetch_assoc();
		$bookID = $row['BookLoginDefaultBookID'];
		$bookLang = $row['BookLoginDefaultLangID'];
		$default_book_sql->free();
	}
	else
		$bookID = intval($bookID);
?>
<html>
<head>
<title>Photobook admin</title>
<link href="jquery-ui/jquery-ui-1.9.2.custom.css" rel="stylesheet">
<script src="jquery-ui/jquery-1.8.3.js"></script>
<script src="jquery-ui/jquery-ui-1.9.2.custom.js"></script>
<script src="jquery-ui/jquery.event.drag-2.2.js"></script>
<script src="jquery-ui/jquery.event.drag.live-2.2.js"></script>
<script src="jquery-ui/jquery.event.drop-2.2.js"></script>
<script src="jquery-ui/jquery.event.drop.live-2.2.js"></script>
<script type="text/javascript">
	var g_pageNum;
	var g_ipadLongEnd = 512;
	var g_ipadShortEnd = 384;
	var g_smallImgLongEnd = 100;
	var g_smallImgShortEnd = 66;
	var g_scrollbarWidth;

	function p_getScrollbarWidth()
	{
		var div, body, W = window.browserScrollbarWidth;
		if (W === undefined)
		{
			body = document.body, div = document.createElement('div');
			div.innerHTML = '<div style="width: 50px; height: 50px; position: absolute; left: -100px; top: -100px; overflow: auto;"><div style="width: 1px; height: 100px;"></div></div>';
			div = div.firstChild;
			body.appendChild(div);
			W = window.browserScrollbarWidth = div.offsetWidth - div.clientWidth;
			body.removeChild(div);
		}
		return W;
	};

	function changePage(pageNum)
	{
		var horizontalPage = document.getElementById("HorizontalPageLayout");
		var verticalPage = document.getElementById("VerticalPageLayout");
		horizontalPage.innerHTML = verticalPage.innerHTML = "";
		horizontalPage.style.backgroundColor = verticalPage.style.backgroundColor = "white";
		if (g_pageNum)
			document.getElementById("Page" + g_pageNum + "Link").setAttribute("class", "smallpage");
		document.getElementById("Page" + pageNum + "Link").setAttribute("class", "smallpageChosen");
		g_pageNum = pageNum;
	}

	var xhr;
	function uploadFile(file)
	{
		// Uploading - for Firefox, Google Chrome and Safari
		xhr = new XMLHttpRequest();
		
		xhr.addEventListener("readystatechange", function()
		{
			if (xhr.readyState==4 && xhr.status==200)
			{
				if (xhr.responseText.indexOf("Success!") == 0)
				{
					var photoDataArray = xhr.responseText.split("|");
					var newArrayLen = g_adminManager.photoArray.push(new photo(photoDataArray[1],photoDataArray[2]));
					g_adminManager.addPhoto(newArrayLen - 1);
					var divID = g_adminManager.createPhotoDivID((newArrayLen - 1), "visible");
					$(function() {
						$(divID).addDrag();
					});
				}
				else
					alert("Upload didn't work. Result was " + xhr.responseText);
			}
		}, false);

		xhr.open("post", "/book/uploadPix_process.php", true);
		
		// Set appropriate headers
		xhr.setRequestHeader("Content-Type", "multipart/form-data");
		xhr.setRequestHeader("X-File-Name", file.name);
		xhr.setRequestHeader("X-File-Size", file.size);
		xhr.setRequestHeader("X-File-Type", file.type);

		// Send the file (doh)
		xhr.send(file);
	}
	
	function returnThumbnail(img)
	{
		var extension_regex = /(?:\.([^.]+))?$/;
		var imageExtension = extension_regex.exec(img)[1];
		return (img.replace("." + imageExtension, "_small." + imageExtension));
	}
	
	function addPage()
	{
		var URLstring = "addData.php?data=page&mode=add&bookID=<?=$bookID?>";
		uploadData(URLstring);
	}

	function getHorizontalCoords(XorY)
	{
		var horizontalIpad = document.getElementById('HorizontalPageLayout');
		alert(horizontalIpad.x);
		if (XorY == "X")
			return horizontalIpad.x;
		else
			return horizontalIpad.y;
	}
	
	var ajaxTextObj = new XMLHttpRequest();
	ajaxTextObj.addEventListener("readystatechange", function()
	{
		if (ajaxTextObj.readyState==4 && ajaxTextObj.status==200)
			alert(ajaxTextObj.responseText);
	}, false);

	function uploadData(dataURLstring)
	{
		if (ajaxTextObj)
		{
			ajaxTextObj.open("GET", dataURLstring, true);
			ajaxTextObj.send(null);
		}
	}
	
	function traverseFiles(files)
	{
		if (typeof files !== "undefined")
		{
			for (var i=0, l=files.length; i<l; i++)
				uploadFile(files[i]);
		}
		else
			alert("No support for the File API in this web browser");
	}

	function init()
	{
		g_dropArea = document.getElementById("drop-area");

		g_dropArea.addEventListener("dragleave", function (evt) {
			var target = evt.target;
			
			if (target && target === g_dropArea)
				this.className = "";
			evt.preventDefault();
			evt.stopPropagation();
		}, false);
		
		g_dropArea.addEventListener("dragenter", function (evt) {
			this.className = "over";
			evt.preventDefault();
			evt.stopPropagation();
		}, false);
		
		g_dropArea.addEventListener("dragover", function (evt) {
			evt.preventDefault();
			evt.stopPropagation();
		}, false);
		
		g_dropArea.addEventListener("drop", function (evt) {
			traverseFiles(evt.dataTransfer.files);
			this.className = "";
			evt.preventDefault();
			evt.stopPropagation();
		}, false);
	}
//********************************
// Begin data structures
	function book(ID, title)
	{
		this.ID = ID;
		this.title = title;
	}
	
	function photo(ID, URL, orientation)
	{
		this.ID = ID;
		this.URL = URL;
		this.orientation = orientation;
	}
	
	function article(ID, title, text)
	{
		this.ID = ID;
		this.title = title;
		this.text = text;
	}
	
	function page(bookID, pageNumber)
	{
		this.bookID = bookID;
		this.pageNumber;
		this.photos = new Array();
		this.articles = new Array();
	}
	function adminManager()
	{
		this.topPhotoMargin = 120;
		this.bookArray = new Array();
		this.photoArray = new Array();
		this.articleArray = new Array();
		
		this.createPhotoDivID = function(photoIndex, prefix)
		{
			return (this.photoArray[photoIndex].URL).replace(".", "") + "_" + prefix;
		}
		this.addPhoto = function(photoIndex)
		{
			return;
			var photosContainer = document.getElementById("photos_container");
			var div, img, divWidth, divHeight;
			var picVisibilityArray = new Array("visible", "hidden");
			for (var y = 0; y < picVisibilityArray.length; y++)
			{
				div = document.createElement('div');
				div.id = this.createPhotoDivID(photoIndex, picVisibilityArray[y]);
				div.className = "drag";
				div.style.display = (picVisibilityArray[y] == "visible") ? "" : "none";
				div.style.left = '20px';
				div.style.top = this.topPhotoMargin + 'px';
				div.setAttribute('topMarginAttr', this.topPhotoMargin + 'px');
				img = document.createElement('img');
				img.className = "resize";
				img.src = "/images/book/zion/" + returnThumbnail(this.photoArray[photoIndex].URL);
				if (this.photoArray[photoIndex].orientation == "horizontal")
				{
					divWidth = 100;
					divHeight = 66;				
				}
				else
				{
					divWidth = 66;
					divHeight = 100;				
				}
				div.style.width = divWidth + g_scrollbarWidth;
				div.style.height = divHeight + g_scrollbarWidth;
				div.appendChild(img);
				photosContainer.appendChild(div);
//				alert(img.naturalHeight);
			}
//			alert(document.getElementById(this.createPhotoDivID(photoIndex, "visible")).children[0].naturalHeight);
			this.topPhotoMargin += (90 + g_scrollbarWidth);
		}
	}
	var g_adminManager = new adminManager();
	var book, photo, article, page;
<?
	// Book data population ***********************************
	$book_str = "SELECT BookID, BookTitle
				FROM Books
				WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "';";
	$book_sql = $mysqli->query($book_str);
	while ($row = $book_sql->fetch_assoc())
		echo "g_adminManager.bookArray.push(new book(" . $row['BookID'] . ",'" . $row['BookTitle'] . "'));\n";
	$book_sql->free();

	// Photo data population **********************************
	$pix_str = "SELECT BookPhotoID, BookPhotoURL,
					CASE WHEN BookPhotoHeight > BookPhotoWidth THEN 'vertical' ELSE 'horizontal' END as 'orientation'
				FROM BookPhotos
				WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "';";
	$images_sql = $mysqli->query($pix_str);
	while ($row = $images_sql->fetch_assoc())
		echo "g_adminManager.photoArray.push(new photo(" . $row['BookPhotoID'] . ",'" . $row['BookPhotoURL'] . "','" . $row['orientation'] . "'));\n";
	$images_sql->free();

	// Article data population ********************************
	$articles_str = "SELECT BookArticleLangID, BookArticleLangTitle, BookArticleLangText
				FROM BookArticleLangs
				WHERE BookArticleID IN
					(SELECT BookArticleID
					FROM BookArticles
					WHERE BookID = $bookID);";
	$articles_sql = $mysqli->query($articles_str);
	while ($row = $articles_sql->fetch_assoc())
	{
		echo "g_adminManager.articleArray.push(new article(" . $row['BookArticleLangID'] . ", " . json_encode($row['BookArticleLangTitle']) . ",";
		echo json_encode($row['BookArticleLangText']) . "));\n";
	}
	$articles_sql->free();
?>
	// Populate form from data onload ******************************
	$(document).ready(function()
	{
		g_scrollbarWidth = p_getScrollbarWidth();

		var bookSelect = document.PhotoBookForm.BookSelect;
		for (var x = 0; x < g_adminManager.bookArray.length; x++)
		{
			bookSelect.options[bookSelect.options.length] = new Option(g_adminManager.bookArray[x].title, g_adminManager.bookArray[x].ID);
			if (g_adminManager.bookArray[x].ID == <?=$bookID?>)
				bookSelect.options[bookSelect.options.length - 1].selected = true;
		}

		for (var x = 0; x < g_adminManager.photoArray.length; x++)
			g_adminManager.addPhoto(x);

		(function($) {
		jQuery.fn.addDrag = function()
		{
			$('.drag')
				.click(function()
				{
					$( this ).toggleClass("selected");
				})
				.drag("init",function(){
					if ( $( this ).is('.selected') )
						return $('.selected');
				})
				.drag(function( ev, dd )
				{
					$( this ).css({
						top: dd.offsetY,
						left: dd.offsetX
					});
				})
				.drop(function( ev, imgDiv )
				{
					var x=$("#HorizontalPageLayout").offset();
					var y=$("#VerticalPageLayout").offset();
					var horizontalDivHeight = $("#HorizontalPageLayout").height();
					var horizontalDivWidth = $("#HorizontalPageLayout").width();
					var verticalDivHeight = $("#VerticalPageLayout").height();
					var verticalDivWidth = $("#VerticalPageLayout").width();

					var insideHorizontal = (imgDiv.offsetX > x.left && imgDiv.offsetY > x.top) &&
						(imgDiv.offsetX < (x.left + horizontalDivWidth) && imgDiv.offsetY < (x.top + horizontalDivHeight));
					var insideVertical = (imgDiv.offsetX > y.left && imgDiv.offsetY > y.top) &&
						(imgDiv.offsetX < (y.left + verticalDivWidth) && imgDiv.offsetY < (y.top + verticalDivHeight));

					var divIDhidden = ($(imgDiv.target).attr("id")).replace("_visible", "_hidden");
					var imgDivHidden = $("#" + divIDhidden);

					var childImg = $(imgDiv.target).children();
					var childImgHidden = imgDivHidden.children();
					if (insideHorizontal || insideVertical)
					{
						var ipadElement = insideHorizontal ? x : y;
						var ipadElementWidth = insideHorizontal ? g_ipadLongEnd : g_ipadShortEnd;
						var ipadElementHeight = insideHorizontal ? g_ipadShortEnd : g_ipadLongEnd;
						imgDivHidden.show();
						$( this ).css({
							top: ipadElement.top,
							left: ipadElement.left
						});
						var original_ratio = childImg.width() / childImg.height();
						var designer_ratio = ipadElementWidth / ipadElementHeight;
						var designer_width = ipadElementWidth;
						var designer_height = ipadElementHeight;
						if (original_ratio > designer_ratio)
							designer_height = ipadElementWidth / original_ratio;
						else
							designer_width = ipadElementHeight * original_ratio;
						childImg.height(designer_height);
						childImg.width(designer_width);

						var ipadElementForHidden = insideHorizontal ? y : x;
						childImgHidden.show();
						imgDivHidden.css({
							top: ipadElementForHidden.top,
							left: ipadElementForHidden.left
						});
						var ipadElementWidthHidden = insideHorizontal ? g_ipadShortEnd : g_ipadLongEnd;
						var ipadElementHeightHidden = insideHorizontal ? g_ipadLongEnd : g_ipadShortEnd;
						var original_ratio_hidden = childImgHidden.width() / childImgHidden.height();
						var designer_ratio_hidden = ipadElementWidthHidden / ipadElementHeightHidden;
						var designer_width_hidden = ipadElementWidthHidden;
						var designer_height_hidden = ipadElementHeightHidden;
						if (original_ratio_hidden > designer_ratio_hidden)
							designer_height_hidden = ipadElementWidthHidden / original_ratio_hidden;
						else
							designer_width_hidden = ipadElementHeightHidden * original_ratio_hidden;
						childImgHidden.height(designer_height_hidden);
						childImgHidden.width(designer_width_hidden);
					}
					else
					{
						var topAttr = $(this).attr("topMarginAttr");
						$( this ).css({
							top: topAttr,
							left: '20px'
						});
						childImg.css('width', 'auto');
						childImg.css('height', 'auto');
						//childImg.height(designer_height);
						//childImg.width(designer_width);
					}
				});
		}
		})(jQuery);
		$('#').addDrag();
	});

// End data structures
//********************************
</script>
<style type="text/css">
	td {vertical-align: top}
	.drag
	{
		position: absolute;
		height: 58px;
		width: 58px;
		cursor: move;
		top: 120px;
		resize: both;
		overflow: auto;
	}
	.resize
	{
		border: 1px solid black;
		height: auto;
		width: auto;
	}
	.selected
	{
		/*
		border: 1px solid #89B;
		background-color: #ECB;
		border-color: #B98;
		*/
	}
	div.initBook
	{
		border: 1px solid black;
		background-color: gray;
		text-align: center;
		vertical-align: middle;
		font: bold 14px Arial, serif;
		color: white;
	}
	div.smallpage
	{
		border: 1px solid black;
		cursor: pointer;
	}
	div.smallpage:Hover
	{
		background-color: black;
		color: white;
	}
	div.smallpageChosen
	{
		background-color: black;
		color: white;
		border: 1px solid black;
		cursor: pointer;
	}
</style>
</head>
<body onload="init()">
<form action="uploadPix_process.php" name="PhotoBookForm" method="post" enctype="multipart/form-data">
<h2>Photobook admin</h2>
<table>
<tr><td colspan="3">
	Book: <select name="BookSelect">
	</select>
</td></tr>
<tr>
<td style="width: 200px">
	<b>Photos:</b><br />
	<span id="photos_container">
	</span>
	<div id="drop-area" style="position: relative; top: 350px; border: 1px solid black; height: 300px; width: 100px">
		<span>Drag and drop photos here to upload</span>
	</div>
</td>
<td style="width: 500px">
	<div id="HorizontalPageLayout" class="initBook" style="height: 384px; width: 512px">Choose page</div><br />
	<div id="VerticalPageLayout" class="initBook" style="height: 512px; width: 384px">Choose page</div>
</td>
<td>
	<b>Pages:</b><br />
	<div style="border: 1px solid black; cursor: pointer" onclick="addPage()">+ Add page</div>
	<?
		$pages_str = "SELECT BookPageNum
					FROM BookPages
					WHERE BookID = " . $bookID . ";";
		$pages_sql = $mysqli->query($pages_str);
		$topMargin = 120;
		echo "<table>";
		while ($row = $pages_sql->fetch_assoc())
		{
			echo "<tr><td><div id=\"Page" . $row['BookPageNum'] . "Link\" class=\"smallpage\" onclick=\"changePage(" . $row['BookPageNum'] . ")\">Page " . $row['BookPageNum'] . "</div></td></tr>\n";
		}
		echo "</table>";
		$pages_sql->free();
	?>
</td>
</tr></table>
</form>
</body>
</html>
<? include("dbclose.php") ?>