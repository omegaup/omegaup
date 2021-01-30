import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice, {
  ActiveProblem,
} from '../components/arena/ContestPractice.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticePayload();
  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: null as types.ProblemInfo | null,
      problems: payload.problems as types.NavbarContestProblem[],
      problem: null as ActiveProblem | null,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          problems: this.problems,
          problemInfo: this.problemInfo,
          problem: this.problem,
          activeTab: 'problems',
        },
        on: {
          'navigate-to-problem': (source: ActiveProblem) => {
            api.Problem.details({
              contest_alias: payload.contest.alias,
              problem_alias: source.alias,
              prevent_problemset_open: false,
            })
              .then((problemInfo) => {
                const currentProblem = payload.problems?.find(
                  ({ alias }) => alias == problemInfo.alias,
                );
                problemInfo.title = currentProblem?.text ?? '';
                contestPractice.problemInfo = problemInfo;
                source.alias = problemInfo.alias;
                source.runs = problemInfo.runs ?? [];
                window.location.hash = `#problems/${source.alias}`;
              })
              .catch(() => {
                ui.dismissNotifications();
                window.location.hash = '#problems';
                contestPractice.problem = null;
              });
          },
        },
      });
    },
  });

  // The hash is of the form `#problems/${alias}`.
  const problemMatch = /#problems\/([^/]+)/.exec(window.location.hash);
  const problemAlias = problemMatch?.[1] ?? null;
  if (problemAlias) {
    contestPractice.problem = { alias: problemAlias, runs: [] };
  }
});
