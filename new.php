<?php
	require('./config.php');
	
	$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	mysqli_set_charset($polaczenie, "utf8");
	
	$secret = $_POST['secret'];
	$nextSecret = bin2hex(openssl_random_pseudo_bytes(44));
	
	$user = mysqli_query($polaczenie, "SELECT * FROM users WHERE username = '{$_COOKIE['username']}' LIMIT 1;");
	$user = mysqli_fetch_array($user);
	
	$parent = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE secret = '{$secret}' LIMIT 1;");
	$parent = mysqli_fetch_array($parent);
	
	mysqli_query($polaczenie, "INSERT INTO catalogs (id, user_id, catalog_id, name, secret) VALUES (null, {$user['id']}, {$parent['id']}, '{$_POST['name']}', '{$nextSecret}');");
	
	mysqli_close($polaczenie);
	
	header('Location: drive.php');
	die;
?>