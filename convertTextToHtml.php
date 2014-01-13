<!DOCTYPE html>
<html>
<head>
<title>Convert text to HTML</title>
</head>
<body>
<?
	include("dbconnect.php");
	$articles_str = "SELECT * FROM BookArticleLangs;";
	$articles_sql = $mysqli->query($articles_str);
	$BookArticleLangID = 0;
	$articleText = "";
	while ($row = $articles_sql->fetch_assoc())
	{
		$BookArticleLangID = $row['BookArticleLangID'];
		echo $BookArticleLangID . "<br />";
		$articleText = $row['BookArticleLangText'];
		echo $articleText;
		continue;
		// Keep &, <, and > in this order first!!!!
		$articleText = str_replace(chr(38), "&amp;", $articleText); // &
		$articleText = str_replace(chr(60), "&lt;", $articleText); // <
		$articleText = str_replace(chr(62), "&gt;", $articleText); // >
		$articleText = str_replace("\r\n", "<br />", $articleText);
		$articleText = str_replace(chr(9), "&#09;", $articleText);
		//$articleText = str_replace(chr(10), "&#10;", $articleText);
		$articleText = str_replace(chr(11), "&#11;", $articleText);
		$articleText = str_replace(chr(12), "&#12;", $articleText);
		$articleText = str_replace(chr(34), "&quot;", $articleText); // "
		$articleText = str_replace(chr(39), "&#39;", $articleText); // '
		//$articleText = str_replace(chr(13), "&#13;", $articleText);
		$articleText = str_replace(chr(138), "&#352;", $articleText); // Š
		$articleText = str_replace(chr(140), "&#338;", $articleText); // Œ
		$articleText = str_replace(chr(145), "&#8216;", $articleText); // left single quote
		$articleText = str_replace(chr(146), "&#8217;", $articleText); // right single quote
		$articleText = str_replace(chr(147), "&#8220;", $articleText); // left double quote
		$articleText = str_replace(chr(148), "&#8221;", $articleText); // right double quote
		$articleText = str_replace(chr(150), "&ndash;", $articleText); // – en dash
		$articleText = str_replace(chr(151), "&mdash;", $articleText); // — em dash
		$articleText = str_replace(chr(152), "&tilde;", $articleText); // ˜
		$articleText = str_replace(chr(153), "&trade;", $articleText); // ™
		$articleText = str_replace(chr(154), "&#353;", $articleText); // š
		$articleText = str_replace(chr(156), "&#339;", $articleText); // œ
		$articleText = str_replace(chr(158), "&#382;", $articleText); // ž
		$articleText = str_replace(chr(159), "&#376;", $articleText); // Ÿ
		$articleText = str_replace(chr(162), "&cent;", $articleText); // ¢
		$articleText = str_replace(chr(163), "&pound;", $articleText); // £
		$articleText = str_replace(chr(169), "&copy;", $articleText); // ©
		$articleText = str_replace(chr(174), "&reg;", $articleText); // ®
		$articleText = str_replace(chr(237), "&iacute;", $articleText); // í
		$articleText = str_replace(chr(252), "&uuml;", $articleText); // ü
		// European characters
		for ($asciinum = 192; $asciinum <= 255; $asciinum++)
			$articleText = str_replace(chr($asciinum), "&#" . $asciinum . ";", $articleText);
		/*
		$articleText = str_replace(chr(32), "&#32;", $articleText); // space
		$articleText = str_replace(chr(402), "&#402;", $articleText); // ƒ
		*/
		$db_update_str = "UPDATE BookArticleLangs
							SET BookArticleLangText = '$articleText'
							WHERE BookArticleLangID = " . $BookArticleLangID . ";";
		//$mysqli->query($db_update_str);
		//echo $articleText;
		echo "<br /><br />";
	}
	$articles_sql->free();
	include("dbclose.php")
?>
</body>
</html>