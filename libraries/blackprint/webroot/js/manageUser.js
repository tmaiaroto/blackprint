/**
 * Various JavaScript methods for managing user accounts.
 * Includes validation and more.
*/
function bootstrapError(selector) {
	removeBootstrapSuccess(selector);
	$(selector).parent().parent().parent('.control-group').addClass('error');
}
function removeBootstrapError(selector) {
	$(selector).parent().parent().parent('.control-group').removeClass('error');
}
function bootstrapSuccess(selector) {
	removeBootstrapError(selector);
	$(selector).parent().parent().parent('.control-group').addClass('success');
}
function removeBootstrapSuccess(selector) {
	$(selector).parent().parent().parent('.control-group').removeClass('success');
}

function submitCheck() {
	if(($('#UserPassword').val() != $('#UserPasswordConfirm').val())) {
		$('#UserPasswordConfirm').parent().siblings('.help-block').html('<p>Passwords must match.</p>');
		bootstrapError('#UserPasswordConfirm');
		//$('#UserPasswordConfirm').parent().parent().parent('.control-group').addClass('error');
		return false;
	}
	return true;
}

function validEmail(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

$(document).ready(function() {
	// Check all fields to see if there is an error, if so then apply the error class for Twitter Bootstrap.
	$('.control-group').each(function() {
		$(this).find('.error').parent().parent().children('.help-block').hide();
		$(this).find('.error').addClass('help-block');
		$(this).find('.error').parent().parent().parent('.control-group').addClass('error');
		
		$(this).find('.error').siblings('input').focus(function() {
			$(this).parent().parent().parent('.control-group').removeClass('error');
			$(this).siblings('.error').hide();
			$(this).parent().siblings('.help-block').show();
		});
	});
	
	
	// Remove this because it comes back encrypted. It would encrypt the encrypted password.
	$('#UserPassword').val('');

	$('input').blur(function() {
		if($('#UserPassword').val().length < 6 && $('#UserPassword').val().length > 0) {
			$('#UserPassword').parent().siblings('.help-block').html('<p>Password must be at least 6 characters long.</p>');
			bootstrapError('#UserPassword');
		} else if($('#UserPassword').val().length >= 6) {
			bootstrapSuccess('#UserPassword');
			$('#UserPassword').parent().siblings('.help-block').html('<p>Password length is good.</p>');
		} else {
			$('#UserPassword').parent().siblings('.help-block').html('<p>Choose a password at least 6 characters long.</p>');
			removeBootstrapError('#UserPassword');
		}
		
		if($('#UserPasswordConfirm').val().length > 0 && ($('#UserPassword').val() != $('#UserPasswordConfirm').val())) {
			bootstrapError('#UserPasswordConfirm');
			$('#UserPasswordConfirm').parent().siblings('.help-block').html('<p>The passwords do not seem to match.</p>');
		} else if ($('#UserPasswordConfirm').val().length > 0 && ($('#UserPassword').val() == $('#UserPasswordConfirm').val())) {
			bootstrapSuccess('#UserPasswordConfirm');
			$('#UserPasswordConfirm').parent().siblings('.help-block').html('<p>Passwords match.</p>');
		} else {
			removeBootstrapError('#UserPasswordConfirm');
			removeBootstrapSuccess('#UserPasswordConfirm');
			$('#UserPasswordConfirm').parent().siblings('.help-block').html('<p>Just to be sure, type the password again.</p>');
		}
	});

	$('#UserEmail').change(function() {
		if($('#UserEmail').val().length > 4 && validEmail($('#UserEmail').val())) {
			$.get('/plugin/blackprint/users/email_check/' + $('#UserEmail').val(), function(data) {
				if(data == '1') {
					$('#UserEmail').parent().siblings('.help-block').html('<p>Sorry, this e-mail address has already been registered to a user.</p>');
					bootstrapError('#UserEmail');
				} else {
					bootstrapSuccess('#UserEmail');
					$('#UserEmail').parent().siblings('.help-block').html('<p>The e-mail address looks valid and it is not currently in use.</p>');
				}
			});
		} else if($('#UserEmail').val().length == 0) {
			removeBootstrapError('#UserEmail');
			removeBootstrapSuccess('#UserEmail');
			$('#UserEmail').parent().siblings('.help-block').html('<p>Provide an e-mail address for the user to login with.</p>');
		} else {
			bootstrapError('#UserEmail');
			$('#UserEmail').parent().siblings('.help-block').html('<p>That e-mail address does not appear to be valid.</p>');
		}
	});

});

/**
 * Allows users to set their profile picture from an external URL via AJAX.
*/
var setProfilePictureFromUrl = function(url) {
	if(url === undefined || url == '') {
		return;
	}
	$.get('/set-profile-picture-from-url.json?url=' + url, function(data) {
		if(data && data.result !== undefined) {
			$('#profile-picture-preview').attr('src', data.result);
		}
	});

};