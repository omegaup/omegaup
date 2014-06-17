$(document).ready(function() {
	var arena = new Arena();

	if (arena.onlyProblem) {
		var onlyProblemAlias = /\/arena\/problem\/([^\/]+)\/?/.exec(window.location.pathname)[1];		
	}
	else {
		var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];
	}

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	function onlyProblemLoaded(problem) {
		if (problem.status == 'error') {
			if (!omegaup.loggedIn && omegaup.login_url) {
				window.location = omegaup.login_url + "?redirect=" + escape(window.location);
			} else {
				$('#loading').html('404');
			}
			return;
		} 
		
		arena.currentProblem = problem;
		
		// Trigger the event (useful on page load).
		$(window).hashchange();

		$('#loading').fadeOut('slow');
		$('#root').fadeIn('slow');
	}

	function contestLoaded(contest) {
		if (contest.status == 'error') {
			if (!omegaup.loggedIn && omegaup.login_url) {
				window.location = omegaup.login_url + "?redirect=" + escape(window.location);
			} else if (contest.start_time) {
				var f = (function(x, y) {
					return function() {
						var t = new Date();
						$('#loading').html(x + ' ' + Arena.formatDelta(y.getTime() - t.getTime()));
						if (t.getTime() < y.getTime()) {
							setTimeout(f, 1000);
						} else {
							omegaup.getContest(x, contestLoaded);
						}
					}
				})(contestAlias, omegaup.time(contest.start_time * 1000));
				setTimeout(f, 1000);
			} else {
				$('#loading').html('404');
			}
			return;
		} else if (arena.practice && contest.finish_time && new Date().getTime() < contest.finish_time.getTime()) {
			window.location = window.location.pathname.replace(/\/practice.*/, '/');
			return;
		}

		$('#title .contest-title').html(omegaup.escape(contest.title));
		$('#summary .title').html(omegaup.escape(contest.title));
		$('#summary .description').html(omegaup.escape(contest.description));
					
		$('#summary .start_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.start_time.getTime()));
		$('#summary .finish_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.finish_time.getTime()));
		var duration = contest.finish_time.getTime() - contest.start_time.getTime();
		$('#summary .window_length').html(Arena.formatDelta((contest.window_length * 60000) || duration));
		$('#summary .scoreboard_cutoff').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S',
			contest.start_time.getTime() + duration * contest.scoreboard / 100));
		$('#summary .contest_organizer').html(
			'<a href="/profile.php?username=' + contest.director + '">' + contest.director + '</a>');

		arena.submissionGap = parseInt(contest.submission_gap);
		if (!(arena.submissionGap > 0)) arena.submissionGap = 0;

		arena.initClock(
				contest.start_time,
				contest.finish_time,
				contest.submission_deadline
		);
		arena.initProblems(contest);

		for (var idx in contest.problems) {
			var problem = contest.problems[idx];
			var problemName = problem.letter + '. ' + omegaup.escape(problem.title);

			var prob = $('#problem-list .template').clone().removeClass('template').addClass('problem_' + problem.alias);
			$('.name', prob).attr('href', '#problems/' + problem.alias).html(problemName);
			$('#problem-list').append(prob);

			$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');
		}

		if (!arena.practice) {
			arena.setupPolls();
		}

		// Trigger the event (useful on page load).
		$(window).hashchange();

		$('#loading').fadeOut('slow');
		$('#root').fadeIn('slow');
	}
	
	if (arena.onlyProblem) {
		$('body').attr('id', 'only-problem');
		omegaup.getProblem(null, onlyProblemAlias, onlyProblemLoaded)
	} else {
		arena.connectSocket();
		omegaup.getContest(contestAlias, contestLoaded);
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

	function submitRun(contestAlias, problemAlias, lang, code) {
		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(contestAlias, problemAlias, lang, code, function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#submit input').removeAttr('disabled');
				return;
			}
			
			if (!arena.onlyProblem) {
				arena.problems[arena.currentProblem.alias].last_submission = new Date().getTime();
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
			$('#submit textarea[name="code"]').val('');
			var code_file = $('#code_file');
			code_file.replaceWith(code_file = code_file.clone(true));
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});
	}

	$('#submit').submit(function(e) {
		if (!arena.onlyProblem && (arena.problems[arena.currentProblem.alias].last_submission + arena.submissionGap * 1000 > new Date().getTime())) {
			alert('Deben pasar ' + arena.submissionGap + ' segundos entre envios de un mismo problema');
			return false;
		}

		if (!$('#submit select[name="language"]').val()) {
			alert('Debes elegir un lenguaje');
			return false;
		}

		var code = $('#submit textarea[name="code"]').val();
		var file = $('#code_file')[0];
		if (file && file.files && file.files.length > 0) {
			file = file.files[0];
			var reader = new FileReader();

			reader.onload = function(e) {
				submitRun((arena.practice || arena.onlyProblem)? '' : contestAlias,
					  arena.currentProblem.alias,
					  $('#submit select[name="language"]').val(),
					  e.target.result);
			};

			var extension = file.name.split(/\./);
			extension = extension[extension.length - 1];

			if (file.type.indexOf('text/') === 0 ||
					extension == 'cpp' || extension == 'c' ||
					extension == 'java' || extension == 'txt' ||
					extension == 'hs' || extension == 'kp' ||
					extension == 'p' || extension == 'pas' ||
					extension == 'py' || extension == 'rb') {
				if (file.size >= 10240) {
					alert('El límite para subir archivos son 10kB');
					return false;
				}
				reader.readAsText(file, 'UTF-8');
			} else {
				// 100kB _must_ be enough for anybody.
				if (file.size >= 102400) {
					alert('El límite para subir archivos son 100kB');
					return false;
				}
				reader.readAsDataURL(file);
			}

			return false;
		}

		if (!code) return false;

		submitRun((arena.practice || arena.onlyProblem) ? '' : contestAlias, arena.currentProblem.alias, $('#submit select[name="language"]').val(), code);

		return false;
	});
	
	$('.clarifpager .clarifpagerprev').click(function () {
		if (arena.clarificationsOffset > 0) {
			arena.clarificationsOffset -= arena.clarificationsRowcount;
			if (arena.clarificationsOffset < 0) {
				arena.clarificationsOffset = 0;
			}
			
			// Refresh with previous page
			omegaup.getClarifications(contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena)); 
		}
	});
	
	$('.clarifpager .clarifpagernext').click(function () {
		arena.clarificationsOffset += arena.clarificationsRowcount;
		if (arena.clarificationsOffset < 0) {
			arena.clarificationsOffset = 0;
		}
		
		// Refresh with previous page
		omegaup.getClarifications(contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena)); 
	});

	$('#clarification').submit(function (e) {
		$('#clarification input').attr('disabled', 'disabled');
		omegaup.newClarification(contestAlias, $('#clarification select[name="problem"]').val(), $('#clarification textarea[name="message"]').val(), function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#clarification input').removeAttr('disabled');
				return;
			}
			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			omegaup.getClarifications(contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena));
			$('#clarification input').removeAttr('disabled');
		});

		return false;
	});

	$(window).hashchange(function(e) {
		if (arena.onlyProblem) {
			function updateOnlyProblem(problem) {
				$('#summary').hide();
				$('#problem').show();
				$('#problem > .title').html(omegaup.escape(problem.title));
				$('#problem .data .points').html(problem.points);
				$('#problem .validator').html(problem.validator);
				$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
				$('#problem .memory_limit').html(problem.memory_limit / 1024 + "MB");
				$('#problem .statement').html(problem.problem_statement);
				$('#problem .source span').html(omegaup.escape(problem.source));
				$('#problem .runs tfoot td a').attr('href', '#new-run');

				$('#problem .run-list .added').remove();

				var language_array = problem.languages.split(',');
				$('#lang-select option').each(function(index, item) {
					if (language_array.indexOf($(item).val()) >= 0) {
						$(item).show();
					} else {
						$(item).hide();
					}
				});

				function updateOnlyProblemRuns(runs, score_column, multiplier) {
					for (var idx in runs) {
						if (!runs.hasOwnProperty(idx)) continue;
						var run = runs[idx];

						var r = $('#problem .run-list .template')
							.clone()
							.removeClass('template')
							.addClass('added')
							.addClass('run_' + run.guid);
						(function(guid) {
							$('.code', r).append($('<input type="button" value="ver" />').click(function() {
								omegaup.runSource(guid, function(data) {
									if (data.compile_error){							
										$('#submit textarea[name="code"]').val(data.source + '\n\n--------------------------\nCOMPILE ERROR:\n' + data.compile_error);
									} else {
										$('#submit textarea[name="code"]').val(data.source);
									}
									$('#submit input').hide();
									$('#submit #lang-select').hide();
									$('#submit').show();
									$('#clarification').hide();
									$('#overlay').show();
									window.location.hash += '/show-run';
								});
								return false;
							}));
						})(run.guid);
						arena.displayRun(run, r);
						$('#problem .runs > tbody:last').append(r);
					}
				}

				omegaup.getProblemRuns(problem.alias, function (data) {
					updateOnlyProblemRuns(data.runs, 'score', 100);
				});

				MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#problem .statement').get(0)]);
			}

			updateOnlyProblem(arena.currentProblem);
			var isNewRunOnlyProblem = window.location.hash.indexOf('#new-run') !== -1;
			
			if (isNewRunOnlyProblem) {
				if (!omegaup.loggedIn && omegaup.login_url) {
					window.location = omegaup.login_url + "?redirect=" + escape(window.location);
					return;
				}
				$('#overlay form').hide();
				$('#submit input').show();
				$('#submit #lang-select').show();
				$('#submit').show();
				$('#overlay').show();
				$('#submit textarea[name="code"]').val('');
			}
		} else {
			arena.onHashChanged();
		}
	});
});
