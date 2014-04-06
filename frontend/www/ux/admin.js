$(document).ready(function() {
	var arena = new Arena();
	var activeTab = 'problems';
	var currentProblem = null;
	var runsOffset = 0;
	var runsRowcount = 100;
	var runsVeredict = "";
	var runsStatus = "";
	var runsProblem = "";
	var runsLang = "";
	var runsUsername = "";	
	var clarificationsOffset = 0;
	var clarificationsRowcount = 20;
	var rankChartLimit = 1e99;
	var practice = false;
	var onlyProblem = false;

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
			$('#title .contest-title').html(omegaup.escape(contest.title));

			arena.currentContest = contest;

			arena.initClock(contest.start_time, contest.finish_time);
			arena.initProblems(contest);

			for (var idx in contest.problems) {
				var problem = contest.problems[idx];
				var problemName = String.fromCharCode(problem.letter) + '. ' + omegaup.escape(problem.title);

				arena.problems[problem.alias] = problem;

				$('#submit select[name="problem"]').append($('<option>' + problemName + '</option>').attr('value', problem.alias));
				$('#rejudge-problem-list').append($('<option>' + problemName + '</option>').attr('value', problem.alias));

				$('select.runsproblem').append($('<option></option>').attr('value', problem.alias).text(problem.alias));


				$('#clarification select').append('<option value="' + problem.alias + '">' + problemName + '</option>');
			}

			omegaup.getRanking(arena.contestAlias, arena.rankingChange.bind(arena));
			setInterval(function() { omegaup.getRanking(arena.contestAlias, arena.rankingChange.bind(arena)); }, 5 * 60 * 1000);

			omegaup.getClarifications(arena.contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			setInterval(function() { 
				clarificationsOffset = 0; // Return pagination to start on refresh
				omegaup.getClarifications(arena.contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
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
		if (clarificationsOffset > 0) {
			clarificationsOffset -= clarificationsRowcount;
			if (clarificationsOffset < 0) {
				clarificationsOffset = 0;
			}
			
			// Refresh with previous page
			omegaup.getClarifications(arena.contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
		}
	});
	
	$('.clarifpager .clarifpagernext').click(function () {
		clarificationsOffset += clarificationsRowcount;
		if (clarificationsOffset < 0) {
			clarificationsOffset = 0;
		}
		
		// Refresh with previous page
		omegaup.getClarifications(arena.contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
	});

	$('#submit').submit(function(e) {
		if (!$('#submit textarea[name="code"]').val()) return false;

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(arena.contestAlias, $('#submit select[name="problem"]').val(), $('#submit select[name="language"]').val(), $('#submit textarea[name="code"]').val(), function (run) {
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

			arena.updateRunFallback(run.guid, run);

			$('#overlay').hide();
			$('#submit input').removeAttr('disabled');
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});

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
			omegaup.getClarifications(arena.contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
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
		} else if (problem && arena.problems[problem[1]]) {
			var newRun = problem[2];
			currentProblem = problem = arena.problems[problem[1]];

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
				omegaup.getProblem(arena.contestAlias, problem.alias, function (problem_ext) {
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
		$('.runs .run-list .added').remove();

		for (var idx in data.runs) {
			if (!data.runs.hasOwnProperty(idx)) continue;
			var run = data.runs[idx];

			var r = $('.runs .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
			$('.id', r).html(run.run_id);
			$('.guid', r).html(run.guid);
			$('.username', r).html(run.username);
			$('.problem', r).html('<a href="/arena/problem/' + run.alias + '">' + run.alias + '</a>');
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
						arena.updateRunFallback(guid, run);
					});
				}));
			})(run.guid, run, r);
			(function(guid, run, row) {
				$('.details', row).append($('<input type="button" value="details" />').click(function() {
					omegaup.runDetails(guid, function(data) {
						$('#run-details .compile_error').html('');
						if (data.compile_error) {
							$('#run-details .compile_error').html(omegaup.escape(data.compile_error));
						}
						if (data.source.indexOf('data:') === 0) {
							$('#run-details .source').html('<a href="' + data.source + '" download="data.zip">descarga</a>');
						} else {
							$('#run-details .source').html(omegaup.escape(data.source));
						}
						$('#run-details .cases div').remove();
						$('#run-details .cases table').remove();
						$('#run-details .download a').attr('href', '/api/run/download/run_alias/' + guid + '/');

						function numericSort(key) {
							function isDigit(x) {
								return '0' <= x && x <= '9';
							}

							return function(x, y) {
								var i = 0, j = 0;
								for (; i < x[key].length && j < y[key].length; i++, j++) {
									if (isDigit(x[key][i]) && isDigit(x[key][j])) {
										var nx = 0, ny = 0;
										while (i < x[key].length && isDigit(x[key][i]))
											nx = (nx * 10) + parseInt(x[key][i++]);
										while (j < y[key].length && isDigit(y[key][j]))
											ny = (ny * 10) + parseInt(y[key][j++]);
										i--; j--;
										if (nx != ny) return nx - ny;
									} else if (x[key][i] < y[key][j]) {
										return -1;
									} else if (x[key][i] > y[key][j]) {
										return 1;
									}
								}
								return (x[key].length - i) - (y[key].length - j);
							};
						}

						data.groups.sort(numericSort('group'));
						for (var i = 0; i < data.groups.length; i++) {
							data.groups[i].cases.sort(numericSort('name'));
						}

						var groups = $('<table><thead><tr><th>Grupo</th><th>Caso</th><th>Metadata</th><th>V</th><th>S</th></thead></table>');

						function addBlock(text, className) {
								groups.append(
									$('<tr></tr>')
										.append($('<td></td>'))
										.append(
											$('<td colspan="3"></td>')
												.append(
													$('<pre></pre>')
														.addClass(className)
														.html(omegaup.escape(g.cases[0].out_diff))
												)
										)
								);
						}

						for (var i = 0; i < data.groups.length; i++) {
							var g = data.groups[i];
							if (g.cases.length == 1) {
									groups.append(
										$('<tr class="group"></tr>')
											.append('<th>' + g.cases[0].name + '</th>')
											.append('<td></td>')
											.append('<td>' + JSON.stringify(g.cases[0].meta) + '</td>')
											.append('<td>' + g.cases[0].veredict + '</td>')
											.append('<th class="score">' + g.cases[0].score + '</th>')
									);
									if (g.cases[0].err) addBlock(g.cases[0].err, 'stderr');
									if (g.cases[0].out_diff) addBlock(g.cases[0].out_diff, 'diff');
							} else {
								groups.append(
									$('<tr class="group"></tr>')
										.append('<th colspan="4">' + omegaup.escape(g.group) + '</th>')
										.append('<th class="score">' + g.score + '</th>')
								);
								for (var j = 0; j < g.cases.length; j++) {
									var c = g.cases[j];
									groups.append(
										$('<tr></tr>')
											.append('<td></td>')
											.append('<td>' + c.name + '</td>')
											.append('<td>' + JSON.stringify(c.meta) + '</td>')
											.append('<td>' + c.veredict + '</td>')
											.append('<td class="score">' + c.score + '</td>')
									);
									if (c.err) addBlock(c.err, 'stderr');
									if (c.out_diff) addBlock(c.out_diff, 'diff');
								}
							}
						}
						$('#run-details .cases').append(groups);
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
				arena.notify(
					clarification.author + " - " + clarification.problem_alias,
					omegaup.escape(clarification.message),
					r[0],
					'clarification-' + clarification.clarification_id
				);
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
