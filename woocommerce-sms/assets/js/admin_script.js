(function($) {
	// Gateway select change event
	$('.hide_class').hide();
	$('#sms_main_settings\\[sms_gateway\\]').on( 'change', function() {
		var self = $(this),
			value = self.val();
		$('.hide_class').hide();
		$('.'+value+'_wrapper').fadeIn();
	});
	// Trigger when a change occurs in gateway select box 
	$('#sms_main_settings\\[sms_gateway\\]').trigger('change');
	// handle send sms from post page in admin panale
	var w = $('.persianwoosms_send_sms').width(),
		h = $('.persianwoosms_send_sms').height(),
		block = $('#persianwoosms_send_sms_overlay_block').css({
					'width' : w+'px',
					'height' : h+'px',
				});
	$( 'input#persianwoosms_send_sms_button' ).on( 'click', function(e) {
		e.preventDefault();
		var self = $(this),
			textareaValue = $('#persianwoosms_sms_to_buyer').val(),
			smsNonce = $('#persianwoosms_send_sms_nonce').val(),
			postId = $('input[name=post_id][type=hidden]').val(),
			postType = $('input[name=post_type][type=hidden]').val(),
			sms = $('#persianwoosms_pm_type_sms:checked').val(),
			tg = $('#persianwoosms_pm_type_tg:checked').val(),
			group = $('#select_group').val(),
			data = {
				action : 'persianwoosms_send_sms_metabox',
				textareavalue: textareaValue,
				sms_nonce: smsNonce,
				post_id: postId,
				post_type: postType,
				sms: sms,
				tg: tg,
				group : group
			};
		self.attr( 'disabled', true );
		block.show();
		$.post( persianwoosms.ajaxurl, data , function( res ) {
			if ( res.success ) {
				$('div.persianwoosms_send_sms_result').html( res.data.message ).show();
				$('#persianwoosms_sms_to_buyer').val('');
				block.hide();
				self.attr( 'disabled', false );
			} else {
				$('div.persianwoosms_send_sms_result').html( res.data.message ).show();	
				block.hide();
				self.attr( 'disabled', false );
			}
		});
	});
})(jQuery);