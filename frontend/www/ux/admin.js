$(document).ready(function() {
	var arena = new Arena();
	var problems = {};
	var activeTab = 'problems';
	var currentProblem = null;
	var currentContest = null;
	var currentNotifications = {count: 0, timer: null};
	var runsOffset = 0;
	var runsRowcount = 100;
	var runsVeredict = "";
	var runsStatus = "";
	var runsProblem = "";
	var runsLang = "";
	var clarificationsOffset = 0;
	var clarificationsRowcount = 20;
	var rankChartLimit = 1e99;
	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];		

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	if (contestAlias === "admin") {		
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
		omegaup.getContest(contestAlias, function(contest) {
			$('#title .contest-title').html(omegaup.escape(contest.title));

			currentContest = contest;

			arena.initClock(contest.start_time, contest.finish_time);
			arena.initProblems(contest.problems);

			for (var idx in contest.problems) {
				var problem = contest.problems[idx];
				var problemName = String.fromCharCode(problem.letter) + '. ' + omegaup.escape(problem.title);

				problems[problem.alias] = problem;


				$('#submit select[name="problem"]').append($('<option>' + problemName + '</option>').attr('value', problem.alias));
				$('#rejudge-problem-list').append($('<option>' + problemName + '</option>').attr('value', problem.alias));

				$('select.runsproblem').append($('<option></option>').attr('value', problem.alias).text(problem.alias));


				$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');
			}

			omegaup.getRanking(contestAlias, rankingChange);
			setInterval(function() { omegaup.getRanking(contestAlias, rankingChange); }, 5 * 60 * 1000);

			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			setInterval(function() { 
				clarificationsOffset = 0; // Return pagination to start on refresh
				omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
			}, 5 * 60 * 1000);

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
	
	$('select.runsveredict, select.runsstatus, select.runsproblem, select.runslang').change(function () {
		runsVeredict = $('select.runsveredict option:selected').val();
		runsStatus	 = $('select.runsstatus	  option:selected').val();
		runsProblem	 = $('select.runsproblem  option:selected').val();
		runsLang	 = $('select.runslang	  option:selected').val();
		console.log("changed select");
		refreshRuns();
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

	$('#submit').submit(function(e) {
		if (!$('#submit textarea[name="code"]').val()) return false;

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(contestAlias, $('#submit select[name="problem"]').val(), $('#submit select[name="language"]').val(), $('#submit textarea[name="code"]').val(), function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#submit input').removeAttr('disabled');
				return;
			}
			run.status = 'new';
			run.contest_score = 0;
			run.time = new Date;
			run.penalty = '-';
			run.language = $('#submit select[name="language"]').val();
			var r = $('tbody.run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
			$('.guid', r).html(run.guid);
			$('.status', r).html('new');
			$('.points', r).html('0');
			$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
			$('.language', r).html(run.language)
			$('table.runs tbody').prepend(r);

			updateRun(run.guid, run);

			$('#overlay').hide();
			$('#submit input').removeAttr('disabled');
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});

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
			$('#overlay').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			$('#clarification input').removeAttr('disabled');
		});

		return false;
	});

	$('#rejudge-problem').click(function() {
		if (confirm('Deseas rejuecear el problema ' + $('#rejudge-problem-list').val() + '?')) {
			omegaup.rejudgeProblem($('#rejudge-problem-list').val(), function (x) {
				refreshRuns();
			});
		}
		return false;
	});

	$('#update-problem').submit(function() {
		return confirm('Deseas actualizar el problema ' + $('#rejudge-problem-list').val() + '?');
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

		if (window.location.hash == '#new-run') {
			$('#overlay form').hide();
			$('#submit').show();
			$('#overlay').show();
		} else if (problem && problems[problem[1]]) {
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
		} else if (window.location.hash == '#run/details') {
			$('#run-details').show();
			$('#overlay').show();
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
			}
		}
		
	});

	function rankingChange(data) {
		arena.onRankingChanged(data);
		omegaup.getRankingEvents(contestAlias, arena.onRankingEvents.bind(arena));
	}

	function updateRun(guid, orig_run) {
		setTimeout(function() {
			omegaup.runStatus(guid, function(run) {
				var r = $('#run_' + run.guid);

				orig_run.runtime = run.runtime;
				orig_run.memory = run.memory;
				orig_run.contest_score = run.contest_score;
				orig_run.status = run.status;
				orig_run.veredict = run.veredict;
				orig_run.submit_delay = run.submit_delay;
				orig_run.time = run.time;
				orig_run.language = run.language;

				$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
				$('.memory', r).html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
				$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
				$('.status', r).html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
				$('.penalty', r).html(run.submit_delay);
				$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
				$('.language', r).html(run.language);

				if (run.status == 'ready') {
					omegaup.getRanking(contestAlias, rankingChange);
				} else {
					updateRun(guid, orig_run);
				}
			});
		}, 5000);
	}
	
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
	
		if (contestAlias === "admin") {
			omegaup.getRuns(options, runsChange);
		} else {
			omegaup.getContestRuns(contestAlias, options, runsChange); 
		}
		
	}

	function runsChange(data) {
		$('.runs .run-list .added').remove();

		for (var idx in data.runs) {
			if (!data.runs.hasOwnProperty(idx)) continue;
			var run = data.runs[idx];

			var r = $('.runs .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
			$('.id', r).html(run.run_id);
			$('.guid', r).html(run.guid);
			$('.username', r).html(run.username);
			$('.problem', r).html(run.alias);
			$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
			$('.memory', r).html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
			$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
			$('.percentage', r).html((parseFloat(run.score) * 100).toFixed(2) + '%');
			$('.status', r).html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
			if (run.veredict == 'JE')
			{
				$('.status', r).css('background-color', '#f00');
			}
			else if (run.veredict == 'RTE' || run.veredict == 'CE' || run.veredict == 'RFE')
			{
				$('.status', r).css('background-color', '#ff9900');
			}
			else if (run.veredict == 'AC')
			{
				$('.status', r).css('background-color', '#CCFF66');
			}
			$('.penalty', r).html(run.submit_delay);
			if (run.time) {
				$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
			}
			$('.language', r).html(run.language);
			(function(guid, run, row) {
				$('.rejudge', row).append($('<input type="button" value="rejudge" />').click(function() {
					$('.status', row).html('rejudging');
					omegaup.runRejudge(guid, function() {
						updateRun(guid, run);
					});
				}));
			})(run.guid, run, r);
			(function(guid, run, row) {
				$('.details', row).append($('<input type="button" value="details" />').click(function() {
					omegaup.runDetails(guid, function(data) {
						$('#run-details .compile_error').html('');
						if (data.compile_error) {
							$('#run-details .compile_error').html(data.compile_error.replace('&', '&amp;').replace('<', '&lt;'));
						}
						if (data.source.indexOf('data:') === 0) {
							$('#run-details .source').html('<a href="' + data.source + '" download="data.zip">descarga</a>');
						} else {
							$('#run-details .source').html(data.source.replace(/</g, "&lt;"));
						}
						$('#run-details .cases div').remove();
						$('#run-details .download a').attr('href', '/api/run/download/run_alias/' + guid + '/');

						function isDigit(x) {
							return '0' <= x && x <= '9';
						}

						function numericSort(x, y) {
							var i = 0, j = 0;
							for (; i < x.name.length && j < y.name.length; i++, j++) {
								if (isDigit(x.name[i]) && isDigit(x.name[j])) {
									var nx = 0, ny = 0;
									while (i < x.name.length && isDigit(x.name[i]))
										nx = (nx * 10) + parseInt(x.name[i++]);
									while (j < y.name.length && isDigit(y.name[j]))
										ny = (ny * 10) + parseInt(y.name[j++]);
									i--; j--;
									if (nx != ny) return nx - ny;
								} else if (x.name[i] < y.name[j]) {
									return -1;
								} else if (x.name[i] > y.name[j]) {
									return 1;
								}
							}
							return (x.name.length - i) - (y.name.length - j);
						}

						data.cases.sort(numericSort);

						for (var i = 0; i < data.cases.length; i++) {
							var c = data.cases[i];
							$('#run-details .cases').append($("<div></div>").append($("<h2></h2>").html(c.name)));
							$('#run-details .cases').append($("<div></div>").html(JSON.stringify(c.meta)));
							$('#run-details .cases').append($("<div></div>").append($("<pre></pre>").html(c.out_diff ? c.out_diff.replace(/&/g, '&amp;').replace(/</g, '&lt;') : "")));
							$('#run-details .cases').append($("<div></div>").append($("<pre></pre>").html(c.err ? c.err.replace(/&/g, '&amp;').replace(/</g, '&lt;') : "")));
						}
						window.location.hash = 'run/details';
						$(window).hashchange();
						$('#run-details').show();
						$('#submit').hide();
						$('#clarification').hide();
						$('#overlay').show();
					});
				}));
			})(run.guid, run, r);
			$('.runs > tbody:last').append(r);
		}
	}
	
	function flashTitle(reset) {
		if (document.title.indexOf("!") === 0) {
			document.title = document.title.substring(2);
		} else if (!reset) {
			document.title = "! " + document.title;
		}
	}

	function notify(title, message, element, id) {
		if (currentNotifications.hasOwnProperty(id)) {
			return;
		}

		if (currentNotifications.timer == null) {
			currentNotifications.timer = setInterval(flashTitle, 1000);
		}

		currentNotifications.count++;

		var gid = $.gritter.add({
			title: title,
			text: message,
			sticky: true,
			before_close: function() {
				if (element) {
					window.focus();
					element.scrollIntoView(true);
				}
				delete currentNotifications[id];

				currentNotifications.count--;
				if (currentNotifications.count == 0) {
					clearInterval(currentNotifications.timer);
					currentNotifications.timer = null;
					flashTitle(true);
				}
			}
		});

		currentNotifications[id] = gid;

		document.getElementById('notification_audio').play();
	}

	function clarificationsChange(data) {
		$('.clarifications tr.inserted').remove();

		for (var i = 0; i < data.clarifications.length; i++) {
			var clarification = data.clarifications[i];

			var r = $('.clarifications tbody tr.template').clone().removeClass('template').addClass('inserted');

			$('.problem', r).html(clarification.problem_alias);
			$('.author', r).html(clarification.author);
			$('.time', r).html(clarification.time);
			$('.message', r).html(omegaup.escape(clarification.message));
			$('.answer', r).html(omegaup.escape(clarification.answer));

			if (!clarification.answer) {
				notify(clarification.author + " - " + clarification.problem_alias, omegaup.escape(clarification.message), r[0], clarification.clarification_id);
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
	}
});
