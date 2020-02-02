<?php


function lecturer_start_install(){
	global $con;
	$id = cs('lecturer_id');
	if(!cs('lecturer_id')){
		return encode(false,'<p>INVALID CREDENTIALS</p>');
	}
	$sql = mysqli_query($con, "SELECT * FROM lecturers WHERE id='$id'");
	$data = mysqli_fetch_assoc($sql);
	$t = '<form id="install_form" autocomplete="off"
	action="javascript:next_step();">';
	if($data['name'] == '0'){
	 	$t = $t.'<div class="options fade unsaved"> <i class="fa
		fa-check-circle-o"></i><span>Name</span> <div> <p class="title titles
		center">First, what is your full name?</p><input type="text" class="form-control" name="full_name" placeholder="Full Name" required/></div></div>'; 
	}else{
		$t = $t.'<div class="options check"> <i class="fa
		fa-check-circle-o"></i><span>'.$data['name'].'</span> <div> <p class="title titles
		center">Already Saved</p> </div>
		</div>';
	}
	if(count(explode('L-',$data['username'])) > 1){
		$t = $t.'<div class="options fade unsaved"> <i class="fa
		fa-check-circle-o"></i><span>Username</span> <div> <p class="title titles
		center">You\'re Currently using a default Username. In our opinion,
		you should change it to something you can remember easily, but you can click Next if you don\'t want to.</p> <input type="text" class="form-control mb-2" name="username"
		placeholder="Username" /> </div> </div>';
	}else{
		$t = $t.'<div class="options check"> <i class="fa
		fa-check-circle-o"></i><span>'.$data['username'].'</span> <div> <p class="title titles
		center">Already Saved</p>  </div>
		</div>';
	}
	{
		$t = $t.' <div class="options fade unsaved"> <i
		class="fa fa-check-circle-o"></i><span>Password</span> <div> <p class="title
		titles center">You can change your Password here or click next if you don\'t want to.</p>
		<input type="password" class="form-control mb-2" name="password"
		placeholder="Password" /> <input type="password" class="form-control mb-2"
		name="confirmpassword" placeholder="Confirm password" /> </div> </div>';
	}
	if($data['email'] == '0'){
		$t=$t.' <div class="options fade unsaved"> <p class="title titles center">Finally, we need an
		email<br>Just so you know, we won\'t be doing much of a validation, so do well to give a valid email.</p> <input type="email" class="form-control mb-2" name="email"
		placeholder="email" /> </div>';
	}else{
		$t=$t.' <div class="options check"> <i class="fa
		fa-check-circle-o"></i><span>['.$data['email'].']</span> <div> <p class="title titles
		center">Already Saved</p> </div></div>';
	}
	$t = $t.'</form> <a class="btn btn-primary pull-right"
	href="javascript:document.getElementById(\'install_form\').submit()">Next <i
	class="fa fa-long-arrow-right"></i></a> <a class="btn btn-warning pull-right
	mr-3" href="javascript:previous_step()"><i class="fa
	fa-long-arrow-left"></i> Previous</a>';
	return encode(true,false,$t);
}

	function register_lecturer(){
		global $con;
		$id = cs('lecturer_id');
		if(!$id){
			return encode(false,'<p>INVALID CREDENTIALS</p>');
		}
		if(cp('data') == 'full_name'){
			$name = e(cp('value'));
			/*Validation...*/
			
			if(empty($name)){
				$error = '<p>Name Can\'t be empty</p>';
				return encode(false, $error);
			}else if(count(explode(' ',$name)) < 2){
				return encode(false, '<p>Your Fullname, Please...</p>');
			}else if(strlen(explode(' ', $name)[0])<2 || strlen(explode(' ', $name)[1])<2){
				$error = '<p>Name and surname must be more than 3 characters long and initials should come at the end. E.g, Dr. John Doe S.</p>';
				return encode(false, $error);
			}
			$sql = mysqli_query($con, "UPDATE lecturers SET name='$name' WHERE id='$id'");
			if(!$sql){ return encode(false,'<p>An ERROR has occurred.<br>Try again later.</p>'); }
			return encode(true);
		}
		if(cp('data') == 'username'){
			$name = strtolower(e(cp('value')));
			if($name == ''){
				return encode(true);
			}
			/*Validation...*/
			
			if($name == 'admin' || $name == 'user'){
				$error = '<p>Username Can\'t be admin or user</p>';
				return encode(false, $error);
			}else if(count(explode(' ',$name)) > 1 || string_contains(',*&^%$@#;:!`~-/?|\+(){}[]',$name)){
				return encode(false, '<p>Your username can\'t contain spaces or special characters [brackets,*&^%$@#!`~-/?|\+].');
			}else if(strlen($name)<4){
				$error = '<p>Username must be at least 4 characters long.</p>';
				return encode(false, $error);
			}else if(ct('lecturers', "username='$name'")){
				return encode(false, "<p>\"$name\" already taken!</p>");
			}
			$sql = mysqli_query($con, "UPDATE lecturers SET username='$name' WHERE id='$id'");
			if(!$sql){ return encode(false,'<p>An ERROR has occurred.<br>Try again later.</p>'); }
			return encode(true);
		}
		if(cp('data') == 'password'){
			$password = cp('value');
			if($password == ' - '){
				return encode(true);
			}
			$cpassword = strtoupper(e(md5(explode(' - ', $password)[0])));
			$password = strtoupper(e(md5(explode(' - ', $password)[1])));
			if($cpassword != $password){
				return encode(false, '<p>Passwords do not match!</p>');
			}else if(strlen(explode(' - ', cp('value'))[0]) < 8){
				return encode(false, '<p>Password must be, at least, 8 characters long.');
			}
			$sql = mysqli_query($con, "UPDATE lecturers SET password='$password' WHERE id='$id'");
			if(!$sql){ return encode(false,'<p>An ERROR has occurred.<br>Try again later.</p>'); }
			return encode(true);
		}
		if(cp('data') == 'email'){
			$name = e(cp('value'));
			if(!(string_contains('@gmail.com',$name,true) || string_contains('@yahoo.com',$name,true))){
				return encode(false, "<p>Only Google and Yahoo mails are allowed</p>");
			}else if((count(explode('@gmail.com', $name)) > 2 || !empty(explode('@gmail.com', $name)[1])) || (count(explode('@yahoo.com', $name))>2 || !empty(explode('@yahoo.com', $name)[1]))){
				return encode(false, '<p>Please provide a valid email. Eg. example@gmail.com or example@yahoo.com</p>');
			}else if(!(!string_contains('\'"!@#$%^&*()=+?|,<>{}]:;[()`~' ,explode('@yahoo.com', $name)[0]) || !string_contains('\'"!@#$%^&*()=+?|,<>{}]:;[()`~' ,explode('@gmail.com', $name)[0]))){
				return encode(false, '<p>Please provide a valid email. Eg. example@gmail.com or example@yahoo.com</p>');
			}else if(ct('lecturers',"email='$name'")){
				return encode(false, "<p>That email is already in use.</p>");
			}
			$sql = mysqli_query($con, "UPDATE lecturers SET email='$name',status='lecturer' WHERE id='$id'");
			if(!$sql){ return encode(false,'<p>An ERROR has occurred.<br>Try again later.</p>'); }
			return encode(true);
		}
	}



?>