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
			if (dataRequested)
				dataString = "load" + dataRequested + "=true";
			else
				dataString = "loadAllData=true";
			dataString += "&UserLogin=" + g_userLogin + "&BookID=" + g_bookID + "&BookLang=" + g_defaultLangID;
			this.saveDataOnServer(dataString, g_loadDataAjaxPage);
		}
		
		this.saveStackOrder = function(pageID)
		{
			var dataToSend = "pageID=" + pageID;
			var parentIDArray = new Array();
			var instanceIDArray = new Array();
			var objectTypeArray = new Array();
			var stackOrderArray = new Array();
			// if item is outside iPads, we add 100 to stackOrder
			// client will need this data on the way down
			var orientationArray = new Array();
			var obj;
			for (var x = 0; x < PageManager.pages[pageID].stackOrderArray.length; x++)
			{
				if (PageManager.pages[pageID].stackOrderArray[x] != "")
				{
					obj = PageManager.pages[pageID].stackOrderArray[x];
					parentIDArray.push(obj.parentID);
					instanceIDArray.push(obj.instanceID);
					objectTypeArray.push(obj.type);
					stackOrderArray.push(obj.stackOrder);
					orientationArray.push(obj.ipadOrientation);
				}
			}
			dataToSend += "&parentID=" + parentIDArray.toString() + "&instanceID=" + instanceIDArray.toString()
						+ "&objectType=" + objectTypeArray.toString() + "&stackOrder=" + stackOrderArray.toString()
						+ "&orientation=" + orientationArray.toString();
			this.saveDataOnServer(dataToSend, g_stackOrderAjaxPage);
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
				var stretchToFill = false;
				var stackOrder = PageManager.pages[PageManager.getCurrentPageID()].photos.length + 1;
				if (photoInstance)
				{
					stretchToFill = photoInstance.stretchToFill;
					stackOrder = AdminManager.getStackOrder(photoID, photoInstanceID, g_photoObjectType);
				}
				var mode = (AdminPhotoManager.isDraggedPhoto(divID) ? "add" : "update");
				var dataToSend = "mode=" + mode + "&pageID=" + pageID + "&ID=" + photoID + "&instanceID=" + photoInstanceID
					+ "&orientation=" + orientation + "&Xcoord=" + Xcoord + "&Ycoord=" + Ycoord
					+ "&width=" + width + "&height=" + height + "&stretchToFill=" + stretchToFill;
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
				var dataToSend = "mode=" + mode + "&ID=" + articleID + "&instanceID=" + articleInstanceID + "&pageID=" + pageID
					+ "&orientation=" + orientation + "&articleText=" + $(UIelement).html() + "&Xcoord=" + Xcoord
					+ "&Ycoord=" + Ycoord + "&width=" + width + "&height=" + height + "&BookID=" + g_bookID + "&LangID=" + g_defaultLangID;
				dataPageName = g_articleAjaxPage;
			}
			if (dataPageName)
				this.saveDataOnServer(dataToSend, dataPageName);
		}
	

		this.deleteObject = function(objectType, objectID, objectInstanceID, pageID)
		{
			var pageName;
			if (AdminManager.isPhotoObj(objectType))
				pageName = g_photoAjaxPage;
			else if (AdminManager.isArticleObj(objectType))
				pageName = g_articleAjaxPage;
			var data = "mode=delete&ID=" + objectID;
			if (typeof(objectInstanceID) != "undefined" && objectInstanceID)
				data += "&instanceID=" + objectInstanceID + "&pageID=" + pageID;
			this.saveDataOnServer(data, pageName);
		}
	
		this.saveDataOnServer = function(dataToSend, page)
		{
			Logger.log(page + ":&nbsp;" + dataToSend, "text", "to_server");
			$.ajax({
				type : "POST",
				url : page,
				data: dataToSend,
				dataType : "html", // data type to be returned
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
					alert(ErrString);
				}
			});
		}
	
		this.showDataFromServer = function(serverdata)
		{
			var data;
			var allDataSetsArray = serverdata.split(g_dataDelimeter);
			var mode, loggingIn;
			for (var z = 0; z < allDataSetsArray.length; z++)
			{
				data = allDataSetsArray[z];
				if (data.substring(0, 6) == "Error:")
					alert("Error is: " + data.substring(6));
				else if (data.substring(0, 9) == "Published")
					alert("Your book was published!");
				else if (data.substring(0, 8) == "Deleted:")
				{
					var IDarray = data.substring(8).split(",");
					if (IDarray[0].substring(0, 5) == "Photo")
					{
						if (IDarray[0] == "Photo")
						{
							var divID = AdminPhotoManager.getPhotoWrapperDivID(IDarray[1]);
							$('#' + divID).remove();
							Logger.log("Deleted Photo ID " + IDarray[1], "text", "from_server", "Delete photo");
						}
						else if (IDarray[0] == "PhotoInstance")
						{
							AdminPhotoManager.removePhotoInstance(IDarray[1], IDarray[2], IDarray[3]);
							var divID = AdminPhotoManager.getPhotoInstanceWrapperDivID(IDarray[1], IDarray[2]);
							$('#' + divID).remove();
							Logger.log("Deleted Photo instance ID " + IDarray[1] + "_" + IDarray[2], "text", "from_server", "Delete photo instance");
						}
					}
					else if (IDarray[0] == "Article" || IDarray[0] == "ArticleInstance")
					{
						AdminArticleManager.removeArticleInstance(IDarray[1], IDarray[2], IDarray[3]);
						var divID = AdminArticleManager.getArticleInstanceDivID(IDarray[1], IDarray[2]);
						$('#' + divID).remove();
					}
					g_objectClicked = null;
				}
				else if (data.substring(0, 5) == "Page:")
				{
					data = data.substring(5);
					var dataHash = {};
					var dataArray = data.split("&");
					var dataTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
						{
							mode = dataHash['mode'];
							loggingIn = (dataHash['loggingIn'] == "true");
							continue;
						}
						PageManager.addPage(dataHash["bookID"], dataHash['pageID'], dataHash['pageNum']);
					}
				}
				else if (data.substring(0, 6) == "Photo:")
				{
					data = data.substring(6);
					var dataHash = {};
					var dataArray = data.split("&");
					var dataTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
						{
							mode = dataHash['mode'];
							loggingIn = (dataHash['loggingIn'] == "true");
							continue;
						}
						AdminPhotoManager.addPhoto(dataHash['ID'], dataHash['photoURL'], dataHash['width'], dataHash['height'],
							dataHash['widthSmall'], dataHash['heightSmall']);
						addJQueryEvents("#" + AdminPhotoManager.getPhotoWrapperDivID(dataHash['ID']));
					}
				}
				else if (data.substring(0, 8) == "Article:")
				{
					data = data.substring(8);
					var dataHash = {};
					var dataArray = data.split("&");
					var dataTempArray = new Array();
					var textTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
						{
							mode = dataHash['mode'];
							loggingIn = (dataHash['loggingIn'] == "true");
							continue;
						}
						var re = new RegExp(g_textDelimeterReplaceAmperstand, 'g');
						dataHash['text'] = dataHash['text'].replace(re, "&");
						//dataHash['text'] = dataHash['text'].replace(/%26amp%3B/g,"&");
						//textTempArray = dataHash['text'].split(g_textDelimeterReplaceAmperstand);
						//dataHash['text'] = textTempArray.join("&");
						//alert(dataHash['text']); //.replace(/%26amp%3B/g,"&")
						if (dataHash['mode'] == "add")
							AdminArticleManager.removeTempTextBoxIfExists();
						AdminArticleManager.addUpdateArticle(dataHash['ID'], dataHash['title'],
							dataHash['author'], dataHash['text'], (dataHash['isShared'] == "1"));
						//AdminArticleManager.addUpdateArticleInstance(dataHash['ID'], dataHash['instanceID'],
						//	dataHash['pageID'], dataHash['orientation'], dataHash['Xcoord'], dataHash['Ycoord'], dataHash['width'], dataHash['height'],
						//	(mode.substring(0, 3) == "add"), false);
					}
				}
				else if (data.substring(0, 14) == "PhotoInstance:")
				{
					data = data.substring(14);
					var dataHash = {}, dataArray, dataTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					var isNewPhoto;
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
						{
							mode = dataHash['mode'];
							loggingIn = (dataHash['loggingIn'] == "true");
							isNewPhoto = (mode == "add")
							continue;
						}
						AdminPhotoManager.addUpdatePhotoInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['Xcoord'], dataHash['Ycoord'], dataHash['width'], dataHash['height'],
							dataHash['stretchToFill'], dataHash['orientation'], isNewPhoto, loggingIn);
						var photoInstanceWrapperDivID = AdminPhotoManager.getPhotoInstanceWrapperDivID(dataHash['ID'], dataHash['instanceID']);
						var photoInstanceDivID = AdminPhotoManager.getPhotoInstanceDivID(dataHash['ID'], dataHash['instanceID']);
						//StackOrderManager.setPhotoStackOrderInsideIpad(photoInstanceWrapperDivID, dataHash['photoID'], dataHash['photoInstanceID']);
						if (isNewPhoto)
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
				else if (data.substring(0, 16) == "ArticleInstance:")
				{
					data = data.substring(16);
					var dataHash = {}, dataArray, dataTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					var loggingIn;
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
						{
							mode = dataHash['mode'];
							loggingIn = (dataHash['loggingIn'] == "true");
							continue;
						}
						var isNewArticleInstance = (mode == "add");
						AdminArticleManager.addUpdateArticleInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['orientation'], dataHash['Xcoord'], dataHash['Ycoord'],
							dataHash['width'], dataHash['height'], isNewArticleInstance, loggingIn);
					}
				}
				else if (data.substring(0, 11) == "StackOrder:")
				{
					data = data.substring(11);
					var dataHash = {}, dataArray, dataTempArray = new Array();
					var allDataArray = data.split(g_itemDelimeter);
					for (var y = 0; y < allDataArray.length; y++)
					{
						data = allDataArray[y];
						dataArray = data.split("&");
						for (var x = 0; x < dataArray.length; x++)
						{
							dataTempArray = dataArray[x].split("=");
							dataHash[dataTempArray[0]] = dataTempArray[1];
						}
						if (y == 0)
							continue;
						StackOrderManager.updateStackOrder(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
							dataHash['objectType'], dataHash['orientation'], dataHash['stackOrder']);
					}
				}
			}
		}
	}