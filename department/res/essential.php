
<?php
	include 'conn.php';
	function cp($n){
		if(is_array($n)){
			$r = Array();
			$t = false;
			for($i = 0; $i < count($n); $i++){
				$t = (isset($_POST[$n[$i]]) && $_POST[$n[$i]])? $_POST[$n[$i]]:false;
				if(isset($_POST[$n[$i]]) && $_POST[$n[$i]]){
					$r[$n[$i]] = $_POST[$n[$i]];
				}
				if($t == false){
					break;
				}
			}
			if(!$t){
				$r = false;
			}
		}else{
			$r = (isset($_POST[$n]) && $_POST[$n])? $_POST[$n]:false;
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
	function encode($n,$m=false,$t=false){
		return json_encode(array('status' => $n, 'error' => $m, 'markup' => $t));
	}
	function runsql($sql){
		global $con;
		return mysqli_query($con, $sql);
	}
	
	function enumWrap($html, $arr){
		$tmp = '';
		for($i =0; $i<count($arr); $i++){
			$tmp = $tmp.str_replace('.data.', $arr[$i], $html);
		}
		return $tmp;
	}
	function loopaction($func,$sql, $arr){
		for($i = 0; $i < count($arr); $i++){
			$func(str_replace('.data.', $arr[$i], $sql));
		}
	}
	function start_install(){
		global $con;
		if(!cs('user_id')){
			return encode(false,'<p>INVALID CREDENTIALS</p>');
		}
		$t = cp(['departments','levels','session','semester']);
		if($t){
			$d = strtoupper(e($t['departments']));
			$l = strtoupper(e($t['levels']));
			$s = strtoupper(e($t['session']));
			$semester = e($t['semester']);
			$d = explode(',', $d);
			$l = explode(',', $l);
			if(!is_numeric(str_replace('/', '', $s)) || explode('/', $s)[0] > explode('/', $s)[1]){
				return encode(false, "<p>$s is an invalid session. It should be in the format lesserYear/higherYear. i.e, 2015/2016.</p>");
			}
			if(!tableEmpty('departments')){ loopaction('runsql',"INSERT INTO departments (dept) VALUES ('.data.')", $d); }
			if(!tableEmpty('levels')){ loopaction('runsql',"INSERT INTO levels (level) VALUES ('.data.')", $l); }
			$sql = mysqli_query($con, "UPDATE admin SET current_sesh='$s',current_semester='$semester' WHERE 1");
			if(!$sql){
				echo 'An error has occurred. Try hitting the Continue button again.';
				exit();
			}
			$t = '<p class="title welcome">THAT WILL BE ALL FOR NOW, ENJOY!</p> <i
			class="fa fa-check-circle-o install-done"></i> <a class="btn btn-success
			pull-right" href="">Continue</a>';
			return encode(true,false,$t);
			exit();
		}
		$t = '<p class="title titles">First, the Departments, Levels, Grades and
		Current Academic Session we will be workin with.</p> <p class="title titles"
		style="text-align: center;"><i class="btn btn-danger">NOTE</i> <span
		style="display: inline-block;text-align: center; color: red; font-weight:
		lighter;">Though you can change these information at any time, be careful
		about it as they affect the total running of this application</span></p>
		<form class="" action="javascript:startinstallation()" id="install_form"> <input
		type="text" name="departments" class="form-control mb-2"
		placeholder="Departments" required /> <span class="footnote mb-2">Give a
		comma (,) separated list. e.g, Mathematics,Physics,Chemistry,Biology</span>
		<input type="text" name="level" class="form-control mb-2"
		placeholder="Levels" required /> <span class="footnote mb-2">Give a comma
		(,) separated list in ascending order. e.g, 100,200,300,400</span> <input type="text" name="session"
		class="form-control mb-2" placeholder="Current Academic Session" required />
		<span class="footnote mb-2">e.g, 2015/2016</span> <input type="text" name="semester"
		class="form-control mb-2" placeholder="Current Semester" required />
		<span class="footnote mb-2">e.g, 1ST, 2ND</span> <input type="submit"
		name="submit" value="Continue" class="btn btn-primary pull-right" /> 
		</form>';
		return encode(true,false,$t);
	}
	function e($n){
		global $con;
		return mysqli_real_escape_string($con,$n);
	}
	function ct($t, $v){
		global $con;
		$t = mysqli_query($con, "SELECT * FROM `$t` WHERE $v");
		$r = (mysqli_num_rows($t) > 0)? $t:false;
		return $r;
	}
	if(cp('mark_up')){
		$mk = cp('mark_up')();
		echo $mk;
		exit();
	}
?>