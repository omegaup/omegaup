$(document).ready(function() {
	var problems = {};
	var activeTab = 'problems';
	var currentProblem = null;
	var currentRanking = {};
	var currentEvents;
	var currentContest = null;
	var currentNotifications = {count: 0, timer: null};
	var startTime = null;
	var finishTime = null;
	var submissionDeadline = null;
	var runsOffset = 0;
	var runsRowcount = 100;
	var runsVeredict = "";
	var runsStatus = "";
	var runsProblem = "";
	var runsLang = "";
	var clarificationsOffset = 0;
	var clarificationsRowcount = 20;
	var veredicts = {
		AC: "Accepted",
		PA: "Partially Accepted",
		WA: "Wrong Answer",
		TLE: "Time Limit Exceeded",
		MLE: "Memory Limit Exceeded",
		OLE: "Output Limit Exceeded",
		RTE: "Runtime Error",
		RFE: "Restricted Function",
		CE: "Compilation Error",
		JE: "Judge Error" 
	};

	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	omegaup.getContest(contestAlias, function(contest) {
		$('#title .contest-title').html(contest.title);

		currentContest = contest;

		startTime = contest.start_time;
		finishTime = contest.finish_time;
		submissionDeadline = contest.submission_deadline;

		var letter = 65;

		for (var idx in contest.problems) {
			var problem = contest.problems[idx];
			var problemName = String.fromCharCode(letter) + '. ' + problem.title;

			problems[problem.alias] = problem;

			problem.letter = String.fromCharCode(letter);

			$('#submit select[name="problem"]').append($('<option>' + problemName + '</option>').attr('value', problem.alias));
			$('#rejudge-problem-list').append($('<option>' + problemName + '</option>').attr('value', problem.alias));
			
			$('select.runsproblem').append($('<option></option>').attr('value', problem.alias).text(problem.alias));
			
			letter++;
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
				$('#problem > .title').html(problem.letter + '. ' + problem.title);
				$('#problem .data .points').html(problem.points);
				$('#problem .validator').html(problem.validator);
				$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
				$('#problem .memory_limit').html(problem.memory_limit / 1024 + "MB");
				$('#problem .statement').html(problem.problem_statement);
				$('#problem .source span').html(problem.source);
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
				if (currentEvents) {
					rankingEvents(currentEvents);
				}
			}
		}
		
	});

	function rankingEvents(data) {
		currentEvents = data;
		var dataInSeries = {};
		var navigatorData = [[startTime.getTime(), 0]];
		var series = [];
		
		// group points by person
		for (var i = 0, l = data.events.length; i < l; i++) {
			var curr = data.events[i];
			if (!dataInSeries[curr.name]) {
				dataInSeries[curr.name] = [[startTime.getTime(), 0]];
			}
			dataInSeries[curr.name].push([
				startTime.getTime() + curr.delta*60*1000,
				curr.total.points
			]);
			
			// check if to add to navigator
			if (curr.total.points > navigatorData[navigatorData.length-1][1]) {
				navigatorData.push([
					startTime.getTime() + curr.delta*60*1000,
					curr.total.points
				]);
			}
		}
		
		// convert datas to series
		for (var i in dataInSeries) {
			if (dataInSeries.hasOwnProperty(i)) {
			dataInSeries[i].push([Math.min(finishTime.getTime(), Date.now()), dataInSeries[i][dataInSeries[i].length - 1][1]]);
			series.push({
				name: i,
				data: dataInSeries[i],
				step: true
			});
			}
		}
		navigatorData.push([Math.min(finishTime.getTime(), Date.now()), navigatorData[navigatorData.length - 1][1]]);
		
		if (series.length > 0) {
			// chart it!
			createChart(series, navigatorData);

			// now animated sort the ranking table!
			$("#ranking").sortTable({
			onCol: 1,
			keepRelationships: true,
			sortType: 'numeric'
			});
		}
	}

	function rankingChange(data) {
		var ranking = data.ranking;
		var newRanking = {};
		var place = 0;
		var lastScore = 1e99;
		var lastPenalty = 0;

		for (var i = 0; i < ranking.length; i++) {
			var rank = ranking[i];
			newRanking[rank.name] = i;
			
			// new user, just add row at the end
			if (currentRanking[rank.name] === undefined) {
				currentRanking[rank.name] = $('#ranking tbody tr.inserted').length;
				$('#ranking tbody').append(
					$('#ranking tbody tr.template').clone().removeClass('template').addClass('inserted').addClass('rank-new')
				);
			}
			
			// update a user's row
			var r = $('#ranking tbody tr.inserted')[currentRanking[rank.name]];
			$('.user', r).html(rank.name);

			for (var alias in rank.problems) {
				if (!rank.problems.hasOwnProperty(alias)) continue;
				
				$('.prob_' + alias + '_points', r).html(rank.problems[alias].points);
				$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty);
			}
			
			// if rank went up, add a class
			if (parseInt($('.points', r)) < parseInt(rank.total.points)) {
				r.addClass('rank-up');
			}

			if (lastScore != rank.total.points || lastPenalty != rank.total.penalty) {
				lastScore = rank.total.points;
			       	lastPenalty = rank.total.penalty;
				place = i + 1;
			}
			
			$('.position', r).html(place);
			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);
		}

		currentRanking = newRanking;
		
		omegaup.getRankingEvents(contestAlias, rankingEvents);
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
				$('.memory', r).html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
				$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
				$('.status', r).html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
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
	
		omegaup.getContestRuns(contestAlias, options, runsChange); 
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
			$('.memory', r).html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
			$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
			$('.status', r).html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
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
						$('#run-details .source').html(data.source.replace(/</g, "&lt;"));
						$('#run-details .cases div').remove();

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
			$('.message', r).html(clarification.message);
			$('.answer', r).html(clarification.answer);

			if (!clarification.answer) {
				notify(clarification.author + " - " + clarification.problem_alias, clarification.message, r[0], clarification.clarification_id);
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
	
	function createChart(series, navigatorSeries) {
		if (series.length == 0) return;
	
		window.chart = new Highcharts.StockChart({
			chart: {
				renderTo: 'ranking-chart',
				height: 300,
				spacingTop: 20
			},

			xAxis: {
				ordinal: false,
				min: startTime.getTime(),
				max: Math.min(finishTime.getTime(), Date.now())
			},

			yAxis: {
				showLastLabel: true,
				showFirstLabel: false,
				min: 0,
				max: (function() {
					var total = 0;
					for (var prob in problems) {
						if (problems.hasOwnProperty(prob)) {
							total += parseInt(problems[prob].points, 10);
						}
					}
					return total;
				})()
			},
			
			plotOptions: {
				series: {
					lineWidth: 3,
					states: {
						hover: {
							lineWidth: 3
						}
					},
					marker: {
						radius: 5,
						symbol: 'circle',
						lineWidth: 1
					}
				}
			},

			navigator: {
				series: {
					type: 'line',
					step: true,
					lineWidth: 3,
					lineColor: '#333',
					data: navigatorSeries
				}
			},

			rangeSelector: {
				enabled: false
			},
			
			series: series
		});
		
		// set legend colors
		for (var name in currentRanking) {
			if (currentRanking.hasOwnProperty(name)) {
				var r = $('#ranking tbody tr.inserted')[currentRanking[name]];
				var color = (function () {
					for (var i = 0; i < window.chart.series.length; i++) {
						if (window.chart.series[i].name === name) {
							return window.chart.series[i].color;
						}
					}
				})();
				
				$('.legend', r).css({
					'background-color': color || 'transparent'
				});
			}
		}
	}
});
