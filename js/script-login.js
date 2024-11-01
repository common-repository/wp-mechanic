// JavaScript Document
jQuery(document).ready(function($) {
	
	function wm_adjust_cells_height(){
		setInterval(function(){
			var document_height = $( window ).height();
			var box_height = Math.floor(document_height/3);
			//$('.wm-login-wrapper .wm-login-grid td').css({height:box_height+'px'});
			$('.wm-login-wrapper .wm-login-grid td').animate({'height': box_height+'px'}, 1000, "linear");
			
			if(!$('.wm-login-wrapper').hasClass('numbered')){
				
				var random_number = Math.floor(Math.random()*12);
	
				$.each($('.wm-login-wrapper .wm-login-grid td'), function(){
					random_number++;
					
						
					$(this).html(random_number);
					
				});
				
				$('.wm-login-wrapper').addClass('numbered');
			}
			
		}, 100);
		
		
	}
	
	var wm_grid = '<div class="wm-login-wrapper"><table class="wm-login-grid red" cellpadding="0" cellspacing="0"><tr><td></td><td></td><td></td><td></td></tr></table>'+
		'<table class="wm-login-grid green" cellpadding="0" cellspacing="0"><tr><td></td><td></td><td></td><td></td></tr></table>'+
		'<table class="wm-login-grid blue" cellpadding="0" cellspacing="0"><tr><td></td><td></td><td></td><td></td></tr></table>'+
		'</div>';
	if(wm_ajax_obj.wm_login=='true'){
		wm_grid_control('on');
	}

	
	var wm_login_access_interval;
	
	$('body').on('click', '.wm-login-wrapper td', function(){
		//console.log($(this));
		//console.log($(this).parents().eq(2).attr('class'));
		var color_type = '';
		if($(this).parents().eq(2).hasClass('red')){
			color_type = 'red';
		}
		if($(this).parents().eq(2).hasClass('green')){
			color_type = 'green';
		}
		if($(this).parents().eq(2).hasClass('blue')){
			color_type = 'blue';
		}
		if(color_type){
			$.post(wm_ajax_obj.ajaxurl, {action:'wm_login_actions', ctype:color_type}, function(resp, status){
				wm_login_access_interval = setInterval(function(){
				$.post(wm_ajax_obj.ajaxurl, {action:'wm_login_access_status'}, function(respo, status){
					respo = $.parseJSON(respo);
					if(respo.msg=='allowed'){
						//console.log(resp);
						clearInterval(wm_login_access_interval);
						//document.location.reload();						
						document.location.href = wm_ajax_obj.admin_url;
					}else{
					}
				});
			}, 5000);
			});
		}
	});
	
	var wm_login_grid_interval;
	//$('.login-action-login').on('dblclick', function(){
		
		$.post(wm_ajax_obj.ajaxurl, {action:'wm_login_grid_log'}, function(resp, status){
			wm_login_grid_interval = setInterval(function(){
				$.post(wm_ajax_obj.ajaxurl, {action:'wm_login_grid_status'}, function(respo, status){
					respo = $.parseJSON(respo);
					switch(respo.msg){
						case "allowed":
							wm_grid_control('on');
						break;
						case "not-allowed":
							wm_grid_control('off');
							
							//clearInterval(wm_login_grid_interval);
						break;
						default:
						break;
					}
				});
			}, 5000);
		});
	//});	
	
	function wm_grid_control(type){
		var grid_obj = $('body .wm-login-wrapper');
		if(grid_obj.length==0){
			$('body').append(wm_grid);
			wm_adjust_cells_height();
			grid_obj = $('body .wm-login-wrapper');
		}
		
		switch(type){
			case "on":
				$('.login h1 a').addClass('wm_acrobatics');
				grid_obj.show();
			break;
			case "off":
				$('.login h1 a').removeClass('wm_acrobatics');
				grid_obj.hide();
			break;
		}
		//console.log(type);
		//console.log(grid_obj);
	}
});