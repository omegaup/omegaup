$('document').ready(function() {
	$("#username").typeahead({
		minLength: 2,
		highlight: true,
	}, {
		source: omegaup.searchUsers,
		displayKey: 'label',
	}).on('typeahead:selected', function(item, val, text) {
		$("#username").val(val.label);
	});

	$('#change-password-form').submit(function() {
		password = $('#password').val();
		username = $("#user").val();

		omegaup.forceChangePassword(username, password, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("Password successfully changed!");
				$('div.post.footer').show();

			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});
});
