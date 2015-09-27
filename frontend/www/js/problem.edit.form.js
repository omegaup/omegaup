$(document).ready(function() {
	$('#problem-form').submit(function() {
		$('.has-error').removeClass('has-error');
		var errors = false;

		if ($('#source').val() == '') {
			OmegaUp.ui.error(OmegaUp.T['editFieldRequired']);
			$('#source-group').addClass('has-error');
			errors = true;
		}
		if (window.location.pathname.indexOf('/problem/new') !== 0 &&
				$('#update-message').val() == '') {
			OmegaUp.ui.error(OmegaUp.T['editFieldRequired']);
			$('#update-message-group').addClass('has-error');
			errors = true;
		}

		if (errors) {
			return false;
		}
	});
});

