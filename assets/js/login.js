"use strict";
jQuery(function ($) {
	$('.loginbtn').on('click', function(event) {
		event.preventDefault();
		/* Act on the event */
		var form = $(this).parents('.farost-login-form');
		var data = form.serialize();
		data += '&action=' + farost_plugin_js_login.action;
		$.ajax({
			url: farost_plugin_js_login.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function (results) {
				if(results.error == 0){
					window.location.href = location.href;
				}else if(results.error == 2){
					var username = $('input[name=username]',form);
					show_error(username,results.fields.username);
					
					var password = $('input[name=password]',form);
					show_error(password,results.fields.password);

					$('.farost-login-result',form).html('');

				}else{
					$('.farost-login-result',form).html(results.message);
					$('.message-error').remove();
				}
			}
		});
	});
	$('.registerbtn').on('click', function(event) {
		event.preventDefault();
		/* Act on the event */
		var form = $(this).parents('.farost-register-form')
		var data = form.serialize();
		data += '&action=' + farost_plugin_js_login.action;
		$.ajax({
			url: farost_plugin_js_login.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function (results) {
				if(results.error == 0){
					window.location.href = location.href;
				}else if(results.error == 2){
					var username = $('input[name=username]',form);
					show_error(username,results.fields.username);
					
					var password = $('input[name=password]',form);
					show_error(password,results.fields.password);

					var email = $('input[name=email]',form);
					show_error(email,results.fields.email);

					$('.farost-register-result',form).html('');

				}else{
					$('.farost-register-result',form).html(results.message);
					$('.message-error').remove();
				}
			}
		});
	});
	function show_error(el,error) {

		if (typeof error != undefined && error) {
			el.addClass('input-error');
			if (el.next('.message-error').length > 0) {
				el.next('.message-error').html(error);
			}else{
				el.after('<p class="message-error">' + error + '<p>');
			}

		}else{
			el.next('.message-error').remove();
		}
		
	}
});