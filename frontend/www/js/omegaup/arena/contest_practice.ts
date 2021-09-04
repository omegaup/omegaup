import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
} from './submissions';
import { getOptionsFromLocation } from './location';
import {
  ContestClarification,
  ContestClarificationType,
  ContestClarificationRequest,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import { navigateToProblem, NavigationType } from './navigation';
import { myRunsStore } from './runsStore';
import { setLocationHref } from '../location';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticeDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const popupDisplayed = payload.runDetails
    ? PopupDisplayed.RunDetails
    : PopupDisplayed.None;

  trackClarifications(payload.clarifications);
  if (payload.problemDetails?.runs) {
    for (const run of payload.problemDetails.runs ?? []) {
      trackRun({ run });
    }
  }

  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: payload.problemDetails as types.ProblemInfo | null,
      problem: payload.problem as types.NavbarProblemsetProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      popupDisplayed,
      showNewClarificationPopup: false,
      guid: payload.guid,
      problemAlias: payload.problemAlias,
      isAdmin: false,
      runDetailsData: payload.runDetails,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          contestAdmin: Boolean(payload.adminPayload),
          problems: this.problems,
          users: payload.adminPayload?.users,
          problemInfo: this.problemInfo,
          problem: this.problem,
          clarifications: clarificationStore.state.clarifications,
          popupDisplayed: this.popupDisplayed,
          showNewClarificationPopup: this.showNewClarificationPopup,
          activeTab,
          guid: this.guid,
          problemAlias: this.problemAlias,
          isAdmin: this.isAdmin,
          runs: myRunsStore.state.runs,
          runDetailsData: this.runDetailsData,
        },
        on: {
          'navigate-to-problem': ({
            problem,
          }: {
            problem: types.NavbarProblemsetProblem;
          }) => {
            navigateToProblem({
              type: NavigationType.ForContest,
              problem,
              target: contestPractice,
              problems: this.problems,
              contestAlias: payload.contest.alias,
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
            contestClarificationRequest,
          }: {
            clarification: types.Clarification;
            clearForm: () => void;
            contestClarificationRequest: ContestClarificationRequest;
          }) => {
            if (!clarification) {
              return;
            }
            const contestAlias = payload.contest.alias;
            api.Clarification.create({
              contest_alias: contestAlias,
              problem_alias: clarification.problem_alias,
              username: clarification.author,
              message: clarification.message,
            })
              .then(() => {
                clearForm();
                refreshContestClarifications(contestClarificationRequest);
              })
              .catch(ui.apiError);
          },
          'clarification-response': ({
            clarification,
            contestClarificationRequest,
          }: ContestClarification) => {
            api.Clarification.update(clarification)
              .then(() => {
                refreshContestClarifications(contestClarificationRequest);
              })
              .catch(ui.apiError);
          },
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
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
    Object.assign(
      contestPractice,
      getOptionsFromLocation(window.location.hash),
    );
  }

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      rowcount: 20,
      offset: 0,
    });
  }, 5 * 60 * 1000);
});
