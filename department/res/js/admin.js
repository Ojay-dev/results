function ajax_improved(obj,func,dur=5000){
	const u = '../res/department.requests.php';
	app.loading();
	$.ajax({
		type: 'POST',
		url: u,
		data: obj,
		success: function(response){
			app.notloading();
			try{
				var r = JSON.parse(response);
				if(!r.status){app.error.err(r.error,dur); return;}
				func(r);
			}catch(r){
				app.error.err(response, 5000);
				throw new Error(r);
			}
		}
	});
}
function direct_entry(n = false){
	admin_sidebar.selectMenu('direct entry');
	var form = document.getElementById('direct_add_form')
	if(n && form){
		var matric=form.matric.value;var dept=form.department.value;
		var yoe=form.yoe.value;
		ajax_improved({
			mark_up: 'direct_entry',
			matric,
			dept,
			yoe
		},function(response){
			app.error.err(response.markup,30000,'black','SUCCESS');
		});
		return;
	}
	ajax_improved({
		mark_up: 'direct_entry'
	},function(response){
		app.loadActivity('#main_content', response.markup);
	});
}
function previous_records(option){
	if(option == 'start_entry'){
		var form = document.getElementById('record_add_form');
		var yo = form.yoe.value;
		var dep = form.department.value;
		var yr = form.yor.value;
		var sem = form.semester.value;
		ajax_improved({
			mark_up: 'previous_records',
			yoe: yo,
			dept: dep,
			yor: yr,
			semester: sem
		},function(response){});
		return;
	}else if(option == 'save_entry'){
		ajax_improved({
			mark_up: 'previous_records',
			save_entry: 1
		},function(response){
			app.error.err('SUCCESS',1000,'green','CONFIRMATION','green');
			setTimeout(previous_records, 1000);
		});
	}
	admin_sidebar.selectMenu('add previous records');
	ajax_improved({
		mark_up: 'previous_records'
	},function(response){
		app.loadActivity('#main_content', response.markup);
	});
}
function record_course_add(option = false){
	if(option[0] == 'change_state'){
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'course_state_change',
			id: option[1]
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 1000);
		});
		return;
	}else if(option[0] == 'delete'){
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'delete',
			id: option[1]
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 1000);
		});
		return;
	}else if(option == 'continue'){
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'continue_courses'
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 1000);
		});
		return;
	}
	var form = document.getElementById('record_courseadd_form');
		var c = form.code.value;
		var t = form.title.value;
		var u = form.unit.value;
		var s = form.state.value;
		ajax_improved({
			mark_up: 'record_activities',
			code: c,
			title: t,
			unit: u,
			state: s,
			opt: 'course_add'
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 1000);
		});
}
var rcg = {mat:null};
function record_course_reg(option = false){
	if(option[1] == 'add'){
		var mat = option[0];
		var c = option[2];
		var t = option[3];
		var u = option[4];
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'course_reg_add',
			matric: mat,
			code: c,
			title: t,
			unit: u
		},function(response){
			app.back();
			rcg.mat = mat;
			app.loading();
			setTimeout(function(){
				record_course_reg(rcg.mat);
				app.notloading();
			},200);
		});
		return;
	}else if(option[1] == 'delete'){
		var mat = option[0];
		var i = option[2];
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'course_reg_delete',
			matric: mat,
			id: i
		},function(response){
			app.back();
			rcg.mat = mat;
			app.loading();
			setTimeout(function(){
				record_course_reg(rcg.mat);
				app.notloading();
			},200);
		});
		return;
	}else if(option == 'continue'){
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'continue_course_reg'
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 200);
		},60000);
		return;
	}else if(option[0] == 'continue' && option[1]){
		ajax_improved({
			mark_up: 'record_activities',
			continue: true,
			opt: 'continue_course_reg'
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 200);
		},60000);
		return;
	}
	ajax_improved({
		mark_up: 'record_activities',
		opt: 'course_reg_win',
		matric: option
	},function(response){
		app.loadActivity('#main_content', response.markup,'Back');
	});
}
var ngsm = {count:0};
function new_grade_system(option = false){
	if(option[0] == 'select_system'){
		ajax_improved({
		mark_up:'new_grade',
		opt: 'select_grade_system',
		sesh: option[1]
		},function(response){
			app.loadActivity('#new_grade_form #grade_systems', response.markup,false,true);
		});
		return;
	}
	if(option[0] == 'delete_row'){
		$('#grade_systems .row-'+option[1]).remove();
		return;
	}
	if(option == 'add_field'){
		var t = '<div class="form-group col-sm-3 row-'+ngsm.count+'A"> <input\
		type="text" class="login_input grades" required/> </div><div\
		class="form-group col-sm-3 row-'+ngsm.count+'A"> <input type="number"\
		class="login_input minimum_scores" required/> </div><div class="form-group\
		col-sm-3 row-'+ngsm.count+'A"> <input type="number" class="login_input gradepoints"\
		required/> </div><div class="form-group col-sm-3 row-'+ngsm.count+'A"> <a\
		href="javascript:new_grade_system([\'delete_row\',\''+ngsm.count+'A\'])" class="mt-2\
		btn btn-primary"><i class="fa fa-trash"></i></a> </div>';
		$('#grade_systems').append(t);
		ngsm.count++;
		return;
	}
	var grades = $('#new_grade_form #grade_systems .grades');
	var minimum_scores = $('#new_grade_form #grade_systems .minimum_scores');
	var gradepoints = $('#new_grade_form #grade_systems .gradepoints');
	var grade = new Array();
	var min = new Array();
	var gp = new Array();
	for(var i = 0; i<grades.length;i++){
		grade[grade.length] = grades.eq(i).val();
		min[min.length] = minimum_scores.eq(i).val();
		gp[gp.length] = gradepoints.eq(i).val();
	}
	grade = grade.toString();
	min = min.toString();
	gp = gp.toString();
	ajax_improved({
		mark_up: 'new_grade',
		grades: grade,
		minimum_scores: min,
		gradepoints: gp
	},function(response){
		app.back();
	});
}
function record_result(n = false){
	if(n){
		ajax_improved({
			mark_up: 'record_activities',
			opt: 'continue_result',
			continue: true
		},function(){
			app.back();
			app.loading();
			setTimeout(function(){ previous_records(); app.notloading(); }, 200);
		},60000);
		return;
	}
	ajax_improved({
		mark_up: 'record_activities',
		opt: 'continue_result'
	},function(){
		app.back();
		app.loading();
		setTimeout(function(){ previous_records(); app.notloading(); }, 200);
	},60000);
}
function record_update_score(value, matric, id, option,cd,last_born=false){
	ajax_improved({
		mark_up: 'record_activities',
		score: value,
		mat: matric,
		opt: option,
		code: cd
	},function(response){
		$(id+'.total').html(response.total);
		$(id+'.grade').html(response.grade);
		$(id+'.remark').html(response.remark);
	});
	if(last_born){
		setTimeout(previous_records, 1500);
	}
}
function end_session(agree = false){
	if(agree){
		ajax_improved({
			mark_up: 'end_session',
			ignore: agree
		},function(response){
			app.dashboard.init();
		});
	}
	ajax_improved({
		mark_up: 'end_session'
	},function(response){
		if(response.success){

		}else{
			app.loadActivity('#main_content', response.markup,false,true);
		}
	});
}
function bind_form_data(form){
	var tmp = {};
	for(var i=0;i < form.length;i++){
		if(!form[i].value || !form[i].name) continue;
		tmp [form[i].name]=form[i].value;
	}
	return tmp;
}
function account_settings(n = false){
	if(n == 'save_changes'){
		var form = document.getElementById('account_settings_form');
		var data = bind_form_data(form);
		data.password = $('#account_settings_password').val();
		data.mark_up = 'account_settings';
		ajax_improved(data,function(response){
			var form = $('#account_settings_form .form_messages');
			var data = response.message.split(',');
			for(var i = 0; i < data.length; i++){
				var t = $('<span></span>');
				t.addClass('text-center d-block '+data[i].split(';')[0]);
				t.text(data[i].split(';')[1]);
				form.html(t);
			}
		});
		return;
	}
	admin_sidebar.selectMenu('account settings');
	ajax_improved({
		mark_up: 'account_settings'
	},function(response){
		app.loadActivity('#main_content', response.markup,false,true);
	});
}