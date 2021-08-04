import { omegaup, OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
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
  updateRunFallback,
} from './submissions';
import { createChart, onRankingChanged, onRankingEvents } from './ranking';
import { EventsSocket } from './events_socket';
import rankingStore from './rankingStore';
import socketStore from './socketStore';
import { myRunsStore, runsStore } from './runsStore';
import T from '../lang';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const contestAdmin = payload.adminPayload?.contestAdmin ?? false;
  const activeTab = getSelectedValidTab(locationHash[0], contestAdmin);
  if (activeTab !== locationHash[0]) {
    window.location.hash = activeTab;
  }
  trackClarifications(payload.clarifications);

  let ranking: types.ScoreboardRankingEntry[];
  let users: omegaup.UserRank[];
  let rankingChartOptions: Highcharts.Options | null = null;
  let lastTimeUpdated: null | Date;
  if (payload.scoreboard && payload.scoreboardEvents) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
    });
    ranking = rankingInfo.ranking;
    users = rankingInfo.users;
    lastTimeUpdated = rankingInfo.lastTimeUpdated;
    rankingStore.commit('updateRanking', ranking);
    rankingStore.commit('updateMiniRankingUsers', users);
    rankingStore.commit('updateLastTimeUpdated', lastTimeUpdated);

    const startTimestamp = payload.contest.start_time.getTime();
    const finishTimestamp = Math.min(
      payload.contest.finish_time?.getTime() || Infinity,
      Date.now(),
    );
    const { series, navigatorData } = onRankingEvents({
      events: payload.scoreboardEvents,
      currentRanking: rankingInfo.currentRanking,
      startTimestamp,
      finishTimestamp,
    });
    if (series.length) {
      rankingChartOptions = createChart({
        series,
        navigatorData,
        startTimestamp,
        finishTimestamp,
        maxPoints: rankingInfo.maxPoints,
      });
      rankingStore.commit('updateRankingChartOptions', rankingChartOptions);
    }
  }

  const contestContestant = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest': arena_Contest },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as types.NavbarProblemsetProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
      guid: null as null | string,
      problemAlias: null as null | string,
      digitsAfterDecimalPoint: 2,
      showPenalty: true,
      searchResultUsers: [] as types.ListItem[],
      runDetailsData: null as types.RunDetails | null,
      shouldShowRunDetailsForAdmin: false,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest', {
        props: {
          contest: payload.contest,
          contestAdmin,
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
          allRuns: runsStore.state.runs,
          searchResultUsers: this.searchResultUsers,
          runDetailsData: this.runDetailsData,
          shouldShowRunDetailsForAdmin: this.shouldShowRunDetailsForAdmin,
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
          'update-search-result-users-contest': ({
            query,
            contestAlias,
          }: {
            query: string;
            contestAlias: string;
          }) => {
            api.Contest.searchUsers({ query, contest_alias: contestAlias })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
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
          'show-run-all': (request: SubmissionRequest) => {
            const hash = `#runs/show-run:${request.request.guid}/`;
            api.Run.details({ run_alias: request.request.guid })
              .then((runDetails) => {
                showSubmission({ request, runDetails, hash });
              })
              .catch((error) => {
                ui.apiError(error);
              })
              .finally(() => {
                this.popupDisplayed = PopupDisplayed.None;
                this.shouldShowRunDetailsForAdmin = false;
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
          'update:activeTab': (tabName: string) => {
            window.location.replace(`#${tabName}`);
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

  function getSelectedValidTab(tab: string, isAdmin: boolean): string {
    const validTabs = ['problems', 'ranking', 'runs', 'clarifications'];
    const defaultTab = 'problems';
    if (tab === 'runs' && !isAdmin) return defaultTab;
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
  }

  if (payload.adminPayload?.allRuns) {
    for (const run of payload.adminPayload.allRuns) {
      trackRun({ run });
    }
  }

  if (locationHash[1] && locationHash[1].includes('show-run:')) {
    const showRunRegex = /.*\/show-run:([a-fA-F0-9]+)/;
    const showRunMatch = window.location.hash.match(showRunRegex);
    contestContestant.guid = showRunMatch?.[1] ?? null;
    contestContestant.shouldShowRunDetailsForAdmin = true;
    contestContestant.popupDisplayed = PopupDisplayed.RunDetails;
  }

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      offset: 0,
      rowcount: 100,
    });
  }, 5 * 60 * 1000);
});
