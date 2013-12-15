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

	// True if this is an admin session. Does not give you extra permissions, sorry :P
	this.admin = false;

	// True if this is a practice session.
	this.practice = false;

	this.currentRanking = {};

	this.problems = {};
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
	// temporarily disable websockets.
	return false;

	var uri;
	if (window.location.protocol === "https:") {
		uri = "wss:";
	} else {
		uri = "ws:";
	}
	uri += "//" + window.location.host + "/api/contest/events/" + currentContest.alias + "/";

	try {
		socket = new WebSocket(uri, "omegaup.com.events");
		socket.onclose = function(e) { socket = null; console.log(e); };
		socket.onmessage = function(message) {
			var data = JSON.parse(message.data);

			if (data.message == "/run/status/") {
				data.run.time = new Date(data.run.time * 1000);
				self.runUpdated(data.run);
			}
		};
		socket.onerror = function(e) { console.log(e); };
	} catch (e) {
		console.log(e);
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

Arena.prototype.initProblems = function(problems) {
	for (var i = 0; i < problems.length; i++) {
		var alias = problems[i].alias;
		this.problems[alias] = problems[i];

		$('<th colspan="2"><a href="#problems/' + alias + '" title="' + alias + '">' + problems[i].letter + '</a></th>').insertBefore('#ranking thead th.total');
		$('<td class="prob_' + alias + '_points"></td>').insertBefore('#ranking tbody .template td.points');
		$('<td class="prob_' + alias + '_penalty"></td>').insertBefore('#ranking tbody .template td.points');			
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

Arena.prototype.onRankingChanged = function(data) {
	$('#mini-ranking tbody tr.inserted').remove();
	$('#ranking tbody tr.inserted').remove();

	var ranking = data.ranking;
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
			$('.prob_' + alias + '_penalty', r).html(rank.problems[alias].penalty);
			if (this.problems[alias]) {
				if (rank.username == omegaup.username) {
					$('#problems .problem_' + alias + ' .solved')
						.html("(" + rank.problems[alias].points + " / " + this.problems[alias].points + ")");
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
