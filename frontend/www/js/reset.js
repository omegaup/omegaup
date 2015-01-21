$('#forgot-password-form').submit(function() {
	$('#submit').prop('disabled', true);
	omegaup.resetCreate($('#email').val(), function() {
		$('#submit').prop('disabled', false);
	});
	return false;
});

$('#reset-password-form').submit(function() {
	$('#submit').prop('disabled', true);
	omegaup.resetUpdate(
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
