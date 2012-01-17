function OmegaUp() {
	var self = this;
	this.username = null;

	this.deltaTime = 0;
	this.authenticated(function(data) {
		if (data.status == 'ok') {
			self.syncTime();
			self.username = data.username;
		} else {
			window.location = data.login_url;
		}
	});
}

OmegaUp.prototype.authenticated = function(callback) {
	$.get(
		'/arena/authenticated/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.syncTime = function() {
	var self = this;

	var t0 = new Date().getTime();
	$.get(
		'/arena/time/',
		function (data) {
			self.deltaTime = data.time * 1000 - t0;
		},
		'json'
	);
};

OmegaUp.prototype.time = function(date) {
	var newDate = this.deltaTime;
	if (date) {
		newDate += new Date(date).getTime();
	} else {
		newDate += new Date().getTime();
	}
	return new Date(newDate);
};

OmegaUp.prototype.login = function(username, password, callback) {
	$.post(
		'/arena/login/',
		{ username: username, password: password },
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getContests = function(callback) {
	var self = this;

	$.get(
		'/arena/contests/',
		function (data) {
			for (var idx in data.contests) {
				var contest = data.contests[idx];
				contest.start_time = self.time(contest.start_time);
				contest.finish_time = self.time(contest.finish_time);
			}
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getContest = function(alias, callback) {
	var self = this;

	$.get(
		'/arena/contests/' + alias + '/',
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time);
				contest.finish_time = self.time(contest.finish_time);
			}
			callback(contest);
		},
		'json'
	);
};

OmegaUp.prototype.getProblem = function(contestAlias, problemAlias, callback) {
	var self = this;

	$.post(
		'/arena/contests/' + contestAlias + '/problem/' + problemAlias + '/',
		{lang:"es"},
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time);
				contest.finish_time = self.time(contest.finish_time);
			}
			callback(contest);
		},
		'json'
	);
};

OmegaUp.prototype.submit = function(contestAlias, problemAlias, language, code, callback) {
	var self = this;

	$.post(
		'/arena/runs/new',
		{
			contest_alias: contestAlias,
			problem_alias: problemAlias,
			language: language,
			source: code
		},
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runStatus = function(guid, callback) {
	var self = this;

	$.get(
		'/arena/runs/' + guid + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRanking = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/arena/contests/' + contestAlias + '/ranking/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRankingEvents = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/arena/contests/' + contestAlias + '/ranking/events/',
		function (data) {
			callback(data);
		},
		'json'
	);
};
