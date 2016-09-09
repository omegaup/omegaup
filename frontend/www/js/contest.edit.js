$('document').ready(function() {
	if(window.location.hash){
		$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
	}

	$('#sections').on('click', 'a', function (e) {
		e.preventDefault();
		// add this line
		window.location.hash = $(this).attr('href');
		$(this).tab('show');
	});

	var contestAlias = /\/contest\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

	omegaup.API.getContestAdminDetails(contestAlias, function(contest) {
		$('.page-header h1 span').html(omegaup.T['contestEdit'] + ' ' + contest.title);
		$('.page-header h1 small').html('&ndash; <a href="/arena/' + contestAlias + '/">' + omegaup.T['contestDetailsGoToContest'] + '</a>');
		$(".new_contest_form #title").val(contest.title);
		$(".new_contest_form #alias").val(contest.alias);
		$(".new_contest_form #description").val(contest.description);
		$(".new_contest_form #start_time").val(omegaup.UI.formatDateTime(contest.start_time));
		$(".new_contest_form #finish_time").val(omegaup.UI.formatDateTime(contest.finish_time));

		if (contest.window_length === null) {
			// Disable window length
			$('#window_length_enabled').removeAttr('checked');
			$('#window_length').val('');
		} else {
			$('#window_length_enabled').attr('checked', 'checked');
			$('#window_length').removeAttr('disabled');
			$('#window_length').val(contest.window_length);
		}

		$(".new_contest_form #points_decay_factor").val(contest.points_decay_factor);
		$(".new_contest_form #submissions_gap").val(contest.submissions_gap / 60);
		$(".new_contest_form #feedback").val(contest.feedback);
		$(".new_contest_form #penalty").val(contest.penalty);
		$(".new_contest_form #public").val(contest.public);
		$(".new_contest_form #register").val(contest.contestant_must_register);
		$(".new_contest_form #scoreboard").val(contest.scoreboard);
		$(".new_contest_form #penalty_type").val(contest.penalty_type);
		$(".new_contest_form #show_scoreboard_after").val(contest.show_scoreboard_after);

		$(".contest-publish-form #public").val(contest.public);

		if (contest.contestant_must_register == null ||
				contest.contestant_must_register == "0"){
			$("#requests").hide();
		}
	});

	omegaup.API.getProblems(function(problems) {
		// Got the problems, lets populate the dropdown with them
		for (var i = 0; i < problems.results.length; i++) {
			problem = problems.results[i];
			$('select#problems').append($('<option></option>').attr('value', problem.alias).text(problem.title));
		}
	});

	refreshContestProblems();
	refreshContestContestants();
	refreshContestAdmins();
	refreshContestRequests();

	// Edit contest
	$('.new_contest_form').submit(function() {
		return updateContest($(".new_contest_form #public").val());
	});

	// Publish
	$('.contest-publish-form').submit(function() {
		return updateContest($(".contest-publish-form #public").val());
	});

	// Update contest
	function updateContest(public) {
		var window_length_value = $('#window_length_enabled').is(':checked') ?
				$('#window_length').val() :
				'NULL';

		omegaup.API.updateContest(
			contestAlias,
			$(".new_contest_form #title").val(),
			$(".new_contest_form #description").val(),
			(new Date($(".new_contest_form #start_time").val()).getTime()) / 1000,
			(new Date($(".new_contest_form #finish_time").val()).getTime()) / 1000,
			window_length_value,
			$(".new_contest_form #alias").val(),
			$(".new_contest_form #points_decay_factor").val(),
			$(".new_contest_form #submissions_gap").val() * 60,
			$(".new_contest_form #feedback").val(),
			$(".new_contest_form #penalty").val(),
			public,
			$(".new_contest_form #scoreboard").val(),
			$(".new_contest_form #penalty_type").val(),
			$(".new_contest_form #show_scoreboard_after").val(),
			$(".new_contest_form #register").val(),
			function(data) {
				if(data.status == "ok") {
					omegaup.UI.success('Tu concurso ha sido editado! <a href="/arena/'+ $('.new_contest_form #alias').val() + '">' + omegaup.T['contestEditGoToContest'] + '</a>');
					$('div.post.footer').show();
					window.scrollTo(0,0);
				} else {
					omegaup.UI.error(data.error || 'error');
				}
			}
		);
		return false;
	}

	// Edit problems
	function refreshContestProblems() {
		omegaup.API.contestProblems(contestAlias, function(response) {
			var problems = $('#contest-problems');
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
									omegaup.API.removeProblemFromContest(contestAlias, problem, function(response) {
										if (response.status == "ok") {
											omegaup.UI.success("Problem successfully removed!");
											$('div.post.footer').show();
											$(e.target.parentElement.parentElement).remove();
										} else {
											omegaup.UI.error(response.error || 'error');
										}
									});
								};
							})(response.problems[i].alias))
						)
				);
			}
		});
	}

	$('#add-problem-form').submit(function() {
		problemAlias = $('input#problems-dropdown').val();
		points = $('input#points').val();
		order = $('input#order').val();

		omegaup.API.addProblemToContest(contestAlias, order, problemAlias, points, function(response){
			if (response.status == "ok") {
				omegaup.UI.success("Problem successfully added!");
				$('div.post.footer').show();
				refreshContestProblems();
			} else {
				omegaup.UI.error(response.error || 'Error');
			}
		});

		return false; // Prevent page refresh
	});

	// Edit users
	function userTypeahead(elm) {
		elm.typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.UI.typeaheadWrapper(omegaup.API.searchUsers),
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
			source: omegaup.UI.typeaheadWrapper(omegaup.API.searchGroups),
			displayKey: 'label',
		}).on('typeahead:selected', function(item, val, text) {
			elm.val(val.label);
		});
	};

	userTypeahead($('#username-contestant'));
	userTypeahead($('#username-admin'));
	groupTypeahead($('#groupalias-admin'));
	$('#problems-dropdown').typeahead({
		minLength: 3,
		highlight: false,
	}, {
		source: function (query, cb) {
			omegaup.API.searchProblems(query, function (data) {
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

	function refreshContestRequests() {
		$("#user-requests-table").bootstrapTable({
			method:'get',
			url : '/api/contest/requests/contest_alias/' + contestAlias + '/',
			onPostBody: function () {
				$(".close.request-accept").click((function() {
						return function() {
							var username = $(this).val();
							omegaup.API.arbitrateContestUserRequest(contestAlias, username, true /* accepted */, "", function(response) {
									if (response.status == "ok") {
										omegaup.UI.success(omegaup.T['successfulOperation']);
										$('#user-requests-table').bootstrapTable('refresh');
									} else {
										omegaup.UI.error(response.error || 'error');
									}
								});
							};
						})());

				$(".close.request-deny").click((function() {
						return function() {
							var username = $(this).val();
							omegaup.API.arbitrateContestUserRequest(contestAlias, username, false /* rejected */, "", function(response) {
									if (response.status == "ok") {
										omegaup.UI.success(omegaup.T['successfulOperation']);
										$('#user-requests-table').bootstrapTable('refresh');
									} else {
										omegaup.UI.error(response.error || 'error');
									}
								});
							};
						})());
			},
			responseHandler: function (res) {
				return res.users;
			},
			columns : [{
				field : 'username'
			}, {
				field : 'country',
				sortable : true
			}, {
				field : 'request_time'
			}, {
				field : 'accepted',
				sortable : true,
				formatter: function(value) {
					if (value == null) {
						return omegaup.T.wordsDenied;
					}

					if (value == "true" || value == "1") {
						return omegaup.T.wordAccepted;
					}

					return omegaup.T.wordsDenied;
				}
			}, {
				field : 'last_update',
				formatter: function(v,o) {
					return v + " (" + o.admin.username + ")";
				}
			}, {
				field : 'accepted',
				formatter : function(a,b,c) {
					return '<button type="button" class="close request-deny" value="'+b.username+'" style="color:red">&times;</button>'
							 + '<button type="button" class="close request-accept" value="'+b.username+'" style="color:green">&#x2713;</button>';
				}
			}]
		});
	}

	function refreshContestContestants() {
		omegaup.API.getContestUsers(contestAlias, function(users) {
			$('#contest-users').empty();
			// Got the contests, lets populate the dropdown with them
			for (var i = 0; i < users.users.length; i++) {
				user = users.users[i];
				$('#contest-users').append(
					$('<tr></tr>')
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/profile/' + user.username + '/')
								.text(user.username).append(omegaup.UI.getFlag(user['country_id']))
						))
						.append($('<td></td>').text(user.access_time))
						.append($('<td><button type="button" class="close">&times;</button></td>')
							.click((function(username) {
								return function(e) {
									omegaup.API.removeUserFromContest(contestAlias, username, function(response) {
										if (response.status == "ok") {
											omegaup.UI.success("User successfully removed!");
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											omegaup.UI.error(response.error || 'error');
										}
									});
								};
							})(user.username))
						)
				);
			}
		});
	}

	$('#add-contestant-form').submit(function() {
		username = $("#username-contestant").val();
		omegaup.API.addUserToContest(contestAlias, username, function(response) {
			if (response.status == "ok") {
				omegaup.UI.success("User successfully added!");
				$('div.post.footer').show();

				refreshContestContestants();
			} else {
				omegaup.UI.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});

	// Add admin
	function refreshContestAdmins() {
		omegaup.API.getContestAdmins(contestAlias, function(admins) {
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
									omegaup.API.removeAdminFromContest(contestAlias, username, function(response) {
										if (response.status == "ok") {
											omegaup.UI.success(omegaup.T['adminAdded']);
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											omegaup.UI.error(response.error || 'error');
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
									omegaup.API.removeGroupAdminFromContest(contestAlias, alias, function(response) {
										if (response.status == "ok") {
											omegaup.UI.success(omegaup.T['adminAdded']);
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											omegaup.UI.error(response.error || 'error');
										}
									});
								};
							})(group_admin.alias))
						)
				);
			}
		});
	}

	$('#add-admin-form').submit(function() {
		var username = $('#username-admin').val();

		omegaup.API.addAdminToContest(contestAlias, username, function(response) {
			if (response.status == "ok") {
				omegaup.UI.success(omegaup.T['adminAdded']);
				$('div.post.footer').show();

				refreshContestAdmins();
			} else {
				omegaup.UI.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});

	$('#add-group-admin-form').submit(function() {
		var groupalias = $('#groupalias-admin').val();

		omegaup.API.addGroupAdminToContest(contestAlias, groupalias, function(response) {
			if (response.status == "ok") {
				omegaup.UI.success(omegaup.T['adminAdded']);
				$('div.post.footer').show();

				refreshContestAdmins();
			} else {
				omegaup.UI.error(response.error || 'error');
			}
		});

		return false; // Prevent refresh
	});
});
