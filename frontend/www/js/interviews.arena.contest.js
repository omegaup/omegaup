$(document).ready(function() {
	var arena = new Arena();
	var admin = null;

	var contestAlias = /\/interview\/([^\/]+)\/arena/.exec(window.location.pathname)[1];
	arena.contestAlias = contestAlias;

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	function contestLoaded(contest) {
		if (contest.status == 'error') {
			if (!omegaup.loggedIn && omegaup.login_url) {
				window.location = omegaup.login_url + "?redirect=" + escape(window.location);
			} else if (contest.start_time) {
				var f = (function(x, y) {
					return function() {
						var t = omegaup.time();
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
		} else if (arena.practice && contest.finish_time && omegaup.time().getTime() < contest.finish_time.getTime()) {
			window.location = window.location.pathname.replace(/\/practice.*/, '/');
			return;
		}

		$('#title .contest-title').html(omegaup.escape(contest.title));
		$('#summary .title').html(omegaup.escape(contest.title));
		$('#summary .description').html(omegaup.escape(contest.description));

		$('#summary .window_length').html(Arena.formatDelta((contest.window_length * 60000)));
		$('#summary .contest_organizer').html(
			'<a href="/profile/' + contest.director + '/">' + contest.director + '</a>');

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

	//arena.connectSocket();
	omegaup.getContest(contestAlias, contestLoaded);

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

	$('#overlay, .close').click(function(e) {
		if (e.target.id === 'overlay' || e.target.className === 'close') {
			$('#submit #clarification').hide();
			arena.hideOverlay();
			var code_file = $('#submit-code-file');
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

			if (arena.lockdown && sessionStorage) {
				sessionStorage.setItem('run:' + run.guid, code);
			}

			if (!arena.onlyProblem) {
				arena.problems[arena.currentProblem.alias].last_submission = omegaup.time().getTime();
			}

			run.username = omegaup.username;
			run.status = 'new';
			run.alias = arena.currentProblem.alias;
			run.contest_score = null;
			run.time = new Date;
			run.penalty = 0;
			run.runtime = 0;
			run.memory = 0;
			run.language = $('#submit select[name="language"]').val();
			arena.updateRun(run);

			$('#submit input').removeAttr('disabled');
			$('#submit textarea[name="code"]').val('');
			var code_file = $('#submit-code-file');
			code_file.replaceWith(code_file = code_file.clone(true));
			arena.hideOverlay();
		});
	}

	$('#submit select[name="language"]').change(function (e) {
		var lang = $('#submit select[name="language"]').val();
		if (lang == 'cpp11') {
			$('#submit-filename-extension').text('.cpp');
		} else if (lang && lang != 'cat') {
			$('#submit-filename-extension').text('.' + lang);
		} else {
			$('#submit-filename-extension').text();
		}
	});

	$('#submit').submit(function(e) {
		if (!arena.onlyProblem && (arena.problems[arena.currentProblem.alias].last_submission + arena.submissionGap * 1000 > omegaup.time().getTime())) {
			alert('Deben pasar ' + arena.submissionGap + ' segundos entre envios de un mismo problema');
			return false;
		}

		if (!$('#submit select[name="language"]').val()) {
			alert('Debes elegir un lenguaje');
			return false;
		}

		var code = $('#submit textarea[name="code"]').val();
		var file = $('#submit-code-file')[0];
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

			if ($('#submit select[name="language"]').val() != 'cat' ||
					file.type.indexOf('text/') === 0 ||
					extension == 'cpp' || extension == 'c' ||
					extension == 'java' || extension == 'txt' ||
					extension == 'hs' || extension == 'kp' ||
					extension == 'kj' || extension == 'p' ||
					extension == 'pas' || extension == 'py' ||
					extension == 'rb') {
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

	$('#clarification').submit(function (e) {
		$('#clarification input').attr('disabled', 'disabled');
		omegaup.newClarification(contestAlias, $('#clarification select[name="problem"]').val(), $('#clarification textarea[name="message"]').val(), function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#clarification input').removeAttr('disabled');
				return;
			}
			arena.hideOverlay();
			omegaup.getClarifications(contestAlias, arena.clarificationsOffset, arena.clarificationsRowcount, arena.clarificationsChange.bind(arena));
			$('#clarification input').removeAttr('disabled');
		});

		return false;
	});

	$(window).hashchange(function(e) {
		if (arena.onlyProblem) {
			onlyProblemHashChanged(e);
		} else {
			arena.onHashChanged();
		}
	});
});
