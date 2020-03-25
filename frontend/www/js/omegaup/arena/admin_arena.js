import API from '../api.js';
import UI from '../ui.js';

export default class ArenaAdmin {
  constructor(arena) {
    var self = this;

    self.arena = arena;
    self.arena.problemsetAdmin = true;

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

    $('.clarifpager .clarifpagerprev').on('click', function() {
      if (self.arena.clarificationsOffset > 0) {
        self.arena.clarificationsOffset -= self.arena.clarificationsRowcount;
        if (self.arena.clarificationsOffset < 0) {
          self.arena.clarificationsOffset = 0;
        }

        self.refreshClarifications();
      }
    });

    $('.clarifpager .clarifpagernext').on('click', function() {
      self.arena.clarificationsOffset += self.arena.clarificationsRowcount;
      if (self.arena.clarificationsOffset < 0) {
        self.arena.clarificationsOffset = 0;
      }

      self.refreshClarifications();
    });

    self.arena.elements.clarification.on('submit', function(e) {
      $('input', self.arena.elements.clarification).attr(
        'disabled',
        'disabled',
      );
      API.Clarification.create({
        contest_alias: self.arena.options.contestAlias,
        problem_alias: $(
          'select[name="problem"]',
          self.arena.elements.clarification,
        ).val(),
        username: $(
          'select[name="user"]',
          self.arena.elements.clarification,
        ).val(),
        message: $(
          'textarea[name="message"]',
          self.arena.elements.clarification,
        ).val(),
      })
        .then(function(run) {
          self.arena.hideOverlay();
          self.refreshClarifications();
        })
        .catch(function(run) {
          alert(run.error);
        })
        .finally(function() {
          $('input', self.arena.elements.clarification).prop('disabled', false);
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
      status: self.arena.runs.filter_status() || undefined,
    };

    if (self.arena.options.onlyProblemAlias) {
      options.show_all = true;
      options.problem_alias = self.arena.options.onlyProblemAlias;
      API.Problem.runs(options)
        .then(self.runsChanged.bind(self))
        .catch(UI.apiError);
    } else if (self.arena.options.contestAlias === 'admin') {
      API.Run.list(options)
        .then(self.runsChanged.bind(self))
        .catch(UI.ignoreError);
    } else if (self.arena.options.contestAlias != null) {
      options.contest_alias = self.arena.options.contestAlias;
      API.Contest.runs(options)
        .then(self.runsChanged.bind(self))
        .catch(UI.apiError);
    } else {
      options.course_alias = self.arena.options.courseAlias;
      options.assignment_alias = self.arena.options.assignmentAlias;
      API.Course.runs(options)
        .then(self.runsChanged.bind(self))
        .catch(UI.apiError);
    }
  }

  refreshClarifications() {
    var self = this;

    if (self.arena.options.onlyProblemAlias) {
      API.Problem.clarifications({
        problem_alias: self.arena.options.onlyProblemAlias,
        offset: self.arena.clarificationsOffset,
        rowcount: self.arena.clarificationsRowcount,
      })
        .then(self.arena.clarificationsChange.bind(self.arena))
        .catch(UI.apiError);
    } else {
      self.arena.refreshClarifications();
    }
  }

  runsChanged(data) {
    var self = this;

    for (var i = 0; i < data.runs.length; i++) {
      self.arena.trackRun(data.runs[i]);
    }
  }
}
