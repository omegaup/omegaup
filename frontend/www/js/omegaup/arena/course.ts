import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as time from '../time';
import * as api from '../api';
import * as ui from '../ui';

import Vue from 'vue';
import arena_Course, { ActiveProblem } from '../components/arena/Course.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { getOptionsFromLocation } from './location';
import { navigateToProblem } from './navigation';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
} from './submissions';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const commonPayload = types.payloadParsers.CommonPayload();
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
      problemAlias: null as null | string,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-course', {
        props: {
          course: payload.courseDetails,
          currentAssignment: payload.currentAssignment,
          popupDisplayed: this.popUpDisplayed,
          problemInfo: this.problemInfo,
          problem: this.problem,
          problemAlias: this.problemAlias,
          problems: this.problems,
          showNewClarificationPopup: this.showNewClarificationPopup,
          showRanking: payload.showRanking,
          shouldShowFirstAssociatedIdentityRunWarning:
            payload.shouldShowFirstAssociatedIdentityRunWarning,
          activeTab,
          guid: this.guid,
        },
        on: {
          'navigate-to-problem': ({ problem, runs }: ActiveProblem) => {
            navigateToProblem({
              problem,
              runs,
              target: arenaCourse,
              problems: this.problems,
            });
          },
          'reset-hash': (request: { selectedTab: string; alias: string }) => {
            window.location.replace(`#${request.selectedTab}/${request.alias}`);
          },
          'show-run': (request: SubmissionRequest) => {
            const hash = `#problems/${
              this.problemAlias ?? request.request.problemAlias
            }/show-run:${request.request.guid}/`;
            api.Run.details({ run_alias: request.request.guid })
              .then((runDetails) => {
                showSubmission({ request, runDetails, hash });
              })
              .catch((error) => {
                ui.apiError(error);
                this.popUpDisplayed = PopupDisplayed.None;
              });
          },
          'submit-run': ({
            problem,
            runs,
            code,
            language,
          }: ActiveProblem & { code: string; language: string }) => {
            api.Run.create({
              problem_alias: problem.alias,
              language: language,
              source: code,
            })
              .then((response) => {
                submitRun({
                  runs,
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
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
          },
        },
      });
    },
  });

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(arenaCourse, getOptionsFromLocation(window.location.hash));
});
