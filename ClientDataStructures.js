//////////////////////////////////////////////
// Code that deals with talking to/from server
//////////////////////////////////////////////
		var ClientCommunicator = new function()
		{
			this.getData = function(dataToSend)
			{
				$.ajax({
					type : "POST",
					url : "admin/loadAllData.php",
					data: JSON.stringify(dataToSend),
					dataType : "json", // data type to be returned
					contentType: "application/json",
					success: function(data) {
						ClientCommunicator.showDataFromServer( data );
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
						//alert(ErrString);
						alert(exception);
						if (jqXHR)
							alert(jqXHR.responseText);
					}
				});
			}
			
			this.showDataFromServer = function(serverdata)
			{
				var dataHash, datasetArray;
				var allDataSetsObj = serverdata["allData"];
				var count, len;
				for (var datasetName in allDataSetsObj)
				{
					datasetArray = allDataSetsObj[datasetName];
					if (datasetName == "pages")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							PageManager.addPage(dataHash['pageID'], dataHash['pageNum']);
						}
						PageManager.updateAvailablePages();
					}
					else if (datasetName == "photos")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							ObjectManager.addPhoto(dataHash['ID'], dataHash['photoURL']);
						}
						// Note:
						// Even though the server currently returns width, height, widthSmall, and heightSmall,
						// they're only used in the admin.
						// Eventually they won't reach the client.
						// For now we just don't process them.
					}
					else if (datasetName == "articles")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							ObjectManager.addArticle(dataHash['ID'], dataHash['title'], dataHash['author'], dataHash['text']);
						}
					}
					else if (datasetName == "photoinstances")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							ObjectManager.processPhotoInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
								dataHash['orientation'], dataHash['Xcoord'], dataHash['Ycoord'], dataHash['width'],
								dataHash['height'], dataHash['URL'], dataHash['stretchToFill']);
						}
					}
					else if (datasetName == "articleinstances")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							ObjectManager.processArticleInstance(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
								dataHash['orientation'], dataHash['Xcoord'], dataHash['Ycoord'], dataHash['width'],
								dataHash['height']);
						}
					}
					else if (datasetName == "stackorder")
					{
						for (count = 0, len = datasetArray.length; count < len; count++)
						{
							dataHash = datasetArray[count];
							ObjectManager.processStackorder(dataHash['ID'], dataHash['instanceID'], dataHash['pageID'],
								dataHash['objectType'], dataHash['orientation'], dataHash['stackOrder']);
						}
					}
					else if (datasetName == "setcurrentpage")
						PageManager.setCurrentPage(allDataSetsObj[datasetName], true);
				}
				PageManager.loadPageObjects();
			}
		}

//////////////////////////////////////////////
// Code that deals with
// 1) storing which photos/articles/captions are located on which page
// 2) the current page and which pages exist in the book
// 3) Writing images/articles/captions to the page
//////////////////////////////////////////////
		var PageManager = new function()
		{
			this.pages = {};
			this.pageNumbers = [];
			this.imageRoot = g_imageRoot;
			this.currentPage;
			
			this.init = function()
			{
				var func, funcargs;
				for (var x in this)
				{
					if ((typeof this[x]) == "function" && x != "init")
					{
						//alert(x);
						func = this[x];
						funcargs = this[x].arguments;
						//this[x] = function(funcargs){func(funcargs);Logger.log(x, funcargs);};
						//this[x] = function(func, funcargs){func(funcargs);};
						this[x] = func;
					}
				}
			}
			
			this.sortPages = function()
			{
				var page;
				for (var pageID in this.pages)
				{
					page = this.pages[pageID];
					for (var x = 0, y = this.pageNumbers.length; x < y; x++)
					{
						if (page.pageNumber < this.pageNumbers[x].pageNumber)
						{
							this.pageNumbers.splice(x, 0, page);
							break;
						}
					}
					if (this.pageNumbers.length == x)
						this.pageNumbers.push(page);
				}
			}

			this.addPage = function(pageID, pageNumber)
			{
				if (!pageID)
					return;
				var page = this.getPage(pageID);
				if (pageNumber)
				{
					pageNumber = parseInt(pageNumber);
					page.pageNumber = pageNumber;
				}
			}
			this.getPage = function(pageID)
			{
				if (!this.pages[pageID])
					this.pages[pageID] = new page(g_bookID, pageID);
				return this.pages[pageID];
			}
			this.addObject = function(ID, instanceID, pageID, objectType)
			{
				var page = this.getPage(pageID);
				var objectHash;
				if (objectType == g_photoObjectType)
					objectHash = page.photos;
				else if (objectType == g_articleObjectType)
					objectHash = page.articles;
				var objectHashIndex = ObjectManager.getDivID(ID, instanceID, objectType);
				objectHash[objectHashIndex] = ObjectManager.getObjectInstance(ID, instanceID, objectType);
			}
			this.setCurrentPage = function(pageID, loadingPage)
			{
				if (this.currentPage)
					this.showHideLoadPage(this.currentPage, loadingPage, false, "horizontal");
				this.currentPage = parseInt(pageID);
				this.showHideLoadPage(this.currentPage, loadingPage, true, "horizontal");
			}
			this.loadPageObjects = function()
			{
				for (var pageID in this.pages)
					this.showHideLoadPage(pageID, true, false, "");
			}
			this.showHideLoadPage = function(pageID, loadingPage, showOrHide, orientation)
			{
				if (!pageID)
					return;
				var page = this.pages[pageID];
				if (!page)
					return;
				var photoObj, articleObj, photoInstanceObj, articleInstanceObj;
				for (var pagePhotoID in page.photos)
				{
					photoInstanceObj = page.photos[pagePhotoID];
					if (loadingPage)
					{
						$("body").append("<img style=\"display:none\" style=\"position: absolute\" id=\"" + pagePhotoID + "\" src='" +
								this.imageRoot + photoInstanceObj.URL + "' />")
							.css("z-index", photoInstanceObj.stackOrder)
							.offset({top: photoInstanceObj.Ycoord, left: photoInstanceObj.Xcoord});
					}
					if (showOrHide && photoInstanceObj.orientation == orientation)
					{
						// For some reason onPageLoad, Safari (and maybe other browsers) won't show this image
						// unless we take it out of this function and show it asynchronously
						if (loadingPage)
							setTimeout(function(){$("#" + pagePhotoID).show();}, 100);
						else
							$("#" + pagePhotoID).show();
					}
					else
						$("#" + pagePhotoID).hide();
				}
				for (var pageArticleID in page.articles)
				{
					articleInstanceObj = page.articles[pageArticleID];
					articleObj = ObjectManager.articles[articleInstanceObj.parentID];
					if (loadingPage)
					{
						$("body").append("<div style=\"display:none\" id=\"" + pageArticleID + "\">" +
								"<h4>" + articleObj.title + "</h4>" + articleObj.text + "</div>");
							$("#" + pageArticleID).css({
								position: 'absolute',
								width: articleInstanceObj.width,
								height: articleInstanceObj.height,
								"overflow-y": "auto",
								"z-index": articleInstanceObj.stackOrder})
							.offset({top: articleInstanceObj.Ycoord, left: articleInstanceObj.Xcoord});
					}
					if (showOrHide && articleInstanceObj.orientation == orientation)
						$("#" + pageArticleID).show();
					else
						$("#" + pageArticleID).hide();
				}
			}
			this.updateAvailablePages = function()
			{
				// Sort pages numerically
				this.sortPages();
				var selectElement = $("#selectPage");
				var newOptionText;
				var lastPageNum = 0;
				for (var x = 0, y = this.pageNumbers.length; x < y; x++)
				{
					if (this.pageNumbers[x].pageNumber > lastPageNum)
					{
						newOptionText = "<option value=\"" +  this.pageNumbers[x].ID + "\">Page " + this.pageNumbers[x].pageNumber + "</option>";
						$("#selectPage").append(newOptionText);
						lastPageNum = this.pageNumbers[x].pageNumber;
					}
				}
				// If pages have ben removed, remove them from drop-down
				while (lastPageNum > this.pageNumbers[x - 1])
				{
					$("#selectPage option[value='" + lastPageNum + "']").remove();
					lastPageNum--;
				}
			}
		}
		
//////////////////////////////////////////////
// Code that deals with
// 1) storing photo/article/caption data
// 2) storing the order of how elements re stacked on the page
//////////////////////////////////////////////
		var ObjectManager = new function()
		{
			this.photos = {};
			this.articles = {};
			this.getDivID = function(ID, instanceID, objectType)
			{
				return (objectType + "_" + ID + "_" + instanceID);
			}
			this.addPhoto = function(ID, URL)
			{
				if (!this.photos[ID])
					this.photos[ID] = new photo(ID);
				this.photos[ID].URL = URL;
			}
			this.addArticle = function(ID, title, author, text)
			{
				if (!this.articles[ID])
					this.articles[ID] = new article(ID);
				this.articles[ID].title = title;
				this.articles[ID].author = author;
				this.articles[ID].text = text;
			}
			this.getObjectInstance = function(ID, instanceID, objectType)
			{
				if (objectType == g_photoObjectType)
				{
					if (!this.photos[ID])
						this.photos[ID] = new photo(ID);
					if (!this.photos[ID].instances[instanceID])
						this.photos[ID].instances[instanceID] = new objectInstance(ID, instanceID, g_photoObjectType);
					return this.photos[ID].instances[instanceID];
				}
				else if (objectType == g_articleObjectType)
				{
					if (!this.articles[ID])
						this.articles[ID] = new article(ID);
					if (!this.articles[ID].instances[instanceID])
						this.articles[ID].instances[instanceID] = new objectInstance(ID, instanceID, g_articleObjectType);
					return this.articles[ID].instances[instanceID];
				}
			}
			this.processPhotoInstance = function(ID, instanceID, pageID, orientation, Xcoord, Ycoord, width, height, URL, stretchToFill)
			{
				PageManager.addObject(ID, instanceID, pageID, g_photoObjectType);
				var photoInstance = this.getObjectInstance(ID, instanceID, g_photoObjectType);
				photoInstance.pageID = pageID;
				photoInstance.orientation = orientation;
				photoInstance.Xcoord = Xcoord;
				photoInstance.Ycoord = Ycoord;
				photoInstance.width = width;
				photoInstance.height = height;
				photoInstance.URL = URL;
				photoInstance.stretchToFill = stretchToFill;
			}
			this.processArticleInstance = function(ID, instanceID, pageID, orientation, Xcoord, Ycoord, width, height, stretchToFill)
			{
				PageManager.addObject(ID, instanceID, pageID, g_articleObjectType);
				var articleInstance = this.getObjectInstance(ID, instanceID, g_articleObjectType);
				articleInstance.pageID = pageID;
				articleInstance.orientation = orientation;
				articleInstance.Xcoord = Xcoord;
				articleInstance.Ycoord = Ycoord;
				articleInstance.width = width;
				articleInstance.height = height;
				articleInstance.stretchToFill = stretchToFill;
			}
			this.processStackorder = function(ID, instanceID, pageID, objectType, orientation, stackOrder)
			{
				var obj = this.getObjectInstance(ID, instanceID, objectType);
				obj.stackOrder = stackOrder;
			}
		};
		
		var Logger = new function()
		{
			this.log = function()
			{
				alert(arguments[0]);
			}
		};
