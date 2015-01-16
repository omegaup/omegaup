$('#forgot-password-form').submit(function() {
	omegaup.resetCreate($('#email').val());
	return false;
});

$('#reset-password-form').submit(function() {
	omegaup.resetUpdate(
		$('#email').val(),
		$('#reset_token').val(),
		$('#password').val(),
		$('#password_confirmation').val()
	);
	return false;
});
