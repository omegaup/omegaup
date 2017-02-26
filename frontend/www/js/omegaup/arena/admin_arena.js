import API from '../api.js';

export default class ArenaAdmin {
  constructor(arena) {
    var self = this;

    self.arena = arena;
    self.arena.contestAdmin = true;

    self.setUpPagers();
    self.arena.runs.attach($('#runs table.runs'));
  }

  setUpPagers() {
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
            self.arena.clarificationsOffset -=
                self.arena.clarificationsRowcount;
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
      $('input', self.arena.elements.clarification)
          .attr('disabled', 'disabled');
      API.Clarification.create({
                         contest_alias: self.arena.options.contestAlias,
                         problem_alias: $('select[name="problem"]',
                                          self.arena.elements.clarification)
                                            .val(),
                         message: $('textarea[name="message"]',
                                    self.arena.elements.clarification)
                                      .val(),
                       })
          .then(function(run) {
            self.arena.hideOverlay();
            self.refreshClarifications();
          })
          .fail(function(run) { alert(run.error); })
          .always(function() {
            $('input', self.arena.elements.clarification)
                .removeAttr('disabled');
          });

      return false;
    });
  }

  refreshRuns() {
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
      API.Problem.runs(options)
          .then(self.runsChanged.bind(self))
          .fail(UI.apiError);
    } else if (self.arena.options.contestAlias === 'admin') {
      API.getRuns(options, self.runsChanged.bind(self));
    } else {
      options.contest_alias = self.arena.options.contestAlias;
      API.Contest.runs(options)
          .then(self.runsChanged.bind(self))
          .fail(UI.apiError);
    }
  }

  refreshClarifications() {
    var self = this;

    if (self.arena.options.onlyProblemAlias) {
      API.getProblemClarifications(
          self.arena.options.onlyProblemAlias, self.arena.clarificationsOffset,
          self.arena.clarificationsRowcount,
          self.arena.clarificationsChange.bind(self.arena));
    } else {
      self.arena.refreshClarifications();
    }
  }

  runsChanged(data) {
    var self = this;

    if (data.status != 'ok') return;

    for (var i = 0; i < data.runs.length; i++) {
      self.arena.trackRun(data.runs[i]);
    }
  }
}
