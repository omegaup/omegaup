$(document).ready(function() {
	$('#title').on('keyup', function() {
		$('#title-group').removeClass('has-error');
	});

	$('#source').on('keyup', function() {
		$('#source-group').removeClass('has-error');
	});

	$('#problem_contents').change(function() {
		$('#problem-contents-group').removeClass('has-error');
	});

	$('#problem-form').submit(function() {
		$('.has-error').removeClass('has-error');
		var errors = false;

		if ($('#source').val() == '') {
			omegaup.UI.error(omegaup.T['editFieldRequired']);
			$('#source-group').addClass('has-error');
			errors = true;
		}

		if ($('#title').val() == '') {
			omegaup.UI.error(omegaup.T['editFieldRequired']);
			$('#title-group').addClass('has-error');
			errors = true;
		}

		if($('#problem_contents').val() == ''){
			omegaup.UI.error(omegaup.T['editFieldRequired']);
			$('#problem-contents-group').addClass('has-error');
			errors = true;
		}

		if (window.location.pathname.indexOf('/problem/new') !== 0 &&
				$('#update-message').val() == '') {
			omegaup.UI.error(omegaup.T['editFieldRequired']);
			$('#update-message-group').addClass('has-error');
			errors = true;
		}

		if (errors) {
			return false;
		}
	});
});

