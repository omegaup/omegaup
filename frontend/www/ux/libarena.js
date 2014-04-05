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

	// Whether the current contest is in practice mode.
	this.practice = window.location.pathname.indexOf('/practice/') !== -1;

	// Whether this is a full contest or only a problem.
	this.onlyProblem = window.location.pathname.indexOf('/problem/') !== -1;

	// The alias of the contest.
	this.contestAlias = /\/arena\/([^\/]+)\/?/.exec(window.location.pathname)[1];

	// If websockets are enabled.
	this.enableSockets = window.location.search.indexOf('ws=on') !== -1;
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
		self.socket.onmessage = function(message) {
			console.log(message);
			var data = JSON.parse(message.data);

			if (data.message == "/run/update/") {
				data.run.time = new Date(data.run.time * 1000);
				self.updateRun(data.run);
			} else if (data.message == "/clarification/update/") {
				data.clarification.time = new Date(data.clarification.time * 1000);
			}
		};
		self.socket.onopen = function() {
			self.socket_keepalive = setInterval((function(socket) {
				return function() {
					socket.send('"ping"');
				};
			})(self.socket), 30000);
		};
		self.socket.onclose = function(e) {
			self.socket = null;
			clearInterval(self.socket_keepalive);
			console.error(e);
		};
		self.socket.onerror = function(e) {
			self.socket = null;
			clearInterval(self.socket_keepalive);
			console.error(e);
		};
	} catch (e) {
		self.socket = null;
		console.error(e);
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
	problems = contest.problems;
	for (var i = 0; i < problems.length; i++) {
		var alias = problems[i].alias;
		problems[i].letter = String.fromCharCode('A'.charCodeAt(0) + i);
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
	var r = '#run_' + run.guid;

	if (run.status == 'ready') {
		$(r + ' .runtime').html((parseFloat(run.runtime) / 1000).toFixed(2));
		$(r + ' .memory').html((run.veredict == "MLE" ? ">" : "") + (parseFloat(run.memory) / (1024 * 1024)).toFixed(2));
		$(r + ' .points').html(parseFloat(run.contest_score).toFixed(2));
		$(r + ' .penalty').html(run.submit_delay);
	}
	$(r + ' .status').html(run.status == 'ready' ? (Arena.veredicts[run.veredict] ? "<abbr title=\"" + Arena.veredicts[run.veredict] + "\">" + run.veredict + "</abbr>" : run.veredict) : run.status);
	$(r + ' .time').html(Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', run.time.getTime()));

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
			delete self.currentNotifications[id];

			self.currentNotifications.count--;
			if (self.currentNotifications.count == 0) {
				clearInterval(self.currentNotifications.timer);
				self.currentNotifications.timer = null;
				self.flashTitle(true);
			}
		}
	});

	self.currentNotifications[id] = gid;

	document.getElementById('notification_audio').play();
};
