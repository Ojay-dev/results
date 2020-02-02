<?php include '../res/conn.php'; ?>
<?php
	if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
		header('Location: '.BASE_URL.'admin/admin_panel.php');
	}
?>
<!DOCTYPE html>
	<html>
		<head>
			<title>Login</title>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width initial-scale=1">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/bs.css">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/fa.css">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/style.css">
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/jquery.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/bs.js"></script>
		</head>
		<body>
			<div class="container login_admin" id="main_content">
				<form action="javascript:app.doLogin('admin')" id="admin_login" class="login_form">
					<div class="loading">
						<div class="ball"></div> <div class="ball"></div> <div
						class="ball"></div> <div class="ball"></div> <div class="ball"></div>
						<div class="ball"></div> <div class="ball"></div>
						<h2>Loading...</h2>
					</div>
					<div class="floating_img">
						<img class="floating_img" src="<?php echo BASE_URL; ?>res/images/default.png">
						<h1>Department</h1>
					</div>
					<p class="info">Please Login</p>
					<input class="login_input" type="text" name="username" placeholder="Username or Email" required/>
					<input class="login_input" type="password" name="password" placeholder="Password" required/>
					<input type="submit" class="login_submit" name="submit" value="login" required/>
					<p class="form_text">Forgot password? <a class="form_link" href="javascript:admin.reset()">Reset</a></p>
				</form>
			</div>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/script.js"></script>
		</body>
	</html>
