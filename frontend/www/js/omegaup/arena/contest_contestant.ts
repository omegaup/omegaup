import { omegaup, OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_Contest from '../components/arena/Contest.vue';
import { ActiveProblem } from '../components/arena/ContestPractice.vue';
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
  let currentRanking: { [username: string]: number };
  if (payload.scoreboard && payload.scoreboardEvents) {
    const rankingInfo = onRankingChanged({
      scoreboard: payload.scoreboard,
      currentUsername: commonPayload.currentUsername,
      navbarProblems: payload.problems,
    });
    ranking = rankingInfo.ranking;
    users = rankingInfo.users;
    currentRanking = rankingInfo.currentRanking;

    const { series, navigatorData } = onRankingEvents({
      events: payload.scoreboardEvents,
      currentRanking,
      startTimestamp: payload.contest.start_time.getTime(),
      finishTimestamp: Math.min(
        payload.contest.finish_time.getTime(),
        Date.now(),
      ),
    });
    createChart({ series, navigatorData });
  }

  const contestContestant = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest': arena_Contest },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problem: null as ActiveProblem | null,
      problems: payload.problems as types.NavbarProblemsetProblem[],
      popupDisplayed: PopupDisplayed.None,
      showNewClarificationPopup: false,
      guid: null as null | string,
      problemAlias: null as null | string,
      ranking,
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
          lastUpdated: this.lastUpdated,
          digitsAfterDecimalPoint: this.digitsAfterDecimalPoint,
          showPenalty: this.showPenalty,
        },
        on: {
          'navigate-to-problem': ({ problem, runs }: ActiveProblem) => {
            navigateToProblem({
              type: NavigationType.ForContest,
              problem,
              runs,
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
