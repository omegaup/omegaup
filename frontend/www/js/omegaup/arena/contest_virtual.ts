import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { dao, types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_Contest from '../components/arena/Contest.vue';
import { getOptionsFromLocation, getProblemAndRunDetails } from './location';
import problemsStore from './problemStore';
import {
  ContestClarification,
  ContestClarificationRequest,
  ContestClarificationType,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import {
  getScoreModeEnum,
  navigateToProblem,
  NavigationType,
} from './navigation';
import clarificationStore from './clarificationsStore';
import {
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
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
              startTime: contest.start_time,
              finishTime: contest.finish_time,
              currentUsername,
              scoreMode: getScoreModeEnum(contest.score_mode),
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
      startTime: payload.contest.start_time,
      finishTime: payload.contest.finish_time,
      currentUsername: commonPayload.currentUsername,
      scoreMode: getScoreModeEnum(payload.contest.score_mode),
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

  let nextSubmissionTimestamp: null | Date = null;
  if (problemDetails?.nextSubmissionTimestamp != null) {
    nextSubmissionTimestamp = time.remoteTime(
      problemDetails?.nextSubmissionTimestamp.getTime(),
    );
  }

  const contestContestant = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest': arena_Contest },
    data: () => ({
      problemInfo: problemDetails,
      problem,
      problems: payload.problems,
      popupDisplayed,
      showNewClarificationPopup,
      guid,
      problemAlias,
      digitsAfterDecimalPoint: 2,
      showPenalty: true,
      nextSubmissionTimestamp,
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
            problem: types.NavbarProblemsetProblem;
            code: string;
            language: string;
            target: Vue & { currentNextSubmissionTimestamp: Date };
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
                target.currentNextSubmissionTimestamp =
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
        },
      });
    },
  });

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.contest.alias,
    isVirtual: true,
    originalProblemsetId: payload.original?.contest.problemset_id,
    startTime: payload.contest.start_time,
    finishTime: payload.contest.finish_time,
    locationProtocol: window.location.protocol,
    locationHost: window.location.host,
    problemsetId: payload.contest.problemset_id,
    scoreboardToken: null,
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: payload.problems,
    currentUsername: commonPayload.currentUsername,
    intervalInMilliseconds: 5 * 60 * 1000,
    scoreMode: getScoreModeEnum(payload.contest.score_mode),
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
