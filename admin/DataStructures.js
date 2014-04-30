	var PageManager = new function()
	{
		this.pages = {};
		this.pageIDs = new Array();
		this.getCurrentPageID = function()
		{
			return this.getPageIDfromPagenum(g_pageNum);
		}
		this.getPageIDfromPagenum = function(pageNumber)
		{
			pageNumber = parseInt(pageNumber);
			if (!isNaN(pageNumber))
				return this.pageIDs[pageNumber - 1];
		}
		this.addPage = function(bookID, pageID, pageNumber)
		{
			Logger.log(arguments, "function", "from_server", "addPage");
			this.pageIDs[pageNumber - 1] = pageID;
			this.pages[pageID] = new page(bookID, pageID, pageNumber);
			var div = document.createElement('div');
			div.id = this.getPageDivID(pageNumber);
			div.className = "smallpage";
			//div.onclick=changePage;
			// Note: today the Function thing below is case-sensitive (as opposed to lower-case f).
			// No surprise if it breaks one day due to changes in JavaScript.
			div.onclick=Function("changePage('" + pageNumber + "')");
			div.innerHTML = "Page " + pageNumber;
			div.style.display = "";
			div.style.position = "relative";
			$("#pages_container").append(div);
		}
		this.getPageDivID = function(pageNumber)
		{
			return "Page_" + pageNumber;
		}
		this.addPhoto = function(pageID, photoID, photoInstanceID)
		{
			var pageIDindex = AdminPhotoManager.getPhotoInstanceDivID(photoID, photoInstanceID);
			this.pages[parseInt(pageID)].photos[pageIDindex] = AdminPhotoManager.getPhotoInstance(photoID, photoInstanceID);
		}
		this.addArticle = function(pageID, articleID, articleInstanceID)
		{
			var pageIDindex = AdminArticleManager.getArticleInstanceDivID(articleID, articleInstanceID);
			this.pages[parseInt(pageID)].articles[pageIDindex] = AdminArticleManager.getArticleInstance(articleID, articleInstanceID);
		}
		this.removePhotoInstance = function(pageID, photoID, photoInstanceID)
		{
			for (var photoPageID in this.pages[pageID].photos)
			{
				var photoInstanceObj = this.pages[pageID].photos[photoPageID];
				if (photoInstanceObj["photoID"] == photoID && photoInstanceObj["photoInstanceID"] == photoInstanceID)
				{
					delete this.pages[pageID].photos[photoPageID];
					return;
				}
			}
		}
		this.removeArticleInstance = function(pageID, articleID, articleInstanceID)
		{
			for (var articlePageID in this.pages[pageID].articles)
			{
				var photoInstanceObj = this.pages[pageID].articles[articlePageID];
				if (articleInstanceObj.parentID == articleID && articleInstanceObj.instanceID == articleInstanceID)
				{
					delete this.pages[pageID].articles[articlePageID];
					return;
				}
			}
		}
		this.switchPages = function(oldPage, newPage)
		{
			var horizontalPage = document.getElementById("HorizontalPageLayout");
			var verticalPage = document.getElementById("VerticalPageLayout");
			horizontalPage.innerHTML = verticalPage.innerHTML = "";
			horizontalPage.style.backgroundColor = verticalPage.style.backgroundColor = "white";
			if (!this.noPageSelected())
				document.getElementById(this.getPageDivID(oldPage)).setAttribute("class", "smallpage");
			document.getElementById(this.getPageDivID(newPage)).setAttribute("class", "smallpageChosen");
		}
		this.switchPageImages = function(oldPage, newPage)
		{
			var pageObj;
			var isOldPage, isNewPage;
			var newPageID = this.getPageIDfromPagenum(newPage);
			for (var page in PageManager.pages)
			{
				pageObj = this.pages[page];
				isOldPage = (!this.noPageSelected() && pageObj.pageNumber == oldPage);
				isNewPage = (pageObj.pageNumber == newPage);
				if (isOldPage || isNewPage)
				{
					for (var photoPageID in pageObj.photos)
					{
						var divID = AdminPhotoManager.getPhotoInstanceWrapperDivID(pageObj.photos[photoPageID].parentID, pageObj.photos[photoPageID].instanceID);
						StackOrderManager.toggleVisibilityOnPageSwitch(g_photoObjectType, isNewPage, pageObj.photos[photoPageID].parentID, pageObj.photos[photoPageID].instanceID);
					}
					for (var articlePageID in pageObj.articles)
					{
						var divID = AdminArticleManager.getArticleInstanceDivID(pageObj.articles[articlePageID].parentID, pageObj.articles[articlePageID].instanceID);
						StackOrderManager.toggleVisibilityOnPageSwitch(g_articleObjectType, isNewPage, pageObj.articles[articlePageID].parentID, pageObj.articles[articlePageID].instanceID);
						if (DocumentClickManager.getMode() == "text")
							removeJQueryEvents("#" + divID, "#" + divID);
						else if (DocumentClickManager.getMode() == "default")
							addJQueryEvents("#" + divID, "#" + divID);
					}
				}
			}
		}
		this.redrawPageImages = function()
		{
			var pageID = this.getPageIDfromPagenum(g_pageNum);
			for (var photoPageID in this.pages[parseInt(pageID)].photos)
			{
				var instanceObj = this.pages[parseInt(pageID)].photos[photoPageID];
				StackOrderManager.setPhotoStackOrderInsideIpad(AdminPhotoManager.getPhotoInstanceWrapperDivID(instanceObj.parentID, instanceObj.instanceID), instanceObj.parentID, instanceObj.instanceID);
			}
		}
		this.updateAllPageArticleModes = function(new_mode)
		{
			var pageObj = this.pages[this.getCurrentPageID()];
			for (var articlePageID in this.pages[parseInt(pageObj.ID)].articles)
				this.updatePageArticleMode(new_mode, pageObj.articles[articlePageID].parentID, pageObj.articles[articlePageID].instanceID);
		}
		this.updatePageArticleMode = function(new_mode, parentID, instanceID)
		{
			var divID = AdminArticleManager.getArticleInstanceDivID(parentID, instanceID);
			if (new_mode == "default")
				addJQueryEvents($("#" + divID), $("#" + divID));
			else if (new_mode == "text")
				removeJQueryEvents($("#" + divID), $("#" + divID));
		}
		this.storeStackOrder = function(ID, instanceID, pageID, objectType, stackOrder)
		{
			stackOrder = parseInt(stackOrder);
			var pageObj = this.pages[pageID];
			var objectObj;
			if (AdminManager.isPhotoObj(objectType))
				objectObj = AdminPhotoManager.getPhotoInstance(ID, instanceID);
			else if (AdminManager.isArticleObj(objectType))
				objectObj = AdminArticleManager.getArticleInstance(ID, instanceID);
			if (pageObj.stackOrderArray.length >= (stackOrder - 1))
				pageObj.stackOrderArray[stackOrder - 1] = objectObj;
			else
			{
				// If we're missing an item in the stack order,
				// just make it work for now by putting in empty placeholder
				for (var x = pageObj.stackOrderArray.length; x < stackOrder; x++)
				{
					if (x == (stackOrder - 1))
						pageObj.stackOrderArray.push(objectObj);
					else
						pageObj.stackOrderArray.push("");
				}
			}
			if (!objectObj)
				;//alert("parent ID is " + ID + "  instance ID is " + instanceID + "  pageID is" + pageID + "  object type is " + objectType);
			else
				objectObj.stackOrder = stackOrder;
		}
		this.moveObject = function(divID, topOrBottom)
		{
			var parentID, instanceID, objectType;
			var pageID = this.getCurrentPageID();
			if (AdminManager.isPhotoInstance(divID))
			{
				parentID = AdminPhotoManager.getPhotoIDfromDivID(divID);
				instanceID = AdminPhotoManager.getPhotoInstanceIDfromDivID(divID);
				objectType = g_photoObjectType;
			}
			else if (AdminManager.isArticleInstance(divID))
			{
				parentID = AdminArticleManager.getArticleIDfromDivID(divID);
				instanceID = AdminArticleManager.getArticleInstanceIDfromDivID(divID);
				objectType = g_articleObjectType;
			}
			// When I move something to the top I:
			//	A) Make desired object to top of the page
			//	B) Decrement all items that were above desired object
			// Note that the stack order is stored in sequence in an array
			var pageObj = this.pages[pageID];
			var oldObjectType, oldID, currObjectType, currID;
			var desiredObjectCurrentStackOrder = AdminManager.getStackOrder(parentID, instanceID, objectType);
			var stackArrayCopy = pageObj.stackOrderArray.slice(0);
			//var debugStr = "";
			if (topOrBottom == "top")
			{
				for (var stackArrayIndex = pageObj.stackOrderArray.length - 1; stackArrayIndex >= (desiredObjectCurrentStackOrder - 1); stackArrayIndex--)
				{
					//debugStr += (stackArrayIndex + ",");
					if (pageObj.stackOrderArray[stackArrayIndex].parentID == parentID && pageObj.stackOrderArray[stackArrayIndex].instanceID == instanceID)
					{
						pageObj.stackOrderArray[stackArrayIndex].stackOrder = pageObj.stackOrderArray.length;
						stackArrayCopy[stackArrayCopy.length - 1] = pageObj.stackOrderArray[stackArrayIndex];
					}
					else
					{
						pageObj.stackOrderArray[stackArrayIndex].stackOrder--;
						stackArrayCopy[stackArrayIndex - 1] = pageObj.stackOrderArray[stackArrayIndex];
					}
				}
			}
			else if (topOrBottom == "bottom")
			{
				for (var stackArrayIndex = 0; stackArrayIndex < desiredObjectCurrentStackOrder; stackArrayIndex++)
				{
					if (pageObj.stackOrderArray[stackArrayIndex].parentID == parentID && pageObj.stackOrderArray[stackArrayIndex].instanceID == instanceID)
					{
						pageObj.stackOrderArray[stackArrayIndex].stackOrder = 1;
						stackArrayCopy[0] = pageObj.stackOrderArray[stackArrayIndex];
					}
					else
					{
						pageObj.stackOrderArray[stackArrayIndex].stackOrder++;
						stackArrayCopy[stackArrayIndex] = pageObj.stackOrderArray[stackArrayIndex];
					}
				}
			}
			pageObj.stackOrderArray = stackArrayCopy;
			Communicator.saveStackOrder(pageID);
		}
		this.addStackOrder = function(pageID, parentID, instanceID, objectType)
		{
			var pageObj = this.pages[pageID];
			for (var stackArrayIndex = 0; stackArrayIndex < pageObj.stackOrderArray.length; stackArrayIndex++)
			{
				if (pageObj.stackOrderArray[stackArrayIndex].parentID == parentID && pageObj.stackOrderArray[stackArrayIndex].instanceID == instanceID
					&& pageObj.stackOrderArray[stackArrayIndex].type == objectType)
					return;
			}
			var instanceObj;
			if (AdminManager.isPhotoObj(objectType))
				instanceObj	= AdminPhotoManager.getPhotoInstance(parentID, instanceID);
			else if (AdminManager.isArticleObj(objectType))
				instanceObj	= AdminArticleManager.getArticleInstance(parentID, instanceID);
			pageObj.stackOrderArray.push(instanceObj);
			instanceObj.stackOrder = pageObj.stackOrderArray.length;
			Communicator.saveStackOrder(pageID);
		}
		this.noPageSelected = function()
		{
			return isNaN(g_pageNum);
		}
	}
	
	var AdminManager = new function()
	{
		this.getParentID = function(divID)
		{
			var underscore_location = divID.indexOf("_");
			if (underscore_location == -1)
				underscore_location = divID.length;
			return divID.substring(0, underscore_location);
		}
		
		this.isPhotoObj = function(objType)
		{
			return objType == g_photoObjectType;
		}
		
		this.isArticleObj = function(objType)
		{
			return objType == g_articleObjectType;
		}
		
		this.getStackOrder = function(ID, instanceID, objectType)
		{
			var instanceObj;
			if (this.isPhotoObj(objectType))
				instanceObj	= AdminPhotoManager.getPhotoInstance(ID, instanceID);
			else if (this.isArticleObj(objectType))
				instanceObj	= AdminArticleManager.getArticleInstance(ID, instanceID);
			if (instanceObj)
				return instanceObj.stackOrder;
		}
		
		this.isPhotoInstance = function(divID)
		{
			return (divID.substring(0, g_photoPrefix.length) == g_photoPrefix);
		}
		
		this.isArticleInstance = function(divID)
		{
			return (divID.substring(0, g_articlePrefix.length) == g_articlePrefix);
		}
		
		this.trimDragPrefix = function(divID)
		{
			return divID.replace(g_dragPrefix, "");
		}
	}

	var AdminArticleManager = new function()
	{
		this.articleHash = {};
		this.getNextArticleInstanceID = function(articleID)
		{
			return "dummyvar";
			//return this.articleHash[articleID].instances.length + 1;
		}
		this.getArticleIDfromDivID = function(divID)
		{
			divID = this.trimAllPrefixes(divID);
			return AdminManager.getParentID(divID);
		}
		this.getArticleInstanceIDfromDivID = function(divID)
		{
			divID = this.trimAllPrefixes(divID);
			return divID.substring(divID.indexOf("_") + 1);
		}
		this.trimAllPrefixes = function(divID)
		{
			return AdminManager.trimDragPrefix(this.p_trimDraggedArticlePrefix(this.p_trimSharedArticlePrefix(this.p_trimArticlePrefix(divID))));
		}
		this.p_trimDraggedArticlePrefix = function(divID)
		{
			return divID.replace("temp_", "");
		}
		this.p_trimArticlePrefix = function(divID)
		{
			return divID.replace(g_articlePrefix, "");
		}
		this.p_trimSharedArticlePrefix = function(divID)
		{
			return divID.replace(g_sharedArticlePrefix, "");
		}
		this.getArticleInstance = function(articleID, articleInstanceID)
		{
			return this.articleHash[articleID].instances[articleInstanceID];
		}
		this.getArticle = function(articleID)
		{
			return this.articleHash[articleID];
		}
		this.getArticleInstanceDivID = function(articleID, articleInstanceID)
		{
			return (g_articlePrefix + articleID + "_" + articleInstanceID);
		}
		this.getArticleDivID = function(articleID)
		{
			return (g_articlePrefix + articleID);
		}
		this.getSharedArticleDivID = function(articleID)
		{
			return (g_dragPrefix + g_sharedArticlePrefix + articleID);
		}
		this.getDraggedArticleDivID = function(articleID)
		{
			return ("temp_" + g_sharedArticlePrefix + articleID);
		}
		this.getNewTextboxTempName = function()
		{
			return "temp";
		}
		this.isNewTextBox = function(divID)
		{
			return ((divID.substring(0, g_articlePrefix.length) == g_articlePrefix)
					&& (divID.substring(g_articlePrefix.length, g_articlePrefix.length + "temp_".length) == "temp_"));
		}
		this.removeTempTextBoxIfExists = function()
		{
			var tempTextBox = $("#" + g_articlePrefix + "temp_1");
			if (tempTextBox)
				tempTextBox.remove();
		}
		this.isArticle = function(divID)
		{
			divID = AdminManager.trimDragPrefix(divID);
			return (divID.substring(0, g_articlePrefix.length) == g_articlePrefix);
		}
		this.isDraggedSharedArticle = function(divID)
		{
			return ((divID.substring(0, "temp_".length) == "temp_")
					&& (divID.substring("temp_".length, "temp_".length + g_sharedArticlePrefix.length) == g_sharedArticlePrefix));
		}
		this.isSharedArticle = function(divID)
		{
			divID = AdminManager.trimDragPrefix(divID);
			return (divID.substring(0, g_sharedArticlePrefix.length) == g_sharedArticlePrefix);
		}
		this.addUpdateArticle = function(articleID, title, author, text, isShared)
		{
			Logger.log(arguments, "function", "from_server", "addUpdateArticle");
			if (!this.articleHash[articleID])
				this.articleHash[articleID] = new article(articleID);
			if (title)
				this.articleHash[articleID].title = title;
			if (author)
				this.articleHash[articleID].author = author;
			if (text)
				this.articleHash[articleID].text = text;
			if (!(isShared===undefined))
			{
				this.articleHash[articleID].isShared = isShared;
				if (isShared)
					this.writeSharedArticle(articleID);
			}
		}
		this.addUpdateArticleInstance = function(articleID, articleInstanceID, pageID, orientation, Xcoord, Ycoord, imgWidth, imgHeight, writeArticle, loggingIn)
		{
			Logger.log(arguments, "function", "from_server", "addUpdateArticleInstance");
			articleID = parseInt(articleID), articleInstanceID = parseInt(articleInstanceID);
			if (!this.articleHash[articleID])
				this.articleHash[articleID] = new article(articleID);
			var isNewArticle;
			if (!(articleInstanceID in this.articleHash[articleID].instances))
			{
				this.articleHash[articleID].instances[articleInstanceID] = new objectInstance(articleID, articleInstanceID, g_articleObjectType);
				isNewArticle = true;
			}
			var articleInstanceObj = this.articleHash[articleID].instances[articleInstanceID];
			articleInstanceObj.pageID = pageID;
			articleInstanceObj.orientation = orientation;
			articleInstanceObj.Xcoord = Xcoord;
			articleInstanceObj.Ycoord = Ycoord;
			articleInstanceObj.width = imgWidth;
			articleInstanceObj.height = imgHeight;
			if (writeArticle)
				this.writeArticle(articleID, articleInstanceID, this.articleHash[articleID].text, orientation, Xcoord, Ycoord, imgWidth, imgHeight, loggingIn);
			if (!loggingIn)
				PageManager.addStackOrder(pageID, articleID, articleInstanceID, g_articleObjectType);
			if (isNewArticle)
				PageManager.addArticle(pageID, articleID, articleInstanceID);
		}
		this.writeSharedArticle = function(articleID)
		{
			if (this.articleHash[articleID])
			{
				var newdiv = document.createElement("div");
				var newdivID = this.getSharedArticleDivID(articleID);
				newdiv.id = newdivID;
				newdiv.style.border = "1px solid black";
				newdiv.style.padding = "10px";
				newdiv.innerHTML = this.articleHash[articleID].title;
				$("#articles_div").append(newdiv);
				addJQueryEvents("#" + newdivID);
			}
		}

		this.writeArticle = function(articleID, articleInstanceID, articleText, orientation, Xcoord, Ycoord, width, height, hideArticle)
		{
			var newname = g_articlePrefix + articleID + "_" + articleInstanceID;
			var newdiv = document.createElement("div");
			newdiv.id = newname;
			newdiv.className = "article";
			$('body').append(newdiv);
			width = parseFloat(width), height = parseFloat(height);
			Xcoord = parseFloat(Xcoord), Ycoord = parseFloat(Ycoord);
			var iPadOffsetLeft = DocumentClickManager.getIPadOffset(orientation, "left");
			var iPadOffsetTop = DocumentClickManager.getIPadOffset(orientation, "top");
			$("#" + newname).css("position", "absolute").css("z-index", 5000);
			//$("#" + newname).attr("contenteditable",true);
			$("#" + newname).css("overflow-x", "hidden");
			$("#" + newname).offset({ top: (iPadOffsetTop + (Ycoord * g_resizeByFactor)), left: (iPadOffsetLeft + (Xcoord * g_resizeByFactor)) } );
			$("#" + newname).css('width', width * g_resizeByFactor).css('height', height * g_resizeByFactor);
			$('#' + newname).on("contextmenu", showRightClickMenu);
			$("#" + newname).on("click", function(event)
			{
				g_objectClicked = $("#" + newname);
				DocumentClickManager.articleOnClick($("#" + newname), event);
			});
			/*
			$("#" + newname).on("blur", function(event)
			{
				//var articleID = AdminArticleManager.getArticleIDfromDivID($(this).attr("id"));
				//var article = AdminArticleManager.articleHash[articleID];
				//DocumentClickManager.articleOnBlur($(this), event);
				//;DocumentClickManager.setArticleDefaultMode(newname);
			})*/
			if (articleText && articleText.length > 0)
				$("#" + newname).html(articleText);
			if (hideArticle)
				$("#" + newname).hide();
			return newname;
		}
		this.setArticleOpacity = function(el)
		{
			if (insideAnIpad(el, "horizontal") || insideAnIpad(el, "vertical"))
				$(el).css({ opacity: 1 });
			else
			{
				//$(el).appendTo('body');
				if (override)
					$(el).css({ opacity: 1 });
				else
					$(el).css({ opacity: 0.5 });
			}
			return true;
		}
		
		this.removeArticleInstance = function(articleID, articleInstanceID, pageID)
		{
			articleID = parseInt(articleID), articleInstanceID = parseInt(articleInstanceID);
			delete this.articleHash[articleID].instances[articleInstanceID];
			PageManager.removeArticleInstance(pageID, articleID, articleInstanceID);
		}
	}
	
	var AdminPhotoManager = new function()
	{
		this.bookArray = new Array();
		this.photoHash = {};

		this.getPhotoWrapperDivID = function(photoIndex)
		{
			return g_dragPrefix + g_photoPrefix + photoIndex;
		}
		this.getPhotoDivID = function(photoIndex)
		{
			return photoIndex;
		}
		this.addUpdatePhotoInstance = function(photoIndex, photoInstanceID, pageID, Xcoord, Ycoord, imgWidth, imgHeight, stretchToFill, ipadOrientation, isNewPhoto, loggingIn)
		{
			Logger.log(arguments, "function", "from_server", "addUpdatePhotoInstance");
			photoIndex = parseInt(photoIndex), photoInstanceID = parseInt(photoInstanceID);
			var isNewInstance;
			if (this.photoHash[photoIndex])
			{
				if (!(photoInstanceID in this.photoHash[photoIndex].instances))
				{
					isNewInstance = true;
					this.photoHash[photoIndex].instances[photoInstanceID] = new objectInstance(photoIndex, photoInstanceID, g_photoObjectType);
				}
				var instance = this.photoHash[photoIndex].instances[photoInstanceID];
				instance.pageID = pageID;
				instance.ipadOrientation = ipadOrientation;
				instance.Xcoord = parseFloat(Xcoord);
				instance.Ycoord = parseFloat(Ycoord);
				instance.width = parseInt(imgWidth);
				instance.height = parseInt(imgHeight);
				instance.stretchToFill = (stretchToFill == "true" || stretchToFill == true);
			}
			if (isNewInstance)
				PageManager.addPhoto(pageID, photoIndex, photoInstanceID);
			//if (isNewPhoto)
			this.writePhotoInstance(photoIndex, photoInstanceID, isNewPhoto, loggingIn);
			if (!loggingIn)
				PageManager.addStackOrder(pageID, photoIndex, photoInstanceID, g_photoObjectType);
		}
		this.clonePhotoInstance = function(photoID, photoInstanceID)
		{
			var imgWidth = this.photoHash[photoID].photoWidthSmall;
			var imgHeight = this.photoHash[photoID].photoHeightSmall;
			var pageID = PageManager.getCurrentPageID();
			var stackOrder = 1;
			this.addUpdatePhotoInstance(photoID, photoInstanceID, pageID, 0, 0, imgWidth, imgHeight, '', stackOrder);
			return this.getPhotoInstance(photoID, photoInstanceID);
		}
		this.getPhoto = function(photoIndex)
		{
			return this.photoHash[photoIndex];
		}
		this.getPhotoInstance = function(photoIndex, photoInstanceID)
		{
			return this.photoHash[photoIndex].instances[photoInstanceID];
		}
		this.getNextPhotoInstanceID = function(photoIndex)
		{
			return "dummyvar";
			//return this.photoHash[photoIndex].instances.length + 1;
		}
		this.getPhotoInstanceWrapperDivID = function(photoIndex, photoInstanceID)
		{
			return (g_dragPrefix + g_photoPrefix + photoIndex + "_" + photoInstanceID);
		}
		this.getPhotoInstanceDivID = function(photoIndex, photoInstanceID)
		{
			return (g_photoPrefix + photoIndex + "_" + photoInstanceID);
		}
		this.isPhotoOrig = function(divID)
		{
			return (this.trimAllPrefixes(divID).indexOf("_") == -1);
		}
		// If there's an underscore, it's a photo instance div ID.
		// Otherwise, it's a photo div ID
		this.isPhotoInstance = function(divID)
		{
			divID = AdminManager.trimDragPrefix(divID);
			return ((divID.substring(0, g_photoPrefix.length) == g_photoPrefix)
					&& (this.p_trimPhotoPrefix(divID).indexOf("_") != -1));
		}
		this.isPhotoInstanceWrapper = function(divID)
		{
			return ((divID.indexOf(g_dragPrefix) != -1)
					&& (this.trimAllPrefixes(divID).indexOf("_") != -1));
		}
		this.trimAllPrefixes = function(divID)
		{
			return AdminManager.trimDragPrefix(this.p_trimPhotoPrefix(divID));
		}
		this.p_trimPhotoPrefix = function(divID)
		{
			return divID.replace(g_photoPrefix, "");
		}
		this.getWrappedElementDivID = function(divID)
		{
			return AdminManager.trimDragPrefix(divID);
		}
		this.getPhotoIDfromDivID = function(divID)
		{
			divID = this.trimAllPrefixes(divID);
			return AdminManager.getParentID(divID);
		}
		this.getPhotoInstanceIDfromDivID = function(divID)
		{
			divID = this.trimAllPrefixes(divID);
			return divID.substring(divID.indexOf("_") + 1);
		}
		this.getDraggedPhotoInstanceWrapperDivID = function(photoIndex)
		{
			return (g_dragPrefix + g_photoPrefix + photoIndex + "_temp");
		}
		this.isDraggedPhoto = function(divID)
		{
			return (divID.indexOf("_temp") != -1);
		}
		this.removeSuffixFromDraggedElement = function(divID)
		{
			return divID.replace("_temp", "");
		}
		this.getPhotoInstanceFromDivID = function(divID)
		{
			var photoID = this.getPhotoIDfromDivID(divID);
			var photoInstanceID = this.getPhotoInstanceIDfromDivID(divID);
			return this.getPhotoInstance(photoID, photoInstanceID);
		}
		this.writePhotoInstance = function(photoIndex, photoInstanceID, clone, hide)
		{
			var instanceObj = this.getPhotoInstance(photoIndex, photoInstanceID);
			var instanceDivID = this.getPhotoInstanceDivID(photoIndex, photoInstanceID);
			var instanceWrapperDivID = this.getPhotoInstanceWrapperDivID(photoIndex, photoInstanceID);
			if (clone)
			{
				$('#' + this.getPhotoWrapperDivID(photoIndex)).clone(false, false).removeAttr("id").attr("id", instanceWrapperDivID).appendTo('body');
				$('#' + instanceWrapperDivID).children('div:first').removeAttr("id").attr("id", instanceDivID);
				$('#' + instanceWrapperDivID).on("contextmenu", showRightClickMenu);
				$('#' + instanceWrapperDivID).on("click", chooseImage);
			}
			var iPadOffsetLeft = DocumentClickManager.getIPadOffset(instanceObj.ipadOrientation, "left");
			var iPadOffsetTop = DocumentClickManager.getIPadOffset(instanceObj.ipadOrientation, "top");
			//$('#' + instanceDivID).css('background-image', ImageSourceManager.getImgSource(photoIndex, photoInstanceID));
			$('#' + instanceWrapperDivID).css('position','absolute').css('className', 'sidebarImage');
			$('#' + instanceDivID).css('width', instanceObj.width * g_resizeByFactor).css('height', instanceObj.height * g_resizeByFactor);
			$('#' + instanceWrapperDivID).css('width', instanceObj.width * g_resizeByFactor).css('height', instanceObj.height * g_resizeByFactor);
			$('#' + instanceWrapperDivID).offset({ top: (iPadOffsetTop + (instanceObj.Ycoord * g_resizeByFactor)), left: (iPadOffsetLeft - 1 + (instanceObj.Xcoord * g_resizeByFactor)) });
			$('#' + instanceDivID).css('background-size', getBackgroundSize(instanceObj.stretchToFill, $('#' + instanceDivID)));
			if (hide)
				$('#' + instanceWrapperDivID).hide();
		}
		this.saveImgCoords = function(photoID, photoInstanceID, ipadOrientation, Xcoord, Ycoord)
		{
			var instanceObj = this.getPhotoInstance(photoID, photoInstanceID);
			instanceObj.ipadOrientation = ipadOrientation;
			instanceObj.Xcoord = Xcoord;
			instanceObj.Ycoord = Ycoord;
		}

		this.addPhoto = function(photoIndex, URL, photoWidth, photoHeight, photoWidthSmall, photoHeightSmall)
		{
			Logger.log(arguments, "function", "from_server", "addPhoto");
			var div, img, divWidth, divHeight;
			if (!this.photoHash[photoIndex])
				this.photoHash[photoIndex] = new photo(photoIndex, URL, photoWidth, photoHeight, photoWidthSmall, photoHeightSmall);

			div = document.createElement('div');
			div.id = this.getPhotoDivID(photoIndex);
			div.className = "ui-widget-content";
			div.style.display = "";
			div.style.border = "1px solid black";
			div.style.position = "relative";
			div.style.backgroundImage = "url('" + ImageSourceManager.returnThumbnail(URL) + "')"; // !important
			div.style.backgroundRepeat = "no-repeat";
			div.style.backgroundSize = "100%";
			var divWidth = parseInt(photoWidthSmall), divHeight = parseInt(photoHeightSmall);
			div.style.width = ((divWidth + g_scrollbarWidth) * g_resizeByFactor) + 'px';
			div.style.height = ((divHeight + g_scrollbarWidth) * g_resizeByFactor) + 'px';
			div.addEventListener("contextmenu", showRightClickMenu, false);
			dragdiv = document.createElement('div');
			dragdiv.id = this.getPhotoWrapperDivID(photoIndex);
			dragdiv.className = "sidebarImageDrag";
			dragdiv.appendChild(div);
			$("#photos_div").append(dragdiv);
		}
		
		this.setImgOpacityAndStackOrder = function(el, override)
		{
			var divID = $(el).attr("id");
			var photoID = this.getPhotoIDfromDivID(divID);
			var photoInstanceID = this.getPhotoInstanceIDfromDivID(divID);
			if (insideAnIpad(el, "horizontal") || insideAnIpad(el, "vertical"))
			{
				$(el).css({ opacity: 1 });
				//if (insideAnIpad(el, "horizontal"))
				//	$(el).appendTo('#HorizontalPageLayout');
				//else if (insideAnIpad(el, "vertical"))
				//	$(el).appendTo('#VerticalPageLayout');
				StackOrderManager.setPhotoStackOrderInsideIpad(divID, photoID, photoInstanceID);
			}
			else
			{
				//$(el).appendTo('body');
				if (override)
					$(el).css({ opacity: 1 });
				else
					$(el).css({ opacity: 0.5 });
				StackOrderManager.setPhotoStackOrderOutsideIpad($(el).attr("id"), photoID, photoInstanceID);
			}
			//StackOrderManager.mouseCloserToiPad(mouseCloserToWhichIpad(), override);
			return true;
		}
		this.removePhotoInstance = function(photoID, photoInstanceID, pageID)
		{
			photoID = parseInt(photoID), photoInstanceID = parseInt(photoInstanceID);
			delete this.photoHash[photoID].instances[photoInstanceID];
			PageManager.removePhotoInstance(pageID, photoID, photoInstanceID);
		}
		
		this.movePhotoToTop = function(photoDivID, ipadOrientation)
		{
			var thisPhotoIndex = this.getPhotoIDfromDivID(photoDivID);
			var thisPhotoInstanceID = this.getPhotoInstanceIDfromDivID(photoDivID);
			var thisPhotoInstanceObj = this.getPhotoInstance(thisPhotoIndex, thisPhotoInstanceID);
			var pageID = PageManager.getCurrentPageID();
			var newStackOrder = 0;
			for (var photoPageID in PageManager.pages[pageID].photos)
			{
				var instanceObj = PageManager.pages[pageID].photos[photoPageID];
				newStackOrder = (instanceObj.stackOrder > newStackOrder) ? instanceObj.stackOrder : newStackOrder;
				if (instanceObj.parentID == thisPhotoInstanceObj.parentID && instanceObj.instanceID == thisPhotoInstanceObj.instanceID)
					continue;
				else if (instanceObj.stackOrder > thisPhotoInstanceObj.stackOrder)
				{
					instanceObj.stackOrder--;
					StackOrderManager.setPhotoStackOrderInsideIpad(this.getPhotoInstanceWrapperDivID(instanceObj.parentID, instanceObj.instanceID), instanceObj.parentID, instanceObj.instanceID);
				}
			}
			thisPhotoInstanceObj.stackOrder = newStackOrder;
			StackOrderManager.setPhotoStackOrderInsideIpad(this.getPhotoInstanceWrapperDivID(thisPhotoInstanceObj.parentID, thisPhotoInstanceObj.instanceID), thisPhotoInstanceObj.parentID, thisPhotoInstanceObj.instanceID);
		}
	}
	
	var StackOrderManager = new function()
	{
		/*
			Here's how we start:
			ipads: 0
			photos in iPad: 1 - 99
			side divs: 100
			photos out of iPad: 200  - 300
			
			If there are more than 100 photos,
			double all the numbers. If more than 200, double again.
			
		*/
		this.p_maxNumPhotos = 100;
		this.p_stackOrderArray = new Array();
		this.init = function()
		{
			// set zindex of ipads and side divs
			$("#masthead").css('z-index', (this.p_maxNumPhotos));
			$("#leftbar").css('z-index', (this.p_maxNumPhotos));
			$("#rightBar").css('z-index', (this.p_maxNumPhotos));
			$("#SpacerDiv").css('z-index', (this.p_maxNumPhotos));
			$("#masthead").css('z-index', 0);
			$("#leftbar").css('z-index', 0);
			$("#rightBar").css('z-index', 0);
			$("#SpacerDiv").css('z-index', 0);

			// iPads always on the bottom. Nothing goes under them
			$("#HorizontalPageLayout").css('z-index', 0);
			$("#VerticalPageLayout").css('z-index', 0);
		}
		this.toggleVisibilityOnPageSwitch = function(type, showImage, ID, instanceID)
		{
			var divID;
			if (AdminManager.isPhotoObj(type))
				divID = AdminPhotoManager.getPhotoInstanceWrapperDivID(ID, instanceID);
			else if (AdminManager.isArticleObj(type))
				divID = AdminArticleManager.getArticleInstanceDivID(ID, instanceID);
			if (showImage)
				$("#" + divID).show();
			else
				$("#" + divID).hide();
		}
		this.getPhotoStackOrderOutsideIpad = function(storedZIndex)
		{
			return (this.p_maxNumPhotos + storedZIndex);
		}
		this.setPhotoStackOrderOutsideIpad = function(divID, photoID, photoInstanceID)
		{
			;//if (AdminPhotoManager.photoHash[photoID] && AdminPhotoManager.photoHash[photoID].instances[parseInt(photoInstanceID) - 1])
			//	$("#" + divID).css('z-index', (this.p_maxNumPhotos + AdminPhotoManager.photoHash[photoID].instances[parseInt(photoInstanceID) - 1].stackOrder));
		}
		this.getPhotoStackOrderInsideIpad = function(storedZIndex)
		{
			return storedZIndex;
		}
		this.setPhotoStackOrderInsideIpad = function(divID, photoID, photoInstanceID)
		{
			;//if (AdminPhotoManager.photoHash[photoID] && AdminPhotoManager.photoHash[photoID].instances[parseInt(photoInstanceID) - 1])
			//	$("#" + divID).css('z-index', (AdminPhotoManager.photoHash[photoID].instances[parseInt(photoInstanceID) - 1].stackOrder));
		}

		this.areThereTooManyPhotos = function(photoCount)
		{
			if (photoCount >= this.p_maxNumPhotos)
			{
				this.p_maxNumPhotos *= 2;
				this.init();
				this.redrawAllImages();
			}
		}
		
		// If we need to double the max number of photos, redraw all images
		// We redraw other divs in the function areThereTooManyPhotos
		this.redrawStackOrderAllImages = function()
		{
			var count = 0;
			for (var photoID in AdminPhotoManager.photoHash)
			{
				for (var photoInstanceID in AdminPhotoManager.photoHash[photoID].instances)
					$(AdminPhotoManager.getPhotoInstanceWrapperDivID(photoID, photoInstanceID)).css('z-index', (this.p_maxNumPhotos + AdminPhotoManager.photoHash[photoID].instances[photoInstanceID].stackOrder));
				count++;
			}
			alert("redrawn");
		}
		this.mouseCloserToiPad = function(ipadType, override)
		{
			if (override == undefined && insideAnIpad(g_objectClicked, "horizontal") && insideAnIpad(g_objectClicked, "vertical"))
			{
				$("#HorizontalPageLayout").css('z-index', ((ipadType == "horizontal") ? 0 : this.p_maxNumPhotos));
				$("#VerticalPageLayout").css('z-index', ((ipadType == "vertical") ? 0 : this.p_maxNumPhotos));
			}
			else
			{
				$("#HorizontalPageLayout").css('z-index', 0);
				$("#VerticalPageLayout").css('z-index', 0);
			}
		}
		
		this.updateStackOrder = function(ID, instanceID, pageID, objectType, orientation, stackOrder)
		{
			Logger.log(arguments, "function", "from_server", "updateStackOrder");
			var divID;
			if (AdminManager.isArticleObj(objectType))
				divID = AdminArticleManager.getArticleInstanceDivID(ID, instanceID);
			else if (AdminManager.isPhotoObj(objectType))
				divID = AdminPhotoManager.getPhotoInstanceWrapperDivID(ID, instanceID);
			stackOrder = parseInt(stackOrder);
			PageManager.storeStackOrder(ID, instanceID, pageID, objectType, stackOrder);
			if (orientation == "outside")
				stackOrder += this.p_maxNumPhotos;
			$("#" + divID).css('z-index', stackOrder);
		}
	}
	
	var ImageSourceManager = new function()
	{
		this.findMiddleSource = function()
		{
		/*
			var p_medLongSide = g_medImgLongSide;
			var p_smallLongSide = g_smallImgLongSide;
			var multiplier = 1;
			do
			{
				multiplier *= 2;
				p_medLongSide /= multiplier;
				p_smallLongSide *= multiplier;
			} while (p_medLongSide > p_smallLongSide)
			return ((p_medLongSide + p_smallLongSide) / 2);
		*/
			return ( ((g_medImgLongSide / 2) + g_smallImgLongSide) / 2 );
		}
		this.getImgSource = function(photoID, photoInstanceID)
		{
			var photoObj = AdminPhotoManager.photoHash[photoID];
			var photoInstanceObj = AdminPhotoManager.getPhotoInstance(photoID, photoInstanceID);
			var longSide = (photoInstanceObj.width > photoInstanceObj.height) ? photoInstanceObj.width : photoInstanceObj.height;
			if (longSide > this.findMiddleSource())
				return this.returnMediumSize(photoObj.URL);
			else
				return this.returnThumbnail(photoObj.URL);
		}
		this.p_getImageSource = function(img, imageSuffix)
		{
			var extension_regex = /(?:\.([^.]+))?$/;
			var imageExtension = extension_regex.exec(img)[1];
			return g_imageWebLocationUser + (img.replace("." + imageExtension, imageSuffix + "." + imageExtension));
		}
		this.returnMediumSize = function(img)
		{
			return this.p_getImageSource(img, g_suffixMedImage);
		}
		this.returnThumbnail = function(img)
		{
			return this.p_getImageSource(img, g_suffixSmallImage);
		}
	}
	
	var Logger = new function()
	{
		this.p_Log = "";
		this.p_MessageToServer_Log = "";
		this.p_DataFromServer_Log = "";
		this.getLogPrefix = function()
		{
			var now = new Date();
			return "<span style=\"font-size:11px\">" + now.toLocaleDateString() + " " + now.toLocaleTimeString() + "&nbsp;</span>";
		}
		this.log = function(data, dataType, logType, funcName)
		{
			var logString;
			if (dataType == "function")
			{
				var args_string = "";
				//for(var item in data)
				for (var x = 0, len = data.length; x < len; x++)
				{
					if (args_string)
						args_string += ","
					args_string += data[x];
				}
				var myName = data.callee.toString();
				myName = myName.substr('function '.length);
				myName = myName.substr(0, myName.indexOf('('));
				if (!myName)
					myName = funcName;
				logString = myName + "(" + args_string + ")";
			}
			else if (dataType == "text")
				logString = data;
			logString = this.getLogPrefix() + logString;
			if (logType == "to_server")
				this.p_MessageToServer_Log += (logString + "\n<br />");
			else if (logType == "from_server")
				this.p_DataFromServer_Log += (logString + "\n<br />");
			else
				this.p_Log += (logString + "\n<br />");
		}

		this.showLog = function(logType)
		{
			var newdiv = document.createElement("div");
			newdiv.id = "LogDiv";
			newdiv.style.width = "1000px";
			newdiv.style.height = "500px";
			newdiv.style.position = "absolute";
			newdiv.style.left = "20px";
			newdiv.style.top = "20px";
			newdiv.style.backgroundColor = "white";
			newdiv.style.border = "1px solid black";
			newdiv.style.zIndex = 10000;
			newdiv.style.overflowX = "hidden";
			newdiv.style.overflowY = "auto";
			newdiv.style.wordBreak = "break-all";
			$('body').append(newdiv);
			$("#LogDiv").html(Logger.getLogData(logType));
		}
		
		this.closeLog = function()
		{
			$("#LogDiv").remove();
		}

		this.getLogData = function(logType)
		{
			var logData;
			if (logType == "to_server")
				logData = this.p_MessageToServer_Log;
			else if (logType == "from_server")
				logData = this.p_DataFromServer_Log;
			else
				logData = this.p_Log;
			return "<img src=\"" + g_systemImageWebLocation + "X.jpg\" onclick=\"Logger.closeLog()\" style=\"position:relative; float: right\"/>" + logData;
		}
	}
