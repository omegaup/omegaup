{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestEdit#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span> <small></small></h1>
</div>

<ul class="nav nav-tabs nav-justified" id="sections">
	<li class="active"><a href="#edit" data-toggle="tab">{#contestEdit#}</a></li>
	<li><a href="#problems" data-toggle="tab">{#wordsAddProblem#}</a></li>
	<li><a href="#contestants" data-toggle="tab">{#contestAdduserAddContestant#}</a></li>
	<li><a href="#admins" data-toggle="tab">{#omegaupTitleContestAddAdmin#}</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane active" id="edit">
		{include file='contest.new.form.tpl'}
	</div>

	<div class="tab-pane" id="problems">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-problem-form">
					<div class="form-group">
						<label for="problems">{#wordsProblems#}</label>
						<select class='form-control' name='problems' id='problems'>
							<option value=""></option>
						</select>
					</div>

					<div class="form-group">
						<label for="points">{#contestAddproblemProblemPoints#}</label>
						<input id='points' name='points' size="3" value="100" class="form-control" />
					</div>

					<div class="form-group">
						<label for="order">{#contestAddproblemContestOrder#}</label>
						<input id='order' name='order' value='1' size="2" class="form-control" />
					</div>

					<div class="form-group">
						<input id='' name='request' value='submit' type='hidden'>
						<button type='submit' class="btn btn-primary">{#wordsAddProblem#}</button>
					</div>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestAddproblemContestOrder#}</th>
					<th>{#contestAddproblemProblemName#}</th>
					<th>{#contestAddproblemProblemPoints#}</th>
					<th>{#contestAddproblemProblemRemove#}</th>
				</thead>
				<tbody id="contest-problems"></tbody>
			</table>
		</div>
	</div>

	<div class="tab-pane" id="contestants">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-contestant-form">
					<div class="form-group">
						<label for="username-contestant">{#wordsUser#}</label>
						<input id="username-contestant" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<input id="user-contestant" name="user" value="" type="hidden">

					<button class="btn btn-primary" type='submit'>Agregar {#wordsUser#}</button>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestAdduserRegisteredUserUsername#}</th>
					<th>{#contestAdduserRegisteredUserTime#}</th>
					<th>{#contestAdduserRegisteredUserDelete#}</th>
				</thead>
				<tbody id="contest-users"></tbody>
			</table>
		</div>
	</div>

	<div class="tab-pane" id="admins">
		<div class="panel panel-primary">
			<div class="panel-body">
				<form class="form" id="add-admin-form">
					<div class="form-group">
						<label for="username-admin">{#wordsAdmin#}</label>
						<input id="username-admin" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
					</div>

					<input id="user-admin" name="user" value="" type="hidden">

					<button class="btn btn-primary" type='submit'>Agregar {#wordsAdmin#}</button>
				</form>
			</div>

			<table class="table table-striped">
				<thead>
					<th>{#contestEditRegisteredAdminUsername#}</th>
					<th>{#contestEditRegisteredAdminRole#}</th>
					<th>{#contestEditRegisteredAdminDelete#}</th>
				</thead>
				<tbody id="contest-admins"></tbody>
			</table>
		</div>
	</div>
</div>

<script>
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

		var contestAlias = '{$smarty.get.contest}';

		omegaup.getContest(contestAlias, function(contest) {
			$('.page-header h1 span').html('{#contestEdit#} ' + contest.title);
			$('.page-header h1 small').html('&ndash; <a href="/arena/' + contestAlias + '/">{#contestDetailsGoToContest#}</a>');
			$(".new_contest_form #title").val(contest.title);
			$(".new_contest_form #alias").val(contest.alias);
			$(".new_contest_form #description").val(contest.description);
			$(".new_contest_form #start_time").val(dateToString(contest.start_time));
			$(".new_contest_form #finish_time").val(dateToString(contest.finish_time));

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
			$(".new_contest_form #scoreboard").val(contest.scoreboard);
			$(".new_contest_form #penalty_time_start").val(contest.penalty_time_start);
			$(".new_contest_form #show_scoreboard_after").val(contest.show_scoreboard_after);
		});

		omegaup.getProblems(function(problems) {
			// Got the problems, lets populate the dropdown with them
			for (var i = 0; i < problems.results.length; i++) {
				problem = problems.results[i];
				$('select#problems').append($('<option></option>').attr('value', problem.alias).text(problem.title));
			}
		});

		refreshContestProblems();
		refreshContestContestants();
		refreshContestAdmins();

		// Edit contest
		$('.new_contest_form').submit(function() {
			var window_length_value = $('#window_length_enabled').is(':checked') ?
					$('#window_length').val() :
					'NULL';

			omegaup.updateContest(
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
				$(".new_contest_form #public").val(),
				$(".new_contest_form #scoreboard").val(),
				$(".new_contest_form #penalty_time_start").val(),
				$(".new_contest_form #show_scoreboard_after").val(),
				function(data) {
					if(data.status == "ok") {
						OmegaUp.ui.success('Tu concurso ha sido editado! <a href="/arena/'+ $('.new_contest_form #alias').val() + '">{#contestEditGoToContest#}</a>');
						$('div.post.footer').show();
						window.scrollTo(0,0);
					} else {
						OmegaUp.ui.error(data.error || 'error');
					}
				}
			);
			return false;
		});

		// Edit problems
		function refreshContestProblems() {
			omegaup.contestProblems(contestAlias, function(response) {
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
										omegaup.removeProblemFromContest(contestAlias, problem, function(response) {
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

		$('#add-problem-form').submit(function() {
			problemAlias = $('select#problems').val();
			points = $('input#points').val();
			order = $('input#order').val();

			omegaup.addProblemToContest(contestAlias, order, problemAlias, points, function(response){
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

		// Edit users
		function typeahead(dest) {
			return {
				ajax: '/api/user/list/',
				display: 'label',
				val: 'label',
				minLength: 2,
				itemSelected: function (item, val, text) {
					$(dest).val(val);
				}
			}
		};

		$('#username-contestant').typeahead(typeahead('#user-contestant'));
		$('#username-admin').typeahead(typeahead('#user-admin'));

		function refreshContestContestants() {
			omegaup.getContestUsers(contestAlias, function(users) {
				$('#contest-users').empty();
				// Got the contests, lets populate the dropdown with them
				for (var i = 0; i < users.users.length; i++) {
					user = users.users[i];
					$('#contest-users').append(
						$('<tr></tr>')
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/profile/' + user.username + '/')
									.text(user.username)
							))
							.append($('<td></td>').text(user.access_time))
							.append($('<td><button type="button" class="close">&times;</button></td>')
								.click((function(username) {
									return function(e) {
										omegaup.removeUserFromContest(contestAlias, username, function(response) {
											if (response.status == "ok") {
												OmegaUp.ui.success("User successfully removed!");
												$('div.post.footer').show();
												var tr = e.target.parentElement.parentElement;
												$(tr).remove();
											} else {
												OmegaUp.ui.error(response.error || 'error');
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
			username = $("#user-contestant").val();
			omegaup.addUserToContest(contestAlias, username, function(response) {
				if (response.status == "ok") {
					OmegaUp.ui.success("User successfully added!");
					$('div.post.footer').show();

					refreshContestContestants();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});
			return false; // Prevent refresh
		});

		// Add admin
		function refreshContestAdmins() {
			omegaup.getContestAdmins(contestAlias, function(admins) {
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
										omegaup.removeAdminFromContest(contestAlias, username, function(response) {
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

		$('#add-admin-form').submit(function() {
			var username = $('#user-admin').val();

			omegaup.addAdminToContest(contestAlias, username, function(response) {
				if (response.status == "ok") {
					OmegaUp.ui.success("Admin successfully added!");
					$('div.post.footer').show();

					refreshContestAdmins();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});

			return false; // Prevent refresh
		});
	});
</script>
{include file='footer.tpl'}
