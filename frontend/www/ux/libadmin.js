function ArenaAdmin(arena, onlyProblemAlias) {
	this.arena = arena;
	this.arena.admin = true;
	this.onlyProblemAlias = onlyProblemAlias;
	this.runsProblemAlias = '';
	this.runsOffset = 0;
	this.runsRowcount = 100;

	this.setUpPagers();
}

ArenaAdmin.prototype.setUpPagers = function() {
	var self = this;

	$('.runspager .runspagerprev').click(function () {
		if (self.runsOffset > 0) {
			self.runsOffset -= self.runsRowcount;
			if (self.runsOffset < 0) {
				self.runsOffset = 0;
			}
			
			// Refresh with previous page
			self.refreshRuns();
		}
	});
	
	$('.runspager .runspagernext').click(function () {
		self.runsOffset += self.runsRowcount;
		if (self.runsOffset < 0) {
			self.runsOffset = 0;
		}
		
		// Refresh with previous page
		self.refreshRuns();
	});
	
	$("#runsusername").typeahead({
		ajax: "/api/user/list/",
		display: 'label',
		val: 'label',
		minLength: 2,
		itemSelected: self.refreshRuns.bind(self)
	});
	
	$('#runsusername-clear').click(function() {
		$("#runsusername").val('');
		self.refreshRuns();
	});
	
	if (self.arena.contestAlias === "admin") {
		$("#runsproblem").typeahead({
			ajax: { 
				url: "/api/problem/list/",
				preProcess: function(data) { 
					return data["results"];
				}
			},
			display: 'title',
			val: 'alias',
			minLength: 2,
			itemSelected: function(item, val, text) {
				self.runsProblemAlias = val;
				self.refreshRuns();
			}
		});

		$('#runsproblem-clear').click(function() {
			self.runsProblemAlias = '';
			$("#runsproblem").val('');
			self.refreshRuns();
		});
	}
	
	$('select.runsverdict, select.runsstatus, select.runsproblem, select.runslang')
		.change(self.refreshRuns.bind(self));
	
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
		omegaup.newClarification(
			self.arena.contestAlias,
			$('#clarification select[name="problem"]').val(),
			$('#clarification textarea[name="message"]').val(),
			function (run) {
				if (run.status != 'ok') {
					alert(run.error);
					$('#clarification input').removeAttr('disabled');
					return;
				}
				$('#overlay').hide();
				window.location.hash = window.location.hash.substring(0,
						window.location.hash.lastIndexOf('/'));
				self.refreshClarifications();
				$('#clarification input').removeAttr('disabled');
			}
		);

		return false;
	});
};

ArenaAdmin.prototype.refreshRuns = function() {
	var self = this;

	var options = {
		offset: self.runsOffset, 
		rowcount: self.runsRowcount
	};
	
	if ($('select.runsverdict option:selected').val()) {
		options.verdict = $('select.runsverdict option:selected').val();
	}
	
	if ($('select.runsstatus option:selected').val()) {
		options.status = $('select.runsstatus option:selected').val();
	}
	
	if ($('select.runslang option:selected').val()) {
		options.language = $('select.runslang option:selected').val();
	}
	
	if (self.runsProblemAlias) {
		options.problem_alias = self.runsProblemAlias;
	}
	
	if ($('#runsusername').val()) {
		options.username = $('#runsusername').val();
	}

	if (self.onlyProblemAlias) {
		options.show_all = true;
		omegaup.getProblemRuns(self.onlyProblemAlias, options, self.runsChanged.bind(self));
	} else if (self.arena.contestAlias === "admin") {
		omegaup.getRuns(options, self.runsChanged.bind(self));
	} else {
		omegaup.getContestRuns(self.arena.contestAlias, options, self.runsChanged.bind(self));
	}
};

ArenaAdmin.prototype.refreshClarifications = function() {
	var self = this;

	if (self.onlyProblemAlias) {
		omegaup.getProblemClarifications(
			self.onlyProblemAlias,
			self.arena.clarificationsOffset,
			self.arena.clarificationsRowcount,
			self.arena.clarificationsChange.bind(self.arena)
		);
	} else {
		omegaup.getClarifications(
			self.arena.contestAlias,
			self.arena.clarificationsOffset,
			self.arena.clarificationsRowcount,
			self.arena.clarificationsChange.bind(self.arena)
		);
	}
};

ArenaAdmin.prototype.runsChanged = function(data) {
	var self = this;
	$('#runs .runs .run-list .added').remove();

	for (var i = 0; i < data.runs.length; i++) {
		var run = data.runs[i];

		var r = self.arena.createAdminRun(run);
		self.arena.displayRun(run, r);
		$('#runs .runs > tbody').append(r);
	}
};
