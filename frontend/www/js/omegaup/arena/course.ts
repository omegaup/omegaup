import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as time from '../time';
import * as ui from '../ui';
import { getOptionsFromLocation } from './location';

import problemsStore from './problemStore';
import { myRunsStore } from '../arena/runsStore';

import Vue from 'vue';
import arena_Course, { ActiveProblem } from '../components/arena/Course.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      popUpDisplayed: PopupDisplayed.None,
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.currentAssignment
        .problems as types.NavbarProblemsetProblem[],
      showNewClarificationPopup: false,
      guid: null as null | string,
    }),
    methods: {
      getMaxScore: (
        runs: types.Run[],
        alias: string,
        previousScore: number,
      ): number => {
        let maxScore = previousScore;
        for (const run of runs) {
          if (alias != run.alias) {
            continue;
          }
          maxScore = Math.max(maxScore, run.contest_score || 0);
        }
        return maxScore;
      },
    },
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          course: payload.courseDetails,
          currentAssignment: payload.currentAssignment,
          popupDisplayed: this.popUpDisplayed,
          problemInfo: this.problemInfo,
          problem: this.problem,
          problems: this.problems,
          showNewClarificationPopup: this.showNewClarificationPopup,
          showRanking: payload.showRanking,
          shouldShowFirstAssociatedIdentityRunWarning:
            payload.shouldShowFirstAssociatedIdentityRunWarning,
          activeTab,
          guid: this.guid,
        },
        on: {
          'navigate-to-problem': (request: ActiveProblem) => {
            if (
              Object.prototype.hasOwnProperty.call(
                problemsStore.state.problems,
                request.problem.alias,
              )
            ) {
              arenaCourse.problemInfo =
                problemsStore.state.problems[request.problem.alias];
              window.location.hash = `#problems/${request.problem.alias}`;
              return;
            }
            api.Problem.details({
              problem_alias: request.problem.alias,
              prevent_problemset_open: false,
            })
              .then((problemInfo) => {
                for (const run of problemInfo.runs ?? []) {
                  trackRun(run);
                }
                const currentProblem = payload.currentAssignment.problems.find(
                  ({ alias }) => alias === problemInfo.alias,
                );
                problemInfo.title = currentProblem?.text ?? '';
                arenaCourse.problemInfo = problemInfo;
                request.problem.alias = problemInfo.alias;
                request.runs = myRunsStore.state.runs;
                request.problem.bestScore = this.getMaxScore(
                  request.runs,
                  problemInfo.alias,
                  0,
                );
                problemsStore.commit('addProblem', problemInfo);
                if (arenaCourse.popUpDisplayed === PopupDisplayed.RunSubmit) {
                  window.location.hash = `#problems/${request.problem.alias}/new-run`;
                  return;
                }
                window.location.hash = `#problems/${request.problem.alias}`;
              })
              .catch(() => {
                ui.dismissNotifications();
                window.location.hash = '#problems';
                arenaCourse.problem = null;
              });
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));

  function trackRun(run: types.Run): void {
    myRunsStore.commit('addRun', run);
  }
});
