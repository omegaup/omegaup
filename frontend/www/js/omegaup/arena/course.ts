import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';

import Vue from 'vue';
import arena_Course from '../components/arena/Course.vue';
import { getOptionsFromLocation } from './location';
import { PopupDisplayed } from '../components/problem/Details.vue';
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
import { setLocationHref } from '../location';

OmegaUp.on('ready', () => {
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
  const popupDisplayed = payload.runDetails
    ? PopupDisplayed.RunDetails
    : PopupDisplayed.None;

  trackClarifications(payload.courseDetails.clarifications);

  if (payload.problemDetails?.runs) {
    for (const run of payload.problemDetails.runs ?? []) {
      trackRun({ run });
    }
  }
  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      popupDisplayed,
      problemInfo: payload.problemDetails as types.ProblemInfo | null,
      problem: payload.problem as types.NavbarProblemsetProblem | null,
      problems: payload.currentAssignment
        .problems as types.NavbarProblemsetProblem[],
      showNewClarificationPopup: false,
      guid: payload.guid as null | string,
      problemAlias: payload.problemAlias as null | string,
      searchResultUsers: [] as types.ListItem[],
      runDetailsData: payload.runDetails as types.RunDetails | null,
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
          'show-run': (source: SubmissionRequest) => {
            api.Run.details({ run_alias: source.request.guid })
              .then((runDetails) => {
                showSubmission({ source, runDetails });
                this.popupDisplayed = PopupDisplayed.RunDetails;
              })
              .catch((error) => {
                ui.apiError(error);
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          'submit-run': ({
            problem,
            code,
            language,
          }: {
            code: string;
            language: string;
            problem: types.NavbarProblemsetProblem;
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
          'reset-url': (request: { selectedTab: string; alias: string }) => {
            this.popupDisplayed = PopupDisplayed.None;
            setLocationHref({
              url: window.location.pathname,
              problemAlias: request.alias,
            });
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  if (popupDisplayed === PopupDisplayed.None) {
    Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));
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

  if (locationHash[1] && locationHash[1].includes('show-run:')) {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    arenaCourse.guid = showRunMatch?.[1] ?? null;
    arenaCourse.popupDisplayed = PopupDisplayed.RunDetails;
  }

  setInterval(() => {
    refreshCourseClarifications({
      type: CourseClarificationType.AllProblems,
      courseAlias: payload.courseDetails.alias,
    });
  }, 5 * 60 * 1000);
});
