<?php 
	if(!isset($_COOKIE['username'])) {
		header("Location: ./index.php"); die();
	}
	
	require('../header.php');
?>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

<?php
	require('../menu.php');
	require('./config.php');
	
	function ip_details($ip) {
		$json = file_get_contents ("http://ipinfo.io/{$ip}/geo");
		$details = json_decode ($json);
		return $details;
	}
	
	// ścieżka do plików
	$path = 'files' . DIRECTORY_SEPARATOR . $_COOKIE['username'] . DIRECTORY_SEPARATOR;
		
	// tworzymy folder rekursywnie	
	if(!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	
	$polaczenie = mysqli_connect($db_host, $db_username, $db_password, $db_name);
	mysqli_set_charset($polaczenie, "utf8");
	
	$user = mysqli_query($polaczenie, "SELECT * FROM users WHERE username = '{$_COOKIE['username']}' LIMIT 1;");
	$user = mysqli_fetch_array($user);
	
	if(isset($_GET['directory'])) {
		$directory = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE secret = '{$_GET['directory']}' LIMIT 1;");
	} else {
		$directory = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE user_id = {$user['id']} AND catalog_id IS NULL;");
	}
	
	$directory = mysqli_fetch_array($directory);
	if($directory['catalog_id']) {
		$parent = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE id = '{$directory['catalog_id']}' LIMIT 1;");
		$parent = mysqli_fetch_array($parent);
	}
	
	$files = mysqli_query($polaczenie, "SELECT * FROM files WHERE user_id = {$user['id']} AND catalog_id = {$directory['id']}");
	$directories = mysqli_query($polaczenie, "SELECT * FROM catalogs WHERE user_id = {$user['id']} AND catalog_id = {$directory['id']}");
	
	$wrongLogin = mysqli_query($polaczenie, "SELECT * FROM logs WHERE user_id = {$user['id']} AND status = 0 ORDER by created_at DESC LIMIT 1");

	if(mysqli_num_rows($wrongLogin) > 0) {
		$wrongLoginRow = mysqli_fetch_array($wrongLogin);
		$details = ip_details($wrongLoginRow['ip']);
	}
	
	mysqli_close($polaczenie);
	
	if(isset($_GET['error'])) {
		if($_GET['error'] == 'too_large') {
			$alertType = 'warning';
			$alertMsg = 'Plik musi być mniejszy niż 2MB!';
		}
	}
?>

<div class="row mb-3">
	<div class="col-sm-6 mr-auto">
		<button 
			type="button" 
			class="btn btn-primary"
			id="uploadButton"
		>	
			Wgraj plik
		</button>
		
		<button 
			type="button" 
			class="btn btn-primary mr-3"
			id="newButton"
		>
			Nowy folder
		</button>
	</div>
	
	<div class="col-sm-3 ml-auto">
		<a href="./logout.php" class="btn btn-primary">Wyloguj!</a>
	</div>
</div>

<div id="upload" class="row mt-3 mb-3" style="display: none;">
	<div class="col-sm-12">
		<form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="secret" value="<?php echo $directory['secret']; ?>" />
			
			<div class="form-group">
				<label>Nazwa pliku</label>
				<input type="text" class="form-control" id="name" name="name" />
			</div>
			
			<div class="form-group">
				<label>Plik</label>
				<input type="file" class="form-control-file" id="file" name="file" />
			</div>
			
			<div class="row">
				<div class="col-sm-12 text-right">
					<button type="submit" class="btn btn-primary" id="uploadSubmit">Zapisz</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div id="new" class="row mt-3 mb-3" style="display: none;">
	<div class="col-sm-12">
		<form id="newForm" action="new.php" method="POST">
			<input type="hidden" name="secret" value="<?php echo $directory['secret']; ?>" />
			
			<div class="form-group">
				<label>Nazwa katalogu</label>
				<input type="text" class="form-control" required="required" id="name" name="name" />
			</div>
			
			<div class="row">
				<div class="col-sm-12 text-right">
					<button type="submit" class="btn btn-primary" id="newSubmit">Zapisz</button>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if(isset($alertMsg)): ?>
	<div class="alert alert-<?php echo $alertType; ?>" role="alert">
		<?php echo $alertMsg; ?>
	</div>
<?php endif; ?>

<?php if(isset($wrongLoginRow)): ?>
	<div class="row">
		<div class="col-sm-12">
			<div class="alert alert-danger" role="alert">
				<h4>Wykryto błędne próby logowania na Twoje konto!</h4>
				Ostatnia z nich miała miejsce <?php echo $wrongLoginRow['created_at']; ?> i wykonana była 
				z następującego adresu IP: <?php echo $wrongLoginRow['ip']; ?>  
				(<?php echo $details -> country; ?>, <?php echo $details -> region; ?>, 
				<?php echo $details -> city; ?>, <?php echo $details -> loc; ?>)
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="row">
	<?php if(isset($_GET['directory']) && isset($parent)): ?>
		<div class="col mb-3 text-center">
			<img class="change-folder" src="./images/folder.png" alt="Folder nadrzędny" data-secret="<?php echo $parent['secret']; ?>" /><br />
			<span>..</span>
		</div>
	<?php endif; ?>
	
	<?php if(mysqli_num_rows($files) == 0 && mysqli_num_rows($directories) == 0): ?>
		<div class="col-sm-12 mb-3">
			<div class="row">
				<div class="col-sm-12 text-center">
					<h2>BRAK PLIKÓW I FOLDERÓW</h2>
				</div>
			</div>
		</div>
	<?php endif; ?>
		
	<?php while($directoryS = mysqli_fetch_array($directories)): ?>
		<div class="col mb-3 text-center">
			<img class="change-folder" src="./images/file-in-folder.png" data-secret="<?php echo $directoryS['secret']; ?>" alt="Folder" /><br />
			<span><?php echo $directoryS['name']; ?></span><br />
			<i data-id="<?php echo $directoryS['id']; ?>" data-type="directory" class="fas fa-trash"></i>
		</div>
	<?php endwhile; ?>	
		
	<?php while($file = mysqli_fetch_array($files)): ?>
		<div class="col mb-3 text-center">
			<img src="./images/file.png" alt="Plik" /><br />
			<span><?php echo $file['name']; ?></span><br />
			<span>(<?php echo $file['original_filename']; ?>)</span><br />
			<i data-id="<?php echo $file['id']; ?>" class="fas fa-download"></i>
			<i data-id="<?php echo $file['id']; ?>" data-type="file" class="fas fa-trash"></i>
		</div>
	<?php endwhile; ?>
</div>

<?php require('../footer.php'); ?>

<script>
	$('#uploadButton').on('click', function() {
		if($('#upload').css('display') == 'none') {
			$('#upload').css('display', 'initial');
		} else {
			$('#upload').css('display', 'none');
		}
		$('#new').css('display', 'none');
	});
	
	$('#newButton').on('click', function() {
		if($('#new').css('display') == 'none') {
			$('#new').css('display', 'initial');
		} else {
			$('#new').css('display', 'none');
		}
		
		$('#upload').css('display', 'none');
	});
	
	$('.fa-download').on('click', function() {
		event.preventDefault();
		var id = $(this).attr('data-id');
				
		$('<form method="POST"><input type="hidden" name="id" value="' + id + '" /></form>')
			.attr('action', 'download.php')
			.appendTo('body').submit().remove();
	});
	
	$('.change-folder').on('click', function() {
		event.preventDefault();
		var secret = $(this).attr('data-secret');
		
		window.location.replace('drive.php?directory=' + secret);
	})
	
	$('.fa-trash').on('click', function() {
		event.preventDefault();
		var id = $(this).attr('data-id');
		var type = $(this).attr('data-type');

		$.post( 'delete.php', { 
			id: id,
			type: type
		})
		.done(function( data ) {
			location.reload();
		})
		.fail(function( data ) {
			console.log( data );
		});
	});
</script>