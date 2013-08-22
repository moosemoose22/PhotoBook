<?php
	include("dbconnect.php");
	include("checkLogin.php");
?>
<html>
<head>
	<title>Upload pix</title>
	<script>
		var g_filesUpload, g_dropArea, g_fileList, g_photoMap;
		var g_imagesUploadArray = new Array();

		function trimString(str)
		{
			str = str.replace(/^\s+/, '');
			for (var i = str.length - 1; i >= 0; i--)
			{
				if (/\S/.test(str.charAt(i)))
				{
					str = str.substring(0, i + 1);
					break;
				}
			}
			return str;
		}
		
		function getExtension(str)
		{
			var regex = /(?:\.([^.]+))?$/;
			return regex.exec(str)[1];
		}

		function uploadFile (file)
		{
			var span = document.createElement("span"),
				div = document.createElement("div"),
				img,
				progressBarContainer = document.createElement("div"),
				progressBar = document.createElement("div"),
				reader,
				xhr,
				fileInfo;
				
			span.appendChild(div);
			
			progressBarContainer.className = "progress-bar-container";
			progressBar.className = "progress-bar";
			progressBarContainer.appendChild(progressBar);
			span.appendChild(progressBarContainer);
			
			/*
				If the file is an image and the web browser supports FileReader,
				present a preview in the file list
			*/
			///////////////////////////////////////////////////////
			// NOTE: Commenting out. We only want to show uploaded files
			/*
			if (typeof FileReader !== "undefined" && (/image/i).test(file.type))
			{
				img = document.createElement("img");
				span.appendChild(img);
				reader = new FileReader();
				reader.onload = (function (theImg) {
					return function (evt) {
						theImg.src = evt.target.result;
					};
				}(img));
				reader.readAsDataURL(file);
			}
			*/
			
			// Uploading - for Firefox, Google Chrome and Safari
			xhr = new XMLHttpRequest();
			
			// Update progress bar
			xhr.upload.addEventListener("progress", function (evt)
			{
				if (evt.lengthComputable)
					progressBar.style.width = (evt.loaded / evt.total) * 100 + "%";
				else
					; // No data to calculate on
			}, false);
			
			// File uploaded
			xhr.addEventListener("load", function ()
			{
				progressBarContainer.className += " uploaded";
				//progressBar.innerHTML = "Uploaded!";
			}, false);
			
			xhr.addEventListener("readystatechange", function()
			{
				if (xhr.readyState==4 && xhr.status==200)
				{
					if (xhr.responseText == "Success!")
					{
						var elem = document.getElementById("no-items");
						if (elem)
							elem.parentNode.removeChild(elem);
						var img = document.createElement("img");
						var uploadedImage = g_imagesUploadArray.shift();
						var imageExtension = getExtension(uploadedImage);
						var newImage = uploadedImage.replace("." + imageExtension, "_small." + imageExtension);
						img.src = "/images/book/<?=$_SESSION["BookLoginUsername"]?>/" + newImage;
						document.getElementById("images").appendChild(img);
					}
					else
						alert(xhr.responseText);
				}
			}, false);
/*
			xhr.onreadystatechange=function()
			{
				if (xhr.readyState==4 && xhr.status==200)
					alert(xhr.responseText);
			}
*/
			xhr.open("post", "/book/uploadPix_process.php", true);
			
			// Set appropriate headers
			xhr.setRequestHeader("Content-Type", "multipart/form-data");
			xhr.setRequestHeader("X-File-Name", file.name);
			xhr.setRequestHeader("X-File-Size", file.size);
			xhr.setRequestHeader("X-File-Type", file.type);
	
			// Send the file (doh)
			xhr.send(file);
			
			g_imagesUploadArray.push(file.name);
			
			// Present file info and append it to the list of files
			fileInfo = "<div><strong>Name:</strong> " + file.name + "</div>";
//			fileInfo += "<div><strong>Size:</strong> " + parseInt(file.size / 1024, 10) + " kb</div>";
//			fileInfo += "<div><strong>Type:</strong> " + file.type + "</div>";
			div.innerHTML = fileInfo;

			g_fileList.appendChild(span);
		}
		
		function traverseFiles (files)
		{
			if (typeof files !== "undefined")
			{
				for (var i=0, l=files.length; i<l; i++)
					uploadFile(files[i]);
			}
			else
				fileList.innerHTML = "No support for the File API in this web browser";
		}
		
		function init()
		{
			g_filesUpload = document.getElementById("files-upload"),
			g_dropArea = document.getElementById("drop-area"),
			g_fileList = document.getElementById("file-list");
		
			g_filesUpload.addEventListener("change", function () {
				traverseFiles(this.files);
			}, false);
			
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
			
			g_photoMap = {};
<?
	$pix_str = "SELECT BookPhotoID, BookPhotoURL
				FROM BookPhotos
				WHERE BookLoginUsername = '" . $_SESSION["BookLoginUsername"] . "';";
	$images_sql = $mysqli->query($pix_str);
	$hasResults = false;
	while ($row = $images_sql->fetch_assoc())
	{
		$hasResults = true;
		echo "g_photoMap['" . $row['BookPhotoID'] . "'] = \"" . $row['BookPhotoURL'] . "\";\n";
	}
	$images_sql->free();
//	if (!$hasResults)
//		echo "<li class=\"no-items\" id=\"no-items\">(no images)</li>";
?>
			addListeners();
		}

		function addListeners()
		{
			var img, imgSrc;
			for (var photoID in g_photoMap)
			{
				img = document.createElement("img");
				img.id = photoID;
				img.draggable = true;
				imgSrc = g_photoMap[photoID];
				var imageExtension = getExtension(imgSrc);
				var newImage = imgSrc.replace("." + imageExtension, "_small." + imageExtension);
				img.src = "/images/book/zion/" + newImage;
				document.getElementById("file-list").appendChild(img);
				document.getElementById(photoID).addEventListener('mousedown', mouseDown, false);
			}
			//document.getElementById("file-list").addEventListener('mousedown', mouseDown, false);
			window.addEventListener('mouseup', mouseUp, false);
		}

		function mouseUp(e)
		{
			alert(e);
			window.removeEventListener('mousemove', divMove, true);
		}

		function mouseDown(e)
		{
			window.addEventListener('mousemove', divMove, true);
		}

		function divMove(e)
		{
			var elementObj = e.target ? e.target : e.toElement;
			if (!elementObj)
				return;
			var img = document.getElementById(elementObj.id);
			if (img)
			{
				img.style.position = 'absolute';
				img.style.top += (e.clientY - img.style.top);
				img.offsetLeft += (e.clientX - img.offsetLeft);
			}
			//alert(img.style.left + "     " + img.offsetLeft);
		}		
	</script>
</head>
<body onload="init()">
<form action="uploadPix_process.php" method="post" enctype="multipart/form-data">
	<h3>Choose file(s)</h3>
	<table><tr>
	<td style="vertical-align: top">
		<p>
			<input id="files-upload" type="file" multiple="true" />
		</p>
	</td>
	<td style="vertical-align: top">
		<p id="drop-area" style="border: 1px solid black; height: 100px; width: 400px">
			<span class="drop-instructions">Or drag and drop files here</span>
		</p>
	</td>
	</tr></table>
	
	<span id="file-list">
	</span>
</form>

<div id="images"></div>

</body>
</html>