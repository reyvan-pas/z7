<?php
	$path = 'files' . DIRECTORY_SEPARATOR . $_COOKIE['username'] . DIRECTORY_SEPARATOR;
	$name = $_POST['name'];
	$max = 2 * 1024 * 1024;
	
	function uniqueFilename() {
		while($name = bin2hex(openssl_random_pseudo_bytes(44))) {
			if(!file_exists($path . $name)) {
				return $name;
			}
		}
	}

	if(!empty($_FILES)) {
		$tempFile = $_FILES['file']['tmp_name'];  
		$filename = uniqueFilename();
		$targetFile =  $path . $filename;
		move_uploaded_file($tempFile, $targetFile);
		
		if($_FILES['file']['size'] > $max) {
			header('Location: drive.php?error=too_large');
			die;
		} 
		
		require('./config.php');
	
		$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
		mysqli_set_charset($polaczenie, "utf8");
		
		$user = mysqli_query($polaczenie, "SELECT * FROM users WHERE username = '{$_COOKIE['username']}' LIMIT 1;");
		$user = mysqli_fetch_array($user);
		
		$secret = $_POST['secret'];
		$parent = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE secret = '{$secret}' LIMIT 1;");
		$parent = mysqli_fetch_array($parent);
		
		mysqli_query($polaczenie, "INSERT INTO files (id, user_id, catalog_id, name, filename, original_filename) VALUES (null, {$user['id']}, {$parent['id']}, '{$name}', '{$filename}', '{$_FILES['file']['name']}');");
	
		mysqli_close($polaczenie);
	}
	
	header('Location: drive.php');
	die;
?> 