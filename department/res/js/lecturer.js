var app = {
	ajaxresponse: false,
	loader: function(){
		try{
			$('.loading').toggleClass('load');
		}catch(e){
			$('.loader_main .loading').toggleClass('load');
		}
	},
	history: new Array(),
	errorindex: 0,
	errorPosX: 10,
	ajaxurl: '../res/department.requests.php',
	main_view: '#main_content'
};
var user = {}
Object.defineProperty(user,'reset',{
	get:function(){reset();}
});
function side_bar(){
	$('.side_bar').toggleClass('show');
	//....
}
function ajax(obj, o){
	app.loader();
	$.ajax({
		type: 'POST',
		url: app.ajaxurl,
		data: obj,
		success: function(response){
			app.loader();
			try{
				o(JSON.parse(response));
			}catch(e){
				error({text: response});
			}
		}
	});
}
function selectMenu(selection){
	var menuItems = $('.side_bar ul li');
	for(var i = 0; i < menuItems.length; i++){
		menuItems.eq(i).removeClass('active');
		if(menuItems.eq(i).text().toLowerCase().indexOf(selection.toLowerCase()) != -1){
			menuItems.eq(i).addClass('active');
		}
	}
	side_bar();
}
function error(obj = {}){
	obj.color = (obj.color)? 'style="background:'+obj.color+';"':'';
	obj.text = (obj.text)? obj.text:'';
	obj.dur = (obj.dur)? obj.dur:5000;
	obj.title = (obj.title)? obj.title:'ERROR';
	obj.closeBtnColor = (obj.closeBtnColor)? 'style="color:'+obj.closeBtnColor+'"':'';
	var c = $('<div></div>').addClass('app_error');
	c.addClass('install_error');
	c.attr('id', app.errorindex);
	c.append(`<div class="content">
		<div class="header" `+obj.color+`>
		<div class="container"><h5>`+obj.title+`
		<a class="close-error" `+obj.closeBtnColor+` href="javascript:closeerror(`+app.errorindex+`)">
		<i class="fa fa-times"></i></a></h5></div>
		</div>
		<div class="body"><div class="container">`+obj.text+`</div></div>
		</div>
		`);
	$('#dash').append(c);
	var elevateHeight = (c.outerHeight()/window.outerHeight)*100+3;
	for(var i = app.errorindex; i >= 0; i--){
		try{
			var b = ($('#'+i).css('bottom').substr(0,$('#'+i).css('bottom').indexOf('px'))/window.innerHeight)*100;
		}catch(e){
			continue;
		}
		b += elevateHeight;
		$('#'+i).css('bottom', b+'%');
		if(b>54)$('#'+i).remove();
	}
	c.css('bottom', app.errorPosX+'%');
	var ref = app.errorindex;
	setTimeout(function(){
		closeerror(ref);
	}, obj.dur)
	app.errorindex++;
	return ref;
}
var scoreSheet = {department:'',course:''};
function score_sheet(option = false){
	selectMenu('score sheet');
	if(option[0] == 'score_sheet'){
		scoreSheet.department = option[1];
		ajax({
			mark_up: 'lecturer_courses',
			options: 'sheet',
			code: scoreSheet.course,
			dept: scoreSheet.department
		},function(response){
			if(!response.status){ error({text:response.error}); return; }
			loadScreen({view:app.main_view,html:response.markup, title:'Departments'});
		});
	}
	else if(option[0] == 'dept'){
		scoreSheet.course = option[1];
		ajax({
			mark_up: 'lecturer_courses',
			options: 'dept',
			code: option[1],
			option: 'score_sheet',
			js: 'score_sheet'
		},function(response){
			if(!response.status){ error({text:response.error}); return; }
			loadScreen({view:app.main_view,html:response.markup, title:'Courses'});
		});
	}else{
		ajax({
			mark_up: 'lecturer_courses',
			option: 'dept',
			js: 'score_sheet'
		},function(response){
			if(!response.status){ error({text:response.error}); return; }
			loadScreen({view:app.main_view,html:response.markup});
		});
	}
}
function closeerror(n = 'not set'){
	if(n == 'all'){
		for(var i = app.errorindex; i >= 0; i--){
			closeerror(i);
		}
		return;
	}else if(n == 'not set'){
		closeerror(app.errorindex-1);
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
		app.errorindex--;
	}, 200);
}
function reset(){
	var t = document.getElementById('admin_reset_form');
	if(t){
		alert();
		ajax({
			mark_up: 'reset_lecturer',
			email: t.email.value,
			reset_student: t.submit.value
		},
		function(response){
			if(response.status){
				$('#admin_reset_form .info').text("A password reset link has been sent to the email you provided");
				setTimeout(function(){
					back();
				}, 4000);
			}else{
				$('#admin_reset_form .info').text("User not found!");
				$('#admin_reset_form .info').css('color', 'red');
				$('#admin_reset_form .info').addClass('wrong');
			}
		});
	}else{
		ajax({
			mark_up: 'admin_reset'
		},
		function (response){
			loadScreen({'view':app.main_view, 'html':response.markup, 'title':'Login'});
		});
	}
}
function loadScreen(value){
	$(value.view).addClass('fade');
	if(!value.dontsave && app.history.indexOf([value.view,$(value.view).html()]) == -1){app.history.push([value.view,$(value.view).html()]);}
	setTimeout(function(){
		if(value.title){
			value.title = '<a href="javascript:back()" class="back_title"><i class="fa fa-long-arrow-left"></i> '+value.title+'</a>';
			$(value.view).html(value.title+value.html)
		}else{
			$(value.view).html(value.html);
		}
		$(value.view).removeClass('fade');
	},200);
}
function back(){
	if(app.history.length>0){
		loadScreen({
			'view':app.history[app.history.length-1][0],
			'html':app.history[app.history.length-1][1],
			'title':false,
			'dontsave':true
		});
		app.history.pop();
	}
}
function doLogin(){
	var form = $('#admin_login')[0];
	$('#admin_login .info').removeClass('wrong');
	ajax({
		mark_up: 'lecturer_login',
		'username': form.username.value,
		'password': form.password.value
	},function(response){
		if(!response.status){
			$('#admin_login .info').addClass('wrong');
			$('#admin_login .info').text(response.msg);
		}else{
			document.location = 'dashboard.php';
		}
	});
}
function update_score(value, student_id, id, option,cd){
	ajax({
		mark_up: 'lecturer_update_score',
		score: value,
		id: student_id,
		opt: option,
		code: cd
	},function(response){
		if(!response.status){ error({text:response.error}); return;}
		$(id+'.total').html(response.total);
		$(id+'.grade').html(response.grade);
		$(id+'.remark').html(response.remark);
	});
}
function submit_score_sheet(n,de,c=0){
	ajax({
		mark_up: 'submit_score_sheet',
		code: n,
		dept: de,
		confirm: c
	},function(response){
		if(!response.status){var d=(response.dur)?response.dur:5000;error({text:response.error, dur:d});return;}
		back();
		app.loader();
		setTimeout(function(){score_sheet(['score_sheet', scoreSheet.department]);app.loader();},1000);
	});
}
function dashboard(){
	selectMenu('dashboard');
	$('#main_content').removeClass('hide-graphical');
	ajax({
		mark_up: 'lecturer_dashboard'
	},function(response){
		if(!response.status){error({text:response.error});}
		loadScreen({view:app.main_view,html:response.markup});
		setTimeout(function(){
			for(var i = 0; i < Object.keys(response.graph).length; i++){
				var id = Object.keys(response.graph)[i];
				if(response.graph[id].length>2){
					$('.'+id.replace('-graph','').replace(' ','')+' .card.card-body').html(response.graph[id]);
					continue;
				}
				var labels = response.graph[id][0];
				var dataSets = response.graph[id][1];
				canvasRun(labels, dataSets, id);
			}
		},1000);
	});
}
function canvasRun(labels, dataSets, id){
	labels.reverse();
	dataSets.reverse();
	var mychart = document.getElementById(id).getContext('2d');
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
	            text: 'Average Performance on '+id.replace('-graph','')+' from '+labels[0]+' to '+labels[labels.length-1]+' Academic Sessions',
	            fontSize: 18
	        }
	    }
	});
}
function notification(){
	dashboard();
	$('#main_content').addClass('hide-graphical');
	selectMenu('notifications');
	setTimeout(function(){
		document.location = '#notifications';
	},1000);
}
function lecturer_updater(option = false){
	if(option){
		ajax({
			mark_up:'not_updater',
			not_for:'lecturer_id',
			opt: option
		},function(response){
			if(response.not){$('#notification_count').html(response.not);}
			if(response.msg){$('#notification_count').text(response.msg);}
			lecturer_updater();
		});
		notification();
		return;
	}
	ajax({
		mark_up:'not_updater',
		not_for:'lecturer_id'
	},function(response){
		if(response.not){$('#notification_count').html(response.not);}
		if(response.msg){$('#notification_count').text(response.msg);}
	});
	setTimeout(lecturer_updater, 60000);
}
$(document).ready(function(){
	if($('#dash').html()){
		dashboard();
		lecturer_updater();
	}
});