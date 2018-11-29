<?php
	require('./config.php');
	
	$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	mysqli_set_charset($polaczenie, "utf8");
	
	$id = $_POST['id'];
	$file = mysqli_query($polaczenie, "SELECT * FROM files WHERE id = {$id} LIMIT 1;");
	$file = mysqli_fetch_array($file);
	
	$path = 'files' . DIRECTORY_SEPARATOR . $_COOKIE['username'] . DIRECTORY_SEPARATOR;
	$filename = $file['filename'];
	$filepath = $path . $filename;
	
	mysqli_close($polaczenie);
	
	if(file_exists($filepath)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		ob_clean();
        flush();
		readfile($filepath);
		exit;
	}
?>