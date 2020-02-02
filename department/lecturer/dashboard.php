<?php require '../res/conn.php'; ?>
<?php 
	if(isset($_SESSION['lecturer_id']) && $_SESSION['lecturer_id']){
		$user = mysqli_real_escape_string($con, $_SESSION['lecturer_id']);
		$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lecturers WHERE id='$user'"));
		$name = $data['name'];
		$email = $data['email'];
		$status = $data['status'];
	}else{
		header('Location: '.BASE_URL.'lecturer');
	}
	if(isset($_GET['logout']) && $_GET['logout']){
		session_unset();
		header('Location: dashboard.php');
	}
?>
<!DOCTYPE html>
	<html>
		<head>
			<title>Admin</title>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width initial-scale=1">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/bs.css">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/fa.css">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/style.css">
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/ice.css">
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/jquery.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/bs.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/chart.min.js"></script>
			<script type="text/javascript">
			</script>
		</head>
		<body id="dash">
			<?php
				if($name = '0' || $email= '0' || is_numeric($status)):
			?>
				<div class="installmodal">
				<div class="loader_main">
				<div class="loading"><div class="ball"></div> <div class="ball"></div><div class="ball"></div> <div class="ball"></div><div class="ball"></div><div class="ball"></div> <div class="ball"></div></div>
				</div>
					<div class="m">
						<div class="middleman container effect7">
							<div id="main_container">
								<p class="title welcome">Hi there, welcome!</p>
								<p class="title titles center">You're here because there are some basic things we need to know before you can start using this app</p>
							<a class="continue_btn btn btn-success pull-right" href="javascript:start()"><i class="fa fa-check" style="font-size: 1.2em;"></i> OK</a>
							</div>
						</div>
					</div>
				</div>
				<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/ice.js"></script>
				<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/lecturerinstall.js"></script>
			<?php
				exit();
				endif;
			?>
			<header>
				<div class="container">
					<div class="identity">
						<img class="logo" src="<?php echo BASE_URL; ?>res/images/default.png">
						<h1 class="title">Department</h1>
						<a href="javascript:side_bar()" class="sidebar_toggle"><i class="fa fa-bars"></i></a>
					</div>
				</div>
			</header>
			<div class="side_bar">
				<div class="container">
					<a href="javascript:side_bar()" class="close_btn">&times;</a>
					<div class="intro">
						<div class="prf_img" style="background: url('<?php echo BASE_URL; ?>res/images/default.png'); background-size: contain; background-position: 0% 50%; background-repeat: no-repeat;"></div>
						<h5>Howdy, <?php echo $data['name']; ?>!</h5>
					</div>
					<ul>
						<li class="active"><a href="javascript:dashboard()"><i class="fa fa-home"></i> Dashboard</a></li>
						<li><a href="javascript:score_sheet()"><i class="fa fa-table"></i> Score Sheet</a></li>
						<li><a href="javascript:notification();"><i class="fa fa-bell"></i><sub class="notification_count" id="notification_count"></sub> Notifications</a></li>
						<li><a href="#"><i class="fa fa-envelope"></i><sub class="notification_count" id="msg_count"></sub> Messages</a></li>
						<li><a href="?logout=1"><i class="fa fa-sign-out"></i> Logout</a></li>
					</ul>
				</div>
			</div>
			<div class="loader_main">
			<div class="loading"><div class="ball"></div> <div class="ball"></div><div class="ball"></div> <div class="ball"></div><div class="ball"></div><div class="ball"></div> <div class="ball"></div></div>
			</div>
			<div id="main_content" class="container">
				
			</div>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/ice.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/lecturer.js"></script>
		</body>
	</html>