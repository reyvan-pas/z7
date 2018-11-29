<?php
	require('./config.php');
		
	$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	mysqli_set_charset($polaczenie, "utf8");
					
	// jeżeli użytkownik jest zalogowany przekierowuję od razu do aplikacji
	if(isset($_COOKIE['username'])) {
		$user = mysqli_query($polaczenie, "SELECT * FROM users WHERE username LIKE '{$_COOKIE['username']}';");
		$user = mysqli_fetch_array($user);
		
		mysqli_close($polaczenie);
		
		header("Location: drive.php");
		die();
	}
?>

<?php require('../header.php'); ?>
<?php require('../menu.php'); ?>

<?php
	if(isset($_POST) && count($_POST) > 0) {
		if(strlen($_POST['username']) <= 3) {
			$alertType = 'warning';
			$alertMsg = 'Nazwa użytkownika musi być dłuższa niż 3 znaki.';
		} else {
			// sprawdzam czy użytkownik exists i weryfikuję hasło
			$user = mysqli_query($polaczenie, "SELECT * FROM users WHERE username LIKE '{$_POST['username']}';");
			$user = mysqli_fetch_array($user);
			$logged = $user && password_verify($_POST['password'], $user['password']);
			$login = mysqli_query($polaczenie, "SELECT count(*) as count from logs where status = 0 AND user_id = {$user['id']} AND created_at > date_sub(now(), interval 5 minute);");
			$login = mysqli_fetch_array($login);
			
			if($login['count'] >= 3) {
				$lastLogin = mysqli_query($polaczenie, "SELECT status from logs where user_id = {$user['id']} order by id desc LIMIT 1;");
				$lastLogin = mysqli_fetch_array($lastLogin);
				
				if(!$lastLogin['status']) {
					echo '<div class="alert alert-danger" role="alert">';
					echo 'Przekroczono maksymalną, dopuszczalną liczbę błędnych logowań! Logowanie chwilowo zablokowane!';
					echo '</div>';
					die;
				}
			}
			
			// zapisujemy logowanie
			$ip = $_SERVER["REMOTE_ADDR"];
			$browser = $_SERVER['HTTP_USER_AGENT'];
			mysqli_query($polaczenie, "INSERT INTO logs (id, user_id, browser, ip, status) VALUES(null, {$user['id']}, '{$browser}', '{$ip}', '{$logged}');");
			
			if($logged) {
				// ustawiam ciasteczko logowania
				setcookie('username', $_POST['username'], time() + 60 * 60 * 24 * 7);

				mysqli_close($polaczenie);
				
				// odświeżam stronę
				header('Location: index.php');
				die;
			} else {
				$alertType = 'warning';
				$alertMsg = 'Coś źle wpisałeś - jesteś pewien, że taki masz login i hasło?';
			}
		}
		
		mysqli_close($polaczenie);
	}
?>

<h2 class="mt-3 mb-4 mx-auto">Reyvan Drive</h2>

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
			
			<div class="mt-2 mb-2 text-right">
				<a href="register.php">Nie posiadasz konta?</a>
			</div>
			
			<div class="mt-3">
				<button type="submit" class="btn btn-primary">Zaloguj się</button>
			</div>
		</div>
	</div>
</form>

<?php require('../footer.php'); ?>