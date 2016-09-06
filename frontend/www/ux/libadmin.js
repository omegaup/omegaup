omegaup.arena = omegaup.arena || {};

omegaup.arena.ArenaAdmin = function(arena, onlyProblemAlias) {
	this.arena = arena;
	this.arena.contestAdmin = true;
	this.onlyProblemAlias = onlyProblemAlias;

	this.setUpPagers();
	this.arena.runs.attach($('#runs table.runs'));
};

omegaup.arena.ArenaAdmin.prototype.setUpPagers = function() {
	var self = this;

	self.arena.runs.filter_verdict.subscribe(self.refreshRuns.bind(self));
	self.arena.runs.filter_status.subscribe(self.refreshRuns.bind(self));
	self.arena.runs.filter_language.subscribe(self.refreshRuns.bind(self));
	self.arena.runs.filter_problem.subscribe(self.refreshRuns.bind(self));
	self.arena.runs.filter_username.subscribe(self.refreshRuns.bind(self));
	self.arena.runs.filter_offset.subscribe(self.refreshRuns.bind(self));

	$('.clarifpager .clarifpagerprev').click(function () {
		if (self.arena.clarificationsOffset > 0) {
			self.arena.clarificationsOffset -= self.arena.clarificationsRowcount;
			if (self.arena.clarificationsOffset < 0) {
				self.arena.clarificationsOffset = 0;
			}

			self.refreshClarifications();
		}
	});

	$('.clarifpager .clarifpagernext').click(function () {
		self.arena.clarificationsOffset += self.arena.clarificationsRowcount;
		if (self.arena.clarificationsOffset < 0) {
			self.arena.clarificationsOffset = 0;
		}

		self.refreshClarifications();
	});

	$('#clarification').submit(function (e) {
		$('#clarification input').attr('disabled', 'disabled');
		omegaup.API.newClarification(
			self.arena.contestAlias,
			$('#clarification select[name="problem"]').val(),
			$('#clarification textarea[name="message"]').val(),
			function (run) {
				if (run.status != 'ok') {
					alert(run.error);
					$('#clarification input').removeAttr('disabled');
					return;
				}
				arena.hideOverlay();
				self.refreshClarifications();
				$('#clarification input').removeAttr('disabled');
			}
		);

		return false;
	});
};

omegaup.arena.ArenaAdmin.prototype.refreshRuns = function() {
	var self = this;

	var options = {
		offset: self.arena.runs.filter_offset() || 0,
		rowcount: self.arena.runs.row_count,
		verdict: self.arena.runs.filter_verdict() || undefined,
		language: self.arena.runs.filter_language() || undefined,
		username: self.arena.runs.filter_username() || undefined,
		problem_alias: self.arena.runs.filter_problem() || undefined,
		status: self.arena.runs.filter_status() || undefined
	};

	if (self.onlyProblemAlias) {
		options.show_all = true;
		options.problem_alias = self.onlyProblemAlias;
		omegaup.API.getProblemRuns(self.onlyProblemAlias, options, self.runsChanged.bind(self));
	} else if (self.arena.contestAlias === "admin") {
		omegaup.API.getRuns(options, self.runsChanged.bind(self));
	} else {
		omegaup.API.getContestRuns(self.arena.contestAlias, options, self.runsChanged.bind(self));
	}
};

omegaup.arena.ArenaAdmin.prototype.refreshClarifications = function() {
	var self = this;

	if (self.onlyProblemAlias) {
		omegaup.API.getProblemClarifications(
			self.onlyProblemAlias,
			self.arena.clarificationsOffset,
			self.arena.clarificationsRowcount,
			self.arena.clarificationsChange.bind(self.arena)
		);
	} else {
		omegaup.API.getClarifications(
			self.arena.contestAlias,
			self.arena.clarificationsOffset,
			self.arena.clarificationsRowcount,
			self.arena.clarificationsChange.bind(self.arena)
		);
	}
};

omegaup.arena.ArenaAdmin.prototype.runsChanged = function(data) {
	var self = this;

	if (data.status != 'ok') return;

	for (var i = 0; i < data.runs.length; i++) {
		self.arena.trackRun(data.runs[i]);
	}
};
