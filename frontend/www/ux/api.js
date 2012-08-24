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
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
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
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.submission_deadline = self.time(contest.submission_deadline * 1000);
			}
			callback(contest);
		},
		'json'
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});

};

OmegaUp.prototype.getProblem = function(contestAlias, problemAlias, callback) {
	var self = this;

	$.post(
		'/arena/contests/' + contestAlias + '/problem/' + problemAlias + '/',
		{lang:"es"},
		function (problem) {
			if (problem.runs) {
				for (var i = 0; i < problem.runs.length; i++) {
					problem.runs[i].time = self.time(problem.runs[i].time * 1000);
				}
			}
			callback(problem);
		},
		'json'
	);
};

OmegaUp.prototype.getContestRuns = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/arena/contests/' + contestAlias + '/runs/',
		function (data) {
			for (var i = 0; i < data.runs.length; i++) {
				data.runs[i].time = self.time(data.runs[i].time * 1000);
			}
			callback(data);
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
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.runStatus = function(guid, callback) {
	var self = this;

	$.get(
		'/arena/runs/' + guid + '/',
		function (data) {
			data.time = self.time(data.time * 1000);
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runDetails = function(guid, callback) {
	var self = this;

	$.get(
		'/arena/runs/' + guid + '/details/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runRejudge = function(guid, callback) {
	var self = this;

	$.get(
		'/arena/runs/' + guid + '/rejudge/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.rejudgeProblem = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/arena/problems/' + problemAlias + '/rejudge/',
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

OmegaUp.prototype.getClarifications = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/arena/contests/' + contestAlias + '/clarifications/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.newClarification = function(contestAlias, problemAlias, message, callback) {
	var self = this;

	$.post(
		'/arena/clarifications/new',
		{
			contest_alias: contestAlias,
			problem_alias: problemAlias,
			message: message
		},
		function (data) {
			callback(data);
		},
		'json'
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'ok', 'error':undefined});
		}
	});
};

OmegaUp.prototype.updateClarification = function(clarificationId, answer, public, callback) {
	var self = this;

	$.post(
		'/arena/clarifications/update/' + clarificationId,
		{
			answer: answer,
			public: public ? 1 : 0
		},
		function (data) {
			callback(data);
		},
		'json'
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};



OmegaUp.prototype.UserEdit = function( username, name, email, birthDate, school, password, oldPassword, callback ){
	var self = this,
		toSend = {};

	if(username !== null) toSend.username = username;
	if(name !== null) toSend.name = name;
	if(email !== null) toSend.email = email;
	if(birthDate !== null) toSend.birthDate = birthDate;		
	if(school !== null) toSend.school = school;
	if(password !== null) toSend.password = password;
	if(oldPassword !== null) toSend.oldPassword = oldPassword;


	$.post(
		'/arena/user/edit/',
		toSend,
		function (data) {
			callback(data);
		},
		'json'
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};
