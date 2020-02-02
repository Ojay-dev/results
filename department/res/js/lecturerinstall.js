var app = {
	loader: function(){
		$('.loading').toggleClass('load');
	},
	errorindex: 0,
	errorPosX: 10,
	ajaxurl: '../res/department.requests.php',
	next_count: 0
};
function start(){
	ajax({
		mark_up: 'lecturer_start_install'
	},function(response){
		if(!response.status){ error({text:response.error}); return; }
		loadScreen({view:'#main_container', html: response.markup});
		setTimeout(function(){ next_step(); }, 1000);
	});
}
function loadScreen(value){
	$(value.view).addClass('fade');
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
		if(b>108.3)$('#'+i).remove();
	}
	c.css('bottom', app.errorPosX+'%');
	var ref = app.errorindex;
	setTimeout(function(){
		closeerror(ref);
	}, obj.dur)
	app.errorindex++;
	return ref;
}
function closeerror(n = 'not set'){
	if(n == 'all'){
		for(var i = app.errorindex; i >= 0; i--){
			closeerror(i);
		}
	}else if(n == 'not set'){
		closeerror(app.errorindex-1);
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
function next_step(validated = false){
	var form = document.getElementById('install_form');
	if(app.next_count > $('.unsaved').length){
		endInstallation();
		return;
	}
	if(app.next_count == 0){
		$('.unsaved').addClass('fade');
		$('.unsaved').eq(app.next_count).removeClass('fade');
		$('.unsaved').eq(app.next_count).removeClass('check');
	}
	if(app.next_count > 0){
		if(validated){
			$('.unsaved').eq(app.next_count).removeClass('check');
			$('.options.unsaved').addClass('fade');
			$('.check').removeClass('fade');
			$('.unsaved').eq(app.next_count).removeClass('fade');
			$('.unsaved').eq(app.next_count-1).removeClass('fade');
			$('.unsaved').eq(app.next_count-1).addClass('check');
			$('.unsaved').eq(app.next_count-1).removeClass('center');
			app.next_count++;
		}else{
			register_lecturer(app.next_count-1);
		}
	}else{ 
		app.next_count++;
	}
}
function register_lecturer(n){
	var name = $('.unsaved input').eq(n).attr('name');
	var value = $('.unsaved input').eq(n).val();
	if(name == 'password'){
		value = $('.unsaved input').eq(n).val()+' - '+$('.unsaved input').eq(n+1).val();
	}else if(name == 'confirmpassword'){
		name = $('.unsaved input').eq(n+1).attr('name');
		value = $('.unsaved input').eq(n+1).val();
	}
	name = name.replace(' ', '');
	ajax({
		mark_up: 'register_lecturer',
		data: name,
		value: value
	},function(response){
		if(!response.status){
			error({text:response.error,dur:3000});	
		}else{
			next_step(true);
		}
	});
}
function endInstallation(){
	ajax({
		mark_up: 'end_install'
	},function(response){
		if(!response.status){
			error({text:response.error});	
		}else{
			loadScreen({view:'#main_container',html:response.markup});
		}
	});
}