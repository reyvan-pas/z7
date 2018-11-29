<?php
	require('./config.php');
	
	$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	mysqli_set_charset($polaczenie, "utf8");
	
	$id = $_POST['id'];
	$type = $_POST['type'];
	$path = 'files' . DIRECTORY_SEPARATOR . $_COOKIE['username'] . DIRECTORY_SEPARATOR;
	
	if($type == 'file') {
		$file = mysqli_query($polaczenie, "SELECT * FROM files WHERE id = {$id} LIMIT 1;");
		$file = mysqli_fetch_array($file);
		
		unlink($path . $file['filename']);
		mysqli_query($polaczenie, "DELETE FROM files WHERE id = {$id};");
	} else if($type == 'directory')	{
		$files = mysqli_query($polaczenie, "SELECT * FROM files WHERE catalog_id = {$id};");
		
		while($file = mysqli_fetch_array($files)) {
			unlink($path . $file['filename']);
			mysqli_query($polaczenie, "DELETE FROM files WHERE id = {$file['id']};");
		}
		
		mysqli_query($polaczenie, "DELETE FROM catalogs WHERE id = {$id};");
	}
	
	mysqli_close($polaczenie);
?>