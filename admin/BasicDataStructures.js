	function book(ID, title)
	{
		this.ID = ID;
		this.title = title;
	}
	
	function photo(ID, URL, photoWidth, photoHeight, photoWidthSmall, photoHeightSmall)
	{
		this.ID = ID;
		this.URL = URL;
		this.photoWidth = photoWidth;
		this.photoHeight = photoHeight;
		this.photoWidthSmall = photoWidthSmall;
		this.photoHeightSmall = photoHeightSmall;
		// have multiple copies ber book!
		this.isInBook = false;
		this.instances = {};
	}
	
	function article(ID)
	{
		this.ID = ID;
		this.title;
		this.text;
		this.isShared;
		this.instances = {};
	}
	
	function objectInstance(parentID, instanceID, type)
	{
		this.type = type;
		this.parentID = parentID;
		this.instanceID = instanceID;
		this.pageID;
		this.ipadOrientation;
		this.Xcoord;
		this.Ycoord;
		this.width;
		this.height;
		this.stackOrder;
		this.stretchToFill;
	}

	function page(bookID, pageID, pageNumber)
	{
		this.bookID = bookID;
		this.ID = pageID;
		this.pageNumber = pageNumber;
		this.reattachedPhotos = false;
		this.photos = {};
		this.articles = {};
		this.stackOrderArray = [];
	}
