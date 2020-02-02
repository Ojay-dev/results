var install = {
	star,
	ajax,
	next,
	back,
	next_count: 0,
	erro: {
		error,
		closeerror,
		e: 0,
		x: 10
	},
	ajaxurl: '../res/department.requests.php',
	loadin: function(){
		$('.loader_main .loading').toggleClass('load');
	}
};
Object.defineProperties(install,{
	'start':{
		get: function(){
			install.star();
		}
	},
	'load':{
		get: function(){ install.loadin(); }
	},
	'error': {
		set: function(value){ install.erro.error(value); }	
	},
	'next_step':{
		get: function(){ install.next(); }
	},
	'previous_step':{
		get: function(){ install.back(); }
	}
});
function loadScreen(obj){
	var html;
	try{
		var r = JSON.parse(obj);
		if(!r.status){
			install.error = {'text':r.error};
		}else{
			html = r.markup;
		}
	}catch(e){
		throw new Error(obj);
		return;
	}
	$('#main_container').addClass('fade');
	setTimeout(function(){
		$('#main_container').html(html);
		$('#main_container').removeClass('fade');
	},200);
}
function star(){
	this.ajax({
		mark_up: 'student_start_install'
	},function(response){
		loadScreen(response);
		setTimeout(function(){ install.next_step; }, 1000);
	});
}
function endInstallation(){
	install.ajax({
		mark_up: 'end_install'
	},function(response){
		try{
			var r = JSON.parse(response);
			if(!r.status){
				install.error = {'text':r.error};	
			}else{
				loadScreen(response);
			}
		}catch(e){
			install.error = {'text': response};
		}
	});
}
function ajax(obj, o){
	install.load;
	$.ajax({
		type: 'POST',
		url: this.ajaxurl,
		data: obj,
		success: function(response){
			install.load;
			o(response);
		}
	});
}
function error(obj = {}){
	obj.color = (obj.color)? 'style="background:'+obj.color+';"':'';
	obj.text = (obj.text)? obj.text:'';
	obj.dur = (obj.dur)? obj.dur:5000;
	obj.title = (obj.title)? obj.title:'ERROR';
	obj.closeBtnColor = (obj.closeBtnColor)? 'style="color:'+obj.closeBtnColor+'"':'';
	var c = $('<div></div>').addClass('app_error');
	c.addClass('install_error');
	c.attr('id', this.e);
	c.append(`<div class="content">
		<div class="header" `+obj.color+`>
		<div class="container"><h5>`+obj.title+`<a class="close-error" `+obj.closeBtnColor+` href="javascript:install.erro.closeerror(`+this.e+`)"><i class="fa fa-times"></i></a></h5></div>
		</div>
		<div class="body"><div class="container">`+obj.text+`</div></div>
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
		if(b>108.3)$('#'+i).remove();
	}
	c.css('bottom', this.x+'%');
	var ref = this.e;
	setTimeout(function(){
		this.closeerror(ref);
	}, obj.dur)
	this.e++;
	return ref;
}
function closeerror(n = 'not set'){
	if(n == 'all'){
		for(var i = this.e; i >= 0; i--){
			install.erro.closeerror(i);
		}
	}else if(n == 'not set'){
		install.erro.closeerror(this.e-1);
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
}
function next(validated = false){
	var form = document.getElementById('install_form');
	if(this.next_count > $('.unsaved').length){
		endInstallation();
		return;
	}
	if(this.next_count == 0){
		$('.unsaved').addClass('fade');
		$('.unsaved').eq(this.next_count).removeClass('fade');
		$('.unsaved').eq(this.next_count).removeClass('check');
	}
	if(this.next_count > 0){
		if(validated){
			$('.unsaved').eq(this.next_count).removeClass('check');
			$('.options.unsaved').addClass('fade');
			$('.check').removeClass('fade');
			$('.unsaved').eq(this.next_count).removeClass('fade');
			$('.unsaved').eq(this.next_count-1).removeClass('fade');
			$('.unsaved').eq(this.next_count-1).addClass('check');
			$('.unsaved').eq(this.next_count-1).removeClass('center');
			this.next_count++;
		}else{
			register_student(this.next_count-1);
		}
	}else{ 
		this.next_count++;
	}
}
function back(){
	if(this.next_count <= -1){
		return;
	}
	this.next_count-=2;
	install.next_step;
}
function register_student(n){
	var name = $('.unsaved input').eq(n).attr('name');
	var value = $('.unsaved input').eq(n).val();
	if(name == 'password'){
		value = $('.unsaved input').eq(n).val()+' - '+$('.unsaved input').eq(n+1).val();
	}else if(name == 'confirmpassword'){
		name = $('.unsaved input').eq(n+1).attr('name');
		value = $('.unsaved input').eq(n+1).val();
	}
	name = name.replace(' ', '');
	install.ajax({
		mark_up: 'register_student',
		data: name,
		value: value
	},function(response){
		try{
			var r = JSON.parse(response);
			if(!r.status){
				install.error = {'text':r.error,'dur':3000};	
			}else{
				install.next(true);
			}
		}catch(e){
			install.error = {'text': response};
		}
	});
}
$(document).ready(function(){

});