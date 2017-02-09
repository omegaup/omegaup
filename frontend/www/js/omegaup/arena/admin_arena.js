var omegaup = typeof global === 'undefined' ?
                  (window.omegaup = window.omegaup || {}) :
                  (global.omegaup = global.omegaup || {});

omegaup.arena = omegaup.arena || {};

omegaup.arena.ArenaAdmin = function(arena) {
  var self = this;

  self.arena = arena;
  self.arena.contestAdmin = true;

  self.setUpPagers();
  self.arena.runs.attach($('#runs table.runs'));
};

omegaup.arena.ArenaAdmin.prototype.setUpPagers = function() {
  var self = this;

  self.arena.runs.filter_verdict.subscribe(self.refreshRuns.bind(self));
  self.arena.runs.filter_status.subscribe(self.refreshRuns.bind(self));
  self.arena.runs.filter_language.subscribe(self.refreshRuns.bind(self));
  self.arena.runs.filter_problem.subscribe(self.refreshRuns.bind(self));
  self.arena.runs.filter_username.subscribe(self.refreshRuns.bind(self));
  self.arena.runs.filter_offset.subscribe(self.refreshRuns.bind(self));

  $('.clarifpager .clarifpagerprev')
      .click(function() {
        if (self.arena.clarificationsOffset > 0) {
          self.arena.clarificationsOffset -= self.arena.clarificationsRowcount;
          if (self.arena.clarificationsOffset < 0) {
            self.arena.clarificationsOffset = 0;
          }

          self.refreshClarifications();
        }
      });

  $('.clarifpager .clarifpagernext')
      .click(function() {
        self.arena.clarificationsOffset += self.arena.clarificationsRowcount;
        if (self.arena.clarificationsOffset < 0) {
          self.arena.clarificationsOffset = 0;
        }

        self.refreshClarifications();
      });

  self.arena.elements.clarification.submit(function(e) {
    $('input', self.arena.elements.clarification).attr('disabled', 'disabled');
    omegaup.API.newClarification(
        self.arena.options.contestAlias,
        $('select[name="problem"]', self.arena.elements.clarification).val(),
        $('textarea[name="message"]', self.arena.elements.clarification).val(),
        function(run) {
          if (run.status != 'ok') {
            alert(run.error);
            $('input', self.arena.elements.clarification)
                .removeAttr('disabled');
            return;
          }
          self.arena.hideOverlay();
          self.refreshClarifications();
          $('input', self.arena.elements.clarification).removeAttr('disabled');
        });

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

  if (self.arena.options.onlyProblemAlias) {
    options.show_all = true;
    options.problem_alias = self.arena.options.onlyProblemAlias;
    omegaup.API.getProblemRuns(options).then(self.runsChanged.bind(self));
  } else if (self.arena.options.contestAlias === 'admin') {
    omegaup.API.getRuns(options, self.runsChanged.bind(self));
  } else {
    options.contest_alias = self.arena.options.contestAlias;
    omegaup.API.getContestRuns(options).then(self.runsChanged.bind(self));
  }
};

omegaup.arena.ArenaAdmin.prototype.refreshClarifications = function() {
  var self = this;

  if (self.arena.options.onlyProblemAlias) {
    omegaup.API.getProblemClarifications(
        self.arena.options.onlyProblemAlias, self.arena.clarificationsOffset,
        self.arena.clarificationsRowcount,
        self.arena.clarificationsChange.bind(self.arena));
  } else {
    omegaup.API.getClarifications(
        self.arena.options.contestAlias, self.arena.clarificationsOffset,
        self.arena.clarificationsRowcount,
        self.arena.clarificationsChange.bind(self.arena));
  }
};

omegaup.arena.ArenaAdmin.prototype.runsChanged = function(data) {
  var self = this;

  if (data.status != 'ok') return;

  for (var i = 0; i < data.runs.length; i++) {
    self.arena.trackRun(data.runs[i]);
  }
};
