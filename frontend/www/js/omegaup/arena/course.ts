import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';

import Vue from 'vue';
import arena_Course from '../components/arena/Course.vue';
import { getOptionsFromLocation } from './location';
import { PopupDisplayed } from '../components/problem/Details.vue';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
} from './submissions';
import { navigateToProblem, NavigationType } from './navigation';
import {
  CourseClarificationType,
  refreshCourseClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import { myRunsStore } from './runsStore';

OmegaUp.on('ready', () => {
  time.setSugarLocale();

  const commonPayload = types.payloadParsers.CommonPayload();
  const payload = types.payloadParsers.AssignmentDetailsPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';

  trackClarifications(payload.courseDetails.clarifications);

  const arenaCourse = new Vue({
    el: '#main-container',
    components: {
      'omegaup-arena-course': arena_Course,
    },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as types.NavbarProblemsetProblem | null,
      popupDisplayed: PopupDisplayed.None,
      shouldShowRunDetails: false,
      problems: payload.currentAssignment
        .problems as types.NavbarProblemsetProblem[],
      showNewClarificationPopup: false,
      guid: null as null | string,
      problemAlias: null as null | string,
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
          shouldShowRunDetails: this.shouldShowRunDetails,
          shouldShowFirstAssociatedIdentityRunWarning:
            payload.shouldShowFirstAssociatedIdentityRunWarning,
          activeTab,
          guid: this.guid,
          runs: myRunsStore.state.runs,
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
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
          'reset-hash': ({
            selectedTab,
            problemAlias,
          }: {
            selectedTab: string;
            problemAlias: string;
          }) => {
            window.location.replace(`#${selectedTab}/${problemAlias}`);
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));

  setInterval(() => {
    refreshCourseClarifications({
      type: CourseClarificationType.AllProblems,
      courseAlias: payload.courseDetails.alias,
    });
  }, 5 * 60 * 1000);
});
