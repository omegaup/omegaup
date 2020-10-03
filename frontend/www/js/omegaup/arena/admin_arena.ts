import Vue from 'vue';

import * as api from '../api';
import { types } from '../api_types';
import T from '../lang';
import arena_Runs from '../components/arena/Runs.vue';
import * as ui from '../ui';
import * as time from '../time';

import { Arena, runsStore } from './arena';

export default class ArenaAdmin {
  arena: Arena;

  runsList: Vue;

  constructor(arena: Arena) {
    const self = this;

    this.arena = arena;
    this.arena.problemsetAdmin = true;
    const globalRuns = this.arena.options.contestAlias === 'admin';

    this.setUpPagers();
    this.runsList = new Vue({
      el: globalRuns ? '#main-container' : '#runs table.runs',
      render: function (createElement) {
        return createElement('omegaup-arena-runs', {
          props: {
            contestAlias: arena.options.contestAlias,
            runs: runsStore.state.runs,
            showContest: arena.options.contestAlias == 'admin',
            showProblem: !arena.options.isOnlyProblem,
            showDetails: true,
            showDisqualify: true,
            showPager: true,
            showRejudge: true,
            showUser: true,
            problemsetProblems: Object.values(arena.problems),
            globalRuns: globalRuns,
          },
          on: {
            details: (run: types.Run) => {
              window.location.hash += `/show-run:${run.guid}`;
            },
            disqualify: (run: types.Run) => {
              if (!window.confirm(T.runDisqualifyConfirm)) {
                return;
              }
              api.Run.disqualify({ run_alias: run.guid })
                .then(() => {
                  run.type = 'disqualified';
                  arena.updateRunFallback(run.guid);
                })
                .catch(ui.ignoreError);
            },
            'filter-changed': () => {
              self.refreshRuns();
            },
            rejudge: (run: types.Run) => {
              api.Run.rejudge({ run_alias: run.guid, debug: false })
                .then(() => {
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

  setUpPagers(): void {
    $('.clarifpager .clarifpagerprev').on('click', () => {
      if (this.arena.clarificationsOffset > 0) {
        this.arena.clarificationsOffset -= this.arena.clarificationsRowcount;
        if (this.arena.clarificationsOffset < 0) {
          this.arena.clarificationsOffset = 0;
        }

        this.refreshClarifications();
      }
    });

    $('.clarifpager .clarifpagernext').on('click', () => {
      this.arena.clarificationsOffset += this.arena.clarificationsRowcount;
      if (this.arena.clarificationsOffset < 0) {
        this.arena.clarificationsOffset = 0;
      }

      this.refreshClarifications();
    });

    this.arena.elements.clarification.on('submit', () => {
      $('input', this.arena.elements.clarification).attr(
        'disabled',
        'disabled',
      );
      api.Clarification.create({
        contest_alias: this.arena.options.contestAlias,
        problem_alias: $(
          'select[name="problem"]',
          this.arena.elements.clarification,
        ).val(),
        username: $(
          'select[name="user"]',
          this.arena.elements.clarification,
        ).val(),
        message: $(
          'textarea[name="message"]',
          this.arena.elements.clarification,
        ).val(),
      })
        .then(() => {
          this.arena.hideOverlay();
          this.refreshClarifications();
        })
        .catch((e: { status: string; error: Error }) => {
          alert(e.error);
        })
        .finally(() => {
          $('input', this.arena.elements.clarification).prop('disabled', false);
        });

      return false;
    });
  }

  refreshRuns(): void {
    const runsListComponent = <arena_Runs>this.runsList.$children[0];

    const options = {
      assignment_alias: <string | undefined>undefined,
      contest_alias: <string | undefined>undefined,
      course_alias: <string | undefined>undefined,
      problem_alias: runsListComponent.filterProblem || undefined,
      offset: runsListComponent.filterOffset * runsListComponent.rowCount,
      rowcount: runsListComponent.rowCount,
      verdict: runsListComponent.filterVerdict || undefined,
      language: runsListComponent.filterLanguage || undefined,
      username: runsListComponent.filterUsername || undefined,
      show_all: <boolean | undefined>undefined,
      status: runsListComponent.filterStatus || undefined,
    };

    if (this.arena.options.onlyProblemAlias) {
      options.show_all = true;
      options.problem_alias = this.arena.options.onlyProblemAlias;
      api.Problem.runs(options)
        .then(time.remoteTimeAdapter)
        .then((response) => this.runsChanged(response))
        .catch(ui.apiError);
    } else if (this.arena.options.contestAlias === 'admin') {
      api.Run.list(options)
        .then(time.remoteTimeAdapter)
        .then((response) => this.runsChanged(response))
        .catch(ui.ignoreError);
    } else if (this.arena.options.contestAlias != null) {
      options.contest_alias = this.arena.options.contestAlias;
      api.Contest.runs(options)
        .then(time.remoteTimeAdapter)
        .then((response) => this.runsChanged(response))
        .catch(ui.apiError);
    } else {
      options.course_alias = this.arena.options.courseAlias || undefined;
      options.assignment_alias =
        this.arena.options.assignmentAlias || undefined;
      api.Course.runs(options)
        .then(time.remoteTimeAdapter)
        .then((response) => this.runsChanged(response))
        .catch(ui.apiError);
    }
  }

  refreshClarifications(): void {
    if (this.arena.options.onlyProblemAlias) {
      api.Problem.clarifications({
        problem_alias: this.arena.options.onlyProblemAlias,
        offset: this.arena.clarificationsOffset,
        rowcount: this.arena.clarificationsRowcount,
      })
        .then(time.remoteTimeAdapter)
        .then((response) =>
          this.arena.clarificationsChange(response.clarifications),
        )
        .catch(ui.apiError);
    } else {
      this.arena.refreshClarifications();
    }
  }

  runsChanged(data: { runs: types.Run[] }): void {
    runsStore.commit('clear');
    for (const run of data.runs) {
      this.arena.trackRun(run);
    }
  }
}
