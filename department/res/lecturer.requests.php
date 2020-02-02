<?php




function lecturer_courses(){
	global $con,$current_session,$current_semester;
	$id = cs('lecturer_id');
	if(!$id){
		return encode(false, '<p>INVALID CREDENTIALS</p>');
	}
	$option = cp('option');
	$js = cp('js');
	if(cp('options') == 'sheet'){
		$code = cp('code');
		$dept = cp('dept');
		$course_datases = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND assigned_to='$id' AND department='$dept' AND ((sesh_assigned='$current_session' AND semester='$current_semester') OR slowed=1)"));
		$course_session = $course_datases['sesh_added'];
		$course_semester = $course_datases['semester'];
		$view_stat = mysqli_fetch_assoc(mysqli_query($con,"select * from results where code='$code' AND sesh_submitted='$course_session' and semester='$course_semester' and department='$dept'"))['view_stat'];
		$view_stat = ($view_stat == 1 || $view_stat == '')? 1:0;
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND assigned_to='$id' AND sesh_assigned='$course_session' AND semester='$course_semester'");
		if(mysqli_num_rows($sql) > 0){
			$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE code='$code' AND sesh='$course_session' AND semester='$course_semester' ORDER BY matric");
			if(mysqli_num_rows($sql)>0){
				$t ='<h5>Score Sheet</h5> <table class="table hover"> <thead> <tr> <th
				scope="col">#</th> <th scope="col">Name</th> <th scope="col">Matric</th>
				<th scope="col">CA</th> <th scope="col">Exam</th> <th
				scope="col">Total</th> <th scope="col">Grade</th> <th
				scope="col">Remark</th> </tr> </thead> <tbody>';
				$count = 1;
				while($data = mysqli_fetch_assoc($sql)){
					$student_id = $data['student_id'];
					$student_data = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM students WHERE id='$student_id' AND department='$dept'"));
					$matric = $student_data['matric'];
					$name = $student_data['name'];
					if(empty($name) || empty($matric)){continue;}
					$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted='$course_session' AND semester='$course_semester' AND code='$code'"));
					$total = ($result_data['score'])? $result_data['score']:'0';
					$exam =  ($result_data['exam'])? $result_data['exam']:'0';
					$ca =  ($result_data['ca'])? $result_data['ca']:'0';
					$grade =  ($result_data['grade'])? $result_data['grade']:'A';
					$remark = ($grade == 'F')? 'Failed':'Passed';
					if($view_stat){
						$t = $t."<tr><td>$count</td><td>$name</td><td>$matric</td><form><td><input style=\"border: 1px solid #fff; padding: 4px 8px; border-radius: 4px;\" min=\"0\" max=\"30\" oninput=\"update_score(this.value,'$student_id','#$student_id$matric','CA','$code')\" value=\"$ca\" type=\"number\"></td><td><input type=\"number\" style=\"border: 1px solid #fff; padding: 4px 8px; border-radius: 4px;\" min=\"0\" max=\"30\" oninput=\"update_score(this.value,'$student_id','#$student_id$matric','EXAM','$code')\" value=\"$exam\"></td><td id=\"$student_id$matric\" class=\"total\">$total</td><td id=\"$student_id$matric\"  class=\"grade\">$grade</td></form><td id=\"$student_id$matric\"  class=\"remark\">$remark</td></tr>";
					}else{
						$t = $t."<tr><td>$count</td><td>$name</td><td>$matric</td><td>$ca</td><td>$exam</td><td>$total</td><td>$grade</td><td>$remark</td></tr>";
					}
					
					$count++;
				}
				$t = $t.'</tbody> </table>';
				if($view_stat){
					$t=$t.'<a href="javascript:submit_score_sheet(\''.$code.'\',\''.$dept.'\')" class="btn btn-primary pull-right">Submit</a>';	
				}else{
					$t=$t.'<a href="javascript:void(0)" class="btn btn-success disabled">Submitted!</a>';
				}
			}else{
				$t = '<h5 class="center" style="margin-top:5em;">No Records!</h5>';
			}
		}else{
			$t = '<h5 class="center" style="margin-top:5em;">'.$code.' Wasn\'t assigned to you!</h5>';
		}
	}
	else if(cp('options') == 'dept'){
		$code = e(cp('code'));
		$t = '';
		$sql = mysqli_query($con, "SELECT DISTINCT student_id FROM course_registered WHERE code='$code' AND ((sesh='$current_session' AND semester='$current_semester') OR slowed=1)");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			$tracker = array();
			while($data = mysqli_fetch_assoc($sql)){
				$student_id = $data['student_id'];
				$dept = mysqli_fetch_assoc(mysqli_query($con, "SELECT department FROM students WHERE id='$student_id'"))['department'];
				if(has_element($dept, $tracker)){ continue; }$tracker[count($tracker)] = $dept;
				$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$dept.'\'])">'.$dept.'</a></li>';
			}
			$t = $t.'</ul>';
		}else{
			$t ='<h5 style="margin-top:5em;" class="text-center">No one has registered yet</h5>';
		}
	}else{
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE assigned_to='$id' AND ((sesh_assigned='$current_session' AND semester='$current_semester') OR slowed=1)");
		if(mysqli_num_rows($sql) > 0){
			$t = '<ul style="margin-top: 5em;" class="tiles">';
			while($data = mysqli_fetch_assoc($sql)){
				$slowed_course = ($data['slowed'] == 1)? '<i class="fa fa-spinner"></i> ':'';
				$code = $data['code'];
				$sesh = $data['sesh_added'];
				$dept = $data['department'];
				$sql1 = mysqli_query($con, "SELECT * FROM results WHERE code='$code' AND sesh_submitted='$sesh' AND department='$dept' AND view_stat>1");
				$sumitted_result = (mysqli_num_rows($sql1))? '<i class="fa fa-check"> Submitted</i>':'';
				$sql2 = mysqli_query($con, "SELECT * FROM results WHERE code='$code' AND sesh_submitted='$sesh' AND department='$dept' AND view_stat>2");
				$sumitted_result = (mysqli_num_rows($sql2))? '<i class="fa fa-check"> Accepted</i>':$sumitted_result;
				$t = $t.'<li><a href="javascript:'.$js.'([\''.$option.'\',\''.$data['code'].'\'])">'.$slowed_course.$data['code'].' ['.$data['title'].'] '.$sumitted_result.'</a></li>';
			}
			$t = $t.'</ul>';
		}else{
			$t = '<h5 class="center" style="margin-top:5em;">No course is currently assigned to you!</h5>';
		}
	}
	return encode(true,false,$t);
}
function lecturer_update_score(){
	global $con,$current_semester,$current_session;
	$id = cs('lecturer_id');
	$data = cp(['id','opt','code']);
	if(!$id || !$data){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	$score = (empty(e($_POST['score'])))? '0': e($_POST['score']);
	$student_id = $data['id'];
	$option = $data['opt'];
	$code = $data['code'];
	$data = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM students WHERE id='$student_id'"));
	$matric = $data['matric'];
	$name = $data['name'];
	$department = $data['department'];
	$yoe = $data['yoe'];
	$grades = array();
	$view_stat = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM results WHERE department='$department' AND view_stat>='2' AND sesh_submitted='$current_session' AND semester='$current_semester' AND code='$code'"))['view_stat'];
	if($view_stat){ return encode(false, '<p>You can\'t make changes to this scoresheet; it has been submitted!</p>'); }
	$sql = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$yoe'");
	while($data = mysqli_fetch_assoc($sql)){
		if($data['minimum_score'] == '-'){ continue; }
		$grades[count($grades)] = [$data['grade'],$data['minimum_score']];
	}
	for($i = 0; $i < count($grades); $i++){
		$minimum = $grades[$i][1];
		$max = 100;
		for($j = 0; $j < count($grades); $j++){
			if($grades[$j][1] < $max && $minimum < $grades[$j][1]){
				$max = $grades[$j][1]-1;
			}
		}
		$grades[$i][2] = $max;
	}
	$course_datases = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND assigned_to='$id' AND department='$department' AND ((sesh_assigned='$current_session' AND semester='$current_semester') OR slowed=1)"));
	$course_session = $course_datases['sesh_added'];
	$course_semester = $course_datases['semester'];
	$sql = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND assigned_to='$id' AND sesh_assigned='$course_session' AND semester='$course_semester'");
	if(mysqli_num_rows($sql) < 0){
		return encode(false, "<p>INVALID CREDENTIALS</p>");
	}
	if($option == 'CA'){
		if($score > 30 || !is_numeric($score)){
			return encode(false, '<p>CA can\'t be greater than 30');
		}
		$sql = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND name='$name' AND code='$code' AND sesh_submitted='$course_session' AND semester='$course_semester'");
		if(mysqli_num_rows($sql) > 0){
			$data = mysqli_fetch_assoc($sql);
			$exam = $data['exam'];
			$result_id = $data['id'];
			$total = $score+$exam;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($total <= $grades[$i][2] && $total >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "UPDATE results SET score='$total', grade='$grade', ca='$score' WHERE id='$result_id'");
		}else{
			$course_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE sesh_added='$course_session' AND semester='$course_semester' AND code='$code' AND assigned_to='$id'"));
			$course_title = $course_data['title'];
			$course_unit = $course_data['unit'];
			$course_sesh = $course_data['sesh'];
			$exam = '0';
			$total = $score+$exam;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($score <= $grades[$i][2] && $score >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "INSERT INTO results (course,code,units,score,grade,name,matric,sesh,semester,department,exam,ca,view_stat,sesh_submitted,yoe) VALUES ('$course_title','$code','$course_unit','$total','$grade','$name','$matric','$course_sesh','$course_semester','$department','$exam','$score','1','$course_session','$yoe')");
		}
		if(!$sql){ encode(false, "<p>Try entering that value again, didn't work that time.</p>"); }
		return json_encode(array('status'=>true,'grade'=>$grade,'remark'=>$remark,'total'=>$total));
	}else if($option == 'EXAM'){
		if($score > 70 || !is_numeric($score)){
			return encode(false, '<p>Examinatioin score can\'t be greater than 70');
		}
		$sql = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND name='$name' AND code='$code' AND sesh_submitted='$course_session' AND semester='$course_semester'");
		if(mysqli_num_rows($sql) > 0){
			$data = mysqli_fetch_assoc($sql);
			$ca = $data['ca'];
			$result_id = $data['id'];
			$total = $score+$ca;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($total <= $grades[$i][2] && $total >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "UPDATE results SET score='$total', grade='$grade', exam='$score' WHERE id='$result_id'");
		}else{
			$course_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE sesh_added='$current_session' AND semester='$current_semester' AND code='$code' AND assigned_to='$id'"));
			$course_title = $course_data['title'];
			$course_unit = $course_data['unit'];
			$course_sesh = $course_data['sesh'];
			$ca = '0';
			$total = $score+$ca;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($score <= $grades[$i][2] && $score >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "INSERT INTO results (course,code,units,score,grade,name,matric,sesh,semester,department,exam,ca,view_stat,sesh_submitted,yoe) VALUES ('$course_title','$code','$course_unit','$total','$grade','$name','$matric','$course_sesh','$course_semester','$department','$score','$ca','1','$course_session','$yoe')");
		}
		if(!$sql){ encode(false, "<p>Try entering that value again, didn't work that time.</p>"); }
		return json_encode(array('status'=>true,'grade'=>$grade,'remark'=>$remark,'total'=>$total));
	}
}
function submit_score_sheet (){
	global $con,$current_semester,$current_session;
	$id = cs('lecturer_id');
	if(!$id){
		return encode(false, '<p>INVALID CREDENTIALS</p>');
	}
	$lecturer_name = mysqli_fetch_assoc(mysqli_query($con, "SELECT name FROM lecturers WHERE id='$id'"))['name'];
	$code = e(cp('code'));
	$confirm = e(cp('confirm'));
	$dept = e(cp('dept'));
	$course_datases = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND assigned_to='$id' AND department='$dept' AND ((sesh_assigned='$current_session' AND semester='$current_semester') OR slowed=1)"));
	$course_session = $course_datases['sesh_added'];
	$course_semester = $course_datases['semester'];
	//**Check if sheet is already submitted
	$sql = mysqli_query($con,"SELECT * FROM results WHERE code='$code' AND sesh_submitted='$course_session' AND semester='$course_semester' AND department='$dept' AND view_stat='2'");
	if(mysqli_num_rows($sql) > 0){
		return encode(false, '<p>Result is already submitted!</p>');
	}
	//****Check if this lecturer owns this course
	$sql = mysqli_query($con, "SELECT * FROM courses WHERE assigned_to='$id' AND code='$code' AND sesh_assigned='$course_session' AND semester='$course_semester'");
	if(mysqli_num_rows($sql) < 0){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	//****Now check for missing scores and results
	$students_missing = array();
	//---First ge students who registered the course
	$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE code='$code' AND sesh='$course_session' AND semester='$course_semester' AND status='SUBMITTED'");
	if(mysqli_num_rows($sql) > 0){
		while($data = mysqli_fetch_assoc($sql)){
			$student_id = $data['student_id'];
			$course_sesh = mysqli_fetch_assoc(mysqli_query($con,"SELECT sesh FROM courses WHERE department='$dept' AND sesh_added='$course_session' AND semester='$course_semester' AND code='$code' AND assigned_to='$id' AND sesh_assigned='$course_session'"))['sesh'];
			$student_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE id='$student_id'"));
			$matric = $student_data['matric'];
			$yoe = $student_data['yoe'];
			$name = $student_data['name'];
			$submit_sheet = 0;
			$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND sesh='$course_sesh' AND sesh_submitted='$course_session' AND semester='$course_semester'");
			if(mysqli_num_rows($sql1) > 0){
				$data2 = mysqli_fetch_assoc($sql1);
				if($data2['exam'] < 1){
					$students_missing[count($students_missing)] = "Zero Exam: $name, $matric";
				}else if($data2['ca'] < 1) {
					$students_missing[count($students_missing)] = "Zero CA: $name, $matric";
				}
			}else{
				$students_missing[count($students_missing)] = "Missing Result: $name, $matric";
			}
		}
		//If missing scores or results are found, return an error.
		if(count($students_missing) > 0 && !$confirm){
			$tmp = '';
			for($i = 0; $i < count($students_missing); $i++){
				$tmp = $tmp."<span>".$students_missing[$i]."</span><br>";
			}
			$tmp = $tmp."<p>Do you want to continue?</p><a class=\"btn btn-danger pull-right\" href=\"javascript:closeerror()\"><i class=\"fa fa-ban\"></i> Cancel</a><a class=\"btn btn-success pull-right mr-3\" href=\"javascript:submit_score_sheet('$code','$dept',1);closeerror()\"><i class=\"fa fa-check\"></i> Yes</a>";
			return json_encode(array('status'=>false,'error'=>$tmp,'dur'=>60000));
		}else if((count($students_missing) > 0 && $confirm) || count($students_missing)<1){
			$submit_sheet = 1;
		}
		//If everything goes well
		$sql = mysqli_query($con,"UPDATE results SET view_stat='2' WHERE code='$code' AND sesh_submitted='$course_session' AND semester='$course_semester' AND department='$dept'");
		$sql = mysqli_query($con, "UPDATE courses SET slowed=0 WHERE code='$code' AND sesh_added='$course_session' AND semester='$course_semester' AND department='$dept' AND slowed=1");
		$sql = mysqli_query($con, "UPDATE course_registered SET slowed=0 WHERE code='$code' AND sesh='$course_session' AND semester='$course_semester' AND department='$dept' AND slowed=1");
		$sql = mysqli_query($con, "INSERT INTO notifications (notification,not_for,view_stat,user_id) VALUES ('$code Results submitted for $dept by $lecturer_name','admin','1','1')");
		if(!$sql){ return encode(false, '<p>Try that again...'.mysqli_error($con).'</p>'); }
		return encode(true);
	}else{
		return encode(false, '<p>You can\'t submit an empty score sheet!</p>');
	}
}
function lecturer_dashboard(){
	global $con, $current_semester, $current_session;
	$id = cs('lecturer_id');
	if(!$id){return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	$sql = mysqli_query($con, "SELECT * FROM courses WHERE assigned_to='$id' AND ((sesh_assigned='$current_session' AND semester='$current_semester') OR slowed=1)");
		$graphical_data = array();
	if(mysqli_num_rows($sql) > 0){
		$t = '<section class="graphical_data"><h5 class="center">Graphical Data</h5>';
		$toggle_showing = 'show';
		$toggle_show = 'showing';
		while($data= mysqli_fetch_assoc($sql)){
			$code = $data['code'];
			$title = $data['title'];
			$box_id = str_replace(' ', '', $code);
			$slowed_course = ($data['slowed'] == 1)? '<i class="fa fa-spinner"></i> ':'';
			$t = $t."<div class=\"course_summary_graph\"><a class=\"btn btn-primary $toggle_show\" onclick=\"this.classList.toggle('showing')\" data-toggle=\"collapse\" href=\"#$code\">$slowed_course$code [$title]</a><div class=\"collapse $toggle_showing $box_id\" id=\"$code\"><div class=\"card card-body\"><div ><canvas class=\"graph\" id=\"$code-graph\"></canvas></div></div> </div> </div>";
			$toggle_showing = '';
			$toggle_show = '';
			//***For Canvas...
			$sql_X = mysqli_query($con, "SELECT DISTINCT sesh_submitted FROM results WHERE code='$code' ORDER BY sesh_submitted DESC");
			if(mysqli_num_rows($sql_X) > 0){
				$count = 0;
				while($data_X = mysqli_fetch_assoc($sql_X)){
					$sesh = $data_X['sesh_submitted'];
					$graphical_data[$code.'-graph'][0][$count] = [$sesh];
					$sql2 = mysqli_query($con, "SELECT * FROM results WHERE sesh_submitted='$sesh' AND code='$code'");
					$grades = array();
					$grade_datas = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$sesh'");
					while($grade_data = mysqli_fetch_assoc($grade_datas)){
						$grades[$grade_data['grade']] = $grade_data['gradepoints'];
					}
					$i = 0;
					$j = 0;
					while($data = mysqli_fetch_assoc($sql2)){
						$i = $i+($data['units']*$grades[$data['grade']]);
						$j = $j+$data['units'];
					}
					$graphical_data[$code.'-graph'][1][$count] = $i/$j;
					if($count == 4){ break; }
					$count++;
				}
			}else{
				$graphical_data[$code.'-graph'] = '<h5 class="center">No Graphical Data!</h5>';
			}
		}
		$t = $t.'</section>';
	}else{
		$t = '<section style="padding: 40px 0;" class="center"><h5>No Courses are Currently Assigned to You!</h5>';
	}
	$t = $t.'<section id="notifications" class="notifications"><h5 class="center"><i class="fa fa-bell"></i> Notifications</h5>';
	$sql = mysqli_query($con, "SELECT * FROM notifications WHERE view_stat=1 AND not_for='lecturer' AND user_id='$id'");
	if(mysqli_num_rows($sql) > 0){
		while($data = mysqli_fetch_assoc($sql)){
			$t = $t.'<li><a href="javascript:lecturer_updater('.$data['id'].');">'.$data['notification'].'</a></li>';
		}
	}else{
		$t = $t.'<h6 class="center">You Currently have no new Notifications</h6>';
	}
	$t = $t.'</section>';
	return json_encode(array('status' => true, 'markup' => $t, 'graph' => $graphical_data));
}
function not_updater(){
	global $con;
	$id = cs(e(cp('not_for')));
	$not_for = (str_replace('_id', '', e(cp('not_for'))) == 'user')? 'admin':'lecturer';
	$not_for = (str_replace('_id', '', e(cp('not_for'))) == 'student')? 'student':$not_for;
	if(cp('opt')){
		$not = e(cp('opt'));
		mysqli_query($con, "UPDATE notifications SET view_stat=2 WHERE id='$not' AND user_id='$id' AND view_stat=1");
	}
	$sql = mysqli_query($con, "SELECT * FROM notifications WHERE view_stat=1 AND not_for='$not_for' AND user_id='$id'");
	$not = mysqli_num_rows($sql);
	return json_encode(array('not' => $not));
}
function reset_student_account(){
	global $con;
	if(!cs('user_id')){return encode(false,'<p>INVALID CREDENTIALS</p>');}
	$id = e(cp('id'));
	$password = strtoupper(md5('password'));
	$sql = mysqli_query($con, "UPDATE students SET password='$password' WHERE id='$id'");
	if(!$sql){return encode(false,'<p>Try that again...</p>');}
	return encode(true,false,'<p>Account password has been set to "password" (without the quotes).</p>');
}
function direct_entry(){
	global $con,$current_session;
	$id=cs('user_id');
	if(!$id){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	$data = cp(['matric','dept','yoe']);
	if($data){
		$matric = $data['matric'];
		$dept = $data['dept'];
		$yoe = $data['yoe'];
		$password = strtoupper(md5('password'));
		$entry_year = (explode('/', $yoe)[0]+1).'/'.(explode('/', $yoe)[1]+1);
		$sql = mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'");
		if(mysqli_num_rows($sql) < 1){
			$sql = mysqli_query($con, "INSERT INTO students (password,matric,department,entry_year,yoe) VALUES ('$password','$matric','$dept','$entry_year','$yoe')");
		}
		if(!$sql){ return encode(false, '<p>An unknown error occurred, try again.</p>'); }
		return encode(true,false,'<p>The student has been added.<br>Can now login with Matric and "password" as password.</p>');
	}
	$t = ' <div class="container mainbody_form"> <form class=""
			action="javascript:direct_entry(\'add_student\')" id="direct_add_form" autocomplete="off"> <p
			class="info">Please Fill Out This Form</p> <input class="login_input"
			type="text" name="matric" placeholder="Matric" required/> <span
			class="footnote">(e.g 16ME2001)</span> <input list="departments" class="login_input mb-3"
			type="text" name="department" placeholder="Joining Department" required/><span class="footnote">The department the student will be joining</span>';
			$sql = mysqli_query($con, "SELECT * FROM departments");
			if(mysqli_num_rows($sql) > 0){
				$t = $t.'<datalist id="departments">';
					while($data=mysqli_fetch_assoc($sql)){
						$t=$t.'<option value="'.$data['dept'].'" />';
					}
				$t = $t.'</datalist>';
			}
			$t = $t.'<input type="text" placeholder="Joining Session" name="yoe" class="login_input" required /><span class="footnote">The session the student will be joining</span> <input type="submit" class="login_submit"
			name="submit" value="Add Student" required/> </form> </div> ';
	return encode(true,false,$t);
}
function record_activities(){
	global $con;
	if(!cs('user_id')){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	$t = cp(['code','title','state','unit']);
	if(cp('opt') == 'course_add' && $t){
		$code = $t['code'];
		$title = $t['title'];
		$state = $t['state'];
		$unit = $t['unit'];
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses WHERE code='$code'")) > 0){
			$error = '<p>'.$code.' is already added.</p>';
			return encode(false, $error);
		}else if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses WHERE title='$title'")) > 0){
			$error = '<p>Another course is titled <i style="color: green; text-transform: uppercase;">'.$title.'</i></p>';
			return encode(false, $error);
		}
		$sql = "INSERT INTO record_courses (code,title,unit,state) VALUES ('$code','$title','$unit','$state')";
		$q = mysqli_query($con, $sql);
		if(!$q){
			$error = "<p>An unknown error is keeping you from adding a course at this time.<br> Try again later</p>";
			return encode(false, $error);
		}
		return encode(true);
	}
	$t = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_data"));
		$yoe = $t['record_for'];
		$dept = $t['record_department'];
	$grades = array();
		$sql = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$yoe'");
		while($data = mysqli_fetch_assoc($sql)){
			if($data['minimum_score'] == '-'){ continue; }
			$grades[count($grades)] = [$data['grade'],$data['minimum_score']];
		}
		for($i = 0; $i < count($grades); $i++){
			$minimum = $grades[$i][1];
			$max = 100;
			for($j = 0; $j < count($grades); $j++){
				if($grades[$j][1] < $max && $minimum < $grades[$j][1]){
					$max = $grades[$j][1]-1;
				}
			}
			$grades[$i][2] = $max;
		}
	$t = cp(['id']);
	if(cp('opt') == 'course_state_change' && $t){
		$id = $t['id'];
		$state = mysqli_fetch_assoc(mysqli_query($con, "SELECT state FROM record_courses WHERE id='$id'"))['state'];
		$q = ($state == 'CORE')? mysqli_query($con, "UPDATE record_courses SET state='ELECTIVE' WHERE id='$id'"):mysqli_query($con, "UPDATE record_courses SET state='CORE' WHERE id='$id'");
		if(!$q){ return encode(false, '<p>Your last request failed.<br>Try again later.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'delete' && $t){
		$id = $t['id'];
		$sql =  mysqli_query($con, "DELETE FROM record_courses WHERE id='$id'");
		if(!$sql){ return encode(false, '<p>Your last request failed.<br>Try again later.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'continue_courses'){
		$no = mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses"));
		if($no < 1){
			return encode(false, '<p>You have to add at least 1 course before continuing.</p>');
		}
		$sql = mysqli_query($con, "UPDATE record_data SET record_stage=2 WHERE 1");
		if(!$sql){ return encode(false, '<p>Try that again.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'course_reg_win' && cp('matric')){
		$matric = cp('matric');
		$sql = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric' ORDER BY code");
		$cutil = array();
		$t = "<h5>$matric's registered courses</h5><table class=\"table hover\">
		<thead> <tr> <th>#</th><th scope=\"col\">Code</th> <th scope=\"col\">Title</th>
		<th scope=\"col\">Unit</th><th
		scope=\"col\">Action</th> </tr> </thead><tbody>";
		$count = 1;
		$total = 0;
		while($data = mysqli_fetch_assoc($sql)){
			$cutil[count($cutil)] = $data['code'];
			$total = $total+$data['unit'];
			$t = $t.'<tr><td>'.$count.'</td><td>'.$data['code'].'</td><td>'.$data['title'].'</td><td>'.$data['unit'].'</td><td><a style="color:white;" class="btn btn-default" href="javascript:record_course_reg([\''.$matric.'\',\'delete\','.$data['id'].'])"><i class="fa fa-trash"></i></a></td></tr>';
			$count++;
		}
		$t = $t.'<tr><td class="center" colspan=3 style="font-weight:bolder;font-size:1.5em;">TOTAL</td><td colspan=2 style="font-weight:bolder;font-size:1.5em;">'.$total.'</td></tr>';
		$sql = mysqli_query($con, "SELECT * FROM record_courses ORDER BY code");
		while($data = mysqli_fetch_assoc($sql)){
			if(has_element($data['code'], $cutil)){ continue; }
			$cutil[count($cutil)] = $data['code'];
			$t = $t.'<tr><td>'.$count.'</td><td>'.$data['code'].'</td><td>'.$data['title'].'</td><td>'.$data['unit'].'</td><td><a style="color:white;" class="btn btn-default" href="javascript:record_course_reg([\''.$matric.'\',\'add\',\''.$data['code'].'\',\''.$data['title'].'\',\''.$data['unit'].'-'.$data['sesh'].'\'])"><i class="fa fa-plus"></i></a></td></tr>';
			$count++;
		}
			$yor = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_year FROM record_data"))['record_year'];
			$sem = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_semester FROM record_data"))['record_semester'];
			$dept = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_department FROM record_data"))['record_department'];
			$yoe = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_for FROM record_data"))['record_for'];
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND semester='$sem' AND sesh>'$yoe' AND sesh_added='$yor'");
		while($data = mysqli_fetch_assoc($sql)){
			if(has_element($data['code'], $cutil)){ continue; }
			$cutil[count($cutil)] = $data['code'];
			$t = $t.'<tr><td>'.$count.'</td><td>'.$data['code'].'</td><td>'.$data['title'].'</td><td>'.$data['unit'].'</td><td><a style="color:white;" class="btn btn-default" href="javascript:record_course_reg([\''.$matric.'\',\'add\',\''.$data['code'].'\',\''.$data['title'].'\',\''.$data['unit'].'-'.$data['sesh'].'\'])"><i class="fa fa-plus"></i></a></td></tr>';
			$count++;
		}
		$t = $t.'</table>';
		return encode(true,false,$t);
	}else if(cp('opt') == 'course_reg_add' && cp(['matric','unit','title','code'])){
		$t = cp(['matric','unit','title','code']);
		$matric = $t['matric'];
		$unit = explode('-',$t['unit'])[0];
		$sesh = explode('-',$t['unit'])[1];
		$title = $t['title'];
		$code = $t['code'];
		$student_id = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'"))['id'];
		$sql = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric' AND code='$code'");
		if(mysqli_num_rows($sql) > 0){ return encode(true); }
		$sql = mysqli_query($con, "INSERT INTO record_courses_registered (code,title,unit,student_id,matric,sesh) VALUES ('$code','$title','$unit','$student_id','$matric','$sesh')");
		if(!$sql){ return encode(false, '<p>We have encountered a problem.<br>Please retry.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'course_reg_delete' && cp(['matric','id'])){
		$t = cp(['matric','id']);
		$matric = $t['matric'];
		$id = $t['id'];
		$sql = mysqli_query($con, "DELETE FROM record_courses_registered WHERE id='$id' AND matric='$matric'");
		if(!$sql){ return encode(false, '<p>We have encountered a problem.<br>Please retry.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'continue_course_reg'){
		$yor = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_year FROM record_data"))['record_year'];
			$sem = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_semester FROM record_data"))['record_semester'];
			$dept = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_department FROM record_data"))['record_department'];
			$yoe = mysqli_fetch_assoc(mysqli_query($con, "SELECT record_for FROM record_data"))['record_for'];
		$sql = mysqli_query($con, "SELECT * FROM students WHERE department='$dept' AND yoe='$yoe' ORDER BY matric");
		$error = '';
		$register_count = 0;
		$under_count = 0;
		while($data = mysqli_fetch_assoc($sql)){
			$matric = $data['matric'];
			$error = $error."<p> $matric ";
			if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric'")) < 1){
				$error = $error.'has no registered courses and';
				$register_count++;
			}
			$units = 0;
			$sql1 = mysqli_query($con,"SELECT * FROM record_courses_registered WHERE matric='$matric'");
			while($data = mysqli_fetch_assoc($sql1)){
				$units = $units+$data['unit'];
			}
			if($units < 18){ $error = $error.' is under registered'; $under_count++; }
			$error = $error.'</p>';
		}
		if($error != '' && !cp('continue')){
			if($register_count > 4 || $under_count > 4){
				$error = "<p>There are $register_count cases of No registered course and $under_count cases of under registered students.</p>";
			}
			$error = $error.'<p>Do you want to cotinue with these errors?</p><a class="btn btn-danger pull-right" href="javascript:app.error.close();"><i class="fa fa-ban"></i> No</a><a class="btn btn-success pull-right mr-3" href="javascript:record_course_reg([\'continue\',true]);app.error.close()"><i class="fa fa-check"></i> Yes</a>';
			return encode(false, $error);
		}
		$sql = mysqli_query($con, "UPDATE record_data SET record_stage=3 WHERE 1");
		if(!$sql){ return encode(false, '<p>Try that again.</p>'); }
		return encode(true);
	}else if(cp('opt') == 'CA' && cp(['mat','code'])){
		$score = (!cp('score'))? '0':cp('score');
		$mat = cp('mat');
		$code = explode(' ! ',cp('code'))[0];
		$sesh = explode(' ! ',cp('code'))[1];
		if($score > 30 || !is_numeric($score)){
			return encode(false, '<p>CA can\'t be greater than 30');
		}
		$sql = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$mat' AND code='$code'");
		if(mysqli_num_rows($sql) > 0){
			$data = mysqli_fetch_assoc($sql);
			$exam = $data['exam'];
			$result_id = $data['id'];
			$total = $score+$exam;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($total <= $grades[$i][2] && $total >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "UPDATE record_results SET score='$total', grade='$grade', ca='$score' WHERE id='$result_id'");
		}else{
			$course_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_courses WHERE code='$code'"));
			$course_data2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND code='$code' AND sesh='$sesh'"));
			$course_title = ($course_data['title'])? $course_data['title']:$course_data2['title'];
			$course_unit = ($course_data['unit'])? $course_data['unit']:$course_data2['unit'];
			$exam = '0';
			$total = $score+$exam;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($score <= $grades[$i][2] && $score >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "INSERT INTO record_results (title,code,unit,score,grade,matric,exam,ca,sesh) VALUES ('$course_title','$code','$course_unit','$total','$grade','$mat','$exam','$score','$sesh')");
		}
		if(!$sql){ encode(false, "<p>Try entering that value again, didn't work that time.</p>"); }
		return json_encode(array('status'=>true,'grade'=>$grade,'remark'=>$remark,'total'=>$total));
	}else if(cp('opt') == 'EXAM' && cp(['mat','code'])){
		$score = (!cp('score'))? '0':cp('score');
		$mat = cp('mat');
		$code = explode(' ! ',cp('code'))[0];
		$sesh = explode(' ! ',cp('code'))[1];
		if($score > 70 || !is_numeric($score)){
			return encode(false, '<p>EXAM can\'t be greater than 70');
		}
		$sql = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$mat' AND code='$code'");
		if(mysqli_num_rows($sql) > 0){
			$data = mysqli_fetch_assoc($sql);
			$ca = $data['ca'];
			$result_id = $data['id'];
			$total = $score+$ca;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($total <= $grades[$i][2] && $total >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "UPDATE record_results SET score='$total', grade='$grade', exam='$score' WHERE id='$result_id'");
		}else{
			$course_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_courses WHERE code='$code'"));
			$course_data2 = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND sesh='$sesh'"));
			$course_title = ($course_data['title'])? $course_data['title']:$course_data2['title'];
			$course_unit = ($course_data['unit'])? $course_data['unit']:$course_data2['unit'];
			$ca = '0';
			$total = $score+$ca;
			$grade = '';
			for($i = 0; $i < count($grades); $i++){
				if($score <= $grades[$i][2] && $score >= $grades[$i][1]){
					$grade = $grades[$i][0];
				}
			}
			$remark = ($grade == 'F')? 'Failed':'Passed';
			$sql = mysqli_query($con, "INSERT INTO record_results (title,code,unit,score,grade,matric,exam,ca,sesh) VALUES ('$course_title','$code','$course_unit','$total','$grade','$mat','$score','$ca','$sesh')");
		}
		if(!$sql){ encode(false, "<p>Try entering that value again, didn't work that time.</p>"); }
		return json_encode(array('status'=>true,'grade'=>$grade,'remark'=>$remark,'total'=>$total));
	}else if(cp('opt') == 'continue_result'){
		$sql = mysqli_query($con, "SELECT DISTINCT matric FROM record_courses_registered ORDER BY matric");
		$error = '<p>';
		$count = 0;
		$mats = array();
		while($data = mysqli_fetch_assoc($sql)){
			$mat = $data['matric'];
			$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$mat'");
			while($data = mysqli_fetch_assoc($sql1)){
				$code = $data['code'];
				$ca = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM record_results WHERE matric='$mat' AND code='$code'"))['ca'];
				$exam = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM record_results WHERE matric='$mat' AND code='$code'"))['exam'];
				$expand = false;
				if(!$ca || !$exam){ $error =$error."$mat is missing result for $code. CA:$ca, EXAM:$exam<br>"; $count++; $mats[count($mats)] = $mat;}
			}
		}
		$mats = implode(',', $mats);
		if($count>2){$error = "There are $count cases of missing results. $mats";}
		$error = $error.'</p>Do you want to continue with this errors?<br><a class="btn btn-danger pull-right" href="javascript:app.error.close();"><i class="fa fa-ban"></i> No</a><a class="btn btn-success pull-right mr-3" href="javascript:record_result(true);app.error.close()"><i class="fa fa-check"></i> Yes</a>';
		if($count && !cp('continue')){return encode(false,$error);}
		$sql = mysqli_query($con, "UPDATE record_data SET record_stage=4 WHERE 1");
		if(!$sql){ return encode(false, '<p>Try that again.</p>'); }
		return encode(true);
	}
	return encode(false, '<p>Din\'t understand your request that time.</p>');
}
function copy_paste($p_table, $c_cols, $p_cols, $c_sql){
	global $con;
	if(!cs('user_id')){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	if(!is_array($c_cols) || !is_array($p_cols)){ return false; }
	if(count($c_cols) < count($p_cols)){return false; }
	$ins = true;
	$check = '';
	$p_col = implode(',', $p_cols);
	while($data = mysqli_fetch_assoc($c_sql)){
		$values = array();
		$vals = array();
		for($i = 0; $i < count($c_cols); $i++){
			$values[count($values)] = "'".$data[$c_cols[$i]]."'";
			$vals[count($vals)] = $p_cols[$i]."='".$data[$c_cols[$i]]."'";
		}
		$check = "SELECT * FROM $p_table WHERE ".implode(' AND ', $vals);
		if(mysqli_num_rows(mysqli_query($con, $check))){ continue; }
		$check = mysqli_query($con, "INSERT INTO $p_table ($p_col) VALUES (".implode(',',$values).")");
	}
	if(!$check){ return false; }
	return true;
}
function account_settings(){
	global $con;
	if(cs('user_id')){
		$id = cs('user_id');
		if(cp('image') && cp('option') == 'prf_image'){
			$data = cp('image');
			$image_array_1 = explode(';', $data);
			$image_array_2=explode(',',$image_array_1[1]);
			$data = base64_decode($image_array_2[1]);
			$image_name = strtoupper(md5(date('YMDhis')).'.png');
			file_put_contents('images/'.$image_name, $data);
			mysqli_query($con, "UPDATE `admin` SET `image`='$image_name' WHERE id=$id");
			exit();
		}
		if(cp('password')){
			$messages = array();
			$password = strtoupper(md5(cp('password')));
			$sql = mysqli_query($con, "SELECT * FROM `admin` WHERE id=$id AND `password`='$password'");
			if(!mysqli_num_rows($sql)){
				$messages[count($messages)] = "text-danger;Wrong password.";
			}else{
				if(cp('name')){
					$name = cp('name');
					if(empty($name)){
						$messages[count($messages)] = 'text-danger;Name Can\'t be empty';
					}else if(count(explode(' ',$name)) < 2){
						$messages[count($messages)] = 'text-danger;Your Fullname please...';
					}else if(strlen(explode(' ', $name)[0])<2 || strlen(explode(' ', $name)[1])<2){
						$messages[count($messages)] = 'text-danger;Name and surname must be more than 3 characters long and initials should come at the end. E.g. Dr. John Doe S.';
					}else{
						mysqli_query($con, "UPDATE `admin` SET name='$name' WHERE id=$id");
						$messages[count($messages)] = 'text-success;Name changed successfully!';
					}
				}
			}
			return json_encode(array('status'=>true,'message'=>implode(',',$messages)));
			exit();
		}
		$info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `admin` WHERE id=$id"));
		$username = $info['username'];
		$name = $info['name'];
		$email = $info['email'];
		$email = ($email)? $email:"Not Set";
		$image = ($info['image'])? $info['image']:"default.png";
		$t='
			<div class="account_settings mb-3">
				<div class="prf-img"><label style="display:block;" for="upload_image_croppie"><img src="../res/images/'.$image.'" title="click to edit"></label><input id="upload_image_croppie" name="upload_image_croppie" type="file" class="d-none"></div>
				<form autocomplete="off" class="mt-3 login_form" title="click on fields to change data" id="account_settings_form" action="javascript:account_settings(\'save_changes\');">
					<div class="form_messages"></div>
					<input class="form-control" type="password" placeholder="Password"  id="account_settings_password" />
					<span class="text-muted">please provide a valid password above, it will be needed for any operation you perform below</span>	
					<input type="text" placeholder="Name: '.$name.'" name="name" class="form-control mb-3" />
					<input type="email" placeholder="Email: '.$email.'" name="email" class="form-control mb-3" />
					<input type="text" placeholder="Username: '.$username.'" name="username" class="form-control mb-3" />
					<input type="password" placeholder="New Password" name="password" class="form-control mb-3" />
					<input type="password" placeholder="Confrim New Password" name="cpassword" class="form-control mb-3" />
					<input type="submit" class="btn btn-primary" value="Save Changes" />
				</form>
			</div>
		';
		return encode(true,false,$t);
		exit();
	}
	return encode(false,"<p>INVALID CREDENTIALS</p>");
	exit();
}


?>