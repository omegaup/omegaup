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

	$('#verify-user-form').submit(function() {
		username = $("#username").val();

		omegaup.forceVerifyEmail(username, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("User successfully verified!");
				$('div.post.footer').show();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});
});
