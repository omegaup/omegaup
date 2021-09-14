import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import Vue from 'vue';
import arena_Course from '../components/arena/Course.vue';
import { getOptionsFromLocation } from './location';
import problemsStore from './problemStore';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
  updateRunFallback,
} from './submissions';
import { navigateToProblem, NavigationType } from './navigation';
import {
  CourseClarificationType,
  refreshCourseClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import { myRunsStore, runsStore } from './runsStore';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();

  const commonPayload = types.payloadParsers.CommonPayload();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const courseAdmin = Boolean(
    payload.courseDetails.is_admin || payload.courseDetails.is_curator,
  );
  const activeTab = getSelectedValidTab(locationHash[0], courseAdmin);
  if (activeTab !== locationHash[0]) {
    window.location.hash = activeTab;
  }
  const {
    guid,
    popupDisplayed,
    problem,
    problemAlias,
    showNewClarificationPopup,
  } = getOptionsFromLocation(window.location.hash);
  const runDetailsResponse: { runDetails: null | types.RunDetails } = {
    runDetails: null,
  };
  const problemDetailsResponse: { problemInfo: null | types.ProblemDetails } = {
    problemInfo: null,
  };
  if (problemAlias) {
    await getProblemDetails({
      problemAlias,
      problems: payload.currentAssignment.problems,
      response: problemDetailsResponse,
    });
    if (guid) {
      await getRunDetails({ guid, response: runDetailsResponse });
    }
  }

  trackClarifications(payload.courseDetails.clarifications);

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      problemInfo: problemDetailsResponse.problemInfo,
      problem,
      problems: payload.currentAssignment.problems,
      popupDisplayed,
      showNewClarificationPopup,
      guid,
      problemAlias,
      searchResultUsers: [] as types.ListItem[],
      runDetailsData: runDetailsResponse.runDetails,
      nextSubmissionTimestamp:
        problemDetailsResponse.problemInfo?.nextSubmissionTimestamp,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          clarifications: clarificationStore.state.clarifications,
          course: payload.courseDetails,
          currentAssignment: payload.currentAssignment,
          popupDisplayed: this.popupDisplayed,
          problemInfo: this.problemInfo,
          problem: this.problem,
          problemAlias: this.problemAlias,
          problems: this.problems,
          scoreboard: payload.scoreboard,
          showNewClarificationPopup: this.showNewClarificationPopup,
          showRanking: payload.showRanking,
          activeTab,
          guid: this.guid,
          runs: myRunsStore.state.runs,
          allRuns: runsStore.state.runs,
          searchResultUsers: this.searchResultUsers,
          runDetailsData: this.runDetailsData,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
        },
        on: {
          'navigate-to-problem': ({
            problem,
          }: {
            problem: types.NavbarProblemsetProblem;
          }) => {
            navigateToProblem({
              type: NavigationType.ForSingleProblemOrCourse,
              problem,
              target: arenaCourse,
              problems: this.problems,
            });
          },
          'show-run': async (source: SubmissionRequest) => {
            await getRunDetails({
              guid: source.request.guid,
              response: runDetailsResponse,
            });
            const runDetails = runDetailsResponse.runDetails;
            if (runDetails == null) {
              return;
            }
            showSubmission({ source, runDetails });
          },
          'submit-run': ({
            problem,
            code,
            language,
            target,
          }: {
            code: string;
            language: string;
            problem: types.NavbarProblemsetProblem;
            target: Vue & { currentNextSubmissionTimestamp: Date };
          }) => {
            api.Run.create({
              problem_alias: problem.alias,
              language: language,
              source: code,
            })
              .then(time.remoteTimeAdapter)
              .then((response) => {
                submitRun({
                  guid: response.guid,
                  submitDelay: response.submit_delay,
                  language,
                  username: commonPayload.currentUsername,
                  classname: commonPayload.userClassname,
                  problemAlias: problem.alias,
                });
                target.currentNextSubmissionTimestamp =
                  response.nextSubmissionTimestamp;
              })
              .catch((run) => {
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
          },
          'new-clarification': ({
            clarification,
            clearForm,
          }: {
            clarification: types.Clarification;
            clearForm: () => void;
          }) => {
            if (!clarification) {
              return;
            }
            api.Clarification.create({
              course_alias: payload.courseDetails.alias,
              assignment_alias: payload.currentAssignment.alias,
              problem_alias: clarification.problem_alias,
              username: clarification.author,
              message: clarification.message,
            })
              .then(() => {
                clearForm();
                refreshCourseClarifications({
                  courseAlias: payload.courseDetails.alias,
                  type: CourseClarificationType.AllProblems,
                });
              })
              .catch(ui.apiError);
          },
          'clarification-response': (clarification: types.Clarification) => {
            api.Clarification.update(clarification)
              .then(() => {
                refreshCourseClarifications({
                  courseAlias: payload.courseDetails.alias,
                  type: CourseClarificationType.AllProblems,
                });
              })
              .catch(ui.apiError);
          },
          // TODO: Implement the API to search users from course, for
          // 'update-search-result-users-contest';
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then(() => {
                run.status = 'rejudging';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          disqualify: (run: types.Run) => {
            if (!window.confirm(T.runDisqualifyConfirm)) {
              return;
            }
            api.Run.disqualify({ run_alias: run.guid })
              .then(() => {
                run.type = 'disqualified';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          'reset-hash': (request: {
            selectedTab: string;
            alias: null | string;
          }) => {
            if (!request.alias) {
              window.location.replace(`#${request.selectedTab}`);
              return;
            }
            window.location.replace(`#${request.selectedTab}/${request.alias}`);
          },
        },
      });
    },
  });

  async function getRunDetails({
    guid,
    response,
  }: {
    guid: string;
    response: { runDetails: null | types.RunDetails };
  }): Promise<void> {
    return api.Run.details({ run_alias: guid })
      .then((runDetails) => {
        response.runDetails = runDetails;
      })
      .catch((error) => {
        ui.apiError(error);
      });
  }

  async function getProblemDetails({
    problemAlias,
    problems,
    response,
  }: {
    problemAlias: string;
    problems: types.NavbarProblemsetProblem[];
    response: { problemInfo: null | types.ProblemInfo };
  }): Promise<void> {
    return api.Problem.details({
      problem_alias: problemAlias,
      prevent_problemset_open: false,
    })
      .then((problemInfo) => {
        for (const run of problemInfo.runs ?? []) {
          trackRun({ run });
        }
        const currentProblem = problems?.find(
          ({ alias }: { alias: string }) => alias === problemInfo.alias,
        );
        problemInfo.title = currentProblem?.text ?? '';
        response.problemInfo = problemInfo;
        problemsStore.commit('addProblem', problemInfo);
      })
      .catch(() => {
        ui.dismissNotifications();
      });
  }

  function getSelectedValidTab(tab: string, isAdmin: boolean): string {
    const validTabs = ['problems', 'ranking', 'runs', 'clarifications'];
    const defaultTab = 'problems';
    if (tab === 'runs' && !isAdmin) return defaultTab;
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
  }

  if (payload.currentAssignment.runs) {
    for (const run of payload.currentAssignment.runs) {
      trackRun({ run });
    }
  }

  /*if (locationHash[1] && locationHash[1].includes('show-run:')) {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    arenaCourse.guid = showRunMatch?.[1] ?? null;
    arenaCourse.popupDisplayed = PopupDisplayed.RunDetails;
  }*/

  setInterval(() => {
    refreshCourseClarifications({
      type: CourseClarificationType.AllProblems,
      courseAlias: payload.courseDetails.alias,
    });
  }, 5 * 60 * 1000);
});
