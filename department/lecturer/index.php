<?php require '../res/conn.php'; ?>
<?php
	if(isset($_SESSION['lecturer_id']) && $_SESSION['lecturer_id']){
		header('Location: '.BASE_URL.'lecturer/dashboard.php');
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
			<script typt="text/javascript">
				const matric = '';
			</script>
		</head>
		<body>
			<div class="container login_admin" id="main_content">
				<form  autocomplete="off" action="javascript:doLogin()" id="admin_login" class="login_form">
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
					<input class="login_input" type="text" name="username" placeholder="Username/Email" required/>
					<input class="login_input" type="password" name="password" placeholder="Password" required/>
					<input type="submit" class="login_submit" name="submit" value="login" required/>
					<p class="form_text">Forgot password? <a class="form_link" href="javascript:reset()">Reset</a></p>
				</form>
			</div>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/lecturer.js"></script>
		</body>
	</html>