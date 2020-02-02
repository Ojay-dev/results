
function User(){
	let loadActivity = loadScreen;
	let back = ret;
	let login = log;
	let mat = matric;
	let sidebar = {
		togg: sideb,
		selectM: selectMenu,
		selection: '',
		select: function(n){
			this.selection = n;
			this.selectM();
		}
	};
	let dashboard = {};
	dashboard.ini = function(){
		sidebar.select('dashboard');
		$('#main_content').removeClass('hide-graphical');
		lecturer_updater();
		user.ajax({
			mark_up: 'student_dashboard',
			matric: mat
		},
		function (){
			var r;
			try{
				r = JSON.parse(user.ajaxresponse);
				if(!r.status){
					//user.error.err(r.error, 5000);
					user.loadActivity = {'view':'#main_content','html':r.error};
				}else{
					user.loadActivity = {'view':'#main_content','html':JSON.parse(user.ajaxresponse).markup};
					setTimeout(function(){ dashboard.canvas(); }, 1000);
				}
			}catch(r){
				user.loadActivity = {'view':'#main_content','html':user.ajaxresponse};
			}
		});
	};
	dashboard.canvas = function(){ 
		user.ajax({
			mark_up: 'student_dashboard',
			graph: true,
			matric: mat
		},
		function (){
			var r;
			try{
				r = JSON.parse(user.ajaxresponse);
				if(!r.status){
					$('#graph').html(r.markup);
				}else{
					canvasRun(r.labels, r.dataSets);
				}
			}catch(r){
				user.loadActivity = {'view': '#main_content', 'html': user.ajaxresponse};

			}
		});
	}
	Object.defineProperties(sidebar, {
		'toggle':{
			get: function(){
				sidebar.togg();
			}
		}
	});
	this.reset = reset;
	let ajaxresponce = '';
	Object.defineProperty(dashboard, 'init', {
		get: function(){
			dashboard.ini();
		}
	});
	this.history =  new Array();
	this.results =  {
		department: false,
		level:false,
		session:false,
		semester:false
	};
	this.courses = {
		department:  false,
		level:  false,
		semester: false
	};
	let ajaxurl = '../res/department.requests.php';
	let loader =  function(n = Math.random()){
		try{
			$('.loading').toggleClass('load');
		}catch(e){
			$('.loader_main .loading').toggleClass('load');
		}
	};
	this.error =  {
		x: 10,
		y:  0,
		e:  0,
		dur:  10000
	};
	this.ajax = ajax;
	Object.defineProperties(this, {
		'sidebar':{
			get: function (){
				return sidebar;
			}
		},
		'dashboard':{
			get: function(){
				return dashboard;
			}
		},
		'matric':{
			get: function(){
				return mat;
			}
		},
		'login':{
			get: function(){
				login();
			}
		},
		'ajaxresponse':{
			get: function(){
				return ajaxresponce;
			},
			set: function(value){
				ajaxresponce = value;
			}
		},
		'ajaxurl':{
			get: function() { return ajaxurl; 	}
		},
		'loader':{
			get: function(){
				return loader();
			},
			set: function(value){
				loader(value);
			}
		},
		'loadActivity':{
			set: function(value){
				loadActivity(value);
			}
		},
		'back':{
			get: function(){
				back();
			}
		}
	});
}
var user = new User();
function loadScreen(value){
	$(value.view).addClass('fade');
	if(!value.dontsave && user.history.indexOf([value.view,$(value.view).html()]) == -1){user.history.push([value.view,$(value.view).html()]);}
	setTimeout(function(){
		if(value.title){
			value.title = '<a href="javascript:user.back" class="back_title"><i class="fa fa-long-arrow-left"></i> '+value.title+'</a>';
			$(value.view).html(value.title+value.html)
		}else{
			$(value.view).html(value.html);
		}
		$(value.view).removeClass('fade');
	},200);
}
function ret(){
	if(user.history.length>0){
		user.loadActivity = {
			'view':user.history[user.history.length-1][0],
			'html':user.history[user.history.length-1][1],
			'title':false,
			'dontsave':true
		};
		user.history.pop();
	}
}
function reset(){
	var t = document.getElementById('admin_reset_form');
	if(t){
		user.ajax({
			mark_up: 'reset_student',
			email: t.email.value,
			reset_student: t.submit.value
		},
		function(){
			if(JSON.parse(user.ajaxresponse).status){
				$('#admin_reset_form .info').text("A password reset link has been sent to the email you provided");
				setTimeout(function(){
					user.back;
				}, 4000);
			}else{
				$('#admin_reset_form .info').text("User not found!");
				$('#admin_reset_form .info').css('color', 'red');
				$('#admin_reset_form .info').addClass('wrong');
			}
		});
	}else{
		user.ajax({
			mark_up: 'admin_reset'
		},
		function (){
			var r; 
			try{
				r = JSON.parse(user.ajaxresponse).markup;
			}catch(r){
				r = user.ajaxresponse;
			}
			user.loadActivity = {'view':'#main_content', 'html':r, 'title':'Login'};
		});
	}
}
function ajax(obj, o){
	let loadref = this.loader;
	$.ajax({
		type: 'POST',
		url: this.ajaxurl,
		data: obj,
		success: function(response){
			user.loader = loadref;
			user.ajaxresponse = response;
			o(response);
		}
	});
}
function log(){
	var form = document.getElementById('admin_login');
	$('#admin_login .info').removeClass('wrong');
	user.ajax({
		mark_up: 'student_login',
		'username': form.username.value,
		'password': form.password.value
	},function(){
		try{
			var r = JSON.parse(user.ajaxresponse);
			if(!r.status){
				$('#admin_login .info').addClass('wrong');
				$('#admin_login .info').text(r.msg);
			}else{
				document.location = 'dashboard.php';
			}
		}catch(e){
			throw new Error(user.ajaxresponse);
		}
	});
}
function sideb(){
	$('.side_bar').toggleClass('show');
	//just for collaspe purposes...
}
function selectMenu(){
	var menuItems = $('.side_bar ul li');
	for(var i = 0; i < menuItems.length; i++){
		menuItems.eq(i).removeClass('active');
		if(menuItems.eq(i).text().toLowerCase().indexOf(this.selection.toLowerCase()) != -1){
			menuItems.eq(i).addClass('active');
		}
	}
	user.sidebar.toggle;
}
function canvasRun(labels, dataSets){
	labels.reverse();
	dataSets.reverse();
	var mychart = document.getElementById('myCanvas').getContext('2d');
	Chart.defaults.global.defaultFontFamily = 'arial';
	Chart.defaults.global.defaultFontSize = 12;
	var resultChart = new Chart(mychart, {
	    type: 'line', //bar, horizontalBar, pie, radar, line, doughnut, polarArea
	    data: {
	        labels: labels,
	        datasets: [{
	            label: 'Average CGPA',
	            data:dataSets,
	            backgroundColor: ['green','red','yellow','black','blue'],
	            borderWidth: 1,
	            borderColor: '#5e67f4',
	            hoverBoderWidth: 3,
	            hoverBoderColor: '#000'
	        }]
	    },
	    options: {
	        legend: {
	            display: false
	        },
	        title: {
	            display: true,
	            text: 'Average CGPAs from '+labels[0]+' to '+labels[labels.length-1]+' Academic Sessions',
	            fontSize: 18
	        }
	    }
	});
}
user.error.err = function(obj = {}){
	obj.color = (obj.color)? 'style="background:'+obj.color+';"':'';
	obj.text = (obj.text)? obj.text:'';
	obj.dur = (obj.dur)? obj.dur:5000;
	obj.title = (obj.title)? obj.title:'ERROR';
	obj.closeBtnColor = (obj.closeBtnColor)? 'style="color:'+obj.closeBtnColor+'"':'';
	var c = $('<div></div>').addClass('app_error');
	c.attr('id', this.e);
	c.append(`<div class="content">
		<div class="header" `+obj.color+`>
		<div class="container"><h5>`+obj.title+`<a class="close-error" `+obj.closeBtnColor+` href="javascript:user.error.close(`+this.e+`)"><i class="fa fa-times"></i></a></h5></div>
		</div>
		<div class="body"><div class="container">`+obj.text+`</div></div>
		</div>
		`);
	$('#dash').append(c);
	var elevateHeight = (c.outerHeight()/window.outerHeight)*100+3;
	for(var i = this.e; i >= 0; i--){
		try{
			var b = ($('#'+i).css('bottom').substr(0,$('#'+i).css('bottom').indexOf('px'))/window.innerHeight)*100;
		}catch(b){
			continue;
		}
		b += elevateHeight;
		$('#'+i).css('bottom', b+'%');
		if(b>54.3)$('#'+i).remove();
	}
	c.css('bottom', this.x+'%');
	var ref = this.e;
	if(obj.dur){
		setTimeout(function(){
			user.error.close(ref);
		}, obj.dur)
	}
	this.e++;
	return ref;
}
user.error.close = function(n){
	var dropHeight = ($('#'+n).outerHeight()/window.innerHeight)*100;
	for(var i = (n); i >= 0; i--){
		try{
			var b = ($('#'+i).css('bottom').substr(0,$('#'+i).css('bottom').indexOf('px'))/window.innerHeight)*100;
		}catch(b){
			continue;
		}
		b -= dropHeight;
		$('#'+i).css('bottom', b+'%');
	}
	$('#'+n).addClass('fade');
	setTimeout(function(){
		$('#'+n).remove();
		this.e--;
	}, 200);
}
var courses={sesh:false,semester:false};
function init_courses(option = false){
	user.sidebar.select('courses');
	if(option[0] == 'courses_register'){
		courses.semester = option[1];
		user.ajax({
			mark_up: 'student_courses',
			department: dept,
			sesh: courses.sesh,
			semester: courses.semester
		},
		function (response){
			var r;
			try{
				r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.loadActivity = {'view':'#main_content','html':r.markup,'title': 'Semesters'};
				}
			}catch(r){
				user.error.err({'text':response});
			}
		});
	}
	else if(option[0] == 'semesters'){
		courses.sesh = option[1];
		user.ajax({
			markup: 'default_semesters',
			option: 'courses_register',
			js:'init_courses'
		},
		function (response){
			var r;
			try{
				user.loadActivity = {'view':'#main_content','html':JSON.parse(response).markup,'title': 'Levels'};
			}catch(e){
				user.error.err({'text':response});
			}
		});
	}else{
		user.ajax({
			mark_up: 'student_levels',
			sesh: yoe,
			option: 'semesters',
			js:'init_courses'
		},
		function (response){
			var r;
			try{
				r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.loadActivity = {'view':'#main_content','html':r.markup};
				}
			}catch(e){
				user.error.err({'text':response});
			}
		});
	}
}
function courses_register(option=false){
	if(option[0] == 'add'){
		var form = $('#courseadd_form')[0]
		user.ajax({
			mark_up: 'student_register_course',
			course: form.course.value,
			sesh: courses.sesh,
			semester: courses.semester,
			action: 'add'
		},function(response){
			try{
				var r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.back;
					user.loader;
					setTimeout(function(){init_courses(['courses_register', courses.semester]); user.loader;}, 1000);
				}
			}catch(r){
				user.error.err({'text':response});
			}
		});
	}else if(option[0] == 'delete'){
		user.ajax({
			mark_up: 'student_register_course',
			course: option[1],
			action: 'delete'
		},function(response){
			try{
				var r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.back;
					user.loader;
					setTimeout(function(){init_courses(['courses_register', courses.semester]); user.loader;}, 1000);
				}
			}catch(r){
				user.error.err({'text':response});
			}
		});
	}else if(option[0] == 'save'){
		user.ajax({
			mark_up: 'student_register_course',
			action: 'save'
		},function(response){
			try{
				var r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.back;
					user.loader;
					setTimeout(function(){init_courses(['courses_register', courses.semester]); user.loader;}, 1000);
				}
			}catch(r){
				user.error.err({'text':response});
			}
		});
	}
}
function notification(){
	user.dashboard.init;
	$('#main_content').addClass('hide-graphical');
	user.sidebar.select('notifications');
	setTimeout(function(){
		document.location = '#notifications';
	},1000);
}
function lecturer_updater(option = false){
	if(option){
		user.ajax({
			mark_up:'not_updater',
			not_for:'student_id',
			opt: option
		},function(response){
			response = JSON.parse(response);
			if(response.not){$('#notification_count').html(response.not);}
			if(response.msg){$('#notification_count').text(response.msg);}
			lecturer_updater();
		});
		notification();
		return;
	}
	user.ajax({
		mark_up:'not_updater',
		not_for:'student_id'
	},function(response){
		response = JSON.parse(response);
		if(response.not){$('#notification_count').html(response.not);}
		if(response.msg){$('#notification_count').text(response.msg);}
	});
	setTimeout(lecturer_updater, 60000);
}
var results={sesh:false,semester:false};
function init_results(option = false){
	user.sidebar.select('results');
	if(option == 'land'){
		user.ajax({
			mark_up:'student_result'
		},function(response){
			try{
				response=JSON.parse(response);
				if(!response.status){user.error.err({text:response.error});}
				else{user.loadActivity = {'view':'#main_content','html':response.markup};}
			}
			catch(e){user.error.err({text:response});}
		});
		return;
	}else if(option=='carryovers'){
		user.ajax({
			mark_up:'student_result',
			opt: 'carryovers'
		},function(response){
			try{
				response=JSON.parse(response);
				if(!response.status){user.error.err({text:response.error});}
				else{user.loadActivity = {'view':'#main_content','html':response.markup,'title':'Back'};}
			}
			catch(e){user.error.err({text:response});}
		});
		return;
	}
	if(option[0] == 'results'){
		results.semester = option[1];
		user.ajax({
			mark_up: 'student_result',
			department: dept,
			opt: 'full',
			sesh: results.sesh,
			semester: results.semester
		},
		function (response){
			var r;
			try{
				r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.loadActivity = {'view':'#main_content','html':r.markup,'title': 'Semesters'};
				}
			}catch(r){
				user.error.err({'text':response});
			}
		});
	}
	else if(option[0] == 'semesters'){
		results.sesh = option[1];
		user.ajax({
			markup: 'default_semesters',
			option: 'results',
			js:'init_results'
		},
		function (response){
			var r;
			try{
				user.loadActivity = {'view':'#main_content','html':JSON.parse(response).markup,'title': 'Levels'};
			}catch(e){
				user.error.err({'text':response});
			}
		});
	}else{
		user.ajax({
			mark_up: 'student_levels',
			sesh: yoe,
			option: 'semesters',
			js:'init_results'
		},
		function (response){
			var r;
			try{
				r = JSON.parse(response);
				if(!r.status){
					user.error.err({'text':r.error});
				}else{
					user.loadActivity = {'view':'#main_content','html':r.markup,title:'Back'};
				}
			}catch(e){
				user.error.err({'text':response});
			}
		});
	}
}
$(document).ready(function(){
	if($('#dash').html()){ user.dashboard.init; }
});