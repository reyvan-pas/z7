<?php require('../header.php'); ?>
<?php require('../menu.php'); ?>

<?php
	if(isset($_POST) && count($_POST) > 0) {
		if(strlen($_POST['username']) <= 1 || strlen($_POST['password']) <= 1) {
			$alertType = 'warning';
			$alertMsg = 'Nazwa użytkownika musi być dłuższa niż 3 znaki, a hasło musi mieć minimum 8 znaków.';
		} else {
			require('./config.php');
		
			$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
			mysqli_set_charset($polaczenie, "utf8");
			$sprawdzenie = mysqli_query($polaczenie, "SELECT EXISTS(SELECT * FROM users WHERE username LIKE '{$_POST['username']}') as existing;");
			$sprawdzenie = mysqli_fetch_array($sprawdzenie);
			
			if($sprawdzenie['existing']) {
				$alertType = 'warning';
				$alertMsg = 'Istnieje użytkownik o podanej nazwie';
			} else {
				if($_POST['password'] == $_POST['rpassword']) {
					// generuję hasło z użyciem algorytmu BCRYPT
					$password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
					mysqli_query($polaczenie, "INSERT INTO users (id, username, password) VALUES (null, '{$_POST['username']}', '{$password}');");
					$userId = mysqli_insert_id($polaczenie);
					$secret = bin2hex(openssl_random_pseudo_bytes(44));
					mysqli_query($polaczenie, "INSERT INTO catalogs (id, user_id, name, secret) VALUES (null, '{$userId}', 'Katalog główny', '{$secret}');");
					$alertType = 'success';
					$alertMsg = 'Zarejestrowano :) Możesz przejść do logowania';
				} else {
					$alertType = 'warning';
					$alertMsg = 'Podane hasła nie są takie same';
				}
			}
			
			mysqli_close($polaczenie);
		}
	}
?>

<form method="POST">
	<div class="row">
		<div class="col-sm-6 mx-auto">
			<?php if(isset($alertMsg)): ?>
				<div class="alert alert-<?php echo $alertType; ?>" role="alert">
					<?php echo $alertMsg; ?>
				</div>
			<?php endif; ?>
		
			<div class="form-group">
				<label for="username">Nazwa użytkownika</label>
				<input type="text" class="form-control" id="username" name="username" placeholder="Nazwa użytkownika">
			</div>
			
			<div class="form-group">
				<label for="password">Hasło</label>
				<input type="password" class="form-control" id="password" name="password" placeholder="Hasło">
			</div>
			
			<div class="form-group">
				<label for="password">Powtórz hasło</label>
				<input type="password" class="form-control" id="rpassword" name="rpassword" placeholder="Powtórz hasło">
			</div>
			
			<div class="mt-2 mb-2 text-right">
				<a href="index.php">Logowanie</a>
			</div>
			
			<div class="mt-3">
				<button type="submit" class="btn btn-primary">Zarejestruj</button>
			</div>
		</div>
	</div>
</form>

<?php require('../footer.php'); ?>