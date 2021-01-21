import { OmegaUp } from '../omegaup';
import * as time from '../time';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import Vue from 'vue';
import arena_ContestPractice from '../components/arena/ContestPractice.vue';

OmegaUp.on('ready', () => {
  time.setSugarLocale();
  const payload = types.payloadParsers.ContestPracticePayload();
  const contestPractice = new Vue({
    el: '#main-container',
    components: { 'omegaup-arena-contest-practice': arena_ContestPractice },
    data: () => ({
      problem: <types.ProblemInfo | null>null,
      problems: <types.NavbarContestProblem[]>payload.problems,
    }),
    render: function (createElement) {
      return createElement('omegaup-arena-contest-practice', {
        props: {
          contest: payload.contest,
          problems: this.problems,
          problem: this.problem,
        },
        on: {
          'navigate-to-problem': (
            source: {
              activeProblem: string | null;
              runs: types.Run[] | undefined;
              problemAlias: string;
            },
            problemAlias: string,
          ) => {
            api.Problem.details({
              contest_alias: payload.contest.alias,
              problem_alias: problemAlias,
              prevent_problemset_open: false,
            })
              .then((problem_ext) => {
                contestPractice.problem = problem_ext;
                const currentProblem = contestPractice.problems.find(
                  ({ alias }) => alias == problem_ext.alias,
                );
                problem_ext.title = currentProblem?.text ?? '';
                source.activeProblem = problem_ext.alias;
                source.runs = problem_ext.runs;
                window.location.hash = `#problems/${problemAlias}`;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
