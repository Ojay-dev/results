<?php
	require 'conn.php';
	require 'markup.php';
	$refer = BASE_URL;
	if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']){
		$refer = $_SERVER['HTTP_REFERER'];
	}
	function dailyRoutine(){
		global $con;
		$date = Date('Ymd');
		$date = $date-3;
		$sql = mysqli_query($con, "UPDATE lecturers SET status='',name='',email='',username='',password='' WHERE (NOT status='lecturer') AND (NOT status='deleted') AND status<'$date'");
	}
	dailyRoutine();
	function ct($t, $v){
		global $con;
		$t = mysqli_query($con, "SELECT * FROM `$t` WHERE $v");
		$r = (mysqli_num_rows($t) > 0)? $t:false;
		return $r;
	}
	function cp($n){
		if(is_array($n)){
			$r = Array();
			for($i = 0; $i < count($n); $i++){
				if(isset($_POST[$n[$i]]) && $_POST[$n[$i]]){
					$r[$n[$i]] = e($_POST[$n[$i]]);
				}else{
					return false;
				}
			}
		}else{
			$r = (isset($_POST[$n]) && $_POST[$n])? e($_POST[$n]):false;
		}
		return $r;
	}
	function cs($n){
		if(is_array($n)){
			$r = Array();
			$t = false;
			for($i = 0; $i < count($n); $i++){
				$t = (isset($_SESSION[$n[$i]]) && $_SESSION[$n[$i]])? $_SESSION[$n[$i]]:false;
				if(isset($_SESSION[$n[$i]]) && $_SESSION[$n[$i]]){
					$r[$n[$i]] = $_SESSION[$n[$i]];
				}
				if($t == false){
					break;
				}
			}
			if(!$t){
				$r = false;
			}
		}else{
			$r = (isset($_SESSION[$n]) && $_SESSION[$n])? $_SESSION[$n]:false;
		}
		return $r;
	}
	function e($n){
		global $con;
		return mysqli_real_escape_string($con,$n);
	}
	function je($a){
 		echo json_encode($a);
	}
	if(cp('mark_up')){
		$mk = cp('mark_up')();
		echo $mk;
		exit();
	}
	if(cp('course_unassign')){
		$r = e(cp('course_unassign'));
		$r = course_unassign($r);
		echo $r;
		exit();
	}
	if(cp('lecturer')){
		$r = e(cp('lecturer'));
		$r = lecturer_veiw($r);
		echo $r;
		exit();
	}
	if(cp(['student','level'])){
		$r = cp(['student','level']);
		$r = student_veiw(e($r['student']), e($r['level']));
		echo $r;
		exit();
	}
	if(cp('username') && cp('password') && cp('admin')){
		session_unset();
		$username = e(cp('username'));
		$password = strtoupper(md5(e(cp('password'))));
		$status = false;
		$msg = 'user not found!';
		if(ct('admin', "(username='$username' OR email='$username') AND password='$password'")){
			$t = mysqli_fetch_assoc(ct('admin', "(username='$username' OR email='$username') AND password='$password'"));
			$_SESSION['user_id'] = $t['id'];
			$status = true;
			$msg = 'Verified';
		}
		je(array('status' => $status, 'msg' => $msg));
		exit();
	}
	if(cp('email') && cp('resetadmin')){
		$t = e(cp('email'));
		$status = false;
		if(ct('admin', "email='$t'")){
			$status = true;
		}
		je(array('status' => $status));
		exit();
	}
	if(cp('markup')){
		$mk = (cp('markup') == 'default_dept')? default_dept(cp('option'),['MATHEMATICS', 'PHYSICS', 'CHEMISTRY', 'BIOLOGY'],cp('js')): $markup[cp('markup')];
		je(array('markup' => $mk));
		exit();
	}
	if(cp('delete_course')){
		if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
			$r = e(cp('delete_course'));
			$sql = mysqli_query($con, "DELETE FROM courses WHERE id='$r' AND sesh_added='$current_session' AND semester='$current_semester'");
			if(!$sql){
				echo json_encode(array('status' => false, 'error' => '<p>An unknown error has occurred</p>'));
				exit();
			}else{
				echo json_encode(array('status' => true));
				exit();
			}
		}
		echo json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		exit();
	}
	header('Location: '.$refer);
?>