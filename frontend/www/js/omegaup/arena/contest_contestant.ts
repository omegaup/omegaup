import { omegaup, OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_Contest from '../components/arena/Contest.vue';
import { PopupDisplayed } from '../components/problem/Details.vue';
import { getOptionsFromLocation } from './location';
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
} from './submissions';
import { createChart, onRankingChanged, onRankingEvents } from './ranking';
import { myRunsStore } from './runsStore';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const activeTab = window.location.hash
    ? window.location.hash.substr(1).split('/')[0]
    : 'problems';

  trackClarifications(payload.clarifications);

  let ranking: types.ScoreboardRankingEntry[];
  let users: omegaup.UserRank[];
  let rankingChartOptions: Highcharts.Options | null = null;
  if (payload.scoreboard && payload.scoreboardEvents) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
    });
    ranking = rankingInfo.ranking;
    users = rankingInfo.users;

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
      ranking,
      rankingChartOptions,
      users,
      lastUpdated: new Date(0),
      digitsAfterDecimalPoint: 2,
      showPenalty: true,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest', {
        props: {
          contest: payload.contest,
          contestAdmin: payload.contestAdmin,
          problems: this.problems,
          users: payload.users,
          problemInfo: this.problemInfo,
          problem: this.problem,
          clarifications: clarificationStore.state.clarifications,
          popupDisplayed: this.popupDisplayed,
          showNewClarificationPopup: this.showNewClarificationPopup,
          activeTab,
          guid: this.guid,
          problemAlias: this.problemAlias,
          minirankingUsers: this.users,
          ranking: this.ranking,
          rankingChartOptions: this.rankingChartOptions,
          lastUpdated: this.lastUpdated,
          digitsAfterDecimalPoint: this.digitsAfterDecimalPoint,
          showPenalty: this.showPenalty,
          runs: myRunsStore.state.runs,
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

  setInterval(() => {
    refreshContestClarifications({
      type: ContestClarificationType.AllProblems,
      contestAlias: payload.contest.alias,
      offset: 0,
      rowcount: 100,
    });
  }, 5 * 60 * 1000);
});
