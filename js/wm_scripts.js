jQuery(document).ready(function($) {

	var hash_check_interval;
	var hash_check_interval_status = false;
	
	var qrSample_all = "li .wm-qrcode-body .wm-qrcode-view .qr-sample";
	
	var qrSample = "li.disconnected.configuring .wm-qrcode-body .wm-qrcode-view .qr-sample";
	
	var modal = $(".wm-qrcode-body .qr-modal");
	
	var qrcode_img = $('.wm-qrcode-body .wm-qrcode-img');
	
	var modal_close = $('.wm-qrcode-body .qr-modal .qr-modal-close');
	
	var interval = null;
	
	var data = {
		'action': 'wm_generate_qrcode'
	};
	
	
	var get_qrcode = function (){	
		$.post(ajaxurl, data, function(response, status) {			
			if(status == 'success'){	
				//qrcode_img.html(response);
				$('.wm-apps-wrapper ul li.configuring .wm-qrcode-view .qr-sample').attr('src', response);
				modal_close.click();
				$(qrSample).removeClass('loading-qr');
			}	
		});
	}
	
	var clear_interval = function (){	
		clearInterval(interval);	
		qrcode_img.html('<span class="qr-loading">Loading....</span>');	
	}
	
	
	
	$('.wm-apps-wrapper .disconnect_app').on('click', function(){
		var data = {
			'action': 'wm_disconnect_app',
			'android_app': $(this).data('slug')
		}
		$.post(ajaxurl, data, function(response, status) {			
			if(status == 'success'){	
				document.location.reload();
			}	
		});
		
	});
	$('.wm-apps-wrapper .configure_app').on('click', function(){
		
		if(hash_check_interval){
			clearInterval(hash_check_interval);
			hash_check_interval_status = false;
		}
		
		var obj_wrapper = $(this).parents().eq(2);
		$('.wm-apps-wrapper ul li.configuring').not(obj_wrapper).removeClass('configuring');
		obj_wrapper.toggleClass('configuring');
		if(!obj_wrapper.hasClass('connected')){
			$(qrSample).click();
			
			if(!hash_check_interval_status){
				hash_check_interval_status = true;
				hash_check_interval = setInterval(function(){
					var app_slug = obj_wrapper.data('slug');
					var app_slug_js = obj_wrapper.data('slug-js');
					var ajax_data = {
						'action': 'wm_app_connect_status',
						'app_slug': app_slug,
						'app_slug_js':app_slug_js,
						'hash_option': wm_ajax_obj.hash_option[app_slug_js],
						
					}
					$.post(ajaxurl, ajax_data, function(response, status) {			
						if(status == 'success'){
							switch(response){
								case "no-change":
									
								break;
								case "refresh-required":
									document.location.reload();
								break;
							}
						}
					});
				}, 5000);
			}
		}
	});
		
	$(qrSample_all).on("click", function(){	
		var obj_wrapper = $(this).parents().eq(3);
		if(!obj_wrapper.hasClass('connected')){
			modal.css("display","block");	
			$(get_qrcode);
			$(this).addClass('loading-qr');
			interval = setInterval(get_qrcode, 1000*60);	
		}
	})
	
	modal.on("click", function(e){
		if(e.target == modal[0]){	
			modal.css("display","none");	
			$(clear_interval);	
		}	
	})
	
	modal_close.on("click", function(){	
		modal.click();	
	});
	
	$(document).keyup(function(e) {
		if (e.keyCode === 27){	
			modal.click();	
			$(clear_interval);	
		}	
	});	
	
	
	
	var wm_force_logout_interval;
	
	
	if(wm_ajax_obj.wm_logged_in=='true' && wm_ajax_obj.wm_force_logout=='false'){
		wm_force_logout_interval = setInterval(function(){
			$.post(wm_ajax_obj.ajaxurl, {action:'wm_force_logout_check'}, function(resp, status){
					
					
					if(resp=='logged-out'){
						//console.log(resp);	
						alert(wm_ajax_obj.force_logout_msg);
						clearInterval(wm_force_logout_interval);
						
						setTimeout(function(){
							document.location.reload();
						}, 1000*60);
					}
					
				});			
		}, 5000);
	}	
});



