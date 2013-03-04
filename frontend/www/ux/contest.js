$(document).ready(function() {
	var problems = {};
	var activeTab = 'problems';
	var currentProblem = null;
	var currentRanking = {};
	var currentEvents;
	var currentContest = null;
	var startTime = null;
	var finishTime = null;
	var submissionDeadline = null;
	var submissionGap = 0;
	var answeredClarifications = 0;
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
	var colors = [
		'#FB3F51',
		'#FF5D40',
		'#FFA240',
		'#FFC740',
		'#59EA3A',
		'#37DD6F',
		'#34D0BA',
		'#3AAACF',
		'#8144D6',
		'#CD35D3',
	];
	var rankChartLimit = 10;
	var practice = window.location.pathname.indexOf('/practice/') !== -1;

	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	function contestLoaded(contest) {
		if (contest.status == 'error') {
			if (contest.start_time) {
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

		var loc = window.location, new_uri;
		if (loc.protocol === "https:") {
			new_uri = "wss:";
		} else {
			new_uri = "ws:";
		}
		new_uri += "//" + loc.host;
		new_uri += "/api/contest/events/" + contest.alias + "/";

		var ws = new WebSocket(new_uri, "omegaup.com.events");
		ws.onopen = function(e) { console.log(e); };
		ws.onclose = function(e) { console.log(e); };
		ws.onmessage = function(e) { console.log(e); };
		ws.onerror = function(e) { console.log(e); };

		$('#login_bar a.user').append(omegaup.username);
		$('#login_bar img').attr('src', 'https://secure.gravatar.com/avatar/' + omegaup.email_md5 + '?s=16');

		$('#title .contest-title').html(contest.title);
		$('#summary .title').html(contest.title);
		$('#summary .description').html(contest.description);
					
		$('#summary .start_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.start_time.getTime()));
		$('#summary .finish_time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', contest.finish_time.getTime()));
		$('#summary .window_length').html(contest.window_length);

		currentContest = contest;

		startTime = contest.start_time;
		finishTime = contest.finish_time;
		submissionDeadline = contest.submission_deadline;

		submissionGap = parseInt(contest.submission_gap);

		if (!(submissionGap > 0)) submissionGap = 0;

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

		if (!practice) {
			omegaup.getRanking(contestAlias, rankingChange);
			setInterval(function() { omegaup.getRanking(contestAlias, rankingChange); }, 5 * 60 * 1000);

			omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange);
			setInterval(function() { 
				clarificationsOffset = 0; // Return pagination to start on refresh
				omegaup.getClarifications(contestAlias, clarificationsOffset, clarificationsRowcount, clarificationsChange); 
			}, 5 * 60 * 1000);

			updateClock();
			setInterval(updateClock, 1000);
		}

		// Trigger the event (useful on page load).
		$(window).hashchange();

			$('#loading').fadeOut('slow');
			$('#root').fadeIn('slow');
	}
	
	omegaup.getContest(contestAlias, contestLoaded);

	$('#overlay, .close').click(function(e) {
		if (e.target.id === 'overlay' || e.target.className === 'close') {
			$('#overlay, #submit #clarification').hide();
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
			return false;
		}
	});

	$('#submit').submit(function(e) {
		if (!$('#submit textarea[name="code"]').val()) return false;

		if (!$('#submit select[name="language"]').val()) {
			alert('Debes elegir un lenguaje');
			return;
		}

		if (problems[currentProblem.alias].last_submission + submissionGap * 1000 > new Date().getTime()) {
			alert('Deben pasar ' + submissionGap + ' segundos entre envios de un mismo problema');
			return;
		}

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(practice ? '' : contestAlias, currentProblem.alias, $('#submit select[name="language"]').val(), $('#submit textarea[name="code"]').val(), function (run) {
			if (run.status != 'ok') {
				alert(run.error);
				$('#submit input').removeAttr('disabled');
				return;
			}
			problems[currentProblem.alias].last_submission = new Date().getTime();
			run.status = 'new';
			run.contest_score = 0;
			run.time = new Date;
			run.penalty = '-';
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
			currentProblem.runs.push(run);

			function updateRun(guid, orig_run) {
				setTimeout(function() {
					omegaup.runStatus(guid, function(run) {
						var r = '#run_' + run.guid;

						orig_run.runtime = run.runtime;
						orig_run.memory = run.memory;
						orig_run.contest_score = run.contest_score;
						orig_run.status = run.status;
						orig_run.veredict = run.veredict;
						orig_run.submit_delay = run.submit_delay;
						orig_run.time = run.time;
						orig_run.language = run.language;

						if (run.status == 'ready') {
							$(r + ' .runtime').html((parseFloat(run.runtime) / 1000).toFixed(2));
							$(r + ' .memory').html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
							$(r + ' .points').html(parseFloat(run.contest_score).toFixed(2));
							$(r + ' .penalty').html(run.submit_delay);
						}
						$(r + ' .status').html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
						$(r + ' .time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));

						if (run.status == 'ready') {
							if (!practice) {
								omegaup.getRanking(contestAlias, rankingChange);
							}
						} else {
							updateRun(run.guid, orig_run);
						}
					});
				}, 5000);
			}

			updateRun(run.guid, run);

			$('#overlay').hide();
			$('#submit input').removeAttr('disabled');
			$('#submit textarea[name="code"]').val('');
			window.location.hash = window.location.hash.substring(0, window.location.hash.lastIndexOf('/'));
		});

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

				function updateProblemRuns(runs, score_column, multiplier) {
					for (var idx in runs) {
						if (!runs.hasOwnProperty(idx)) continue;
						var run = runs[idx];

						var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
						$('.guid', r).html(run.guid.substring(run.guid.length - 5));
						$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
						$('.memory', r).html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
						$('.points', r).html((parseFloat(run[score_column]) * multiplier).toFixed(2));
						$('.status', r).html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
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

				if (practice) {
					omegaup.getProblemRuns(problem.alias, function (data) {
						updateProblemRuns(data.runs, 'score', 100);
					});
				} else {
					updateProblemRuns(problem.runs, 'contest_score', 1);
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
						if (currentEvents) {
							rankingEvents(currentEvents);
						}
					} else if (activeTab == 'clarifications') {
				$('#clarifications-count').css("font-weight", "normal");
			}
		}
		
	});

	function rankingEvents(data) {
		currentEvents = data;
		var dataInSeries = {};
		var navigatorData = [[startTime.getTime(), 0]];
		var series = [];
		var usernames = {};
		
		// group points by person
		for (var i = 0, l = data.events.length; i < l; i++) {
			var curr = data.events[i];
			
			// limit chart to top n users
			if (currentRanking[curr.username] > rankChartLimit - 1) continue;
			
			if (!dataInSeries[curr.name]) {
					dataInSeries[curr.name] = [[startTime.getTime(), 0]];
					usernames[curr.name] = curr.username;
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
						rank: currentRanking[usernames[i]],
						data: dataInSeries[i],
						step: true
					});
			}
		}
		
		series.sort(function (a, b) {
			return a.rank - b.rank;
		});
		
		navigatorData.push([Math.min(finishTime.getTime(), Date.now()), navigatorData[navigatorData.length - 1][1]]);
		
		if (series.length > 0) {
			// chart it!
			createChart(series, navigatorData);

			// now animated sort the ranking table!
			$("#ranking-table").sortTable({
			onCol: 1,
			keepRelationships: true,
			sortType: 'numeric'
			});
		}
	}

	function rankingChange(data) {
		$('#mini-ranking tbody tr.inserted').remove();

		var ranking = data.ranking;
		var newRanking = {};

		var place = 0;
		var lastPoints = 1e99;
		var lastPenalty = 0;

		for (var i = 0; i < ranking.length; i++) {
			var rank = ranking[i];
			newRanking[rank.username] = i;
			
			// new user, just add row at the end
			if (currentRanking[rank.username] === undefined) {
				currentRanking[rank.username] = $('#ranking tbody tr.inserted').length;
				$('#ranking tbody').append(
					$('#ranking tbody tr.template').clone().removeClass('template').addClass('inserted').addClass('rank-new')
				);
			}
			
			// update a user's row
			var r = $('#ranking tbody tr.inserted')[currentRanking[rank.username]];
			$('.user', r).html(rank.username + ' (' + rank.name + ')');

			for (var alias in rank.problems) {
				if (!rank.problems.hasOwnProperty(alias)) continue;
				
				$('.prob_' + alias + '_points', r).html(rank.problems[alias].points);
				$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty);

				if (rank.username == omegaup.username) {
					$('#problems .problem_' + alias + ' .solved').html("(" + rank.problems[alias].points + " / " + problems[alias].points + ")");
				}
			}
			
			// if rank went up, add a class
			if (parseInt($('.points', r).html()) < parseInt(rank.total.points)) {
				r.addClass('rank-up');
			}
			
			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);

			if (lastPoints != rank.total.points || lastPenalty != rank.total.penalty) {
				lastPoints = rank.total.points;
				lastPenalty = rank.total.penalty;
				place = i + 1;
			}
			$('.position', r).html(place);
			
			// update miniranking
			if (i < 10) {
				r = $('#mini-ranking tbody tr.template').clone().removeClass('template').addClass('inserted');

				$('.position', r).html(place);
				var username = rank.username + ' (' + rank.name + ')';
				$('.user', r).html('<span title="' + username + '">' + rank.username + '</span>');
				$('.points', r).html(rank.total.points);
				$('.penalty', r).html(rank.total.penalty);

				$('#mini-ranking tbody').append(r);
			}
		}

		currentRanking = newRanking;
		
		omegaup.getRankingEvents(contestAlias, rankingEvents);
	}
	
	function updateClock() {
		var date = new Date().getTime();
		var clock = "";

		if (date < startTime.getTime()) {
				clock = "-" + formatDelta(startTime.getTime() - (date + omegaup.deltaTime));
		} else if (date > submissionDeadline.getTime()) {
				clock = "00:00:00";
		} else {
				clock = formatDelta(submissionDeadline.getTime() - (date + omegaup.deltaTime));
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
			$('.message', r).html(clarification.message);
			$('.answer', r).html(clarification.answer);
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
	
	function createChart(series, navigatorSeries) {
		if (series.length == 0) return;
		
		Highcharts.setOptions({
			colors: colors
		});
	
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
		var rows = $('#ranking tbody tr.inserted');
		for (var r = 0; r < rows.length; r++) {
			$('.legend', rows[r]).css({
				'background-color': r < rankChartLimit ? colors[r] : 'transparent'
			});
		}
	}
});
