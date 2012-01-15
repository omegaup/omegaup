$(document).ready(function() {
	var omegaup = new OmegaUp();
	var problems = {};
	var activeTab = 'summary';
	var currentProblem = null;

	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	omegaup.getContest(contestAlias, function(contest) {
		$('#title .contest-title').html(contest.title);
		for (var idx in contest.problems) {
			var problem = contest.problems[idx];

			problems[problem.alias] = problem;

			var prob = $('#problem-list .template').clone().removeClass('template');
			$('.name', prob).attr('href', '#problems/' + problem.alias).html(problem.title);
			$('#problem-list').append(prob);
		}

		// Trigger the event (useful on page load).
		$(window).hashchange();

        	$('#loading').fadeOut('slow');
	        $('#root').fadeIn('slow');
	});

	$('#overlay, #close').click(function(e) {
		if (e.target.id === 'overlay' || e.target.id === 'close') {
			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		}
	});

	$('#submit').submit(function(e) {
		omegaup.submit(contestAlias, currentProblem.alias, $('#submit select[name="language"]').val(), $('#submit textarea[name="code"]').val(), function (run) {
			var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
			$('.status', r).html('new');
			$('#problem .runs > tbody:last').after(r);
			currentProblem.runs.push(run);

			function updateRun(guid, orig_run) {
				setTimeout(function() {
					omegaup.runStatus(guid, function(run) {
						var r = $('#run_' + run.guid);

						orig_run.contest_score = run.contest_score;
						orig_run.status = run.status;
						orig_run.veredict = run.veredict;
						orig_run.submit_delay = run.submit_delay;
						orig_run.time = run.time;
						orig_run.language = run.language;

						$('.points', r).html(run.contest_score.toFixed(2));
						$('.status', r).html(run.status == 'ready' ? run.veredict : run.status);
						$('.penalty', r).html(run.submit_delay);
						$('.time', r).html(run.time);
						$('.language', r).html(run.language);

						if (run.status != 'ready') {
							updateRun(guid, orig_run);
						}
					});
				}, 5000);
			}

			updateRun(run.guid, run);

			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});

		return false;
	});

	$(window).hashchange(function(e) {
		var tabChanged = false;
		var tabs = ['summary', 'problems', 'ranking', 'clarifications'];

		for (var i = 0; i < 4; i++) {
			if (window.location.hash == '#' + tabs[i]) {
				tabChanged = activeTab != tabs[i];
				activeTab = tabs[i];

				break;
			}
		}

		var problem = /#problems\/([^\/]+)(\/new-run)?/.exec(window.location.hash);

		if (problem && problems[problem[1]]) {
			tabChanged = activeTab != 'problems';
			activeTab = 'problems';

			var newRun = problem[2];
			currentProblem = problem = problems[problem[1]];

			function update(problem) {
				$('#problem').show();
				$('#problem > .title').html(problem.title);
				$('#problem > .points').html(problem.points);
				$('#problem .validator').html(problem.validator);
				$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
				$('#problem .memory_limit').html(problem.memory_limit / 1024 + "MB");
				$('#problem .statement').html(problem.problem_statement);
				$('#problem .source span').html(problem.source);
				$('#problem .runs tfoot td a').attr('href', '#problems/' + problem.alias + '/new-run');

				$('#problem .run-list .added').remove();

				for (var idx in problem.runs) {
					if (!problem.runs.hasOwnProperty(idx)) continue;
					var run = problem.runs[idx];

					var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
					$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
					$('.status', r).html(run.status == 'ready' ? run.veredict : run.status);
					$('.penalty', r).html(run.submit_delay);
					$('.time', r).html(run.time);
					$('.language', r).html(run.language);
					$('#problem .runs > tbody:last').append(r);
				}

				MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#problem .statement').get(0)]);
			}

			if (problem.problem_statement) {
				update(problem);
			} else {
				omegaup.getProblem(contestAlias, problem.alias, function (problem_ext) {
					problem.source = problem_ext.source;
					problem.problem_statement = problem_ext.problem_statement;
					problem.runs = problem_ext.runs;
					update(problem);
				});
			}

			if (newRun) {
				$('#overlay form').hide();
				$('#submit').show();
				$('#overlay').show();
			}
		}

		if (tabChanged) {
			$('.tabs a.active').removeClass('active');
			$('.tabs a[href="#' + activeTab + '"]').addClass('active');
			$('.tab').hide();
			$('#' + activeTab).show();
		}
	});
});
