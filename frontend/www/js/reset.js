$('#forgot-password-form').submit(function() {
	$('#submit').prop('disabled', true);
	omegaup.API.resetCreate($('#email').val(), function() {
		$('#submit').prop('disabled', false);
	});
	return false;
});

$('#reset-password-form').submit(function() {
	$('#submit').prop('disabled', true);
	omegaup.API.resetUpdate(
		$('#email').val(),
		$('#reset_token').val(),
		$('#password').val(),
		$('#password_confirmation').val(),
		function() {
			$('#submit').prop('disabled', false);
		}
	);
	return false;
});
