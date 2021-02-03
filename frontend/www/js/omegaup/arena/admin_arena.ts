import Vue from 'vue';

import * as api from '../api';
import { types } from '../api_types';
import T from '../lang';
import arena_Runs from '../components/arena/Runs.vue';
import arena_Runsv2 from '../components/arena/Runsv2.vue';
import * as ui from '../ui';
import * as time from '../time';

import { Arena } from './arena';
import { runsStore } from './runsStore';

export default class ArenaAdmin {
  arena: Arena;

  runsList: Vue;

  constructor(arena: Arena) {
    // eslint-disable-next-line @typescript-eslint/no-this-alias
    const self = this;

    this.arena = arena;
    this.arena.problemsetAdmin = true;
    const globalRuns = this.arena.options.contestAlias === 'admin';

    this.setUpPagers();
    this.runsList = new Vue({
      el: globalRuns ? '#main-container' : '#runs table.runs',
      components: {
        'omegaup-arena-runs': globalRuns ? arena_Runsv2 : arena_Runs,
      },
      render: function (createElement) {
        return createElement('omegaup-arena-runs', {
          props: {
            contestAlias: arena.options.contestAlias,
            runs: runsStore.state.runs,
            showContest: arena.options.contestAlias == 'admin',
            showProblem: true,
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
    });
  }

  setUpPagers(): void {
    document
      .querySelector('.clarifpager .clarifpagerprev')
      ?.addEventListener('click', () => {
        if (this.arena.clarificationsOffset > 0) {
          this.arena.clarificationsOffset -= this.arena.clarificationsRowcount;
          if (this.arena.clarificationsOffset < 0) {
            this.arena.clarificationsOffset = 0;
          }

          this.refreshClarifications();
        }
      });

    document
      .querySelector('.clarifpager .clarifpagernext')
      ?.addEventListener('click', () => {
        this.arena.clarificationsOffset += this.arena.clarificationsRowcount;
        if (this.arena.clarificationsOffset < 0) {
          this.arena.clarificationsOffset = 0;
        }

        this.refreshClarifications();
      });

    this.arena.elements.clarification?.addEventListener('submit', () => {
      const clarificationElement = this.arena.elements.clarification;
      if (clarificationElement === null) {
        return;
      }
      clarificationElement
        .querySelectorAll('input')
        .forEach((input) => input.setAttribute('disabled', 'disabled'));
      api.Clarification.create({
        contest_alias: this.arena.options.contestAlias,
        problem_alias: (clarificationElement.querySelector(
          'select[name="problem"]',
        ) as HTMLInputElement).value,
        username: (clarificationElement.querySelector(
          'select[name="user"]',
        ) as HTMLInputElement).value,
        message: (clarificationElement.querySelector(
          'textarea[name="message"]',
        ) as HTMLInputElement).value,
      })
        .then(() => {
          this.arena.hideOverlay();
          this.refreshClarifications();
        })
        .catch((e: { status: string; error: Error }) => {
          alert(e.error);
        })
        .finally(() => {
          clarificationElement
            .querySelectorAll('input')
            .forEach((input) => input.removeAttribute('disabled'));
        });

      return false;
    });
  }

  refreshRuns(): void {
    const runsListComponent = this.runsList.$children[0] as arena_Runs;

    const options = {
      assignment_alias: undefined as string | undefined,
      contest_alias: undefined as string | undefined,
      course_alias: undefined as string | undefined,
      problem_alias: runsListComponent.filterProblem || undefined,
      offset: runsListComponent.filterOffset * runsListComponent.rowCount,
      rowcount: runsListComponent.rowCount,
      verdict: runsListComponent.filterVerdict || undefined,
      language: runsListComponent.filterLanguage || undefined,
      username: runsListComponent.filterUsername || undefined,
      show_all: undefined as boolean | undefined,
      status: runsListComponent.filterStatus || undefined,
    };

    if (this.arena.options.contestAlias === 'admin') {
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
    this.arena.refreshClarifications();
  }

  runsChanged(data: { runs: types.Run[] }): void {
    runsStore.commit('clear');
    for (const run of data.runs) {
      this.arena.trackRun(run);
    }
  }
}
