var DEBUG = true;

function OmegaUp() {
	var self = this;
	this.username = null;

	this.deltaTime = 0;
	this.authenticated(function(data) {
		if (data.valid) {
			self.loggedIn = true;
			self.syncTime();
			self.username = data.username;
			self.email = data.email;
			self.email_md5 = data.email_md5;
		} else {
			self.loggedIn = false;
			self.login_url = data.login_url;
		}
	});
}

OmegaUp.UI = {
	Error : function ( reason ){
		window.scroll(0,0);
		$("#OmegaupUIError").html(reason).fadeIn();
	}
}

$(document).ajaxError(function(e, xhr, settings, exception) {
	var errorToUser = "Unknown error.";
	try{
		var response = jQuery.parseJSON(xhr.responseText);
		errorToUser = response.error;
	}catch(e){
		
	}

	if (settings.url != "/api/grader/status/") {
		OmegaUp.UI.Error( errorToUser );
	}
});

OmegaUp.prototype.createUser = function(s_Email, s_Username, s_PlainPassword, callback) {
	$.post(
		'/api/user/create/',
		{ email: s_Email, username: s_Username, password : s_PlainPassword },
		function (data) {
			if( data.status !== undefined && data.status == "error") {
				OmegaUp.UI.Error( data.error );
			} else {
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
					submissions_gap,
					feedback, 
					penalty,
					public,
					scoreboard, 
					penalty_time_start, 					
					show_scoreboard_after,
					callback
				) {
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
			submissions_gap		: submissions_gap,
			feedback			: feedback, 
			penalty				: penalty , 			
			scoreboard			: scoreboard, 
			penalty_time_start	: penalty_time_start, 
			show_scoreboard_after	: show_scoreboard_after 
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

OmegaUp.prototype.updateContest = function(
					contest_alias,
					title,
					description,
					start_time,
					finish_time,
					window_length,
					alias,
					points_decay_factor,					 
					submissions_gap,
					feedback, 
					penalty,
					public,
					scoreboard, 
					penalty_time_start, 					
					show_scoreboard_after,
					callback
				) {
	$.post(
		'/api/contest/update/contest_alias/' + contest_alias + '/' ,
		{
			contest_alias       : contest_alias,
			title				: title,
			description			: description,
			start_time			: start_time,
			finish_time			: finish_time,
			window_length		: window_length,
			public				: public,
			alias				: alias,
			points_decay_factor	: points_decay_factor,			
			submissions_gap		: submissions_gap,
			feedback			: feedback, 
			penalty				: penalty , 			
			scoreboard			: scoreboard, 
			penalty_time_start	: penalty_time_start, 
			show_scoreboard_after	: show_scoreboard_after 
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
		'/api/session/currentsession/',
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
		'/api/time/get/',
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
		'/api/user/login/',
		{ usernameOrEmail: username, password: password },
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getUserStats = function(username, callback) {
	$.get(
		username == null ? '/api/user/stats/' : '/api/user/stats/username/' + username,		
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

OmegaUp.prototype.getContests = function(callback) {
	var self = this;

	$.get(
		'/api/contest/list/',
		function (data) {
			for (var idx in data.results) {
				var contest = data.results[idx];
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
		'/api/contest/details/contest_alias/' + alias + '/',
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

OmegaUp.prototype.getProfile = function(username, callback) {
	var self = this;

	$.get(
		username == null ? '/api/user/profile/' : '/api/user/profile/username/' + username,
		function (data) {
			if (data.status == 'ok') {
				data.userinfo.birth_date = self.time(data.userinfo.birth_date * 1000);
				data.userinfo.graduation_date = self.time(data.userinfo.graduation_date * 1000);
			}
			
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


OmegaUp.prototype.updateProfile = function(name, birth_date, country_id, state_id, scholar_degree, graduation_date, school_id, school_name, callback) {
	var self = this;

	$.post(
		'/api/user/update/',
		{
			name: name,
			birth_date: birth_date,
			country_id: country_id,
			state_id: state_id,
			scholar_degree: scholar_degree,
			graduation_date: graduation_date,
			school_id : school_id,
			school_name : school_name
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

OmegaUp.prototype.addProblemToContest = function(contestAlias, order, problemAlias, points, callback) {
	var self = this;

	$.post(
		'/api/contest/addProblem/contest_alias/' + contestAlias + '/problem_alias/' + problemAlias + '/',
		{			
			problem_alias : problemAlias,
			points : points,
			order_in_contest : order
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

OmegaUp.prototype.addUserToContest = function(contestAlias, username, callback) {
	var self = this;

	$.post(
		'/api/contest/addUser/contest_alias/' + contestAlias + '/',
		{			
			usernameOrEmail : username			
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

OmegaUp.prototype.getProblems = function(callback) {
	var self = this;

	$.get(
		'/api/problem/list/',
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

OmegaUp.prototype.getMyProblems = function(callback) {
	var self = this;

	$.get(
		'/api/problem/mylist/',
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

OmegaUp.prototype.getMyContests = function(callback) {
	var self = this;

	$.get(
		'/api/contest/mylist/',
		function (data) {
			for (var idx in data.results) {
				var contest = data.results[idx];
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
			}
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

OmegaUp.prototype.getProblem = function(contestAlias, problemAlias, callback) {
	var self = this;

	$.post(
		contestAlias === null ? 
			'/api/problem/details/problem_alias/' + problemAlias + '/' :
			'/api/problem/details/contest_alias/' + contestAlias + '/problem_alias/' + problemAlias + '/',
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
	).error(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblemRuns = function(problemAlias, callback) {
	var self = this;

	$.post(
		'/api/problem/runs/problem_alias/' + problemAlias + '/',
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

OmegaUp.prototype.createProblem = function(contestAlias, problemAlias, callback) {
	var self = this;

	$.post(
		'/api/problem/create/',
		{
			"author_username" : 0
		},
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
	var self = this;

	$.post(
		'/api/contest/runs/contest_alias/' + contestAlias + '/',
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

OmegaUp.prototype.getContestStats = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/stats/contest_alias/' + contestAlias + '/' ,
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

OmegaUp.prototype.getContestUsers = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/users/contest_alias/' + contestAlias + '/' ,
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


OmegaUp.prototype.getProblemStats = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/stats/problem_alias/' + problemAlias + '/' ,
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

OmegaUp.prototype.getProblemStats = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/stats/problem_alias/' + problemAlias + '/' ,
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

OmegaUp.prototype.getRankByProblemsSolved = function(callback) {
	var self = this;

	$.get(
		'/api/user/rankbyproblemssolved/',
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

OmegaUp.prototype.getContestStatsForUser = function(username, callback) {
	var self = this;

	$.get(
		username == null ? '/api/user/conteststats/' : '/api/user/conteststats/username/' + username + '/' ,
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

OmegaUp.prototype.getProblemsSolved = function(username, callback) {
	var self = this;

	$.get(
		username == null ? '/api/user/problemssolved/' : '/api/user/problemssolved/username/' + username + '/' ,
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


OmegaUp.prototype.getRuns = function(options, callback) {
	var self = this;

	$.post(
		'/api/run/list/',
		options,
		function (data) {
			for (var i = 0; i < data.runs.length; i++) {
				data.runs[i].time = self.time(data.runs[i].time * 1000);
			}
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

OmegaUp.prototype.submit = function(contestAlias, problemAlias, language, code, callback) {
	var self = this;

	$.post(
		'/api/run/create/',
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
		'/api/run/status/run_alias/' + guid + '/',
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
		'/api/run/admindetails/run_alias/' + guid + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runCounts = function(callback) {
	var self = this;

	$.get(
		'/api/run/counts/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runSource = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/source/run_alias/' + guid + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.runRejudge = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/rejudge/run_alias/' + guid + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.rejudgeProblem = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/rejudge/problem_alias/' + problemAlias + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRanking = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboard/contest_alias/' + contestAlias + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getRankingEvents = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboardevents/contest_alias/' + contestAlias + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.getScoreboardMerge = function(contestAliases, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboardmerge/contest_aliases/' + contestAliases.join(',') + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};


OmegaUp.prototype.getGraderStats = function(callback) {
	var self = this;

	$.get(
		'/api/grader/status/',
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

OmegaUp.prototype.getClarifications = function(contestAlias, offset, count, callback) {
	var self = this;

	$.get(
		'/api/contest/clarifications/contest_alias/' + contestAlias + '/offset/' + offset + '/rowcount/' + count + '/',
		function (data) {
			callback(data);
		},
		'json'
	);
};

OmegaUp.prototype.newClarification = function(contestAlias, problemAlias, message, callback) {
	var self = this;

	$.post(
		'/api/clarification/create/',
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
		'/api/clarification/update/',
		{
			clarification_id: clarificationId,
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

var omegaup = new OmegaUp();

function dateToString(currentDate) {
	return currentDate.format("{MM}/{dd}/{yyyy} {HH}:{mm}");
}

function onlyDateToString(currentDate) {
	return currentDate.format("{MM}/{dd}/{yyyy}");
}
