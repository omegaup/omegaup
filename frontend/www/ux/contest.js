$(document).ready(function() {
	var omegaup = new OmegaUp();
	var problems = {};
	var activeTab = 'problems';
	var currentProblem = null;
	var currentRanking = {};
	var startTime = null;
	var finishTime = null;

	var contestAlias = 'prueba'; // /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	omegaup.getContest(contestAlias, function(contest) {
		$('#title .contest-title').html(contest.title);
		$('#summary .title').html(contest.title);
		$('#summary .description').html(contest.description);

		startTime = contest.start_time;
		finishTime = contest.finish_time;

		var letter = 65;

		for (var idx in contest.problems) {
			var problem = contest.problems[idx];
			var problemName = String.fromCharCode(letter) + '. ' + problem.title;

			problems[problem.alias] = problem;

			problem.letter = String.fromCharCode(letter);

			var prob = $('#problem-list .template').clone().removeClass('template').addClass('problem_' + problem.alias);
			$('.name', prob).attr('href', '#problems/' + problem.alias).html(problemName);
			$('#problem-list').append(prob);

			$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');

			$('<th colspan="2"><a href="#problems/' + problem.alias + '" title="' + problem.alias + '">' + String.fromCharCode(letter++) + '</a></th>').insertBefore('#ranking thead th.total');
			$('<td class="prob_' + problem.alias + '_points"></td>').insertBefore('#ranking tbody .template td.points');
			$('<td class="prob_' + problem.alias + '_penalty"></td>').insertBefore('#ranking tbody .template td.points');
		}

		omegaup.getRanking(contestAlias, rankingChange);
		setInterval(function() { omegaup.getRanking(contestAlias, rankingChange); }, 5 * 60 * 1000);

		omegaup.getRankingEvents(contestAlias, rankingEvents);
		setInterval(function() { omegaup.getRankingEvents(contestAlias, rankingEvents); }, 5 * 60 * 1000);

		omegaup.getClarifications(contestAlias, clarificationsChange);
		setInterval(function() { omegaup.getClarifications(contestAlias, clarificationsChange); }, 5 * 60 * 1000);

		updateClock();
		setInterval(updateClock, 1000);

		// Trigger the event (useful on page load).
		$(window).hashchange();

        	$('#loading').fadeOut('slow');
	        $('#root').fadeIn('slow');
	});

	$('#overlay, .close').click(function(e) {
		if (e.target.id === 'overlay' || e.target.className === 'close') {
			$('#overlay, #submit #clarification').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			return false;
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

						if (run.status == 'ready') {
							omegaup.getRanking(contestAlias, rankingChange);
							omegaup.getRankingEvents(contestAlias, rankingEvents);
						} else {
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

	$('#clarification').submit(function (e) {
		omegaup.newClarification(contestAlias, $('#clarification select[name="problem"]').val(), $('#clarification textarea[name="message"]').val(), function (run) {
			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			omegaup.getClarifications(contestAlias, clarificationsChange);
		});

		return false;
	});

	$(window).hashchange(function(e) {
		var tabChanged = false;
		var tabs = ['summary', 'problems', 'ranking', 'clarifications'];

		for (var i = 0; i < 4; i++) {
			if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
				tabChanged = activeTab != tabs[i];
				activeTab = tabs[i];

				break;
			}
		}

		var problem = /#problems\/([^\/]+)(\/new-run)?/.exec(window.location.hash);

		if (problem && problems[problem[1]]) {
			var newRun = problem[2];
			currentProblem = problem = problems[problem[1]];

			$('#problem-list .active').removeClass('active');
			$('#problem-list .problem_' + problem.alias).addClass('active');

			function update(problem) {
				$('#summary').hide();
				$('#problem').show();
				$('#problem > .title').html(problem.letter + '. ' + problem.title);
				$('#problem .data .points').html(problem.points);
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
		} else if (activeTab == 'problems') {
			$('#problem').hide();
			$('#summary').show();
			$('#problem-list .active').removeClass('active');
			$('#problem-list .summary').addClass('active');
		} else if (activeTab == 'clarifications') {
			if (window.location.hash == '#clarifications/new') {
				$('#overlay form').hide();
				$('#overlay, #clarification').show();
			}
		}

		if (tabChanged) {
			$('.tabs a.active').removeClass('active');
			$('.tabs a[href="#' + activeTab + '"]').addClass('active');
			$('.tab').hide();
			$('#' + activeTab).show();
		}
	});

	function rankingEvents(data) {
	}

	function rankingChange(data) {
		$('#mini-ranking tbody tr.inserted').remove();

		drawChart();

		var ranking = data.ranking;
		var newRanking = {};

		for (var i = 0; i < ranking.length; i++) {
			var rank = ranking[i];
			newRanking[rank.name] = i;

			if (currentRanking[rank.name] === undefined) {
				currentRanking[rank.name] = $('#ranking tbody tr.inserted').length;
				$('#ranking tbody').append(
					$('#ranking tbody tr.template').clone().removeClass('template').addClass('inserted').addClass('rank-new')
				);
			}

			var r = $('#ranking tbody tr.inserted')[currentRanking[rank.name]];
			$('.position', r).html(i+1);
			$('.user', r).html(rank.name);

			for (var alias in rank.problems) {
				if (!rank.problems.hasOwnProperty(alias)) continue;
				
				$('.prob_' + alias + '_points', r).html(rank.problems[alias].points);
				$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty);

				if (rank.username == omegaup.username) {
					$('#problems .problem_' + alias + ' .solved').html("(" + rank.problems[alias].points + " / " + problems[alias].points + ")");
				}
			}

			if (parseInt($('.points', r)) < parseInt(rank.total.points)) {
				r.addClass('rank-up');
			}

			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);

			if (i < 10) {
				r = $('#mini-ranking tbody tr.template').clone().removeClass('template').addClass('inserted');

				$('.position', r).html(i+1);
				$('.user', r).html(rank.name);
				$('.points', r).html(rank.total.points);
				$('.penalty', r).html(rank.total.penalty);

				$('#mini-ranking tbody').append(r);
			}
		}

		currentRanking = newRanking;
	}
	
	function updateClock() {
		var date = new Date().getTime();
		var clock = "";

		if (date < startTime.getTime()) {
				clock = "-" + formatDelta(startTime.getTime() - (date + omegaup.deltaTime));
		} else if (date > finishTime.getTime()) {
				clock = "00:00:00";
		} else {
				clock = formatDelta(finishTime.getTime() - (date + omegaup.deltaTime));
		}

		$('#title .clock').html(clock);
	}

	function formatDelta(delta) {
		var days = Math.floor(delta / (24 * 60 * 60 * 1000));
		delta -= days * (24 * 60 * 60 * 1000);
		var hours = Math.floor(delta / (60 * 60 * 1000));
		delta -= hours * (60 * 60 * 1000);
		var minutes = Math.floor(delta / (60 * 1000));
		delta -= minutes * (60 * 1000);
		var seconds = Math.floor(delta / 1000);

		var clock = "";

		if (days > 0) {
			clock += days + ":";
		}
		if (hours < 10) clock += "0";
		clock += hours + ":";
		if (minutes < 10) clock += "0";
		clock += minutes + ":";
		if (seconds < 10) clock += "0";
		clock += seconds;

		return clock;
	}

	function clarificationsChange(data) {
		$('.clarifications tr.inserted').remove();

		for (var i = 0; i < data.clarifications.length; i++) {
			var clarification = data.clarifications[i];

			var r = $('.clarifications tbody tr.template').clone().removeClass('template').addClass('inserted');

			$('.problem', r).html(clarification.problem_alias);
			$('.time', r).html(clarification.time);
			$('.message', r).html(clarification.message);
			$('.answer', r).html(clarification.answer);

			$('.clarifications tbody').append(r);
		}
	}
	
	function drawChart() {
		drawLineChart({
			element: 'ranking-chart',
	    		labels: [new Date("17 Jan 2012 4:15"), new Date("17 Jan 2012 5:15"), new Date("17 Jan 2012 6:15")],
	    		values: [1, 2, 3],
	    		colorhue: 0,
	    		width: 800,
	    		height: 300
		});
	}
});
