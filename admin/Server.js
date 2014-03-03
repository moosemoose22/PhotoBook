	var g_loadDataAjaxPage = 'loadAllData.php';
	var g_photoAjaxPage = 'savePhotoData.php';
	var g_articleAjaxPage = 'saveArticleData.php';
	var g_stackOrderAjaxPage = 'saveStackOrder.php';
	
	var Communicator = new function()
	{
		this.publishBook = function()
		{
			if (confirm("Are you sure you want to publish ?\nYou'll overwrite the current live version."))
				this.saveDataOnServer("mode=publishBook&bookID=" + g_bookID, g_photoAjaxPage);
		}
	
		this.loadData = function(dataRequested)
		{
			//"ArticleInstances"
			var dataString;
			var dataObj = {};
			if (dataRequested)
				dataString = "load" + dataRequested;
			else
				dataString = "loadAllData";
			dataObj = {UserLogin: g_userLogin, BookID: g_bookID, BookLang: g_defaultLangID};
			dataObj[dataString] = "true";
			this.saveDataOnServer(dataObj, g_loadDataAjaxPage);
		}
		
		this.saveStackOrder = function(pageID)
		{
			var dataObj = {};
			var stackOrderArray = [];
			dataObj['globals'] = {'pageID': pageID};
			// if item is outside iPads, we add 100 to stackOrder
			// client will need this data on the way down
			var orientationArray = new Array();
			var obj;
			for (var x = 0; x < PageManager.pages[pageID].stackOrderArray.length; x++)
			{
				if (PageManager.pages[pageID].stackOrderArray[x] != "")
				{
					obj = PageManager.pages[pageID].stackOrderArray[x];
					stackOrderArray.push(obj);
				}
			}
			dataObj['stackorder'] = stackOrderArray;
			this.saveDataOnServer(dataObj, g_stackOrderAjaxPage);
		}
		
		this.prepareServerMessage = function(UIelement, objectType)
		{
			// Store coords of image that was just dragged
			var Xcoord, Ycoord;
			var objectOffset = $(UIelement).offset();
			var orientation = "";
			if (insideAnIpad(UIelement, "horizontal"))
			{
				Xcoord = objectOffset.left - g_horizontalOffsetLeft;
				Ycoord = objectOffset.top - g_horizontalOffsetTop;
				orientation = "horizontal";
			}
			else if (insideAnIpad(UIelement, "vertical"))
			{
				Xcoord = objectOffset.left - g_verticalOffsetLeft;
				Ycoord = objectOffset.top - g_verticalOffsetTop;
				orientation = "vertical";
			}
			else
			{
				Xcoord = objectOffset.left;
				Ycoord = objectOffset.top;
				orientation = "outside";
			}
			Xcoord = roundToDecimalPoint((Xcoord * g_backwardsResizeByFactor), 4)
			Ycoord = roundToDecimalPoint((Ycoord * g_backwardsResizeByFactor), 4);
			// border around image needs to be added also
			//Xcoord += 1;
			//Ycoord += 1;);
			var pageID = PageManager.getCurrentPageID();
			var width = (parseInt($(UIelement).width()) * g_backwardsResizeByFactor);
			var height = (parseInt($(UIelement).height()) * g_backwardsResizeByFactor);
			var divID = $(UIelement).attr("id");
			var dataPageName;
			var dataObj = {};
			dataObj['pageID'] = pageID;
			dataObj['orientation'] = orientation;
			dataObj['Xcoord'] = Xcoord;
			dataObj['Ycoord'] = Ycoord;
			dataObj['width'] = width;
			dataObj['height'] = height;
			if (AdminManager.isPhotoObj(objectType))
			{
				var photoID = AdminPhotoManager.getPhotoIDfromDivID(divID);
				var photoInstance, photoInstanceID;
				if (AdminPhotoManager.isDraggedPhoto(divID))
					photoInstanceID = AdminPhotoManager.getNextPhotoInstanceID(photoID);
				else
				{
					photoInstanceID = AdminPhotoManager.getPhotoInstanceIDfromDivID(divID);
					photoInstance = AdminPhotoManager.getPhotoInstance(photoID, photoInstanceID);
				}
				var stretchToFill = "false";
				var stackOrder = PageManager.pages[PageManager.getCurrentPageID()].photos.length + 1;
				if (photoInstance)
				{
					stretchToFill = photoInstance.stretchToFill ? "true" : "false";
					stackOrder = AdminManager.getStackOrder(photoID, photoInstanceID, g_photoObjectType);
				}
				var mode = (AdminPhotoManager.isDraggedPhoto(divID) ? "add" : "update");
				dataObj['mode'] = mode;
				dataObj['ID'] = photoID;
				dataObj['instanceID'] = photoInstanceID;
				dataObj['stretchToFill'] = stretchToFill;
				dataPageName = g_photoAjaxPage;
			}
			else if (AdminManager.isArticleObj(objectType))
			{
				var articleID, articleInstanceID, mode, stackOrder;
				if (AdminArticleManager.isNewTextBox(divID))
				{
					mode = "add";
					articleID = "dummyvar";
					articleInstanceID = "dummyvar";
				}
				else
				{
					articleID = AdminArticleManager.getArticleIDfromDivID(divID);
					if (AdminArticleManager.isDraggedSharedArticle(divID))
					{
						mode = "add_instance";
						articleInstanceID = AdminArticleManager.getNextArticleInstanceID(articleID);
					}
					else
					{
						mode = "update";
						articleInstanceID = AdminArticleManager.getArticleInstanceIDfromDivID(divID);
						stackOrder = AdminManager.getStackOrder(articleID, articleInstanceID, g_articleObjectType);
					}
				}
				dataObj['mode'] = mode;
				dataObj['ID'] = articleID;
				dataObj['instanceID'] = articleInstanceID;
				dataObj['BookID'] = g_bookID;
				dataObj['LangID'] = g_defaultLangID;
				
				// Note: JQuery UI adds classes to the innerHTML in order to get resizable to work.
				// You need to turn resizable off in order to get the correct inner HTML.
				// We then immediately turn it back on.
				// See http://stackoverflow.com/questions/2830066/jquery-resizable-ui-problem
				// for more information
				removeJQueryEvents($(UIelement), $(UIelement));
				dataObj['articleText'] = $(UIelement).html();
				addJQueryEvents($(UIelement), $(UIelement));

				dataPageName = g_articleAjaxPage;
			}
			if (dataPageName)
				this.saveDataOnServer(dataObj, dataPageName);
		}
	

		this.deleteObject = function(objectType, objectID, objectInstanceID, pageID)
		{
			var pageName;
			if (AdminManager.isPhotoObj(objectType))
				pageName = g_photoAjaxPage;
			else if (AdminManager.isArticleObj(objectType))
				pageName = g_articleAjaxPage;
			var data = {mode:"delete",ID:objectID};
			if (typeof(objectInstanceID) != "undefined" && objectInstanceID)
			{
				data["instanceID"] = objectInstanceID;
				data["pageID"] = pageID;
			}
			this.saveDataOnServer(data, pageName);
		}
	
		this.saveDataOnServer = function(dataToSend, page)
		{
			Logger.log(page + ":&nbsp;" + JSON.stringify(dataToSend), "text", "to_server");
			$.ajax({
				type : "POST",
				url : page,
				data: JSON.stringify(dataToSend),
				dataType : "json", // data type to be returned
				contentType: "application/json",
				success: function(data) {
					//alert( data ); // shows whole dom
					Communicator.showDataFromServer( data );
					//alert( $(data).find('#wrapper').html() ); // returns null
				},
				error: function(jqXHR, exception)
				{
					var ErrString = "Sorry, The requested property could not be found\n";
					if (jqXHR.status === 0)
						ErrString += 'Not connect.\n Verify Network.';
					else if (jqXHR.status == 404)
						ErrString += 'Requested page not found. [404]';
					else if (jqXHR.status == 500)
						ErrString += 'Internal Server Error [500].';
					else if (exception === 'parsererror')
						ErrString += 'Requested JSON parse failed.';
					else if (exception === 'timeout')
						ErrString += 'Time out error.';
					else if (exception === 'abort')
						ErrString += 'Ajax request aborted.';
					else
						ErrString += 'Uncaught Error.\n' + jqXHR.responseText;
					mydebug(ErrString, true, true);
					mydebug(jqXHR.status, false, true);
					mydebug(jqXHR.responseText, false, true);
					mydebug(exception, false, true);
					//alert(ErrString);
					//for (var x in jqXHR)
						//mydebug(x + ":" + jqXHR[x], false, true);
						//alert(x + ":" + jqXHR[x]);
				}
			});
		}
	
		this.showDataFromServer = function(serverdata)
		{
			var data;
			var dataObj = serverdata;
			var allDataSetsObj = dataObj["allData"];
			var globalVarsObj = allDataSetsObj["globals"];
			var isAddingMode, loggingIn;
			if (globalVarsObj)
			{
				isAddingMode = (("mode" in globalVarsObj) && globalVarsObj['mode'] == "add");
				loggingIn = (("loggingIn" in globalVarsObj) && globalVarsObj['loggingIn'] == "true");
			}
			for (var datasetName in allDataSetsObj)
			{
				if (datasetName == "error")
					alert("Error is: " + allDataSetsObj[datasetName]);
				else if (datasetName == "published")
					alert("Your book was published!");
				else if (datasetName == "deleted")
				{
					var allDataArray = allDataSetsObj[datasetName];
					var type, ID, instanceID, pageID;
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						switch (dataHash["type"])
						{
							case "photoinstance":
								AdminPhotoManager.removePhotoInstance(dataHash["ID"],  dataHash["instanceID"], dataHash["pageID"]);
								var divID = AdminPhotoManager.getPhotoInstanceWrapperDivID(dataHash["ID"], dataHash["instanceID"]);
								$('#' + divID).remove();
								Logger.log("Deleted Photo instance ID " + dataHash["ID"] + "_" + dataHash["instanceID"], "text", "from_server", "Delete photo instance");
								break;
							case "photo":
								var divID = AdminPhotoManager.getPhotoWrapperDivID(dataHash["ID"]);
								$('#' + divID).remove();
								Logger.log("Deleted Photo ID " + dataHash["ID"], "text", "from_server", "Delete photo");
								break;
							case "articleinstance":
								AdminArticleManager.removeArticleInstance(dataHash["ID"], dataHash["instanceID"], dataHash["pageID"]);
								var divID = AdminArticleManager.getArticleInstanceDivID(dataHash["ID"], dataHash["instanceID"]);
								$('#' + divID).remove();
								break;
						}
					}
					/*
					var IDarray = data.substring(8).split(",");
					else if (IDarray[0] == "Article" || IDarray[0] == "ArticleInstance")
					{
						AdminArticleManager.removeArticleInstance(IDarray[1], IDarray[2], IDarray[3]);
						var divID = AdminArticleManager.getArticleInstanceDivID(IDarray[1], IDarray[2]);
						$('#' + divID).remove();
					}
					*/
					g_objectClicked = null;
				}
				else if (datasetName == "pages")
				{
					var allDataArray = allDataSetsObj[datasetName];
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						PageManager.addPage(dataHash["bookID"], dataHash['pageID'], dataHash['pageNum']);
					}
				}
				else if (datasetName == "photos")
				{
					var allDataArray = allDataSetsObj[datasetName];
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						AdminPhotoManager.addPhoto(dataHash['ID'], dataHash['photoURL'], dataHash['width'], dataHash['height'],
							dataHash['widthSmall'], dataHash['heightSmall']);
						addJQueryEvents("#" + AdminPhotoManager.getPhotoWrapperDivID(dataHash['ID']));
					}
				}
				else if (datasetName == "articles")
				{
					var allDataArray = allDataSetsObj[datasetName];
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						var re = new RegExp(g_textDelimeterReplaceAmperstand, 'g');
						dataHash['text'] = dataHash['text'].replace(re, "&");
						if (("mode" in dataHash) && dataHash['mode'] == "add")
							AdminArticleManager.removeTempTextBoxIfExists();
						AdminArticleManager.addUpdateArticle(dataHash['ID'], dataHash['title'],
							dataHash['author'], dataHash['text'], (dataHash['isShared'] == "1"));
					}
				}
				else if (datasetName == "photoinstances")
				{
					var allDataArray = allDataSetsObj[datasetName];
					var isNewPhoto, loggingIn;
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						AdminPhotoManager.addUpdatePhotoInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['Xcoord'], dataHash['Ycoord'], dataHash['width'], dataHash['height'],
							dataHash['stretchToFill'], dataHash['orientation'], isAddingMode, loggingIn);
						var photoInstanceWrapperDivID = AdminPhotoManager.getPhotoInstanceWrapperDivID(dataHash['ID'], dataHash['instanceID']);
						var photoInstanceDivID = AdminPhotoManager.getPhotoInstanceDivID(dataHash['ID'], dataHash['instanceID']);
						if (isAddingMode)
						{
							// Doing this due to JQuery bug
							//
							// JQuery has a ghost of the resizable functionality on the cloned object
							// You can't destroy it because it doesn't exist. Yet it blocks you from
							// resizing the newly cloned object.
							// The current workaround is to add resizable, destroy it, and put it back in
							addJQueryEvents("#" + photoInstanceWrapperDivID, "#" + photoInstanceDivID);
							removeJQueryEvents("#" + photoInstanceWrapperDivID, "#" + photoInstanceDivID);
							//$("#" + photoInstanceWrapperDivID).draggable('destroy');
							//$("#" + AdminPhotoManager.getPhotoInstanceDivID(dataHash['photoID'], dataHash['photoInstanceID'])).resizable('destroy');
							addJQueryEvents("#" + photoInstanceWrapperDivID, "#" + photoInstanceDivID);
						}
					}
				}
				else if (datasetName == "articlesinstances")
				{
					var allDataArray = allDataSetsObj[datasetName];
					var mode, isNewArticleInstance, loggingIn;
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						AdminArticleManager.addUpdateArticleInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['orientation'], dataHash['Xcoord'], dataHash['Ycoord'],
							dataHash['width'], dataHash['height'], isAddingMode, loggingIn);
					}
				}
				else if (datasetName == "stackorder")
				{
					// Note: we're not using the "new" parameter from the server on updates
					var allDataArray = allDataSetsObj[datasetName];
					for (var y = 0; y < allDataArray.length; y++)
					{
						dataHash = allDataArray[y];
						StackOrderManager.updateStackOrder(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['objectType'], dataHash['orientation'], dataHash['stackOrder']);
					}
				}
			}
		}
	}