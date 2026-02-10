import Vue from 'vue';
import * as api from '../api';
import { types } from '../api_types';
import arena_Contest from '../components/arena/Contest.vue';
import { DisqualificationType } from '../components/arena/Runs.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import T from '../lang';
import { omegaup, OmegaUp } from '../omegaup';
import * as time from '../time';
import * as ui from '../ui';
import {
  ContestClarification,
  ContestClarificationRequest,
  ContestClarificationType,
  refreshContestClarifications,
  trackClarifications,
} from './clarifications';
import clarificationStore from './clarificationsStore';
import { EventsSocket } from './events_socket';
import { getOptionsFromLocation, getProblemAndRunDetails } from './location';
import {
  getScoreModeEnum,
  navigateToProblem,
  NavigationType,
} from './navigation';
import problemsStore from './problemStore';
import { createChart, onRankingChanged, onRankingEvents } from './ranking';
import rankingStore from './rankingStore';
import { myRunsStore, RunFilters, runsStore } from './runsStore';
import { enforceSingleTab } from './singleTabEnforcer';
import socketStore from './socketStore';
import {
  onRefreshRuns,
  showSubmission,
  SubmissionRequest,
  submitRun,
  submitRunFailed,
  trackRun,
  updateRunFallback,
} from './submissions';

OmegaUp.on('ready', async () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substr(1).split('/');
  const contestAdmin = Boolean(payload.adminPayload);

  // Enforce single tab for non-admin contestants
  let isBlocked = false;
  let blockedMessage: string | null = null;

  if (!contestAdmin) {
    const TAB_ENFORCER_TIMEOUT_MS = 1000;

    await new Promise<void>((resolve) => {
      let settled = false;

      const timeoutId = window.setTimeout(() => {
        if (settled) {
          return;
        }
        settled = true;
        resolve();
      }, TAB_ENFORCER_TIMEOUT_MS);

      enforceSingleTab(payload.contest.alias, (message: string) => {
        if (settled) {
          return;
        }
        settled = true;
        isBlocked = true;
        blockedMessage = message;
        window.clearTimeout(timeoutId);
        resolve();
      });
    });
  }

  const activeTab = getSelectedValidTab(locationHash[0], contestAdmin);
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

  let ranking: types.ScoreboardRankingEntry[];
  let users: omegaup.UserRank[];
  let rankingChartOptions: Highcharts.Options | null = null;
  let lastTimeUpdated: null | Date;
  if (payload.scoreboard && payload.scoreboardEvents) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
      scoreMode: getScoreModeEnum(payload.contest.score_mode),
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

  let nextSubmissionTimestamp: null | Date = null;
  if (problemDetails?.nextSubmissionTimestamp != null) {
    nextSubmissionTimestamp = problemDetails?.nextSubmissionTimestamp;
  }
  let nextExecutionTimestamp: null | Date = null;
  if (problemDetails?.nextExecutionTimestamp != null) {
    nextExecutionTimestamp = problemDetails?.nextExecutionTimestamp;
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
      searchResultUsers: [] as types.ListItem[],
      nextSubmissionTimestamp,
      nextExecutionTimestamp,
      runDetailsData: runDetails,
      shouldShowFirstAssociatedIdentityRunWarning:
        payload.shouldShowFirstAssociatedIdentityRunWarning,
      isBlocked,
      blockedMessage,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest', {
        props: {
          lockdown: commonPayload.omegaUpLockDown,
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
          totalRuns: runsStore.state.totalRuns,
          searchResultUsers: this.searchResultUsers,
          runDetailsData: this.runDetailsData,
          nextSubmissionTimestamp: this.nextSubmissionTimestamp,
          nextExecutionTimestamp: this.nextExecutionTimestamp,
          shouldShowFirstAssociatedIdentityRunWarning: this
            .shouldShowFirstAssociatedIdentityRunWarning,
          submissionDeadline: payload.submissionDeadline,
          isBlocked: this.isBlocked,
          blockedMessage: this.blockedMessage,
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
              })
              .finally(() => {
                this.popupDisplayed = PopupDisplayed.None;
              });
          },
          'execute-run': ({
            target,
          }: {
            target: Vue & { currentNextExecutionTimestamp: Date };
          }) => {
            api.Run.execute()
              .then(time.remoteTimeAdapter)
              .then((response) => {
                target.currentNextExecutionTimestamp =
                  response.nextExecutionTimestamp;
              })
              .catch(ui.apiError);
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
          rejudge: (run: types.Run) => {
            api.Run.rejudge({ run_alias: run.guid, debug: false })
              .then(() => {
                run.status = 'rejudging';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          requalify: (run: types.Run) => {
            api.Run.requalify({ run_alias: run.guid })
              .then(() => {
                run.type = 'normal';
                updateRunFallback({ run });
              })
              .catch(ui.ignoreError);
          },
          disqualify: ({
            run,
            disqualificationType,
          }: {
            run: types.Run;
            disqualificationType: DisqualificationType;
          }) => {
            const request: {
              run_alias: null | string;
              problem_alias: null | string;
              username: string;
              contest_alias: null | string;
            } = {
              run_alias: run.guid,
              contest_alias: payload.contest.alias,
              problem_alias: null,
              username: run.username,
            };
            let message = T.runDisqualifyConfirm;
            if (disqualificationType === DisqualificationType.ByGUID) {
              request.contest_alias = null;
            } else if (disqualificationType === DisqualificationType.ByUser) {
              request.run_alias = null;
              message = T.runDisqualifyByUserConfirm;
            } else if (
              disqualificationType === DisqualificationType.ByProblem
            ) {
              request.run_alias = null;
              request.problem_alias = run.alias;
              message = T.runDisqualifyByProblemConfirm;
            }
            if (!window.confirm(message)) {
              return;
            }
            api.Run.disqualify(request)
              .then(({ runs }) => {
                const filteredRuns = runsStore.state.runs.filter(
                  (filteredRun) =>
                    runs.some(
                      (disqualifiedRun) =>
                        disqualifiedRun.guid === filteredRun.guid,
                    ),
                );
                for (const disqualifiedRun of filteredRuns) {
                  disqualifiedRun.type = 'disqualified';
                  updateRunFallback({ run: disqualifiedRun });
                }
              })
              .catch(ui.ignoreError);
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
            if (!alias) {
              history.replaceState(
                { selectedTab },
                'resetHash',
                `#${selectedTab}`,
              );
              return;
            }
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
          'apply-filter': ({
            filter,
            value,
          }: {
            filter: 'verdict' | 'language' | 'username' | 'status' | 'offset';
            value: string;
          }) => {
            if (value != '') {
              const newFilter: RunFilters = { [filter]: value };
              runsStore.commit('applyFilter', newFilter);
            } else {
              runsStore.commit('removeFilter', filter);
            }
            refreshRuns();
          },
        },
      });
    },
  });

  const socket = new EventsSocket({
    disableSockets: false,
    problemsetAlias: payload.contest.alias,
    isVirtual: false,
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

  function refreshRuns(): void {
    api.Contest.runs({
      contest_alias: payload.contest.alias,
      show_all: true,
      problem_alias: runsStore.state.filters?.problem,
      offset: runsStore.state.filters?.offset,
      rowcount: runsStore.state.filters?.rowcount,
      verdict: runsStore.state.filters?.verdict,
      language: runsStore.state.filters?.language,
      username: runsStore.state.filters?.username,
      status: runsStore.state.filters?.status,
    })
      .then(time.remoteTimeAdapter)
      .then((response) => {
        onRefreshRuns({ runs: response.runs, totalRuns: response.totalRuns });
      })
      .catch(ui.apiError);
  }

  function getSelectedValidTab(tab: string, isAdmin: boolean): string {
    const validTabs = ['problems', 'ranking', 'runs', 'clarifications'];
    const defaultTab = 'problems';
    if (tab === 'runs' && !isAdmin) return defaultTab;
    const isValidTab = validTabs.includes(tab);
    return isValidTab ? tab : defaultTab;
  }

  if (payload.adminPayload?.allRuns) {
    runsStore.commit('setTotalRuns', payload.adminPayload.totalRuns);
    for (const run of payload.adminPayload.allRuns) {
      trackRun({ run });
    }
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
