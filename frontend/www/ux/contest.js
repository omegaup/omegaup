$(document).ready(function() {
	var arena = new Arena();
	var problems = {};
	var activeTab = 'problems';	
	var currentProblem = null;
	var currentContest = null;
	var submissionGap = 0;
	var answeredClarifications = 0;
	var clarificationsOffset = 0;
	var clarificationsRowcount = 20;
	var socket = null;
	var rankChartLimit = 10;
	var practice = window.location.pathname.indexOf('/practice/') !== -1;
	var onlyProblem = window.location.pathname.indexOf('/problem/') !== -1;

	if (onlyProblem) {		
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

	function updateRunFallback(guid, orig_run) {
		setTimeout(function() { omegaup.runStatus(guid, updateRun); }, 5000);
	}

	function updateRun(run) {
		// Actualiza el objeto en los problemas. 
		for (p in problems) {
			if (!problems.hasOwnProperty(p)) continue;
			for (r in problems[p].runs) {
				if (!problems[p].runs.hasOwnProperty(r)) continue;

				if (problems[p].runs[r].guid == run.guid) {
					problems[p].runs[r] = run;
					break;
				}
			}
		}
		var r = '#run_' + run.guid;

		if (run.status == 'ready') {
			$(r + ' .runtime').html((parseFloat(run.runtime) / 1000).toFixed(2));
			$(r + ' .memory').html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
			$(r + ' .points').html(parseFloat(run.contest_score).toFixed(2));
			$(r + ' .penalty').html(run.submit_delay);
		}
		$(r + ' .status').html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
		$(r + ' .time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));

		if (socket == null) {
			if (run.status == 'ready') {
				if (!practice && !onlyProblem) {
					omegaup.getRanking(contestAlias, rankingChange);
				}
			} else {
				updateRunFallback(run.guid, run);
			}
		}
	}

	function onlyProblemLoaded(problem) {
		if (problem.status == 'error') {
			if (!omegaup.loggedIn && omegaup.login_url) {
				window.location = omegaup.login_url + "?redirect=" + escape(window.location);
			} else {
				$('#loading').html('404');
			}
			return;
		} 
		
		currentProblem = problem;
		
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
						$('#loading').html(x + ' ' + formatDelta(y.getTime() - t.getTime()));
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
		} else if (practice && contest.finish_time && new Date().getTime() < contest.finish_time.getTime()) {
			window.location = window.location.pathname.replace(/\/practice\/.*/, '/');
			return;
		}

		$('#title .contest-title').html(omegaup.escape(contest.title));
		$('#summary .title').html(omegaup.escape(contest.title));
		$('#summary .description').html(omegaup.escape(contest.description));
					
		$('#summary .start_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.start_time.getTime()));
		$('#summary .finish_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.finish_time.getTime()));
		$('#summary .window_length').html(contest.window_length);

		currentContest = contest;

		arena.initClock(contest.start_time, contest.finish_time, contest.submission_deadline);

		submissionGap = parseInt(contest.submission_gap);

		if (!(submissionGap > 0)) submissionGap = 0;

		arena.initProblems(contest.problems);

		for (var idx in contest.problems) {
			var problem = contest.problems[idx];
			var problemName = problem.letter + '. ' + omegaup.escape(problem.title);

			problems[problem.alias] = problem;
			if (!problems[problem.alias].runs) {
				problems[problem.alias].runs = [];
			}

			var prob = $('#problem-list .template').clone().removeClass('template').addClass('problem_' + problem.alias);
			$('.name', prob).attr('href', '#problems/' + problem.alias).html(problemName);
			$('#problem-list').append(prob);

			$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');
		}

		if (!practice) {
			omegaup.getRanking(contestAlias, rankingChange);
			setInterval(function() { omegaup.getRanking(contestAlias, rankingChange); }, 5 * 60 * 1000);

			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			setInterval(function() { 
				clarificationsOffset = 0; // Return pagination to start on refresh
				omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
			}, 5 * 60 * 1000);
		}

		// Trigger the event (useful on page load).
		$(window).hashchange();

		$('#loading').fadeOut('slow');
		$('#root').fadeIn('slow');

		arena.connectSocket();
	}
	
	if (onlyProblem) {
		omegaup.getProblem(null, onlyProblemAlias, onlyProblemLoaded)
	} else {
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
			
			if (!onlyProblem) {
				problems[currentProblem.alias].last_submission = new Date().getTime();
			}
		
			run.status = 'new';
			run.contest_score = 0;
			run.time = new Date;
			run.penalty = '-';
			run.runtime = 0;
			run.memory = 0;
			run.language = $('#submit select[name="language"]').val();
			var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
			$('.guid', r).html(run.guid.substring(run.guid.length - 5));
			$('.runtime', r).html('-');
			$('.memory', r).html('-');
			$('.status', r).html('new');
			$('.points', r).html('0');
			$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
			$('.language', r).html(run.language)
			$('#problem .runs > tbody:last').append(r);
			if (!currentProblem.runs) {
				currentProblem.runs = [];
			}
			currentProblem.runs.push(run);

			if (socket == null) {
				updateRunFallback(run.guid, run);
			}

			$('#overlay').hide();
			$('#submit input').removeAttr('disabled');
			$('#submit textarea[name="code"]').val('');
			var code_file = $('#code_file');
			code_file.replaceWith(code_file = code_file.clone(true));
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});
	}

	$('#submit').submit(function(e) {
		if (!onlyProblem && (problems[currentProblem.alias].last_submission + submissionGap * 1000 > new Date().getTime())) {
			alert('Deben pasar ' + submissionGap + ' segundos entre envios de un mismo problema');
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

			if (file.size >= 10240) {
				// 10kb should be enough for anybody.
				alert('El límite para subir archivos son 10kB');
				return false;
			}

			reader.onload = function(e) {
				submitRun((practice || onlyProblem)? '' : contestAlias,
					  currentProblem.alias,
					  $('#submit select[name="language"]').val(),
					  e.target.result);
			};

			if (file.type.indexOf('text/') === 0) {
				reader.readAsText(file, 'UTF-8');
			} else {
				reader.readAsDataURL(file);
			}

			return false;
		}

		if (!code) return false;

		submitRun((practice || onlyProblem)? '' : contestAlias, currentProblem.alias, $('#submit select[name="language"]').val(), code);

		return false;
	});
	
	$('.clarifpager .clarifpagerprev').click(function () {
		if (clarificationsOffset > 0) {
			clarificationsOffset -= clarificationsRowcount;
			if (clarificationsOffset < 0) {
				clarificationsOffset = 0;
			}
			
			// Refresh with previous page
			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
		}
	});
	
	$('.clarifpager .clarifpagernext').click(function () {
		clarificationsOffset += clarificationsRowcount;
		if (clarificationsOffset < 0) {
			clarificationsOffset = 0;
		}
		
		// Refresh with previous page
		omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
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
			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			$('#clarification input').removeAttr('disabled');
		});

		return false;
	});

	$(window).hashchange(function(e) {
		
		if (onlyProblem) {
						
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

				function updateOnlyProblemRuns(runs, score_column, multiplier) {
					for (var idx in runs) {
						if (!runs.hasOwnProperty(idx)) continue;
						var run = runs[idx];

						var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
						$('.guid', r).html(run.guid.substring(run.guid.length - 5));
						$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
						$('.memory', r).html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
						$('.points', r).html((parseFloat(run[score_column]) * multiplier).toFixed(2));
						$('.status', r).html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
						$('.penalty', r).html(run.submit_delay);
						if (run.time) {
							$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
						}
						$('.language', r).html(run.language);
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
						$('#problem .runs > tbody:last').append(r);
					}
				}


				omegaup.getProblemRuns(problem.alias, function (data) {
					updateOnlyProblemRuns(data.runs, 'score', 100);
				});


				MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#problem .statement').get(0)]);
			}

			updateOnlyProblem(currentProblem);		
			var isNewRunOnlyProblem = window.location.hash.indexOf('#new-run') !== -1;
			
			if (isNewRunOnlyProblem) {
				$('#overlay form').hide();
				$('#submit input').show();
				$('#submit #lang-select').show();
				$('#submit').show();
				$('#overlay').show();
				$('#submit textarea[name="code"]').val('');
			}
			
		} else {
							
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
					$('#problem > .title').html(problem.letter + '. ' + omegaup.escape(problem.title));
					$('#problem .data .points').html(problem.points);
					$('#problem .validator').html(problem.validator);
					$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
					$('#problem .memory_limit').html(problem.memory_limit / 1024 + "MB");
					$('#problem .statement').html(problem.problem_statement);
					$('#problem .source span').html(omegaup.escape(problem.source));
					$('#problem .runs tfoot td a').attr('href', '#problems/' + problem.alias + '/new-run');

					$('#problem .run-list .added').remove();

					function updateProblemRuns(runs, score_column, multiplier) {
						for (var idx in runs) {
							if (!runs.hasOwnProperty(idx)) continue;
							var run = runs[idx];

							var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
							$('.guid', r).html(run.guid.substring(run.guid.length - 5));
							$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
							$('.memory', r).html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
							$('.points', r).html((parseFloat(run[score_column]) * multiplier).toFixed(2));
							$('.status', r).html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
							$('.penalty', r).html(run.submit_delay);
							if (run.time) {
								$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
							}
							$('.language', r).html(run.language);
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
							$('#problem .runs > tbody:last').append(r);
						}
					}

					if (practice || onlyProblem) {
						omegaup.getProblemRuns(problem.alias, function (data) {
							updateProblemRuns(data.runs, 'score', 100);
						});
					} else {
						updateProblemRuns(problem.runs, 'contest_score', 1);
					}

					MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#problem .statement').get(0)]);
				}

				if (problem.problem_statement !== undefined) {
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
					$('#submit input').show();
					$('#submit #lang-select').show();
					$('#submit').show();
					$('#overlay').show();
					$('#submit textarea[name="code"]').val('');
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

				if (activeTab == 'ranking') {
							if (arena.currentEvents) {
								arena.onRankingEvents(arena.currentEvents);
							}
				} else if (activeTab == 'clarifications') {
					$('#clarifications-count').css("font-weight", "normal");
				}
			}
		}
		
	});

	function rankingChange(data) {
		arena.onRankingChanged(data);
		omegaup.getRankingEvents(contestAlias, arena.onRankingEvents.bind(arena));
	}
	
	function clarificationsChange(data) {
		$('.clarifications tr.inserted').remove();
		if (data.clarifications.length > 0 && data.clarifications.length < clarificationsRowcount) {
			$('#clarifications-count').html("(" + data.clarifications.length + ")");
		} else if (data.clarifications.length >= clarificationsRowcount) {
			$('#clarifications-count').html("("+ data.clarifications.length + "+)");
		}

		var previouslyAnswered = answeredClarifications;
		answeredClarifications = 0;

		for (var i = 0; i < data.clarifications.length; i++) {
			var clarification = data.clarifications[i];

			var r = $('.clarifications tbody tr.template').clone().removeClass('template').addClass('inserted');

			$('.problem', r).html(clarification.problem_alias);
						
						if (clarification.can_answer) {
							$('.author', r).html(clarification.author);
						}
						
			$('.time', r).html(clarification.time);
			$('.message', r).html(omegaup.escape(clarification.message));
			$('.answer', r).html(omegaup.escape(clarification.answer));
			if (clarification.answer) {
				answeredClarifications++;
			}

			if (clarification.can_answer) {
				(function(id, answer, answerNode) {
					if (clarification.public == 1) {
						$('input[type="checkbox"]', answer).attr('checked', 'checked');
					}
					answer.submit(function () {
						omegaup.updateClarification(
							id,
							$('textarea', answer).val(),
							$('input[type="checkbox"]', answer).attr('checked'),
							function() {
								answerNode.html($('textarea', answer).val());
								$('textarea', answer).val('');
							}
						);
						return false;
					});

					answerNode.append(answer);
				})(clarification.clarification_id, $('<form><input type="checkbox" /><textarea></textarea><input type="submit" /></form>'), $('.answer', r));
			}

			$('.clarifications tbody').append(r);
		}

		if (answeredClarifications > previouslyAnswered && activeTab != 'clarifications') {
			$('#clarifications-count').css("font-weight", "bold");
		}
	}
});
