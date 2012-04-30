$(document).ready(function() {
	var omegaup = new OmegaUp();
	var problems = {};
	var activeTab = 'problems';
	var currentProblem = null;
	var currentRanking = {};
	var currentEvents;
	var currentContest = null;
	var startTime = null;
	var finishTime = null;
	var submissionDeadline = null;
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

	var contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	Highcharts.setOptions({
		global: {
			useUTC: false
		}
	});

	omegaup.getContest(contestAlias, function(contest) {
		if (contest.status == 'error') {
			$('#loading').html('404');
			return;
		}

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
		if (!$('#submit textarea[name="code"]').val()) return false;

		$('#submit input').attr('disabled', 'disabled');
		omegaup.submit(contestAlias, currentProblem.alias, $('#submit select[name="language"]').val(), $('#submit textarea[name="code"]').val(), function (run) {
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
			var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
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
							$(r + ' .points').html(parseInt(run.contest_score).toFixed(2));
							$(r + ' .penalty').html(run.submit_delay);
						}
						$(r + ' .status').html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
						$(r + ' .time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));

						if (run.status == 'ready') {
							omegaup.getRanking(contestAlias, rankingChange);
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
			omegaup.getClarifications(contestAlias, clarificationsChange);
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

				for (var idx in problem.runs) {
					if (!problem.runs.hasOwnProperty(idx)) continue;
					var run = problem.runs[idx];

					var r = $('#problem .run-list .template').clone().removeClass('template').addClass('added').attr('id', 'run_' + run.guid);
					$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
					$('.memory', r).html((parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
					$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
					$('.status', r).html(run.status == 'ready' ? (veredicts[run.veredict] ? "<abbr title=\"" + veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
					$('.penalty', r).html(run.submit_delay);
					if (run.time) {
						$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
					}
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
            
			// if rank went up, add a class
			if (parseInt($('.points', r).html()) < parseInt(rank.total.points)) {
				r.addClass('rank-up');
			}
            
			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);
            
			// update miniranking
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

		for (var i = 0; i < data.clarifications.length; i++) {
			var clarification = data.clarifications[i];

			var r = $('.clarifications tbody tr.template').clone().removeClass('template').addClass('inserted');

			$('.problem', r).html(clarification.problem_alias);
			$('.time', r).html(clarification.time);
			$('.message', r).html(clarification.message);
			$('.answer', r).html(clarification.answer);

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
