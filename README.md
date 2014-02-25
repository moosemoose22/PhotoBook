PhotoBook
=========

**_Photo e-book creation software_**

This project's goal is to help people dsign and publish hosted photo e-books.

There are 2 products here: a product that shows an e-book over the web and a product that allows somebody to design an e-book.

*book2.php--*	The main page of the front end. Required URL values: bookID and pageNum.

*PhotoBook.sql--*	SQL of all tables used in PhotoBook

**Admin directory main files:**  
*book_admin.php--*	Main admin page  
*DataStructures.js--*	Data structures used by admin    
	Main data structures: PageManager, AdminManager, AdminArticleManager, AdminPhotoManager, StackOrderManager, ImageSourceManager, Logger  
*MouseEvents.js--*	Document DOM event functions such as MouseUp, MouseDown, onBlur, etc.  
	Main data structures: DocumentClickManager  
*Server.js--*			Main AJAX functionality to connect with server  
	Main data structures: Communicator  
*toolbar.inc--*		HTML for floating toolbar


**Admin directory login setup files:**  
*htaccess--*			Makes the default page in admin directory to login.php. Used if you type in /admin and no page is specified.  
	NOTE: You need to rename this file to .htaccess (with a preceding period) to use.  
*login.php--*			Main login page for admin.  
*checkLogin.php--*	Block access to anybody who isn't logged in. Include this file on every page.  
	NOTE: Maybe should be moved to an _includes directory in admin  
*chooseBook.php--*	Forwards a user who logged in to their default book. Puts the book ID into a session variable.


**Admin photo upload files (Accessed by hitting photo upload button on toolbar):**  
*uploadPix.php--*			HTML/JavaScript to upload new photos. Page automatically creates a thumbnail and a medium-sized version.  
*uploadPix_process.php--*	PHP page that handles uploading new photos  
*SimpleImage.php--*		Contains PHP class that resizes images on upload.


**Admin main server pages:**  
*saveArticleData.php--*	Saves all article/caption data from user.  
						Including:  
*						location of moved article/caption on a page  
*						width/height of article/caption on page (articles can be resized)

*savePhotoData.php--*		Saves all photo data from user, including  
						Including:  
*						location of moved article on a page  
*						width/height of article on page (articles can be resized)  
*						code to publish a book!!!!!!

*saveStackOrder.php--*	Save order of photos/articles/captions on page  
						Every page has to store the order of elements on a page.
						If 2 elements overlap, it'll know which to show on top.

*SimpleImage_class.php--*	Used by savePhotoData.php to make final resizing for publishing book!  
						When you publish a book, we pre-resize the images. This speeds up the book loading.
						This class does all the resizing!  


**_Thanks for taking a look!!!!!_**
