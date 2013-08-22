<?php
	include("../_sharedIncludes/dbconnect.php");
	include("checkLogin.php");
	include("../_sharedIncludes/globals.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Photobook admin</title>
<script src="http://code.jquery.com/jquery-1.10.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="jquery/perfect_scrollbar/perfect-scrollbar.js"></script>
<script src="jquery/perfect_scrollbar/jquery.mousewheel.js"></script>
<script src="Server.js"></script>
<script src="MouseEvents.js"></script>
<script src="DataStructures.js"></script>
<script>
	var g_pageNum;
	var g_bookID = <?=$_SESSION["BookID"]?>;
	var g_userLogin = "<?=$_SESSION['BookLoginUsername']?>";
	var g_defaultLangID = "<?=$g_book_default_lang?>";
	var g_itemDelimeter = "<?=$item_delimiter?>";
	var g_dataDelimeter = "<?=$data_delimiter?>";
	var g_textDelimeterReplaceAmperstand = "<?=$text_delimiter_replace_amperstand?>";
	var g_resizeByFactor = .5;
	var g_backwardsResizeByFactor = 2;
	//var g_ipadLongEnd = 512;
	//var g_ipadShortEnd = 384;
	var g_ipadLongEnd = 1024 * g_resizeByFactor;
	var g_ipadShortEnd = 768 * g_resizeByFactor;
	var g_smallImgLongEnd = 100;
	var g_smallImgShortEnd = 66;
	var g_photoZindexOffset = 60;
	var g_horizontalOffsetLeft, g_horizontalOffsetTop, g_verticalOffsetLeft, g_verticalOffsetTop;
	var g_scrollbarWidth;
	var g_mouseX = 0, g_mouseY = 0;
	var g_objectClicked = null, g_objectClicked = null, g_objectClicked = null, g_imageDataToCopyToObj;
	var g_alertMe = false;
	var g_fullSize = false;
	var g_dragPrefix = "drag_";
	var g_photoPrefix = "photo_";
	var g_articlePrefix = "art_";
	var g_photoObjectType = "<?=$g_photo_object_name?>";
	var g_articleObjectType = "<?=$g_article_object_name?>";
	var g_sharedArticlePrefix = "shared_";
	var g_medImgLongSide = <?=$g_med_img_long_side?>;
	var g_smallImgLongSide = <?=$g_small_img_long_side?>;
	var g_suffixMedImage = "<?=$g_suffix_medium_image?>";
	var g_suffixSmallImage = "<?=$g_suffix_small_image?>";
	var g_imageWebLocationUser = "<?=$g_image_web_location_user?>";
	var g_systemImageWebLocation = "<?=$g_system_image_web_location?>";

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
	
	function roundToDecimalPoint(data, numberOfPoints)
	{
		var numberOfPoints = parseInt(numberOfPoints);
		if (!isNaN(numberOfPoints))
			return (Math.round( data * numberOfPoints ) / numberOfPoints);
		else
			return numberOfPoints;
	}
	
	function myalert(data, override)
	{
		if (g_alertMe || override)
			alert(data);
	}
	
	function mydebug(data, overwrite, override)
	{
		if (override)
			$("#debug").show();
		if (overwrite)
			$("#debug").html('');
		if (g_alertMe || override)
			$("#debug").html($("#debug").html() + data);
	}
	
	function addJQueryEvents(UIelement1, UIelement2)
	{
		var divID = $(UIelement1).attr("id");
		if (AdminArticleManager.isArticle(divID))
		{
			$(UIelement1).attr("contenteditable", true);
			$(UIelement1).css("overflow-y", "hidden");
		}
		$(function() {
			$( UIelement1 ).hover(
				function () {
					$(this).css("cursor", "move");
				},
				function () {
					$(this).css("cursor", "default");
				}
			).draggable({
				helper:  function () {
					// Here we clone the photo on the left side when you start to drag it
					// We grab photo data from it and create new photo instance data
					// Due to jquery bug, the cloned object disappears on drop.
					// So I put a random zindex of 5000 and we create it later
					var divID = $(this).attr("id");
					// If this is a photo in an iPad, just drag it
					if (AdminArticleManager.isArticle(divID) || AdminPhotoManager.isPhotoInstance(divID))
						return $(this);
					// If this is a photo on the sidebar, clone it
					// so the user can drag a new copy on to an iPad
					else
					{
						var divID;
						if (AdminPhotoManager.isPhotoOrig(divID))
						{
							var photoID = AdminPhotoManager.getPhotoIDfromDivID(divID);
							divID = AdminPhotoManager.getDraggedPhotoInstanceWrapperDivID(photoID);
						}
						else if (AdminArticleManager.isSharedArticle(divID))
						{
							divID = AdminArticleManager.trimAllPrefixes(divID);
							divID = AdminArticleManager.getDraggedArticleDivID(divID);
						}
						return $(this).clone(false, false).attr("id", divID).appendTo('body').css('position','absolute').css('z-index', 5000).css('width', ("'" + (g_resizeByFactor * 100) + "%'")).height("'" + (g_resizeByFactor * 100) + "%'");
					}
 				},
				start: function( event, ui ) {
					var divID = $(ui.helper).attr("id");
					if (AdminArticleManager.isArticle(divID) || AdminArticleManager.isSharedArticle(divID))
					{
						if (g_objectClicked)
							DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
						if (AdminArticleManager.isArticle(divID))
							g_objectClicked = $(ui.helper);
						DocumentClickManager.setBorderColor(divID, "highlight");
					}
					else
					{
						if (g_objectClicked)
							DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
						g_objectClicked = $(ui.helper);
						DocumentClickManager.setBorderColor(divID, "highlight");
					}
				},
				drag: function( event, ui ) {
					var divID = $(ui.helper).attr("id");
					if (AdminPhotoManager.isPhotoOrig(divID) || AdminPhotoManager.isPhotoInstance(divID))
						AdminPhotoManager.setImgOpacityAndStackOrder($(ui.helper));
					//else if (AdminArticleManager.isArticle(divID) || AdminArticleManager.isSharedArticle(divID))
					//	AdminArticleManager.setArticleOpacity($(ui.helper));
				},
				stop: function( event, ui ) {
					/**********************************
						When you finish dragging an image, we run the code here
					**********************************/
					// When you drag an image outside an ipad, the image gets 50% opacity.
					// If you've stopped dragged an image to outside an iPad, we want it to have 100% opacity.
					// We do that by running this code
					var divID = $(ui.helper).attr("id");
					if (AdminPhotoManager.isPhotoOrig(divID) || AdminPhotoManager.isPhotoInstance(divID))
						AdminPhotoManager.setImgOpacityAndStackOrder($(ui.helper), true);

					// If the use hasn't selected a page yet, put an alert
					if (PageManager.noPageSelected())
					{
						alert("Please select a page before moving around images");
						return;
					}
					var divID = $(ui.helper).attr("id");
					if (AdminArticleManager.isArticle(divID))
						Communicator.prepareServerMessage($(ui.helper), g_articleObjectType);
					else if (AdminArticleManager.isDraggedSharedArticle(divID))
						Communicator.prepareServerMessage($(ui.helper), g_articleObjectType);
					else if (AdminPhotoManager.isPhotoOrig(divID) || AdminPhotoManager.isPhotoInstance(divID))
						Communicator.prepareServerMessage($(ui.helper), g_photoObjectType);
				}
			});
			$( UIelement2 ).resizable({
				resize : function(event,ui) {
					var divID = $(ui.helper).attr("id");
					if (AdminPhotoManager.isPhotoOrig(divID) || AdminPhotoManager.isPhotoInstance(divID))
					{
						var photoInstance = AdminPhotoManager.getPhotoInstanceFromDivID($(this).attr("id"));
						var bgSize = getBackgroundSize(false);
						if (photoInstance)
							bgSize = getBackgroundSize(photoInstance.stretchToFill, $(ui.helper));
						$(ui.helper).css('background-size', bgSize);
					}
				},
				start: function( event, ui ) {
					var divID = $(ui.helper).attr("id");
					if (AdminArticleManager.isArticle(divID))
					{
						if (g_objectClicked)
							DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
						g_objectClicked = $(ui.helper);
						DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "highlight");
					}
					else
					{
						if (g_objectClicked)
							DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
						g_objectClicked = $(ui.helper);
						DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "highlight");
					}
				},
				stop: function(event, ui) {
					// If the use hasn't selected a page yet, put an alert
					if (PageManager.noPageSelected())
					{
						alert("Please select a page before moving around images");
						return;
					}

					var divID = $(ui.helper).attr("id");
					if (AdminArticleManager.isArticle(divID))
						Communicator.prepareServerMessage($(ui.helper), g_articleObjectType);
					else if (AdminPhotoManager.isPhotoInstance(divID))
						Communicator.prepareServerMessage($(ui.helper), g_photoObjectType);
				}
			});
		});
	}
	
	function removeJQueryEvents(UIelement1, UIelement2)
	{
		if ($(UIelement1).data('uiDraggable'))
		{
			$(UIelement1).draggable( "destroy" );
			$(UIelement2).resizable( "destroy" );
		}
		var divID = $(UIelement1).attr("id");
		if (AdminArticleManager.isArticle(divID))
		{
			$( UIelement1 ).hover(
				function () {
					$(this).css("cursor", "text");
				},
				function () {
					$(this).css("cursor", "default");
				}
			);
			$(UIelement1).attr("contenteditable", true);
			$(UIelement1).css("overflow-y", "auto");
		}
	}

	function resizePage()
	{
		var windowWidth = $(window).width();   // returns width of browser viewport
		var windowHeight = $(window).height();   // returns height of browser viewport

		// menubar will always be 15% of height. IPads have fixed coordinated.
		// If page needs to scroll vertically, lat left and right bars have full height
		var actualHeightOfDivs = (windowHeight * .15) + g_ipadShortEnd + g_ipadLongEnd;
		var heightForSideBars = (actualHeightOfDivs > windowHeight) ? (actualHeightOfDivs - (windowHeight * .15)) : (windowHeight * .85);
		g_horizontalOffsetLeft = (windowWidth * .2);
		g_horizontalOffsetTop = (windowHeight * .15);
		g_verticalOffsetLeft = (windowWidth * .2);
		g_verticalOffsetTop = ((windowHeight * .15) + g_ipadShortEnd + 2);

		// Note: 2 pixels is width of border. We often have to add that because
		// the 1px black border adds width
		$("#masthead").offset({ top: 0, left: 0 });
		$("#masthead").css('width', (windowWidth - p_getScrollbarWidth()) + 'px');
		$("#masthead").css('height', (windowHeight * .15) + 'px');
		$("#leftbar").offset({ top: (windowHeight * .15), left: 0 });
		$("#leftbar").css('width', (windowWidth * .2) + 'px');
		$("#leftbar").css('height', (heightForSideBars - p_getScrollbarWidth()) + 'px');
		$("#HorizontalPageLayout").offset({ top: g_horizontalOffsetTop, left: g_horizontalOffsetLeft });
		$("#HorizontalPageLayout").css('width', g_ipadLongEnd + 'px');
		$("#HorizontalPageLayout").css('height', g_ipadShortEnd + 'px');
		$("#VerticalPageLayout").offset({ top: g_verticalOffsetTop, left: g_verticalOffsetLeft });
		$("#VerticalPageLayout").css('width', g_ipadShortEnd + 'px');
		$("#VerticalPageLayout").css('height', g_ipadLongEnd + 'px');
		$("#SpacerDiv").offset({ top: ((windowHeight * .15) + g_ipadShortEnd + 2), left: ((windowWidth * .2) + g_ipadShortEnd + 2) });
		$("#SpacerDiv").css('width', (g_ipadLongEnd - g_ipadShortEnd) + 'px');
		$("#SpacerDiv").css('height', g_ipadLongEnd + 'px');
		$("#rightBar").offset({ top: (windowHeight * .15), left: ((windowWidth * .2) + g_ipadLongEnd + 2) });
		$("#rightBar").css('width', ((windowWidth - p_getScrollbarWidth()) - g_ipadLongEnd - (windowWidth * .2)) + 'px');
		$("#rightBar").css('height', (heightForSideBars - p_getScrollbarWidth()) + 'px');
		var photosDivWidth = (g_smallImgLongEnd + 50 + p_getScrollbarWidth()) * g_resizeByFactor;
		$("#photos_div").offset({ top: (windowHeight * .15), left: 10 });
		$("#photos_div").css('width', photosDivWidth + 'px');
		$("#photos_div").css('height', (windowHeight - (windowHeight * .15) - p_getScrollbarWidth()) + 'px');
		$("#articles_div").offset({ top: (windowHeight * .15), left: 20 + photosDivWidth });
		$("#articles_div").css('width', ((g_smallImgLongEnd + 80 + p_getScrollbarWidth()) * g_resizeByFactor) + 'px');
		$("#articles_div").css('height', (windowHeight - (windowHeight * .15) - p_getScrollbarWidth()) + 'px');
		$("#story").offset({ top: (g_ipadShortEnd + g_ipadLongEnd + (windowHeight * .15)), left: 0 });
		$("#story").css('width', (80) + '%');
		$("#story").css('height', '400px');
	}
	
	function changePage(pageNum)
	{
		PageManager.switchPages(g_pageNum, pageNum);
		PageManager.switchPageImages(g_pageNum, pageNum);
		if (g_objectClicked)
			DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
		if (g_objectClicked)
			DocumentClickManager.setBorderColor($(g_objectClicked).attr("id"), "standard");
		g_objectClicked = null;
		g_objectClicked = null;
		g_pageNum = pageNum;
	}

	function addPage()
	{
		var URLstring = "addData.php?data=page&mode=add&bookID=<?=$_SESSION["BookID"]?>";
		uploadData(URLstring);
	}
	
	function changeImageBehavior(behavior)
	{
		if (g_objectClicked)
		{
			var photoInstance = AdminPhotoManager.getPhotoInstanceFromDivID( $( g_objectClicked ).attr("id") );
			photoInstance.stretchToFill = (behavior == "fill");
			if (photoInstance.stretchToFill)
				$( g_objectClicked ).css('background-size', getBackgroundSize(true, g_objectClicked));
			else if (behavior == "fit")
				$( "#resizable" ).css('background-size', getBackgroundSize(false));
			var stretchImageLoc = "<?=$g_system_image_web_location?>System_Photo_StretchToFill" + (photoInstance.stretchToFill ? "_down.gif" : ".gif");
			var fitImageLoc = "<?=$g_system_image_web_location?>System_Photo_FitInBorder" + (!photoInstance.stretchToFill ? "_down.gif" : ".gif");
			$("#StretchImage").attr("src", stretchImageLoc);
			$("#FitImage").attr("src", fitImageLoc);
			// Image wasn't filling background until you resized it.
			// Doing this made it resize the bg immediately
			$( g_objectClicked ).css('width', ($( g_objectClicked ).width() - 1));
			$( g_objectClicked ).css('height', ($( g_objectClicked ).height() - 1));
			$( g_objectClicked ).css('width', ($( g_objectClicked ).width() + 1));
			$( g_objectClicked ).css('height', ($( g_objectClicked ).height() + 1));
			Communicator.prepareServerMessage(g_objectClicked, g_photoObjectType);
		}
	}
	
	function getBackgroundSize(stretchTofillBg, element)
	{
		if (stretchTofillBg)
			return $(element).outerWidth() + 'px ' + $(element).outerHeight() + 'px';
		else
			return '100%';
	}
	
	function prePublishOperations()
	{
		var newServerData = "", tempImgData;
		for (var photoID in AdminPhotoManager.photoHash)
		{
			for (var photoInstanceIndex = 0; photoInstanceIndex < AdminPhotoManager.photoHash[photoID].instances.length; photoInstanceIndex++)
			{
				tempImgData = removeExtraPaddingFromImages(photoID, photoInstanceIndex);
				if (newServerData != "")
					newServerData += "&";
				newServerData += tempImgData;
				tempImgData = "";
			}
		}
		Communicator.saveDataOnServer(newServerData);
	}
	
	/*********************************
	Dealing with: images that are proportional to original width/height
		When you increase the height on a resizable object, the width of the bg
		image stays the same. In this case, there's padding on the top and bottom.
		This causes a problem when we generate the book. The system places the image
		at the top left corner instead of in the middle.
		For now, the best solution seems to be to contract the div so that
		there is no padding.
	*********************************/
	function removeExtraPaddingFromImages(photoID, photoInstanceIndex)
	{
		var photoObj = AdminPhotoManager.getPhoto(photoID);
		var photoInstanceID = AdminPhotoManager.photoHash[photoID].instances[photoInstanceIndex].instanceID;
		var photoInstanceObj = AdminPhotoManager.getPhotoInstance(photoID, photoInstanceID);
		var origPhotoRatio = photoObj.photoWidth / photoObj.photoHeight;
		var pagePhotoRatio = photoInstanceObj.width / photoInstanceObj.height;
		if (pagePhotoRatio < origPhotoRatio)
		{
			var newHeight = ((photoInstanceObj.width * photoObj.photoHeight) / photoObj.photoWidth);
			var Ycoord = photoInstanceObj.Ycoord;
			Ycoord += ((photoInstanceObj.height - newHeight) / 2);
			photoInstanceObj.height = newHeight;
			photoInstanceObj.Ycoord = Ycoord;
			AdminPhotoManager.writePhotoInstance(photoID, photoInstanceID, false);
			return "photoID=" + photoID + "&photoInstanceID=" + photoInstanceID
			+ "&Ycoord=" + roundToDecimalPoint(Ycoord, 4) + "&photoHeight=" + newHeight;
		}
	}
	
function toggleToolbarOptions(cursor)
{
	if (PageManager.noPageSelected())
		alert("You need to choose a page first");
	else
	{
		var ImageLocation = "/images/book/system/";
		$("#ExitText").attr("src", (ImageLocation + ((cursor == "text") ? "Edit_Text_Icon_48_down.png" : "Edit_Text_Icon_48.png")));
		$("#Arrow").attr("src", (ImageLocation + ((cursor == "default") ? "System_mouse_Icon_48_down.png" : "System_mouse_Icon_48.png")));
	}
}

function fillIpad(ipadOrientation)
{
	var iPadDiv;
	if (insideAnIpad(g_objectClicked, "horizontal"))
		iPadDiv = $('#HorizontalPageLayout');
	else if (insideAnIpad(g_objectClicked, "vertical"))
		iPadDiv = $('#VerticalPageLayout');
	if (ipadOrientation == "horizontal" || ipadOrientation == "both")
		$(g_objectClicked).css('width', iPadDiv.width());
	if (ipadOrientation == "vertical" || ipadOrientation == "both")
		$(g_objectClicked).css('height', iPadDiv.height());
	//$( g_objectClicked ).offset({ top: g_horizontalOffsetTop + 'px', left: g_horizontalOffsetLeft + 'px' });
	var imageWrapper;
	if (AdminPhotoManager.isPhotoInstance(g_objectClicked.id) && !AdminPhotoManager.isPhotoInstanceWrapper(g_objectClicked.id))
	{
		imageWrapper = g_objectClicked.parentNode;
		if (ipadOrientation == "horizontal" || ipadOrientation == "both")
			$(imageWrapper).css('width', iPadDiv.width());
		if (ipadOrientation == "vertical" || ipadOrientation == "both")
			$(imageWrapper).css('height', iPadDiv.height());
		$( imageWrapper ).offset({ top: (iPadDiv.offset().top), left: (iPadDiv.offset().left) });
	}
	return true;
}
	
function moveFront()
{
	var iPadOrientation = insideAnIpad(g_objectClicked, "horizontal") ? "horizontal" : "vertical";
	var objectID = ($(g_objectClicked).attr('id'));
	PageManager.moveToTop(objectID);
}

function deletePhoto()
{
	var photoID = AdminPhotoManager.getPhotoIDfromDivID( $( g_objectClicked ).attr("id") );
	if (confirm("Are you sure you want to delete this photo?\nThis will delete the photo from the server."))
		deletePhotoAJAX(photoID);
}

function deleteInstance()
{
	if (AdminManager.isPhotoInstance($( g_objectClicked ).attr("id")))
	{
		var photoID = AdminPhotoManager.getPhotoIDfromDivID( $( g_objectClicked ).attr("id") );
		var photoInstanceID = AdminPhotoManager.getPhotoInstanceIDfromDivID( $( g_objectClicked ).attr("id") );
		if (confirm("Are you sure you want to delete this photo instance?"))
			Communicator.deleteObject(g_photoObjectType, photoID, photoInstanceID, PageManager.getCurrentPageID());
	}
	else if (AdminManager.isArticleInstance($( g_objectClicked ).attr("id")))
	{
		var articleID = AdminArticleManager.getArticleIDfromDivID( $( g_objectClicked ).attr("id") );
		var articleInstanceID = AdminArticleManager.getArticleInstanceIDfromDivID( $( g_objectClicked ).attr("id") );
		if (confirm("Are you sure you want to delete this text box?"))
			Communicator.deleteObject(g_articleObjectType, articleID, articleInstanceID, PageManager.getCurrentPageID());
	}
}

function moveIPad(iPadType)
{
	;
}
	
	// Javascript structures are in a PHP file because
	// we use some global variables there

	// Populate form from data onload ******************************
	$(document).ready(function()
	{
		g_scrollbarWidth = p_getScrollbarWidth();
		// resizePage needs to be called before DocumentClickManager.init
		// because DocumentClickManager.init uses a global variable set in resizePage
		resizePage();
		DocumentClickManager.init();
		$(window).bind('resize', resizePage);
		$('#HorizontalPageLayout').perfectScrollbar();
<?
	// Book data population ***********************************
	$book_str = "SELECT BookID, BookTitle
				FROM Books
				WHERE BookLoginUsername = '" . $_SESSION['BookLoginUsername'] . "';";
	$book_sql = $mysqli->query($book_str);
	while ($row = $book_sql->fetch_assoc())
		echo "AdminPhotoManager.bookArray.push(new book(" . $row['BookID'] . ",'" . $row['BookTitle'] . "'));\n";
	$book_sql->free();
?>
		Communicator.loadData();
		var bookSelect = document.getElementById("BookSelect");
		for (var x = 0; x < AdminPhotoManager.bookArray.length; x++)
		{
			bookSelect.options[bookSelect.options.length] = new Option(AdminPhotoManager.bookArray[x].title, AdminPhotoManager.bookArray[x].ID);
			if (AdminPhotoManager.bookArray[x].ID == g_bookID)
				bookSelect.options[bookSelect.options.length - 1].selected = true;
		}
		//addJQueryEvents(".sidebarImageDrag", ".sidebarImage");
		//addJQueryEvents(".article", ".article");
/*
		(function(){
			$('div.monitor').contextMenu({
				selector: 'div', 
				callback: function(key, options) {
					var m = "clicked: " + key + " on " + $(this).text();
					window.console && console.log(m) || alert(m); 
				},
				items: {
					"edit": {name: "Edit", icon: "edit"},
					"cut": {name: "Cut", icon: "cut"},
					"copy": {name: "Copy", icon: "copy"},
					"paste": {name: "Paste", icon: "paste"},
					"delete": {name: "Delete", icon: "delete"},
					"sep1": "---------",
					"quit": {name: "Quit", icon: "quit"}
				}
			});
		});
*/
		// When you click on a menuitem, this code runs
		$(function() {
			$( "#rightclick_menu" ).menu({
				select: function( event, ui )
				{
					;//alert($(event.target).prop("tagName"));
				}
			});
		});
		
		$('body').click(function() {
			$('#rightclick_menu').hide();
		});
		window.resizeBy(1, 1);
		StackOrderManager.init();
		// Without this line, you get an extra horizontal scroolbar
		window.resizeBy(-1, -1);
	});

// End data structures
//********************************
</script>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css" type="text/css" media="all" />
<link rel="stylesheet" href="jquery/perfect_scrollbar/perfect-scrollbar.css" />
 
<style>
	div.sidebarImage
	{
		position: relative;
		background-repeat: no-repeat;
		background-size: 100%;
	}
	div.sidebarImageDrag
	{
		display: inline;
	}
	div.article
	{
		border: 1px solid black;
	}
	/*background: whitesmoke;*/
	/**/
	div.initBook
	{
		background-color: gray;
		position: absolute;
		border: 1px solid black;
		text-align: center;
		vertical-align: middle;
		font: bold 14px Arial, serif;
		color: white;
	}
	div.sideBarDiv
	{
		position: absolute;
		background-color: white;
	}
	div.smallpage
	{
		cursor: pointer;
		border: 1px solid black;
		width: 11ex;
	}
	div.smallpage:Hover
	{
		color: white;
		background-color: black;
	}
	div.smallpageChosen
	{
		color: white;
		cursor: pointer;
		background-color: black;
		border: 1px solid black;
		width: 11ex;
	}
	td
	{
		vertical-align: top;
	}
	.ui-menu { width: 150px; }
	#toolbar img
	{
		vertical-align: middle;
	}
</style>
</head>
<body style="overflow-y: auto; overflow-x: hidden">
<ul id="rightclick_menu" style="display:none; position:absolute; z-index: 5000; nowrap: true">
  <li id="menu_title" class="ui-state-disabled"><a href="#">Resize image</a></li>
  <li class="resize_menu_option" onclick="onMenuClick('fill_vertical')"><a href="#">Fill screen vertically</a></li>
  <li class="resize_menu_option" onclick="onMenuClick('fill_horizontal')"><a href="#">Fill screen horizontally</a></li>
  <li class="resize_menu_option" onclick="onMenuClick('fill_both')"><a href="#">Fill screen both ways</a></li>
  <li class="resize_menu_option" onclick="onMenuClick('move_front')"><a href="#">Move to front</a></li>
  <li class="delete_menu_option" onclick="onMenuClick('delete_instance')"><a href="#">Delete</a></li>
  <li class="move_menu_option" onclick="onMenuClick('delete_photo')"><a href="#">Delete</a></li>
  <li class="move_menu_option" onclick="onMenuClick('move_horizontal_iPad')"><a href="#">Move to horizontal iPad</a></li>
  <li class="move_menu_option" onclick="onMenuClick('move_vertical_iPad')"><a href="#">Move to vertical iPad</a></li>
</ul>
<!--
<?=$articles_str?>
  <li>
    <a href="#">Delphi</a>
    <ul>
      <li class="ui-state-disabled"><a href="#">Ada</a></li>
      <li><a href="#">Saarland</a></li>
      <li><a href="#">Salzburg</a></li>
    </ul>
  </li>
-->
	<div id="toolbar" style="position:absolute; z-index:10000; border: 1px solid black">
		<table cellspacing="0" cellpadding="0">
		<tr><td>Mode</td><td>Photo</td><td>Publish</td></tr>
		<tr>
		<td><img title="Text mode" src="<?=$g_system_image_web_location?>Edit_Text_Icon_48.png" id="ExitText" />
		<img title="Mouse mode" src="<?=$g_system_image_web_location?>System_mouse_Icon_48_down.png" id="Arrow" />
		</td><td><img title="Stretch image to fill" onclick="changeImageBehavior('fill');" src="<?=$g_system_image_web_location?>System_Photo_StretchToFill.gif" id="StretchImage" />
		<img title="Keep original image proportions" onclick="changeImageBehavior('fit');" src="<?=$g_system_image_web_location?>System_Photo_FitInBorder.gif" id="FitImage" />
		</td><td><img title="Publish book!" src="<?=$g_system_image_web_location?>System_the_scream.jpg" id="GenerateBook" />
		</td></tr></table>
	</div>
	<div style="overflow-y:scroll; background-color: white; border: 1px solid black; position: relative; z-index:101" id="photos_div">Photos:<br/></div>
	<div style="overflow-y:scroll; background-color: white; border: 1px solid black; position: relative; z-index:101" id="articles_div">Shared Articles:<br/></div>
	<div id="masthead" class="sideBarDiv" style="width:100px;height:20px">
		<h2>Photobook admin</h2>
		Book: <select id="BookSelect"></select>
		<button type="button" onclick="Logger.showLog('from_server')">Show log from server</button>
		<button type="button" onclick="Logger.showLog('to_server')">Show log to server</button>
		<!--<button type="button" onclick="g_alertMe=!g_alertMe;$('#debug').show()">Alert toggle</button>
		<button type="button" onclick="window.scrollBy(50, 50)">Scroll</button>
		<button type="button" onclick="$(g_objectClicked).focus()">Focus</button>-->
		<span id="debug" style="display:none"></span>
	</div>
	<div id="leftbar" class="sideBarDiv" style="width:20px; height:80px"></div>
	<div id="HorizontalPageLayout" class="initBook" style="width:50px; height:30px">HorizontalPageLayout</div>
	<div id="VerticalPageLayout" class="initBook" style="width:30px; height:50px">VerticalPageLayout</div>
	<div id="SpacerDiv" class="sideBarDiv" style="width:20px; height:50px"></div>
	<div id="rightBar" class="sideBarDiv" style="width:30px; height:80px">
			<b>Pages:</b><br />
			<div style="border: 1px solid black; cursor: pointer; width: 12ex" onclick="addPage()">+ Add page</div>
			<div id="pages_container">
			</div>
	</div>
	<div id="story"></div>
</body>
</html>
<? include("dbclose.php") ?>