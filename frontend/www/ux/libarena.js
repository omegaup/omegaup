function Arena() {
	// The current contest.
	this.currentContest = null;

	// The interval for clock updates.
	this.clockInterval = null;

	// The start time of the contest.
	this.startTime = null;

	// The finish time of the contest.
	this.finishTime = null;

	// The deadline for submissions. This might be different from the end time.
	this.submissionDeadline = null;

	// All runs in this contest/problem.
	this.runs = {};

	// The guid of any run that is pending.
	this.pendingRuns = {};

	// The set of problems in this contest.
	this.problems = {};

	// WebSocket for real-time updates.
	this.socket = null;

	// The offset of each user into the ranking table.
	this.currentRanking = {};

	// The previous ranking information. Useful to show diffs.
	this.prevRankingState = null;

	// Every time a recent event is shown, have this interval clear it after 30s.
	this.removeRecentEventClassTimeout = null;

	// The last known scoreboard event stream.
	this.currentEvents = null;

	// Currently opened notifications.
	this.currentNotifications = {count: 0, timer: null};

	// Currently opened problem.
	this.currentProblem = null;

	// Whether the current contest is in practice mode.
	this.practice = window.location.pathname.indexOf('/practice') !== -1;

	// Whether this is a full contest or only a problem.
	this.onlyProblem = window.location.pathname.indexOf('/problem/') !== -1;

	// Whether this is a scoreboard-only view.
	this.onlyScoreboard = window.location.pathname.indexOf('/scoreboard/') !== -1;

	// The alias of the contest.
	this.contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	// The token for standalone scoreboards.
	this.scoreboardToken = null;

	// If websockets are enabled.
	this.enableSockets = window.location.search.indexOf('ws=off') === -1;

	// If we have admin powers in this contest.
	this.admin = false;
	this.answeredClarifications = 0;
	this.clarificationsOffset = 0;
	this.clarificationsRowcount = 20;
	this.activeTab = 'problems';
	this.clarifications = {};
	this.submissionGap = 0;

	// Setup any global hooks.
	this.installLibinteractiveHooks();
};

Arena.verdicts = {
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

Arena.scoreboardColors = [
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

Arena.prototype.installLibinteractiveHooks = function() {
	$('#libinteractive-download').submit(function(e) {
		var form = $(e.target);
		var alias = e.target.attributes['data-alias'].value;
		var os = form.find('.download-os').val();
		var lang = form.find('.download-lang').val();
		var extension = (os == 'unix' ? '.tar.bz2' : '.zip');

		var new_location = (
			window.location.protocol + '//' + window.location.host + '/templates/' +
			alias + '/' + alias + '_' + os + '_' + lang + extension);
		window.location = new_location;

		return false;
	});

	$('#libinteractive-download .download-lang').change(function(e) {
		var form = $('#libinteractive-download');
		form.find('.libinteractive-extension').html(form.find('.download-lang').val());
	});
}

Arena.prototype.connectSocket = function() {
	var self = this;
	if (self.practice || !self.enableSockets || self.contestAlias == 'admin') {
		return false;
	}

	var uri;
	if (window.location.protocol === "https:") {
		uri = "wss:";
	} else {
		uri = "ws:";
	}
	uri += "//" + window.location.host + "/api/contest/events/" + self.contestAlias + "/";
	if (self.scoreboardToken) {
		uri += "?token=" + self.scoreboardToken;
	}

	try {
		self.socket = new WebSocket(uri, "com.omegaup.events");
		$('#title .socket-status').html('&bull;');
		self.socket.onmessage = function(message) {
			console.log(message);
			var data = JSON.parse(message.data);

			if (data.message == "/run/update/") {
				data.run.time = omegaup.time(data.run.time * 1000);
				self.updateRun(data.run);
			} else if (data.message == "/clarification/update/") {
				if (!self.onlyScoreboard) {
					data.clarification.time = omegaup.time(data.clarification.time * 1000);
					self.updateClarification(data.clarification);
				}
			} else if (data.message == '/scoreboard/update/') {
				self.rankingChange(data.scoreboard);
			}
		};
		self.socket.onopen = function() {
			$('#title .socket-status').html('&bull;').css('color', '#080');
			self.socket_keepalive = setInterval((function(socket) {
				return function() {
					socket.send('"ping"');
				};
			})(self.socket), 30000);
		};
		self.socket.onclose = function(e) {
			$('#title .socket-status').html('&cross;').css('color', '#800');
			self.socket = null;
			clearInterval(self.socket_keepalive);
			setTimeout(function() { self.setupPolls(); }, Math.random() * 15000);
			console.error(e);
		};
		self.socket.onerror = function(e) {
			$('#title .socket-status').html('&cross;').css('color', '#800');
			self.socket = null;
			clearInterval(self.socket_keepalive);
			setTimeout(function() { self.setupPolls(); }, Math.random() * 15000);
			console.error(e);
		};
	} catch (e) {
		self.socket = null;
		console.error(e);
	}
};

Arena.prototype.setupPolls = function() {
	var self = this;

	omegaup.getRanking(
		self.contestAlias,
		self.rankingChange.bind(self)
	);
	omegaup.getClarifications(
		self.contestAlias,
		self.clarificationsOffset,
		self.clarificationsRowcount,
		self.clarificationsChange.bind(self)
	);

	if (!self.socket) {
		self.clarificationInterval = setInterval(function() {
			self.clarificationsOffset = 0; // Return pagination to start on refresh
			omegaup.getClarifications(
				self.contestAlias,
				self.clarificationsOffset,
				self.clarificationsRowcount,
				self.clarificationsChange.bind(self));
		}, 5 * 60 * 1000);

		self.rankingInterval = setInterval(function() {
			omegaup.getRanking(self.contestAlias, self.rankingChange.bind(self));
		}, 5 * 60 * 1000);
	}
};

Arena.prototype.initClock = function(start, finish, deadline) {
	this.startTime = start;
	this.finishTime = finish;
	if (this.practice) {
		$('#title .clock').html('&infin;');
		return;
	}
	if (deadline) this.submissionDeadline = deadline;
	if (!this.clockInterval) {
		this.updateClock();
		this.clockInterval = setInterval(this.updateClock.bind(this), 1000);
	}
};

Arena.prototype.initProblems = function(contest) {
	var self = this;
	self.currentContest = contest;
	self.admin = contest.admin;
	problems = contest.problems;
	for (var i = 0; i < problems.length; i++) {
		var alias = problems[i].alias;
		problems[i].runs = problems[i].runs || [];
		self.problems[alias] = problems[i];

		$('<th><a href="#problems/' + alias + '" title="' + alias + '">' +
				problems[i].letter + '</a></th>').insertBefore('#ranking thead th.total');
		$('<td class="prob_' + alias + '_points"></td>')
			.insertBefore('#ranking tbody .template td.points');
	}
	$('#ranking thead th').attr('colspan', '');
	$('#ranking tbody .template .penalty').remove();
};

Arena.prototype.updateClock = function() {
	var countdownTime = this.submissionDeadline || this.finishTime;
	if (this.startTime === null || countdownTime === null) {
		return;
	}

	var date = omegaup.time().getTime();
	var clock = "";

	if (date < this.startTime.getTime()) {
		clock = "-" + Arena.formatDelta(this.startTime.getTime() - (date + omegaup.deltaTime));
	} else if (date > countdownTime.getTime()) {
		clock = "00:00:00";
		clearInterval(this.clockInterval);
		this.clockInterval = null;
	} else {
		clock = Arena.formatDelta(countdownTime.getTime() - (date + omegaup.deltaTime));
	}

	$('#title .clock').html(clock);
};

Arena.prototype.updateRunFallback = function(guid, orig_run) {
	var self = this;
	if (self.socket == null) {
		setTimeout(function() { omegaup.runStatus(guid, self.updateRun.bind(self)); }, 5000);
	}
}

Arena.prototype.updateRun = function(run) {
	var self = this;

	// Actualiza el objeto en los problemas.
	for (p in self.problems) {
		if (!self.problems.hasOwnProperty(p)) continue;
		for (r in self.problems[p].runs) {
			if (!self.problems[p].runs.hasOwnProperty(r)) continue;

			if (self.problems[p].runs[r].guid == run.guid) {
				self.problems[p].runs[r] = run;
				break;
			}
		}
	}
	if (self.admin && $('#runs .run_' + run.guid).length == 0) {
		$('#runs .runs > tbody:last').prepend(self.createAdminRun(run));
	}
	var r = $('.run_' + run.guid);
	self.displayRun(run, r);

	if (self.socket == null) {
		if (run.status == 'ready') {
			if (!self.practice && !self.onlyProblem && self.contestAlias != 'admin') {
				omegaup.getRanking(self.contestAlias, self.rankingChange.bind(self));
			}
		} else {
			self.updateRunFallback(run.guid, run);
		}
	}
};

Arena.prototype.createAdminRun = function(run) {
	var self = this;
	var r = $('#runs .runs .run-list .template')
		.clone()
		.removeClass('template')
		.addClass('added')
		.addClass('run_' + run.guid);

	// Rejudge
	(function(guid, run, row) {
		$('.rejudge', row).append($('<input type="button" value="rejudge" />').click(function() {
			$('.status', row).html('rejudging').css('background-color', '');
			omegaup.runRejudge(guid, false, function(data) {
				if (data.status == 'error') {
					self.updateRun(run);
				} else {
					self.updateRunFallback(guid, run);
				}
			});
		}));
	})(run.guid, run, r);

	// Debug-Rejudge
	(function(guid, run, row) {
		$('.rejudge', row).append($('<input type="button" value="debug" />').click(function() {
			$('.status', row).html('rejudging').css('background-color', '');
			omegaup.runRejudge(guid, true, function(data) {
				if (data.status == 'error') {
					self.updateRun(run);
				} else {
					self.updateRunFallback(guid, run);
				}
			});
		}));
	})(run.guid, run, r);

	// Details
	(function(guid, run, row) {
		$('.details', row).append($('<input type="button" value="details" />').click(function() {
			omegaup.runDetails(guid, function(data) {
				if (data.compile_error) {
					$('#run-details .compile_error').html(omegaup.escape(data.compile_error)).show();
				} else {
					$('#run-details .compile_error').html('').hide();
				}
				if (data.logs) {
					$('#run-details .logs').html(omegaup.escape(data.logs)).show();
				} else {
					$('#run-details .logs').html('').hide();
				}
				if (data.source.indexOf('data:') === 0) {
					$('#run-details .source').html('<a href="' + data.source + '" download="data.zip">descarga</a>');
				} else {
					$('#run-details .source').html(omegaup.escape(data.source));
				}
				
				$('#run-details .judged_by').html('');
				if (data.judged_by) {
					$('#run-details .judged_by').html(data.judged_by);
				}
				
				$('#run-details .cases div').remove();
				$('#run-details .cases table').remove();
				$('#run-details .download a').attr('href', '/api/run/download/run_alias/' + guid + '/');
				$('#run-details .download a.details').attr('href', '/api/run/download/run_alias/' + guid + '/complete/true/');

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
									.append('<td>' + g.cases[0].verdict + '</td>')
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
									.append('<td>' + c.verdict + '</td>')
									.append('<td class="score">' + c.score + '</td>')
							);
							if (c.err) addBlock(c.err, 'stderr');
							if (c.out_diff) addBlock(c.out_diff, 'diff');
						}
					}
				}
				$('#run-details .cases').append(groups);
				window.location.hash = 'runs/details';
				$(window).hashchange();
				$('#run-details').show();
				$('#submit').hide();
				$('#clarification').hide();
				$('#overlay').show();
			});
		}));
	})(run.guid, run, r);

	return r;
};

Arena.prototype.displayRun = function(run, r) {
	var self = this;

	$('.id', r).html(run.run_id);
	$('.guid', r).html(self.admin ? run.guid : run.guid.substring(run.guid.length - 5));
	$('.username', r).html(run.username);
	$('.language', r).html(run.language);
	if (run.alias) {
		$('.problem', r).html('<a href="/arena/problem/' + run.alias + '">' + run.alias + '</a>');
	}

	$('.runtime', r).html((parseFloat(run.runtime || "0") / 1000).toFixed(2));
	$('.memory', r).html((run.verdict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
	if (run.contest_score != null) {
		$('.points', r).html(parseFloat(run.contest_score || "0").toFixed(2));
	} else {
		$('.points', r).html('-');
	}
	$('.percentage', r).html((parseFloat(run.score || "0") * 100).toFixed(2) + '%');
	$('.status', r).html(
		run.status == 'ready' ?
		(
		 Arena.verdicts[run.verdict] ?
		 "<abbr title=\"" + Arena.verdicts[run.verdict] + "\">" + run.verdict + "</abbr>" :
		 run.verdict
		) :
		run.status
	);
	if (run.verdict == 'JE')	{
		$('.status', r).css('background-color', '#f00');
	}	else if (run.verdict == 'RTE' || run.verdict == 'CE' || run.verdict == 'RFE') {
		$('.status', r).css('background-color', '#ff9900');
	} else if (run.verdict == 'AC') {
		$('.status', r).css('background-color', '#CCFF66');
	} else {
		$('.status', r).css('background-color', '');
	}
	$('.penalty', r).html(run.submit_delay);
	if (run.time) {
		$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));
	}
};

Arena.prototype.rankingChange = function(data) {
	var self = this;
	self.onRankingChanged(data);
	if (self.scoreboardToken) {
		omegaup.getRankingEventsByToken(self.contestAlias, self.scoreboardToken, self.onRankingEvents.bind(self));
	} else {
		omegaup.getRankingEvents(self.contestAlias, self.onRankingEvents.bind(self));
	}
}

Arena.prototype.onRankingChanged = function(data) {
	var self = this;
	$('#mini-ranking tbody tr.inserted').remove();
	$('#ranking tbody tr.inserted').remove();

	if (self.removeRecentEventClassTimeout) {
		clearTimeout(self.removeRecentEventClassTimeout);
		self.removeRecentEventClassTimeout = null;
	}

	var ranking = data.ranking || [];
	var newRanking = {};
	var order = {};
	var currentRankingState = {};

	for (var i = 0; i < data.problems.length; i++) {
		order[data.problems[i].alias] = i;
	}
	
	// Push data to ranking table
	for (var i = 0; i < ranking.length; i++) {
		var rank = ranking[i];
		newRanking[rank.username] = i;
		
		var r = $('#ranking tbody tr.template')
			.clone()
			.removeClass('template')
			.addClass('inserted')
			.addClass('rank-new');
		
		var username = rank.username +
			((rank.name == rank.username) ? '' : (' (' + omegaup.escape(rank.name) + ')'));
		$('.user', r).html(username);

		currentRankingState[username] = {
			place: rank.place,
			accepted: {}
		};

		// Update problem scores.
		var totalRuns = 0;
		for (var alias in order) {
			if (!order.hasOwnProperty(alias)) continue;
			var problem = rank.problems[order[alias]];
			totalRuns += problem.runs;
	
			var pointsCell = $('.prob_' + alias + '_points', r);
			if (problem.runs == 0) {
				pointsCell.html('-');
			} else if (self.currentContest.show_penalty) {
				pointsCell.html(
					'<div class="points">' + (problem.points ? '+' + problem.points : '0') + '</div>\n' +
					'<div class="penalty">' + problem.penalty + ' (' + problem.runs  + ')</div>'
				);
			} else {
				pointsCell.html(
					'<div class="points">' + (problem.points ? '+' + problem.points : '0') + '</div>\n' +
					'<div class="penalty">(' + problem.runs  + ')</div>'
				);
			}
			pointsCell
				.removeClass('pending accepted wrong');
			if (problem.runs > 0) {
				if (problem.percent == 100) {
					currentRankingState[username].accepted[problem.alias] = true;
					pointsCell.addClass('accepted');
					if (this.prevRankingState) {
						if (!this.prevRankingState[username] ||
								!this.prevRankingState[username].accepted[problem.alias]) {
							pointsCell.addClass('recent-event');
						}
					}
				} else if (problem.pending) {
					pointsCell.addClass('pending');
				} else if (problem.percent == 0) {
					pointsCell.addClass('wrong');
				}
			}

			if (self.problems[alias]) {
				if (rank.username == omegaup.username) {
					$('#problems .problem_' + alias + ' .solved')
						.html("(" + problem.points + " / " + self.problems[alias].points + ")");
				}
			}
		}

		if (self.currentContest.show_penalty) {
			$('td.points', r).html(
				'<div class="points">' + rank.total.points + '</div>' +
				'<div class="penalty">' + rank.total.penalty + ' (' + totalRuns + ')</div>'
			);
		} else {
			$('td.points', r).html(
				'<div class="points">' + rank.total.points + '</div>' +
				'<div class="penalty">(' + totalRuns + ')</div>'
			);
		}
		$('.position', r)
			.html(rank.place)
			.removeClass('recent-event');
		if (this.prevRankingState) {
			if (!this.prevRankingState[username] ||
					this.prevRankingState[username].place > rank.place) {
				$('.position', r).addClass('recent-event');
			}
		}

		$('#ranking tbody').append(r);

		// update miniranking
		if (i < 10) {
			r = $('#mini-ranking tbody tr.template')
				.clone()
				.removeClass('template')
				.addClass('inserted');

			$('.position', r).html(rank.place);
			$('.user', r).html('<span title="' + username + '">' + rank.username + '</span>');
			$('.points', r).html(rank.total.points);
			$('.penalty', r).html(rank.total.penalty);

			$('#mini-ranking tbody').append(r);
		}
	}

	if (data.time) {
		$('#ranking .footer').html(omegaup.time(data.time));
	}

	this.currentRanking = newRanking;
	this.prevRankingState = currentRankingState;
	self.removeRecentEventClassTimeout = setTimeout(function() {
		$('.recent-event').removeClass('recent-event');
	}, 30000);
};

Arena.prototype.onRankingEvents = function(data) {
	var dataInSeries = {};
	var navigatorData = [[this.startTime.getTime(), 0]];
	var series = [];
	var usernames = {};
	this.currentEvents = data;

	// group points by person
	for (var i = 0, l = data.events.length; i < l; i++) {
		var curr = data.events[i];
		
		// limit chart to top n users
		if (this.currentRanking[curr.username] > Arena.scoreboardColors.length - 1) continue;
		
		if (!dataInSeries[curr.name]) {
				dataInSeries[curr.name] = [[this.startTime.getTime(), 0]];
				usernames[curr.name] = curr.username;
		}
		dataInSeries[curr.name].push([
				this.startTime.getTime() + curr.delta*60*1000,
				curr.total.points
		]);
		
		// check if to add to navigator
		if (curr.total.points > navigatorData[navigatorData.length-1][1]) {
				navigatorData.push([
					this.startTime.getTime() + curr.delta*60*1000,
					curr.total.points
				]);
		}
	}
	
	// convert datas to series
	for (var i in dataInSeries) {
		if (dataInSeries.hasOwnProperty(i)) {
				dataInSeries[i].push([
					Math.min(this.finishTime.getTime(), Date.now()),
					dataInSeries[i][dataInSeries[i].length - 1][1]
				]);
				series.push({
					name: i,
					rank: this.currentRanking[usernames[i]],
					data: dataInSeries[i],
					step: true
				});
		}
	}
	
	series.sort(function (a, b) {
		return a.rank - b.rank;
	});
	
	navigatorData.push([
			Math.min(this.finishTime.getTime(), Date.now()),
			navigatorData[navigatorData.length - 1][1]
	]);
	this.createChart(series, navigatorData);
};

Arena.prototype.createChart = function(series, navigatorSeries) {
	if (series.length == 0) return;
	
	Highcharts.setOptions({
		colors: Arena.scoreboardColors
	});

	window.chart = new Highcharts.StockChart({
		chart: {
			renderTo: 'ranking-chart',
			height: 300,
			spacingTop: 20
		},

		xAxis: {
			ordinal: false,
			min: this.startTime.getTime(),
			max: Math.min(this.finishTime.getTime(), Date.now())
		},

		yAxis: {
			showLastLabel: true,
			showFirstLabel: false,
			min: 0,
			max: (function(problems) {
				var total = 0;
				for (var prob in problems) {
					if (!problems.hasOwnProperty(prob)) continue;
					total += parseInt(problems[prob].points, 10);
				}
				return total;
			})(this.problems)
		},
		
		plotOptions: {
			series: {
				animation: false,
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
			'background-color': (r < Arena.scoreboardColors.length) ?
				Arena.scoreboardColors[r] :
				'transparent'
		});
	}
};

Arena.prototype.flashTitle = function(reset) {
	if (document.title.indexOf("!") === 0) {
		document.title = document.title.substring(2);
	} else if (!reset) {
		document.title = "! " + document.title;
	}
};

Arena.prototype.notify = function(title, message, element, id) {
	var self = this;

	if (self.currentNotifications.hasOwnProperty(id)) {
		return;
	}

	if (self.currentNotifications.timer == null) {
		self.currentNotifications.timer = setInterval(self.flashTitle, 1000);
	}

	self.currentNotifications.count++;

	var gid = $.gritter.add({
		title: title,
		text: message,
		sticky: true,
		before_close: function() {
			if (element) {
				window.focus();
				element.scrollIntoView(true);
			}

			self.currentNotifications.count--;
			if (self.currentNotifications.count == 0) {
				clearInterval(self.currentNotifications.timer);
				self.currentNotifications.timer = null;
				self.flashTitle(true);
			}
		}
	});

	self.currentNotifications[id] = gid;

	var audio = document.getElementById('notification_audio');
	if (audio != null) audio.play();
};

Arena.prototype.updateClarification = function(clarification) {
	var self = this;
	var r = null;
	if (self.clarifications[clarification.clarification_id]) {
		r = self.clarifications[clarification.clarification_id];
	} else {
		r = $('.clarifications tbody tr.template')
			.clone()
			.removeClass('template')
			.addClass('inserted');

		if (self.admin) {
			(function(id, answer, answerNode) {
				if (clarification.public == 1) {
					$('input[type="checkbox"]', answer).attr('checked', 'checked');
				}
				answer.submit(function () {
					omegaup.updateClarification(
						id,
						$('textarea', answer).val(),
						$('input[type="checkbox"]', answer)[0].checked,
						function() {
							$('pre', answerNode).html($('textarea', answer).val());
							$('textarea', answer).val('');
						}
					);
					return false;
				});

				answerNode.append(answer);
			})(clarification.clarification_id, $('<form><input type="checkbox" /><textarea></textarea><input type="submit" /></form>'), $('.answer', r));
		}
	}

	$('.contest', r).html(clarification.contest_alias);
	$('.problem', r).html(clarification.problem_alias);
	if (self.admin) $('.author', r).html(clarification.author);
	$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', clarification.time.getTime()));
	$('.message', r).html(omegaup.escape(clarification.message));
	$('.answer pre', r).html(omegaup.escape(clarification.answer));
	if (clarification.answer) {
		self.answeredClarifications++;
	}

	if (self.admin != !!clarification.answer) {
		self.notify(
			(clarification.author ? clarification.author + " - " : '') + clarification.problem_alias,
			omegaup.escape(clarification.message) +
				(clarification.answer ? ('<hr/>' + omegaup.escape(clarification.answer)) : ''),
			r[0],
			'clarification-' + clarification.clarification_id
		);
	}

	if (!self.clarifications[clarification.clarification_id]) {
		$('.clarifications tbody').prepend(r);
		self.clarifications[clarification.clarification_id] = r;
	}
};

Arena.prototype.clarificationsChange = function(data) {
	var self = this;
	$('.clarifications tr.inserted').remove();
	if (data.clarifications.length > 0 && data.clarifications.length < self.clarificationsRowcount) {
		$('#clarifications-count').html("(" + data.clarifications.length + ")");
	} else if (data.clarifications.length >= self.clarificationsRowcount) {
		$('#clarifications-count').html("("+ data.clarifications.length + "+)");
	}

	var previouslyAnswered = self.answeredClarifications;
	self.answeredClarifications = 0;
	self.clarifications = {};

	for (var i = data.clarifications.length - 1; i >= 0; i--) {
		self.updateClarification(data.clarifications[i]);
	}

	if (self.answeredClarifications > previouslyAnswered && self.activeTab != 'clarifications') {
		$('#clarifications-count').css("font-weight", "bold");
	}
};

Arena.prototype.onHashChanged = function() {
	var self = this;
	var tabChanged = false;
	var tabs = ['summary', 'problems', 'ranking', 'clarifications', 'runs'];

	for (var i = 0; i < tabs.length; i++) {
		if (window.location.hash.indexOf('#' + tabs[i]) == 0) {
			tabChanged = self.activeTab != tabs[i];
			self.activeTab = tabs[i];

			break;
		}
	}

	var problem = /#problems\/([^\/]+)(\/new-run)?/.exec(window.location.hash);

	if (problem && self.problems[problem[1]]) {
		var newRun = problem[2];
		self.currentProblem = problem = self.problems[problem[1]];

		$('#problem-list .active').removeClass('active');
		$('#problem-list .problem_' + problem.alias).addClass('active');

		function update(problem) {
			$('#summary').hide();
			$('#problem').show();
			$('#problem > .title').html(problem.letter + '. ' + omegaup.escape(problem.title));
			$('#problem .data .points').html(problem.points);
			$('#problem .memory_limit').html(problem.memory_limit / 1024 + "MB");
			$('#problem .time_limit').html(problem.time_limit / 1000 + "s");
			$('#problem .overall_wall_time_limit').html(problem.overall_wall_time_limit / 1000 + "s");
			$('#problem .statement').html(problem.problem_statement);
			var karel_langs = ['kp', 'kj'];
			var language_array = problem.languages.split(',');
			if (karel_langs.every(function(x) { return language_array.indexOf(x) != -1})) {
				var original_href = $('#problem .karel-js-link a').attr('href');
				var hash_index = original_href.indexOf('#');
				if (hash_index != -1) {
					original_href = original_href.substring(0, hash_index);
				}
				if (problem.sample_input) {
					$('#problem .karel-js-link a').attr('href', original_href + '#mundo:'
							+ encodeURIComponent(problem.sample_input));
				} else {
					$('#problem .karel-js-link a').attr('href', original_href);
				}
				$('#problem .karel-js-link').removeClass('hide');
			} else {
				$('#problem .karel-js-link').addClass('hide');
			}
			$('#problem .source span').html(omegaup.escape(problem.source));
			$('#problem .runs tfoot td a').attr('href', '#problems/' + problem.alias + '/new-run');
			self.installLibinteractiveHooks();

			$('#problem .run-list .added').remove();

			$('#lang-select option').each(function(index, item) {
				if (language_array.indexOf($(item).val()) >= 0) {
					$(item).show();
				} else {
					$(item).hide();
				}
			});

			function updateProblemRuns(runs, score_column, multiplier) {
				for (var idx in runs) {
					if (!runs.hasOwnProperty(idx)) continue;
					var run = runs[idx];

					var r = $('#problem .run-list .template')
						.clone()
						.removeClass('template')
						.addClass('added')
						.addClass('run_' + run.guid);
					self.displayRun(run, r);
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
								$('#overlay form').hide();
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

			if (self.practice || self.onlyProblem) {
				omegaup.getProblemRuns(problem.alias, {}, function (data) {
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
			omegaup.getProblem(self.contestAlias, problem.alias, function (problem_ext) {
				problem.source = problem_ext.source;
				problem.problem_statement = problem_ext.problem_statement;
				problem.sample_input = problem_ext.sample_input;
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
	} else if (self.activeTab == 'problems') {
		$('#problem').hide();
		$('#summary').show();
		$('#problem-list .active').removeClass('active');
		$('#problem-list .summary').addClass('active');
	} else if (self.activeTab == 'clarifications') {
		if (window.location.hash == '#clarifications/new') {
			$('#overlay form').hide();
			$('#overlay, #clarification').show();
		}
	} else if (window.location.hash == '#runs/details') {
		$('#overlay form').hide();
		$('#run-details').show();
		$('#overlay').show();
	}

	if (tabChanged) {
		$('.tabs a.active').removeClass('active');
		$('.tabs a[href="#' + self.activeTab + '"]').addClass('active');
		$('.tab').hide();
		$('#' + self.activeTab).show();
		
		if (self.activeTab == 'ranking') {
			if (self.currentEvents) {
				self.onRankingEvents(self.currentEvents);
			}
		} else if (self.activeTab == 'clarifications') {
			$('#clarifications-count').css("font-weight", "normal");
		}
	}
};

Arena.formatDelta = function(delta) {
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
};
