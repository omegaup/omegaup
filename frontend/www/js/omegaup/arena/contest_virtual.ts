import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { dao, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_Contest from '../components/arena/Contest.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { getOptionsFromLocation } from './location';
import problemsStore from './problemStore';
import {
  ContestClarification,
  ContestClarificationRequest,
  ContestClarificationType,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import { navigateToProblem, NavigationType } from './navigation';
import clarificationStore from './clarificationsStore';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
} from './submissions';
import { onVirtualRankingChanged } from './ranking';
import { EventsSocket } from './events_socket';
import rankingStore from './rankingStore';
import socketStore from './socketStore';
import { myRunsStore } from './runsStore';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';
  const { guid, problemAlias } = getOptionsFromLocation(window.location.hash);
  let runDetails: null | types.RunDetails = null;
  let problemDetails: null | types.ProblemDetails = null;
  if (problemAlias) {
    try {
      [problemDetails, runDetails] = await Promise.all([
        api.Problem.details({
          problem_alias: problemAlias,
          prevent_problemset_open: false,
          contest_alias: payload.contest.alias,
        }),
        guid ? api.Run.details({ run_alias: guid }) : Promise.resolve(null),
      ]);
      for (const run of problemDetails.runs ?? []) {
        trackRun({ run });
      }
      const currentProblem = payload.problems?.find(
        ({ alias }: { alias: string }) => alias === problemDetails?.alias,
      );
      problemDetails.title = currentProblem?.text ?? '';
      problemsStore.commit('addProblem', problemDetails);
    } catch (e) {
      ui.apiError(e);
    }
  }
  trackClarifications(payload.clarifications);

  // Refresh after time T
  const refreshTime: number = 30 * 1000; // 30 seconds

  function loadVirtualRanking({
    problems,
    contest,
    originalContest,
    currentUsername,
  }: {
    virtualContestRefreshInterval: ReturnType<typeof setInterval> | null;
    problems: types.NavbarProblemsetProblem[];
    contest: types.ContestPublicDetails;
    originalContest: dao.Contests | null;
    currentUsername: string;
  }): void {
    api.Problemset.scoreboard({
      problemset_id: contest.problemset_id,
    })
      .then((scoreboard) => {
        api.Problemset.scoreboardEvents({
          problemset_id: originalContest?.problemset_id,
        })
          .then((response) => {
            onVirtualRankingChanged({
              scoreboard,
              scoreboardEvents: response.events,
              problems,
              contest,
              currentUsername,
            });
          })
          .catch(ui.apiError);
      })
      .catch(ui.ignoreError);
  }

  // Cache scoreboard data for virtual contest
  let virtualContestRefreshInterval: ReturnType<
    typeof setInterval
  > | null = null;
  if (
    payload.scoreboard &&
    payload.scoreboardEvents &&
    payload.original?.scoreboard &&
    payload.original?.scoreboardEvents
  ) {
    onVirtualRankingChanged({
      scoreboard: payload.scoreboard,
      scoreboardEvents: payload.original.scoreboardEvents,
      problems: payload.problems,
      contest: payload.contest,
      currentUsername: commonPayload.currentUsername,
    });
    virtualContestRefreshInterval = setInterval(() => {
      loadVirtualRanking({
        virtualContestRefreshInterval,
        problems: payload.problems,
        contest: payload.contest,
        originalContest: payload.original?.contest ?? null,
        currentUsername: commonPayload.currentUsername,
      });
    }, refreshTime);
  }

  const contestContestant = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest': arena_Contest },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as types.NavbarProblemsetProblem | null,
      problems: payload.problems,
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
      guid: null as null | string,
      problemAlias: null as null | string,
      digitsAfterDecimalPoint: 2,
      showPenalty: true,
      nextSubmissionTimestamp: problemDetails?.nextSubmissionTimestamp,
      runDetailsData: runDetails,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest', {
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
          miniRankingUsers: rankingStore.state.miniRankingUsers,
          ranking: rankingStore.state.ranking,
          rankingChartOptions: rankingStore.state.rankingChartOptions,
          lastUpdated: rankingStore.state.lastTimeUpdated,
          digitsAfterDecimalPoint: this.digitsAfterDecimalPoint,
          showPenalty: this.showPenalty,
          socketStatus: socketStore.state.socketStatus,
          runs: myRunsStore.state.runs,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
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
              target: contestContestant,
              problems: this.problems,
              contestAlias: payload.contest.alias,
            });
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
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          'submit-run': ({
            problem,
            code,
            language,
            target,
          }: {
            problem: types.NavbarProblemsetProblem;
            code: string;
            language: string;
            target: Vue & { nextSubmissionTimestamp: Date };
          }) => {
            api.Run.create({
              contest_alias: payload.contest.alias,
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
                target.nextSubmissionTimestamp =
                  response.nextSubmissionTimestamp;

                if (
                  Object.prototype.hasOwnProperty.call(
                    problemsStore.state.problems,
                    problem.alias,
                  )
                ) {
                  const problemInfo =
                    problemsStore.state.problems[problem.alias];
                  problemInfo.nextSubmissionTimestamp =
                    response.nextSubmissionTimestamp;
                  problemsStore.commit('addProblem', problemInfo);
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
            api.Clarification.create({
              contest_alias: payload.contest.alias,
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

  // This needs to be set here and not at the top because it depends
  // on the `navigate-to-problem` callback being invoked, and that is
  // not the case if this is set a priori.
  Object.assign(
    contestContestant,
    getOptionsFromLocation(window.location.hash),
  );

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.contest.alias,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.contest.problemset_id,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: payload.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
  });
  socket.connect();

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      offset: 0,
      rowcount: 100,
    });
  }, 5 * 60 * 1000);
});
