var app = {
	dashboard: {},
	history: new Array(),
	ajaxresponse: '',
	results: {
		department: false,
		session: false,
		semester: false,
		targert_session: false,
		view_sesh: false,
		course: false
	},
	add_student: {},
	courses:{
		department: false,
		semester: false,
		sesh: false,
		view_sesh: false
	},
	loading: function (){
		$('.loader_main .loading').addClass('load');
	},
	notloading: function (){
		$('.loader_main .loading').removeClass('load')
	},
	error: {
		x: 10,
		y: 0,
		e: 0,
		dur: 10000
	},
	lecturer:{
		viewing: ''
	},
	student:{
		department: '',
		level: '',
		viewig: '',
		sesh: ''
	}
};
var admin = {};
admin.reset = function(){
	var t = document.getElementById('admin_reset_form');
	if(t){
		app.ajax({
			email: t.email.value,
			resetadmin: t.submit.value
		},
		function(){
			if(JSON.parse(app.ajaxresponse).status){
				$('#admin_reset_form .info').text("A password reset link has been sent to the email you provided");
				setTimeout(function(){
					app.back();
				}, 4000);
			}else{
				$('#admin_reset_form .info').text("User not found!");
				$('#admin_reset_form .info').css('color', 'red');
				$('#admin_reset_form .info').addClass('wrong');
			}
		});
	}else{
		app.ajax({
			markup: 'admin_reset'
		},
		function (){
			app.loadActivity('#main_content', JSON.parse(app.ajaxresponse).markup, 'Login');
		});
	}
}
app.loadActivity = function(view, html, title=false,dontsave=false){
	$(view).addClass('fade');
	if(!dontsave && this.history.indexOf([view,$(view).html()]) == -1){this.history.push([view,$(view).html()]);}
	setTimeout(function(){
		if(title){
			title = '<a href="javascript:app.back()" class="back_title"><i class="fa fa-long-arrow-left"></i> '+title+'</a>';
			$(view).html(title+html)
		}else{
			$(view).html(html);
		}
		$(view).removeClass('fade');
	},200);
}
app.dashboard.init = function(){
	admin_sidebar.selectMenu('dashboard');
	lecturer_updater();
	$('#main_content').removeClass('hide-graphical');
	app.ajax({
		mark_up: 'dashboard'
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup);
				setTimeout(function(){ app.dashboard.canvas(); }, 1000);
			}
		}catch(r){
			app.loadActivity('#main_content',app.ajaxresponse);
		}
	});
}
app.dashboard.canvas = function(){
	app.ajax({
		mark_up: 'dashboard',
		graph: true
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				$('#graph').html(r.markup);
			}else{
				canvasRun(r.labels, r.dataSets);
			}
		}catch(r){
			app.loadActivity('#main_content',app.ajaxresponse);

		}
	});
}
app.back = function(){
	if(this.history.length>0){
		this.loadActivity(this.history[this.history.length-1][0],this.history[this.history.length-1][1],false,true);
		this.history.pop();
	}
}
app.ajax = function(obj, o, u='../res/department.requests.php'){
	app.loading();
	$.ajax({
		type: 'POST',
		url: u,
		data: obj,
		success: function(response){
			app.notloading();
			app.ajaxresponse = response;
			o();
		}
	});
}
app.doLogin = function(n){
	if(n == 'admin'){
		var form = document.getElementById('admin_login');
		$('#admin_login .loading').addClass('load');
		$('#admin_login .info').removeClass('wrong');
		setTimeout(function(){
			app.ajax({
						'username':form.username.value,
						'password': form.password.value,
						'admin': form.submit.value
					}, function(){
						var response = JSON.parse(app.ajaxresponse);
						if(!response.status){
							$('#admin_login .loading').removeClass('load');
							$('#admin_login .info').addClass('wrong');
							$('#admin_login .info').text(response.msg);
						}else{
							document.location = 'admin_panel.php';
						}
			})
		}, 1);
	}
}
app.add_student.init = function(){
	admin_sidebar.selectMenu('add student(s)');
	app.ajax({
		mark_up: 'add_student'
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup);
			}
		}catch(r){
			app.loadActivity('#main_content',app.ajaxresponse);
		}
	});
}
app.add_student.add = function(){
	var form = document.getElementById('student_add_form');
	$('#student_add_form .info').removeClass('wrong');
	app.ajax({
		mark_up: 'student_add',
		matric: form.matric.value,
		department: form.department.value,
		level: form.level.value
	},function(){
		try{
			var r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				$('#student_add_form .info').addClass('wrong');
				app.error.err(r.msg);
			}else{
				if(r.mark_up){
					app.loadActivity('#main_content',r.mark_up,'Back');
					return;
				}
				$('#student_add_form .info').removeClass('wrong');
				$('#student_add_form .info').text(r.markup);
				$('#student_add_form .info').css('color','green');
				form.department.value = '';
				form.level.value = '';
				form.matric.value = '';
			}
		}catch(r){
			throw new Error(app.ajaxresponse);
		}
	});
}
app.results.init = function(){
	admin_sidebar.selectMenu('results');
	app.ajax({
		markup: 'admin_results'
	},
	function (){
		app.loadActivity('#main_content', JSON.parse(app.ajaxresponse).markup);
	});
}
app.results.rawscores = function(options = []){
	if(options == 'reject'){
		app.ajax({
			mark_up: 'raw_markup',
			department: app.results.department,
			sesh: app.results.session,
			semester: app.results.semester,
			view_sesh: app.results.view_sesh,
			course: app.results.course,
			opt: 'reject'
		}, 
		function(){
			try{
				var response = JSON.parse(app.ajaxresponse);
				if(!response.status){
					app.error.err(response.error);
				}else{
					app.back();
					app.back();
					app.back();
					app.loading();
					setTimeout(function(){app.results.rawscores(['semester', app.results.view_sesh]);app.notloading();},
						1000);
				}
			}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
		return;
	}else if(options == 'accept'){
		app.ajax({
			mark_up: 'raw_markup',
			department: app.results.department,
			sesh: app.results.session,
			semester: app.results.semester,
			view_sesh: app.results.view_sesh,
			course: app.results.course,
			opt: 'accept'
		}, 
		function(){
			try{
				var response = JSON.parse(app.ajaxresponse);
				if(!response.status){
					app.error.err(response.error);
				}else{
					app.back();
					app.loading();
					setTimeout(function(){app.results.rawscores(['course', app.results.course]);app.notloading();},
						1000);
				}
			}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
		return;
	}
	if(options[0] == 'results' || options[0] == 'course'){
		app.results.semester = (options[0] == 'results')? options[1]:app.results.semester;
		app.results.course = (options[0] == 'course')? options[1]:false;
		app.ajax({
			mark_up: 'raw_markup',
			department: app.results.department,
			sesh: app.results.session,
			semester: app.results.semester,
			view_sesh: app.results.view_sesh,
			course: app.results.course
		}, 
		function(){
			try{ app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Return');}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
	}else if(options[0] == 'semester'){
		app.results.view_sesh = options[1];
		app.ajax({
			markup: 'default_semesters',
			option: 'results',
			js: 'app.results.rawscores'
		}, 
		function(){
			try{ app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Levels');}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
	}else if(options[0] == 'levels'){
		app.results.session = options[1];
		app.ajax({
			mark_up: 'student_levels',
			sesh: options[1],
			option: 'semester',
			js: 'app.results.rawscores'
		}, 
		function(){
			try{ app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Sessions');}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
	}else if(options[0] == 'sesh'){
		app.results.department = options[1];
		app.ajax({
			mark_up: 'default_sesh',
			option: 'levels',
			js: 'app.results.rawscores'
		}, 
		function(){
			try{ app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Departments');}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
	}else{
		app.ajax({
			markup: 'default_dept',
			option: 'sesh',
			js: 'app.results.rawscores'
		}, 
		function(){
			try{ app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Results');}
			catch(e){ throw new Error(app.ajaxresponse); }
		});
	}
}
app.results.full = function(option = false){
	if(option[0] == 'full_results'){
		app.results.semester = option[1];
		app.ajax({
			mark_up: 'fullresult_mark',
			department: app.results.department,
			session: app.results.session,
			semester: app.results.semester,
			level: app.results.targert_session
		}, 
		function(){
			var r;
			try{
				r = JSON.parse(app.ajaxresponse);
				if(!r.status){
					app.error.err(r.error, 5000);
				}else{
					app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Semesters');
				}
			}catch(r){
				app.loadActivity('#main_content',app.ajaxresponse,'Semester');
			}
		});
	}else if(option[0] == 'semester'){
		app.results.targert_session = option[1];
		app.ajax({
			markup: 'default_semesters',
			option: 'full_results',
			js: 'app.results.full'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Sessions');
			}catch(e){
				app.loadActivity('#main_content',app.ajaxresponse,'Sessions');
			}
		});
	}else if(option[0] == 'targert_sesh'){
		app.results.session = option[1];
		app.ajax({
			mark_up: 'targert_sesh',
			current_sesh: app.results.session,
			option: 'semester',
			js: 'app.results.full'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Sessions');
			}catch(e){
				app.loadActivity('#main_content',app.ajaxresponse,'Sessions');
			}
		});
	}else if(option[0] == 'session'){
		app.results.department = option[1];
		app.ajax({
			mark_up: 'default_sesh',
			option: 'targert_sesh',
			js: 'app.results.full'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Levels');
			}catch(e){
				app.loadActivity('#main_content',app.ajaxresponse,'Levels');
			}
		});
	}else{
		app.ajax({
			markup: 'default_dept',
			option: 'session',
			js: 'app.results.full'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Results');
			}catch(e){
				app.loadActivity('#main_content',app.ajaxresponse,'Results');
			}
		});
	}
}
app.courses.init = function(){
	admin_sidebar.selectMenu('courses');
	app.ajax({
		markup: 'default_dept',
		option: 'sesh',
		js: 'app.courses.course'
	},
	function (){
		try{app.loadActivity('#main_content', JSON.parse(app.ajaxresponse).markup);}
		catch(e){
			app.error.err(app.ajaxresponse);
		}
	});
}
app.courses.course = function(option){
	if(option[0] == 'courses'){
		app.courses.semester = option[1];
		app.ajax({
			mark_up: 'courses_mark',
			department: app.courses.department,
			semester: app.courses.semester,
			sesh: app.courses.sesh,
			view_sesh: app.courses.view_sesh
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Semesters');
			}catch(e){
				throw new Error(app.ajaxresponse);
			}
		});
	}else if(option[0] == 'semester'){
		app.courses.view_sesh = option[1];
		app.ajax({
			markup: 'default_semesters',
			option: 'courses',
			js: 'app.courses.course'
		}, 
		function(){
			app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Levels');
		});
	}else if(option[0] == 'levels'){
		app.courses.sesh = option[1];
		app.ajax({
			mark_up: 'student_levels',
			sesh: option[1],
			option: 'semester',
			js: 'app.courses.course'
		}, 
		function(){
			app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Levels');
		});
	}else if(option[0] == 'sesh'){
		app.courses.department = option[1];
		app.ajax({
			mark_up: 'default_sesh',
			option: 'levels',
			js: 'app.courses.course'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Courses');
			}catch(e){
				throw new Error(app.ajaxresponse);
			}
		});
	}
}
app.courses.delete = function(n){
	app.ajax({
		delete_course: n
	},
	function(){
		var r = JSON.parse(app.ajaxresponse);
		if(!r.status){
			app.error.err(r.error);
		}else{
			app.back();
			app.loading();
			setTimeout(function(){ app.courses.course(['courses', app.courses.semester]); app.notloading(); }, 1000);
		}
	});
}
app.error.err = function(text, dur=false, color='', title='ERROR', text_color=""){
	var c = $('<div></div>').addClass('app_error');
	c.attr('id', this.e);
	c.append(`<div class="content">
		<div class="header" style="background:`+color+`;">
		<div class="container"><h5>`+title+`<a class="close-error" style="text-shadow: black;" href="javascript:app.error.close(`+this.e+`)">
		<i class="fa fa-times"></i></a></h5></div>
		</div>
		<div class="body" style="`+text_color+`"><div class="container">`+text+`</div></div>
		</div>
		`);
	$('#dash').append(c);
	var elevateHeight = (c.outerHeight()/window.outerHeight)*100+3;
	for(var i = this.e; i >= 0; i--){
		try{
			var b = ($('#'+i).css('bottom').substr(0,$('#'+i).css('bottom').indexOf('px'))/window.innerHeight)*100;
		}catch(e){
			continue;
		}
		b += elevateHeight;
		$('#'+i).css('bottom', b+'%');
		if(b>100.3)$('#'+i).remove();
	}
	c.css('bottom', this.x+'%');
	var ref = this.e;
	if(dur){
		setTimeout(function(){
			app.error.close(ref);
		}, dur)
	}
	this.e++;
	return ref;
}
app.error.close = function(n = 'all'){
		if(n == 'all'){
			$('.app_error').remove();
			this.e = 0;
			return;
		}
		var dropHeight = ($('#'+n).outerHeight()/window.innerHeight)*100;
		for(var i = (n); i >= 0; i--){
			try{
				var b = ($('#'+i).css('bottom').substr(0,$('#'+i).css('bottom').indexOf('px'))/window.innerHeight)*100;
			}catch(e){
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
		return n;
}
app.courses.add = function(){
	var form = document.getElementById('courseadd_form');
	app.ajax({
		mark_up: 'add_course',
		code: form.code.value,
		department: app.courses.department,
		semester: app.courses.semester,
		title: form.title.value,
		unit: form.unit.value,
		lecturer: form.lecturer.value,
		sesh: app.courses.sesh
	},
	function(){
		try{
			var r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error);
			}else if(r.status == 'js'){
				form.lecturer.value = '';
				app.courses.error_ref = app.error.err(r.error+'<a href="javascript:app.courses.add()" class="btn btn-warning pull-right">Continue</a>');
			}else{
				app.back();
				app.loading();
				setTimeout(function(){ app.courses.course(['courses', app.courses.semester]); app.notloading(); }, 1000);
			}
		}catch(e){
			throw new Error(app.ajaxresponse);
		}
	});
}
var lecturer_add_option = 'lecturer';
app.lecturer.init = function(opt = 'lecturer'){
	admin_sidebar.selectMenu('lecturers');
	lecturer_add_option = opt;
	app.ajax({
		mark_up: 'lecturer',
		option: opt
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error);
			}else{
				app.loadActivity('#main_content', r.markup);
			}
		}catch(r){
			app.loadActivity('#main_content', app.ajaxresponse);
		}
	});
}
app.lecturer.view = function(n){
	this.viewing = n;
	app.ajax({
		lecturer: n
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error);
			}else{
				app.loadActivity('#lecturers', r.markup, "Lecturers");
			}
		}catch(r){
			app.loadActivity('#lecturers', app.ajaxresponse);
		}
	});
}
app.lecturer.assign = function(){
	var form = document.getElementById('assigncourse_form');
	var t = form.code.value;
	t = t.replace(' [', '[');
	t = t.split('[');
	t[1] = t[1].replace(']', '');
	app.ajax({
		mark_up: 'course_assign',
		code: t[0],
		department: t[1],
		id: form.id.value
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.back();
				app.loading();
				setTimeout(function(){ app.lecturer.view(app.lecturer.viewing); app.notloading(); }, 1000);
			}
		}catch(r){
			app.loadActivity('#lecturers', app.ajaxresponse);
		}
	});
}
app.lecturer.unassign = function(n){
	app.ajax({
		course_unassign: n
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.back();
				app.loading();
				setTimeout(function(){ app.lecturer.view(app.lecturer.viewing); }, 1000);
			}
		}catch(r){
			app.loadActivity('#lecturers', app.ajaxresponse);
		}
	});
}
app.student.init = function(option){
	admin_sidebar.selectMenu('students');
	app.ajax({
		markup: 'default_dept',
		option: 'sesh',
		js: 'app.student.students'
	},
	function (){
		app.loadActivity('#main_content', JSON.parse(app.ajaxresponse).markup);
	});
}
app.student.students = function(option){
	admin_sidebar.selectMenu('students');
	if(option[0] == 'students'){
		app.student.sesh = option[1];
		app.ajax({
			mark_up: 'student',
			department: app.student.department,
			level: app.student.sesh
		}, 
		function(){
			var r;
			try{
				r = JSON.parse(app.ajaxresponse);
				if(!r.status){
					app.error.err(r.error, 5000);
				}else{
					app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Levels<h5>'+app.student.department+' '+app.student.level+' Level '+'Students<h5>');
				}
			}catch(r){
				app.loadActivity('#main_content',app.ajaxresponse,'Levels<h5>'+app.student.department+' '+app.student.level+' Level '+'Students<h5>');
			}
		});
	}else if(option[0] == 'sesh'){
		app.student.department = option[1];
		app.ajax({
			mark_up: 'default_sesh',
			option: 'students',
			js: 'app.student.students'
		}, 
		function(){
			try{
				app.loadActivity('#main_content',JSON.parse(app.ajaxresponse).markup,'Courses');
			}catch(e){
				throw new Error(app.ajaxresponse);
			}
		});
	}
}
app.student.view = function(n){
	this.viewing = n;
	app.ajax({
		student: n,
		level: this.sesh
	},
	function (){
		var r;
		try{
			r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error);
			}else{
				app.loadActivity('#main_content', r.markup, "Students");
			}
		}catch(r){
			app.loadActivity('#main_content', app.ajaxresponse, "Students");
		}
	});
}
app.student.reset = function(n){
	app.ajax({
		mark_up: 'reset_student_account',
		id: n
	},function(){
		try{
			if(!JSON.parse(app.ajaxresponse).status){
				app.error.err(JSON.parse(app.ajaxresponse).error);
			}else{
				app.error.err(JSON.parse(app.ajaxresponse).markup);
			}
		}catch(e){
			throw new Error(app.ajaxresponse);
		}
	})
}
var admin_sidebar = {
	sidebar: $('.side_bar'),
	hide: ''
};
admin_sidebar.toggle = function(){
	this.sidebar.toggleClass('show');
	//just for collaspe purposes...
}
admin_sidebar.selectMenu = function(n){
	var menuItems = $('.side_bar ul li');
	for(var i = 0; i < menuItems.length; i++){
		menuItems.eq(i).removeClass('active');
		if(menuItems.eq(i).text().toLowerCase().indexOf(n.toLowerCase()) != -1){
			menuItems.eq(i).addClass('active');
		}
	}
	this.toggle();
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
function changeCourseState(n, m){
	app.ajax({
		mark_up:'changeCourseState',
		id: n
	},function(){
		try{
			var r = JSON.parse(app.ajaxresponse);
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.back();
				app.loading();
				setTimeout(function(){ app.courses.course(['courses', m]); app.notloading(); }, 1000);
			}
		}catch(r){
			throw new Error(app.ajaxresponse);
		}
	});
}
function jsonParse(){
	try{
		var r = JSON.parse(app.ajaxresponse);
		return r;
	}catch(e){
		app.error.err(app.ajaxresponse, 5000)
	}
	return false;
}
function lecturer_add(option = false){
	if(option[0] == 'delete'){
		app.ajax({
			mark_up: 'lecturer_add',
			action: 'delete',
			id: option[1] 
		},function(){
			var r = jsonParse();
			if(!r){return;}
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.back();
				app.loading();
				setTimeout(function(){app.lecturer.init(lecturer_add_option); app.loading();}, 1000);
			}
		});
	}else{
		app.ajax({
			mark_up: 'lecturer_add'
		},function(){
			var r = jsonParse();
			if(!r){return;}
			if(!r.status){
				app.error.err(r.error, 5000);
			}else{
				app.back();
				app.loading();
				setTimeout(function(){
					app.lecturer.init('tmp');
					app.error.err(r.markup, 60000, '#333','Alert','#333');
					app.loading();
				},1000);
			}
		});
	}
}
function notification(){
	app.dashboard.init();
	$('#main_content').addClass('hide-graphical');
	admin_sidebar.selectMenu('notifications');
	setTimeout(function(){
		document.location = '#notifications';
	},1000);
}
function lecturer_updater(option = false){
	if(option){
		app.ajax({
			mark_up:'not_updater',
			not_for: 'user_id',
			opt: option
		},function(){
			var response = JSON.parse(app.ajaxresponse);
			if(response.not){$('#notification_count').html(response.not);}
			if(response.msg){$('#notification_count').text(response.msg);}
			lecturer_updater();
		});
		notification();
		lecturer_updater();
		return;
	}
	app.ajax({
		mark_up:'not_updater',
		not_for: 'user_id'
	},function(){
		var response = JSON.parse(app.ajaxresponse);
		if(response.not){$('#notification_count').html(response.not);}
		if(response.msg){$('#notification_count').text(response.msg);}
	});
	setTimeout(lecturer_updater, 60000);
}
function prf_update(n=document.getElementById('upload_image_croppie')){
	if(!n){ setTimeout(prf_update,1000); return;}
	$('#upload_image_croppie').on('change',function(){	
		var reader = new FileReader();
		reader.onload = function(event){
			$image_crop.croppie('bind', {
				url: event.target.result
			}).then(function(){
				console.log("BINDING COMPLETE!");
			});
		}
		reader.readAsDataURL(this.files[0]);
		$('#imageUploadModal').modal('show');
	});
}
function image_upload(){
	$image_crop.croppie('result', {
		type: 'canvas',
		size: 'viewport'
	}).then(function(response){
		$.ajax({
			url: "../res/department.requests.php",
			method: 'POST',
			data: {mark_up:'account_settings',option:'prf_image','image': response},
			success: function(data){
				$('#imageUploadModal').modal('hide');
				account_settings();
			}
		});
	});
}
$(document).ready(function(){
	app.dashboard.init();
	$image_crop = $('#image_selected').croppie({
		enableExif: true,
		viewport: {
			width: 200,
			height: 200,
			type: 'circle'
		},
		boundary: {
			width: 300,
			height: 300
		}
	});
	prf_update();
});