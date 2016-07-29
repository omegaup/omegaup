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

OmegaUp.prototype.escape = function(s) {
	return (typeof s === 'string') ? s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : '';
};

OmegaUp.ui = {
	error: function(reason) {
		if ($('#status .message').length == 0) console.error("Showing warning but there is no status div");
		$('#status .message').html(reason);
		$('#status')
			.removeClass('alert-success')
			.removeClass('alert-info')
			.addClass('alert-danger')
			.slideDown();
	},

	info: function(message) {
		$('#status .message').html(message);
		$('#status')
			.removeClass('alert-danger')
			.removeClass('alert-info')
			.addClass('alert-info')
			.slideDown();
	},

	success: function(message) {
		$('#status .message').html(message);
		$('#status')
			.removeClass('alert-danger')
			.removeClass('alert-success')
			.addClass('alert-success')
			.slideDown();
	},

	dismissNotifications: function() {
		$('#status').slideUp();
	},

	bulkOperation: function (operation, onOperationFinished) {
		var isStopExecuted = false;
		var success = true;
		var error = null;

		handleResponseCallback = function(data) {
			if(data.status !== "ok") {
				success = false;
				error = data.error;
			}
		};
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				operation(this.id, handleResponseCallback);
			}
		});

		// Wait for all
		$(document).ajaxStop(function() {
			if (!isStopExecuted) {
				// Make sure we execute this block once. onOperationFinish might have
				// async calls that would fire ajaxStop event
				isStopExecuted = true;
				$(document).off("ajaxStop");

				onOperationFinished();

				if (success === false) {
					OmegaUp.ui.error("Error actualizando items: " + error);
				} else {
					OmegaUp.ui.success("Todos los items han sido actualizados");
				}
			}
		});
	},

	prettyPrintJSON: function(json) {
		return OmegaUp.ui.syntaxHighlight(JSON.stringify(json, undefined, 4) || "");
	},

	syntaxHighlight: function(json) {
		json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
				var cls = 'number';
				if (/^"/.test(match)) {
					if (/:$/.test(match)) {
						cls = 'key';
					} else {
						cls = 'string';
					}
				} else if (/true|false/.test(match)) {
					cls = 'boolean';
				} else if (/null/.test(match)) {
					cls = 'null';
				}
				return '<span class="' + cls + '">' + match + '</span>';
				});
	}
};

OmegaUp.prototype.createUser = function(s_Email, s_Username, s_PlainPassword, s_ReCaptchaToken, callback) {
	$.post(
		'/api/user/create/',
		{ email: s_Email, username: s_Username, password: s_PlainPassword, recaptcha : s_ReCaptchaToken },
		function (data) {
			if (data.status !== undefined && data.status == "error") {
				OmegaUp.ui.error(data.error);
			} else {
				if (callback !== undefined){ callback(data); }
			}
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.createGroup = function(
					alias,
					name,
					description,
					callback
				) {
	$.post(
		'/api/group/create/' ,
		{
			alias				: alias,
			name				: name,
			description			: description,
		},
		function(data) {
			if (data.status !== undefined && data.status == "error") {
				OmegaUp.ui.error(data.error);
			}
			if (callback !== undefined) { callback(data); }
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
					penalty_type,
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
			penalty_type	: penalty_type,
			show_scoreboard_after	: show_scoreboard_after
		},
		function(data) {
			if (data.status !== undefined && data.status == "error") {
				OmegaUp.ui.error(data.error);
			}
			if (callback !== undefined) { callback(data); }
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
					penalty_type,
					show_scoreboard_after,
					contestant_must_register,
					callback
				) {
	$.post(
		'/api/contest/update/contest_alias/' + encodeURIComponent(contest_alias) + '/' ,
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
			penalty				: penalty,
			scoreboard			: scoreboard,
			penalty_type		: penalty_type,
			show_scoreboard_after	: show_scoreboard_after,
			contestant_must_register	: contestant_must_register
		},
		function(data) {
			if (data.status !== undefined && data.status == "error") {
				OmegaUp.ui.error(data.error);
			} else {
				if (callback !== undefined) { callback(data); }
			}
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.authenticated = function(callback) {
	$.get(
		'/api/session/currentsession/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.googleLogin = function(storeToken, callback) {
	$.post(
		'/api/session/googlelogin/',
		{ storeToken: storeToken },
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getUserStats = function(username, callback) {
	$.get(
		username == null ? '/api/user/stats/' : '/api/user/stats/username/' + encodeURIComponent(username),
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getMyGroups = function(callback) {
	var self = this;

	$.get(
		'/api/group/mylist/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
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
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.openContest = function(alias, callback) {
	var self = this;

	$.get(
		'/api/contest/open/contest_alias/' + encodeURIComponent(alias) + '/',
		function (contest) {
			callback(contest);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getContest = function(alias, callback) {
	var self = this;

	$.get(
		'/api/contest/details/contest_alias/' + encodeURIComponent(alias) + '/',
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.submission_deadline = self.time(contest.submission_deadline * 1000);
				contest.show_penalty = (contest.penalty != 0 || contest.penalty_type != "none");
			}
			callback(contest);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getContestAdminDetails = function(alias, callback) {
	var self = this;

	$.get(
		'/api/contest/admindetails/contest_alias/' + encodeURIComponent(alias) + '/',
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.submission_deadline = self.time(contest.submission_deadline * 1000);
				contest.show_penalty = (contest.penalty != 0 || contest.penalty_type != "none");
			}
			callback(contest);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getContestPublicDetails = function(alias, callback) {
	var self = this;

	$.get(
		'/api/contest/publicdetails/contest_alias/' + encodeURIComponent(alias) + '/',
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.submission_deadline = self.time(contest.submission_deadline * 1000);
				contest.show_penalty = (contest.penalty != 0 || contest.penalty_type != "none");
			}
			callback(contest);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error': undefined});
		}
	});
};

OmegaUp.prototype.getContestByToken = function(alias, token, callback) {
	var self = this;

	$.get(
		'/api/contest/details/contest_alias/' + encodeURIComponent(alias) + '/token/' + encodeURIComponent(token) + '/',
		function (contest) {
			if (contest.status == 'ok') {
				contest.start_time = self.time(contest.start_time * 1000);
				contest.finish_time = self.time(contest.finish_time * 1000);
				contest.show_penalty = (contest.penalty || contest.penalty_type != "none");
			}
			callback(contest);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		username == null ? '/api/user/profile/' : '/api/user/profile/username/' + encodeURIComponent(username) + '/',
		function (data) {
			if (data.status == 'ok') {
				data.userinfo.birth_date = self.time(data.userinfo.birth_date * 1000);
				data.userinfo.graduation_date = self.time(data.userinfo.graduation_date * 1000);
			}

			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getCoderOfTheMonth = function(callback) {
	var self = this;

	$.get(
		'/api/user/coderofthemonth/',
		function (data) {
			if (data.status == 'ok') {
				data.userinfo.birth_date = self.time(data.userinfo.birth_date * 1000);
				data.userinfo.graduation_date = self.time(data.userinfo.graduation_date * 1000);
			}

			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.arbitrateContestUserRequest = function(contest_alias, username, resolution, notes, callback) {
	$.post(
		'/api/contest/arbitraterequest/',
		{
			contest_alias: contest_alias,
			username : username,
			resolution : resolution,
			note : notes
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
}

OmegaUp.prototype.registerForContest = function(contest_alias, callback) {
	var self = this;
	$.post(
		'/api/contest/registerforcontest/',
		{
			contest_alias: contest_alias,
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.updateProblem = function(alias, public, callback) {
	var self = this;

	$.post(
		'/api/problem/update/',
		{
			problem_alias: alias,
			public: public,
			message: public ? 'private -> public' : 'public -> private'
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.updateProfile = function(name, birth_date, country_id, state_id, scholar_degree, graduation_date, school_id, school_name, locale, recruitment_optin, callback) {
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
			school_name : school_name,
			locale : locale,
			recruitment_optin: recruitment_optin
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.updateBasicProfile = function(username, name, password, callback) {
	var self = this;

	$.post(
		'/api/user/updatebasicinfo/',
		{
			username: username,
			name: name,
			password : password
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.updateMainEmail = function(email, callback) {
	var self = this;

	$.post(
		'/api/user/updateMainEmail/',
		{
			email: email
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		'/api/contest/addProblem/contest_alias/' + encodeURIComponent(contestAlias) + '/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			problem_alias : problemAlias,
			points : points,
			order_in_contest : order
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeProblemFromContest = function(contestAlias, problemAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/removeProblem/contest_alias/' + encodeURIComponent(contestAlias) + '/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.contestProblems = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/problems/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addAdminToContest = function(contestAlias, username, callback) {
	var self = this;

	$.post(
		'/api/contest/addAdmin/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeAdminFromContest = function(contestAlias, username, callback) {
	var self = this;

	$.post(
		'/api/contest/removeAdmin/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addAdminToProblem = function(problemAlias, username, callback) {
	var self = this;

	$.post(
		'/api/problem/addAdmin/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeAdminFromProblem = function(problemAlias, username, callback) {
	var self = this;

	$.post(
		'/api/problem/removeAdmin/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addGroupAdminToContest = function(contestAlias, alias, callback) {
	var self = this;

	$.post(
		'/api/contest/addGroupAdmin/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			group: alias
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeGroupAdminFromContest = function(contestAlias, alias, callback) {
	var self = this;

	$.post(
		'/api/contest/removeGroupAdmin/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			group: alias
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addGroupAdminToProblem = function(problemAlias, alias, callback) {
	var self = this;

	$.post(
		'/api/problem/addGroupAdmin/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			group: alias
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeGroupAdminFromProblem = function(problemAlias, alias, callback) {
	var self = this;

	$.post(
		'/api/problem/removeGroupAdmin/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			group: alias
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addTagToProblem = function(problemAlias, tagname, public, callback) {
	var self = this;

	$.post(
		'/api/problem/addTag/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			name: tagname,
			public: public
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeTagFromProblem = function(problemAlias, tagname, callback) {
	var self = this;

	$.post(
		'/api/problem/removeTag/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		{
			name : tagname
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addUserToGroup = function(groupAlias, username, callback) {
	var self = this;

	$.post(
		'/api/group/addUser/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeUserFromGroup = function(groupAlias, username, callback) {
	var self = this;

	$.post(
		'/api/group/removeUser/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addScoreboardToGroup = function(groupAlias, alias, name, description, callback) {
	var self = this;

	$.post(
		'/api/group/createScoreboard/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			alias		: alias,
			name		: name,
			description : description
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addContestToScoreboard = function(groupAlias, scoreboardAlias, contestAlias, onlyAC, weight, callback) {
	var self = this;

	$.post(
		'/api/groupScoreboard/addContest/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			scoreboard_alias		: scoreboardAlias,
			contest_alias			: contestAlias,
			only_ac					: onlyAC,
			weight					: weight,
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeContestFromScoreboard = function(groupAlias, scoreboardAlias, contestAlias, callback) {
	var self = this;

	$.post(
		'/api/groupScoreboard/removeContest/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			scoreboard_alias		: scoreboardAlias,
			contest_alias			: contestAlias
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		'/api/contest/addUser/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.removeUserFromContest = function(contestAlias, username, callback) {
	var self = this;

	$.post(
		'/api/contest/removeUser/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		{
			usernameOrEmail : username
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.searchProblems = function(query, callback) {
	var self = this;

	$.post(
		'/api/problem/list/',
		{query: query},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.searchTags = function(query, callback) {
	var self = this;

	$.post(
		'/api/tag/list/',
		{query: query},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.searchSchools = function(query, callback) {
	var self = this;

	$.post(
		'/api/school/list/',
		{query: query},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.searchUsers = function(query, callback) {
	var self = this;

	$.post(
		'/api/user/list/',
		{query: query},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.searchGroups = function(query, callback) {
	var self = this;

	$.post(
		'/api/group/list/',
		{query: query},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblem = function(contestAlias, problemAlias, callback, statement_type, show_solvers, language) {
	var self = this;
	if (statement_type === undefined) {
		statement_type = "html";
	}
	var params = {statement_type:statement_type, show_solvers: !!show_solvers};
	if (language) {
		params.lang = language;
	}
	$.post(
		contestAlias === null ?
			'/api/problem/details/problem_alias/' + encodeURIComponent(problemAlias) + '/' :
			'/api/problem/details/contest_alias/' + encodeURIComponent(contestAlias) + '/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		params,
		function (problem) {
			if (problem.runs) {
				for (var i = 0; i < problem.runs.length; i++) {
					problem.runs[i].time = self.time(problem.runs[i].time * 1000);
				}
			}
			callback(problem);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getGroupMembers = function(groupAlias, callback) {
	var self = this;

	$.post(
		'/api/group/members/group_alias/' + encodeURIComponent(groupAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getGroup = function(groupAlias, callback) {
	var self = this;

	$.post(
		'/api/group/details/group_alias/' + encodeURIComponent(groupAlias) + '/',
		function (problem) {
			callback(problem);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getGroupScoreboard = function(groupAlias, scoreboardAlias, callback) {
	var self = this;

	$.post(
		'/api/groupScoreboard/details/group_alias/' + encodeURIComponent(groupAlias) + '/',
		{
			scoreboard_alias : scoreboardAlias
		},
		function (problem) {
			callback(problem);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblemRuns = function(problemAlias, options, callback) {
	var self = this;

	$.post(
		'/api/problem/runs/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		options,
		function (problem) {
			if (problem.runs) {
				for (var i = 0; i < problem.runs.length; i++) {
					problem.runs[i].time = self.time(problem.runs[i].time * 1000);
				}
			}
			callback(problem);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getContestRuns = function(contestAlias, options, callback) {
	var self = this;

	$.post(
		'/api/contest/runs/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		options,
		function (data) {
			for (var i = 0; i < data.runs.length; i++) {
				data.runs[i].time = self.time(data.runs[i].time * 1000);
			}
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getContestStats = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/stats/contest_alias/' + encodeURIComponent(contestAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		'/api/contest/users/contest_alias/' + encodeURIComponent(contestAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getContestRequests = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/requests/contest_alias/' + encodeURIComponent(contestAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getContestAdmins = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/admins/contest_alias/' + encodeURIComponent(contestAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblemAdmins = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/admins/problem_alias/' + encodeURIComponent(problemAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblemTags = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/tags/problem_alias/' + encodeURIComponent(problemAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		'/api/problem/stats/problem_alias/' + encodeURIComponent(problemAlias) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getProblemClarifications = function(problemAlias, offset, rowcount, callback) {
	var self = this;

	$.get(
		'/api/problem/clarifications/problem_alias/' + encodeURIComponent(problemAlias) +
		'/offset/' + offset + '/rowcount/' + rowcount + '/',
		function (data) {
			for (var idx in data.clarifications) {
				var clarification = data.clarifications[idx];
				clarification.time = self.time(clarification.time * 1000);
			}
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getRankByProblemsSolved = function(rowcount, callback) {
	var self = this;

	$.get(
		'/api/user/rankbyproblemssolved/rowcount/' + encodeURIComponent(rowcount) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		username == null ? '/api/user/conteststats/' : '/api/user/conteststats/username/' + encodeURIComponent(username) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		username == null ? '/api/user/problemssolved/' : '/api/user/problemssolved/username/' + encodeURIComponent(username) + '/' ,
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
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
		'/api/run/status/run_alias/' + encodeURIComponent(guid) + '/',
		function (data) {
			data.time = self.time(data.time * 1000);
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.runDetails = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/details/run_alias/' + encodeURIComponent(guid) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.runCounts = function(callback) {
	var self = this;

	$.get(
		'/api/run/counts/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.runSource = function(guid, callback) {
	var self = this;

	$.get(
		'/api/run/source/run_alias/' + encodeURIComponent(guid) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.runRejudge = function(guid, debug, callback) {
	var self = this;

	$.get(
		'/api/run/rejudge/run_alias/' + encodeURIComponent(guid) + '/' + (debug ? 'debug/true/' : ''),
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.rejudgeProblem = function(problemAlias, callback) {
	var self = this;

	$.get(
		'/api/problem/rejudge/problem_alias/' + encodeURIComponent(problemAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getRanking = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboard/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getRankingByToken = function(contestAlias, token, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboard/contest_alias/' + encodeURIComponent(contestAlias) + '/token/' + encodeURIComponent(token) + '/',
		function (data) {
			data.start_time = self.time(data.start_time * 1000);
			data.finish_time = self.time(data.finish_time * 1000);
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getRankingEventsByToken = function(contestAlias, token, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboardevents/contest_alias/' + encodeURIComponent(contestAlias) + '/token/' + encodeURIComponent(token) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getRankByProblemsSolved = function(offset, rowcount, callback) {
	var self = this;

	$.get(
		'/api/user/RankByProblemsSolved/offset/' + encodeURIComponent(offset) + '/rowcount/' + encodeURIComponent(rowcount) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getRankingEvents = function(contestAlias, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboardevents/contest_alias/' + encodeURIComponent(contestAlias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getScoreboardMerge = function(contestAliases, callback) {
	var self = this;

	$.get(
		'/api/contest/scoreboardmerge/contest_aliases/' + contestAliases.map(encodeURIComponent).join(',') + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getGraderStats = function(callback) {
	var self = this;

	$.get(
		'/api/grader/status/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
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
		'/api/contest/clarifications/contest_alias/' + encodeURIComponent(contestAlias) + '/offset/' + encodeURIComponent(offset) + '/rowcount/' + encodeURIComponent(count) + '/',
		function (data) {
			for (var idx in data.clarifications) {
				var clarification = data.clarifications[idx];
				clarification.time = self.time(clarification.time * 1000);
			}
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
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
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
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
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.addUsersToInterview = function(interviewAlias, usernameOrEmailsCSV, callback) {
	var self = this;

	$.post(
		'/api/interview/addUsers/interview_alias/' + encodeURIComponent(interviewAlias) + '/',
		{
			usernameOrEmailsCSV : usernameOrEmailsCSV
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getInterview = function(alias, callback) {
	var self = this;

	$.get(
		'/api/interview/details/interview_alias/' + encodeURIComponent(alias) + '/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.getInterviewStatsForUser = function(interviewAlias, username, callback) {
	var self = this;

	$.get(
		'/api/user/interviewstats/username/' + encodeURIComponent(username) + '/interview/' + encodeURIComponent(interviewAlias),
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.getInterviews = function(callback) {
	var self = this;

	$.get(
		'/api/interview/list/',
		function (data) {
			callback(data);
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.createInterview = function(s_Alias, s_Title, s_Duration, callback) {
	$.post(
		'/api/interview/create/',
		{ alias : s_Alias, title : s_Title, duration : s_Duration},
		function (data) {
			if (data.status !== undefined && data.status == "error") {
				OmegaUp.ui.error(data.error);
			} else {
				if (callback !== undefined){ callback(data); }
			}
		},
		'json'
	).fail(function (data) {
		if (callback !== undefined) {
			try {
				callback(JSON.parse(data.responseText));
			} catch (err) {
				callback({status: 'error', error: err});
			}
		}
	});
};

OmegaUp.prototype.forceVerifyEmail = function(username, callback) {
	var self = this;

	$.post(
		'/api/user/verifyemail/',
		{
			usernameOrEmail: username,
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.forceChangePassword = function(username, newpassword, callback) {
	var self = this;

	$.post(
		'/api/user/changepassword/',
		{
			username: username,
			password: newpassword
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.changePassword = function(oldPassword, newPassword, callback) {
	var self = this;

	$.post(
		'/api/user/changepassword/',
		{
			old_password: oldPassword,
			password: newPassword
		},
		function (data) {
			callback(data);
		},
		'json'
	).fail(function(j, status, errorThrown) {
		try {
			callback(JSON.parse(j.responseText));
		} catch (err) {
			callback({status:'error', 'error':undefined});
		}
	});
};

OmegaUp.prototype.resetCreate = function(email, callback) {
    OmegaUp.ui.dismissNotifications();
	$.post(
		'/api/reset/create',
		{ email: email },
		function(data) {
			OmegaUp.ui.success(data.message);
			callback();
		},
		'json'
	).fail(function(j, status, errorThrown) {
		OmegaUp.ui.error(JSON.parse(j.responseText).error);
		callback();
	});
};

OmegaUp.prototype.resetUpdate = function(email, resetToken, password, passwordConfirmation, callback) {
    OmegaUp.ui.dismissNotifications();
    $.post(
		'/api/reset/update',
		{
			email: email,
			reset_token: resetToken,
			password: password,
			password_confirmation: passwordConfirmation
		},
		function(data) {
			OmegaUp.ui.success(data.message);
			callback();
		},
		'json'
	).fail(function(j, status, errorThrown) {
		OmegaUp.ui.error(JSON.parse(j.responseText).error);
		callback();
	});
}

OmegaUp.prototype.typeaheadWrapper = function(f) {
	var self = this;
	var lastRequest = null;
	var pending = false;
	function wrappedCall(query, callback) {
		if (pending) {
			lastRequest = [query, callback];
		} else {
			pending = true;
			f(query, function(data) {
				if (lastRequest != null) {
					// Typeahead will ignore any stale callbacks. Given that we
					// will start a new request ASAP, let's do a best-effort
					// callback to the current request with the old data.
					lastRequest[1](data);
				} else {
					callback(data);
				}
				pending = false;
				if (lastRequest != null) {
					var request = lastRequest;
					lastRequest = null;
					wrappedCall(request[0], request[1]);
				}
			});
		}
	}
	return wrappedCall;
};

var omegaup = new OmegaUp();

function dateToString(currentDate) {
	return currentDate.format("{MM}/{dd}/{yyyy} {HH}:{mm}");
}

function onlyDateToString(currentDate) {
	return currentDate.format("{MM}/{dd}/{yyyy}");
}

$(document).ajaxError(function(e, xhr, settings, exception) {
	try {
		var response = jQuery.parseJSON(xhr.responseText);
		console.error(settings.url, xhr.status, response.error, response);
	} catch(e) {
		console.error(settings.url, xhr.status, xhr.responseText);
	}
});

// From http://stackoverflow.com/questions/6312993/javascript-seconds-to-time-with-format-hhmmss
function toHHMM(duration) {
    var sec_num = parseInt(duration, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}

    var time    = hours+'h '+minutes+'m';
    return time;
}

function getFlag(country) {
	if (!country) {
		return '';
	} else {
		return ' <img src="/media/flags/' + country.toLowerCase() + '.png" width="16" height="11" title="' + country + '" />';
	}
}

function getProfileLink(username) {
	return '<a href="/profile/' + username +'" >' + username + '</a>';
}
