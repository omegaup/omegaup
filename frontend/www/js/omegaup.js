var omegaup = omegaup || {};

omegaup.OmegaUp = {
	loggedIn: false,

	username: null,

	ready: false,

	_documentReady: false,

	_initialized: false,

	_deltaTime: undefined,

	_listeners: {
		'ready': [],
	},

	_onDocumentReady: function() {
		omegaup.OmegaUp._documentReady = true;
		if (typeof(omegaup.OmegaUp._deltaTime) !== 'undefined') {
			omegaup.OmegaUp._notify('ready');
		}
	},

	_initialize: function() {
		var t0 = new Date().getTime();
		omegaup.API.currentSession().then(function(data) {
			if (data.session.valid) {
				omegaup.OmegaUp.loggedIn = true;
				omegaup.OmegaUp._deltaTime = data.time * 1000 - t0;
				omegaup.OmegaUp.username = data.session.user.username;
				omegaup.OmegaUp.email = data.session.email;
			}

			omegaup.OmegaUp.ready = true;
			if (omegaup.OmegaUp._documentReady) {
				omegaup.OmegaUp._notify('ready');
			}
		});
	},

	_notify: function(eventName) {
		for (var i = 0; i < omegaup.OmegaUp._listeners[eventName].length; i++) {
			omegaup.OmegaUp._listeners[eventName][i]();
		}
		omegaup.OmegaUp._listeners[eventName] = [];
	},

	on: function(events, handler) {
		if (omegaup.OmegaUp._initialized) return;
		omegaup.OmegaUp._initialize();
		var splitNames = events.split(' ');
		for (var i = 0; i < splitNames.length; i++) {
			if (!omegaup.OmegaUp._listeners.hasOwnProperty(splitNames[i])) continue;

			if (splitNames[i] == 'ready' && omegaup.OmegaUp.ready) {
				handler();
				return;
			}

			omegaup.OmegaUp._listeners[splitNames[i]].push(handler);
		}
	},

	syncTime: function() {
		var t0 = new Date().getTime();
		omegaup.API.time().then(function(data) {
			if (data.status != 'ok') return;
			omegaup.OmegaUp._deltaTime = data.time * 1000 - t0;
		});
	},

	_realTime: function(timestamp) {
		if (typeof(timestamp) === 'undefined') {
			return new Date().getTime();
		}
		return new Date(timestamp).getTime();
	},

	time: function(timestamp) {
		return new Date(omegaup.OmegaUp._realTime(timestamp) +
		                (omegaup.OmegaUp._deltaTime || 0));
	}
};

omegaup.API = {
	_wrapDeferred: function(jqXHR) {
		var dfd = $.Deferred();
		jqXHR.done(function(data) {
			dfd.resolve(data);
		}).fail(function(jqXHR) {
			var errorData;
			try {
				errorData = JSON.parse(jqXHR.responseText);
			} catch (err) {
				errorData = {status: 'error', error: err};
			}
			dfd.resolve(errorData);
		});
		return dfd.promise();
	},

	currentSession: function() {
		return omegaup.API._wrapDeferred($.get(
			'/api/session/currentsession/',
			'json'
		));
	},

	time: function() {
		return omegaup.API._wrapDeferred($.get(
			'/api/time/get/',
			'json'
		));
	},

	createUser: function(s_Email, s_Username, s_PlainPassword, s_ReCaptchaToken, callback) {
		$.post(
			'/api/user/create/',
			{ email: s_Email, username: s_Username, password: s_PlainPassword, recaptcha : s_ReCaptchaToken },
			function (data) {
				if (data.status !== undefined && data.status == "error") {
					omegaup.UI.error(data.error);
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
	},

	createGroup: function(
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
					omegaup.UI.error(data.error);
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
	},

	createContest: function(
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
					omegaup.UI.error(data.error);
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
	},

	updateContest: function(
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
					omegaup.UI.error(data.error);
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
	},

	login: function(username, password, callback) {
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
	},

	googleLogin: function(storeToken, callback) {
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
	},

	getUserStats: function(username, callback) {
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
	},

	getMyGroups: function(callback) {
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
	},

	getContests: function(callback, params) {
		return $.get(
			'/api/contest/list/',
			params,
			function (data) {
				for (var idx in data.results) {
					var contest = data.results[idx];
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
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
	},

	openContest: function(alias, callback) {
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
	},

	getContest: function(alias, callback) {
		$.get(
			'/api/contest/details/contest_alias/' + encodeURIComponent(alias) + '/',
			function (contest) {
				if (contest.status == 'ok') {
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
					contest.submission_deadline = omegaup.OmegaUp.time(contest.submission_deadline * 1000);
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
	},

	getContestAdminDetails: function(alias, callback) {
		$.get(
			'/api/contest/admindetails/contest_alias/' + encodeURIComponent(alias) + '/',
			function (contest) {
				if (contest.status == 'ok') {
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
					contest.submission_deadline = omegaup.OmegaUp.time(contest.submission_deadline * 1000);
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
	},

	getContestPublicDetails: function(alias, callback) {
		$.get(
			'/api/contest/publicdetails/contest_alias/' + encodeURIComponent(alias) + '/',
			function (contest) {
				if (contest.status == 'ok') {
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
					contest.submission_deadline = omegaup.OmegaUp.time(contest.submission_deadline * 1000);
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
	},

	getContestByToken: function(alias, token, callback) {
		$.get(
			'/api/contest/details/contest_alias/' + encodeURIComponent(alias) + '/token/' + encodeURIComponent(token) + '/',
			function (contest) {
				if (contest.status == 'ok') {
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
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
	},

	getProfile: function(username, callback) {
		$.get(
			username == null ? '/api/user/profile/' : '/api/user/profile/username/' + encodeURIComponent(username) + '/',
			function (data) {
				if (data.status == 'ok') {
					data.userinfo.birth_date = omegaup.OmegaUp.time(data.userinfo.birth_date * 1000);
					data.userinfo.graduation_date = omegaup.OmegaUp.time(data.userinfo.graduation_date * 1000);
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
	},

	getCoderOfTheMonth: function(callback) {
		$.get(
			'/api/user/coderofthemonth/',
			function (data) {
				if (data.status == 'ok') {
					data.userinfo.birth_date = omegaup.OmegaUp.time(data.userinfo.birth_date * 1000);
					data.userinfo.graduation_date = omegaup.OmegaUp.time(data.userinfo.graduation_date * 1000);
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
	},

	arbitrateContestUserRequest: function(contest_alias, username, resolution, notes, callback) {
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
	},

	registerForContest: function(contest_alias, callback) {
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
	},

	updateProblem: function(alias, public, callback) {
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
	},

	updateProfile: function(name, birth_date, country_id, state_id, scholar_degree, graduation_date, school_id, school_name, locale, recruitment_optin, callback) {
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
	},

	updateBasicProfile: function(username, name, password, callback) {
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
	},

	updateMainEmail: function(email, callback) {
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
	},

	addProblemToContest: function(contestAlias, order, problemAlias, points, callback) {
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
	},

	removeProblemFromContest: function(contestAlias, problemAlias, callback) {
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
	},

	contestProblems: function(contestAlias, callback) {
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
	},

	addAdminToContest: function(contestAlias, username, callback) {
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
	},

	removeAdminFromContest: function(contestAlias, username, callback) {
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
	},

	addAdminToProblem: function(problemAlias, username, callback) {
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
	},

	removeAdminFromProblem: function(problemAlias, username, callback) {
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
	},

	addGroupAdminToContest: function(contestAlias, alias, callback) {
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
	},

	removeGroupAdminFromContest: function(contestAlias, alias, callback) {
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
	},

	addGroupAdminToProblem: function(problemAlias, alias, callback) {
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
	},

	removeGroupAdminFromProblem: function(problemAlias, alias, callback) {
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
	},

	addTagToProblem: function(problemAlias, tagname, public, callback) {
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
	},

	removeTagFromProblem: function(problemAlias, tagname, callback) {
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
	},

	addUserToGroup: function(groupAlias, username, callback) {
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
	},

	removeUserFromGroup: function(groupAlias, username, callback) {
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
	},

	addScoreboardToGroup: function(groupAlias, alias, name, description, callback) {
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
	},

	addContestToScoreboard: function(groupAlias, scoreboardAlias, contestAlias, onlyAC, weight, callback) {
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
	},

	removeContestFromScoreboard: function(groupAlias, scoreboardAlias, contestAlias, callback) {
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
	},

	addUserToContest: function(contestAlias, username, callback) {
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
	},

	removeUserFromContest: function(contestAlias, username, callback) {
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
	},

	getProblems: function(callback) {
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
	},

	searchProblems: function(query, callback) {
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
	},

	searchTags: function(query, callback) {
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
	},

	searchSchools: function(query, callback) {
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
	},

	searchUsers: function(query, callback) {
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
	},

	searchGroups: function(query, callback) {
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
	},

	getMyProblems: function(callback) {
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
	},

	getMyContests: function(callback) {
		$.get(
			'/api/contest/mylist/',
			function (data) {
				for (var idx in data.results) {
					var contest = data.results[idx];
					contest.start_time = omegaup.OmegaUp.time(contest.start_time * 1000);
					contest.finish_time = omegaup.OmegaUp.time(contest.finish_time * 1000);
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
	},

	getProblem: function(contestAlias, problemAlias, callback, statement_type, show_solvers, language) {
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
						problem.runs[i].time = omegaup.OmegaUp.time(problem.runs[i].time * 1000);
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
	},

	getGroupMembers: function(groupAlias, callback) {
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
	},

	getGroup: function(groupAlias, callback) {
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
	},

	getGroupScoreboard: function(groupAlias, scoreboardAlias, callback) {
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
	},

	getProblemRuns: function(problemAlias, options, callback) {
		$.post(
			'/api/problem/runs/problem_alias/' + encodeURIComponent(problemAlias) + '/',
			options,
			function (problem) {
				if (problem.runs) {
					for (var i = 0; i < problem.runs.length; i++) {
						problem.runs[i].time = omegaup.OmegaUp.time(problem.runs[i].time * 1000);
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
	},

	createProblem: function(contestAlias, problemAlias, callback) {
		$.post(
			'/api/problem/create/',
			{
				"author_username" : 0
			},
			function (problem) {
				if (problem.runs) {
					for (var i = 0; i < problem.runs.length; i++) {
						problem.runs[i].time = omegaup.OmegaUp.time(problem.runs[i].time * 1000);
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
	},

	getContestRuns: function(contestAlias, options, callback) {
		$.post(
			'/api/contest/runs/contest_alias/' + encodeURIComponent(contestAlias) + '/',
			options,
			function (data) {
				for (var i = 0; i < data.runs.length; i++) {
					data.runs[i].time = omegaup.OmegaUp.time(data.runs[i].time * 1000);
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
	},

	getContestStats: function(contestAlias, callback) {
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
	},

	getContestUsers: function(contestAlias, callback) {
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
	},

	getContestRequests: function(contestAlias, callback) {
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
	},

	getContestAdmins: function(contestAlias, callback) {
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
	},

	getProblemAdmins: function(problemAlias, callback) {
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
	},

	getProblemTags: function(problemAlias, callback) {
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
	},

	getProblemStats: function(problemAlias, callback) {
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
	},

	getProblemClarifications: function(problemAlias, offset, rowcount, callback) {
		$.get(
			'/api/problem/clarifications/problem_alias/' + encodeURIComponent(problemAlias) +
			'/offset/' + offset + '/rowcount/' + rowcount + '/',
			function (data) {
				for (var idx in data.clarifications) {
					var clarification = data.clarifications[idx];
					clarification.time = omegaup.OmegaUp.time(clarification.time * 1000);
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
	},

	getRankByProblemsSolved: function(rowcount, callback) {
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
	},

	getContestStatsForUser: function(username, callback) {
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
	},

	getProblemsSolved: function(username, callback) {
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
	},

	getRuns: function(options, callback) {
		$.post(
			'/api/run/list/',
			options,
			function (data) {
				for (var i = 0; i < data.runs.length; i++) {
					data.runs[i].time = omegaup.OmegaUp.time(data.runs[i].time * 1000);
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
	},

	submit: function(contestAlias, problemAlias, language, code, callback) {
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
	},

	runStatus: function(guid, callback) {
		$.get(
			'/api/run/status/run_alias/' + encodeURIComponent(guid) + '/',
			function (data) {
				data.time = omegaup.OmegaUp.time(data.time * 1000);
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
	},

	runDetails: function(guid, callback) {
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
	},

	runCounts: function(callback) {
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
	},

	runSource: function(guid, callback) {
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
	},

	runRejudge: function(guid, debug, callback) {
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
	},

	rejudgeProblem: function(problemAlias, callback) {
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
	},

	getRanking: function(contestAlias, callback) {
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
	},

	getRankingByToken: function(contestAlias, token, callback) {
		$.get(
			'/api/contest/scoreboard/contest_alias/' + encodeURIComponent(contestAlias) + '/token/' + encodeURIComponent(token) + '/',
			function (data) {
				data.start_time = omegaup.OmegaUp.time(data.start_time * 1000);
				data.finish_time = omegaup.OmegaUp.time(data.finish_time * 1000);
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
	},

	getRankingEventsByToken: function(contestAlias, token, callback) {
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
	},

	getRankByProblemsSolved: function(offset, rowcount, callback) {
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
	},

	getRankingEvents: function(contestAlias, callback) {
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
	},

	getScoreboardMerge: function(contestAliases, callback) {
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
	},

	getGraderStats: function(callback) {
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
	},

	getClarifications: function(contestAlias, offset, count, callback) {
		$.get(
			'/api/contest/clarifications/contest_alias/' + encodeURIComponent(contestAlias) + '/offset/' + encodeURIComponent(offset) + '/rowcount/' + encodeURIComponent(count) + '/',
			function (data) {
				for (var idx in data.clarifications) {
					var clarification = data.clarifications[idx];
					clarification.time = omegaup.OmegaUp.time(clarification.time * 1000);
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
	},

	newClarification: function(contestAlias, problemAlias, message, callback) {
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
	},

	updateClarification: function(clarificationId, answer, public, callback) {
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
	},

	UserEdit: function( username, name, email, birthDate, school, password, oldPassword, callback ){
		var toSend = {};

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
	},

	addUsersToInterview: function(interviewAlias, usernameOrEmailsCSV, callback) {
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
	},

	getInterview: function(alias, callback) {
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
	},

	getInterviewStatsForUser: function(interviewAlias, username, callback) {
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
	},

	getInterviews: function(callback) {
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
	},

	createInterview: function(s_Alias, s_Title, s_Duration, callback) {
		$.post(
			'/api/interview/create/',
			{ alias : s_Alias, title : s_Title, duration : s_Duration},
			function (data) {
				if (data.status !== undefined && data.status == "error") {
					omegaup.UI.error(data.error);
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
	},

	forceVerifyEmail: function(username, callback) {
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
	},

	forceChangePassword: function(username, newpassword, callback) {
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
	},

	changePassword: function(oldPassword, newPassword, callback) {
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
	},

	resetCreate: function(email, callback) {
			omegaup.UI.dismissNotifications();
		$.post(
			'/api/reset/create',
			{ email: email },
			function(data) {
				omegaup.UI.success(data.message);
				callback();
			},
			'json'
		).fail(function(j, status, errorThrown) {
			omegaup.UI.error(JSON.parse(j.responseText).error);
			callback();
		});
	},

	resetUpdate: function(email, resetToken, password, passwordConfirmation, callback) {
			omegaup.UI.dismissNotifications();
			$.post(
			'/api/reset/update',
			{
				email: email,
				reset_token: resetToken,
				password: password,
				password_confirmation: passwordConfirmation
			},
			function(data) {
				omegaup.UI.success(data.message);
				callback();
			},
			'json'
		).fail(function(j, status, errorThrown) {
			omegaup.UI.error(JSON.parse(j.responseText).error);
			callback();
		});
	}
};

omegaup.UI = {
	navigateTo: function(url) {
		window.location = url;
	},

	escape: function(s) {
		if (typeof s !== 'string') return '';
		return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
	},

	displayStatus: function(message, type) {
		if ($('#status .message').length == 0) {
			console.error("Showing warning but there is no status div");
		}

		$('#status .message').html(message);
		$('#status')
			.removeClass('alert-success alert-info alert-warning alert-danger')
			.addClass(type)
			.slideDown();
	},

	error: function(message) {
		omegaup.UI.displayStatus(message, 'alert-danger');
	},

	info: function(message) {
		omegaup.UI.displayStatus(message, 'alert-info');
	},

	success: function(message) {
		omegaup.UI.displayStatus(message, 'alert-success');
	},

	warning: function(message) {
		omegaup.UI.displayStatus(message, 'alert-warning');
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
					omegaup.UI.error("Error actualizando items: " + error);
				} else {
					omegaup.UI.success("Todos los items han sido actualizados");
				}
			}
		});
	},

	prettyPrintJSON: function(json) {
		return omegaup.UI.syntaxHighlight(JSON.stringify(json, undefined, 4) || "");
	},

	syntaxHighlight: function(json) {
		var jsonRE = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g;
		json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		return json.replace(jsonRE, function (match) {
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
	},

	typeaheadWrapper: function(f) {
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
	},

	getProfileLink: function(username) {
		return '<a href="/profile/' + username +'" >' + username + '</a>';
	},

	// From http://stackoverflow.com/questions/6312993/javascript-seconds-to-time-with-format-hhmmss
	toHHMM: function(duration) {
			var sec_num = parseInt(duration, 10);
			var hours   = Math.floor(sec_num / 3600);
			var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
			var seconds = sec_num - (hours * 3600) - (minutes * 60);

			if (minutes < 10) {minutes = "0"+minutes;}
			if (seconds < 10) {seconds = "0"+seconds;}

			var time    = hours+'h '+minutes+'m';
			return time;
	},

	formatDateTime: function(date) {
		return date.format("{MM}/{dd}/{yyyy} {HH}:{mm}");
	},

	formatDate: function(date) {
		return date.format("{MM}/{dd}/{yyyy}");
	}
};

$(document).ajaxError(function(e, xhr, settings, exception) {
	try {
		var response = jQuery.parseJSON(xhr.responseText);
		console.error(settings.url, xhr.status, response.error, response);
	} catch(e) {
		console.error(settings.url, xhr.status, xhr.responseText);
	}
});

$(document).ready(omegaup.OmegaUp._onDocumentReady);
// TODO(lhchavez): Remove this.
omegaup.OmegaUp._initialize();
