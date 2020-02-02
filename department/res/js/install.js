var install = {
	star,
	ajax,
	erro: {
		error,
		closeerror,
		e: 0,
		x: 10
	},
	ajaxurl: '../res/essential.php',
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
		mark_up: 'start_install'
	},function(response){
		loadScreen(response);
	});
}
function startinstallation(){
	var form = document.getElementById('install_form');
	install.ajax({
		mark_up: 'start_install',
		departments: form.departments.value,
		levels: form.level.value,
		session: form.session.value,
		semester: form.semester.value
	},function(response){
		try{
			var r = JSON.parse(response);
			if(!r.status){
				install.error = {'text':r.error,'title': 'Confirmation','dur':60000};	
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