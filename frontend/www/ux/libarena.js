function Arena() {
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

	// The last known scoreboard event stream.
	this.currentEvents = null;

	// Currently opened notifications.
	this.currentNotifications = {count: 0, timer: null};

	// Currently opened problem.
	this.currentProblem = null;

	// Whether the current contest is in practice mode.
	this.practice = window.location.pathname.indexOf('/practice/') !== -1;

	// Whether this is a full contest or only a problem.
	this.onlyProblem = window.location.pathname.indexOf('/problem/') !== -1;

	// The alias of the contest.
	this.contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	// If websockets are enabled.
	this.enableSockets = window.location.search.indexOf('ws=on') !== -1;

	// If we have admin powers in this contest.
	this.admin = false;
	this.answeredClarifications = 0;
	this.clarificationsOffset = 0;
	this.clarificationsRowcount = 20;
	this.activeTab = 'problems';
	this.clarifications = {};
	this.submissionGap = 0;
};

Arena.veredicts = {
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

Arena.prototype.connectSocket = function() {
	var self = this;
	if (!self.enableSockets || self.contestAlias == 'admin') {
		return false;
	}

	var uri;
	if (window.location.protocol === "https:") {
		uri = "wss:";
	} else {
		uri = "ws:";
	}
	uri += "//" + window.location.host + "/api/contest/events/" + self.contestAlias + "/";

	try {
		self.socket = new WebSocket(uri, "com.omegaup.events");
		$('#title .socket-status').html('&bull;');
		self.socket.onmessage = function(message) {
			console.log(message);
			var data = JSON.parse(message.data);

			if (data.message == "/run/update/") {
				data.run.time = new Date(data.run.time * 1000);
				self.updateRun(data.run);
			} else if (data.message == "/clarification/update/") {
				data.clarification.time = new Date(data.clarification.time * 1000);
				self.updateClarification(data.clarification);
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
			console.error(e);
		};
		self.socket.onerror = function(e) {
			$('#title .socket-status').html('&cross;').css('color', '#800');
			self.socket = null;
			clearInterval(self.socket_keepalive);
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

	self.rankingInterval = setInterval(function() {
		omegaup.getRanking(self.contestAlias, self.rankingChange.bind(self));
	}, 5 * 60 * 1000);

	if (!self.socket) {
		self.clarificationInterval = setInterval(function() {
			self.clarificationsOffset = 0; // Return pagination to start on refresh
			omegaup.getClarifications(
				self.contestAlias,
				self.clarificationsOffset,
				self.clarificationsRowcount,
				self.clarificationsChange.bind(self));
		}, 5 * 60 * 1000);
	}
};

Arena.prototype.initClock = function(start, finish, deadline) {
	this.startTime = start;
	this.finishTime = finish;
	if (deadline) this.submissionDeadline = deadline;
	if (!this.clockInterval) {
		this.updateClock();
		this.clockInterval = setInterval(this.updateClock.bind(this), 1000);
	}
};

Arena.prototype.initProblems = function(contest) {
	var self = this;
	self.admin = contest.admin;
	problems = contest.problems;
	for (var i = 0; i < problems.length; i++) {
		var alias = problems[i].alias;
		problems[i].runs = problems[i].runs || [];
		self.problems[alias] = problems[i];

		$('<th colspan="2"><a href="#problems/' + alias + '" title="' + alias + '">' +
				problems[i].letter + '</a></th>').insertBefore('#ranking thead th.total');
		$('<td class="prob_' + alias + '_points"></td>')
			.insertBefore('#ranking tbody .template td.points');
		if (contest.show_penalty) {
			$('<td class="prob_' + alias + '_penalty"></td>')
				.insertBefore('#ranking tbody .template td.points');
		}
	}
	if (!contest.show_penalty) {
		$('#ranking thead th').attr('colspan', '');
		$('#ranking tbody .template .penalty').remove();
	}
};

Arena.prototype.updateClock = function() {
	var countdownTime = this.submissionDeadline || this.finishTime;
	if (this.startTime === null || countdownTime === null) {
		return;
	}

	var date = new Date().getTime();
	var clock = "";

	if (date < this.startTime.getTime()) {
		clock = "-" + this.formatDelta(this.startTime.getTime() - (date + omegaup.deltaTime));
	} else if (date > countdownTime.getTime()) {
		clock = "00:00:00";
		clearInterval(this.clockInterval);
		this.clockInterval = null;
	} else {
		clock = this.formatDelta(countdownTime.getTime() - (date + omegaup.deltaTime));
	}

	$('#title .clock').html(clock);
};

Arena.prototype.formatDelta = function(delta) {
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
	var r = $('.run_' + run.guid);

	if (run.status == 'ready') {
		$('.runtime', r).html((parseFloat(run.runtime) / 1000).toFixed(2));
		$('.memory', r).html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
		$('.points', r).html(parseFloat(run.contest_score).toFixed(2));
		$('.penalty', r).html(run.submit_delay);
	}
	$('.status', r).html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
	$('.time', r).html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));

	if (run.status == 'ready') {
		if (!self.practice && !self.onlyProblem && self.contestAlias != 'admin') {
			omegaup.getRanking(self.contestAlias, self.rankingChange.bind(self));
		}
	} else if (self.socket == null) {
		self.updateRunFallback(run.guid, run);
	}
};

Arena.prototype.rankingChange = function(data) {
	var self = this;
	self.onRankingChanged(data);
	omegaup.getRankingEvents(self.contestAlias, self.onRankingEvents.bind(self));
}

Arena.prototype.onRankingChanged = function(data) {
	var self = this;
	$('#mini-ranking tbody tr.inserted').remove();
	$('#ranking tbody tr.inserted').remove();

	var ranking = data.ranking || [];
	var newRanking = {};		
	
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

		// Update problem scores.
		for (var alias in rank.problems) {
			if (!rank.problems.hasOwnProperty(alias)) continue;
			
			$('.prob_' + alias + '_points', r).html(rank.problems[alias].points);
			$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty + " (" + rank.problems[alias].runs  + ")");
			if (self.problems[alias]) {
				if (rank.username == omegaup.username) {
					$('#problems .problem_' + alias + ' .solved')
						.html("(" + rank.problems[alias].points + " / " + self.problems[alias].points + ")");
				}
			}
		}

		$('.points', r).html(rank.total.points);
		$('.penalty', r).html(rank.total.penalty);
		$('.position', r).html(rank.place);

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

	this.currentRanking = newRanking;
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
						$('input[type="checkbox"]', answer).attr('checked'),
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

					var r = $('#problem .run-list .template')
						.clone()
						.removeClass('template')
						.addClass('added')
						.addClass('run_' + run.guid);
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

			if (self.practice || self.onlyProblem) {
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
			omegaup.getProblem(self.contestAlias, problem.alias, function (problem_ext) {
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
	} else if (window.location.hash == '#run/details') {
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
