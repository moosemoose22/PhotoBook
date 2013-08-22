<?	$mysqli = new mysqli("localhost", "orent", "sRULIIZCOOL", "orent_info_-_zion");

	if ($mysqli->connect_errno)
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;

	session_start();
?>