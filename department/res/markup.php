<?php
	include 'student.markup.php';
	include 'student.requests.php';
	include 'lecturer.markup.php';
	include 'lecturer.requests.php';
	$markup = Array();
	$current_session = mysqli_fetch_assoc(mysqli_query($con, "SELECT current_sesh FROM admin"))['current_sesh'];
	$current_semester = mysqli_fetch_assoc(mysqli_query($con, "SELECT current_semester FROM admin"))['current_semester'];
	$markup['admin_reset'] = '
	<div class="resetadmin login_form">
		<form class="form-inline" id="admin_reset_form" action="javascript:admin.reset()">
			<p class="info" style="display: block; width: 100%; text-align: left; color: green; padding-left: 1em;"></p>
			<div class="form-group col-md-6 mb-2 mr-3">
				<input type="email" class="form-control col-md-12" placeholder="Email" name="email" required />
			</div>
			<input type="submit" name="submit" value="Reset" class="btn btn-primary mb-2" />
		</form>
	</div>
	';

	$markup['admin_results'] = '<div class="result_btns"><center>
		<a class="btn btn-primary mr-3" href="javascript:app.results.rawscores()">Raw Scores <i class="fa fa-long-arrow-right"></i></a><a class="btn btn-primary" href="javascript:app.results.full()">Full Results <i class="fa fa-long-arrow-right"></i></a>
		</center></div>
	';
	if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
		$sql = mysqli_query($con, "SELECT DISTINCT department FROM results");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			while($data = mysqli_fetch_assoc($sql)){
				$t = $t.'<li><a href="javascript:app.results.rawscores(\''.$data['department'].'\')">'.$data['department'].'</a></li>';
			}
			$t = $t.'</ul>';
		}else{
			$t = "<center><h3 class=\"fa fa-chain-broken\" style=\"text-align: center; font-size: 20em;\"></h3><h3>THERE ARE CURRENTLY NO RESULTS TO BE DISPLAYED AT THIS TIME<h3></center>";
		}
	}else{
		$t = '<h3>ACCESS HAS BEEN DENIED FOR THE CREDENTIALS YOU PROVIDED</h3>';
	}
	$markup['rawscores_departments'] = $t;
	if(cp('markup') && cp('markup') == 'default_semesters'){ array_assign('default_semesters', default_semester(cp('option'),['1ST','2ND'], cp('js'))); }
	function has_element($value, $arr){
		for($i = 0; $i < count($arr); $i++){
			if($arr[$i] == $value){
				return true;
			}
		}
		return false;
	}
	function carryoverChecker($matric,$yoe,$cur_sesh){
		global $con;
		$matric = strtoupper($matric);
		$carryovers = array();
		$grades = array();
		$grade_datas = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$yoe'");
		$student_datas = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'"));
		$dept = $student_datas['department'];
		$entry_year = $student_datas['entry_year'];
		$yoe = $student_datas['yoe'];
		while($grade_data = mysqli_fetch_assoc($grade_datas)){
			$grades[$grade_data['grade']] = $grade_data['gradepoints'];
			//...
		}
		$sql = mysqli_query($con, "SELECT * FROM results WHERE sesh<='$cur_sesh' AND matric='$matric'");
		//First check for failed courses
		if(mysqli_num_rows($sql) > 0){		
			while($data = mysqli_fetch_assoc($sql)){
				$course = $data['code'];
				$state = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE code='$course' AND sesh='$yoe' AND department='$dept'"))['state'];
				if($state=='ELECTIVE'){continue;}
				$grade = $data['grade'];
				if(!$grades[$grade]){
					$check = mysqli_num_rows(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$course' AND (NOT grade='$grade')"));
					if($check < 1){
						if(!has_element($course, $carryovers)){
							$carryovers[count($carryovers)] = $course;
						}
					}
				}
			}
		}
		//Then check for dropped courses...
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE sesh='$yoe' AND department='$dept' AND sesh_added<='$cur_sesh'");
		if(mysqli_num_rows($sql) > 0){
			while($data = mysqli_fetch_assoc($sql)){
				$code = $data['code'];
				if($data['state'] == 'ELECTIVE'){ continue; }
				if($data['slowed'] == 1){ continue; }
				if($entry_year>$yoe && $data['sesh_added'] == $yoe){ continue; }
				if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code'")) < 1){
					if(!has_element($code, $carryovers)){
						$carryovers[count($carryovers)] = $code;
					}
				}
			}
		}
		$units = 0;
		$lunits =0;
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND sesh<='$cur_sesh'");
		$tracker = array();
		while($data = mysqli_fetch_assoc($sql)){
			if($data['slowed'] == 1){ continue; }
			if(has_element($data['code'], $tracker)){ continue; }
			$tracker[count($tracker)] = $data['code'];
			if($data['sesh'] < $cur_sesh){
				$lunits = $lunits+$data['unit'];
			}else if($data['sesh'] == $cur_sesh){
				$units = $units+$data['unit'];
			}
		}
		$tce = 0;
		$ltce =0;
		$sql = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$cur_sesh'");
		while($data = mysqli_fetch_assoc($sql)){
			if($data['sesh_submitted'] < $cur_sesh){
				$ltce = $ltce+($grades[$data['grade']]*$data['units']);
			}else if($data['sesh_submitted'] == $cur_sesh){
				$tce = $tce+($grades[$data['grade']]*$data['units']);
			}
		}
		if($tce && $units){$cgpa = round(($tce+$ltce)/($units+$lunits),2);}else{$cgpa='-';$tce='-';$units='-';}
		if($ltce && $lunits){$gpa = round($ltce/$lunits,2);}else{$gpa='-';$ltce='-';$lunits='-';}
		if(!count($carryovers)){ $carryovers[count($carryovers)] = "Passed";}
		$carryovers = implode(', ', $carryovers);
		return array('carryovers'=>$carryovers,'lunits'=>$lunits,'ltce'=>$ltce,'gpa'=>$gpa,'units'=>$units,'tce'=>$tce,'cgpa'=>$cgpa);
	}
	function array_assign($n, $func){
		global $markup;
		if(cp('markup') && cp('markup') == $n){
			$markup[$n] = $func;
		}
	}
	function admin_reset(){
		$t = ' <div class="resetadmin login_form" autocomplete="off"> <form class="form-inline" id="admin_reset_form" action="javascript:user.reset()"> <p class="info" style="display: block; width: 100%; text-align: left; color: green; padding-left: 1em;"></p> <div class="form-group col-md-6 mb-2 mr-3"> <input type="email" class="form-control col-md-12" placeholder="Email" name="email" required /> </div> <input type="submit" name="submit" value="Reset" class="btn btn-primary mb-2" /> </form> </div> ';
		return json_encode(array('markup' => $t));
	}
	function student_login(){
		session_unset();
		if(cp(['username','password'])){
			$username = e(cp('username'));
			$password = strtoupper(md5(e(cp('password'))));
			$status = false;
			$msg = 'user not found!';
			if(ct('students', "(username='$username' OR email='$username' OR matric='$username') AND password='$password'")){
				$t = mysqli_fetch_assoc(ct('students', "(username='$username' OR email='$username' OR matric='$username') AND password='$password'"));
				$_SESSION['student_id'] = $t['id'];
				$status = true;
				$msg = 'Verified';
			}
			return je(array('status' => $status, 'msg' => $msg)); 
		}
	}
	function lecturer_login(){
		session_unset();
		if(cp(['username','password'])){
			$username = e(cp('username'));
			$password = strtoupper(md5(e(cp('password'))));
			$status = false;
			$msg = 'user not found!';
			$t = ct('lecturers', "(username='$username' OR email='$username') AND password='$password'");
			if($t){
				$t = mysqli_fetch_assoc($t);
				$_SESSION['lecturer_id'] = $t['id'];
				$status = true;
				$msg = 'Verified';
			}
			return je(array('status' => $status, 'msg' => $msg)); 
		}
	}
	function student_dashboard(){
		global $con;
		$t = e(cp('matric'));
		$i = e(cs('student_id'));
		if(ct('students', "matric='$t' AND id='$i'")){
			$m = $t;
			$t = '<div id="dashboard">
				<div id="graph" class="admin_graph"><canvas id="myCanvas"></canvas></div>';
			$t = $t.'<section id="notifications" class="notifications"><h5 class="center"><i class="fa fa-bell"></i> Notifications</h5>';
			$sql = mysqli_query($con, "SELECT * FROM notifications WHERE view_stat=1 AND not_for='student' AND user_id='$i'");
			if(mysqli_num_rows($sql) > 0){
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<li><a href="javascript:lecturer_updater('.$data['id'].');">'.$data['notification'].'</a></li>';
				}
			}else{
				$t = $t.'<h6 class="center">You Currently have no new Notifications</h6>';
			}
			$t = $t.'</section><div>';
			if(cp('graph')){
				$grades = array('A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0);
				$sql = mysqli_query($con, "SELECT DISTINCT sesh_submitted FROM results WHERE matric='$m' ORDER BY sesh_submitted DESC");
				if(mysqli_num_rows($sql) > 0){
					$labels = [];
					$dataSets = [];
					$count = 1;
					while($data = mysqli_fetch_assoc($sql)){
						$sesh = $data['sesh_submitted'];
						$labels[count($labels)] = $sesh;
						$sql2 = mysqli_query($con, "SELECT * FROM results WHERE sesh_submitted='$sesh' AND matric='$m'");
						$i = 0;
						$j = 0;
						while($data = mysqli_fetch_assoc($sql2)){
							$i = $i+($data['units']*$grades[$data['grade']]);
							$j = $j+$data['units'];
						}
						$dataSets[count($dataSets)] = $i/$j;
						if($count == 5){ break; }
						$count++;
					}
					return json_encode(array('status' => true, 'dataSets' => $dataSets, 'labels' => $labels));
				}else{
					return json_encode(array('status' => false, 'markup' => '<h6>No Graphical Data to be Displayed at this Time</h6>'));
				}
			}
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function add_student(){
		global $con,$current_session;
		if(cs('user_id')){
			$t = ' <div class="container mainbody_form"> <form class=""
			action="javascript:app.add_student.add()" id="student_add_form" autocomplete="off"> <p
			class="info">Please Fill Out This Form</p> <input class="login_input"
			type="text" name="matric" placeholder="Matric" required/> <span
			class="footnote">You can give a range (e.g, 16ME1001-16ME1065) or just one
			Matric Number (e.g 16ME1001)</span> <input list="departments" class="login_input mb-3"
			type="text" name="department" placeholder="Department" required/>';
			$sql = mysqli_query($con, "SELECT * FROM departments");
			if(mysqli_num_rows($sql) > 0){
				$t = $t.'<datalist id="departments">';
					while($data=mysqli_fetch_assoc($sql)){
						$t=$t.'<option value="'.$data['dept'].'" />';
					}
				$t = $t.'</datalist>';
			}
			$t = $t.'<input
			class="login_input" type="text" name="level" placeholder="Entry Session"/> <span class="footnote mb-3">If left empty, will be set to current session ['.$current_session.']</span> <input type="submit" class="login_submit"
			name="submit" value="Add Student(s)" required/> </form> </div> ';
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function student_add(){
		global $con, $current_session;
		function matricSplit($arr){
			$prefix = strtoupper(substr($arr[0], 0, strlen($arr[0])-4));
			if($prefix != strtoupper(substr($arr[1], 0, strlen($arr[1])-4))){
				return false;
			}
			$start = substr($arr[0], strlen($arr[0])-4, strlen($arr[0]));
			$stop = substr($arr[1], strlen($arr[1])-4, strlen($arr[1]));
			if(!is_numeric($stop) || !is_numeric($start) || $stop < $start){
				return false;
			}
			$tmp = array();
			for($i = $start; $i <= $stop; $i++){
				$tmp[count($tmp)] = $prefix.$i;
			}
			return $tmp;
		}
		function validateSession($n){
			global $current_session;
			$check = explode('/', $n);
			if(!is_array($check) || count($check) != 2){
				return false;
			}else if(!is_numeric($check[0]) || !is_numeric($check[1])){
				return false;
			}else if(!($n <= $current_session)){
				return false;
			}
			return true;
		}
		if(cs('user_id')){
			$j = cp(['matric','department','level']);
			if($j){
				$m = e($j['matric']);
				$d = strtoupper(e($j['department']));
				$l = e($j['level']);
				$m = explode('-', str_replace(' ', '', $m));
				$c = count($m);
				$m = (count($m) == 2)? matricSplit($m): strtoupper($m[0]);
				$password = strtoupper(md5('password'));
				if(!$m || $c > 2){
					return json_encode(array('status' => false, 'msg' => "<p>Please check the range of Matric Numbers<br> It should be in this form: 17ME1001-17ME1020. i.e, it should start from smaller Matric Numbers to larger ones and it should be between two Matric Numbers, you shouldn't have more than 2 arguments.</p>"));
				}
				$g = ct('departments', "dept='$d'"); 
				if(!$g){
					return json_encode(array('status' => false, 'msg' => "<p>$d is not a registered Department. <br>You can change that with the General Settings on the left <i class=\"fa fa-hand-o-left\"></i> menu</p>"));
				}else if(!validateSession($l)){
					return json_encode(array('status' => false, 'msg' => "<p>$l is not a valid year of entry</p>"));					
				}
				if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$l'")) < 1){
					return new_grade($l);
				}
				if(is_array($m)){
					for($i = 0; $i < count($m); $i++){
						$tm = $m[$i];
						if(ct('students',"matric='$tm'")){
							continue;
						}
						$sql = mysqli_query($con, "INSERT INTO students (password,matric,department,yoe,entry_year) VALUES ('$password', '$tm','$d','$l','$l')");
					}
					$t = 'These students can now access their accounts with their Matric numbers and a default password of "password" (without the quotes).';
				}else{
					if(!ct('students',"matric='$m'")){
						$sql = mysqli_query($con, "INSERT INTO students (password,matric,department,yoe,entry_year) VALUES ('$password', '$m','$d','$l','$l')");
					}
					$t = 'This student can now access this account using the matric number and a default password of "password" (without the quotes).';
				}
			}
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function reset_student(){
		if(cp(['email','reset_student'])){
			$t = e(cp('email'));
			$status = false;
			if(ct('students', "email='$t'")){
				$status = true;
			}
			return json_encode(array('status' => $status));
		}
	}
	function reset_lecturer(){
		if(cp(['email','reset_student'])){
			$t = e(cp('email'));
			$status = false;
			if(ct('lecturers', "email='$t'")){
				$status = true;
			}
			return json_encode(array('status' => $status));
		}
	}
	function dashboard(){
		global $con, $current_session,$current_semester;
		if(cs('user_id')){
			$whattosay = ($current_semester == '2ND')? 'End Academic Session':'End Semester';
			$t = '<div id="dashboard"><a href="javascript:end_session()" class="btn btn-primary hide">'.$whattosay.' ['.$current_session.']</a><div id="graph" class="admin_graph"><canvas
			id="myCanvas"></canvas></div> <section id="notifications"
			class="notifications"> <h5 class="title" style="text-align:center"><i
			class="fa fa-bell"></i> Notifications</h5>';
			$sql = mysqli_query($con, "SELECT * FROM notifications WHERE not_for='admin' AND view_stat=1 ORDER BY id");
			if(mysqli_num_rows($sql) > 0){
				while($data=mysqli_fetch_assoc($sql)){
					$t = $t.'<li><a href="javascript:lecturer_updater('.$data['id'].');">'.$data['notification'].'</a></li>';
				}
			}else{
				$t = $t.'<h6 class="center">You currently have no new Notifications</h6>';
			}
			$t=$t.'</section> </div>';
			if(cp('graph')){
				$sql = mysqli_query($con, "SELECT DISTINCT sesh_submitted FROM results ORDER BY sesh_submitted DESC");
				if(mysqli_num_rows($sql) > 0){
					$labels = [];
					$dataSets = [];
					$count = 1;
					while($data = mysqli_fetch_assoc($sql)){
						$sesh = $data['sesh_submitted'];
						$labels[count($labels)] = $sesh;
						$sql2 = mysqli_query($con, "SELECT * FROM results WHERE sesh_submitted='$sesh'");
						$i = 0;
						$j = 0;
						$grades = array();
						$grade_datas = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$sesh'");
						while($grade_data = mysqli_fetch_assoc($grade_datas)){
							$grades[$grade_data['grade']] = $grade_data['gradepoints'];
						}
						while($data = mysqli_fetch_assoc($sql2)){
							$i = $i+($data['units']*$grades[$data['grade']]);
							$j = $j+$data['units'];
						}
						$dataSets[count($dataSets)] = $i/$j;
						if($count == 5){ break; }
						$count++;
					}
					return json_encode(array('status' => true, 'dataSets' => $dataSets, 'labels' => $labels));
				}else{
					return json_encode(array('status' => false, 'markup' => '<h6>No Graphical Data to be Displayed at this Time</h6>'));
				}
			}
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function lecturer_veiw($r){
		global $con, $current_session, $current_semester;
		if(cs('user_id')){
			$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE id='$r'");
			if(mysqli_num_rows($sql) > 0){
				$t = '<div class="lecturer">';
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<div class="prf_img" style="background:
					url(\''.BASE_URL.'res/images/default.png\'); background-size: contain;
					background-repeat: no-repeat;"></div> <h5>'.$data['name'].'</h5>';
				}
				$t = $t.'<div class="courses"><h5>Courses Assigned</h5> <form
				class="form-inline mb-3" id="assigncourse_form" autocomplete="off" action="javascript:app.lecturer.assign()"> <div
				class="form-group mb-2 mr-2"><input type="hidden" value="'.$r.'" name="id" /><input id="assign_input" name="code" list="unassigned" class="form-control mb-2 mr-2"  required/>';
				$sql = mysqli_query($con, "SELECT * FROM courses WHERE assigned_to='NOT SET' AND sesh_added='$current_session' AND semester='$current_semester'");
				if(mysqli_num_rows($sql) > 0){
					$t = $t.'<datalist id="unassigned">';
					while($data = mysqli_fetch_assoc($sql)){
						if(getLevel(str_replace('/', '', $data['sesh'])) == 'Graduates'){ continue; }
						$t = $t.'<option value="'.$data['code'].' ['.$data['department'].']"/>';
					}
					$t = $t.'</datalist>';
				}
				$sql = mysqli_query($con, "SELECT * FROM courses WHERE assigned_to='$r' AND sesh_added='$current_session' AND semester='$current_semester' ORDER BY semester,sesh DESC");
				 $t = $t.'<button class="btn btn-primary mb-2" type="submit"
				name="submit" value="assing course"><i class="fa fa-plus"></i> Assign
				Course</button> </form> <table class="table hover"> <thead> <tr> <th
				scope="col">Code</th> <th scope="col">Title</th><th scope="col">Department</th> <th scope="col">Unassign</th> </tr> </thead>
				<tbody>';
				if(mysqli_num_rows($sql) > 0){
					while($data = mysqli_fetch_assoc($sql)){
						if(getLevel(str_replace('/', '', $data['sesh'])) == 'Graduates'  || $data['sesh_assigned'] != $current_session){ continue; }
					    $t = $t.'<tr> <td>'.$data['code'].'</td> <td>'.$data['title'].'</td><td>'.$data['department'].'</td> <td><a  class="trash" href="javascript:app.lecturer.unassign('.$data['id'].')"><i class="fa fa-trash"></i></a></td> </tr>';
					}
				}
				$t = $t.'</tbody> </table> </div> </div>';
				return json_encode(array('status' => true, 'markup' => $t));
			}else{
				return json_encode(array('status' => false, 'error' => '<p>An error occurred</p>'));
			}
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function fullresult_mark(){
		global $con;
		$t = cp(['department','semester','session','level']);
		if(cs('user_id') && $t){
			$sesh = $t['session'];
			$semester = $t['semester'];
			$dept = $t['department'];
			$level = $t['level'];
			$grades = array();
				$sql = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$sesh'");
				while($data = mysqli_fetch_assoc($sql)){
					if($data['minimum_score'] == '-'){ continue; }
					$grades[$data['grade']] = $data['gradepoints'];
				}
			$core_span = mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND semester='$semester' AND sesh='$sesh' AND sesh_added='$level' AND state='CORE'"));
			$ele_span = mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND semester='$semester' AND sesh='$sesh' AND sesh_added='$level' AND state='ELECTIVE'"));
			$core_span=($core_span)? $core_span:1;
			$ele_span=($ele_span)? $ele_span:1;
			$pre_span = $ele_span+$core_span+4;
			$t = ' <h6 class="center">FACULTY OF EDUCATION, DEPARTMENT OF '.$dept.'
				EXAMINATION RESULTS</h6> <h6 class="center"><span
				style="border-radius:0;background:transparent;color:#000;" class="btn
				btn-primary disabled">ACADEMIC SESSION: '.$level.'</span><span
				style="border-radius:0;background:transparent;color:#000;" class="btn
				btn-primary disabled">ACADEMIC SEMESTER/LEVEL:
				'.calcLevelFromSesh($level,$sesh).' Level '.$semester.' SEMESTER</span><span
				style="border-radius:0;background:transparent;color:#000;" class="btn
				btn-primary disabled">PROGRAMME: B.SC ED '.$dept.'</span></h6> <table
				class="table hover"> <thead> <tr> <th style="vertical-align: middle;"
				rowspan=2>#</th> <th style="vertical-align: middle;" rowspan=2>Name</th>
				<th style="vertical-align: middle;" rowspan=2>Matric</th> <th
				class="center" colspan='.$pre_span.'>Present Semester</th> <th
				class="center" colspan=4>Previous</th> <th class="center"
				colspan=4>Summary</th> <th style="vertical-align: middle;"
				rowspan=2>Remarks</th></tr> <tr> <th class="center"
				colspan='.$core_span.'>Core</th> <th class="center"
				colspan='.$ele_span.'>Electives</th> <th>TCR</th> <th>TCE</th> <th>TGP</th>
				<th>GPA</th> <th>CTCR</th> <th>CTCE</th> <th>CTGP</th> <th>CGPA</th>
				<th>CTCR</th> <th>CTCE</th> <th>CTGP</th> <th>CGPA</th> </tr> </thead>
				<tbody>';
				$sql = mysqli_query($con, "SELECT * FROM students WHERE yoe='$sesh' ORDER BY matric");
				$count = 1;
				while($data = mysqli_fetch_assoc($sql)){
					$matric = $data['matric'];
					
					$entry_year = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'"))['entry_year'];
					if($entry_year>$level){
						continue;
					}
					$t = $t.'<tr><td>'.$count.'</td><td>'.$data['name'].'</td><td>'.$matric.'</td>';
					/**Core and Elective courses display**/
						$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND semester='$semester' AND sesh='$sesh' AND sesh_added='$level' AND state='CORE'");
						if(mysqli_num_rows($sql1) > 0){
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								$unit = $data['unit'];
								$t = $t.'<td class="center">'.str_replace(' ', '', $code).'<br>'.$unit.'<br>';
								$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND sesh_submitted='$level' AND semester='$semester'"));
								$grade = $result_data['grade'];
								$score = $result_data['score'];
								if(!$score){ $t = $t.'DRP</td>'; continue; }
								$t = $t.$score.$grade.'<br>'.($grades[$result_data['grade']]*$result_data['units']).'</td>';
							}
						}else{ $t = $t.'<td>-</td>'; }
						$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND semester='$semester' AND sesh='$sesh' AND sesh_added='$level' AND state='ELECTIVE'");
						if(mysqli_num_rows($sql1) > 0){
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								$unit = $data['unit'];
								$t = $t.'<td class="center">'.str_replace(' ', '', $code).'<br>'.$unit.'<br>';
								$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND sesh_submitted='$level' AND semester='$semester'"));
								$grade = $result_data['grade'];
								$score = $result_data['score'];
								if(!$score){ $t = $t.'DRP</td>'; continue; }
								$t = $t.$score.$grade.'<br>'.($grades[$result_data['grade']]*$result_data['units']).'</td>';
							}
						}else{ $t = $t.'<td>-</td>'; }
					/****TCR*****/
						$sql1 = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND sesh='$level' AND semester='$semester'");
						$tcr = 0;$ltcr=0;$tce=0;
						while($data = mysqli_fetch_assoc($sql1)){
							$code = $data['code'];
							if($data['yoe'] > $sesh){
								$ltcr=$ltcr+$data['unit'];
							}else{
								$tcr=$tcr+$data['unit'];
							}
							$sql2 = mysqli_query($con, "select * from results where matric='$matric' and code='$code' and (not grade='F')");
							if(!mysqli_num_rows($sql2)){
								continue;
							}
							$tce = $tce+$data['unit'];
						}
						if($ltcr){$t = $t.'<td>'.$tcr.'+'.$ltcr.'=<br>'.($tcr+$ltcr).'</td><td>'.$tce.'</td>';}
						else{$t=$t.'<td>'.$tcr.'</td><td>'.$tce.'</td>';}
					/****TGP****/
						$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted='$level' AND semester='$semester'");
						$tgp = 0;
						while($data = mysqli_fetch_assoc($sql1)){
							$tgp = $tgp+($grades[$data['grade']]*$data['units']);
						}
						$t = $t.'<td>'.$tgp.'</td>';
						if($tcr+$ltcr==0){
							$t=$t.'<td>0</td>';
						}else{
							$t=$t.'<td style="vertical-align: middle;">'.round($tgp/($tcr+$ltcr),2).'</td>';
						}
					/****CTCR*****/
						$sql1 = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND sesh<='$level'");
						$ctcr = 0;
						$ctce = 0;
						while($data = mysqli_fetch_assoc($sql1)){
							$code=$data['code'];
							if($data['sesh'] == $level && $data['semester']>$semester){
								continue;
							}
							$ctcr=$ctcr+$data['unit'];
							$sql2 = mysqli_query($con, "select * from results where matric='$matric' and code='$code' and (not grade='F')");
							if(!mysqli_num_rows($sql2)){
								continue;
							}
							$ctce = $ctce+$data['unit'];
						}
						$t=$t.'<td>'.($ctcr-$tcr).'</td><td>'.($ctce-$tce).'</td>';
					/****CTGP****/
						$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$level'");
						$ctgp = 0;
						while($data = mysqli_fetch_assoc($sql1)){
							if($data['sesh_submitted'] == $level && $data['semester']>$semester){
								continue;
							}
							$ctgp = $ctgp+($grades[$data['grade']]*$data['units']);
						}
						$t = $t.'<td>'.($ctgp-$tgp).'</td>';
						if(($ctcr-$tcr)<=0){
							$t=$t.'<td>0</td>';
						}else{
							$t=$t.'<td style="vertical-align: middle;">'.round(($ctgp-$tgp)/($ctcr-$tcr),2).'</td>';
						}
						$t = $t."<td>$ctcr</td><td>$ctce</td><td>$ctgp</td>";
						if(($ctcr)<=0){
							$t=$t.'<td>0</td>';
						}else{
							$t=$t.'<td style="vertical-align: middle;">'.round(($ctgp)/($ctcr),2).'</td>';
						}
					/****Remarks***/
						$rems = array();
						/**Courses Failed**/
							$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$level'");
							while($data=mysqli_fetch_assoc($sql1)){
								$code=$data['code'];
								if($data['sesh_submitted'] == $level && $data['semester'] > $semester){
									continue;
								}
								$check = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$level' AND code='$code' AND (NOT grade='F')");
								$state_check = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND state='CORE' AND sesh='$sesh'");
								if(!mysqli_num_rows($state_check)){
									continue;
								}
								if(!mysqli_num_rows($check) && !has_element($data['code'],$rems)){
									$rems[count($rems)] = str_replace(' ', '', $data['code']);
								}
							}
						/**Course Not registered**/
							$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE state='CORE' AND sesh_added<='$level' AND sesh='$sesh'");
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								if($data['sesh_added'] == $level && $data['semester'] > $semester){
									continue;
								}
								$check = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND code='$code'");
								$entry_year = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'"))['entry_year'];
								if($entry_year>$data['sesh_added']){
									continue;
								}
								if(!mysqli_num_rows($check) && !has_element($code, $rems)){
									$rems[count($rems)] = str_replace(' ', '', $code);
								}
							}
						$rems = implode(', ', $rems);
						if(!$rems){ $rems='Passed'; }
						$t = $t."<td>$rems</td></tr>";
					$count++;
				}
			$t = $t.'</tbody></table>';
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function student_veiw($r,$l){
		global $con;
		if(cs('user_id')){
			$sql = mysqli_query($con, "SELECT * FROM students WHERE id='$r'");
			if(mysqli_num_rows($sql) > 0){
				$t = '<div class="lecturer">';
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<div class="prf_img" ><img src="'.BASE_URL.'res/images/default.png"></div> <h5>'.$data['name'].' ['.$data['matric'].']</h5><a class="btn btn-warning pull-right" href="javascript:app.student.reset('.$data['id'].')">Reset Account</a>';
				}
				$t = $t.'<div class="courses"><h5>Courses Registered</h5>';
				$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$r' ORDER BY code");
				 $t = $t.'<table class="table hover"> <thead> <tr> <th
				scope="col">Code</th> <th scope="col">Title</th> <th
				scope="col">Unit</th></thead>
				<tbody>';
				if(mysqli_num_rows($sql) > 0){
					while($data = mysqli_fetch_assoc($sql)){
					    $t = $t.'<tr> <td>'.$data['code'].'</td> <td>'.$data['title'].'</td><td>'.$data['unit'].'</td></tr>';
					}
				}
				$t = $t.'</tbody> </table> </div> </div>';
				return json_encode(array('status' => true, 'markup' => $t));
			}else{
				return json_encode(array('status' => false, 'error' => '<p>An error occurred</p>'));
			}
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function course_assign(){
		global $con, $current_session;
		$t = cp(['code','id', 'department']);
		if(cs('user_id') && $t){
			$c = e($t['code']);
			$id = e($t['id']);
			$d = e($t['department']);
			$sql = mysqli_query($con, "SELECT * FROM  courses WHERE code='$c' AND department='$d' AND assigned_to='NOT SET'");
			if(mysqli_num_rows($sql) < 1){
				return json_encode(array('status' => false, 'error' => '<p>'.$c.' is already assigned</p>'));
			}

			$sql = mysqli_query($con, "UPDATE courses SET assigned_to='$id', sesh_assigned='$current_session' WHERE code='$c' AND department='$d' AND assigned_to='NOT SET' ");
			if($sql){
				return json_encode(array('status' => true));
			}else{
				return json_encode(array('status' => false, 'error' => '<p>An unknown error has occured</p>'));
			}
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function course_unassign($r){
		global $con;
		if(cs('user_id')){
			
			$sql = mysqli_query($con, "UPDATE courses SET assigned_to='NOT SET', sesh_assigned='' WHERE id='$r'");
			if($sql){
				return json_encode(array('status' => true));
			}else{
				return json_encode(array('status' => false, 'error' => '<p>An unknown error has occured</p>'));
			}
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function lecturer_add(){
		global $con;
		if(!cs('user_id')){
			return encode(false, '<p>INVALID CREDENTIALS</p>');
		}
		if(cp('action') == 'delete'){
			$id = e(cp('id'));
			$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE id='$id' AND status='lecturer'");
			if(mysqli_num_rows($sql) > 0){
				$sql = mysqli_query($con, "UPDATE lecturers SET status='deleted' WHERE id='$id'");
			}else if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM lecturers WHERE id='$id' AND name='0' AND email='0'")) > 0){
				$sql = mysqli_query($con, "UPDATE lecturers SET status='',name='',email='',username='',password='' WHERE id='$id'");
			}else{
				$sql = mysqli_query($con, "UPDATE lecturers SET status='lecturer' WHERE id='$id'");
			}
			if(!$sql){ return encode(false, '<p>An ERROR occurred. Try doing that again</p>'); }
			return encode(true);
		}else{
			$rand = rand(0,11);
			$token = 'L-'.substr(md5(Date('Ymdhis')),$rand,4);
			$date = Date('Ymd');
			$password = strtoupper(md5($token));
			$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE status=''");
			if(mysqli_num_rows($sql) > 0){
				$id = mysqli_fetch_assoc($sql)['id'];
				$sql = mysqli_query($con, "UPDATE lecturers SET username='$token', password='$password', status='$date', name='0', email='0' WHERE id='$id'");
			}else{
				$sql = mysqli_query($con, "INSERT INTO lecturers (username,password,status,email,name) VALUES ('$token','$password','$date','0','0')");
			}
			if(!$sql){ return encode(false, '<p>An ERROR occurred. Try doing that again</p>'); }
			return encode(true,false,"<p>The lecturer can login with $token as their temporary Username and Password.<br>The temporary account will be deleted in 3 days if it is not activated.</p>");
		}
	}
	function lecturer(){
		global $con;
		if(isset($_SESSION['user_id']) && $_SESSION['user_id']){
			$status = cp('option');
			function checker($stat, $return = 'active'){
				if(cp('option') == $stat){ return $return; } return false;
			}
			$action = ($status == 'lecturer' || $status == 'tmp')? 'Remove':'Restore';
			$icon = ($status == 'lecturer' || $status == 'tmp')? 'fa-trash':'fa-repeat';
			$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE status='$status' ORDER BY id DESC");
			if($status == 'tmp'){$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE email='0' AND name='0' ORDER BY id DESC");}
			$t = '<div class="container"><div class="row"><div class="actions col-sm-7"><div class="toggle-switch"><a href="javascript:app.lecturer.init()" class="btn btn-primary '.checker('lecturer').'">Lecturers</a><a class="btn btn-primary '.checker('deleted').'" href="javascript:app.lecturer.init(\'deleted\')">Deleted</a><a class="btn btn-primary '.checker('tmp').'" href="javascript:app.lecturer.init(\'tmp\')">Temporary Accounts</a></div></div><div class="col-sm-3"><a class="btn btn-primary"href="javascript:lecturer_add()"><i class="fa fa-plus"></i> Add Lecturer</a></div></div>';
			if(mysqli_num_rows($sql) > 0){
				$t = $t.'<div style="margin-top: 5em;" id="lecturers" class="row">';
				while($data = mysqli_fetch_assoc($sql)){
					$n = (checker('tmp'))? $data[checker('tmp','username')]:$data['name'];
					$left_days = (checker('tmp'))? '<br>['.(($data['status']+3)-Date('Ymd')).' days left]':'';
					$t = $t.'<div class="col-md-3
				col-xs-6"> <a href="javascript:app.lecturer.view('.$data['id'].')"><div class="prf_img"
				style="background: url(\''.BASE_URL.'res/images/default.png\'); background-size:
				contain; background-repeat: no-repeat;"></div> <h5>'.$n.$left_days.'</h5></a><a style="display: block; text-align:center;" href="javascript:lecturer_add([\'delete\', '.$data['id'].'])"><i class="fa '.$icon.'"></i> '.$action.'</a> </div>';
				}
				$t = $t.'</div>';
			}else{
				$t = $t."<h5 style=\"margin-top: 5em;\">No Records!</h5>";
			}
			$t = $t.'</div>';
			return json_encode(array('status' => true, 'markup' => $t));
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function student(){
		global $con;
		if(cs('user_id')){
			$d = e(cp('department'));
			$l = e(cp('level'));
			$sql = mysqli_query($con, "SELECT * FROM students WHERE department='$d' AND yoe='$l' AND (NOT name='') AND (NOT username='') AND (NOT email='') ORDER BY matric");
			if(mysqli_num_rows($sql) > 0){
				$t = '<div class="container"><div style="margin-top: 5em;"id="students" class="row">';
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<div class="col-md-3
				col-xs-6"> <a href="javascript:app.student.view('.$data['id'].')"><div class="prf_img"
				style="background: url(\''.BASE_URL.'res/images/default.png\'); background-size:
				contain; background-repeat: no-repeat;"></div> <h5>'.$data['name'].' ['.$data['matric'].']</h5></a> </div>';
				}
				$t = $t.'</div></div>';
				return json_encode(array('status' => true, 'markup' => $t));
			}else{
				return json_encode(array('status' => false, 'error' => '<p>There are currently no registered '.$l.'Level '.$d.' Students at this time..</p>'));
			}
		}else{
			return json_encode(array('status' => false, 'error' => '<p>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</p>'));
		}
	}
	function courses_mark(){
		global $con,$current_semester,$current_session;
		$t = cp(['department','semester','sesh','view_sesh']);
		if(isset($_SESSION['user_id']) && $_SESSION['user_id'] && $t){
			$d = e($t['department']);
			$s = e($t['semester']);
			$se = e($t['sesh']);
			$vs = e($t['view_sesh']);
			$sql = mysqli_query($con, "SELECT * FROM courses WHERE sesh_added='$vs' AND department='$d' AND semester='$s' AND sesh='$se' ORDER BY sesh_added,code");
			$count = 1;
			$l = getLevel(str_replace('/', '', $se));
			$badnews = ($current_session==$vs && $current_semester==$s)? '':'<h5>You cant edit courses for previous and future sessions and semesters</h5>';
			$t = $badnews.'<h5>'.$d.' '.$se.' ['.$l.'] '.$s.'Semester Courses</h5><table class="table hover"> <thead> <tr> <th>#</th><th scope="col">Code</th> <th
			scope="col">Title</th> <th scope="col">Unit</th>
			<th scope="col">Course Lecturer</th><th scope="col"></th><th scope="col">Delete</th> </tr> </thead><tbody>';
			if(mysqli_num_rows($sql) > 0){
				while($data = mysqli_fetch_assoc($sql)){
					$saded = $data['sesh_added'];
					$a = $data['assigned_to'];
					$assigned_to = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lecturers WHERE id='$a'"))['name'];
					$a = ($assigned_to == '')? $data['assigned_to']:$assigned_to;
					$t = $t.'<tr> <td>'.$count.'</td><td>'.$data['code'].'</td> <td>'.$data['title'].'</td>
					<td>'.$data['unit'].'</td> <td>'.$a.'</td><td>'.$data['state'];
					if($l != 'Graduates' && $s == $current_semester && $saded == $current_session){ $t = $t.'<a style="color:red;" class="btn btn-default" href="javascript:changeCourseState('.$data['id'].', \''.$s.'\')">change</a>';}
					$t = $t.'</td><td>';
					if($l != 'Graduates' && $s == $current_semester && $saded == $current_session){$t = $t.'<a  class="trash"
					href="javascript:app.courses.delete('.$data['id'].')"><i class="fa
					fa-trash"></i></a>'; }
					$t = $t.'</td> </tr>';
					$count++;
				}
			}
			$t = $t.'</tbody></table>';
			if($l != 'Graduates' && $s == $current_semester && $vs == $current_session){
				$t = $t.'<form autocomplete="off" class="form-inline mb-3" id="courseadd_form" action="javascript:app.courses.add()"> <div class="form-group mb-3 mr-1"><input type="text" placeholder="Code" class="form-control" name="code" required/></div><div class="form-group mb-3 mr-1"><input type="text" class="form-control" placeholder="Title" name="title" required/></div><div class="form-group mb-3 mr-1"><input placeholder="Unit" type="number" name="unit" required class="form-control"/></div><div class="form-group mb-3 mr-1"><input type="text" list="lecturers" name="lecturer" placeholder="Lecturer (optional)" class="form-control"/>';
				$sql = mysqli_query($con, "SELECT * FROM lecturers");
				if(mysqli_num_rows($sql) > 0){
					$t = $t.'<datalist id="lecturers">';
					while($data = mysqli_fetch_assoc($sql)){
						$t = $t.'<option value="'.$data['name'].'" />';
					}
					$t = $t.'</datalist>';
				}
				$t = $t.'</div><input type="submit" class="btn btn-primary mb-3" name="submit" value="Add Course"/></form>';
			}
		}else{
			$t = '<h3>ACCESS DENIED FOR THE CREDENTIALS YOU PROVIDED</h3>';
		}
		return json_encode(array('markup' => $t));
	}
	function changeCourseState(){
		global $con,$current_session,$current_semester;
		if(!cs('user_id') || !cp('id')){
			return json_encode(array('status' => false, 'error' => 'INVALID CREDENTIALS'));
		}
		$id = e(cp('id'));
		$state = mysqli_fetch_assoc(mysqli_query($con, "SELECT state FROM courses WHERE id='$id'"))['state'];
		$q = ($state == 'CORE')? mysqli_query($con, "UPDATE courses SET state='ELECTIVE' WHERE id='$id' AND semester='$current_semester' AND sesh_added='$current_session'"):mysqli_query($con, "UPDATE courses SET state='CORE' WHERE id='$id' AND semester='$current_semester' AND sesh_added='$current_session'");
		$r = ($q)? array('status' => true, 'error' => 'This is not an error'):array('status' => false, 'error' => 'AN ERROR OCCURED!');
		return json_encode($r);
	}
	function add_course(){
		global $con, $current_session,$current_semester;
		$r = cp(['department','semester','code','title','unit','sesh']);
		if(cs('user_id') && is_array($r)){
			$d = e($r['department']);
			$s = e($r['semester']);
			$c = e($r['code']);
			$t = e($r['title']);
			$u = e($r['unit']);
			$se = e($r['sesh']);
			$le = e($_POST['lecturer']);
			$cl = false;
			if($le != ''){
				$cl = (mysqli_num_rows(mysqli_query($con, "SELECT * FROM lecturers WHERE name='$le'")) > 0)? mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lecturers WHERE name='$le'"))['id']:false;
			}
			if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE code='$c'")) > 0){
				$error = '<p>'.$c.' is already added.</p>';
				return json_encode(array('status' => 'js', 'error' => $error));
			}else if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE title='$t'")) > 0){
				$error = '<p>Another course is titled <i style="color: green; text-transform: uppercase;">'.$t.'</i></p>';
				return json_encode(array('status' => 'js', 'error' => $error));
			}
			if($le != '' && $cl == false){
				$error = '<p>'.$le.' is not a registered lecturer, but you can continue with this operation.</p>';
				$error = $error.'<p>Do you want to continue? The lecturer field will be given a default value of "NOT SET"</p>';
				return json_encode(array('status' => 'js', 'error' => $error));
			}
			$sql = ($le != '')? "INSERT INTO courses (code,title,department,semester,unit,assigned_to,sesh,sesh_added,sesh_assigned) VALUES ('$c','$t','$d','$current_semester','$u','$cl','$se','$current_session','$current_session')":"INSERT INTO courses (code,title,department,semester,unit,sesh,sesh_added) VALUES ('$c','$t','$d','$current_semester','$u','$se','$current_session')";
			$q = mysqli_query($con, $sql);
			if(!$q){
				$error = "<p>An unknown error is keeping you from adding a course at this time.<br> Try again later</p>";
				return json_encode(array('status' => false, 'error' => $error));
			}else{
				return json_encode(array('status' => true));
			}
		}else{
			$error = '<p>INVALID CREDENTIALS</p>';
			return json_encode(array('status' => false, 'error' => $error));
		}
	}
	function raw_markup(){
		global $con,$current_session,$current_semester;
		$t = cp(['department','sesh','semester','view_sesh']);

		if(!cs('user_id') || !$t){
			return json_encode(array('markup' => 'INVALID CREDENTIALS'));
			exit();
		}
		$d = e($t['department']);
		$s = e($t['sesh']);
		$se = e($t['semester']);
		$vs = e($t['view_sesh']);
		$sql = mysqli_query($con, "SELECT DISTINCT code FROM results WHERE sesh_submitted='$vs' AND department='$d' AND sesh='$s' AND semester='$se' AND view_stat>=2 ORDER BY code DESC");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			while($data = mysqli_fetch_assoc($sql)){
				$t = $t.'<li><a href="javascript:app.results.rawscores([\'course\',\''.$data['code'].'\'])">'.$data['code'].'</a></li>';
			}
			$t = $t.'</ul>';
		}else{
			return json_encode(array('markup' => '<h5 style="margin-top: 5em; text-align: center">No Records!</h5>'));
		}
		if(cp('opt') == 'accept'){
			$code = e(cp('course'));
			$sql = mysqli_query($con, "UPDATE results SET view_stat=3 WHERE department='$d' AND sesh='$s' AND sesh_submitted='$vs' AND semester='$se' AND code='$code' AND view_stat=2");
			if(!$sql){ return encode(false, "<p>Try that again!</p>"); }
			return encode(true);
		}else if(cp('opt') == 'reject'){
			$code = e(cp('course'));
			$sql = mysqli_query($con, "UPDATE results SET view_stat=1 WHERE department='$d' AND sesh='$s' AND sesh_submitted='$vs' AND semester='$se' AND code='$code' AND view_stat=2");
			$user_id = mysqli_fetch_assoc(mysqli_query($con, "SELECT assigned_to FROM courses WHERE code='$code' AND sesh_assigned='$current_session' AND semester='$current_semester' AND department='$d'"))['assigned_to'];
			$sql = mysqli_query($con, "INSERT INTO notifications (notification,not_for,view_stat,user_id) VALUES ('$code has been returned to you for review by the Admin.', 'lecturer','1','$user_id')");
			if(!$sql){ return encode(false, "<p>Try that again!</p>"); }
			return encode(true);
		}
		if(cp('course') != 'false'){
			$c = cp('course');
			$l = getLevel(str_replace('/', '', $s));
			$n = mysqli_query($con, "SELECT * FROM results WHERE code='$c' AND department='$d' AND sesh='$s' AND semester='$se' AND sesh_submitted='$vs' AND view_stat>='2' ORDER BY matric ");
			if(mysqli_num_rows($n)){
				$t = '<h5>'.cp('department').' '.$c.' '.cp('sesh').' '.$se.' Semester '.$l.'
				Result</h5><table class="table hover"> <thead> <tr> <th scope="col">#</th>
				<th scope="col">Name</th> <th scope="col">Matric</th> <th
				scope="col">CA</th> <th scop="col">Exam</th> <th scope="col">Total</th> <th
				scope="col">Grade</th> </tr> </thead><tbody>';
				$count =1;
				$view_stat = 1;
				while($data = mysqli_fetch_assoc($n)){
					$view_stat = $data['view_stat'];
					$t = $t.'<tr> <td>'.$count.'</td> <td>'.$data['name'].'</td>
					<td>'.$data['matric'].'</td> <td>'.$data['ca'].'</td>
					<td>'.$data['exam'].'</td> <td>'.$data['score'].'</td>
					<td>'.$data['grade'].'</td> </tr>';
					$count++;
				}
				$t = $t.'</tbody></table>';
				if($view_stat == 2){
					$t = $t.'<a class="btn btn-success" href="javascript:app.results.rawscores(\'accept\')"><i class="fa fa-check"></i> Accept</a><a class="btn btn-danger pull-right" href="javascript:app.results.rawscores(\'reject\')"><i class="fa fa-ban"></i> Reject</a>';
				}else{
					$t = $t.'<a class="btn btn-success disabled" href="javascript:void(0)"><i class="fa fa-check"></i> Accepted!</a>';
				}
			}else{
				$t = '<h5 style="margin-top:5em; text-align: center">There is currently no result for this group.</h5>';
			}
			return json_encode(array('markup' => $t));
		}
		return json_encode(array('markup' => $t));
	}
	function course_mark($n){
		$t = '<ul style="margin-top: 5em;" class="tiles">';
		while($data = mysqli_fetch_assoc($n)){
			$t = $t.'<li><a href="javascript:app.results.rawscores(\''.cp('department').'\',\''.cp('sesh').'\',\''.cp('semester').'\',\''.$data['course'].'\')">'.$data['code'].' ['.$data['course'].']</a></li>';
		}
		$t = $t.'</ul>';
		return $t;
	}
	function default_dept($option,$arr, $js){
		global $con;
		$sql = mysqli_query($con, "SELECT * FROM departments");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			while($data = mysqli_fetch_assoc($sql)){
				$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$data['dept'].'\'])">'.$data['dept'].'</a></li>';
			}
			$t = $t.'</ul>';
		}else{ $t = '<h5 style="margin-top: 5em;">No Departments</h5>'; }
		return $t;
	}
	function getLevel($s){
		global $con;
		$basesession = mysqli_fetch_assoc(mysqli_query($con, "SELECT current_sesh FROM admin"))['current_sesh'];
		$basesession = str_replace('/', '', $basesession);
		$id = (($basesession-$s)/10001)+1;
		$s = mysqli_query($con, "SELECT * FROM levels WHERE id='$id'");
		if(mysqli_num_rows($s) > 0){
			$l = mysqli_fetch_assoc($s)['level'].' Level';
		}else{
			$l = 'Graduates';
		}
		return $l;
	}
	function targert_sesh(){
		global $con,$current_session;
		$t = cp(['option','js','current_sesh']);
		if(!$t){
			return json_encode(array('markup' => '<h5 style="margin-top: 5em; text-align: center;">An Error Occurred</h5>'));
		}
		$option = $t['option'];
		$js = $t['js'];
		$cur = $t['current_sesh'];
		$current_session = str_replace('/', '', $current_session);
		$target_session = str_replace('/', '', $cur);
		$arr_target_session = explode('/', $cur);
		$numberOfLevels = (($current_session-$target_session)/10001)+1;
		$t = '<ul style="margin-top: 5em;" class="tiles">';
			$sql = mysqli_query($con, "SELECT level FROM levels");
			if(mysqli_num_rows($sql) > 0){
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$arr_target_session[0].'/'.$arr_target_session[1].'\'])">'.$data['level'].'</a></li>';
					$arr_target_session[0] = $arr_target_session[0]+1;
					$arr_target_session[1] = $arr_target_session[1]+1;
					$numberOfLevels--;
					if($numberOfLevels <= 0){ break; }
				}
			}
		$t = $t.'</ul>';	
		return json_encode(array('markup' => $t));
	}
	function default_sesh(){
		global $con;
		$option = cp('option');
		$js = cp('js');
		if(!$option && !$js){
			return false;
		}
		$sql = mysqli_query($con, "SELECT DISTINCT yoe FROM students ORDER BY yoe DESC");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			while($data = mysqli_fetch_assoc($sql)){
				$s = str_replace('/', '', $data['yoe']);
				$l = getLevel($s);
				$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$data['yoe'].'\'])">'.$data['yoe'].' ['.$l.']</a></li>';
			}
			$t = $t.'</ul>';
		}
		else{
			$t = '<h5 style="margin-top: 5em; text-align:center;">No Sessions</h5>';
		}
		return json_encode(array('markup' => $t));
	}
	function default_semester($option,$arr, $js){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			for($i = 0; $i < count($arr); $i++){
				$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$arr[$i].'\'])">'.$arr[$i].'</a></li>';
			}
			$t = $t.'</ul>';
			return $t;
	}

?>