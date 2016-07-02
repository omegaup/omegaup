
$(document).ready(function() {
	var interviewAlias = /\/interview\/([^\/]+)\/?.*/.exec(window.location.pathname)[1];

	if(window.location.hash){
		$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
	}

	$('#sections').on('click', 'a', function (e) {
		e.preventDefault();
		// add this line
		window.location.hash = $(this).attr('href');
		$(this).tab('show');
	});

	$('form#add_user_to_interview').submit(function() {
		var userOrEmail = $("#usernameOrEmail").val();
		var html = "<tr>"
			+ "<td>" + omegaup.escape(userOrEmail) + "</td>"
			+ "</tr>";

		InvitedUsers.push(userOrEmail);
		$("#invitepeople > table > tbody").append(html);
		$("#send_invites").show();
		$("#usernameOrEmail").val("");

		return false; // Prevent page refresh on submit
	});

	var InvitedUsers = Array();

	$('form#send_invites').submit(function() {
		omegaup.addUsersToInterview(
			interviewAlias,
			InvitedUsers.join(),
			function(response) {
				if (response.status == "ok") {
					OmegaUp.ui.success(OmegaUp.T['userEditSuccess']);
					InvitedUsers = Array();
					fillCandidatesTable();
				} else {
					OmegaUp.ui.error(response.error);
					fillCandidatesTable();
				}
			}
		);
		return false; // Prevent page refresh on submit
	});

	omegaup.getContestAdminDetails(interviewAlias, function(contest) {
		$('.page-header h1 span').html(OmegaUp.T['interviewEdit'] + ' ' + contest.title);
		$('.page-header h1 small').html('&ndash; <a href="/interview/' + interviewAlias + '/arena">' + OmegaUp.T['interviewGoToInterview'] + '</a>');
		$(".new_interview_form #title").val(contest.title);
		$(".new_interview_form #description").val(contest.description);
		$('#window_length').val(contest.window_length);
	});

	function fillCandidatesTable() {
		omegaup.getInterview(interviewAlias, function(interview) {
			var html = "";
			for (var i = 0; i < interview.users.length; i++) {
				html += "<tr>"
					+ "<td>" + omegaup.escape(interview.users[i].username) + "</td>"
					+ "<td>" + interview.users[i].email + "</td>"
					+ "<td>" + (interview.users[i].opened_interview ? interview.users[i].access_time : OmegaUp.T['interviewNotStarted'] ) + "</td>"
					+ "<td>" + "</td>"
					+ "</tr>";
			}

			$("#candidate_list > table > tbody").empty().html(html);
		});
	}

	$('#add-problem-form').submit(function() {
		problemAlias = $('input#problems-dropdown').val();
		points = $('input#points').val();
		order = $('input#order').val();

		omegaup.addProblemToContest(interviewAlias, order, problemAlias, points, function(response){
			if (response.status == "ok") {
				OmegaUp.ui.success("Problem successfully added!");
				$('div.post.footer').show();
				refreshContestProblems();
			} else {
				OmegaUp.ui.error(response.error || 'Error');
			}
		});

		return false; // Prevent page refresh
	});

	function refreshContestProblems() {
		omegaup.contestProblems(interviewAlias, function(response) {
			var problems = $('#contest-problems-table');
			problems.empty();

			for (var i = 0; i < response.problems.length; i++) {
				problems.append(
					$('<tr></tr>')
						.append($('<td></td>').text(response.problems[i].order))
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/arena/problem/' + response.problems[i].alias + '/')
								.text(response.problems[i].alias))
						)
						.append($('<td></td>').text(response.problems[i].points))
						.append($('<td><button type="button" class="close">&times;</button></td>')
							.click((function(problem) {
								return function(e) {
									omegaup.removeProblemFromContest(interviewAlias, problem, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success("Problem successfully removed!");
											$('div.post.footer').show();
											$(e.target.parentElement.parentElement).remove();
										} else {
											OmegaUp.ui.error(response.error || 'error');
										}
									});
								};
							})(response.problems[i].alias))
						)
				);
			}
		});
	}

	omegaup.getProblems(function(problems) {
		// Got the problems, lets populate the dropdown with them
		for (var i = 0; i < problems.results.length; i++) {
			problem = problems.results[i];
			$('select#problems').append($('<option></option>').attr('value', problem.alias).text(problem.title));
		}
	});

	$('#problems-dropdown').typeahead({
		minLength: 3,
		highlight: false,
	}, {
		source: function (query, cb) {
			omegaup.searchProblems(query, function (data) {
				cb(data.results);
			});
		},
		displayKey: 'alias',
		templates: {
			suggestion: function (elm) {
				return "<strong>" + elm.title + "</strong> (" + elm.alias + ")";
			}
		}
	}).on('typeahead:selected', function(item, val, text) {
		$('#problems-dropdown').val(val.alias);
	});

	// Edit users
	function userTypeahead(elm) {
		elm.typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.typeaheadWrapper(omegaup.searchUsers.bind(omegaup)),
			displayKey: 'label',
		}).on('typeahead:selected', function(item, val, text) {
			elm.val(val.label);
		});
	};

	function groupTypeahead(elm) {
		elm.typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.typeaheadWrapper(omegaup.searchGroups.bind(omegaup)),
			displayKey: 'label',
		}).on('typeahead:selected', function(item, val, text) {
			elm.val(val.label);
		});
	};

	userTypeahead($('#username-admin'));
	groupTypeahead($('#groupalias-admin'));

	$('#add-admin-form').submit(function() {
		var username = $('#username-admin').val();

		omegaup.addAdminToContest(interviewAlias, username, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success(OmegaUp.T['adminAdded']);
				$('div.post.footer').show();

				refreshContestAdmins();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});

	// Add admin
	function refreshContestAdmins() {
		omegaup.getContestAdmins(interviewAlias, function(admins) {
			$('#contest-admins').empty();
			// Got the contests, lets populate the dropdown with them
			for (var i = 0; i < admins.admins.length; i++) {
				var admin = admins.admins[i];
				$('#contest-admins').append(
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
									omegaup.removeAdminFromContest(interviewAlias, username, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success(OmegaUp.T['adminAdded']);
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
			$('#contest-group-admins').empty();
			for (var i = 0; i < admins.group_admins.length; i++) {
				var group_admin = admins.group_admins[i];
				$('#contest-group-admins').append(
					$('<tr></tr>')
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/group/' + group_admin.alias + '/edit/')
								.text(group_admin.name)
						))
						.append($('<td></td>').text(group_admin.role))
						.append((group_admin.role != "admin") ? $('<td></td>') : $('<td><button type="button" class="close">&times;</button></td>')
							.click((function(alias) {
								return function(e) {
									omegaup.removeGroupAdminFromContest(interviewAlias, alias, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success(OmegaUp.T['adminAdded']);
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											OmegaUp.ui.error(response.error || 'error');
										}
									});
								};
							})(group_admin.alias))
						)
				);
			}
		});
	}
	$('#add-group-admin-form').submit(function() {
		var groupalias = $('#groupalias-admin').val();

		omegaup.addGroupAdminToContest(interviewAlias, groupalias, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success(OmegaUp.T['adminAdded']);
				$('div.post.footer').show();

				refreshContestAdmins();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});

	refreshContestProblems();
	refreshContestAdmins();
	fillCandidatesTable();
});

