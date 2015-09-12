$(document).ready(function() {
	var chosenLanguage = null;

	if(window.location.hash) {
		$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
	}

	$('#sections').on('click', 'a', function (e) {
		e.preventDefault();
		// add this line
		window.location.hash = $(this).attr('href');
		$(this).tab('show');
	});

	var problemAlias = $('#problem-alias').val();
	refreshEditForm(problemAlias);

	// Add typeaheads
	refreshProblemAdmins();
	$("#username-admin").typeahead({
		minLength: 2,
		highlight: true,
	}, {
		source: omegaup.searchUsers,
		displayKey: 'label',
	}).on('typeahead:selected', function(item, val, text) {
		$("#username-admin").val(val.label);
	});

	refreshProblemTags();
	$("#tag-name").typeahead({
		minLength: 2,
		highlight: true,
	}, {
		source: omegaup.searchTags,
		displayKey: 'name',
	}).on('typeahead:selected', function(item, val, text) {
		$("#tag-name").val(val.name);
	});

	$('#add-admin-form').submit(function() {
		var username = $('#username-admin').val();

		omegaup.addAdminToProblem(problemAlias, username, function(response) {
			if (response.status === "ok") {
				OmegaUp.ui.success("Admin successfully added!");
				$('div.post.footer').show();

				refreshProblemAdmins();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});

	$('#download form').submit(function() {
		window.location = '/api/problem/download/problem_alias/' + omegaup.escape(problemAlias) + '/';
		return false;
	});

	function refreshProblemAdmins() {
		omegaup.getProblemAdmins(problemAlias, function(admins) {
			$('#problem-admins').empty();
			// Got the contests, lets populate the dropdown with them
			for (var i = 0; i < admins.admins.length; i++) {
				var admin = admins.admins[i];
				$('#problem-admins').append(
					$('<tr></tr>')
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/profile/' + admin.username + '/')
								.text(admin.username)
						))
						.append($('<td></td>').text(admin.role))
						.append((admin.role != "admin") ? $('<td></td>') : $('<td><button type="button" class="close">&times;</button></td>')
							.click((function(username) {
								return function(e) {
									omegaup.removeAdminFromProblem(problemAlias, username, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success("Admin successfully removed!");
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											OmegaUp.ui.error(response.error || 'error');
										}
									});
								};
							})(admin.username))
						)
				);
			}
		});
	}

	$('#add-tag-form').submit(function() {
		var tagname = $('#tag-name').val();
		var public = $('#tag-public').val();

		omegaup.addTagToProblem(problemAlias, tagname, public, function(response) {
			if (response.status === "ok") {
				OmegaUp.ui.success("Tag successfully added!");
				$('div.post.footer').show();

				refreshProblemTags();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});

	function refreshProblemTags() {
		omegaup.getProblemTags(problemAlias, function(result) {
			$('#problem-tags').empty();
			// Got the contests, lets populate the dropdown with them
			for (var i = 0; i < result.tags.length; i++) {
				var tag = result.tags[i];
				$('#problem-tags').append(
					$('<tr></tr>')
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/problem/?tag=' + tag.name)
								.text(tag.name)
						))
						.append($('<td></td>').text(tag.public))
						.append($('<td><button type="button" class="close">&times;</button></td>')
							.click((function(tagname) {
								return function(e) {
									omegaup.removeTagFromProblem(problemAlias, tagname, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success("Tag successfully removed!");
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											OmegaUp.ui.error(response.error || 'error');
										}
									});
								};
							})(tag.name))
						)
				);
			}
		});
	}

	var md_converter = Markdown.getSanitizingConverter();
	md_editor = new Markdown.Editor(md_converter, '-statement');		// Global.
	md_editor.run();

	function refreshEditForm(problemAlias) {
		if (problemAlias === "") {
			$('input[name=title]').val('');
			$('input[name=time_limit]').val('');
			$('input[name=validator_time_limit]').val('');
			$('input[name=overall_wall_time_limit]').val('');
			$('input[name=extra_wall_time]').val('');
			$('input[name=memory_limit]').val('');
			$('input[name=output_limit]').val('');
			$('input[name=source]').val('');
			$('input[name=stack_limit]').val('');
			return;
		}

		omegaup.getProblem(null, problemAlias, problemCallback, "markdown");
	}

	function problemCallback(problem) {
		$('.page-header h1 span').html(OmegaUp.T.problemEditEditProblem + ' ' + problem.title);
		$('.page-header h1 small').html('&ndash; <a href="/arena/problem/' + problemAlias + '/">' + OmegaUp.T.problemEditGoToProblem + '</a>');
		$('input[name=title]').val(problem.title);
		$('#statement-preview .title').html(omegaup.escape(problem.title));
		$('input[name=time_limit]').val(problem.time_limit);
		$('input[name=validator_time_limit]').val(problem.validator_time_limit);
		$('input[name=overall_wall_time_limit]').val(problem.overall_wall_time_limit);
		$('input[name=extra_wall_time]').val(problem.extra_wall_time);
		$('input[name=memory_limit]').val(problem.memory_limit);
		$('input[name=output_limit]').val(problem.output_limit);
		$('input[name=stack_limit]').val(problem.stack_limit);
		$('input[name=source]').val(problem.source);
		$('#statement-preview .source').html(omegaup.escape(problem.source));
		$('select[name=email_clarifications]').val(problem.email_clarifications);
		$('select[name=validator]').val(problem.validator);
		$('select[name=public]').val(problem.public);
		$('#languages').val(problem.languages);
		$('input[name=alias]').val(problemAlias);
		if (chosenLanguage == null || chosenLanguage == problem.problem_statement_language) {
			chosenLanguage = problem.problem_statement_language;
			$('#wmd-input-statement').val(problem.problem_statement);
			$('#statement-language').val(problem.problem_statement_language);
		} else {
			$('#wmd-input-statement').val('');
		}
		md_editor.refreshPreview();
		if (problem.slow == 1) {
			$('.slow-warning').show();
		}
	}

	$('#statement-preview-link').on('show.bs.tab', function(e) {
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#wmd-preview').get(0)]);
	});

	$('#statement-language').on('change', function(e) {
		chosenLanguage = $('#statement-language').val();
		omegaup.getProblem(null, problemAlias, problemCallback, "markdown",
			false, chosenLanguage);
	});
});
