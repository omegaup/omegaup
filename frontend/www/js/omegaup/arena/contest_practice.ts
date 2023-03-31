import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import T from '../lang';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
} from './submissions';
import { getOptionsFromLocation, getProblemAndRunDetails } from './location';
import {
  ContestClarification,
  ContestClarificationType,
  ContestClarificationRequest,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import {
  getScoreModeEnum,
  navigateToProblem,
  NavigationType,
} from './navigation';
import { myRunsStore } from './runsStore';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticeDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const {
    guid,
    popupDisplayed,
    problem,
    problemAlias,
    showNewClarificationPopup,
  } = getOptionsFromLocation(window.location.hash);
  let runDetails: null | types.RunDetails = null;
  let problemDetails: null | types.ProblemDetails = null;
  try {
    ({ runDetails, problemDetails } = await getProblemAndRunDetails({
      contestAlias: payload.contest.alias,
      problems: payload.problems,
      location: window.location.hash,
    }));
  } catch (e: any) {
    ui.apiError(e);
  }
  trackClarifications(payload.clarifications);

  let nextSubmissionTimestamp: null | Date = null;
  if (problemDetails?.nextSubmissionTimestamp != null) {
    nextSubmissionTimestamp = time.remoteTime(
      problemDetails?.nextSubmissionTimestamp.getTime(),
    );
  }

  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: problemDetails,
      problem,
      problems: payload.problems,
      popupDisplayed,
      showNewClarificationPopup,
      guid,
      problemAlias,
      nextSubmissionTimestamp,
      runDetailsData: runDetails,
      shouldShowFirstAssociatedIdentityRunWarning:
        payload.shouldShowFirstAssociatedIdentityRunWarning,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          contestAdmin: payload.contestAdmin,
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
          runs: myRunsStore.state.runs,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
          runDetailsData: this.runDetailsData,
          shouldShowFirstAssociatedIdentityRunWarning: this
            .shouldShowFirstAssociatedIdentityRunWarning,
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
              contestMode: getScoreModeEnum(payload.contest.score_mode),
            });
          },
          'show-run': (request: SubmissionRequest) => {
            api.Run.details({ run_alias: request.guid })
              .then((runDetails) => {
                this.runDetailsData = showSubmission({ request, runDetails });
                if (request.hash) {
                  window.location.hash = request.hash;
                }
              })
              .catch((run) => {
                submitRunFailed({
                  error: run.error,
                  errorname: run.errorname,
                  run,
                });
              });
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
            history.replaceState({ tabName }, 'updateTab', `#${tabName}`);
          },
          'reset-hash': ({
            selectedTab,
            alias,
          }: {
            selectedTab: string;
            alias: string;
          }) => {
            history.replaceState(
              { selectedTab, alias },
              'resetHash',
              `#${selectedTab}/${alias}`,
            );
          },
          'new-submission-popup-displayed': () => {
            if (this.shouldShowFirstAssociatedIdentityRunWarning) {
              this.shouldShowFirstAssociatedIdentityRunWarning = false;
              ui.warning(T.firstSumbissionWithIdentity);
            }
          },
        },
      });
    },
  });

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      rowcount: 20,
      offset: 0,
    });
  }, 5 * 60 * 1000);
});
