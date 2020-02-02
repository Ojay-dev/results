<?php

function enumWrap($html, $arr){
	$tmp = '';
	for($i =0; $i<count($arr); $i++){
		$tmp = $tmp.str_replace('.data.', $arr[$i], $html);
	}
	return $tmp;
}
function student_courses(){
	global $con,$current_session,$current_semester;
	$t = cp(['department','semester','sesh']);
	if(cs('student_id') && $t){
		$d = cs('dept');
		$s = e($t['semester']);
		$semester = $s;
		$se = e($t['sesh']);
		$student_id = cs('student_id');
		$matric = cs('mat');
		$yoe = cs('yoe');
		$entry_year = cs('entry_year');
		$l = getLevel(str_replace('/', '', $yoe));
		if($entry_year > $yoe && calcLevelFromSesh($yoe,$se)==100){
			return encode(true, false, '<h5 style="margin-top: 5em;" class="center">Direct Entry Students Don\'t Have a 100 Level</h5>');
		}
		$t = '<h5 style="margin-top: 2em;">'.calcLevelFromSesh($yoe,$se).' Level '.$s.' Semester Course Registration</h5>';
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE sesh='$se' AND semester='$s' AND student_id='$student_id' AND (NOT status='NOT SUBMITTED')");
		if(mysqli_num_rows($sql) < 1 && $s == $current_semester && $se == $current_session){
			$t = $t.'<form autocomplete="off" class="mb-3" id="courseadd_form" action="javascript:courses_register([\'add\'])"><div class="form-row"><div class="col-sm-6 mb-3 mr-2"><input type="text" list="lecturers" name="course" placeholder="Course" class="form-control"/>';
			$sql = mysqli_query($con, "SELECT * FROM courses WHERE department='$d' AND semester='$s' AND sesh_added='$se' AND sesh>='$yoe' ORDER BY sesh_added,code");
			if(mysqli_num_rows($sql) > 0){
				$rems = array();
				/**Courses Failed**/
					$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$se'");
					while($data=mysqli_fetch_assoc($sql1)){
						$code=$data['code'];
						if($data['sesh_submitted'] == $se && $data['semester'] >= $semester){
							continue;
						}
						$check = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND (NOT grade='F')");
						$state_check = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND state='CORE' AND sesh='$yoe'");
						if(!mysqli_num_rows($state_check)){
							continue;
						}
						if(!mysqli_num_rows($check) && !has_element($data['code'],$rems)){
							$rems[count($rems)] = $data['code'];
						}
					}
				/**Course Not registered**/
					$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE state='CORE' AND sesh_added<='$se' AND sesh='$yoe'");
					while($data = mysqli_fetch_assoc($sql1)){
						$code = $data['code'];
						if($data['sesh_added'] == $se && $data['semester'] >= $current_semester){
							continue;
						}
						$check = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND code='$code'");
						$entry_year = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM students WHERE matric='$matric'"))['entry_year'];
						if($entry_year>$data['sesh_added']){
							continue;
						}
						if(!mysqli_num_rows($check) && !has_element($code, $rems)){
							$rems[count($rems)] = $code;
						}
					}
				$t = $t.'<datalist id="lecturers">';
				while($data = mysqli_fetch_assoc($sql)){
					$c = $data['code']; $ti = $data['title']; $units = $data['unit'];
					if($data['sesh']>$yoe && !has_element($data['code'],$rems)){continue;}
					if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM course_registered WHERE semester='$s' AND  sesh='$se' AND code='$c' AND title='$ti' AND student_id='$student_id' AND unit='$units'"))>0){ continue; }
					$t = $t.'<option value="'.$data['code'].' ['.$data['title'].']" />';
				}
				$t = $t.'</datalist>';
			}
			$t = $t.'</div><div class="col-sm-3"><input type="submit" class="btn btn-primary mb-3" name="submit" value="Register Course"/></div></div></form>';
		}
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE semester='$s' AND  sesh='$se' AND student_id='$student_id' ORDER BY code");
		$count = 1;
		$total = 0;
		$t=$t.'<table class="table hover"> <thead> <tr> <th>#</th><th scope="col">Code</th> <th
		scope="col">Title</th> <th scope="col">Unit</th><th scope="col">Remove</th> </tr> </thead><tbody>';
		if(mysqli_num_rows($sql) > 0){
			while($data = mysqli_fetch_assoc($sql)){
				$t = $t.'<tr> <td>'.$count.'</td><td>'.$data['code'].'</td> <td>'.$data['title'].'</td>
				<td>'.$data['unit'].'</td><td><a  class="trash"
				href="javascript:courses_register([\'delete\','.$data['id'].'])"><i class="fa
				fa-trash"></i></a></td> </tr>';
				$count++;
				$total = $total+$data['unit'];
			}
			$t = $t.'<tr><td></td><td></td><td class="center"> Total</td><td>'.$total.'</td><td></td></tr>';
		}
		$t = $t.'</tbody></table>';
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE sesh='$se' AND semester='$s' AND student_id='$student_id' AND (NOT status='NOT SUBMITTED')");
		if(mysqli_num_rows($sql) < 1 && $s == $current_semester && $se == $current_session){
			$t = $t.'<a href="javascript:courses_register([\'save\'])" class="btn btn-primary">Submit</a>';
		}else{
			$t = $t.'<a href="javascript:void(0)" class="btn btn-success disabled">Submitted</a>';
		}
		return encode(true,false,$t);
	}else{
		$t = '<p>INVALID CREDENTIALS</p>';
	}
	return encode(false,$t);
}
function student_register_course(){
	global $con,$current_session;
	$t = cp(['course','sesh','semester']);
	if(!cs('student_id')){
		return encode(false, '<p>INVALID CREDENTIALS</p>');
	}
	if(cp('action') == 'add' && $t){
		$c = e(explode(' [',$t['course'])[0]);
		$se = e($t['sesh']);
		$s = e($t['semester']);
		$d = cs('dept');
		$yoe = cs('yoe');
		$mat = cs('mat');
		$l = calcLevelFromSesh($yoe, $current_session);
		$l = ($l)? $l:"Graduate";
		$id = cs('student_id');
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE department='$d' AND code='$c' AND semester='$s' AND sesh_added='$se' AND sesh>='$yoe'");
		if(mysqli_num_rows($sql)<1){
			return encode(false, "<p>You can't register $c</p>");
		}
		$data = mysqli_fetch_assoc($sql);
		$ti = $data['title'];
		$unit = $data['unit'];
		$yoe = $data['sesh'];
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE semester='$s' AND  sesh='$se' AND student_id='$id' AND (NOT status='NOT SUBMITTED')");
		if(mysqli_num_rows($sql) > 0){
			return encode(false, "<p>You have already submitted this course form, changes can't be made to it.</p>");
		}
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE semester='$s' AND  sesh='$se' AND code='$c' AND title='$ti' AND student_id='$id' AND unit='$unit' AND yoe='$yoe'");
		if(mysqli_num_rows($sql) > 0){
			return encode(false,"<p>$c is already registered</p>");
		}
		if($l == "Graduate"){
			return encode(false, "<p>Meet the admin..</p>");
		}else{
			$sql = mysqli_query($con, "SELECT * FROM levels WHERE level>='$l'");
			$state = 27;
			if(mysqli_num_rows($sql) > 1){
				$state = 24;
			}
		}
		if($state == 24){
			$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$id' AND status='NOT SUBMITTED'");
			$units = 0;
			while($data=mysqli_fetch_assoc($sql)){ $units = $units+$data['unit']; } if(($unit+$units) > $state){
				return encode(false,'<p>Maximum credit load reached!</p>');
				exit();
			}
		}else if($state == 27){
			if($s == '1ST'){
				$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$id' AND status='NOT SUBMITTED'");
				$units = 0;
				while($data=mysqli_fetch_assoc($sql)){ $units = $units+$data['unit']; } if(($unit+$units) > $state){
					return encode(false,"<p>You can't go above $state points!</p>");
					exit();
				}
			}else if($s == '2ND'){
				$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$id' AND semester='1ST' AND sesh='$se'");
				$units =0;
				while($data=mysqli_fetch_assoc($sql)){ $units = $units+$data['unit']; } if(($unit+$units) > $state){
					$state = 24;
				}
				$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$id' AND status='NOT SUBMITTED'");
				$units = 0;
				while($data=mysqli_fetch_assoc($sql)){ $units = $units+$data['unit']; } if(($unit+$units) > $state && $state == 27){
					return encode(false,"<p>You can't go above $state points.</p>");
					exit();
				}else if($state == 24){ return encode(false, "<p>You can't go above $state points.<br>You did it last semester.</p>"); exit(); }
			}	
		}
		$sql = mysqli_query($con, "INSERT INTO course_registered (yoe,code,title,student_id,unit,sesh,semester,matric,department) VALUES ('$yoe','$c','$ti','$id','$unit','$se','$s','$mat','$d')");
		if(!$sql){ return encode(false, '<p>An unknown ERROR has occurred.</p>'); }
		return encode(true,false,true);
	}
	$t = cp('course');
	if(cp('action') == 'delete' && $t){
		$id = e($t);
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE id='$id' AND (NOT status='NOT SUBMITTED')");
		if(mysqli_num_rows($sql) > 0){
			return encode(false, "<p>You have already submitted this course form, changes can't be made to it.</p>");
		}
		$sql = mysqli_query($con, "DELETE FROM course_registered WHERE id='$id'");
		if(!$sql){ return encode(false, "<p>An unknown ERROR occurred.</p>"); }
		return encode(true);
	}
	if(cp('action') == 'save'){
		$id = cs('student_id');
		$sql = mysqli_query($con, "SELECT * FROM course_registered WHERE student_id='$id' AND status='NOT SUBMITTED'");
		$units = 0;
		while($data=mysqli_fetch_assoc($sql)){ $units = $units+$data['unit']; } if($units < 18){
			return encode(false,'<p>You can\'t register below 18 units!</p>');
			exit();
		}
		$sql = mysqli_query($con, "UPDATE course_registered SET status='SUBMITTED' WHERE student_id='$id'");
		if(!$sql){ return encode(false, "<p>Try that again, it seems to have failed that time.</p>"); }
		return encode(true);
	}
}
function calcLevelFromSesh($baseSesh, $sesh){
	global $con;
	$cur = $baseSesh;
	$current_session = str_replace('/', '', $sesh);
	$target_session = str_replace('/', '', $cur);
	$numberOfLevels = (($current_session-$target_session)/10001)+1;
	$level = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM levels WHERE id='$numberOfLevels'"))['level'];
	return $level;
}
function student_levels(){
	global $con,$current_session;
	$t = cp(['option','js','sesh']);
	if(!$t){
		return json_encode(array('markup' => '<h5 style="margin-top: 5em; text-align: center;">An Error Occurred</h5>'));
	}
	$option = $t['option'];
	$js = $t['js'];
	$cur = $t['sesh'];
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
	return json_encode(array('status' => true,'markup' => $t));
}
function student_result(){
	global $con,$current_session,$current_semester;
	if(!cs('student_id')){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	$matric = cs('mat');
	$yoe = cs('yoe');
	$semester = $current_semester;
	if(cp('opt') == 'full'){
		$t = cp(['semester','sesh']);
			$semester = $t['semester'];
			$dept = cs('dept');
			$level = $t['sesh'];
			$matric = cs('mat');
			$sesh = cs('yoe');
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND semester='$semester' AND sesh_submitted='$level' AND view_stat>2")) > 0){
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
				$sql = mysqli_query($con, "SELECT * FROM students WHERE matric='$matric' AND yoe='$sesh' ORDER BY matric");
				$count = 1;
				while($data = mysqli_fetch_assoc($sql)){
					$matric = $data['matric'];
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
								$check = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric'  AND sesh_submitted<='$level' AND code='$code' AND (NOT grade='F')");
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
		}else{ return encode(false,'<p>No result!</p>'); }
			return encode(true,false,$t);
	}else if(cp('opt') == 'carryovers'){
		$rems = array();
		/**Courses Failed**/
			$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$current_session'");
			while($data=mysqli_fetch_assoc($sql1)){
				$code=$data['code'];
				if($data['sesh_submitted'] == $current_session && $data['semester'] >= $semester){
					continue;
				}
				$check = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND (NOT grade='F')");
				$state_check = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND state='CORE' AND sesh='$yoe'");
				if(!mysqli_num_rows($state_check)){
					continue;
				}
				if(!mysqli_num_rows($check) && !has_element($data['code'],$rems)){
					$rems[count($rems)] = str_replace(' ', '', $data['code']);
				}
			}
		/**Course Not registered**/
			$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE state='CORE' AND sesh_added<='$current_session' AND sesh='$yoe'");
			while($data = mysqli_fetch_assoc($sql1)){
				$code = $data['code'];
				if($data['sesh_added'] == $current_session && $data['semester'] >= $current_semester){
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
		$rems = (count($rems)<1)? 'Passed':$rems;
		if($rems == 'Passed'){
			return encode(true, false, "<h5 style=\"margin-top: 5em;\" class=\"center\">Nothing Here! You're doing a great Job.</h5>");
			exit();
		}
		$btn = enumWrap("<a href=\"javascript:void(0)\" class=\"btn btn-danger disabled mr-3 mb-2\"><b>.data.</b></a>",$rems);
		$t = "<h5 style=\"margin-top: 5em;\" class=\"center\">You have the following scores to settle</h5><div class=\"center\">$btn</div>";
		return encode(true, false, $t);
	}else{
		$t = '<div class="result_btns"><center>
		<a class="btn btn-primary mr-3" href="javascript:init_results(\'carryovers\')">Carryovers <i class="fa fa-long-arrow-right"></i></a><a class="btn btn-primary" href="javascript:init_results()">Full Results <i class="fa fa-long-arrow-right"></i></a>
		</center></div>';
		return encode(true,false,$t);
	}
}
function previous_records(){
	global $con,$current_session,$current_semester;
	if(!cs('user_id')){ return encode(false, '<p>INVALID CREDENTIALS</p>'); }
	function destroy_record_session(){
		global $con;
		$sql = mysqli_query($con, "DROP TABLE record_data,record_courses_registered,record_courses,record_results");
		return encode(true);
	}
	$t = mysqli_query($con, "SELECT * FROM record_data");
	if($t){
		$data = mysqli_fetch_assoc($t);
			$yoe = $data['record_for'];
			$yor = $data['record_year'];
			$dept = $data['record_department'];
			$sem = $data['record_semester'];
			$stage = $data['record_stage'];
		$grades = array();
			$sql = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$yoe'");
			while($data = mysqli_fetch_assoc($sql)){
				if($data['minimum_score'] == '-'){ continue; }
				$grades[$data['grade']] = $data['gradepoints'];
			}
		if(cp('save_entry') && $stage == 4){
			$copy = copy_paste('results',['code','title','unit','score','grade','name','matric','exam','ca','sesh','record_semester','record_department','record_for','record_year'],['code','course','units','score','grade','name','matric','exam','ca','sesh','semester','department','yoe','sesh_submitted'],mysqli_query($con, "SELECT * FROM `record_results`,`record_data`"));
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "UPDATE results SET view_stat=3 WHERE yoe='$yoe' AND sesh_submitted='$yor' AND department='$dept'");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "DROP TABLE record_results");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
			$copy = copy_paste('course_registered',['code','title','unit','student_id','matric','sesh','record_semester','record_department','record_year'],['code','title','unit','student_id','matric','yoe','semester','department','sesh'],mysqli_query($con, "SELECT * FROM `record_courses_registered`,`record_data`"));
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "UPDATE course_registered SET slowed=0,status='SUBMITTED' WHERE sesh='$yor' AND department='$dept'");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "DROP TABLE record_courses_registered");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
			$copy = copy_paste('courses',['code','title','unit','state','sesh','record_semester','record_department','record_year'],['code','title','unit','state','sesh','semester','department','sesh_added'],mysqli_query($con, "SELECT * FROM `record_courses`,`record_data`"));
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "UPDATE courses SET slowed=0 WHERE sesh_added='$yor' AND department='$dept' AND sesh='$yoe'");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
				$copy = mysqli_query($con, "DROP TABLE record_courses");
				if(!$copy){ return encode(false, "<p>Try again</p>"); }
			return destroy_record_session();
		}
		if($stage == 1){
			$sql = mysqli_query($con, "
				CREATE TABLE IF NOT EXISTS record_courses(
					id int(11) AUTO_INCREMENT PRIMARY KEY,
					code varchar(255),
					title varchar(255),
					unit varchar(255),
					state varchar(255),
					sesh varchar(255)
				);
			");
			if(mysqli_num_rows(mysqli_query($con,"SELECT * FROM record_courses"))<1){copy_paste('record_courses',['code','title','unit','state','sesh'],['code','title','unit','state','sesh'],mysqli_query($con, "SELECT * FROM courses WHERE sesh='$yoe' AND department='$dept' AND semester='$sem' AND sesh_added='$yor'"));}
			$sql = mysqli_query($con, "SELECT * FROM record_courses ORDER BY code");
			$count = 1;
			$l = calcLevelFromSesh($yoe,$yor);
			$t = '<h5>'.$dept.' '.$yoe.' ['.$l.' Level] '.$sem.'Semester Courses</h5><table
			class="table hover"> <thead> <tr> <th>#</th><th scope="col">Code</th> <th
			scope="col">Title</th> <th scope="col">Unit</th> <th scope="col">Course
			State</th><th scope="col">Delete</th> </tr> </thead><tbody>';
			if(mysqli_num_rows($sql) > 0){
				while($data = mysqli_fetch_assoc($sql)){
					$t = $t.'<tr> <td>'.$count.'</td><td>'.$data['code'].'</td> <td>'.$data['title'].'</td>
					<td>'.$data['unit'].'</td><td>'.$data['state'].'<a style="color:red;" class="btn btn-default" href="javascript:record_course_add([\'change_state\','.$data['id'].'])">change</a></td><td><a  class="trash"
					href="javascript:record_course_add([\'delete\','.$data['id'].'])"><i class="fa
					fa-trash"></i></a></td> </tr>';
					$count++;
				}
			}
			$t = $t.'</tbody></table><form autocomplete="off" class="form-inline mb-3" id="record_courseadd_form" action="javascript:record_course_add()"> <div class="form-group mb-3 mr-1"><input type="text" placeholder="Code" class="form-control" name="code" required/></div><div class="form-group mb-3 mr-1"><input type="text" class="form-control" placeholder="Title" name="title" required/></div><div class="form-group mb-3 mr-1"><input placeholder="Unit" type="number" name="unit" required class="form-control"/></div><div class="form-group mb-3 mr-1"><input type="text" list="lecturers" name="state" placeholder="Course State" class="form-control" required/><datalist id="lecturers"><option value="CORE" /><option value="ELECTIVE" /></datalist></div><input type="submit" class="btn btn-primary mb-3" name="submit" value="Add Course"/></form><a class="btn btn-primary mr-3" href="javascript:record_course_add(\'continue\')">Continue</a><a class="btn btn-danger" href="javascript:previous_records(\'destroy\')">Destroy Record</a>';
			return encode(true,false,$t);
		}else if($stage == 2){
			$sql = mysqli_query($con, "
				CREATE TABLE IF NOT EXISTS record_courses_registered(
					id int(11) AUTO_INCREMENT PRIMARY KEY,
					code varchar(255),
					title varchar(255),
					unit varchar(255),
					student_id varchar(255),
					matric varchar(255),
					sesh varchar(255)
				);
			");
			$sql = mysqli_query($con, "SELECT * FROM students WHERE department='$dept' AND entry_year<='$yor' ORDER BY matric");
			$l = calcLevelFromSesh($yoe,$yor);
			if(mysqli_num_rows($sql) > 0){
				$t = '<div class="container"><h5>'.$yoe.' '.$dept.' '.$l.'Level '.$sem.' Semester Course Registration</h5><ul style="margin-top: 2em;" class="tiles">';
				while($data = mysqli_fetch_assoc($sql)){
					$mat = $data['matric'];
					$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$mat'");
					$rem = '';
					if(mysqli_num_rows($sql1) < 1){ $rem = '<span style="color:red">*No registered courses </span>'; }
					$units = 0;
					while($data1 = mysqli_fetch_assoc($sql1)){
						$units = $units+$data1['unit'];
					}
					if($units<16){$rem=(isset($rem))? $rem.'<span style="color:red">*Under registered. </span>':'<span style="color:red">*Under registered. </span>';}
					$rem = ($rem == '')? '<i class="fa fa-check-circle" style="color:green"></i>':$rem;
					$t = $t.'<li><a href="javascript:record_course_reg(\''.$data['matric'].'\')">'.$data['matric'].' ['.$data['name'].'] '.$rem.'</a></li>';
				}
				$t = $t.'</ul><a class="btn btn-primary mr-3 mb-3" href="javascript:record_course_reg(\'continue\')">Continue</a><a class="btn btn-danger  mb-3" href="javascript:previous_records(\'destroy\')">Destroy Record</a></div>';
			}else{
				$t = "<h5 style=\"margin-top: 5em;\" class=\"center\">Add students for $dept $yoe from the add students menu option on the left to continue with the current record.</h5><a class=\"btn btn-danger\" href=\"javascript:previous_records('destroy')\">Destroy Record</a>";
				return encode(true, false, $t);
			}
			return encode(true, false, $t);
		}else if($stage == 3){
			$sql = mysqli_query($con, "
				CREATE TABLE IF NOT EXISTS record_results(
					id int(11) AUTO_INCREMENT PRIMARY KEY,
					code varchar(255),
					title varchar(255),
					unit varchar(255),
					score varchar(255),
					grade varchar(255),
					name varchar(255),
					matric varchar(255),
					exam varchar(255),
					ca varchar(255),
					sesh varchar(255)
				);
			");
			$sql = mysqli_query($con, "SELECT DISTINCT matric FROM record_courses_registered ORDER BY matric");
			$l = calcLevelFromSesh($yoe,$yor);
			if(mysqli_num_rows($sql) > 0){
				$t = '<div class="container"><h5>'.$yoe.' '.$dept.' '.$l.'Level '.$sem.' Semester Result</h5><div style="margin-top: 2em;">';
				$toggle_showing = 'show';
				$toggle_show = 'showing';
				while($data = mysqli_fetch_assoc($sql)){
					$mat = $data['matric'];
					$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$mat' ORDER BY code");
					while($data = mysqli_fetch_assoc($sql1)){
						$code = $data['code'];
						$sesh = $data['sesh'];
						$ca = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM record_results WHERE matric='$mat' AND code='$code'"))['ca'];
						$exam = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM record_results WHERE matric='$mat' AND code='$code'"))['exam'];
						$check = ($ca && $exam)? "<i class=\"fa fa-check-circle\"></i>":"<i class=\"fa fa-times-circle\" style=\"color:red;\"></i>";
						$expand = false;
						if(!$ca || !$exam){ $expand = true; break; }
					}
					if($expand){
						$toggle_show = 'showing';
						$toggle_showing = 'show';
					}else{
						$toggle_showing = '';
						$toggle_show = '';
					}
					$t =$t."<div class=\"course_summary_graph\" style=\"margin:initial;width:90%;\"><a class=\"btn btn-primary $toggle_show\" onclick=\"this.classList.toggle('showing')\" data-toggle=\"collapse\" href=\"#$mat\">$mat $check</a><div class=\"collapse $toggle_showing $mat\" id=\"$mat\"><div style=\"border:1px solid blue;border-radius:0 4px 4px 4px;padding:10px;\"><div >";
					$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$mat' ORDER BY code");

					$t = $t.'<h5>Score Sheet</h5> <table class="table hover"> <thead> <tr>
						<th scope="col">#</th> <th scope="col">Code</th><th scope="col">CA</th> <th
						scope="col">Exam</th> <th scope="col">Total</th> <th scope="col">Grade</th>
						<th scope="col">Remark</th> </tr> </thead> <tbody>';
						$count = 1;
						$last = mysqli_num_rows($sql1);
					while($data = mysqli_fetch_assoc($sql1)){
						$code = $data['code'];
						$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_results WHERE matric='$mat' AND code='$code'"));
						$total = ($result_data['score'])? $result_data['score']:'0';
						$exam =  ($result_data['exam'])? $result_data['exam']:'0';
						$ca =  ($result_data['ca'])? $result_data['ca']:'0';
						$grade =  ($result_data['grade'])? $result_data['grade']:'A';
						$remark = ($grade == 'F')? 'Failed':'Passed';
						$but_i = '';
						if($count == $last){
							$but_i = ',true';
						}
						$t = $t.'<tr><td>'.$count.'</td><td>'.$code.'</td>';
						$t = $t."<form><td><input style=\"border: 1px solid #fff; padding: 4px 8px; border-radius: 4px;\" min=\"0\" max=\"30\" oninput=\"record_update_score(this.value,'$mat','#$mat$count','CA','$code ! $sesh')\" value=\"$ca\" type=\"number\"></td><td><input type=\"number\" style=\"border: 1px solid #fff; padding: 4px 8px; border-radius: 4px;\" min=\"0\" max=\"30\" oninput=\"record_update_score(this.value,'$mat','#$mat$count','EXAM','$code ! $sesh'$but_i);\" value=\"$exam\"></td><td id=\"$mat$count\" class=\"total\">$total</td><td id=\"$mat$count\"  class=\"grade\">$grade</td></form><td id=\"$mat$count\"  class=\"remark\">$remark</td>";
						$count++;
					}
					$t = $t.'</tbody></table>';
					$t = $t."</div></div> </div> </div>";
					$toggle_showing = '';
					$toggle_show = '';
				}
				$t = $t.'</div><a class="btn btn-primary mr-3 mb-3" href="javascript:record_result()">Continue</a><a class="btn btn-danger  mb-3" href="javascript:previous_records(\'destroy\')">Destroy Record</a></div>';
			}else{
				$t = "<h5 style=\"margin-top: 5em;\" class=\"center\">Add students for $dept $yoe from the add students menu option on the left to continue with the current record.</h5><a class=\"btn btn-danger\" href=\"javascript:previous_records('destroy')\">Destroy Record</a>";
				return encode(true, false, $t);
			}
			return encode(true, false, $t);
		}else if($stage == 4){
			$core_span = mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses WHERE state='CORE'"));
			$ele_span = mysqli_num_rows(mysqli_query($con, "SELECT * FROM record_courses WHERE state='ELECTIVE'"));
			$core_span=($core_span)? $core_span:1;
			$ele_span=($ele_span)? $ele_span:1;
			$pre_span = $ele_span+$core_span+4;
			$t = ' <h6 class="center">FACULTY OF EDUCATION, DEPARTMENT OF '.$dept.'
				EXAMINATION RESULTS</h6> <h6 class="center"><span
				style="border-radius:0;background:transparent;color:#000;" class="btn
				btn-primary disabled">ACADEMIC SESSION: '.$yor.'</span><span
				style="border-radius:0;background:transparent;color:#000;" class="btn
				btn-primary disabled">ACADEMIC SEMESTER/LEVEL:
				'.calcLevelFromSesh($yoe,$yor).' Level '.$sem.' SEMESTER</span><span
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
				$sql = mysqli_query($con, "SELECT * FROM students WHERE yoe='$yoe' ORDER BY matric");
				$count = 1;
				while($data = mysqli_fetch_assoc($sql)){
					$matric = $data['matric'];
					$t = $t.'<tr><td>'.$count.'</td><td>'.$data['name'].'</td><td>'.$matric.'</td>';
					/**Core and Elective courses display**/
						$sql1 = mysqli_query($con, "SELECT * FROM record_courses WHERE state='CORE'");
						if(mysqli_num_rows($sql1) > 0){
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								$unit = $data['unit'];
								$t = $t.'<td class="center">'.str_replace(' ', '', $code).'<br>'.$unit.'<br>';
								$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric' AND code='$code'"));
								$grade = $result_data['grade'];
								$score = $result_data['score'];
								if(!$score){ $t = $t.'DRP</td>'; continue; }
								$t = $t.$score.$grade.'<br>'.($grades[$result_data['grade']]*$result_data['unit']).'</td>';
							}
						}else{ $t = $t.'<td>-</td>'; }
						$sql1 = mysqli_query($con, "SELECT * FROM record_courses WHERE state='ELECTIVE'");
						if(mysqli_num_rows($sql1) > 0){
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								$unit = $data['unit'];
								$t = $t.'<td class="center">'.str_replace(' ', '', $code).'<br>'.$unit.'<br>';
								$result_data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric' AND code='$code'"));
								$grade = $result_data['grade'];
								$score = $result_data['score'];
								if(!$score){ $t = $t.'DRP</td>'; continue; }
								$t = $t.$score.$grade.'<br>'.($grades[$result_data['grade']]*$result_data['unit']).'</td>';
							}
						}else{ $t = $t.'<td>-</td>'; }
					/****TCR*****/
						$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric'");
						$tcr = 0;$ltcr=0;$tce=0;
						while($data = mysqli_fetch_assoc($sql1)){
							$code = $data['code'];
							if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE department='$dept' AND code='$code' AND sesh>'$yoe' AND sesh_added='$yor'"))){
								$ltcr=$ltcr+$data['unit'];
							}else{
								$tcr=$tcr+$data['unit'];
							}
							$sql2 = mysqli_query($con, "select * from record_results where matric='$matric' and code='$code' and (not grade='F')");
							if(!mysqli_num_rows($sql2)){
								continue;
							}
							$tce = $tce+$data['unit'];
						}
						if($ltcr){$t = $t.'<td>'.$tcr.'+'.$ltcr.'=<br>'.($tcr+$ltcr).'</td><td>'.$tce.'</td>';}
						else{$t=$t.'<td>'.$tcr.'</td><td>'.$tce.'</td>';}
					/****TGP****/
						$sql1 = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric'");
						$tgp = 0;
						while($data = mysqli_fetch_assoc($sql1)){
							$tgp = $tgp+($grades[$data['grade']]*$data['unit']);
						}
						$t = $t.'<td>'.$tgp.'</td>';
						if($tcr+$ltcr==0){
							$t=$t.'<td>0</td>';
						}else{
							$t=$t.'<td style="vertical-align: middle;">'.round($tgp/($tcr+$ltcr),2).'</td>';
						}
					/****CTCR*****/
						$sql1 = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric'");
						$ctcr = 0;$ctce=0;
						while($data = mysqli_fetch_assoc($sql1)){
							$code=$data['code'];
							$ctcr=$ctcr+$data['unit'];
							$sql2 = mysqli_query($con, "select * from record_results where matric='$matric' and code='$code' and (not grade='F')");
							if(!mysqli_num_rows($sql2)){
								continue;
							}
							$ctce = $ctce+$data['unit'];
						}
						$sql1 = mysqli_query($con, "SELECT * FROM course_registered WHERE matric='$matric' AND sesh<='$yor'");
						while($data = mysqli_fetch_assoc($sql1)){
							$code=$data['code'];
							$ctcr=$ctcr+$data['unit'];
							$sql2 = mysqli_query($con, "select * from results where matric='$matric' and code='$code' and (not grade='F')");
							if(!mysqli_num_rows($sql2)){
								continue;
							}
							$ctce = $ctce+$data['unit'];
						}
						$t=$t.'<td>'.($ctcr-$tcr).'</td><td>'.($ctce-$tce).'</td>';
					/****CTGP****/
						$sql1 = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric'");
						$ctgp = 0;
						while($data = mysqli_fetch_assoc($sql1)){
							$ctgp = $ctgp+($grades[$data['grade']]*$data['unit']);
						}
						$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$yor'");
						while($data = mysqli_fetch_assoc($sql1)){
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
							$sql1 = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric'");
							while($data=mysqli_fetch_assoc($sql1)){
								$code=$data['code'];
								$check = mysqli_query($con, "SELECT * FROM record_results WHERE matric='$matric' AND code='$code' AND (NOT grade='F')");
								$state_check = mysqli_query($con, "SELECT * FROM record_courses WHERE code='$code' AND state='CORE'");
								if(!mysqli_num_rows($state_check)){
									continue;
								}
								if(!mysqli_num_rows($check) && !has_element($data['code'],$rems)){
									$rems[count($rems)] = str_replace(' ', '', $data['code']);;
								}
							}
							$sql1 = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND sesh_submitted<='$yor'");
							while($data=mysqli_fetch_assoc($sql1)){
								$code=$data['code'];
								if($data['sesh_submitted'] == $yor && $data['semester'] > $sem){
									continue;
								}
								$check = mysqli_query($con, "SELECT * FROM results WHERE matric='$matric' AND code='$code' AND (NOT grade='F')");
								$state_check = mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND state='CORE' AND sesh='$yoe'");
								if(!mysqli_num_rows($state_check)){
									continue;
								}
								if(!mysqli_num_rows($check) && !has_element($data['code'],$rems)){
									$rems[count($rems)] = str_replace(' ', '', $data['code']);
								}
							}
						/**Course Not registered**/
							$sql1 = mysqli_query($con, "SELECT * FROM record_courses WHERE state='CORE'");
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								$check = mysqli_query($con, "SELECT * FROM record_courses_registered WHERE matric='$matric' AND code='$code'");
								if(!mysqli_num_rows($check) && !has_element($code, $rems)){
									$rems[count($rems)] = str_replace(' ', '', $code);
								}
							}
							$sql1 = mysqli_query($con, "SELECT * FROM courses WHERE state='CORE' AND sesh_added<='$yor' AND sesh='$yoe'");
							while($data = mysqli_fetch_assoc($sql1)){
								$code = $data['code'];
								if($data['sesh_added'] == $yor && $data['semester'] > $sem){
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
			$t = $t.'</tbody></table><a href="javascript:previous_records(\'save_entry\');" class="btn-primary btn mr-3">Save</a><a class="btn btn-danger" href="javascript:previous_records(\'destroy\')">Destroy Record</a>';
			return encode(true,false,$t);
		}
	}
	$t = cp(['yoe','yor','dept','semester']);
	if($t){
		$l = $t['yoe'];
		$r = $t['yor'];
		$d = $t['dept'];
		$s = $t['semester'];
		if($l > $current_session || $r > $current_session){
			return encode(false,'<p>You can\'t enter a record for that session.</p>');
		}else if(($l == $current_session && $s == $current_semester) || ($r == $current_session && $s == $current_semester)){ return encode(false,'<p>You can\'t enter a record for that session.</p>'); }
		if(mysqli_num_rows(mysqli_query($con, "SELECT * FROM students WHERE yoe='$l' AND department='$d'")) < 1){
			return encode(false,'<p>They are currently no '.$d.' students registered for this session.<br>Use the add student menu option to add students for '.$d.' '.$l.'.</p>');
		}
		$sql = mysqli_query($con, "
			CREATE TABLE IF NOT EXISTS record_data(
				id INT(11) AUTO_INCREMENT PRIMARY KEY,
				record_for VARCHAR(255),
				record_year VARCHAR(255),
				record_department VARCHAR(255),
				record_semester VARCHAR(255),
				record_stage VARCHAR(255)
			);
		");
		if(!$sql){ return encode(false, '<p>Unable to start record entry. Try again.</p>'); }
		$sql = mysqli_query($con, "INSERT INTO record_data (record_for,record_year,record_department,record_semester,record_stage) VALUES ('$l','$r','$d','$s',1)");
		if(!$sql){ return encode(false, '<p>Unable to start record entry. Try again.</p>'); }
		unset($_POST['dept'],$_POST['semester'],$_POST['yoe'],$_POST['yor']);
		return previous_records();
	}
	$t = ' <div class="container mainbody_form"> <form class=""
			action="javascript:previous_records(\'start_entry\')" id="record_add_form" autocomplete="off"> <p
			class="info">Please Fill Out This Form</p> <input class="login_input"
			type="text" name="yoe" placeholder="Entry Year" required/> <span
			class="footnote">(e.g 2015/2016)</span><input class="login_input mb-3" type="text" name="semester" placeholder="Semester" list="semesters" required/><datalist id="semesters"><option value="1ST"/><option value="2ND"/></datalist> <input list="departments" class="login_input mb-3"
			type="text" name="department" placeholder="Department" required/>';
			$sql = mysqli_query($con, "SELECT * FROM departments");
			if(mysqli_num_rows($sql) > 0){
				$t = $t.'<datalist id="departments">';
					while($data=mysqli_fetch_assoc($sql)){
						$t=$t.'<option value="'.$data['dept'].'" />';
					}
				$t = $t.'</datalist>';
			}
			$t = $t.'<input type="text" placeholder="Record Session" name="yor" class="login_input" required /><span class="footnote">The session for this record</span> <input type="submit" class="login_submit"
			name="submit" value="Continue" required/></form> </div> ';
	return encode(true,false,$t);
}
function new_grade($n = false){
	global $con;
	if(!cs('user_id')){ return json_encode(array('status'=>false, 'msg'=>'<p>INVALID CREDENTIALS</p>')); }
	if(!$n && !cs('grade_system')){return encode(false, '<p>Session not set!</p>');}
	if(!cs('grade_system')){ $_SESSION['grade_system'] = $n; }
	$sesh = cs('grade_system');
	if(cp('opt') == 'select_grade_system'){
		$sesh = cp('sesh');
		$sql =	mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$sesh'");
		if(mysqli_num_rows($sql) < 1){ return encode(false, "<p>$sesh has no grade system set.</p>"); }
		$tmp = '';
		$count = 0;
		while($data = mysqli_fetch_assoc($sql)){
			$tmp = $tmp.'<div class="form-group col-sm-3 row-'.$count.'"> <input type="text"
			class="login_input grades" value="'.$data['grade'].'" required/> </div><div
			class="form-group col-sm-3 row-'.$count.'"> <input type="number" class="login_input
			minimum_scores" value="'.$data['minimum_score'].'" required/>
			</div><div class="form-group col-sm-3 row-'.$count.'"> <input type="number"
			class="login_input gradepoints" value="'.$data['gradepoints'].'" required/>
			</div><div class="form-group col-sm-3 row-'.$count.'"> <a
			href="javascript:new_grade_system([\'delete_row\','.$count.'])" class="mt-2 btn btn-primary"><i
			class="fa fa-trash"></i></a> </div>';
			$count++;
		}
		return encode(true, false, $tmp);
	}
	$t = cp(['grades','minimum_scores','gradepoints']);
	if($t){
		$grades = explode(',',strtoupper($t['grades']));
		$minimum_scores = explode(',',strtoupper($t['minimum_scores']));
		$gradepoints = explode(',',strtoupper($t['gradepoints']));
		if(count($grades) != count($minimum_scores) || count($minimum_scores) != count($gradepoints)){
			return encode(false, '<p>INVALID INPUT</p>');
		}
		if(ct('grades',"sesh_updated='$n'")){ unset($_SESSION['grade_system']); return encode(true); }
		for($i = 0; $i < count($grades); $i++){
			$g=$grades[$i];$m=$minimum_scores[$i];$gp=$gradepoints[$i];
			mysqli_query($con, "INSERT INTO grades (grade,minimum_score,gradepoints,sesh_updated) VALUES ('$g','$m','$gp','$sesh')");
		}
		unset($_SESSION['grade_system']);
		return encode(true);
	}
	$t = '';
		$count = 1;
		$sql = mysqli_query($con, "SELECT DISTINCT sesh_updated FROM grades");
		if(mysqli_num_rows($sql) > 0){
			$t = $t.'<h6>Please select grade system for '.$n.'</h6>';
			while($data = mysqli_fetch_assoc($sql)){
				$sesh = $data['sesh_updated'];
				$t = $t.'<a class="btn btn-primary mb-3" href="javascript:new_grade_system([\'select_system\',\''.$sesh.'\'])">'.$sesh.'</a><p class="btn btn-warning disabled">';
				$sql1 = mysqli_query($con, "SELECT * FROM grades WHERE sesh_updated='$sesh'");
				while($data = mysqli_fetch_assoc($sql1)){
					$t = $t.$data['grade'].'=>'.$data['minimum_score'].'=>'.$data['gradepoints'].',';
				}
				$t = $t.'</p><br>';
			}
			$t = $t.'<h6 class="center">OR</h6>';
		}
		$t = $t.'<h6>Please define a grade system for '.$n.'</h6>
			<form id="new_grade_form" action="javascript:new_grade_system()">
			<div class="container">
				<div class="form-row">
					<div class="form-group col-sm-3">
						<p>Grade</p>
					</div>
					<div class="form-group col-sm-3">
						<p>Minimum score</p>
					</div>
					<div class="form-group col-sm-3">
						<p>Grade points</p>
					</div>
				</div>
				<div class="form-row" id="grade_systems">
					<div class="form-group col-sm-3 row-'.$count.'"> <input type="text"
				class="login_input grades" required/> </div><div
				class="form-group col-sm-3 row-'.$count.'"> <input type="number" class="login_input
				minimum_scores" required/>
				</div><div class="form-group col-sm-3 row-'.$count.'"> <input type="number"
				class="login_input gradepoints" required/>
				</div><div class="form-group col-sm-3 row-'.$count.'"> <a
				href="javascript:new_grade_system([\'delete_row\','.$count.'])" class="mt-2 btn btn-primary"><i
				class="fa fa-trash"></i></a> </div>
				</div>
				<a class="btn btn-primary mr-3" href="javascript:new_grade_system(\'add_field\')"><i class="fa fa-plus"></i> Add field</a>
				<input type="submit" class="btn btn-primary" value="Submit Grade System" />
				</div>
			</form>';
	return json_encode(array('status'=>true,'mark_up'=>$t,'markup'=>$t));
}
function end_session(){
	global $con,$current_session,$current_semester;
	if(!cs('user_id')){return encode(false,'<p>INVALID CREDENTIALS</p>');}
	$status = array();
	/**
	 * Previously Slowed Courses
	 */
		$sql = mysqli_query($con,"SELECT * FROM courses  WHERE semester='$current_semester' AND slowed=1");
		if(mysqli_num_rows($sql) > 0){
			while($data = mysqli_fetch_assoc($sql)){
				$lecturer_id = $data['assigned_to'];
				$assigned_to = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lecturers WHERE id='$lecturer_id'"))['name'];
				$assigned_to = ($assigned_to)? 'Assigned to '.$assigned_to:'Currently Unassigned';
				$status[$data['code']] = [$assigned_to,$data['semester'],$data['sesh_added'],$data['id']];
			}
		}
	/**
	 * Unsubmitted Courses
	 */
		$sql = mysqli_query($con, "SELECT * FROM courses WHERE sesh_added='$current_session' AND semester='$current_semester' AND (NOT `state`='ELECTIVE')");
		if(mysqli_num_rows($sql) > 0){
			while($data = mysqli_fetch_assoc($sql)){
				$code = $data['code'];
				$check = mysqli_num_rows(mysqli_query($con, "SELECT * FROM results WHERE code='$code' AND sesh_submitted='$current_session'"));
				if($check){ continue; }
				$lecturer_id = $data['assigned_to'];
				$assigned_to = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM lecturers WHERE id='$lecturer_id'"))['name'];
				$assigned_to = ($assigned_to)? 'Assigned to '.$assigned_to:'Currently Unassigned';
				$status[$data['code']] = [$assigned_to,$data['semester'],$data['sesh_added'],$data['id']];
			}
		}
	if(count($status) > 0){
		$count = (count($status) > 1)? 'courses have':'course has';
		$title = '<p>Before you continue, there are some things you should know.</p>
		<p>The results for the following '.$count.' not been submitted:';
		foreach ($status as $key => $value) {
			$title = $title."<span class=\"btn disabled\"> $key [$value[0]. $value[1] Semester, $value[2].]</span>";
		}
		$count = (count($status) > 1)? 'these courses':'this course';
		$count2 = (count($status) > 1)? 'they':'it';
		$title = $title.'</p><p>
			If you decide to continue with '.$count.' unsubmitted, '.$count2.' will be slowed down and the lecturer can
			still submit the results regardless.
		</p>';
	}else{
		$title = '<p>Everything is okay!</p><p>Click the "continue" button to continue.</p>';
	}
	$heading = ($current_semester=='2ND')? "Ending $current_session Academic session":"Ending $current_semester semester, $current_session";
	if(cp('ignore')){
		foreach ($status as $key => $value) {
			$sql = mysqli_query($con,"UPDATE courses SET slowed=1 WHERE id=$value[3]");
			if(!$sql){ return encode(false, '<p>Please Try That again</p>'); }
			$sql = mysqli_query($con, "UPDATE course_registered SET slowed=1 WHERE code='$key' AND sesh='$current_session'");
			if(!$sql){ return encode(false, '<p>Please Try That again</p>'); }
		}
		/**
		 * Copy and paste courses...
		 */
			$sql = mysqli_query($con, "SELECT * FROM courses WHERE sesh_added<='$current_session' AND semester='$current_semester'");
			if(mysqli_num_rows($sql) > 0 && $sql){
				while($data = mysqli_fetch_assoc($sql)){
					$code = $data['code'];
					$title = $data['title'];
					$department = $data['department'];
					$semester = $data['semester'];
					$unit = $data['unit'];
					$assigned_to = $data['assigned_to'];
					$sesh = (explode('/',$data['sesh'])[0]+1).'/'.(explode('/',$data['sesh'])[1]+1);
					$state = $data['state'];
					$sesh_assigned = (explode('/',$data['sesh_assigned'])[0]+1).'/'.(explode('/',$data['sesh_assigned'])[1]+1);
					$sesh_added = (explode('/',$data['sesh_added'])[0]+1).'/'.(explode('/',$data['sesh_added'])[1]+1);
					$check = mysqli_num_rows(mysqli_query($con, "SELECT * FROM courses WHERE code='$code' AND department='$department' AND semester='$semester' AND sesh_added='$sesh_added'"));
					if($check){ continue; }
					$sql1 = mysqli_query($con, "INSERT INTO courses (code,title,department,semester,unit,assigned_to,sesh,`state`,sesh_assigned,sesh_added) VALUES ('$code','$title','$department','$semester','$unit','$assigned_to','$sesh','$state','$sesh_assigned','$sesh_added')");
					if(!$sql1){ return encode(false,'<p>An Error Occurred.<br>Please try again.</p>');}
				}
			}
		/**
		 * Change Session And Semester
		 */
		$new_semester = ($current_semester == '2ND')? '1ST':'2ND';
		$new_session = ($current_semester=='2ND')? (explode('/',$current_session)[0]+1).'/'.(explode('/',$current_session)[1]+1):$current_session;
		$sql = mysqli_query($con, "UPDATE `admin` SET current_sesh='$new_session', current_semester='$new_semester' WHERE 1");
		if(!$sql){ return encode(false,'<p>An Error Occurred.<br>Please try again.</p>'); }
		return encode(true,false,'<p>Done</p>');
	}
	$t = '
		<div id="end_session_modal">
		<div style="display:table;width:100%;height:100%;">
			<div class="content">
				<div class="cont">
					<div class="header">
						<div class="container"><h5>'.$heading.'</div>
					</div>
					<div class="body">
						<div class="container">
							'.$title.'
							<a class="pull-right  btn btn-danger" href="javascript:app.dashboard.init();">Cancel</a>
							<a class="pull-right mr-3 btn btn-primary" href="javascript:end_session(1);">Continue</a>
							<span class="clearfix"></span>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	';
	return encode(true,false,$t);
}

?>