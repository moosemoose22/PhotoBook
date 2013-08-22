function insideAnIpad(el, ipadType)
{
	var iPadDivName = (ipadType == "horizontal") ? "#HorizontalPageLayout" : "#VerticalPageLayout";
	var x1 = $(el).offset().left;
	var y1 = $(el).offset().top;
	var h1 = $(el).outerHeight(true);
	var w1 = $(el).outerWidth(true);
	var b1 = y1 + h1;
	var r1 = x1 + w1;
	var x2 = $(iPadDivName).offset().left;
	var y2 = $(iPadDivName).offset().top;
	var h2 = $(iPadDivName).outerHeight(true);
	var w2 = $(iPadDivName).outerWidth(true);
	var b2 = y2 + h2;
	var r2 = x2 + w2;

	if (b1 < y2 || y1 > b2 || r1 < x2 || x1 > r2) return false;
	return true;
}

function mouseInHorizontal()
{
	var windowWidth = $(window).width();   // returns width of browser viewport
	var windowHeight = $(window).height();   // returns height of browser viewport
	var horizontalXcoord = (windowWidth * .2);
	var horizontalYcoord = (windowHeight * .15);
	return ( (g_mouseX > horizontalXcoord && g_mouseX < (horizontalXcoord + g_ipadLongEnd))
		&& (g_mouseY > horizontalYcoord && g_mouseX < (horizontalYcoord + g_ipadShortEnd)));
}

function mouseInVertical()
{
	var windowWidth = $(window).width();   // returns width of browser viewport
	var windowHeight = $(window).height();   // returns height of browser viewport
	var horizontalXcoord = (windowWidth * .2);
	var horizontalYcoord = (windowHeight * .15) + g_ipadShortEnd + 2;
	return ( (g_mouseX > horizontalXcoord && g_mouseX < (horizontalXcoord + g_ipadShortEnd))
		&& (g_mouseY > horizontalYcoord && g_mouseX < (horizontalYcoord + g_ipadLongEnd)));
}

function mouseCloserToWhichIpad()
{
	// If mouse is above bottom of horizontal, say it's closer to it
	var windowHeight = $(window).height();   // returns height of browser viewport
	var horizontalYcoord = (windowHeight * .15);
	var dividingLine = horizontalYcoord + g_ipadShortEnd + 1;
	if (g_mouseY < dividingLine)
		return "horizontal";
	else
		return "vertical";
}

function elementCloserToWhichIpad(elementID)
{
	// If mouse is above bottom of horizontal, say it's closer to it
	var windowHeight = $(window).height();   // returns height of browser viewport
	var horizontalYcoord = (windowHeight * .15);
	var dividingLine = horizontalYcoord + g_ipadShortEnd + 1;
	if ($("#" + elementID).offset().top < dividingLine)
		return "horizontal";
	else
		return "vertical";
}

function showRightClickMenu(e)
{
	$("#rightclick_menu").show();
	$("#rightclick_menu").offset({ top: g_mouseY, left: g_mouseX } );
	if (AdminManager.isPhotoInstance(e.target.getAttribute("id")))
	{
		g_objectClicked = e.target;
		if (insideAnIpad(e.target, "horizontal") || insideAnIpad(e.target, "vertical"))
		{
			$("#menu_title").html("Resize Image");
			$('.resize_menu_option').css('display', '');
			$('.move_menu_option').css('display', 'none');
			$('.delete_menu_option').css('display', '');
		}
		else
		{
			$("#menu_title").html("Move Image");
			$('.resize_menu_option').css('display', 'none');
			$('.move_menu_option').css('display', '');
			$('.delete_menu_option').css('display', '');
		}
	}
	else if (AdminManager.isArticleInstance(e.target.getAttribute("id")))
	{
		g_objectClicked = e.target;
		$("#menu_title").html("Article Options");
		$('.resize_menu_option').css('display', '');
		$('.move_menu_option').css('display', 'none');
		$('.delete_menu_option').css('display', '');
	}
	if (e.stopPropagation)
		e.stopPropagation();
	if (e.preventDefault)
		e.preventDefault();
	e.cancelBubble = true;
	return false;
}

function chooseImage(e)
{
	var clickedDiv = e.target;
	if (e.stopPropagation)
		e.stopPropagation();
	if (e.preventDefault)
		e.preventDefault();
	e.cancelBubble = true;
	return false;
}

function onMenuClick(menu_action)
{
	//alert(menu_action);
	$("#rightclick_menu").hide();
	if (menu_action == "fill_horizontal")
		fillIpad('horizontal');
	else if (menu_action == "fill_vertical")
		fillIpad('vertical');
	else if (menu_action == "fill_both")
		fillIpad('both');
	else if (menu_action == "move_front")
		moveFront();
	else if (menu_action == "delete_instance")
		deleteInstance();
	else if (menu_action == "move_horizontal_iPad")
		moveIPad("horizontal");
	else if (menu_action == "move_vertical_iPad")
		moveIPad("vertical");
	return true;
}

	var DocumentClickManager = new function()
	{
		this.p_Mode = "default";
		this.p_counter = 1;
		this.p_blockArticleDraw = false;
		this.isMouseDown = false;
		this.Xcoord;
		this.Ycoord;
		this.tempStartDrawXcoord;
		this.tempStartDrawYcoord;
		this.currentArticleID;
		this.init = function()
		{
			$("body").on("click", function(event)
			{
				DocumentClickManager.defocusArticle();
				return false;
			});

			$(document).mousemove( function (e) {
				g_mouseX = e.pageX;
				g_mouseY = e.pageY;
				DocumentClickManager.onMouseMove(parseInt(e.pageX, 10), parseInt(e.pageY, 10));
				return false;
			});

			$(document).mousedown( function() {
				DocumentClickManager.isMouseDown = true;
				return false;
			});

			$(document).mouseup( function()
			{
				DocumentClickManager.onMouseUp();
				return false;
			});
			$("#toolbar").hover(
				function () {
					$(this).css("cursor", "move");
				},
				function () {
					$(this).css("cursor", "default");
				}
			).draggable();
			$("#toolbar").offset({ top: 0, left: g_horizontalOffsetLeft });			
			$("#ExitText").hover(
				function () {
					$(this).css("cursor", "default");
				}
			).on("click", function(event){
				toggleToolbarOptions("text");
				DocumentClickManager.updateMode('text');
			});
			$("#Arrow").hover(
				function () {
					$(this).css("cursor", "default");
				}
			).on("click", function(event){
				toggleToolbarOptions("default");
				DocumentClickManager.updateMode('default');
			});
			$("#StretchImage").hover(
				function () {
					$(this).css("cursor", "default");
				}
			).on("click", function(event){
				changeImageBehavior('fill');
			});
			$("#FitImage").hover(
				function () {
					$(this).css("cursor", "default");
				}
			).on("click", function(event){
				changeImageBehavior('fit');
			});
			$("#GenerateBook").hover(
				function () {
					$(this).css("cursor", "default");
				}
			).on("click", function(event){
				Communicator.publishBook();
			});
		}
		
		this.onMouseMove = function(Xcoord, Ycoord)
		{
			this.Xcoord = Xcoord;
			this.Ycoord = Ycoord;
			if (this.isMouseDown && !this.isArticleDrawBlocked())
			{
				if (this.getMode() == "text" && !this.currentArticleID)
				{
					mydebug("making a box! " + Xcoord, false, true);
					this.currentArticleID = AdminArticleManager.getNewTextboxTempName();
					this.tempStartDrawXcoord = this.Xcoord;
					this.tempStartDrawYcoord = this.Ycoord;
					var orientation = "horizontal";
					this.currentArticleID = AdminArticleManager.writeArticle(AdminArticleManager.getNewTextboxTempName(), 1, "", orientation, this.Xcoord, this.Ycoord, 10, 10);
					//this.currentArticleID = this.writeArticle(this.tempStartDrawXcoord, this.tempStartDrawYcoord);
					mydebug("  making a box! article ID is " + this.currentArticleID, false, true);
				}
				$("#" + this.currentArticleID).css("width", this.Xcoord - this.tempStartDrawXcoord);
				$("#" + this.currentArticleID).css("height", this.Ycoord - this.tempStartDrawYcoord);
			}
		}
		
		this.onMouseUp = function()
		{
			if (this.isMouseDown)
			{
				this.onEndArticleDraw();
				this.isMouseDown = false;
				this.currentArticleID = null;
			}
		}
		
		this.isArticleDrawBlocked = function()
		{
			return this.p_blockArticleDraw;
		}
		
		this.onEndArticleDraw = function()
		{
			if (this.currentArticleID && !this.p_blockArticleDraw)
			{
				g_objectClicked = $("#" + this.currentArticleID);
				this.p_blockArticleDraw = true;
				Communicator.prepareServerMessage(g_objectClicked, g_articleObjectType);
			}
		}
		
		this.getMode = function()
		{
			return this.p_Mode;
		}
		
		this.setBorderColor = function(divID, mode)
		{
			var borderColor;
			if (mode == "highlight")
				borderColor = "red";
			else if (mode == "standard")
				borderColor = "black";
			else
				borderColor = "black";
			var wrappedElementDivID = AdminPhotoManager.getWrappedElementDivID(divID);
			$("#" + wrappedElementDivID).css("border-color", borderColor);
		}
		
		this.setArticleDefaultMode = function(divID)
		{
			;//removeJQueryEvents("#" + divID, "#" + divID);
			;//addJQueryEvents("#" + divID, "#" + divID);
		}
		
		this.articleOnClick = function(element, event)
		{
			var divID = $(element).attr("id");
			if (this.getMode() == "text")
			{
				$(element).attr("contenteditable", true);
				if ($(element).data('draggable'))
					removeJQueryEvents("#" + divID, "#" + divID);
				//$(element).focus();
			}
			else
			{
				$(element).attr("contenteditable", false);
				if (!($(element).data('draggable')))
					addJQueryEvents("#" + divID, "#" + divID);
			}
			$(element).focus();
			if (event)
				event.stopPropagation();
		}
		
		this.articleOnBlur = function(element, event)
		{
			this.p_blockArticleDraw = false;
			$(element).blur();
		}
		/*
		this.writeArticle = function(Xcoord, Ycoord)
		{
			var orientation = "horizontal";
			var newArticleID = AdminArticleManager.writeArticle("temp_" + this.p_counter, 1, "", orientation, Xcoord, Ycoord, 10, 10);
			this.p_counter++;
			return newArticleID;
		}
		*/
		this.defocusArticle = function()
		{
			//mydebug("defocusArticle", false, true);
			if (this.p_Mode == "text")
			{
				if (g_objectClicked)
				{
					this.articleOnBlur(g_objectClicked);
					g_objectClicked = null;
				}
			}
		}
		this.updateMode = function(new_mode)
		{
			this.p_Mode = new_mode;
			$("body").css("cursor", new_mode);
			if (new_mode == "default" && g_objectClicked)
			{
				g_objectClicked.blur();
				this.articleOnClick(g_objectClicked);
			}
			PageManager.updatePageArticleModes(new_mode);
		}
		
		this.getIPadOffset = function(orientation, leftOrTop)
		{
			if (orientation == "horizontal")
			{
				if (leftOrTop == "left")
					return g_horizontalOffsetLeft;
				else if (leftOrTop == "top")
					return g_horizontalOffsetTop;
				else
					return 0;
			}
			else if (orientation == "vertical")
			{
				if (leftOrTop == "left")
					return g_verticalOffsetLeft;
				else if (leftOrTop == "top")
					return g_verticalOffsetTop;
				else
					return 0;
			}
			else
				return 0;
		}
	}