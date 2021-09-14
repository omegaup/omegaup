import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';
import problemsStore from './problemStore';
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
  const runDetailsResponse: { runDetails: null | types.RunDetails } = {
    runDetails: null,
  };
  const problemDetailsResponse: { problemInfo: null | types.ProblemDetails } = {
    problemInfo: null,
  };
  if (problemAlias) {
    await getProblemDetails({
      problemAlias,
      contestAlias: payload.contest.alias,
      problems: payload.problems,
      response: problemDetailsResponse,
    });
    if (guid) {
      await getRunDetails({ guid, response: runDetailsResponse });
    }
  }

  trackClarifications(payload.clarifications);

  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: problemDetailsResponse.problemInfo,
      problem,
      problems: payload.problems,
      popupDisplayed,
      showNewClarificationPopup,
      guid,
      problemAlias,
      isAdmin: false,
      runDetailsData: runDetailsResponse.runDetails,
      nextSubmissionTimestamp:
        problemDetailsResponse.problemInfo?.nextSubmissionTimestamp,
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
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
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
          'reset-hash': (request: { selectedTab: string; alias: string }) => {
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
    contestAlias,
    problems,
    response,
  }: {
    problemAlias: string;
    contestAlias: string;
    problems: types.NavbarProblemsetProblem[];
    response: { problemInfo: null | types.ProblemInfo };
  }): Promise<void> {
    return api.Problem.details({
      problem_alias: problemAlias,
      prevent_problemset_open: false,
      contest_alias: contestAlias,
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

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      rowcount: 20,
      offset: 0,
    });
  }, 5 * 60 * 1000);
});
