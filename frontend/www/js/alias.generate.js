function generateAlias(title) {
	// Remove accents
	title = title.latinize();

	// Replace whitespace
	title = title.replace(/\s+/g, '-');

	// Remove invalid characters
	title = title.replace(/[^a-zA-Z0-9_-]/g, '');

	return title;
}

$(document).ready(function() {
	var formData = $('#form-data');
	var formName = formData.attr('data-name');
	var existsFn = null;

	function checkExists(obj) {
		if (obj.status !== 'error') {
			// Problem already exists
			onAliasExists();
		} else {
			onAliasNew();
		}
	}

	function onAliasExists() {
		OmegaUp.ui.error(omegaup.escape($('#alias').val()) + '" ya existe. Elige otro nombre');
		$('#alias').focus();
	}

	function onAliasNew() {
		OmegaUp.ui.dismissNotifications();
	}

	switch (formName) {
		case "problems":
			existsFn= function(alias) {
				omegaup.getProblem(null, alias, checkExists);
			};
			break;

		case "groups":
			existsFn= function(alias) {
				omegaup.getGroup(alias, checkExists);
			};
			break;
	}

	$('#title').blur(function() {
		$('#alias').val(generateAlias($(this).val())).change();
	});

	$('#alias').change(function() {
		existsFn($('#alias').val());
	});
});

