$(document).ready(function() {
	var omegaup = new OmegaUp();
	var problems = {};
	var activeTab = 'summary';

	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	omegaup.getContest(contestAlias, function(contest) {
		$('#title .contest-title').html(contest.title);
		console.log(contest);
		for (var idx in contest.problems) {
			var problem = contest.problems[idx];

			console.log(problem);
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

		var problem = /#problems\/(.+)/.exec(window.location.hash);

		if (problem && problems[problem[1]]) {
			tabChanged = activeTab != 'problems';
			activeTab = 'problems';

			problem = problems[problem[1]];

			function update(problem) {
				$('#problem').show();
				$('#problem .title').html(problem.title);
				$('#problem .points').html(problem.points);
				$('#problem .validator').html(problem.validator);
				$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
				$('#problem .memory_limit').html(problem.points + "MB");
				$('#problem .statement').html(problem.problem_statement);
				$('#problem .source span').html(problem.source);
				MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#problem .statement').get(0)]);
			}

			if (problem.problem_statement) {
				update(problem);
			} else {
				omegaup.getProblem(contestAlias, problem.alias, function (problem_ext) {
					problem.source = problem_ext.source;
					problem.problem_statement = problem_ext.problem_statement;
					update(problem);
				});
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
