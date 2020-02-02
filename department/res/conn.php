<?php
	define('HOST', $_SERVER['HTTP_HOST']);
	define('USERNAME', 'root');
	define('PASSWORD', '');
	define('DB', 'department');
	define('BASE_URL', 'http://'.HOST.'/department/');
	$con = mysqli_connect(HOST,USERNAME,PASSWORD) or die("<h1>".mysqli_connect_error()."</h1>");
	mysqli_select_db($con, DB) or mysqli_query($con, "CREATE DATABASE IF NOT EXISTS ".DB) or die(mysqli_error($con));
	mysqli_select_db($con, DB) or die('<h1>'.mysqli_error($con).'</h1>');
	require 'install.php';
	if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM `admin`"))<1){
		$password = strtoupper(md5('admin'));
		$password = strtoupper($password);
		mysqli_query($con, "INSERT INTO `admin` (`name`,username,`password`) VALUES ('admin','admin','$password')");
	}
	session_start();
	function tableEmpty($table){
		global $con;
		if(is_array($table)){
			for($i = 0; $i < count($table); $i++){
				if(!tableEmpty($table[$i])){ return false; }
			}
			return true;
		}
		$sql = mysqli_query($con, "SELECT * FROM `$table` WHERE 1");
		$q = ($sql)? mysqli_num_rows($sql):0;
		$result = ($q)? true: false;
		return $result;
	}
?>