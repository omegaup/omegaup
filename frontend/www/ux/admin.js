$(document).ready(function() {
	var arena = new Arena();
	var admin = new ArenaAdmin(arena);

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	if (arena.contestAlias === "admin") {
		$('#runs').show();
		admin.refreshRuns();
		setInterval(function() { 
			runsOffset = 0; // Return pagination to start on refresh
			admin.refreshRuns();
		}, 5 * 60 * 1000);		

		// Trigger the event (useful on page load).
		$(window).hashchange();

		$('#loading').fadeOut('slow');
		$('#root').fadeIn('slow');
	} else {
		arena.connectSocket();
		omegaup.getContest(arena.contestAlias, function(contest) {
			if (contest.status == 'error' || !contest.admin) {
				if (!omegaup.loggedIn && omegaup.login_url) {
					window.location = omegaup.login_url + "?redirect=" + escape(window.location);
				} else {
					$('#loading').html('404');
				}
				return;
			} else if (arena.practice && contest.finish_time && new Date().getTime() < contest.finish_time.getTime()) {
				window.location = window.location.pathname.replace(/\/practice\/.*/, '/');
				return;
			}
			$('#title .contest-title').html(omegaup.escape(contest.title));

			$('#summary .title').html(omegaup.escape(contest.title));
			$('#summary .description').html(omegaup.escape(contest.description));
						
			$('#summary .start_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.start_time.getTime()));
			$('#summary .finish_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.finish_time.getTime()));
			$('#summary .window_length').html(contest.window_length);

			arena.submissionGap = parseInt(contest.submission_gap);
			if (!(arena.submissionGap > 0)) arena.submissionGap = 0;

			arena.initClock(contest.start_time, contest.finish_time);
			arena.initProblems(contest);

			for (var idx in contest.problems) {
				var problem = contest.problems[idx];
				var problemName = problem.letter + '. ' + omegaup.escape(problem.title);

				arena.problems[problem.alias] = problem;

				var prob = $('#problem-list .template').clone().removeClass('template').addClass('problem_' + problem.alias);
				$('.name', prob).attr('href', '#problems/' + problem.alias).html(problemName);
				$('#problem-list').append(prob);

				$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');
			}

			arena.setupPolls();
			admin.refreshRuns();
			if (!arena.socket) {
				setInterval(function() {
					runsOffset = 0; // Return pagination to start on refresh
					admin.refreshRuns();
				}, 5 * 60 * 1000);
			}

			// Trigger the event (useful on page load).
			$(window).hashchange();

			$('#loading').fadeOut('slow');
			$('#root').fadeIn('slow');
		});
	}
	
	$('#overlay, .close').click(function(e) {
		if (e.target.id === 'overlay' || e.target.className === 'close') {
			$('#overlay, #submit #clarification').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			var code_file = $('#code_file');
			code_file.replaceWith(code_file = code_file.clone(true));
			return false;
		}
	});
	
	$('#submit').submit(function(e) {
		if (!$('#submit textarea[name="code"]').val()) return false;

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(
			arena.contestAlias,
			arena.currentProblem.alias,
			$('#submit select[name="language"]').val(),
			$('#submit textarea[name="code"]').val(),
			'textarea',
			function (run) {
				if (run.status != 'ok') {
					alert(run.error);
					$('#submit input').removeAttr('disabled');
					return;
				}
				run.status = 'new';
				run.alias = arena.currentProblem.alias;
				run.contest_score = null;
				run.time = new Date;
				run.penalty = 0;
				run.runtime = 0;
				run.memory = 0;
				run.language = $('#submit select[name="language"]').val();
				var r = $('#problem .run-list .template')
					.clone()
					.removeClass('template')
					.addClass('added')
					.addClass('run_' + run.guid);
				arena.displayRun(run, r);
				$('#problem .runs > tbody:last').append(r);
				if (!arena.currentProblem.runs) {
					arena.currentProblem.runs = [];
				}
				arena.currentProblem.runs.push(run);
				arena.updateRunFallback(run.guid, run);

				$('#overlay').hide();
				$('#submit input').removeAttr('disabled');
				window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			}
		);

		return false;
	});

	$('#rejudge-problem').click(function() {
		if (confirm('Deseas rejuecear el problema ' + arena.currentProblem.alias + '?')) {
			omegaup.rejudgeProblem(arena.currentProblem.alias, function (x) {
				admin.refreshRuns();
			});
		}
		return false;
	});

	$('#update-problem').submit(function() {
		$('#update-problem input[name="problem_alias"]').val(arena.currentProblem.alias);
		return confirm('Deseas actualizar el problema ' + arena.currentProblem.alias + '?');
	});

	$(window).hashchange(arena.onHashChanged.bind(arena));
});
