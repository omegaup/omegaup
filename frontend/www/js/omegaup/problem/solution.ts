import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';
import { types } from '../api_types';

import problem_Solution from '../components/problem/Solution.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemDetailsPayload();
  const problemSolution = new Vue({
    el: '#problem-solution',
    components: {
      'omegaup-problem-solution': problem_Solution,
    },
    data: () => ({
      status: payload.solution_status || 'not_logged_in',
      solution: <types.ProblemStatement | null>null,
      allTokens: 0,
      availableTokens: 0,
    }),
    render: function (createElement) {
      return createElement('omegaup-problem-solution', {
        props: {
          status: this.status,
          solution: this.solution,
          allTokens: this.allTokens,
          availableTokens: this.availableTokens,
        },
        on: {
          'unlock-solution': () => {
            api.Problem.solution(
              {
                problem_alias: payload.alias,
                forfeit_problem: true,
              },
              { quiet: true },
            )
              .then((data) => {
                if (!data.solution) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                problemSolution.status = 'unlocked';
                problemSolution.solution = data.solution;
                ui.info(
                  ui.formatString(T.solutionTokens, {
                    available: problemSolution.availableTokens - 1,
                    total: problemSolution.allTokens,
                  }),
                );
              })
              .catch((error) => {
                if (error.httpStatusCode == 404) {
                  ui.error(T.wordsProblemOrSolutionNotExist);
                  return;
                }
                ui.apiError(error);
              });
          },
          'get-tokens': () => {
            api.ProblemForfeited.getCounts()
              .then((data) => {
                problemSolution.allTokens = data.allowed;
                problemSolution.availableTokens = data.allowed - data.seen;
                if (problemSolution.availableTokens <= 0) {
                  ui.warning(T.solutionNoTokens);
                }
              })
              .catch(ui.apiError);
          },
          'get-solution': () => {
            if (payload.solution_status === 'unlocked') {
              api.Problem.solution(
                { problem_alias: payload.alias },
                { quiet: true },
              )
                .then((data) => {
                  if (!data.solution) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  problemSolution.solution = data.solution;
                })
                .catch((error) => {
                  if (error.httpStatusCode == 404) {
                    ui.error(T.wordsProblemOrSolutionNotExist);
                    return;
                  }
                  ui.apiError(error);
                });
            }
          },
        },
      });
    },
  });
});
