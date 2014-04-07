$(document).ready(function() {
	var arena = new Arena();
	var runsOffset = 0;
	var runsRowcount = 100;
	var runsVeredict = "";
	var runsStatus = "";
	var runsProblem = "";
	var runsLang = "";
	var runsUsername = "";	

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	if (arena.contestAlias === "admin") {
		refreshRuns();
		setInterval(function() { 
			runsOffset = 0; // Return pagination to start on refresh
			refreshRuns();
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
			refreshRuns();
			setInterval(function() { 
				runsOffset = 0; // Return pagination to start on refresh
				refreshRuns();
			}, 5 * 60 * 1000);

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
	
	$('.runspager .runspagerprev').click(function () {
		if (runsOffset > 0) {
			runsOffset -= runsRowcount;
			if (runsOffset < 0) {
				runsOffset = 0;
			}
			
			// Refresh with previous page
			refreshRuns();
		}
	});
	
	$('.runspager .runspagernext').click(function () {
		runsOffset += runsRowcount;
		if (runsOffset < 0) {
			runsOffset = 0;
		}
		
		// Refresh with previous page
		refreshRuns();
	});
	
	$("#runsusername").typeahead({
		ajax: "/api/user/list/",
		display: 'label',
		val: 'label',
		minLength: 2,
		itemSelected: function (item, val, text) {						
			// Refresh runs by calling change func
			runsUsername = val;
			$('select.runsveredict').change();
		}
	});
	
	$('#runsusername-clear').click(function() {
		runsUsername = "";
		$("#runsusername").val('');
		$('select.runsveredict').change();
	});
	
	if (arena.contestAlias === "admin") {
		$("#runsproblem").typeahead({
			ajax: { 
				url: "/api/problem/list/",
				preProcess: function(data) { 
					return data["results"];
				}
			},
			display: 'title',
			val: 'alias',
			minLength: 2,
			itemSelected: function (item, val, text) {						
				// Refresh runs by calling change func
				runsProblem = val;
				$('select.runsveredict').change();
			}
		});

		$('#runsproblem-clear').click(function() {
			runsProblem = "";
			$("#runsproblem").val('');
			$('select.runsveredict').change();
		});
	}
	
	$('select.runsveredict, select.runsstatus, select.runsproblem, select.runslang').change(function () {
		runsVeredict = $('select.runsveredict option:selected').val();
		runsStatus	 = $('select.runsstatus	  option:selected').val();						
		runsLang	 = $('select.runslang	  option:selected').val();
		
		// in general admin panel, runsProblem is populated via the typehead
		if (arena.contestAlias != "admin") {
			runsProblem	 = $('select.runsproblem  option:selected').val();
		}
		
		console.log("changed select");
		refreshRuns();
	});
	
	$('.clarifpager .clarifpagerprev').click(function () {
		if (arena.clarificationsOffset > 0) {
			arena.clarificationsOffset -= arena.clarificationsRowcount;
			if (arena.clarificationsOffset < 0) {
				arena.clarificationsOffset = 0;
			}
			
			// Refresh with previous page
			omegaup.getClarifications(arena.contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena));
		}
	});
	
	$('.clarifpager .clarifpagernext').click(function () {
		arena.clarificationsOffset += arena.clarificationsRowcount;
		if (arena.clarificationsOffset < 0) {
			arena.clarificationsOffset = 0;
		}
		
		// Refresh with previous page
		omegaup.getClarifications(arena.contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena)); 
	});

	$('#submit').submit(function(e) {
		if (!$('#submit textarea[name="code"]').val()) return false;

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(
			arena.contestAlias,
			arena.currentProblem.alias,
			$('#submit select[name="language"]').val(),
			$('#submit textarea[name="code"]').val(),
			function (run) {
				if (run.status != 'ok') {
					alert(run.error);
					$('#submit input').removeAttr('disabled');
					return;
				}
				run.status = 'new';
				run.alias = arena.currentProblem.alias;
				run.contest_score = 0;
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

	$('#clarification').submit(function (e) {
		$('#clarification input').attr('disabled', 'disabled');
		omegaup.newClarification(arena.contestAlias, $('#clarification select[name="problem"]').val(), $('#clarification textarea[name="message"]').val(), function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#clarification input').removeAttr('disabled');
				return;
			}
			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			omegaup.getClarifications(arena.contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, clarificationsChange);
			$('#clarification input').removeAttr('disabled');
		});

		return false;
	});

	$('#rejudge-problem').click(function() {
		if (confirm('Deseas rejuecear el problema ' + arena.currentProblem.alias + '?')) {
			omegaup.rejudgeProblem(arena.currentProblem.alias, function (x) {
				refreshRuns();
			});
		}
		return false;
	});

	$('#update-problem').submit(function() {
		$('#update-problem input[name="problem_alias"]').val(arena.currentProblem.alias);
		return confirm('Deseas actualizar el problema ' + arena.currentProblem.alias + '?');
	});

	$(window).hashchange(arena.onHashChanged.bind(arena));

	function refreshRuns() {
		var options = {
			offset: runsOffset, 
			rowcount: runsRowcount
		};
		
		if (runsVeredict != "") {
			options.veredict = runsVeredict;
		}
		
		if (runsStatus != "") {
			options.status = runsStatus;
		}
		
		if (runsProblem != "") {
			options.problem_alias = runsProblem;
		}
		
		if (runsLang != "") {
			options.language = runsLang;
		}
		
		if (runsUsername != "") {
			options.username = runsUsername;
		}
	
		if (arena.contestAlias === "admin") {
			omegaup.getRuns(options, runsChange);
		} else {
			omegaup.getContestRuns(arena.contestAlias, options, runsChange);
		}
	}

	function runsChange(data) {
		$('#runs .runs .run-list .added').remove();

		for (var idx in data.runs) {
			if (!data.runs.hasOwnProperty(idx)) continue;
			var run = data.runs[idx];

			var r = arena.createAdminRun(run);
			arena.displayRun(run, r);
			$('#runs .runs > tbody:last').append(r);
		}
	}
});
