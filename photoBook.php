<?
	include("_sharedIncludes/dbconnect.php");
	include("_sharedIncludes/globals.php");
?>
<html>
<head>
	<title>Book</title>
	<script src="http://code.jquery.com/jquery-2.1.0.js"></script>
	<script>
<?
	$BookID = $_GET['bookID'];
	if ($BookID == "" || !is_numeric($BookID))
	{
		echo "var loaded = false;
				function onload(){document.write('Please specify a bookID in the URL<br />Example:book2.php?bookID=1');}";
	}
	else
	{
		echo "var loaded = true";
		$bookTitle = "";
		$bookAuthor = "";
		$query_str = "SELECT BookTitleTitle, BookTitleAuthor, BookDefaultLangID, BookDefaultPageID 
					FROM BookTitleLangs 
					INNER JOIN Books 
					ON BookTitleLangs.BookID = Books.BookID 
					AND Books.BookDefaultLangID = BookTitleLangs.BookLangID 
					WHERE BookTitleLangs.BookID = {$BookID}
						AND BookTitleIsDefault = 1;";
		$book_title_sql = $mysqli->query($query_str);
		$row = $book_title_sql->fetch_assoc();
		if ($row)
		{
			$BookTitle = $row['BookTitleTitle'];
			$BookAuthor = $row['BookTitleAuthor'];
			$BookDefaultLangID = $row['BookDefaultLangID'];
			$BookDefaultPageID = $row['BookDefaultPageID'];
		}
	}
?>

		var ClientCommunicator = new function()
		{
			this.getData = function(dataToSend)
			{
				$.ajax({
					type : "POST",
					url : "admin/loadAllData.php",
					data: JSON.stringify(dataToSend),
					dataType : "html", // data type to be returned
					contentType: "application/json",
					success: function(data) {
						//alert( data ); // shows whole dom
						ClientCommunicator.showDataFromServer( data );
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
						alert(ErrString);
						alert(exception);
					}
				});
			}
			
			this.showDataFromServer = function(serverdata)
			{
				alert(serverdata);
			}
		}
		
		$(document).ready(function()
		{
			if (loaded)
				ClientCommunicator.getData({loadPageData: 'true', pageID:'<?=$BookDefaultPageID?>', bookID: '<?=$BookID?>', bookLang: '<?=$BookDefaultLangID?>'});
			else
				onload();
		});
	</script>
</head>
<body>
<h1><?=$BookTitle?></h1>
<h4><i><?=$BookAuthor?></i></h4>
</body>
</html>
<? include("dbclose.php") ?>