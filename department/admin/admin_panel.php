<?php require '../res/conn.php'; ?>
<?php 
	if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
		$user = mysqli_real_escape_string($con, $_SESSION['user_id']);
		$data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM admin WHERE id='$user'"));
		$image = ($data['image'])? $data['image']:'default.png';
	}else{
		header('Location: '.BASE_URL.'admin');
		exit();
	}
	if(isset($_GET['logout']) && $_GET['logout']){
		session_unset();
		header('Location: admin_panel.php');
		exit();
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
			<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>res/css/croppie.css">
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/jquery.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/bs.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/chart.min.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/croppie.min.js"></script>
		</head>
		<body id="dash">
			<?php if(!tableEmpty(['departments','levels']) || mysqli_fetch_assoc(mysqli_query($con, "SELECT current_sesh FROM admin"))['current_sesh'] == ''): ?>
				<div class="installmodal">
				<div class="loader_main">
				<div class="loading"><div class="ball"></div> <div class="ball"></div><div class="ball"></div> <div class="ball"></div><div class="ball"></div><div class="ball"></div> <div class="ball"></div></div>
				</div>
					<div class="m">
						<div class="middleman container effect7">
							<div id="main_container">
								<p class="title welcome">Hi there, before you go on to start using this app, there are just a few things we will need to know.</p>
							<a class="continue_btn btn btn-success pull-right" href="javascript:install.start"><i class="fa fa-check" style="font-size: 1.2em;"></i> OK</a>
							</div>
						</div>
					</div>
				</div>
				<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/ice.js"></script>
				<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/install.js"></script>	
			<?php  exit(); endif; ?>
			<!--
			-Image Upload Modal	
			-->
			<div id="imageUploadModal" class="modal">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header clearfix">
							<h4 class="modal-title">Crop and upload</h4>
							<a data-dismiss="modal" href="javascript:void(0)" style="color:red;font-size:1.5em;float:right;text-decoration:none;" class="pull-right">&times;</a>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-8 text-center">
									<div id="image_selected"></div>
								</div>
								<div class="col-md-4" style="padding-top: 30px">
									<a class="btn btn-success crop_image" href="javascript:image_upload()">Crop and Upload</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<header>
				<div class="container">
					<div class="identity">
						<img class="logo" src="<?php echo BASE_URL; ?>res/images/default.png">
						<h1 class="title">Department </h1>
						<a href="javascript:admin_sidebar.toggle()" class="sidebar_toggle"><i class="fa fa-bars"></i></a>
					</div>
				</div>
			</header>
			<div class="side_bar">
				<div class="container">
					<a href="javascript:admin_sidebar.toggle()" class="close_btn">&times;</a>
					<div class="intro">
						<div class="prf_img" style="background: url('<?php echo BASE_URL; ?>res/images/<?php echo $image; ?>'); background-size: contain; background-position: 0% 50%; background-repeat: no-repeat;"></div>
						<h5>Howdy, <?php echo $data['name']; ?>!</h5>
					</div>
					<ul>
						<li class="active"><a href="javascript:app.dashboard.init()"><i class="fa fa-home"></i> Dashboard</a></li>
						<li><a href="javascript:app.results.init()"><i class="fa fa-table"></i> Results</a></li>
						<li><a href="javascript:app.lecturer.init()"><i class="fa fa-graduation-cap"></i> Lecturers</a></li>
						<li><a href="javascript:app.courses.init()"><i class="fa fa-book"></i> Courses</a></li>
						<li><a href="javascript:app.student.init()"><i class="fa fa-users"></i> Students</a></li>
						<li><a href="javascript:app.add_student.init()"><i class="fa fa-plus"></i><i class="fa fa-users"></i> Add Student(s)</a></li>
						<li><a href="javascript:direct_entry()"><i class="fa fa-plus"></i><i class="fa fa-user"></i> Direct Entry</a></li>
						<li><a href="javascript:previous_records()"><i class="fa fa-clock-o"></i> Add Previous Records</a></li>
						<li><a href="javascript:notification()"><i class="fa fa-bell"></i><sub id="notification_count" class="notification_count"></sub> Notifications</a></li>
						<li><a href="#"><i class="fa fa-envelope"></i><sub id="msg_count" class="notification_count"></sub> Messages</a></li>
						<li><a href="javascript:account_settings()"><i class="fa fa-cogs"></i> Account Settings</a></li>
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
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/script.js"></script>
			<script type="text/javascript" src="<?php echo BASE_URL; ?>res/js/admin.js"></script>
		</body>
	</html>