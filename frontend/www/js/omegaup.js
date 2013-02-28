var DEBUG = true;


function OmegaUp() {
	var self = this;
	this.username = null;

	this.deltaTime = 0;
	this.authenticated(function(data) {
		if (data.status == 'ok') {
			self.syncTime();
			self.username = data.username;
		} else {
			//window.location = data.login_url;
		}
	});
}

OmegaUp.UI = {
	Error : function ( reason ){
		$.msgBox({
		    title: "Error",
		    content: reason,
		    type: "error",
		    showButtons: false,
		    opacity: 0.9,
		    autoClose:false
		});
	}
}

$(document).ajaxError(function(e, xhr, settings, exception) {
	var errorToUser = "Unknown error.";
	try{
		var response = jQuery.parseJSON(xhr.responseText);
		errorToUser = response.error;
	}catch(e){
		
	}

	OmegaUp.UI.Error( errorToUser );
});

OmegaUp.prototype.createUser = function(s_Email, s_Username, s_PlainPassword, callback) {
	console.log("Creating user");
	$.post(
		'/api/user/create/email/' + s_Email + "/username" + s_Username + "/password/" + s_PlainPassword ,
		{ email: s_Email, username: s_Username, password : s_PlainPassword },
		function (data) {
			if( data.status !== undefined && data.status == "error") {
				OmegaUp.UI.Error( data.error );
			}else{
				if(callback !== undefined){ callback( data ) }
			}
		},
		'json'
	);
};

OmegaUp.prototype.createContest = function(
					title,
					description,
					start_time,
					finish_time,
					window_length,
					alias,
					points_decay_factor,
					partial_score, 
					submissions_gap,
					feedback, 
					penalty,
					public,
					scoreboard, 
					penalty_time_start, 
					penalty_calc_policy, 
					callback
				) {
	console.log("Creating contest", penalty_time_start);
	$.post(
		'/api/contest/create/' ,
		{
			title				: title,
			description			: description,
			start_time			: start_time,
			finish_time			: finish_time,
			window_length		: window_length,
			public				: public,
			alias				: alias,
			points_decay_factor	: points_decay_factor,
			partial_score		: partial_score ,
			submissions_gap		: submissions_gap,
			feedback			: feedback, 
			penalty				: penalty , 
			public				: public,
			scoreboard			: scoreboard, 
			penalty_time_start	: penalty_time_start, 
			penalty_calc_policy	: penalty_calc_policy 
		},
		function (data) {
			if( data.status !== undefined && data.status == "error") {
				OmegaUp.UI.Error( data.error );
			}else{
				if(callback !== undefined){ callback( data ) }
			}
		},
		'json'
	);
};

OmegaUp.prototype.authenticated = function(callback) {
	$.get(
		'/api/session/currentsession',
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
		'/api/controllername/time/',
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
		'/api/session/login/',
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
		'/api/contest/list',
		function (data) {
			/*
			for (var idx in data.contests) {
				var contest = data.contests[idx];
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
			}
			*/
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getContest = function(alias, callback) {
	var self = this;

	$.get(
		'/api/contest/details/alias/' + alias + '/',
		function (contest) {
			/*
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.submission_deadline = self.time(contest.submission_deadline * 1000);
			}
			*/
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
		'/api/controllername/contests/' + contestAlias + '/problem/' + problemAlias + '/',
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

OmegaUp.prototype.getContestRuns = function(contestAlias, offset, rowcount, callback) {
	var self = this;

	$.post(
		'/api/controllername/contests/' + contestAlias + '/runs/',
		{offset: offset, rowcount: rowcount},
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
		'/api/controllername/runs/new',
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
		'/api/controllername/runs/' + guid + '/',
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
		'/api/controllername/runs/' + guid + '/details/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runSource = function(guid, callback) {
	var self = this;

	$.get(
		'/api/controllername/runs/' + guid + '/source/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runRejudge = function(guid, callback) {
	var self = this;

	$.get(
		'/api/controllername/runs/' + guid + '/rejudge/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.rejudgeProblem = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/controllername/problems/' + problemAlias + '/rejudge/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRanking = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/controllername/contests/' + contestAlias + '/ranking/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRankingEvents = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/controllername/contests/' + contestAlias + '/ranking/events/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getClarifications = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/controllername/contests/' + contestAlias + '/clarifications/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.newClarification = function(contestAlias, problemAlias, message, callback) {
	var self = this;

	$.post(
		'/api/controllername/clarifications/new',
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
		'/api/controllername/clarifications/update/' + clarificationId,
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
		'/api/controllername/user/edit/',
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

omega = new OmegaUp();
