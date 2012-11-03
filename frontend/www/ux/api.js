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
		'/api/authenticated/',
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
		'/api/time/',
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
		'/api/login/',
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
		'/api/contest/list/',
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
		'/api/contest/' + alias + '/',
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
		'/api/contest/' + contestAlias + '/problem/' + problemAlias + '/',
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

OmegaUp.prototype.getContestRuns = function(contestAlias, options, callback) {
	// Opciones validas son offset, rowcount, veredict, status
	// offset y rowcount son para paginar
	// status y veredict son para filtrar
	// status puede ser: 'new','waiting','compiling','running','ready'
	// veredict puede ser: "AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE"

	var self = this;

	$.post(
		'/api/contest/' + contestAlias + '/run/list/',
		options,
		function (data) {
			for (var i = 0; i < data.runs.length; i++) {
				data.runs[i].time = self.time(data.runs[i].time * 1000);
			}
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getProblemRuns = function(problemAlias, callback) {
	var self = this;

	$.post(
		'/api/problem/' + problemAlias + '/run/list/',
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
		'/api/run/new/',
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
		'/api/run/' + guid + '/',
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
		'/api/run/' + guid + '/details/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runSource = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/' + guid + '/source/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runRejudge = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/' + guid + '/rejudge/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.rejudgeProblem = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/' + problemAlias + '/rejudge/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRanking = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/' + contestAlias + '/ranking/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRankingEvents = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/' + contestAlias + '/ranking/events/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getClarifications = function(contestAlias, offset, rowcount, callback) {
	var self = this;

	$.post(
		'/api/contest/' + contestAlias + '/clarification/list/',
		{offset: offset, rowcount: rowcount},
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.newClarification = function(contestAlias, problemAlias, message, callback) {
	var self = this;

	$.post(
		'/api/clarification/new/',
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
		'/api/clarification/' + clarificationId + '/update/',
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

OmegaUp.prototype.UserEdit = function(username, name, email, birthDate, school, password, oldPassword, callback) {
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
		'/api/user/edit/',
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
