import Vue from 'vue';

import * as api from '../api_transitional';
import arena_Runs from '../components/arena/Runs.vue';
import * as ui from '../ui';
import * as time from '../time';

import { runsStore } from './arena_transitional';

export default class ArenaAdmin {
  constructor(arena) {
    var self = this;

    self.arena = arena;
    self.arena.problemsetAdmin = true;

    self.setUpPagers();
    self.runsList = new Vue({
      el: '#runs table.runs',
      render: function(createElement) {
        return createElement('omegaup-arena-runs', {
          props: {
            runs: runsStore.state.runs,
            showContest: self.arena.options.contestAlias == 'admin',
            showProblem: !arena.options.isOnlyProblem,
            showDetails: true,
            showDisqualify: true,
            showPager: true,
            showRejudge: true,
            showUser: true,
          },
          on: {
            details: run => {
              window.location.hash += `/show-run:${run.guid}`;
            },
            disqualify: run => {
              api.Run.disqualify({ run_alias: run.guid })
                .then(data => {
                  run.type = 'disqualified';
                  self.arena.updateRunFallback(run.guid);
                })
                .catch(ui.ignoreError);
            },
            'filter-changed': () => {
              self.refreshRuns();
            },
            rejudge: run => {
              api.Run.rejudge({ run_alias: run.guid, debug: false })
                .then(data => {
                  run.status = 'rejudging';
                  self.arena.updateRunFallback(run.guid);
                })
                .catch(ui.ignoreError);
            },
          },
        });
      },
      components: { 'omegaup-arena-runs': arena_Runs },
    });
  }

  setUpPagers() {
    var self = this;

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
      api.Clarification.create({
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
    const runsListComponent = self.runsList.$children[0];

    var options = {
      offset: runsListComponent.filterOffset * runsListComponent.rowCount,
      rowcount: runsListComponent.rowCount,
      verdict: runsListComponent.filterVerdict || undefined,
      language: runsListComponent.filterLanguage || undefined,
      username: runsListComponent.filterUsername || undefined,
      problem_alias: runsListComponent.filterProblem || undefined,
      status: runsListComponent.filterStatus || undefined,
    };

    if (self.arena.options.onlyProblemAlias) {
      options.show_all = true;
      options.problem_alias = self.arena.options.onlyProblemAlias;
      api.Problem.runs(options)
        .then(time.remoteTimeAdapter)
        .then(self.runsChanged.bind(self))
        .catch(ui.apiError);
    } else if (self.arena.options.contestAlias === 'admin') {
      api.Run.list(options)
        .then(time.remoteTimeAdapter)
        .then(self.runsChanged.bind(self))
        .catch(ui.ignoreError);
    } else if (self.arena.options.contestAlias != null) {
      options.contest_alias = self.arena.options.contestAlias;
      api.Contest.runs(options)
        .then(time.remoteTimeAdapter)
        .then(self.runsChanged.bind(self))
        .catch(ui.apiError);
    } else {
      options.course_alias = self.arena.options.courseAlias;
      options.assignment_alias = self.arena.options.assignmentAlias;
      api.Course.runs(options)
        .then(time.remoteTimeAdapter)
        .then(self.runsChanged.bind(self))
        .catch(ui.apiError);
    }
  }

  refreshClarifications() {
    var self = this;

    if (self.arena.options.onlyProblemAlias) {
      api.Problem.clarifications({
        problem_alias: self.arena.options.onlyProblemAlias,
        offset: self.arena.clarificationsOffset,
        rowcount: self.arena.clarificationsRowcount,
      })
        .then(time.remoteTimeAdapter)
        .then(self.arena.clarificationsChange.bind(self.arena))
        .catch(ui.apiError);
    } else {
      self.arena.refreshClarifications();
    }
  }

  runsChanged(data) {
    var self = this;

    runsStore.commit('clear');
    for (var i = 0; i < data.runs.length; i++) {
      self.arena.trackRun(data.runs[i]);
    }
  }
}
