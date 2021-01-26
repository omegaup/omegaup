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
  const problemInfo = payload.problem;
  const problem: ActiveProblem | null = payload.problem
    ? { runs: payload.problem.runs ?? [], alias: payload.problem.alias }
    : null;

  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problemInfo: problemInfo as types.ProblemInfo | null,
      problems: payload.problems as types.NavbarContestProblem[],
      problem: problem as ActiveProblem,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          problems: this.problems,
          problemInfo: this.problemInfo,
          problem: this.problem,
        },
        on: {
          'navigate-to-problem': (source: ActiveProblem) => {
            api.Problem.details({
              contest_alias: payload.contest.alias,
              problem_alias: source.alias,
              prevent_problemset_open: false,
            })
              .then((problemExt) => {
                const currentProblem = payload.problems?.find(
                  ({ alias }) => alias == problemExt.alias,
                );
                problemExt.title = currentProblem?.text ?? '';
                contestPractice.problemInfo = problemExt;
                source.alias = problemExt.alias;
                source.runs = problemExt.runs ?? [];
                window.location.hash = `#problems/${source.alias}`;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
